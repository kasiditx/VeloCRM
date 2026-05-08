<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Lead;
use Livewire\Component;

class LeadKanban extends Component
{
    public $statuses = ['New', 'Contacted', 'Qualified', 'Lost', 'Won'];

    public function updateTaskOrder($groups)
    {
        foreach ($groups as $group) {
            $status = $group['value'];

            if (! in_array($status, $this->statuses)) {
                continue;
            }

            $leadIds = collect($group['items'])->pluck('value')->toArray();

            if (! empty($leadIds)) {
                // Update status for all leads in this group to match the column
                // (In a real app with strict ordering, we'd also update an 'order' or 'sort' column here)
                Lead::whereIn('id', $leadIds)->update(['status' => $status]);
            }
        }

        $this->dispatch('leadUpdated');
    }

    public function render()
    {
        $leads = Lead::all()->groupBy('status');

        return view('livewire.lead-kanban', [
            'leads' => $leads,
        ]);
    }
}
