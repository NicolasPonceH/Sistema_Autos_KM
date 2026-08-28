<?php

namespace App\Http\Requests;

use App\Rules\PatenteChilena;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('patente')) {
            $this->merge(['patente' => PatenteChilena::normalizar($this->string('patente'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patente' => ['required', 'string', new PatenteChilena, 'unique:vehiculo,patente'],
            'tipo_codigo' => ['required', 'exists:tipo_vehiculo,codigo'],
            'marca' => ['nullable', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'anio' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'nro_motor' => ['nullable', 'string', 'max:40', 'unique:vehiculo,nro_motor'],
            'nro_chasis' => ['nullable', 'string', 'max:40', 'unique:vehiculo,nro_chasis'],
            'email_contacto' => ['required', 'email', 'max:120'],
        ];
    }
}
