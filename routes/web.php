<?php

use App\Http\Controllers\Team\GeneralSettingController;
use App\Http\Controllers\Team\TeamBillingController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Team\TeamInvitationController;
use App\Http\Controllers\Team\MemberController;
use App\Http\Controllers\Team\TeamSettingsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Public team invitation routes (no authentication required)
Route::get('team-invitations/{invitation}', [TeamInvitationController::class, 'show'])
    ->name('team-invitations.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = Auth::user();

        // If user has teams, redirect to their default team dashboard
        if ($user->hasAnyTeam()) {
            $defaultTeam = $user->getDefaultTeam();
            if ($defaultTeam) {
                return redirect()->route('team.dashboard', $defaultTeam);
            }
        }

        // If no teams, show team creation or general dashboard
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Team management routes
    Route::get('teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');

    // Team invitation routes (for authenticated users)
    Route::post('invite/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('team-invitations.accept');
    Route::delete('invite/{invitation}/decline', [TeamInvitationController::class, 'decline'])->name('team-invitations.decline');
});

// Team-aware routes (with slug prefix)
Route::middleware(['auth', 'verified'])->prefix('{team}')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('team.dashboard');

    Route::post('switch', [TeamController::class, 'switch'])->name('teams.switch');

    // Team settings routes
    Route::get('settings/general', [GeneralSettingController::class, 'index'])->name('team.settings.general.index');
    Route::post('settings/general', [GeneralSettingController::class, 'update'])->name('team.settings.general.update');

    Route::delete('/', [TeamController::class, 'delete'])->name('team.delete');

    // Team member management routes
    Route::get('settings/members', [MemberController::class, 'index'])->name('team.settings.members.index');
    Route::delete('settings/members/{user}', [MemberController::class, 'destroy'])->name('teams.settings.members.destroy');

    Route::post('members/invite', [MemberController::class, 'invite'])->name('teams.members.invite');
    Route::put('members/{user}/role', [MemberController::class, 'updateRole'])->name('teams.members.update-role');
    Route::delete('invitations/{invitation}', [MemberController::class, 'removeInvitation'])->name('teams.invitations.remove');

    // Team billing routes

    Route::get('settings/billing', [TeamBillingController::class, 'index'])->name('team.settings.billing');
    Route::get('settings/billing/plans', [TeamBillingController::class, 'plans'])->name('team.settings.billing.plans');
    Route::post('settings/billing', [TeamBillingController::class, 'updateBillingSettings'])->name('team.settings.billing.update');
    Route::post('settings/billing/address', [TeamBillingController::class, 'updateBillingAddress'])->name('team.settings.billing.address.update');
    Route::post('settings/billing/checkout', [TeamBillingController::class, 'checkout'])->name('team.settings.billing.checkout');
    Route::get('settings/billing/portal', [TeamBillingController::class, 'billingPortal'])->name('team.settings.billing.portal');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
