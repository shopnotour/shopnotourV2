@extends('admin.layouts.app')

@section('content')
    <style>
        :root {
            --navy:   #0f172a;
            --blue:   #3b82f6;
            --indigo: #6366f1;
            --green:  #10b981;
            --amber:  #f59e0b;
            --red:    #ef4444;
            --slate:  #64748b;
            --light:  #f8fafc;
            --card-bg:#ffffff;
            --border: #e2e8f0;
            --radius: 14px;
            --shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
            --shadow-md: 0 4px 6px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.1);
        }
        body { background: #f1f5f9; }

        .vd-header { background:linear-gradient(135deg,var(--navy) 0%,#1e3a5f 100%); padding:28px 32px; border-radius:var(--radius); margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; box-shadow:var(--shadow-md); position:relative; overflow:hidden; }
        .vd-header::before { content:''; position:absolute; top:-40px; right:-40px; width:200px; height:200px; background:rgba(99,102,241,.15); border-radius:50%; }
        .vd-header-left h2 { color:#fff; font-size:22px; font-weight:700; margin:0 0 4px; }
        .vd-header-left p  { color:rgba(255,255,255,.55); margin:0; font-size:13px; }
        .vd-btn-back { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); color:#fff; padding:8px 18px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:background .2s; position:relative; z-index:1; }
        .vd-btn-back:hover { background:rgba(255,255,255,.22); color:#fff; text-decoration:none; }

        .status-dot { width:9px; height:9px; border-radius:50%; display:inline-block; margin-right:5px; }
        .status-online  { background:var(--green); box-shadow:0 0 0 3px rgba(16,185,129,.2); animation:pulse 2s infinite; }
        .status-offline { background:var(--slate); }
        @keyframes pulse { 0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,.2)} 50%{box-shadow:0 0 0 6px rgba(16,185,129,.05)} }

        .vd-card { background:var(--card-bg); border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid var(--border); margin-bottom:20px; overflow:hidden; }
        .vd-card-header { padding:14px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px; font-weight:700; font-size:14px; background:var(--light); }
        .vd-card-header .icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .vd-card-body { padding:20px; }

        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:12px; margin-bottom:20px; }
        .stat-chip { background:var(--light); border:1px solid var(--border); border-radius:10px; padding:14px 16px; text-align:center; }
        .stat-chip .val { font-size:22px; font-weight:800; color:var(--navy); line-height:1; margin-bottom:4px; }
        .stat-chip .lbl { font-size:11px; color:var(--slate); font-weight:600; text-transform:uppercase; letter-spacing:.04em; }

        .info-table { width:100%; border-collapse:collapse; }
        .info-table tr td { padding:9px 0; border-bottom:1px solid var(--border); font-size:13.5px; }
        .info-table tr:last-child td { border-bottom:none; }
        .info-table td:first-child { color:var(--slate); font-weight:600; width:38%; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .info-table td:last-child { color:var(--navy); font-weight:500; }

        .act-table { width:100%; border-collapse:collapse; font-size:12.5px; }
        .act-table thead tr { background:#f1f5f9; }
        .act-table th { padding:8px 10px; font-size:11px; font-weight:700; color:var(--slate); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); }
        .act-table td { padding:9px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .act-table tr:last-child td { border-bottom:none; }
        .act-table tbody tr:hover { background:#f8faff; }

        .act-type { padding:3px 9px; border-radius:100px; font-size:11px; font-weight:700; white-space:nowrap; display:inline-flex; align-items:center; gap:4px; }
        .act-click   { background:#f1f5f9; color:var(--slate); }
        .act-flight  { background:#eff6ff; color:var(--blue); }
        .act-search  { background:#ecfdf5; color:var(--green); }
        .act-form    { background:#fffbeb; color:var(--amber); }
        .act-default { background:#f8fafc; color:var(--slate); border:1px solid var(--border); }

        /* ── Page group collapsable ── */
        .page-group { border:1px solid var(--border); border-radius:10px; margin-bottom:10px; overflow:hidden; transition:border-color .2s; }
        .page-group.pg-open { border-color:var(--blue); }
        .page-group-btn { width:100%; background:var(--light); border:none; padding:10px 14px; display:flex; align-items:center; gap:8px; cursor:pointer; text-align:left; transition:background .15s; }
        .page-group-btn:hover { background:#eff6ff; }
        .page-group.pg-open .page-group-btn { background:#eff6ff; }
        .pg-chevron { transition:transform .25s; color:var(--slate); font-size:12px; flex-shrink:0; }
        .page-group.pg-open .pg-chevron { transform:rotate(180deg); }
        .page-group-body { display:none; border-top:1px solid var(--border); }
        .page-group.pg-open .page-group-body { display:block; }

        /* ── Date List ── */
        .date-item { padding:11px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; cursor:pointer; transition:background .15s; }
        .date-item:last-child { border-bottom:none; }
        .date-item:hover { background:#eff6ff; }
        .date-item.active { background:#eff6ff; border-left:3px solid var(--blue); }
        .date-label { font-size:13px; font-weight:600; color:var(--navy); }
        .date-sub   { font-size:11px; color:var(--slate); margin-top:2px; }
        .date-actions { display:flex; gap:5px; }

        .btn-sm { padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:4px; text-decoration:none; transition:opacity .15s; }
        .btn-sm:hover { opacity:.8; text-decoration:none; }
        .btn-dl  { background:#ecfdf5; color:#065f46; }
        .btn-del { background:#fef2f2; color:#991b1b; }
        .btn-search { background:#eff6ff; color:var(--blue); border:1px solid #bfdbfe; }
        .btn-flight { background:#eef2ff; color:var(--indigo); border:1px solid #c7d2fe; }

        .visit-item { padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; transition:background .15s; text-decoration:none; color:inherit; }
        .visit-item:last-child { border-bottom:none; }
        .visit-item:hover { background:#eff6ff; text-decoration:none; color:inherit; }
        .visit-item.current-visit { background:#eff6ff; border-left:3px solid var(--blue); }

        .device-icon  { font-size:28px; margin-bottom:8px; }
        .device-label { font-size:13px; font-weight:600; color:var(--navy); }
        .toggle-all-btn { padding:5px 12px; border-radius:7px; font-size:12px; font-weight:600; border:1px solid var(--border); background:#fff; cursor:pointer; color:var(--slate); display:inline-flex; align-items:center; gap:5px; transition:background .15s; }
        .toggle-all-btn:hover { background:#f1f5f9; }

        /* ── Flight leg card ── */
        .leg-card { border:1px solid var(--border); border-radius:10px; padding:14px 16px; margin-bottom:12px; }
        .leg-card:last-child { margin-bottom:0; }
        .leg-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .airport-block { text-align:center; }
        .airport-code { font-size:22px; font-weight:800; color:var(--navy); line-height:1; }
        .airport-name { font-size:11px; color:var(--slate); margin-top:2px; max-width:120px; }
        .airport-time { font-size:13px; font-weight:700; color:var(--navy); margin-top:4px; }
        .airport-date { font-size:11px; color:var(--slate); }
        .flight-middle { text-align:center; flex:1; padding:0 12px; }
        .flight-line { display:flex; align-items:center; gap:4px; justify-content:center; margin-bottom:4px; }
        .flight-line hr { flex:1; border:none; border-top:1.5px dashed var(--border); }
        .flight-duration { font-size:11px; color:var(--slate); }

        .spinner { width:22px; height:22px; border:2px solid var(--border); border-top-color:var(--blue); border-radius:50%; animation:spin .7s linear infinite; margin:0 auto; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* ── Modal ── */
        .vd-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; }
        .vd-modal-box { background:#fff; border-radius:14px; width:90%; max-width:600px; max-height:85vh; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .vd-modal-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
        .vd-modal-header h5 { font-size:15px; font-weight:700; color:var(--navy); margin:0; }
        .vd-modal-body { padding:20px; overflow-y:auto; flex:1; }
        .modal-close { background:none; border:none; font-size:18px; cursor:pointer; color:var(--slate); line-height:1; }
    </style>

    <div class="container-fluid py-3">

        {{-- ── HEADER ── --}}
        <div class="vd-header">
            <div class="vd-header-left">
                <h2><i class="fas fa-user-circle mr-2"></i>Visitor Details
                    <span style="font-size:14px;font-weight:500;opacity:.6;margin-left:10px;">#{{ $visitor->id }}</span>
                </h2>
                <p>
                    <span class="status-dot {{ $visitor->is_online ? 'status-online' : 'status-offline' }}"></span>
                    {{ $visitor->is_online ? 'Currently Online' : 'Offline' }}
                    &nbsp;·&nbsp;
                    @if($visitor->user) <i class="fas fa-user mr-1"></i>{{ $visitor->user->name }}
                    @else <i class="fas fa-user-secret mr-1"></i>Guest @endif
                    &nbsp;·&nbsp; {{ $visitor->visited_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <a href="{{ route('visitor.history') }}" class="vd-btn-back"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        {{-- ── STAT CHIPS ── --}}
        <div class="stat-grid">
            <div class="stat-chip"><div class="val">{{ $visitor->page_views }}</div><div class="lbl">Page Views</div></div>
            <div class="stat-chip">
                <div class="val">
                    @if($visitor->duration > 0) @php $m=floor($visitor->duration/60);$s=$visitor->duration%60; @endphp {{ $m>0?$m.'m '.$s.'s':$s.'s' }}
                    @else — @endif
                </div>
                <div class="lbl">Duration</div>
            </div>
            <div class="stat-chip"><div class="val">{{ $visitor->activities?$visitor->activities->count():0 }}</div><div class="lbl">Activities</div></div>
            <div class="stat-chip"><div class="val">{{ $visitor->activities?$visitor->activities->groupBy('page_url')->count():0 }}</div><div class="lbl">Pages Visited</div></div>
            <div class="stat-chip"><div class="val">{{ $relatedVisits->count()+1 }}</div><div class="lbl">Total Visits</div></div>
        </div>

        <div class="row">
            {{-- ══ LEFT ══ --}}
            <div class="col-md-8">

                {{-- Visit Info --}}
                <div class="vd-card">
                    <div class="vd-card-header">
                        <div class="icon" style="background:#eff6ff;color:var(--blue)"><i class="fas fa-info-circle"></i></div>
                        Visit Information
                    </div>
                    <div class="vd-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="info-table">
                                    <tr><td>User</td><td>
                                            @if($visitor->user) <strong>{{ $visitor->user->name }}</strong><div style="font-size:11px;color:var(--slate)">{{ $visitor->user->email }}</div>
                                            @else <span style="color:var(--slate)">Guest User</span> @endif
                                        </td></tr>
                                    <tr><td>IP Address</td><td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{{ $visitor->ip_address }}</code></td></tr>
                                    <tr><td>Session</td><td><code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;">{{ Str::limit($visitor->session_id,18) }}</code></td></tr>
                                    <tr><td>Status</td><td><span class="status-dot {{ $visitor->is_online?'status-online':'status-offline' }}"></span>{{ $visitor->is_online?'Online':'Offline' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="info-table">
                                    <tr><td>Visited At</td><td>{{ $visitor->visited_at->format('d M Y, h:i A') }}<div style="font-size:11px;color:var(--slate)">{{ $visitor->visited_at->diffForHumans() }}</div></td></tr>
                                    <tr><td>Last Active</td><td>{{ $visitor->last_activity_at?$visitor->last_activity_at->diffForHumans():'—' }}</td></tr>
                                    <tr><td>Landing Page</td><td><a href="{{ $visitor->landing_page }}" target="_blank" style="font-size:12px;color:var(--blue)">{{ Str::limit(parse_url($visitor->landing_page,PHP_URL_PATH)?:'/',35) }}</a></td></tr>
                                    <tr><td>Current Page</td><td><a href="{{ $visitor->current_page }}" target="_blank" style="font-size:12px;color:var(--blue)">{{ Str::limit(parse_url($visitor->current_page,PHP_URL_PATH)?:'/',35) }}</a></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity Journey --}}
                <div id="date-content-area">
                    @if($visitor->activities && $visitor->activities->count() > 0)
                        @php
                            $groupedByPage = $visitor->activities->sortBy('occurred_at')->groupBy('page_url');
                            $sessionStore  = [];
                        @endphp
                        <div class="vd-card">
                            <div class="vd-card-header">
                                <div class="icon" style="background:#f0fdf4;color:var(--green)"><i class="fas fa-route"></i></div>
                                Activity Journey
                                <span style="margin-left:auto;background:#f1f5f9;color:var(--slate);padding:2px 8px;border-radius:100px;font-size:11px;font-weight:600;">
                                {{ $visitor->activities->count() }} activities &nbsp;·&nbsp; {{ $groupedByPage->count() }} pages
                            </span>
                                <button class="toggle-all-btn ml-2" id="toggleAllBtn" onclick="toggleAll()">
                                    <i class="fas fa-compress-alt" id="toggleAllIcon"></i>
                                    <span id="toggleAllText">Collapse All</span>
                                </button>
                            </div>
                            <div class="vd-card-body" id="journey-container">
                                @foreach($groupedByPage as $pageUrl => $acts)
                                    @php
                                        $path     = parse_url($pageUrl, PHP_URL_PATH) ?: '/';
                                        $firstAct = $acts->first();
                                        $lastAct  = $acts->last();
                                        $idx      = $loop->index;

                                        $flightSearch   = null;
                                        $selectedFlight = null;
                                        foreach ($acts as $act) {
                                            if (!empty($act->session_data['_tracked']['flight_search_params']))
                                                $flightSearch = $act->session_data['_tracked']['flight_search_params'];
                                            if (!empty($act->session_data['_tracked']['selected_flight']))
                                                $selectedFlight = $act->session_data['_tracked']['selected_flight'];
                                            if (!empty($act->session_data['flight_search_params']))
                                                $flightSearch = $act->session_data['flight_search_params'];
                                            if (!empty($act->session_data['selected_flight']))
                                                $selectedFlight = $act->session_data['selected_flight'];
                                        }
                                        $sessionStore[$idx] = [
                                            'flightSearch'   => $flightSearch,
                                            'selectedFlight' => $selectedFlight,
                                        ];
                                        $seg = $flightSearch['segments'][0] ?? [];
                                        $fromAirport = isset($seg['from']) ? ($airportMap[$seg['from']] ?? null) : null;
                                        $toAirport   = isset($seg['to'])   ? ($airportMap[$seg['to']]   ?? null) : null;
                                    @endphp

                                    <div class="page-group {{ $loop->first ? 'pg-open' : '' }}" id="pg-{{ $idx }}">
                                        <button class="page-group-btn" onclick="toggleGroup({{ $idx }})">
                                            <div style="flex:1;min-width:0;">
                                                <div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                    <i class="fas fa-file-alt mr-1" style="color:var(--blue);font-size:11px;"></i>{{ $path }}
                                                </div>
                                                <div style="font-size:11px;color:var(--slate);">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($firstAct->occurred_at)->format('h:i:s A') }}
                                                    @if($firstAct->occurred_at != $lastAct->occurred_at) → {{ \Carbon\Carbon::parse($lastAct->occurred_at)->format('h:i:s A') }} @endif
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;margin-left:10px;">
                                                @if($flightSearch) <span style="background:#eff6ff;color:var(--blue);padding:3px 8px;border-radius:100px;font-size:11px;font-weight:700;"><i class="fas fa-search mr-1"></i>Search</span> @endif
                                                @if($selectedFlight) <span style="background:#ecfdf5;color:var(--green);padding:3px 8px;border-radius:100px;font-size:11px;font-weight:700;"><i class="fas fa-plane mr-1"></i>Selected</span> @endif
                                                <span style="background:#f1f5f9;color:var(--slate);padding:3px 8px;border-radius:100px;font-size:11px;font-weight:700;"><i class="fas fa-bolt mr-1"></i>{{ $acts->count() }}</span>
                                                <i class="fas fa-chevron-down pg-chevron"></i>
                                            </div>
                                        </button>

                                        <div class="page-group-body">
                                            {{-- Quick info bar --}}
                                            @if($flightSearch || $selectedFlight)
                                                <div style="padding:8px 14px;background:#f0f7ff;border-bottom:1px solid var(--border);display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
                                                    @if($flightSearch)
                                                        <span style="font-size:12px;color:var(--navy);">
                                                        <i class="fas fa-plane-departure mr-1" style="color:var(--blue);"></i>
                                                        <strong>{{ $fromAirport ? $fromAirport['code'].' — '.$fromAirport['name'] : ($seg['from'] ?? '?') }}</strong>
                                                        &nbsp;→&nbsp;
                                                        <strong>{{ $toAirport ? $toAirport['code'].' — '.$toAirport['name'] : ($seg['to'] ?? '?') }}</strong>
                                                        &nbsp;·&nbsp; {{ $seg['departure'] ?? '—' }}
                                                            @if(!empty($flightSearch['return_date'])) → {{ $flightSearch['return_date'] }} @endif
                                                        &nbsp;·&nbsp; {{ ucfirst($flightSearch['trip_type'] ?? '') }}
                                                        &nbsp;·&nbsp; {{ $flightSearch['travel_class'] ?? '' }}
                                                        &nbsp;·&nbsp; {{ $flightSearch['adults'] ?? 1 }}A
                                                        @if(($flightSearch['children']??0)>0) {{ $flightSearch['children'] }}C @endif
                                                    </span>
                                                        <button onclick="openSearchModal({{ $idx }})" class="btn-sm btn-search">
                                                            <i class="fas fa-search"></i> Search Details
                                                        </button>
                                                    @endif
                                                    @if($selectedFlight)
                                                        <span style="font-size:12px;color:var(--green);">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        <strong>{{ $selectedFlight['validating_carrier'] ?? '' }}</strong>
                                                        &nbsp;·&nbsp; BDT {{ number_format($selectedFlight['price']['total'] ?? 0) }}
                                                        &nbsp;·&nbsp; {{ ($selectedFlight['refundable']??false) ? 'Refundable' : 'Non-refundable' }}
                                                    </span>
                                                        <button onclick="openFlightModal({{ $idx }})" class="btn-sm btn-flight">
                                                            <i class="fas fa-plane"></i> Flight Details
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Activities table --}}
                                            <div style="overflow-x:auto;">
                                                <table class="act-table">
                                                    <thead><tr><th>Time</th><th>Type</th><th>Detail</th></tr></thead>
                                                    <tbody>
                                                    @foreach($acts->sortBy('occurred_at') as $act)
                                                        <tr>
                                                            <td style="color:var(--slate);white-space:nowrap;font-size:12px;">{{ \Carbon\Carbon::parse($act->occurred_at)->format('h:i:s A') }}</td>
                                                            <td>
                                                                @if($act->activity_type=='click') <span class="act-type act-click"><i class="fas fa-mouse-pointer"></i> Click</span>
                                                                @elseif($act->activity_type=='flight_search') <span class="act-type act-flight"><i class="fas fa-plane"></i> Flight Search</span>
                                                                @elseif($act->activity_type=='search') <span class="act-type act-search"><i class="fas fa-search"></i> Search</span>
                                                                @elseif($act->activity_type=='form_submit') <span class="act-type act-form"><i class="fas fa-paper-plane"></i> Form Submit</span>
                                                                @else <span class="act-type act-default">{{ $act->activity_type }}</span> @endif
                                                            </td>
                                                            <td>
                                                                @if($act->activity_type=='click' && $act->element_text)
                                                                    <span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:11px;">{{ Str::limit($act->element_text,80) }}</span>
                                                                    @if($act->activity_data && isset($act->activity_data['element_tag'])) <span style="color:var(--slate);font-size:10px;margin-left:4px;">&lt;{{ $act->activity_data['element_tag'] }}&gt;</span> @endif
                                                                @elseif($act->activity_type=='flight_search')
                                                                    @php $fs=$act->activity_data['flight_search']??[]; @endphp
                                                                    @if(!empty($fs)) <strong>{{ $fs['from']??'?' }}</strong> <i class="fas fa-arrow-right mx-1" style="color:var(--slate);font-size:10px;"></i> <strong>{{ $fs['to']??'?' }}</strong> @endif
                                                                @elseif($act->activity_type=='form_submit' && $act->activity_data)
                                                                    <span style="color:var(--slate);font-size:11px;">{{ Str::limit(json_encode($act->activity_data),80) }}</span>
                                                                @elseif($act->element_text)
                                                                    <span style="color:var(--slate);font-size:11px;">{{ Str::limit($act->element_text,80) }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="vd-card"><div class="vd-card-body" style="text-align:center;padding:40px;color:var(--slate);"><i class="fas fa-bolt" style="font-size:32px;margin-bottom:10px;display:block;opacity:.3;"></i><div style="font-size:13px;">কোনো activity নেই।</div></div></div>
                    @endif
                </div>

                {{-- Device & Browser --}}
                <div class="vd-card">
                    <div class="vd-card-header"><div class="icon" style="background:#ecfdf5;color:var(--green)"><i class="fas fa-laptop"></i></div>Device & Browser</div>
                    <div class="vd-card-body">
                        <div class="row align-items-center">
                            <div class="col-auto text-center" style="padding:0 24px;">
                                <div class="device-icon">@if($visitor->device_type=='mobile')📱@elseif($visitor->device_type=='tablet')💻@else🖥️@endif</div>
                                <div class="device-label">{{ ucfirst($visitor->device_type??'Unknown') }}</div>
                            </div>
                            <div class="col">
                                <table class="info-table">
                                    <tr><td>Browser</td><td>{{ $visitor->browser??'—' }}</td></tr>
                                    <tr><td>Platform</td><td>{{ $visitor->platform??'—' }}</td></tr>
                                    <tr><td>User Agent</td><td style="font-size:11px;color:var(--slate);">{{ Str::limit($visitor->user_agent,80) }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ RIGHT ══ --}}
            <div class="col-md-4">

                {{-- Location --}}
                <div class="vd-card">
                    <div class="vd-card-header"><div class="icon" style="background:#fffbeb;color:var(--amber)"><i class="fas fa-map-marker-alt"></i></div>Location</div>
                    <div class="vd-card-body">
                        @if($visitor->country)
                            <div style="text-align:center;margin-bottom:16px;">
                                <div style="font-size:48px;">🌍</div>
                                <div style="font-size:18px;font-weight:700;color:var(--navy);margin-top:4px;">{{ $visitor->country }}</div>
                                <div style="font-size:13px;color:var(--slate);">{{ $visitor->city??'' }}@if($visitor->region), {{ $visitor->region }}@endif</div>
                            </div>
                            <table class="info-table">
                                <tr><td>Country</td><td>{{ $visitor->country }} ({{ $visitor->country_code }})</td></tr>
                                <tr><td>Region</td><td>{{ $visitor->region??'N/A' }}</td></tr>
                                <tr><td>City</td><td>{{ $visitor->city??'N/A' }}</td></tr>
                                @if($visitor->latitude)<tr><td>Coordinates</td><td style="font-size:11px;">{{ $visitor->latitude }}, {{ $visitor->longitude }}</td></tr>@endif
                            </table>
                        @else
                            <div style="text-align:center;padding:20px 0;color:var(--slate);"><div style="font-size:36px;margin-bottom:8px;">🌐</div><div style="font-size:13px;">Location data not available</div></div>
                        @endif
                    </div>
                </div>

                {{-- Activity Summary --}}
                @if($visitor->activities && $visitor->activities->count() > 0)
                    <div class="vd-card">
                        <div class="vd-card-header"><div class="icon" style="background:#eef2ff;color:var(--indigo)"><i class="fas fa-chart-bar"></i></div>Activity Summary</div>
                        <div class="vd-card-body" style="padding:0;">
                            @foreach($visitor->activities->groupBy('activity_type') as $type => $acts)
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 20px;border-bottom:1px solid var(--border);">
                                    <span style="font-size:13px;font-weight:500;color:var(--navy);">
                                        @if($type=='click')<i class="fas fa-mouse-pointer mr-2" style="color:var(--slate)"></i>
                                        @elseif($type=='flight_search')<i class="fas fa-plane mr-2" style="color:var(--blue)"></i>
                                        @elseif($type=='search')<i class="fas fa-search mr-2" style="color:var(--green)"></i>
                                        @elseif($type=='form_submit')<i class="fas fa-paper-plane mr-2" style="color:var(--amber)"></i>
                                        @else<i class="fas fa-dot-circle mr-2" style="color:var(--slate)"></i>@endif
                                        {{ ucfirst(str_replace('_',' ',$type)) }}
                                    </span>
                                    <span style="background:var(--blue);color:#fff;padding:2px 10px;border-radius:100px;font-size:12px;font-weight:700;">{{ $acts->count() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Date List --}}
                @php
                    $activityDates = $visitor->activities ? $visitor->activities->groupBy(fn($a)=>\Carbon\Carbon::parse($a->created_at)->format('Y-m-d')) : collect();
                    $allDates = $activityDates->keys()->sortDesc();
                @endphp
                @if($allDates->count() > 0)
                    <div class="vd-card">
                        <div class="vd-card-header">
                            <div class="icon" style="background:#fdf4ff;color:#a855f7"><i class="fas fa-calendar-alt"></i></div>
                            Session by Date
                            <span style="margin-left:auto;background:#f1f5f9;color:var(--slate);padding:2px 8px;border-radius:100px;font-size:11px;font-weight:600;">{{ $allDates->count() }} days</span>
                        </div>
                        <div style="padding:8px 16px;background:#faf5ff;border-bottom:1px solid var(--border);font-size:11px;color:#7c3aed;">
                            <i class="fas fa-info-circle mr-1"></i> Date select করলে সেই দিনের activities দেখাবে
                        </div>
                        <div id="date-list">
                            @foreach($allDates as $date)
                                @php
                                    $dayActs   = $activityDates[$date];
                                    $dateLabel = \Carbon\Carbon::parse($date)->format('d M Y');
                                    $dayLabel  = \Carbon\Carbon::parse($date)->format('l');
                                @endphp
                                <div class="date-item" id="date-{{ $date }}" onclick="loadDateData('{{ $date }}')">
                                    <div style="flex:1;min-width:0;">
                                        <div class="date-label">
                                            <i class="fas fa-calendar-day mr-1" style="color:var(--blue);font-size:11px;"></i>
                                            {{ $dateLabel }} <span style="font-size:10px;color:var(--slate);font-weight:400;margin-left:4px;">{{ $dayLabel }}</span>
                                        </div>
                                        <div class="date-sub">
                                            <i class="fas fa-bolt" style="font-size:9px;"></i> {{ $dayActs->count() }} activities
                                            &nbsp;·&nbsp; <i class="fas fa-file-alt" style="font-size:9px;"></i> {{ $dayActs->groupBy('page_url')->count() }} pages
                                        </div>
                                    </div>
                                    <div class="date-actions" onclick="event.stopPropagation()">
                                        <a href="{{ route('visitor.date.download',$visitor->id) }}?date={{ $date }}" class="btn-sm btn-dl" title="Download CSV"><i class="fas fa-download"></i></a>
                                        <button class="btn-sm btn-del" title="Delete" onclick="deleteDate('{{ $date }}')"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Related Visits --}}
                @if($relatedVisits->count() > 0)
                    <div class="vd-card">
                        <div class="vd-card-header">
                            <div class="icon" style="background:#fdf2f8;color:#a855f7"><i class="fas fa-history"></i></div>
                            {{ $filterType==='user'?'All Visits by this User':'All Visits from this IP' }}
                            <span style="margin-left:auto;background:#f1f5f9;color:var(--slate);padding:2px 8px;border-radius:100px;font-size:11px;font-weight:600;">{{ $relatedVisits->count()+1 }} total</span>
                        </div>
                        <div style="padding:8px 16px;background:#faf5ff;border-bottom:1px solid var(--border);font-size:11px;color:#7c3aed;">
                            @if($filterType==='user') <i class="fas fa-user mr-1"></i> {{ $visitor->user->name }} এর সব visits
                            @else <i class="fas fa-network-wired mr-1"></i> IP: {{ $visitor->ip_address }} @endif
                        </div>
                        <div class="visit-item current-visit" style="cursor:default;">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:var(--navy);">
                                    <span style="background:var(--blue);color:#fff;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;margin-right:6px;">Current</span>
                                    {{ $visitor->visited_at->format('d M Y') }}
                                </div>
                                <div style="font-size:11px;color:var(--slate);margin-top:2px;">
                                    <span class="status-dot {{ $visitor->is_online?'status-online':'status-offline' }}" style="width:7px;height:7px;"></span>
                                    {{ $visitor->is_online?'Online':'Offline' }} &nbsp;·&nbsp; {{ $visitor->page_views }} pages &nbsp;·&nbsp; {{ $visitor->visited_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                        @foreach($relatedVisits as $visit)
                            <a href="{{ route('visitor.show',$visit->id) }}" class="visit-item">
                                <div style="min-width:0;flex:1;">
                                    <div style="font-size:13px;font-weight:600;color:var(--navy);">{{ $visit->visited_at->format('d M Y') }}</div>
                                    <div style="font-size:11px;color:var(--slate);margin-top:2px;">
                                        <span class="status-dot {{ $visit->is_online?'status-online':'status-offline' }}" style="width:7px;height:7px;"></span>
                                        {{ $visit->is_online?'Online':'Offline' }} &nbsp;·&nbsp; {{ $visit->page_views }} pages &nbsp;·&nbsp; {{ $visit->visited_at->format('h:i A') }}
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right" style="color:var(--slate);font-size:12px;"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ══ SEARCH MODAL ══ --}}
    <div class="vd-modal" id="searchModal">
        <div class="vd-modal-box">
            <div class="vd-modal-header">
                <h5><i class="fas fa-search mr-2" style="color:var(--blue);"></i>Flight Search Details</h5>
                <button class="modal-close" onclick="closeModal('searchModal')">✕</button>
            </div>
            <div class="vd-modal-body" id="searchModalBody"></div>
        </div>
    </div>

    {{-- ══ FLIGHT MODAL ══ --}}
    <div class="vd-modal" id="flightModal">
        <div class="vd-modal-box" style="max-width:680px;">
            <div class="vd-modal-header">
                <h5><i class="fas fa-plane mr-2" style="color:var(--indigo);"></i>Selected Flight Details</h5>
                <button class="modal-close" onclick="closeModal('flightModal')">✕</button>
            </div>
            <div class="vd-modal-body" id="flightModalBody"></div>
        </div>
    </div>

    {{-- ══ DELETE MODAL ══ --}}
    <div class="vd-modal" id="deleteModal">
        <div style="background:#fff;border-radius:14px;padding:28px;max-width:380px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <div style="font-size:32px;text-align:center;margin-bottom:12px;">🗑️</div>
            <h5 style="text-align:center;color:var(--navy);font-weight:700;margin-bottom:8px;">Delete Confirmation</h5>
            <p id="deleteModalText" style="text-align:center;color:var(--slate);font-size:13px;margin-bottom:20px;"></p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <button onclick="closeModal('deleteModal')" style="padding:8px 20px;border-radius:8px;border:1px solid var(--border);background:#fff;cursor:pointer;font-weight:600;color:var(--slate);">Cancel</button>
                <button id="confirmDeleteBtn" style="padding:8px 20px;border-radius:8px;border:none;background:var(--red);color:#fff;cursor:pointer;font-weight:600;">Delete</button>
            </div>
        </div>
    </div>

    <script>
        var sessionDataStore = @json($sessionStore ?? []);
        var airportMap       = @json($airportMap ?? []);

        var VISITOR_ID      = {{ $visitor->id }};
        var CSRF_TOKEN      = '{{ csrf_token() }}';
        var DATE_DATA_URL   = '{{ route('visitor.date.data', $visitor->id) }}';
        var DATE_DELETE_URL = '{{ route('visitor.date.delete', $visitor->id) }}';
        var pendingDeleteDate = null;
        var allExpanded = true;

        // ── Helpers ──
        function getAirport(id) {
            return airportMap[id] || null;
        }
        function airportLabel(id) {
            var a = getAirport(id);
            return a ? '<strong>' + a.code + '</strong> <span style="color:var(--slate);font-weight:400;">— ' + a.name + '</span>' : '<strong>' + id + '</strong>';
        }
        function fmt(n) { return n ? Number(n).toLocaleString() : '—'; }
        function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : s; }

        // ── Collapse / Expand ──
        function toggleGroup(idx) {
            var el = document.getElementById('pg-' + idx);
            if (el) el.classList.toggle('pg-open');
            updateToggleBtn();
        }
        function toggleAll() {
            var groups = document.querySelectorAll('#journey-container .page-group');
            allExpanded = !allExpanded;
            groups.forEach(function(g) { allExpanded ? g.classList.add('pg-open') : g.classList.remove('pg-open'); });
            updateToggleBtn();
        }
        function updateToggleBtn() {
            var groups   = document.querySelectorAll('#journey-container .page-group');
            var openCount = document.querySelectorAll('#journey-container .page-group.pg-open').length;
            allExpanded  = openCount === groups.length;
            var icon = document.getElementById('toggleAllIcon');
            var text = document.getElementById('toggleAllText');
            if (icon) icon.className = allExpanded ? 'fas fa-compress-alt' : 'fas fa-expand-alt';
            if (text) text.textContent = allExpanded ? 'Collapse All' : 'Expand All';
        }

        // ── Search Modal ──
        function openSearchModal(idx) {
            var d  = sessionDataStore[idx] || {};
            var fs = d.flightSearch;
            if (!fs) return;

            var seg  = (fs.segments && fs.segments[0]) ? fs.segments[0] : {};
            var html = '';

            // Route card
            html += '<div style="background:linear-gradient(135deg,#eff6ff,#eef2ff);border-radius:12px;padding:20px;margin-bottom:16px;text-align:center;">';
            html += '<div style="display:flex;align-items:center;justify-content:center;gap:16px;">';
            html += '<div><div style="font-size:28px;font-weight:800;color:var(--navy);">' + (getAirport(seg.from) ? getAirport(seg.from).code : seg.from || '?') + '</div>';
            html += '<div style="font-size:11px;color:var(--slate);margin-top:2px;">' + (getAirport(seg.from) ? getAirport(seg.from).name : '') + '</div></div>';
            html += '<div style="font-size:22px;color:var(--blue);">✈</div>';
            html += '<div><div style="font-size:28px;font-weight:800;color:var(--navy);">' + (getAirport(seg.to) ? getAirport(seg.to).code : seg.to || '?') + '</div>';
            html += '<div style="font-size:11px;color:var(--slate);margin-top:2px;">' + (getAirport(seg.to) ? getAirport(seg.to).name : '') + '</div></div>';
            html += '</div>';
            html += '<div style="margin-top:10px;font-size:13px;color:var(--slate);">';
            html += ucFirst(fs.trip_type||'') + ' &nbsp;·&nbsp; ' + (fs.travel_class||'') + ' &nbsp;·&nbsp; ';
            html += (fs.adults||1) + ' Adult';
            if ((fs.children||0)>0) html += ' · ' + fs.children + ' Child';
            if ((fs.infants||0)>0) html += ' · ' + fs.infants + ' Infant';
            html += '</div></div>';

            // Date table
            html += '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            var rows = [
                ['Departure', seg.departure || '—'],
                ['Return',    fs.return_date || (fs.trip_type==='oneway' ? 'One Way' : '—')],
            ];
            rows.forEach(function(r) {
                html += '<tr style="border-bottom:1px solid var(--border);">';
                html += '<td style="padding:9px 0;color:var(--slate);font-size:12px;font-weight:600;width:40%;">' + r[0] + '</td>';
                html += '<td style="padding:9px 0;color:var(--navy);font-weight:600;">' + r[1] + '</td></tr>';
            });

            // Multi-segment
            if (fs.segments && fs.segments.length > 1) {
                fs.segments.forEach(function(s, i) {
                    var fa = getAirport(s.from), ta = getAirport(s.to);
                    html += '<tr style="border-bottom:1px solid var(--border);">';
                    html += '<td style="padding:9px 0;color:var(--slate);font-size:12px;font-weight:600;">Segment ' + (i+1) + '</td>';
                    html += '<td style="padding:9px 0;color:var(--navy);">';
                    html += (fa ? fa.code + ' — ' + fa.name : s.from) + ' → ' + (ta ? ta.code + ' — ' + ta.name : s.to);
                    html += ' &nbsp;·&nbsp; ' + (s.departure||'—');
                    html += '</td></tr>';
                });
            }
            html += '</table>';

            document.getElementById('searchModalBody').innerHTML = html;
            document.getElementById('searchModal').style.display = 'flex';
        }

        // ── Flight Modal ──
        function openFlightModal(idx) {
            var d  = sessionDataStore[idx] || {};
            var sf = d.selectedFlight;
            if (!sf) return;

            var price = sf.price || {};
            var html  = '';

            // Price summary
            html += '<div style="background:linear-gradient(135deg,#eef2ff,#f0fdf4);border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">';
            html += '<div><div style="font-size:11px;color:var(--slate);font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Total Price</div>';
            html += '<div style="font-size:26px;font-weight:800;color:var(--navy);">BDT ' + fmt(price.total) + '</div></div>';
            html += '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
            html += '<span style="background:#fff;border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:var(--navy);">✈ ' + (sf.validating_carrier||'—') + '</span>';
            html += '<span style="background:' + (sf.refundable?'#ecfdf5':'#fef2f2') + ';border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:' + (sf.refundable?'var(--green)':'var(--red)') + ';">' + (sf.refundable?'✅ Refundable':'❌ Non-refundable') + '</span>';
            html += '<span style="background:#fffbeb;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:var(--amber);">🗓 ' + (sf.last_ticket_date||'—') + '</span>';
            html += '</div></div>';

            // Legs
            var legs = sf.legs || [];
            legs.forEach(function(leg) {
                var dep  = leg.departure || {};
                var arr  = leg.arrival   || {};
                var segs = leg.segments  || [];
                var firstSeg = segs[0] || {};

                html += '<div class="leg-card">';
                html += '<div class="leg-header">';
                html += '<div style="font-size:11px;font-weight:700;color:' + (leg.leg_type==='outbound'?'var(--blue)':'var(--indigo)') + ';text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">';
                html += '<i class="fas fa-plane' + (leg.leg_type==='return'?'-arrival':'-departure') + ' mr-1"></i> ' + ucFirst(leg.leg_type||'') + ' — Leg ' + leg.leg_number;
                html += '</div></div>';

                // Departure → Arrival visual
                html += '<div style="display:flex;align-items:flex-start;gap:8px;margin-bottom:14px;">';

                // From
                html += '<div class="airport-block">';
                html += '<div class="airport-code">' + dep.airport_code + '</div>';
                html += '<div class="airport-name">' + dep.airport_name + '</div>';
                html += '<div class="airport-time">' + (dep.time_12h||'—') + '</div>';
                html += '<div class="airport-date">' + (dep.date||'') + '</div>';
                html += '</div>';

                // Middle
                html += '<div class="flight-middle">';
                html += '<div class="flight-line"><hr><i class="fas fa-plane" style="color:var(--blue);font-size:14px;"></i><hr></div>';
                html += '<div class="flight-duration">' + (leg.duration_formatted||'') + ' &nbsp;·&nbsp; ' + (leg.stops===0?'Direct':leg.stops+' stop') + '</div>';
                if (firstSeg.full_flight_number) html += '<div style="font-size:11px;color:var(--slate);margin-top:3px;">' + firstSeg.full_flight_number + '</div>';
                html += '</div>';

                // To
                html += '<div class="airport-block">';
                html += '<div class="airport-code">' + arr.airport_code + '</div>';
                html += '<div class="airport-name">' + arr.airport_name + '</div>';
                html += '<div class="airport-time">' + (arr.time_12h||'—') + '</div>';
                html += '<div class="airport-date">' + (arr.date||'') + '</div>';
                html += '</div>';
                html += '</div>';

                // Segment details
                segs.forEach(function(seg) {
                    html += '<table style="width:100%;border-collapse:collapse;font-size:12px;background:#f8fafc;border-radius:8px;overflow:hidden;">';
                    var srows = [
                        ['Airline',   seg.carrier_name || seg.carrier],
                        ['Flight No', seg.full_flight_number],
                        ['Aircraft',  seg.aircraft_name || seg.aircraft],
                        ['Class',     seg.fare_info ? seg.fare_info.cabin_name : '—'],
                        ['Booking Code', seg.fare_info ? seg.fare_info.booking_code : '—'],
                        ['Seats Left', seg.fare_info ? seg.fare_info.seats_available : '—'],
                        ['Duration',  seg.duration_formatted],
                    ];
                    srows.forEach(function(r) {
                        html += '<tr style="border-bottom:1px solid var(--border);">';
                        html += '<td style="padding:7px 10px;color:var(--slate);font-weight:600;width:40%;">' + r[0] + '</td>';
                        html += '<td style="padding:7px 10px;color:var(--navy);font-weight:500;">' + (r[1]||'—') + '</td>';
                        html += '</tr>';
                    });
                    html += '</table>';
                });

                html += '</div>';
            });

            // Price breakdown
            html += '<div style="margin-top:16px;border:1px solid var(--border);border-radius:10px;overflow:hidden;">';
            html += '<div style="padding:10px 14px;background:var(--light);font-size:12px;font-weight:700;color:var(--slate);text-transform:uppercase;letter-spacing:.04em;">Price Breakdown</div>';
            html += '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            [
                ['Base Fare',       'BDT ' + fmt(price.api_base_fare)],
                ['Tax',             'BDT ' + fmt(price.api_tax)],
                ['AIT',             'BDT ' + fmt(price.ait_amount)],
                ['Service Charge',  'BDT ' + fmt(price.service_charge)],
                ['Total',           'BDT ' + fmt(price.total)],
            ].forEach(function(r, i) {
                var isLast = i === 4;
                html += '<tr style="border-bottom:' + (isLast?'none':'1px solid var(--border)') + ';' + (isLast?'background:#f0fdf4;':'') + '">';
                html += '<td style="padding:9px 14px;color:var(--slate);font-weight:' + (isLast?'700':'500') + ';">' + r[0] + '</td>';
                html += '<td style="padding:9px 14px;color:' + (isLast?'var(--green)':'var(--navy)') + ';font-weight:' + (isLast?'800':'500') + ';text-align:right;">' + r[1] + '</td>';
                html += '</tr>';
            });
            html += '</table></div>';

            document.getElementById('flightModalBody').innerHTML = html;
            document.getElementById('flightModal').style.display = 'flex';
        }

        // ── Modal helpers ──
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
            if (id === 'deleteModal') pendingDeleteDate = null;
        }
        ['searchModal','flightModal','deleteModal'].forEach(function(id) {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) closeModal(id);
            });
        });

        // ── Date filter ──
        function loadDateData(date) {
            document.querySelectorAll('.date-item').forEach(function(el) { el.classList.remove('active'); });
            var el = document.getElementById('date-' + date);
            if (el) el.classList.add('active');

            var area = document.getElementById('date-content-area');
            area.innerHTML = '<div style="text-align:center;padding:50px 0;"><div class="spinner"></div><p style="color:var(--slate);font-size:13px;margin-top:14px;">Loading...</p></div>';

            fetch(DATE_DATA_URL + '?date=' + date, { headers: { 'Accept':'application/json','X-CSRF-TOKEN':CSRF_TOKEN } })
                .then(function(r) { return r.json(); })
                .then(function(data) { renderDateContent(date, data.activities||[]); })
                .catch(function() {
                    area.innerHTML = '<div style="text-align:center;padding:40px;color:var(--red);"><i class="fas fa-exclamation-circle" style="font-size:24px;"></i><p style="margin-top:8px;font-size:13px;">Data load করতে সমস্যা হয়েছে।</p></div>';
                });
        }

        var dynAllExpanded = true;
        function renderDateContent(date, activities) {
            var dateLabel = new Date(date+'T00:00:00').toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'});
            var grouped = {};
            activities.forEach(function(act) {
                var url = act.page_url||'unknown';
                if (!grouped[url]) grouped[url] = [];
                grouped[url].push(act);
            });

            var html = '<div class="vd-card">';
            html += '<div class="vd-card-header"><div class="icon" style="background:#f0fdf4;color:var(--green)"><i class="fas fa-route"></i></div>';
            html += 'Activity Journey — ' + dateLabel;
            html += '<span style="margin-left:auto;background:#f1f5f9;color:var(--slate);padding:2px 8px;border-radius:100px;font-size:11px;font-weight:600;">' + activities.length + ' activities</span>';
            html += '<button class="toggle-all-btn ml-2" onclick="toggleAllDyn()"><i class="fas fa-compress-alt" id="dynIcon"></i> <span id="dynText">Collapse All</span></button>';
            html += '</div><div class="vd-card-body" id="dyn-journey">';

            if (!activities.length) {
                html += '<div style="text-align:center;padding:30px;color:var(--slate);"><i class="fas fa-calendar-times" style="font-size:28px;display:block;opacity:.3;margin-bottom:8px;"></i>এই দিনে কোনো activity নেই।</div>';
            } else {
                var pi = 0;
                Object.keys(grouped).forEach(function(pageUrl) {
                    var acts = grouped[pageUrl];
                    var path = '/'; try { path = new URL(pageUrl).pathname||pageUrl; } catch(e) { path = pageUrl; }
                    var t1 = acts[0].occurred_at ? new Date(acts[0].occurred_at).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '—';
                    var tN = acts[acts.length-1].occurred_at ? new Date(acts[acts.length-1].occurred_at).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '';

                    html += '<div class="page-group pg-open" id="dpg-'+pi+'">';
                    html += '<button class="page-group-btn" onclick="toggleDyn('+pi+')">';
                    html += '<div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><i class="fas fa-file-alt mr-1" style="color:var(--blue);font-size:11px;"></i>'+path+'</div>';
                    html += '<div style="font-size:11px;color:var(--slate);"><i class="fas fa-clock mr-1"></i>'+t1+(tN&&tN!==t1?' → '+tN:'')+'</div></div>';
                    html += '<div style="display:flex;gap:6px;align-items:center;flex-shrink:0;margin-left:10px;">';
                    html += '<span style="background:#f1f5f9;color:var(--slate);padding:3px 8px;border-radius:100px;font-size:11px;font-weight:700;"><i class="fas fa-bolt mr-1"></i>'+acts.length+'</span>';
                    html += '<i class="fas fa-chevron-down pg-chevron"></i></div></button>';
                    html += '<div class="page-group-body"><div style="overflow-x:auto;"><table class="act-table">';
                    html += '<thead><tr><th>Time</th><th>Type</th><th>Detail</th></tr></thead><tbody>';
                    acts.forEach(function(act) {
                        var t = act.occurred_at ? new Date(act.occurred_at).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '—';
                        html += '<tr><td style="color:var(--slate);white-space:nowrap;font-size:12px;">'+t+'</td><td>'+actTypeBadge(act.activity_type)+'</td><td style="font-size:11px;color:var(--slate);">'+actDetail(act)+'</td></tr>';
                    });
                    html += '</tbody></table></div></div></div>';
                    pi++;
                });
            }
            html += '</div></div>';
            dynAllExpanded = true;
            document.getElementById('date-content-area').innerHTML = html;
        }

        function toggleDyn(idx) { var el=document.getElementById('dpg-'+idx); if(el) el.classList.toggle('pg-open'); }
        function toggleAllDyn() {
            dynAllExpanded = !dynAllExpanded;
            document.querySelectorAll('#dyn-journey .page-group').forEach(function(g){ dynAllExpanded?g.classList.add('pg-open'):g.classList.remove('pg-open'); });
            var icon=document.getElementById('dynIcon'), text=document.getElementById('dynText');
            if(icon) icon.className = dynAllExpanded?'fas fa-compress-alt':'fas fa-expand-alt';
            if(text) text.textContent = dynAllExpanded?'Collapse All':'Expand All';
        }

        function actTypeBadge(type) {
            var map = {'click':'<span class="act-type act-click"><i class="fas fa-mouse-pointer"></i> Click</span>','flight_search':'<span class="act-type act-flight"><i class="fas fa-plane"></i> Flight Search</span>','search':'<span class="act-type act-search"><i class="fas fa-search"></i> Search</span>','form_submit':'<span class="act-type act-form"><i class="fas fa-paper-plane"></i> Form Submit</span>'};
            return map[type]||'<span class="act-type act-default">'+type+'</span>';
        }
        function actDetail(act) {
            if (act.element_text) return '<span style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">'+String(act.element_text).substring(0,80)+'</span>';
            if (act.activity_data) { try { var d=typeof act.activity_data==='string'?JSON.parse(act.activity_data):act.activity_data; return String(JSON.stringify(d)).substring(0,80); } catch(e){} }
            return '';
        }

        // ── Delete ──
        function deleteDate(date) {
            pendingDeleteDate = date;
            var label = new Date(date+'T00:00:00').toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'});
            document.getElementById('deleteModalText').textContent = label+' এর সব activities delete হয়ে যাবে। পূর্বাবস্থায় ফেরানো যাবে না।';
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('confirmDeleteBtn').onclick = confirmDelete;
        }
        function confirmDelete() {
            if (!pendingDeleteDate) return;
            var btn=document.getElementById('confirmDeleteBtn');
            btn.disabled=true; btn.textContent='Deleting...';
            var d = pendingDeleteDate;
            fetch(DATE_DELETE_URL,{method:'DELETE',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json'},body:JSON.stringify({date:d})})
                .then(function(r){return r.json();})
                .then(function(data){
                    closeModal('deleteModal');
                    btn.disabled=false; btn.textContent='Delete';
                    if(data.success){
                        var row=document.getElementById('date-'+d);
                        if(row){row.style.transition='opacity .3s';row.style.opacity='0';setTimeout(function(){row.remove();},300);}
                        document.getElementById('date-content-area').innerHTML='<div style="text-align:center;padding:40px;color:var(--green);"><i class="fas fa-check-circle" style="font-size:28px;"></i><p style="margin-top:8px;font-size:13px;">Successfully deleted.</p></div>';
                    }
                })
                .catch(function(){closeModal('deleteModal');btn.disabled=false;btn.textContent='Delete';alert('Delete করতে সমস্যা হয়েছে।');});
        }
    </script>
@endsection
