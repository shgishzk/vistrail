@php
    use Illuminate\Support\Str;
@endphp

@extends('admin.layouts.app')

@section('title', __('Settings'))

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>@lang('Settings')</strong>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                @foreach ($groups as $group)
                    <div class="mb-5">
                        <h5 class="fw-semibold mb-3">{{ $group['label'] }}</h5>

                        @if (!empty($group['sections']))
                            @foreach ($group['sections'] as $section)
                                <div class="mb-4">
                                    @if (!empty($section['label']))
                                        <h6 class="fw-semibold mb-3">{{ $section['label'] }}</h6>
                                    @endif
                                    <div class="row g-4">
                                        @foreach ($section['fields'] as $fieldRef)
                                            @php
                                                $field = $fields[$fieldRef['key']] ?? null;
                                                $key = $field['key'] ?? $fieldRef['key'];
                                                $inputId = 'setting-' . Str::kebab($key);
                                                $inputConfig = $field['input'] ?? [];
                                                $inputType = $inputConfig['type'] ?? 'text';
                                                unset($inputConfig['type']);
                                                $value = old('settings.' . $key, $field['value'] ?? '');
                                                $isColor = $inputType === 'color';
                                                $inputClasses = $isColor
                                                    ? 'form-control form-control-color w-100'
                                                    : 'form-control';
                                                @endphp
                                                @if ($field)
                                                    <div class="col-md-6 col-xl-4">
                                                        <div class="mb-3">
                                                            <label for="{{ $inputId }}" class="form-label fw-semibold">
                                                                {{ $field['label'] ?? $key }}
                                                            </label>
                                                            <input
                                                                id="{{ $inputId }}"
                                                                name="settings[{{ $key }}]"
                                                                type="{{ $inputType }}"
                                                                value="{{ $value }}"
                                                                class="{{ $inputClasses }} {{ $errors->has('settings.' . $key) ? 'is-invalid' : '' }}"
                                                                @if ($isColor)
                                                                    data-color-output="#{{ $inputId }}-value"
                                                                    title="{{ $field['label'] ?? $key }}"
                                                                @endif
                                                                @foreach ($inputConfig as $attr => $attrValue)
                                                                    {{ $attr }}="{{ $attrValue }}"
                                                                @endforeach
                                                            />
                                                            @if ($isColor)
                                                                <div class="form-text mt-1">
                                                                    <span class="text-muted">現在値:</span>
                                                                    <span id="{{ $inputId }}-value" class="ms-1 font-monospace">{{ $value }}</span>
                                                                </div>
                                                            @endif
                                                            @if (!empty($field['description']))
                                                                <small class="text-muted d-block mt-1">
                                                                    {{ $field['description'] }}
                                                                </small>
                                                            @endif
                                                        @error('settings.' . $key)
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if (!empty($group['fields']))
                            <div class="row g-4">
                                @foreach ($group['fields'] as $fieldRef)
                                    @php
                                        $field = $fields[$fieldRef['key']] ?? null;
                                        $key = $field['key'] ?? $fieldRef['key'];
                                        $inputId = 'setting-' . Str::kebab($key);
                                        $inputConfig = $field['input'] ?? [];
                                        $inputType = $inputConfig['type'] ?? 'text';
                                        unset($inputConfig['type']);
                                        $value = old('settings.' . $key, $field['value'] ?? '');
                                        $isColor = $inputType === 'color';
                                        $inputClasses = $isColor
                                            ? 'form-control form-control-color w-100'
                                            : 'form-control';
                                    @endphp
                                    @if ($field)
                                        <div class="col-md-6 col-xl-4">
                                            <div class="mb-3">
                                                <label for="{{ $inputId }}" class="form-label fw-semibold">
                                                    {{ $field['label'] ?? $key }}
                                                </label>
                                                <input
                                                    id="{{ $inputId }}"
                                                    name="settings[{{ $key }}]"
                                                    type="{{ $inputType }}"
                                                    value="{{ $value }}"
                                                    class="{{ $inputClasses }} {{ $errors->has('settings.' . $key) ? 'is-invalid' : '' }}"
                                                    @if ($isColor)
                                                        data-color-output="#{{ $inputId }}-value"
                                                        title="{{ $field['label'] ?? $key }}"
                                                    @endif
                                                    @foreach ($inputConfig as $attr => $attrValue)
                                                        {{ $attr }}="{{ $attrValue }}"
                                                    @endforeach
                                                />
                                                @if ($isColor)
                                                    <div class="form-text mt-1">
                                                        <span class="text-muted">現在値:</span>
                                                        <span id="{{ $inputId }}-value" class="ms-1 font-monospace">{{ $value }}</span>
                                                    </div>
                                                @endif
                                                @if (!empty($field['description']))
                                                    <small class="text-muted d-block mt-1">
                                                        {{ $field['description'] }}
                                                    </small>
                                                @endif
                                                @error('settings.' . $key)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="cil-save"></i> @lang('Save Settings')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const colorInputs = document.querySelectorAll('input[type="color"][data-color-output]');
            colorInputs.forEach((input) => {
                const targetSelector = input.getAttribute('data-color-output');
                const target = targetSelector ? document.querySelector(targetSelector) : null;
                if (!target) {
                    return;
                }
                const updateValue = () => {
                    target.textContent = input.value;
                };
                input.addEventListener('input', updateValue);
                input.addEventListener('change', updateValue);
                updateValue();
            });
        });
    </script>
@endpush
