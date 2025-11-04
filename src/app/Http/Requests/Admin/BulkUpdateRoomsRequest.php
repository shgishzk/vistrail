<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateRoomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rooms' => ['sometimes', 'array'],
            'rooms.*.number' => ['required', 'string', 'max:10'],
            'rooms.*.status' => ['required', Rule::in(array_map(fn ($case) => $case->value, RoomStatus::cases()))],
        ];
    }
}
