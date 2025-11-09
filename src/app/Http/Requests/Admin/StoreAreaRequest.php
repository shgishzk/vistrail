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
            'boundary_kml' => 'required|string',
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
            'memo' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'number.required' => '区域番号は必須です。',
            'number.unique' => 'この区域番号は既に使用されています。',
            'boundary_kml.required' => '境界KMLは必須です。',
            'center_lat.required' => '地図の中心座標（緯度）が取得できませんでした。画面を再読み込みして再度お試しください。',
            'center_lng.required' => '地図の中心座標（経度）が取得できませんでした。画面を再読み込みして再度お試しください。',
        ];
    }
}
