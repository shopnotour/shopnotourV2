@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Flight API Management') }}</h1>
            <div class="title-actions">
                <a href="{{ route('flight.admin.api.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> {{ __('Add New API') }}
                </a>
            </div>
        </div>

        @include('admin.message')

        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="get" action="{{ route('flight.admin.api.index') }}" class="filter-form filter-form-left d-flex" role="search">
                    <input type="text" name="s" value="{{ Request()->s }}" placeholder="{{ __('Search by name or provider') }}" class="form-control">

                    <select name="status" class="form-control">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="active" {{ Request()->status == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ Request()->status == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>

                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i> {{ __('Search') }}
                    </button>
                </form>
            </div>

            <div class="col-right">
                <button type="button" class="btn btn-info btn-sm" onclick="bulkEdit('activate')">
                    <i class="fa fa-check"></i> {{ __('Activate') }}
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="bulkEdit('deactivate')">
                    <i class="fa fa-ban"></i> {{ __('Deactivate') }}
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkEdit('delete')">
                    <i class="fa fa-trash"></i> {{ __('Delete') }}
                </button>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <form action="{{ route('flight.admin.api.bulkEdit') }}" method="post" class="bravo-form-item">
                    @csrf
                    <input type="hidden" name="action" class="action-input">

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th width="50px">
                                    <input type="checkbox" class="check-all">
                                </th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Provider') }}</th>
                                <th>{{ __('API URL') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Created Date') }}</th>
                                <th width="150px">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($rows->total() > 0)
                                @foreach($rows as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="check-item">
                                        </td>
                                        <td>
                                            <strong>{{ $item->name }}</strong>
                                        </td>
                                        <td>{{ $item->provider }}</td>
                                        <td>
                                            @if($item->api_url)
                                                <small class="text-muted">{{ Str::limit($item->api_url, 50) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->status == 'active')
                                                <span class="badge badge-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->priority }}</td>
                                        <td>{{ display_date($item->created_at) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-primary" href="{{ route('flight.admin.api.edit', $item->id) }}" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                <form action="{{ route('flight.admin.api.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this API?')"
                                                            title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No APIs found') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            {{ $rows->appends(request()->query())->links() }}
        </div>
    </div>

    <script>
        function bulkEdit(action) {
            if (!$('.check-item:checked').length) {
                alert('{{ __("Please select at least one item") }}');
                return false;
            }

            if (action == 'delete') {
                if (!confirm('{{ __("Are you sure you want to delete selected items?") }}')) {
                    return false;
                }
            }

            $('.action-input').val(action);
            $('.bravo-form-item').submit();
        }

        $('.check-all').on('change', function() {
            $('.check-item').prop('checked', $(this).prop('checked'));
        });
    </script>
@endsection
