<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string', 
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'max:20',
                'regex:/[A-Z]/',    // At least one uppercase letter
                'regex:/[a-z]/',    // At least one lowercase letter
                'regex:/[0-9]/',    // At least one number
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.regex' => 'パスワードは大文字、小文字、数字をすべて含む必要があります。',
        ];
    }
}
