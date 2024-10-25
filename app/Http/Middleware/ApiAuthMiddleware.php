<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Obtén el token del encabezado Authorization
        $token = $request->header('Authorization');

        // Verifica si el token está presente
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'code'   =>  403,
                'msj'    => 'Token no proporcionado',
            ], 403);
        }

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token, true);

        // Si el token es válido, procede al siguiente middleware o ruta
        if (!$checkToken) {
            // Devuelve una respuesta de error si el token es inválido
            $data = [
                'status' => 'error',
                'code'   =>  403,
                'msj'    => 'El Usuario no está identificado',
            ];
            return response()->json($data, $data['code']);
        }

        // Agrega el usuario al encabezado de la solicitud
        $request->headers->set('user', json_encode($checkToken));
        return $next($request);
    }
}
