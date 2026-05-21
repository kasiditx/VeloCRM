<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_customer_portal(): void
    {
        $this->get(route('portal.invoices.index'))->assertRedirect(route('login'));
    }

    public function test_customer_role_can_enter_portal_but_not_internal_crm(): void
    {
        $this->seed(DefaultRolesSeeder::class);

        $owner = User::factory()->create();
        $owner->assignRole('Admin');

        $customer = Customer::forceCreate([
            'name' => 'Portal Guard Customer',
            'email' => 'portal-guard@example.com',
            'user_id' => $owner->id,
        ]);

        $customerUser = User::factory()
            ->forCustomer($customer->id)
            ->create(['email' => 'portal-guard-user@example.com']);
        $customerUser->assignRole('Customer');

        $this->actingAs($customerUser);

        $this->get(route('portal.invoices.index'))->assertOk();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('leads.index'))->assertForbidden();
        $this->get(route('admin.settings'))->assertForbidden();
    }
}
