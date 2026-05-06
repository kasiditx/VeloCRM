<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Leads\LeadImport;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_admin_can_preview_and_import_leads_from_csv(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('Admin');

        $owner = User::factory()->create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
        ]);
        $owner->assignRole('Staff');

        $this->actingAs($admin);

        $csv = <<<CSV
Full Name,Email Address,Phone Number,Company Name,Lead Status,Lead Source,Deal Value,Description,Assigned To
Acme Prospect,lead@example.com,0812345678,Acme Co,Qualified,Website,25000,Warm inbound lead,owner@example.com
CSV;

        Livewire::test(LeadImport::class)
            ->set('file', UploadedFile::fake()->createWithContent('leads.csv', $csv))
            ->assertSet('csvHeaders.0', 'Full Name')
            ->assertSet('columnMap.0', 'name')
            ->call('import')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Acme Prospect',
            'email' => 'lead@example.com',
            'company' => 'Acme Co',
            'status' => 'Qualified',
            'source' => 'Website',
            'user_id' => $owner->id,
        ]);
    }

    public function test_import_skips_duplicate_rows_and_reports_failures(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('Admin');

        Lead::create([
            'name' => 'Existing Lead',
            'email' => 'existing@example.com',
            'status' => 'New',
            'value' => 1000,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $csv = <<<CSV
Name,Email,Phone,Company,Status,Source,Value,Notes,Assigned To
Duplicate Lead,existing@example.com,0811111111,Acme,Qualified,Website,2000,Should skip,admin@example.com
Broken Lead,not-an-email,0822222222,Acme,New,Referral,3000,Bad email,admin@example.com
Fresh Lead,fresh@example.com,0833333333,Fresh Co,Contacted,Event,4500,Should import,admin@example.com
CSV;

        Livewire::test(LeadImport::class)
            ->set('file', UploadedFile::fake()->createWithContent('leads.csv', $csv))
            ->call('import')
            ->assertHasNoErrors()
            ->assertSet('importSummary.imported', 1)
            ->assertSet('importSummary.skipped', 1)
            ->assertSet('importSummary.failed', 1);

        $this->assertDatabaseHas('leads', [
            'email' => 'fresh@example.com',
            'status' => 'Contacted',
        ]);
        $this->assertDatabaseMissing('leads', [
            'name' => 'Broken Lead',
        ]);
    }

    public function test_admin_can_download_lead_import_template(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->withoutMiddleware()
            ->actingAs($admin)
            ->get(route('leads.import.template'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }
}
