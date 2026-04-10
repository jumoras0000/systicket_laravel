<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\TempsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use Illuminate\Support\Facades\Route;

// --- Pages publiques ---
Route::get('/', fn() => view('pages.home'))->name('home');
Route::get('/cgu', fn() => view('pages.cgu'))->name('cgu');

// --- Auth (guest) ---
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/mot-de-passe-oublie', fn() => view('pages.mot-de-passe-oublie'))->name('password.request');
});

// --- Routes authentifiées ---
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (tous les authentifiés)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rapports (admin uniquement)
    Route::get('/rapports', [DashboardController::class, 'rapports'])->name('rapports')->middleware('role:admin');

    // Profil (tous les authentifiés)
    Route::get('/profil', [UserController::class, 'profil'])->name('profil');

    // Tickets - Liste & Détail (tous les authentifiés, filtré par le contrôleur)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create')->middleware('role:admin,collaborateur');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit')->middleware('role:admin,collaborateur');

    // Projets - Liste & Détail (tous les authentifiés, filtré par le contrôleur)
    Route::get('/projets', [ProjetController::class, 'index'])->name('projets.index');
    Route::get('/projets/create', [ProjetController::class, 'create'])->name('projets.create')->middleware('role:admin,collaborateur');
    Route::get('/projets/{projet}', [ProjetController::class, 'show'])->name('projets.show');
    Route::get('/projets/{projet}/edit', [ProjetController::class, 'edit'])->name('projets.edit')->middleware('role:admin,collaborateur');

    // Contrats - Liste & Détail (admin + client)
    Route::get('/contrats', [ContratController::class, 'index'])->name('contrats.index')->middleware('role:admin,client');
    Route::get('/contrats/create', [ContratController::class, 'create'])->name('contrats.create')->middleware('role:admin');
    Route::get('/contrats/{contrat}', [ContratController::class, 'show'])->name('contrats.show')->middleware('role:admin,client');
    Route::get('/contrats/{contrat}/edit', [ContratController::class, 'edit'])->name('contrats.edit')->middleware('role:admin');

    // Temps (admin, collaborateur)
    Route::get('/temps', [TempsController::class, 'index'])->name('temps.index')->middleware('role:admin,collaborateur');

    // Validation (admin, client)
    Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index')->middleware('role:admin,client');

    // Utilisateurs (admin uniquement)
    Route::middleware('role:admin')->group(function () {
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('/utilisateurs/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/utilisateurs/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    });
});
