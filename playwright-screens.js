import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    const outputDir = path.join(process.cwd(), 'audit_screenshots');
    if (!fs.existsSync(outputDir)){
        fs.mkdirSync(outputDir);
    }

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'admin@velocrm.com');
    await page.fill('input[name="password"]', 'admin1234');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    console.log('Logged in successfully!');

    const pagesToScreenshot = [
        { url: '/dashboard', name: '01_dashboard' },
        { url: '/leads', name: '02_leads_index' },
        { url: '/leads/kanban', name: '03_leads_kanban' },
        { url: '/leads/create', name: '04_leads_create' },
        { url: '/customers', name: '05_customers_index' },
        { url: '/customers/create', name: '06_customers_create' },
        { url: '/invoices', name: '07_invoices_index' },
        { url: '/invoices/create', name: '08_invoices_create' },
        { url: '/proposals', name: '09_proposals_index' },
        { url: '/proposals/create', name: '10_proposals_create' },
        { url: '/tasks', name: '11_tasks_index' },
        { url: '/tasks/board', name: '12_tasks_board' },
        { url: '/tasks/create', name: '13_tasks_create' },
        { url: '/calendar', name: '14_calendar' },
        { url: '/reports', name: '15_reports' },
        { url: '/admin/users', name: '16_admin_users' },
        { url: '/admin/settings', name: '17_admin_settings' },
    ];

    for (const p of pagesToScreenshot) {
        console.log(`Capturing ${p.name}...`);
        await page.goto(`http://127.0.0.1:8000${p.url}`);
        // Wait for Livewire or Alpine transitions
        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(outputDir, `${p.name}.png`), fullPage: true });
    }

    // Toggle Dark Mode
    console.log('Toggling dark mode...');
    // Trying to find dark mode toggle - usually a button with class containing 'dark' or 'theme' or icon
    // We'll execute JS to toggle dark class on html element directly for guaranteed result in audit
    await page.evaluate(() => document.documentElement.classList.add('dark'));

    const darkPagesToScreenshot = [
        { url: '/dashboard', name: '18_dashboard_dark' },
        { url: '/leads', name: '19_leads_index_dark' },
    ];

    for (const p of darkPagesToScreenshot) {
        console.log(`Capturing ${p.name}...`);
        await page.goto(`http://127.0.0.1:8000${p.url}`);
        await page.evaluate(() => document.documentElement.classList.add('dark'));
        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(outputDir, `${p.name}.png`), fullPage: true });
    }

    await browser.close();
    console.log('Audit screenshots complete!');
})();
