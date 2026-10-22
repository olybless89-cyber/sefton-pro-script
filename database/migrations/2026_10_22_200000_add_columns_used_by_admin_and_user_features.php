<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The application code writes to many columns that the original migrations
 * never created, so most admin actions failed with "Unknown column ...":
 * crediting/debiting accounts (withdrawals.Description), editing users
 * (users.irs_filing_id, account_status, limit, ...), 2FA, transfer limits,
 * KYC, settings pages, etc.
 *
 * Also converts money columns that were stored as whole numbers or 2-decimal
 * floats (users.account_bal was INT, so cents were lost; crypto balances only
 * kept 2 decimals) to exact DECIMAL columns. Existing values are preserved.
 *
 * Every change is guarded, so this is safe on new and existing databases.
 */
class AddColumnsUsedByAdminAndUserFeatures extends Migration
{
    /**
     * table => [column => [type, default]]
     * type: string | short | text | decimal | btc | integer | bigint | boolean | timestamp
     */
    private function columns(): array
    {
        return [
            'users' => [
                'account_status'        => ['string', 'active'],
                'action'                => ['string', null],
                'amount'                => ['decimal', null],
                'bonus_matched'         => ['string', 'no'],
                'btc_balance'           => ['btc', 0],
                'dailyTotal'            => ['integer', 0],
                'weeklyTotal'           => ['integer', 0],
                'monthlyTotal'          => ['integer', 0],
                'grant_limit'           => ['decimal', 0],
                'limit'                 => ['decimal', 0],
                'irs_filing_id'         => ['string', null],
                'kyc_id'                => ['bigint', null],
                'sendotpemail'          => ['string', 'No'],
                'sendroiemail'          => ['string', 'No'],
                'sendinvplanemail'      => ['string', 'No'],
                'signalamount'          => ['decimal', null],
                'signalname'            => ['string', null],
                'signalstatus'          => ['string', null],
                'swift_code'            => ['string', null],
                'transferaction'        => ['integer', 0],
                'two_factor_code'       => ['string', null],
                'two_factor_enabled'    => ['boolean', 0],
                'two_factor_expires_at' => ['timestamp', null],
                'usdt_address'          => ['string', null],
                'userupdate'            => ['text', null],
            ],
            'withdrawals' => [
                'Description'          => ['text', null],
                'accountname'          => ['string', null],
                'accountnumber'        => ['string', null],
                'Accounttype'          => ['string', null],
                'bankname'             => ['string', null],
                'bankaddress'          => ['string', null],
                'country'              => ['string', null],
                'bal'                  => ['decimal', null],
                'btc_amount'           => ['btc', null],
                'btc_rate_at_transfer' => ['btc', null],
                'date'                 => ['string', null],
                'paydetails'           => ['text', null],
                'type'                 => ['string', null],
            ],
            'admins' => [
                'enable_2fa'       => ['string', 'disabled'],
                'pass_2fa'         => ['string', null],
                'token_2fa'        => ['string', null],
                'token_2fa_expiry' => ['timestamp', null],
                'password_token'   => ['string', null],
            ],
            'kycs' => [
                'title'        => ['string', null],
                'gender'       => ['string', null],
                'accounttype'  => ['string', null],
                'employer'     => ['string', null],
                'income'       => ['string', null],
                'kinname'      => ['string', null],
                'kinaddress'   => ['string', null],
                'relationship' => ['string', null],
                'statenumber'  => ['string', null],
                'zipcode'      => ['string', null],
            ],
            // settings is already at MySQL's row-size limit, so every new column is
            // TEXT (stored off-row). TEXT can't have a default, so the defaults for
            // the single settings row are written in up() instead.
            'settings' => [
                'address'                 => ['text',   null],
                'auto_merchant_option'    => ['text',   null],
                'capt_secret'             => ['text',   null],
                'capt_sitekey'            => ['text',   null],
                'code1'                   => ['text',   null],
                'code1message'            => ['text',   null],
                'code1status'             => ['text',   null],
                'code2'                   => ['text',   null],
                'code2message'            => ['text',   null],
                'code2status'             => ['text',   null],
                'code3'                   => ['text',   null],
                'code3message'            => ['text',   null],
                'code3status'             => ['text',   null],
                'code4'                   => ['text',   null],
                'code4message'            => ['text',   null],
                'code4status'             => ['text',   null],
                'code5'                   => ['text',   null],
                'code5message'            => ['text',   null],
                'code5status'             => ['text',   null],
                'credit_card_provider'    => ['text',   null],
                'deduction_option'        => ['text',   null],
                'deposit_bonus'           => ['text',   null],
                'deposit_option'          => ['text',   null],
                'emailfrom'               => ['text',   null],
                'emailfromname'           => ['text',   null],
                'enable_kyc_registration' => ['text',   null],
                'enable_social_login'     => ['text',   null],
                'google_id'               => ['text',   null],
                'google_redirect'         => ['text',   null],
                'google_secret'           => ['text',   null],
                'install_type'            => ['text',   null],
                'mail_server'             => ['text',   null],
                'merchant_key'            => ['text',   null],
                'otp'                     => ['text',   null],
                'redirect_url'            => ['text',   null],
                'return_capital'          => ['text',   null],
                's_currency'              => ['text',   null],
                'should_cancel_plan'      => ['text',   null],
                'sms'                     => ['text',   null],
                'smtp_encrypt'            => ['text',   null],
                'smtp_host'               => ['text',   null],
                'smtp_password'           => ['text',   null],
                'smtp_port'               => ['text',   null],
                'smtp_user'               => ['text',   null],
                'subscription_service'    => ['text',   null],
                'tido'                    => ['text',   null],
                'timezone'                => ['text',   null],
                'welcome_message'         => ['text',   null],
                'whatsapp'                => ['text',   null],
            ],
            'settings_conts' => [
                'currency_rate'    => ['string', null],
                'fee'              => ['string', null],
                'min_transfer'     => ['string', null],
                'minamt'           => ['string', null],
                'transfer_charges' => ['string', null],
                'use_transfer'     => ['string', null],
            ],
            'crypto_accounts' => [
                'ada' => ['btc', 0],
                'bnb' => ['btc', 0],
            ],
            'tp__transactions' => [
                'remark'       => ['text', null],
                'user_plan_id' => ['bigint', null],
            ],
            'user_plans' => [
                'profit_earned' => ['decimal', 0],
            ],
        ];
    }

