<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupBuildingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buildings' => ['array'],
            'buildings.*.id' => ['nullable', 'integer', Rule::exists('buildings', 'id')],
            'buildings.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
