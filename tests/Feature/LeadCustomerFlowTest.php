<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Leads\LeadShow;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Customers\CustomerIndex;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadCustomerFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_authenticated_user_can_view_lead_and_customer_indexes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $this->actingAs($user);

        Livewire::test(LeadIndex::class)
            ->assertSee('Leads');

        Livewire::test(CustomerIndex::class)
            ->assertSee('Customers');
    }

    public function test_lead_can_be_converted_to_customer(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $lead = Lead::create([
            'name' => 'Acme Prospect',
            'email' => 'lead@example.com',
            'phone' => '123456789',
            'company' => 'Acme Co',
            'status' => 'Qualified',
            'source' => 'Website',
            'value' => 2500,
            'notes' => 'Warm lead',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(LeadShow::class, ['leadId' => $lead->id])
            ->call('openConvertModal')
            ->set('customerAddress', 'Bangkok')
            ->call('convertToCustomer');

        $lead->refresh();

        $this->assertSame('Won', $lead->status);
        $this->assertDatabaseHas('customers', [
            'lead_id' => $lead->id,
            'name' => 'Acme Prospect',
            'email' => 'lead@example.com',
            'user_id' => $user->id,
        ]);
        $this->assertInstanceOf(Customer::class, $lead->fresh()->customer);
    }
}
