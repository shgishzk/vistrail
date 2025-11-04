@extends('admin.layouts.app')

@section('title', __('Areas'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Areas')</strong>
        <a href="{{ route('admin.areas.create') }}" class="btn btn-primary">
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
        
<form action="{{ route('admin.areas') }}" method="GET" class="card card-body mb-4 border-0 shadow-sm">
    <div class="row g-3">
                <div class="col-md-4">
                    <label for="number" class="form-label">@lang('Number')</label>
                    <input type="text" class="form-control" id="number" name="number" value="{{ $filters['number'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="name" class="form-label">@lang('Area Name')</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $filters['name'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="memo" class="form-label">@lang('Memo')</label>
                    <input type="text" class="form-control" id="memo" name="memo" value="{{ $filters['memo'] ?? '' }}">
                </div>
                <div class="col-md-8">
                    <label for="visit_from" class="form-label">@lang('Visit Start Date')</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="visit_from" name="visit_from" value="{{ $filters['visit_from'] ?? '' }}"> 〜
                        <input type="date" class="form-control" id="visit_to" name="visit_to" value="{{ $filters['visit_to'] ?? '' }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="latest_visit_user_search" class="form-label">@lang('Latest Visit User Name')</label>
                    <input type="text"
                        class="form-control js-suggest-input"
                        id="latest_visit_user_search"
                        name="latest_visit_user_search"
                        list="latest-visit-users-list"
                        value="{{ $suggestSelectedDisplay ?? '' }}"
                        autocomplete="off">
                    <input type="hidden" name="latest_visit_user" id="latest_visit_user" value="{{ $filters['latest_visit_user'] ?? '' }}">
                    <datalist id="latest-visit-users-list">
                        @foreach($suggestUsers as $user)
                            <option data-value="{{ $user['id'] }}" value="{{ $user['display'] }}"></option>
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary">@lang('Reset')</a>
                <button type="submit" class="btn btn-primary">@lang('Search')</button>
            </div>
        </form>

        <table class="table table-responsive-sm table-striped">
            <thead>
                <tr>
                    <th>@lang('Number')</th>
                    <th>@lang('Area Name')</th>
                    <th>@lang('Memo')</th>
                    <th>@lang('Created At')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                <tr>
                    <td>{{ $area->number }}</td>
                    <td>
                        <a href="{{ route('admin.areas.edit', $area) }}">
                            {{ $area->name }}
                        </a>
                    </td>
                    <td>{{ Str::limit($area->memo, 50) }}</td>
                    <td>{{ $area->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.areas.visits', $area) }}" class="btn btn-outline-primary" title="@lang('View Visits')">
                            <i class="cil-briefcase"></i>
                        </a>
                        <a href="{{ route('admin.areas.print', $area) }}" target="_blank" class="btn btn-outline-secondary" title="@lang('Print')">
                            <i class="cil-print"></i>
                        </a>
                        <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $area->id }}">
                            <i class="cil-trash"></i>
                        </button>
                        
                        <!-- Delete Confirmation Modal -->
                        <div class="modal fade" id="deleteModal{{ $area->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $area->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel{{ $area->id }}">@lang('Confirm Delete')</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @lang('Are you sure you want to delete area :number?', ['number' => $area->number])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">@lang('Cancel')</button>
                                        <form action="{{ route('admin.areas.destroy', $area) }}" method="POST" style="display: inline;">
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
            {{ $areas->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputId = 'latest_visit_user_search';
    const hiddenId = 'latest_visit_user';
    const datalistId = 'latest-visit-users-list';

    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const datalist = document.getElementById(datalistId);

    if (!input || !hidden || !datalist) {
        return;
    }

    const options = Array.from(datalist.options);

    const findByValue = (value) => options.find((option) => option.value === value.trim());
    const findById = (id) => options.find((option) => option.dataset.value === id);

    const syncFromHidden = () => {
        if (!hidden.value) {
            input.value = '';
            return;
        }

        const option = findById(hidden.value);
        if (option) {
            input.value = option.value;
        }
    };

    const updateHidden = () => {
        const option = findByValue(input.value);
        hidden.value = option ? option.dataset.value : '';
    };

    input.addEventListener('input', updateHidden);
    input.addEventListener('change', updateHidden);

    input.addEventListener('blur', () => {
        const option = findByValue(input.value);
        if (option) {
            hidden.value = option.dataset.value;
            input.value = option.value;
        } else {
            hidden.value = '';
            input.value = '';
        }
    });

    syncFromHidden();
});
</script>
@endpush
