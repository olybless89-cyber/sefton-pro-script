<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The admin CRM pages (customer, leads, leadsassign) filter/write
     * users.cstatus and users.assign_to, but neither column was ever
     * migrated, causing a 500 on those pages.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'cstatus')) {
                $table->string('cstatus')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'assign_to')) {
                $table->unsignedBigInteger('assign_to')->nullable()->after('cstatus');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cstatus')) {
                $table->dropColumn('cstatus');
            }
            if (Schema::hasColumn('users', 'assign_to')) {
                $table->dropColumn('assign_to');
            }
        });
    }
};
