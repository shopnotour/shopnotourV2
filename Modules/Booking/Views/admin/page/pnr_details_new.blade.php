@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        navy: { 50:'#f0f2ff', 100:'#e0e5ff', 200:'#c5cdf8', 300:'#9aaaf2', 400:'#6b7de8', 500:'#4555d8', 600:'#2e3fc4', 700:'#1a237e', 800:'#161d6b', 900:'#111658' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .section-body { display: block; }
        .section-body.collapsed { display: none; }
        .fare-calc-bg { background: #0f172a; color: #86efac; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; padding: 10px 14px; word-break: break-all; }
        /* Bootstrap override */
        #user-options-list { list-style: none !important; margin: 0 !important; padding: 4px 0 !important; }
        #user-options-list li { list-style: none !important; }
        .pnr-uname { color: #1e293b !important; font-size: 0.83rem !important; font-weight: 600 !important; }
        .pnr-uid   { color: #94a3b8 !important; font-size: 0.72rem !important; font-family: monospace !important; }
        .pnr-uavatar { background: #e0e5ff !important; color: #1a237e !important; }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-slate-50 p-4 md:p-6" style="font-family:'DM Sans',sans-serif;">

        {{-- ── PNR Search Header ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-5 overflow-hidden">
            <div class="bg-gradient-to-r from-navy-700 to-navy-600 px-6 py-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fa fa-ticket text-white text-sm"></i>
                        </div>
                        <h1 class="text-white font-bold text-lg tracking-tight">Booking Details</h1>
                    </div>
                    <div class="md:ml-auto">
                        <form method="GET" action="{{ route('pnr.booking.search') }}" class="flex gap-2">
                            <select name="source" required class="text-sm rounded-xl border-0 bg-white/15 text-white px-3 py-2 focus:outline-none" style="min-width:120px;">
                                <option value="" class="text-slate-800">Source</option>
                                <option value="sabre" class="text-slate-800" {{ ($searchSource ?? request('source')) == 'sabre' ? 'selected' : '' }}>Sabre</option>
                                <option value="travelport" class="text-slate-800" {{ ($searchSource ?? request('source')) == 'travelport' ? 'selected' : '' }}>Travelport</option>
                            </select>
                            <input type="text" name="pnr" required placeholder="Enter PNR (e.g. TNDUCL)" value="{{ $searchPnr ?? request('pnr') }}"
                                   class="text-sm rounded-xl border-0 bg-white/15 text-white placeholder-white/60 px-4 py-2 focus:outline-none flex-1 min-w-0">
                            <button type="submit" class="bg-white text-navy-700 font-bold text-sm px-5 py-2 rounded-xl hover:bg-slate-100 transition-colors whitespace-nowrap flex items-center gap-2">
                                <i class="fa fa-search text-xs"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.message')

        @if(isset($existingBooking) && $existingBooking)
            <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 mb-4 flex items-start gap-3">
                <i class="fa fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="flex-1 text-sm">
                    <strong class="text-blue-800">Existing Booking Found!</strong>
                    <span class="text-blue-700"> This PNR is already in the system. </span>
                    <a href="#" class="text-blue-600 font-semibold underline">View Booking #{{ $existingBooking->code }}</a>
                </div>
                <button onclick="this.closest('div').remove()" class="text-blue-400 hover:text-blue-600 text-lg leading-none">&times;</button>
            </div>
        @endif

        @if(isset($bookingData) && ($bookingData['success'] ?? false))
            @php
                $b              = $bookingData;
                $bookingId      = $b['booking_id']    ?? 'N/A';
                $startDate      = $b['start_date']    ?? null;
                $endDate        = $b['end_date']      ?? null;
                $isTicketed     = $b['is_ticketed']   ?? false;
                $isCancelable   = $b['is_cancelable'] ?? false;
                $provRes        = $b['provider_reservation'] ?? [];
                $creationDate   = $provRes['host_create_date'] ?? '';
                $creationTime   = $provRes['create_date'] ?? '';
                // creation time আলাদাভাবে নাও
                $agentSine      = $b['agency_info']['agent_code'] ?? 'N/A';
                $owningPcc      = $provRes['owning_pcc'] ?? 'N/A';
                $supplierCode   = $b['supplier_locator']['supplier_code'] ?? null;
                $supplierLocator= $b['supplier_locator']['locator_code']  ?? null;
                $providerCode   = $provRes['locator_code'] ?? null;
                $contactInfo    = $b['contact_info'] ?? [];
                $phones         = $contactInfo['phones'] ?? [];
                $emails         = $contactInfo['emails'] ?? [];
                $passengers     = $b['passengers'] ?? [];
                $flightTickets  = $b['flight_tickets'] ?? [];
                $segments       = $b['segments'] ?? [];
                $journeys       = $b['journeys'] ?? [];
                $tripType       = count($journeys) > 1 ? 'Round Trip' : (count($journeys) == 1 ? 'One Way' : (count($segments) > 0 ? 'Multi-City' : 'N/A'));
                $pricing        = $b['pricing'] ?? [];
                $grandTotal     = $pricing['grand_total'] ?? [];
                $fareBreakdowns = $pricing['fare_breakdowns'] ?? [];
                $currency       = $grandTotal['currency'] ?? 'BDT';
                $checkedBagKg   = $pricing['checked_bag_kg'] ?? 0;
                $cabinBagKg     = $pricing['cabin_bag_kg']   ?? 0;
                $baggageCharges = $pricing['checked_baggage_charges'] ?? [];
                $perLegBaggage  = $pricing['per_leg_baggage'] ?? [];
                $fareRules      = $b['fare_rules'] ?? [];
                $actionStatus   = $b['action_status'] ?? [];
                $ticketDeadline = $actionStatus['ticket_date'] ?? null;
                $accountingItems= $b['accounting_items'] ?? [];
                $specialServices= $b['special_services'] ?? [];
                $remarks        = $b['remarks'] ?? [];
                $adultCount     = collect($passengers)->where('traveler_type','ADT')->count();
                $childCount     = collect($passengers)->where('traveler_type','CNN')->count();
                $infantCount    = collect($passengers)->where('traveler_type','INF')->count();
                $originSeg      = $segments[0] ?? [];
                $lastSeg        = !empty($segments) ? end($segments) : [];
                $firstAirport   = $originSeg['origin']      ?? 'N/A';
                $lastAirport    = $lastSeg['destination']   ?? 'N/A';

                // ── PQ vs PQR separation ──
                $activeFares   = collect($fareBreakdowns)->filter(fn($f) => ($f['record_type_code'] ?? 'PQ') === 'PQ')->values()->all();
                $exchangeFares = collect($fareBreakdowns)->filter(fn($f) => in_array($f['record_type_code'] ?? '', ['PQR','PQX']))->values()->all();
                if (empty($activeFares) && empty($exchangeFares)) $activeFares = $fareBreakdowns;
            @endphp

            {{-- ── Overview Card ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                <div class="bg-gradient-to-r from-navy-700 to-navy-600 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fa fa-plane text-white text-sm"></i>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-white/70 text-xs font-medium uppercase tracking-widest">PNR</span>
                            <span class="text-white font-black text-xl tracking-wider">{{ $bookingId }}</span>
                            @if($providerCode && $providerCode !== $bookingId)
                                <span class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-lg font-mono">GDS: {{ $providerCode }}</span>
                            @endif
                            @if($supplierLocator)
                                <span class="bg-sky-500/40 text-white text-xs px-2 py-0.5 rounded-lg">{{ $supplierCode }} {{ $supplierLocator }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isTicketed)
                            <span class="bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5"><i class="fa fa-check-circle"></i> TICKETED</span>
                        @endif
                        @if($isCancelable)
                            <span class="bg-amber-500 text-white text-xs font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5"><i class="fa fa-undo"></i> CANCELABLE</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 divide-x divide-y md:divide-y-0 divide-slate-100">
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Booking Date</div>
                        <div class="font-bold text-slate-800 text-sm">{{ $creationDate ? \Carbon\Carbon::parse($creationDate)->format('d M Y') : '—' }}</div>
                    </div>
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Route</div>
                        <div class="font-black text-navy-700 text-sm tracking-wider">{{ $firstAirport }} → {{ $lastAirport }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">{{ $tripType }}</div>
                    </div>
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Travel Date</div>
                        <div class="font-bold text-slate-800 text-sm">{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '—' }}</div>
                        @if($endDate && $endDate !== $startDate)
                            <div class="text-xs text-slate-400 mt-0.5">→ {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        @endif
                    </div>
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Passengers</div>
                        <div class="font-bold text-slate-800 text-sm">{{ $adultCount }}A@if($childCount > 0) + {{ $childCount }}C@endif@if($infantCount > 0) + {{ $infantCount }}I@endif</div>
                    </div>
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">PCC / Agent</div>
                        <div class="font-bold text-slate-800 text-sm">{{ $owningPcc }}</div>
                        <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $agentSine }}</div>
                    </div>
                    <div class="px-4 py-4 text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-widest font-medium mb-1">Grand Total</div>
                        <div class="font-black text-emerald-600 text-base">{{ number_format($grandTotal['total'] ?? 0, 2) }}</div>
                        <div class="text-xs text-slate-400">{{ $currency }}</div>
                    </div>
                </div>

                @if($ticketDeadline || !empty($phones) || !empty($emails))
                    <div class="border-t border-slate-100 px-6 py-3 flex flex-wrap gap-4 items-center bg-slate-50/50">
                        @if($ticketDeadline)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="w-6 h-6 bg-red-100 rounded-lg flex items-center justify-center"><i class="fa fa-clock-o text-red-500 text-xs"></i></span>
                                <span class="text-slate-500">Ticket Deadline:</span>
                                <span class="font-bold text-red-600">{{ \Carbon\Carbon::parse($ticketDeadline)->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                        @if(!empty($phones))
                            <div class="flex items-center gap-2 text-sm">
                                <i class="fa fa-phone text-slate-400 text-xs"></i>
                                <span class="text-slate-600">{{ is_array($phones[0]) ? ($phones[0]['number'] ?? $phones[0]) : $phones[0] }}</span>
                            </div>
                        @endif
                        @if(!empty($emails))
                            <div class="flex items-center gap-2 text-sm">
                                <i class="fa fa-envelope text-slate-400 text-xs"></i>
                                <span class="text-slate-600">{{ is_array($emails[0]) ? ($emails[0]['email'] ?? $emails[0]) : $emails[0] }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ── ASSIGN ACTION CARD ── --}}
            @if(!empty($passengers))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4" style="overflow:visible;">
                    <div class="flex flex-wrap items-center">
                        <div class="flex items-center gap-3 px-5 py-4 border-r border-slate-100">
                            <div class="w-9 h-9 bg-navy-700 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fa fa-user-plus text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="font-bold text-navy-700 text-sm">Assign Booking</div>
                                <div class="text-xs text-slate-400">Select customer & assign tickets</div>
                            </div>
                        </div>

                        <div class="flex-1 px-5 py-3 flex items-center min-w-0">
                            @if(isset($users) && $users && !($users instanceof \Illuminate\Support\Collection))
                                <input type="hidden" id="selected-user-id" value="{{ $users->id }}">
                                <div style="display:inline-flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:12px;padding:8px 14px;font-size:0.84rem;font-weight:600;">
                                    <span style="width:26px;height:26px;background:#22c55e;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($users->name,0,1)) }}</span>
                                    {{ $users->name }}
                                </div>
                            @elseif(isset($users) && $users instanceof \Illuminate\Support\Collection && $users->count())
                                <input type="hidden" id="selected-user-id">
                                <div id="user-dropdown-wrap" style="position:relative;min-width:260px;max-width:320px;">
                                    <div id="user-select-trigger" onclick="toggleUserDropdown(event)"
                                         style="display:flex;align-items:center;justify-content:space-between;gap:10px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:9px 14px;cursor:pointer;user-select:none;transition:border-color 0.15s;">
                        <span style="display:flex;align-items:center;gap:8px;font-size:0.83rem;font-weight:600;color:#475569;">
                            <i class="fa fa-user" style="color:#94a3b8;font-size:0.75rem;"></i>
                            <span id="user-select-label">Select Customer</span>
                        </span>
                                        <i class="fa fa-chevron-down" id="user-dd-arrow" style="color:#94a3b8;font-size:0.72rem;transition:transform 0.2s;"></i>
                                    </div>
                                    <div id="user-dropdown-panel"
                                         style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;background:#fff;border:2px solid #c5cae9;border-radius:14px;z-index:9999;box-shadow:0 8px 24px rgba(26,35,126,0.13);overflow:hidden;min-width:280px;">
                                        <div style="padding:8px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                                            <div style="display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:7px 10px;">
                                                <i class="fa fa-search" style="color:#94a3b8;font-size:0.75rem;"></i>
                                                <input type="text" id="user-search-input" placeholder="Search by name..." oninput="filterUsers(this.value)"
                                                       style="flex:1;border:none;outline:none;font-size:0.82rem;color:#334155;background:transparent;font-family:'DM Sans',sans-serif;">
                                            </div>
                                        </div>
                                        <ul id="user-options-list" style="list-style:none;margin:0;padding:4px 0;max-height:210px;overflow-y:auto;">
                                            <li data-value="" data-label="Select Customer" onclick="selectUser('', 'Select Customer')"
                                                style="display:flex;align-items:center;padding:8px 12px;cursor:pointer;border-bottom:1px solid #f8fafc;font-size:0.82rem;color:#94a3b8;list-style:none;">
                                                — Select Customer —
                                            </li>
                                            @foreach($users as $user)
                                                <li data-value="{{ $user->id }}" data-label="{{ $user->name }}"
                                                    onclick="selectUser('{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='transparent'"
                                                    style="display:flex;align-items:center;gap:10px;padding:8px 12px;cursor:pointer;list-style:none;transition:background 0.12s;">
                                <span class="pnr-uavatar" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;background:#e0e5ff !important;color:#1a237e !important;">
                                    {{ strtoupper(substr($user->name,0,1)) }}
                                </span>
                                                    <span class="pnr-uname" style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#1e293b !important;font-weight:600 !important;font-size:0.83rem !important;">{{ $user->name }}</span>
                                                    <span class="pnr-uid" style="flex-shrink:0;color:#94a3b8 !important;font-family:monospace !important;font-size:0.72rem !important;">#{{ $user->id }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="px-5 py-3 border-l border-slate-100">
                            @if($isTicketed)
                                <button type="button" onclick="assignTicketsToPassengers({{ isset($existingBooking) && $existingBooking ? 'true' : 'false' }})"
                                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm whitespace-nowrap">
                                    <i class="fa fa-{{ isset($existingBooking) && $existingBooking ? 'refresh' : 'ticket' }} text-xs"></i>
                                    {{ isset($existingBooking) && $existingBooking ? 'Update Tickets' : 'Assign Tickets' }}
                                </button>
                            @else
                                <div class="inline-flex items-center gap-2 bg-slate-100 text-slate-400 font-semibold text-sm px-5 py-2.5 rounded-xl whitespace-nowrap">
                                    <i class="fa fa-clock-o text-xs"></i> Not Ticketed
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── PASSENGERS ── --}}
            @if(!empty($passengers))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-passengers', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fa fa-users text-sky-600 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Passengers</span>
                            <span class="bg-sky-100 text-sky-700 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($passengers) }}</span>
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-passengers" class="section-body overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                @foreach(['#','Name','Type','Ticket No.','Status','Passport','FF / Loyalty','Contact'] as $h)
                                    <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $h }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                            @foreach($passengers as $idx => $pax)
                                @php
                                    $travelerIdx   = $idx + 1;
                                    $ticketsForPax = collect($flightTickets)->filter(fn($t) => ($t['traveler_index'] ?? null) == $travelerIdx);

                                    // Active ticket: Not Flown (I) বা Flown (B/F) আছে, all_exchanged না
                                    $activeTicket = $ticketsForPax->first(fn($t) =>
                                        !($t['all_exchanged'] ?? false) &&
                                        !empty($t['flight_coupons']) &&
                                        collect($t['flight_coupons'])->some(fn($c) =>
                                            in_array($c['coupon_status_code'] ?? '', ['I','F','B'])
                                        )
                                    );
                                    // Fully exchanged ticket (পুরানো)
                                    $exchangedTicket = $ticketsForPax->first(fn($t) =>
                                        ($t['all_exchanged'] ?? false)
                                    );
                                    $ticket = $activeTicket ?? $ticketsForPax->last();

                                    $ticketNum    = $ticket['number']             ?? null;
                                    $ticketDate   = $ticket['date']               ?? null;
                                    $ticketStatus = $ticket['ticket_status']      ?? null;
                                    $ticketCode   = $ticket['ticket_status_code'] ?? '';
                                    $firstCoupon  = $ticket['flight_coupons'][0]  ?? null;
                                    $couponStatus = $firstCoupon['coupon_status']      ?? null;
                                    $couponCode   = $firstCoupon['coupon_status_code'] ?? '';

                                    // Exchanged ticket number (পুরানো ticket আলাদা দেখানোর জন্য)
                                    $exchNum = ($exchangedTicket && $exchangedTicket !== $ticket)
                                        ? ($exchangedTicket['number'] ?? null)
                                        : null;

                                    $passportDoc = collect($pax['identity_documents'] ?? [])->firstWhere('documentType','PASSPORT')
                                                ?? collect($pax['identity_documents'] ?? [])->firstWhere('document_type','PASSPORT');

                                    $typeColorMap = ['ADT'=>'bg-emerald-100 text-emerald-700','CNN'=>'bg-amber-100 text-amber-700','INF'=>'bg-sky-100 text-sky-700'];
                                    $tStatusMap   = ['TE'=>'bg-emerald-100 text-emerald-700','Issued'=>'bg-emerald-100 text-emerald-700','TV'=>'bg-red-100 text-red-700','Voided'=>'bg-red-100 text-red-700','TR'=>'bg-amber-100 text-amber-700','TX'=>'bg-sky-100 text-sky-700'];
                                    $cStatusMap   = ['I'=>'bg-sky-100 text-sky-700','Not Flown'=>'bg-sky-100 text-sky-700','F'=>'bg-emerald-100 text-emerald-700','Flown'=>'bg-emerald-100 text-emerald-700','B'=>'bg-emerald-100 text-emerald-700','E'=>'bg-purple-100 text-purple-700','Exchanged'=>'bg-purple-100 text-purple-700','V'=>'bg-red-100 text-red-700','R'=>'bg-amber-100 text-amber-700'];
                                    $paxType = $pax['traveler_type'] ?? 'ADT';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="w-6 h-6 bg-navy-100 text-navy-700 rounded-full flex items-center justify-center text-xs font-bold">{{ $travelerIdx }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $pax['first_name'] ?? '' }} {{ $pax['last_name'] ?? '' }}</td>
                                    <td class="px-4 py-3">
                        <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $typeColorMap[$paxType] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $pax['passenger_type'] ?? $pax['traveler_type'] ?? 'ADULT' }}
                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($ticketNum)
                                            <div class="font-bold text-navy-700 text-xs font-mono">{{ $ticketNum }}</div>
                                            @if($ticketDate)<div class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($ticketDate)->format('d M Y') }}</div>@endif
                                            @if($exchNum)
                                                <div class="mt-1">
                                    <span class="text-xs bg-purple-50 text-purple-500 px-1.5 py-0.5 rounded font-mono border border-purple-100" title="Exchanged from">
                                        ↩ {{ $exchNum }}
                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">Not Issued</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($ticketStatus)
                                            <span class="text-xs font-semibold px-2 py-1 rounded-lg {{ $tStatusMap[$ticketCode] ?? $tStatusMap[$ticketStatus] ?? 'bg-slate-100 text-slate-600' }}">{{ $ticketStatus }}</span>
                                            @if($couponStatus)
                                                <div class="mt-1"><span class="text-xs px-2 py-0.5 rounded-lg {{ $cStatusMap[$couponCode] ?? $cStatusMap[$couponStatus] ?? 'bg-slate-100 text-slate-500' }}">{{ $couponStatus }}</span></div>
                                            @endif
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600 space-y-0.5">
                                        @if($passportDoc)
                                            @php
                                                $exp = $pax['passport_expiry'] ?? ($passportDoc['expiryDate'] ?? null);
                                                $dob = $pax['dob'] ?? ($passportDoc['birthDate'] ?? null);
                                                $g   = $pax['gender'] ?? ($passportDoc['gender'] ?? '');
                                            @endphp
                                            <div><span class="text-slate-400">No:</span> <span class="font-mono font-semibold">{{ $pax['passport_number'] ?? ($passportDoc['documentNumber'] ?? 'N/A') }}</span></div>
                                            <div><span class="text-slate-400">Exp:</span> {{ $exp ? \Carbon\Carbon::parse($exp)->format('d M Y') : 'N/A' }}</div>
                                            <div><span class="text-slate-400">DOB:</span> {{ $dob ? \Carbon\Carbon::parse($dob)->format('d M Y') : 'N/A' }}</div>
                                            <div>@if(in_array(strtoupper($g),['M','MALE']))<span class="text-blue-600 font-semibold">♂ Male</span>@elseif(in_array(strtoupper($g),['F','FEMALE']))<span class="text-pink-600 font-semibold">♀ Female</span>@else N/A @endif</div>
                                            <div><span class="text-slate-400">Country:</span> {{ $pax['passport_country'] ?? ($passportDoc['issuingCountryCode'] ?? 'N/A') }}</div>
                                        @else
                                            <span class="text-slate-300">No passport info</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(!empty($pax['loyalty_programs']))
                                            @foreach($pax['loyalty_programs'] as $lp)
                                                <div class="text-xs"><span class="bg-sky-100 text-sky-700 font-bold px-1.5 py-0.5 rounded">{{ $lp['supplier_code'] }}</span><span class="font-mono font-semibold ml-1">{{ $lp['program_number'] }}</span></div>
                                            @endforeach
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600 space-y-0.5">
                                        @if(!empty($pax['email']))<div><i class="fa fa-envelope text-slate-400 mr-1"></i>{{ $pax['email'] }}</div>@endif
                                        @if(!empty($pax['phone']))<div><i class="fa fa-phone text-slate-400 mr-1"></i>{{ $pax['phone'] }}</div>@endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── FLIGHT ITINERARY ── --}}
            @if(!empty($segments))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-flights', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fa fa-plane text-blue-600 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Flight Itinerary</span>
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($segments) }} segments</span>
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-flights" class="section-body p-4 space-y-3">
                        @foreach($segments as $idx => $seg)
                            @php
                                $origin=$seg['origin']??'N/A'; $dest=$seg['destination']??'N/A';
                                $depTime=$seg['departure_time']??null; $arrTime=$seg['arrival_time']??null;
                                $carrier=$seg['carrier']??''; $flightNum=$seg['flight_number']??'';
                                $airline=$seg['airline_name']??''; $cabin=$seg['cabin_class']??'Economy';
                                $cls=$seg['class_of_service']??''; $status=$seg['status_name']??'Confirmed';
                                $aircraft=$seg['aircraft_name']??''; $duration=$seg['travel_time']??0;
                                $seats=$seg['number_of_seats']??1;
                                $depTerminal=$seg['departure_terminal']??''; $arrTerminal=$seg['arrival_terminal']??'';
                                $meals=$seg['meals']??[]; $connection=$seg['connection']??null;
                                $group=$seg['group']??0;
                                $prevGroup=$idx>0?($segments[$idx-1]['group']??0):$group;
                                $newJourney=$idx>0&&$group!==$prevGroup;
                            @endphp
                            @if($newJourney)
                                <div class="flex items-center gap-3 py-2">
                                    <div class="flex-1 h-px bg-slate-200"></div>
                                    <span class="text-xs text-slate-400 font-semibold flex items-center gap-1.5"><i class="fa fa-exchange"></i> Return Journey</span>
                                    <div class="flex-1 h-px bg-slate-200"></div>
                                </div>
                            @endif
                            <div class="bg-slate-50 rounded-xl border border-slate-100 p-4">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="w-7 h-7 bg-navy-700 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $idx+1 }}</span>
                                    <div class="min-w-0">
                                        <div class="text-2xl font-black text-slate-800 tracking-wider leading-none">{{ $origin }}</div>
                                        @if($depTime)<div class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($depTime)->format('d M Y') }}</div><div class="text-sm font-bold text-slate-700 font-mono">{{ \Carbon\Carbon::parse($depTime)->format('H:i') }}</div>@endif
                                        @if($depTerminal)<div class="text-xs text-slate-400">{{ $depTerminal }}</div>@endif
                                    </div>
                                    <div class="flex-1 flex flex-col items-center min-w-0 px-2">
                                        <span class="font-black text-navy-700 text-sm tracking-wider">{{ $carrier }}{{ $flightNum }}</span>
                                        <div class="flex items-center gap-1 w-full my-1"><div class="flex-1 h-px bg-slate-300"></div><i class="fa fa-plane text-slate-400 text-xs"></i><div class="flex-1 h-px bg-slate-300"></div></div>
                                        @if($duration)<div class="text-xs text-slate-400 font-mono">{{ intdiv($duration,60) }}h {{ $duration%60 }}m</div>@endif
                                        <div class="flex gap-1 mt-1 flex-wrap justify-center">
                                            <span class="text-xs bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-medium">{{ $cabin }}</span>
                                            @if($cls)<span class="text-xs bg-navy-100 text-navy-700 px-1.5 py-0.5 rounded font-bold font-mono">{{ $cls }}</span>@endif
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-2xl font-black text-emerald-600 tracking-wider leading-none">{{ $dest }}</div>
                                        @if($arrTime)<div class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($arrTime)->format('d M Y') }}</div><div class="text-sm font-bold text-slate-700 font-mono">{{ \Carbon\Carbon::parse($arrTime)->format('H:i') }}</div>@endif
                                        @if($arrTerminal)<div class="text-xs text-slate-400">{{ $arrTerminal }}</div>@endif
                                    </div>
                                    <div class="text-xs text-slate-500 space-y-1 ml-auto min-w-0">
                                        @if($airline)<div><i class="fa fa-building-o"></i> {{ $airline }}</div>@endif
                                        @if($aircraft)<div><i class="fa fa-plane"></i> {{ $aircraft }}</div>@endif
                                        <div class="text-emerald-600 font-semibold"><i class="fa fa-check-circle"></i> {{ $status }}</div>
                                        <div><i class="fa fa-users"></i> {{ $seats }} seat(s)</div>
                                        @foreach($meals as $meal)<div class="text-amber-600"><i class="fa fa-cutlery"></i> {{ $meal['description']??$meal }}</div>@endforeach
                                    </div>
                                </div>
                                @if($connection)
                                    <div class="mt-3 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-xl">
                                        <i class="fa fa-clock-o"></i> Layover: {{ intdiv($connection['duration'],60) }}h {{ $connection['duration']%60 }}m
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── FARE BREAKDOWN ── --}}
            @if(!empty($fareBreakdowns))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-fare', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fa fa-money text-emerald-600 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Fare Breakdown</span>
                            @if(!empty($exchangeFares))
                                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-lg flex items-center gap-1"><i class="fa fa-exchange"></i> Exchanged</span>
                            @endif
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-fare" class="section-body p-4">

                        {{-- Active PQ Fares --}}
                        @if(!empty($activeFares))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @foreach($activeFares as $fare)
                                    @php
                                        $fCurr=$fare['currency']??'BDT'; $fSub=$fare['subtotal']??0;
                                        $fTaxes=$fare['taxes']??0; $fTotal=$fare['total']??0;
                                        $fType=$fare['traveler_type']??'ADT';
                                        $fCalc=$fare['fare_calculation']??'';
                                        $taxBreak=$fare['tax_breakdown']??[];
                                        $fareCons=$fare['fare_construction']??[];
                                        $fValidating=$fare['validating_carrier']??'';
                                        $fOrigTotal=$fare['original_total']??null; $fOrigCurr=$fare['original_currency']??null;
                                        $fTravelers=$fare['traveler_indices']??[];
                                        $fComm=$fare['commission']??[];
                                    @endphp
                                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                                        <div class="bg-slate-50 px-4 py-2.5 flex items-center justify-between border-b border-slate-100">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-700 text-sm">Pax: <span class="text-navy-700">{{ $fType }}</span></span>
                                                @if(!empty($fTravelers))<span class="text-xs text-slate-400">(#{{ implode(',', $fTravelers) }})</span>@endif
                                                @if(!empty($fComm['percentage']))<span class="text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded font-semibold">{{ $fComm['percentage'] }}% comm</span>@endif
                                            </div>
                                            <div class="flex gap-2">
                                                @if($fValidating)<span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded font-mono">{{ $fValidating }}</span>@endif
                                                @if($fOrigTotal&&$fOrigCurr)<span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded">{{ $fOrigTotal }} {{ $fOrigCurr }}</span>@endif
                                            </div>
                                        </div>
                                        <table class="w-full text-sm">
                                            <tr class="border-b border-slate-50"><td class="px-4 py-2 text-slate-600">Base Fare</td><td class="px-4 py-2 text-right font-bold text-slate-800">{{ number_format($fSub,2) }} {{ $fCurr }}</td></tr>
                                            @foreach($taxBreak as $tx)
                                                @php $txCode=$tx['code']??''; $txAmt=$tx['amount']??0; $txCurr=$tx['currency']??$fCurr; $isPaid=$tx['is_paid']??false; @endphp
                                                <tr class="border-b border-slate-50">
                                                    <td class="px-4 py-1.5 text-xs pl-7">
                                                        <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono text-xs">{{ $txCode }}</span>
                                                        @if($isPaid)<span class="text-emerald-500 text-xs ml-1" title="Paid">✓</span>@endif
                                                    </td>
                                                    <td class="px-4 py-1.5 text-right text-xs text-slate-500">{{ number_format($txAmt,2) }} {{ $txCurr }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="border-b border-slate-100"><td class="px-4 py-2 text-slate-600">Total Taxes</td><td class="px-4 py-2 text-right text-slate-700">{{ number_format($fTaxes,2) }} {{ $fCurr }}</td></tr>
                                            <tr class="bg-emerald-50"><td class="px-4 py-2.5 font-bold text-slate-700">Total</td><td class="px-4 py-2.5 text-right font-black text-emerald-700 text-base">{{ number_format($fTotal,2) }} {{ $fCurr }}</td></tr>
                                        </table>
                                        @if(!empty($fareCons))
                                            <div class="px-4 py-2 border-t border-slate-100 space-y-1">
                                                @foreach($fareCons as $fc)
                                                    @php $fcBasis=$fc['fare_basis']??''; $fcBrand=$fc['brand_fare_name']??''; $fcBag=$fc['checked_bag_kg']??0; @endphp
                                                    <div class="text-xs text-slate-500 flex items-center gap-2">
                                                        <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-mono">{{ $fcBasis }}</span>
                                                        @if($fcBrand)<span>{{ $fcBrand }}</span>@endif
                                                        @if($fcBag)<span class="text-slate-400"><i class="fa fa-suitcase mr-1"></i>{{ $fcBag }}kg</span>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($fCalc)<div class="fare-calc-bg">{{ $fCalc }}</div>@endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Exchange Fares (PQR) ── আলাদা section --}}
                        @if(!empty($exchangeFares))
                            <div class="border border-purple-200 rounded-xl overflow-hidden mb-4">
                                <div class="bg-purple-50 px-4 py-2.5 border-b border-purple-100 flex items-center gap-2">
                                    <i class="fa fa-exchange text-purple-500 text-xs"></i>
                                    <span class="font-bold text-purple-700 text-sm">Exchange / Refund Quotes</span>
                                    <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-lg">{{ count($exchangeFares) }} record(s)</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                                    @foreach($exchangeFares as $fare)
                                        @php
                                            $fCurr=$fare['currency']??'BDT'; $fSub=$fare['subtotal']??0;
                                            $fTotal=$fare['total']??0; $fType=$fare['traveler_type']??'ADT';
                                            $taxBreak=$fare['tax_breakdown']??[];
                                            $fTravelers=$fare['traveler_indices']??[];
                                            $recType=$fare['record_type_name']??'';
                                        @endphp
                                        <div class="border border-purple-100 rounded-xl overflow-hidden">
                                            <div class="bg-purple-50/50 px-3 py-2 border-b border-purple-100 flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-700 text-sm">Pax: <span class="text-purple-700">{{ $fType }}</span></span>
                                                    @if(!empty($fTravelers))<span class="text-xs text-slate-400">(#{{ implode(',', $fTravelers) }})</span>@endif
                                                </div>
                                                @if($recType)<span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded">{{ $recType }}</span>@endif
                                            </div>
                                            <table class="w-full text-sm">
                                                <tr class="border-b border-slate-50"><td class="px-3 py-1.5 text-slate-600 text-xs">Base Fare</td><td class="px-3 py-1.5 text-right font-bold text-slate-800 text-xs">{{ number_format($fSub,2) }} {{ $fCurr }}</td></tr>
                                                @foreach($taxBreak as $tx)
                                                    @php $txCode=$tx['code']??''; $txAmt=$tx['amount']??0; $txCurr=$tx['currency']??$fCurr; $isPaid=$tx['is_paid']??false; @endphp
                                                    <tr class="border-b border-slate-50">
                                                        <td class="px-3 py-1 text-xs pl-5">
                                                            <span class="bg-slate-100 text-slate-500 px-1 py-0.5 rounded font-mono text-xs">{{ $txCode }}</span>
                                                            @if($isPaid)<span class="text-emerald-500 text-xs ml-1">✓</span>@endif
                                                        </td>
                                                        <td class="px-3 py-1 text-right text-xs text-slate-500">{{ number_format($txAmt,2) }} {{ $txCurr }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-purple-50"><td class="px-3 py-2 font-bold text-slate-700 text-xs">Total</td><td class="px-3 py-2 text-right font-black text-purple-700">{{ number_format($fTotal,2) }} {{ $fCurr }}</td></tr>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Grand Total --}}
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between text-sm"><span class="text-slate-600 font-medium">Base Fare Total</span><span class="font-semibold text-slate-800">{{ number_format($grandTotal['subtotal'] ?? 0,2) }} {{ $currency }}</span></div>
                            <div class="flex justify-between text-sm"><span class="text-slate-600">Taxes & Fees</span><span class="text-slate-700">{{ number_format($grandTotal['taxes'] ?? 0,2) }} {{ $currency }}</span></div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                                <span class="font-black text-slate-800 uppercase tracking-wide text-sm">Grand Total</span>
                                <span class="font-black text-emerald-600 text-xl">{{ number_format($grandTotal['total'] ?? 0,2) }} <span class="text-sm font-bold">{{ $currency }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── BAGGAGE ── --}}
            @php
                $hasBaggage = $checkedBagKg || $cabinBagKg || !empty($baggageCharges) || !empty($perLegBaggage);
                // per_leg_baggage থেকে সব extra charges একত্র করো
                $allExtraCharges = collect($perLegBaggage)->flatMap(fn($leg) => $leg['extra_charges'] ?? [])->all();
                // যদি per_leg_baggage না থাকে তাহলে checked_baggage_charges ব্যবহার করো
                if (empty($allExtraCharges)) $allExtraCharges = $baggageCharges;
            @endphp
            @if($hasBaggage)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-baggage', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center"><i class="fa fa-suitcase text-slate-600 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Baggage Allowance</span>
                            @if(count($perLegBaggage) > 1)
                                <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($perLegBaggage) }} legs</span>
                            @endif
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-baggage" class="section-body p-4">

                        {{-- ── Summary tiles (fareOffers[0] থেকে) ── --}}
                        @if($cabinBagKg || $checkedBagKg)
                            <div class="flex gap-3 flex-wrap mb-4">
                                @if($cabinBagKg)
                                    <div class="flex items-center gap-3 bg-blue-50 border border-blue-100 rounded-xl px-5 py-3">
                                        <i class="fa fa-briefcase text-blue-500 text-xl"></i>
                                        <div>
                                            <div class="text-xs text-slate-400 mb-0.5">Cabin Baggage</div>
                                            <div class="font-black text-blue-700 text-lg">{{ $cabinBagKg }} KG</div>
                                        </div>
                                    </div>
                                @endif
                                @if($checkedBagKg)
                                    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-100 rounded-xl px-5 py-3">
                                        <i class="fa fa-suitcase text-emerald-500 text-xl"></i>
                                        <div>
                                            <div class="text-xs text-slate-400 mb-0.5">Checked Baggage</div>
                                            <div class="font-black text-emerald-700 text-lg">{{ $checkedBagKg }} KG</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ── Per-leg breakdown (একাধিক fareOffer থাকলে) ── --}}
                        @if(count($perLegBaggage) > 1)
                            <div class="mb-4">
                                <h6 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i class="fa fa-map-o text-slate-400"></i> Per Journey Leg
                                </h6>
                                <div class="space-y-3">
                                    @foreach($perLegBaggage as $legIdx => $leg)
                                        @php
                                            $legFlights  = $leg['flight_item_ids'] ?? [];
                                            $legCabin    = $leg['cabin_bag_kg']    ?? 0;
                                            $legChecked  = $leg['checked_bag_kg']  ?? 0;
                                            $legCabinPcs = $leg['cabin_bag_pieces']   ?? null;
                                            $legChkPcs   = $leg['checked_bag_pieces'] ?? null;

                                            // Flight item IDs থেকে segment info বের করো
                                            $legSegments = collect($segments)->filter(fn($s) =>
                                                in_array($s['item_id'] ?? '', $legFlights)
                                            )->values();

                                            $legFrom = $legSegments->first()['origin']      ?? '—';
                                            $legTo   = $legSegments->last()['destination']  ?? '—';

                                            // Extra charges এই leg এর জন্য
                                            $legCharges = $leg['extra_charges'] ?? [];
                                        @endphp
                                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                                            {{-- Leg header --}}
                                            <div class="bg-slate-50 px-4 py-2.5 flex items-center gap-3 border-b border-slate-100">
                                                <span class="w-6 h-6 bg-navy-700 text-white rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $legIdx + 1 }}</span>
                                                <span class="font-bold text-navy-700 text-sm tracking-wider">{{ $legFrom }} → {{ $legTo }}</span>
                                                @if(count($legFlights) > 1)
                                                    <span class="text-xs text-slate-400">{{ count($legFlights) }} flights</span>
                                                @endif
                                            </div>

                                            {{-- Leg baggage --}}
                                            <div class="p-3 flex gap-3 flex-wrap">
                                                @if($legCabin)
                                                    <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2">
                                                        <i class="fa fa-briefcase text-blue-400 text-sm"></i>
                                                        <div>
                                                            <div class="text-xs text-slate-400">Cabin</div>
                                                            <div class="font-bold text-blue-700 text-sm">{{ $legCabin }} KG
                                                                @if($legCabinPcs)<span class="font-normal text-xs text-slate-400">/ {{ $legCabinPcs }} pc</span>@endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($legChecked)
                                                    <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2">
                                                        <i class="fa fa-suitcase text-emerald-400 text-sm"></i>
                                                        <div>
                                                            <div class="text-xs text-slate-400">Checked</div>
                                                            <div class="font-bold text-emerald-700 text-sm">{{ $legChecked }} KG
                                                                @if($legChkPcs)<span class="font-normal text-xs text-slate-400">/ {{ $legChkPcs }} pc</span>@endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Extra charges এই leg এর জন্য --}}
                                            @if(!empty($legCharges))
                                                <div class="border-t border-slate-100 px-3 py-2 bg-amber-50/40">
                                                    <div class="text-xs font-bold text-amber-700 mb-2 flex items-center gap-1.5">
                                                        <i class="fa fa-plus-circle"></i> Extra Baggage Charges
                                                    </div>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($legCharges as $lc)
                                                            <div class="inline-flex items-center gap-2 bg-white border border-amber-200 rounded-lg px-3 py-1.5 text-xs">
                                        <span class="text-slate-600 font-mono">
                                            {{ $lc['max_weight_kg'] ? $lc['max_weight_kg'].'kg' : ($lc['max_weight_lbs'] ? $lc['max_weight_lbs'].'lbs' : '—') }}
                                            @if($lc['max_size_cm']) / {{ $lc['max_size_cm'] }}cm @endif
                                        </span>
                                                                <span class="text-slate-400">→</span>
                                                                <span class="font-bold text-amber-700">{{ number_format($lc['fee_amount'], 2) }} {{ $lc['fee_currency'] }}</span>
                                                                @if($lc['special_item'])<span class="text-slate-400 text-xs">{{ $lc['special_item'] }}</span>@endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── Extra / Paid Baggage Charges ── --}}
                        @if(!empty($baggageCharges))
                            <div>
                                <h6 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                    <i class="fa fa-plus-circle text-amber-500"></i> Additional Baggage Charges
                                </h6>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm border border-slate-200 rounded-xl overflow-hidden">
                                        <thead class="bg-slate-50">
                                        <tr>
                                            @foreach(['Weight','Size','Pieces','Fee','Note'] as $h)
                                                <th class="text-left px-3 py-2 text-xs font-bold text-slate-500">{{ $h }}</th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                        @foreach($baggageCharges as $bc)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-3 py-2 font-mono text-xs font-semibold text-slate-700">
                                                    {{ $bc['max_weight_kg'] ? $bc['max_weight_kg'].'kg' : ($bc['max_weight_lbs'] ? $bc['max_weight_lbs'].'lbs' : '—') }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-slate-600">
                                                    {{ $bc['max_size_cm'] ? $bc['max_size_cm'].'cm' : ($bc['max_size_inches'] ? $bc['max_size_inches'].'"' : '—') }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-slate-600">{{ $bc['pieces'] ?? 1 }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="font-bold text-amber-700 text-sm">{{ number_format($bc['fee_amount'], 2) }}</span>
                                                    <span class="text-xs text-slate-400 ml-0.5">{{ $bc['fee_currency'] }}</span>
                                                </td>
                                                <td class="px-3 py-2 text-xs text-slate-500">{{ $bc['special_item'] ?? '—' }}</td>
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

            {{-- ── FARE RULES ── --}}
            @if(!empty($fareRules))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-farerules', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center"><i class="fa fa-file-text text-red-500 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Fare Rules & Penalties</span>
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-farerules" class="section-body p-4 space-y-4">
                        @foreach($fareRules as $rule)
                            @php
                                $isRefundable=$rule['is_refundable']??false; $isChangeable=$rule['is_changeable']??false;
                                $paxCode=$rule['passenger_code']??'ADT';
                                $refundPens=$rule['refund_penalties']??[]; $exchPens=$rule['exchange_penalties']??[];
                                $orig=$rule['origin']??''; $dest2=$rule['destination']??'';
                            @endphp
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 px-4 py-3 flex flex-wrap items-center gap-2 border-b border-slate-100">
                                    <span class="font-bold text-slate-700">{{ $paxCode }}</span>
                                    @if($orig || $dest2)<span class="text-slate-500 text-sm">{{ $orig }} → {{ $dest2 }}</span>@endif
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl flex items-center gap-1.5 {{ $isRefundable ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        <i class="fa fa-{{ $isRefundable ? 'check' : 'times' }}"></i> {{ $isRefundable ? 'Refundable' : 'Non-Refundable' }}
                    </span>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl flex items-center gap-1.5 {{ $isChangeable ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">
                        <i class="fa fa-{{ $isChangeable ? 'exchange' : 'lock' }}"></i> {{ $isChangeable ? 'Changeable' : 'Non-Changeable' }}
                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 divide-x divide-slate-100">
                                    @if(!empty($refundPens))
                                        <div class="p-4">
                                            <h6 class="text-xs font-bold text-red-600 uppercase tracking-widest mb-3 flex items-center gap-1.5"><i class="fa fa-times-circle"></i> Cancellation Penalties</h6>
                                            <table class="w-full text-xs">
                                                <thead><tr class="border-b border-slate-100"><th class="text-left py-1.5 text-slate-400 font-semibold">When</th><th class="text-right py-1.5 text-slate-400 font-semibold">Penalty</th></tr></thead>
                                                <tbody class="divide-y divide-slate-50">
                                                @foreach($refundPens as $pen)
                                                    @php $app=$pen['applicability']??''; $amt=$pen['penalty_amount']??'0'; $cur=$pen['penalty_currency']??'BDT'; $nsAmt=$pen['no_show_amount']??null; @endphp
                                                    <tr><td class="py-2 text-slate-600">{{ str_replace('_',' ',$app) }}</td><td class="py-2 text-right"><span class="font-bold text-slate-800">{{ number_format($amt,2) }} {{ $cur }}</span>@if($nsAmt && $nsAmt != '0')<div class="text-slate-400">No-show: {{ number_format($nsAmt,2) }}</div>@endif</td></tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    @if(!empty($exchPens))
                                        <div class="p-4">
                                            <h6 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-3 flex items-center gap-1.5"><i class="fa fa-exchange"></i> Change Penalties</h6>
                                            <table class="w-full text-xs">
                                                <thead><tr class="border-b border-slate-100"><th class="text-left py-1.5 text-slate-400 font-semibold">When</th><th class="text-right py-1.5 text-slate-400 font-semibold">Penalty</th></tr></thead>
                                                <tbody class="divide-y divide-slate-50">
                                                @foreach($exchPens as $pen)
                                                    @php $app=$pen['applicability']??''; $amt=$pen['penalty_amount']??'0'; $cur=$pen['penalty_currency']??'BDT'; $nsAmt=$pen['no_show_amount']??null; @endphp
                                                    <tr><td class="py-2 text-slate-600">{{ str_replace('_',' ',$app) }}</td><td class="py-2 text-right"><span class="font-bold text-slate-800">{{ number_format($amt,2) }} {{ $cur }}</span>@if($nsAmt && $nsAmt != '0')<div class="text-slate-400">No-show: {{ number_format($nsAmt,2) }}</div>@endif</td></tr>
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

            {{-- ── TICKET DETAILS ── --}}
            @if(!empty($flightTickets))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100" onclick="toggleSection('sec-tickets', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-slate-800 rounded-lg flex items-center justify-center"><i class="fa fa-ticket text-white text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Ticket Details</span>
                            <span class="bg-slate-800 text-white text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($flightTickets) }}</span>
                        </div>
                        <i class="fa fa-chevron-up text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-tickets" class="section-body overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                @foreach(['Passenger','Ticket No.','Type','Issue Date','Status','Coupons','Amount','Commission'] as $h)
                                    <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $h }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                            @foreach($flightTickets as $tkt)
                                @php
                                    $tIdx    = $tkt['traveler_index'] ?? 0;
                                    $pax2    = $passengers[$tIdx-1]   ?? null;
                                    $pName   = $pax2 ? (($pax2['first_name']??'').' '.($pax2['last_name']??'')) : 'N/A';
                                    $tNum    = $tkt['number']          ?? 'N/A';
                                    $tDate   = $tkt['date']            ?? null;
                                    $tStat   = $tkt['ticket_status']   ?? null;
                                    $tCode   = $tkt['ticket_status_code'] ?? '';
                                    $tPay    = $tkt['payment'] ?? [];
                                    $tTotal  = $tPay['total']    ?? 0;
                                    $tCurr   = $tPay['currency'] ?? 'BDT';
                                    $coupons = $tkt['flight_coupons'] ?? [];
                                    $isExch        = $tkt['is_exchanged']       ?? false;
                                    $isPartialExch = $tkt['is_partial_exchange']?? false;
                                    $allExchanged  = $tkt['all_exchanged']      ?? false;

                                    // Commission: ticket থেকে অথবা accounting থেকে
                                    $commAmt = $tkt['commission_amount'] ?? null;
                                    if ($commAmt === null && !empty($accountingItems)) {
                                        $acct    = collect($accountingItems)->first(fn($a) => ($a['ticket_number'] ?? '') == $tNum);
                                        $commAmt = $acct['commission_amount'] ?? null;
                                    }

                                    $tStatusMap = ['TE'=>'bg-emerald-100 text-emerald-700','Issued'=>'bg-emerald-100 text-emerald-700','TV'=>'bg-red-100 text-red-700','Voided'=>'bg-red-100 text-red-700','TR'=>'bg-amber-100 text-amber-700','TX'=>'bg-sky-100 text-sky-700'];
                                    $cStatusMap = ['I'=>'bg-sky-100 text-sky-700','Not Flown'=>'bg-sky-100 text-sky-700','F'=>'bg-emerald-100 text-emerald-700','Flown'=>'bg-emerald-100 text-emerald-700','B'=>'bg-emerald-100 text-emerald-700','E'=>'bg-purple-100 text-purple-700','Exchanged'=>'bg-purple-100 text-purple-700','V'=>'bg-red-100 text-red-700','R'=>'bg-amber-100 text-amber-700'];
                                @endphp
                                <tr class="hover:bg-slate-50/60 {{ $allExchanged ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3"><span class="font-semibold text-slate-800">{{ $pName }}</span><br><span class="text-xs text-slate-400 font-mono">#{{ $tIdx }}</span></td>
                                    <td class="px-4 py-3 font-mono font-bold text-xs {{ $allExchanged ? 'text-slate-400 line-through' : 'text-navy-700' }}">{{ $tNum }}</td>
                                    <td class="px-4 py-3">
                                        @if($allExchanged)
                                            <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-lg font-semibold">Exchanged</span>
                                        @elseif($isPartialExch)
                                            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-lg font-semibold">Partial Exch.</span>
                                        @else
                                            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-lg font-semibold">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600">{{ $tDate ? \Carbon\Carbon::parse($tDate)->format('d M Y') : '—' }}</td>
                                    <td class="px-4 py-3">@if($tStat)<span class="text-xs font-semibold px-2 py-1 rounded-lg {{ $tStatusMap[$tCode] ?? $tStatusMap[$tStat] ?? 'bg-slate-100 text-slate-600' }}">{{ $tStat }}</span>@endif</td>
                                    <td class="px-4 py-3 space-y-1">
                                        @foreach($coupons as $cp)
                                            @php $cs=$cp['coupon_status']??''; $cc=$cp['coupon_status_code']??''; @endphp
                                            <div><span class="text-xs px-2 py-0.5 rounded-lg {{ $cStatusMap[$cc] ?? $cStatusMap[$cs] ?? 'bg-slate-100 text-slate-500' }}">{{ $cs }}</span></div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 font-bold text-slate-800">{{ number_format($tTotal,2) }} <span class="text-xs font-normal text-slate-400">{{ $tCurr }}</span></td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        @if($commAmt !== null && $commAmt != '0') {{ number_format($commAmt,2) }} {{ $tCurr }}
                                        @else <span class="text-slate-300">—</span> @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── SPECIAL SERVICES ── --}}
            @if(!empty($specialServices))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50" onclick="toggleSection('sec-ssr', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center"><i class="fa fa-list-alt text-amber-600 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Special Services (SSR)</span>
                            <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($specialServices) }}</span>
                        </div>
                        <i class="fa fa-chevron-down text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-ssr" class="section-body collapsed overflow-x-auto border-t border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50"><tr>
                                @foreach(['Code','Name','Message','Status'] as $h)<th class="text-left px-4 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $h }}</th>@endforeach
                            </tr></thead>
                            <tbody class="divide-y divide-slate-50">
                            @foreach($specialServices as $svc)
                                @php $sCode=$svc['code']??''; $sName=$svc['name']??''; $sMsg=$svc['message']??''; $sStat=$svc['status_name']??''; $sSCode=$svc['status_code']??''; $ssMap=['HK'=>'bg-emerald-100 text-emerald-700','Confirmed'=>'bg-emerald-100 text-emerald-700','HN'=>'bg-amber-100 text-amber-700','UN'=>'bg-red-100 text-red-700']; @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2.5"><span class="bg-slate-100 text-slate-600 text-xs font-mono px-2 py-1 rounded">{{ $sCode }}</span></td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">{{ $sName }}</td>
                                    <td class="px-4 py-2.5 text-xs font-mono text-slate-500">{{ $sMsg }}</td>
                                    <td class="px-4 py-2.5">@if($sStat)<span class="text-xs font-semibold px-2 py-1 rounded-lg {{ $ssMap[$sSCode] ?? $ssMap[$sStat] ?? 'bg-slate-100 text-slate-500' }}">{{ $sStat }}</span>@endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── REMARKS ── --}}
            @if(!empty($remarks))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50" onclick="toggleSection('sec-remarks', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center"><i class="fa fa-comment text-slate-500 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Remarks</span>
                            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($remarks) }}</span>
                        </div>
                        <i class="fa fa-chevron-down text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-remarks" class="section-body collapsed px-5 py-4 space-y-2 border-t border-slate-100">
                        @foreach($remarks as $rem)
                            <div class="flex items-start gap-2 py-1.5 border-b border-slate-50 last:border-0">
                                @if(!empty($rem['type']))<span class="bg-slate-100 text-slate-500 text-xs px-2 py-0.5 rounded font-medium flex-shrink-0">{{ $rem['type'] }}</span>@endif
                                <span class="text-xs font-mono text-slate-600">{{ $rem['text'] ?? $rem }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── ACCOUNTING ── --}}
            @if(!empty($accountingItems))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer hover:bg-slate-50" onclick="toggleSection('sec-accounting', this)">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center"><i class="fa fa-calculator text-slate-500 text-xs"></i></div>
                            <span class="font-bold text-slate-700 text-sm">Accounting</span>
                            <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-0.5 rounded-lg">{{ count($accountingItems) }}</span>
                        </div>
                        <i class="fa fa-chevron-down text-slate-400 text-xs section-icon"></i>
                    </div>
                    <div id="sec-accounting" class="section-body collapsed overflow-x-auto border-t border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                @foreach(['Passenger','Ticket No.','Type','Fare','Tax','Commission','Payment'] as $h)
                                    <th class="text-left px-4 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $h }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                            @php
                                // Passenger별로 group করো: original ticket + exchange ticket একসাথে
                                $acByPax = collect($accountingItems)->groupBy('traveler_index');
                            @endphp
                            @foreach($acByPax as $tIdx => $acItems)
                                @php
                                    $index  = max(0, ((int)$tIdx) - 1);
                                     $paxRow = $passengers[$index] ?? null;
                                    $paxName   = $paxRow ? (($paxRow['first_name']??'').' '.($paxRow['last_name']??'')) : ($acItems->first()['passenger_name'] ?? 'N/A');
                                    $paxCount  = $acItems->count();
                                @endphp
                                @foreach($acItems as $acIdx => $ac)
                                    @php
                                        $isExch   = $ac['is_exchange']         ?? false;
                                        $origRef  = $ac['original_ticket_ref'] ?? null;
                                        $tNum     = $ac['ticket_number']       ?? 'N/A';
                                        $fare     = $ac['fare_amount']         ?? 0;
                                        $tax      = $ac['tax_amount']          ?? 0;
                                        $total    = floatval($fare) + floatval($tax);
                                        $comm     = $ac['commission_amount']   ?? null;
                                        $payment  = $ac['form_of_payment']     ?? '';
                                        $cType    = $ac['creation_type']       ?? '';
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 {{ $isExch ? 'bg-purple-50/30' : '' }}">
                                        {{-- Passenger — শুধু প্রথম row এ দেখাও --}}
                                        <td class="px-4 py-3">
                                            @if($acIdx === 0)
                                                <div class="font-semibold text-slate-800 text-sm">{{ $paxName }}</div>
                                                <div class="text-xs text-slate-400 font-mono">#{{ $tIdx }}</div>
                                            @else
                                                <div class="text-xs text-slate-400 pl-2 border-l-2 border-slate-200">↓ same</div>
                                            @endif
                                        </td>

                                        {{-- Ticket No. --}}
                                        <td class="px-4 py-3">
                                            <div class="font-mono font-bold text-xs {{ $isExch ? 'text-purple-700' : 'text-navy-700' }}">
                                                {{ $tNum }}
                                            </div>
                                            @if($origRef)
                                                <div class="text-xs text-slate-400 mt-0.5 font-mono">
                                                    <span class="text-purple-500">↩</span> {{ $origRef }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Type --}}
                                        <td class="px-4 py-3">
                                            @if($isExch)
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-lg font-semibold">Exchange</span>
                                            @else
                                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-lg font-semibold">{{ $cType ?: 'Original' }}</span>
                                            @endif
                                        </td>

                                        {{-- Fare --}}
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-800 text-sm">{{ number_format($fare, 2) }}</div>
                                        </td>

                                        {{-- Tax --}}
                                        <td class="px-4 py-3 text-slate-600">{{ number_format($tax, 2) }}</td>

                                        {{-- Commission --}}
                                        <td class="px-4 py-3">
                                            @if($comm !== null && $comm != '0')
                                                <span class="text-emerald-700 font-semibold">{{ number_format($comm, 2) }}</span>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>

                                        {{-- Payment --}}
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $payment }}</td>
                                    </tr>
                                @endforeach

                                {{-- Per-passenger subtotal যদি একাধিক ticket থাকে --}}
                                @if($paxCount > 1)
                                    @php
                                        $paxTotalFare = $acItems->sum(fn($a) => floatval($a['fare_amount'] ?? 0));
                                        $paxTotalTax  = $acItems->sum(fn($a) => floatval($a['tax_amount']  ?? 0));
                                        $paxTotalComm = $acItems->sum(fn($a) => floatval($a['commission_amount'] ?? 0));
                                    @endphp
                                    <tr class="bg-navy-50/40 border-t-2 border-navy-100">
                                        <td class="px-4 py-2" colspan="2">
                                            <span class="text-xs font-bold text-navy-700 uppercase tracking-wide">{{ $paxName }} — Net Total</span>
                                        </td>
                                        <td class="px-4 py-2"></td>
                                        <td class="px-4 py-2 font-black text-navy-700">{{ number_format($paxTotalFare, 2) }}</td>
                                        <td class="px-4 py-2 font-bold text-slate-700">{{ number_format($paxTotalTax, 2) }}</td>
                                        <td class="px-4 py-2 font-bold text-emerald-700">{{ number_format($paxTotalComm, 2) }}</td>
                                        <td class="px-4 py-2"></td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── Actions ── --}}
            <div class="flex items-center justify-center gap-3 py-4">
                <a href="{{ route('report.admin.booking') }}"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm">
                    <i class="fa fa-arrow-left text-xs"></i> Back to List
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm">
                    <i class="fa fa-print text-xs"></i> Print
                </button>
            </div>

        @elseif(isset($bookingData) && !($bookingData['success'] ?? false))
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 py-16 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-lg mb-2">Error Retrieving Booking</h3>
                <p class="text-slate-400 text-sm">{{ $bookingData['error'] ?? 'Unknown error' }}</p>
            </div>

        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 py-20 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa fa-search text-slate-400 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-600 text-lg mb-2">No Booking Found</h3>
                <p class="text-slate-400 text-sm">Enter a PNR code above to view booking details</p>
            </div>
        @endif

    </div>
