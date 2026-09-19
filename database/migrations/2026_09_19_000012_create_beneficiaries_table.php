<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Quick Transfer" beneficiaries widget (loaded via AJAX on every
     * dashboard visit, plus local/international transfer forms) has no
     * migration for its table at all - same shipped-without-a-table
     * pattern as cards, grant_applications, irs_refunds.
     */
    public function up()
    {
        if (Schema::hasTable('beneficiaries')) {
            return;
        }

        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_type')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('country')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('iban')->nullable();
            $table->string('crypto_currency')->nullable();
            $table->string('crypto_network')->nullable();
            $table->string('wallet_address')->nullable();
            $table->string('paypal_email')->nullable();
            $table->string('wise_email')->nullable();
            $table->string('skrill_email')->nullable();
            $table->string('venmo_username')->nullable();
            $table->string('venmo_phone')->nullable();
            $table->string('zelle_email')->nullable();
            $table->string('zelle_phone')->nullable();
            $table->string('cashapp_tag')->nullable();
            $table->string('revolut_email')->nullable();
            $table->string('alipay_id')->nullable();
            $table->string('wechat_id')->nullable();
            $table->string('method_type')->nullable();
            $table->string('initials')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->dateTime('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('beneficiaries');
    }
};
