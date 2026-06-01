<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Request de validación para actualizar una ruta existente.
 *
 * Soporta actualización parcial (PATCH) o completa (PUT).
 * El color de la ruta actual se obtiene del parámetro de la URL.
 *
 * Ejemplos de actualización parcial:
 * - Solo color:              { "color": "#00FF00" }
 * - Solo horarios:           { "horarios": [...] }
 * - Solo puntos_navegacion:  { "puntos_navegacion": [...] }
 * - Combinado:               { "color": "#00FF00", "horarios": [...] }
 */
class UpdateRutaRequest extends FormRequest
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
        // El color actual viene en la URL como route parameter
        $currentColor = $this->route('color');

        return [
            'color' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('rutas', 'color')->where(function ($query) use ($currentColor) {
                    // Ignorar la ruta actual al validar unicidad
                    $query->where('color', '!=', $currentColor);
                }),
            ],

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
            'color.unique' => 'Ya existe otra ruta con este color.',
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
