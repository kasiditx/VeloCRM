<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use App\Support\InvoiceDocuments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_number_does_not_increment_the_document_sequence(): void
    {
        Setting::set(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_RECEIPT, 2026), '7');

        $this->assertSame('REC-2026-0007', InvoiceDocuments::previewNumber(InvoiceDocuments::TYPE_RECEIPT, '2026-05-15'));
        $this->assertSame('7', Setting::get(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_RECEIPT, 2026)));
    }

    public function test_next_number_increments_per_document_type_and_year(): void
    {
        Setting::set(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_TAX_INVOICE, 2026), '7');

        $this->assertSame('TAX-2026-0007', InvoiceDocuments::nextNumber(InvoiceDocuments::TYPE_TAX_INVOICE, '2026-05-15'));
        $this->assertSame('8', Setting::get(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_TAX_INVOICE, 2026)));
    }

    public function test_next_number_skips_existing_invoice_numbers_before_incrementing(): void
    {
        Setting::set(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_RECEIPT, 2026), '1');
        $this->invoiceWithNumber('REC-2026-0001');

        $this->assertSame('REC-2026-0002', InvoiceDocuments::nextNumber(InvoiceDocuments::TYPE_RECEIPT, '2026-05-15'));
        $this->assertSame('3', Setting::get(InvoiceDocuments::nextSettingKey(InvoiceDocuments::TYPE_RECEIPT, 2026)));
    }

    private function invoiceWithNumber(string $number): Invoice
    {
        $owner = User::factory()->create();
        $customer = Customer::forceCreate([
            'name' => 'Numbering Customer',
            'email' => strtolower($number).'@example.com',
            'user_id' => $owner->id,
        ]);

        return Invoice::forceCreate([
            'number' => $number,
            'customer_id' => $customer->id,
            'invoice_date' => '2026-05-15',
            'due_date' => '2026-05-22',
            'subtotal' => 100,
            'tax_total' => 0,
            'discount' => 0,
            'total' => 100,
            'amount_paid' => 0,
            'balance_due' => 100,
            'status' => 'Sent',
            'user_id' => $owner->id,
        ]);
    }
}
