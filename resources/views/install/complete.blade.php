<x-install-layout :currentStep="6">

    <div class="text-center">
        <div class="success-icon">🎉</div>

        <h1>Installation Complete!</h1>
        <p class="subtitle" style="margin-bottom: 1rem;">
            {{ config('app.name', 'VeloCRM') }} has been installed successfully. Review the setup summary below, then log in with your admin credentials and complete the final checklist.
        </p>

        <ul class="check-list" style="text-align: left; max-width: 360px; margin: 1.5rem auto;">
            <li>
                <span class="check-icon pass">✓</span>
                Environment configured
            </li>
            <li>
                <span class="check-icon pass">✓</span>
                Database tables created
            </li>
            <li>
                <span class="check-icon pass">✓</span>
                Default roles seeded
            </li>
            <li>
                <span class="check-icon pass">✓</span>
                Admin account created
            </li>
            <li>
                <span class="check-icon pass">✓</span>
                Company profile and regional defaults saved
            </li>
            <li>
                <span class="check-icon {{ ! empty($summary['smtp_configured']) ? 'pass' : 'warn' }}">{{ ! empty($summary['smtp_configured']) ? '✓' : '!' }}</span>
                {{ ! empty($summary['smtp_configured']) ? 'SMTP saved during installation' : 'SMTP skipped during installation' }}
            </li>
        </ul>

        <div class="summary-grid" style="text-align: left;">
            <div class="summary-card">
                <div class="summary-card-label">Company</div>
                <div class="summary-card-value">{{ $summary['company_name'] ?? config('app.name', 'VeloCRM') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Company Address</div>
                <div class="summary-card-value">{{ $summary['company_address'] ?? 'Not set' }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Site Title</div>
                <div class="summary-card-value">{{ $summary['site_title'] ?? config('app.name', 'VeloCRM') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Regional Format</div>
                <div class="summary-card-value">{{ ($summary['currency_symbol'] ?? '$') . ' / ' . ($summary['date_format'] ?? 'd/m/Y') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Default Locale</div>
                <div class="summary-card-value">{{ strtoupper($summary['app_locale'] ?? 'EN') }}</div>
            </div>
        </div>

        <div class="alert alert-success" style="text-align: left; margin-top: 1.5rem;">
            <span class="alert-icon">✓</span>
            <span>
                Post-install checklist:
                test SMTP, upload your logo, review admin settings, and configure a cron job for <code>php artisan schedule:run</code> every minute.
            </span>
        </div>

        <div class="alert alert-warning" style="text-align: left;">
            <span class="alert-icon">🔒</span>
            <span>
                For security, the installer will be permanently disabled after you click the button below.
                To re-install, delete the file <code>storage/installed</code> from your server.
            </span>
        </div>

        <form method="POST" action="/install/finalize" style="margin-top: 1.5rem;">
            @csrf
            <button type="submit" class="btn btn-success btn-block" style="font-size: 1rem; padding: 1rem;">
                Launch CRM
            </button>
        </form>
    </div>

</x-install-layout>
