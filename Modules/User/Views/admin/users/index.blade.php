@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
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
            --shadow:     0 1px 3px rgba(0,0,0,.08);
        }
        body, .content-area { background: var(--bg) !important; font-family: 'DM Sans', sans-serif; }

        /* Header */
        .pg-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .pg-header h1 { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .pg-header h1 .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; }
        .pg-header p { font-size:13px; color:var(--muted); margin:4px 0 0; }

        /* Stat mini cards */
        .mini-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
        @media(max-width:992px){ .mini-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:576px){ .mini-grid{ grid-template-columns:1fr; } }
        .mini-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 16px; box-shadow:var(--shadow); display:flex; align-items:center; gap:12px; }
        .mini-icon { width:38px; height:38px; border-radius:9px; display:grid; place-items:center; font-size:15px; flex-shrink:0; }
        .mini-icon.blue   { background:var(--primary-lt); color:var(--primary); }
        .mini-icon.green  { background:var(--success-lt); color:var(--success); }
        .mini-icon.amber  { background:var(--warn-lt);    color:var(--warn); }
        .mini-icon.violet { background:#EEEDFE;            color:#534AB7; }
        .mini-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
        .mini-value { font-size:18px; font-weight:700; color:var(--text); font-family:'DM Mono',monospace; line-height:1.2; }

        /* Filter bar */
        .filter-bar { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:12px 16px; margin-bottom:16px; box-shadow:var(--shadow); display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
        .role-tabs { display:flex; gap:6px; flex-wrap:wrap; }
        .role-tab { display:inline-flex; align-items:center; gap:5px; padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600; border:1px solid var(--border); background:var(--bg); color:var(--muted); text-decoration:none; transition:all .15s; }
        .role-tab:hover { text-decoration:none; }
        .role-tab.all.active, .role-tab.all:hover    { background:#1e293b; color:#fff; border-color:#1e293b; }
        .role-tab.b2c.active, .role-tab.b2c:hover    { background:#534AB7; color:#fff; border-color:#534AB7; }
        .role-tab.b2b.active, .role-tab.b2b:hover    { background:#0F6E56; color:#fff; border-color:#0F6E56; }
        .filter-info { margin-left:auto; font-size:12px; color:var(--muted); font-family:'DM Mono',monospace; }

        /* Panel */
        .users-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .users-panel-head { padding:12px 16px; border-bottom:1px solid var(--border); background:#fafafa; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
        .users-panel-title { font-size:13px; font-weight:600; color:var(--text); display:flex; align-items:center; gap:7px; }

        /* DataTable overrides */
        #usersTable_wrapper .dataTables_filter label { font-size:12px; color:var(--muted); display:flex; align-items:center; gap:8px; margin:0; }
        #usersTable_wrapper .dataTables_filter input { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; outline:none; width:200px; }
        #usersTable_wrapper .dataTables_filter input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        #usersTable_wrapper .dataTables_length select { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 8px; outline:none; }
        #usersTable_wrapper .dataTables_info { font-size:12px; color:var(--muted); padding:10px 16px; }
        #usersTable_wrapper .dataTables_paginate { padding:8px 16px; }
        #usersTable_wrapper .page-link { font-size:12px; padding:5px 10px; color:var(--primary); border-color:var(--border); }
        #usersTable_wrapper .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }
        .dt-top-bar { padding:10px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }

        /* Table */
        #usersTable { width:100% !important; }
        #usersTable thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); padding:10px 12px; border-bottom:2px solid var(--border); border-top:none; white-space:nowrap; }
        #usersTable tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
        #usersTable tbody tr:last-child { border-bottom:none; }
        #usersTable tbody tr:hover { background:#f8fafc; }
        #usersTable tbody td { padding:11px 12px; vertical-align:middle; border:none; font-size:13px; color:var(--text); }

        /* User name cell */
        .user-name { font-weight:600; color:var(--text); line-height:1.2; }
        .user-meta { font-size:11px; color:var(--muted); margin-top:2px; }

        /* Role badge */
        .role-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; letter-spacing:.04em; margin-top:3px; }
        .role-customer { background:#EEEDFE; color:#534AB7; }
        .role-agent    { background:#E1F5EE; color:#0F6E56; }
        .role-admin    { background:var(--danger-lt); color:var(--danger); }
        .role-other    { background:#f1f5f9; color:#475569; }

        /* Balance */
        .balance-pos { color:var(--success); font-weight:600; font-family:'DM Mono',monospace; font-size:13px; }
        .balance-zero { color:var(--muted); font-family:'DM Mono',monospace; font-size:13px; }

        /* Status */
        .status-active   { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600; background:var(--success-lt); color:var(--success); }
        .status-inactive { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600; background:#f1f5f9; color:#94a3b8; }

        /* Action buttons */
        .action-wrap { display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
        .act-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border-radius:6px; font-size:11px; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .15s; white-space:nowrap; }
        .act-btn:hover { text-decoration:none; filter:brightness(.92); }
        .act-bookings { background:#eff6ff; color:#2563eb; }
        .act-txns     { background:#ecfdf5; color:#059669; }
        .act-tickets  { background:#f0fdf4; color:#16a34a; }
        .act-password { background:#fef9c3; color:#854d0e; }

        /* Row num */
        .row-num { font-family:'DM Mono',monospace; font-size:11px; color:var(--muted); }

        /* Fade */
        .fade-up { opacity:0; transform:translateY(10px); animation:fadeUp .3s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        {{-- Header --}}
        <div class="pg-header fade-up">
            <div>
                <h1>
                    <span class="icon-wrap"><i class="fa fa-users"></i></span>
                    User Management
                </h1>
                <p>Manage all registered customers and agents</p>
            </div>
        </div>

        @include('admin.message')

        {{-- Mini Stats --}}
        @php
            $totalUsers   = $users->count();
            $activeUsers  = $users->where('status', 'publish')->count();
            $totalBalance = $users->sum('credit_balance');
            $newThisMonth = $users->filter(fn($u) => \Carbon\Carbon::parse($u->created_at)->gte(now()->startOfMonth()))->count();
            $b2cCount     = $users->where('role_id', 3)->count();
            $b2bCount     = $users->where('role_id', 2)->count();
            $currentRole  = request('role_id');
        @endphp

        <div class="mini-grid">
            <div class="mini-card fade-up">
                <div class="mini-icon blue"><i class="fa fa-users"></i></div>
                <div>
                    <div class="mini-label">Total Users</div>
                    <div class="mini-value">{{ number_format($totalUsers) }}</div>
                </div>
            </div>
            <div class="mini-card fade-up">
                <div class="mini-icon green"><i class="fa fa-check-circle"></i></div>
                <div>
                    <div class="mini-label">Active</div>
                    <div class="mini-value">{{ number_format($activeUsers) }}</div>
                </div>
            </div>
            <div class="mini-card fade-up">
                <div class="mini-icon amber"><i class="fa fa-user-plus"></i></div>
                <div>
                    <div class="mini-label">New This Month</div>
                    <div class="mini-value">{{ number_format($newThisMonth) }}</div>
                </div>
            </div>
            <div class="mini-card fade-up">
                <div class="mini-icon violet"><i class="fa fa-wallet"></i></div>
                <div>
                    <div class="mini-label">Total Balance</div>
                    <div class="mini-value" style="font-size:14px;">৳{{ number_format($totalBalance, 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Role Filter Tabs --}}
        <div class="filter-bar fade-up">
            <div class="role-tabs">
                <a href="{{ route('admin.users.index') }}"
                   class="role-tab all {{ !$currentRole ? 'active' : '' }}">
                    <i class="fa fa-th-large"></i> All
                    <span style="font-family:'DM Mono',monospace;">{{ $users->count() }}</span>
                </a>
                <a href="{{ route('admin.users.index', ['role_id' => 3]) }}"
                   class="role-tab b2c {{ $currentRole == 3 ? 'active' : '' }}">
                    <i class="fa fa-user"></i> B2C Customers
                    <span style="font-family:'DM Mono',monospace;">{{ $b2cCount }}</span>
                </a>
                <a href="{{ route('admin.users.index', ['role_id' => 2]) }}"
                   class="role-tab b2b {{ $currentRole == 2 ? 'active' : '' }}">
                    <i class="fa fa-briefcase"></i> B2B Agents
                    <span style="font-family:'DM Mono',monospace;">{{ $b2bCount }}</span>
                </a>
            </div>
            <span class="filter-info">
            @if($currentRole == 3) Showing B2C Customers
                @elseif($currentRole == 2) Showing B2B Agents
                @else Showing all users
                @endif
        </span>
        </div>

        {{-- Users Table --}}
        <div class="users-panel fade-up">
            <div class="table-responsive">
                <table id="usersTable" class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:45px">#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Business</th>
                        <th style="width:130px">Balance</th>
                        <th style="width:90px">Status</th>
                        <th style="width:100px">Joined</th>
                        <th style="width:220px">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $i => $user)
                        @php
                            $roleLabel = match((int)$user->role_id) {
                                1 => ['Admin',    'role-admin'],
                                2 => ['Agent',    'role-agent'],
                                3 => ['Customer', 'role-customer'],
                                default => ['User', 'role-other'],
                            };
                            $roleIcon = match((int)$user->role_id) {
                                2 => 'fa-briefcase',
                                3 => 'fa-user',
                                default => 'fa-shield',
                            };
                        @endphp
                        <tr>
                            {{-- # --}}
                            <td><span class="row-num">{{ $i + 1 }}</span></td>

                            {{-- User --}}
                            <td>
                                <div class="user-name">
                                    {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—' }}
                                </div>
                                <span class="role-badge {{ $roleLabel[1] }}">
                                <i class="fa {{ $roleIcon }}" style="font-size:8px;"></i>
                                {{ $roleLabel[0] }}
                            </span>
                            </td>

                            {{-- Email --}}
                            <td>
                                <span style="font-size:12px;color:var(--muted);">{{ $user->email ?? '—' }}</span>
                            </td>

                            {{-- Phone --}}
                            <td>
                            <span style="font-size:12px;color:var(--muted);font-family:'DM Mono',monospace;">
                                {{ $user->phone ?? '—' }}
                            </span>
                            </td>

                            {{-- Business --}}
                            <td>
                            <span style="font-size:12px;color:var(--muted);">
                                {{ $user->business_name ?? '—' }}
                            </span>
                            </td>

                            {{-- Balance --}}
                            <td>
                                @if(($user->credit_balance ?? 0) > 0)
                                    <span class="balance-pos">৳{{ number_format($user->credit_balance, 2) }}</span>
                                @else
                                    <span class="balance-zero">৳0.00</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                @if($user->status === 'publish')
                                    <span class="status-active">
                                    <i class="fa fa-circle" style="font-size:7px;"></i> Active
                                </span>
                                @else
                                    <span class="status-inactive">
                                    <i class="fa fa-circle" style="font-size:7px;"></i> Inactive
                                </span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td>
                            <span style="font-size:12px;color:var(--muted);font-family:'DM Mono',monospace;">
                                {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                            </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="action-wrap">
                                    @if(auth()->user()->hasPermission('user_booking_view'))
                                        <a href="{{ route('admin.users.bookings', $user->id) }}"
                                           class="act-btn act-bookings" title="View Bookings">
                                            <i class="fa fa-plane"></i> Bookings
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('user_transactions_view'))

{{--                                            <a href="{{ route('user.admin.wallet.list', $row->user_id) }}"--}}
{{--                                               class="fw-semibold text-decoration-none text-dark">--}}
{{--                                                {{ $row->author->name ?? 'N/A' }}--}}
{{--                                            </a>--}}
                                        <a href="{{ route('user.admin.wallet.list', $user->id) }}"
                                           class="act-btn act-txns" title="View Transactions">
                                            <i class="fa fa-exchange"></i> Txns
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('user_tickets_view'))
                                            <a href="{{ route('admin.users.bookings', $user->id) }}?status=issued"
                                               class="act-btn act-tickets" title="View Issued Tickets">
                                                <i class="fa fa-print"></i> Tickets
                                            </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('user_password_change'))
                                        <a href="{{ route('user.admin.password', $user->id) }}"
                                           class="act-btn act-password" title="Change Password">
                                            <i class="fa fa-key"></i> Password
                                        </a>
                                    @endif

                                    @if(
                                        !auth()->user()->hasPermission('user_booking_view') &&
                                        !auth()->user()->hasPermission('user_transactions_view') &&
                                        !auth()->user()->hasPermission('user_tickets_view') &&
                                        !auth()->user()->hasPermission('user_password_change')
                                    )
                                        <span style="font-size:11px;color:var(--muted);">No Access</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $.fn.dataTable.ext.errMode = 'none';
            $('#usersTable').DataTable({
                pageLength: 25,
                order: [[7, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 8] },
                    { searchable: false, targets: [0, 5, 6, 7, 8] },
                ],
                dom: '<"dt-top-bar"lf>t<"d-flex justify-content-between align-items-center px-3 py-2"ip>',
                language: {
                    search: '',
                    searchPlaceholder: '🔍 Search users...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ users',
                    paginate: {
                        previous: '‹ Prev',
                        next: 'Next ›',
                    }
                },
            });
        });
    </script>
@endpush
