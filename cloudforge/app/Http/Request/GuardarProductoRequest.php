<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('producto')?->id;

        return [
            'proveedor_id'     => ['required', 'integer', 'exists:proveedores,id'],
            'tipo_producto_id' => ['required', 'integer', 'exists:tipos_producto,id'],
            'nombre'           => ['required', 'string', 'min:3', 'max:120'],
            'sku'              => ['required', 'string', 'max:40', Rule::unique('productos', 'sku')->ignore($id)],
            'descripcion'      => ['nullable', 'string', 'max:1000'],
            'modalidad'        => ['required', 'in:mensual,unico,consumo'],
            'tarifa'           => ['required', 'numeric', 'min:0', 'max:100000'],
            'unidad'           => ['required', 'string', 'max:30'],
            // El tope sólo tiene sentido en la modalidad por consumo.
            'tope_mensual'     => ['nullable', 'numeric', 'min:0', 'required_if:modalidad,consumo'],
            'activo'           => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique'            => 'Ya existe un producto con ese SKU.',
            'tope_mensual.required_if' => 'La modalidad por consumo necesita un tope mensual.',
        ];
    }
}
