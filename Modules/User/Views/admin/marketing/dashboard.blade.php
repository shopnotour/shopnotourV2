@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --surface:    #ffffff;
            --bg:         #f3f4f6;
            --text:       #111827;
            --radius:     10px;
            --shadow:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        }

        body, .content-area { background: var(--bg) !important; font-family: 'DM Sans', sans-serif; }

        /* Page Header */
        .mk-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .mk-header h1 { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .mk-header h1 .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; }

        /* Filter Card */
        .filter-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .filter-group { display:flex; align-items:center; gap:6px; }
        .filter-group label { font-size:12px; color:var(--muted); margin:0; white-space:nowrap; }
        .filter-group input[type="date"] { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; font-family:'DM Mono',monospace; transition:border-color .15s,box-shadow .15s; width:140px; }
        .filter-group input[type="date"]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); background:#fff; }
        .btn-filter { height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--primary); color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
        .btn-filter:hover { background:#1d4ed8; }
        .btn-clear { height:32px; padding:0 12px; font-size:12px; font-weight:500; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; transition:all .15s; }
        .btn-clear:hover { background:var(--bg); color:var(--text); text-decoration:none; }
        .filter-date-range { margin-left:auto; font-size:12px; color:var(--muted); font-family:'DM Mono',monospace; background:var(--bg); padding:5px 12px; border-radius:6px; border:1px solid var(--border); white-space:nowrap; }

        /* Stat Cards */
        .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        @media(max-width:992px){ .stat-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:576px){ .stat-grid{ grid-template-columns:1fr; } }

        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; box-shadow:var(--shadow); display:flex; align-items:center; gap:14px; text-decoration:none; transition:box-shadow .18s,transform .18s; position:relative; overflow:hidden; }
        .stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); transform:translateY(-2px); text-decoration:none; }
        .stat-card::before { content:''; position:absolute; top:0; left:0; width:3px; height:100%; background:var(--card-accent, var(--primary)); border-radius:var(--radius) 0 0 var(--radius); }
        .stat-icon { width:46px; height:46px; border-radius:12px; display:grid; place-items:center; font-size:18px; flex-shrink:0; }
        .stat-icon.violet { background:#EEEDFE; color:#534AB7; --card-accent:#534AB7; }
        .stat-icon.green  { background:#E1F5EE; color:#0F6E56; --card-accent:#0F6E56; }
        .stat-icon.lime   { background:#EAF3DE; color:#3B6D11; --card-accent:#3B6D11; }
        .stat-icon.red    { background:#FAECE7; color:#993C1D; --card-accent:#993C1D; }

        .stat-content { min-width:0; }
        .stat-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
        .stat-value { font-size:22px; font-weight:700; color:var(--text); line-height:1.1; font-family:'DM Mono',monospace; }
        .stat-sub { font-size:11px; margin-top:4px; display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:4px; }
        .stat-sub.warn { background:var(--warn-lt); color:var(--warn); }
        .stat-sub.success { background:var(--success-lt); color:var(--success); }

        /* Activity Panel */
        .activity-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .activity-panel-head { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; background:#fafafa; }
        .activity-panel-head h5 { margin:0; font-size:15px; font-weight:600; color:var(--text); }
        .activity-panel-head .badge-count { font-size:11px; background:var(--primary-lt); color:var(--primary); padding:3px 9px; border-radius:20px; font-weight:600; margin-left:auto; }

        .activity-table { width:100%; border-collapse:collapse; }
        .activity-table thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); padding:10px 16px; border-bottom:2px solid var(--border); }
        .activity-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
        .activity-table tbody tr:last-child { border-bottom:none; }
        .activity-table tbody tr:hover { background:#f8fafc; }
        .activity-table tbody td { padding:12px 16px; font-size:13px; color:var(--text); vertical-align:middle; }

        .activity-label { display:flex; align-items:center; gap:9px; }
        .activity-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .activity-dot.search    { background:var(--primary); }
        .activity-dot.price     { background:var(--warn); }
        .activity-dot.confirmed { background:var(--success); }
        .activity-dot.pending   { background:#f59e0b; }
        .activity-dot.cancelled { background:var(--danger); }
        .activity-dot.default   { background:#94a3b8; }

        .count-badge { font-family:'DM Mono',monospace; font-size:14px; font-weight:700; color:var(--text); }

        .btn-detail { display:inline-flex; align-items:center; gap:5px; padding:5px 12px; font-size:12px; font-weight:600; border-radius:6px; background:var(--primary-lt); color:var(--primary); border:none; text-decoration:none; transition:background .15s; }
        .btn-detail:hover { background:#dbeafe; color:var(--primary); text-decoration:none; }

        /* User Role Cards */
        .user-role-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:20px; box-shadow:var(--shadow); height:100%; }
        .user-role-card.b2c { border-top:3px solid #534AB7; }
        .user-role-card.b2b { border-top:3px solid #0F6E56; }
        .urc-header { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .urc-icon { width:44px; height:44px; border-radius:11px; display:grid; place-items:center; font-size:18px; flex-shrink:0; }
        .b2c-icon { background:#EEEDFE; color:#534AB7; }
        .b2b-icon { background:#E1F5EE; color:#0F6E56; }
        .urc-title { font-size:15px; font-weight:700; color:var(--text); line-height:1.2; }
        .urc-sub   { font-size:11px; color:var(--muted); margin-top:2px; font-family:'DM Mono',monospace; }
        .urc-link  { margin-left:auto; font-size:12px; font-weight:600; color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:5px; padding:5px 10px; border-radius:6px; background:var(--primary-lt); transition:background .15s; white-space:nowrap; }
        .urc-link:hover { background:#dbeafe; text-decoration:none; color:var(--primary); }
        .urc-stats { display:flex; align-items:center; }
        .urc-stat  { flex:1; text-align:center; padding:0 12px; }
        .urc-stat-val   { font-size:24px; font-weight:700; color:var(--text); font-family:'DM Mono',monospace; line-height:1.1; }
        .urc-stat-label { font-size:11px; color:var(--muted); margin-top:4px; text-transform:uppercase; letter-spacing:.05em; }
        .urc-divider { width:1px; height:40px; background:var(--border); flex-shrink:0; }

        /* Stat grid 4 cols = 2 rows of 4 */
        .stat-grid { grid-template-columns:repeat(4,1fr) !important; }
        @media(max-width:992px){ .stat-grid{ grid-template-columns:repeat(2,1fr) !important; } }
        @media(max-width:576px){ .stat-grid{ grid-template-columns:1fr !important; } }

        /* Fade */
        .fade-up { opacity:0; transform:translateY(12px); animation:fadeUp .35s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.04s}
        .fade-up:nth-child(2){animation-delay:.08s}
        .fade-up:nth-child(3){animation-delay:.12s}
        .fade-up:nth-child(4){animation-delay:.16s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="mk-header fade-up">
            <h1>
                <span class="icon-wrap"><i class="fa fa-bar-chart"></i></span>
                {{ __('Marketing Dashboard') }}
            </h1>
        </div>

        @include('admin.message')

        {{-- Date Filter --}}
        <div class="filter-card fade-up">
            <span class="filter-label"><i class="fa fa-calendar"></i> Date Range</span>
            <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}">
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fa fa-search"></i> Apply
                </button>
                <a href="{{ url()->current() }}" class="btn-clear">
                    <i class="fa fa-times"></i> Today
                </a>
            </form>
            <span class="filter-date-range">
            <i class="fa fa-calendar-o"></i>
            {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                @if($dateFrom !== $dateTo)
                    — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                @endif
        </span>
        </div>

        {{-- 6 Cards in 2 rows --}}
        <div class="stat-grid">

            {{-- B2C Customers --}}
            <a href="{{ route('admin.users.index', ['role_id' => 3]) }}" class="stat-card fade-up" style="border-top:3px solid #534AB7;">
                <div class="stat-icon violet"><i class="fa fa-user"></i></div>
                <div class="stat-content">
                    <div class="stat-label">B2C Customers</div>
                    <div class="stat-value">{{ number_format($totalB2CUsers) }}</div>
                    <span class="stat-sub success">
                    <i class="fa fa-circle" style="font-size:7px;"></i>
                    {{ number_format($loggedInB2CUsers) }} Active
                </span>
                </div>
            </a>

            {{-- B2B Agents --}}
            <a href="{{ route('admin.users.index', ['role_id' => 2]) }}" class="stat-card fade-up" style="border-top:3px solid #0F6E56;">
                <div class="stat-icon green"><i class="fa fa-briefcase"></i></div>
                <div class="stat-content">
                    <div class="stat-label">B2B Agents</div>
                    <div class="stat-value">{{ number_format($totalB2BUsers) }}</div>
                    <span class="stat-sub success">
                    <i class="fa fa-circle" style="font-size:7px;"></i>
                    {{ number_format($loggedInB2BUsers) }} Active
                </span>
                </div>
            </a>

            {{-- Deposit Confirmed --}}
            <a href="{{ route('user.admin.wallet.transactions', ['status' => 'confirmed', 'type' => 'deposit']) }}" class="stat-card fade-up">
                <div class="stat-icon lime"><i class="fa fa-arrow-down"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Deposit (Confirmed)</div>
                    <div class="stat-value">{{ number_format($totalDeposit) }}</div>
                    @if($pendingDeposit > 0)
                        <span class="stat-sub warn">
                        <i class="fa fa-clock-o"></i> {{ number_format($pendingDeposit) }} Pending
                    </span>
                    @endif
                </div>
            </a>

            {{-- Deposit Pending --}}
            <a href="{{ route('user.admin.wallet.transactions', ['status' => 'pending', 'type' => 'deposit']) }}" class="stat-card fade-up" style="border-top:3px solid #d97706;">
                <div class="stat-icon" style="background:#fffbeb;color:#d97706;"><i class="fa fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Deposit (Pending)</div>
                    <div class="stat-value" style="color:#d97706;">{{ number_format($pendingDeposit) }}</div>
                    <span class="stat-sub warn"><i class="fa fa-clock-o"></i> Awaiting Approval</span>
                </div>
            </a>

            {{-- Withdraw Confirmed --}}
            <a href="{{ route('user.admin.wallet.transactions', ['status' => 'confirmed', 'type' => 'withdraw']) }}" class="stat-card fade-up">
                <div class="stat-icon red"><i class="fa fa-arrow-up"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Withdraw (Confirmed)</div>
                    <div class="stat-value">{{ number_format($totalWithdraw) }}</div>
                    @if($pendingWithdraw > 0)
                        <span class="stat-sub warn">
                        <i class="fa fa-clock-o"></i> {{ number_format($pendingWithdraw) }} Pending
                    </span>
                    @endif
                </div>
            </a>

            {{-- Withdraw Pending --}}
            <a href="{{ route('user.admin.wallet.transactions', ['status' => 'pending', 'type' => 'withdraw']) }}" class="stat-card fade-up" style="border-top:3px solid #993C1D;">
                <div class="stat-icon" style="background:#FAECE7;color:#993C1D;"><i class="fa fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Withdraw (Pending)</div>
                    <div class="stat-value" style="color:#993C1D;">{{ number_format($pendingWithdraw) }}</div>
                    <span class="stat-sub warn"><i class="fa fa-clock-o"></i> Awaiting Approval</span>
                </div>
            </a>

            {{-- Total Payment --}}
            <a href="{{ route('user.admin.wallet.transactions', ['status' => 'payment', 'type' => 'debit']) }}" class="stat-card fade-up" style="border-top:3px solid #2563eb;">
                <div class="stat-icon blue"><i class="fa fa-credit-card"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Payments</div>
                    <div class="stat-value" style="font-size:16px;">
                        ৳{{ number_format($totalPaymentAmount, 0) }}
                    </div>
                    <span class="stat-sub success">
                    <i class="fa fa-check-circle" style="font-size:7px;"></i>
                    {{ number_format($totalPaymentCount) }} Transactions
                </span>
                </div>
            </a>

            {{-- Blank — reserved --}}
            <div class="stat-card fade-up" style="border-top:3px solid #e5e7eb;opacity:.45;cursor:default;">
                <div class="stat-icon" style="background:#f1f5f9;color:#cbd5e1;"><i class="fa fa-plus"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Coming Soon</div>
                    <div class="stat-value" style="color:#cbd5e1;">—</div>
                    <span class="stat-sub" style="background:#f1f5f9;color:#94a3b8;">Reserved</span>
                </div>
            </div>

        </div>

        {{-- Flight Activities --}}
        <div class="row">
            <div class="col-md-6 fade-up">
                <div class="activity-panel">
                    <div class="activity-panel-head">
                        <i class="fa fa-plane" style="color:var(--primary);"></i>
                        <h5>{{ __('Flight Activities') }}</h5>
                        <span class="badge-count">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }}
                            @if($dateFrom !== $dateTo)
                                — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($dateFrom)->format('Y') }}
                            @endif
                    </span>
                    </div>

                    <table class="activity-table">
                        <thead>
                        <tr>
                            <th>{{ __('Activity') }}</th>
                            <th style="width:100px;text-align:center;">{{ __('Count') }}</th>
                            <th style="width:110px;text-align:center;">{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{-- Search --}}
                        <tr>
                            <td>
                                <div class="activity-label">
                                    <span class="activity-dot search"></span>
                                    {{ __('Search') }}
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span class="count-badge">{{ number_format($searchCount) }}</span>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('admin.marketing.search.sessions', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn-detail"> {{-- modify this line --}}
                                    <i class="fa fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>

                        {{-- Price Checked --}}
                        <tr>
                            <td>
                                <div class="activity-label">
                                    <span class="activity-dot price"></span>
                                    {{ __('Price Checked') }}
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span class="count-badge">{{ number_format($priceCheckedCount) }}</span>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('admin.marketing.select.sessions', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn-detail"> {{-- modify this line --}}
                                    <i class="fa fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>

                        {{-- Booking Statuses --}}
                        @foreach($bookingActivities as $activity)
                            @php
                                $status = strtolower($activity->status);
                                $dotClass = in_array($status, ['confirmed','approved']) ? 'confirmed'
                                    : (in_array($status, ['pending','processing']) ? 'pending'
                                    : (in_array($status, ['cancelled','canceled','failed']) ? 'cancelled'
                                    : 'default'));
                            @endphp
                            <tr>
                                <td>
                                    <div class="activity-label">
                                        <span class="activity-dot {{ $dotClass }}"></span>
                                        {{ ucfirst(str_replace('_', ' ', $activity->status)) }}
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <span class="count-badge">{{ number_format($activity->total) }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('bookings.index', ['status' => $activity->status]) }}" class="btn-detail">
                                        <i class="fa fa-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
