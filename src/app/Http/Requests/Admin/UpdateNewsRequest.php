<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_public' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'content.required' => '本文は必須です。',
            'is_public.required' => '公開状態を選択してください。',
            'is_public.boolean' => '公開状態の値が不正です。',
        ];
    }
}
