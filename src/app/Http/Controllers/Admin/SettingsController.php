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

        [$groups, $fields] = $this->structureFields($definitions, $values);

        return view('admin.settings.index', [
            'groups' => $groups,
            'fields' => $fields,
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

    /**
     * @param array<string, array<string, mixed>> $definitions
     * @param array<string, string> $values
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, array<string, mixed>>}
     */
    private function structureFields(array $definitions, array $values): array
    {
        $groups = [];
        $fields = [];

        foreach ($definitions as $key => $definition) {
            $fields[$key] = [
                'key' => $key,
                'label' => $definition['label'] ?? $key,
                'description' => $definition['description'] ?? '',
                'input' => $definition['input'] ?? [],
                'value' => $values[$key] ?? '',
            ];

            $groupKey = $definition['group'] ?? 'general';
            $groupLabel = $definition['group_label'] ?? __('Settings');
            $groupOrder = $definition['group_order'] ?? 100;

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'key' => $groupKey,
                    'label' => $groupLabel,
                    'order' => $groupOrder,
                    'sections' => [],
                    'fields' => [],
                ];
            }

            $fieldRef = [
                'key' => $key,
                'order' => $definition['field_order'] ?? 100,
            ];

            $sectionKey = $definition['section'] ?? null;
            if ($sectionKey) {
                if (!isset($groups[$groupKey]['sections'][$sectionKey])) {
                    $groups[$groupKey]['sections'][$sectionKey] = [
                        'key' => $sectionKey,
                        'label' => $definition['section_label'] ?? null,
                        'order' => $definition['section_order'] ?? 100,
                        'fields' => [],
                    ];
                }

                $groups[$groupKey]['sections'][$sectionKey]['fields'][] = $fieldRef;
            } else {
                $groups[$groupKey]['fields'][] = $fieldRef;
            }
        }

        $groupList = array_values($groups);
        usort($groupList, static fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        foreach ($groupList as &$group) {
            if (!empty($group['sections'])) {
                $sections = array_values($group['sections']);
                usort($sections, static fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

                foreach ($sections as &$section) {
                    usort(
                        $section['fields'],
                        static fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0)
                    );
                }

                $group['sections'] = $sections;
            } else {
                $group['sections'] = [];
            }

            if (!empty($group['fields'])) {
                usort(
                    $group['fields'],
                    static fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0)
                );
            } else {
                $group['fields'] = [];
            }
        }
        unset($group);

        return [$groupList, $fields];
    }
}
