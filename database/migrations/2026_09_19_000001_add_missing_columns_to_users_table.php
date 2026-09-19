<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('lastname')->nullable()->after('name');
            $table->string('middlename')->nullable()->after('lastname');
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('curr')->nullable();
            $table->string('s_curr')->nullable();
            $table->string('accounttype')->nullable();
            $table->string('pin')->nullable();
            $table->string('usernumber')->nullable();
            $table->string('code1')->nullable();
            $table->string('code2')->nullable();
            $table->string('code3')->nullable();
            $table->string('code4')->nullable();
            $table->string('code5')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'lastname', 'middlename', 'username', 'curr', 's_curr',
                'accounttype', 'pin', 'usernumber',
                'code1', 'code2', 'code3', 'code4', 'code5',
            ]);
        });
    }
}
