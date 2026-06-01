<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de autenticación para conductores vía API.
 *
 * Permite a los conductores iniciar sesión usando su ruta (color),
 * número de combi y contraseña, obteniendo un token para el WebSocket.
 */
class ConductorAuthController extends Controller
{
    /**
     * Inicia sesión como conductor.
     *
     * POST /api/conductor/login
     *
     * Espera: { color, num_combi, password }
     * Retorna: { token, conductor: { id, nombre, apellido, num_combi, id_conductor, color, ruta_id } }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'color' => 'required|string',
            'num_combi' => 'required|integer|min:1',
            'password' => 'required|string',
        ], [
            'color.required' => 'El color de la ruta es obligatorio.',
            'num_combi.required' => 'El número de combi es obligatorio.',
            'num_combi.integer' => 'El número de combi debe ser un número entero.',
            'num_combi.min' => 'El número de combi debe ser mayor a 0.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $color = $request->input('color');
        $numCombi = (int) $request->input('num_combi');
        $password = $request->input('password');

        $ruta = Ruta::where('color', $color)->first();

        if (!$ruta) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta no encontrada. Verifica el color.',
            ], 404);
        }

        $conductor = Conductor::where('ruta_id', $ruta->id)
            ->where('num_combi', $numCombi)
            ->first();

        if (!$conductor) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un conductor con ese número de combi en esta ruta.',
            ], 404);
        }

        if (!Hash::check($password, $conductor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta.',
            ], 401);
        }

        // Revocar token anterior y generar uno nuevo
        $conductor->revokeToken();
        $token = $conductor->createToken();

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'conductor' => [
                'id' => $conductor->id,
                'nombre' => $conductor->nombre,
                'apellido' => $conductor->apellido,
                'num_combi' => $conductor->num_combi,
                'id_conductor' => $conductor->id_conductor,
                'color' => $ruta->color,
                'ruta_id' => $ruta->id,
            ],
        ]);
    }

    /**
     * Cierra sesión del conductor (invalida el token).
     *
     * POST /api/conductor/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            $token = $request->input('token');
        }

        if ($token) {
            $conductor = Conductor::findByToken($token);
            if ($conductor) {
                $conductor->revokeToken();
                return response()->json([
                    'success' => true,
                    'message' => 'Sesión cerrada correctamente.',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Token no proporcionado o inválido.',
        ], 400);
    }
}
