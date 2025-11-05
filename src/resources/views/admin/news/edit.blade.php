@extends('admin.layouts.app')

@section('title', __('Edit News'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit News')</strong>
        <a href="{{ route('admin.news') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to News')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.news.update', $news) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.news.partials.form-fields', ['news' => $news])

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.news') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update News')</button>
            </div>
        </form>
    </div>
</div>
@endsection
