<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Repository\User\IUserRepository;
use App\Constants\HttpStatusCodes;
use App\Constants\ErrorMessages\GeneralStatusResponse;
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
