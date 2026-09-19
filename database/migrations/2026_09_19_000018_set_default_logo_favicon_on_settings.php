<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every layout renders <link href="{{ asset('storage/app/public/'.$settings->favicon) }}">
     * and an <img src="...logo...">. When settings.logo / settings.favicon are empty
     * (never configured via the admin App Settings page), this resolves to a bare
     * directory URL ("/storage/app/public") which Apache 403s on, breaking the
     * favicon/logo on every single page. Seed sane defaults (bundled branding images,
     * committed to storage/app/public/photos) so the site has a working favicon/logo
     * out of the box; an admin who uploads their own logo later simply overwrites this.
     */
    public function up()
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = DB::table('settings')->where('id', 1)->first();
        if (! $settings) {
            return;
        }

        $updates = [];
        if (empty($settings->logo)) {
            $updates['logo'] = 'photos/default-branding-logo.png';
        }
        if (empty($settings->favicon)) {
            $updates['favicon'] = 'photos/default-branding-favicon.png';
        }

        if (! empty($updates)) {
            DB::table('settings')->where('id', 1)->update($updates);
        }
    }

    public function down()
    {
        // Non-destructive: leave any seeded defaults in place.
    }
};
