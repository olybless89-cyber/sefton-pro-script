<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('card_transactions')) {
            return;
        }

        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('user_id');
            $table->float('amount')->default(0);
            $table->string('currency')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('merchant_name')->nullable();
            $table->string('merchant_category')->nullable();
            $table->string('merchant_city')->nullable();
            $table->string('merchant_country')->nullable();
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->dateTime('settlement_date')->nullable();
            $table->timestamps();

            $table->index('card_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_transactions');
    }
};
