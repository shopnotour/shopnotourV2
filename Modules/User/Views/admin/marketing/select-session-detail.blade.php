@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --surface:    #ffffff;
            --bg:         #f3f4f6;
            --text:       #111827;
            --radius:     10px;
            --shadow:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md:  0 4px 16px rgba(0,0,0,.08);
        }

        body, .content-area { background: var(--bg) !important; font-family: 'DM Sans', sans-serif; }

        /* Header */
        .ss-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .ss-header h1 { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .ss-header h1 .icon-wrap { width:38px; height:38px; background:var(--warn-lt); border-radius:9px; display:grid; place-items:center; color:var(--warn); font-size:15px; flex-shrink:0; }
        .ss-header h1 small { font-size:14px; font-weight:400; color:var(--muted); margin-left:4px; }
        .btn-back { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:8px; font-size:13px; font-weight:500; color:var(--text); text-decoration:none; transition:all .18s; box-shadow:var(--shadow); }
        .btn-back:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* Stat Cards */
        .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:992px){ .stat-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:576px){ .stat-grid{ grid-template-columns:1fr; } }
        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:16px 18px; box-shadow:var(--shadow); display:flex; align-items:center; gap:14px; }
        .stat-icon { width:42px; height:42px; border-radius:10px; display:grid; place-items:center; font-size:16px; flex-shrink:0; }
        .stat-icon.blue  { background:var(--primary-lt); color:var(--primary); }
        .stat-icon.green { background:var(--success-lt); color:var(--success); }
        .stat-icon.amber { background:var(--warn-lt);    color:var(--warn); }
        .stat-icon.slate { background:#f1f5f9;            color:#475569; }
        .stat-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px; }
        .stat-value { font-size:15px; font-weight:600; color:var(--text); line-height:1.2; }
        .stat-value.accent { color:var(--warn); }

        /* Filter Card */
        .filter-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; flex-wrap:wrap; gap:12px; }
        .filter-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .filter-group { display:flex; align-items:center; gap:6px; }
        .filter-group label { font-size:12px; color:var(--muted); margin:0; white-space:nowrap; }
        .filter-group input[type="date"] { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; font-family:'DM Mono',monospace; transition:border-color .15s,box-shadow .15s; width:140px; }
        .filter-group input[type="date"]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); background:#fff; }
        .btn-filter { height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--primary); color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background .15s,transform .1s; }
        .btn-filter:hover { background:#1d4ed8; }
        .btn-filter:active { transform:scale(.97); }
        .btn-clear { height:32px; padding:0 12px; font-size:12px; font-weight:500; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; transition:all .15s; }
        .btn-clear:hover { background:var(--bg); color:var(--text); text-decoration:none; }

        /* Panel */
        .ss-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .ss-panel-head { padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; background:#fafafa; }
        .ss-panel-title { font-size:13px; font-weight:600; color:var(--text); display:flex; align-items:center; gap:7px; }
        .ss-panel-body { padding:0; }
        .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }

        /* DataTable overrides */
        #selectSessionTable_wrapper .dt-buttons { display:flex; gap:6px; flex-wrap:wrap; }
        #selectSessionTable_wrapper .dt-buttons .btn { height:32px !important; padding:0 13px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:6px !important; border:none !important; box-shadow:none !important; }
        .btn-excel { background:#059669 !important; color:#fff !important; }
        .btn-excel:hover { background:#047857 !important; }
        .btn-pdf   { background:#dc2626 !important; color:#fff !important; }
        .btn-pdf:hover   { background:#b91c1c !important; }
        .btn-print { background:#475569 !important; color:#fff !important; }
        .btn-print:hover { background:#334155 !important; }

        #selectSessionTable_wrapper .dataTables_filter { display:flex; align-items:center; }
        #selectSessionTable_wrapper .dataTables_filter label { font-size:12px; color:var(--muted); margin:0; display:flex; align-items:center; gap:8px; }
        #selectSessionTable_wrapper .dataTables_filter input { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; outline:none; transition:border-color .15s,box-shadow .15s; width:180px; }
        #selectSessionTable_wrapper .dataTables_filter input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        #selectSessionTable_wrapper .dataTables_info { font-size:12px; color:var(--muted); padding:10px 18px; }

        /* Table */
        #selectSessionTable { width:100% !important; border-collapse:collapse; }
        #selectSessionTable thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); padding:11px 14px; border-bottom:2px solid var(--border); border-right:none; border-left:none; border-top:none; white-space:nowrap; }
        #selectSessionTable tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
        #selectSessionTable tbody tr:last-child { border-bottom:none; }
        #selectSessionTable tbody tr:hover { background:#f8fafc; }
        #selectSessionTable tbody td { padding:12px 14px; vertical-align:top; border:none; font-size:13px; color:var(--text); }

        .row-num { font-family:'DM Mono',monospace; font-size:12px; color:var(--muted); background:var(--bg); border-radius:5px; padding:2px 7px; display:inline-block; }

        /* Source badge */
        .source-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.04em; white-space:nowrap; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }

        /* Leg card */
        .legs-wrap { display:flex; flex-direction:column; gap:8px; }
        .leg-card { background:var(--bg); border-radius:8px; padding:10px 12px; border-left:3px solid var(--primary); }
        .leg-card:nth-child(even) { border-left-color: var(--warn); }
        .leg-header { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
        .leg-type-badge { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:2px 8px; border-radius:4px; background:var(--primary-lt); color:var(--primary); }
        .leg-direct { font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px; background:var(--success-lt); color:var(--success); }
        .leg-stop { font-size:10px; font-weight:600; padding:2px 7px; border-radius:4px; background:#fef2f2; color:#dc2626; }
        .leg-duration { font-size:11px; color:var(--muted); font-family:'DM Mono',monospace; margin-left:auto; }

        /* Route row */
        .route-row { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .airport-block { display:flex; flex-direction:column; min-width:0; }
        .airport-code { font-family:'DM Mono',monospace; font-size:16px; font-weight:700; color:var(--text); line-height:1; }
        .airport-name-sm { font-size:11px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; }
        .airport-time { font-size:12px; font-weight:600; color:var(--primary); font-family:'DM Mono',monospace; margin-top:2px; }
        .airport-date { font-size:10px; color:var(--muted); }
        .arrow-block { display:flex; flex-direction:column; align-items:center; gap:2px; flex-shrink:0; }
        .arrow-line { height:1px; width:60px; background:var(--border); position:relative; }
        .arrow-line::after { content:'\25B6'; position:absolute; right:-6px; top:-6px; font-size:10px; color:var(--muted); }
        .arrow-label { font-size:9px; color:var(--muted); white-space:nowrap; }

        /* Airline */
        .airline-chip { display:inline-flex; align-items:center; gap:5px; padding:3px 8px; background:var(--surface); border:1px solid var(--border); border-radius:6px; font-size:11px; font-weight:600; color:var(--text); margin-top:6px; }
        .airline-chip img { width:18px; height:18px; object-fit:contain; border-radius:3px; }
        .airline-fn { font-family:'DM Mono',monospace; font-size:10px; color:var(--muted); }

        /* Price */
        .price-wrap { display:flex; flex-direction:column; gap:3px; min-width:130px; }
        .price-total { font-size:16px; font-weight:700; color:var(--text); font-family:'DM Mono',monospace; }
        .price-currency { font-size:10px; color:var(--muted); }
        .price-row { display:flex; justify-content:space-between; font-size:11px; color:var(--muted); }
        .price-row span:last-child { font-family:'DM Mono',monospace; }
        .discount-chip { display:inline-flex; align-items:center; gap:3px; padding:2px 7px; background:#fef9c3; border-radius:4px; font-size:10px; font-weight:600; color:#854d0e; margin-top:3px; }
        .refund-chip { display:inline-flex; align-items:center; gap:3px; padding:2px 7px; border-radius:4px; font-size:10px; font-weight:600; margin-top:3px; }
        .refund-yes { background:var(--success-lt); color:var(--success); }
        .refund-no  { background:#fef2f2; color:#dc2626; }

        /* Date cell */
        .date-cell { text-align:center; white-space:nowrap; }
        .date-main { font-size:12px; font-weight:600; color:var(--text); font-family:'DM Mono',monospace; }
        .date-time { font-size:11px; color:var(--muted); margin-top:2px; }

        /* Empty */
        .empty-state { padding:48px 20px; text-align:center; color:var(--muted); }
        .empty-state i { font-size:32px; margin-bottom:10px; display:block; opacity:.4; }

        /* Pagination */
        .pagination-wrap { padding:12px 18px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; align-items:center; gap:12px; flex-wrap:wrap; }
        .pagination-wrap .pagination { margin:0; }
        .pagination-wrap .page-link { font-size:12px; padding:5px 11px; border-color:var(--border); color:var(--primary); }
        .pagination-wrap .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }

        /* Checkbox & Delete */
        .select-checkbox { width:18px; height:18px; cursor:pointer; accent-color:var(--primary); }
        .btn-delete-selected { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--danger); color:#fff; cursor:pointer; transition:background .15s; }
        .btn-delete-selected:hover { background:#b91c1c; }
        .btn-delete-selected:disabled { opacity:.4; cursor:not-allowed; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.45); align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:28px 32px 24px; max-width:400px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,.2); text-align:center; }
        .modal-icon { font-size:40px; color:var(--danger); margin-bottom:12px; }
        .modal-title { font-size:17px; font-weight:700; color:var(--text); margin-bottom:8px; }
        .modal-desc { font-size:13px; color:var(--muted); margin-bottom:20px; line-height:1.5; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .modal-btn { padding:8px 22px; font-size:13px; font-weight:600; border-radius:8px; border:none; cursor:pointer; transition:background .15s; }
        .modal-btn-cancel { background:#f3f4f6; color:var(--text); }
        .modal-btn-cancel:hover { background:#e5e7eb; }
        .modal-btn-confirm { background:var(--danger); color:#fff; }
        .modal-btn-confirm:hover { background:#b91c1c; }

        /* Toolbar */
        .toolbar-wrap { padding:12px 18px 10px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; border-bottom:1px solid var(--border); }

        /* Fade */
        .fade-up { opacity:0; transform:translateY(12px); animation:fadeUp .35s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.04s}
        .fade-up:nth-child(2){animation-delay:.08s}
        .fade-up:nth-child(3){animation-delay:.12s}
        .fade-up:nth-child(4){animation-delay:.16s}
        .fade-up:nth-child(5){animation-delay:.20s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="ss-header fade-up">
            <h1>
                <span class="icon-wrap"><i class="fa fa-check-square-o"></i></span>
                {{ __('Price Check Details') }}
                <small>— {{ $user->first_name }} {{ $user->last_name }}</small>
            </h1>
            <a href="{{ route('admin.marketing.select.sessions') }}" class="btn-back">
                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @include('admin.message')

        {{-- Stat Cards --}}
        <div class="stat-grid">
            <div class="stat-card fade-up">
                <div class="stat-icon blue"><i class="fa fa-user"></i></div>
                <div>
                    <div class="stat-label">Name</div>
                    <div class="stat-value">{{ $user->first_name }} {{ $user->last_name }}</div>
                </div>
            </div>
            <div class="stat-card fade-up">
                <div class="stat-icon green"><i class="fa fa-envelope"></i></div>
                <div>
                    <div class="stat-label">Email</div>
                    <div class="stat-value" style="font-size:13px;">{{ $user->email }}</div>
                </div>
            </div>
            <div class="stat-card fade-up">
                <div class="stat-icon amber"><i class="fa fa-phone"></i></div>
                <div>
                    <div class="stat-label">Phone</div>
                    <div class="stat-value">{{ $user->phone }}</div>
                </div>
            </div>
            <div class="stat-card fade-up">
                <div class="stat-icon slate"><i class="fa fa-bar-chart"></i></div>
                <div>
                    <div class="stat-label">Total Price Checks</div>
                    <div class="stat-value accent">{{ number_format($sessions->total()) }}</div>
                </div>
            </div>
        </div>

        {{-- Main Table Panel --}}
        <div class="ss-panel fade-up">

            <div class="ss-panel-head">
                <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                    <span class="filter-label"><i class="fa fa-filter"></i> Filter by Date</span>
                    <div class="filter-group">
                        <label>From</label>
                        <input type="date" name="date_from"
                               value="{{ request('date_from', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label>To</label>
                        <input type="date" name="date_to"
                               value="{{ request('date_to', date('Y-m-d')) }}">
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
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <button type="button" id="deleteSelectedBtn" class="btn-delete-selected" disabled>
                        <i class="fa fa-trash"></i> {{ __('Delete Selected') }}
                    </button>
                    <div id="export-btn-slot" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;"></div>
                </div>
            </div>

            <form id="sessionsForm" method="POST" action="{{ route('admin.marketing.select.sessions.delete') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="table-scroll ss-panel-body">
                    <table id="selectSessionTable" class="table" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="width:40px;text-align:center;">
                                <input type="checkbox" id="selectAll" class="select-checkbox">
                            </th>
                            <th style="width:90px">Source</th>
                            <th>Flight Details</th>
                            <th style="width:160px">Price</th>
                            <th style="width:120px">Selected At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($sessions as $session)
                            @php
                                $raw    = json_decode($session->data, true) ?? [];
                                $flight = $raw['flight'] ?? [];
                                $source = strtoupper($raw['source'] ?? $flight['source'] ?? '—');
                                $legs   = $flight['legs'] ?? [];
                                $price  = $flight['price'] ?? [];
                                $refundable   = $flight['refundable'] ?? false;
                                $selectedAt   = $raw['selected_at'] ?? $session->created_at;
                                $validCarrier = $flight['validating_carrier'] ?? '';

                                // Export plain text
                                $exportText = $source.' | ';
                                foreach($legs as $leg) {
                                    $dep = $leg['departure'] ?? [];
                                    $arr = $leg['arrival']   ?? [];
                                    $seg = $leg['segments'][0] ?? [];
                                    $exportText .= ($dep['airport_code'] ?? '').' → '.($arr['airport_code'] ?? '');
                                    $exportText .= ' '.$leg['leg_type'].' | ';
                                    $exportText .= ($seg['carrier_name'] ?? '').' '.($seg['full_flight_number'] ?? '').' | ';
                                }
                                $exportText .= 'Total: '.number_format($price['total'] ?? 0, 2).' '.($price['currency'] ?? '');
                            @endphp
                            <tr>
                                {{-- # --}}
                                <td>
                                <span class="row-num">
                                    {{ ($sessions->currentPage() - 1) * $sessions->perPage() + $loop->iteration }}
                                </span>
                                </td>

                                {{-- Select --}}
                                <td style="text-align:center;">
                                    <input type="checkbox" name="ids[]" value="{{ $session->id }}" class="select-checkbox session-checkbox">
                                </td>

                                {{-- Source --}}
                                <td>
                                <span class="source-badge">
                                    <i class="fa fa-server" style="font-size:10px;"></i>
                                    {{ $source }}
                                </span>
                                    @if($validCarrier)
                                        <div style="margin-top:5px;">
                                            <span style="font-size:10px;color:var(--muted);">Validating:</span>
                                            <span style="font-size:11px;font-weight:600;font-family:'DM Mono',monospace;">{{ $validCarrier }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Flight Details --}}
                                <td>
                                    <span class="d-none export-text">{{ $exportText }}</span>
                                    <div class="legs-wrap">
                                        @forelse($legs as $legIdx => $leg)
                                            @php
                                                $dep     = $leg['departure'] ?? [];
                                                $arr     = $leg['arrival']   ?? [];
                                                $legType = strtoupper($leg['leg_type'] ?? '');
                                                $isDirect = $leg['is_direct'] ?? true;
                                                $stops   = $leg['stops'] ?? 0;
                                                $dur     = $leg['duration_formatted'] ?? '';
                                                $firstSeg = $leg['segments'][0] ?? [];
                                                $allSegs  = $leg['segments'] ?? [];
                                            @endphp
                                            <div class="leg-card">
                                                {{-- Leg Header --}}
                                                <div class="leg-header">
                                                    <span class="leg-type-badge">{{ $legType }}</span>
                                                    @if($isDirect)
                                                        <span class="leg-direct"><i class="fa fa-check"></i> Direct</span>
                                                    @else
                                                        <span class="leg-stop"><i class="fa fa-circle" style="font-size:8px;"></i> {{ $stops }} Stop{{ $stops > 1 ? 's' : '' }}</span>
                                                    @endif
                                                    @if($dur)
                                                        <span class="leg-duration"><i class="fa fa-clock-o"></i> {{ $dur }}</span>
                                                    @endif
                                                </div>

                                                {{-- Route --}}
                                                <div class="route-row">
                                                    {{-- Departure --}}
                                                    <div class="airport-block">
                                                        <span class="airport-code">{{ $dep['airport_code'] ?? '—' }}</span>
                                                        <span class="airport-name-sm">{{ $dep['airport_name'] ?? '' }}</span>
                                                        @if(!empty($dep['time_12h']))
                                                            <span class="airport-time">{{ $dep['time_12h'] }}</span>
                                                        @endif
                                                        @if(!empty($dep['date']))
                                                            <span class="airport-date">{{ \Carbon\Carbon::parse($dep['date'])->format('d M Y') }}</span>
                                                        @endif
                                                        @if(!empty($dep['terminal']))
                                                            <span class="airport-date">T{{ $dep['terminal'] }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- Arrow --}}
                                                    <div class="arrow-block">
                                                        <div class="arrow-line"></div>
                                                        <span class="arrow-label">{{ $dur }}</span>
                                                    </div>

                                                    {{-- Arrival --}}
                                                    <div class="airport-block">
                                                        <span class="airport-code">{{ $arr['airport_code'] ?? '—' }}</span>
                                                        <span class="airport-name-sm">{{ $arr['airport_name'] ?? '' }}</span>
                                                        @if(!empty($arr['time_12h']))
                                                            <span class="airport-time">{{ $arr['time_12h'] }}</span>
                                                        @endif
                                                        @if(!empty($arr['date']))
                                                            <span class="airport-date">
                                                            {{ \Carbon\Carbon::parse($arr['date'])->format('d M Y') }}
                                                                @if(!empty($arr['date_adjustment']) && $arr['date_adjustment'] != 0)
                                                                    <span style="color:var(--danger);font-weight:700;">
                                                                    {{ $arr['date_adjustment'] > 0 ? '+'.$arr['date_adjustment'] : $arr['date_adjustment'] }}
                                                                </span>
                                                                @endif
                                                        </span>
                                                        @endif
                                                        @if(!empty($arr['terminal']))
                                                            <span class="airport-date">T{{ $arr['terminal'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Airlines per segment --}}
                                                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:7px;">
                                                    @foreach($allSegs as $seg)
                                                        <div class="airline-chip">
                                                            @if(!empty($seg['carrier_images']['thumb']))
                                                                <img src="{{ $seg['carrier_images']['thumb'] }}"
                                                                     alt="{{ $seg['carrier_name'] ?? '' }}"
                                                                     onerror="this.style.display='none'">
                                                            @endif
                                                            <span>{{ $seg['carrier_name'] ?? $seg['carrier'] ?? '—' }}</span>
                                                            <span class="airline-fn">{{ $seg['full_flight_number'] ?? '' }}</span>
                                                            @if(!empty($seg['aircraft_name']))
                                                                <span style="font-size:10px;color:var(--muted);">· {{ $seg['aircraft_name'] }}</span>
                                                            @endif
                                                            @if(!empty($seg['fare_info']['cabin_name']))
                                                                <span style="font-size:10px;background:#f1f5f9;padding:1px 5px;border-radius:3px;color:#475569;">
                                                                {{ $seg['fare_info']['cabin_name'] }}
                                                            </span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>
                                        @empty
                                            <span class="text-muted" style="font-size:12px;">No leg data</span>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- Price --}}
                                <td>
                                    @if(!empty($price))
                                        <div class="price-wrap">
                                            <div>
                                                <span class="price-total">{{ number_format($price['total'] ?? 0, 0) }}</span>
                                                <span class="price-currency"> {{ $price['currency'] ?? '' }}</span>
                                            </div>
                                            @if(!empty($price['api_base_fare']))
                                                <div class="price-row">
                                                    <span>Base Fare</span>
                                                    <span>{{ number_format($price['api_base_fare'], 0) }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($price['api_tax']))
                                                <div class="price-row">
                                                    <span>Tax</span>
                                                    <span>{{ number_format($price['api_tax'], 0) }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($price['service_charge']))
                                                <div class="price-row">
                                                    <span>Service</span>
                                                    <span>{{ number_format($price['service_charge'], 0) }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($price['total_discounts']))
                                                <div class="discount-chip">
                                                    <i class="fa fa-tag" style="font-size:9px;"></i>
                                                    -{{ number_format($price['total_discounts'], 0) }} discount
                                                </div>
                                            @endif
                                            <div class="refund-chip {{ $refundable ? 'refund-yes' : 'refund-no' }}">
                                                <i class="fa fa-{{ $refundable ? 'check' : 'times' }}" style="font-size:9px;"></i>
                                                {{ $refundable ? 'Refundable' : 'Non-Refundable' }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endif
                                </td>

                                {{-- Selected At --}}
                                <td class="date-cell">
                                    <div class="date-main">
                                        {{ \Carbon\Carbon::parse($selectedAt)->format('d M Y') }}
                                    </div>
                                    <div class="date-time">
                                        {{ \Carbon\Carbon::parse($selectedAt)->format('h:i A') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fa fa-inbox"></i>
                                        <p>No price check sessions found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            {{-- Pagination --}}
            <div class="pagination-wrap">
                {{ $sessions->appends(request()->only(['date_from','date_to']))->links() }}
            </div>
        </div>

    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon"><i class="fa fa-exclamation-triangle"></i></div>
            <div class="modal-title">{{ __('Confirm Delete') }}</div>
            <div class="modal-desc">{{ __('Are you sure you want to delete the selected price check sessions? This action cannot be undone.') }}</div>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn-cancel" id="modalCancel">{{ __('Cancel') }}</button>
                <button type="button" class="modal-btn modal-btn-confirm" id="modalConfirm">{{ __('Yes, Delete') }}</button>
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
            var userName    = '{{ addslashes($user->first_name." ".$user->last_name) }}';
            var exportTitle = 'Price Check Sessions - ' + userName;

            // Export: exclude checkbox column (col index 1)
            var exportOpts = {
                columns: [0, 2, 3, 4, 5],
                format: {
                    body: function (data, row, col, node) {
                        if (col === 3) {
                            var exp = $(node).find('.export-text').text().trim();
                            return exp || $(node).find('.legs-wrap').text().replace(/\s+/g, ' ').trim();
                        }
                        return $(node).text().replace(/\s+/g, ' ').trim();
                    }
                }
            };

            // Select All toggle
            $('#selectAll').on('change', function () {
                $('.session-checkbox').prop('checked', this.checked);
                toggleDeleteBtn();
            });

            // Individual checkbox change
            $(document).on('change', '.session-checkbox', function () {
                var allChecked = $('.session-checkbox:checked').length === $('.session-checkbox').length;
                $('#selectAll').prop('checked', allChecked);
                toggleDeleteBtn();
            });

            function toggleDeleteBtn() {
                var checked = $('.session-checkbox:checked').length > 0;
                $('#deleteSelectedBtn').prop('disabled', !checked);
            }

            // Show modal on delete button click
            $('#deleteSelectedBtn').on('click', function () {
                var checked = $('.session-checkbox:checked').length;
                if (checked === 0) return;
                $('#deleteModal').addClass('active');
            });

            // Modal: Cancel
            $('#modalCancel, #deleteModal').on('click', function (e) {
                if (e.target === this) $('#deleteModal').removeClass('active');
            });

            // Modal: Confirm → submit form
            $('#modalConfirm').on('click', function () {
                $('#deleteModal').removeClass('active');
                $('#sessionsForm').submit();
            });

            var table = $('#selectSessionTable').DataTable({
                paging:    false,
                ordering:  true,
                info:      true,
                searching: true,
                dom:       '<"d-none"B>t<"#dt-info-slot"i>',
                buttons:   [],
                language: {
                    search: '',
                    searchPlaceholder: '\uD83D\uDD0D Search...',
                    info: 'Showing _START_–_END_ of _TOTAL_ records',
                    infoEmpty: 'No records',
                    zeroRecords: 'No matching records found'
                },
                initComplete: function () {
                    var api = this.api();

                    // Export buttons
                    var btnContainer = new $.fn.dataTable.Buttons(api, {
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
                                className: 'btn btn-print', title: exportTitle, exportOptions: exportOpts
                            }
                        ]
                    });
                    btnContainer.container().appendTo('#export-btn-slot');

                    // Search box
                    var searchWrap = $('<div style="display:flex;align-items:center;gap:6px;"></div>');
                    var searchInput = $('<input type="text" placeholder="\uD83D\uDD0D Search..." style="height:32px;font-size:12px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;outline:none;width:170px;">');
                    searchInput.on('keyup', function () { api.search(this.value).draw(); });
                    searchWrap.append(searchInput);
                    searchWrap.appendTo('#export-btn-slot');

                    $('#dt-info-slot').appendTo('.pagination-wrap');
                }
            });
        });
    </script>
@endpush