@endsection

@push('js')
    <script>
        function toggleSection(id, header) {
            const body = document.getElementById(id);
            const icon = header.querySelector('.section-icon');
            const isCollapsed = body.classList.contains('collapsed');
            body.classList.toggle('collapsed', !isCollapsed);
            if (icon) {
                icon.classList.toggle('fa-chevron-up', isCollapsed);
                icon.classList.toggle('fa-chevron-down', !isCollapsed);
            }
        }

        function toggleUserDropdown(e) {
            e.stopPropagation();
            const panel   = document.getElementById('user-dropdown-panel');
            const arrow   = document.getElementById('user-dd-arrow');
            const trigger = document.getElementById('user-select-trigger');
            if (!panel) return;
            const isOpen = panel.style.display === 'block';
            panel.style.display = isOpen ? 'none' : 'block';
            if (arrow)   arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
            if (trigger) trigger.style.borderColor = isOpen ? '#e2e8f0' : '#1a237e';
            if (!isOpen) setTimeout(() => document.getElementById('user-search-input')?.focus(), 50);
        }

        function selectUser(id, label) {
            const hiddenInput = document.getElementById('selected-user-id');
            const labelEl     = document.getElementById('user-select-label');
            const panel       = document.getElementById('user-dropdown-panel');
            const arrow       = document.getElementById('user-dd-arrow');
            const trigger     = document.getElementById('user-select-trigger');
            const searchInput = document.getElementById('user-search-input');
            if (hiddenInput) hiddenInput.value = id;
            if (labelEl)     labelEl.textContent = label;
            if (panel)       panel.style.display = 'none';
            if (arrow)       arrow.style.transform = '';
            if (trigger)     trigger.style.borderColor = id ? '#1a237e' : '#e2e8f0';
            if (searchInput) { searchInput.value = ''; filterUsers(''); }
            document.querySelectorAll('#user-options-list li').forEach(li => {
                li.style.background = '';
                if (li.dataset.value == id && id) li.style.background = '#f0f4ff';
            });
        }

        function filterUsers(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('#user-options-list li').forEach(li => {
                const label = (li.dataset.label || '').toLowerCase();
                li.style.display = (!q || label.includes(q)) ? '' : 'none';
            });
        }

        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('user-dropdown-wrap');
            if (wrap && !wrap.contains(e.target)) {
                const panel   = document.getElementById('user-dropdown-panel');
                const arrow   = document.getElementById('user-dd-arrow');
                const trigger = document.getElementById('user-select-trigger');
                if (panel)   panel.style.display = 'none';
                if (arrow)   arrow.style.transform = '';
                if (trigger) trigger.style.borderColor = '#e2e8f0';
            }
        });

        function assignTicketsToPassengers(isExistingBooking = false) {
            const selectedUserId = document.getElementById('selected-user-id')?.value;
            if (!isExistingBooking && !selectedUserId) {
                alert('{{ __("Please select a customer first!") }}');
                return;
            }
            const message = isExistingBooking
                ? '{{ __("Update ticket information for existing booking?") }}'
                : '{{ __("Create new booking and assign tickets?") }}';
            if (!confirm(message)) return;

            const bookingData       = @json($bookingData ?? []);
            const pnr               = '{{ $searchPnr ?? "" }}';
            const source            = '{{ $searchSource ?? request("source") ?? "sabre" }}';
            const existingBookingId = {{ isset($existingBooking) && $existingBooking ? $existingBooking->id : 'null' }};

            if (!bookingData || !pnr) { alert('{{ __("No booking data available") }}'); return; }

            const btn  = event.target.closest('button');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Processing...';

            fetch('{{ route("booking.assign.tickets") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ pnr, source, booking_data: bookingData, existing_booking_id: existingBookingId, user_id: selectedUserId })
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false; btn.innerHTML = orig;
                    if (data.success) {
                        alert(data.message || '{{ __("Done!") }}');
                        if (data.data?.booking_id && !isExistingBooking) {
                            window.location.href = '{{ url("/admin/module/booking") }}/' + data.data.booking_id;
                        } else { location.reload(); }
                    } else { alert(data.message || '{{ __("Failed") }}'); }
                })
                .catch(() => { btn.disabled = false; btn.innerHTML = orig; alert('{{ __("Error occurred") }}'); });
        }
    </script>
@endpush
