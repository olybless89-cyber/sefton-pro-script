<?php

namespace App\Services;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Local stand-in for the script vendor's remote API (app.getonlinetrader.pro),
 * which the courses (membership) and trading-signal features depended on and
 * which no longer responds.
 *
 * It answers the same paths with the same JSON shapes, using this site's own
 * database, so the existing controllers, Livewire components and views work
 * unchanged. Paths it doesn't handle return null and fall through to the
 * original remote call.
 */
class LocalTraderApi
{
    public function handle(string $url, array $data, string $method): ?Response
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
        $data = array_merge($query, $data);

        $routes = [
            // courses / membership
            '#^/courses$#'                    => 'courses',
            '#^/categories$#'                 => 'categories',
            '#^/add-category$#'               => 'addCategory',
            '#^/delete-cat/(\d+)$#'           => 'deleteCategory',
            '#^/add-course$#'                 => 'addCourse',
            '#^/update-course$#'              => 'updateCourse',
            '#^/delete-course/(\d+)$#'        => 'deleteCourse',
            '#^/course$#'                     => 'courseInfo',
            '#^/courses-lessons/(\d+)$#'      => 'courseLessons',
            '#^/lessons-without-course$#'     => 'lessonsWithoutCourse',
            '#^/add-lesson$#'                 => 'addLesson',
            '#^/update-lesson$#'              => 'updateLesson',
            '#^/delete-lesson/(\d+)$#'        => 'deleteLesson',
            '#^/lesson$#'                     => 'lesson',
            '#^/user-course$#'                => 'userCourse',
            '#^/user-courses$#'               => 'userCourses',
            '#^/buy-course$#'                 => 'buyCourse',
            // trading signals
            '#^/trading-signals$#'            => 'signals',
            '#^/post-signals$#'               => 'postSignal',
            '#^/publish-signals/(\d+)$#'      => 'publishSignal',
            '#^/update-result$#'              => 'updateResult',
            '#^/delete-signal/(\d+)$#'        => 'deleteSignal',
            '#^/signal$#'                     => 'signal',
            '#^/signal-settings$#'            => 'signalSettings',
            '#^/save-signal-settings$#'       => 'saveSignalSettings',
            '#^/chat-id$#'                    => 'chatId',
            '#^/delete-id$#'                  => 'deleteChatId',
            '#^/signal-subscribers$#'         => 'subscribers',
            '#^/subscription$#'               => 'subscription',
            '#^/subscribe$#'                  => 'subscribe',
            '#^/renew-subscription$#'         => 'renewSubscription',
            // feature switches (Settings → modules)
            '#^/set-modules$#'                => 'setModules',
        ];

        foreach ($routes as $pattern => $handler) {
            if (preg_match($pattern, $path, $m)) {
                try {
                    return $this->{$handler}($data, $m[1] ?? null);
                } catch (\Throwable $e) {
                    report($e);
                    return $this->fail('Something went wrong: ' . $e->getMessage(), 500);
                }
            }
        }

