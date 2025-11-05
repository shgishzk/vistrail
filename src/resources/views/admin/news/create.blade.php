@extends('admin.layouts.app')

@section('title', __('Create News'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Create News')</strong>
        <a href="{{ route('admin.news') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to News')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.news.store') }}" method="POST">
            @csrf

            @include('admin.news.partials.form-fields')

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.news') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Create News')</button>
            </div>
        </form>
    </div>
</div>
@endsection
