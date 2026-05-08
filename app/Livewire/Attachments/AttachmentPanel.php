<?php

declare(strict_types=1);

namespace App\Livewire\Attachments;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class AttachmentPanel extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $attachableType;

    public int $attachableId;

    public ?TemporaryUploadedFile $file = null;

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,webp,csv,xlsx,docx', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $attachable = $this->attachable();
        $this->authorize('view', $attachable);
        $this->authorize('create', Attachment::class);

        $directory = 'attachments/'.Str::kebab(class_basename($this->attachableType)).'/'.$this->attachableId;
        $extension = $this->file->getClientOriginalExtension();
        $storedPath = $this->file->storeAs($directory, Str::uuid().'.'.$extension, 'uploads');

        $attachment = $attachable->attachments()->make([
            'filename' => $this->file->getClientOriginalName(),
            'path' => $storedPath,
            'size' => $this->file->getSize(),
        ]);
        $attachment->user_id = auth()->id();
        $attachment->save();

        $this->reset('file');
        session()->flash('success', 'Attachment uploaded successfully.');
    }

    public function delete(int $attachmentId): void
    {
        $attachment = $this->attachable()->attachments()->findOrFail($attachmentId);
        $this->authorize('delete', $attachment);

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

        /** @var Model $model */
        $model = $this->attachableType::query()->findOrFail($this->attachableId);

        return $model;
    }

    public function render()
    {
        $this->authorize('view', $this->attachable());

        return view('livewire.attachments.attachment-panel', [
            'attachments' => $this->attachable()->attachments()->with('user')->latest()->get(),
        ]);
    }
}
