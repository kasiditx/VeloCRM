<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Tasks\TaskBoard;
use App\Livewire\Tasks\TaskForm;
use App\Livewire\Tasks\TaskIndex;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskUpdatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);

        $this->admin = User::factory()->create([
            'is_active' => true,
        ]);
        $this->admin->assignRole('Admin');
        $this->actingAs($this->admin);
    }

    public function test_task_board_status_update_persists_and_dispatches_refresh_event(): void
    {
        $task = Task::create([
            'title' => 'Follow up lead',
            'status' => 'Todo',
            'priority' => 'Medium',
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(TaskBoard::class)
            ->call('updateTaskStatus', $task->id, 'In Progress')
            ->assertHasNoErrors()
            ->assertDispatched('taskUpdated');

        $this->assertSame('In Progress', $task->fresh()->status);
    }

    public function test_task_index_delete_dispatches_refresh_event_and_uses_global_toast_key(): void
    {
        $task = Task::create([
            'title' => 'Archive task',
            'status' => 'Todo',
            'priority' => 'Medium',
            'user_id' => $this->admin->id,
        ]);

        Livewire::test(TaskIndex::class)
            ->call('delete', $task->id)
            ->assertDispatched('taskUpdated');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_form_uses_status_query_parameter_from_board_column(): void
    {
        Livewire::withQueryParams(['status' => 'Done'])
            ->test(TaskForm::class)
            ->assertSet('status', 'Done');
    }

    public function test_task_form_save_dispatches_refresh_event(): void
    {
        Livewire::test(TaskForm::class)
            ->set('title', 'Prepare proposal')
            ->set('status', 'In Progress')
            ->set('priority', 'High')
            ->set('due_date', now()->addDay()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('taskUpdated');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Prepare proposal',
            'status' => 'In Progress',
            'priority' => 'High',
            'user_id' => $this->admin->id,
        ]);
    }
}
