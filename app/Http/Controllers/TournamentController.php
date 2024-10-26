<?php

namespace App\Http\Controllers;

use App\Services\TournamentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Constants\HttpStatusCodes;
use App\Constants\ErrorMessages\GeneralStatusResponse;
use App\Constants\Messages\TournamentResponseMessages;


class TournamentController extends Controller
{
    private $tournamentService;

    public function __construct(TournamentService $tournamentService)
    {
        $this->tournamentService = $tournamentService;
    }

    /**
     * @OA\Post(
     *     path="/api/tournaments/register",
     *     summary="Crear un nuevo torneo",
     *     description="Crea un nuevo torneo con los parámetros proporcionados.",
     *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Tournament"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"gender", "name", "number_players"},
     *             @OA\Property(property="gender", type="string", example="male"),
     *             @OA\Property(property="name", type="string", example="Torneo de Verano"),
     *             @OA\Property(property="number_players", type="integer", example=16)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Torneo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de los parámetros",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El número de participantes debe ser mayor a cero y múltiplo de dos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Mensaje de error")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                "gender" => "required|string|exists:genders,slug",
                "name" => "required|string|max:255",
                "number_players" => "required|integer",
            ]);

            if (
                !$this->tournamentService->numberPlayersValidate(
                    $request->input("number_players")
                )
            ) {
                return response()->json(
                    [
                        "error" => TournamentResponseMessages::TOURNAMENT_INVALID_PLAYERS,
                    ],
                    HttpStatusCodes::BAD_REQUEST
                );
            }

            $tournament = $this->tournamentService->register(
                $request->input("name"),
                $request->input("gender"),
                $request->input("number_players")
            );

            if (!$tournament) {
                return response()->json(
                    ["error" =>   TournamentResponseMessages::ERROR_CREATING_TOURNAMENT],
                    HttpStatusCodes::INTERNAL_SERVER_ERROR
                );
            }

            return response()->json(
                [
                    "status" => GeneralStatusResponse::SUCCESS,
                    "data" => $tournament,
                ],
                HttpStatusCodes::CREATED
            );
        } catch (\Exception $e) {
                 info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tournaments/register-player",
     *     summary="Registrar un jugador en un torneo",
     *     description="Registra un jugador en un torneo con los parámetros proporcionados.",
     *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Tournament"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tournament_id", "player_id"},
     *             @OA\Property(property="tournament_id", type="integer", example=1),
     *             @OA\Property(property="player_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jugador registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de los parámetros",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El torneo ya tiene el número de jugadores requeridos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Mensaje de error")
     *         )
     *     )
     * )
     */
    public function registerPlayer(Request $request)
    {
        try {
            $request->validate([
                "tournament_id" => "required|integer|exists:tournaments,id",
                "player_id" => "required|integer|exists:players,id",
            ]);

            if (
                $this->tournamentService->tournamentIsComplete(
                    $request->input("tournament_id")
                )
            ) {
                return response()->json(
                    [
                        "error" => TournamentResponseMessages::TOURNAMENT_IS_COMPLETE,
                    ],
                    HttpStatusCodes::BAD_REQUEST
                );
            }

            if (
                $this->tournamentService->tournamentAndPlayerExist(
                    $request->input("tournament_id"),
                    $request->input("player_id")
                )
            ) {
                return response()->json(
                    ["error" =>  TournamentResponseMessages::PLAYER_ALREADY_REGISTERED],
                    HttpStatusCodes::BAD_REQUEST
                );
            }

            $tournamentPlayer = $this->tournamentService->registerPlayer(
                $request->input("tournament_id"),
                $request->input("player_id")
            );

            return response()->json(
                [
                    "status" => GeneralStatusResponse::SUCCESS,
                    "data" => $tournamentPlayer,
                ],
                HttpStatusCodes::CREATED
            );
        } catch (\Exception $e) {
                 info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()],);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tournaments/is-complete/{tournamentId}",
     *     summary="Verificar si el torneo está completo",
     *     description="Verifica si un torneo específico ha alcanzado el número de jugadores requeridos.",
     *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Tournament"},
     *     @OA\Parameter(
     *         name="tournamentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID del torneo"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado del torneo",
     *         @OA\JsonContent(
     *             @OA\Property(property="isComplete", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Mensaje de error")
     *         )
     *     )
     * )
     */
    public function tournamentIsComplete(int $tournamentId): JsonResponse
    {
        try {
            $response = $this->tournamentService->tournamentIsComplete(
                $tournamentId
            );
            return response()->json(
                ["status" => GeneralStatusResponse::SUCCESS, "isComplete" => $response],
                HttpStatusCodes::OK
            );
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()],  HttpStatusCodes::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tournaments/gender/{gender}",
     *     summary="Obtener torneos por género",
     *     description="Obtiene una lista de torneos filtrados por género.",
     *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Tournament"},
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Género de los torneos a filtrar",
     *         example="male"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de torneos",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Torneo de Verano"),
     *                     @OA\Property(property="gender", type="string", example="male"),
     *                     @OA\Property(property="number_players", type="integer", example=16)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Mensaje de error")
     *         )
     *     )
     * )
     */
    public function getTournamentsByGender(string $gender)
    {
        try {
            $tournaments = $this->tournamentService->getTournamentsByGender(
                $gender
            );
            return response()->json([
                "status" => GeneralStatusResponse::SUCCESS,
                "data" => $tournaments,
            ]);
        } catch (\Exception $e) {
                 info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()]);
            return response()->json(["error" => $e->getMessage()], HttpStatusCodes::INTERNAL_SERVER_ERROR);
        }
    }
}
