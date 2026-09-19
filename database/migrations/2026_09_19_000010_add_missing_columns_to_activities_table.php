<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The activities table (login-activity log) was created with only id +
     * timestamps, but every place that writes to it (JetstreamServiceProvider
     * on every login, SocialLoginController) or reads it (Admin
     * ManageUsersController's login-activity page) uses user, ip_address,
     * device, browser, os. This crashed every single login attempt.
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            if (! Schema::hasColumn('activities', 'user')) {
                $table->unsignedBigInteger('user')->nullable()->after('id');
            }
            if (! Schema::hasColumn('activities', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('user');
            }
            if (! Schema::hasColumn('activities', 'device')) {
                $table->string('device')->nullable()->after('ip_address');
            }
            if (! Schema::hasColumn('activities', 'browser')) {
                $table->string('browser')->nullable()->after('device');
            }
            if (! Schema::hasColumn('activities', 'os')) {
                $table->string('os')->nullable()->after('browser');
            }
        });
    }

    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            foreach (['user', 'ip_address', 'device', 'browser', 'os'] as $col) {
                if (Schema::hasColumn('activities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
