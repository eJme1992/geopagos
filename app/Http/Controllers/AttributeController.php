<?php

namespace App\Http\Controllers;

use App\Models\Repository\Attribute\IAttributeRepository;
use Illuminate\Http\Request;
use App\Constants\HttpStatusCodes;
use App\Constants\ErrorMessages\GeneralStatusResponse;

class AttributeController extends Controller
{
    private $attributeRepository;

    public function __construct(IAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    
    /**
     * @OA\Get(
     *     path="/api/genders/attributes/{gender}",
     *     summary="Get attributes by gender",
     *     description="Registers a new player with the specified details. Requires JWT token for authentication.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Attributes"},
     *     @OA\Parameter(
     *         name="gender",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Gender slug to filter attributes"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attributes retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getAttributesByGenderSlug($gender)
    {
        try {
            $attributes = $this->attributeRepository->getAttributesByGenderSlug(
                $gender
            );
            return response()->json([
                "status" => GeneralStatusResponse::SUCCESS,
                "data" => $attributes,
            ]);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], HttpStatusCodes::INTERNAL_SERVER_ERROR);
        }
    }
}
