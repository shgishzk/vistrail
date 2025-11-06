<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'settings' => $this->buildSettingsPayload(),
        ]);
    }

    public function publicIndex(): JsonResponse
    {
        return response()->json([
            'settings' => $this->buildSettingsPayload(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $settings = $validated['settings'] ?? [];

        foreach (Setting::fieldDefinitions() as $key => $definition) {
            if (!array_key_exists($key, $settings)) {
                continue;
            }

            $value = $settings[$key];

            if (($definition['type'] ?? null) === 'float') {
                $value = (float) $value;
            } elseif (($definition['type'] ?? null) === 'int') {
                $value = (int) $value;
            }

            Setting::setValue($key, $value);
        }

        return response()->json([
            'settings' => $this->buildSettingsPayload(true),
        ]);
    }

    /**
     * @return array<string, int|float>
     */
    private function buildSettingsPayload(bool $fresh = false): array
    {
        if ($fresh) {
            Setting::flushCache();
        }

        $defaults = Setting::defaults();
        $payload = [];

        foreach (Setting::fieldDefinitions() as $key => $definition) {
            $default = $defaults[$key] ?? null;

            if (($definition['type'] ?? null) === 'float') {
                $payload[$key] = Setting::getFloat($key, (float) $default);
            } elseif (($definition['type'] ?? null) === 'int') {
                $payload[$key] = Setting::getInt($key, (int) $default);
            }
        }

        return $payload;
    }
}

