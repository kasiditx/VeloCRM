# VeloCRM

VeloCRM is a self-hosted CRM for small and medium sales teams. It is built with Laravel 11, Livewire 3, Blade, Tailwind CSS, Sanctum, Spatie Permission, Spatie Activitylog, Laravel Excel, and DomPDF.

The application is designed for commercial distribution and shared-hosting deployment. It includes a browser installer, English/Thai localization, CRM workflows, PDF documents, reporting, customer portal access, public share links, and REST API access.

## Highlights

- Lead management with list, form, import, and Kanban views
- Customer records with notes, attachments, invoices, proposals, and custom fields
- Invoice and proposal generation with PDF export and public share links
- Payment-ready invoice flow with webhook endpoint support
- Customer portal for invoices, proposals, and profile access
- Tasks, task board, calendar, dashboard, and reports
- Recurring invoices and overdue reminder scheduling
- CSV import/export for operational data
- Admin settings for branding, SMTP, regional preferences, API tokens, and backups
- Roles and permissions powered by Spatie Permission
- Audit trail powered by Spatie Activitylog
- REST API protected by Laravel Sanctum
- Thai tax ID and branch fields with invoice snapshot support
- English and Thai locale switching

## Tech Stack

| Area | Technology |
| --- | --- |
| Backend | Laravel 11, PHP 8.2+ |
| UI | Livewire 3, Blade, Tailwind CSS |
| Auth/API | Laravel Breeze, Sanctum |
| Roles | Spatie Laravel Permission |
| Audit trail | Spatie Laravel Activitylog |
| Documents | DomPDF |
| Imports/exports | Laravel Excel |
| Frontend build | Vite |
| Tests | PHPUnit |

## Server Requirements

- PHP `8.2` or `8.3`
- MySQL `8+` or compatible MariaDB
- Composer dependencies installed
- Node dependencies and built assets for development/build workflows
- Web server document root pointed to `public/`

Required PHP extensions:

- `bcmath`
- `ctype`
- `fileinfo`
- `gd` or `imagick`
- `json`
- `mbstring`
- `openssl`
- `pdo_mysql`
- `tokenizer`
- `xml`
- `zip`

Writable paths:

- `storage/`
- `bootstrap/cache/`
- `public/uploads/`

## Installation

1. Upload the project files to your server or hosting account.
2. Point the domain or subdomain document root to the `public/` directory.
3. Create an empty MySQL database and database user.
4. Open `/install` in the browser.
5. Complete the installer steps:
   - server requirements check
   - database connection
   - company, regional, and SMTP setup
   - admin account creation
   - finalization
6. Sign in with the admin account and review the admin settings.

Detailed buyer documentation is available in [`documentation/installation.html`](documentation/installation.html).

## First-Time Setup

After installation, review these areas from an admin account:

1. `Admin > Settings > Branding`
2. `Admin > Settings > SMTP`
3. `Admin > Settings > Regional`
4. `Admin > Users`
5. `Admin > Custom Fields`
6. `Admin > Activity Log`
7. `Reports`

## Local Development

Install dependencies:

```bash
composer install
npm install
```

Create an environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Start the development stack:

```bash
composer run dev
```

The `composer run dev` script starts Laravel, the queue listener, logs, and Vite together.

## Build

Compile frontend assets for production:

```bash
npm run build
```

## Quality Checks

Run the relevant checks before packaging or shipping a change:

```bash
./vendor/bin/pint --test
./vendor/bin/phpunit
npm run build
composer validate --strict
composer audit
```

Useful focused test examples:

```bash
./vendor/bin/phpunit tests/Feature/Api/RestApiTest.php
./vendor/bin/phpunit tests/Feature/CustomFieldsTest.php
./vendor/bin/phpunit tests/Feature/AuditTrailTest.php
./vendor/bin/phpunit tests/Feature/ThaiTaxFieldsTest.php
```

`package.json` currently defines `build`, `dev`, `pw`, and `pw:calendar`. It does not currently define `lint`, `typecheck`, or `test` scripts.

## Cron Jobs

VeloCRM uses Laravel Scheduler for recurring invoices and reminder workflows. Configure the scheduler to run every minute:

```bash
* * * * * php /path/to/velocrm/artisan schedule:run >> /dev/null 2>&1
```

More details are in [`documentation/cron.html`](documentation/cron.html).

## REST API

The REST API uses Laravel Sanctum tokens.

Available API areas:

- `POST /api/login`
- `/api/leads`
- `/api/customers`
- `/api/invoices`
- `/api/reports/summary`

See [`documentation/api.html`](documentation/api.html) for request and response details.

## Documentation

- [`documentation/index.html`](documentation/index.html) - documentation overview
- [`documentation/installation.html`](documentation/installation.html) - installation guide
- [`documentation/configuration.html`](documentation/configuration.html) - configuration guide
- [`documentation/api.html`](documentation/api.html) - REST API guide
- [`documentation/modules.html`](documentation/modules.html) - module overview
- [`documentation/cron.html`](documentation/cron.html) - scheduler setup
- [`documentation/customization.html`](documentation/customization.html) - customization notes
- [`documentation/faq.html`](documentation/faq.html) - frequently asked questions
- [`documentation/changelog.html`](documentation/changelog.html) - release notes

## Deployment Notes

- Only expose the `public/` directory to the web.
- Keep `APP_DEBUG=false` in production.
- Ensure `storage/`, `bootstrap/cache/`, and `public/uploads/` are writable.
- Configure SMTP before enabling email-heavy workflows.
- Configure the scheduler cron if recurring invoices or reminders are used.
- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` only after the production environment is finalized.
- Keep Composer platform compatibility aligned with the target PHP runtime.

## Security Notes

- Never commit `.env`, credentials, API keys, tokens, private certificates, database dumps, or generated backups.
- Keep the project root private; the web server should serve only from `public/`.
- Use least-privilege database credentials.
- Rotate API tokens when staff access changes.
- Review public share links and customer portal access before distributing invoices or proposals externally.
- Keep dependencies patched and run `composer audit` before release.

## Packaging Notes

- Build frontend assets with `npm run build`.
- Remove local uploads, logs, test databases, generated cache, and temporary files before distribution.
- Keep buyer-facing documentation under `documentation/`.
- Verify the installer flow on a clean database before packaging a release.

## License

This project is distributed according to the license terms provided with the product package.
