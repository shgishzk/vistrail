@extends('admin.layouts.app')

@section('title', __('Create Group'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Create Group')</strong>
        <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">
            <i class="cil-arrow-left"></i> @lang('Back to Groups')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.groups.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">@lang('Group Name')</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label for="is_public" class="form-label">@lang('Public')</label>
                <select id="is_public" name="is_public" class="form-select @error('is_public') is-invalid @enderror">
                    <option value="1" {{ old('is_public', '1') === '1' ? 'selected' : '' }}>@lang('Public')</option>
                    <option value="0" {{ old('is_public') === '0' ? 'selected' : '' }}>@lang('Private')</option>
                </select>
                @error('is_public')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Create Group')</button>
            </div>
        </form>
    </div>
</div>
@endsection
