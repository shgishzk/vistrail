@extends('admin.layouts.app')

@section('title', __('Edit Building'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit Building')</strong>
        <a href="{{ route('admin.buildings') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to Buildings')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.buildings.update', $building) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">@lang('Building Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $building->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="url" class="form-label">@lang('URL')</label>
                <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $building->url) }}">
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">@lang('Self Lock Type')</label>
                @foreach($selfLockOptions as $option)
                    <div class="form-check">
                        <input class="form-check-input @error('self_lock_type') is-invalid @enderror" type="radio" name="self_lock_type" id="self_lock_type_{{ $option->value }}" value="{{ $option->value }}" {{ old('self_lock_type', $building->self_lock_type->value) === $option->value ? 'checked' : '' }}>
                        <label class="form-check-label" for="self_lock_type_{{ $option->value }}">
                            {{ $selfLockLabels[$option->value] ?? $option->value }}
                        </label>
                    </div>
                @endforeach
                @error('self_lock_type')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="lat" class="form-label">@lang('Latitude')</label>
                        <input type="number" step="0.0000001" class="form-control @error('lat') is-invalid @enderror" id="lat" name="lat" value="{{ old('lat', $building->lat) }}" required>
                        @error('lat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="lng" class="form-label">@lang('Longitude')</label>
                        <input type="number" step="0.0000001" class="form-control @error('lng') is-invalid @enderror" id="lng" name="lng" value="{{ old('lng', $building->lng) }}" required>
                        @error('lng')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="is_public" class="form-label">@lang('Public')</label>
                <select class="form-select @error('is_public') is-invalid @enderror" id="is_public" name="is_public">
                    <option value="0" {{ old('is_public', (string) $building->is_public) === '0' ? 'selected' : '' }}>@lang('Private')</option>
                    <option value="1" {{ old('is_public', (string) $building->is_public) === '1' ? 'selected' : '' }}>@lang('Public')</option>
                </select>
                @error('is_public')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="memo" class="form-label">@lang('Memo')</label>
                <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="3">{{ old('memo', $building->memo) }}</textarea>
                @error('memo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.buildings') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>

                        <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn-outline-primary" title="@lang('View Rooms')"><i class="cil-list"></i> @lang('View Rooms')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Building')</button>
            </div>
        </form>
    </div>
</div>
@endsection
