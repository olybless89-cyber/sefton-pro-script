<?php

namespace Tests\Feature;

use App\Models\Wdmethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPaymentMethodFieldsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function wdmethod_persists_new_payment_detail_fields()
    {
        $m = new Wdmethod();
        $m->name = 'PayPal';
        $m->minimum = '10';
        $m->maximum = '5000';
        $m->charges_amount = '1.50';
        $m->charges_type = 'fixed';
        $m->methodtype = 'currency';
        $m->type = 'deposit';
        $m->status = 'enabled';
        $m->paypal_email = 'payments@bank.example';
        $m->cashapp_tag = '$seftonbank';
        $m->cashapp_fullname = 'Sefton Bank';
        $m->iban = 'DE89370400440532013000';
        $m->routing_number = '021000021';
        $m->bank_address = '1 Main Street, New York';
        $m->bank_country = 'United States';
        $m->bankname = 'Test Bank';
        $m->account_name = 'Sefton Pro';
        $m->account_number = '000123456';
        $m->swift_code = 'SBANKUS33';
        $m->save();

        $stored = Wdmethod::find($m->id);

        $this->assertSame('PayPal', $stored->name);
        $this->assertSame('payments@bank.example', $stored->paypal_email);
        $this->assertSame('$seftonbank', $stored->cashapp_tag);
        $this->assertSame('Sefton Bank', $stored->cashapp_fullname);
        $this->assertSame('DE89370400440532013000', $stored->iban);
        $this->assertSame('021000021', $stored->routing_number);
        $this->assertSame('1 Main Street, New York', $stored->bank_address);
        $this->assertSame('United States', $stored->bank_country);

        // Update via simple update (like PaymentController::updatemethod does)
        $updated = Wdmethod::where('id', $m->id)->update([
            'paypal_email'  => 'new@paypal.example',
            'cashapp_tag'   => '$newcashapp',
            'cashapp_fullname' => 'New CashApp Name',
        ]);

        $this->assertSame(1, $updated);
        $fresh = Wdmethod::find($m->id);
        $this->assertSame('new@paypal.example', $fresh->paypal_email);
        $this->assertSame('$newcashapp', $fresh->cashapp_tag);
        $this->assertSame('New CashApp Name', $fresh->cashapp_fullname);
    }

    /** @test */
    public function deposit_page_lists_all_enabled_methods_regardless_of_type()
    {
        // Admin has marked some methods withdrawal-only, which previously
        // hid them from the deposit page. Deposits should show every enabled
        // method so users can fund their account with the same options they
        // see on the transfer page.
        $btc = new Wdmethod();
        $btc->name = 'Bitcoin';
        $btc->type = 'both';
        $btc->status = 'enabled';
        $btc->save();

        $bank = new Wdmethod();
        $bank->name = 'Bank Transfer';
        $bank->type = 'withdrawal';
        $bank->status = 'enabled';
        $bank->save();

        $legacy = new Wdmethod();
        $legacy->name = 'PayPal';
        $legacy->type = null; // legacy row without a type
        $legacy->status = 'enabled';
        $legacy->save();

        $disabled = new Wdmethod();
        $disabled->name = 'CashApp (disabled)';
        $disabled->type = 'deposit';
        $disabled->status = 'disabled';
        $disabled->save();

        $user = \App\Models\User::create([
            'name'             => 'Deposit User',
            'email'            => 'deposit-user@example.com',
            'username'         => 'deposituser1',
            'password'         => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now(),
            'account_bal'      => 0,
            'account_status'   => 'active',
            'pin'              => '1234',
            'pinstatus'        => 0,
            'dailyTotal'       => 0,
            'weeklyTotal'      => 0,
            'monthlyTotal'     => 0,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('deposits'));
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringContainsString('Bitcoin', $html);
        $this->assertStringContainsString('Bank Transfer', $html);
        $this->assertStringContainsString('PayPal', $html);
        // Disabled methods must not be listed.
        $this->assertStringNotContainsString('CashApp (disabled)', $html);
    }

    /** @test */
    public function payment_page_renders_new_payment_details()
    {
        \App\Models\Settings::where('id', 1)->update([
            'currency' => '$',
        ]);

        $method = new Wdmethod();
        $method->name = 'CashApp';
        $method->minimum = '5';
        $method->maximum = '2000';
        $method->charges_amount = '0';
        $method->charges_type = 'percentage';
        $method->methodtype = 'currency';
        $method->type = 'deposit';
        $method->status = 'enabled';
        $method->cashapp_tag = '$seftonbank';
        $method->cashapp_fullname = 'Sefton Bank';
        $method->save();

        $user = \App\Models\User::create([
            'name'             => 'Test User',
            'email'            => 'depositor@example.com',
            'username'         => 'depositor1',
            'password'         => \Illuminate\Support\Facades\Hash::make('password123'),
            'email_verified_at' => now(),
            'account_bal'      => 10000,
            'account_status'   => 'active',
            'pin'              => '1234',
            'pinstatus'        => 0,
            'dailyTotal'       => 0,
            'weeklyTotal'      => 0,
            'monthlyTotal'     => 0,
        ]);

        $this->actingAs($user, 'web');
        $this->withSession([
            'payment_mode' => 'CashApp',
            'amount'       => 100,
            'intent'       => null,
        ]);

        $response = $this->get(route('payment'));
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertStringContainsString('CashApp', $html);
        $this->assertStringContainsString('$seftonbank', $html);
        $this->assertStringContainsString('Sefton Bank', $html);
    }
}