<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarProyectoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // en UT2 todavía no hay autenticación ni permisos
    }

    public function rules(): array
    {
        return [
            'nombre'              => ['required', 'string', 'min:3', 'max:120'],
            'cliente'             => ['required', 'string', 'min:3', 'max:120'],
            'descripcion'         => ['nullable', 'string', 'max:2000'],
            // Se pide en euros y se convierte a céntimos en el controlador.
            'presupuesto_max'     => ['required', 'numeric', 'min:1', 'max:1000000'],
            'region_preferida_id' => ['nullable', 'integer', 'exists:regiones,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'          => 'El proyecto necesita un nombre.',
            'cliente.required'         => 'Indica para qué cliente es el proyecto.',
            'presupuesto_max.required' => 'Indica el presupuesto máximo mensual en euros.',
            'presupuesto_max.min'      => 'El presupuesto máximo debe ser de al menos 1 €.',
            'region_preferida_id.exists' => 'La región seleccionada no existe.',
        ];
    }

    /** Importe en céntimos, que es como se guarda. */
    public function presupuestoCentimos(): int
    {
        return (int) round(((float) $this->input('presupuesto_max')) * 100);
    }
}
