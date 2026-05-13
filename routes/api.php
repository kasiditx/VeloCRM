<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\ReportSummaryController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');

Route::middleware(['auth:sanctum', EnsureUserIsActive::class, 'role:Admin|Staff'])->group(function (): void {
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('reports/summary', ReportSummaryController::class);
});
