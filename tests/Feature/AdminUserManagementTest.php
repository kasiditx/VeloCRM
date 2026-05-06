<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserIndex;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_admin_can_create_and_update_a_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin);

        Livewire::test(UserForm::class)
            ->set('name', 'Staff Member')
            ->set('email', 'staff@example.com')
            ->set('password', 'secret123')
            ->set('password_confirmation', 'secret123')
            ->set('role', 'Staff')
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'staff@example.com')->firstOrFail();

        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('Staff'));

        Livewire::test(UserForm::class, ['userId' => $user->id])
            ->set('name', 'Updated Staff')
            ->set('role', 'Admin')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Updated Staff', $user->name);
        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_admin_can_toggle_staff_activation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $staff = User::factory()->create(['is_active' => true]);
        $staff->assignRole('Staff');

        $this->actingAs($admin);

        Livewire::test(UserIndex::class)
            ->call('toggleActive', $staff->id);

        $this->assertFalse($staff->fresh()->is_active);
    }
}
