@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">@lang('Admin Dashboard')</div>
            <div class="card-body">
                <p>@lang('Welcome to the Vistrail Admin Dashboard')</p>
            </div>
        </div>
    </div>
</div>
@endsection
