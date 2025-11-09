@extends('admin.layouts.app')

@section('title', __('Action Logs'))

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <strong class="fs-5">@lang('Action Logs')</strong>
        <small class="text-muted">@lang('All admin-side write actions are captured automatically.')</small>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-6 col-xl-4">
                <label for="search" class="form-label fw-semibold">@lang('Search')</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="form-control"
                    placeholder="@lang('Admin name or action content')"
                >
            </div>
            <div class="col-md-3 col-xl-2">
                <label for="method" class="form-label fw-semibold">@lang('Method')</label>
                <select id="method" name="method" class="form-select">
                    <option value="">@lang('All')</option>
                    @foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-xl-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="cil-filter"></i> @lang('Filter')
                </button>
                <a href="{{ route('admin.action_logs') }}" class="btn btn-outline-secondary flex-grow-1">
                    <i class="cil-x"></i> @lang('Reset')
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 180px;">@lang('Occurred At')</th>
                        <th style="width: 220px;">@lang('Admins')</th>
                        <th>@lang('Action')</th>
                        <th style="width: 220px;">@lang('Request')</th>
                        <th>@lang('Context')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        @php
                            $context = $log->context ?? [];
                            $payload = $context['payload'] ?? [];
                            $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                {{ optional($log->created_at)->format('Y-m-d H:i:s') }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->admin->name ?? __('System') }}</div>
                                <div class="small text-muted">{{ $log->admin->email ?? __('N/A') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->content }}</div>
                                <div class="small text-muted">
                                    @lang('Route'): {{ $context['route'] ?? __('N/A') }}
                                </div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge text-bg-secondary">{{ $context['method'] ?? '—' }}</span>
                                    <span class="small text-muted">{{ $context['path'] ?? '' }}</span>
                                </div>
                                <div class="small text-muted">@lang('IP'): {{ $context['ip'] ?? __('N/A') }}</div>
                                <div class="small text-muted">@lang('User Agent'): <span class="d-inline-block text-truncate" style="max-width: 160px;" title="{{ $context['user_agent'] ?? '' }}">{{ $context['user_agent'] ?? __('N/A') }}</span></div>
                            </td>
                            <td>
                                @if (!empty($payload))
                                    <pre class="small bg-body-tertiary rounded p-2 mb-0" style="max-height: 180px; overflow:auto;">{{ $payloadJson }}</pre>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                @lang('No action logs were found for the selected filters.')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
