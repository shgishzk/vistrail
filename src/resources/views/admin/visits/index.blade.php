@extends('admin.layouts.app')

@php
    use App\Enums\VisitStatus;
    $filters = $filters ?? [];
@endphp

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

        <form action="{{ route('admin.visits') }}" method="GET" class="card card-body mb-4 border-0 shadow-sm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">@lang('User')</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">@lang('All')</option>
                        @foreach($filterUsers as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                                {{ $user->name }} @if($user->email) ({{ $user->email }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="area_id" class="form-label">@lang('Area')</label>
                    <select name="area_id" id="area_id" class="form-select">
                        <option value="">@lang('All')</option>
                        @foreach($filterAreas as $areaOption)
                            <option value="{{ $areaOption->id }}" @selected(($filters['area_id'] ?? '') == $areaOption->id)>
                                {{ $areaOption->number }} @if($areaOption->name) - {{ $areaOption->name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">@lang('Status')</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">@lang('All')</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="start_from" class="form-label">@lang('Visit Start Date')</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control" id="start_from" name="start_from" value="{{ $filters['start_from'] ?? '' }}">
                        <span class="text-muted">-</span>
                        <input type="date" class="form-control" id="start_to" name="start_to" value="{{ $filters['start_to'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.visits') }}" class="btn btn-outline-secondary">@lang('Reset')</a>
                <button type="submit" class="btn btn-primary">@lang('Search')</button>
            </div>
        </form>

        <table class="table table-responsive-sm table-striped">
            <thead>
                <tr>
                    <th>@lang('User')</th>
                    <th>@lang('Area')</th>
                    <th>@lang('Start Date')</th>
                    <th>@lang('End Date')</th>
                    <th>@lang('Memo')</th>
                    <th>@lang('Status')</th>
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
                    <td>
                        @php
                            $status = $visit->status instanceof VisitStatus
                                ? $visit->status
                                : VisitStatus::from($visit->status ?? VisitStatus::default()->value);
                        @endphp
                        <span class="badge {{ $status->badgeClass() }}">
                            {{ $status->label() }}
                        </span>
                    </td>
                    <td>{{ $visit->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.visits.edit', $visit) }}" class="btn btn-outline-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $visit->id }}">
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
            {{ $visits->links() }}
        </div>
    </div>
</div>
@endsection
