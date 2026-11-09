<?php

namespace App\Http\Requests;

use App\Enums\TipoDato;
use App\Models\Requerimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuardarRequerimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clave'       => ['required', 'string', 'in:' . implode(',', array_keys(Requerimiento::CLAVES))],
            'operador'    => ['required', 'string', 'in:>=,<=,=,!=,in'],
            'valor'       => ['required', 'string', 'max:120'],
            'obligatorio' => ['nullable', 'boolean'],
            'origen'      => ['required', 'string', 'in:cliente,equipo,ia'],
        ];
    }

    /**
     * El tipo no lo elige la persona: lo fija la clave. Aquí se comprueba que
     * el operador tenga sentido para ese tipo, que es la validación cruzada
     * que el enunciado pide rechazar sin guardar nada.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $clave = $this->input('clave');
            $tipo  = Requerimiento::TIPO_POR_CLAVE[$clave] ?? null;

            if ($tipo === null) {
                return;
            }

            if (! TipoDato::from($tipo)->admite($this->input('operador'))) {
                $v->errors()->add(
                    'operador',
                    "El requisito «{$clave}» es de tipo {$tipo} y no admite el operador "
                    . "«{$this->input('operador')}». Operadores válidos: "
                    . implode(', ', TipoDato::from($tipo)->operadores()) . '.'
                );
            }

            if ($tipo === 'numero' && ! is_numeric($this->input('valor'))) {
                $v->errors()->add('valor', "El requisito «{$clave}» espera un número.");
            }
        });
    }

    public function tipoDeLaClave(): string
    {
        return Requerimiento::TIPO_POR_CLAVE[$this->input('clave')];
    }
}
