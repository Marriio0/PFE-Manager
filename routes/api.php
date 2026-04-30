<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\VersionRapportController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FiliereController;

// ── Routes publiques (sans token) ──
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/filieres',  [FiliereController::class, 'index']);

// ── Routes protégées ──
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',     [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);

    // Upload
    Route::post('/upload', [FileUploadController::class, 'upload']);

    // Encadrants (dropdown soumission)
    Route::get('/encadrants', [UserController::class, 'encadrants']);

    // Filières (POST/DELETE — admin)
    Route::post('/filieres',             [FiliereController::class, 'store']);
    Route::delete('/filieres/{filiere}', [FiliereController::class, 'destroy']);

    // Utilisateurs (admin)
    Route::get('/users',                 [UserController::class, 'index']);
    Route::get('/users/pending',         [UserController::class, 'pending']);
    Route::post('/users/{user}/approve', [UserController::class, 'approve']);
    Route::post('/users/{user}/reject',  [UserController::class, 'reject']);
    Route::put('/users/{user}',          [UserController::class, 'update']);
    Route::delete('/users/{user}',       [UserController::class, 'destroy']);

    // Invite codes
    Route::get('/invite-codes',                 [UserController::class, 'inviteCodes']);
    Route::post('/invite-codes',                [UserController::class, 'generateInviteCode']);
    Route::delete('/invite-codes/{inviteCode}', [UserController::class, 'deleteInviteCode']);

    // Rapports
    Route::get('/rapports',              [RapportController::class, 'index']);
    Route::post('/rapports',             [RapportController::class, 'store']);
    Route::get('/rapports/{rapport}',    [RapportController::class, 'show']);
    Route::put('/rapports/{rapport}',    [RapportController::class, 'update']);
    Route::delete('/rapports/{rapport}', [RapportController::class, 'destroy']);

    // Commentaires
    Route::get('/rapports/{rapport}/commentaires',  [CommentaireController::class, 'index']);
    Route::post('/rapports/{rapport}/commentaires', [CommentaireController::class, 'store']);
    Route::delete('/commentaires/{commentaire}',    [CommentaireController::class, 'destroy']);

    // Versions
    Route::get('/rapports/{rapport}/versions',   [VersionRapportController::class, 'index']);
    Route::post('/rapports/{rapport}/versions',  [VersionRapportController::class, 'store']);
    Route::delete('/versions/{versionRapport}',  [VersionRapportController::class, 'destroy']);

    // Validations
    Route::get('/rapports/{rapport}/validations',  [ValidationController::class, 'index']);
    Route::post('/rapports/{rapport}/validations', [ValidationController::class, 'store']);
    Route::delete('/validations/{validation}',     [ValidationController::class, 'destroy']);
});