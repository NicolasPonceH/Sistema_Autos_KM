<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLecturaOdometroRequest extends FormRequest
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
            'km' => ['required', 'integer', 'min:0'],
            'es_correccion' => ['sometimes', 'boolean'],
            'observacion' => ['nullable', 'string', 'max:1000', 'required_if:es_correccion,1'],
            'confirmar_salto' => ['sometimes', 'boolean'],
        ];
    }
}
