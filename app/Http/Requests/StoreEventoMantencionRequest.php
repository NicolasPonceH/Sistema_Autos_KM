<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventoMantencionRequest extends FormRequest
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
            'plan_id' => ['required', 'exists:plan_mantencion,id'],
            'km_evento' => ['required', 'integer', 'min:0'],
            'fecha' => ['required', 'date'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'taller' => ['nullable', 'string', 'max:100'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
