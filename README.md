# VeloCRM

VeloCRM is a Laravel 11 CRM built for commercial distribution and shared-hosting deployment. It includes lead and customer management, invoices, proposals, tasks, calendar, reports, PDF export, recurring invoices, installer flow, localization, and admin settings for branding, SMTP, and regional preferences.

## Product Scope

- Lead pipeline with list and Kanban views
- Customer management with linked invoices and proposals
- Invoice and proposal generation with PDF export
- Tasks, calendar, dashboard, and reports
- CSV export and lead CSV import
- Admin user management with active/inactive accounts
- Notes, attachments, notifications, soft deletes, and recurring invoices
- English / Thai locale switching
- Shared-hosting friendly installer

## Server Requirements

- PHP `8.2+`
- MySQL `8+` or compatible MariaDB
- Composer dependencies already installed in the package
- Writable directories:
  - `storage/`
  - `bootstrap/cache/`
  - `public/uploads/`
- Required PHP extensions:
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

## Installation

1. Upload the project files to your hosting account or server.
2. Point your domain or subdomain to the `public/` directory.
3. Create an empty MySQL database and database user.
4. Open `/install` in the browser.
5. Complete the installer:
   - requirements check
   - database connection
   - company / regional / SMTP onboarding
   - admin account creation
6. Finish the installer and sign in with the admin account.

Detailed installation documentation is available in [docs/installation.md](/Users/kasidit/Documents/VeloCRM/docs/installation.md).

## First-Time Setup After Install

After installation, review these areas from the admin account:

1. `Admin > Settings > Branding`
2. `Admin > Settings > SMTP`
3. `Admin > Settings > Regional`
4. `Admin > Users`
5. `Reports`

Quick-start guidance is available in [docs/quick-start.md](/Users/kasidit/Documents/VeloCRM/docs/quick-start.md).

## Cron Jobs

VeloCRM uses Laravel scheduler for recurring invoices and overdue reminders. Configure a cron job to run every minute:

```bash
* * * * * php /path/to/velocrm/artisan schedule:run >> /dev/null 2>&1
```

Cron setup notes are in [docs/cron-jobs.md](/Users/kasidit/Documents/VeloCRM/docs/cron-jobs.md).

## Documentation Pack

- [docs/installation.md](/Users/kasidit/Documents/VeloCRM/docs/installation.md)
- [docs/quick-start.md](/Users/kasidit/Documents/VeloCRM/docs/quick-start.md)
- [docs/smtp-setup.md](/Users/kasidit/Documents/VeloCRM/docs/smtp-setup.md)
- [docs/customization.md](/Users/kasidit/Documents/VeloCRM/docs/customization.md)
- [docs/upgrade-guide.md](/Users/kasidit/Documents/VeloCRM/docs/upgrade-guide.md)
- [docs/cron-jobs.md](/Users/kasidit/Documents/VeloCRM/docs/cron-jobs.md)
- [docs/faq.md](/Users/kasidit/Documents/VeloCRM/docs/faq.md)

## Deployment Notes

- `public/uploads/` stores branding assets and attachments.
- `storage:link` is attempted by the installer, but the app tolerates shared hosting environments where symlinks are unavailable.
- SMTP settings can be stored from the installer or later from admin settings.
- Localization supports `en` and `th`.

## Support and Customization

- Use the admin settings pages before editing code.
- Customization guidance is in [docs/customization.md](/Users/kasidit/Documents/VeloCRM/docs/customization.md).
- Upgrade process guidance is in [docs/upgrade-guide.md](/Users/kasidit/Documents/VeloCRM/docs/upgrade-guide.md).

## Security and Packaging Notes

- Remove any unused demo data before distributing a production instance.
- Keep `APP_DEBUG=false` in production.
- Protect the project root and only expose `public/` to the web.
- Do not skip the installer finalization step; it creates the installation flag and prevents rerunning the setup flow.
