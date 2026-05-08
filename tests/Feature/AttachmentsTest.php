<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Attachments\AttachmentPanel;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AttachmentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        Storage::fake('uploads');
    }

    public function test_user_can_upload_and_delete_lead_attachment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $lead = Lead::create([
            'name' => 'Lead Attachment',
            'status' => 'New',
            'value' => 1000,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(AttachmentPanel::class, [
            'attachableType' => Lead::class,
            'attachableId' => $lead->id,
        ])
            ->set('file', UploadedFile::fake()->create('proposal.pdf', 120))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('proposal.pdf');

        /** @var Attachment $attachment */
        $attachment = Attachment::query()->firstOrFail();

        Storage::disk('uploads')->assertExists($attachment->path);
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'attachable_type' => Lead::class,
            'attachable_id' => $lead->id,
            'filename' => 'proposal.pdf',
            'user_id' => $user->id,
        ]);

        Livewire::test(AttachmentPanel::class, [
            'attachableType' => Lead::class,
            'attachableId' => $lead->id,
        ])
            ->call('delete', $attachment->id)
            ->assertHasNoErrors();

        Storage::disk('uploads')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('attachments', [
            'id' => $attachment->id,
        ]);
    }

    public function test_customer_attachment_panel_renders_existing_attachment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');

        $customer = Customer::create([
            'name' => 'Customer Attachment',
            'email' => 'customer@example.com',
            'user_id' => $user->id,
        ]);

        $attachment = $customer->attachments()->create([
            'filename' => 'contract.pdf',
            'path' => 'attachments/customer/'.$customer->id.'/contract.pdf',
            'size' => 2048,
            'user_id' => $user->id,
        ]);

        Storage::disk('uploads')->put($attachment->path, 'dummy-file');

        $this->actingAs($user);

        Livewire::test(AttachmentPanel::class, [
            'attachableType' => Customer::class,
            'attachableId' => $customer->id,
        ])
            ->assertSee('contract.pdf')
            ->assertSee('Attachments');
    }
}
