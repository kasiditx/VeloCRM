<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';

    public $results = [
        'leads' => [],
        'customers' => [],
    ];

    public function updatedQuery()
    {
        $key = 'global-search:'.auth()->id().':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->results = ['leads' => [], 'customers' => []];

            return;
        }

        RateLimiter::hit($key, 60);

        if (strlen($this->query) < 2) {
            $this->results = ['leads' => [], 'customers' => []];

            return;
        }

        $this->results['leads'] = Lead::where(function ($q) {
            $q->where('name', 'like', '%'.$this->query.'%')
                ->orWhere('email', 'like', '%'.$this->query.'%')
                ->orWhere('company', 'like', '%'.$this->query.'%');
        })
            ->limit(5)
            ->get();

        $this->results['customers'] = Customer::where(function ($q) {
            $q->where('name', 'like', '%'.$this->query.'%')
                ->orWhere('email', 'like', '%'.$this->query.'%')
                ->orWhere('company', 'like', '%'.$this->query.'%');
        })
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}
