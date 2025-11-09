@php
    $title = old('title', $news->title ?? '');
    $content = old('content', $news->content ?? '');
    $isPublic = old('is_public', isset($news) ? (int) $news->is_public : 0);
@endphp

<div class="mb-3">
    <label for="title" class="form-label">@lang('Title')</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ $title }}" placeholder="@lang('Optional')">
    <div class="form-text">@lang('Optional. Leave blank to skip a headline.')</div>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="content" class="form-label">@lang('Content')</label>
    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required>{{ $content }}</textarea>
    @error('content')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label d-block">@lang('Visibility')</label>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="is_public" id="is_public_on" value="1" {{ (string) $isPublic === '1' ? 'checked' : '' }} required>
        <label class="form-check-label" for="is_public_on">@lang('Published')</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="is_public" id="is_public_off" value="0" {{ (string) $isPublic === '0' ? 'checked' : '' }} required>
        <label class="form-check-label" for="is_public_off">@lang('Draft')</label>
    </div>
    @error('is_public')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
