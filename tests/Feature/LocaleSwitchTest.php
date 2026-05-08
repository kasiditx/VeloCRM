<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_guest_can_switch_locale_to_thai(): void
    {
        $response = $this->from('/login')->withoutMiddleware(VerifyCsrfToken::class)->post(route('locale.switch'), [
            'locale' => 'th',
        ]);

        $response->assertRedirect('/login');
        $this->assertSame('th', session('locale'));

        $this->withSession(['locale' => 'th'])->get('/login')
            ->assertSee('อีเมล')
            ->assertSee('เข้าสู่ระบบ');
    }

    public function test_authenticated_user_sees_thai_navigation_after_switching_locale(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $this->actingAs($user);

        $this->withoutMiddleware(VerifyCsrfToken::class)->post(route('locale.switch'), [
            'locale' => 'th',
        ])->assertRedirect();

        $this->withSession(['locale' => 'th'])->get('/profile')
            ->assertSee('แดชบอร์ด')
            ->assertSee('โปรไฟล์');
    }
}
