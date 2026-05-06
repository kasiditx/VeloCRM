<?php

declare(strict_types=1);

namespace App\Livewire\Notes;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class NotesList extends Component
{
    public string $notableType;
    public int $notableId;
    public string $content = '';

    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->notable()->notes()->create([
            'content' => trim($data['content']),
            'user_id' => auth()->id(),
        ]);

        $this->reset('content');
        session()->flash('success', 'Note added successfully.');
    }

    public function delete(int $noteId): void
    {
        $note = $this->notable()->notes()->findOrFail($noteId);

        if ($note->user_id !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $note->delete();

        session()->flash('success', 'Note deleted successfully.');
    }

    protected function notable(): Model
    {
        $allowedTypes = [
            Lead::class,
            Customer::class,
        ];

        abort_unless(in_array($this->notableType, $allowedTypes, true), 404);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->notableType::query()->findOrFail($this->notableId);

        return $model;
    }

    public function render()
    {
        return view('livewire.notes.notes-list', [
            'notes' => $this->notable()->notes()->with('user')->latest()->get(),
        ]);
    }
}