        return null;
    }

    // ------------------------------------------------------------------ helpers

    private function respond($data = null, string $message = 'Action Successful', bool $error = false, int $status = 200): Response
    {
        $body = json_encode(['error' => $error, 'message' => $message, 'data' => $data]);
        return new Response(new PsrResponse($status, ['Content-Type' => 'application/json'], $body));
    }

    private function fail(string $message, int $status = 422): Response
    {
        return $this->respond(null, $message, true, $status);
    }

    private function course($id)
    {
        return DB::table('courses')->where('id', $id)->first();
    }

    private function lessonsFor($courseId)
    {
        return DB::table('course_lessons')->where('course_id', $courseId)->orderBy('id')->get()->all();
    }

    private function wrapCourse($c): array
    {
        return [
            'course'  => $c,
            'lessons' => $this->lessonsFor($c->id),
            'users'   => DB::table('user_courses')->where('course_id', $c->id)->pluck('user_id')->all(),
        ];
    }

    private function categoryList(): array
    {
        return DB::table('course_categories')->orderBy('category')->get()->all();
    }

    private function settingsCont()
    {
        return DB::table('settings_conts')->where('id', 1)->first();
    }

    private function updateSettingsCont(array $values): void
    {
        if (DB::table('settings_conts')->where('id', 1)->exists()) {
            DB::table('settings_conts')->where('id', 1)->update($values + ['updated_at' => now()]);
        } else {
            DB::table('settings_conts')->insert($values + ['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function amountOrNull($v)
    {
        return ($v === null || $v === '') ? null : (float) $v;
    }

    // ------------------------------------------------------------ courses: admin

    private function courses(array $d)
    {
        $q = DB::table('courses')->orderByDesc('id');
        if (! empty($d['value'])) {
            $q->where('course_title', 'like', '%' . $d['value'] . '%');
        }
        $list = array_map(fn ($c) => $this->wrapCourse($c), $q->get()->all());

        return $this->respond([
            'courses'    => ['data' => $list],
            'categories' => $this->categoryList(),
        ]);
    }

    private function categories(array $d)
    {
        $list = array_map(function ($cat) {
            return [
                'category' => $cat,
                'lessons'  => DB::table('course_lessons')->whereNull('course_id')
                    ->where('category', $cat->category)->orderBy('id')->get()->all(),
            ];
        }, $this->categoryList());

        return $this->respond(['categories' => $list]);
    }

    private function addCategory(array $d)
    {
        $name = trim($d['category'] ?? '');
        if ($name === '') {
            return $this->fail('Please enter a category name');
        }
        if (DB::table('course_categories')->where('category', $name)->exists()) {
            return $this->fail('This category already exists');
        }
        DB::table('course_categories')->insert(['category' => $name, 'created_at' => now(), 'updated_at' => now()]);
        return $this->respond(null, 'Category added successfully');
    }

    private function deleteCategory(array $d, $id)
    {
        DB::table('course_categories')->where('id', $id)->delete();
        return $this->respond(null, 'Category deleted successfully');
    }

    private function courseValues(array $d): array
    {
        $amount = $this->amountOrNull($d['amount'] ?? null);
        $category = $d['category'] ?? null;
        return [
            'course_title' => $d['title'] ?? 'Untitled course',
            'course_image' => $d['image_url'] ?? null,
            'description'  => $d['desc'] ?? null,
            'amount'       => $amount,
            'paid'         => $amount ? 1 : 0,
            'category'     => ($category === 'Null' || $category === '') ? null : $category,
            'updated_at'   => now(),
        ];
    }

    private function addCourse(array $d)
    {
        DB::table('courses')->insert($this->courseValues($d) + ['created_at' => now()]);
        return $this->respond(null, 'Course created successfully');
    }

    private function updateCourse(array $d)
    {
        if (! $this->course($d['course_id'] ?? 0)) {
            return $this->fail('Course not found', 404);
        }
        DB::table('courses')->where('id', $d['course_id'])->update($this->courseValues($d));
        return $this->respond(null, 'Course updated successfully');
    }

    private function deleteCourse(array $d, $id)
    {
        DB::table('course_lessons')->where('course_id', $id)->delete();
        DB::table('user_courses')->where('course_id', $id)->delete();
        DB::table('courses')->where('id', $id)->delete();
        return $this->respond(null, 'Course and its lessons deleted successfully');
    }

    private function courseLessons(array $d, $id)
    {
        $course = $this->course($id);
        if (! $course) {
            return $this->fail('Course not found', 404);
        }
        return $this->respond(['course' => $course, 'lessons' => ['data' => $this->lessonsFor($id)]]);
    }

    private function lessonsWithoutCourse(array $d)
    {
        return $this->respond([
            'lessons'    => DB::table('course_lessons')->whereNull('course_id')->orderByDesc('id')->get()->all(),
            'categories' => $this->categoryList(),
        ]);
    }

    private function lessonValues(array $d): array
    {
        $courseId = $d['course_id'] ?? null;
        $cat = $d['cat'] ?? ($d['course_category_id'] ?? null);
        return [
            'course_id'   => ($courseId === '' || $courseId === null) ? null : (int) $courseId,
            'title'       => $d['title'] ?? 'Untitled lesson',
            'description' => $d['desc'] ?? null,
            'length'      => $d['length'] ?? null,
            'video_link'  => $d['videolink'] ?? null,
            'thumbnail'   => $d['thumbnail'] ?? null,
            'locked'      => (($d['preview'] ?? 'false') === 'true') ? 'true' : 'false',
            'category'    => ($cat === 'Null' || $cat === '') ? null : $cat,
            'updated_at'  => now(),
        ];
    }

    private function addLesson(array $d)
    {
        DB::table('course_lessons')->insert($this->lessonValues($d) + ['created_at' => now()]);
        return $this->respond(null, 'Lesson added successfully');
    }

    private function updateLesson(array $d)
    {
        $id = $d['lesson_id'] ?? 0;
        $existing = DB::table('course_lessons')->where('id', $id)->first();
        if (! $existing) {
            return $this->fail('Lesson not found', 404);
        }
        $values = $this->lessonValues($d);
        // the edit form doesn't always resend these
        if (empty($d['course_id'])) {
            $values['course_id'] = $existing->course_id;
        }
        if (! isset($d['preview'])) {
            $values['locked'] = $existing->locked;
        }
        DB::table('course_lessons')->where('id', $id)->update($values);
        return $this->respond(null, 'Lesson updated successfully');
    }

    private function deleteLesson(array $d, $id)
    {
        DB::table('course_lessons')->where('id', $id)->delete();
        return $this->respond(null, 'Lesson deleted successfully');
    }

    // --------------------------------------------------------- courses: customer

    // "/course"
    private function courseInfo(array $d)
    {
        $course = $this->course($d['courseId'] ?? 0);
        if (! $course) {
            return $this->fail('Course not found', 404);
        }
        return $this->respond(['course' => $course, 'lessons' => $this->lessonsFor($course->id)]);
    }

    private function lesson(array $d)
    {
        $lesson = DB::table('course_lessons')->where('id', $d['lessonId'] ?? 0)->first();
        if (! $lesson) {
            return $this->fail('Lesson not found', 404);
        }
        $siblings = DB::table('course_lessons');
        $lesson->course_id
            ? $siblings->where('course_id', $lesson->course_id)
            : $siblings->whereNull('course_id')->where('category', $lesson->category);

        $next = (clone $siblings)->where('id', '>', $lesson->id)->orderBy('id')->first();
        $prev = (clone $siblings)->where('id', '<', $lesson->id)->orderByDesc('id')->first();

        // the lesson page builds links from these, so they are lesson IDs
        return $this->respond([
            'lesson'         => $lesson,
            'nextlesson'     => $next->id ?? null,
            'previousLesson' => $prev->id ?? null,
        ]);
    }

    private function userCourse(array $d)
    {
        $owned = DB::table('user_courses')
            ->where('user_id', $d['clientId'] ?? 0)->where('course_id', $d['courseId'] ?? 0)->exists();
        $course = $owned ? $this->course($d['courseId']) : null;

        return $this->respond([
            'course'  => $course,
            'lessons' => $course ? $this->lessonsFor($course->id) : [],
        ]);
    }

    private function userCourses(array $d)
    {
        $ids = DB::table('user_courses')->where('user_id', $d['userId'] ?? 0)->orderByDesc('id')->pluck('course_id');
        $list = [];
        foreach ($ids as $id) {
            if ($c = $this->course($id)) {
                $list[] = ['course' => $c, 'lessons' => $this->lessonsFor($c->id)];
            }
        }
        return $this->respond(['courses' => $list]);
    }

    private function buyCourse(array $d)
    {
        $course = $this->course($d['courseId'] ?? 0);
        if (! $course) {
            return $this->fail('Course not found', 404);
        }
        DB::table('user_courses')->updateOrInsert(
            ['user_id' => $d['clientId'], 'course_id' => $course->id],
            ['amount' => $course->amount ?? 0, 'created_at' => now(), 'updated_at' => now()]
        );
        return $this->respond(null, 'Course purchased successfully. You can find it under My Courses.');
    }

    // ------------------------------------------------------------------ signals

    private function signals(array $d)
    {
        return $this->respond(['signals' => DB::table('trading_signals')->orderByDesc('id')->limit(200)->get()->all()]);
    }

    private function signal(array $d)
    {
        $s = DB::table('trading_signals')->where('id', $d['signalId'] ?? ($d['id'] ?? 0))->first();
        return $s ? $this->respond(['signal' => $s]) : $this->fail('Signal not found', 404);
    }

    private function postSignal(array $d)
    {
        if (empty($d['pair']) || empty($d['direction'])) {
            return $this->fail('Please enter the currency pair and trade direction');
        }
        $tp = implode(' / ', array_filter([$d['tp1'] ?? null, $d['tp2'] ?? null], fn ($v) => $v !== null && $v !== ''));
        $sl = implode(' / ', array_filter([$d['sl1'] ?? null, $d['sl2'] ?? null], fn ($v) => $v !== null && $v !== ''));

        DB::table('trading_signals')->insert([
            'reference'       => strtoupper(Str::random(8)),
            'trade_direction' => $d['direction'],
            'currency_pair'   => $d['pair'],
            'price'           => $d['price'] ?? null,
            'take_profit'     => $tp ?: null,
            'stop_loss'       => $sl ?: null,
            'status'          => 'unpublished',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        return $this->respond(null, 'Signal created. Publish it to send it to subscribers.');
    }

    private function signalMessage($s): string
    {
        return "*New Trading Signal* ({$s->reference})\n"
            . "Pair: {$s->currency_pair}\n"
            . "Direction: {$s->trade_direction}\n"
            . "Entry: {$s->price}\n"
            . "Take profit: {$s->take_profit}\n"
            . "Stop loss: {$s->stop_loss}";
    }

    private function publishSignal(array $d, $id)
    {
        $s = DB::table('trading_signals')->where('id', $id)->first();
        if (! $s) {
            return $this->fail('Signal not found', 404);
        }
        if ($s->status === 'published') {
            return $this->fail('This signal has already been published');
        }
        DB::table('trading_signals')->where('id', $id)->update(['status' => 'published', 'updated_at' => now()]);
        $s->status = 'published';

        return $this->respond(
            ['chat_id' => optional($this->settingsCont())->chat_id, 'message' => $this->signalMessage($s)],
            'Signal published successfully'
        );
    }

    private function updateResult(array $d)
    {
        $s = DB::table('trading_signals')->where('id', $d['signalId'] ?? 0)->first();
        if (! $s) {
            return $this->fail('Signal not found', 404);
        }
        DB::table('trading_signals')->where('id', $s->id)->update(['result' => $d['result'] ?? null, 'updated_at' => now()]);

        return $this->respond([
            'chat_id' => optional($this->settingsCont())->chat_id,
            'message' => "*Signal result* ({$s->reference})\n{$s->currency_pair} {$s->trade_direction}: " . ($d['result'] ?? ''),
        ], 'Signal result updated');
    }

    private function deleteSignal(array $d, $id)
    {
        DB::table('trading_signals')->where('id', $id)->delete();
        return $this->respond(null, 'Signal deleted successfully');
    }

    private function signalSettings(array $d)
    {
        $s = $this->settingsCont();
        return $this->respond(['settings' => [
            'signal_monthly_fee' => $s->signal_monthly_fee ?? 0,
            'signal_quartly_fee' => $s->signal_quartly_fee ?? 0,
            'signal_yearly_fee'  => $s->signal_yearly_fee ?? 0,
            'chat_id'            => $s->chat_id ?? '',
            'telegram_bot_api'   => $s->telegram_bot_api ?? '',
            'telegram_link'      => $s->telegram_link ?? '',
        ]]);
    }

    private function saveSignalSettings(array $d)
    {
        $values = [
            'signal_monthly_fee' => $this->amountOrNull($d['monthly'] ?? null),
            'signal_quartly_fee' => $this->amountOrNull($d['quaterly'] ?? null),
            'signal_yearly_fee'  => $this->amountOrNull($d['yearly'] ?? null),
            'telegram_bot_api'   => $d['telegram_bot_api'] ?? null,
        ];
        if (array_key_exists('telegram_link', $d) && $d['telegram_link'] !== null) {
            $values['telegram_link'] = $d['telegram_link'];
        }
        $this->updateSettingsCont($values);
        return $this->respond(null, 'Signal settings saved');
    }

    private function chatId(array $d)
    {
        $token = optional($this->settingsCont())->telegram_bot_api;
        if (! $token) {
            return $this->fail('Save your Telegram bot API token first');
        }
        try {
            $res = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates");
        } catch (\Throwable $e) {
            return $this->fail('Could not reach Telegram: ' . $e->getMessage());
        }
        $chatId = null;
        foreach (array_reverse($res->json('result') ?? []) as $update) {
            $chat = $update['channel_post']['chat'] ?? $update['message']['chat'] ?? $update['my_chat_member']['chat'] ?? null;
            if ($chat && isset($chat['id'])) {
                $chatId = $chat['id'];
                break;
            }
        }
        if (! $chatId) {
            return $this->fail('No chat found. Add the bot to your Telegram channel/group as admin, post a message there, then try again.');
        }
        $this->updateSettingsCont(['chat_id' => (string) $chatId]);
        return $this->respond(['chat_id' => $chatId], 'Telegram chat ID saved');
    }

    private function deleteChatId(array $d)
    {
        $this->updateSettingsCont(['chat_id' => null]);
        return $this->respond(null, 'Telegram chat ID removed');
    }

    private function subscribers(array $d)
    {
        $subs = DB::table('signal_subscriptions as s')
            ->leftJoin('users as u', 'u.id', '=', 's.client_id')
            ->select('s.*', 'u.name as client_name', 'u.email as client_email')
            ->orderByDesc('s.id')->get()->all();
        return $this->respond(['subscribers' => ['data' => $subs]]);
    }

    private function subscription(array $d)
    {
        return $this->respond(DB::table('signal_subscriptions')->where('client_id', $d['id'] ?? 0)->first());
    }

    private function subscribe(array $d)
    {
        $expire = ! empty($d['expire']) ? \Carbon\Carbon::parse($d['expire']) : now()->addMonth();
        DB::table('signal_subscriptions')->updateOrInsert(
            ['client_id' => $d['id']],
            [
                'subscription' => $d['duration'] ?? 'Monthly',
                'amount_paid'  => $this->amountOrNull($d['amount'] ?? 0) ?? 0,
                'expired_at'   => $expire,
                'reminded_at'  => (clone $expire)->subDays(3),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        );
        return $this->respond([
            'inviteLink'   => optional($this->settingsCont())->telegram_link,
            'hasSubscribe' => true,
        ], 'Subscription successful');
    }

    private function renewSubscription(array $d)
    {
        $sub = DB::table('signal_subscriptions')->where('client_id', $d['id'] ?? 0)->first();
        if (! $sub) {
            return $this->fail('No subscription to renew', 404);
        }
        $from = $sub->expired_at && now()->lt($sub->expired_at) ? \Carbon\Carbon::parse($sub->expired_at) : now();
        $expire = $sub->subscription === 'Yearly' ? $from->addYear()
            : ($sub->subscription === 'Quarterly' ? $from->addMonths(4) : $from->addMonth());

        DB::table('signal_subscriptions')->where('id', $sub->id)->update([
            'expired_at'  => $expire,
            'reminded_at' => (clone $expire)->subDays(3),
            'updated_at'  => now(),
        ]);
        return $this->respond(null, 'Subscription renewed');
    }

    private function setModules(array $d)
    {
        return $this->respond(null, 'Module updated successfully');
    }
}
