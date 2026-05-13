<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public string $userId = '';

    public string $modelType = '';

    public string $startDate = '';

    public string $endDate = '';

    protected $queryString = [
        'userId' => ['except' => ''],
        'modelType' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public array $modelTypes = [
        Lead::class => 'Lead',
        Customer::class => 'Customer',
        Invoice::class => 'Invoice',
        Proposal::class => 'Proposal',
        Task::class => 'Task',
        Payment::class => 'Payment',
    ];

    public function updated(string $property): void
    {
        if (in_array($property, ['userId', 'modelType', 'startDate', 'endDate'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['userId', 'modelType', 'startDate', 'endDate']);
        $this->resetPage();
    }

    public function render()
    {
        $activities = Activity::query()
            ->with('causer')
            ->when($this->userId !== '', fn ($query) => $query->where('causer_type', User::class)->where('causer_id', $this->userId))
            ->when($this->modelType !== '', fn ($query) => $query->where('subject_type', $this->modelType))
            ->when($this->startDate !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->endDate))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.activity-log-index', [
            'activities' => $activities,
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ])->layout('layouts.app');
    }
}
