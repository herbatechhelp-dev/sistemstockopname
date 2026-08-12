<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UomController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecountController;
use App\Http\Controllers\TeamProgressController;
use App\Http\Controllers\Superadmin;

// Public
Route::get('/', fn() => redirect('/login'));

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Superadmin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [Superadmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
    Route::get('/users', [Superadmin\UserManagementController::class, 'index'])->name('superadmin.users.index');
    Route::get('/users/create', [Superadmin\UserManagementController::class, 'create'])->name('superadmin.users.create');
    Route::post('/users', [Superadmin\UserManagementController::class, 'store'])->name('superadmin.users.store');
    Route::get('/users/{user}/edit', [Superadmin\UserManagementController::class, 'edit'])->name('superadmin.users.edit');
    Route::put('/users/{user}', [Superadmin\UserManagementController::class, 'update'])->name('superadmin.users.update');
    Route::post('/users/{user}/toggle-active', [Superadmin\UserManagementController::class, 'toggleActive'])->name('superadmin.users.toggle');
    Route::post('/users/{user}/reset-password', [Superadmin\UserManagementController::class, 'resetPassword'])->name('superadmin.users.reset-password');

    Route::get('/sessions', [Superadmin\SessionController::class, 'index'])->name('superadmin.sessions.index');
    Route::get('/sessions/{session}', [Superadmin\SessionController::class, 'show'])->name('superadmin.sessions.show');
    Route::post('/sessions/{session}/force-close', [Superadmin\SessionController::class, 'forceClose'])->name('superadmin.sessions.force-close');

    Route::get('/audit-logs', [Superadmin\AuditLogController::class, 'index'])->name('superadmin.audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [Superadmin\AuditLogController::class, 'show'])->name('superadmin.audit-logs.show');

    Route::get('/settings', [Superadmin\SettingsController::class, 'index'])->name('superadmin.settings.index');
    Route::put('/settings', [Superadmin\SettingsController::class, 'update'])->name('superadmin.settings.update');
});

// Admin & Superadmin Routes
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{entry}', [DashboardController::class, 'show'])->name('dashboard.show');

    // Master Data
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/admin/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/admin/uoms', [UomController::class, 'index'])->name('uoms.index');
    Route::get('/admin/uoms/create', [UomController::class, 'create'])->name('uoms.create');
    Route::post('/admin/uoms', [UomController::class, 'store'])->name('uoms.store');
    Route::get('/admin/uoms/{uom}/edit', [UomController::class, 'edit'])->name('uoms.edit');
    Route::put('/admin/uoms/{uom}', [UomController::class, 'update'])->name('uoms.update');
    Route::delete('/admin/uoms/{uom}', [UomController::class, 'destroy'])->name('uoms.destroy');

    Route::get('/admin/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/admin/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/admin/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/admin/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/admin/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/admin/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/admin/items/import', [ItemController::class, 'showImport'])->name('items.import');
    Route::post('/admin/items/import', [ItemController::class, 'import'])->name('items.import.process');
    Route::get('/admin/items/template', [ItemController::class, 'downloadTemplate'])->name('items.template');

    Route::get('/admin/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/admin/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/admin/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/admin/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/admin/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/admin/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('/admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // SO Sessions
    Route::get('/admin/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('/admin/sessions/create', [SessionController::class, 'create'])->name('sessions.create');
    Route::post('/admin/sessions', [SessionController::class, 'store'])->name('sessions.store');
    Route::get('/admin/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
    Route::post('/admin/sessions/{session}/start', [SessionController::class, 'start'])->name('sessions.start');
    Route::post('/admin/sessions/{session}/complete', [SessionController::class, 'complete'])->name('sessions.complete');
    Route::post('/admin/sessions/{session}/close', [SessionController::class, 'close'])->name('sessions.close');
    Route::post('/admin/sessions/{session}/teams', [SessionController::class, 'addTeam'])->name('sessions.add-team');
    Route::post('/admin/teams/{team}/members', [SessionController::class, 'addMember'])->name('teams.add-member');
    Route::post('/admin/teams/{team}/locations', [SessionController::class, 'allocateLocation'])->name('teams.allocate-location');
    Route::delete('/admin/allocations/{allocation}', [SessionController::class, 'removeAllocation'])->name('allocations.remove');
    Route::delete('/admin/team-members/{member}', [SessionController::class, 'removeMember'])->name('members.remove');
    Route::delete('/admin/teams/{team}', [SessionController::class, 'deleteTeam'])->name('teams.delete');

    // Recounts
    Route::get('/admin/recounts', [RecountController::class, 'index'])->name('recounts.index');
    Route::post('/admin/entries/{entry}/recount', [RecountController::class, 'store'])->name('recounts.store');
    Route::post('/admin/recounts/{recount}/complete', [RecountController::class, 'complete'])->name('recounts.complete');
    Route::get('/admin/recount-options', [RecountController::class, 'getAssignOptions'])->name('recounts.options');

    // Team Monitoring
    Route::get('/admin/monitoring', [TeamProgressController::class, 'index'])->name('monitoring.index');
});

// Field Entry (Petugas SO & TL)
Route::middleware(['auth', 'active.session', 'role:petugas_so,team_leader'])->group(function () {
    Route::get('/entry', [EntryController::class, 'index'])->name('entry.index');
    Route::get('/entry/create', [EntryController::class, 'create'])->name('entry.create');
    Route::post('/entry', [EntryController::class, 'store'])->name('entry.store');
    Route::get('/api/item-by-sku', [EntryController::class, 'getItemBySku'])->name('api.item-sku');
});

// Verification (TL only)
Route::middleware(['auth', 'active.session', 'role:team_leader'])->group(function () {
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{entry}', [VerificationController::class, 'show'])->name('verification.show');
    Route::put('/verification/{entry}', [VerificationController::class, 'update'])->name('verification.update');
    Route::post('/verification/{entry}/verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/verification/verify-all', [VerificationController::class, 'verifyAll'])->name('verification.verify-all');
});

// Session context (semua user login)
Route::middleware(['auth'])->group(function () {
    Route::get('/session/picker', [SessionController::class, 'showPicker'])->name('session.picker');
    Route::post('/session/select', [SessionController::class, 'selectSession'])->name('session.select');
    Route::post('/session/clear', [SessionController::class, 'clearSession'])->name('session.clear');
});

// API endpoint for item search (admin & field)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/items/search', [ItemController::class, 'search'])->name('api.items.search');
});
