@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        :root {
            --primary:#2563eb; --primary-lt:#eff6ff;
            --success:#059669; --success-lt:#ecfdf5;
            --warn:#d97706;    --warn-lt:#fffbeb;
            --danger:#dc2626;  --danger-lt:#fef2f2;
            --muted:#6b7280;   --border:#e5e7eb;
            --surface:#ffffff; --bg:#f3f4f6;
            --text:#111827;    --radius:10px;
            --shadow:0 1px 3px rgba(0,0,0,.08);
        }
        body, .content-area { background:var(--bg) !important; font-family:'DM Sans',sans-serif; }

        .pg-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .pg-title { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .pg-title .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; }
        .pg-sub { font-size:12px; color:var(--muted); margin-top:4px; }
        .btn-back { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:8px; font-size:13px; font-weight:500; color:var(--text); text-decoration:none; box-shadow:var(--shadow); }
        .btn-back:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* User card */
        .user-info-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:16px 20px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
        .user-avatar { width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); display:grid; place-items:center; color:#fff; font-size:18px; font-weight:700; flex-shrink:0; }
        .user-detail { flex:1; }
        .user-detail-name { font-size:15px; font-weight:600; color:var(--text); }
        .user-detail-email { font-size:12px; color:var(--muted); }
        .user-wallet { background:var(--success-lt); border:1px solid #a7f3d0; border-radius:8px; padding:10px 16px; text-align:center; }
        .user-wallet-label { font-size:10px; color:var(--success); text-transform:uppercase; letter-spacing:.06em; font-weight:600; }
        .user-wallet-val { font-size:20px; font-weight:700; font-family:'DM Mono',monospace; color:var(--success); }

        /* Balance Bar */
        .balance-bar { background:linear-gradient(135deg,#1a2a5e,#2563eb); border-radius:var(--radius); padding:16px 24px; margin-bottom:20px; display:flex; align-items:center; flex-wrap:wrap; gap:0; box-shadow:0 4px 16px rgba(37,99,235,.2); }
        .bal-item { flex:1; min-width:130px; padding:0 18px; border-right:1px solid rgba(255,255,255,.15); }
        .bal-item:first-child { padding-left:0; }
        .bal-item:last-child  { border-right:none; }
        .bal-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.6); margin-bottom:4px; }
        .bal-value { font-size:18px; font-weight:700; font-family:'DM Mono',monospace; color:#fff; line-height:1; }
        .bal-value.positive { color:#6ee7b7; }
        .bal-value.negative { color:#fca5a5; }
        .bal-value.neutral  { color:#fde68a; }
        .bal-sub { font-size:11px; color:rgba(255,255,255,.5); margin-top:3px; }

        /* Stats Grid */
        .stats-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:20px; }
        @media(max-width:1200px){ .stats-grid{ grid-template-columns:repeat(3,1fr); } }
        @media(max-width:768px) { .stats-grid{ grid-template-columns:repeat(2,1fr); } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 16px; box-shadow:var(--shadow); }
        .stat-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
        .stat-icon { width:34px; height:34px; border-radius:8px; display:grid; place-items:center; font-size:14px; }
        .stat-badge { font-size:10px; font-weight:700; padding:2px 7px; border-radius:20px; }
        .stat-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
        .stat-val   { font-size:18px; font-weight:700; font-family:'DM Mono',monospace; color:var(--text); }
        .stat-amount { font-size:12px; font-weight:600; font-family:'DM Mono',monospace; margin-top:4px; }
        .stat-sub   { font-size:10px; color:var(--muted); margin-top:2px; }
        .c-deposit  { border-top:3px solid var(--success); }
        .c-withdraw { border-top:3px solid var(--danger); }
        .c-payment  { border-top:3px solid var(--primary); }
        .c-refund   { border-top:3px solid #0891b2; }
        .c-pending  { border-top:3px solid var(--warn); }

        /* Filter */
        .filter-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
        .filter-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .filter-group { display:flex; align-items:center; gap:6px; }
        .filter-group label { font-size:12px; color:var(--muted); margin:0; white-space:nowrap; }
        .filter-group input[type="date"] { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; font-family:'DM Mono',monospace; width:140px; }
        .filter-group input[type="date"]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .filter-group select { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; }
        .btn-filter { height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--primary); color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .btn-filter:hover { background:#1d4ed8; }
        .btn-clear { height:32px; padding:0 12px; font-size:12px; font-weight:500; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
        .btn-clear:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* Panel */
        .tx-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .table-scroll { overflow-x:auto; }

        /* Export buttons */
        .btn-excel  { background:#059669 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }
        .btn-pdf    { background:#dc2626 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }
        .btn-print2 { background:#475569 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }

        /* Table */
        #creditListTable { width:100% !important; }
        #creditListTable thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); padding:10px 12px; border-bottom:2px solid var(--border); border-top:none; white-space:nowrap; }
        #creditListTable tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
        #creditListTable tbody tr:last-child { border-bottom:none; }
        #creditListTable tbody tr:hover { background:#f8fafc; }
        #creditListTable tbody td { padding:10px 12px; vertical-align:middle; border:none; font-size:13px; }

        .tx-mr { font-size:11px; color:var(--muted); font-family:'DM Mono',monospace; }
        .amount-pos { color:var(--success); font-weight:700; font-family:'DM Mono',monospace; }
        .amount-neg { color:var(--danger);  font-weight:700; font-family:'DM Mono',monospace; }
        .type-chip { display:inline-flex; align-items:center; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; }
        .type-deposit  { background:var(--success-lt); color:var(--success); }
        .type-withdraw { background:var(--danger-lt);  color:var(--danger); }
        .type-debit    { background:#f5f3ff; color:#7c3aed; }
        .type-credit   { background:var(--primary-lt); color:var(--primary); }
        .type-other    { background:#f1f5f9; color:#475569; }
        .status-chip { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
        .s-pending   { background:var(--warn-lt);    color:var(--warn); }
        .s-confirmed { background:var(--success-lt); color:var(--success); }
        .s-payment   { background:var(--danger-lt);  color:var(--danger); }
        .s-completed { background:var(--primary-lt); color:var(--primary); }
        .s-default   { background:#f1f5f9; color:#475569; }
        .row-num { font-family:'DM Mono',monospace; font-size:11px; color:var(--muted); }
        .date-main { font-size:12px; font-weight:600; font-family:'DM Mono',monospace; }

        #creditListTable_wrapper .dataTables_filter input { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; outline:none; width:180px; }
        #creditListTable_wrapper .dataTables_filter input:focus { border-color:var(--primary); }
        #creditListTable_wrapper .dataTables_info { font-size:12px; color:var(--muted); }
        #creditListTable_wrapper .page-link { font-size:12px; padding:5px 10px; color:var(--primary); border-color:var(--border); }
        #creditListTable_wrapper .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }

        .fade-up { opacity:0; transform:translateY(10px); animation:fadeUp .3s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.03s} .fade-up:nth-child(2){animation-delay:.06s}
        .fade-up:nth-child(3){animation-delay:.09s} .fade-up:nth-child(4){animation-delay:.12s}
        .fade-up:nth-child(5){animation-delay:.15s} .fade-up:nth-child(6){animation-delay:.18s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="pg-header fade-up">
            <div>
                <h1 class="pg-title">
                    <span class="icon-wrap"><i class="fa fa-exchange"></i></span>
                    {{ $user->name }}
                </h1>
                <div class="pg-sub">Transaction History</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                @if(auth()->user()->hasPermission('transaction_add'))
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTransactionModal"
                            style="height:36px;padding:0 16px;font-size:13px;font-weight:600;border-radius:8px;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fa fa-plus"></i> Add Transaction
                    </button>
                @endif
                <a href="{{ route('user.admin.wallet.transactions') }}" class="btn-back">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        @include('admin.message')

        {{-- User Info + Wallet --}}
        <div class="user-info-card fade-up">
            <div class="user-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
            <div class="user-detail">
                <div class="user-detail-name">{{ $user->name }}</div>
                <div class="user-detail-email">{{ $user->email ?? '' }}</div>
                @if($user->phone)
                    <div class="user-detail-email"><i class="fa fa-phone" style="font-size:10px;"></i> {{ $user->phone }}</div>
                @endif
            </div>
            <div class="user-wallet">
                <div class="user-wallet-label">Wallet Balance</div>
                <div class="user-wallet-val">৳{{ number_format($user->credit_balance ?? 0, 0) }}</div>
            </div>
        </div>

        {{-- Opening / Closing Balance Bar --}}
        <div class="balance-bar fade-up">
            <div class="bal-item">
                <div class="bal-label"><i class="fa fa-calendar-o"></i> Period</div>
                <div class="bal-value neutral" style="font-size:13px;">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                </div>
                <div class="bal-sub">Filtered range</div>
            </div>
            <div class="bal-item">
                <div class="bal-label"><i class="fa fa-history"></i> Opening Balance</div>
                <div class="bal-value {{ $openingBalance >= 0 ? 'positive' : 'negative' }}">
                    ৳{{ number_format($openingBalance, 0) }}
                </div>
                <div class="bal-sub">Before {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</div>
            </div>
            <div class="bal-item">
                <div class="bal-label"><i class="fa fa-arrow-down"></i> Total Received</div>
                <div class="bal-value positive">৳{{ number_format($periodReceived, 0) }}</div>
                <div class="bal-sub">Deposit in period</div>
            </div>
            <div class="bal-item">
                <div class="bal-label"><i class="fa fa-arrow-up"></i> Total Paid</div>
                <div class="bal-value negative">৳{{ number_format($periodPaid, 0) }}</div>
                <div class="bal-sub">Withdraw + Payment</div>
            </div>
            <div class="bal-item">
                <div class="bal-label"><i class="fa fa-check-circle"></i> Closing Balance</div>
                <div class="bal-value {{ $closingBalance >= 0 ? 'positive' : 'negative' }}">
                    ৳{{ number_format($closingBalance, 0) }}
                </div>
                <div class="bal-sub">Opening + Received − Paid</div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="stats-grid">
            <div class="stat-card c-deposit fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:var(--success-lt);color:var(--success);"><i class="fa fa-arrow-down"></i></div>
                    <span class="stat-badge" style="background:var(--success-lt);color:var(--success);">Confirmed</span>
                </div>
                <div class="stat-label">Deposit</div>
                <div class="stat-val">{{ number_format($depositConfirmedCount) }}</div>
                <div class="stat-amount" style="color:var(--success);">৳{{ number_format($depositConfirmedAmount, 0) }}</div>
                @if($depositPendingCount > 0)
                    <div class="stat-sub"><i class="fa fa-clock-o"></i> {{ $depositPendingCount }} pending</div>
                @endif
            </div>
            <div class="stat-card c-pending fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:var(--warn-lt);color:var(--warn);"><i class="fa fa-hourglass-half"></i></div>
                    <span class="stat-badge" style="background:var(--warn-lt);color:var(--warn);">Pending</span>
                </div>
                <div class="stat-label">Deposit Pending</div>
                <div class="stat-val">{{ number_format($depositPendingCount) }}</div>
                <div class="stat-amount" style="color:var(--warn);">৳{{ number_format($depositPendingAmount, 0) }}</div>
                <div class="stat-sub">Awaiting approval</div>
            </div>
            <div class="stat-card c-withdraw fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:var(--danger-lt);color:var(--danger);"><i class="fa fa-arrow-up"></i></div>
                    <span class="stat-badge" style="background:var(--danger-lt);color:var(--danger);">Confirmed</span>
                </div>
                <div class="stat-label">Withdraw</div>
                <div class="stat-val">{{ number_format($withdrawConfirmedCount) }}</div>
                <div class="stat-amount" style="color:var(--danger);">৳{{ number_format($withdrawConfirmedAmount, 0) }}</div>
                @if($withdrawPendingCount > 0)
                    <div class="stat-sub"><i class="fa fa-clock-o"></i> {{ $withdrawPendingCount }} pending</div>
                @endif
            </div>
            <div class="stat-card c-pending fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:var(--warn-lt);color:var(--warn);"><i class="fa fa-hourglass-half"></i></div>
                    <span class="stat-badge" style="background:var(--warn-lt);color:var(--warn);">Pending</span>
                </div>
                <div class="stat-label">Withdraw Pending</div>
                <div class="stat-val">{{ number_format($withdrawPendingCount) }}</div>
                <div class="stat-amount" style="color:var(--warn);">৳{{ number_format($withdrawPendingAmount, 0) }}</div>
                <div class="stat-sub">Awaiting approval</div>
            </div>
            <div class="stat-card c-payment fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:var(--primary-lt);color:var(--primary);"><i class="fa fa-credit-card"></i></div>
                    <span class="stat-badge" style="background:var(--primary-lt);color:var(--primary);">Debit</span>
                </div>
                <div class="stat-label">Payments</div>
                <div class="stat-val">{{ number_format($paymentCount) }}</div>
                <div class="stat-amount" style="color:var(--primary);">৳{{ number_format($paymentAmount, 0) }}</div>
                <div class="stat-sub">Booking payments</div>
            </div>
            <div class="stat-card c-refund fade-up">
                <div class="stat-top">
                    <div class="stat-icon" style="background:#ecfeff;color:#0891b2;"><i class="fa fa-undo"></i></div>
                    <span class="stat-badge" style="background:#ecfeff;color:#0891b2;">Credit</span>
                </div>
                <div class="stat-label">Refund / Credit</div>
                <div class="stat-val">{{ number_format($refundCount) }}</div>
                <div class="stat-amount" style="color:#0891b2;">৳{{ number_format($refundAmount, 0) }}</div>
                <div class="stat-sub">Refunded amount</div>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="filter-card fade-up">
            <span class="filter-label"><i class="fa fa-filter"></i> Filter</span>
            <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="">All Types</option>
                        @foreach(['deposit'=>'Deposit','withdraw'=>'Withdraw','debit'=>'Debit','credit'=>'Credit'] as $val => $label)
                            <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fa fa-search"></i> Apply
                </button>
                <a href="{{ url()->current() }}" class="btn-clear">
                    <i class="fa fa-times"></i> Clear
                </a>
            </form>
            <div id="export-btn-slot" style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap;"></div>
        </div>

        {{-- Table --}}
        <div class="tx-panel fade-up">
            <div class="table-scroll">
                <table id="creditListTable" class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:45px">#</th>
                        <th style="width:110px">Date</th>
                        <th style="width:130px" class="text-right">Receivable</th>
                        <th style="width:130px" class="text-right">Payable</th>
                        <th>Created By</th>
                        <th>Approved By</th>
                        <th style="width:90px">Type</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th style="width:80px" class="text-center">Image</th>
                        <th style="width:100px" class="text-center">Status</th>
                        <th>Remarks</th>
                        <th style="width:80px" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $row)
                        @php
                            $isDeposit  = in_array($row->type, ['deposit','credit','topup']);
                            $isWithdraw = in_array($row->type, ['withdraw','debit','payment']);
                            $typeClass  = match($row->type) {
                                'deposit','topup' => 'type-deposit',
                                'withdraw'        => 'type-withdraw',
                                'debit'           => 'type-debit',
                                'credit'          => 'type-credit',
                                default           => 'type-other',
                            };
                            $statusClass = match($row->status) {
                                'pending'   => 's-pending',
                                'confirmed' => 's-confirmed',
                                'payment'   => 's-payment',
                                'completed' => 's-completed',
                                default     => 's-default',
                            };
                        @endphp
                        <tr>
                            <td><span class="row-num">{{ $row->id }}</span></td>
                            <td>
                                <div class="date-main">{{ $row->deposit_date }}</div>
                                <div class="tx-mr">MR-{{ $row->id }}</div>
                            </td>
                            <td class="text-right">
                                @if($isDeposit)
                                    <span class="amount-pos">৳{{ number_format($row->amount, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($isWithdraw)
                                    <span class="amount-neg">৳{{ number_format($row->amount, 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;">{{ $row->creator->name ?? '—' }}</td>
                            <td style="font-size:12px;">{{ $row->update_user ? ($row->updater->name ?? '—') : '—' }}</td>
                            <td><span class="type-chip {{ $typeClass }}">{{ ucfirst($row->type) }}</span></td>
                            <td style="font-size:12px;">{{ $row->transaction_type ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--muted);">{{ $row->reference ?? '—' }}</td>
                            <td class="text-center">
                                @if($row->attachment_id)
                                    <img src="{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb') }}"
                                         style="width:46px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid var(--border);"
                                         onclick="showBigImage('{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full') }}')">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="status-chip {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                            </td>
                            <td style="font-size:12px;color:var(--muted);">{{ $row->remarks ?? '—' }}</td>
                            <td class="text-center">
                                @if(auth()->user()->hasPermission('transaction_delete'))
                                    <button class="btn btn-danger btn-sm px-2 btn-delete-transaction"
                                            data-id="{{ $row->id }}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="dt-footer-slot"></div>
        </div>

    </div>

    {{-- Image Overlay --}}
    <div id="imageOverlay" onclick="closeBigImage()"
         style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);z-index:99999;cursor:pointer;">
        <span style="position:absolute;top:20px;right:40px;color:#fff;font-size:36px;">&times;</span>
        <img id="bigImage" src="" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:90%;max-height:90%;border:3px solid #fff;border-radius:8px;">
    </div>

    {{-- Add Transaction Modal --}}
    <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
                <div class="modal-header" style="background:linear-gradient(135deg,#1a2a5e,#2563eb);border-radius:12px 12px 0 0;padding:18px 24px;">
                    <h5 class="modal-title text-white" style="font-weight:600;display:flex;align-items:center;gap:8px;">
                        <i class="fa fa-plus-circle"></i> Add Transaction
                        <small style="font-size:12px;opacity:.7;font-weight:400;">— {{ $user->name }}</small>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.8;">&times;</button>
                </div>

                <form action="{{ route('user.admin.wallet.store', $user->id) }}"
                      method="POST" enctype="multipart/form-data" id="addTransactionForm">
                    @csrf
                    <div class="modal-body" style="padding:24px;">

                        {{-- Current Balance Info --}}
                        <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
                            <i class="fa fa-wallet" style="color:#059669;font-size:16px;"></i>
                            <div>
                                <div style="font-size:11px;color:#059669;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Current Wallet Balance</div>
                                <div style="font-size:18px;font-weight:700;font-family:'DM Mono',monospace;color:#059669;">৳{{ number_format($user->credit_balance ?? 0, 2) }}</div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Type --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                                    Transaction Type <span class="text-danger">*</span>
                                </label>
                                <select name="type" class="form-control" required
                                        style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;"
                                        onchange="updateStatusOptions(this.value)">
                                    <option value="">-- Select Type --</option>
                                    <option value="deposit">💰 Deposit (Receivable)</option>
                                    <option value="withdraw">💸 Withdraw (Payable)</option>
                                    <option value="credit">↩ Credit / Refund</option>
                                    <option value="debit">↪ Debit / Charge</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="status" id="statusSelect" class="form-control" required
                                        style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;">
                                    <option value="confirmed">✅ Confirmed</option>
                                    <option value="pending">⏳ Pending</option>
                                    <option value="payment">💳 Payment</option>
                                    <option value="refund">↩ Refund</option>
                                    <option value="void">🚫 Void</option>
                                </select>
                            </div>

                            {{-- Amount --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                                    Amount (BDT) <span class="text-danger">*</span>
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;font-weight:700;color:#6b7280;">৳</span>
                                    <input type="number" name="credit_amount" required min="0.01" step="0.01"
                                           placeholder="0.00"
                                           style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:0 12px 0 28px;width:100%;outline:none;"
                                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Payment Method</label>
                                <input type="text" name="transaction_type" placeholder="e.g. brac_bank, cheque, cash"
                                       style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:0 12px;width:100%;outline:none;"
                                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                            </div>

                            {{-- Reference --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Reference / Cheque No</label>
                                <input type="text" name="reference" placeholder="Transaction / Cheque number"
                                       style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:0 12px;width:100%;outline:none;"
                                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                            </div>

                            {{-- Deposit Date --}}
                            <div class="col-md-6 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                                    Deposit Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="deposit_date" required value="{{ date('Y-m-d') }}"
                                       style="height:38px;font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:0 12px;width:100%;outline:none;font-family:'DM Mono',monospace;"
                                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                            </div>

                            {{-- Remarks --}}
                            <div class="col-md-12 mb-3">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">Remarks</label>
                                <textarea name="remarks" rows="2" placeholder="Optional notes..."
                                          style="font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:8px 12px;width:100%;outline:none;resize:none;"
                                          onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                            </div>

                            {{-- Attachment --}}
                            <div class="col-md-12 mb-2">
                                <label style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                                    Attachment <small style="font-weight:400;color:#9ca3af;">(Image optional)</small>
                                </label>
                                <input type="file" name="attachment_id" accept="image/*"
                                       style="font-size:13px;border-radius:7px;border:1px solid #e5e7eb;padding:6px 12px;width:100%;">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer" style="padding:14px 24px;border-top:1px solid #e5e7eb;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btn-add-transaction"
                                style="min-width:120px;">
                            <i class="fa fa-check"></i> Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';

            var exportTitle = '{{ addslashes($user->name) }} - Transactions';
            var exportOpts = {
                columns: [0,1,2,3,4,5,6,7,8,10,11],
                format: {
                    body: function(data, row, col, node) {
                        return $(node).text().replace(/\s+/g,' ').trim();
                    }
                }
            };

            var table = $('#creditListTable').DataTable({
                paging:    false,
                ordering:  true,
                info:      true,
                searching: true,
                order:     [],  // DB এর id DESC order maintain করবে
                dom: '<"d-none"B>t<"#dt-footer-slot"i>',
                buttons: [],
                columnDefs: [
                    { orderable: false, targets: [9, 12] },
                    { className: 'text-right',  targets: [2,3] },
                    { className: 'text-center', targets: [0,9,10,12] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: '🔍 Search...',
                    info: 'Showing _START_–_END_ of _TOTAL_ records',
                },
                initComplete: function() {
                    var api = this.api();
                    var btns = new $.fn.dataTable.Buttons(api, {
                        buttons: [
                            {
                                extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i> Excel',
                                className: 'btn btn-excel', title: exportTitle, exportOptions: exportOpts
                            },
                            {
                                extend: 'pdfHtml5', text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                className: 'btn btn-pdf', title: exportTitle,
                                orientation: 'landscape', pageSize: 'A4', exportOptions: exportOpts
                            },
                            {
                                extend: 'print', text: '<i class="fa fa-print"></i> Print',
                                className: 'btn btn-print2', title: exportTitle, exportOptions: exportOpts
                            }
                        ]
                    });
                    btns.container().appendTo('#export-btn-slot');

                    var searchInput = $('<input type="text" placeholder="🔍 Search..." style="height:32px;font-size:12px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;outline:none;width:160px;">');
                    searchInput.on('keyup', function() { api.search(this.value).draw(); });
                    $('<div></div>').append(searchInput).appendTo('#export-btn-slot');

                    $('#dt-footer-slot').appendTo('.tx-panel > div:last-child');
                }
            });

            // Delete
            $(document).on('click', '.btn-delete-transaction', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this transaction? Credit balance will be adjusted.')) return;
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: '{{ route("user.admin.wallet.delete", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) { alert('✅ ' + res.message); location.reload(); }
                        else { alert('❌ ' + res.message); btn.prop('disabled', false).html('<i class="fa fa-trash"></i>'); }
                    },
                    error: function(xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || 'Error'));
                        btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                    }
                });
            });
        });

        function showBigImage(src) { document.getElementById('bigImage').src = src; document.getElementById('imageOverlay').style.display = 'block'; }
        function closeBigImage()   { document.getElementById('imageOverlay').style.display = 'none'; }
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeBigImage(); });

        // Type এর উপর ভিত্তি করে status auto-select
        function updateStatusOptions(type) {
            var select = document.getElementById('statusSelect');
            var map = {
                'deposit':  'confirmed',
                'withdraw': 'confirmed',
                'credit':   'refund',
                'debit':    'payment',
            };
            if (map[type]) select.value = map[type];
        }

        // Form submit — double click prevent
        document.getElementById('addTransactionForm').addEventListener('submit', function() {
            var btn = document.getElementById('btn-add-transaction');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        });
    </script>
@endpush
