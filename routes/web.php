<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\ProposalController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Admin\ActivityLogIndex;
use App\Livewire\Admin\CustomFieldIndex;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Calendar\CalendarIndex;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Dashboard;
use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Invoices\InvoiceShow;
use App\Livewire\LeadKanban;
use App\Livewire\Leads\LeadForm;
use App\Livewire\Leads\LeadImport;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Leads\LeadShow;
use App\Livewire\Portal\InvoiceIndex as PortalInvoiceIndex;
use App\Livewire\Portal\InvoiceShow as PortalInvoiceShow;
use App\Livewire\Portal\Profile as PortalProfile;
use App\Livewire\Portal\ProposalShow as PortalProposalShow;
use App\Livewire\Proposals\ProposalForm;
use App\Livewire\Proposals\ProposalIndex;
use App\Livewire\Proposals\ProposalShow;
use App\Livewire\PublicShare\InvoiceShow as PublicInvoiceShow;
use App\Livewire\PublicShare\ProposalShow as PublicProposalShow;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Tasks\TaskBoard;
use App\Livewire\Tasks\TaskForm;
use App\Livewire\Tasks\TaskIndex;
use App\Models\Invoice;

/*
|--------------------------------------------------------------------------
| Installer Routes (no auth, no middleware — standalone)
|--------------------------------------------------------------------------
*/
Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'welcome'])->name('install.welcome');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/database', [InstallController::class, 'testDatabase'])->name('install.test-database');
    Route::get('/setup', [InstallController::class, 'setup'])->name('install.setup');
    Route::post('/setup', [InstallController::class, 'runSetup'])->name('install.run-setup');
    Route::get('/admin', [InstallController::class, 'admin'])->name('install.admin');
    Route::post('/admin', [InstallController::class, 'createAdmin'])->name('install.create-admin');
    Route::get('/complete', [InstallController::class, 'complete'])->name('install.complete');
    Route::post('/finalize', [InstallController::class, 'finalize'])->name('install.finalize');
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::post('locale', LocaleController::class)
    ->middleware('throttle:12,1')
    ->name('locale.switch');

Route::prefix('p')->name('public.')->middleware('throttle:60,1')->group(function () {
    Route::get('invoice/{token}', PublicInvoiceShow::class)->name('invoice.show');
    Route::get('invoice/{token}/pdf', [InvoiceController::class, 'generatePublicPdf'])->name('invoice.pdf');
    Route::get('invoice/{token}/pay', [PaymentController::class, 'checkout'])->name('invoice.pay');
    Route::get('proposal/{token}', PublicProposalShow::class)->name('proposal.show');
    Route::get('proposal/{token}/pdf', [ProposalController::class, 'generatePublicPdf'])->name('proposal.pdf');
});

Route::post('payments/webhook/{gateway}', [PaymentController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->name('payments.webhook');

Route::middleware(['auth', EnsureUserIsActive::class, 'verified'])->group(function () {
    Route::prefix('portal')->name('portal.')->middleware('portal')->group(function () {
        Route::get('invoices', PortalInvoiceIndex::class)->name('invoices.index');
        Route::get('invoices/{invoiceId}', PortalInvoiceShow::class)->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', function (int $invoice) {
            $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoice);

            return app(InvoiceController::class)->generatePdf($invoice);
        })->name('invoices.pdf');
        Route::get('proposals/{proposalId}', PortalProposalShow::class)->name('proposals.show');
        Route::get('profile', PortalProfile::class)->name('profile');
        Route::get('/', fn () => redirect()->route('portal.invoices.index'))->name('dashboard');
    });

    Route::middleware('role:Admin|Staff')->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');

        // Leads
        Route::get('leads', LeadIndex::class)->name('leads.index');
        Route::get('leads/kanban', LeadKanban::class)->name('leads.kanban');
        Route::get('leads/create', LeadForm::class)->name('leads.create');
        Route::middleware('role:Admin')->group(function () {
            Route::get('leads/import', LeadImport::class)->name('leads.import');
            Route::get('import/leads/template', [ExportController::class, 'leadImportTemplate'])->name('leads.import.template');
        });
        Route::get('leads/{leadId}', LeadShow::class)->name('leads.show');
        Route::get('leads/{leadId}/edit', LeadForm::class)->name('leads.edit');

        // Customers
        Route::get('customers', CustomerIndex::class)->name('customers.index');
        Route::get('customers/create', CustomerForm::class)->name('customers.create');
        Route::get('customers/{customerId}', CustomerShow::class)->name('customers.show');
        Route::get('customers/{customerId}/edit', CustomerForm::class)->name('customers.edit');

        // Invoices
        Route::get('invoices', InvoiceIndex::class)->name('invoices.index');
        Route::get('invoices/create', InvoiceForm::class)->name('invoices.create');
        Route::get('invoices/{invoiceId}', InvoiceShow::class)->name('invoices.show');
        Route::get('invoices/{invoiceId}/edit', InvoiceForm::class)->name('invoices.edit');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])
            ->name('invoices.pdf');

        // Proposals
        Route::get('proposals', ProposalIndex::class)->name('proposals.index');
        Route::get('proposals/create', ProposalForm::class)->name('proposals.create');
        Route::get('proposals/{proposalId}', ProposalShow::class)->name('proposals.show');
        Route::get('proposals/{proposalId}/edit', ProposalForm::class)->name('proposals.edit');
        Route::get('proposals/{proposal}/pdf', [ProposalController::class, 'generatePdf'])
            ->name('proposals.pdf');

        // Tasks
        Route::get('tasks', TaskIndex::class)->name('tasks.index');
        Route::get('tasks/board', TaskBoard::class)->name('tasks.board');
        Route::get('tasks/create', TaskForm::class)->name('tasks.create');
        Route::get('tasks/{taskId}/edit', TaskForm::class)->name('tasks.edit');

        // Calendar
        Route::get('calendar', CalendarIndex::class)->name('calendar.index');
        Route::get('reports', ReportIndex::class)->name('reports.index');

        // Admin-only routes
        Route::middleware('role:Admin')->group(function () {
            Route::get('admin/users', UserIndex::class)->name('admin.users.index');
            Route::get('admin/users/create', UserForm::class)->name('admin.users.create');
            Route::get('admin/users/{userId}/edit', UserForm::class)->name('admin.users.edit');
            Route::get('admin/custom-fields', CustomFieldIndex::class)->name('admin.custom-fields.index');
            Route::get('admin/activity-log', ActivityLogIndex::class)->name('admin.activity-log.index');

            // Admin Settings
            Route::get('admin/settings', Settings::class)->name('admin.settings');

            // Exports
            Route::get('export/leads', [ExportController::class, 'leads'])->name('export.leads');
            Route::get('export/customers', [ExportController::class, 'customers'])->name('export.customers');
        });
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth', EnsureUserIsActive::class])
    ->name('profile');

require __DIR__.'/auth.php';
