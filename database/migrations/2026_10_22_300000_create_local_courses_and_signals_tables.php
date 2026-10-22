<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Courses (membership) and trading signals used to live on the script vendor's
 * remote service (app.getonlinetrader.pro), which is no longer reachable, so
 * those admin and customer pages failed. These tables let the site store them
 * itself (see App\Services\LocalTraderApi).
 */
class CreateLocalCoursesAndSignalsTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('course_categories')) {
            Schema::create('course_categories', function (Blueprint $t) {
                $t->id();
                $t->string('category');
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $t) {
                $t->id();
                $t->string('course_title');
                $t->string('course_image')->nullable();
                $t->text('description')->nullable();
                $t->decimal('amount', 20, 2)->nullable();
                $t->boolean('paid')->default(false);
                $t->string('category')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('course_lessons')) {
            Schema::create('course_lessons', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('course_id')->nullable()->index();
                $t->string('title');
                $t->text('description')->nullable();
                $t->string('length')->nullable();
                $t->string('video_link')->nullable();
                $t->string('thumbnail')->nullable();
                // 'true' = free preview allowed (name kept from the original API)
                $t->string('locked')->default('false');
                $t->string('category')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('user_courses')) {
            Schema::create('user_courses', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedBigInteger('course_id')->index();
                $t->decimal('amount', 20, 2)->default(0);
                $t->timestamps();
                $t->unique(['user_id', 'course_id']);
            });
        }

        if (! Schema::hasTable('trading_signals')) {
            Schema::create('trading_signals', function (Blueprint $t) {
                $t->id();
                $t->string('reference')->nullable();
                $t->string('trade_direction')->nullable();
                $t->string('currency_pair')->nullable();
                $t->string('price')->nullable();
                $t->string('take_profit')->nullable();
                $t->string('stop_loss')->nullable();
                $t->string('result')->nullable();
                $t->string('status')->default('unpublished');
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('signal_subscriptions')) {
            Schema::create('signal_subscriptions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('client_id')->unique();
                $t->string('subscription')->nullable(); // Monthly / Quarterly / Yearly
                $t->decimal('amount_paid', 20, 2)->default(0);
                $t->timestamp('expired_at')->nullable();
                $t->timestamp('reminded_at')->nullable();
                $t->timestamps();
            });
        }

        // Signal settings live with the Telegram settings the notifications already read.
        if (Schema::hasTable('settings_conts')) {
            $cols = [
                'signal_monthly_fee' => 'decimal', 'signal_quartly_fee' => 'decimal', 'signal_yearly_fee' => 'decimal',
                'chat_id' => 'string', 'telegram_bot_api' => 'string', 'telegram_link' => 'string',
            ];
            foreach ($cols as $col => $type) {
                if (! Schema::hasColumn('settings_conts', $col)) {
                    Schema::table('settings_conts', function (Blueprint $t) use ($col, $type) {
                        $type === 'decimal' ? $t->decimal($col, 20, 2)->nullable() : $t->string($col)->nullable();
                    });
                }
            }
        }

        // Which optional features show in the customer menu. Was never created,
        // so Courses and Signals were hidden and their pages errored.
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'modules')) {
            Schema::table('settings', function (Blueprint $t) {
                $t->text('modules')->nullable();
            });
        }
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'modules')) {
            DB::table('settings')->whereNull('modules')->update([
                'modules' => json_encode([
                    'investment'   => false,
                    'cryptoswap'   => false,
                    'subscription' => false,
                    'membership'   => true,
                    'signal'       => true,
                ]),
            ]);
        }
    }

    public function down()
    {
        // Non-destructive: keep data.
    }
}
