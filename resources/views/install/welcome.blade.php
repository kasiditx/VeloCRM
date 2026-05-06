<x-install-layout :currentStep="1">

    <h1>Welcome to {{ velocrm_app_name() }}</h1>
    <p class="subtitle">
        Thank you for purchasing {{ velocrm_app_name() }}. This wizard will guide you through the installation process
        in just a few minutes. Please make sure you have your database credentials ready.
    </p>

    <div class="setup-steps">
        <div class="setup-step">
            <div class="setup-step-icon">1</div>
            <span>Check server requirements & PHP extensions</span>
        </div>
        <div class="setup-step">
            <div class="setup-step-icon">2</div>
            <span>Configure your database connection</span>
        </div>
        <div class="setup-step">
            <div class="setup-step-icon">3</div>
            <span>Set up environment & run migrations</span>
        </div>
        <div class="setup-step">
            <div class="setup-step-icon">4</div>
            <span>Create your admin account</span>
        </div>
        <div class="setup-step">
            <div class="setup-step-icon">5</div>
            <span>Start using your CRM</span>
        </div>
    </div>

    <div class="alert alert-warning">
        <span class="alert-icon">⚠</span>
        <span>
            Make sure you have created an <strong>empty MySQL database</strong> before proceeding.
            You'll need the database name, username, and password.
        </span>
    </div>

    <div class="btn-group" style="justify-content: flex-end;">
        <a href="/install/requirements" class="btn btn-primary">
            Let's Begin →
        </a>
    </div>

</x-install-layout>
