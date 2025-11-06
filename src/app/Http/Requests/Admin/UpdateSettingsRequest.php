<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.GOOGLE_MAPS_DEFAULT_LAT' => ['required', 'numeric', 'between:-90,90'],
            'settings.GOOGLE_MAPS_DEFAULT_LNG' => ['required', 'numeric', 'between:-180,180'],
            'settings.BUILDING_MAP_HALF_SIDE_KM' => ['required', 'numeric', 'min:0.1', 'max:100'],
            'settings.ROOM_VISITED_ALERT_DAYS' => ['required', 'integer', 'min:0', 'max:3650'],
            'settings.ROOM_NOT_AT_HOME_ALERT_DAYS' => ['required', 'integer', 'min:0', 'max:365'],
            'settings.GOOGLE_MARKER_HAS_LOCK_BACKGROUND' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MARKER_NO_LOCK_BACKGROUND' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MARKER_NO_LOCK_BORDER_COLOR' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'settings.GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY' => ['nullable', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $settings = $this->input('settings', []);
        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                if (preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $trimmed)) {
                    $settings[$key] = strtoupper($trimmed);
                } else {
                    $settings[$key] = $trimmed;
                }
            }
        }

        $this->merge([
            'settings' => $settings,
        ]);
    }
}
