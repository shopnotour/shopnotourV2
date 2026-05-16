{{--@extends('Layout::empty')--}}

{{--@push('css')--}}
{{--    <!-- Tailwind CDN -->--}}
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}

{{--    <style>--}}
{{--        @media print {--}}
{{--            .no-print { display:none; }--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--<div id="invoice-print-zone" class="bg-white shadow-xl mx-auto my-10 p-8 rounded-xl border border-gray-300 max-w-4xl">--}}

{{--    <!-- TOP HEADER -->--}}
{{--    <div class="border-b pb-5">--}}

{{--        <!-- Top row: Logo + Title -->--}}
{{--        <div class="flex justify-between items-start">--}}
{{--            <div>--}}
{{--                @if(!empty($logo = setting_item('logo_invoice_id') ?? setting_item('logo_id')))--}}
{{--                    <img class="max-w-[160px]" src="{{ get_file_url($logo,'full') }}" alt="Logo">--}}
{{--                @endif--}}

{{--                <div class="mt-3 text-gray-600 text-sm leading-5">--}}
{{--                    {!! setting_item_with_lang("invoice_company_info") !!}--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- QR Code Centered -->--}}
{{--            <div class="flex justify-center mt-4">--}}
{{--                <img src="{{ $booking->qr_code ?? 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.$booking->code }}"--}}
{{--                     class="w-24 h-24 rounded-md border shadow-sm">--}}
{{--            </div>--}}

{{--            <div class="text-right">--}}
{{--                <h1 class="text-3xl font-bold text-red-700 uppercase">E-Ticket</h1>--}}

{{--                <p class="text-gray-600 text-sm">PNR:--}}
{{--                    <span class="font-semibold text-red-700">{{ $booking->code }}</span>--}}
{{--                </p>--}}

{{--                @if(!empty($booking->ticket_number))--}}
{{--                    <p class="text-gray-600 text-sm">--}}
{{--                        Ticket: <span class="font-semibold text-red-700">{{ $booking->ticket_number }}</span>--}}
{{--                    </p>--}}
{{--                @endif--}}

{{--                <p class="text-gray-600 text-sm">Issued:--}}
{{--                    <span class="font-semibold">{{ display_date($booking->created_at) }}</span>--}}
{{--                </p>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}


{{--    <!-- PASSENGER INFO -->--}}
{{--    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Passenger Information</h2>--}}

{{--    <table class="w-full mt-3 text-sm border border-gray-300">--}}
{{--        <thead>--}}
{{--        <tr class="bg-gray-100">--}}
{{--            <th class="p-2 border">Name</th>--}}
{{--            <th class="p-2 border">Gender</th>--}}
{{--            <th class="p-2 border">Type</th>--}}
{{--            <th class="p-2 border">Ticket #</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}

{{--        <tbody>--}}
{{--        @foreach($booking['passengers'] as $p)--}}
{{--            <tr class="hover:bg-gray-50">--}}
{{--                <td class="p-2 border">{{ $p->first_name }} {{ $p->last_name }}</td>--}}
{{--                <td class="p-2 border">{{ $p->gender }}</td>--}}
{{--                <td class="p-2 border">{{ $p->traveler_type }}</td>--}}
{{--                <td class="p-2 border">{{ $booking->ticket_number ?? '--' }}</td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}

{{--    <!-- FLIGHT INFORMATION CARD VIEW -->--}}
{{--    <h2 class="text-lg font-semibold text-red-700 mb-3 uppercase border-l-4 border-red-700 pl-2">--}}
{{--        Flight Information--}}
{{--    </h2>--}}

{{--    <div class="space-y-3">--}}

{{--        @foreach($booking['routes'] as $route)--}}
{{--            <div class="border rounded-lg p-4 bg-gray-50 shadow-sm">--}}

{{--                <div class="grid grid-cols-3 gap-3 text-sm">--}}

{{--                    <!-- Departure -->--}}
{{--                    <div>--}}
{{--                        <p class="text-gray-500 text-xs">DEPARTURE</p>--}}
{{--                        <p class="text-xl font-bold text-red-700">{{ $route->departure_iata_code }}</p>--}}
{{--                        <p class="text-gray-700">{{ airport_from_code($route->departure_iata_code) }}</p>--}}
{{--                        @if(!empty($route->departure_terminal))--}}
{{--                            <p class="text-gray-600 text-xs">Terminal:--}}
{{--                                <span class="font-semibold text-red-700">{{ $route->departure_terminal }}</span>--}}
{{--                            </p>--}}
{{--                        @endif--}}
{{--                        <p class="text-gray-600">{{ date('d M, h:i A', strtotime($route->departure_at)) }}</p>--}}
{{--                    </div>--}}

{{--                    <!-- Duration -->--}}
{{--                    <div class="flex items-center justify-center">--}}
{{--                        <div class="text-center">--}}
{{--                            <p class="text-gray-500 text-xs">DURATION</p>--}}

{{--                            <div class="flex items-center justify-center space-x-2 my-1">--}}
{{--                                <span class="block w-8 h-0.5 bg-gray-400"></span>--}}
{{--                                ✈️--}}
{{--                                <span class="block w-8 h-0.5 bg-gray-400"></span>--}}
{{--                            </div>--}}

{{--                            <p class="font-semibold text-red-700">{{ $route->duration }}</p>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <!-- Arrival -->--}}
{{--                    <div class="text-right">--}}
{{--                        <p class="text-gray-500 text-xs">ARRIVAL</p>--}}
{{--                        <p class="text-xl font-bold text-red-700">{{ $route->arrival_iata_code }}</p>--}}
{{--                        <p class="text-gray-700">{{ airport_from_code($route->arrival_iata_code) }}</p>--}}

{{--                        @if(!empty($route->arrival_terminal))--}}
{{--                            <p class="text-gray-600 text-xs">Terminal:--}}
{{--                                <span class="font-semibold text-red-700">{{ $route->arrival_terminal }}</span>--}}
{{--                            </p>--}}
{{--                        @endif--}}

{{--                        <p class="text-gray-600">{{ date('d M, h:i A', strtotime($route->arrival_at)) }}</p>--}}
{{--                    </div>--}}

{{--                </div>--}}

{{--            </div>--}}
{{--        @endforeach--}}

{{--    </div>--}}


{{--    <!-- FLIGHT TABLE VIEW -->--}}
{{--    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Flight Information</h2>--}}

{{--    <table class="w-full mt-3 text-sm border border-gray-300">--}}
{{--        <thead>--}}
{{--        <tr class="bg-gray-100">--}}
{{--            <th class="p-2 border">Flight</th>--}}
{{--            <th class="p-2 border">Terminal</th>--}}
{{--            <th class="p-2 border">Departure</th>--}}
{{--            <th class="p-2 border">Arrival</th>--}}
{{--            <th class="p-2 border">Time</th>--}}
{{--            <th class="p-2 border">Duration</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}

{{--        <tbody>--}}
{{--        @foreach($booking['routes'] as $r)--}}
{{--            <tr class="hover:bg-gray-50">--}}
{{--                <td class="p-2 border font-semibold text-red-700">{{ airline_from_code($booking->airline) }}</td>--}}
{{--                <td class="p-2 border text-red-700 font-semibold">T{{ $r->terminal ?? '—' }}</td>--}}
{{--                <td class="p-2 border">{{ airport_from_code($r->departure_iata_code) }}</td>--}}
{{--                <td class="p-2 border">{{ airport_from_code($r->arrival_iata_code) }}</td>--}}
{{--                <td class="p-2 border">--}}
{{--                    <div class="flex flex-col text-xs">--}}
{{--                        <span class="font-semibold text-green-700">Dep: {{ date('d M, h:i A', strtotime($r->departure_at)) }}</span>--}}
{{--                        <span class="font-semibold text-red-700">Arr: {{ date('d M, h:i A', strtotime($r->arrival_at)) }}</span>--}}
{{--                    </div>--}}
{{--                </td>--}}
{{--                <td class="p-2 border">--}}
{{--                    <div class="flex items-center gap-2">--}}
{{--                        <span class="text-red-700 font-semibold">{{ $r->duration }}</span>--}}
{{--                        ✈️--}}
{{--                    </div>--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}


{{--    <!-- FARE SUMMARY -->--}}
{{--    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Fare Summary</h2>--}}

{{--    <table class="w-full mt-3 text-sm border border-gray-300">--}}
{{--        <thead>--}}
{{--        <tr class="bg-gray-100">--}}
{{--            <th class="p-2 border">Passenger Type</th>--}}
{{--            <th class="p-2 border">Fare</th>--}}
{{--            <th class="p-2 border">Tax</th>--}}
{{--            <th class="p-2 border">Total</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}

{{--        <tbody>--}}
{{--        @foreach($booking['passengers'] as $p)--}}
{{--            <tr class="hover:bg-gray-50">--}}
{{--                <td class="p-2 border">{{ $p->traveler_type }}</td>--}}
{{--                <td class="p-2 border">{{ $p->base }} {{ $p->currency }}</td>--}}
{{--                <td class="p-2 border">{{ $p->total - $p->base }} {{ $p->currency }}</td>--}}
{{--                <td class="p-2 border font-semibold text-red-700">{{ $p->total }} {{ $p->currency }}</td>--}}
{{--            </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--    </table>--}}

{{--    <!-- TERMS -->--}}
{{--    <h2 class="text-lg font-bold text-red-700 mt-6 border-l-4 border-red-700 pl-3">Terms & Conditions</h2>--}}

{{--    <ul class="text-sm text-gray-700 mt-3 leading-6">--}}
{{--        <li>• This ticket is issued as per airline rules and regulations.</li>--}}
{{--        <li>• Refund & date change charges apply based on the airline fare class.</li>--}}
{{--        <li>• Passenger must carry valid passport, visa, and travel documents.</li>--}}
{{--        <li>• Airline may change flight schedule without prior notice.</li>--}}
{{--        <li>• Baggage allowance is based on the specific airline policy.</li>--}}
{{--        <li>• Name correction is limited and may not be allowed by some airlines.</li>--}}
{{--        <li>• No-show penalties apply if passenger fails to report on time.</li>--}}
{{--    </ul>--}}

{{--</div>--}}


{{--@extends('Layout::empty')--}}

{{--@push('css')--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">--}}
{{--    <style>--}}
{{--        * { box-sizing: border-box; margin: 0; padding: 0; }--}}

{{--        :root {--}}
{{--            --ink:     #0f1923;--}}
{{--            --red:     #c0392b;--}}
{{--            --red-dk:  #96281b;--}}
{{--            --silver:  #f0f2f4;--}}
{{--            --mid:     #7f8c8d;--}}
{{--            --border:  #dce1e6;--}}
{{--            --white:   #ffffff;--}}
{{--        }--}}

{{--        body {--}}
{{--            font-family: 'Libre Baskerville', Georgia, serif;--}}
{{--            background: #e8eaed;--}}
{{--            color: var(--ink);--}}
{{--            font-size: 13px;--}}
{{--            line-height: 1.5;--}}
{{--        }--}}

{{--        /* ── PRINT BAR ─────────────────────────────────────────────── */--}}
{{--        .print-bar {--}}
{{--            background: var(--ink);--}}
{{--            color: #fff;--}}
{{--            padding: 12px 24px;--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: center;--}}
{{--            position: sticky;--}}
{{--            top: 0;--}}
{{--            z-index: 100;--}}
{{--        }--}}
{{--        .print-bar h2 { font-size: 14px; font-weight: 600; font-family: 'IBM Plex Mono', monospace; letter-spacing: .04em; }--}}
{{--        .btn-print {--}}
{{--            background: var(--red);--}}
{{--            color: #fff;--}}
{{--            border: none;--}}
{{--            padding: 8px 20px;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 12px;--}}
{{--            letter-spacing: .08em;--}}
{{--            cursor: pointer;--}}
{{--            border-radius: 2px;--}}
{{--            text-transform: uppercase;--}}
{{--        }--}}
{{--        .btn-print:hover { background: var(--red-dk); }--}}

{{--        /* ── TICKET WRAPPER ─────────────────────────────────────────── */--}}
{{--        .ticket-stack {--}}
{{--            max-width: 820px;--}}
{{--            margin: 28px auto 60px;--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--            gap: 32px;--}}
{{--            padding: 0 16px;--}}
{{--        }--}}

{{--        /* ── SINGLE TICKET ──────────────────────────────────────────── */--}}
{{--        .ticket {--}}
{{--            background: var(--white);--}}
{{--            border-radius: 4px;--}}
{{--            overflow: hidden;--}}
{{--            box-shadow: 0 2px 16px rgba(0,0,0,.12);--}}
{{--            page-break-inside: avoid;--}}
{{--            break-inside: avoid;--}}
{{--        }--}}

{{--        /* ── TICKET HEADER ──────────────────────────────────────────── */--}}
{{--        .ticket-header {--}}
{{--            background: var(--ink);--}}
{{--            color: #fff;--}}
{{--            padding: 18px 24px;--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: center;--}}
{{--        }--}}
{{--        .ticket-header .airline-block { display: flex; align-items: center; gap: 14px; }--}}
{{--        .airline-logo {--}}
{{--            width: 48px; height: 48px;--}}
{{--            background: var(--red);--}}
{{--            border-radius: 50%;--}}
{{--            display: flex; align-items: center; justify-content: center;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 15px; font-weight: 600; color: #fff;--}}
{{--            flex-shrink: 0;--}}
{{--        }--}}
{{--        .airline-name { font-size: 15px; font-weight: 700; letter-spacing: .03em; }--}}
{{--        .airline-flight { font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: rgba(255,255,255,.65); margin-top: 2px; }--}}

{{--        .ticket-header .pnr-block { text-align: right; }--}}
{{--        .pnr-label { font-size: 10px; text-transform: uppercase; letter-spacing: .12em; color: rgba(255,255,255,.55); }--}}
{{--        .pnr-value { font-family: 'IBM Plex Mono', monospace; font-size: 20px; font-weight: 600; letter-spacing: .08em; color: var(--white); }--}}
{{--        .airline-pnr { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: rgba(255,255,255,.6); margin-top: 2px; }--}}

{{--        /* ── ROUTE BAND ─────────────────────────────────────────────── */--}}
{{--        .route-band {--}}
{{--            background: var(--red);--}}
{{--            padding: 20px 28px;--}}
{{--            display: grid;--}}
{{--            grid-template-columns: 1fr auto 1fr;--}}
{{--            align-items: center;--}}
{{--            gap: 12px;--}}
{{--            color: #fff;--}}
{{--        }--}}
{{--        .airport { }--}}
{{--        .airport.right { text-align: right; }--}}
{{--        .iata {--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 42px;--}}
{{--            font-weight: 600;--}}
{{--            line-height: 1;--}}
{{--            letter-spacing: .04em;--}}
{{--        }--}}
{{--        .airport-city { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; opacity: .8; margin-top: 4px; }--}}
{{--        .airport-time { font-size: 16px; font-weight: 700; margin-top: 6px; }--}}
{{--        .airport-date { font-size: 11px; opacity: .75; }--}}
{{--        .airport-terminal { font-size: 10px; opacity: .65; margin-top: 2px; }--}}

{{--        .flight-arrow {--}}
{{--            text-align: center;--}}
{{--            display: flex;--}}
{{--            flex-direction: column;--}}
{{--            align-items: center;--}}
{{--            gap: 6px;--}}
{{--        }--}}
{{--        .arrow-line {--}}
{{--            width: 100%;--}}
{{--            max-width: 120px;--}}
{{--            display: flex;--}}
{{--            align-items: center;--}}
{{--            gap: 0;--}}
{{--        }--}}
{{--        .arrow-dash { flex: 1; height: 1px; background: rgba(255,255,255,.5); }--}}
{{--        .arrow-plane { font-size: 18px; }--}}
{{--        .duration-pill {--}}
{{--            background: rgba(255,255,255,.2);--}}
{{--            padding: 2px 10px;--}}
{{--            border-radius: 20px;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 11px;--}}
{{--            white-space: nowrap;--}}
{{--        }--}}
{{--        .cabin-pill {--}}
{{--            background: rgba(0,0,0,.2);--}}
{{--            padding: 2px 10px;--}}
{{--            border-radius: 20px;--}}
{{--            font-size: 10px;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: .06em;--}}
{{--            white-space: nowrap;--}}
{{--        }--}}

{{--        /* ── INFO GRID ──────────────────────────────────────────────── */--}}
{{--        .info-grid {--}}
{{--            display: grid;--}}
{{--            grid-template-columns: repeat(4, 1fr);--}}
{{--            border-bottom: 1px solid var(--border);--}}
{{--        }--}}
{{--        .info-cell {--}}
{{--            padding: 14px 20px;--}}
{{--            border-right: 1px solid var(--border);--}}
{{--        }--}}
{{--        .info-cell:last-child { border-right: none; }--}}
{{--        .info-cell.span2 { grid-column: span 2; }--}}
{{--        .cell-label {--}}
{{--            font-size: 9px;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: .12em;--}}
{{--            color: var(--mid);--}}
{{--            margin-bottom: 4px;--}}
{{--        }--}}
{{--        .cell-value {--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 13px;--}}
{{--            font-weight: 600;--}}
{{--            color: var(--ink);--}}
{{--        }--}}
{{--        .cell-value.red { color: var(--red); }--}}
{{--        .cell-sub { font-size: 11px; color: var(--mid); margin-top: 2px; }--}}

{{--        /* ── PASSENGER ROW ──────────────────────────────────────────── */--}}
{{--        .passenger-section {--}}
{{--            background: var(--silver);--}}
{{--            padding: 14px 20px;--}}
{{--            border-bottom: 1px solid var(--border);--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: center;--}}
{{--            flex-wrap: wrap;--}}
{{--            gap: 8px;--}}
{{--        }--}}
{{--        .pax-name {--}}
{{--            font-size: 16px;--}}
{{--            font-weight: 700;--}}
{{--            letter-spacing: .03em;--}}
{{--            text-transform: uppercase;--}}
{{--        }--}}
{{--        .pax-badges { display: flex; gap: 6px; flex-wrap: wrap; }--}}
{{--        .badge {--}}
{{--            padding: 3px 10px;--}}
{{--            border-radius: 2px;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 10px;--}}
{{--            font-weight: 600;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: .06em;--}}
{{--        }--}}
{{--        .badge-adult   { background: #27ae60; color: #fff; }--}}
{{--        .badge-child   { background: #e67e22; color: #fff; }--}}
{{--        .badge-infant  { background: #3498db; color: #fff; }--}}
{{--        .badge-ticket  { background: var(--ink); color: #fff; }--}}
{{--        .badge-issued  { background: #27ae60; color: #fff; }--}}
{{--        .badge-voided  { background: var(--red); color: #fff; }--}}
{{--        .badge-pending { background: #e67e22; color: #fff; }--}}

{{--        /* ── PASSPORT ROW ───────────────────────────────────────────── */--}}
{{--        .passport-row {--}}
{{--            display: grid;--}}
{{--            grid-template-columns: repeat(5, 1fr);--}}
{{--            border-bottom: 1px solid var(--border);--}}
{{--        }--}}
{{--        .passport-row .info-cell { border-bottom: none; }--}}

{{--        /* ── COUPON TABLE ───────────────────────────────────────────── */--}}
{{--        .coupon-table {--}}
{{--            width: 100%;--}}
{{--            border-collapse: collapse;--}}
{{--        }--}}
{{--        .coupon-table thead tr {--}}
{{--            background: var(--ink);--}}
{{--            color: #fff;--}}
{{--        }--}}
{{--        .coupon-table th {--}}
{{--            padding: 8px 14px;--}}
{{--            text-align: left;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 10px;--}}
{{--            letter-spacing: .08em;--}}
{{--            text-transform: uppercase;--}}
{{--            font-weight: 600;--}}
{{--        }--}}
{{--        .coupon-table td {--}}
{{--            padding: 10px 14px;--}}
{{--            border-bottom: 1px solid var(--border);--}}
{{--            font-size: 12px;--}}
{{--        }--}}
{{--        .coupon-table tr:last-child td { border-bottom: none; }--}}
{{--        .coupon-table tr:nth-child(even) td { background: #fafbfc; }--}}
{{--        .coupon-num {--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--            font-size: 11px;--}}
{{--            color: var(--mid);--}}
{{--        }--}}
{{--        .status-dot {--}}
{{--            display: inline-block;--}}
{{--            width: 7px; height: 7px;--}}
{{--            border-radius: 50%;--}}
{{--            margin-right: 5px;--}}
{{--            vertical-align: middle;--}}
{{--        }--}}
{{--        .dot-green  { background: #27ae60; }--}}
{{--        .dot-blue   { background: #3498db; }--}}
{{--        .dot-red    { background: var(--red); }--}}
{{--        .dot-orange { background: #e67e22; }--}}

{{--        /* ── FARE ROW ───────────────────────────────────────────────── */--}}
{{--        .fare-row {--}}
{{--            display: grid;--}}
{{--            grid-template-columns: repeat(4, 1fr);--}}
{{--            border-top: 2px solid var(--border);--}}
{{--        }--}}
{{--        .fare-row .info-cell { border-top: none; }--}}
{{--        .fare-total {--}}
{{--            background: var(--ink);--}}
{{--            color: #fff;--}}
{{--            padding: 14px 20px;--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: center;--}}
{{--        }--}}
{{--        .fare-total-label { font-size: 12px; text-transform: uppercase; letter-spacing: .1em; opacity: .7; }--}}
{{--        .fare-total-value { font-family: 'IBM Plex Mono', monospace; font-size: 18px; font-weight: 600; }--}}

{{--        /* ── TICKET FOOTER ──────────────────────────────────────────── */--}}
{{--        .ticket-footer {--}}
{{--            background: var(--silver);--}}
{{--            padding: 12px 20px;--}}
{{--            display: flex;--}}
{{--            justify-content: space-between;--}}
{{--            align-items: flex-start;--}}
{{--            flex-wrap: wrap;--}}
{{--            gap: 12px;--}}
{{--            border-top: 1px solid var(--border);--}}
{{--        }--}}
{{--        .conditions { flex: 1; min-width: 220px; }--}}
{{--        .conditions h4 { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: var(--mid); margin-bottom: 6px; }--}}
{{--        .conditions ul { list-style: none; }--}}
{{--        .conditions li { font-size: 10px; color: #555; line-height: 1.7; padding-left: 12px; position: relative; }--}}
{{--        .conditions li::before { content: '•'; position: absolute; left: 0; color: var(--red); }--}}

{{--        .qr-block { text-align: center; }--}}
{{--        .qr-block img { width: 64px; height: 64px; border: 1px solid var(--border); }--}}
{{--        .qr-block p { font-size: 9px; color: var(--mid); margin-top: 4px; font-family: 'IBM Plex Mono', monospace; }--}}

{{--        /* ── SEGMENT SEPARATOR ──────────────────────────────────────── */--}}
{{--        .segment-sep {--}}
{{--            background: repeating-linear-gradient(90deg, var(--border) 0, var(--border) 6px, transparent 6px, transparent 14px);--}}
{{--            height: 1px;--}}
{{--            margin: 0;--}}
{{--        }--}}
{{--        .segment-label {--}}
{{--            background: #fff8f0;--}}
{{--            border-top: 1px solid #f0d9c0;--}}
{{--            border-bottom: 1px solid #f0d9c0;--}}
{{--            padding: 6px 20px;--}}
{{--            font-size: 10px;--}}
{{--            text-transform: uppercase;--}}
{{--            letter-spacing: .1em;--}}
{{--            color: #b05010;--}}
{{--            font-family: 'IBM Plex Mono', monospace;--}}
{{--        }--}}

{{--        /* ── PRINT ──────────────────────────────────────────────────── */--}}
{{--        @media print {--}}
{{--            /* ✅ Force background colors & images to print */--}}
{{--            * {--}}
{{--                -webkit-print-color-adjust: exact !important;--}}
{{--                print-color-adjust: exact !important;--}}
{{--                color-adjust: exact !important;--}}
{{--            }--}}

{{--            @page {--}}
{{--                size: A4 portrait;--}}
{{--                margin: 8mm;--}}
{{--            }--}}

{{--            html, body {--}}
{{--                width: 100% !important;--}}
{{--                margin: 0 !important;--}}
{{--                padding: 0 !important;--}}
{{--                background: #fff !important;--}}
{{--            }--}}

{{--            .print-bar { display: none !important; }--}}

{{--            /* body কে center করার জন্য wrapper */--}}
{{--            .ticket-stack {--}}
{{--                width: 100% !important;--}}
{{--                margin: 0 auto !important;--}}
{{--                padding: 0 !important;--}}
{{--                gap: 0 !important;--}}
{{--                max-width: 100% !important;--}}
{{--                display: block !important;--}}
{{--            }--}}

{{--            /* প্রতিটা ticket-page = 1 A4 page */--}}
{{--            .ticket-page {--}}
{{--                width: 100% !important;--}}
{{--                height: calc(297mm - 16mm) !important; /* 297 - top+bottom 8mm margin */--}}
{{--                overflow: hidden !important;--}}
{{--                position: relative !important;--}}
{{--                page-break-after: always !important;--}}
{{--                break-after: page !important;--}}
{{--                display: flex !important;--}}
{{--                align-items: flex-start !important;--}}
{{--                justify-content: center !important;  /* ← center করবে */--}}
{{--            }--}}
{{--            .ticket-page:last-child {--}}
{{--                page-break-after: avoid !important;--}}
{{--                break-after: avoid !important;--}}
{{--            }--}}

{{--            /* .ticket — JS scale করবে, center এ থাকবে */--}}
{{--            .ticket {--}}
{{--                box-shadow: none !important;--}}
{{--                border-radius: 0 !important;--}}
{{--                transform-origin: top center !important;--}}
{{--                /* width & transform set by JS at print time */--}}
{{--            }--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--@php--}}
{{--    // ── Parse pnr_raw_data ──────────────────────────────────────────--}}
{{--    $raw = $booking->pnr_raw_data;--}}
{{--    $pnr = is_string($raw) ? json_decode($raw, true) : (array)$raw;--}}

{{--    $passengers    = $pnr['passengers']     ?? [];--}}
{{--    $segments      = $pnr['segments']       ?? [];--}}
{{--    $flightTickets = $pnr['flight_tickets'] ?? [];--}}
{{--    $pricing       = $pnr['pricing']        ?? [];--}}
{{--    $grandTotal    = $pricing['grand_total']    ?? [];--}}
{{--    $fareBreakdowns= $pricing['fare_breakdowns']?? [];--}}
{{--    $fareRules     = $pnr['fare_rules']     ?? [];--}}
{{--    $supplier      = $pnr['supplier_locator']   ?? [];--}}
{{--    $provRes       = $pnr['provider_reservation']?? [];--}}
{{--    $actionStatus  = $pnr['action_status']  ?? [];--}}
{{--    $agencyInfo    = $pnr['agency_info']    ?? [];--}}
{{--    $contactInfo   = $pnr['contact_info']   ?? [];--}}

{{--    $bookingId     = $pnr['booking_id']     ?? $booking->pnr_id ?? 'N/A';--}}
{{--    $airlinePnr    = $supplier['locator_code']  ?? null;--}}
{{--    $isTicketed    = $pnr['is_ticketed']    ?? false;--}}
{{--    $isRefundable  = $pnr['is_cancelable']  ?? false;--}}
{{--    $currency      = $grandTotal['currency']?? 'BDT';--}}
{{--    $totalAmount   = $grandTotal['total']   ?? 0;--}}
{{--    $baseAmount    = $grandTotal['subtotal']?? 0;--}}
{{--    $taxAmount     = $grandTotal['taxes']   ?? 0;--}}
{{--    $ticketDeadline= $actionStatus['ticket_date'] ?? null;--}}

{{--    // Fare rules quick map--}}
{{--    $fareRuleMap = [];--}}
{{--    foreach ($fareRules as $fr) {--}}
{{--        $fareRuleMap[$fr['passenger_code'] ?? 'ADT'] = $fr;--}}
{{--    }--}}

{{--    // Coupon map: item_id → coupon data (per ticket)--}}
{{--    // Build segment map by item_id--}}
{{--    $segmentMap = [];--}}
{{--    foreach ($segments as $seg) {--}}
{{--        $segmentMap[$seg['item_id'] ?? ''] = $seg;--}}
{{--    }--}}

{{--    // Helper: format duration--}}
{{--    $fmtDur = fn($min) => intdiv((int)$min, 60).'h '.((int)$min % 60).'m';--}}

{{--    // Helper: badge class by type--}}
{{--    $typeBadge = ['ADT' => 'badge-adult', 'CNN' => 'badge-child', 'INF' => 'badge-infant'];--}}
{{--    $typeLabel  = ['ADT' => 'Adult', 'CNN' => 'Child', 'INF' => 'Infant'];--}}

{{--    // Status dot--}}
{{--    $couponDot = ['I' => 'dot-blue', 'Not Flown' => 'dot-blue', 'F' => 'dot-green', 'Flown' => 'dot-green', 'V' => 'dot-red', 'Voided' => 'dot-red', 'R' => 'dot-orange', 'Refunded' => 'dot-orange'];--}}
{{--    $ticketDot = ['TE' => 'dot-green', 'Issued' => 'dot-green', 'TV' => 'dot-red', 'Voided' => 'dot-red', 'TR' => 'dot-orange', 'Refunded' => 'dot-orange', 'TX' => 'dot-blue'];--}}

{{--    // Per-passenger fare (from fare_breakdowns or divide grand total)--}}
{{--    $paxFareMap = [];--}}
{{--    foreach ($fareBreakdowns as $fb) {--}}
{{--        $type = $fb['traveler_type'] ?? 'ADT';--}}
{{--        $paxFareMap[$type] = [--}}
{{--            'base'  => $fb['subtotal'] ?? 0,--}}
{{--            'taxes' => $fb['taxes']    ?? 0,--}}
{{--            'total' => $fb['total']    ?? 0,--}}
{{--        ];--}}
{{--    }--}}

{{--    // Company info--}}
{{--    $companyName = setting_item('company_name') ?? setting_item('site_name') ?? 'Travel Agency';--}}
{{--    $logoId      = setting_item('logo_invoice_id') ?? setting_item('logo_id');--}}
{{--@endphp--}}

{{-- ── PRINT BAR ───────────────────────────────────────────────── --}}
{{--<div class="print-bar no-print">--}}
{{--    <h2>✈ E-TICKET &mdash; {{ $bookingId }} &mdash; {{ count($passengers) }} PASSENGER(S)</h2>--}}
{{--    <button class="btn-print" onclick="window.print()">⎙ Print All Tickets</button>--}}
{{--</div>--}}

{{-- ── TICKET STACK ────────────────────────────────────────────── --}}
{{--<div class="ticket-stack">--}}

{{--    @foreach($passengers as $paxIdx => $pax)--}}
{{--        @php--}}
{{--            $travelerIndex = $paxIdx + 1;--}}

{{--            // Find this passenger's ticket--}}
{{--            $ticket = collect($flightTickets)->first(fn($t) =>--}}
{{--                ($t['traveler_index'] ?? null) == $travelerIndex--}}
{{--            );--}}

{{--            $ticketNum    = $ticket['number']            ?? null;--}}
{{--            $ticketDate   = $ticket['date']              ?? null;--}}
{{--            $ticketStatus = $ticket['ticket_status']     ?? null;--}}
{{--            $ticketCode   = $ticket['ticket_status_code']?? '';--}}
{{--            $coupons      = $ticket['flight_coupons']    ?? [];--}}
{{--            $tktPayment   = $ticket['payment']           ?? [];--}}

{{--            // Build coupon → segment map--}}
{{--            $couponSegMap = [];--}}
{{--            foreach ($coupons as $cp) {--}}
{{--                $couponSegMap[$cp['item_id'] ?? ''] = $cp;--}}
{{--            }--}}

{{--            // Passenger type--}}
{{--            $paxType     = $pax['traveler_type'] ?? 'ADT';--}}
{{--            $paxBadge    = $typeBadge[$paxType]  ?? 'badge-adult';--}}
{{--            $paxTypeName = $typeLabel[$paxType]  ?? 'Adult';--}}

{{--            // Fare for this pax type--}}
{{--            $paxFare = $paxFareMap[$paxType] ?? [--}}
{{--                'base'  => $baseAmount,--}}
{{--                'taxes' => $taxAmount,--}}
{{--                'total' => $totalAmount,--}}
{{--            ];--}}

{{--            // Fare rules--}}
{{--            $rule         = $fareRuleMap[$paxType] ?? [];--}}
{{--            $isRefPax     = $rule['is_refundable']  ?? $isRefundable;--}}
{{--            $isChgPax     = $rule['is_changeable']  ?? false;--}}

{{--            // First segment carrier--}}
{{--            $firstSeg     = $segments[0] ?? [];--}}
{{--            $carrier      = $firstSeg['carrier']      ?? '';--}}
{{--            $airlineName  = $firstSeg['airline_name'] ?? '';--}}

{{--            // Ticket status badge--}}
{{--            $tktBadgeClass = 'badge-pending';--}}
{{--            if (in_array($ticketCode, ['TE']) || $ticketStatus === 'Issued') $tktBadgeClass = 'badge-issued';--}}
{{--            if (in_array($ticketCode, ['TV']) || $ticketStatus === 'Voided') $tktBadgeClass = 'badge-voided';--}}
{{--        @endphp--}}

{{--        <div class="ticket">--}}

{{--            --}}{{-- ── HEADER ──────────────────────────────────────────────────── --}}
{{--            <div class="ticket-header">--}}
{{--                <div class="airline-block">--}}
{{--                    <div class="airline-logo">{{ $carrier }}</div>--}}
{{--                    <div>--}}
{{--                        <div class="airline-name">{{ $airlineName ?: $carrier }}</div>--}}
{{--                        <div class="airline-flight">--}}
{{--                            @foreach($segments as $si => $seg)--}}
{{--                                {{ $seg['carrier'] }}{{ $seg['flight_number'] }}@if(!$loop->last) · @endif--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="pnr-block">--}}
{{--                    <div class="pnr-label">Booking Reference</div>--}}
{{--                    <div class="pnr-value">{{ $bookingId }}</div>--}}
{{--                    @if($airlinePnr && $airlinePnr !== $bookingId)--}}
{{--                        <div class="airline-pnr">Airline PNR: {{ $airlinePnr }}</div>--}}
{{--                    @endif--}}
{{--                    @if($ticketNum)--}}
{{--                        <div class="airline-pnr">Ticket: {{ $ticketNum }}</div>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- ── PASSENGER ───────────────────────────────────────────────── --}}
{{--            <div class="passenger-section">--}}
{{--                <div>--}}
{{--                    <div style="font-size:9px;text-transform:uppercase;letter-spacing:.1em;color:var(--mid);margin-bottom:3px;">Passenger {{ $travelerIndex }}</div>--}}
{{--                    <div class="pax-name">{{ $pax['last_name'] ?? '' }} / {{ $pax['first_name'] ?? '' }}</div>--}}
{{--                </div>--}}
{{--                <div class="pax-badges">--}}
{{--                    <span class="badge {{ $paxBadge }}">{{ $paxTypeName }}</span>--}}
{{--                    @if($ticketNum)--}}
{{--                        <span class="badge badge-ticket">{{ $ticketNum }}</span>--}}
{{--                    @endif--}}
{{--                    @if($ticketStatus)--}}
{{--                        <span class="badge {{ $tktBadgeClass }}">{{ $ticketStatus }}</span>--}}
{{--                    @endif--}}
{{--                    @if($isTicketed)--}}
{{--                        <span class="badge badge-issued">E-Ticket</span>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- ── PASSPORT / DOC INFO ─────────────────────────────────────── --}}
{{--            <div class="passport-row">--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Passport No.</div>--}}
{{--                    <div class="cell-value">{{ $pax['passport_number'] ?? '—' }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Nationality</div>--}}
{{--                    <div class="cell-value">{{ $pax['passport_country'] ?? ($pax['nationality'] ?? '—') }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Date of Birth</div>--}}
{{--                    <div class="cell-value">{{ $pax['dob'] ? \Carbon\Carbon::parse($pax['dob'])->format('d M Y') : '—' }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Passport Expiry</div>--}}
{{--                    <div class="cell-value {{ $pax['passport_expiry'] && \Carbon\Carbon::parse($pax['passport_expiry'])->isPast() ? 'red' : '' }}">--}}
{{--                        {{ $pax['passport_expiry'] ? \Carbon\Carbon::parse($pax['passport_expiry'])->format('d M Y') : '—' }}--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Gender</div>--}}
{{--                    <div class="cell-value">--}}
{{--                        @php $g = strtoupper($pax['gender'] ?? ''); @endphp--}}
{{--                        {{ in_array($g,['F','FEMALE']) ? 'Female' : (in_array($g,['M','MALE']) ? 'Male' : '—') }}--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- ── SEGMENTS / COUPONS ──────────────────────────────────────── --}}
{{--            @foreach($segments as $sIdx => $seg)--}}
{{--                @php--}}
{{--                    $coupon     = $couponSegMap[$seg['item_id'] ?? ''] ?? null;--}}
{{--                    $couponSt   = $coupon['coupon_status']      ?? null;--}}
{{--                    $couponCode = $coupon['coupon_status_code']  ?? '';--}}
{{--                    $depTime    = $seg['departure_time'] ? \Carbon\Carbon::parse($seg['departure_time']) : null;--}}
{{--                    $arrTime    = $seg['arrival_time']   ? \Carbon\Carbon::parse($seg['arrival_time'])   : null;--}}
{{--                    $duration   = $seg['travel_time']   ?? 0;--}}
{{--                    $isConn     = isset($segments[$sIdx + 1]);--}}
{{--                    $connDur    = $seg['connection']['duration'] ?? 0;--}}
{{--                @endphp--}}

{{--                --}}{{-- Segment label --}}
{{--                <div class="segment-label">--}}
{{--                    Segment {{ $sIdx + 1 }} of {{ count($segments) }}--}}
{{--                    &nbsp;·&nbsp; {{ $seg['carrier'] }}{{ $seg['flight_number'] }}--}}
{{--                    &nbsp;·&nbsp; {{ $seg['class_of_service'] ?? '' }} / {{ $seg['cabin_class'] ?? 'Economy' }}--}}
{{--                    @if(!empty($seg['aircraft_name'])) &nbsp;·&nbsp; {{ $seg['aircraft_name'] }} @endif--}}
{{--                </div>--}}

{{--                --}}{{-- Route band --}}
{{--                <div class="route-band">--}}
{{--                    <div class="airport">--}}
{{--                        <div class="iata">{{ $seg['origin'] }}</div>--}}
{{--                        @if($depTime)--}}
{{--                            <div class="airport-time">{{ $depTime->format('H:i') }}</div>--}}
{{--                            <div class="airport-date">{{ $depTime->format('D, d M Y') }}</div>--}}
{{--                        @endif--}}
{{--                        @if(!empty($seg['departure_terminal']))--}}
{{--                            <div class="airport-terminal">Terminal: {{ $seg['departure_terminal'] }}</div>--}}
{{--                        @endif--}}
{{--                    </div>--}}

{{--                    <div class="flight-arrow">--}}
{{--                        <div class="arrow-line">--}}
{{--                            <span class="arrow-dash"></span>--}}
{{--                            <span class="arrow-plane">✈</span>--}}
{{--                            <span class="arrow-dash"></span>--}}
{{--                        </div>--}}
{{--                        <div class="duration-pill">{{ $fmtDur($duration) }}</div>--}}
{{--                        <div class="cabin-pill">{{ $seg['cabin_class'] ?? 'Economy' }}</div>--}}
{{--                    </div>--}}

{{--                    <div class="airport right">--}}
{{--                        <div class="iata">{{ $seg['destination'] }}</div>--}}
{{--                        @if($arrTime)--}}
{{--                            <div class="airport-time">{{ $arrTime->format('H:i') }}</div>--}}
{{--                            <div class="airport-date">{{ $arrTime->format('D, d M Y') }}</div>--}}
{{--                        @endif--}}
{{--                        @if(!empty($seg['arrival_terminal']))--}}
{{--                            <div class="airport-terminal">Terminal: {{ $seg['arrival_terminal'] }}</div>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                --}}{{-- Segment info row --}}
{{--                <div class="info-grid">--}}
{{--                    <div class="info-cell">--}}
{{--                        <div class="cell-label">Flight</div>--}}
{{--                        <div class="cell-value">{{ $seg['carrier'] }}{{ $seg['flight_number'] }}</div>--}}
{{--                        <div class="cell-sub">{{ $seg['operating_carrier'] != $seg['carrier'] ? 'Operated by '.$seg['operating_airline_name'] : $seg['airline_name'] }}</div>--}}
{{--                    </div>--}}
{{--                    <div class="info-cell">--}}
{{--                        <div class="cell-label">Class / Basis</div>--}}
{{--                        <div class="cell-value">{{ $seg['class_of_service'] ?? '—' }}</div>--}}
{{--                        <div class="cell-sub">{{ $seg['cabin_class'] ?? 'Economy' }}</div>--}}
{{--                    </div>--}}
{{--                    <div class="info-cell">--}}
{{--                        <div class="cell-label">Status</div>--}}
{{--                        <div class="cell-value" style="font-size:11px;">--}}
{{--                            <span class="status-dot {{ $seg['status'] === 'HK' ? 'dot-green' : 'dot-orange' }}"></span>--}}
{{--                            {{ $seg['status_name'] ?? $seg['status'] }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="info-cell">--}}
{{--                        <div class="cell-label">Coupon Status</div>--}}
{{--                        <div class="cell-value" style="font-size:11px;">--}}
{{--                            @if($couponSt)--}}
{{--                                <span class="status-dot {{ $couponDot[$couponCode] ?? $couponDot[$couponSt] ?? 'dot-orange' }}"></span>--}}
{{--                                {{ $couponSt }}--}}
{{--                            @else--}}
{{--                                <span style="color:var(--mid);">—</span>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                --}}{{-- Meals + messages --}}
{{--                @if(!empty($seg['meals']) || !empty($seg['sell_messages']))--}}
{{--                    <div style="padding:8px 20px;background:#fffdf5;border-bottom:1px solid var(--border);font-size:11px;color:#7a6000;">--}}
{{--                        @foreach($seg['meals'] as $meal)--}}
{{--                            🍽 {{ $meal['description'] ?? '' }}--}}
{{--                        @endforeach--}}
{{--                        @foreach($seg['sell_messages'] as $msg)--}}
{{--                            &nbsp;· {{ $msg }}--}}
{{--                        @endforeach--}}
{{--                    </div>--}}
{{--                @endif--}}

{{--                --}}{{-- Layover notice --}}
{{--                @if($isConn && $connDur > 0)--}}
{{--                    <div style="padding:7px 20px;background:#f0f7ff;border-bottom:1px solid #c8ddf5;font-size:11px;color:#1a5276;font-family:'IBM Plex Mono',monospace;">--}}
{{--                        ⏱ Layover at {{ $seg['destination'] }}: {{ $fmtDur($connDur) }}--}}
{{--                    </div>--}}
{{--                @endif--}}

{{--            @endforeach --}}{{-- segments --}}

{{--            --}}{{-- ── BOOKING DETAILS ROW ─────────────────────────────────────── --}}
{{--            <div class="info-grid">--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Issue Date</div>--}}
{{--                    <div class="cell-value">--}}
{{--                        {{ $ticketDate ? \Carbon\Carbon::parse($ticketDate)->format('d M Y') : ($provRes['host_create_date'] ? \Carbon\Carbon::parse($provRes['host_create_date'])->format('d M Y') : '—') }}--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Issuing Agent</div>--}}
{{--                    <div class="cell-value">{{ $agencyInfo['agent_code'] ?? '—' }}</div>--}}
{{--                    <div class="cell-sub">PCC: {{ $provRes['owning_pcc'] ?? '—' }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Baggage</div>--}}
{{--                    <div class="cell-value">{{ $pricing['checked_bag_kg'] ?? '—' }} KG</div>--}}
{{--                    <div class="cell-sub">Cabin: {{ $pricing['cabin_bag_kg'] ?? '—' }} KG</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Fare Conditions</div>--}}
{{--                    <div class="cell-value" style="font-size:11px;">--}}
{{--                        @if($isRefPax)--}}
{{--                            <span style="color:#27ae60;">✓ Refundable</span>--}}
{{--                        @else--}}
{{--                            <span style="color:var(--red);">✗ Non-Refundable</span>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                    <div class="cell-sub">--}}
{{--                        @if($isChgPax) ✓ Changeable @else ✗ Non-Changeable @endif--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            --}}{{-- Penalties row if available --}}
{{--            @if(!empty($rule))--}}
{{--                <div class="info-grid" style="border-top:none;">--}}
{{--                    @foreach($rule['refund_penalties'] ?? [] as $pen)--}}
{{--                        <div class="info-cell">--}}
{{--                            <div class="cell-label">Cancel ({{ str_replace('_',' ',$pen['applicability'] ?? '') }})</div>--}}
{{--                            <div class="cell-value red">{{ number_format($pen['penalty_amount'] ?? 0) }} {{ $pen['penalty_currency'] ?? $currency }}</div>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                    @foreach($rule['exchange_penalties'] ?? [] as $pen)--}}
{{--                        <div class="info-cell">--}}
{{--                            <div class="cell-label">Change ({{ str_replace('_',' ',$pen['applicability'] ?? '') }})</div>--}}
{{--                            <div class="cell-value" style="color:#1a5276;">{{ number_format($pen['penalty_amount'] ?? 0) }} {{ $pen['penalty_currency'] ?? $currency }}</div>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            --}}{{-- ── FARE SUMMARY ────────────────────────────────────────────── --}}
{{--            <div class="fare-row">--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Base Fare</div>--}}
{{--                    <div class="cell-value">{{ number_format($paxFare['base']) }} {{ $currency }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Taxes & Fees</div>--}}
{{--                    <div class="cell-value">{{ number_format($paxFare['taxes']) }} {{ $currency }}</div>--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    @if($ticketDeadline)--}}
{{--                        <div class="cell-label">Ticket Deadline</div>--}}
{{--                        <div class="cell-value red">{{ \Carbon\Carbon::parse($ticketDeadline)->format('d M Y H:i') }}</div>--}}
{{--                    @else--}}
{{--                        <div class="cell-label">Payment</div>--}}
{{--                        <div class="cell-value">{{ $ticket['payment']['total'] ? number_format($ticket['payment']['total']).' '.$currency : '—' }}</div>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div class="info-cell">--}}
{{--                    <div class="cell-label">Contact</div>--}}
{{--                    <div class="cell-value" style="font-size:11px;">{{ $pax['phone'] ?? ($contactInfo['phones'][0] ?? '—') }}</div>--}}
{{--                    <div class="cell-sub">{{ $pax['email'] ?? '' }}</div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="fare-total">--}}
{{--                <div class="fare-total-label">Total Amount Paid</div>--}}
{{--                <div class="fare-total-value">{{ number_format($paxFare['total']) }} {{ $currency }}</div>--}}
{{--            </div>--}}

{{--            --}}{{-- ── FOOTER ──────────────────────────────────────────────────── --}}
{{--            <div class="ticket-footer">--}}
{{--                <div class="conditions">--}}
{{--                    <h4>Important Conditions</h4>--}}
{{--                    <ul>--}}
{{--                        <li>Carry valid passport, visa & travel documents.</li>--}}
{{--                        <li>Check-in at least 3 hours before international departure.</li>--}}
{{--                        <li>Name changes are not permitted after ticketing.</li>--}}
{{--                        <li>Airline may change schedule without prior notice.</li>--}}
{{--                        <li>Baggage allowance per airline policy: {{ $pricing['checked_bag_kg'] ?? '—' }}kg checked.</li>--}}
{{--                        @if(!$isRefPax)<li>This ticket is NON-REFUNDABLE.</li>@endif--}}
{{--                        @if(!$isChgPax)<li>Date changes are NOT permitted.</li>@endif--}}
{{--                        <li>No-show penalties apply as per fare conditions.</li>--}}
{{--                    </ul>--}}
{{--                </div>--}}

{{--                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;">--}}
{{--                    --}}{{-- Logo --}}
{{--                    @if(!empty($logoId))--}}
{{--                        <img src="{{ get_file_url($logoId,'full') }}" style="max-height:36px;max-width:120px;object-fit:contain;">--}}
{{--                    @else--}}
{{--                        <div style="font-size:14px;font-weight:700;font-family:'IBM Plex Mono',monospace;">{{ $companyName }}</div>--}}
{{--                    @endif--}}

{{--                    --}}{{-- QR --}}
{{--                    <div class="qr-block">--}}
{{--                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=128x128&data={{ urlencode($bookingId.($ticketNum ? '/'.$ticketNum : '')) }}"--}}
{{--                             alt="QR">--}}
{{--                        <p>{{ $bookingId }}</p>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}{{-- .ticket --}}
{{--    @endforeach --}}{{-- passengers --}}

{{--</div>--}}{{-- .ticket-stack --}}

{{--<script>--}}
{{--    (function () {--}}
{{--        // A4 dimensions in mm → px at 96dpi: 210×297mm--}}
{{--        // 1mm = 3.7795px  →  210mm = 793.7px,  297mm = 1122.5px--}}
{{--        var A4_W_PX = 794;--}}
{{--        var A4_H_PX = 1123;--}}

{{--        /**--}}
{{--         * Wrap each .ticket in a .ticket-page div,--}}
{{--         * then scale the .ticket so it fits inside A4.--}}
{{--         */--}}
{{--        function setupTickets() {--}}
{{--            document.querySelectorAll('.ticket').forEach(function (ticket) {--}}
{{--                // Wrap if not already wrapped--}}
{{--                if (!ticket.parentElement.classList.contains('ticket-page')) {--}}
{{--                    var page = document.createElement('div');--}}
{{--                    page.className = 'ticket-page';--}}
{{--                    // Screen: mimic A4 size--}}
{{--                    page.style.cssText = [--}}
{{--                        'width:'  + A4_W_PX + 'px',--}}
{{--                        'height:' + A4_H_PX + 'px',--}}
{{--                        'overflow:hidden',--}}
{{--                        'position:relative',--}}
{{--                        'background:#fff',--}}
{{--                        'margin:0 auto 32px',--}}
{{--                        'box-shadow:0 4px 24px rgba(0,0,0,.18)'--}}
{{--                    ].join(';');--}}
{{--                    ticket.parentNode.insertBefore(page, ticket);--}}
{{--                    page.appendChild(ticket);--}}
{{--                }--}}

{{--                scaleTicket(ticket);--}}
{{--            });--}}
{{--        }--}}

{{--        function scaleTicket(ticket) {--}}
{{--            // Reset--}}
{{--            ticket.style.transform = '';--}}
{{--            ticket.style.width     = '';--}}
{{--            ticket.style.marginLeft = '';--}}

{{--            // Set width to A4 width--}}
{{--            ticket.style.width = A4_W_PX + 'px';--}}
{{--            var naturalH = ticket.scrollHeight;--}}

{{--            // Available print height: A4 - 2×8mm margin = 297 - 16 = 281mm ≈ 1062px--}}
{{--            var printH = A4_H_PX - 61; // ~1062px--}}

{{--            if (naturalH > printH) {--}}
{{--                var scale = printH / naturalH;--}}
{{--                ticket.style.transform = 'scale(' + scale + ')';--}}
{{--                ticket.style.transformOrigin = 'top center';--}}
{{--                // Center horizontally on screen--}}
{{--                var scaledW = A4_W_PX * scale;--}}
{{--                ticket.style.marginLeft = ((A4_W_PX - scaledW) / 2) + 'px';--}}
{{--            } else {--}}
{{--                ticket.style.transform = '';--}}
{{--                ticket.style.transformOrigin = '';--}}
{{--            }--}}
{{--            // Always fix page height on screen--}}
{{--            ticket.parentElement.style.height = A4_H_PX + 'px';--}}
{{--        }--}}

{{--        /** Before printing — reset inline styles, let CSS @page handle layout */--}}
{{--        function beforePrint() {--}}
{{--            document.querySelectorAll('.ticket-page').forEach(function (page) {--}}
{{--                page.style.cssText = '';--}}
{{--            });--}}
{{--            document.querySelectorAll('.ticket').forEach(function (ticket) {--}}
{{--                // Measure at full A4 width--}}
{{--                ticket.style.cssText = '';--}}
{{--                ticket.style.width = 'calc(210mm - 0px)';--}}
{{--                var naturalH = ticket.scrollHeight;--}}
{{--                // Print usable height = 297mm - 16mm margin = 281mm ≈ 1062px--}}
{{--                var printH_mm = 281;--}}
{{--                var naturalH_mm = naturalH / 3.7795;--}}
{{--                if (naturalH_mm > printH_mm) {--}}
{{--                    var scale = printH_mm / naturalH_mm;--}}
{{--                    ticket.style.transform = 'scale(' + scale + ')';--}}
{{--                    ticket.style.transformOrigin = 'top center';--}}
{{--                    // Push right margin auto to center--}}
{{--                    ticket.style.display = 'block';--}}
{{--                    ticket.style.marginLeft = 'auto';--}}
{{--                    ticket.style.marginRight = 'auto';--}}
{{--                } else {--}}
{{--                    ticket.style.transform = '';--}}
{{--                    ticket.style.display = 'block';--}}
{{--                    ticket.style.marginLeft = 'auto';--}}
{{--                    ticket.style.marginRight = 'auto';--}}
{{--                }--}}
{{--            });--}}
{{--        }--}}

{{--        /** After print: restore screen layout */--}}
{{--        function afterPrint() {--}}
{{--            document.querySelectorAll('.ticket-page').forEach(function (page) {--}}
{{--                page.style.width  = A4_W_PX + 'px';--}}
{{--                page.style.height = A4_H_PX + 'px';--}}
{{--                page.style.margin = '0 auto 32px';--}}
{{--                page.style.boxShadow = '0 4px 24px rgba(0,0,0,.18)';--}}
{{--            });--}}
{{--            document.querySelectorAll('.ticket').forEach(function (ticket) {--}}
{{--                scaleTicket(ticket);--}}
{{--            });--}}
{{--        }--}}

{{--        window.addEventListener('load', setupTickets);--}}
{{--        window.addEventListener('beforeprint', beforePrint);--}}
{{--        window.addEventListener('afterprint',  afterPrint);--}}
{{--        // Also handle Ctrl+P via matchMedia--}}
{{--        if (window.matchMedia) {--}}
{{--            window.matchMedia('print').addListener(function (mq) {--}}
{{--                if (mq.matches) beforePrint(); else afterPrint();--}}
{{--            });--}}
{{--        }--}}
{{--    })();--}}
{{--</script>--}}

{{--2=====================================================================--}}


@extends('Layout::empty')

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #c8cacf;
            color: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── TOP BAR ───────────────────────────────────────── */
        .topbar {
            background: #111; color: #fff;
            padding: 9px 24px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
            font-size: 12px; font-family: 'IBM Plex Mono', monospace;
        }
        .topbar-btn {
            background: #c0392b; color: #fff;
            border: none; cursor: pointer;
            padding: 6px 16px; font-family: 'IBM Plex Mono', monospace;
            font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
            border-radius: 2px;
        }

        /* ── PAGE WRAPPER ───────────────────────────────────── */
        .page-wrap {
            display: flex; flex-direction: column;
            align-items: center;
            padding: 28px 0 60px;
            gap: 32px;
        }

        /* ── TICKET CARD ────────────────────────────────────── */
        .ticket {
            width: 720px;
            background: #fff;
            box-shadow: 0 6px 32px rgba(0,0,0,.22);
            border-radius: 3px;
            overflow: hidden;
            font-size: 12px;
        }

        /* ── HEADER ─────────────────────────────────────────── */
        .t-header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 12px 18px;
            background: #fff;
            border-bottom: 2px solid #c0392b;
            gap: 10px;
        }
        .airline-col { display: flex; align-items: center; gap: 10px; }
        .airline-circle {
            width: 38px; height: 38px; border-radius: 50%;
            background: #c0392b;
            display: flex; align-items: center; justify-content: center;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11px; font-weight: 600; color: #fff;
            flex-shrink: 0;
        }
        .airline-name  { font-size: 14px; font-weight: 700; color: #111; }
        .airline-flist { font-family: 'IBM Plex Mono', monospace; font-size: 9px; color: #888; margin-top: 2px; }
        .logo-col      { text-align: center; }
        .logo-col img  { max-height: 34px; max-width: 110px; object-fit: contain; }
        .logo-col .co-name { font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 700; color: #111; }
        .pnr-col       { text-align: right; }
        .pnr-label     { font-size: 8px; text-transform: uppercase; letter-spacing: .12em; color: #888; }
        .pnr-val       { font-family: 'IBM Plex Mono', monospace; font-size: 22px; font-weight: 700; color: #c0392b; line-height: 1.1; }
        .pnr-sub       { font-family: 'IBM Plex Mono', monospace; font-size: 9px; color: #888; margin-top: 1px; }

        /* ── PASSENGER ROW ──────────────────────────────────── */
        .t-pax {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 18px;
            background: #f4f5f7;
            border-bottom: 1px solid #e0e3e8;
        }
        .pax-no   { font-size: 8px; text-transform: uppercase; letter-spacing: .1em; color: #888; }
        .pax-name { font-size: 16px; font-weight: 700; text-transform: uppercase; margin-top: 1px; }
        .badges   { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .badge {
            padding: 2px 8px; border-radius: 2px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em;
        }
        .b-adult  { background:#27ae60; color:#fff; }
        .b-child  { background:#e67e22; color:#fff; }
        .b-infant { background:#3498db; color:#fff; }
        .b-dark   { background:#111;    color:#fff; }
        .b-ok     { background:#27ae60; color:#fff; }
        .b-void   { background:#c0392b; color:#fff; }
        .b-pend   { background:#e67e22; color:#fff; }
        .b-purple { background:#8e44ad; color:#fff; }

        /* ── PASSPORT ROW ───────────────────────────────────── */
        .t-passport {
            display: grid;
            grid-template-columns: 20% 15% 22% 23% 20%;
            border-bottom: 1px solid #e0e3e8;
        }
        .pp-cell {
            padding: 6px 12px;
            border-right: 1px solid #e0e3e8;
        }
        .pp-cell:last-child { border-right: none; }

        /* ── SEGMENT ────────────────────────────────────────── */
        .segment { border-top: 1px solid #e0e3e8; }
        .seg-label {
            padding: 3px 14px;
            background: #fff8f0;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 8px; text-transform: uppercase; letter-spacing: .08em;
            color: #b05010;
            border-bottom: 1px solid #f0d9c0;
        }
        /* Route band */
        .route-band {
            background: #b83224;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 10px 16px;
            color: #fff;
        }
        .iata      { font-family: 'IBM Plex Mono', monospace; font-size: 34px; font-weight: 700; line-height: 1; }
        .r-time    { font-size: 14px; font-weight: 600; margin-top: 3px; }
        .r-date    { font-size: 9px; opacity: .8; margin-top: 1px; }
        .r-term    { font-size: 8px; opacity: .6; margin-top: 2px; }
        .route-mid { text-align: center; }
        .plane-icon{ font-size: 20px; display: block; margin-bottom: 4px; }
        .dur-pill  {
            display: inline-block; background: rgba(255,255,255,.22);
            padding: 2px 10px; border-radius: 12px;
            font-family: 'IBM Plex Mono', monospace; font-size: 9px;
        }
        .cab-pill  {
            display: inline-block; background: rgba(0,0,0,.2);
            padding: 1px 8px; border-radius: 12px;
            font-size: 8px; text-transform: uppercase; letter-spacing: .04em;
            margin-top: 4px;
        }
        .route-right { text-align: right; }
        /* Seg info strip */
        .seg-info {
            display: grid;
            grid-template-columns: 28% 20% 26% 26%;
            border-bottom: 1px solid #e0e3e8;
        }
        .si-cell {
            padding: 6px 12px;
            border-right: 1px solid #e0e3e8;
        }
        .si-cell:last-child { border-right: none; }
        /* Layover */
        .layover-bar {
            padding: 4px 14px;
            background: #eaf3fb;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 9px; color: #1a5276;
            border-top: 1px solid #cde0f0;
        }

        /* ── SHARED CELL STYLES ─────────────────────────────── */
        .lbl { font-size: 7px; text-transform: uppercase; letter-spacing: .1em; color: #888; margin-bottom: 2px; }
        .val { font-family: 'IBM Plex Mono', monospace; font-size: 11px; font-weight: 600; color: #111; }
        .val.red   { color: #c0392b; }
        .val.green { color: #27ae60; }
        .val.blue  { color: #1a5276; }
        .val.sm    { font-size: 10px; }
        .sub       { font-size: 9px; color: #888; margin-top: 1px; }
        .dot {
            display: inline-block; width: 7px; height: 7px; border-radius: 50%;
            margin-right: 3px; vertical-align: middle;
        }
        .dot-g { background: #27ae60; }
        .dot-b { background: #3498db; }
        .dot-r { background: #c0392b; }
        .dot-o { background: #e67e22; }

        /* ── BOOKING INFO ───────────────────────────────────── */
        .t-bookinfo {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            border-top: 2px solid #e0e3e8;
        }
        .bi-cell {
            padding: 8px 12px;
            border-right: 1px solid #e0e3e8;
        }
        .bi-cell:last-child { border-right: none; }

        /* ── FARE ROW ───────────────────────────────────────── */
        .t-fare {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-top: 2px solid #e0e3e8;
        }
        .fare-cell {
            padding: 8px 14px;
            border-right: 1px solid #e0e3e8;
        }
        .fare-cell:last-child { border-right: none; }

        /* ── TOTAL BAR ──────────────────────────────────────── */
        .t-total {
            background: #111;
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 18px;
        }
        .total-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.5); }
        .total-val { font-family: 'IBM Plex Mono', monospace; font-size: 20px; font-weight: 700; color: #fff; }

        /* ── FOOTER ─────────────────────────────────────────── */
        .t-footer {
            display: grid;
            grid-template-columns: 1fr 140px;
            background: #f4f5f7;
            border-top: 1px solid #e0e3e8;
            min-height: 90px;
        }
        .footer-left  { padding: 12px 16px; }
        .footer-right { padding: 12px 12px; text-align: center; border-left: 1px solid #e0e3e8; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; }
        .cond-title   { font-size: 8px; text-transform: uppercase; letter-spacing: .1em; color: #888; margin-bottom: 6px; }
        .cond-list    { list-style: none; }
        .cond-list li {
            font-size: 9px; color: #555; line-height: 1.7;
            padding-left: 12px; position: relative;
        }
        .cond-list li::before { content: '•'; position: absolute; left: 0; color: #c0392b; }

        /* ══════════════════════════════════════════════════════
           PRINT  —  screen-identical layout, just no chrome
           ══════════════════════════════════════════════════════ */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 8mm;
            }

            body { background: #fff !important; }

            .topbar { display: none !important; }

            .page-wrap {
                padding: 0 !important;
                gap: 0 !important;
                display: block !important;
            }

            /* Each ticket fills one A4 page */
            .ticket {
                width: 194mm !important;        /* 210 - 8-8 side margins */
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 auto !important;      /* center on page */
                page-break-after: always !important;
                break-after: page !important;
                font-size: 10px !important;
            }
            .ticket:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            /* Tighten spacing for print */
            .t-header   { padding: 8px 14px !important; }
            .pnr-val    { font-size: 18px !important; }
            .t-pax      { padding: 6px 14px !important; }
            .pax-name   { font-size: 14px !important; }
            .pp-cell    { padding: 4px 10px !important; }
            .seg-label  { padding: 2px 12px !important; }
            .route-band { padding: 7px 14px !important; }
            .iata       { font-size: 26px !important; }
            .r-time     { font-size: 12px !important; }
            .dur-pill   { font-size: 8px !important; }
            .si-cell    { padding: 4px 10px !important; }
            .bi-cell    { padding: 6px 10px !important; }
            .fare-cell  { padding: 6px 12px !important; }
            .t-total    { padding: 7px 14px !important; }
            .total-val  { font-size: 16px !important; }
            .t-footer   { min-height: 0 !important; }
            .footer-left  { padding: 8px 12px !important; }
            .footer-right { padding: 8px 10px !important; }
            .layover-bar  { padding: 3px 12px !important; }
            .val  { font-size: 10px !important; }
            .lbl  { font-size: 7px !important; }
            .sub  { font-size: 8px !important; }
            .cond-list li { font-size: 8px !important; line-height: 1.6 !important; }
        }
    </style>
@endpush

@php
    $raw  = $booking->pnr_raw_data;
    $pnr  = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);

    $passengers     = $pnr['passengers']          ?? [];
    $segments       = $pnr['segments']            ?? [];
    $flightTickets  = $pnr['flight_tickets']      ?? [];
    $pricing        = $pnr['pricing']             ?? [];
    $grandTotal     = $pricing['grand_total']      ?? [];
    $fareBreakdowns = $pricing['fare_breakdowns']  ?? [];
    $fareRules      = $pnr['fare_rules']           ?? [];
    $supplier       = $pnr['supplier_locator']     ?? [];
    $provRes        = $pnr['provider_reservation'] ?? [];
    $actionStatus   = $pnr['action_status']        ?? [];
    $agencyInfo     = $pnr['agency_info']          ?? [];
    $contactInfo    = $pnr['contact_info']         ?? [];

    $bookingId      = $pnr['booking_id']          ?? ($booking->pnr_id ?? 'N/A');
    $airlinePnr     = $supplier['locator_code']   ?? null;
    $isTicketed     = $pnr['is_ticketed']         ?? false;
    $currency       = $grandTotal['currency']      ?? 'BDT';
    $baseAmount     = $grandTotal['subtotal']      ?? 0;
    $taxAmount      = $grandTotal['taxes']         ?? 0;
    $totalAmount    = $grandTotal['total']         ?? 0;
    $ticketDeadline = $actionStatus['ticket_date'] ?? null;

    $fareRuleMap = [];
    foreach ($fareRules      as $fr) $fareRuleMap[$fr['passenger_code'] ?? 'ADT'] = $fr;
    $fareMap = [];
    foreach ($fareBreakdowns as $fb) $fareMap[$fb['traveler_type']      ?? 'ADT'] = $fb;

    $couponDot = [
        'I'=>'dot-b','Not Flown'=>'dot-b',
        'F'=>'dot-g','Flown'=>'dot-g',
        'V'=>'dot-r','Voided'=>'dot-r',
        'R'=>'dot-o','Refunded'=>'dot-o',
    ];
    $typeBadge = ['ADT'=>'b-adult','CNN'=>'b-child','INF'=>'b-infant'];
    $typeLabel = ['ADT'=>'Adult','CNN'=>'Child','INF'=>'Infant'];
    $fmtDur    = fn($m) => intdiv((int)$m,60).'h '.((int)$m%60).'m';

    $firstSeg   = $segments[0]   ?? [];
    $carrier    = $firstSeg['carrier']      ?? '';
    $airName    = $firstSeg['airline_name'] ?? $carrier;
    $flightList = implode(' · ', array_map(fn($s)=>($s['carrier']??'').($s['flight_number']??''), $segments));

    $companyName = setting_item('company_name') ?? setting_item('site_name') ?? '';
    $logoId      = setting_item('logo_invoice_id') ?? setting_item('logo_id');
@endphp

{{-- TOP BAR --}}
<div class="topbar">
    <span>✈ E-TICKET &mdash; {{ $bookingId }} &mdash; {{ count($passengers) }} PASSENGER(S)</span>
    <button class="topbar-btn" onclick="window.print()">⎙ Print All Tickets</button>
</div>

<div class="page-wrap">

    @foreach($passengers as $paxIdx => $pax)
        @php
            $tIdx    = $paxIdx + 1;
            $ticket  = collect($flightTickets)->first(fn($t)=>($t['traveler_index']??null)==$tIdx);

            $tNum    = $ticket['number']             ?? null;
            $tDate   = $ticket['date']               ?? null;
            $tStat   = $ticket['ticket_status']      ?? null;
            $tCode   = $ticket['ticket_status_code'] ?? '';
            $coupons = $ticket['flight_coupons']     ?? [];
            $cMap    = [];
            foreach ($coupons as $cp) $cMap[$cp['item_id']??''] = $cp;

            $paxType = $pax['traveler_type'] ?? 'ADT';
            $rule    = $fareRuleMap[$paxType] ?? [];
            $fb      = $fareMap[$paxType]    ?? [];
            $isRef   = $rule['is_refundable']  ?? ($pnr['is_cancelable'] ?? false);
            $isChg   = $rule['is_changeable']  ?? false;
            $pBase   = $fb['subtotal'] ?? $baseAmount;
            $pTax    = $fb['taxes']    ?? $taxAmount;
            $pTot    = $fb['total']    ?? $totalAmount;

            $tBadge  = 'b-pend';
            if ($tCode==='TE'||$tStat==='Issued') $tBadge='b-ok';
            if ($tCode==='TV'||$tStat==='Voided') $tBadge='b-void';

            $phone = $pax['phone'] ?? ($contactInfo['phones'][0] ?? '—');
            if (is_array($phone)) $phone = $phone['number'] ?? '—';
            $email = $pax['email'] ?? '';

            $passDoc = collect($pax['identity_documents']??[])->first(fn($d)=>($d['documentType']??'')==='PASSPORT');
            $g = strtoupper($pax['gender']??'');
            $gLabel = in_array($g,['F','FEMALE'])?'Female':(in_array($g,['M','MALE'])?'Male':'—');

            $refPens = $rule['refund_penalties']   ?? [];
            $chgPens = $rule['exchange_penalties'] ?? [];
        @endphp

        <div class="ticket">

            {{-- ── HEADER ─────────────────────────────────────── --}}
            <div class="t-header">
                {{-- Left: airline --}}
                <div class="airline-col">
                    <div class="airline-circle">{{ $carrier }}</div>
                    <div>
                        <div class="airline-name">{{ $airName }}</div>
                        <div class="airline-flist">{{ $flightList }}</div>
                    </div>
                </div>
                {{-- Center: logo --}}
                <div class="logo-col">
                    @if(!empty($logoId))
                        <img src="{{ get_file_url($logoId,'full') }}" alt="Logo">
                    @elseif($companyName)
                        <div class="co-name">{{ $companyName }}</div>
                    @else
                        <div class="co-name" style="color:#c0392b;">E-TICKET</div>
                    @endif
                </div>
                {{-- Right: PNR --}}
                <div class="pnr-col">
                    <div class="pnr-label">Booking Reference</div>
                    <div class="pnr-val">{{ $bookingId }}</div>
                    @if($airlinePnr && $airlinePnr !== $bookingId)
                        <div class="pnr-sub">Airline PNR: {{ $airlinePnr }}</div>
                    @endif
                    @if($tNum)
                        <div class="pnr-sub">Ticket: {{ $tNum }}</div>
                    @endif
                </div>
            </div>

            {{-- ── PASSENGER ───────────────────────────────────── --}}
            <div class="t-pax">
                <div>
                    <div class="pax-no">Passenger {{ $tIdx }}</div>
                    <div class="pax-name">{{ $pax['last_name']??'' }} / {{ $pax['first_name']??'' }}</div>
                </div>
                <div class="badges">
                    <span class="badge {{ $typeBadge[$paxType]??'b-adult' }}">{{ $typeLabel[$paxType]??'Adult' }}</span>
                    @if($tNum)   <span class="badge b-dark">{{ $tNum }}</span> @endif
                    @if($tStat)  <span class="badge {{ $tBadge }}">{{ $tStat }}</span> @endif
                    @if($isTicketed) <span class="badge b-purple">E-Ticket</span> @endif
                </div>
            </div>

            {{-- ── PASSPORT ────────────────────────────────────── --}}
            <div class="t-passport">
                <div class="pp-cell">
                    <div class="lbl">Passport No.</div>
                    <div class="val">{{ $pax['passport_number']??($passDoc['documentNumber']??'—') }}</div>
                </div>
                <div class="pp-cell">
                    <div class="lbl">Nationality</div>
                    <div class="val">{{ $pax['passport_country']??($pax['nationality']??'—') }}</div>
                </div>
                <div class="pp-cell">
                    <div class="lbl">Date of Birth</div>
                    <div class="val">{{ $pax['dob'] ? \Carbon\Carbon::parse($pax['dob'])->format('d M Y') : '—' }}</div>
                </div>
                <div class="pp-cell">
                    <div class="lbl">Passport Expiry</div>
                    <div class="val {{ ($pax['passport_expiry']&&\Carbon\Carbon::parse($pax['passport_expiry'])->isPast()) ? 'red':'' }}">
                        {{ $pax['passport_expiry'] ? \Carbon\Carbon::parse($pax['passport_expiry'])->format('d M Y') : '—' }}
                    </div>
                </div>
                <div class="pp-cell">
                    <div class="lbl">Gender</div>
                    <div class="val">{{ $gLabel }}</div>
                </div>
            </div>

            {{-- ── SEGMENTS ─────────────────────────────────────── --}}
            @foreach($segments as $sIdx => $seg)
                @php
                    $cp     = $cMap[$seg['item_id']??''] ?? null;
                    $cpSt   = $cp['coupon_status']      ?? null;
                    $cpCode = $cp['coupon_status_code'] ?? '';
                    $dep    = !empty($seg['departure_time']) ? \Carbon\Carbon::parse($seg['departure_time']) : null;
                    $arr    = !empty($seg['arrival_time'])   ? \Carbon\Carbon::parse($seg['arrival_time'])   : null;
                    $dur    = $fmtDur($seg['travel_time']??0);
                    $conn   = $seg['connection']['duration'] ?? 0;
                    $isLast = $sIdx === count($segments)-1;
                    $meals  = collect($seg['meals']??[])->pluck('description')->implode(', ');
                    $msgs   = implode(' · ', array_filter($seg['sell_messages']??[]));
                    $oper   = ($seg['operating_carrier']??''!==($seg['carrier']??''))
                              ? 'Op: '.($seg['operating_airline_name']??'')
                              : ($seg['airline_name']??'');
                @endphp
                <div class="segment">

                    {{-- Segment label --}}
                    <div class="seg-label">
                        SEG {{ $sIdx+1 }}/{{ count($segments) }}
                        &nbsp;·&nbsp;{{ $seg['carrier'] }}{{ $seg['flight_number'] }}
                        &nbsp;·&nbsp;{{ $seg['class_of_service']??'' }}/{{ $seg['cabin_class']??'Economy' }}
                        @if(!empty($seg['aircraft_name'])) &nbsp;·&nbsp;{{ $seg['aircraft_name'] }} @endif
                        @if(!empty($meals)) &nbsp;·&nbsp;🍽 {{ $meals }} @endif
                        @if(!empty($msgs))  &nbsp;·&nbsp;{{ $msgs }} @endif
                    </div>

                    {{-- Route band --}}
                    <div class="route-band">
                        <div>
                            <div class="iata">{{ $seg['origin'] }}</div>
                            @if($dep)
                                <div class="r-time">{{ $dep->format('H:i') }}</div>
                                <div class="r-date">{{ $dep->format('D, d M Y') }}</div>
                            @endif
                            @if(!empty($seg['departure_terminal']))
                                <div class="r-term">Terminal: {{ $seg['departure_terminal'] }}</div>
                            @endif
                        </div>
                        <div class="route-mid">
                            <span class="plane-icon">✈</span>
                            <span class="dur-pill">{{ $dur }}</span><br>
                            <span class="cab-pill" style="margin-top:5px;display:inline-block;">{{ $seg['cabin_class']??'Economy' }}</span>
                        </div>
                        <div class="route-right">
                            <div class="iata">{{ $seg['destination'] }}</div>
                            @if($arr)
                                <div class="r-time">{{ $arr->format('H:i') }}</div>
                                <div class="r-date">{{ $arr->format('D, d M Y') }}</div>
                            @endif
                            @if(!empty($seg['arrival_terminal']))
                                <div class="r-term">Terminal: {{ $seg['arrival_terminal'] }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Info strip --}}
                    <div class="seg-info">
                        <div class="si-cell">
                            <div class="lbl">Flight</div>
                            <div class="val">{{ $seg['carrier'] }}{{ $seg['flight_number'] }}</div>
                            <div class="sub">{{ $oper }}</div>
                        </div>
                        <div class="si-cell">
                            <div class="lbl">Class / Basis</div>
                            <div class="val">{{ $seg['class_of_service']??'—' }}</div>
                            <div class="sub">{{ $seg['cabin_class']??'Economy' }}</div>
                        </div>
                        <div class="si-cell">
                            <div class="lbl">Seg Status</div>
                            <div class="val sm">
                                <span class="dot {{ $seg['status']==='HK'?'dot-g':'dot-o' }}"></span>
                                {{ $seg['status_name']??$seg['status'] }}
                            </div>
                        </div>
                        <div class="si-cell">
                            <div class="lbl">Coupon</div>
                            <div class="val sm">
                                @if($cpSt)
                                    <span class="dot {{ $couponDot[$cpCode]??$couponDot[$cpSt]??'dot-o' }}"></span>{{ $cpSt }}
                                @else <span style="color:#bbb;">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Layover --}}
                    @if(!$isLast && $conn > 0)
                        <div class="layover-bar">⏱ Layover at {{ $seg['destination'] }}: {{ $fmtDur($conn) }}</div>
                    @endif

                </div>
            @endforeach

            {{-- ── BOOKING INFO ──────────────────────────────────── --}}
            <div class="t-bookinfo">
                <div class="bi-cell">
                    <div class="lbl">Issue Date</div>
                    <div class="val">{{ $tDate ? \Carbon\Carbon::parse($tDate)->format('d M Y') : ($provRes['host_create_date'] ? \Carbon\Carbon::parse($provRes['host_create_date'])->format('d M Y') : '—') }}</div>
                </div>
                <div class="bi-cell">
                    <div class="lbl">Issuing Agent</div>
                    <div class="val">{{ $agencyInfo['agent_code']??'—' }}</div>
                    <div class="sub">PCC: {{ $provRes['owning_pcc']??'—' }}</div>
                </div>
                <div class="bi-cell">
                    <div class="lbl">Baggage</div>
                    <div class="val">{{ $pricing['checked_bag_kg']??'—' }} KG</div>
                    <div class="sub">Cabin: {{ $pricing['cabin_bag_kg']??'—' }} KG</div>
                </div>
                <div class="bi-cell">
                    <div class="lbl">Fare Rules</div>
                    <div class="val sm {{ $isRef?'green':'red' }}">{{ $isRef?'✓ Refundable':'✗ Non-Refundable' }}</div>
                    <div class="sub">{{ $isChg?'✓ Changeable':'✗ Non-Changeable' }}</div>
                </div>
                <div class="bi-cell">
                    @if(!empty($refPens[0]))
                        <div class="lbl">Cancel ({{ str_replace('_',' ',$refPens[0]['applicability']??'') }})</div>
                        <div class="val red">{{ number_format($refPens[0]['penalty_amount']??0) }} {{ $refPens[0]['penalty_currency']??$currency }}</div>
                    @endif
                    @if(!empty($refPens[1]))
                        <div class="lbl" style="margin-top:4px;">Cancel ({{ str_replace('_',' ',$refPens[1]['applicability']??'') }})</div>
                        <div class="val red">{{ number_format($refPens[1]['penalty_amount']??0) }} {{ $refPens[1]['penalty_currency']??$currency }}</div>
                    @endif
                </div>
                <div class="bi-cell">
                    @if(!empty($chgPens[0]))
                        <div class="lbl">Change ({{ str_replace('_',' ',$chgPens[0]['applicability']??'') }})</div>
                        <div class="val blue">{{ number_format($chgPens[0]['penalty_amount']??0) }} {{ $chgPens[0]['penalty_currency']??$currency }}</div>
                    @endif
                    @if(!empty($chgPens[1]))
                        <div class="lbl" style="margin-top:4px;">Change ({{ str_replace('_',' ',$chgPens[1]['applicability']??'') }})</div>
                        <div class="val blue">{{ number_format($chgPens[1]['penalty_amount']??0) }} {{ $chgPens[1]['penalty_currency']??$currency }}</div>
                    @endif
                </div>
            </div>

            {{-- ── FARE ROW ──────────────────────────────────────── --}}
            <div class="t-fare">
                <div class="fare-cell">
                    <div class="lbl">Base Fare</div>
                    <div class="val">{{ number_format($pBase) }} {{ $currency }}</div>
                </div>
                <div class="fare-cell">
                    <div class="lbl">Taxes &amp; Fees</div>
                    <div class="val">{{ number_format($pTax) }} {{ $currency }}</div>
                </div>
                <div class="fare-cell">
                    <div class="lbl">{{ $ticketDeadline ? 'Ticket Deadline' : 'Payment' }}</div>
                    <div class="val {{ $ticketDeadline?'red':'' }}">
                        {{ $ticketDeadline ? \Carbon\Carbon::parse($ticketDeadline)->format('d M Y H:i') : '—' }}
                    </div>
                </div>
                <div class="fare-cell">
                    <div class="lbl">Contact</div>
                    <div class="val sm">{{ $phone }}</div>
                    <div class="sub">{{ $email }}</div>
                </div>
            </div>

            {{-- ── TOTAL ─────────────────────────────────────────── --}}
            <div class="t-total">
                <div class="total-lbl">Total Amount Paid</div>
                <div class="total-val">{{ number_format($pTot) }} {{ $currency }}</div>
            </div>

            {{-- ── FOOTER ────────────────────────────────────────── --}}
            <div class="t-footer">
                <div class="footer-left">
                    <div class="cond-title">Important Conditions</div>
                    <ul class="cond-list">
                        <li>Carry valid passport, visa &amp; travel documents.</li>
                        <li>Check-in at least 3 hours before international departure.</li>
                        <li>Name changes are not permitted after ticketing.</li>
                        <li>Airline may change schedule without prior notice.</li>
                        <li>Baggage allowance: {{ $pricing['checked_bag_kg']??'—' }}kg checked / {{ $pricing['cabin_bag_kg']??'—' }}kg cabin per airline policy.</li>
                        @if(!$isRef)<li style="color:#c0392b;font-weight:600;">This ticket is NON-REFUNDABLE.</li>@endif
                        @if(!$isChg)<li style="color:#c0392b;font-weight:600;">Date changes are NOT permitted.</li>@endif
                        <li>No-show penalties apply as per fare conditions.</li>
                    </ul>
                </div>
                <div class="footer-right">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($bookingId.($tNum?'/'.$tNum:'')) }}"
                         style="width:60px;height:60px;" alt="QR">
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:8px;color:#888;margin-top:2px;">{{ $bookingId }}</div>
                </div>
            </div>

        </div>{{-- .ticket --}}
    @endforeach

</div>{{-- .page-wrap --}}

