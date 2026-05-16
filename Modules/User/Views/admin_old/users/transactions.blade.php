@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-exchange-alt text-primary"></i> Transactions
                </h1>
                <p class="text-muted mb-0" style="font-size:12px;">
                    <a href="{{ route('admin.users.index') }}" class="text-muted">Users</a>
                    <i class="fas fa-chevron-right mx-1" style="font-size:9px;"></i>
                    <a href="{{ route('admin.users.bookings', $user->id) }}" class="text-muted">
                        {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                    </a>
                    <i class="fas fa-chevron-right mx-1" style="font-size:9px;"></i>
                    <strong>Transactions</strong>
                </p>
            </div>
            <a href="{{ route('admin.users.bookings', $user->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        @include('admin.message')

        {{-- User Info + Stats --}}
        <div class="card shadow mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:18px;font-weight:700;">
                            {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="font-weight-bold text-gray-800" style="font-size:15px;">
                            {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                        </div>
                        <div class="text-muted small">{{ $user->email ?? '' }}</div>
                    </div>
                    @php
                        $totalCredit = $transactions->where('type', 'credit')->sum('amount');
                        $totalDebit  = $transactions->where('type', 'debit')->sum('amount');
                        $totalCount  = $transactions->count();
                    @endphp
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Total</div>
                        <div class="h5 mb-0 font-weight-bold text-primary">{{ $totalCount }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Total Credit</div>
                        <div class="h5 mb-0 font-weight-bold text-success">৳{{ number_format($totalCredit, 2) }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Total Debit</div>
                        <div class="h5 mb-0 font-weight-bold text-danger">৳{{ number_format($totalDebit, 2) }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Credit Balance</div>
                        <div class="h5 mb-0 font-weight-bold text-info">৳{{ number_format($user->credit_balance ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-1"></i> All Transactions
                </h6>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                    <div class="table-responsive">
                        <table id="transactionsTable" class="table table-bordered table-hover" width="100%">
                            <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Transaction Type</th>
                                <th>Reference</th>
                                <th>Booking</th>
                                <th>Status</th>
                                <th>Deposit Date</th>
                                <th>Remarks</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($transactions as $i => $txn)
                                @php
                                    $isCredit = strtolower($txn->type ?? '') === 'credit';
                                    $isDebit  = strtolower($txn->type ?? '') === 'debit';

                                    $statusConfig = match(strtolower($txn->status ?? '')) {
                                        'approved', 'completed', 'success' => ['badge-success', ucfirst($txn->status)],
                                        'pending'  => ['badge-warning', 'Pending'],
                                        'rejected', 'failed', 'cancelled' => ['badge-danger', ucfirst($txn->status)],
                                        default    => ['badge-secondary', ucfirst($txn->status ?? '—')],
                                    };

                                    // meta decode
                                    $meta = [];
                                    if (!empty($txn->meta)) {
                                        $meta = is_string($txn->meta)
                                            ? (json_decode($txn->meta, true) ?? [])
                                            : (array)$txn->meta;
                                    }
                                @endphp
                                <tr>
                                    {{-- # --}}
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>

                                    {{-- Type --}}
                                    <td>
                                        @if($isCredit)
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-down mr-1"></i>Credit
                                            </span>
                                        @elseif($isDebit)
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-up mr-1"></i>Debit
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">{{ $txn->type ?? '—' }}</span>
                                        @endif
                                    </td>

                                    {{-- Amount --}}
                                    <td>
                                        <span class="font-weight-bold {{ $isCredit ? 'text-success' : ($isDebit ? 'text-danger' : 'text-muted') }}"
                                              style="font-size:14px;">
                                            {{ $isDebit ? '−' : '+' }}৳{{ number_format($txn->amount ?? 0, 2) }}
                                        </span>
                                    </td>

                                    {{-- Transaction Type --}}
                                    <td>
                                        @if(!empty($txn->transaction_type))
                                            <span class="badge badge-light text-dark border" style="font-size:11px;">
                                                {{ ucwords(str_replace('_', ' ', $txn->transaction_type)) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Reference --}}
                                    <td class="small text-muted">
                                        {{ $txn->reference ?? '—' }}
                                    </td>

                                    {{-- Booking --}}
                                    <td>
                                        @if(!empty($txn->booking_id))
                                            <a href="{{ route('admin.users.booking.detail', [$user->id, $txn->booking_id]) }}"
                                               class="text-primary font-weight-bold small">
                                                #{{ $txn->booking_id }}
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        <span class="badge {{ $statusConfig[0] }}">{{ $statusConfig[1] }}</span>
                                    </td>

                                    {{-- Deposit Date --}}
                                    <td class="small text-muted">
                                        {{ !empty($txn->deposit_date) ? \Carbon\Carbon::parse($txn->deposit_date)->format('d M Y') : '—' }}
                                    </td>

                                    {{-- Remarks --}}
                                    <td class="small text-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $txn->remarks ?? '' }}">
                                        {{ $txn->remarks ?? '—' }}
                                    </td>

                                    {{-- Created At --}}
                                    <td class="small text-muted">
                                        {{ $txn->created_at ? \Carbon\Carbon::parse($txn->created_at)->format('d M Y') : '—' }}
                                        <br>
                                        <span style="font-size:10px;">
                                            {{ $txn->created_at ? \Carbon\Carbon::parse($txn->created_at)->format('h:i A') : '' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No transactions found for this user.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#transactionsTable').DataTable({
                pageLength: 25,
                order: [[9, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0] },
                    { searchable: false, targets: [0, 9] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search transactions...',
                    lengthMenu: 'Show _MENU_ transactions',
                    info: 'Showing _START_ to _END_ of _TOTAL_ transactions',
                    paginate: {
                        previous: '&laquo; Prev',
                        next: 'Next &raquo;',
                    }
                },
            });
        });
    </script>
@endpush
