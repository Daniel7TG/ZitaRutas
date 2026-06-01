<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request de validación para crear una nueva ruta.
 *
 * Espera un JSON con la estructura:
 * {
 *   "color": "#FF5733",
 *   "horarios": [
 *     { "name": "Mañana", "identifier": "AM" }
 *   ],
 *   "puntos_navegacion": [
 *     { "latitud": 19.4326, "longitud": -99.1332, "tipo_de_giro": "straight", "instruccion": "..." }
 *   ]
 * }
 */
class StoreRutaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'color' => ['required', 'string', 'max:50', 'unique:rutas,color'],

            'horarios' => ['sometimes', 'array'],
            'horarios.*.name' => ['required_with:horarios', 'string', 'max:255'],
            'horarios.*.identifier' => ['required_with:horarios', 'string', 'max:255'],

            'puntos_navegacion' => ['sometimes', 'array'],
            'puntos_navegacion.*.latitud' => ['required_with:puntos_navegacion', 'numeric', 'between:-90,90'],
            'puntos_navegacion.*.longitud' => ['required_with:puntos_navegacion', 'numeric', 'between:-180,180'],
            'puntos_navegacion.*.tipo_de_giro' => ['required_with:puntos_navegacion', 'string', 'in:u_turn,right,straight,left'],
            'puntos_navegacion.*.instruccion' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'color.required' => 'El color es obligatorio.',
            'color.unique' => 'Ya existe una ruta con este color.',
            'horarios.*.name.required_with' => 'El nombre del horario es obligatorio.',
            'horarios.*.identifier.required_with' => 'El identificador del horario es obligatorio.',
            'puntos_navegacion.*.latitud.required_with' => 'La latitud es obligatoria para cada punto de navegación.',
            'puntos_navegacion.*.latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'puntos_navegacion.*.longitud.required_with' => 'La longitud es obligatoria para cada punto de navegación.',
            'puntos_navegacion.*.longitud.between' => 'La longitud debe estar entre -180 y 180.',
            'puntos_navegacion.*.tipo_de_giro.in' => 'El tipo de giro debe ser: u_turn, right, straight o left.',
        ];
    }

    /**
     * Retorna errores de validación como JSON.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
