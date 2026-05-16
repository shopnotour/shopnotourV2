
{{--@extends('admin.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container-fluid">--}}
{{--        <!-- Page Header -->--}}
{{--        <div class="row mb-4">--}}
{{--            <div class="col-12">--}}
{{--                <h2 class="mb-0">Credit Transactions</h2>--}}
{{--                <p class="text-muted">View and manage all credit transactions</p>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <!-- Filter Section -->--}}
{{--        <div class="row mb-4">--}}
{{--            <div class="col-md-8">--}}
{{--                <form action="{{ route('transactions.index') }}" method="GET">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-8">--}}
{{--                            <div class="form-group">--}}
{{--                                <label for="user_id">Filter by User:</label>--}}
{{--                                <select name="user_id" id="user_id" class="form-control" onchange="this.form.submit()">--}}
{{--                                    <option value="">All Users</option>--}}
{{--                                    @foreach($users as $user)--}}
{{--                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>--}}
{{--                                            {{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}--}}
{{--                                            @if($user->business_name)--}}
{{--                                                - {{ $user->business_name }}--}}
{{--                                            @endif--}}
{{--                                            ({{ $user->email }})--}}
{{--                                            @if($user->credit_balance)--}}
{{--                                                - Balance: ৳{{ number_format($user->credit_balance, 2) }}--}}
{{--                                            @endif--}}
{{--                                        </option>--}}
{{--                                    @endforeach--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-4">--}}
{{--                            @if($userId)--}}
{{--                                <label>&nbsp;</label>--}}
{{--                                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-block">--}}
{{--                                    <i class="fas fa-times"></i> Clear Filter--}}
{{--                                </a>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <!-- Transactions Table -->--}}
{{--        <div class="card">--}}
{{--            <div class="card-header d-flex justify-content-between align-items-center">--}}
{{--                <h5 class="mb-0">Transaction List (লেনদেন তালিকা)</h5>--}}
{{--                <div>--}}
{{--                    <span class="text-muted small">--}}
{{--                        <i class="fas fa-info-circle"></i>--}}
{{--                        Balance column: meta থেকে নেওয়া (balance_after)--}}
{{--                    </span>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="card-body">--}}
{{--                <div class="table-responsive">--}}
{{--                    <table id="transactionsTable" class="table table-bordered table-striped table-hover table-sm" style="width:100%">--}}
{{--                        <thead class="thead-dark">--}}
{{--                        <tr>--}}
{{--                            <th>ID</th>--}}
{{--                            <th>User Info</th>--}}
{{--                            <th>Booking ID</th>--}}
{{--                            <th>Type</th>--}}
{{--                            <th>Transaction Type</th>--}}
{{--                            --}}{{-- Receive = Credit (আয়/জমা) --}}
{{--                            <th class="text-right text-success">Receive (জমা)</th>--}}
{{--                            --}}{{-- Sale/Debit = খরচ --}}
{{--                            <th class="text-right text-danger">Sale/Debit (খরচ)</th>--}}
{{--                            --}}{{-- Balance After = Remaining Balance from meta --}}
{{--                            <th class="text-right text-primary">Balance (ব্যালেন্স)</th>--}}
{{--                            <th>Status</th>--}}
{{--                            <th>Date</th>--}}
{{--                            <th>Reference</th>--}}
{{--                            <th>Action</th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @forelse($transactions as $transaction)--}}
{{--                            @php--}}
{{--                                // Parse meta JSON to get balance_after (remaining balance)--}}
{{--                                $meta = null;--}}
{{--                                $balanceAfter = null;--}}
{{--                                $balanceBefore = null;--}}

{{--                                if (!empty($transaction->meta)) {--}}
{{--                                    $metaDecoded = json_decode($transaction->meta, true);--}}
{{--                                    if (is_array($metaDecoded)) {--}}
{{--                                        $meta = $metaDecoded;--}}
{{--                                        $balanceAfter = $meta['balance_after'] ?? null;--}}
{{--                                        $balanceBefore = $meta['balance_before'] ?? null;--}}
{{--                                    }--}}
{{--                                }--}}
{{--                            @endphp--}}
{{--                            <tr>--}}
{{--                                <td>{{ $transaction->id }}</td>--}}
{{--                                <td>--}}
{{--                                    <strong>{{ $transaction->user_name ?? ($transaction->first_name . ' ' . $transaction->last_name) }}</strong>--}}
{{--                                    <br>--}}
{{--                                    <small class="text-muted">--}}
{{--                                        ID: {{ $transaction->user_id }}--}}
{{--                                        @if($transaction->business_name)--}}
{{--                                            <br>{{ $transaction->business_name }}--}}
{{--                                        @endif--}}
{{--                                        @if($transaction->phone)--}}
{{--                                            <br><i class="fas fa-phone"></i> {{ $transaction->phone }}--}}
{{--                                        @endif--}}
{{--                                    </small>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($transaction->booking_id)--}}
{{--                                        <span class="badge badge-secondary">{{ $transaction->booking_id }}</span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">N/A</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($transaction->type === 'credit' || $transaction->type === 'deposit')--}}
{{--                                        <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Credit</span>--}}
{{--                                    @else--}}
{{--                                        <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Debit</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <small>{{ $transaction->transaction_type ?? ($transaction->type === 'deposit' ? 'Deposit' : 'N/A') }}</small>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Receive Column (Credit/Deposit) --}}
{{--                                <td class="text-right">--}}
{{--                                    @if($transaction->type === 'credit' || $transaction->type === 'deposit')--}}
{{--                                        <span class="text-success font-weight-bold">--}}
{{--                                            +৳{{ number_format($transaction->amount, 2) }}--}}
{{--                                        </span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Sale/Debit Column --}}
{{--                                <td class="text-right">--}}
{{--                                    @if($transaction->type === 'debit')--}}
{{--                                        <span class="text-danger font-weight-bold">--}}
{{--                                            -৳{{ number_format($transaction->amount, 2) }}--}}
{{--                                        </span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Balance Column (from meta balance_after) --}}
{{--                                <td class="text-right">--}}
{{--                                    @if($balanceAfter !== null)--}}
{{--                                        <span class="font-weight-bold {{ $balanceAfter >= 0 ? 'text-primary' : 'text-danger' }}">--}}
{{--                                            ৳{{ number_format($balanceAfter, 2) }}--}}
{{--                                        </span>--}}
{{--                                        @if($balanceBefore !== null)--}}
{{--                                            <br>--}}
{{--                                            <small class="text-muted" title="Balance before this transaction">--}}
{{--                                                Before: ৳{{ number_format($balanceBefore, 2) }}--}}
{{--                                            </small>--}}
{{--                                        @endif--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                <td>--}}
{{--                                    @if($transaction->status === 'confirmed')--}}
{{--                                        <span class="badge badge-success">Confirmed</span>--}}
{{--                                    @elseif($transaction->status === 'payment')--}}
{{--                                        <span class="badge badge-warning">Payment</span>--}}
{{--                                    @elseif($transaction->status === 'completed')--}}
{{--                                        <span class="badge badge-info">Completed</span>--}}
{{--                                    @elseif($transaction->status === 'refund')--}}
{{--                                        <span class="badge badge-secondary">Refund</span>--}}
{{--                                    @else--}}
{{--                                        <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <small>{{ date('d M Y', strtotime($transaction->created_at)) }}</small>--}}
{{--                                    <br>--}}
{{--                                    <small class="text-muted">{{ date('h:i A', strtotime($transaction->created_at)) }}</small>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <small>{{ $transaction->reference ?? 'N/A' }}</small>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <a href="{{ route('transactions.show', $transaction->id) }}"--}}
{{--                                       class="btn btn-sm btn-primary">--}}
{{--                                        <i class="fas fa-eye"></i>--}}
{{--                                    </a>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @empty--}}
{{--                            <tr>--}}
{{--                                <td colspan="12" class="text-center py-4">--}}
{{--                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>--}}
{{--                                    <p class="text-muted">No transactions found</p>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforelse--}}
{{--                        </tbody>--}}
{{--                        @if(count($transactions) > 0)--}}
{{--                            <tfoot class="bg-light font-weight-bold">--}}
{{--                            <tr>--}}
{{--                                <th colspan="5" class="text-right">Total:</th>--}}
{{--                                <th class="text-right text-success">--}}
{{--                                    +৳{{ number_format($totalCredit, 2) }}--}}
{{--                                </th>--}}
{{--                                <th class="text-right text-danger">--}}
{{--                                    -৳{{ number_format($totalDebit, 2) }}--}}
{{--                                </th>--}}
{{--                                <th class="text-right">--}}
{{--                                    <span class="{{ $walletBalance >= 0 ? 'text-primary' : 'text-warning' }}">--}}
{{--                                        ৳{{ number_format($walletBalance, 2) }}--}}
{{--                                    </span>--}}
{{--                                </th>--}}
{{--                                <th colspan="4" class="text-right">--}}
{{--                                    <span class="text-muted small">Balance = Credit - Debit</span>--}}
{{--                                </th>--}}
{{--                            </tr>--}}
{{--                            </tfoot>--}}
{{--                        @endif--}}
{{--                    </table>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}


{{--@endsection--}}
{{--@push('css')--}}
{{--    <style>--}}
{{--        .card {--}}
{{--            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);--}}
{{--            margin-bottom: 1rem;--}}
{{--        }--}}

{{--        .table th {--}}
{{--            font-size: 0.875rem;--}}
{{--            font-weight: 600;--}}
{{--            vertical-align: middle;--}}
{{--        }--}}

{{--        .table td {--}}
{{--            vertical-align: middle;--}}
{{--            font-size: 0.875rem;--}}
{{--        }--}}

{{--        .badge {--}}
{{--            font-size: 0.75rem;--}}
{{--            padding: 0.35em 0.65em;--}}
{{--        }--}}

{{--        .table-sm td {--}}
{{--            padding: 0.5rem;--}}
{{--        }--}}

{{--        /* DataTable buttons spacing */--}}
{{--        .dt-buttons {--}}
{{--            margin-bottom: 0.5rem;--}}
{{--        }--}}

{{--        .dt-buttons .btn {--}}
{{--            margin-right: 4px;--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--@push('js')--}}

{{--    --}}{{-- DataTables CSS & JS --}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">--}}

{{--    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>--}}

{{--    --}}{{-- Buttons Extension (Export/Download) --}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>--}}

{{--    <script>--}}
{{--        $(document).ready(function () {--}}
{{--            $('#transactionsTable').DataTable({--}}
{{--                dom: '<"row mb-3"<"col-sm-6"B><"col-sm-6"f>>' +--}}
{{--                    '<"row"<"col-sm-12"tr>>' +--}}
{{--                    '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',--}}
{{--                buttons: [--}}
{{--                    {--}}
{{--                        extend: 'excelHtml5',--}}
{{--                        text: '<i class="fas fa-file-excel"></i> Excel',--}}
{{--                        className: 'btn btn-success btn-sm',--}}
{{--                        title: 'Credit Transactions',--}}
{{--                        exportOptions: {--}}
{{--                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // exclude action column (11)--}}
{{--                        }--}}
{{--                    },--}}
{{--                    {--}}
{{--                        extend: 'csvHtml5',--}}
{{--                        text: '<i class="fas fa-file-csv"></i> CSV',--}}
{{--                        className: 'btn btn-info btn-sm',--}}
{{--                        title: 'Credit Transactions',--}}
{{--                        exportOptions: {--}}
{{--                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]--}}
{{--                        }--}}
{{--                    },--}}
{{--                    {--}}
{{--                        extend: 'pdfHtml5',--}}
{{--                        text: '<i class="fas fa-file-pdf"></i> PDF',--}}
{{--                        className: 'btn btn-danger btn-sm',--}}
{{--                        title: 'Credit Transactions',--}}
{{--                        orientation: 'landscape',--}}
{{--                        pageSize: 'A4',--}}
{{--                        exportOptions: {--}}
{{--                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]--}}
{{--                        }--}}
{{--                    },--}}
{{--                    {--}}
{{--                        extend: 'print',--}}
{{--                        text: '<i class="fas fa-print"></i> Print',--}}
{{--                        className: 'btn btn-secondary btn-sm',--}}
{{--                        exportOptions: {--}}
{{--                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]--}}
{{--                        }--}}
{{--                    }--}}
{{--                ],--}}
{{--                pageLength: 25,--}}
{{--                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],--}}
{{--                order: [[0, 'desc']], // newest first by ID--}}
{{--                language: {--}}
{{--                    search: "Search / খুঁজুন:",--}}
{{--                    lengthMenu: "Show _MENU_ entries",--}}
{{--                    info: "Showing _START_ to _END_ of _TOTAL_ transactions",--}}
{{--                    paginate: {--}}
{{--                        first: "First",--}}
{{--                        last: "Last",--}}
{{--                        next: "Next",--}}
{{--                        previous: "Previous"--}}
{{--                    }--}}
{{--                },--}}
{{--                // Disable sorting on action column--}}
{{--                columnDefs: [--}}
{{--                    { orderable: false, targets: [11] },--}}
{{--                    // Right-align numeric columns--}}
{{--                    { className: 'text-right', targets: [5, 6, 7] }--}}
{{--                ]--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}

{{--@endpush--}}

@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Transactions</h2>
                <p class="text-muted">View and manage all Transactions</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <form action="{{ route('transactions.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="user_id">Filter by User:</label>
                                <select name="user_id" id="user_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                            {{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}
                                            @if($user->business_name) - {{ $user->business_name }} @endif
                                            ({{ $user->email }})
                                            @if($user->credit_balance) - Balance: ৳{{ number_format($user->credit_balance, 2) }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if($userId)
                            <div class="col-md-2">
                                <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- User Details Card -->
        @if($userWallet)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1"><i class="fas fa-user-circle"></i> Selected User</h6>
                                    <strong>{{ $userWallet->name ?? ($userWallet->first_name . ' ' . $userWallet->last_name) }}</strong>
                                    @if($userWallet->business_name) — {{ $userWallet->business_name }} @endif
                                    <br><small class="text-muted">{{ $userWallet->email }} @if($userWallet->phone) | {{ $userWallet->phone }} @endif</small>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    @if($userWallet->status == 1)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                    @if($userWallet->credit_balance)
                                        <span class="ml-2 font-weight-bold text-primary">
                                            DB Balance: ৳{{ number_format($userWallet->credit_balance, 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Cards — 5 cards, 1 row -->
        <div class="row mb-4">
            <div class="col">
                <div class="card bg-success text-white h-100">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-1"><i class="fas fa-arrow-down"></i> Total Credit (আয়)</h6>
                        <h4 class="mb-0">৳{{ number_format($totalCredit, 2) }}</h4>
                        <small>Money In / জমা</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-1"><i class="fas fa-arrow-up"></i> Total Debit (খরচ)</h6>
                        <h4 class="mb-0">৳{{ number_format($totalDebit, 2) }}</h4>
                        <small>Money Out / খরচ</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card {{ $walletBalance >= 0 ? 'bg-primary' : 'bg-warning' }} text-white h-100">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-1"><i class="fas fa-wallet"></i> Calculated Balance</h6>
                        <h4 class="mb-0">৳{{ number_format($walletBalance, 2) }}</h4>
                        <small>From Transactions / হিসাব থেকে</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card text-white h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-1"><i class="fas fa-users"></i> All Users Wallet</h6>
                        <h4 class="mb-0">৳{{ number_format($totalUsersWallet, 2) }}</h4>
                        <small>Total credit_balance</small>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card bg-info text-white h-100">
                    <div class="card-body py-3">
                        <h6 class="card-title mb-1"><i class="fas fa-list"></i> Total Transactions</h6>
                        <h4 class="mb-0">{{ count($transactions) }}</h4>
                        <small>All Records / সব লেনদেন</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Rows Summary (hidden by default) -->
        <div id="selectionSummary" class="alert alert-warning d-none mb-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <i class="fas fa-check-square"></i>
                    <strong><span id="selectedCount">0</span> row(s) selected</strong>
                    &nbsp;|&nbsp; Receive: <strong class="text-success">৳<span id="selectedCredit">0.00</span></strong>
                    &nbsp;|&nbsp; Debit: <strong class="text-danger">৳<span id="selectedDebit">0.00</span></strong>
                    &nbsp;|&nbsp; Net: <strong class="text-primary">৳<span id="selectedNet">0.00</span></strong>
                </div>
                <div class="col-md-4 text-right">
                    <button id="clearSelection" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaction List (লেনদেন তালিকা)</h5>
                <small class="text-muted"><i class="fas fa-info-circle"></i> Balance: meta থেকে (balance_after)</small>
            </div>
            <div class="card-body">

                <!-- Date Range Filter -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i>&nbsp; From</span>
                            </div>
                            <input type="date" id="dateFrom" class="form-control">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text bg-white">To</span>
                            </div>
                            <input type="date" id="dateTo" class="form-control">
                            <div class="input-group-append">
                                <button id="filterDate" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button id="clearDate" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-right">
                        <button class="btn btn-sm btn-outline-secondary date-shortcut" data-range="today">Today</button>
                        <button class="btn btn-sm btn-outline-secondary date-shortcut" data-range="week">This Week</button>
                        <button class="btn btn-sm btn-outline-secondary date-shortcut" data-range="month">This Month</button>
                        <button class="btn btn-sm btn-outline-secondary date-shortcut" data-range="year">This Year</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="transactionsTable" class="table table-bordered table-striped table-hover table-sm" style="width:100%">
                        <thead class="thead-dark">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" title="Select All">
                            </th>
                            <th>User Info</th>
                            <th>Type</th>
                            <th>Transaction Type</th>
                            <th class="text-right text-success">Receive (জমা)</th>
                            <th class="text-right text-danger">Sale/Debit (খরচ)</th>
                            <th class="text-right text-primary">Balance (ব্যালেন্স)</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($transactions as $transaction)
                            @php
                                $balanceAfter  = null;
                                $balanceBefore = null;
                                if (!empty($transaction->meta)) {
                                    $metaDecoded = json_decode($transaction->meta, true);
                                    if (is_array($metaDecoded)) {
                                        $balanceAfter  = $metaDecoded['balance_after']  ?? null;
                                        $balanceBefore = $metaDecoded['balance_before'] ?? null;
                                    }
                                }
                                $isCredit = in_array($transaction->type, ['credit', 'deposit']);
                                $isDebit  = $transaction->type === 'debit';
                            @endphp
                            <tr
                                data-credit="{{ $isCredit ? $transaction->amount : 0 }}"
                                data-debit="{{ $isDebit ? $transaction->amount : 0 }}"
                                data-date="{{ date('Y-m-d', strtotime($transaction->created_at)) }}"
                            >
                                <td><input type="checkbox" class="row-checkbox"></td>
                                <td>
                                    <strong>{{ $transaction->user_name ?? ($transaction->first_name . ' ' . $transaction->last_name) }}</strong>
                                    <br>
{{--                                    <small class="text-muted">--}}
{{--                                        ID: {{ $transaction->user_id }}--}}
{{--                                        @if($transaction->business_name)<br>{{ $transaction->business_name }}@endif--}}
{{--                                        @if($transaction->phone)<br><i class="fas fa-phone"></i> {{ $transaction->phone }}@endif--}}
{{--                                    </small>--}}
                                </td>
                                <td>
                                    @if($isCredit)
                                        <span class="badge badge-success"><i class="fas fa-arrow-down"></i> Credit</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Debit</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $transaction->transaction_type ?? ($transaction->type === 'deposit' ? 'Deposit' : 'N/A') }}</small>
                                </td>
                                <td class="text-right">
                                    @if($isCredit)
                                        <span class="text-success font-weight-bold">+৳{{ number_format($transaction->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($isDebit)
                                        <span class="text-danger font-weight-bold">-৳{{ number_format($transaction->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($balanceAfter !== null)
                                        <span class="font-weight-bold {{ $balanceAfter >= 0 ? 'text-primary' : 'text-danger' }}">
                                            ৳{{ number_format($balanceAfter, 2) }}
                                        </span>
                                        @if($balanceBefore !== null)
                                            <br><small class="text-muted">Before: ৳{{ number_format($balanceBefore, 2) }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->status === 'confirmed')
                                        <span class="badge badge-success">Confirmed</span>
                                    @elseif($transaction->status === 'payment')
                                        <span class="badge badge-warning">Payment</span>
                                    @elseif($transaction->status === 'completed')
                                        <span class="badge badge-info">Completed</span>
                                    @elseif($transaction->status === 'refund')
                                        <span class="badge badge-secondary">Refund</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ date('d M Y', strtotime($transaction->created_at)) }}</small>
                                    <br>
                                    <small class="text-muted">{{ date('h:i A', strtotime($transaction->created_at)) }}</small>
                                </td>
                                <td>
                                    <small>{{ $transaction->reference ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if(count($transactions) > 0)
                            <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <th colspan="4" class="text-right">Total:</th>
                                <th class="text-right text-success">+৳{{ number_format($totalCredit, 2) }}</th>
                                <th class="text-right text-danger">-৳{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-right">
                                    <span class="{{ $walletBalance >= 0 ? 'text-primary' : 'text-warning' }}">
                                        ৳{{ number_format($walletBalance, 2) }}
                                    </span>
                                </th>
                                <th colspan="4" class="text-muted small text-right">Balance = Credit - Debit</th>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075); margin-bottom: 1rem; }
        .table th { font-size: .875rem; font-weight: 600; vertical-align: middle; }
        .table td { vertical-align: middle; font-size: .875rem; }
        .badge { font-size: .75rem; padding: .35em .65em; }
        .dt-buttons { margin-bottom: .5rem; }
        .dt-buttons .btn { margin-right: 4px; }
        .date-shortcut { margin-left: 3px; }
        /* Selected row highlight */
        tr.selected-row td { background-color: #fff8e1 !important; }
        #selectAll, .row-checkbox { cursor: pointer; width: 15px; height: 15px; }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            // ── DataTable ────────────────────────────────────────────────────
            var table = $('#transactionsTable').DataTable({
                dom: '<"row mb-2"<"col-sm-4"l><"col-sm-4"B><"col-sm-4"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Credit Transactions',
                        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-info btn-sm',
                        title: 'Credit Transactions',
                        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Credit Transactions',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[1, 'desc']],
                language: {
                    search: "Search / খুঁজুন:",
                    info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                    paginate: { first: "First", last: "Last", next: "›", previous: "‹" }
                },
                columnDefs: [
                    { orderable: false, targets: [0, 10] },
                    { className: 'text-right', targets: [4, 5, 6] }
                ]
            });

            // ── Date Range Filter ────────────────────────────────────────────
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var from = $('#dateFrom').val();
                var to   = $('#dateTo').val();
                if (!from && !to) return true;

                var rowDate = $(table.row(dataIndex).node()).data('date'); // "YYYY-MM-DD"
                if (!rowDate) return true;
                if (from && rowDate < from) return false;
                if (to   && rowDate > to)   return false;
                return true;
            });

            $('#filterDate').on('click', function () {
                table.draw();
            });

            $('#clearDate').on('click', function () {
                $('#dateFrom, #dateTo').val('');
                table.draw();
            });

            // Quick shortcuts
            $('.date-shortcut').on('click', function () {
                var range = $(this).data('range');
                var today = new Date();
                var from, to = today.toISOString().slice(0, 10);

                if (range === 'today') {
                    from = to;
                } else if (range === 'week') {
                    var day = today.getDay() || 7;
                    var mon = new Date(today);
                    mon.setDate(today.getDate() - day + 1);
                    from = mon.toISOString().slice(0, 10);
                } else if (range === 'month') {
                    from = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';
                } else if (range === 'year') {
                    from = today.getFullYear() + '-01-01';
                }

                $('#dateFrom').val(from);
                $('#dateTo').val(to);
                table.draw();
            });

            // ── Row Selection ────────────────────────────────────────────────
            function updateSummary() {
                var checked = $('.row-checkbox:checked');
                var count = checked.length;
                var credit = 0, debit = 0;

                checked.each(function () {
                    var row = $(this).closest('tr');
                    credit += parseFloat(row.data('credit') || 0);
                    debit  += parseFloat(row.data('debit')  || 0);
                });

                if (count > 0) {
                    var net = credit - debit;
                    $('#selectionSummary').removeClass('d-none');
                    $('#selectedCount').text(count);
                    $('#selectedCredit').text(credit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $('#selectedDebit').text(debit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    $('#selectedNet').text(net.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                } else {
                    $('#selectionSummary').addClass('d-none');
                }
            }

            // Select All (visible rows only)
            $('#selectAll').on('change', function () {
                var checked = $(this).is(':checked');
                table.rows({ search: 'applied' }).nodes().to$()
                    .find('.row-checkbox').prop('checked', checked)
                    .closest('tr').toggleClass('selected-row', checked);
                updateSummary();
            });

            // Individual checkbox
            $('#transactionsTable tbody').on('change', '.row-checkbox', function () {
                $(this).closest('tr').toggleClass('selected-row', $(this).is(':checked'));
                var total   = table.rows({ search: 'applied' }).count();
                var checked = table.rows({ search: 'applied' }).nodes().to$().find('.row-checkbox:checked').length;
                $('#selectAll')
                    .prop('indeterminate', checked > 0 && checked < total)
                    .prop('checked', checked > 0 && checked === total);
                updateSummary();
            });

            // Row click toggles checkbox
            $('#transactionsTable tbody').on('click', 'tr', function (e) {
                if ($(e.target).is('input[type=checkbox], a, button, i')) return;
                var cb = $(this).find('.row-checkbox');
                cb.prop('checked', !cb.prop('checked')).trigger('change');
            });

            // Clear selection
            $('#clearSelection').on('click', function () {
                $('.row-checkbox, #selectAll').prop('checked', false).prop('indeterminate', false);
                $('tr').removeClass('selected-row');
                updateSummary();
            });

            // Reset on DataTable redraw (page change / search)
            table.on('draw', function () {
                $('#selectAll').prop('checked', false).prop('indeterminate', false);
                updateSummary();
            });
        });
    </script>
@endpush
