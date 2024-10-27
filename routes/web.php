<?php

use Illuminate\Support\Facades\Route;

/**
 * @OA\Info(
 *     title="API de Torneos",
 *     version="1.0.0",
 *     description="Documentación de la API para la gestión de torneos"
 * )
 */

/**
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor API"
 * )
 */

use App\Http\Middleware\ApiAuthMiddleware;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\AttributeController;


Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('api/players')->middleware(ApiAuthMiddleware::class)->group(function () {
    Route::post('/register', [PlayerController::class, 'register'])->name('api.player.register');
    Route::get('/gender/{gender}',   [PlayerController::class, 'getPlayersForGender']);
    Route::get('/gender/nottournament/{gender}/{tournament}',   [PlayerController::class, 'getPlayersForGenderNotTournament']);
});

Route::prefix('api/genders')->middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/all', [GenderController::class, 'getAll']);
    Route::get('/attributes/{gender}', [AttributeController::class, 'getAttributesByGenderSlug']);
});

Route::prefix('api/tournaments')->middleware(ApiAuthMiddleware::class)->group(function () {
    Route::post('/register', [TournamentController::class, 'register']);
    Route::post('/register-player', [TournamentController::class, 'registerPlayer']);
    Route::get('/is-complete/{tournamentId}', [TournamentController::class, 'tournamentIsComplete']);
    //Torneos por genero
    Route::get('/gender/{gender}', [TournamentController::class, 'getTournamentsByGender']);
    Route::get('/{tournamentId}/start', [TournamentController::class, 'startTournament']);
    Route::get('/tournaments/results', [TournamentController::class, 'getTournamentResults']);
});


   
