<?php

declare(strict_types=1);

namespace App\Livewire\Attachments;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class AttachmentPanel extends Component
{
    use WithFileUploads;

    public string $attachableType;
    public int $attachableId;
    public TemporaryUploadedFile|null $file = null;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $attachable = $this->attachable();
        $directory = 'attachments/' . Str::kebab(class_basename($this->attachableType)) . '/' . $this->attachableId;
        $storedPath = $this->file->store($directory, 'uploads');

        $attachable->attachments()->create([
            'filename' => $this->file->getClientOriginalName(),
            'path' => $storedPath,
            'size' => $this->file->getSize(),
            'user_id' => auth()->id(),
        ]);

        $this->reset('file');
        session()->flash('success', 'Attachment uploaded successfully.');
    }

    public function delete(int $attachmentId): void
    {
        $attachment = $this->attachable()->attachments()->findOrFail($attachmentId);

        if ($attachment->user_id !== auth()->id() && ! auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        Storage::disk('uploads')->delete($attachment->path);
        $attachment->delete();

        session()->flash('success', 'Attachment deleted successfully.');
    }

    protected function attachable(): Model
    {
        $allowedTypes = [
            Lead::class,
            Customer::class,
        ];

        abort_unless(in_array($this->attachableType, $allowedTypes, true), 404);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->attachableType::query()->findOrFail($this->attachableId);

        return $model;
    }

    public function render()
    {
        return view('livewire.attachments.attachment-panel', [
            'attachments' => $this->attachable()->attachments()->with('user')->latest()->get(),
        ]);
    }
}
