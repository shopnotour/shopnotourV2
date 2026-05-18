@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Bonus Transactions') }}</h1>
            <div class="title-actions">
                @if(Auth::user()->hasPermission('bonus_manage'))
                    <a href="{{ route('user.admin.bonus.index') }}" class="btn btn-primary">
                        <i class="fa fa-gift"></i> {{ __('Give Bonus') }}
                    </a>
                @endif
            </div>
        </div>

        @include('admin.message')

        {{-- Filter --}}
        <div class="filter-div d-flex justify-content-between mb20">
            <div></div>
            <form method="GET" class="filter-form d-flex">
                <select name="type" class="form-control" style="width:150px; margin-right:5px;">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="bonus_credit" @if(request('type') == 'bonus_credit') selected @endif>{{ __('Credit') }}</option>
                    <option value="bonus_debit"  @if(request('type') == 'bonus_debit')  selected @endif>{{ __('Debit') }}</option>
                </select>
                <input type="text" name="s" value="{{ request('s') }}"
                       placeholder="{{ __('Search user...') }}"
                       class="form-control" style="width:220px; margin-right:5px;">
                <button type="submit" class="btn btn-info">{{ __('Search') }}</button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Remarks') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>
                                    @if($row->author)
                                        @if(Auth::user()->hasPermission('user_view'))
                                            <a href="{{ route('user.admin.detail', ['id' => $row->author->id]) }}">
                                                {{ $row->author->getDisplayName() }}
                                            </a>
                                        @else
                                            {{ $row->author->getDisplayName() }}
                                        @endif
                                        <br><small class="text-muted">{{ $row->author->email }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->type == 'bonus_credit')
                                        <span class="badge badge-success">↑ {{ __('Credit') }}</span>
                                    @elseif($row->type == 'bonus_debit')
                                        <span class="badge badge-danger">↓ {{ __('Debit') }}</span>
                                    @else
                                        <span class="badge badge-default">{{ $row->type }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: {{ $row->type == 'bonus_credit' ? '#16a34a' : '#dc2626' }}">
                                        {{ $row->type == 'bonus_credit' ? '+' : '-' }}{{ format_money($row->amount) }}
                                    </strong>
                                </td>
                                <td><code>{{ $row->reference ?? '-' }}</code></td>
                                <td>{{ $row->remarks ?? '-' }}</td>
                                <td>
                                <span class="badge badge-{{ $row->status == 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                                </td>
                                <td>{{ display_datetime($row->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">{{ __('No transactions found.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $rows->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
