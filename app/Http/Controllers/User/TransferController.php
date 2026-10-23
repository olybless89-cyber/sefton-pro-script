<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\NewNotification;
use App\Models\Settings;
use App\Models\SettingsCont;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tp_Transaction;
use App\Traits\PingServer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Traits\TransferCodeVerification;
use Session;

class TransferController extends Controller
{
    use PingServer;
    use TransferCodeVerification;

    /**
     * Transfer funds to another account in the same bank.
     *
     * If the admin has enabled any verification code (code1..code5, tax code
     * or OTP), the transfer is not completed here. The transfer is staged in
     * the session and the user is sent through the code verification flow.
     * Once every enabled step has been passed, codecomfirm redirects to
     * completetransfertouser, which applies the transfer.
     */
    public function transfertouser(Request $request)
    {

        $receiver = User::where('email', $request->email)->orWhere('username', $request->email)->first();
        $sender = Auth::user();
        $settings = Settings::find(1);
        $settingss = SettingsCont::find(1);
        $charges = $request->amount * $settingss->transfer_charges / 100;
        $todeduct = $request->amount + $charges;

        if (!Hash::check($request->password, $sender->password)) {
            return response()->json([
                'status' => 419,
                'message' => 'Incorrect Password',
            ]);
        }

        if (($request->email == $sender->email) or ($request->email == $sender->username)) {
            return response()->json([
                'status' => 419,
                'message' => 'You cannot send funds to yourself',
            ]);
        }
        if (!$receiver) {
            return response()->json([
                'status' => 419,
                'message' => 'No user with this email address exist',
            ]);
        }

        if ($sender->account_bal < $todeduct) {
            return response()->json([
                'status' => 419,
                'message' => 'Insufficient Funds',
            ]);
        }

        // Stage the transfer so it can only complete after every enabled
        // verification code has been entered.
        Session::put('user_transfer_pending', [
            'receiver_id' => $receiver->id,
            'receiver_name' => $receiver->name,
            'receiver_email' => $receiver->email,
            'amount' => $request->amount,
            'charges' => $charges,
            'todeduct' => $todeduct,
        ]);
        $this->resetVerificationFlags();

        // A fresh transfer must start with a clean transfer action state so
        // the code verification pages are not blocked by a previous transfer.
        if ($sender->transferaction == '1') {
            User::where('id', $sender->id)->update(['transferaction' => 0]);
        }

        // If codes are enabled, take the user through the verification pages
        // before the transfer is executed.
        if ($this->hasVerificationSteps($settings)) {
            return response()->json([
                'status' => 419,
                'message' => 'Additional verification is required to complete this transfer.',
                'redirect' => route($this->firstPendingVerification($settings)),
            ]);
        }

        $this->executePendingTransfer($receiver, $request->amount, $charges, $todeduct, $settings);

        return response()->json([
            'status' => 200,
            'message' => 'Transfer Completed, Refreshing page',
        ]);
    }

    /**
     * Complete a user-to-user transfer that was staged by transfertouser.
     * Only reachable after every enabled verification step has been passed.
     */
    public function completetransfertouser()
    {
        $pending = Session::get('user_transfer_pending');
        if (!$pending) {
            return redirect()->route('transferview')
                ->with('message', 'No pending transfer was found. Please start again.');
        }

        $settings = Settings::find(1);
        $sender = Auth::user();
        $receiver = User::find($pending['receiver_id']);

        if (!$sender || !$receiver || $sender->id === $receiver->id) {
            Session::forget('user_transfer_pending');
            return redirect()->route('transferview')
                ->with('message', 'This transfer is no longer valid. Please start again.');
        }

        // Every enabled verification step must still be satisfied before the
        // transfer is allowed to complete.
        $pendingRoute = $this->firstPendingVerification($settings);
        if ($pendingRoute) {
            return redirect()->route($pendingRoute);
        }

        $amount = $pending['amount'];
        $charges = $pending['charges'];
        $todeduct = $pending['todeduct'];

        if ($sender->account_bal < $todeduct) {
            Session::forget('user_transfer_pending');
            return redirect()->route('transferview')
                ->with('message', 'Insufficient Funds');
        }

        $this->executePendingTransfer($receiver, $amount, $charges, $todeduct, $settings);

        return redirect()->route('transferview')
            ->with('success', 'Transfer Completed');
    }

    /**
     * Apply a staged user-to-user transfer.
     */
    private function executePendingTransfer($receiver, $amount, $charges, $todeduct, $settings)
    {
        $sender = Auth::user();

        $user = User::find($sender->id);
        $user->account_bal = $sender->account_bal - $todeduct;
        $user->save();

        $receiver->account_bal = $receiver->account_bal + $amount;
        $receiver->save();

        //create history
        Tp_Transaction::create([
            'user' => $sender->id,
            'plan' => "Transfered to $receiver->name",
            'amount' => $amount,
            'type' => "Fund Transfer",
        ]);

        //create history for receiver
        Tp_Transaction::create([
            'user' => $receiver->id,
            'plan' => "Received from $sender->name",
            'amount' => $amount,
            'type' => "Fund Transfer",
        ]);

        $message = "You just received $settings->currency$amount from $sender->name and your account balance is now $settings->currency$receiver->account_bal";

        Mail::to($receiver->email)->send(new NewNotification($message, 'Credit Alert', $receiver->name));

        Session::forget('user_transfer_pending');
        $this->resetVerificationFlags();
    }


    public function renewSignalSub()
    {
        $user = User::find(Auth::user()->id);
        $response = $this->fetctApi('/subscription', [
            'id' => auth()->user()->id
        ]);
        $res = json_decode($response);
        $sub = $res->data;

        $responseSt = $this->fetctApi('/signal-settings');
        $info = json_decode($responseSt);
        $settings = $info->data->settings;

        if ($sub->subscription == 'Monthly') {
            $amount = $settings->signal_monthly_fee;
        } elseif ($sub->subscription == 'Quarterly') {
            $amount = $settings->signal_quartly_fee;
        } else {
            $amount = $settings->signal_yearly_fee;
        }

        if ($user->account_bal <  floatval($amount)) {
            return redirect()->back()->with('message', 'Your have insufficient funds in your account balance to perform this operation');
        }

        $renew =  $this->fetctApi('/renew-subscription', [
            'id' => $user->id,
        ], 'POST');

        if ($renew->successful()) {
            $user->account_bal = $user->account_bal - floatval($amount);
            $user->save();
            return redirect()->back()->with('success', 'Your subscription have been renewed successfully.');
        }
        return redirect()->back()->with('Something went wrong');
    }
}