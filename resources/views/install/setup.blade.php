<x-install-layout :currentStep="4">

    <h1>Environment Setup</h1>
    <p class="subtitle">
        Configure the application profile, regional defaults, and optional SMTP settings before the installer writes your environment and database setup.
    </p>

    <form method="POST" action="/install/setup" id="setup-form">
        @csrf

        <div class="section-title">License Verification</div>
        <div class="form-group">
            <label class="form-label" for="purchase_code">Envato Purchase Code</label>
            <input type="text"
                   class="form-input"
                   id="purchase_code"
                   name="purchase_code"
                   value="{{ old('purchase_code') }}"
                   placeholder="e.g. 1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p"
                   required>
            <div class="form-hint">Required to verify your purchase and receive future updates.</div>
        </div>

        <div class="section-title">Application</div>
        <div class="form-group">
            <label class="form-label" for="app_url">Application URL</label>
            <input type="url"
                   class="form-input"
                   id="app_url"
                   name="app_url"
                   value="{{ $defaults['app_url'] }}"
                   placeholder="https://yourdomain.com"
                   required>
            <div class="form-hint">The full URL where the CRM will be accessible, for example https://crm.example.com.</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="company_name">Company Name</label>
                <input type="text" class="form-input" id="company_name" name="company_name" value="{{ $defaults['company_name'] }}" placeholder="Your Company" required>
                <div class="form-hint">Used in PDFs, settings, and system branding.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="site_title">Site Title</label>
                <input type="text" class="form-input" id="site_title" name="site_title" value="{{ $defaults['site_title'] }}" placeholder="Sales CRM" required>
                <div class="form-hint">Used for browser title and application name.</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="company_address">Company Address</label>
            <textarea class="form-input" id="company_address" name="company_address" rows="3" placeholder="Company billing address">{{ $defaults['company_address'] }}</textarea>
            <div class="form-hint">Optional, but recommended for invoice and proposal PDFs.</div>
        </div>

        <div class="section-title">Regional Defaults</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="app_locale">Default Language</label>
                <select class="form-input" id="app_locale" name="app_locale">
                    <option value="en" @selected($defaults['app_locale'] === 'en')>English</option>
                    <option value="th" @selected($defaults['app_locale'] === 'th')>Thai</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="app_timezone">Timezone</label>
                <input type="text" class="form-input" id="app_timezone" name="app_timezone" value="{{ $defaults['app_timezone'] }}" placeholder="Asia/Bangkok" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="currency_code">Currency Code</label>
                <input type="text" class="form-input" id="currency_code" name="currency_code" value="{{ $defaults['currency_code'] }}" placeholder="USD" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="currency_symbol">Currency Symbol</label>
                <input type="text" class="form-input" id="currency_symbol" name="currency_symbol" value="{{ $defaults['currency_symbol'] }}" placeholder="$" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="date_format">Date Format</label>
            <select class="form-input" id="date_format" name="date_format">
                <option value="d/m/Y" @selected($defaults['date_format'] === 'd/m/Y')>d/m/Y</option>
                <option value="m/d/Y" @selected($defaults['date_format'] === 'm/d/Y')>m/d/Y</option>
                <option value="Y-m-d" @selected($defaults['date_format'] === 'Y-m-d')>Y-m-d</option>
            </select>
        </div>

        <div class="section-title">SMTP (Optional)</div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="mail_host">Mail Host</label>
                <input type="text" class="form-input" id="mail_host" name="mail_host" value="{{ $defaults['mail_host'] }}" placeholder="smtp.mailtrap.io">
            </div>

            <div class="form-group">
                <label class="form-label" for="mail_port">Mail Port</label>
                <input type="text" class="form-input" id="mail_port" name="mail_port" value="{{ $defaults['mail_port'] }}" placeholder="587">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="mail_encryption">Encryption</label>
                <select class="form-input" id="mail_encryption" name="mail_encryption">
                    <option value="tls" @selected($defaults['mail_encryption'] === 'tls')>TLS</option>
                    <option value="ssl" @selected($defaults['mail_encryption'] === 'ssl')>SSL</option>
                    <option value="" @selected($defaults['mail_encryption'] === '')>None</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="mail_from_name">Sender Name</label>
                <input type="text" class="form-input" id="mail_from_name" name="mail_from_name" value="{{ $defaults['mail_from_name'] }}" placeholder="Sales Team">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="mail_username">Username</label>
                <input type="text" class="form-input" id="mail_username" name="mail_username" value="{{ $defaults['mail_username'] }}" placeholder="smtp-user">
            </div>

            <div class="form-group">
                <label class="form-label" for="mail_password">Password</label>
                <input type="password" class="form-input" id="mail_password" name="mail_password" value="{{ $defaults['mail_password'] }}" placeholder="••••••••">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="mail_from_address">Sender Email</label>
            <input type="email" class="form-input" id="mail_from_address" name="mail_from_address" value="{{ $defaults['mail_from_address'] }}" placeholder="noreply@example.com">
            <div class="form-hint">You can skip SMTP now and configure it later from Admin Settings.</div>
        </div>

        <div class="setup-steps" style="margin-top: 1.5rem;">
            <div class="section-title text-sm border-none mb-2">Starter Content</div>
            <label class="flex items-center gap-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition mb-6">
                <input type="checkbox" name="install_demo_data" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-4 h-4">
                <div>
                    <strong class="block text-gray-800">Install sample records</strong>
                    <span class="text-xs text-gray-500">Enable this only for testing. Leave it off for a clean production setup.</span>
                </div>
            </label>

            <div class="setup-step">
                <div class="setup-step-icon">📄</div>
                <span>Write the <code>.env</code> file with app, locale, and mail settings</span>
            </div>
            <div class="setup-step">
                <div class="setup-step-icon">🔑</div>
                <span>Generate application encryption key</span>
            </div>
            <div class="setup-step">
                <div class="setup-step-icon">🗄️</div>
                <span>Run database migrations (create tables)</span>
            </div>
            <div class="setup-step">
                <div class="setup-step-icon">🌱</div>
                <span>Seed default roles & permissions, then save onboarding settings</span>
            </div>
        </div>

        <div class="alert alert-warning">
            <span class="alert-icon">⚠</span>
            <span>
                This process may take a minute. <strong>Do not close this page</strong> until it completes.
            </span>
        </div>

        <div class="btn-group">
            <a href="/install/database" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary" id="setup-btn" onclick="this.innerHTML='<span class=\'spinner\'></span> Installing...'; this.disabled=true; this.form.submit();">
                Install Now →
            </button>
        </div>
    </form>

</x-install-layout>
