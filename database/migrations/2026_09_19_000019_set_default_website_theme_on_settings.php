<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * layouts/app.blade.php and layouts/dash.blade.php build the site's main
     * stylesheet <link> from settings.website_theme:
     *   asset('dash/css/' . $theme)  /  asset('dash2/css/' . $settings->website_theme)
     * When that column is empty (never set via the admin theme picker), this
     * resolves to a bare directory URL ("/dash/css", "/dash2/css") which the
     * server aborts/403s on, so NO theme CSS loads at all and admin/user
     * dashboard pages render as unstyled HTML. Seed a real, existing
     * stylesheet filename as the default so the site has working styling
     * out of the box. "blue.css" is the one filename that exists in every
     * theme folder this setting feeds (dash/css, dash2/css, temp/css/colors)
     * -- app.blade.php even special-cases it, remapping to atlantis.min.css
     * for the dash/css path -- so it is a safe universal default.
     */
    public function up()
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        // The column was never created by an earlier migration on this DB.
        if (! Schema::hasColumn('settings', 'website_theme')) {
            Schema::table('settings', function ($table) {
                $table->string('website_theme')->nullable();
            });
        }

        $settings = DB::table('settings')->where('id', 1)->first();
        if (! $settings) {
            return;
        }

        if (empty($settings->website_theme)) {
            DB::table('settings')->where('id', 1)->update([
                'website_theme' => 'blue.css',
            ]);
        }
    }

    public function down()
    {
        // Non-destructive: leave the seeded theme in place.
    }
};
