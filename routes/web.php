<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',         [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',        [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',       [AuthController::class, 'logout'])->name('logout');

// ── Protected (semua role yang sudah login) ───────────────────────────────────
Route::middleware('auth.session')->group(function () {

    // Dashboard
    Route::get('/',          [TicketController::class, 'index'])->name('dashboard');

    // App data (JSON untuk JS)
    Route::get('/app-data',  [AdminController::class, 'appData'])->name('app.data');

    // Password
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change');

    // Export Excel (IT + Manager) — harus SEBELUM wildcard {ticketId}
    Route::get('/tickets/export/excel',             [TicketController::class, 'exportExcel'])->name('tickets.export');

    // Tiket
    Route::post('/tickets',                         [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticketId}',               [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticketId}/comment',      [TicketController::class, 'comment'])->name('tickets.comment');
    Route::get('/attachments/{id}/download',        [TicketController::class, 'downloadAttachment'])->name('attachments.download');

    // Tasks (checklist)
    // ── Manager Only (approve/reject) ─────────────────────────────────────────
    Route::middleware('role:manager')->group(function () {
        Route::post('/tickets/{ticketId}/approve',  [TicketController::class, 'approve'])->name('tickets.approve');
        Route::delete('/tickets/{ticketId}/reject', [TicketController::class, 'reject'])->name('tickets.reject');
    });

    // ── IT + Manager ─────────────────────────────────────────────────────────
    Route::middleware('role:it,manager')->group(function () {
        Route::post('/tickets/{ticketId}/stage-due',[TicketController::class, 'saveStageDue'])->name('tickets.stage-due');
    });

    // ── IT Only ───────────────────────────────────────────────────────────────
    Route::middleware('role:it')->group(function () {
        // Tasks (checklist) — hanya IT SIM
        Route::post('/tickets/{ticketId}/tasks',    [AdminController::class, 'storeTask'])->name('tasks.store');
        Route::patch('/tasks/{taskId}/toggle',      [AdminController::class, 'toggleTask'])->name('tasks.toggle');
        Route::delete('/tasks/{taskId}',            [AdminController::class, 'destroyTask'])->name('tasks.destroy');

        Route::post('/tickets/{ticketId}/advance',  [TicketController::class, 'advance'])->name('tickets.advance');
        Route::post('/tickets/{ticketId}/close',    [TicketController::class, 'close'])->name('tickets.close');
        Route::delete('/tickets/{ticketId}',        [TicketController::class, 'destroy'])->name('tickets.destroy');
        Route::post('/tickets/{ticketId}/reassign', [TicketController::class, 'reassign'])->name('tickets.reassign');

        // Users
        Route::get('/users',         [UserController::class, 'index'])->name('users.index');
        Route::post('/users',        [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{id}',  [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Clients
        Route::get('/clients',         [AdminController::class, 'getClients'])->name('clients.index');
        Route::post('/clients',        [AdminController::class, 'storeClient'])->name('clients.store');
        Route::delete('/clients/{id}', [AdminController::class, 'destroyClient'])->name('clients.destroy');

        // Auto Assign
        Route::get('/auto-assign',         [AdminController::class, 'getAutoAssign'])->name('autoassign.index');
        Route::post('/auto-assign',        [AdminController::class, 'storeAutoAssign'])->name('autoassign.store');
        Route::delete('/auto-assign/{id}', [AdminController::class, 'destroyAutoAssign'])->name('autoassign.destroy');

        // App Config
        Route::get('/config',           [AdminController::class, 'getConfig'])->name('config.get');
        Route::post('/config',          [AdminController::class, 'saveConfig'])->name('config.save');
        Route::post('/config/reset',    [AdminController::class, 'resetConfig'])->name('config.reset');
    });
});
