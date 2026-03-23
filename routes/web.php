<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',                    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',                   [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::get('/forgot-password',          [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password',         [AuthController::class, 'forgotPassword'])->name('password.forgot.post')->middleware('throttle:3,5');
Route::get('/reset-password/{token}',   [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password/{token}',  [AuthController::class, 'resetPassword'])->name('password.reset.post');

// ── Protected (semua role yang sudah login) ───────────────────────────────────
Route::middleware('auth.session')->group(function () {

    // Logout
    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/',          [TicketController::class, 'index'])->name('dashboard');

    // App data (JSON untuk JS)
    Route::get('/app-data',  [AdminController::class, 'appData'])->name('app.data');

    // Password
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change');

    // Notifikasi in-app (semua role)
    Route::get('/notifications',            [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');

    // Export Excel (IT + Manager) — harus SEBELUM wildcard {ticketId}
    Route::get('/tickets/export/excel',             [TicketController::class, 'exportExcel'])->name('tickets.export');

    // Rejection notice (User) — harus SEBELUM wildcard {ticketId}
    Route::get('/tickets/rejection-notice',         [TicketController::class, 'getRejectionNotice'])->name('tickets.rejection.get');
    Route::post('/tickets/rejection-notice/dismiss',[TicketController::class, 'dismissRejectionNotice'])->name('tickets.rejection.dismiss');

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
        Route::post('/freezes/{freezeId}/approve',  [TicketController::class, 'approveFreeze'])->name('freezes.approve');
        Route::post('/freezes/{freezeId}/reject',   [TicketController::class, 'rejectFreeze'])->name('freezes.reject');
    });

    // ── IT SIM + Manager IT ───────────────────────────────────────────────────
    Route::middleware('role:it,it_manager')->group(function () {
        // Tasks (checklist)
        Route::post('/tickets/{ticketId}/tasks',    [AdminController::class, 'storeTask'])->name('tasks.store');
        Route::patch('/tasks/{taskId}/toggle',      [AdminController::class, 'toggleTask'])->name('tasks.toggle');
        Route::patch('/tasks/{taskId}',             [AdminController::class, 'updateTask'])->name('tasks.update');
        Route::delete('/tasks/{taskId}',            [AdminController::class, 'destroyTask'])->name('tasks.destroy');

        Route::post('/tickets/{ticketId}/close',    [TicketController::class, 'close'])->name('tickets.close');
        Route::delete('/tickets/{ticketId}',        [TicketController::class, 'destroy'])->name('tickets.destroy');
        Route::post('/tickets/{ticketId}/freeze',   [TicketController::class, 'requestFreeze'])->name('tickets.freeze');
        Route::post('/tickets/{ticketId}/unfreeze', [TicketController::class, 'unfreeze'])->name('tickets.unfreeze');

        // Users
        Route::get('/users',                              [UserController::class, 'pageIndex'])->name('users.page');
        Route::get('/users/data',                         [UserController::class, 'index'])->name('users.data');
        Route::post('/users',                             [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{id}',                       [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/toggle-active',         [UserController::class, 'toggleActive'])->name('users.toggleActive');
        Route::patch('/users/{id}/reset-password',        [UserController::class, 'resetPassword'])->name('users.resetPassword');
        Route::delete('/users/{id}',                      [UserController::class, 'destroy'])->name('users.destroy');

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

        // Security: login logs & pending password resets
        Route::get('/security/login-logs',      [AdminController::class, 'loginLogs'])->name('security.loginLogs');
        Route::get('/security/reset-requests',  [AdminController::class, 'resetRequests'])->name('security.resetRequests');
    });

    // ── Manager IT Only (reassign) ────────────────────────────────────────────
    Route::middleware('role:it_manager')->group(function () {
        Route::post('/tickets/{ticketId}/reassign', [TicketController::class, 'reassign'])->name('tickets.reassign');
    });
});
