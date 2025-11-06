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

                <div class="row g-4">
                    @foreach ($fields as $key => $field)
                        @php
                            $inputId = 'setting-' . Str::kebab($key);
                            $inputConfig = $field['input'] ?? [];
                            $inputType = $inputConfig['type'] ?? 'text';
                            unset($inputConfig['type']);
                            $value = old('settings.' . $key, $values[$key] ?? '');
                        @endphp
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
                                    @foreach ($inputConfig as $attr => $attrValue)
                                        {{ $attr }}="{{ $attrValue }}"
                                    @endforeach
                                    class="form-control {{ $errors->has('settings.' . $key) ? 'is-invalid' : '' }}"
                                    inputmode="{{ $inputType === 'number' ? 'decimal' : 'text' }}"
                                />
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
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="cil-save"></i> @lang('Save Settings')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
