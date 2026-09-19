<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every successful login sets users.pinstatus = 1 (Fortify::authenticateUsing
     * in JetstreamServiceProvider) to require a PIN step-up
     * (EnsureKycIsCompleted middleware, ViewsController::pinstatus resets it
     * to 0 once the PIN is verified), but no migration ever created the
     * column - crashing every single login right after the activities fix.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pinstatus')) {
                $table->string('pinstatus')->nullable()->default('0')->after('pin');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pinstatus')) {
                $table->dropColumn('pinstatus');
            }
        });
    }
};
