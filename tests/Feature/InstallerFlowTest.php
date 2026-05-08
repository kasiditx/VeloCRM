<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class InstallerFlowTest extends TestCase
{
    private ?string $backupPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $installedPath = storage_path('installed');

        if (file_exists($installedPath)) {
            $this->backupPath = $installedPath.'.bak-testing';
            rename($installedPath, $this->backupPath);
        }
    }

    protected function tearDown(): void
    {
        $installedPath = storage_path('installed');

        if ($this->backupPath && file_exists($this->backupPath)) {
            if (file_exists($installedPath)) {
                unlink($installedPath);
            }

            rename($this->backupPath, $installedPath);
        }

        parent::tearDown();
    }

    public function test_setup_page_renders_onboarding_fields(): void
    {
        $response = $this->withSession([
            'install_db' => [
                'db_host' => '127.0.0.1',
                'db_port' => '3306',
                'db_database' => 'velocrm',
                'db_username' => 'root',
                'db_password' => '',
            ],
        ])->get('/install/setup');

        $response->assertOk();
        $response->assertSee('Company Name');
        $response->assertSee('Company Address');
        $response->assertSee('Default Language');
        $response->assertSee('SMTP (Optional)');
    }

    public function test_complete_page_renders_install_summary(): void
    {
        $response = $this->withSession([
            'install_summary' => [
                'company_name' => 'Acme CRM',
                'company_address' => 'Bangkok',
                'site_title' => 'Acme CRM',
                'app_locale' => 'th',
                'currency_symbol' => '฿',
                'date_format' => 'd/m/Y',
                'smtp_configured' => true,
            ],
        ])->get('/install/complete');

        $response->assertOk();
        $response->assertSee('Acme CRM');
        $response->assertSee('Bangkok');
        $response->assertSee('SMTP saved during installation');
        $response->assertSee('Post-install checklist');
    }
}
