@extends('admin.layouts.app')

@section('title', __('Create Visit'))

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Create Visit')</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.visits.store') }}" method="POST">
            @csrf

            @include('admin.visits.partials.form-fields', [
                'visit' => null,
                'users' => $users,
                'areas' => $areas,
            ])

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.visits') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Create Visit')</button>
            </div>
        </form>
    </div>
</div>
@endsection
