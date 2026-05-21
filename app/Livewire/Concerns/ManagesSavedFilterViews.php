<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\SavedFilterView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

trait ManagesSavedFilterViews
{
    public string $filterViewName = '';

    public function saveFilterView(): void
    {
        if (! $this->savedFilterViewsTableExists()) {
            session()->flash('error', __('Saved filter views are not ready yet. Please run database migrations.'));

            return;
        }

        $name = trim($this->filterViewName);

        if ($name === '') {
            session()->flash('error', __('Name this filter view before saving.'));

            return;
        }

        SavedFilterView::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'resource' => $this->filterViewResource(),
                'name' => $name,
            ],
            ['filters' => $this->currentFilterViewState()]
        );

        $this->filterViewName = '';
        session()->flash('success', __('Filter view saved.'));
    }

    public function applyFilterView(int $viewId): void
    {
        if (! $this->savedFilterViewsTableExists()) {
            return;
        }

        $view = SavedFilterView::query()
            ->where('user_id', auth()->id())
            ->where('resource', $this->filterViewResource())
            ->findOrFail($viewId);

        $this->applyFilterViewState($view->filters ?? []);

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function deleteFilterView(int $viewId): void
    {
        if (! $this->savedFilterViewsTableExists()) {
            return;
        }

        SavedFilterView::query()
            ->where('user_id', auth()->id())
            ->where('resource', $this->filterViewResource())
            ->whereKey($viewId)
            ->delete();

        session()->flash('success', __('Filter view deleted.'));
    }

    public function savedFilterViews(): Collection
    {
        if (! $this->savedFilterViewsTableExists()) {
            return new Collection;
        }

        return SavedFilterView::query()
            ->where('user_id', auth()->id())
            ->where('resource', $this->filterViewResource())
            ->orderBy('name')
            ->get();
    }

    private function savedFilterViewsTableExists(): bool
    {
        return Schema::hasTable('saved_filter_views');
    }

    abstract protected function filterViewResource(): string;

    abstract protected function currentFilterViewState(): array;

    abstract protected function applyFilterViewState(array $filters): void;
}
