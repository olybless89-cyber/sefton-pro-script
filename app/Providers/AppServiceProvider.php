<?php

namespace App\Providers;

use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Settings;
use App\Models\SettingsCont;
use App\Models\TermsPrivacy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        FacadesStorage::extend('sftp', function ($app, $config) {
            return new Filesystem(new SftpAdapter($config));
        });

        Paginator::useBootstrap();

        // Sharing settings with all views (safe when tables/rows don't exist yet)
        $settings = Schema::hasTable('settings') ? Settings::where('id', '1')->first() : null;
        $terms = Schema::hasTable('terms_privacies') ? TermsPrivacy::find(1) : null;
        $moreset = Schema::hasTable('settings_conts') ? SettingsCont::find(1) : null;

        View::share('settings', $settings);
        View::share('terms', $terms);
        View::share('moresettings', $moreset);
        View::share('mod', $settings->modules ?? null);
    }
}
