@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        :root {
            --primary:    #2563eb;
            --primary-lt: #eff6ff;
            --success:    #059669;
            --success-lt: #ecfdf5;
            --warn:       #d97706;
            --warn-lt:    #fffbeb;
            --danger:     #dc2626;
            --danger-lt:  #fef2f2;
            --info:       #0891b2;
            --info-lt:    #ecfeff;
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --surface:    #ffffff;
            --bg:         #f3f4f6;
            --text:       #111827;
            --radius:     10px;
            --shadow:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        }
        body, .content-area { background: var(--bg) !important; font-family: 'DM Sans', sans-serif; }

        /* Header */
        .pg-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .pg-title { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .pg-title .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; flex-shrink:0; }
        .breadcrumb-line { font-size:12px; color:var(--muted); margin-top:5px; display:flex; align-items:center; gap:5px; }
        .breadcrumb-line a { color:var(--muted); text-decoration:none; }
        .breadcrumb-line a:hover { color:var(--primary); }
        .breadcrumb-line .sep { font-size:9px; opacity:.5; }
        .btn-back { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:8px; font-size:13px; font-weight:500; color:var(--text); text-decoration:none; box-shadow:var(--shadow); transition:all .18s; }
        .btn-back:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* User card */
        .user-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:16px 20px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
        .user-avatar { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2); display:grid; place-items:center; color:#fff; font-size:18px; font-weight:700; flex-shrink:0; }
        .user-info { flex:1; min-width:0; }
        .user-info-name { font-size:15px; font-weight:600; color:var(--text); }
        .user-info-email { font-size:12px; color:var(--muted); margin-top:2px; }
        .user-stat { text-align:center; padding:0 16px; border-left:1px solid var(--border); }
        .user-stat-val { font-size:20px; font-weight:700; font-family:'DM Mono',monospace; line-height:1.1; }
        .user-stat-label { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-top:2px; }

        /* Filter card */
        .filter-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
        .filter-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .filter-group { display:flex; align-items:center; gap:6px; }
        .filter-group label { font-size:12px; color:var(--muted); margin:0; white-space:nowrap; }
        .filter-group input[type="date"] { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; font-family:'DM Mono',monospace; transition:border-color .15s; width:140px; }
        .filter-group input[type="date"]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); background:#fff; }
        .btn-filter { height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--primary); color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
        .btn-filter:hover { background:#1d4ed8; }
        .btn-clear { height:32px; padding:0 12px; font-size:12px; font-weight:500; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; transition:all .15s; }
        .btn-clear:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* Stats grid */
        .stats-grid { display:grid; grid-template-columns:repeat(8,1fr); gap:12px; margin-bottom:20px; }
        @media(max-width:1200px){ .stats-grid{ grid-template-columns:repeat(4,1fr); } }
        @media(max-width:768px) { .stats-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:480px) { .stats-grid{ grid-template-columns:1fr; } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 16px; box-shadow:var(--shadow); }
        .stat-card-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); margin-bottom:6px; }
        .stat-card-val { font-size:20px; font-weight:700; font-family:'DM Mono',monospace; line-height:1; }
        .stat-card-sub { font-size:11px; color:var(--muted); margin-top:4px; }
        .stat-card.total    { border-top:3px solid var(--primary); }
        .stat-card.paid     { border-top:3px solid var(--success); }
        .stat-card.due      { border-top:3px solid var(--danger); }
        .stat-card.issued   { border-top:3px solid #0891b2; }
        .stat-card.booked   { border-top:3px solid #7c3aed; }
        .stat-card.cancelled{ border-top:3px solid var(--danger); }
        .stat-card.pending  { border-top:3px solid var(--warn); }

        /* Panel */
        .bk-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .table-scroll { overflow-x:auto; }

        /* Export buttons */
        .btn-excel { background:#059669 !important; color:#fff !important; height:32px !important; padding:0 13px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:6px !important; border:none !important; }
        .btn-excel:hover { background:#047857 !important; }
        .btn-pdf   { background:#dc2626 !important; color:#fff !important; height:32px !important; padding:0 13px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:6px !important; border:none !important; }
        .btn-pdf:hover   { background:#b91c1c !important; }
        .btn-print-dt { background:#475569 !important; color:#fff !important; height:32px !important; padding:0 13px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:6px !important; border:none !important; }
        .btn-print-dt:hover { background:#334155 !important; }

        /* Table */
        #bookingsTable { width:100% !important; }
        #bookingsTable thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); padding:10px 12px; border-bottom:2px solid var(--border); border-top:none; white-space:nowrap; }
        #bookingsTable tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
        #bookingsTable tbody tr:last-child { border-bottom:none; }
        #bookingsTable tbody tr:hover { background:#f8fafc; }
        #bookingsTable tbody td { padding:11px 12px; vertical-align:middle; border:none; font-size:13px; }

        .bk-code { font-family:'DM Mono',monospace; font-weight:700; font-size:13px; color:var(--primary); text-decoration:none; }
        .bk-code:hover { text-decoration:underline; }
        .pnr-code { font-family:'DM Mono',monospace; font-size:11px; color:var(--muted); }
        .ticket-nums { font-size:10px; color:var(--success); }

        .route-cell { font-weight:600; font-size:13px; white-space:nowrap; }
        .route-arrow { color:var(--muted); font-size:10px; margin:0 4px; }

        .source-badge { display:inline-block; padding:2px 7px; border-radius:4px; font-size:10px; font-weight:700; background:#f1f5f9; color:#475569; font-family:'DM Mono',monospace; border:1px solid var(--border); }

        .type-badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; background:#f8fafc; color:var(--muted); border:1px solid var(--border); white-space:nowrap; }

        .status-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap; }
        .status-issued    { background:#ecfeff; color:#0891b2; }
        .status-booked    { background:#f5f3ff; color:#7c3aed; }
        .status-pending,
        .status-pnr_pending { background:var(--warn-lt); color:var(--warn); }
        .status-cancelled { background:var(--danger-lt); color:var(--danger); }
        .status-refunded  { background:var(--success-lt); color:var(--success); }
        .status-default   { background:#f1f5f9; color:#475569; }

        .paid-chip { display:inline-flex; align-items:center; gap:3px; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:600; background:var(--success-lt); color:var(--success); margin-top:3px; }

        .amount-val { font-family:'DM Mono',monospace; font-weight:600; font-size:13px; color:var(--text); }
        .amount-base { font-size:10px; color:var(--muted); margin-top:2px; }

        .pax-total { font-family:'DM Mono',monospace; font-weight:700; font-size:14px; }
        .pax-breakdown { font-size:10px; color:var(--muted); }

        .date-main { font-size:12px; font-weight:600; font-family:'DM Mono',monospace; color:var(--text); }
        .date-time { font-size:10px; color:var(--muted); margin-top:2px; }
        .row-num { font-family:'DM Mono',monospace; font-size:11px; color:var(--muted); }

        /* dt search */
        #bookingsTable_wrapper .dataTables_filter input { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; outline:none; width:180px; }
        #bookingsTable_wrapper .dataTables_filter input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        #bookingsTable_wrapper .dataTables_info { font-size:12px; color:var(--muted); }
        #bookingsTable_wrapper .page-link { font-size:12px; padding:5px 10px; color:var(--primary); border-color:var(--border); }
        #bookingsTable_wrapper .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }
        .dt-toolbar { padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#fafafa; }
        .dt-toolbar-left { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }

        /* Empty */
        .empty-state { padding:48px 20px; text-align:center; color:var(--muted); }
        .empty-state i { font-size:32px; margin-bottom:10px; display:block; opacity:.4; }

        /* Pagination */
        .pagination-wrap { padding:12px 16px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; align-items:center; gap:12px; flex-wrap:wrap; }
        .pagination-wrap .pagination { margin:0; }

        /* Fade */
        .fade-up { opacity:0; transform:translateY(10px); animation:fadeUp .3s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.03s}
        .fade-up:nth-child(2){animation-delay:.06s}
        .fade-up:nth-child(3){animation-delay:.09s}
        .fade-up:nth-child(4){animation-delay:.12s}
        .fade-up:nth-child(5){animation-delay:.15s}
        .fade-up:nth-child(6){animation-delay:.18s}
        .fade-up:nth-child(7){animation-delay:.21s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="pg-header fade-up">
            <div>
                <h1 class="pg-title">
                    <span class="icon-wrap"><i class="fa fa-plane"></i></span>
                    {{ $pageTitle ?? 'Bookings' }}
                </h1>
                <div class="breadcrumb-line">
                    <a href="{{ route('admin.users.index') }}">Users</a>
                    <span class="sep">›</span>
                    <strong>{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}</strong>
                    <span class="sep">›</span>
                    <span>Bookings</span>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Users
            </a>
        </div>

        @include('admin.message')

        {{-- User Info --}}
        <div class="user-card fade-up">
            <div class="user-avatar">
                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
            </div>
            <div class="user-info">
                <div class="user-info-name">
                    {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                </div>
                <div class="user-info-email">{{ $user->email ?? '' }}</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-val text-primary">{{ number_format($totalBookings) }}</div>
                <div class="user-stat-label">Total</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-val" style="color:var(--success);">{{ number_format($issuedCount) }}</div>
                <div class="user-stat-label">Issued</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-val" style="color:var(--danger);">{{ number_format($cancelledCount) }}</div>
                <div class="user-stat-label">Cancelled</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-val" style="color:{{ $creditBalance > 0 ? 'var(--success)' : 'var(--muted)' }};">
                    ৳{{ number_format($creditBalance, 0) }}
                </div>
                <div class="user-stat-label">Wallet Balance</div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="stats-grid">
            <div class="stat-card fade-up" style="border-top:3px solid #059669;">
                <div class="stat-card-label">Wallet Balance</div>
                <div class="stat-card-val" style="color:var(--success);font-size:15px;">৳{{ number_format($creditBalance, 0) }}</div>
                <div class="stat-card-sub">Wallet balance</div>
            </div>
            <div class="stat-card total fade-up">
                <div class="stat-card-label">Total Amount</div>
                <div class="stat-card-val" style="color:var(--primary);font-size:15px;">৳{{ number_format($totalPayNow, 0) }}</div>
                <div class="stat-card-sub">{{ number_format($totalBookings) }} bookings</div>
            </div>
            <div class="stat-card paid fade-up">
                <div class="stat-card-label">Total Paid</div>
                <div class="stat-card-val" style="color:var(--success);font-size:15px;">৳{{ number_format($totalPaid, 0) }}</div>
                <div class="stat-card-sub">Confirmed payments</div>
            </div>
            <div class="stat-card due fade-up">
                <div class="stat-card-label">Total Due</div>
                <div class="stat-card-val" style="color:var(--danger);font-size:15px;">৳{{ number_format($totalDue, 0) }}</div>
                <div class="stat-card-sub">Remaining balance</div>
            </div>

            <div class="stat-card issued fade-up">
                <div class="stat-card-label">Issued</div>
                <div class="stat-card-val" style="color:#0891b2;">{{ number_format($issuedCount) }}</div>
                <div class="stat-card-sub">Tickets issued</div>
            </div>
            <div class="stat-card booked fade-up">
                <div class="stat-card-label">Booked</div>
                <div class="stat-card-val" style="color:#7c3aed;">{{ number_format($bookedCount) }}</div>
                <div class="stat-card-sub">Confirmed</div>
            </div>
            <div class="stat-card cancelled fade-up">
                <div class="stat-card-label">Cancelled</div>
                <div class="stat-card-val" style="color:var(--danger);">{{ number_format($cancelledCount) }}</div>
                <div class="stat-card-sub">Cancelled</div>
            </div>
            <div class="stat-card pending fade-up">
                <div class="stat-card-label">Pending</div>
                <div class="stat-card-val" style="color:var(--warn);">{{ number_format($pendingCount) }}</div>
                <div class="stat-card-sub">Awaiting</div>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="filter-card fade-up">
            <span class="filter-label"><i class="fa fa-calendar"></i> Filter by Date</span>
            <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fa fa-search"></i> Apply
                </button>
                @if(request('date_from') || request('date_to'))
                    <a href="{{ url()->current() }}" class="btn-clear">
                        <i class="fa fa-times"></i> Clear
                    </a>
                @endif
            </form>
            <div id="export-btn-slot" style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap;"></div>
        </div>

        {{-- Bookings Table --}}
        <div class="bk-panel fade-up">
            <div class="table-scroll">
                <table id="bookingsTable" class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:45px">#</th>
                        <th>Booking</th>
                        <th>Route</th>
                        <th>Airline</th>
                        <th style="width:60px">Pax</th>
                        <th style="width:80px">Source</th>
                        <th style="width:90px">Type</th>
                        <th>Travel Date</th>
                        <th style="width:130px">Amount</th>
                        <th style="width:110px">Status</th>
                        <th style="width:110px">Booked At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($bookings as $i => $booking)
                        @php
                            $statusClass = match($booking->status) {
                                'issued'      => 'status-issued',
                                'booked'      => 'status-booked',
                                'pnr_pending' => 'status-pnr_pending',
                                'pending'     => 'status-pending',
                                'cancelled','canceled' => 'status-cancelled',
                                'refunded'    => 'status-refunded',
                                default       => 'status-default',
                            };
                            $statusLabel = match($booking->status) {
                                'issued'      => 'Issued',
                                'booked'      => 'Booked',
                                'pnr_pending' => 'PNR Pending',
                                'pending'     => 'Pending',
                                'cancelled','canceled' => 'Cancelled',
                                'refunded'    => 'Refunded',
                                default       => ucfirst($booking->status ?? '—'),
                            };
                            $typeLabel = match($booking->flight_type ?? '') {
                                'one_way'    => 'One Way',
                                'round_trip' => 'Round Trip',
                                'multi_city' => 'Multi City',
                                default      => ucfirst(str_replace('_',' ', $booking->flight_type ?? '—')),
                            };
                            $paxTotal = (int)($booking->adult_count ?? 0)
                                      + (int)($booking->child_count ?? 0)
                                      + (int)($booking->infant_count ?? 0);

                            // Export text
                            $exportRoute = ($booking->flight_from ?? '').' → '.($booking->flight_to ?? '');
                        @endphp
                        <tr>
                            <td><span class="row-num">{{ $i + 1 }}</span></td>

                            {{-- Booking Code --}}
                            <td>
                                <a href="{{ route('admin.users.booking.detail', [$user->id, $booking->id]) }}"
                                   class="bk-code">{{ $booking->code }}</a>
                                @if(!empty($booking->pnr_id))
                                    <div class="pnr-code">PNR: {{ $booking->pnr_id }}</div>
                                @endif
                                @if(!empty($booking->ticket_number))
                                    @php
                                        $tickets = is_string($booking->ticket_number)
                                            ? json_decode($booking->ticket_number, true)
                                            : $booking->ticket_number;
                                    @endphp
                                    @if(!empty($tickets))
                                        <div class="ticket-nums">
                                            <i class="fa fa-ticket"></i>
                                            {{ implode(', ', (array)$tickets) }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Route --}}
                            <td>
                            <span class="route-cell">
                                {{ $booking->flight_from ?? '—' }}
                                <span class="route-arrow">›</span>
                                {{ $booking->flight_to ?? '—' }}
                            </span>
                            </td>

                            {{-- Airline --}}
                            <td>
                            <span style="font-size:12px;color:var(--muted);">
                                {{ $booking->airline ?? '—' }}
                            </span>
                            </td>

                            {{-- Pax --}}
                            <td class="text-center">
                                <span class="pax-total">{{ $paxTotal }}</span>
                                <div class="pax-breakdown">
                                    @if(($booking->adult_count ?? 0) > 0) {{ $booking->adult_count }}A @endif
                                    @if(($booking->child_count ?? 0) > 0) {{ $booking->child_count }}C @endif
                                    @if(($booking->infant_count ?? 0) > 0) {{ $booking->infant_count }}I @endif
                                </div>
                            </td>

                            {{-- Source --}}
                            <td><span class="source-badge">{{ strtoupper($booking->source ?? '—') }}</span></td>

                            {{-- Type --}}
                            <td><span class="type-badge">{{ $typeLabel }}</span></td>

                            {{-- Travel Date --}}
                            <td>
                                @if($booking->start_date)
                                    <div class="date-main">{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</div>
                                @endif
                                @if($booking->end_date && $booking->end_date != $booking->start_date)
                                    <div class="date-time">→ {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</div>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td>
                                <div class="amount-val">{{ $booking->currency ?? 'BDT' }} {{ number_format($booking->total ?? 0, 2) }}</div>
                                @if(!empty($booking->base_fee))
                                    <div class="amount-base">Base: {{ number_format($booking->base_fee, 2) }}</div>
                                @endif
                                @if(!empty($booking->is_paid) && $booking->is_paid)
                                    <div class="paid-chip"><i class="fa fa-check" style="font-size:8px;"></i> Paid</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            </td>

                            {{-- Booked At --}}
                            <td>
                                <div class="date-main">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</div>
                                <div class="date-time">{{ \Carbon\Carbon::parse($booking->created_at)->format('h:i A') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="fa fa-plane"></i>
                                    <p>No bookings found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap" id="dt-pagination-slot"></div>
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

            var exportTitle = 'Bookings - {{ addslashes(trim(($user->first_name ?? "")." ".($user->last_name ?? ""))) }}';
            var exportOpts = {
                columns: [0,1,2,3,4,5,6,7,8,9,10],
                format: {
                    body: function(data, row, col, node) {
                        return $(node).text().replace(/\s+/g,' ').trim();
                    }
                }
            };

            var table = $('#bookingsTable').DataTable({
                paging:    false,
                ordering:  true,
                info:      true,
                searching: true,
                order:     [[10, 'desc']],
                columnDefs: [{ orderable: false, targets: [0] }],
                dom: '<"d-none"B>t<"#dt-info-wrap"i>',
                buttons: [],
                language: {
                    search: '',
                    searchPlaceholder: '🔍 Search...',
                    info: 'Showing _START_–_END_ of _TOTAL_ bookings',
                    zeroRecords: 'No matching bookings found'
                },
                initComplete: function() {
                    var api = this.api();

                    // Export buttons → filter card slot
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
                                className: 'btn btn-print-dt', title: exportTitle, exportOptions: exportOpts
                            }
                        ]
                    });
                    btns.container().appendTo('#export-btn-slot');

                    // Search box
                    var searchInput = $('<input type="text" placeholder="🔍 Search bookings..." style="height:32px;font-size:12px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;outline:none;width:170px;">');
                    searchInput.on('keyup', function() { api.search(this.value).draw(); });
                    $('<div></div>').append(searchInput).appendTo('#export-btn-slot');

                    // Info slot
                    $('#dt-info-wrap').appendTo('#dt-pagination-slot');
                }
            });
        });
    </script>
@endpush
