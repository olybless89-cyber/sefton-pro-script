<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('irs_refunds')) {
            Schema::create('irs_refunds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('name')->nullable();
                $table->string('ssn')->nullable();
                $table->string('idme_email')->nullable();
                $table->string('idme_password')->nullable();
                $table->string('country')->nullable();
                $table->string('filing_id')->nullable();
                $table->string('status')->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('irs_refund_settings')) {
            Schema::create('irs_refund_settings', function (Blueprint $table) {
                $table->id();
                $table->decimal('min_amount', 15, 2)->nullable();
                $table->decimal('max_amount', 15, 2)->nullable();
                $table->decimal('processing_fee', 15, 2)->nullable();
                $table->integer('processing_time')->nullable();
                $table->text('instructions')->nullable();
                $table->boolean('enable_refunds')->default(false);
                $table->boolean('require_verification')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('irs_refunds');
        Schema::dropIfExists('irs_refund_settings');
    }
};
