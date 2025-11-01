@extends('admin.layouts.app')

@section('title', __('Visits'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Visits')</strong>
        <a href="{{ route('admin.visits.create') }}" class="btn btn-primary">
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
                    <th>@lang('User')</th>
                    <th>@lang('Area')</th>
                    <th>@lang('Start Date')</th>
                    <th>@lang('End Date')</th>
                    <th>@lang('Memo')</th>
                    <th>@lang('Created At')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($visits as $visit)
                <tr>
                    <td>
                        {{ $visit->user?->name }}
                        <small class="text-muted d-block">{{ $visit->user?->email }}</small>
                    </td>
                    <td>
                        @if($visit->area)
                            <a href="{{ route('admin.areas.edit', $visit->area) }}">
                                {{ $visit->area->number }}
                            </a>
                            @if($visit->area->name)
                                <small class="text-muted d-block">{{ $visit->area->name }}</small>
                            @endif
                        @else
                            <span class="text-muted">@lang('Area not found')</span>
                        @endif
                    </td>
                    <td>{{ optional($visit->start_date)->format('Y-m-d') }}</td>
                    <td>{{ optional($visit->end_date)->format('Y-m-d') }}</td>
                    <td>{{ Str::limit($visit->memo, 50) }}</td>
                    <td>{{ $visit->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.visits.edit', $visit) }}" class="btn btn-sm btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $visit->id }}">
                            <i class="cil-trash"></i>
                        </button>

                        <div class="modal fade" id="deleteModal{{ $visit->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $visit->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $visit->id }}">@lang('Confirm Delete')</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @lang('Are you sure you want to delete this visit record?')
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">@lang('Cancel')</button>
                                        <form action="{{ route('admin.visits.destroy', $visit) }}" method="POST" style="display: inline;">
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
            {{ $visits->links() }}
        </div>
    </div>
</div>
@endsection
