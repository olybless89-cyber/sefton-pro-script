<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetDefaultsOnCryptoAccountsTable extends Migration
{
    protected $columns = ['btc', 'eth', 'ltc', 'xrp', 'link', 'bat', 'aave', 'usdt', 'xlm', 'bch'];

    public function up()
    {
        foreach ($this->columns as $column) {
            DB::statement("ALTER TABLE crypto_accounts MODIFY `{$column}` FLOAT(8,2) NOT NULL DEFAULT 0");
        }
    }

    public function down()
    {
        foreach ($this->columns as $column) {
            DB::statement("ALTER TABLE crypto_accounts MODIFY `{$column}` FLOAT(8,2) NOT NULL");
        }
    }
}
