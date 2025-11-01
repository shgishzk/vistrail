@php
    $selectedUserId = old('user_id', optional($visit)->user_id);
    $selectedAreaId = old('area_id', optional($visit)->area_id);

    $selectedUser = $users->firstWhere('id', $selectedUserId);
    $selectedArea = $areas->firstWhere('id', $selectedAreaId);

    $formatUserDisplay = function ($user) {
        if (!$user) {
            return '';
        }

        $parts = [$user->name];
        if (!empty($user->name_kana)) {
            $parts[] = $user->name_kana;
        }

        return implode(' / ', $parts) . ' (' . $user->email . ')';
    };

    $userDisplayValue = $formatUserDisplay($selectedUser);

    $areaDisplayValue = $selectedArea
        ? $selectedArea->number . ($selectedArea->name ? ' - ' . $selectedArea->name : '')
        : '';

    $startDateValue = old('start_date', optional(optional($visit)->start_date)->format('Y-m-d'));
    $endDateValue = old('end_date', optional(optional($visit)->end_date)->format('Y-m-d'));
@endphp

<div class="mb-3">
    <label for="user_search" class="form-label">@lang('User')</label>
    <input type="text"
        class="form-control @error('user_id') is-invalid @enderror"
        id="user_search"
        name="user_search"
        list="users-list"
        value="{{ old('user_search', $userDisplayValue) }}"
        placeholder="@lang('Type to search user...')"
        autocomplete="off"
        required>
    <input type="hidden" name="user_id" id="user_id" value="{{ $selectedUserId }}">
    <datalist id="users-list">
        @foreach($users as $user)
            @php
                $parts = [$user->name];
                if (!empty($user->name_kana)) {
                    $parts[] = $user->name_kana;
                }
                $optionValue = implode(' / ', $parts);
            @endphp
            <option data-value="{{ $user->id }}" value="{{ $optionValue }}"></option>
        @endforeach
    </datalist>
    @error('user_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="area_search" class="form-label">@lang('Area')</label>
    <input type="text"
        class="form-control @error('area_id') is-invalid @enderror"
        id="area_search"
        name="area_search"
        list="areas-list"
        value="{{ old('area_search', $areaDisplayValue) }}"
        placeholder="@lang('Type to search area...')"
        autocomplete="off"
        required>
    <input type="hidden" name="area_id" id="area_id" value="{{ $selectedAreaId }}">
    <datalist id="areas-list">
        @foreach($areas as $area)
            <option data-value="{{ $area->id }}" value="{{ $area->number }}{{ $area->name ? ' - ' . $area->name : '' }}"></option>
        @endforeach
    </datalist>
    @error('area_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="start_date" class="form-label">@lang('Start Date')</label>
            <input type="date"
                class="form-control @error('start_date') is-invalid @enderror"
                id="start_date"
                name="start_date"
                value="{{ $startDateValue }}"
                required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="end_date" class="form-label">@lang('End Date')</label>
            <input type="date"
                class="form-control @error('end_date') is-invalid @enderror"
                id="end_date"
                name="end_date"
                value="{{ $endDateValue }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    </div>

<div class="mb-3">
    <label for="memo" class="form-label">@lang('Memo')</label>
    <textarea class="form-control @error('memo') is-invalid @enderror"
        id="memo"
        name="memo"
        rows="3">{{ old('memo', optional($visit)->memo) }}</textarea>
    @error('memo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const setupSuggestion = (inputId, hiddenId, datalistId) => {
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const datalist = document.getElementById(datalistId);

        if (!input || !hidden || !datalist) {
            return;
        }

        const options = Array.from(datalist.options);

        const findByValue = (value) => options.find(option => option.value === value.trim());
        const findById = (id) => options.find(option => option.dataset.value === id);

        const syncFromHidden = () => {
            if (!hidden.value) {
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
    };

    setupSuggestion('user_search', 'user_id', 'users-list');
    setupSuggestion('area_search', 'area_id', 'areas-list');
});
</script>
