@extends('admin.layouts.app')

@section('title', __('Buildings'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Buildings')</strong>
        <a href="{{ route('admin.buildings.create') }}" class="btn btn-primary">
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
                    <th>@lang('Building Name')</th>
                    <th>@lang('Self Lock Type')</th>
                    <th>@lang('Public')</th>
                    <th>@lang('URL')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($buildings as $building)
                <tr>
                    <td>{{ $building->name }}</td>
                    <td>{{ \App\Enums\SelfLockType::labels()[$building->self_lock_type->value] ?? $building->self_lock_type->value }}</td>
                    <td>
                        @if($building->is_public)
                            <span class="badge bg-success">@lang('Public')</span>
                        @else
                            <span class="badge bg-secondary">@lang('Private')</span>
                        @endif
                    </td>
                    <td><a href="{{ $building->url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($building->url, 40) }}</a></td>
                    <td>
                        <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn-sm btn-outline-primary" title="@lang('View Rooms')">
                            <i class="cil-list"></i>
                        </a>
                        <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn-sm btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $building->id }}">
                            <i class="cil-trash"></i>
                        </button>

                        <div class="modal fade" id="deleteModal{{ $building->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $building->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $building->id }}">@lang('Confirm Delete')</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @lang('Are you sure you want to delete building :name?', ['name' => $building->name])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">@lang('Cancel')</button>
                                        <form action="{{ route('admin.buildings.destroy', $building) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">@lang('Delete')</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $buildings->links() }}
        </div>
    </div>
</div>
@endsection
