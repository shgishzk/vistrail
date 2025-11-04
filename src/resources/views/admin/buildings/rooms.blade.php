@extends('admin.layouts.app')

@section('title', __('Rooms in :building', ['building' => $building->name]))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Rooms in :building', ['building' => $building->name])</strong>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.buildings.rooms.create', $building) }}" class="btn btn-primary btn-sm">
                <i class="cil-plus"></i> @lang('Add multiple rooms')
            </a>
            <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn-outline-primary btn-sm">
                <i class="cil-arrow-left"></i> {{ __('Back to :building', ['building' => $building->name]) }}
            </a>
            <a href="{{ route('admin.buildings') }}" class="btn btn-outline-secondary btn-sm">
                <i class="cil-arrow-left"></i> @lang('Back to Buildings')
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @lang('Validation failed. Please check the inputs highlighted below.')
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="alert alert-info" role="alert">
            @lang('Total rooms: :count', ['count' => number_format($totalRooms)])
        </div>

        <form action="{{ route('admin.buildings.rooms.update', $building) }}" method="POST">
            @csrf
            @method('PUT')

            <table class="table table-responsive-sm table-striped">
                <thead>
                    <tr>
                        <th>@lang('Room Number')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Created At')</th>
                        <th>@lang('Updated At')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                        @php
                            $numberField = "rooms.{$room->id}.number";
                            $statusField = "rooms.{$room->id}.status";
                            $numberValue = old($numberField, $room->number);
                            $statusValue = old($statusField, $room->status->value);
                        @endphp
                        <tr>
                            <td>
                                <input
                                    type="text"
                                    name="rooms[{{ $room->id }}][number]"
                                    value="{{ $numberValue }}"
                                    class="form-control {{ $errors->has($numberField) ? 'is-invalid' : '' }}"
                                >
                                @if($errors->has($numberField))
                                    <div class="invalid-feedback">{{ $errors->first($numberField) }}</div>
                                @endif
                            </td>
                            <td>
                                <select
                                    name="rooms[{{ $room->id }}][status]"
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
                            <td>{{ $room->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $room->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-4">
                {{ $rooms->links() }}
                <a href="{{ route('admin.buildings.edit', $building) }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">
                    @lang('Save')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
