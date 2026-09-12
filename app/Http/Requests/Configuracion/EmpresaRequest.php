<?php

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del módulo Empresa.
 * La autorización fina la aplica PermisoHelper en el controlador (patrón FlowStock).
 */
class EmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'ruc' => [
                'required',
                'string',
                'max:15',
                Rule::unique('empresa', 'ruc')->ignore($id),
            ],
            'razon_social'     => ['required', 'string', 'max:250'],
            'nombre_comercial' => ['nullable', 'string', 'max:250'],
            'direccion'        => ['nullable', 'string', 'max:250'],
            'telefono'         => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:150'],
            'logo_file'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ruc'              => 'RUC',
            'razon_social'     => 'razón social',
            'nombre_comercial' => 'nombre comercial',
            'direccion'        => 'dirección',
            'telefono'         => 'teléfono',
            'email'            => 'correo',
            'logo_file'        => 'logo',
        ];
    }
}
