<?php

namespace App\Http\Requests\Admin;

use App\Enums\SelfLockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'url' => 'required|url|max:255',
            'self_lock_type' => ['required', Rule::in(array_map(fn ($case) => $case->value, SelfLockType::cases()))],
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'is_public' => 'required|in:0,1',
            'memo' => 'nullable|string',
        ];
    }
}
