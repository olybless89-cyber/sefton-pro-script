<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * App\Models\Paystack backs the Paystack payment-gateway settings
     * panel (admin/dashboard/settings/payment-settings). No migration
     * ever created the "paystacks" table, causing a 500 on that page.
     */
    public function up()
    {
        if (! Schema::hasTable('paystacks')) {
            Schema::create('paystacks', function (Blueprint $table) {
                $table->id();
                $table->string('paystack_public_key')->nullable();
                $table->string('paystack_secret_key')->nullable();
                $table->string('paystack_url')->nullable();
                $table->string('paystack_email')->nullable();
                $table->timestamps();
            });
        }

        if (DB::table('paystacks')->count() === 0) {
            DB::table('paystacks')->insert([
                'id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('paystacks');
    }
};
