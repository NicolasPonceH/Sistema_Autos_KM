<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanMantencionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:60'],
            'intervalo_km' => ['required', 'integer', 'min:1'],
            'umbral_aviso' => ['required', 'integer', 'min:0'],
            'intervalo_meses' => ['nullable', 'integer', 'min:1'],
            'umbral_aviso_dias' => ['nullable', 'integer', 'min:0'],
            'tipo_codigo' => ['nullable', 'exists:tipo_vehiculo,codigo'],
        ];
    }
}
