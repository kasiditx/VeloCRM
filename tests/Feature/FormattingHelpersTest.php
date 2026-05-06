<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormattingHelpersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_currency_and_date_helpers_follow_settings(): void
    {
        Setting::set('currency_symbol', '€');
        Setting::set('date_format', 'Y-m-d');

        $this->assertSame('€150.50', format_currency(150.5));
        $this->assertSame('2026-04-15', format_date('2026-04-15 10:30:00'));
    }

    public function test_lead_index_uses_configured_currency_symbol(): void
    {
        Setting::set('currency_symbol', '€');
        Setting::set('date_format', 'Y-m-d');

        $user = User::factory()->create();
        $user->assignRole('Staff');

        Lead::create([
            'name' => 'Euro Lead',
            'status' => 'Qualified',
            'value' => 999.99,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Leads\LeadIndex::class)
            ->assertSee('€999.99');
    }
}
