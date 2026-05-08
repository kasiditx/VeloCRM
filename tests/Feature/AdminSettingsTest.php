<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\Settings;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DefaultRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultRolesSeeder::class);
        touch(storage_path('installed'));

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');
        $this->actingAs($admin);
    }

    public function test_admin_settings_page_renders(): void
    {
        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('SMTP');
    }

    public function test_general_settings_are_saved(): void
    {
        Livewire::test(Settings::class)
            ->set('company_name', 'Acme CRM')
            ->set('company_address', 'Bangkok')
            ->set('site_title', 'Acme Console')
            ->set('envato_purchase_code', 'purchase-code')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        $this->assertSame('Acme CRM', Setting::get('company_name'));
        $this->assertSame('Bangkok', Setting::get('company_address'));
        $this->assertSame('Acme Console', Setting::get('site_title'));
        $this->assertSame('purchase-code', Setting::get('envato_purchase_code'));
    }

    public function test_smtp_settings_validate_and_save(): void
    {
        Livewire::test(Settings::class)
            ->set('mail_host', 'smtp.example.com')
            ->set('mail_port', '587')
            ->set('mail_username', 'mailer')
            ->set('mail_password', 'secret')
            ->set('mail_encryption', 'none')
            ->set('mail_from_address', 'noreply@example.com')
            ->set('mail_from_name', 'VeloCRM')
            ->call('saveSMTP')
            ->assertHasNoErrors();

        $this->assertSame('smtp.example.com', Setting::get('mail_host'));
        $this->assertSame('587', Setting::get('mail_port'));
        $this->assertSame('none', Setting::get('mail_encryption'));
        $this->assertSame('secret', Setting::get('mail_password', '', true));
    }

    public function test_regional_settings_are_normalized_and_saved(): void
    {
        Livewire::test(Settings::class)
            ->set('currency_code', 'thb')
            ->set('currency_symbol', '฿')
            ->set('date_format', 'Y-m-d')
            ->call('saveRegional')
            ->assertHasNoErrors();

        $this->assertSame('THB', Setting::get('currency_code'));
        $this->assertSame('฿', Setting::get('currency_symbol'));
        $this->assertSame('Y-m-d', Setting::get('date_format'));
    }

    public function test_email_template_can_be_edited(): void
    {
        $template = EmailTemplate::create([
            'name' => 'Invoice Sent',
            'subject' => 'Original subject',
            'body' => 'Original body',
        ]);

        Livewire::test(Settings::class)
            ->call('editTemplate', $template->id)
            ->assertSet('editingTemplate', $template->id)
            ->set('template_subject', 'Updated subject')
            ->set('template_body', 'Updated body')
            ->call('saveTemplate')
            ->assertHasNoErrors()
            ->assertSet('editingTemplate', null);

        $this->assertSame('Updated subject', $template->fresh()->subject);
        $this->assertSame('Updated body', $template->fresh()->body);
    }

    public function test_invalid_settings_tab_is_ignored(): void
    {
        Livewire::test(Settings::class)
            ->assertSet('activeTab', 'general')
            ->call('setTab', 'smtp')
            ->assertSet('activeTab', 'smtp')
            ->call('setTab', 'invalid')
            ->assertSet('activeTab', 'smtp');
    }
}
