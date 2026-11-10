<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirFicheroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // el tipo y el tamaño se comprueban aqui en el servidor, porque
            // el accept del input en el HTML se puede saltar facilmente
            'documento' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,txt,md', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.required' => 'Selecciona un archivo.',
            'documento.mimes'    => 'Sólo se admiten PDF, PNG, JPG, TXT o MD.',
            'documento.max'      => 'El archivo no puede superar los 2 MB.',
        ];
    }
}
