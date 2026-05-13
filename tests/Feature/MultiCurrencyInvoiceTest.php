<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Reports\ReportIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiCurrencyInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        touch(storage_path('installed'));

        Setting::set('currency_code', 'THB');
        Setting::set('currency_symbol', '฿');

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
        $this->actingAs($this->admin);

        $this->customer = Customer::create([
            'name' => 'Multi Currency Customer',
            'email' => 'multi@example.com',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_invoice_form_persists_currency_and_exchange_rate(): void
    {
        Livewire::test(InvoiceForm::class)
            ->set('number', 'INV-MC-1')
            ->set('customer_id', $this->customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->set('currency', 'usd')
            ->set('exchange_rate', '35.250000')
            ->set('items.0.description', 'USD service')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 100)
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('number', 'INV-MC-1')->firstOrFail();

        $this->assertSame('USD', $invoice->currency);
        $this->assertSame('35.250000', $invoice->exchange_rate);
        $this->assertSame('$100.00 USD', $invoice->money($invoice->total));
    }

    public function test_invoice_views_use_invoice_currency_not_global_currency(): void
    {
        $invoice = $this->invoice('INV-MC-2', 'USD', 35.25, 100);
        $invoice->ensurePublicToken();

        $this->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('$100.00 USD');

        $this->get(route('invoices.show', $invoice->id))
            ->assertOk()
            ->assertSee('$100.00 USD')
            ->assertSee('USD');

        $this->get(route('public.invoice.show', $invoice->public_token))
            ->assertOk()
            ->assertSee('$100.00 USD')
            ->assertSee('USD');
    }

    public function test_reports_convert_paid_invoice_revenue_to_base_currency(): void
    {
        $this->invoice('INV-THB-1', 'THB', 1, 1000, 'Paid');
        $this->invoice('INV-USD-1', 'USD', 35, 100, 'Paid');

        Livewire::test(ReportIndex::class)
            ->set('startDate', now()->startOfMonth()->toDateString())
            ->set('endDate', now()->endOfMonth()->toDateString())
            ->assertSee('฿4,500.00');
    }

    private function invoice(string $number, string $currency, float $exchangeRate, float $total, string $status = 'Sent'): Invoice
    {
        return Invoice::forceCreate([
            'number' => $number,
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => $total,
            'tax_total' => 0,
            'discount' => 0,
            'total' => $total,
            'amount_paid' => $status === 'Paid' ? $total : 0,
            'balance_due' => $status === 'Paid' ? 0 : $total,
            'status' => $status,
            'currency' => $currency,
            'exchange_rate' => $exchangeRate,
            'user_id' => $this->admin->id,
        ]);
    }
}
