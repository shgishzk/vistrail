@extends('admin.layouts.app')

@section('title', __('Create Admin'))

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Create Admin')</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">@lang('Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">@lang('Email')</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">@lang('Password')</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                <div class="form-text">@lang('Password must be 6-20 characters and include uppercase, lowercase, and numbers.')</div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.admins') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Create Admin')</button>
            </div>
        </form>
    </div>
</div>
@endsection
