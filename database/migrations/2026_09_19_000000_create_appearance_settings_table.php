<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppearanceSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('appearance_settings', function (Blueprint $table) {
            $table->id();

            // Primary color variants
            $table->string('primary_color')->nullable();
            $table->string('primary_color_50')->nullable();
            $table->string('primary_color_100')->nullable();
            $table->string('primary_color_200')->nullable();
            $table->string('primary_color_300')->nullable();
            $table->string('primary_color_400')->nullable();
            $table->string('primary_color_600')->nullable();
            $table->string('primary_color_700')->nullable();
            $table->string('primary_color_foreground')->nullable();

            // Secondary color variants
            $table->string('secondary_color')->nullable();
            $table->string('secondary_color_50')->nullable();
            $table->string('secondary_color_100')->nullable();
            $table->string('secondary_color_200')->nullable();
            $table->string('secondary_color_300')->nullable();
            $table->string('secondary_color_400')->nullable();
            $table->string('secondary_color_600')->nullable();
            $table->string('secondary_color_700')->nullable();
            $table->string('secondary_color_foreground')->nullable();

            // Accent color variants
            $table->string('accent_color')->nullable();
            $table->string('accent_color_50')->nullable();
            $table->string('accent_color_100')->nullable();
            $table->string('accent_color_200')->nullable();
            $table->string('accent_color_300')->nullable();
            $table->string('accent_color_400')->nullable();
            $table->string('accent_color_600')->nullable();
            $table->string('accent_color_700')->nullable();
            $table->string('accent_color_foreground')->nullable();

            // Background, foreground, and other UI colors
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->string('card_color')->nullable();
            $table->string('card_foreground_color')->nullable();
            $table->string('muted_color')->nullable();
            $table->string('muted_foreground_color')->nullable();
            $table->string('border_color')->nullable();
            $table->string('input_color')->nullable();
            $table->string('ring_color')->nullable();

            // Gradient colors
            $table->string('gradient_pink_from')->nullable();
            $table->string('gradient_purple_via')->nullable();
            $table->string('gradient_indigo_to')->nullable();

            // Action colors
            $table->string('yellow_action')->nullable();
            $table->string('green_positive')->nullable();
            $table->string('red_negative')->nullable();

            // Preloader specific colors
            $table->string('preloader_background')->nullable();
            $table->string('preloader_background_dark')->nullable();
            $table->string('preloader_text_color')->nullable();
            $table->string('preloader_accent_color')->nullable();

            // Other settings
            $table->boolean('use_gradient')->default(true);
            $table->string('gradient_direction')->nullable();
            $table->text('custom_css')->nullable();
            $table->boolean('disable_animations')->default(false);
            $table->string('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appearance_settings');
    }
}
