@php use Modules\Media\Helpers\FileHelper; @endphp
@extends('layouts.user')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        :root {
            --primary:   #1e3a5f;
            --primary2:  #2563eb;
            --accent:    #0ea5e9;
            --green:     #059669;
            --red:       #dc2626;
            --amber:     #d97706;
            --border:    #e5e7eb;
            --bg:        #f3f6fb;
            --card:      #ffffff;
            --text:      #0f172a;
            --muted:     #64748b;
            --radius:    14px;
        }

        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

        /* ── Page wrapper ── */
        .wc-wrap  { padding: 24px 20px 60px; max-width: 1280px; margin: 0 auto; }

        /* ── Header ── */
        .wc-header {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
        }
        .wc-title  { font-size: 22px; font-weight: 800; color: var(--primary); margin: 0; line-height: 1.2; }
        .wc-sub    { font-size: 13px; color: var(--muted); margin-top: 3px; }
        .btn-add-credit {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 20px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            color: #fff; font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(37,99,235,.25);
            transition: transform .15s, box-shadow .15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-add-credit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,.35); color: #fff; text-decoration: none; }

        /* ── Stats row ── */
        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px; margin-bottom: 20px;
        }
        .stat-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .stat-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .stat-val   { font-size: 20px; font-weight: 800; color: var(--text); font-family: 'DM Mono', monospace; }
        .stat-card.green .stat-val { color: var(--green); }
        .stat-card.red   .stat-val { color: var(--red); }
        .stat-card.blue  .stat-val { color: var(--primary2); }

        /* ── Alerts ── */
        .alert {
            display: flex; align-items: center; gap: 9px;
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 500; margin-bottom: 14px;
        }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* ── Table card ── */
        .table-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: 0 1px 6px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .table-card-head {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 10px;
            padding: 16px 20px; border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }
        .table-card-title {
            font-size: 14px; font-weight: 700; color: var(--text);
            display: flex; align-items: center; gap: 8px;
        }
        .table-card-title .badge {
            background: #dbeafe; color: #1e40af;
            font-size: 11px; font-weight: 700;
            padding: 2px 8px; border-radius: 999px;
        }

        /* ── DataTable overrides ── */
        #creditTable_wrapper { padding: 16px 20px 20px; }

        /* Top bar */
        #creditTable_wrapper > div:first-child {
            display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between;
            gap: 10px; margin-bottom: 14px;
        }

        /* Buttons */
        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; }
        .dt-button {
            font-size: 11.5px !important; padding: 5px 11px !important;
            border-radius: 7px !important; border: 1px solid var(--border) !important;
            background: #fff !important; color: #374151 !important;
            cursor: pointer; font-family: 'DM Sans', sans-serif !important;
            font-weight: 600 !important; transition: background .12s, border .12s;
        }
        .dt-button:hover { background: #f1f5f9 !important; border-color: #94a3b8 !important; }

        /* Search */
        #creditTable_filter { display: flex; align-items: center; gap: 6px; }
        #creditTable_filter label { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted); }
        #creditTable_filter input {
            border: 1.5px solid var(--border); border-radius: 8px;
            padding: 6px 12px; font-size: 12.5px; outline: none;
            font-family: 'DM Sans', sans-serif;
            transition: border .15s, box-shadow .15s; width: 200px;
        }
        #creditTable_filter input:focus {
            border-color: var(--primary2);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        /* Table header */
        #creditTable thead th {
            background: var(--primary); color: #fff;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: 11px 14px;
            border-bottom: none !important; white-space: nowrap;
        }
        #creditTable thead th.sorting_asc::after  { content: ' ↑'; opacity: .8; }
        #creditTable thead th.sorting_desc::after { content: ' ↓'; opacity: .8; }

        /* Table body */
        #creditTable tbody td {
            padding: 11px 14px; font-size: 13px;
            vertical-align: middle; border-color: #f1f5f9; color: #374151;
        }
        #creditTable tbody tr:nth-child(even) td { background: #f8fafc; }
        #creditTable tbody tr:hover td { background: #eff6ff !important; }
        #creditTable tbody tr { cursor: pointer; transition: background .1s; }

        /* Bottom bar */
        #creditTable_wrapper > div:last-child {
            display: flex; flex-wrap: wrap;
            align-items: center; justify-content: space-between;
            gap: 10px; margin-top: 14px;
        }
        #creditTable_info { font-size: 12px; color: var(--muted); }
        #creditTable_length label { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 6px; }
        #creditTable_length select {
            border: 1.5px solid var(--border); border-radius: 7px;
            padding: 4px 8px; font-size: 12px; outline: none;
        }

        /* Pagination */
        #creditTable_paginate { display: flex; align-items: center; gap: 2px; }
        #creditTable_paginate .paginate_button {
            border-radius: 7px !important; padding: 5px 11px !important;
            font-size: 12px !important; border: 1px solid var(--border) !important;
            color: #374151 !important; font-family: 'DM Sans', sans-serif !important;
            transition: all .12s;
        }
        #creditTable_paginate .paginate_button.current {
            background: var(--primary) !important; color: #fff !important;
            border-color: var(--primary) !important;
        }
        #creditTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background: #eff6ff !important; border-color: var(--primary2) !important;
            color: var(--primary2) !important;
        }
        #creditTable_paginate .paginate_button.disabled { opacity: .35; cursor: not-allowed; }

        /* Responsive */
        table.dataTable > tbody > tr.child ul.dtr-details li {
            border-bottom: 1px solid #f1f5f9; padding: 7px 4px;
            font-size: 12.5px; display: flex; gap: 8px; align-items: flex-start;
        }
        table.dataTable > tbody > tr.child ul.dtr-details li:last-child { border-bottom: none; }
        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        /* ── Badges ── */
        .r-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .r-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .b-success   { background: #dcfce7; color: #166534; } .b-success::before   { background: #16a34a; }
        .b-danger    { background: #fee2e2; color: #991b1b; } .b-danger::before    { background: #dc2626; }
        .b-warning   { background: #fef3c7; color: #92400e; } .b-warning::before   { background: #d97706; }
        .b-info      { background: #dbeafe; color: #1e40af; } .b-info::before      { background: #2563eb; }
        .b-secondary { background: #f1f5f9; color: #475569; } .b-secondary::before { background: #94a3b8; }

        /* ── Thumbnail ── */
        .thumb-img {
            width: 44px; height: 44px; object-fit: cover;
            border-radius: 8px; cursor: pointer;
            border: 2px solid var(--border);
            transition: transform .15s, border-color .15s;
        }
        .thumb-img:hover { transform: scale(1.08); border-color: var(--primary2); }

        /* ── Image overlay ── */
        #imageOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.88); z-index: 99999; cursor: pointer;
            backdrop-filter: blur(4px);
        }
        #imageOverlay .close-btn {
            position: absolute; top: 20px; right: 28px;
            color: white; font-size: 36px; font-weight: 300;
            line-height: 1; opacity: .8; transition: opacity .15s;
        }
        #imageOverlay .close-btn:hover { opacity: 1; }
        #bigImage {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90vw; max-height: 88vh;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
            border: 3px solid rgba(255,255,255,.15);
        }
        .overlay-hint {
            position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
            color: rgba(255,255,255,.5); font-size: 12px; pointer-events: none;
        }

        /* ── Mobile cards (<=640px) ── */
        .mobile-cards { display: none; padding: 12px; }
        .m-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: 12px; padding: 14px; margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .m-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .m-card-amount { font-size: 20px; font-weight: 800; color: var(--green); font-family: 'DM Mono', monospace; }
        .m-card-date { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .m-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .m-card-field { background: #f8fafc; border-radius: 8px; padding: 8px 10px; }
        .m-card-field-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
        .m-card-field-val { font-size: 12px; font-weight: 600; color: var(--text); }
        .m-card-remarks { margin-top: 8px; padding: 8px 10px; background: #f8fafc; border-radius: 8px; font-size: 12px; color: var(--muted); }

        @media (max-width: 640px) {
            .wc-wrap { padding: 16px 14px 60px; }
            .wc-title { font-size: 18px; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            .stat-val { font-size: 17px; }
            /* Hide desktop table, show mobile cards */
            .desktop-table-area { display: none !important; }
            .mobile-cards { display: block; }
            /* Search + length above cards */
            .mobile-controls {
                display: flex; flex-wrap: wrap; gap: 8px;
                padding: 12px 14px; border-bottom: 1px solid var(--border);
            }
            .mobile-search {
                flex: 1; min-width: 120px;
                border: 1.5px solid var(--border); border-radius: 8px;
                padding: 8px 12px; font-size: 13px; outline: none;
                font-family: 'DM Sans', sans-serif;
            }
            .mobile-search:focus { border-color: var(--primary2); }
            .mobile-pagination {
                display: flex; justify-content: center; gap: 4px;
                padding: 12px 14px; border-top: 1px solid var(--border);
            }
            .mob-page-btn {
                padding: 5px 12px; border-radius: 7px; border: 1px solid var(--border);
                background: #fff; font-size: 12px; color: #374151; cursor: pointer;
                font-family: 'DM Sans', sans-serif;
            }
            .mob-page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

            #creditTable_filter input { width: 100%; }
        }

        @media (min-width: 641px) {
            .mobile-controls { display: none; }
            .mobile-pagination { display: none; }
        }

        /* ── Animations ── */
        @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp .4s ease both; }
        .d1 { animation-delay: .05s; } .d2 { animation-delay: .1s; } .d3 { animation-delay: .15s; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 48px 20px; color: var(--muted); }
        .empty-state i { font-size: 40px; display: block; margin-bottom: 12px; opacity: .4; }
        .empty-state p { font-size: 14px; margin: 0; }
    </style>
@endpush

@section('content')
    @php
        // Stats calculation
        $totalCredit  = $rows->where('status', '!=', 'payment')->sum('amount') ?? 0;
        $totalDebit   = $rows->where('status', 'payment')->sum('amount') ?? 0;
        $totalTxn     = $rows->count();
        $pendingCount = $rows->where('status', 'pending')->count() ?? 0;
    @endphp

    <div class="wc-wrap">

        {{-- ── Header ── --}}
        <div class="wc-header fade-up d1">
            <div>
                <h2 class="wc-title">{{ $page_title }}</h2>
                <p class="wc-sub">
                    <i class="fas fa-user" style="font-size:11px;margin-right:3px"></i>
                    {{ auth()->user()->name }} · {{ __("Wallet Top Up History") }}
                </p>
            </div>
            <a href="{{ route('user.wallet.addcredit') }}" class="btn-add-credit">
                <i class="fas fa-plus-circle"></i>
                {{ __('Add Credit') }}
            </a>
        </div>

        {{-- ── Alerts ── --}}
        @if(session('success'))
            <div class="alert alert-success fade-up d1">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error fade-up d1">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ── Stats row ── --}}
        <div class="stats-row fade-up d2">
            <div class="stat-card blue">
                <div class="stat-label"><i class="fas fa-list" style="margin-right:4px"></i>Total Transactions</div>
                <div class="stat-val">{{ $totalTxn }}</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label"><i class="fas fa-arrow-down" style="margin-right:4px"></i>Total Credit</div>
                <div class="stat-val">৳{{ number_format($totalCredit, 2) }}</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label"><i class="fas fa-arrow-up" style="margin-right:4px"></i>Total Debit</div>
                <div class="stat-val">৳{{ number_format($totalDebit, 2) }}</div>
            </div>
            @if($pendingCount > 0)
                <div class="stat-card" style="border-color:#fde68a">
                    <div class="stat-label" style="color:#d97706"><i class="fas fa-clock" style="margin-right:4px"></i>Pending</div>
                    <div class="stat-val" style="color:#d97706">{{ $pendingCount }}</div>
                </div>
            @endif
        </div>

        {{-- ── Table Card ── --}}
        <div class="table-card fade-up d3">
{{--            <div class="table-card-head">--}}
{{--                <div class="table-card-title">--}}
{{--                    <i class="fas fa-wallet" style="color:var(--primary)"></i>--}}
{{--                    Transaction History--}}
{{--                    <span class="badge">{{ $totalTxn }}</span>--}}
{{--                </div>--}}
{{--                <a href="{{ route('user.wallet.addcredit') }}" class="btn-add-credit" style="padding:7px 14px;font-size:12px">--}}
{{--                    <i class="fas fa-plus"></i> Add Credit--}}
{{--                </a>--}}
{{--            </div>--}}

            {{-- ── Mobile: Search controls ── --}}
            <div class="mobile-controls">
                <input type="text" class="mobile-search" id="mobileSearch" placeholder="Search transactions..." oninput="filterMobileCards(this.value)">
            </div>

            {{-- ── DESKTOP TABLE ── --}}
            <div class="desktop-table-area">
                <div id="creditTable_wrapper">
                    <table id="creditTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>{{ __('SL') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Payment Method') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Image') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Remarks') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td style="color:var(--muted);font-size:12px">{{ $loop->iteration }}</td>
                                <td data-order="{{ strtotime($row->deposit_date) }}" style="white-space:nowrap;font-family:'DM Mono',monospace;font-size:12px">
                                    {{ $row->deposit_date }}
                                </td>
                                <td>
                                    <span style="font-weight:800;color:{{ $row->status === 'payment' ? 'var(--red)' : 'var(--green)' }};font-family:'DM Mono',monospace;font-size:13px">
                                        {{ $row->status === 'payment' ? '−' : '+' }}৳{{ number_format($row->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="r-badge b-info">{{ ucfirst($row->type) }}</span>
                                </td>
                                <td style="font-size:12px;color:var(--muted)">{{ $row->transaction_type }}</td>
                                <td>
                                    <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text)">{{ $row->reference ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($row->attachment_id)
                                        <img class="thumb-img"
                                             src="{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb') }}"
                                             alt="Receipt"
                                             onclick="showBigImage('{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full') }}')">
                                    @else
                                        <span style="color:#cbd5e1;font-size:12px">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $cls = match($row->status) {
                                            'payment'  => 'b-danger',
                                            'approved' => 'b-success',
                                            'pending'  => 'b-warning',
                                            'rejected' => 'b-danger',
                                            default    => 'b-success',
                                        };
                                    @endphp
                                    <span class="r-badge {{ $cls }}">{{ ucfirst($row->status) }}</span>
                                </td>
                                <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px;color:var(--muted)" title="{{ $row->remarks }}">
                                    {{ $row->remarks ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>{{ __('No transactions found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── MOBILE CARDS ── --}}
            <div class="mobile-cards" id="mobileCardsContainer">
                @forelse($rows->sortByDesc('deposit_date') as $row)
                    @php
                        $isDebit = $row->status === 'payment';
                        $cls = match($row->status) {
                            'payment'  => 'b-danger',
                            'approved' => 'b-success',
                            'pending'  => 'b-warning',
                            'rejected' => 'b-danger',
                            default    => 'b-success',
                        };
                    @endphp
                    <div class="m-card" data-search="{{ strtolower($row->deposit_date.' '.$row->type.' '.$row->transaction_type.' '.$row->reference.' '.$row->status.' '.$row->remarks) }}">
                        <div class="m-card-top">
                            <div>
                                <div class="m-card-amount" style="color:{{ $isDebit ? 'var(--red)' : 'var(--green)' }}">
                                    {{ $isDebit ? '−' : '+' }}৳{{ number_format($row->amount, 2) }}
                                </div>
                                <div class="m-card-date">
                                    <i class="fas fa-calendar-alt" style="font-size:10px;margin-right:3px"></i>{{ $row->deposit_date }}
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:7px">
                                @if($row->attachment_id)
                                    <img class="thumb-img" style="width:36px;height:36px"
                                         src="{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb') }}"
                                         alt="Receipt"
                                         onclick="showBigImage('{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full') }}')">
                                @endif
                                <span class="r-badge {{ $cls }}">{{ ucfirst($row->status) }}</span>
                            </div>
                        </div>
                        <div class="m-card-grid">
                            <div class="m-card-field">
                                <div class="m-card-field-label">Type</div>
                                <div class="m-card-field-val">{{ ucfirst($row->type) }}</div>
                            </div>
                            <div class="m-card-field">
                                <div class="m-card-field-label">Method</div>
                                <div class="m-card-field-val">{{ $row->transaction_type ?? '—' }}</div>
                            </div>
                            @if($row->reference)
                                <div class="m-card-field" style="grid-column:span 2">
                                    <div class="m-card-field-label">Reference</div>
                                    <div class="m-card-field-val" style="font-family:'DM Mono',monospace;font-size:11px">{{ $row->reference }}</div>
                                </div>
                            @endif
                        </div>
                        @if($row->remarks)
                            <div class="m-card-remarks">
                                <i class="fas fa-comment-alt" style="font-size:10px;margin-right:4px;opacity:.5"></i>{{ $row->remarks }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>{{ __('No transactions found.') }}</p>
                    </div>
                @endforelse
            </div>

        </div>{{-- /table-card --}}

    </div>{{-- /wc-wrap --}}

    {{-- ── Image Overlay ── --}}
    <div id="imageOverlay" onclick="closeBigImage()">
        <div class="close-btn">&times;</div>
        <img id="bigImage" src="" alt="Receipt">
        <div class="overlay-hint">Click anywhere or press ESC to close</div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        /* ── DataTable init ── */
        $(document).ready(function () {
            $('#creditTable').DataTable({
                dom:
                    "<'dt-top-bar'B<'dt-search'f>>" +
                    "tr" +
                    "<'dt-bot-bar'li<'dt-pages'p>>",

                pageLength: 15,
                lengthMenu: [[15, 25, 50, 100, -1], [15, 25, 50, 100, 'All']],
                order: [[1, 'desc']],

                responsive: {
                    details: {
                        type: 'inline',
                        renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes()
                    }
                },

                columnDefs: [
                    { responsivePriority: 1, targets: 1 },   // Date
                    { responsivePriority: 2, targets: 2 },   // Amount
                    { responsivePriority: 3, targets: 7 },   // Status
                    { responsivePriority: 4, targets: 3 },   // Type
                    { responsivePriority: 5, targets: 4 },   // Method
                    { responsivePriority: 6, targets: 5 },   // Reference
                    { responsivePriority: 7, targets: 6, orderable: false }, // Image
                    { responsivePriority: 8, targets: 0, orderable: false }, // SL
                    { responsivePriority: 9, targets: 8 },   // Remarks
                ],

                buttons: [
                    { extend: 'copy',  text: '<i class="fas fa-copy"></i> Copy',        exportOptions: { columns: [0,1,2,3,4,5,7,8] } },
                    { extend: 'csv',   text: '<i class="fas fa-file-csv"></i> CSV',      exportOptions: { columns: [0,1,2,3,4,5,7,8] } },
                    { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel',  exportOptions: { columns: [0,1,2,3,4,5,7,8] } },
                    { extend: 'pdf',   text: '<i class="fas fa-file-pdf"></i> PDF',      exportOptions: { columns: [0,1,2,3,4,5,7,8] } },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Print',       exportOptions: { columns: [0,1,2,3,4,5,7,8] } },
                ],

                language: {
                    search: '', searchPlaceholder: '🔍 Search...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries found',
                    emptyTable: '{{ __("No transactions found.") }}',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },

                initComplete: function() {
                    // Style the dt-top-bar and dt-bot-bar
                    $('.dt-top-bar').css({ 'display':'flex','flex-wrap':'wrap','align-items':'center','justify-content':'space-between','gap':'10px','padding':'14px 20px','border-bottom':'1px solid #f1f5f9' });
                    $('.dt-bot-bar').css({ 'display':'flex','flex-wrap':'wrap','align-items':'center','justify-content':'space-between','gap':'10px','padding':'12px 20px','border-top':'1px solid #f1f5f9' });
                    $('.dt-search').css({ 'display':'flex','align-items':'center' });
                    $('#creditTable_wrapper').css({ 'padding':'0' });
                }
            });
        });

        /* ── Mobile search ── */
        function filterMobileCards(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.m-card').forEach(card => {
                const text = card.dataset.search || '';
                card.style.display = (!q || text.includes(q)) ? 'block' : 'none';
            });
        }

        /* ── Image overlay ── */
        function showBigImage(src) {
            document.getElementById('bigImage').src = src;
            document.getElementById('imageOverlay').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        function closeBigImage() {
            document.getElementById('imageOverlay').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBigImage(); });
    </script>
@endpush
