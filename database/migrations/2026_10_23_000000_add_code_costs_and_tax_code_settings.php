<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The settings table is already at MySQL's row-size limit, so new columns
     * must be TEXT (stored off-row), matching the pattern used by
     * AddColumnsUsedByAdminAndUserFeatures.
     *
     * Adds:
     *  - settings.code1cost..code5cost: the admin-configurable price of each
     *    transfer verification code (shown to the user before they complete a
     *    transfer).
     *  - settings.tax_code / tax_code_status / tax_code_message / tax_code_cost:
     *    an additional admin-configurable verification code (e.g. a tax code).
     *  - users.tax_code: the per-user value for the tax code, generated at
     *    registration exactly like code1..code5.
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['code1cost', 'code2cost', 'code3cost', 'code4cost', 'code5cost', 'tax_code', 'tax_code_status', 'tax_code_message', 'tax_code_cost'] as $column) {
                if (! Schema::hasColumn('settings', $column)) {
                    $table->text($column)->nullable();
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tax_code')) {
                $table->string('tax_code', 32)->nullable()->after('code5');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['code1cost', 'code2cost', 'code3cost', 'code4cost', 'code5cost', 'tax_code', 'tax_code_status', 'tax_code_message', 'tax_code_cost'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tax_code')) {
                $table->dropColumn('tax_code');
            }
        });
    }
};