<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives the transfer verification codes descriptive default names and adds
 * extra payment detail fields to wdmethods so the admin can configure
 * PayPal, CashApp, wire transfer and other deposit/withdrawal methods.
 *
 * Verified names:
 *   - code1      = Cost of transfer code
 *   - code2      = Anti-money laundering (AML) code
 *   - code3      = Source of Funds code
 *   - code4      = Compliance code
 *   - code5      = Fund Release code
 *   - tax_code   = Tax clearance code
 */
class SeedCodeNamesAndAddPaymentDetails extends Migration
{
    private $codeNames = [
        'code1'     => 'Cost of transfer code',
        'code2'     => 'Anti-money laundering (AML) code',
        'code3'     => 'Source of Funds code',
        'code4'     => 'Compliance code',
        'code5'     => 'Fund Release code',
        'tax_code'  => 'Tax clearance code',
    ];

    private $paymentColumns = [
        'paypal_email'    => 'string',
        'cashapp_tag'     => 'string',
        'cashapp_fullname'=> 'string',
        'iban'            => 'string',
        'routing_number'  => 'string',
        'bank_address'    => 'string',
        'bank_country'    => 'string',
    ];

    public function up()
    {
        // Seed descriptive code names into the settings row (only where empty)
        if (Schema::hasTable('settings')) {
            foreach ($this->codeNames as $column => $name) {
                if (Schema::hasColumn('settings', $column)) {
                    DB::table('settings')
                        ->where('id', 1)
                        ->where(function ($q) use ($column) {
                            $q->whereNull($column)->orWhere($column, '');
                        })
                        ->update([$column => $name]);
                }
            }
        }

        // Add payment detail columns to wdmethods
        if (Schema::hasTable('wdmethods')) {
            foreach ($this->paymentColumns as $column => $type) {
                if (! Schema::hasColumn('wdmethods', $column)) {
                    Schema::table('wdmethods', function (Blueprint $table) use ($column, $type) {
                        $table->{$type}($column)->nullable();
                    });
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('settings')) {
            foreach (array_keys($this->codeNames) as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    DB::table('settings')->where('id', 1)->update([$column => null]);
                }
            }
        }

        if (Schema::hasTable('wdmethods')) {
            foreach (array_keys($this->paymentColumns) as $column) {
                if (Schema::hasColumn('wdmethods', $column)) {
                    Schema::table('wdmethods', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
}