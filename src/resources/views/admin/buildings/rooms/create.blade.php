@extends('admin.layouts.app')

@section('title', __('Add rooms to :building', ['building' => $building->name]))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Add rooms to :building', ['building' => $building->name])</strong>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn-outline-secondary btn-sm">
                <i class="cil-arrow-left"></i> @lang('Back to room list')
            </a>
            <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn-outline-primary btn-sm">
                <i class="cil-arrow-left"></i> {{ __('Back to :building', ['building' => $building->name]) }}
            </a>
        </div>
    </div>
    <div class="card-body">
        @if ($errors->has('rooms'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errors->first('rooms') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.buildings.rooms.store', $building) }}" method="POST">
            @csrf

            <p class="text-muted mb-3">
                @lang('You can register up to :count rooms at once.', ['count' => $maxRows])
            </p>

            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>@lang('Room Number')</th>
                        <th>@lang('Status')</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($index = 0; $index < $maxRows; $index++)
                        @php
                            $numberField = "rooms.$index.number";
                            $statusField = "rooms.$index.status";
                            $numberValue = old($numberField, '');
                            $statusValue = old($statusField, \App\Enums\RoomStatus::UNVISITED->value);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <input
                                    type="text"
                                    name="rooms[{{ $index }}][number]"
                                    value="{{ $numberValue }}"
                                    tabindex="{{ $index + 1 }}"
                                    class="form-control {{ $errors->has($numberField) ? 'is-invalid' : '' }}"
                                >
                                @if($errors->has($numberField))
                                    <div class="invalid-feedback">{{ $errors->first($numberField) }}</div>
                                @endif
                            </td>
                            <td>
                                <select
                                    name="rooms[{{ $index }}][status]"
                                    class="form-select {{ $errors->has($statusField) ? 'is-invalid' : '' }}"
                                >
                                    @foreach($statusOptions as $status)
                                        <option value="{{ $status->value }}" {{ $statusValue === $status->value ? 'selected' : '' }}>
                                            {{ \App\Enums\RoomStatus::labels()[$status->value] ?? $status->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has($statusField))
                                    <div class="invalid-feedback">{{ $errors->first($statusField) }}</div>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.buildings.rooms', $building) }}" class="btn btn-outline-secondary">
                    @lang('Cancel')
                </a>
                <button type="submit" class="btn btn-primary">
                    @lang('Bulk register')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
