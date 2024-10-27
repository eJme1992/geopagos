<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Repository\User\IUserRepository;
use App\Constants\HttpStatusCodes;
use App\Constants\GeneralStatusResponse;
use App\Constants\Messages\AuthResponseMessages;
use App\Helpers\JwtAuth;

class AuthController extends Controller
{
    protected $jwtAuth;
    protected $userRepository;

    public function __construct(
        JwtAuth $jwtAuth,
        IUserRepository $userRepository
    ) {
        $this->jwtAuth = $jwtAuth;
        $this->userRepository = $userRepository;
    }

   
        /**
         * @OA\Post(
         *     path="/login",
         *     tags={"Auth"},
         *     summary="Login a user",
         *     description="Logs in a user with the specified email and password.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
         *             @OA\Property(property="password", type="string", format="password", example="password123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User logged in successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="status", type="string", example="success"),
         *             @OA\Property(property="code", type="integer", example=200),
         *             @OA\Property(property="msj", type="string", example="Login successful"),
         *             @OA\Property(property="data", type="object", description="User data")
         *         )
         *     ),
         *     @OA\Response(
         *         response=400,
         *         description="Validation error or user not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="status", type="string", example="error"),
         *             @OA\Property(property="code", type="integer", example=400),
         *             @OA\Property(property="msj", type="string", example="Login failed"),
         *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Internal server error",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Internal server error")
         *         )
         *     )
         * )
         */
        public function login(Request $request)
        {
            try {
                $params_array = $request->all();
    
                $validator = Validator::make($params_array, [
                    "email" => "required|email",
                    "password" => "required",
                ]);
    
                if ($validator->fails()) {
                    return $this->errorResponse(
                         HttpStatusCodes::BAD_REQUEST,
                         AuthResponseMessages::LOGIN_FAILURE,
                         $validator->errors()
                    );
                }
    
                $password = hash("sha256", $params_array["password"]);
                $user = $this->jwtAuth->signup($params_array["email"], $password);
    
                if (empty($user)) {
                    return $this->errorResponse(
                         HttpStatusCodes::BAD_REQUEST,
                         AuthResponseMessages::LOGIN_FAILURE,
                         AuthResponseMessages::LOGIN_USER_NOT_FOUND
                    );
                }
    
                $data = [
                    "status" => GeneralStatusResponse::SUCCESS,
                    "code"   => HttpStatusCodes::OK,
                    "msj"    => AuthResponseMessages::LOGIN_SUCCESS,
                    "data"   => $user,
                ];
    
                return response()->json($data, HttpStatusCodes::OK);
            } catch (\Exception $e) {
                return response()->json(
                    ["message" => AuthResponseMessages::INTERNAL_SERVER_ERROR . " " . $e->getMessage() . $e->getLine()],
                     HttpStatusCodes::INTERNAL_SERVER_ERROR
                );
            }
        }
    

        /**
         * @OA\Post(
         *     path="/register",
         *     tags={"Auth"},
         *     summary="Register a new user",
         *     description="Registers a new user with the specified email and password.",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
         *             @OA\Property(property="password", type="string", format="password", example="password123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="User registered successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="status", type="string", example="success"),
         *             @OA\Property(property="code", type="integer", example=201),
         *             @OA\Property(property="msj", type="string", example="User registered successfully")
         *         )
         *     ),
         *     @OA\Response(
         *         response=400,
         *         description="Validation error",
         *         @OA\JsonContent(
         *             @OA\Property(property="status", type="string", example="error"),
         *             @OA\Property(property="code", type="integer", example=400),
         *             @OA\Property(property="msj", type="string", example="Registration failed"),
         *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Internal server error",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Internal server error")
         *         )
         *     )
         * )
         */
        public function register(Request $request)
        {
            try {
                $params_array = $request->all();
    
                $validator = Validator::make($params_array, [
                    "email" => "required|email|unique:users",
                    "password" => "required",
                ]);
    
                if ($validator->fails()) {
                    return $this->errorResponse(
                         HttpStatusCodes::BAD_REQUEST,
                         AuthResponseMessages::REGISTRATION_FAILURE,
                         $validator->errors()
                    );
                }
    
                $password = hash("sha256", $params_array["password"]);
                $user = $this->userRepository->create([
                    "name"     => $params_array["email"],
                    "email"    => $params_array["email"],
                    "password" => $password,
                ]);
    
                $user->save();
    
                $data = [
                    "status" =>  GeneralStatusResponse::SUCCESS,
                    "code"   =>  HttpStatusCodes::CREATED,
                    "msj"    =>  AuthResponseMessages::REGISTRATION_SUCCESS,
                ];
    
                return response()->json($data, $data["code"]);
            } catch (\Exception $e) {
                return response()->json(
                    ["message" => AuthResponseMessages::INTERNAL_SERVER_ERROR . " " . $e->getMessage() . $e->getLine()],
                    HttpStatusCodes::INTERNAL_SERVER_ERROR
                );
            }
        }
    

    protected function errorResponse($code, $message, $errors)
    {
        $data = [
            "status" => GeneralStatusResponse::ERROR,
            "code"   => $code,
            "msj"    => $message,
            "errors" => $errors,
        ];
        return response()->json($data, $code);
    }
}
