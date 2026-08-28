<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * La patente es la clave primaria: no se edita desde este formulario.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patente = $this->route('vehiculo')->patente;

        return [
            'tipo_codigo' => ['required', 'exists:tipo_vehiculo,codigo'],
            'marca' => ['nullable', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'anio' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'nro_motor' => ['nullable', 'string', 'max:40', Rule::unique('vehiculo', 'nro_motor')->ignore($patente, 'patente')],
            'nro_chasis' => ['nullable', 'string', 'max:40', Rule::unique('vehiculo', 'nro_chasis')->ignore($patente, 'patente')],
            'email_contacto' => ['required', 'email', 'max:120'],
        ];
    }
}
