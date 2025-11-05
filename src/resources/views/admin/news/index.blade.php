@extends('admin.layouts.app')

@section('title', __('News'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('News')</strong>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm">
            <i class="cil-plus"></i> @lang('Add News')
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>@lang('Title')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Updated At')</th>
                        <th>@lang('Excerpt')</th>
                        <th class="text-end">@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($news as $item)
                        <tr>
                            <td class="fw-semibold">
                                {{ $item->title ?: __('(No Title)') }}
                            </td>
                            <td>
                                @if ($item->is_public)
                                    <span class="badge bg-success">@lang('Published')</span>
                                @else
                                    <span class="badge bg-secondary">@lang('Draft')</span>
                                @endif
                            </td>
                            <td>{{ $item->updated_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-muted">
                                {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 40) }}
                            </td>
                            <td>
                                <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-outline-primary">
                                    <i class="cil-pencil"></i>
                                </a>
                                <form action="{{ route('admin.news.destroy', $item) }}" method="POST" onsubmit="return confirm(@js(__('Are you sure you want to delete news :title?', ['title' => $item->title ?: __('(No Title)')])))" class="ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="cil-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">@lang('No news has been created yet.')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $news->links() }}
    </div>
</div>
@endsection
