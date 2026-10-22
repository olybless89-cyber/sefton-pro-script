<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin → Settings → Payment Settings → "Add New" saves bank and crypto details
 * (bank name, account number, wallet address, QR code, ...) but the original
 * wdmethods table never had those columns, so saving a payment method failed
 * with "Unknown column 'charges_amount'". Adds every column the admin form and
 * the user deposit/withdrawal pages use. Only adds what's missing.
 */
class AddPaymentDetailsToWdmethodsTable extends Migration
{
    private $columns = [
        'charges_amount' => 'string',
        'charges_type'   => 'string',
        'methodtype'     => 'string',
        'img_url'        => 'string',
        'bankname'       => 'string',
        'account_name'   => 'string',
        'account_number' => 'string',
        'swift_code'     => 'string',
        'wallet_address' => 'string',
        'barcode'        => 'string',
        'network'        => 'string',
    ];

    public function up()
    {
        if (! Schema::hasTable('wdmethods')) {
            return;
        }

        foreach ($this->columns as $column => $type) {
            if (! Schema::hasColumn('wdmethods', $column)) {
                Schema::table('wdmethods', function (Blueprint $table) use ($column, $type) {
                    $table->{$type}($column)->nullable();
                });
            }
        }
    }

    public function down()
    {
        foreach (array_keys($this->columns) as $column) {
            if (Schema::hasColumn('wdmethods', $column)) {
                Schema::table('wdmethods', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
