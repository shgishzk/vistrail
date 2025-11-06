<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $definitions = Setting::fieldDefinitions();
        $defaults = Setting::defaults();

        $values = [];

        foreach ($definitions as $key => $definition) {
            $type = $definition['type'] ?? 'string';
            $default = $defaults[$key] ?? null;

            if ($type === 'float') {
                $value = Setting::getFloat($key, (float) $default);
            } elseif ($type === 'int') {
                $value = Setting::getInt($key, (int) $default);
            } else {
                $value = Setting::getValue($key, $default);
            }

            $values[$key] = $this->formatValueForInput($value, $type);
        }

        return view('admin.settings.index', [
            'fields' => $definitions,
            'values' => $values,
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $settings = $validated['settings'] ?? [];

        foreach (Setting::fieldDefinitions() as $key => $definition) {
            if (!array_key_exists($key, $settings)) {
                continue;
            }

            $value = $settings[$key];
            $type = $definition['type'] ?? 'string';

            if ($type === 'float') {
                $value = (float) $value;
            } elseif ($type === 'int') {
                $value = (int) $value;
            }

            Setting::setValue($key, $value);
        }

        return redirect()
            ->route('admin.settings')
            ->with('success', __('Settings have been updated.'));
    }

    private function formatValueForInput(mixed $value, string $type): string
    {
        if ($type === 'float') {
            return rtrim(rtrim(sprintf('%.7F', (float) $value), '0'), '.');
        }

        return (string) $value;
    }
}

