<?php

namespace Tests\Feature;

use App\Models\Settings;
use App\Models\User;
use App\Models\Wdmethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransferCodeEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    private function seedSettings(array $overrides = [])
    {
        $attrs = array_merge([
            'site_name' => 'Test Bank',
            'contact_email' => 'admin@example.com',
            'currency' => '$',
            'code1' => 'Authentication Code',
            'code1status' => '1',
            'code1message' => 'Please enter your authentication code',
            'code1cost' => '5.00',
            'code2' => null,
            'code2status' => '0',
            'code2cost' => null,
            'code3' => null,
            'code3status' => '0',
            'code3cost' => null,
            'code4' => null,
            'code4status' => '0',
            'code4cost' => null,
            'code5' => null,
            'code5status' => '0',
            'code5cost' => null,
            'tax_code' => 'Tax Code',
            'tax_code_status' => '1',
            'tax_code_message' => 'Enter your tax code',
            'tax_code_cost' => '20.00',
            'otp' => '0',
        ], $overrides);

        return Settings::where('id', 1)->update($attrs + ['id' => 1]);
    }

    private function makeUser(array $overrides = [])
    {
        $attrs = array_merge([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'username' => 'user1',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'account_bal' => 10000,
            'account_status' => 'active',
            'pin' => '1234',
            'code1' => 'SECRET1',
            'tax_code' => 'TX-12345',
            'dailyTotal' => 0,
            'weeklyTotal' => 0,
            'monthlyTotal' => 0,
        ], $overrides);

        return User::create($attrs);
    }

    public function test_user_to_user_transfer_is_blocked_until_codes_entered()
    {
        $this->seedSettings();
        $s = Settings::find(1);
        $this->assertEquals('1', $s->code1status);
        $this->assertEquals('1', $s->tax_code_status);

        $sender = $this->makeUser(['email' => 'sender@example.com', 'username' => 'sender1']);
        $receiver = $this->makeUser(['email' => 'receiver@example.com', 'username' => 'receiver1']);

        $this->actingAs($sender);

        // Attempt a user-to-user transfer without any verification codes
        $response = $this->post(route('transfertouser'), [
            'email' => $receiver->email,
            'amount' => 100,
            'password' => 'password123',
        ]);

        // Transfer must NOT complete immediately; the user is sent for verification
        $response->assertOk()
            ->assertJsonPath('status', 419)
            ->assertJsonPath('redirect', route('code1verification'));

        // Balance must not have changed yet
        $this->assertDatabaseHas('users', [
            'id' => $sender->id,
            'account_bal' => 10000,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $receiver->id,
            'account_bal' => 10000,
        ]);

        // Now complete code1
        $response = $this->post(route('codecomfirm'), [
            'code1' => 'SECRET1',
        ]);
        // Should be redirected to tax code verification since it is next
        $response->assertRedirect(route('taxcodeverification'));

        // Complete tax code
        $response = $this->post(route('codecomfirm'), [
            'tax_code' => 'TX-12345',
        ]);
        // Every step done -> complete the user-to-user transfer
        $response->assertRedirect(route('completetransfertouser'));

        // Follow the completion redirect
        $this->get(route('completetransfertouser'));

        // Transfer should now have been applied
        $this->assertDatabaseHas('users', [
            'id' => $receiver->id,
            'account_bal' => 10100,
        ]);
    }

    public function test_local_transfer_preview_is_blocked_until_all_codes_entered()
    {
        $this->seedSettings();

        $user = $this->makeUser();
        $this->actingAs($user);

        // Stage a local transfer. This should stage session data and redirect
        // back to the first verification step (code1).
        $response = $this->post(route('localtransfer'), [
            'amount' => 100,
            'bankname' => 'Test Bank',
            'accountname' => 'Jane Doe',
            'accountnumber' => '12345678',
            'Accounttype' => 'Checking',
            'routing_number' => null,
            'swift_code' => null,
            'Description' => 'Test transfer',
            'pin' => '1234',
            'balance_type' => 'fiat',
        ]);

        // Should be redirected to the first pending verification (code1)
        $response->assertRedirect(route('code1verification'));

        // Balance unchanged (code verification first)
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_bal' => 10000,
        ]);

        // Going straight to preview (without completing codes) must bounce
        // back to verification.
        $this->get(route('previewtransfer'))
            ->assertRedirect(route('code1verification'));

        // Complete code1, then tax code, then preview passes.
        $this->post(route('codecomfirm'), ['code1' => 'SECRET1'])
            ->assertRedirect(route('taxcodeverification'));
        $this->post(route('codecomfirm'), ['tax_code' => 'TX-12345'])
            ->assertRedirect(route('previewtransfer'));

        // Preview now succeeds and processes the transfer (redirects to
        // transfer.success since all codes were entered).
        $this->get(route('previewtransfer'))
            ->assertRedirect();
    }

    public function test_code_pages_render_with_cost_info()
    {
        $this->seedSettings();
        $user = $this->makeUser();
        $this->actingAs($user);

        // Ensure the page renders successfully through the HTTP stack.
        $this->get(route('code1verification'))->assertStatus(200);
        $this->get(route('taxcodeverification'))->assertStatus(200);

        // The AppServiceProvider shares settings at boot time, so a direct
        // render with freshly-read settings confirms the cost block appears.
        $settings = Settings::find(1);
        $html1 = view('user.code1', ['settings' => $settings])->render();
        $this->assertStringContainsString('Cost of Authentication Code', $html1);
        $this->assertStringContainsString('5.00', $html1);

        $html2 = view('user.taxcode', ['settings' => $settings])->render();
        $this->assertStringContainsString('Cost of Tax Code', $html2);
        $this->assertStringContainsString('20.00', $html2);
    }

    public function test_admin_can_set_code_costs_and_tax_settings()
    {
        $this->seedSettings();

        $admin = new \App\Models\Admin();
        $admin->firstName = 'Admin';
        $admin->lastName = 'One';
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('password123');
        $admin->save();
        $this->actingAs($admin, 'admin');

        $this->put(route('updatertransfercodes'), [
            'code1' => 'Auth Code',
            'code1status' => '1',
            'code1cost' => '7.50',
            'code1message' => 'Enter auth code',
            'code2' => '',
            'code2status' => '0',
            'code3' => '',
            'code3status' => '0',
            'code4' => '',
            'code4status' => '0',
            'code5' => '',
            'code5status' => '0',
            'tax_code' => 'IRS Refund Code',
            'tax_code_status' => '1',
            'tax_code_message' => 'Enter your IRS refund code',
            'tax_code_cost' => '15.00',
            'otp' => '0',
        ]);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'code1' => 'Auth Code',
            'code1cost' => '7.50',
            'tax_code' => 'IRS Refund Code',
            'tax_code_status' => '1',
            'tax_code_cost' => '15.00',
        ]);
    }
}