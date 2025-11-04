@extends('admin.layouts.app')

@section('title', __('Edit Group'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit Group')</strong>
        <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">
            <i class="cil-arrow-left"></i> @lang('Back to Groups')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.groups.update', $group) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">@lang('Group Name')</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $group->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label for="is_public" class="form-label">@lang('Public')</label>
                <select id="is_public" name="is_public" class="form-select @error('is_public') is-invalid @enderror">
                    @php $currentPublic = old('is_public', $group->is_public ? '1' : '0'); @endphp
                    <option value="1" {{ $currentPublic === '1' ? 'selected' : '' }}>@lang('Public')</option>
                    <option value="0" {{ $currentPublic === '0' ? 'selected' : '' }}>@lang('Private')</option>
                </select>
                @error('is_public')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Group')</button>
            </div>
        </form>
    </div>
</div>
@endsection
