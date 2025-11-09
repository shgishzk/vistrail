@extends('admin.layouts.app')

@section('title', __('Edit Area'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit Area')</strong>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary btn-sm">
                <i class="cil-arrow-left"></i> @lang('Back to Areas')
            </a>
            <a href="{{ route('admin.areas.print', $area) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="cil-print"></i> @lang('Print Area')
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.areas.update', $area) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="number" class="form-label">@lang('Number')</label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number', $area->number) }}" required>
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">@lang('Area Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $area->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="boundary_kml_upload" class="form-label">@lang('Upload KML')</label>
                <input type="file" class="form-control" id="boundary_kml_upload" accept=".kml">
                <div class="form-text">@lang('Select a .kml file to load into the editor.')</div>
            </div>

            <div class="mb-3">
                <label for="boundary_kml" class="form-label">@lang('Boundary KML')</label>
                <textarea class="form-control @error('boundary_kml') is-invalid @enderror" id="boundary_kml" name="boundary_kml" rows="8" required>{{ old('boundary_kml', $area->boundary_kml) }}</textarea>
                <div class="form-text">
                    @lang('Paste KML data from Google Maps here.')
                </div>
                @error('boundary_kml')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @php
                $currentCenterLat = old('center_lat', $area->center_lat ?? $defaultPosition['lat']);
                $currentCenterLng = old('center_lng', $area->center_lng ?? $defaultPosition['lng']);
            @endphp

            @include('admin.areas.partials.boundary-map-preview', [
                'googleMapsApiKey' => $googleMapsApiKey,
                'initialCenterLat' => $currentCenterLat,
                'initialCenterLng' => $currentCenterLng,
                'defaultPosition' => $defaultPosition,
            ])

            <input type="hidden" id="center_lat" name="center_lat" value="{{ $currentCenterLat }}">
            <input type="hidden" id="center_lng" name="center_lng" value="{{ $currentCenterLng }}">
            @error('center_lat')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
            @error('center_lng')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
            
            <div class="mb-3">
                <label for="memo" class="form-label">@lang('Memo')</label>
                <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="3">{{ old('memo', $area->memo) }}</textarea>
                @error('memo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Area')</button>
            </div>
        </form>
    </div>
</div>
@endsection
