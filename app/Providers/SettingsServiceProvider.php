<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Settings;
use App\Models\Paystack;
use App\Models\SettingsCont;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $settings = Settings::where('id', '1')->first();

        if (!$settings) {
            return;
        }

        $paystack = Schema::hasTable('paystacks') ? Paystack::where('id', '1')->first() : null;
        $settings2 = Schema::hasTable('settings_conts') ? SettingsCont::find(1) : null;

        if ($settings->install_type == 'Sub-Folder') {
            $urls = explode('/', $settings->site_address);
            $assetUrl = '/' . end($urls);
        } else {
            $assetUrl = null;
        }

        // Set configuration values at run time
        config([
            'captcha.secret' => $settings->capt_secret,
            'captcha.sitekey' => $settings->capt_sitekey,
            'services.google.client_id' =>  $settings->google_id,
            'services.google.client_secret' =>  $settings->google_secret,
            'services.google.redirect' =>  $settings->google_redirect,
            'mail.mailers.smtp.host' =>  $settings->smtp_host,
            'mail.mailers.smtp.port' =>  $settings->smtp_port,
            'mail.mailers.smtp.encryption' =>  $settings->smtp_encrypt,
            'mail.mailers.smtp.username' =>  $settings->smtp_user,
            'mail.mailers.smtp.password' =>  $settings->smtp_password,
            'mail.default' => $settings->mail_server,
            'mail.from.address' => $settings->emailfrom,
            'mail.from.name' => $settings->emailfromname,
            'app.timezone' => $settings->timezone,
            'app.name' => $settings->site_name,
            'app.url' => $settings->site_address,
            'paystack.publicKey' => $paystack->paystack_public_key ?? null,
            'paystack.secretKey' => $paystack->paystack_secret_key ?? null,
            'paystack.paymentUrl' => $paystack->paystack_url ?? null,
            'paystack.merchantEmail' => $paystack->paystack_email ?? null,
            'livewire.asset_url' => $assetUrl,
            'flutterwave.publicKey' => $settings2->flw_public_key ?? null,
            'flutterwave.secretKey' => $settings2->flw_secret_key ?? null,
            'flutterwave.secretHash' => $settings2->flw_secret_hash ?? null,
            'services.telegram-bot-api.token' =>  $settings2->telegram_bot_api ?? null,
        ]);
    }
}
