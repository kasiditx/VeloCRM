# Upgrade Guide

## Before Upgrading

1. Back up the database.
2. Back up the full project directory.
3. Keep a copy of `.env`.
4. Note any custom code changes.

## Upgrade Process

1. Replace application files with the new package version.
2. Preserve `.env`, `storage/`, and `public/uploads/`.
3. Run database migrations:

```bash
php artisan migrate --force
```

4. Clear caches if shell access is available:

```bash
php artisan optimize:clear
```

5. Verify dashboard, invoices, reports, and recurring billing.

## After Upgrading

- check cron is still active
- open admin settings and confirm branding and SMTP values
- test PDF generation
- test lead import if you use CSV onboarding
