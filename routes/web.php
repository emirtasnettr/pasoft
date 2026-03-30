<?php

use App\Http\Controllers\Admin\DemoRequestController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\SystemUserController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\CompanyPolicyTypeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DefinitionsController;
use App\Http\Controllers\DemoRequestPageController;
use App\Http\Controllers\Install\InstallWizardController;
use App\Http\Controllers\InsuranceCompanyController;
use App\Http\Controllers\InsurancePolicyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PolicyTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('install.open')->prefix('install')->group(function () {
    Route::get('/', [InstallWizardController::class, 'welcome'])->name('install.welcome');
    Route::get('/database', [InstallWizardController::class, 'databaseForm'])->name('install.database');
    Route::post('/database', [InstallWizardController::class, 'databaseStore'])
        ->middleware('throttle:12,1')
        ->name('install.database.store');
    Route::get('/admin', [InstallWizardController::class, 'adminForm'])->name('install.admin');
    Route::post('/admin', [InstallWizardController::class, 'adminStore'])
        ->middleware('throttle:6,1')
        ->name('install.admin.store');
});

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/demo', [DemoRequestPageController::class, 'create'])->name('demo.request');
Route::get('/kullanim-kilavuzu', fn () => Inertia::render('UserGuide'))->name('guide');
Route::post('/demo', [DemoRequestPageController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('demo.request.store');

Route::middleware(['auth', 'verified', 'system.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.settings.edit'))->name('home');
    Route::get('settings', [SystemSettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [SystemSettingsController::class, 'update'])->name('settings.update');
    Route::get('demo-requests', [DemoRequestController::class, 'index'])->name('demo-requests.index');
    Route::resource('users', SystemUserController::class)->except(['show']);
});

Route::middleware(['auth', 'verified', 'crm.access'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('customers/{customer}/documents', [CustomerController::class, 'storeDocument'])->name('customers.documents.store');
    Route::delete('customer-documents/{document}', [CustomerController::class, 'destroyDocument'])->name('customers.documents.destroy');
    Route::post('customers/{customer}/notes', [CustomerController::class, 'storeNote'])->name('customers.notes.store');
    Route::resource('customers', CustomerController::class);

    Route::get('leads/kanban', [LeadController::class, 'kanban'])->name('leads.kanban');
    Route::post('leads/{lead}/notes', [LeadController::class, 'storeNote'])->name('leads.notes.store');
    Route::post('leads/{lead}/touch', [LeadController::class, 'touch'])->name('leads.touch');
    Route::resource('leads', LeadController::class)->except(['show', 'create', 'edit']);
    Route::patch('leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.stage');

    Route::post('policies/{policy}/installments/plan', [InsurancePolicyController::class, 'storeInstallmentPlan'])->name('policies.installments.plan');
    Route::post('policies/{policy}/notes', [InsurancePolicyController::class, 'storeNote'])->name('policies.notes.store');
    Route::post('policies/{policy}/documents', [InsurancePolicyController::class, 'storeDocument'])->name('policies.documents.store');
    Route::delete('policy-documents/{document}', [InsurancePolicyController::class, 'destroyDocument'])->name('policies.documents.destroy');
    Route::patch('policies/{policy}/renewal', [InsurancePolicyController::class, 'updateRenewal'])->name('policies.renewal.update');
    Route::post('policies/{policy}/renewal/start', [InsurancePolicyController::class, 'startRenewal'])->name('policies.renewal.start');
    Route::post('policies/{policy}/reminder', [InsurancePolicyController::class, 'sendRenewalReminder'])->name('policies.reminder');
    Route::resource('policies', InsurancePolicyController::class);
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::post('claims/{claim}/notes', [ClaimController::class, 'storeNote'])->name('claims.notes.store');
    Route::post('claims/{claim}/documents', [ClaimController::class, 'storeDocument'])->name('claims.documents.store');
    Route::delete('claim-documents/{document}', [ClaimController::class, 'destroyDocument'])->name('claims.documents.destroy');
    Route::patch('claims/{claim}/status', [ClaimController::class, 'updateStatus'])->name('claims.status');
    Route::resource('claims', ClaimController::class);

    Route::get('tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
    Route::post('tasks/bulk', [TaskController::class, 'bulk'])->name('tasks.bulk');
    Route::post('tasks/{task}/notes', [TaskController::class, 'storeNote'])->name('tasks.notes.store');
    Route::post('tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');
    Route::delete('task-attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('definitions', [DefinitionsController::class, 'index'])->name('definitions.index');
    Route::post('company-policy-types', [CompanyPolicyTypeController::class, 'store'])->name('company-policy-types.store');
    Route::patch('company-policy-types/{company_policy_type}', [CompanyPolicyTypeController::class, 'update'])->name('company-policy-types.update');
    Route::delete('company-policy-types/{company_policy_type}', [CompanyPolicyTypeController::class, 'destroy'])->name('company-policy-types.destroy');
    Route::resource('insurance-companies', InsuranceCompanyController::class);
    Route::resource('policy-types', PolicyTypeController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