    /** Existing money columns to convert: table => [column => type] */
    private function moneyColumns(): array
    {
        return [
            'users' => [
                'account_bal'     => 'DECIMAL(20,2)',
                'roi'             => 'DECIMAL(20,2)',
                'bonus'           => 'DECIMAL(20,2)',
                'ref_bonus'       => 'DECIMAL(20,2)',
                'signup_bonus'    => 'DECIMAL(20,2)',
                'bonus_released'  => 'DECIMAL(20,2)',
            ],
            'crypto_accounts' => [
                'btc' => 'DECIMAL(24,8)', 'eth' => 'DECIMAL(24,8)', 'ltc' => 'DECIMAL(24,8)',
                'xrp' => 'DECIMAL(24,8)', 'link' => 'DECIMAL(24,8)', 'bat' => 'DECIMAL(24,8)',
                'aave' => 'DECIMAL(24,8)', 'usdt' => 'DECIMAL(24,8)', 'xlm' => 'DECIMAL(24,8)',
                'bch' => 'DECIMAL(24,8)',
            ],
            'cards' => [
                'balance' => 'DECIMAL(20,2)', 'daily_limit' => 'DECIMAL(20,2)', 'monthly_limit' => 'DECIMAL(20,2)',
            ],
            'card_transactions' => ['amount' => 'DECIMAL(20,2)'],
            'crypto_records'    => ['amount' => 'DECIMAL(24,8)'],
        ];
    }

    public function up()
    {
        $this->makeRoomInSettingsTable();

        foreach ($this->columns() as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column => [$type, $default]) {
                if (Schema::hasColumn($table, $column)) {
                    continue;
                }
                Schema::table($table, function (Blueprint $t) use ($column, $type, $default) {
                    switch ($type) {
                        case 'text':      $c = $t->text($column); break;
                        case 'short':     $c = $t->string($column, 32); break;
                        case 'decimal':   $c = $t->decimal($column, 20, 2); break;
                        case 'btc':       $c = $t->decimal($column, 24, 8); break;
                        case 'integer':   $c = $t->integer($column); break;
                        case 'bigint':    $c = $t->unsignedBigInteger($column); break;
                        case 'boolean':   $c = $t->boolean($column); break;
                        case 'timestamp': $c = $t->timestamp($column); break;
                        default:          $c = $t->string($column);
                    }
                    $c->nullable();
                    if ($default !== null && $type !== 'text') {
                        $c->default($default);
                    }
                });
            }
        }

        if (Schema::hasTable('settings')) {
            $defaults = [
                'deposit_option'          => 'manual',
                'enable_kyc_registration' => 'no',
                'otp'                     => '0',
                'sms'                     => '0',
            ];
            foreach ($defaults as $column => $value) {
                if (Schema::hasColumn('settings', $column)) {
                    DB::table('settings')->whereNull($column)->update([$column => $value]);
                }
            }
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->moneyColumns() as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $column => $sqlType) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                $current = DB::selectOne(
                    'SELECT DATA_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$table, $column]
                );
                if ($current && strtolower($current->t) === 'decimal') {
                    continue;
                }
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` {$sqlType} NULL DEFAULT 0");
            }
        }
    }

    /**
     * settings has ~60 VARCHAR(255) columns, which already fills MySQL's 64KB
     * row limit, so no column can be added. Convert the VARCHAR columns that have
     * no default to TEXT (stored off-row). Values are kept as they are.
     */
    private function makeRoomInSettingsTable()
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('settings')) {
            return;
        }

        $rows = DB::select(
            "SELECT COLUMN_NAME AS name FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings'
               AND DATA_TYPE = 'varchar'
               AND (COLUMN_DEFAULT IS NULL OR COLUMN_DEFAULT = 'NULL')"
        );
        if (empty($rows)) {
            return;
        }

        $parts = array_map(function ($row) {
            return "MODIFY `{$row->name}` TEXT NULL";
        }, $rows);

        DB::statement('ALTER TABLE `settings` ' . implode(', ', $parts));
    }

    public function down()
    {
        // Non-destructive: keep added columns and data.
    }
}
