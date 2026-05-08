<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Reports\ReportIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_reports_page_filters_metrics_and_can_export_csv(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $customer = Customer::create([
            'name' => 'Acme Co',
            'email' => 'acme@example.com',
            'user_id' => $admin->id,
        ]);

        $lead = Lead::create([
            'name' => 'Lead Alpha',
            'email' => 'lead@example.com',
            'status' => 'Won',
            'source' => 'Website',
            'value' => 25000,
            'user_id' => $admin->id,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        $customer->update([
            'lead_id' => $lead->id,
        ]);

        Invoice::create([
            'number' => 'INV-RPT-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->subDays(8)->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => 15000,
            'total' => 15000,
            'balance_due' => 0,
            'status' => 'Paid',
            'user_id' => $admin->id,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $this->actingAs($admin);

        Livewire::test(ReportIndex::class)
            ->set('startDate', now()->subDays(30)->toDateString())
            ->set('endDate', now()->toDateString())
            ->call('applyFilters')
            ->assertSee('Lead Conversion Summary')
            ->assertSee('Acme Co')
            ->assertSee('Website')
            ->assertSee('100.0%')
            ->call('exportCsv')
            ->assertFileDownloaded('reports-'.now()->subDays(30)->toDateString().'-to-'.now()->toDateString().'.csv');
    }
}
