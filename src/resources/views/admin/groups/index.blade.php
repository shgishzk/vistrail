@extends('admin.layouts.app')

@section('title', __('Groups'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Groups')</strong>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">
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
                    <th>@lang('Group Name')</th>
                    <th>@lang('Public')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>
                        <span class="badge {{ $group->is_public ? 'bg-info' : 'bg-secondary' }}">
                            {{ $group->is_public ? __('Public') : __('Private') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.groups.buildings.edit', $group) }}" class="btn btn btn-outline-primary" title="@lang('Manage Buildings')">
                            <i class="cil-building"></i>
                        </a>
                        <a href="{{ route('admin.groups.edit', $group) }}" class="btn btn btn-outline-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button
                            type="button"
                            class="btn btn btn-outline-danger"
                            data-coreui-toggle="modal"
                            data-coreui-target="#deleteGroupModal{{ $group->id }}"
                        >
                            <i class="cil-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $groups->links() }}
        </div>
    </div>
</div>

@foreach($groups as $group)
    <div class="modal fade" id="deleteGroupModal{{ $group->id }}" tabindex="-1" aria-labelledby="deleteGroupModalLabel{{ $group->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupModalLabel{{ $group->id }}">@lang('Confirm Delete')</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @lang('Are you sure you want to delete group :name?', ['name' => $group->name])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">@lang('Cancel')</button>
                    <form action="{{ route('admin.groups.destroy', $group) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">@lang('Delete')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
