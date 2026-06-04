<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoutingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origen' => ['required', 'array'],
            'origen.latitud' => ['required', 'numeric', 'between:-90,90'],
            'origen.longitud' => ['required', 'numeric', 'between:-180,180'],

            'destino' => ['required', 'array'],
            'destino.latitud' => ['required', 'numeric', 'between:-90,90'],
            'destino.longitud' => ['required', 'numeric', 'between:-180,180'],

            'opciones' => ['sometimes', 'array'],
            'opciones.max_trasbordos' => ['sometimes', 'integer', 'between:0,5'],
            'opciones.max_caminata_m' => ['sometimes', 'integer', 'between:100,2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'origen.required' => 'El origen es obligatorio.',
            'origen.latitud.required' => 'La latitud del origen es obligatoria.',
            'origen.latitud.between' => 'La latitud del origen debe estar entre -90 y 90.',
            'origen.longitud.required' => 'La longitud del origen es obligatoria.',
            'origen.longitud.between' => 'La longitud del origen debe estar entre -180 y 180.',
            'destino.required' => 'El destino es obligatorio.',
            'destino.latitud.required' => 'La latitud del destino es obligatoria.',
            'destino.latitud.between' => 'La latitud del destino debe estar entre -90 y 90.',
            'destino.longitud.required' => 'La longitud del destino es obligatoria.',
            'destino.longitud.between' => 'La longitud del destino debe estar entre -180 y 180.',
            'opciones.max_trasbordos.between' => 'El maximo de trasbordos debe estar entre 0 y 5.',
            'opciones.max_caminata_m.between' => 'La caminata maxima debe estar entre 100 y 2000 metros.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validacion.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
