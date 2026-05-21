<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceForm;
use App\Models\Customer;
use App\Models\TaxTemplate;
use App\Models\User;
use App\Support\InvoiceItemCatalog;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceItemCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
        $this->actingAs($this->admin);

        $this->customer = Customer::create([
            'name' => 'Catalog Customer',
            'email' => 'catalog-customer@example.test',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_catalog_search_matches_name_code_sku_and_description(): void
    {
        $this->assertNotEmpty(InvoiceItemCatalog::search('CRM'));
        $this->assertNotEmpty(InvoiceItemCatalog::search('CRM-IMPL'));
        $this->assertNotEmpty(InvoiceItemCatalog::search('SVC-CRM-001'));
        $this->assertNotEmpty(InvoiceItemCatalog::search('implementation'));
    }

    public function test_selecting_catalog_item_fills_invoice_line_and_default_tax(): void
    {
        $vat = TaxTemplate::forceCreate([
            'name' => 'VAT 7%',
            'rate' => 7,
            'is_default' => true,
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(InvoiceForm::class)
            ->set('customer_id', $this->customer->id)
            ->call('selectCatalogItem', 0, 'crm-implementation')
            ->assertSet('items.0.description', 'CRM implementation service')
            ->assertSet('items.0.unit_price', 68000.0)
            ->assertSet('items.0.unit_label', 'project')
            ->assertSet('items.0.tax_option', 'vat_7')
            ->assertSet('tax_id', $vat->id)
            ->assertSet('currency', 'THB')
            ->assertSet('subtotal', 68000.0);
    }

    public function test_catalog_modal_can_add_multiple_items_and_merge_duplicates(): void
    {
        Livewire::test(InvoiceForm::class)
            ->call('clearItems')
            ->call('toggleCatalogSelection', 'crm-implementation')
            ->call('addSelectedCatalogItems', false)
            ->assertSet('items.0.description', 'CRM implementation service')
            ->call('toggleCatalogSelection', 'crm-implementation')
            ->call('addSelectedCatalogItems', true)
            ->assertSet('items.0.quantity', 2.0)
            ->assertCount('items', 1);
    }

    public function test_catalog_item_replaces_initial_blank_row(): void
    {
        Livewire::test(InvoiceForm::class)
            ->call('toggleCatalogSelection', 'crm-implementation')
            ->call('addSelectedCatalogItems', false)
            ->assertSet('items.0.description', 'CRM implementation service')
            ->assertCount('items', 1);
    }

    public function test_custom_item_query_is_saved_as_invoice_line_description(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('items.0.catalog_query', 'Custom onboarding workshop')
            ->call('useCustomItemQuery', 0)
            ->assertSet('items.0.description', 'Custom onboarding workshop')
            ->assertSet('items.0.catalog_key', null)
            ->assertSet('items.0.catalog_code', null);
    }

    public function test_custom_item_query_can_be_committed_without_waiting_for_debounce(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('items.0.catalog_query', 'Old visible value')
            ->call('commitItemQuery', 0, 'Fast typed custom item')
            ->assertSet('items.0.catalog_query', 'Fast typed custom item')
            ->assertSet('items.0.description', 'Fast typed custom item')
            ->assertSet('items.0.catalog_key', null);
    }

    public function test_committing_item_query_selects_first_catalog_match(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('items.0.catalog_query', 'CRM')
            ->call('commitItemQuery', 0)
            ->assertSet('items.0.description', 'CRM implementation service')
            ->assertSet('items.0.unit_price', 68000.0)
            ->assertSet('items.0.tax_option', 'vat_7');
    }

    public function test_invoice_requires_at_least_one_item(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('number', 'INV-NO-ITEMS')
            ->set('customer_id', $this->customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->call('clearItems')
            ->call('save')
            ->assertHasErrors(['items']);
    }
}
