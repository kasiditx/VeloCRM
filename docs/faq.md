# FAQ

## The installer says a folder is not writable

Set write permissions for:

- `storage/`
- `bootstrap/cache/`
- `public/uploads/`

## The installer cannot connect to the database

- confirm the database exists
- confirm the username and password
- confirm the host and port
- confirm remote database access is allowed if the DB is on another server

## The app sends no email

- verify SMTP settings
- test with the SMTP test button in admin settings
- confirm your hosting provider allows outbound SMTP

## Recurring invoices are not generating

- confirm the invoice is marked recurring
- confirm `next_recurring_date` is due
- confirm the scheduler cron is active

## Overdue reminders are not being sent

- confirm the scheduler cron is active
- confirm invoices are past due and not paid
- confirm SMTP is configured correctly
