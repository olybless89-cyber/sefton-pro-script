<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * App\Models\CardSettings backs admin/cards/settings. No migration
     * ever created the "card_settings" table, causing a 500 on that page,
     * and the view calls ->first() with no null-guard so a seeded row is
     * required too.
     */
    public function up()
    {
        if (! Schema::hasTable('card_settings')) {
            Schema::create('card_settings', function (Blueprint $table) {
                $table->id();
                $table->float('standard_fee')->default(0);
                $table->float('gold_fee')->default(0);
                $table->float('platinum_fee')->default(0);
                $table->float('black_fee')->default(0);
                $table->float('monthly_fee')->default(0);
                $table->float('topup_fee_percentage')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->float('max_daily_limit')->default(0);
                $table->float('min_daily_limit')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (DB::table('card_settings')->count() === 0) {
            DB::table('card_settings')->insert([
                'id' => 1,
                'standard_fee' => 0,
                'gold_fee' => 0,
                'platinum_fee' => 0,
                'black_fee' => 0,
                'monthly_fee' => 0,
                'topup_fee_percentage' => 0,
                'is_enabled' => true,
                'max_daily_limit' => 0,
                'min_daily_limit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('card_settings');
    }
};
