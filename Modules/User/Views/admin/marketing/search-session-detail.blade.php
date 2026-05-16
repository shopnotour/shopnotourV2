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

        /* ── Page Header ── */
        .ss-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .ss-header h1 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ss-header h1 .icon-wrap {
            width: 38px; height: 38px;
            background: var(--primary-lt);
            border-radius: 9px;
            display: grid; place-items: center;
            color: var(--primary);
            font-size: 15px;
            flex-shrink: 0;
        }
        .ss-header h1 small {
            font-size: 14px;
            font-weight: 400;
            color: var(--muted);
            margin-left: 4px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            text-decoration: none;
            transition: all .18s;
            box-shadow: var(--shadow);
        }
        .btn-back:hover { background: var(--bg); color: var(--text); text-decoration: none; }

        /* ── Stat Cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        @media (max-width: 992px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 576px) { .stat-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 18px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: grid; place-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .stat-icon.blue   { background: var(--primary-lt); color: var(--primary); }
        .stat-icon.green  { background: var(--success-lt); color: var(--success); }
        .stat-icon.amber  { background: var(--warn-lt);    color: var(--warn);    }
        .stat-icon.slate  { background: #f1f5f9;           color: #475569;        }

        .stat-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 2px; }
        .stat-value { font-size: 15px; font-weight: 600; color: var(--text); line-height: 1.2; }
        .stat-value.accent { color: var(--primary); }

        /* ── Filter Card ── */
        .filter-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-group label {
            font-size: 12px;
            color: var(--muted);
            margin: 0;
            white-space: nowrap;
        }
        .filter-group input[type="date"] {
            height: 32px;
            font-size: 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 0 10px;
            color: var(--text);
            background: var(--bg);
            outline: none;
            font-family: 'DM Mono', monospace;
            transition: border-color .15s, box-shadow .15s;
            width: 140px;
        }
        .filter-group input[type="date"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
            background: #fff;
        }
        .btn-filter {
            height: 32px;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 7px;
            border: none;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .15s, transform .1s;
        }
        .btn-filter:hover { background: #1d4ed8; }
        .btn-filter:active { transform: scale(.97); }
        .btn-clear {
            height: 32px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all .15s;
        }
        .btn-clear:hover { background: var(--bg); color: var(--text); text-decoration: none; }

        /* ── Main Panel ── */
        .ss-panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .ss-panel-head {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            background: #fafafa;
        }
        .ss-panel-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .ss-panel-body { padding: 0; }

        /* ── DataTable overrides ── */
        #searchSessionTable_wrapper .dt-buttons { display: flex; gap: 6px; flex-wrap: wrap; }
        #searchSessionTable_wrapper .dt-buttons .btn {
            height: 32px !important;
            padding: 0 13px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            border-radius: 7px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            border: none !important;
            box-shadow: none !important;
        }
        .btn-excel { background: #059669 !important; color: #fff !important; }
        .btn-excel:hover { background: #047857 !important; }
        .btn-pdf   { background: #dc2626 !important; color: #fff !important; }
        .btn-pdf:hover   { background: #b91c1c !important; }
        .btn-print { background: #475569 !important; color: #fff !important; }
        .btn-print:hover { background: #334155 !important; }

        #searchSessionTable_wrapper .dataTables_filter {
            display: flex; align-items: center;
        }
        #searchSessionTable_wrapper .dataTables_filter label {
            font-size: 12px; color: var(--muted); margin: 0; display: flex; align-items: center; gap: 8px;
        }
        #searchSessionTable_wrapper .dataTables_filter input {
            height: 32px;
            font-size: 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 0 10px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            width: 180px;
        }
        #searchSessionTable_wrapper .dataTables_filter input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        #searchSessionTable_wrapper .dataTables_info {
            font-size: 12px; color: var(--muted); padding: 10px 18px;
        }
        .toolbar-wrap {
            padding: 12px 18px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid var(--border);
        }

        /* ── Table ── */
        #searchSessionTable { width: 100% !important; border-collapse: collapse; }
        #searchSessionTable thead th {
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            padding: 11px 14px;
            border-bottom: 2px solid var(--border);
            border-right: none;
            border-left: none;
            border-top: none;
            white-space: nowrap;
        }
        #searchSessionTable tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .12s;
        }
        #searchSessionTable tbody tr:last-child { border-bottom: none; }
        #searchSessionTable tbody tr:hover { background: #f8fafc; }
        #searchSessionTable tbody td {
            padding: 12px 14px;
            vertical-align: top;
            border: none;
            font-size: 13px;
            color: var(--text);
        }
        .row-num {
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            color: var(--muted);
            background: var(--bg);
            border-radius: 5px;
            padding: 2px 7px;
            display: inline-block;
        }

        /* ── Badges ── */
        .trip-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .trip-round   { background: var(--primary-lt); color: var(--primary); }
        .trip-oneway  { background: var(--success-lt); color: var(--success); }
        .trip-multi   { background: var(--warn-lt);    color: var(--warn);    }
        .trip-unknown { background: #f1f5f9;           color: #475569;        }

        .class-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            font-family: 'DM Mono', monospace;
            letter-spacing: .03em;
        }

        /* ── Passengers ── */
        .pax-wrap { display: flex; flex-wrap: wrap; gap: 5px; }
        .pax-chip {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            background: #f8fafc;
            border: 1px solid var(--border);
            color: var(--text);
            white-space: nowrap;
        }

        /* ── Route Segments ── */
        .segments-wrap { display: flex; flex-direction: column; gap: 7px; }
        .segment-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            padding: 7px 10px;
            background: var(--bg);
            border-radius: 7px;
            border-left: 3px solid var(--primary);
        }
        .segment-row:nth-child(even) { border-left-color: var(--success); }
        .segment-row:nth-child(3n)   { border-left-color: var(--warn); }

        .airport-name { font-size: 12px; font-weight: 600; color: var(--text); }
        .airport-code-chip {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1px 5px;
            color: var(--primary);
        }
        .seg-arrow {
            color: var(--muted);
            font-size: 11px;
            flex-shrink: 0;
        }
        .dep-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: var(--muted);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 2px 7px;
            font-family: 'DM Mono', monospace;
            margin-left: auto;
        }
        .return-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: var(--warn);
            background: var(--warn-lt);
            border-radius: 5px;
            padding: 3px 9px;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ── Date Cell ── */
        .date-cell { text-align: center; white-space: nowrap; }
        .date-main { font-size: 12px; font-weight: 600; color: var(--text); font-family: 'DM Mono', monospace; }
        .date-time { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* ── Empty State ── */
        .empty-state {
            padding: 48px 20px;
            text-align: center;
            color: var(--muted);
        }
        .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; opacity: .4; }
        .empty-state p { margin: 0; font-size: 14px; }

        /* ── Pagination ── */
        .pagination-wrap { padding: 12px 18px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; }
        .pagination-wrap .pagination { margin: 0; }
        .pagination-wrap .page-link {
            font-size: 12px;
            padding: 5px 11px;
            border-color: var(--border);
            color: var(--primary);
        }
        .pagination-wrap .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* ── Responsive table scroll ── */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* ── Fade-in animation ── */
        .fade-up {
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp .35s ease forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up:nth-child(1) { animation-delay: .04s; }
        .fade-up:nth-child(2) { animation-delay: .08s; }
        .fade-up:nth-child(3) { animation-delay: .12s; }
        .fade-up:nth-child(4) { animation-delay: .16s; }

        /* ── Checkbox & Delete ── */
        .select-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); }
        .btn-delete-selected {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; font-size: 12px; font-weight: 600;
            border-radius: 7px; border: none;
            background: var(--danger); color: #fff; cursor: pointer;
            transition: background .15s;
        }
        .btn-delete-selected:hover { background: #b91c1c; }
        .btn-delete-selected:disabled { opacity: .4; cursor: not-allowed; }

        /* ── Modal ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,.45); align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff; border-radius: 12px; padding: 28px 32px 24px;
            max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.2);
            text-align: center;
        }
        .modal-icon { font-size: 40px; color: var(--danger); margin-bottom: 12px; }
        .modal-title { font-size: 17px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .modal-desc { font-size: 13px; color: var(--muted); margin-bottom: 20px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-btn {
            padding: 8px 22px; font-size: 13px; font-weight: 600; border-radius: 8px;
            border: none; cursor: pointer; transition: background .15s;
        }
        .modal-btn-cancel { background: #f3f4f6; color: var(--text); }
        .modal-btn-cancel:hover { background: #e5e7eb; }
        .modal-btn-confirm { background: var(--danger); color: #fff; }
        .modal-btn-confirm:hover { background: #b91c1c; }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="ss-header fade-up">
            <h1>
                <span class="icon-wrap"><i class="fa fa-search"></i></span>
                {{ __('Search Details') }}
                <small>— {{ $user->first_name }} {{ $user->last_name }}</small>
            </h1>
            <a href="{{ route('admin.marketing.search.sessions') }}" class="btn-back">
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
                    <div class="stat-label">Total Searches</div>
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

            <form id="sessionsForm" method="POST" action="{{ route('admin.marketing.search.sessions.delete') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="table-scroll ss-panel-body">
                    <table id="searchSessionTable" class="table" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th style="width:40px;text-align:center;">
                                <input type="checkbox" id="selectAll" class="select-checkbox">
                            </th>
                            <th style="width:90px">Type</th>
                            <th style="width:110px">Class</th>
                            <th style="width:130px">Passengers</th>
                            <th>Route(s)</th>
                            <th style="width:120px">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($sessions as $session)
                            @php
                                $data        = json_decode($session->data, true);
                                $tripType    = strtoupper($data['trip_type']    ?? '');
                                $travelClass = strtoupper($data['travel_class'] ?? '');
                                $adults      = (int)($data['adults']   ?? 0);
                                $children    = (int)($data['children'] ?? 0);
                                $infants     = (int)($data['infants']  ?? 0);
                                $segments    = $data['segments']    ?? [];
                                $returnDate  = $data['return_date'] ?? null;

                                // Plain text for export
                                $routeExport = '';
                                foreach ($segments as $seg) {
                                    $f = $airports[$seg['from']] ?? null;
                                    $t = $airports[$seg['to']]   ?? null;
                                    $routeExport .= ($f ? $f->name.' ('.$f->code.')' : 'ID:'.$seg['from']);
                                    $routeExport .= ' → ';
                                    $routeExport .= ($t ? $t->name.' ('.$t->code.')' : 'ID:'.$seg['to']);
                                    if (!empty($seg['departure'])) $routeExport .= ' | Dep: '.$seg['departure'];
                                    $routeExport .= "\n";
                                }
                                if ($returnDate && $tripType === 'ROUND') $routeExport .= 'Return: '.$returnDate;
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

                                {{-- Trip Type --}}
                                <td>
                                    @if($tripType === 'ROUND')
                                        <span class="trip-badge trip-round"><i class="fa fa-refresh"></i> Round</span>
                                    @elseif($tripType === 'ONEWAY')
                                        <span class="trip-badge trip-oneway"><i class="fa fa-arrow-right"></i> One Way</span>
                                    @elseif($tripType === 'MULTI')
                                        <span class="trip-badge trip-multi"><i class="fa fa-random"></i> Multi</span>
                                    @else
                                        <span class="trip-badge trip-unknown">{{ $tripType ?: '—' }}</span>
                                    @endif
                                </td>

                                {{-- Class --}}
                                <td><span class="class-badge">{{ $travelClass ?: '—' }}</span></td>

                                {{-- Passengers --}}
                                <td>
                                    <div class="pax-wrap">
                                        @if($adults)
                                            <span class="pax-chip">👤 {{ $adults }} Adult{{ $adults > 1 ? 's' : '' }}</span>
                                        @endif
                                        @if($children)
                                            <span class="pax-chip">🧒 {{ $children }} Child{{ $children > 1 ? 'ren' : '' }}</span>
                                        @endif
                                        @if($infants)
                                            <span class="pax-chip">👶 {{ $infants }} Infant{{ $infants > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Route --}}
                                <td>
                                    {{-- Hidden export text --}}
                                    <span class="d-none export-text">{{ $routeExport }}</span>

                                    {{-- Visual --}}
                                    <div class="segments-wrap">
                                        @foreach($segments as $i => $segment)
                                            @php
                                                $from = $airports[$segment['from']] ?? null;
                                                $to   = $airports[$segment['to']]   ?? null;
                                            @endphp
                                            <div class="segment-row">
                                            <span class="airport-name">
                                                {{ $from ? $from->name : 'ID:'.$segment['from'] }}
                                            </span>
                                                @if($from)
                                                    <span class="airport-code-chip">{{ $from->code }}</span>
                                                @endif

                                                <span class="seg-arrow"><i class="fa fa-long-arrow-right"></i></span>

                                                <span class="airport-name">
                                                {{ $to ? $to->name : 'ID:'.$segment['to'] }}
                                            </span>
                                                @if($to)
                                                    <span class="airport-code-chip">{{ $to->code }}</span>
                                                @endif

                                                @if(!empty($segment['departure']))
                                                    <span class="dep-chip">
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($segment['departure'])->format('d M Y') }}
                                                </span>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if($returnDate && $tripType === 'ROUND')
                                            <div class="return-chip">
                                                <i class="fa fa-undo"></i>
                                                Return: {{ \Carbon\Carbon::parse($returnDate)->format('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Date --}}
                                <td class="date-cell">
                                    <div class="date-main">
                                        {{ \Carbon\Carbon::parse($session->created_at)->format('d M Y') }}
                                    </div>
                                    <div class="date-time">
                                        {{ \Carbon\Carbon::parse($session->created_at)->format('h:i A') }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fa fa-inbox"></i>
                                        <p>No search sessions found.</p>
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
            <div class="modal-desc">{{ __('Are you sure you want to delete the selected search sessions? This action cannot be undone.') }}</div>
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
            var userName = '{{ addslashes($user->first_name." ".$user->last_name) }}';
            var exportTitle = 'Search Sessions - ' + userName;

            // Export: exclude checkbox column (col index 1)
            var exportOpts = {
                columns: [0, 2, 3, 4, 5, 6],
                format: {
                    body: function (data, row, col, node) {
                        // col 0=#, 2=Type, 3=Class, 4=Passengers, 5=Route, 6=Date
                        if (col === 5) {
                            var exp = $(node).find('.export-text').text().trim();
                            return exp || $(node).find('.segments-wrap').text().replace(/\s+/g, ' ').trim();
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

            $('#searchSessionTable').DataTable({
                paging:   false,
                ordering: true,
                info:     true,
                searching: true,
                dom: '<"d-none"B>t<"#dt-info-slot"i>',
                buttons: [],
                language: {
                    search:   '',
                    searchPlaceholder: '🔍  Search anything...',
                    info:     'Showing _START_–_END_ of _TOTAL_ records',
                    infoEmpty: 'No records',
                    zeroRecords: 'No matching records found'
                },
                initComplete: function() {
                    // Buttons গুলো filter card এর ডানে নিয়ে যাও
                    var api = this.api();
                    var btnContainer = new $.fn.dataTable.Buttons(api, {
                        buttons: [
                            {
                                extend:        'excelHtml5',
                                text:          '<i class="fa fa-file-excel-o"></i> Excel',
                                className:     'btn btn-excel',
                                title:         exportTitle,
                                exportOptions: exportOpts
                            },
                            {
                                extend:        'pdfHtml5',
                                text:          '<i class="fa fa-file-pdf-o"></i> PDF',
                                className:     'btn btn-pdf',
                                title:         exportTitle,
                                orientation:   'landscape',
                                pageSize:      'A4',
                                exportOptions: exportOpts
                            },
                            {
                                extend:    'print',
                                text:      '<i class="fa fa-print"></i> Print',
                                className: 'btn btn-print',
                                title:     exportTitle,
                                exportOptions: exportOpts
                            }
                        ]
                    });
                    btnContainer.container().appendTo('#export-btn-slot');

                    // Search box
                    var searchWrap = $('<div style="display:flex;align-items:center;gap:6px;"></div>');
                    var searchInput = $('<input type="text" placeholder="🔍 Search..." style="height:32px;font-size:12px;border:1px solid #e5e7eb;border-radius:7px;padding:0 10px;outline:none;width:170px;">');
                    searchInput.on('keyup', function() {
                        api.search(this.value).draw();
                    });
                    searchWrap.append(searchInput);
                    searchWrap.appendTo('#export-btn-slot');

                    $('#dt-info-slot').appendTo('.pagination-wrap');
                }
            });
        });
    </script>
@endpush
