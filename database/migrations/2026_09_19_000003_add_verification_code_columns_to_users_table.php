<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The code-based email verification flow (User::sendEmailVerificationNotification,
     * User::verifyEmailWithCode) reads/writes these two columns, but no prior
     * migration ever created them, causing a "1054 Unknown column" error the
     * first time a verification code is generated (e.g. on registration or
     * "resend code").
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'verification_code')) {
                $table->string('verification_code')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'verification_code_expires_at')) {
                $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'verification_code')) {
                $table->dropColumn('verification_code');
            }
            if (Schema::hasColumn('users', 'verification_code_expires_at')) {
                $table->dropColumn('verification_code_expires_at');
            }
        });
    }
};
