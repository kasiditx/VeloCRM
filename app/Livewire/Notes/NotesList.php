<?php

declare(strict_types=1);

namespace App\Livewire\Notes;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class NotesList extends Component
{
    use AuthorizesRequests;

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
        $notable = $this->notable();
        $this->authorize('view', $notable);
        $this->authorize('create', Note::class);

        $note = $notable->notes()->make([
            'content' => trim($data['content']),
        ]);
        $note->user_id = auth()->id();
        $note->save();

        $this->reset('content');
        session()->flash('success', 'Note added successfully.');
    }

    public function delete(int $noteId): void
    {
        $note = $this->notable()->notes()->findOrFail($noteId);
        $this->authorize('delete', $note);

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

        /** @var Model $model */
        $model = $this->notableType::query()->findOrFail($this->notableId);

        return $model;
    }

    public function render()
    {
        $this->authorize('view', $this->notable());

        return view('livewire.notes.notes-list', [
            'notes' => $this->notable()->notes()->with('user')->latest()->get(),
        ]);
    }
}
