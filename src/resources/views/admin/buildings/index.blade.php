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
                    <td>
                        <span class="badge {{ $building->badgeClass() }}">{{ $building->selfLockLabel() }}</span>
                    <td>
                        <span class="badge {{ $building->publicClass() }}">{{ $building->publicLabel() }}</span>
                    </td>
                    <td>
                        @if ($building->url)
                        <a href="{{ $building->url }}" target="_blank" rel="noopener"><span class="btn btn-sm btn-outline">@lang('See at external site') <i class="cil-external-link"></i></span></a></td>
                        @endif
                    <td>
                        <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn btn-outline-primary" title="@lang('View Rooms')">
                            <i class="cil-list"></i>
                        </a>
                        <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn btn-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $building->id }}">
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
