<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A brand-new database has the tables but none of the starter rows the site
 * reads on every page. Without them the admin area loads with no stylesheet
 * (empty website_theme), the site name is blank and some pages error.
 *
 * Creates each row only if it is missing, so existing sites are untouched.
 */
class SeedDefaultSiteRecords extends Migration
{
    public function up()
    {
        $now = now();

        if (Schema::hasTable('settings') && ! DB::table('settings')->where('id', 1)->exists()) {
            $name = env('APP_NAME', 'Online Banking');

            DB::table('settings')->insert([
                'id'            => 1,
                'site_name'     => $name,
                'site_title'    => $name,
                'currency'      => '$',
                'website_theme' => 'blue.css',
                'logo'          => 'photos/default-branding-logo.png',
                'favicon'       => 'photos/default-branding-favicon.png',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        if (Schema::hasTable('settings_conts') && ! DB::table('settings_conts')->where('id', 1)->exists()) {
            DB::table('settings_conts')->insert([
                'id'                 => 1,
                'use_crypto_feature' => 1,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        if (Schema::hasTable('terms_privacies') && ! DB::table('terms_privacies')->where('id', 1)->exists()) {
            DB::table('terms_privacies')->insert([
                'id'          => 1,
                'description' => '<p>Our privacy policy and terms of service will be published here.</p>',
                'useterms'    => 'yes',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // Admin pages are designed for light mode; dark mode leaves white text on
        // white cards (e.g. Create New User). Only touches the built-in admin.
        if (Schema::hasTable('admins')) {
            DB::table('admins')
                ->where('email', 'admin@seftonpro.com')
                ->where('dashboard_style', 'dark')
                ->update(['dashboard_style' => 'light']);
        }
    }

    public function down()
    {
        // Non-destructive: keep seeded rows.
    }
}
