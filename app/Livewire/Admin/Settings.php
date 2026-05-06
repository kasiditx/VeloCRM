<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\EmailTemplate;

class Settings extends Component
{
    use WithFileUploads;

    public string $activeTab = 'general';

    // General
    public string $company_name = '';
    public string $company_address = '';
    public string $site_title = '';
    public string $envato_purchase_code = '';

    // Email Templates
    public $templates = [];
    public $editingTemplate = null;
    public string $template_subject = '';
    public string $template_body = '';

    // Branding
    public $logo;
    public $favicon;
    public $current_logo;
    public $current_favicon;
    public string $primary_color = '#4f46e5';

    // SMTP
    public string $mail_host = '';
    public string $mail_port = '';
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = '';
    public string $mail_from_name = '';

    // Regional
    public string $currency_code = 'USD';
    public string $currency_symbol = '$';
    public string $date_format = 'd/m/Y';

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'company_address' => 'nullable|string|max:1000',
        'site_title' => 'required|string|max:255',
        'logo' => 'nullable|image|max:1024',
        'favicon' => 'nullable|image|max:512',
        'primary_color' => 'required|string|max:7',
        'mail_host' => 'nullable|string',
        'mail_port' => 'nullable|string',
        'mail_username' => 'nullable|string',
        'mail_password' => 'nullable|string',
        'mail_from_address' => 'nullable|email',
        'currency_code' => 'required|string|max:10',
        'currency_symbol' => 'required|string|max:10',
        'date_format' => 'required|string',
        'envato_purchase_code' => 'required|string|max:255',
    ];

    public function mount(): void
    {
        $this->company_name = (string) Setting::get('company_name', config('app.name') ?? 'VeloCRM');
        $this->company_address = (string) Setting::get('company_address', '');
        $this->site_title = (string) Setting::get('site_title', 'VeloCRM');

        $this->current_logo = Setting::get('logo');
        $this->current_favicon = Setting::get('favicon');
        $this->primary_color = (string) Setting::get('primary_color', '#4f46e5');

        $this->mail_host = (string) Setting::get('mail_host', config('mail.mailers.smtp.host') ?? '');
        $this->mail_port = (string) Setting::get('mail_port', config('mail.mailers.smtp.port') ?? '587');
        $this->mail_username = (string) Setting::get('mail_username', config('mail.mailers.smtp.username') ?? '');
        $this->mail_password = (string) Setting::get('mail_password', '', true);
        $this->mail_encryption = (string) Setting::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $this->mail_from_address = (string) Setting::get('mail_from_address', config('mail.from.address') ?? '');
        $this->mail_from_name = (string) Setting::get('mail_from_name', config('mail.from.name') ?? '');

        $this->currency_code = (string) Setting::get('currency_code', 'USD');
        $this->currency_symbol = (string) Setting::get('currency_symbol', '$');
        $this->date_format = (string) Setting::get('date_format', 'd/m/Y');
        $this->envato_purchase_code = (string) Setting::get('envato_purchase_code', '');

        $this->loadTemplates();
    }

    public function loadTemplates(): void
    {
        $this->templates = EmailTemplate::all()->toArray();
    }

    public function editTemplate(int $id): void
    {
        $template = EmailTemplate::find($id);
        if ($template) {
            $this->editingTemplate = $template->id;
            $this->template_subject = $template->subject;
            $this->template_body = $template->body;
        }
    }

    public function saveTemplate(): void
    {
        if (!$this->editingTemplate) return;

        $template = EmailTemplate::find($this->editingTemplate);
        if ($template) {
            $template->update([
                'subject' => $this->template_subject,
                'body' => $this->template_body,
            ]);
            $this->editingTemplate = null;
            $this->loadTemplates();
            session()->flash('success', 'Email template updated successfully.');
        }
    }

    public function cancelEdit(): void
    {
        $this->editingTemplate = null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'site_title' => 'required|string|max:255',
        ]);

        Setting::set('company_name', $this->company_name);
        Setting::set('company_address', $this->company_address);
        Setting::set('site_title', $this->site_title);
        Setting::set('envato_purchase_code', $this->envato_purchase_code);

        session()->flash('success', 'General settings saved successfully.');
    }

    public function saveBranding(): void
    {
        $this->validate([
            'logo' => 'nullable|image|max:1024',
            'favicon' => 'nullable|image|max:512',
            'primary_color' => 'required|string|max:7',
        ]);

        if ($this->logo) {
            $path = $this->logo->store('branding', 'uploads');
            Setting::set('logo', $path);
            $this->current_logo = $path;
        }

        if ($this->favicon) {
            $path = $this->favicon->store('branding', 'uploads');
            Setting::set('favicon', $path);
            $this->current_favicon = $path;
        }

        Setting::set('primary_color', $this->primary_color);

        session()->flash('success', 'Branding settings saved successfully.');
    }

    public function saveSMTP(): void
    {
        $this->validate([
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_from_address' => 'required|email',
        ]);

        Setting::set('mail_host', $this->mail_host);
        Setting::set('mail_port', $this->mail_port);
        Setting::set('mail_username', $this->mail_username);

        if (!empty($this->mail_password)) {
            Setting::set('mail_password', $this->mail_password, true);
        }

        Setting::set('mail_encryption', $this->mail_encryption);
        Setting::set('mail_from_address', $this->mail_from_address);
        Setting::set('mail_from_name', $this->mail_from_name);

        session()->flash('success', 'SMTP settings saved successfully.');
    }

    public function sendTestEmail(): void
    {
        $this->validate([
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_from_address' => 'required|email',
        ]);

        try {
            // Temporarily configure mail
            Config::set('mail.mailers.smtp.host', $this->mail_host);
            Config::set('mail.mailers.smtp.port', $this->mail_port);
            Config::set('mail.mailers.smtp.username', $this->mail_username);
            Config::set('mail.mailers.smtp.password', $this->mail_password);
            Config::set('mail.mailers.smtp.encryption', $this->mail_encryption);
            Config::set('mail.from.address', $this->mail_from_address);
            Config::set('mail.from.name', $this->mail_from_name);

            Mail::raw('This is a test email from VeloCRM. If you received this, your SMTP settings are correct.', function ($message) {
                $message->to(auth()->user()->email)
                    ->subject('VeloCRM SMTP Test');
            });

            session()->flash('success', 'Test email sent successfully to ' . auth()->user()->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function saveRegional(): void
    {
        $this->validate([
            'currency_code' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'date_format' => 'required|string',
        ]);

        Setting::set('currency_code', $this->currency_code);
        Setting::set('currency_symbol', $this->currency_symbol);
        Setting::set('date_format', $this->date_format);

        session()->flash('success', 'Regional settings saved successfully.');
    }

    #[Computed]
    public function healthStatus(): array
    {
        $extensions = ['bcmath', 'ctype', 'fileinfo', 'gd', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'];
        $results = [];

        foreach ($extensions as $ext) {
            $results['extensions'][$ext] = extension_loaded($ext);
        }

        $results['php_version'] = PHP_VERSION_ID >= 80200;
        $results['php_version_text'] = PHP_VERSION;

        $paths = [
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'public/uploads' => public_path('uploads'),
        ];

        foreach ($paths as $name => $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true, true);
            }
            $results['permissions'][$name] = File::isWritable($path);
        }

        return $results;
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'health' => $this->healthStatus,
        ])->layout('layouts.app');
    }
}
