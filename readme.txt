VeloCRM - Laravel 11 CRM with Invoicing, Proposals, Reports and Thai-first setup

REQUIREMENTS
- PHP 8.2+
- MySQL 8 or compatible MariaDB
- Composer dependencies installed in the package
- Writable storage/, bootstrap/cache/, public/uploads/

INSTALLATION
1. Upload files to your hosting account.
2. Point the web root to public/.
3. Create an empty database.
4. Open /install in the browser.
5. Complete requirements, database, company, SMTP, regional, and admin setup.
6. Configure cron:
   * * * * * php /path/to/velocrm/artisan schedule:run >> /dev/null 2>&1

DOCUMENTATION
Open documentation/index.html for the full buyer guide.

DEMO CREDENTIALS
Reviewer demo credentials are listed in demo-credentials.txt.

SUPPORT
Support covers installation guidance, configuration guidance, and bug clarification for the stock package. Custom feature development and server administration are not included.
