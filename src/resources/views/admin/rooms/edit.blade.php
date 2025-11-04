@extends('admin.layouts.app')

@section('title', __('Edit Room'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit Room')</strong>
        <a href="{{ route('admin.rooms') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to Rooms')
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="building_id" class="form-label">@lang('Building')</label>
                <select class="form-select @error('building_id') is-invalid @enderror" id="building_id" name="building_id" required>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}" {{ old('building_id', $room->building_id) == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                    @endforeach
                </select>
                @error('building_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="number" class="form-label">@lang('Room Number')</label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number', $room->number) }}" required>
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">@lang('Status')</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ old('status', $room->status->value) === $status->value ? 'selected' : '' }}>
                            {{ \App\Enums\RoomStatus::labels()[$status->value] ?? $status->value }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.rooms') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Room')</button>
            </div>
        </form>
    </div>
</div>
@endsection
