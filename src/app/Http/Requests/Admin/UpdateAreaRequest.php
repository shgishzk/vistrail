<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $areaId = $this->route('area');
        
        return [
            'number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->ignore($areaId)
            ],
            'name' => 'nullable|string|max:255',
            'boundary_kml' => 'required|string',
            'memo' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => '区域番号は必須です。',
            'number.unique' => 'この区域番号は既に使用されています。',
            'boundary_kml.required' => '境界KMLは必須です。',
        ];
    }
}
