<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:areas',
            'name' => 'nullable|string|max:255',
            'boundary_geojson' => 'required|string',
            'memo' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => 'エリア番号は必須です。',
            'number.unique' => 'このエリア番号は既に使用されています。',
            'boundary_geojson.required' => '境界GeoJSONは必須です。',
        ];
    }
}
