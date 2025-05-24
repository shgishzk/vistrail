@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Users')</strong>
    </div>
    <div class="card-body">
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
