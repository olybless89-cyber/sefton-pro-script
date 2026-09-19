<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The virtual-card feature (App\Models\Card, Admin\VirtualCardController,
     * the user dashboard's "cards" widget) has no migration anywhere in the
     * shipped script, so any page touching it fails with
     * "Base table or view not found: cards".
     */
    public function up()
    {
        if (Schema::hasTable('cards')) {
            return;
        }

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('card_number')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('expiry_month')->nullable();
            $table->string('expiry_year')->nullable();
            $table->string('cvv')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_level')->nullable();
            $table->string('currency')->nullable();
            $table->float('balance')->default(0);
            $table->string('status')->default('pending');
            $table->string('last_four')->nullable();
            $table->string('bin')->nullable();
            $table->text('card_pan')->nullable();
            $table->string('card_token')->nullable();
            $table->string('reference_id')->nullable();
            $table->dateTime('application_date')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip')->nullable();
            $table->float('daily_limit')->default(0);
            $table->float('monthly_limit')->default(0);
            $table->boolean('is_virtual')->default(true);
            $table->boolean('is_physical')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cards');
    }
};
