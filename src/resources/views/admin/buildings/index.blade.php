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

        <form action="{{ route('admin.buildings') }}" method="GET" class="card card-body mb-4 border-0 shadow-sm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="name" class="form-label">@lang('Building Name')</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $filters['name'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="self_lock_type" class="form-label">@lang('Self Lock Type')</label>
                    <select class="form-select" id="self_lock_type" name="self_lock_type">
                        <option value="">@lang('All')</option>
                        @foreach($selfLockOptions as $option)
                            <option value="{{ $option->value }}" {{ ($filters['self_lock_type'] ?? '') === $option->value ? 'selected' : '' }}>
                                {{ $selfLockLabels[$option->value] ?? $option->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="is_public" class="form-label">@lang('Public')</label>
                    <select class="form-select" id="is_public" name="is_public">
                        <option value="">@lang('All')</option>
                        <option value="1" {{ ($filters['is_public'] ?? '') === '1' ? 'selected' : '' }}>@lang('Public')</option>
                        <option value="0" {{ ($filters['is_public'] ?? '') === '0' ? 'selected' : '' }}>@lang('Private')</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.buildings') }}" class="btn btn-outline-secondary">@lang('Reset')</a>
                <button type="submit" class="btn btn-primary">@lang('Search')</button>
            </div>
        </form>

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
                    </td>
                    <td>
                        <span class="badge {{ $building->publicClass() }}">{{ $building->publicLabel() }}</span>
                    </td>
                    <td>
                        @if ($building->url)
                            <a href="{{ $building->url }}" target="_blank" rel="noopener">
                                <span class="btn btn-sm btn-outline">@lang('See at external site') <i class="cil-external-link"></i></span>
                            </a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn btn-outline-primary" title="@lang('View Rooms')">
                            <i class="cil-list"></i>
                        </a>
                        <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn btn-outline-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn btn-outline-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $building->id }}">
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
                                            <button type="submit" class="btn btn-outline-danger">@lang('Delete')</button>
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
