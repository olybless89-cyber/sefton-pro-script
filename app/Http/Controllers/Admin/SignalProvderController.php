<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SettingsCont;
use App\Notifications\PostForexSignal;
use App\Notifications\UpdateForexSignalResult;
use App\Traits\PingServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SignalProvderController extends Controller
{
    use PingServer;

    public function tradeSignals(Request $request)
    {
        $page = $request->query('page', 1);

        $response = $this->fetctApi('/trading-signals?page=' . $page);
        $info = json_decode($response);

        return view('admin.signals.tradeSignals', [
            'title' => 'Trading Signals',
            'signals' => $info->data->signals,
        ]);
    }


    public function settings()
    {
        $response = $this->fetctApi('/signal-settings');
        $info = json_decode($response);

        return view('admin.signals.signalSettings', [
            'title' => 'Signals Settings',
            'signalSettings' => $info->data->settings,
        ]);
    }

    public function subscribers()
    {
        $response = $this->fetctApi('/signal-subscribers');
        if ($response->failed()) {
            return redirect()->back()->with('message', $response['message']);
        }
        $info = json_decode($response);
        return view('admin.signals.subscribers', [
            'title' => 'Subscribers',
            'subscribers' => $info->data->subscribers,
        ]);
    }


    public function addSignals(Request $request)
    {
        $response = $this->fetctApi('/post-signals', [
            'direction' => $request->direction,
            'pair' => $request->pair,
            'price' => $request->price,
            'tp1' => $request->tp1,
            'tp2' => $request->tp2,
            'sl1' => $request->sl1,
        ], 'POST');

        $respond = $this->backWithResponse($response);
        return redirect()->back()->with($respond['type'], $respond['message']);
    }


    public function publishSignals($signal)
    {

        $response = $this->fetctApi("/publish-signals/$signal");
        $info = json_decode($response);

        if ($info->error) {
            return redirect()->back()->with('message', $response['message']);
        }

        $respond = $this->backWithResponse($response);
        $note = $this->sendToTelegram($info->data->chat_id ?? null, new PostForexSignal($info->data->message));
        return redirect()->back()->with($respond['type'], $respond['message'] . $note);
    }

    public function updateResult(Request $request)
    {
        $response = $this->fetctApi('/update-result', [
            'signalId' => $request->signalId,
            'result' => $request->result
        ], 'POST');

        if ($response->failed()) {
            return redirect()->back()->with('message', $response['message']);
        }
        $info = json_decode($response);

        $respond = $this->backWithResponse($response);
        $note = $this->sendToTelegram($info->data->chat_id ?? null, new UpdateForexSignalResult($info->data->message));
        return redirect()->back()->with($respond['type'], $respond['message'] . $note);
    }

    public function deleteSignal($signal)
    {
        $response = $this->fetctApi("/delete-signal/$signal");
        $respond = $this->backWithResponse($response);
        return redirect()->back()->with($respond['type'], $respond['message']);
    }


    public function saveSettings(Request $request)
    {
        $settings = SettingsCont::find(1);

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $link = "https";
        } else {
            $link = "http";
        }
        $website = $link . '://' . $_SERVER['HTTP_HOST'] . '/get-started';

        $response = $this->fetctApi("/save-signal-settings", [
            'website' => $website,
            'monthly' => $request->monthly,
            'quaterly' => $request->quaterly,
            'yearly' => $request->yearly,
            'telegram_link' => $request->telegram_link,
            'telegram_bot_api' => $request->telegram_bot_api
        ], 'PUT');

        if ($response->successful()) {
            $settings->telegram_bot_api = $request->telegram_bot_api;
            $settings->save();
        }
        $respond = $this->backWithResponse($response);
        return redirect()->back()->with($respond['type'], $respond['message']);
    }


    public function getChatId()
    {
        $response = $this->fetctApi("/chat-id");
        $respond = $this->backWithResponse($response);
        return redirect()->back()->with($respond['type'], $respond['message']);
    }

    public function deleteChatId()
    {
        $response = $this->fetctApi("/delete-id");

        $respond = $this->backWithResponse($response);
        return redirect()->back()->with($respond['type'], $respond['message']);
    }

    /**
     * Post to the Telegram channel if a bot token and chat ID are set up.
     * Never fails the request; returns a short note for the admin instead.
     */
    private function sendToTelegram($chatId, $notification): string
    {
        $settings = SettingsCont::find(1);
        if (empty($chatId) || empty($settings->telegram_bot_api)) {
            return ' (Not sent to Telegram: add your bot token and chat ID in Signal Settings.)';
        }
        try {
            config(['services.telegram-bot-api.token' => $settings->telegram_bot_api]);
            Notification::route('telegram', $chatId)->notify($notification);
            return ' Sent to Telegram.';
        } catch (\Throwable $e) {
            report($e);
            return ' (Telegram error: ' . $e->getMessage() . ')';
        }
    }
}
