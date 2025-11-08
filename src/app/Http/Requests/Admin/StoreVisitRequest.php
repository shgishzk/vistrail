<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'area_id' => 'required|integer|exists:areas,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'memo' => 'nullable|string',
            'status' => 'prohibited',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーは必須です。',
            'user_id.exists' => '選択したユーザーが存在しません。',
            'area_id.required' => '区域は必須です。',
            'area_id.exists' => '選択した区域が存在しません。',
            'start_date.required' => '訪問開始日は必須です。',
            'start_date.date' => '訪問開始日は日付形式で入力してください。',
            'end_date.date' => '訪問終了日は日付形式で入力してください。',
            'end_date.after_or_equal' => '訪問終了日は訪問開始日以降の日付を指定してください。',
            'status.prohibited' => '訪問作成時に状態を指定することはできません。',
        ];
    }
}
