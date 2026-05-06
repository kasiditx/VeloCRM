# Installation Guide

## Before You Start

- Upload the package to your server.
- Point the web root to `public/`.
- Create an empty MySQL database and user.
- Confirm PHP `8.2+` and the required extensions are available.

## Browser Installer

1. Open `https://your-domain.com/install`.
2. Run the requirements check.
3. Enter database credentials and test the connection.
4. Complete the onboarding form:
   - application URL
   - company name
   - site title
   - locale and timezone
   - currency code and symbol
   - date format
   - optional SMTP settings
5. Run the setup step.
6. Create the admin account.
7. Complete the installer and sign in.

## What the Installer Does

- writes `.env`
- generates `APP_KEY`
- runs migrations
- seeds roles and permissions
- attempts `storage:link`
- saves onboarding settings to the database
- creates the admin account

## Shared Hosting Notes

- If `storage:link` is blocked by hosting policy, uploads can still work through the `public/uploads/` disk.
- Make sure `storage/`, `bootstrap/cache/`, and `public/uploads/` are writable.

## After Install

- Set the cron job for Laravel scheduler.
- Test SMTP.
- Upload logo and favicon.
- Review recurring invoice setup if you use billing automation.
