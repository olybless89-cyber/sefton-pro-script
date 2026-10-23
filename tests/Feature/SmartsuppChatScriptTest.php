<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SmartsuppChatScriptTest extends TestCase
{
    use DatabaseTransactions;

    private const KEY = '473324014e6663e24047be5194677fb4fe243332';

    /** @test */
    public function landing_page_includes_smartsupp_script()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertStringContainsString(self::KEY, $response->getContent());
        $this->assertStringContainsString('smartsuppchat.com/loader.js', $response->getContent());
    }

    /** @test */
    public function all_layout_files_include_the_livechat_partial()
    {
        foreach (['app', 'auth', 'base', 'dash', 'dash2', 'guest', 'guest2', 'dashtransfer'] as $layout) {
            $blade = file_get_contents(resource_path("views/layouts/{$layout}.blade.php"));
            $this->assertStringContainsString('layouts.livechat', $blade, "Layout {$layout} missing livechat include");
        }

        // And the partial itself carries the Smartsupp loader.
        $partial = file_get_contents(resource_path('views/layouts/livechat.blade.php'));
        $this->assertStringContainsString(self::KEY, $partial);
        $this->assertStringContainsString('smartsuppchat.com/loader.js', $partial);
    }

    /** @test */
    public function auth_layout_includes_smartsupp_script()
    {
        $settings = \App\Models\Settings::where('id', 1)->first();
        $html = view('layouts.auth', ['settings' => $settings])->render();
        $this->assertStringContainsString(self::KEY, $html);
    }
}