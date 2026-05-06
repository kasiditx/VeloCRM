<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProposalController;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Admin\UserForm;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\LeadKanban;
use App\Livewire\Leads\LeadForm;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Leads\LeadImport;
use App\Livewire\Leads\LeadShow;
use App\Livewire\Invoices\InvoiceIndex;
use App\Livewire\Invoices\InvoiceForm;
use App\Livewire\Invoices\InvoiceShow;
use App\Livewire\Proposals\ProposalIndex;
use App\Livewire\Proposals\ProposalForm;
use App\Livewire\Proposals\ProposalShow;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Tasks\TaskIndex;
use App\Livewire\Tasks\TaskBoard;
use App\Livewire\Tasks\TaskForm;
use App\Livewire\Calendar\CalendarIndex;

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

Route::get('/', function() {
    return redirect()->route('dashboard');
});

Route::post('locale', LocaleController::class)->name('locale.switch');

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsActive::class, 'verified'])->group(function () {
    Route::get('dashboard', \App\Livewire\Dashboard::class)->name('dashboard');

    // Leads
    Route::get('leads', LeadIndex::class)->name('leads.index');
    Route::get('leads/kanban', LeadKanban::class)->name('leads.kanban');
    Route::get('leads/create', LeadForm::class)->name('leads.create');
    Route::middleware('role:Admin')->group(function () {
        Route::get('leads/import', LeadImport::class)->name('leads.import');
        Route::get('import/leads/template', [App\Http\Controllers\ExportController::class, 'leadImportTemplate'])->name('leads.import.template');
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

        // Admin Settings
        Route::get('admin/settings', \App\Livewire\Admin\Settings::class)->name('admin.settings');

        // Exports
        Route::get('export/leads', [App\Http\Controllers\ExportController::class, 'leads'])->name('export.leads');
        Route::get('export/customers', [App\Http\Controllers\ExportController::class, 'customers'])->name('export.customers');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
