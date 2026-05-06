<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Notes\NotesList;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotesSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
    }

    public function test_user_can_add_and_delete_note_on_lead(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Lead With Notes',
            'email' => 'lead@example.com',
            'status' => 'New',
            'value' => 1000,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(NotesList::class, [
            'notableType' => Lead::class,
            'notableId' => $lead->id,
        ])
            ->set('content', 'First lead note')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('First lead note');

        $note = Note::query()->firstOrFail();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'notable_type' => Lead::class,
            'notable_id' => $lead->id,
            'user_id' => $user->id,
            'content' => 'First lead note',
        ]);

        Livewire::test(NotesList::class, [
            'notableType' => Lead::class,
            'notableId' => $lead->id,
        ])
            ->call('delete', $note->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_customer_notes_component_renders_existing_notes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $customer = Customer::create([
            'name' => 'Customer With Notes',
            'email' => 'customer@example.com',
            'user_id' => $user->id,
        ]);

        $customer->notes()->create([
            'content' => 'Customer onboarding note',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(NotesList::class, [
            'notableType' => Customer::class,
            'notableId' => $customer->id,
        ])
            ->assertSee('Customer onboarding note')
            ->assertSee('Notes');
    }
}
