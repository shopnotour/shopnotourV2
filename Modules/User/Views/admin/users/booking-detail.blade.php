@extends('admin.layouts.app')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }

        .st-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .st-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer; user-select: none;
            transition: background .15s;
        }
        .st-header:hover { background: #f1f5f9; }
        .st-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 700; color: #1e293b;
        }
        .st-icon {
            width: 28px; height: 28px; border-radius: 7px;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 12px;
        }
        .st-body { display: block; }
        .st-chevron { transition: transform .25s; color: #94a3b8; font-size: 11px; }
        .st-collapsed .st-chevron { transform: rotate(-90deg); }
        .st-collapsed .st-body { display: none; }

        .pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
        }

        .pnr-dark {
            background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);
            border-radius: 14px; position: relative; overflow: hidden;
            padding: 20px; margin-bottom: 16px;
        }
        .pnr-dark::after {
            content: '✈'; position: absolute; right: -10px; bottom: -20px;
            font-size: 120px; opacity: .04; color: #fff; transform: rotate(-30deg);
        }

        /* Tab styles */
        .tab-bar { display:flex; gap:2px; border-bottom:1px solid #f1f5f9; padding:10px 16px 0; background:#f8fafc; }
        .tab-btn { padding:7px 14px; font-size:12px; font-weight:600; border:none; background:none; cursor:pointer; border-bottom:2px solid transparent; color:#94a3b8; margin-bottom:-1px; border-radius:6px 6px 0 0; transition:all .15s; }
        .tab-btn:hover { color:#1e293b; }
        .tab-btn.active { color:#0ea5e9; border-bottom-color:#0ea5e9; }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }

        /* Fare details pax filter */
        .pax-btn { padding:4px 12px; font-size:11px; font-weight:600; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer; transition:all .15s; }
        .pax-btn.active { background:#0ea5e9; color:#fff; border-color:#0ea5e9; }

        /* Table */
        .det-table { width:100%; border-collapse:collapse; font-size:12px; }
        .det-table th { text-align:left; padding:8px 14px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
        .det-table td { padding:10px 14px; border-bottom:1px solid #f8fafc; color:#334155; vertical-align:middle; }
        .det-table tr:last-child td { border-bottom:none; }
        .det-table tr:hover td { background:#f8fafc; }
        .det-table tfoot td { border-top:2px solid #f1f5f9; font-weight:600; }

        @media print {
            .no-print { display:none !important; }
            .st-card { box-shadow:none !important; }
        }
    </style>
@endpush

@section('content')
    @php
        $fmtDur = fn(int $m) => floor($m/60).'h '.($m%60).'m';
    @endphp

    <div class="container-fluid">

        {{-- ── Breadcrumb + Back --}}
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h1 class="h4 mb-0 text-gray-800">
                    <i class="fas fa-ticket-alt text-primary mr-1"></i>
                    Booking Detail
                </h1>
                <p class="text-muted mb-0" style="font-size:12px;">
                    <a href="{{ route('admin.users.index') }}" class="text-muted">Users</a>
                    <i class="fas fa-chevron-right mx-1" style="font-size:9px;"></i>
                    <a href="{{ route('admin.users.bookings', $user->id) }}" class="text-muted">
                        {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                    </a>
                    <i class="fas fa-chevron-right mx-1" style="font-size:9px;"></i>
                    <strong>{{ $booking->code }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.bookings', $user->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="row">

            {{-- ══════════════ LEFT COLUMN ══════════════ --}}
            <div class="col-lg-8">

                {{-- ── 1. Booking Header --}}
                <div class="st-card">
                    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:18px 20px;color:white;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <div style="font-size:22px;font-weight:800;letter-spacing:.04em;font-family:monospace;">
                                    {{ $booking->code }}
                                </div>
                                <div style="font-size:11px;opacity:.6;margin-top:3px;">
                                    Booked {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y, h:i A') : '—' }}
                                    @if(!empty($provRes['owning_pcc'])) · PCC: {{ $provRes['owning_pcc'] }} @endif
                                </div>
                            </div>
                            <span class="badge badge-{{ $statusConfig[0] }}" style="font-size:12px;padding:6px 14px;">
                            {{ $statusConfig[1] }}
                        </span>
                        </div>

                        {{-- Status pills --}}
                        <div class="d-flex flex-wrap gap-2 mt-3" style="font-size:10px;opacity:.7;">
                            @if(isset($booking->is_ticketed))
                                <span>Ticketed: <strong style="color:{{ $booking->is_ticketed ? '#6ee7b7':'#fca5a5' }}">{{ $booking->is_ticketed ? 'Yes':'No' }}</strong></span>
                            @endif
                            @if(isset($booking->is_paid))
                                <span>Paid: <strong style="color:{{ $booking->is_paid ? '#6ee7b7':'#fca5a5' }}">{{ $booking->is_paid ? 'Yes':'No' }}</strong></span>
                            @endif
                            @if(!empty($booking->source))
                                <span>Source: <strong style="color:white;">{{ strtoupper($booking->source) }}</strong></span>
                            @endif
                            @if(!empty($booking->flight_type))
                                <span>Type: <strong style="color:white;">{{ $booking->flight_type === 'round_trip' ? 'Round Trip' : 'One Way' }}</strong></span>
                            @endif
                        </div>
                    </div>

                    {{-- Status row --}}
                    <div style="padding:14px 18px;">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <div style="background:#f8fafc;border-radius:10px;padding:10px;text-align:center;">
                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Status</div>
                                    <span class="badge badge-{{ $statusConfig[0] }}">{{ $statusConfig[1] }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div style="background:#f8fafc;border-radius:10px;padding:10px;text-align:center;">
                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Payment</div>
                                    <span class="badge badge-{{ !empty($booking->is_paid) ? 'success':'warning' }}">
                                    {{ !empty($booking->is_paid) ? 'Paid':'Unpaid' }}
                                </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div style="background:#f8fafc;border-radius:10px;padding:10px;text-align:center;">
                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Booked On</div>
                                    <div style="font-size:12px;font-weight:600;color:#1e293b;">
                                        {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y') : '—' }}
                                    </div>
                                </div>
                            </div>
                            @if($tauDate)
                                <div class="col-6 col-md-3">
                                    <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:10px;text-align:center;">
                                        <div style="font-size:10px;color:#e11d48;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;font-weight:700;">⏰ Ticket Deadline</div>
                                        <div style="font-size:11px;font-weight:700;color:#be123c;">{{ $tauDate }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── 2. PNR Block --}}
                <div class="pnr-dark">
                    <div style="font-size:10px;color:#7dd3fc;text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:12px;">Reservation Codes</div>
                    <div class="row g-3">
                        <div class="col-md-{{ $pnrAirline ? '6' : '12' }}">
                            <div style="background:rgba(255,255,255,.1);border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,.15);">
                                <div style="font-size:10px;color:rgba(255,255,255,.5);margin-bottom:6px;">
                                    GDS · {{ $provCode }} · {{ strtoupper($booking->source ?? '') }}
                                </div>
                                <div style="font-size:28px;font-weight:800;color:#fff;font-family:monospace;letter-spacing:.15em;">
                                    {{ $pnrGDS ?? '—' }}
                                </div>
                                <div style="font-size:10px;color:rgba(255,255,255,.4);margin-top:4px;">Universal Record Locator</div>
                            </div>
                        </div>
                        @if($pnrAirline)
                            <div class="col-md-6">
                                <div style="background:rgba(255,255,255,.1);border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,.15);">
                                    <div style="font-size:10px;color:rgba(255,255,255,.5);margin-bottom:6px;">
                                        Airline · {{ $airCode }}
                                    </div>
                                    <div style="font-size:28px;font-weight:800;color:#93c5fd;font-family:monospace;letter-spacing:.15em;">
                                        {{ $pnrAirline }}
                                    </div>
                                    <div style="font-size:10px;color:rgba(255,255,255,.4);margin-top:4px;">Airline Confirmation Code</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- PCC / Agent meta --}}
                    <div class="row g-2 mt-2">
                        @if(!empty($provRes['owning_pcc']))
                            <div class="col-6 col-md-3">
                                <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;border:1px solid rgba(255,255,255,.1);">
                                    <div style="font-size:9px;color:rgba(255,255,255,.45);margin-bottom:2px;">PCC / Office</div>
                                    <div style="font-size:13px;font-weight:700;color:#fff;font-family:monospace;">{{ $provRes['owning_pcc'] }}</div>
                                </div>
                            </div>
                        @endif
                        @if(!empty($provRes['prime_host_id']))
                            <div class="col-6 col-md-3">
                                <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;border:1px solid rgba(255,255,255,.1);">
                                    <div style="font-size:9px;color:rgba(255,255,255,.45);margin-bottom:2px;">Host</div>
                                    <div style="font-size:13px;font-weight:700;color:#fff;">{{ $provRes['prime_host_id'] }}</div>
                                </div>
                            </div>
                        @endif
                        @if(!empty($provRes['number_of_updates']))
                            <div class="col-6 col-md-3">
                                <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px;border:1px solid rgba(255,255,255,.1);">
                                    <div style="font-size:9px;color:rgba(255,255,255,.45);margin-bottom:2px;">Updates</div>
                                    <div style="font-size:13px;font-weight:700;color:#fff;">{{ $provRes['number_of_updates'] }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── 3. Ticket Numbers --}}
                @if(!empty($ticketNumbers))
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.06em;">
                        <i class="fas fa-ticket-alt"></i> Ticket Numbers
                    </span>
                        @foreach($ticketNumbers as $tktNo)
                            <span style="font-family:monospace;font-size:13px;font-weight:700;color:#166534;background:#dcfce7;padding:3px 10px;border-radius:6px;border:1px solid #86efac;">
                            {{ $tktNo }}
                        </span>
                        @endforeach
                        @if(!empty($booking->ticket_issued_at))
                            <span style="font-size:11px;color:#15803d;margin-left:auto;">
                            Issued: {{ \Carbon\Carbon::parse($booking->ticket_issued_at)->format('d M Y, h:i A') }}
                        </span>
                        @endif
                    </div>
                @endif

                {{-- ── 4. Flight Itinerary --}}
                @if(!empty($segments))
                    <div class="st-card">
                        <div class="st-header" onclick="toggleCard(this)">
                        <span class="st-title">
                            <span class="st-icon" style="background:#e0f2fe;">
                                <i class="fas fa-route" style="color:#0284c7;"></i>
                            </span>
                            Flight Itinerary
                            <span style="font-size:11px;font-weight:400;color:#94a3b8;">
                                {{ count($journeys) }} leg{{ count($journeys)>1?'s':'' }} · {{ count($segments) }} segment{{ count($segments)>1?'s':'' }}
                            </span>
                        </span>
                            <i class="fas fa-chevron-down st-chevron"></i>
                        </div>
                        <div class="st-body" style="padding:16px;">
                            @foreach($legGroups as $legIdx => $legSegs)
                                @php
                                    $legFirst = $legSegs[0];
                                    $legLast  = $legSegs[count($legSegs)-1];
                                @endphp
                                <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:12px;background:#f8fafc;">

                                    {{-- Leg header --}}
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #e2e8f0;">
                                    <span style="background:#e2e8f0;color:#475569;font-size:10px;font-weight:700;padding:2px 8px;border-radius:5px;">
                                        Leg {{ $legIdx + 1 }}
                                    </span>
                                        <span style="font-size:11px;color:#94a3b8;">
                                        {{ $legFirst['origin'] ?? '' }} → {{ $legLast['destination'] ?? '' }}
                                        · {{ count($legSegs) }} flight{{ count($legSegs)>1?'s':'' }}
                                        · {{ !empty($legFirst['departure_time']) ? \Carbon\Carbon::parse($legFirst['departure_time'])->format('d M Y') : '' }}
                                    </span>
                                    </div>

                                    {{-- Main row --}}
                                    <div style="display:flex;align-items:center;gap:8px;">

                                        {{-- LEFT: Departure --}}
                                        <div style="flex-shrink:0;min-width:80px;">
                                            <div style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $legFirst['origin'] ?? '' }}</div>
                                            <div style="font-size:15px;font-weight:600;color:#0284c7;margin-top:3px;">
                                                {{ !empty($legFirst['departure_time']) ? \Carbon\Carbon::parse($legFirst['departure_time'])->format('H:i') : '' }}
                                            </div>
                                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                                {{ !empty($legFirst['departure_time']) ? \Carbon\Carbon::parse($legFirst['departure_time'])->format('D, d M') : '' }}
                                            </div>
                                            @if(!empty($legFirst['departure_terminal']))
                                                <div style="font-size:10px;color:#94a3b8;">T{{ $legFirst['departure_terminal'] }}</div>
                                            @endif
                                        </div>

                                        {{-- MIDDLE: Stops --}}
                                        <div style="flex:1;">
                                            @foreach($legSegs as $sIdx => $seg)
                                                {{-- Dashed line --}}
                                                <div style="display:flex;align-items:center;gap:4px;margin:3px 0;">
                                                    <div style="flex:1;border-top:2px dashed #cbd5e1;"></div>
                                                    <i class="fas fa-plane" style="color:#0ea5e9;font-size:10px;"></i>
                                                    <div style="flex:1;border-top:2px dashed #cbd5e1;"></div>
                                                </div>
                                                {{-- Flight tag --}}
                                                <div style="text-align:center;font-size:10px;color:#94a3b8;margin:2px 0;">
                                                <span style="background:white;border:1px solid #e2e8f0;padding:1px 7px;border-radius:4px;font-family:monospace;font-weight:600;">
                                                    {{ ($seg['carrier'] ?? '').$seg['flight_number'] }}
                                                </span>
                                                    <span style="margin-left:4px;">{{ $seg['cabin_class'] ?? '' }}</span>
                                                    <span style="margin-left:3px;color:#cbd5e1;">·</span>
                                                    <span style="margin-left:3px;">{{ $seg['equipment'] ?? '' }}</span>
                                                </div>

                                                {{-- Stopover box --}}
                                                @if($sIdx < count($legSegs) - 1)
                                                    @php
                                                        $nextSeg    = $legSegs[$sIdx + 1];
                                                        $layoverMin = (int)($seg['connection']['duration'] ?? 0);
                                                        $layoverStr = $layoverMin > 0 ? $fmtDur($layoverMin) : '—';
                                                    @endphp
                                                    <div style="background:white;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;margin:4px 8px;font-size:11px;">
                                                        <span style="font-weight:700;color:#1e293b;">{{ $seg['destination'] ?? '' }}</span>
                                                        <span style="color:#94a3b8;margin:0 5px;">·</span>
                                                        <span style="color:#64748b;">
                                                        Arr {{ !empty($seg['arrival_time']) ? \Carbon\Carbon::parse($seg['arrival_time'])->format('H:i') : '' }}
                                                        / Dep {{ !empty($nextSeg['departure_time']) ? \Carbon\Carbon::parse($nextSeg['departure_time'])->format('H:i') : '' }}
                                                    </span>
                                                        @if($layoverMin > 0)
                                                            <span style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1px 7px;border-radius:999px;font-weight:600;margin-left:6px;font-size:10px;">
                                                            {{ $layoverStr }} layover
                                                        </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach

                                            {{-- Last dashed line --}}
                                            <div style="display:flex;align-items:center;gap:4px;margin:3px 0;">
                                                <div style="flex:1;border-top:2px dashed #cbd5e1;"></div>
                                                <i class="fas fa-plane" style="color:#0ea5e9;font-size:10px;"></i>
                                                <div style="flex:1;border-top:2px dashed #cbd5e1;"></div>
                                            </div>
                                        </div>

                                        {{-- RIGHT: Arrival --}}
                                        <div style="flex-shrink:0;min-width:80px;text-align:right;">
                                            <div style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;">{{ $legLast['destination'] ?? '' }}</div>
                                            <div style="font-size:15px;font-weight:600;color:#0284c7;margin-top:3px;">
                                                {{ !empty($legLast['arrival_time']) ? \Carbon\Carbon::parse($legLast['arrival_time'])->format('H:i') : '' }}
                                            </div>
                                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                                {{ !empty($legLast['arrival_time']) ? \Carbon\Carbon::parse($legLast['arrival_time'])->format('D, d M') : '' }}
                                            </div>
                                            @if(!empty($legLast['arrival_terminal']))
                                                <div style="font-size:10px;color:#94a3b8;">T{{ $legLast['arrival_terminal'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Baggage pills --}}
                            @if(!empty($checkedBag) || !empty($cabinBag))
                                <div style="display:flex;flex-wrap:wrap;gap:6px;padding-top:10px;border-top:1px solid #f1f5f9;">
                                    @if($checkedBag)
                                        <span class="pill" style="background:#eef2ff;color:#4338ca;">
                                        <i class="fas fa-suitcase" style="font-size:9px;"></i> Checked: {{ $checkedBag }}kg
                                    </span>
                                    @endif
                                    @if($cabinBag)
                                        <span class="pill" style="background:#eef2ff;color:#4338ca;">
                                        <i class="fas fa-briefcase" style="font-size:9px;"></i> Cabin: {{ $cabinBag }}kg
                                    </span>
                                    @endif
                                    @foreach($fareBreakdowns as $fb)
                                        @if(!empty($fb['fare_construction'][0]['fare_basis']))
                                            <span class="pill" style="background:#f1f5f9;color:#475569;">
                                            <i class="fas fa-barcode" style="font-size:9px;"></i>
                                            {{ $fb['fare_construction'][0]['fare_basis'] }}
                                        </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ── 5. Passengers --}}
                @if(!empty($passengers))
                    <div class="st-card">
                        <div class="st-header" onclick="toggleCard(this)">
                        <span class="st-title">
                            <span class="st-icon" style="background:#ede9fe;">
                                <i class="fas fa-users" style="color:#7c3aed;"></i>
                            </span>
                            Passengers
                            <span style="font-size:11px;font-weight:400;color:#94a3b8;">({{ count($passengers) }})</span>
                        </span>
                            <i class="fas fa-chevron-down st-chevron"></i>
                        </div>
                        <div class="st-body">
                            <div style="overflow-x:auto;">
                                <table class="det-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Passenger</th>
                                        <th>Type</th>
                                        <th>Gender</th>
                                        <th>DOB</th>
                                        <th>Passport No.</th>
                                        <th>Expiry</th>
                                        <th>Nationality</th>
                                        <th>Ticket No.</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($passengers as $pi => $pax)
                                        @php
                                            $ptype    = $pax['traveler_type'] ?? 'ADT';
                                            $isAdult  = $ptype === 'ADT';
                                            $isChild  = in_array($ptype, ['CNN','CHD','C07']);
                                            $avatarBg = $isAdult ? '#4F46E5' : ($isChild ? '#059669' : '#F97316');
                                            $typePill = $isAdult
                                                ? 'background:#e0e7ff;color:#3730a3;'
                                                : ($isChild
                                                    ? 'background:#d1fae5;color:#065f46;'
                                                    : 'background:#ffedd5;color:#9a3412;');
                                            $initial  = strtoupper(substr($pax['first_name'] ?? 'P', 0, 1));
                                            $gender   = strtoupper($pax['gender'] ?? '');
                                            $passExp  = $pax['passport_expiry'] ?? '';
                                            $isExpiring = $passExp && strtotime($passExp) < strtotime('+6 months');
                                            $tktNo    = $tktMap[$pi + 1] ?? '';
                                        @endphp
                                        <tr>
                                            <td style="color:#94a3b8;">{{ $pi + 1 }}</td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:8px;">
                                                    <div style="width:30px;height:30px;border-radius:50%;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">
                                                        {{ $initial }}
                                                    </div>
                                                    <div>
                                                        <div style="font-weight:600;color:#1e293b;font-size:12px;">
                                                            {{ trim(($pax['prefix'] ?? '').' '.($pax['first_name'] ?? '').' '.($pax['last_name'] ?? '')) }}
                                                        </div>
                                                        @if(!empty($pax['name_association_id']))
                                                            <div style="font-size:10px;color:#94a3b8;">PAX #{{ $pax['name_association_id'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="pill" style="{{ $typePill }}">
                                                    {{ $paxLabel[$ptype] ?? $ptype }}
                                                </span>
                                            </td>
                                            <td style="color:#64748b;font-size:11px;">
                                                @if(in_array($gender, ['F','FEMALE']))
                                                    <i class="fas fa-venus mr-1"></i>Female
                                                @else
                                                    <i class="fas fa-mars mr-1"></i>Male
                                                @endif
                                            </td>
                                            <td style="color:#64748b;font-size:11px;">
                                                {{ !empty($pax['dob']) ? \Carbon\Carbon::parse($pax['dob'])->format('d M Y') : '—' }}
                                            </td>
                                            <td>
                                                <span style="font-family:monospace;font-size:11px;font-weight:600;color:#334155;letter-spacing:.05em;">
                                                    {{ $pax['passport_number'] ?? '—' }}
                                                </span>
                                            </td>
                                            <td style="font-size:11px;">
                                                @if($passExp)
                                                    <span style="{{ $isExpiring ? 'color:#e11d48;font-weight:600;' : 'color:#64748b;' }}">
                                                        {{ \Carbon\Carbon::parse($passExp)->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span style="color:#cbd5e1;">—</span>
                                                @endif
                                            </td>
                                            <td style="color:#64748b;font-size:11px;">
                                                {{ strtoupper($pax['nationality'] ?? ($pax['passport_country'] ?? '—')) }}
                                            </td>
                                            <td>
                                                @if($tktNo)
                                                    <span style="font-family:monospace;font-size:11px;font-weight:700;color:#0369a1;">{{ $tktNo }}</span>
                                                @else
                                                    <span style="color:#cbd5e1;">—</span>
                                                @endif
                                            </td>
                                            <td style="color:#64748b;font-size:11px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                {{ $pax['email'] ?? '—' }}
                                            </td>
                                            <td style="color:#64748b;font-size:11px;">
                                                {{ $pax['phone'] ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── 6. Fare Summary + Rules + SSR — Tab --}}
                @if(!empty($fareBreakdowns) || !empty($fareRules) || !empty($specialSvc))
                    <div class="st-card">
                        <div class="st-header" onclick="toggleCard(this)">
                        <span class="st-title">
                            <span class="st-icon" style="background:#fff1f2;">
                                <i class="fas fa-receipt" style="color:#e11d48;"></i>
                            </span>
                            Fare & Rules
                        </span>
                            <i class="fas fa-chevron-down st-chevron"></i>
                        </div>
                        <div class="st-body">
                            <div class="tab-bar">
                                <button class="tab-btn active" onclick="switchTab('fare-summary', this)">
                                    <i class="fas fa-receipt mr-1"></i>Fare Summary
                                </button>
                                @if(!empty($fareRules))
                                    <button class="tab-btn" onclick="switchTab('fare-rules', this)">
                                        <i class="fas fa-gavel mr-1"></i>Rules
                                    </button>
                                    <button class="tab-btn" onclick="switchTab('fare-penalties', this)">
                                        <i class="fas fa-ban mr-1"></i>Penalties
                                    </button>
                                @endif
                                @if(!empty($specialSvc))
                                    <button class="tab-btn" onclick="switchTab('fare-ssr', this)">
                                        <i class="fas fa-concierge-bell mr-1"></i>SSR
                                        <span style="background:#e0f2fe;color:#0369a1;border-radius:999px;padding:1px 6px;font-size:10px;margin-left:3px;">{{ count($specialSvc) }}</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Tab: Fare Summary --}}
                            <div id="fare-summary" class="tab-panel active">
                                <div style="overflow-x:auto;">
                                    <table class="det-table">
                                        <thead>
                                        <tr>
                                            <th>Passenger</th>
                                            <th style="text-align:right;">Base Fare</th>
                                            <th style="text-align:right;">Tax</th>
                                            <th style="text-align:right;">Total</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($fareBreakdowns as $fb)
                                            @php
                                                $fbType = $fb['traveler_type'] ?? 'ADT';
                                                $fbBase = (float)($fb['subtotal'] ?? 0);
                                                $fbTax  = (float)($fb['taxes']   ?? 0);
                                                $fbTot  = (float)($fb['total']   ?? 0);
                                                $fbCur  = $fb['currency'] ?? 'BDT';
                                                $paxIdxs = $fb['traveler_indices'] ?? [];
                                                $fbPaxList = collect($passengers)->filter(function($p, $i) use ($paxIdxs) {
                                                    return empty($paxIdxs) || in_array($i+1, $paxIdxs);
                                                });
                                                $typePill2 = match($fbType) {
                                                    'ADT' => 'background:#e0e7ff;color:#3730a3;',
                                                    'CNN','CHD','C07' => 'background:#d1fae5;color:#065f46;',
                                                    'INF' => 'background:#ffedd5;color:#9a3412;',
                                                    default => 'background:#f1f5f9;color:#475569;',
                                                };
                                            @endphp
                                            @foreach($fbPaxList as $bp)
                                                <tr>
                                                    <td>
                                                        <span class="pill" style="{{ $typePill2 }}">{{ $paxLabel[$fbType] ?? $fbType }}</span>
                                                        <span style="margin-left:8px;font-weight:600;color:#1e293b;font-size:12px;">
                                                            {{ trim(($bp['first_name'] ?? '').' '.($bp['last_name'] ?? '')) }}
                                                        </span>
                                                    </td>
                                                    <td style="text-align:right;color:#64748b;font-size:12px;">{{ $fbCur }} {{ number_format($fbBase, 2) }}</td>
                                                    <td style="text-align:right;color:#64748b;font-size:12px;">{{ $fbCur }} {{ number_format($fbTax, 2) }}</td>
                                                    <td style="text-align:right;font-weight:700;color:#e11d48;">{{ $fbCur }} {{ number_format($fbTot, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr style="background:#fff1f2;">
                                            <td colspan="3" style="text-align:right;font-weight:700;color:#1e293b;padding:10px 14px;">Grand Total</td>
                                            <td style="text-align:right;font-size:15px;font-weight:800;color:#e11d48;padding:10px 14px;">
                                                {{ $grandTotal['currency'] ?? 'BDT' }} {{ number_format((float)($grandTotal['total'] ?? 0), 2) }}
                                            </td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- Tab: Fare Rules --}}
                            @if(!empty($fareRules))
                                <div id="fare-rules" class="tab-panel">
                                    <div style="overflow-x:auto;">
                                        <table class="det-table">
                                            <thead>
                                            <tr>
                                                <th>Airline · Pax</th>
                                                <th>Refundable</th>
                                                <th>Changeable</th>
                                                <th>Refund Penalty</th>
                                                <th>Change Penalty</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($fareRules as $rule)
                                                @php
                                                    $rp0 = $rule['refund_penalties'][0]   ?? null;
                                                    $ep0 = $rule['exchange_penalties'][0] ?? null;
                                                @endphp
                                                <tr>
                                                    <td style="font-size:12px;color:#64748b;">
                                                        {{ $rule['airline'] ?? '' }} · {{ $paxLabel[$rule['passenger_code'] ?? ''] ?? ($rule['passenger_code'] ?? '') }}
                                                    </td>
                                                    <td>
                                                        @if($rule['is_refundable'] ?? false)
                                                            <span class="pill" style="background:#d1fae5;color:#065f46;"><i class="fas fa-check" style="font-size:8px;"></i> Yes</span>
                                                        @else
                                                            <span class="pill" style="background:#fee2e2;color:#991b1b;"><i class="fas fa-times" style="font-size:8px;"></i> No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($rule['is_changeable'] ?? false)
                                                            <span class="pill" style="background:#d1fae5;color:#065f46;"><i class="fas fa-check" style="font-size:8px;"></i> Yes</span>
                                                        @else
                                                            <span class="pill" style="background:#fee2e2;color:#991b1b;"><i class="fas fa-times" style="font-size:8px;"></i> No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($rp0)
                                                            <span style="font-weight:700;color:#e11d48;font-size:13px;">{{ $rp0['penalty_currency'] ?? '' }} {{ number_format((float)($rp0['penalty_amount'] ?? 0)) }}</span>
                                                            <div style="font-size:10px;color:#94a3b8;">{{ str_replace('_', ' ', $rp0['applicability'] ?? '') }}</div>
                                                        @else
                                                            <span style="color:#cbd5e1;">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($ep0)
                                                            <span style="font-weight:700;color:#2563eb;font-size:13px;">{{ $ep0['penalty_currency'] ?? '' }} {{ number_format((float)($ep0['penalty_amount'] ?? 0)) }}</span>
                                                            <div style="font-size:10px;color:#94a3b8;">{{ str_replace('_', ' ', $ep0['applicability'] ?? '') }}</div>
                                                        @else
                                                            <span style="color:#cbd5e1;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Tab: Penalties --}}
                                <div id="fare-penalties" class="tab-panel">
                                    <div style="overflow-x:auto;">
                                        <table class="det-table">
                                            <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Applicability</th>
                                                <th>Amount</th>
                                                <th>No-show</th>
                                                <th>No-show Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($fareRules as $rule)
                                                @foreach($rule['refund_penalties'] ?? [] as $p)
                                                    <tr>
                                                        <td><span class="pill" style="background:#fee2e2;color:#991b1b;"><i class="fas fa-undo" style="font-size:8px;"></i> Cancellation</span></td>
                                                        <td style="font-size:11px;color:#64748b;">{{ str_replace('_', ' ', $p['applicability'] ?? '') }}</td>
                                                        <td style="font-weight:700;color:#e11d48;">{{ $p['penalty_currency'] ?? '' }} {{ number_format((float)($p['penalty_amount'] ?? 0)) }}</td>
                                                        <td style="font-size:11px;color:#64748b;">{{ ($p['has_no_show_cost'] ?? false) ? 'Yes' : 'No' }}</td>
                                                        <td style="font-size:11px;color:#64748b;">{{ ($p['has_no_show_cost'] ?? false) ? (($p['no_show_currency'] ?? '').' '.number_format((float)($p['no_show_amount'] ?? 0))) : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                                @foreach($rule['exchange_penalties'] ?? [] as $p)
                                                    <tr>
                                                        <td><span class="pill" style="background:#dbeafe;color:#1e40af;"><i class="fas fa-exchange-alt" style="font-size:8px;"></i> Date Change</span></td>
                                                        <td style="font-size:11px;color:#64748b;">{{ str_replace('_', ' ', $p['applicability'] ?? '') }}</td>
                                                        <td style="font-weight:700;color:#2563eb;">{{ $p['penalty_currency'] ?? '' }} {{ number_format((float)($p['penalty_amount'] ?? 0)) }}</td>
                                                        <td style="font-size:11px;color:#64748b;">{{ ($p['has_no_show_cost'] ?? false) ? 'Yes' : 'No' }}</td>
                                                        <td style="font-size:11px;color:#64748b;">{{ ($p['has_no_show_cost'] ?? false) ? (($p['no_show_currency'] ?? '').' '.number_format((float)($p['no_show_amount'] ?? 0))) : '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Tab: SSR --}}
                            @if(!empty($specialSvc))
                                <div id="fare-ssr" class="tab-panel">
                                    <div style="overflow-x:auto;">
                                        <table class="det-table">
                                            <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Service</th>
                                                <th>Passenger</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($specialSvc as $svc)
                                                @php
                                                    $svcPaxNames = collect($svc['traveler_indices'] ?? [])
                                                        ->map(fn($idx) => trim(($passengers[$idx-1]['first_name'] ?? '').' '.($passengers[$idx-1]['last_name'] ?? '')))
                                                        ->filter()->implode(', ');
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <span style="font-family:monospace;font-size:11px;font-weight:700;background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:4px;">
                                                            {{ $svc['code'] ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td style="font-size:12px;color:#334155;">{{ $svc['name'] ?? '—' }}</td>
                                                    <td style="font-size:11px;color:#64748b;">{{ $svcPaxNames ?: '—' }}</td>
                                                    <td style="font-family:monospace;font-size:11px;color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ $svc['message'] ?? '—' }}
                                                    </td>
                                                    <td>
                                                        <span class="pill" style="{{ ($svc['status_code'] ?? '') === 'HK' ? 'background:#d1fae5;color:#065f46;' : 'background:#fef3c7;color:#92400e;' }}">
                                                            <i class="fas fa-circle" style="font-size:5px;"></i>
                                                            {{ $svc['status_name'] ?? ($svc['status_code'] ?? '') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ── 7. Fare Details (Tax Breakdown) --}}
                @if(!empty($fareBreakdowns))
                    <div class="st-card st-collapsed">
                        <div class="st-header" onclick="toggleCard(this)">
                        <span class="st-title">
                            <span class="st-icon" style="background:#f5f3ff;">
                                <i class="fas fa-file-invoice-dollar" style="color:#7c3aed;"></i>
                            </span>
                            Fare Details
                        </span>
                            <i class="fas fa-chevron-down st-chevron"></i>
                        </div>
                        <div class="st-body">

                            {{-- Pax filter --}}
                            <div style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                <span style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;">Passenger</span>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    @foreach($fareBreakdowns as $fbIdx => $fb)
                                        @php
                                            $paxKey9 = strtolower($fb['traveler_type']).'_'.$fbIdx;
                                        @endphp
                                        <button onclick="switchFdPax('{{ $paxKey9 }}', this)"
                                                class="pax-btn {{ $fbIdx === 0 ? 'active' : '' }}">
                                            {{ $fb['traveler_type'] }} · {{ $paxLabel[$fb['traveler_type']] ?? $fb['traveler_type'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Sub tabs --}}
                            <div class="tab-bar" id="fd-tab-bar">
                                <button class="tab-btn active" onclick="switchFdTab('fd-sum', this)" id="fd-tab-sum">Summary</button>
                                <button class="tab-btn" onclick="switchFdTab('fd-tax', this)" id="fd-tab-tax">Tax Breakdown</button>
                                <button class="tab-btn" onclick="switchFdTab('fd-calc', this)" id="fd-tab-calc">Fare Calculation</button>
                            </div>

                            {{-- Pax panels --}}
                            @foreach($fareBreakdowns as $fbIdx => $fb)
                                @php $paxKey9 = strtolower($fb['traveler_type']).'_'.$fbIdx; @endphp
                                <div class="fd-pax {{ $fbIdx !== 0 ? 'd-none' : '' }}" id="fdpax-{{ $paxKey9 }}">

                                    {{-- Summary --}}
                                    <div class="fd-panel" id="fdpax-{{ $paxKey9 }}-fd-sum" style="padding:16px;">
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;text-align:center;">
                                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Base Fare</div>
                                                    <div style="font-weight:700;color:#1e293b;">{{ $fb['currency'] ?? '' }} {{ number_format((float)($fb['subtotal'] ?? 0)) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;text-align:center;">
                                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Taxes</div>
                                                    <div style="font-weight:700;color:#1e293b;">{{ $fb['currency'] ?? '' }} {{ number_format((float)($fb['taxes'] ?? 0)) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;text-align:center;">
                                                    <div style="font-size:10px;color:#1d4ed8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Total</div>
                                                    <div style="font-weight:700;color:#1d4ed8;">{{ $fb['currency'] ?? '' }} {{ number_format((float)($fb['total'] ?? 0)) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="border:1px solid #f1f5f9;border-radius:10px;overflow:hidden;">
                                            @if(!empty($fb['validating_carrier']))
                                                <div style="display:flex;justify-content:space-between;padding:8px 14px;border-bottom:1px solid #f8fafc;font-size:12px;">
                                                    <span style="color:#94a3b8;">Validating Carrier</span>
                                                    <span style="font-weight:600;color:#1e293b;">{{ $fb['validating_carrier'] }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($fb['pricing_status_name']))
                                                <div style="display:flex;justify-content:space-between;padding:8px 14px;border-bottom:1px solid #f8fafc;font-size:12px;">
                                                    <span style="color:#94a3b8;">Pricing Status</span>
                                                    <span class="pill" style="{{ ($fb['pricing_status_code'] ?? '') === 'A' ? 'background:#d1fae5;color:#065f46;' : 'background:#fef3c7;color:#92400e;' }}">
                                                    {{ $fb['pricing_status_name'] }}
                                                </span>
                                                </div>
                                            @endif
                                            @if(!empty($fb['fare_construction'][0]['fare_basis']))
                                                <div style="display:flex;justify-content:space-between;padding:8px 14px;border-bottom:1px solid #f8fafc;font-size:12px;">
                                                    <span style="color:#94a3b8;">Fare Basis</span>
                                                    <span style="font-family:monospace;font-size:11px;color:#334155;">{{ $fb['fare_construction'][0]['fare_basis'] }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($fb['fare_construction'][0]['checked_bag_kg']))
                                                <div style="display:flex;justify-content:space-between;padding:8px 14px;font-size:12px;">
                                                    <span style="color:#94a3b8;">Checked Bag</span>
                                                    <span style="color:#334155;">{{ $fb['fare_construction'][0]['checked_bag_kg'] }} kg</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tax Breakdown --}}
                                    <div class="fd-panel d-none" id="fdpax-{{ $paxKey9 }}-fd-tax">
                                        @if(!empty($fb['tax_breakdown']))
                                            <div style="overflow-x:auto;">
                                                <table class="det-table">
                                                    <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Description</th>
                                                        <th style="text-align:right;">Amount</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($fb['tax_breakdown'] as $tx)
                                                        <tr>
                                                            <td>
                                                                <span style="background:#ede9fe;color:#5b21b6;font-size:10px;font-weight:700;font-family:monospace;padding:2px 7px;border-radius:4px;min-width:28px;display:inline-block;text-align:center;">
                                                                    {{ $tx['code'] ?? '' }}
                                                                </span>
                                                            </td>
                                                            <td style="font-size:12px;color:#64748b;">
                                                                {{ $taxLabels[$tx['code'] ?? ''] ?? 'Misc ('.$tx['code'].')' }}
                                                            </td>
                                                            <td style="text-align:right;font-weight:600;color:#334155;font-size:12px;">
                                                                {{ $tx['currency'] ?? '' }} {{ number_format((float)($tx['amount'] ?? 0)) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                    <tr>
                                                        <td colspan="2" style="padding:10px 14px;font-weight:700;color:#1e293b;">Total Tax</td>
                                                        <td style="text-align:right;font-weight:800;font-size:14px;color:#0284c7;padding:10px 14px;">
                                                            {{ $fb['currency'] ?? '' }} {{ number_format((float)($fb['taxes'] ?? 0)) }}
                                                        </td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <p style="padding:20px;color:#94a3b8;font-size:12px;">No tax breakdown available.</p>
                                        @endif
                                    </div>

                                    {{-- Fare Calculation --}}
                                    <div class="fd-panel d-none" id="fdpax-{{ $paxKey9 }}-fd-calc">
                                        @if(!empty($fb['fare_calculation']))
                                            <div style="padding:16px;">
                                                <div style="background:#1e293b;border-radius:10px;padding:14px;">
                                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Fare Calculation String</div>
                                                    <div style="font-family:monospace;font-size:12px;color:#86efac;word-break:break-all;line-height:1.8;">
                                                        {{ $fb['fare_calculation'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(!empty($fb['fare_construction']))
                                            <div style="overflow-x:auto;">
                                                <table class="det-table">
                                                    <thead>
                                                    <tr>
                                                        <th>Fare Basis</th>
                                                        <th>Route</th>
                                                        <th>Base Amount</th>
                                                        <th>Checked Bag</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($fb['fare_construction'] as $fc)
                                                        <tr>
                                                            <td style="font-family:monospace;font-size:11px;color:#334155;">{{ $fc['fare_basis'] ?? '—' }}</td>
                                                            <td style="font-weight:600;color:#1e293b;font-size:12px;">
                                                                {{ $fc['origin'] ?? '' }}
                                                                @if(!empty($fc['origin']) && !empty($fc['destination']))
                                                                    <span style="color:#94a3b8;margin:0 4px;">→</span>
                                                                @endif
                                                                {{ $fc['destination'] ?? '' }}
                                                            </td>
                                                            <td style="font-size:12px;color:#64748b;">
                                                                @if(!empty($fc['base_amount']) && (float)$fc['base_amount'] > 0)
                                                                    {{ $fc['base_currency'] ?? '' }} {{ $fc['base_amount'] }}
                                                                @else
                                                                    <span style="color:#cbd5e1;">—</span>
                                                                @endif
                                                            </td>
                                                            <td style="font-size:12px;color:#64748b;">
                                                                {{ !empty($fc['checked_bag_kg']) ? $fc['checked_bag_kg'].' kg' : '—' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── 8. Remarks --}}
                @if(!empty($remarks))
                    <div class="st-card st-collapsed">
                        <div class="st-header" onclick="toggleCard(this)">
                        <span class="st-title">
                            <span class="st-icon" style="background:#fef3c7;">
                                <i class="fas fa-comment-alt" style="color:#d97706;"></i>
                            </span>
                            Remarks
                            <span style="font-size:11px;font-weight:400;color:#94a3b8;">({{ count($remarks) }})</span>
                        </span>
                            <i class="fas fa-chevron-down st-chevron"></i>
                        </div>
                        <div class="st-body" style="padding:14px 16px;">
                            @foreach($remarks as $remark)
                                <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f8fafc;">
                                <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;flex-shrink:0;margin-top:1px;">
                                    {{ $remark['type'] ?? '' }}
                                </span>
                                    <span style="font-size:12px;color:#334155;">{{ $remark['text'] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
            {{-- /LEFT --}}

            {{-- ══════════════ RIGHT SIDEBAR ══════════════ --}}
            <div class="col-lg-4">
                <div style="position:sticky;top:90px;">

                    {{-- Payment Summary --}}
                    <div class="st-card">
                        <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px;">
                        <span class="st-icon" style="background:#ecfdf5;">
                            <i class="fas fa-receipt" style="color:#059669;"></i>
                        </span>
                            <span style="font-size:13px;font-weight:700;color:#1e293b;">Payment Summary</span>
                        </div>
                        <div style="padding:14px 16px;">
                            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#64748b;">Base Fare</span>
                                    <span style="font-weight:600;color:#1e293b;">৳{{ number_format($baseFee, 2) }}</span>
                                </div>
                                @if($taxFee > 0)
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#64748b;">Taxes</span>
                                        <span style="font-weight:600;color:#1e293b;">৳{{ number_format($taxFee, 2) }}</span>
                                    </div>
                                @endif
                                @if($serviceFee > 0)
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#64748b;">Service Charge</span>
                                        <span style="font-weight:600;color:#1e293b;">৳{{ number_format($serviceFee, 2) }}</span>
                                    </div>
                                @endif
                                @if($aitFee > 0)
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#64748b;">AIT</span>
                                        <span style="font-weight:600;color:#1e293b;">৳{{ number_format($aitFee, 2) }}</span>
                                    </div>
                                @endif
                                @if($penaltyAmt > 0)
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#e11d48;">Penalty</span>
                                        <span style="font-weight:600;color:#e11d48;">৳{{ number_format($penaltyAmt, 2) }}</span>
                                    </div>
                                @endif
                                @if($totalDisc > 0)
                                    <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px dashed #f1f5f9;">
                                        <span style="color:#059669;">Discount</span>
                                        <span style="font-weight:600;color:#059669;">−৳{{ number_format($totalDisc, 2) }}</span>
                                    </div>
                                @endif
                                <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:2px solid #f1f5f9;">
                                    <span style="font-weight:700;color:#1e293b;font-size:14px;">Total</span>
                                    <span style="font-weight:800;color:#0284c7;font-size:18px;">৳{{ number_format($bookTotal, 2) }}</span>
                                </div>
                                @if($walletUsed > 0)
                                    <div style="display:flex;justify-content:space-between;font-size:12px;">
                                        <span style="color:#7c3aed;">Wallet Used</span>
                                        <span style="font-weight:600;color:#7c3aed;">−৳{{ number_format($walletUsed, 2) }}</span>
                                    </div>
                                    <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:10px;padding:10px 14px;">
                                        <div style="font-size:10px;color:#059669;font-weight:700;margin-bottom:3px;">Payable Amount</div>
                                        <div style="font-size:20px;font-weight:800;color:#15803d;">৳{{ number_format($payable, 2) }}</div>
                                    </div>
                                @else
                                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:10px 14px;">
                                        <div style="font-size:10px;color:#1d4ed8;font-weight:700;margin-bottom:3px;">Amount Paid</div>
                                        <div style="font-size:20px;font-weight:800;color:#1d4ed8;">৳{{ number_format($bookTotal, 2) }}</div>
                                    </div>
                                @endif
                                @if(!empty($booking->gateway))
                                    <div style="font-size:11px;color:#94a3b8;padding-top:6px;border-top:1px solid #f1f5f9;">
                                        <i class="fas fa-credit-card mr-1"></i>
                                        Via <strong style="color:#475569;">{{ ucfirst(str_replace('_', ' ', $booking->gateway)) }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="st-card">
                        <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;">Quick Info</span>
                        </div>
                        <div style="padding:12px 16px;">
                            <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#94a3b8;">Route</span>
                                    <span style="font-weight:700;color:#1e293b;">{{ $booking->flight_from ?? '—' }} → {{ $booking->flight_to ?? '—' }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#94a3b8;">Airline</span>
                                    <span style="font-weight:600;color:#334155;">{{ $booking->airline ?? '—' }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#94a3b8;">Cabin</span>
                                    <span style="font-weight:600;color:#334155;">{{ $booking->seat_class ?? '—' }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#94a3b8;">Passengers</span>
                                    <span style="font-weight:600;color:#334155;">
                                    {{ $adultCount + $childCount + $infantCount }} pax
                                    <span style="color:#94a3b8;font-weight:400;">
                                        ({{ $adultCount }}A{{ $childCount > 0 ? ' '.$childCount.'C' : '' }}{{ $infantCount > 0 ? ' '.$infantCount.'I' : '' }})
                                    </span>
                                </span>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:#94a3b8;">Source</span>
                                    <span style="font-weight:600;color:#334155;">{{ strtoupper($booking->source ?? '—') }}</span>
                                </div>
                                @if(!empty($checkedBag))
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#94a3b8;">Checked Bag</span>
                                        <span style="font-weight:600;color:#334155;">{{ $checkedBag }}kg</span>
                                    </div>
                                @endif
                                @if(!empty($cabinBag))
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#94a3b8;">Cabin Bag</span>
                                        <span style="font-weight:600;color:#334155;">{{ $cabinBag }}kg</span>
                                    </div>
                                @endif
                                @if($tauDate)
                                    <div style="display:flex;justify-content:space-between;">
                                        <span style="color:#94a3b8;">TAU Deadline</span>
                                        <span style="font-weight:700;color:#e11d48;">{{ $tauDate }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- User Info --}}
                    <div class="st-card">
                        <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;">Customer</span>
                        </div>
                        <div style="padding:14px 16px;display:flex;align-items:center;gap:10px;">
                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:15px;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;color:#1e293b;font-size:13px;">
                                    {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                                </div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $user->email ?? '' }}</div>
                                @if(!empty($user->phone))
                                    <div style="font-size:11px;color:#94a3b8;">{{ $user->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            {{-- /RIGHT --}}

        </div>
    </div>
@endsection

@push('js')
    <script>
        // ── Section collapse/expand
        function toggleCard(header) {
            header.closest('.st-card').classList.toggle('st-collapsed');
        }

        // ── Main tabs (Fare & Rules card)
        let activeMainTab = 'fare-summary';
        function switchTab(id, btn) {
            document.querySelectorAll('#fare-summary, #fare-rules, #fare-penalties, #fare-ssr')
                .forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                if (b.closest('#fd-tab-bar') === null) b.classList.remove('active');
            });
            const panel = document.getElementById(id);
            if (panel) panel.classList.add('active');
            btn.classList.add('active');
            activeMainTab = id;
        }

        // ── Fare Details pax filter
        let fdActivePax = '{{ strtolower($fareBreakdowns[0]["traveler_type"] ?? "adt") }}_0';
        let fdActiveTab = 'fd-sum';

        function switchFdPax(paxKey, btn) {
            document.querySelectorAll('.pax-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.fd-pax').forEach(d => d.classList.add('d-none'));
            btn.classList.add('active');
            fdActivePax = paxKey;
            const panel = document.getElementById('fdpax-' + paxKey);
            if (panel) panel.classList.remove('d-none');
            // show correct sub-tab
            document.querySelectorAll('#fdpax-' + paxKey + ' .fd-panel').forEach(p => p.classList.add('d-none'));
            const active = document.getElementById('fdpax-' + paxKey + '-' + fdActiveTab);
            if (active) active.classList.remove('d-none');
        }

        function switchFdTab(tab, btn) {
            document.querySelectorAll('#fd-tab-bar .tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            fdActiveTab = tab;
            document.querySelectorAll('#fdpax-' + fdActivePax + ' .fd-panel').forEach(p => p.classList.add('d-none'));
            const active = document.getElementById('fdpax-' + fdActivePax + '-' + tab);
            if (active) active.classList.remove('d-none');
        }
    </script>
@endpush
