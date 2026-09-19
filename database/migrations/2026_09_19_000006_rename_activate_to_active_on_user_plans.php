<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The user_plans table was created with a column named "activate", but
     * every single place in the app (ViewsController, AutoTaskController,
     * Admin HomeController, ManageUsersController, InvestmentPlan Livewire
     * component, LoanController, UserInvPlanController) reads/writes a
     * column named "active" instead. That's a typo in the original
     * migration, not an intentional rename — this fixes the column to match
     * what the rest of the codebase actually expects. Uses a raw ALTER
     * TABLE (via DB::statement) since doctrine/dbal isn't installed and
     * Schema::rename requires it on Laravel 8.
     */
    public function up()
    {
        if (Schema::hasColumn('user_plans', 'activate') && ! Schema::hasColumn('user_plans', 'active')) {
            DB::statement('ALTER TABLE `user_plans` CHANGE `activate` `active` VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('user_plans', 'active') && ! Schema::hasColumn('user_plans', 'activate')) {
            DB::statement('ALTER TABLE `user_plans` CHANGE `active` `activate` VARCHAR(255) NULL');
        }
    }
};
