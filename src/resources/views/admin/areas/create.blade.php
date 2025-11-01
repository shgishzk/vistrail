@extends('admin.layouts.app')

@section('title', __('Create Area'))

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Create Area')</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.areas.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="number" class="form-label">@lang('Number')</label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number') }}" required>
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">@lang('Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="boundary_kml" class="form-label">@lang('Boundary KML')</label>
                <textarea class="form-control @error('boundary_kml') is-invalid @enderror" id="boundary_kml" name="boundary_kml" rows="8" required>{{ old('boundary_kml') }}</textarea>
                <div class="form-text">
                    @lang('Paste KML data from Google Maps here.')
                </div>
                @error('boundary_kml')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="memo" class="form-label">@lang('Memo')</label>
                <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="3">{{ old('memo') }}</textarea>
                @error('memo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Create Area')</button>
            </div>
        </form>
    </div>
</div>
@endsection
