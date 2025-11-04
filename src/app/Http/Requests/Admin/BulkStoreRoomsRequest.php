<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreRoomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rooms' => ['required', 'array', 'max:50'],
            'rooms.*.number' => ['nullable', 'string', 'max:10'],
            'rooms.*.status' => ['nullable', Rule::in(array_map(fn ($case) => $case->value, RoomStatus::cases()))],
        ];
    }
}
