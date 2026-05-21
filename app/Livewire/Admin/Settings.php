<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Services\Payments\PaymentGatewayManager;
use App\Support\InvoiceDocuments;
use App\Support\PromptPay;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

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
    public array $templates = [];

    public ?int $editingTemplate = null;

    public string $template_subject = '';

    public string $template_body = '';

    // Branding
    public $logo;

    public $favicon;

    public ?string $current_logo = null;

    public ?string $current_favicon = null;

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

    public array $document_number_prefixes = [];

    public array $document_number_next = [];

    // Payment gateways
    public string $payment_driver = 'manual';

    public string $payment_mode = 'test';

    public string $payment_currency = 'USD';

    public string $payment_bank_transfer_instructions = '';

    public string $promptpay_id = '';

    public string $payment_stripe_public_key = '';

    public string $payment_stripe_secret_key = '';

    public string $payment_stripe_webhook_secret = '';

    public string $payment_paypal_checkout_url = '';

    public string $payment_paypal_webhook_secret = '';

    public string $payment_omise_checkout_url = '';

    public string $payment_omise_webhook_secret = '';

    public string $api_token_name = '';

    public ?string $newApiToken = null;

    public array $apiTokens = [];

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
        $this->company_name = (string) Setting::get('company_name', velocrm_company_name());
        $this->company_address = (string) Setting::get('company_address', '');
        $this->site_title = (string) Setting::get('site_title', velocrm_app_name());

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
        $this->loadDocumentNumberSettings();
        $this->envato_purchase_code = (string) Setting::get('envato_purchase_code', '');

        $this->payment_driver = (string) Setting::get('payment_driver', config('payments.default', 'manual'));
        $this->payment_mode = (string) Setting::get('payment_mode', config('payments.mode', 'test'));
        $this->payment_currency = (string) Setting::get('payment_currency', $this->currency_code);
        $this->payment_bank_transfer_instructions = (string) Setting::get('payment_manual_instructions', config('payments.drivers.manual.instructions', ''));
        $this->promptpay_id = (string) Setting::get('promptpay_id', '');
        $this->payment_stripe_public_key = (string) Setting::get('payment_stripe_public_key', config('payments.drivers.stripe.public_key', ''));
        $this->payment_stripe_secret_key = (string) Setting::get('payment_stripe_secret_key', '', true);
        $this->payment_stripe_webhook_secret = (string) Setting::get('payment_stripe_webhook_secret', '', true);
        $this->payment_paypal_checkout_url = (string) Setting::get('payment_paypal_checkout_url', config('payments.drivers.paypal.checkout_url', ''));
        $this->payment_paypal_webhook_secret = (string) Setting::get('payment_paypal_webhook_secret', '', true);
        $this->payment_omise_checkout_url = (string) Setting::get('payment_omise_checkout_url', config('payments.drivers.omise.checkout_url', ''));
        $this->payment_omise_webhook_secret = (string) Setting::get('payment_omise_webhook_secret', '', true);

        $this->loadTemplates();
        $this->loadApiTokens();
    }

    public function loadTemplates(): void
    {
        $this->templates = EmailTemplate::query()
            ->orderBy('name')
            ->get()
            ->toArray();
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
        if (! $this->editingTemplate) {
            session()->flash('error', 'Choose an email template before saving.');

            return;
        }

        $this->validate([
            'template_subject' => 'required|string|max:255',
            'template_body' => 'required|string',
        ]);

        $template = EmailTemplate::find($this->editingTemplate);
        if (! $template) {
            session()->flash('error', 'Email template was not found.');

            return;
        }

        $template->update([
            'subject' => $this->template_subject,
            'body' => $this->template_body,
        ]);

        $this->editingTemplate = null;
        $this->template_subject = '';
        $this->template_body = '';
        $this->loadTemplates();

        session()->flash('success', 'Email template updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->editingTemplate = null;
        $this->template_subject = '';
        $this->template_body = '';
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['general', 'branding', 'smtp', 'regional', 'payments', 'templates', 'api', 'exports', 'health'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function loadApiTokens(): void
    {
        $this->apiTokens = auth()->user()
            ->tokens()
            ->latest()
            ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at'])
            ->map(fn ($token): array => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->diffForHumans(),
                'created_at' => $token->created_at?->diffForHumans(),
            ])
            ->all();
    }

    public function createApiToken(): void
    {
        $this->validate([
            'api_token_name' => 'required|string|max:100',
        ]);

        $token = auth()->user()->createToken($this->api_token_name, ['crm:read', 'crm:write']);

        $this->newApiToken = $token->plainTextToken;
        $this->api_token_name = '';
        $this->loadApiTokens();

        session()->flash('success', 'API token created successfully. Copy it now because it will not be shown again.');
    }

    public function revokeApiToken(int $tokenId): void
    {
        auth()->user()
            ->tokens()
            ->whereKey($tokenId)
            ->delete();

        if ($this->newApiToken !== null) {
            $this->newApiToken = null;
        }

        $this->loadApiTokens();
        session()->flash('success', 'API token revoked successfully.');
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'site_title' => 'required|string|max:255',
            'envato_purchase_code' => 'nullable|string|max:255',
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
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:1024',
            'favicon' => 'nullable|file|mimes:png,jpg,jpeg,webp,ico|max:512',
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if ($this->logo) {
            $path = $this->logo->storeAs('branding', Str::uuid().'.'.$this->logo->getClientOriginalExtension(), 'uploads');
            Setting::set('logo', $path);
            $this->current_logo = $path;
        }

        if ($this->favicon) {
            $path = $this->favicon->storeAs('branding', Str::uuid().'.'.$this->favicon->getClientOriginalExtension(), 'uploads');
            Setting::set('favicon', $path);
            $this->current_favicon = $path;
        }

        Setting::set('primary_color', $this->primary_color);

        session()->flash('success', 'Branding settings saved successfully.');
    }

    public function saveSMTP(): void
    {
        $this->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        Setting::set('mail_host', $this->mail_host);
        Setting::set('mail_port', $this->mail_port);
        Setting::set('mail_username', $this->mail_username);

        if (! empty($this->mail_password)) {
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
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        try {
            Config::set('mail.mailers.smtp.host', $this->mail_host);
            Config::set('mail.mailers.smtp.port', $this->mail_port);
            Config::set('mail.mailers.smtp.username', $this->mail_username);
            Config::set('mail.mailers.smtp.password', $this->mail_password);
            Config::set('mail.mailers.smtp.encryption', $this->mail_encryption === 'none' ? null : $this->mail_encryption);
            Config::set('mail.from.address', $this->mail_from_address);
            Config::set('mail.from.name', $this->mail_from_name !== '' ? $this->mail_from_name : velocrm_app_name());

            Mail::raw(__('This is a test email from :app. If you received this, your SMTP settings are correct.', ['app' => velocrm_app_name()]), function ($message) {
                $message->to(auth()->user()->email)
                    ->subject(__('SMTP Test from :app', ['app' => velocrm_app_name()]));
            });

            session()->flash('success', 'Test email sent successfully to '.auth()->user()->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send test email: '.$e->getMessage());
        }
    }

    public function saveRegional(): void
    {
        $this->validate([
            'currency_code' => 'required|string|size:3',
            'currency_symbol' => 'required|string|max:10',
            'date_format' => 'required|in:d/m/Y,m/d/Y,Y-m-d,M d, Y',
            'document_number_prefixes.*' => 'required|string|max:10|regex:/^[A-Za-z0-9-]+$/',
            'document_number_next.*' => 'required|integer|min:1|max:999999',
        ]);

        $this->currency_code = strtoupper($this->currency_code);

        Setting::set('currency_code', $this->currency_code);
        Setting::set('default_currency', $this->currency_code);
        Setting::set('currency_symbol', $this->currency_symbol);
        Setting::set('date_format', $this->date_format);
        $this->saveDocumentNumberSettings();

        session()->flash('success', 'Regional settings saved successfully.');
    }

    public function savePaymentGateways(): void
    {
        $this->validate([
            'payment_driver' => 'required|in:manual,stripe,paypal,omise',
            'payment_mode' => 'required|in:test,live',
            'payment_currency' => 'required|string|size:3',
            'payment_bank_transfer_instructions' => 'nullable|string|max:2000',
            'promptpay_id' => [
                'nullable',
                'string',
                'max:32',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && $value !== '' && PromptPay::normalizeIdentifier((string) $value) === null) {
                        $fail(__('PromptPay ID must be a Thai mobile number, national ID, or corporate tax ID.'));
                    }
                },
            ],
            'payment_stripe_public_key' => 'nullable|string|max:255',
            'payment_stripe_secret_key' => 'nullable|string|max:255',
            'payment_stripe_webhook_secret' => 'nullable|string|max:255',
            'payment_paypal_checkout_url' => 'nullable|url|max:500',
            'payment_paypal_webhook_secret' => 'nullable|string|max:255',
            'payment_omise_checkout_url' => 'nullable|url|max:500',
            'payment_omise_webhook_secret' => 'nullable|string|max:255',
        ]);

        $this->payment_currency = strtoupper($this->payment_currency);
        $this->promptpay_id = trim($this->promptpay_id);

        Setting::set('payment_driver', $this->payment_driver);
        Setting::set('payment_mode', $this->payment_mode);
        Setting::set('payment_currency', $this->payment_currency);
        Setting::set('payment_manual_instructions', $this->payment_bank_transfer_instructions);
        Setting::set('promptpay_id', $this->promptpay_id);
        Setting::set('payment_stripe_public_key', $this->payment_stripe_public_key);
        Setting::set('payment_paypal_checkout_url', $this->payment_paypal_checkout_url);
        Setting::set('payment_omise_checkout_url', $this->payment_omise_checkout_url);

        if ($this->payment_stripe_secret_key !== '') {
            Setting::set('payment_stripe_secret_key', $this->payment_stripe_secret_key, true);
        }

        if ($this->payment_stripe_webhook_secret !== '') {
            Setting::set('payment_stripe_webhook_secret', $this->payment_stripe_webhook_secret, true);
        }

        if ($this->payment_paypal_webhook_secret !== '') {
            Setting::set('payment_paypal_webhook_secret', $this->payment_paypal_webhook_secret, true);
        }

        if ($this->payment_omise_webhook_secret !== '') {
            Setting::set('payment_omise_webhook_secret', $this->payment_omise_webhook_secret, true);
        }

        session()->flash('success', 'Payment gateway settings saved successfully.');
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
            if (! File::exists($path)) {
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
            'paymentGateways' => app(PaymentGatewayManager::class)->labels(),
            'documentTypes' => InvoiceDocuments::labels(),
        ])->layout('layouts.app');
    }

    private function loadDocumentNumberSettings(): void
    {
        $year = now()->year;

        foreach (InvoiceDocuments::types() as $type) {
            $this->document_number_prefixes[$type] = InvoiceDocuments::prefix($type);
            $this->document_number_next[$type] = (int) Setting::get(InvoiceDocuments::nextSettingKey($type, $year), 1);
        }
    }

    private function saveDocumentNumberSettings(): void
    {
        $year = now()->year;

        foreach (InvoiceDocuments::types() as $type) {
            $prefix = strtoupper(trim((string) ($this->document_number_prefixes[$type] ?? InvoiceDocuments::prefix($type))));
            $next = max((int) ($this->document_number_next[$type] ?? 1), 1);

            Setting::set(InvoiceDocuments::prefixSettingKey($type), $prefix);
            Setting::set(InvoiceDocuments::nextSettingKey($type, $year), (string) $next);
        }
    }
}
