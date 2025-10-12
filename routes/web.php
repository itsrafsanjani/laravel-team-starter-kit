<?php

use App\Http\Controllers\Team\TeamBillingController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Team\TeamInvitationController;
use App\Http\Controllers\Team\TeamMemberController;
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
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('{team}/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('team.dashboard');

    Route::post('{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');

    // Team settings routes
    Route::get('{team}/settings/general', [TeamSettingsController::class, 'generalSettings'])->name('team.settings.general');
    Route::post('{team}/settings/general', [TeamSettingsController::class, 'updateGeneralSettings'])->name('team.settings.general.update');
    Route::delete('{team}/settings/general', [TeamSettingsController::class, 'deleteTeam'])->name('team.settings.general.delete');
    Route::get('{team}/settings/members', [TeamMemberController::class, 'index'])->name('team.settings.members');

    // Team member management routes
    Route::get('{team}/members', [TeamMemberController::class, 'index'])->name('teams.members.index');
    Route::post('{team}/members/invite', [TeamMemberController::class, 'invite'])->name('teams.members.invite');
    Route::put('{team}/members/{user}/role', [TeamMemberController::class, 'updateRole'])->name('teams.members.update-role');
    Route::delete('{team}/members/{user}', [TeamMemberController::class, 'remove'])->name('teams.members.remove');
    Route::delete('{team}/invitations/{invitation}', [TeamMemberController::class, 'removeInvitation'])->name('teams.invitations.remove');

    // Team billing routes

    Route::get('{team}/settings/billing', [TeamBillingController::class, 'index'])->name('team.settings.billing');
    Route::get('{team}/settings/billing/plans', [TeamBillingController::class, 'plans'])->name('team.settings.billing.plans');
    Route::post('{team}/settings/billing', [TeamBillingController::class, 'updateBillingSettings'])->name('team.settings.billing.update');
    Route::post('{team}/settings/billing/address', [TeamBillingController::class, 'updateBillingAddress'])->name('team.settings.billing.address.update');
    Route::post('{team}/settings/billing/checkout', [TeamBillingController::class, 'checkout'])->name('team.settings.billing.checkout');
    Route::get('{team}/settings/billing/portal', [TeamBillingController::class, 'billingPortal'])->name('team.settings.billing.portal');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
