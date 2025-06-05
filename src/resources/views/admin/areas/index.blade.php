@extends('admin.layouts.app')

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
        
        <table class="table table-responsive-sm table-striped">
            <thead>
                <tr>
                    <th>@lang('Number')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Memo')</th>
                    <th>@lang('Created At')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                <tr>
                    <td>{{ $area->number }}</td>
                    <td>{{ $area->name }}</td>
                    <td>{{ Str::limit($area->memo, 50) }}</td>
                    <td>{{ $area->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.areas.edit', $area) }}" class="btn btn-sm btn-primary">
                            <i class="cil-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" data-coreui-toggle="modal" data-coreui-target="#deleteModal{{ $area->id }}">
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
