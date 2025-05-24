@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Users')</strong>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="cil-plus"></i> @lang('Add new')
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <table class="table table-responsive-sm table-striped">
            <thead>
                <tr>
                    <th>@lang('ID')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Created At')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-danger">
                            <i class="cil-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
