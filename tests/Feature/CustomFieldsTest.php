<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\CustomFieldIndex;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Leads\LeadForm;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        touch(storage_path('installed'));

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
        $this->actingAs($this->admin);
    }

    public function test_admin_can_manage_custom_fields(): void
    {
        $this->get(route('admin.custom-fields.index'))
            ->assertOk()
            ->assertSee('Custom Fields');

        Livewire::test(CustomFieldIndex::class)
            ->set('model_type', Lead::class)
            ->set('key', 'customer_segment')
            ->set('label_en', 'Customer Segment')
            ->set('label_th', 'กลุ่มลูกค้า')
            ->set('type', CustomField::TYPE_SELECT)
            ->set('options_text', "SME\nEnterprise")
            ->call('save')
            ->assertHasNoErrors();

        $field = CustomField::firstOrFail();

        $this->assertSame(Lead::class, $field->model_type);
        $this->assertSame(['SME', 'Enterprise'], $field->options);

        Livewire::test(CustomFieldIndex::class)
            ->call('edit', $field->id)
            ->set('label_en', 'Segment')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Segment', $field->fresh()->label_en);
    }

    public function test_lead_form_saves_custom_field_values(): void
    {
        $segment = CustomField::create([
            'model_type' => Lead::class,
            'key' => 'segment',
            'label_en' => 'Segment',
            'type' => CustomField::TYPE_SELECT,
            'options' => ['SME', 'Enterprise'],
        ]);
        $priority = CustomField::create([
            'model_type' => Lead::class,
            'key' => 'priority_account',
            'label_en' => 'Priority Account',
            'type' => CustomField::TYPE_BOOLEAN,
        ]);

        Livewire::test(LeadForm::class)
            ->set('name', 'Custom Field Lead')
            ->set('status', 'New')
            ->set('value', '1000')
            ->set('customFieldValues.'.$segment->id, 'Enterprise')
            ->set('customFieldValues.'.$priority->id, true)
            ->call('save')
            ->assertHasNoErrors();

        $lead = Lead::where('name', 'Custom Field Lead')->firstOrFail();

        $this->assertDatabaseHas('custom_field_values', [
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'custom_field_id' => $segment->id,
            'value' => 'Enterprise',
        ]);
        $this->assertDatabaseHas('custom_field_values', [
            'model_type' => Lead::class,
            'model_id' => $lead->id,
            'custom_field_id' => $priority->id,
            'value' => '1',
        ]);
    }

    public function test_customer_form_validates_custom_number_field(): void
    {
        $score = CustomField::create([
            'model_type' => Customer::class,
            'key' => 'credit_score',
            'label_en' => 'Credit Score',
            'type' => CustomField::TYPE_NUMBER,
        ]);

        Livewire::test(CustomerForm::class)
            ->set('name', 'Custom Field Customer')
            ->set('customFieldValues.'.$score->id, 'not-a-number')
            ->call('save')
            ->assertHasErrors(['customFieldValues.'.$score->id => 'numeric']);
    }

    public function test_invoice_form_saves_custom_date_field(): void
    {
        $deliveryDate = CustomField::create([
            'model_type' => Invoice::class,
            'key' => 'delivery_date',
            'label_en' => 'Delivery Date',
            'type' => CustomField::TYPE_DATE,
        ]);
        $customer = Customer::create([
            'name' => 'Invoice Custom Customer',
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(InvoiceForm::class)
            ->set('number', 'INV-CF-1')
            ->set('customer_id', $customer->id)
            ->set('invoice_date', now()->toDateString())
            ->set('due_date', now()->addDays(7)->toDateString())
            ->set('items.0.description', 'Custom field service')
            ->set('items.0.quantity', 1)
            ->set('items.0.unit_price', 250)
            ->set('customFieldValues.'.$deliveryDate->id, '2026-05-20')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::where('number', 'INV-CF-1')->firstOrFail();

        $this->assertDatabaseHas('custom_field_values', [
            'model_type' => Invoice::class,
            'model_id' => $invoice->id,
            'custom_field_id' => $deliveryDate->id,
            'value' => '2026-05-20',
        ]);
    }
}
