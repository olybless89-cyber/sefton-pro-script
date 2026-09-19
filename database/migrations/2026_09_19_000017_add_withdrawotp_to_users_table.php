<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The withdrawal OTP flow (WithdrawalController::getotp/otpview/
     * completewithdrawal) reads/writes users.withdrawotp, but no
     * migration ever created it, causing a 500 the first time a user
     * requests a withdrawal OTP code.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'withdrawotp')) {
                $table->string('withdrawotp')->nullable()->after('remember_token');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'withdrawotp')) {
                $table->dropColumn('withdrawotp');
            }
        });
    }
};
