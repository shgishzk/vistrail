@extends('admin.layouts.app')

@php
    use App\Enums\VisitStatus;

    $currentStatus ??= ($visit->status instanceof VisitStatus)
        ? $visit->status
        : VisitStatus::from($visit->status ?? VisitStatus::default()->value);
    $statusTransitions = $statusTransitions ?? [];

    $transitionActions = [
        VisitStatus::IN_PROGRESS->value => [
            VisitStatus::COMPLETED->value => [
                'label' => '訪問を完了',
                'confirm' => 'この訪問を完了状態に更新します。よろしいですか？',
                'class' => 'btn-success',
            ],
            VisitStatus::PENDING_REASSIGNMENT->value => [
                'label' => '再割当可能な状態にする',
                'confirm' => 'この訪問を再割当待機中に更新します。よろしいですか？',
                'class' => 'btn-warning',
            ],
            VisitStatus::CANCELED->value => [
                'label' => '未着手として返却',
                'confirm' => 'この訪問を未着手として返却状態に更新します。よろしいですか？',
                'class' => 'btn-outline-secondary',
            ],
        ],
        VisitStatus::PENDING_REASSIGNMENT->value => [
            VisitStatus::IN_PROGRESS->value => [
                'label' => '訪問を再開',
                'confirm' => 'この訪問を訪問中に戻します。よろしいですか？',
                'class' => 'btn-primary',
            ],
            VisitStatus::REASSIGNED->value => [
                'label' => '再割当済みにする',
                'confirm' => 'この訪問を再割当済みに更新します。よろしいですか？',
                'class' => 'btn-outline-info',
            ],
        ],
        VisitStatus::COMPLETED->value => [
            VisitStatus::CANCELED->value => [
                'label' => '完了を取り消して未着手に戻す',
                'confirm' => 'この訪問を未着手として返却状態に戻します。よろしいですか？',
                'class' => 'btn-outline-secondary',
            ],
        ],
    ];
@endphp

@section('title', __('Edit Visit'))

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Edit Visit')</strong>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.visits.update', $visit) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.visits.partials.form-fields', [
                'visit' => $visit,
                'users' => $users,
                'areas' => $areas,
                'preselectedAreaId' => null,
            ])

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.visits') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Visit')</button>
            </div>
        </form>

        @if(!empty($statusTransitions))
            <hr class="my-4">
            <h5 class="mb-3">@lang('Status Actions')</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach($statusTransitions as $transition)
                    @php
                        $transitionValue = $transition->value;
                        $actionConfig = $transitionActions[$currentStatus->value][$transitionValue] ?? null;
                    @endphp
                    @if($actionConfig)
                        <form action="{{ route('admin.visits.update', $visit) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $actionConfig['confirm'] }}');">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="user_id" value="{{ $visit->user_id }}">
                            <input type="hidden" name="area_id" value="{{ $visit->area_id }}">
                            <input type="hidden" name="start_date" value="{{ optional($visit->start_date)->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" value="{{ optional($visit->end_date)->format('Y-m-d') }}">
                            <input type="hidden" name="memo" value="{{ $visit->memo }}">
                            <input type="hidden" name="status" value="{{ $transitionValue }}">
                            <button type="submit" class="btn btn-sm {{ $actionConfig['class'] }}">
                                {{ $actionConfig['label'] }}
                            </button>
                        </form>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
