<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminTeamController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Middleware\AdminPanelAccess;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', AdminPanelAccess::class, HandleInertiaRequests::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Admin dashboard
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // Users management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/assign-role', [AdminUserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('/users/{user}/remove-role', [AdminUserController::class, 'removeRole'])->name('users.remove-role');

        // Teams management
        Route::get('/teams', [AdminTeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/{team}', [AdminTeamController::class, 'show'])->name('teams.show');
        Route::get('/teams/{team}/edit', [AdminTeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [AdminTeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [AdminTeamController::class, 'destroy'])->name('teams.destroy');

        // Admin roles management
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

        // User role assignments
        Route::post('/users/{user}/roles/{role}', [AdminUserController::class, 'assignRole'])->name('users.roles.assign');
        Route::delete('/users/{user}/roles/{role}', [AdminUserController::class, 'removeRole'])->name('users.roles.remove');

        // Plans management
        Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [AdminPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}', [AdminPlanController::class, 'show'])->name('plans.show');
        Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');
    });
