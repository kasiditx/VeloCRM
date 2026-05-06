# Cron Jobs

VeloCRM schedules background jobs through Laravel scheduler.

## Required Cron Entry

```bash
* * * * * php /path/to/velocrm/artisan schedule:run >> /dev/null 2>&1
```

## Features That Depend on Scheduler

- overdue invoice reminders
- recurring invoice generation

## cPanel Note

If you use cPanel, create a cron job with the command above and adjust the PHP binary path if required by your host.
