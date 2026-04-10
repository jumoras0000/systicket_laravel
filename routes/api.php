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

// --- Auth publique ---
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);

// --- Routes protégées par Sanctum ---
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'apiUser']);
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::post('/change-password', [AuthController::class, 'apiChangePassword']);

    // Dashboard (tous les authentifiés, filtré par le contrôleur)
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
    Route::get('/dashboard/charts', [DashboardController::class, 'apiCharts']);
    Route::get('/dashboard/recent', [DashboardController::class, 'apiRecentActivity']);

    // Rapports (admin uniquement)
    Route::get('/rapports', [DashboardController::class, 'apiRapports'])->middleware('role:admin');

    // Tickets - Lecture (tous les authentifiés, filtré par le contrôleur)
    Route::get('/tickets', [TicketController::class, 'apiIndex']);
    Route::get('/tickets/to-validate', [TicketController::class, 'apiToValidate']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'apiShow']);
    Route::get('/tickets/{ticket}/comments', [TicketController::class, 'apiComments']);
    Route::get('/tickets/{ticket}/assignees', [TicketController::class, 'apiAssignees']);
    Route::get('/tickets/{ticket}/time-entries', [TicketController::class, 'apiTimeEntries']);

    // Tickets - Écriture (admin, collaborateur)
    Route::middleware('role:admin,collaborateur')->group(function () {
        Route::post('/tickets', [TicketController::class, 'apiStore']);
        Route::put('/tickets/{ticket}', [TicketController::class, 'apiUpdate']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'apiDestroy']);
        Route::post('/tickets/{ticket}/assignees', [TicketController::class, 'apiAddAssignee']);
        Route::delete('/tickets/{ticket}/assignees', [TicketController::class, 'apiRemoveAssignee']);
    });

    // Tickets - Commentaires (tous les authentifiés peuvent commenter)
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'apiAddComment']);

    // Projets - Lecture (tous les authentifiés, filtré par le contrôleur)
    Route::get('/projets', [ProjetController::class, 'apiIndex']);
    Route::get('/projets/counts', [ProjetController::class, 'apiCounts']);
    Route::get('/projets/{projet}', [ProjetController::class, 'apiShow']);
    Route::get('/projets/{projet}/assignees', [ProjetController::class, 'apiAssignees']);
    Route::get('/projets/{projet}/tickets', [ProjetController::class, 'apiTickets']);
    Route::get('/projets/{projet}/contrat', [ProjetController::class, 'apiContrat']);

    // Projets - Écriture (admin, collaborateur)
    Route::middleware('role:admin,collaborateur')->group(function () {
        Route::post('/projets', [ProjetController::class, 'apiStore']);
        Route::put('/projets/{projet}', [ProjetController::class, 'apiUpdate']);
        Route::delete('/projets/{projet}', [ProjetController::class, 'apiDestroy']);
        Route::post('/projets/{projet}/assignees', [ProjetController::class, 'apiAddAssignee']);
        Route::delete('/projets/{projet}/assignees', [ProjetController::class, 'apiRemoveAssignee']);
    });

    // Contrats - Lecture (admin + client, filtré par le contrôleur)
    Route::get('/contrats', [ContratController::class, 'apiIndex'])->middleware('role:admin,client');
    Route::get('/contrats/{contrat}', [ContratController::class, 'apiShow'])->middleware('role:admin,client');
    Route::get('/contrats/{contrat}/summary', [ContratController::class, 'apiSummary'])->middleware('role:admin,client');

    // Contrats - Écriture (admin uniquement)
    Route::middleware('role:admin')->group(function () {
        Route::post('/contrats', [ContratController::class, 'apiStore']);
        Route::put('/contrats/{contrat}', [ContratController::class, 'apiUpdate']);
        Route::delete('/contrats/{contrat}', [ContratController::class, 'apiDestroy']);
    });

    // Temps - Lecture (admin, collaborateur)
    Route::middleware('role:admin,collaborateur')->group(function () {
        Route::get('/temps', [TempsController::class, 'apiIndex']);
        Route::get('/temps/week-summary', [TempsController::class, 'apiWeekSummary']);
        Route::get('/temps/month-total', [TempsController::class, 'apiMonthTotal']);
        Route::get('/temps/by-project', [TempsController::class, 'apiHoursByProject']);
        Route::get('/temps/by-user', [TempsController::class, 'apiHoursByUser']);
    });

    // Temps - Écriture (admin, collaborateur)
    Route::middleware('role:admin,collaborateur')->group(function () {
        Route::post('/temps', [TempsController::class, 'apiStore']);
        Route::put('/temps/{temp}', [TempsController::class, 'apiUpdate']);
        Route::delete('/temps/{temp}', [TempsController::class, 'apiDestroy']);
    });

    // Validations (admin, client)
    Route::middleware('role:admin,client')->group(function () {
        Route::get('/validations', [ValidationController::class, 'apiToValidate']);
        Route::post('/validations/{ticket}', [ValidationController::class, 'apiValidate']);
    });
    Route::get('/validations/{ticket}/history', [ValidationController::class, 'apiHistory']);

    // Utilisateurs - Lecture (tous les authentifiés - nécessaire pour les listes déroulantes)
    Route::get('/users', [UserController::class, 'apiIndex']);
    Route::get('/users/collaborateurs', [UserController::class, 'apiCollaborateurs']);
    Route::get('/users/clients', [UserController::class, 'apiClients']);
    Route::get('/users/profil', [UserController::class, 'apiProfil']);
    Route::put('/users/profil', [UserController::class, 'apiUpdateProfil']);
    Route::get('/users/{user}', [UserController::class, 'apiShow']);

    // Utilisateurs - Écriture (admin uniquement)
    Route::middleware('role:admin')->group(function () {
        Route::post('/users', [UserController::class, 'apiStore']);
        Route::put('/users/{user}', [UserController::class, 'apiUpdate']);
        Route::delete('/users/{user}', [UserController::class, 'apiDestroy']);
    });
});
