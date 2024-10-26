<?php

namespace App\Http\Controllers;

use App\Constants\ErrorMessages\GeneralErrorMessages;
use App\Constants\ErrorMessages\GeneralStatusResponse;
use App\Factories\PlayerDTOFactory;
use App\Models\Repository\Gender\IGenderRepository;
use Illuminate\Http\Request;
use App\Services\PlayerService;
use Illuminate\Http\JsonResponse;

use App\Utils\ArrayUtils;

use App\Constants\HttpStatusCodes;
use Illuminate\Support\Facades\Http;

class PlayerController extends Controller
{
    private $playerDTOFactory;
    private $playerService;
    private $genderRepository;

    public function __construct(
        PlayerDTOFactory $playerDTOFactory,
        PlayerService $playerService,
        IGenderRepository $genderRepository
    ) {
        $this->playerDTOFactory = $playerDTOFactory;
        $this->playerService = $playerService;
        $this->genderRepository = $genderRepository;
    }

  
   /**
 * @OA\Post(
 *     path="/api/players/register",
 *     tags={"Players"},
 *     summary="Register a new player",
 *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male", description="Gender of the player"),
 *             @OA\Property(property="name", type="string", example="John Doe", description="Name of the player"),
 *             @OA\Property(property="ability", type="integer", format="int32", example=75, description="Ability of the player (1-100)", minimum=1, maximum=100),
 *             @OA\Property(property="atributesPlayer", type="object", description="Attributes of the player as key-value pairs",
 *                 @OA\Property(property="strength", type="number", example=5, description="Strength attribute of the player"),
 *                 @OA\Property(property="speed", type="number", example=5, description="Speed attribute of the player")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Player registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="object", description="Registered player data",
 *                 @OA\Property(property="id", type="integer", example=1, description="Player ID"),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="gender", type="string", example="male"),
 *                 @OA\Property(property="ability", type="integer", format="int32", example=75),
 *                 @OA\Property(property="atributesPlayer", type="object", description="Attributes of the player as key-value pairs",
 *                     @OA\Property(property="strength", type="number", example=5),
 *                     @OA\Property(property="speed", type="number", example=5)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="object", description="Validation error messages",
 *                 @OA\Property(property="gender", type="array", @OA\Items(type="string"), example={"The gender field is required."}),
 *                 @OA\Property(property="name", type="array", @OA\Items(type="string"), example={"The name field is required."}),
 *                 @OA\Property(property="ability", type="array", @OA\Items(type="string"), example={"The ability must be an integer between 1 and 100."}),
 *                 @OA\Property(property="atributesPlayer", type="array", @OA\Items(type="string"), example={"The atributes player field is required."})
 *             )
 *         )
 *     )
 * )
 */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                "gender" => "required|string|exists:genders,slug",
                "name" => "required|string|max:255",
                "ability" => "required|int|min:1|max:100",
                "atributesPlayer" => "required|array",
            ]);

            $gender = $request->input("gender");
            $name = $request->input("name");
            $ability = $request->input("ability");
            $atributesPlayer = $request->input("atributesPlayer");

            if (!ArrayUtils::verifyAttributes($atributesPlayer)) {
                return response()->json(
                    [
                        "status" => GeneralStatusResponse::ERROR,
                        "message" => GeneralErrorMessages::INVALID_ATTRIBUTES,
                    ],
                    HttpStatusCodes::BAD_REQUEST
                );
            }

            $gender = $this->genderRepository->findBySlug($gender);
            $playerDTO = $this->playerDTOFactory->createPlayerDTO(
                $name,
                $ability,
                $gender,
                $atributesPlayer
            );

            if (is_null($playerDTO)) {
                return response()->json(
                    [
                        "status" => GeneralStatusResponse::ERROR,
                        "message" =>GeneralErrorMessages::INVALID_ATTRIBUTES_GENDER,
                    ],
                    HttpStatusCodes::BAD_REQUEST
                );
            }

            $player = $this->playerService->register($playerDTO);

            return response()->json([
                "status" => GeneralStatusResponse::SUCCESS,
                "data" => $player,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], HttpStatusCodes::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            // registrar el error en un log
            info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()],);
            return response()->json(
                [
                    "status" => GeneralStatusResponse::ERROR,
                    "message" => GeneralErrorMessages::INTERNAL_SERVER_ERROR,
                ],
                HttpStatusCodes::INTERNAL_SERVER_ERROR
            );
        }
    }

   
    /**
     * @OA\Get(
     *     path="/api/players/gender/{gender}",
     *     summary="Obtiene una lista de jugadores según el género",
     *     tags={"Players"},
     *     description="Obtiene una lista de jugadores según el género especificado. Requiere un token JWT para la autenticación.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         required=true,
     *         description="Género de los jugadores a buscar (ej: 'male', 'female')",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de jugadores encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="John Doe"
     *                     ),
     *                     @OA\Property(
     *                         property="gender",
     *                         type="string",
     *                         example="male"
     *                     ),
     *                     @OA\Property(
     *                         property="ability",
     *                         type="integer",
     *                         example=85
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron jugadores",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No players found."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Internal server error."
     *             )
     *         )
     *     )
     * )
     */
    public function getPlayersForGender(string $gander): JsonResponse
    {
        try {
            $players = $this->playerService->getPlayersForGenderSlug($gander);

            if (empty($players)) {
                return response()->json(
                    [
                        "status" => GeneralStatusResponse::ERROR,
                        "message" => GeneralErrorMessages::NO_PLAYERS_FOUND,
                    ],
                    HttpStatusCodes::NOT_FOUND
                );
            }

            return response()->json([
                "status" =>  GeneralStatusResponse::SUCCESS,
                "data" => $players,
            ]);
        } catch (\Exception $e) {
            info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()],);
            return response()->json(
                [
                    "status" => GeneralStatusResponse::ERROR,
                    "message" => GeneralErrorMessages::INTERNAL_SERVER_ERROR,
                    "error" => $e->getMessage(),
                ],
                HttpStatusCodes::INTERNAL_SERVER_ERROR
            );
        }
    }

    
    /**
     * @OA\Get(
     *     path="/api/players/gender/nottournament/{gender}/{tournamentId}",
     *     tags={"Players"},
     *     summary="Obtiene jugadores por género que no están en el torneo",
     *     description="Este endpoint devuelve una lista de jugadores de un género específico que no están inscritos en un torneo determinado. Requiere un token JWT para la autenticación.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         required=true,
     *         description="El slug del género de los jugadores que se desean obtener.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tournamentId",
     *         in="path",
     *         required=true,
     *         description="El ID del torneo en el que se desea verificar la inscripción de los jugadores.",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de jugadores obtenida exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron jugadores para el género especificado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No players found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function getPlayersForGenderNotTournament(
        string $gender,
        int $tournamentId
    ): JsonResponse {
        try {
            $players = $this->playerService->getPlayersForGenderSlugNotTournament(
                $gender,
                $tournamentId
            );

            if (empty($players)) {
                return response()->json(
                    [
                        "status" => GeneralStatusResponse::ERROR,
                        "message" => GeneralErrorMessages::NO_PLAYERS_FOUND,
                    ],
                    HttpStatusCodes::NOT_FOUND
                );
            }

            return response()->json([
                "status" => GeneralStatusResponse::SUCCESS,
                "data" => $players,
            ]);
        } catch (\Exception $e) {
            info($e->getMessage(), ['line' => $e->getLine()], ['file' => $e->getFile()],);
            return response()->json(
                [
                    "status" => GeneralStatusResponse::ERROR,
                    "message" => GeneralErrorMessages::INTERNAL_SERVER_ERROR,
                    "error" => $e->getMessage(),
                ],
                HttpStatusCodes::INTERNAL_SERVER_ERROR
            );
        }
    }
}
