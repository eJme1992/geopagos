<?php

namespace App\Http\Controllers;

use App\Models\Repository\Gender\IGenderRepository;
use Illuminate\Http\Request;

use App\Constants\HttpStatusCodes;
use App\Constants\GeneralStatusResponse;

class GenderController extends Controller
{
    private $genderRepository;

    public function __construct(IGenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }

      
    /**
     * @OA\Get(
     *     path="/api/genders/all",
     *     summary="Obtener todos los géneros",
     *     description="Devuelve una lista de todos los géneros con los campos `slug` y `name`. Requiere un token JWT para la autenticación.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Genders"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de géneros obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="slug",
     *                     type="string",
     *                     example="femenino"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Femenino"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener la lista de géneros",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function getAll()
    {
        try {
            $attributes = $this->genderRepository->all(["slug", "name"]);
            return response()->json([
                "status" => GeneralStatusResponse::SUCCESS,
                "data"   => $attributes,
            ]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
