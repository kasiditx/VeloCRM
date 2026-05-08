<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\InstallerSeeder;
use Database\Seeders\LeadSeeder;
use Database\Seeders\Phase3Seeder;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InstallController extends Controller
{
    /**
     * Step 1: Welcome page
     */
    public function welcome(): View
    {
        return view('install.welcome');
    }

    /**
     * Step 2: Server requirements check
     */
    public function requirements(): View
    {
        $phpVersion = PHP_VERSION;
        $phpVersionOk = version_compare($phpVersion, '8.2.0', '>=');

        $extensions = [
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'ctype' => extension_loaded('ctype'),
            'json' => extension_loaded('json'),
            'bcmath' => extension_loaded('bcmath'),
            'fileinfo' => extension_loaded('fileinfo'),
            'gd' => extension_loaded('gd') || extension_loaded('imagick'),
            'zip' => extension_loaded('zip'),
        ];

        $folders = [
            'storage/framework' => is_writable(storage_path('framework')),
            'storage/framework/cache' => is_writable(storage_path('framework/cache')),
            'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
            'storage/framework/views' => is_writable(storage_path('framework/views')),
            'storage/logs' => is_writable(storage_path('logs')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            'public/uploads' => $this->checkOrCreateUploads(),
        ];

        $allExtensionsOk = ! in_array(false, $extensions, true);
        $allFoldersOk = ! in_array(false, $folders, true);
        $allOk = $phpVersionOk && $allExtensionsOk && $allFoldersOk;

        return view('install.requirements', compact(
            'phpVersion',
            'phpVersionOk',
            'extensions',
            'folders',
            'allOk',
        ));
    }

    /**
     * Step 3: Database configuration form
     */
    public function database(): View
    {
        return view('install.database');
    }

    /**
     * Step 3b: Test database connection via AJAX-style form post
     */
    public function testDatabase(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'db_host' => 'required|string|max:191',
            'db_port' => 'required|string|max:10',
            'db_database' => 'required|string|max:191',
            'db_username' => 'required|string|max:191',
            'db_password' => 'nullable|string|max:191',
        ])->validate();

        try {
            $pdo = new \PDO(
                "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}",
                $validated['db_username'],
                $validated['db_password'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo = null; // close

            // Store in session for the setup step
            session([
                'install_db' => $validated,
            ]);

            return redirect('/install/setup')->with('success', 'Database connection successful!');
        } catch (\PDOException $e) {
            return redirect('/install/database')
                ->withInput()
                ->withErrors(['db_connection' => 'Connection failed: '.$e->getMessage()]);
        }
    }

    /**
     * Step 4: Show environment setup / run migrations
     */
    public function setup(): View|RedirectResponse
    {
        $dbConfig = session('install_db');

        if (! $dbConfig) {
            return redirect('/install/database')
                ->withErrors(['db_connection' => 'Please configure database first.']);
        }

        return view('install.setup', [
            'dbConfig' => $dbConfig,
            'defaults' => [
                'app_url' => old('app_url', url('/')),
                'company_name' => old('company_name', velocrm_app_name()),
                'company_address' => old('company_address', ''),
                'site_title' => old('site_title', velocrm_app_name()),
                'app_locale' => old('app_locale', 'en'),
                'app_timezone' => old('app_timezone', 'Asia/Bangkok'),
                'currency_code' => old('currency_code', 'USD'),
                'currency_symbol' => old('currency_symbol', '$'),
                'date_format' => old('date_format', 'd/m/Y'),
                'mail_host' => old('mail_host', ''),
                'mail_port' => old('mail_port', '587'),
                'mail_encryption' => old('mail_encryption', 'tls'),
                'mail_username' => old('mail_username', ''),
                'mail_password' => old('mail_password', ''),
                'mail_from_address' => old('mail_from_address', ''),
                'mail_from_name' => old('mail_from_name', velocrm_app_name()),
            ],
        ]);
    }

    /**
     * Step 4b: Execute the setup (generate .env, migrate, seed)
     */
    public function runSetup(Request $request): RedirectResponse
    {
        $dbConfig = session('install_db');

        if (! $dbConfig) {
            return redirect('/install/database')
                ->withErrors(['db_connection' => 'Please configure database first.']);
        }

        $validated = Validator::make($request->all(), [
            'purchase_code' => 'required|string|max:255',
            'app_url' => 'required|url|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'site_title' => 'required|string|max:255',
            'app_locale' => 'required|in:en,th',
            'app_timezone' => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:20',
            'mail_encryption' => 'nullable|string|max:20',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'install_demo_data' => 'nullable|boolean',
        ])->validate();

        try {
            // 1. Generate .env file
            $this->generateEnvFile($dbConfig, $validated);

            // 2. Clear all caches since config changed
            Artisan::call('config:clear');

            // 3. Force Laravel to reload the .env
            $app = app();
            $app->bootstrapWith([
                LoadEnvironmentVariables::class,
                LoadConfiguration::class,
            ]);

            // 4. Reconfigure database connection at runtime
            config([
                'database.connections.mysql.host' => $dbConfig['db_host'],
                'database.connections.mysql.port' => $dbConfig['db_port'],
                'database.connections.mysql.database' => $dbConfig['db_database'],
                'database.connections.mysql.username' => $dbConfig['db_username'],
                'database.connections.mysql.password' => $dbConfig['db_password'] ?? '',
            ]);
            config(['database.default' => 'mysql']);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 5. Generate APP_KEY
            Artisan::call('key:generate', ['--force' => true]);

            // 6. Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // 7. Run installer seeder (roles only)
            Artisan::call('db:seed', [
                '--class' => InstallerSeeder::class,
                '--force' => true,
            ]);

            if (! empty($validated['install_demo_data'])) {
                if (class_exists(LeadSeeder::class)) {
                    Artisan::call('db:seed', ['--class' => LeadSeeder::class, '--force' => true]);
                }
                if (class_exists(Phase3Seeder::class)) {
                    Artisan::call('db:seed', ['--class' => Phase3Seeder::class, '--force' => true]);
                }
            }

            Setting::set('company_name', $validated['company_name']);
            Setting::set('company_address', $validated['company_address'] ?? '');
            Setting::set('site_title', $validated['site_title']);
            Setting::set('currency_code', $validated['currency_code']);
            Setting::set('currency_symbol', $validated['currency_symbol']);
            Setting::set('date_format', $validated['date_format']);
            Setting::set('mail_host', $validated['mail_host'] ?? '');
            Setting::set('mail_port', $validated['mail_port'] ?? '');
            Setting::set('mail_username', $validated['mail_username'] ?? '');
            if (! empty($validated['mail_password'])) {
                Setting::set('mail_password', $validated['mail_password'], true);
            }
            Setting::set('mail_encryption', $validated['mail_encryption'] ?? '');
            Setting::set('mail_from_address', $validated['mail_from_address'] ?? '');
            Setting::set('mail_from_name', $validated['mail_from_name'] ?? $validated['company_name']);
            Setting::set('envato_purchase_code', $validated['purchase_code']);

            session([
                'install_summary' => [
                    'company_name' => $validated['company_name'],
                    'company_address' => $validated['company_address'] ?? null,
                    'site_title' => $validated['site_title'],
                    'app_url' => $validated['app_url'],
                    'app_locale' => $validated['app_locale'],
                    'app_timezone' => $validated['app_timezone'],
                    'currency_code' => $validated['currency_code'],
                    'currency_symbol' => $validated['currency_symbol'],
                    'date_format' => $validated['date_format'],
                    'smtp_configured' => ! empty($validated['mail_host']) && ! empty($validated['mail_from_address']),
                    'mail_from_address' => $validated['mail_from_address'] ?? null,
                ],
            ]);

            // 8. Try storage:link (may fail on shared hosting — that's OK)
            try {
                Artisan::call('storage:link');
            } catch (\Exception) {
                // Ignore — shared hosting may disable symlink()
            }

            // Clear session db config after successful setup
            session()->forget('install_db');

            return redirect('/install/admin')->with('success', 'Environment configured and database migrated successfully!');
        } catch (\Exception $e) {
            return redirect('/install/setup')
                ->withErrors(['setup' => 'Setup failed: '.$e->getMessage()]);
        }
    }

    /**
     * Step 5: Admin account creation form
     */
    public function admin(): View
    {
        return view('install.admin');
    }

    /**
     * Step 5b: Create admin user
     */
    public function createAdmin(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'password' => 'required|string|min:8|confirmed',
        ])->validate();

        try {
            // Reconfigure DB from .env in case session was cleared
            $this->ensureDatabaseConnection();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('Admin');

            return redirect('/install/complete')->with('success', 'Admin account created successfully!');
        } catch (\Exception $e) {
            return redirect('/install/admin')
                ->withInput()
                ->withErrors(['admin' => 'Failed to create admin: '.$e->getMessage()]);
        }
    }

    /**
     * Step 6: Installation complete
     */
    public function complete(): View|RedirectResponse
    {
        return view('install.complete', [
            'summary' => session('install_summary', []),
        ]);
    }

    /**
     * Finalize: Create the installed flag
     */
    public function finalize(): RedirectResponse
    {
        // Create the installed flag file
        file_put_contents(storage_path('installed'), json_encode([
            'installed_at' => now()->toIso8601String(),
            'version' => '1.0.0',
            'company_name' => Setting::get('company_name', velocrm_app_name()),
            'locale' => config('app.locale'),
            'currency_code' => Setting::get('currency_code', 'USD'),
            'smtp_configured' => (bool) Setting::get('mail_host'),
        ]));

        session()->forget('install_summary');

        return redirect('/login');
    }

    /**
     * Generate the .env file from template
     */
    private function generateEnvFile(array $dbConfig, array $installConfig): void
    {
        $appName = '"'.addslashes($installConfig['site_title']).'"';
        $appUrl = $installConfig['app_url'];
        $appTimezone = $installConfig['app_timezone'];
        $appLocale = $installConfig['app_locale'];
        $mailHost = $installConfig['mail_host'] !== '' ? $installConfig['mail_host'] : '127.0.0.1';
        $mailPort = $installConfig['mail_port'] !== '' ? $installConfig['mail_port'] : '587';
        $mailUsername = $installConfig['mail_username'] !== '' ? $installConfig['mail_username'] : 'null';
        $mailPassword = $installConfig['mail_password'] !== '' ? $installConfig['mail_password'] : 'null';
        $mailEncryption = $installConfig['mail_encryption'] !== '' ? $installConfig['mail_encryption'] : 'null';
        $mailFromAddress = '"'.addslashes($installConfig['mail_from_address'] !== '' ? $installConfig['mail_from_address'] : 'noreply@example.com').'"';
        $mailFromName = '"'.addslashes($installConfig['mail_from_name'] !== '' ? $installConfig['mail_from_name'] : $installConfig['company_name']).'"';

        $envContent = <<<ENV
APP_NAME={$appName}
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE={$appTimezone}
APP_URL={$appUrl}

APP_LOCALE={$appLocale}
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$dbConfig['db_host']}
DB_PORT={$dbConfig['db_port']}
DB_DATABASE={$dbConfig['db_database']}
DB_USERNAME={$dbConfig['db_username']}
DB_PASSWORD={$dbConfig['db_password']}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MAIL_MAILER=smtp
MAIL_HOST={$mailHost}
MAIL_PORT={$mailPort}
MAIL_USERNAME={$mailUsername}
MAIL_PASSWORD={$mailPassword}
MAIL_ENCRYPTION={$mailEncryption}
MAIL_FROM_ADDRESS={$mailFromAddress}
MAIL_FROM_NAME={$mailFromName}
ENV;

        file_put_contents(base_path('.env'), $envContent);
    }

    /**
     * Check or create the public/uploads directory
     */
    private function checkOrCreateUploads(): bool
    {
        $uploadsPath = public_path('uploads');

        if (! is_dir($uploadsPath)) {
            @mkdir($uploadsPath, 0755, true);
        }

        return is_writable($uploadsPath);
    }

    /**
     * Ensure database connection is properly configured from .env
     */
    private function ensureDatabaseConnection(): void
    {
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $lines = explode("\n", $envContent);

            $dbConfig = [];
            foreach ($lines as $line) {
                if (str_starts_with($line, 'DB_HOST=')) {
                    $dbConfig['host'] = substr($line, 8);
                } elseif (str_starts_with($line, 'DB_PORT=')) {
                    $dbConfig['port'] = substr($line, 8);
                } elseif (str_starts_with($line, 'DB_DATABASE=')) {
                    $dbConfig['database'] = substr($line, 12);
                } elseif (str_starts_with($line, 'DB_USERNAME=')) {
                    $dbConfig['username'] = substr($line, 12);
                } elseif (str_starts_with($line, 'DB_PASSWORD=')) {
                    $dbConfig['password'] = substr($line, 12);
                }
            }

            if (! empty($dbConfig)) {
                config([
                    'database.default' => 'mysql',
                    'database.connections.mysql.host' => $dbConfig['host'] ?? '127.0.0.1',
                    'database.connections.mysql.port' => $dbConfig['port'] ?? '3306',
                    'database.connections.mysql.database' => $dbConfig['database'] ?? '',
                    'database.connections.mysql.username' => $dbConfig['username'] ?? '',
                    'database.connections.mysql.password' => $dbConfig['password'] ?? '',
                ]);
                DB::purge('mysql');
                DB::reconnect('mysql');
            }
        }
    }
}
