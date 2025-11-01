@extends('admin.layouts.app')

@section('title', 'Edit Visit')

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Edit Visit')</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.visits.update', $visit) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.visits.partials.form-fields', [
                'visit' => $visit,
                'users' => $users,
                'areas' => $areas,
            ])

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.visits') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Visit')</button>
            </div>
        </form>
    </div>
</div>
@endsection
