
@extends('layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue:    #1d4ed8;
            --blue2:   #1e3a8a;
            --blue-lt: #eff6ff;
            --green:   #059669;
            --red:     #dc2626;
            --amber:   #d97706;
            --purple:  #7c3aed;
            --border:  #e2e8f0;
            --bg:      #f0f4f8;
            --card:    #ffffff;
            --text:    #0f172a;
            --muted:   #64748b;
            --radius:  14px;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── Layout ── */
        .bc-wrap   { padding: 28px 0 60px; }
        .bc-inner  { max-width: 1160px; margin: 0 auto; padding: 0 16px; }
        .bc-grid   { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; margin-top: 20px; }
        .bc-left   { display: flex; flex-direction: column; gap: 14px; }
        .bc-right  { position: sticky; top: 24px; display: flex; flex-direction: column; gap: 14px; }

        /* ── Card ── */
        .card {
            background: var(--card); border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .card-head {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 18px; border-bottom: 1px solid var(--border);
        }
        .card-icon {
            width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 13px;
        }
        .card-title { font-size: 14px; font-weight: 700; color: var(--text); }
        .card-sub   { font-size: 11px; color: var(--muted); margin-left: auto; }

        /* ── Pill ── */
        .pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 999px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .pill-green  { background: #dcfce7; color: #166534; }
        .pill-blue   { background: #dbeafe; color: #1e40af; }
        .pill-amber  { background: #fef3c7; color: #92400e; }
        .pill-red    { background: #fee2e2; color: #991b1b; }
        .pill-purple { background: #ede9fe; color: #5b21b6; }
        .pill-gray   { background: #f1f5f9; color: #475569; }

        /* ── Animations ── */
        @keyframes fuAnim { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fu { animation: fuAnim .35s ease both; }
        .d1 { animation-delay: .05s; } .d2 { animation-delay: .1s; }
        .d3 { animation-delay: .15s; } .d4 { animation-delay: .2s; }
        .d5 { animation-delay: .25s; }

        /* ══════════════════════════════════
           HEADER
        ══════════════════════════════════ */
        .bc-header {
            background: linear-gradient(135deg, var(--blue2), var(--blue));
            border-radius: 16px;
            padding: 20px 24px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap;
            box-shadow: 0 6px 20px rgba(29,78,216,.2);
        }
        .bc-hd-left { display: flex; align-items: center; gap: 14px; }
        .bc-hd-icon {
            width: 48px; height: 48px; border-radius: 14px;
            background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .bc-hd-title { font-size: 20px; font-weight: 800; color: #fff; }
        .bc-hd-sub   { font-size: 12px; color: rgba(255,255,255,.65); margin-top: 3px; }
        .bc-hd-ref   { text-align: right; }
        .bc-hd-reflabel { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.55); }
        .bc-hd-refcode  { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: .12em; font-family: 'DM Mono', monospace; }

        /* Action buttons */
        .bc-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .bc-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 9px; font-size: 12px; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none; transition: all .15s;
        }
        .bc-btn-dark  { background: #0f172a; color: #fff; }
        .bc-btn-dark:hover  { background: #1e293b; }
        .bc-btn-ghost { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.25); }
        .bc-btn-ghost:hover { background: rgba(255,255,255,.25); }
        .bc-btn-red   { background: rgba(239,68,68,.15); color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
        .bc-btn-red:hover { background: rgba(239,68,68,.25); }
        .bc-btn-green { background: rgba(16,185,129,.15); color: #6ee7b7; border: 1px solid rgba(16,185,129,.3); }
        .bc-btn-green:hover { background: rgba(16,185,129,.25); }

        /* ══════════════════════════════════
           PNR BLOCK
        ══════════════════════════════════ */
        .pnr-block {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            border-radius: var(--radius); padding: 20px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
        }
        .pnr-box {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 11px; padding: 16px;
        }
        .pnr-label  { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; margin-bottom: 8px; }
        .pnr-code   { font-size: 32px; font-weight: 800; letter-spacing: .18em; font-family: 'DM Mono', monospace; color: #fff; }
        .pnr-code-blue { color: #93c5fd; }
        .pnr-sub    { font-size: 11px; color: #64748b; margin-top: 6px; }
        .pnr-meta   { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; margin-top: 12px; }
        .pnr-meta-box {
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
            border-radius: 9px; padding: 10px 12px;
        }
        .pnr-meta-label { font-size: 10px; color: #64748b; margin-bottom: 3px; }
        .pnr-meta-val   { font-size: 12px; font-weight: 700; color: #e2e8f0; font-family: 'DM Mono', monospace; }

        /* ══════════════════════════════════
           STATUS ROW
        ══════════════════════════════════ */
        .status-row {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;
        }
        .status-box {
            background: #f8fafc; border: 1px solid var(--border);
            border-radius: 10px; padding: 12px 14px; text-align: center;
        }
        .status-box-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .status-box-val   { font-size: 13px; font-weight: 700; color: var(--text); }
        .status-box-sub   { font-size: 10px; color: var(--muted); margin-top: 2px; }

        /* ══════════════════════════════════
           ITINERARY
        ══════════════════════════════════ */
        .itin-leg {
            border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 10px;
        }
        .itin-leg:last-child { margin-bottom: 0; }

        .itin-leg-head {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        .itin-leg-badge {
            font-size: 10px; font-weight: 700; padding: 3px 10px;
            border-radius: 999px;
        }
        .itin-leg-route { font-size: 13px; font-weight: 700; color: var(--text); }
        .itin-leg-date  { font-size: 11px; color: var(--muted); margin-left: auto; }

        /* Segment row */
        .seg-row {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .seg-row:last-child { border-bottom: none; }

        /* Airline header */
        .seg-airline {
            display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
        }
        .seg-airline-logo {
            width: 32px; height: 22px; object-fit: contain;
            border: 1px solid var(--border); border-radius: 5px; background: #fff; padding: 2px;
        }
        .seg-airline-name { font-size: 12px; font-weight: 700; color: var(--text); }
        .seg-airline-fn   { font-size: 11px; color: var(--muted); font-family: 'DM Mono', monospace; margin-left: 4px; }
        .seg-badges       { display: flex; flex-wrap: wrap; gap: 4px; margin-left: auto; }

        /* Route strip */
        .seg-route {
            display: flex; align-items: center; gap: 8px;
        }
        .seg-ep { flex-shrink: 0; min-width: 70px; }
        .seg-ep-time { font-size: 22px; font-weight: 800; color: var(--text); line-height: 1; }
        .seg-ep-iata { font-size: 14px; font-weight: 700; color: var(--blue); }
        .seg-ep-date { font-size: 10px; color: var(--muted); margin-top: 1px; }
        .seg-ep-term { font-size: 10px; color: var(--muted); }
        .seg-ep-right { text-align: right; }

        .seg-mid { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; }
        .seg-mid-dur { font-size: 10px; color: var(--muted); font-weight: 600; }
        .seg-mid-line { width: 100%; display: flex; align-items: center; gap: 4px; }
        .seg-mid-line .l { flex: 1; height: 1.5px; background: #cbd5e1; }
        .seg-mid-line i { color: var(--blue); font-size: 12px; }
        .seg-mid-tags { display: flex; flex-wrap: wrap; gap: 3px; justify-content: center; }

        /* Connection pill between segments */
        .seg-connection {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 14px; background: #fffbeb;
            border-top: 1px dashed #fde68a;
            border-bottom: 1px dashed #fde68a;
        }
        .seg-connection-icon { color: var(--amber); font-size: 12px; flex-shrink: 0; }
        .seg-connection-text { font-size: 11px; color: #92400e; font-weight: 600; }
        .seg-connection-sub  { font-size: 10px; color: #a16207; margin-left: auto; }

        /* ══════════════════════════════════
           PASSENGERS TABLE
        ══════════════════════════════════ */
        .pax-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .pax-table th {
            text-align: left; padding: 9px 14px;
            font-size: 10px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .06em;
            background: #f8fafc; border-bottom: 1px solid var(--border);
        }
        .pax-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .pax-table tr:last-child td { border-bottom: none; }
        .pax-table tr:hover td { background: #f8fafc; }
        .pax-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .pax-name-cell { display: flex; align-items: center; gap: 8px; }
        .pax-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .mono { font-family: 'DM Mono', monospace; font-size: 12px; letter-spacing: .05em; }

        /* ══════════════════════════════════
           FARE / RULES TABS
        ══════════════════════════════════ */
        .tabs-bar {
            display: flex; gap: 2px; padding: 10px 14px 0;
            background: #f8fafc; border-bottom: 1px solid var(--border);
        }
        .tab-btn {
            padding: 8px 14px; font-size: 12px; font-weight: 600;
            color: var(--muted); border: none; background: transparent;
            border-bottom: 2px solid transparent; margin-bottom: -1px;
            cursor: pointer; border-radius: 8px 8px 0 0; transition: all .15s;
            font-family: 'DM Sans', sans-serif;
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── Generic table ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .data-table th {
            text-align: left; padding: 8px 14px;
            font-size: 10px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: .06em;
            background: #f8fafc; border-bottom: 1px solid var(--border);
        }
        .data-table td { padding: 10px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #f8fafc; }

        /* ── Price rows (sidebar) ── */
        .pr-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 13px; border-bottom: 1px dashed #f1f5f9; }
        .pr-row:last-child { border-bottom: none; }
        .pr-lbl { color: var(--muted); }
        .pr-val { font-weight: 600; color: var(--text); }
        .pr-sub { background: #f8fafc; margin: 0 -18px; padding: 8px 18px; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .pr-sub .pr-lbl, .pr-sub .pr-val { font-weight: 700; color: var(--text); }
        .pr-total-row { border-top: 2px solid var(--border); padding-top: 12px; margin-top: 4px; }
        .pr-total-row .pr-lbl { font-size: 15px; font-weight: 700; }
        .pr-total-row .pr-val { font-size: 22px; font-weight: 800; color: var(--blue); }

        /* ── Alerts ── */
        .alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 12px; }
        .alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .alert-info { background: var(--blue-lt); border: 1px solid #bfdbfe; color: var(--blue2); }

        /* ── Print modal ── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15,23,42,.65); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center; padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #fff; border-radius: 18px; width: 100%; max-width: 680px; max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; }
        .modal-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); }
        .modal-body { flex: 1; overflow-y: auto; padding: 20px; background: #f8fafc; }
        .modal-foot { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: flex-end; gap: 10px; }

        /* ── Itinerary responsive ── */
        .itin-desktop { padding:16px 18px; overflow-x:auto; }
        .itin-mobile  { display:none; padding:12px 14px; }

        /* Mobile vertical timeline */
        .vtl          { position:relative; padding-left:18px; }
        .vtl-vline    { position:absolute; left:5px; top:6px; bottom:6px; width:2px; background:var(--border); border-radius:1px; }
        .vtl-dot      { position:absolute; left:-15px; top:5px; width:10px; height:10px; border-radius:50%; border:2px solid #fff; z-index:1; }
        .vtl-ap       { position:relative; margin-bottom:0; }
        .vtl-flight   { padding:6px 0; }
        .vtl-flight-inner { background:#f8fafc; border-radius:8px; padding:8px 10px; }
        .vtl-lv       { position:relative; padding:6px 0; }
        .vtl-lv-dot   { position:absolute; left:-15px; top:50%; transform:translateY(-50%); width:10px; height:10px; border-radius:50%; background:#fde68a; border:2px solid #fff; z-index:1; }
        .vtl-lv-inner { display:flex; align-items:center; gap:8px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:7px 10px; }

        .pill-orange { background:#fff7ed; color:#c2410c; }

        @media (max-width: 600px) {
            .itin-desktop { display:none !important; }
            .itin-mobile  { display:block !important; }
        }

        /* ── Responsive ── */
        @media (max-width: 1023px) {
            .bc-grid { grid-template-columns: 1fr; }
            .bc-right { position: static; }
            .pnr-block { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .bc-header { flex-direction: column; align-items: flex-start; }
            .bc-hd-ref { text-align: left; }
            .bc-hd-refcode { font-size: 22px; }
            .status-row { grid-template-columns: 1fr 1fr; }
            .seg-ep-time { font-size: 18px; }
            .pax-table { display: block; overflow-x: auto; }
            .bc-inner { padding: 0 12px; }
        }
        @media print {
            .no-print { display: none !important; }
            header, footer, nav { display: none !important; }
            body { background: #fff !important; }
            .bc-right { position: static !important; }
            .card { box-shadow: none !important; }
        }
    </style>
@endpush

@section('content')
    @php
        $toHM = fn(int $m): string => floor($m/60).'h '.($m%60).'m';

        /* ── Raw data ── */
        $rawData     = $flightData;
        $segments    = $rawData['segments']    ?? [];
        $journeys    = $rawData['journeys']    ?? [];
        $passengers  = $rawData['passengers']  ?? ($rawData['passenger'] ? [$rawData['passenger']] : []);
        $pricing     = $rawData['pricing']     ?? [];
        $fareRules   = $rawData['fare_rules']  ?? [];
        $specialSvc  = $rawData['special_services'] ?? [];
        $actionStatus= $rawData['action_status'] ?? [];
        $agencyInfo  = $rawData['agency_info'] ?? [];
        $provRes     = $rawData['provider_reservation'] ?? [];
        $supplierLoc = $rawData['supplier_locator'] ?? [];

        /* ── PNR ── */
        $pnrGDS     = $rawData['air_reservation']['locator_code'] ?? ($rawData['universal_record']['locator_code'] ?? null);
        $pnrAirline = $supplierLoc['locator_code'] ?? null;
        $airCode    = $supplierLoc['supplier_code'] ?? null;
        $provCode   = $provRes['provider_code'] ?? '1G';
        $tauDate    = $actionStatus['ticket_date'] ?? null;

        /* ── Booking fees ── */
        $baseFee    = (float)($booking->base_fee      ?? 0);
        $taxFee     = (float)($booking->total_fee     ?? 0);
        $serviceFee = (float)($booking->ticketing_fee ?? 0);
        $aitFee     = (float)($booking->supplier_fee  ?? 0);
        $flightDisc = (float)($booking->flight_discount  ?? 0);
        $segDisc    = (float)($booking->segment_discount ?? 0);
        $totalDisc  = $flightDisc + $segDisc;
        $bookTotal  = (float)($booking->total ?? 0);
        $walletUsed = (float)($booking->wallet_credit_used ?? 0);
        $penaltyAmt = (float)($booking->penalty_amount ?? 0);

        /* ── Pricing ── */
        $grandTotal      = $pricing['grand_total']       ?? [];
        $fareBreakdowns  = $pricing['fare_breakdowns']   ?? [];
        $checkedBag      = $pricing['checked_bag_kg']    ?? null;
        $cabinBag        = $pricing['cabin_bag_kg']      ?? null;
        $bagCharges      = $pricing['checked_baggage_charges'] ?? [];

        /* ── Group segments by journey ── */
        /*
         * Segments কে journey-তে assign করার সঠিক পদ্ধতি:
         * journey.first_airport_code দিয়ে match করো, date নয়।
         * কারণ timezone offset এর কারণে date mismatch হতে পারে।
         *
         * Logic:
         *  - journey[0]: first_airport_code = DAC → DAC থেকে শুরু হওয়া segments
         *  - journey[1]: first_airport_code = DXB → DXB থেকে শুরু হওয়া segments
         *  - প্রতিটা journey-তে number_of_flights টা segment নেওয়া হবে
         */
        $legGroups = [];
        foreach ($journeys as $ji => $journey) {
            $legGroups[$ji] = [
                'journey'  => $journey,
                'segments' => [],
                'type'     => $ji === 0 ? 'outbound' : 'return',
            ];
        }

        // segments কে travel_order দিয়ে sort
        usort($segments, fn($a,$b) => ($a['travel_order']??0) - ($b['travel_order']??0));

        // প্রতিটা journey-তে number_of_flights টা segment assign করো — sequential
        $segmentPool = $segments;
        foreach ($legGroups as $ji => &$legGroup) {
            $needed = $legGroup['journey']['number_of_flights'] ?? 1;
            $legGroup['segments'] = array_splice($segmentPool, 0, $needed);
        }
        unset($legGroup);

        /* ── layover calculation ── */
        function calcLayover($seg1, $seg2) {
            $a = strtotime($seg1['arrival_time']);
            $d = strtotime($seg2['departure_time']);
            $min = (int)(($d - $a) / 60);
            return [
                'airport'  => $seg1['destination'],
                'duration' => floor($min/60).'h '.($min%60).'m',
                'arr_term' => $seg1['arrival_terminal']   ?? null,
                'dep_term' => $seg2['departure_terminal'] ?? null,
                'overnight'=> date('Y-m-d',$a) !== date('Y-m-d',$d),
            ];
        }

                    $rawTime = function(string $dt): string {
                        preg_match('/T(\d{2}:\d{2})/', $dt, $m);
                        return $m[1] ?? '00:00';
                    };
                    $rawDate = function(string $dt): string {
                        preg_match('/^(\d{4}-\d{2}-\d{2})/', $dt, $m);
                        return !empty($m[1]) ? date('D d M', strtotime($m[1])) : '';
                    };
                    $rawDateFull = function(string $dt): string {
                        preg_match('/^(\d{4}-\d{2}-\d{2})/', $dt, $m);
                        return !empty($m[1]) ? date('D, d M Y', strtotime($m[1])) : '';
                    };

        $paxLabel = ['ADT'=>'Adult','CNN'=>'Child','CHD'=>'Child','C07'=>'Child','INF'=>'Infant'];
        $taxLabels = [
            'BD'=>'Airport Development Fee','UT'=>'Travel Tax','OW'=>'Security Charge',
            'E5'=>'Embarkation Fee','BH'=>'Fuel Surcharge','HM'=>'IATA Misc Charge',
            'ZR'=>'Security Service Fee','P7'=>'Pax Service Charge (Dep)',
            'P8'=>'Pax Service Charge (Arr)','YQ'=>'Fuel Surcharge','YR'=>'Carrier Surcharge',
            'AE'=>'Passenger Service Charge','TP'=>'Security Fee','F6'=>'Facility Charge',
        ];
    @endphp

    <div class="bc-wrap">
        <div class="bc-inner">

            {{-- ── Header ── --}}
            <div class="bc-header fu d1">
                <div class="bc-hd-left">
                    <div class="bc-hd-icon">✅</div>
                    <div>
                        <div class="bc-hd-title">Booking Confirmed!</div>
                        <div class="bc-hd-sub">
                            Your reservation has been successfully created.
                            @if($tauDate)
                                &nbsp;·&nbsp;
                                <span style="color:#fde68a;font-weight:700">
                            <i class="fa fa-clock"></i>
                            Ticket by {{ \Carbon\Carbon::parse($tauDate)->format('d M Y, H:i') }}
                        </span>
                            @endif
                        </div>
                        <div class="bc-actions no-print">
                            <button onclick="document.getElementById('printModal').classList.add('open');document.body.style.overflow='hidden'" class="bc-btn bc-btn-dark">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <a href="{{ route('user.booking_history') }}" class="bc-btn bc-btn-ghost">
                                <i class="fa fa-list"></i> My Bookings
                            </a>
                            <a href="{{ route('home') }}" class="bc-btn bc-btn-ghost">
                                <i class="fa fa-home"></i> Home
                            </a>
                            @if($booking->status === 'booked')
                                <a href="{{ route('booking.cancel', $booking->id) }}"
                                   onclick="return confirm('Cancel this booking?')"
                                   class="bc-btn bc-btn-red">
                                    <i class="fa fa-times-circle"></i> Cancel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bc-hd-ref">
                    <div class="bc-hd-reflabel">Booking Ref</div>
                    <div class="bc-hd-refcode">{{ $booking->code }}</div>
                </div>
            </div>

            {{-- ── Main Grid ── --}}
            <div class="bc-grid">

                {{-- ════ LEFT ════ --}}
                <div class="bc-left">

                    {{-- 1. Status ──────────────── --}}
                    <div class="card fu d1">
                        <div class="card-head">
                            <div class="card-icon" style="background:#f0fdf4;color:#16a34a"><i class="fa fa-circle-check"></i></div>
                            <div class="card-title">Booking Status</div>
                        </div>
                        <div style="padding:14px 18px">
                            <div class="status-row">
                                <div class="status-box">
                                    <div class="status-box-label">Status</div>
                                    <span class="pill {{ in_array($booking->status??'',['confirmed','booked']) ? 'pill-green' : 'pill-amber' }}">
                                <i class="fa fa-circle" style="font-size:5px"></i>
                                {{ ucfirst($booking->status ?? 'Pending') }}
                            </span>
                                </div>
                                <div class="status-box">
                                    <div class="status-box-label">Payment</div>
                                    <span class="pill {{ !empty($booking->is_paid) ? 'pill-green' : 'pill-amber' }}">
                                <i class="fa fa-circle" style="font-size:5px"></i>
                                {{ !empty($booking->is_paid) ? 'Paid' : 'Pending' }}
                            </span>
                                </div>
                                <div class="status-box">
                                    <div class="status-box-label">Booked On</div>
                                    <div class="status-box-val">{{ isset($booking->created_at) ? date('d M Y', strtotime($booking->created_at)) : '—' }}</div>
                                    <div class="status-box-sub">{{ isset($booking->created_at) ? date('h:i A', strtotime($booking->created_at)) : '' }}</div>
                                </div>
                                @if($tauDate)
                                    <div class="status-box" style="background:#fef2f2;border-color:#fecaca">
                                        <div class="status-box-label" style="color:#dc2626">⏰ Ticket Deadline</div>
                                        <div class="status-box-val" style="color:#dc2626">{{ \Carbon\Carbon::parse($tauDate)->format('d M Y') }}</div>
                                        <div class="status-box-sub" style="color:#ef4444">{{ \Carbon\Carbon::parse($tauDate)->format('h:i A') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- 2. PNR ──────────────────── --}}
                    <div class="pnr-block fu d2">
                        <div class="pnr-box">
{{--                            <div class="pnr-label">GDS Locator · {{ $provCode }}</div>--}}
                            <div class="pnr-label">GDS Locator </div>
                            <div class="pnr-code">{{ $pnrGDS ?? '—' }}</div>
                            <div class="pnr-sub">Universal Record Locator</div>
                        </div>
                        @if($pnrAirline)
                            <div class="pnr-box">
                                <div class="pnr-label">Airline PNR · {{ $airCode }}</div>
                                <div class="pnr-code pnr-code-blue">{{ $pnrAirline }}</div>
                                <div class="pnr-sub">Use at airport check-in</div>
                            </div>
                        @endif
                        @if(!empty($provRes['owning_pcc']) || !empty($agencyInfo['agent_code']) || !empty($rawData['timestamp']))
                            <div class="pnr-meta" style="grid-column: 1 / -1">
{{--                                @if(!empty($provRes['owning_pcc']))--}}
{{--                                    <div class="pnr-meta-box">--}}
{{--                                        <div class="pnr-meta-label">PCC / Office</div>--}}
{{--                                        <div class="pnr-meta-val">{{ $provRes['owning_pcc'] }}</div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                                @if(!empty($provRes['prime_host_id']))--}}
{{--                                    <div class="pnr-meta-box">--}}
{{--                                        <div class="pnr-meta-label">Host ID</div>--}}
{{--                                        <div class="pnr-meta-val">{{ $provRes['prime_host_id'] }}</div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                                @if(!empty($agencyInfo['agent_code']))--}}
{{--                                    <div class="pnr-meta-box">--}}
{{--                                        <div class="pnr-meta-label">Agent Sine</div>--}}
{{--                                        <div class="pnr-meta-val">{{ $agencyInfo['agent_code'] }}</div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                                @if(!empty($rawData['timestamp']))--}}
{{--                                    <div class="pnr-meta-box">--}}
{{--                                        <div class="pnr-meta-label">Booked at</div>--}}
{{--                                        <div class="pnr-meta-val" style="font-size:11px">{{ date('d M Y, H:i', strtotime($rawData['timestamp'])) }}</div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
                            </div>
                        @endif
                    </div>

                    {{-- 3. Itinerary — per leg, responsive ──────── --}}
                    @foreach($legGroups as $li => $legGroup)
                        @php
                            $journey  = $legGroup['journey'];
                            $legSegs  = $legGroup['segments'];
                            $legType  = $legGroup['type'];
                            $isOut    = $legType === 'outbound';
                            $firstSeg = $legSegs[0]       ?? null;
                            $lastSeg  = end($legSegs) ?: null;
                            if(empty($legSegs) || !$firstSeg) continue;

                            $legDepTs   = strtotime($firstSeg['departure_time']);
                            $legArrTs   = strtotime($lastSeg['arrival_time']);
                            $legMinutes = (int)(($legArrTs - $legDepTs) / 60);
                            $legDur     = floor($legMinutes/60).'h '.($legMinutes%60).'m';

                            $legBag = $pricing['per_leg_baggage'][$li] ?? null;
                            $fcBag  = null;
                            foreach($fareBreakdowns as $fb) {
                                if(isset($fb['fare_construction'][$li])) { $fcBag = $fb['fare_construction'][$li]; break; }
                            }
                            $thisCheckedBag = $legBag['checked_bag_kg'] ?? $fcBag['checked_bag_kg'] ?? $checkedBag;
                            $thisCabinBag   = $legBag['cabin_bag_kg']   ?? $fcBag['cabin_bag_kg']   ?? $cabinBag;
                            $thisFareBasis  = $fcBag['fare_basis']      ?? null;
                            $thisBrandName  = $fcBag['brand_fare_name'] ?? null;

                            $accentColor = $isOut ? '#1d4ed8' : '#7c3aed';
                            $accentLight = $isOut ? '#eff6ff'  : '#f5f3ff';
                            $destColor   = $isOut ? '#6366f1'  : '#a78bfa';
                            $lineGrad    = $isOut ? '#1d4ed8,#6366f1' : '#7c3aed,#a78bfa';
                        @endphp

                        <div class="card fu d2">
                            {{-- Card header --}}
                            <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap">
                                <div style="width:30px;height:30px;border-radius:8px;background:{{ $accentLight }};color:{{ $accentColor }};display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0">
                                    <i class="fa fa-{{ $isOut ? 'plane-departure' : 'plane-arrival' }}"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:13px;font-weight:700;color:var(--text)">
                                        <span style="color:{{ $accentColor }}">{{ $isOut ? '→ Outbound' : '← Return' }}</span>
                                        <span style="color:var(--muted);font-weight:400;margin-left:6px">{{ $journey['first_airport_code'] }} → {{ $journey['last_airport_code'] }}</span>
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px">
                                        {{ date('D, d M Y', strtotime($journey['departure_date'])) }}
                                        · {{ count($legSegs) }} flight{{ count($legSegs)>1?'s':'' }}
                                        · {{ $legDur }} total
                                    </div>
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center">
                                    @if($thisCheckedBag)
                                        <span class="pill pill-purple" style="font-size:11px"><i class="fa fa-suitcase" style="font-size:9px"></i> {{ $thisCheckedBag }}kg</span>
                                    @endif
                                    @if($thisCabinBag)
                                        <span class="pill pill-purple" style="font-size:11px"><i class="fa fa-briefcase" style="font-size:9px"></i> {{ $thisCabinBag }}kg</span>
                                    @endif
                                    @if($thisFareBasis)
                                        <span class="pill pill-gray mono" style="font-size:10px">{{ $thisFareBasis }}</span>
                                    @endif
                                    @if($thisBrandName)
                                        <span class="pill pill-purple" style="font-size:10px">{{ $thisBrandName }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- ══ DESKTOP: Horizontal timeline (>=600px) ══ --}}
                            <div class="itin-desktop">
                                <div style="display:flex;align-items:center;gap:0;min-width:300px">
                                    @foreach($legSegs as $si => $seg)
                                        @php
                                            $isLastSeg = $si === count($legSegs)-1;
                                            $acName = $seg['aircraft_name'] ?? '';
                                            if(strlen($acName) <= 4 && ctype_alnum($acName)) $acName = '';
                                            $lv = null;
                                            if(!$isLastSeg) {
                                                $nextSeg = $legSegs[$si+1];
                                                $aTs = strtotime($seg['arrival_time']);
                                                $dTs = strtotime($nextSeg['departure_time']);
                                                $lvMin = (int)(($dTs-$aTs)/60);

                                                $lv = [
                                                    'airport'   => $seg['destination'],
                                                    'arr_time'  => $rawTime($seg['arrival_time']),
                                                    'arr_date'  => $rawDate($seg['arrival_time']),
                                                    'dep_time'  => $rawTime($nextSeg['departure_time']),
                                                    'dep_date'  => $rawDate($nextSeg['departure_time']),
                                                    'duration'  => floor($lvMin/60).'h '.($lvMin%60).'m',
                                                    'arr_term'  => $seg['arrival_terminal']      ?? null,
                                                    'dep_term'  => $nextSeg['departure_terminal'] ?? null,
                                                    'overnight' => substr($seg['arrival_time'],0,10) !== substr($nextSeg['departure_time'],0,10),
                                                ];
                                            }
                                        @endphp

                                        {{-- Origin airport (first seg only) --}}
                                        @if($si===0)
                                            <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                {{-- NEW --}}
                                                <div style="font-size:11px;font-weight:600;color:var(--muted)">{{ $rawTime($seg['departure_time']) }}</div>
                                                <div style="font-size:22px;font-weight:800;color:{{ $accentColor }};line-height:1.1">{{ $seg['origin'] }}</div>
                                                <div style="font-size:10px;color:var(--muted)">{{ $rawDate($seg['departure_time']) }}</div>
                                                @if(!empty($seg['departure_terminal']))
                                                    <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:var(--muted)">T{{ $seg['departure_terminal'] }}</div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Flight tube --}}
                                        <div style="flex:1;display:flex;flex-direction:column;padding:0 4px;min-width:130px">
                                            <div style="display:flex;align-items:center;margin-top:18px">
                                                <div style="width:8px;height:8px;border-radius:50%;background:{{ $si===0 ? $accentColor : '#fde68a' }};flex-shrink:0"></div>
                                                <div style="flex:1;height:2px;background:linear-gradient(90deg,{{ $lineGrad }});position:relative">
                                                    <span style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:0 4px;font-size:11px;color:{{ $accentColor }}">✈</span>
                                                </div>
                                                <div style="width:8px;height:8px;border-radius:50%;background:{{ $isLastSeg ? $destColor : '#fde68a' }};flex-shrink:0"></div>
                                            </div>
                                            <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:5px;justify-content:center">
                                                <span class="pill pill-gray mono" style="font-size:10px">{{ $seg['carrier'] }}{{ $seg['flight_number'] }}</span>
                                                <span class="pill pill-blue" style="font-size:10px">{{ $seg['cabin_class'] }}</span>
                                                <span class="pill pill-gray" style="font-size:10px">{{ $seg['class_of_service'] }}</span>
                                                <span class="pill pill-gray" style="font-size:10px">{{ $toHM($seg['travel_time']) }}</span>
                                                @if($acName)<span class="pill pill-gray" style="font-size:10px">{{ $acName }}</span>@endif
                                                @if(!empty($seg['meals']))
                                                    @foreach($seg['meals'] as $meal)
                                                        <span class="pill pill-orange" style="font-size:10px"><i class="fa fa-utensils" style="font-size:8px"></i> {{ $meal['description']??$meal['code'] }}</span>
                                                    @endforeach
                                                @endif
                                                <span class="pill {{ $seg['status']==='HK' ? 'pill-green' : 'pill-amber' }}" style="font-size:10px">{{ $seg['status_name']??$seg['status'] }}</span>
                                            </div>
                                        </div>

                                        {{-- Layover node OR destination --}}
                                        @if($lv)
                                            <div style="flex-shrink:0;text-align:center;min-width:76px">
                                                <div style="font-size:10px;font-weight:600;color:var(--muted)">{{ $lv['arr_time'] }}</div>
                                                <div style="font-size:18px;font-weight:800;color:#92400e;line-height:1.1">{{ $lv['airport'] }}</div>
                                                <div style="font-size:10px;color:var(--muted)">{{ $lv['arr_date'] }}</div>
                                                @if($lv['arr_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;color:var(--muted)">Arr T{{ $lv['arr_term'] }}</div>@endif
                                                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:3px 8px;margin:4px 0;display:inline-block">
                                                    <div style="font-size:10px;font-weight:700;color:#d97706"><i class="fa fa-clock" style="font-size:9px"></i> {{ $lv['duration'] }}</div>
                                                    @if($lv['overnight'])<div style="font-size:9px;font-weight:700;color:#dc2626">Overnight</div>@endif
                                                </div>
                                                <div style="font-size:10px;font-weight:600;color:var(--muted)">{{ $lv['dep_time'] }}</div>
                                                <div style="font-size:10px;color:var(--muted)">{{ $lv['dep_date'] }}</div>
                                                @if($lv['dep_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;color:var(--muted)">Dep T{{ $lv['dep_term'] }}</div>@endif
                                            </div>
                                        @else
                                            <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                {{-- NEW --}}
                                                <div style="font-size:11px;font-weight:600;color:var(--muted)">{{ $rawTime($seg['arrival_time']) }}</div>
                                                <div style="font-size:22px;font-weight:800;color:{{ $destColor }};line-height:1.1">{{ $seg['destination'] }}</div>
                                                <div style="font-size:10px;color:var(--muted)">{{ $rawDate($seg['arrival_time']) }}</div>
                                                @if(!empty($seg['arrival_terminal']))
                                                    <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:var(--muted)">T{{ $seg['arrival_terminal'] }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            {{-- ══ MOBILE: Vertical timeline (<600px) ══ --}}
                            <div class="itin-mobile">
                                <div class="vtl">
                                    <div class="vtl-vline"></div>
                                    @foreach($legSegs as $si => $seg)
                                        @php
                                            $isLastSeg = $si === count($legSegs)-1;
                                            $acName = $seg['aircraft_name'] ?? '';
                                            if(strlen($acName) <= 4 && ctype_alnum($acName)) $acName = '';
                                            $lv = null;
                                            if(!$isLastSeg) {
                                                $nextSeg = $legSegs[$si+1];
                                                $aTs = strtotime($seg['arrival_time']);
                                                $dTs = strtotime($nextSeg['departure_time']);
                                                $lvMin = (int)(($dTs-$aTs)/60);

                                                $lv = [
                                                    'airport'   => $seg['destination'],
                                                    'arr_time'  => $rawTime($seg['arrival_time']),
                                                    'dep_time'  => $rawTime($nextSeg['departure_time']),
                                                    'arr_date'  => $rawDate($seg['arrival_time']),
                                                    'dep_date'  => $rawDate($nextSeg['departure_time']),
                                                    'duration'  => floor($lvMin/60).'h '.($lvMin%60).'m',
                                                    'arr_term'  => $seg['arrival_terminal']      ?? null,
                                                    'dep_term'  => $nextSeg['departure_terminal'] ?? null,
                                                    'overnight' => substr($seg['arrival_time'],0,10) !== substr($nextSeg['departure_time'],0,10),
                                                ];
                                            }
                                        @endphp

                                        @if($si===0)
                                            <div class="vtl-ap">
                                                <div class="vtl-dot" style="background:{{ $accentColor }}"></div>
                                                <div>
                                                    <div style="display:flex;align-items:baseline;gap:6px">
                                                        <div style="font-size:20px;font-weight:700;color:{{ $accentColor }};line-height:1">{{ $seg['origin'] }}</div>
                                                        {{-- NEW --}}
                                                        <div style="font-size:12px;font-weight:500;color:var(--muted)">{{ $rawTime($seg['departure_time']) }}</div>
                                                    </div>
                                                    <div style="font-size:10px;color:var(--muted);margin-top:1px">{{ $rawDateFull($seg['departure_time']) }}{{ !empty($seg['departure_terminal']) ? ' · T'.$seg['departure_terminal'] : '' }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="vtl-flight">
                                            <div class="vtl-flight-inner">
                                                <div style="font-size:11px;font-weight:600;color:var(--text);margin-bottom:5px">
                                                    <span style="color:{{ $accentColor }}">✈</span>
                                                    {{ $seg['carrier'] }}{{ $seg['flight_number'] }} · {{ $seg['airline_name'] ?? $seg['carrier'] }}
                                                </div>
                                                <div style="display:flex;flex-wrap:wrap;gap:3px">
                                                    <span class="pill pill-blue" style="font-size:10px">{{ $seg['cabin_class'] }}</span>
                                                    <span class="pill pill-gray" style="font-size:10px">{{ $seg['class_of_service'] }} · {{ $toHM($seg['travel_time']) }}</span>
                                                    @if($acName)<span class="pill pill-gray" style="font-size:10px">{{ $acName }}</span>@endif
                                                    @if(!empty($seg['meals']))
                                                        @foreach($seg['meals'] as $meal)
                                                            <span class="pill pill-orange" style="font-size:10px"><i class="fa fa-utensils" style="font-size:8px"></i> {{ $meal['description']??$meal['code'] }}</span>
                                                        @endforeach
                                                    @endif
                                                    <span class="pill {{ $seg['status']==='HK' ? 'pill-green' : 'pill-amber' }}" style="font-size:10px">{{ $seg['status_name']??$seg['status'] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($lv)
                                            <div class="vtl-lv">
                                                <div class="vtl-lv-dot"></div>
                                                <div class="vtl-lv-inner">
                                                    <div style="font-size:16px;font-weight:700;color:#92400e">{{ $lv['airport'] }}</div>
                                                    <div style="flex:1">
                                                        <div style="display:flex;align-items:center;gap:5px">
                                                            <div style="font-size:11px;font-weight:600;color:#d97706"><i class="fa fa-clock" style="font-size:9px"></i> {{ $lv['duration'] }} layover</div>
                                                            @if($lv['overnight'])<span style="font-size:9px;font-weight:600;color:#dc2626;background:#fee2e2;border-radius:4px;padding:1px 5px">Overnight</span>@endif
                                                        </div>
                                                        <div style="font-size:10px;color:#a16207;margin-top:1px">Arr {{ $lv['arr_time'] }} {{ $lv['arr_date'] }}@if($lv['arr_term']) · T{{ $lv['arr_term'] }}@endif · Dep {{ $lv['dep_time'] }}@if($lv['dep_term']) T{{ $lv['dep_term'] }}@endif</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($isLastSeg)
                                            <div class="vtl-ap">
                                                <div class="vtl-dot" style="background:{{ $destColor }}"></div>
                                                <div>
                                                    <div style="display:flex;align-items:baseline;gap:6px">
                                                        <div style="font-size:20px;font-weight:700;color:{{ $destColor }};line-height:1">{{ $seg['destination'] }}</div>
                                                        {{-- NEW --}}
                                                        <div style="font-size:12px;font-weight:500;color:var(--muted)">{{ $rawTime($seg['arrival_time']) }}</div>
                                                    </div>
                                                    <div style="font-size:10px;color:var(--muted);margin-top:1px">{{ $rawDateFull($seg['arrival_time']) }}{{ !empty($seg['arrival_terminal']) ? ' · T'.$seg['arrival_terminal'] : '' }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>{{-- /card --}}
                    @endforeach

                    {{-- 4. Passengers ────────────── --}}
                    @if(!empty($passengers))
                        <div class="card fu d3">
                            <div class="card-head">
                                <div class="card-icon" style="background:#ede9fe;color:#7c3aed"><i class="fa fa-users"></i></div>
                                <div class="card-title">Passengers</div>
                                <div class="card-sub">{{ count($passengers) }} pax</div>
                            </div>
                            <div style="overflow-x:auto">
                                <table class="pax-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Passenger</th>
                                        <th>Type</th>
                                        <th>DOB</th>
                                        <th>Passport</th>
                                        <th>Expiry</th>
                                        <th>Nationality</th>
                                        <th>Contact</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($passengers as $pi => $pax)
                                        @php
                                            $fn      = trim($pax['first_name'] ?? '');
                                            $ln      = trim($pax['last_name']  ?? '');
                                            $prefix  = trim($pax['prefix']     ?? '');
                                            $name    = trim(($prefix ? $prefix.' ' : '').$fn.' '.$ln) ?: 'Passenger '.($pi+1);
                                            $initial = strtoupper(substr($fn ?: $ln ?: 'P', 0, 1));
                                            $type    = $pax['traveler_type'] ?? 'ADT';
                                            $isAdult = $type === 'ADT';
                                            $isChild = in_array($type, ['CNN','CHD','C07']);
                                            $isInfant= $type === 'INF';
                                            $avatarColor = $isAdult ? '#1d4ed8' : ($isChild ? '#059669' : '#7c3aed');
                                            $typePill    = $isAdult ? 'pill-blue' : ($isChild ? 'pill-green' : 'pill-purple');
                                            $gender  = $pax['gender'] ?? '';
                                        @endphp
                                        <tr>
                                            <td style="color:var(--muted);font-size:11px">{{ $pi+1 }}</td>
                                            <td>
                                                <div class="pax-name-cell">
                                                    <div class="pax-avatar" style="background:{{ $avatarColor }}">{{ $initial }}</div>
                                                    <div>
                                                        <div class="pax-name">{{ $name }}</div>
                                                        <div style="font-size:10px;color:var(--muted)">
                                                            <i class="fa fa-{{ $gender==='F' ? 'venus' : 'mars' }}"></i>
                                                            {{ $gender==='F' ? 'Female' : 'Male' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="pill {{ $typePill }}">{{ $paxLabel[$type] ?? $type }}</span></td>
                                            <td style="font-size:12px;color:var(--muted)">{{ !empty($pax['dob']) ? date('d M Y', strtotime($pax['dob'])) : '—' }}</td>
                                            <td><span class="mono">{{ $pax['passport_number'] ?? '—' }}</span></td>
                                            <td style="font-size:12px">
                                                @if(!empty($pax['passport_expiry']))
                                                    @php $exp = strtotime($pax['passport_expiry']); @endphp
                                                    <span style="color:{{ $exp < strtotime('+6 months') ? '#dc2626' : 'var(--muted)' }};font-weight:{{ $exp < strtotime('+6 months') ? '700' : '400' }}">
                                                    {{ date('d M Y', $exp) }}
                                                </span>
                                                @else —
                                                @endif
                                            </td>
                                            <td style="font-size:12px;color:var(--muted)">{{ $pax['nationality'] ?? $pax['passport_country'] ?? '—' }}</td>
                                            <td style="font-size:11px;color:var(--muted)">
                                                @if(!empty($pax['email']))<div>{{ $pax['email'] }}</div>@endif
                                                @if(!empty($pax['phone']))<div>{{ $pax['phone'] }}</div>@endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 5. Fare / Rules / SSR tabs ─── --}}
                    @if(!empty($fareBreakdowns) || !empty($fareRules) || !empty($specialSvc))
                        <div class="card fu d4">
                            <div class="tabs-bar">
                                @if(!empty($fareBreakdowns))
                                    <button class="tab-btn active" onclick="switchTab('fare',this)">
                                        <i class="fa fa-receipt"></i> Fare Details
                                    </button>
                                @endif
                                @if(!empty($fareRules))
                                    <button class="tab-btn {{ empty($fareBreakdowns)?'active':'' }}" onclick="switchTab('rules',this)">
                                        <i class="fa fa-gavel"></i> Fare Rules
                                    </button>
                                @endif
                                @if(!empty($specialSvc))
                                    <button class="tab-btn" onclick="switchTab('ssr',this)">
                                        <i class="fa fa-concierge-bell"></i> SSR
                                        <span class="pill pill-blue" style="margin-left:3px">{{ count($specialSvc) }}</span>
                                    </button>
                                @endif
                                @if(!empty($bagCharges))
                                    <button class="tab-btn" onclick="switchTab('baggage',this)">
                                        <i class="fa fa-suitcase"></i> Extra Baggage
                                    </button>
                                @endif
                            </div>

                            {{-- Fare Details --}}
{{--                            @if(!empty($fareBreakdowns))--}}
{{--                                <div id="tab-fare" class="tab-panel active">--}}
{{--                                    @foreach($fareBreakdowns as $fbIdx => $fb)--}}
{{--                                        <div style="padding:14px 18px;border-bottom:1px solid var(--border);background:{{ $fbIdx%2===0 ? '#fff' : '#f8fafc' }}">--}}
{{--                                            --}}{{-- Pax type header --}}
{{--                                            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px">--}}
{{--                                                <span class="pill pill-purple">{{ $paxLabel[$fb['traveler_type']] ?? $fb['traveler_type'] }}</span>--}}
{{--                                                @if(!empty($fb['validating_carrier']))--}}
{{--                                                    <span class="pill pill-blue">Validating: {{ $fb['validating_carrier'] }}</span>--}}
{{--                                                @endif--}}
{{--                                                <span class="pill {{ $fb['pricing_status_code']==='A' ? 'pill-green' : 'pill-amber' }}">{{ $fb['pricing_status_name'] ?? $fb['pricing_status_code'] }}</span>--}}
{{--                                                @if(!empty($fb['fare_construction'][0]['brand_fare_name']))--}}
{{--                                                    <span class="pill pill-gray">{{ $fb['fare_construction'][0]['brand_fare_name'] }}</span>--}}
{{--                                                @endif--}}
{{--                                                <span style="margin-left:auto;font-size:13px;font-weight:700;color:var(--blue)">--}}
{{--                                            {{ $fb['currency'] }} {{ number_format((float)$fb['total']) }}--}}
{{--                                        </span>--}}
{{--                                            </div>--}}
{{--                                            --}}{{-- 3 boxes --}}
{{--                                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px">--}}
{{--                                                <div style="background:#f8fafc;border:1px solid var(--border);border-radius:9px;padding:10px;text-align:center">--}}
{{--                                                    <div style="font-size:10px;color:var(--muted);margin-bottom:3px">Base Fare</div>--}}
{{--                                                    <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $fb['currency'] }} {{ number_format((float)$fb['subtotal']) }}</div>--}}
{{--                                                </div>--}}
{{--                                                <div style="background:#f8fafc;border:1px solid var(--border);border-radius:9px;padding:10px;text-align:center">--}}
{{--                                                    <div style="font-size:10px;color:var(--muted);margin-bottom:3px">Taxes</div>--}}
{{--                                                    <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $fb['currency'] }} {{ number_format((float)$fb['taxes']) }}</div>--}}
{{--                                                </div>--}}
{{--                                                <div style="background:var(--blue-lt);border:1px solid #bfdbfe;border-radius:9px;padding:10px;text-align:center">--}}
{{--                                                    <div style="font-size:10px;color:var(--blue);margin-bottom:3px">Total</div>--}}
{{--                                                    <div style="font-size:14px;font-weight:800;color:var(--blue)">{{ $fb['currency'] }} {{ number_format((float)$fb['total']) }}</div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            --}}{{-- Tax breakdown --}}
{{--                                            @if(!empty($fb['tax_breakdown']))--}}
{{--                                                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Tax Breakdown</div>--}}
{{--                                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:4px">--}}
{{--                                                    @foreach($fb['tax_breakdown'] as $tx)--}}
{{--                                                        <div style="display:flex;justify-content:space-between;align-items:center;padding:5px 8px;background:#f8fafc;border-radius:6px;font-size:11px">--}}
{{--                                                            <div style="display:flex;align-items:center;gap:6px">--}}
{{--                                                                <span class="mono" style="background:#ede9fe;color:#5b21b6;padding:1px 6px;border-radius:4px;font-size:10px">{{ $tx['code'] }}</span>--}}
{{--                                                                <span style="color:var(--muted)">{{ $taxLabels[$tx['code']] ?? 'Misc' }}</span>--}}
{{--                                                            </div>--}}
{{--                                                            <span style="font-weight:600;color:var(--text)">{{ $tx['currency'] }} {{ number_format((float)$tx['amount']) }}</span>--}}
{{--                                                        </div>--}}
{{--                                                    @endforeach--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
{{--                                            --}}{{-- Fare calc string --}}
{{--                                            @if(!empty($fb['fare_calculation']))--}}
{{--                                                <div style="margin-top:10px;background:#1e293b;border-radius:8px;padding:10px 12px">--}}
{{--                                                    <div style="font-size:10px;color:#64748b;margin-bottom:4px">Fare Calculation</div>--}}
{{--                                                    <div style="font-size:11px;color:#86efac;font-family:'DM Mono',monospace;word-break:break-all;line-height:1.5">{{ $fb['fare_calculation'] }}</div>--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
{{--                                </div>--}}
{{--                            @endif--}}


                            @if(!empty($fareBreakdowns))
                                <div id="tab-fare" class="tab-panel active">

                                    {{-- ── Pax pill tab bar ── --}}
                                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:12px 16px;border-bottom:1px solid var(--border);background:#f8fafc">
                                        <div style="display:flex;gap:4px;flex-wrap:wrap" id="fareTabBar">
                                            @foreach($fareBreakdowns as $fbIdx => $fb)
                                                @php
                                                    $pType  = $fb['traveler_type'] ?? 'ADT';
                                                    $pLabel = $paxLabel[$pType] ?? $pType;
                                                    $pTotal = number_format((float)$fb['total']);
                                                    $pCurr  = $fb['currency'] ?? 'BDT';
                                                    $pillBg = $pType === 'ADT' ? '#dbeafe' : ($pType === 'INF' ? '#ede9fe' : '#dcfce7');
                                                    $pillCo = $pType === 'ADT' ? '#1e40af' : ($pType === 'INF' ? '#5b21b6' : '#166534');
                                                    $activeBg = $pType === 'ADT' ? 'var(--blue)' : ($pType === 'INF' ? '#7c3aed' : '#059669');
                                                @endphp
                                                <button
                                                    onclick="switchFareTab({{ $fbIdx }}, this)"
                                                    id="fareTabBtn{{ $fbIdx }}"
                                                    style="
                            display:inline-flex;align-items:center;gap:5px;
                            padding:6px 12px;border-radius:999px;border:1.5px solid {{ $fbIdx===0 ? $activeBg : 'var(--border)' }};
                            background:{{ $fbIdx===0 ? $activeBg : '#fff' }};
                            color:{{ $fbIdx===0 ? '#fff' : 'var(--muted)' }};
                            font-size:12px;font-weight:700;cursor:pointer;
                            font-family:'DM Sans',sans-serif;transition:all .15s;
                        "
                                                >
                                                    {{ $pLabel }}
                                                    <span style="
                            background:{{ $fbIdx===0 ? 'rgba(255,255,255,.25)' : $pillBg }};
                            color:{{ $fbIdx===0 ? '#fff' : $pillCo }};
                            font-size:10px;font-weight:700;
                            padding:1px 6px;border-radius:999px;
                        ">{{ $pCurr }} {{ $pTotal }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                        {{-- Grand Total always visible --}}
                                        @if(!empty($pricing['grand_total']))
                                            @php
                                                $gt = $pricing['grand_total'];
                                            @endphp
                                            <div style="text-align:right;flex-shrink:0">
                                                <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Grand Total</div>
                                                <div style="font-size:18px;font-weight:800;color:var(--blue);font-family:'DM Mono',monospace">
                                                    {{ $gt['currency'] }} {{ number_format((float)$gt['total']) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- ── Pax fare panels ── --}}
                                    @foreach($fareBreakdowns as $fbIdx => $fb)
                                        @php
                                            $pType = $fb['traveler_type'] ?? 'ADT';
                                            $activeBg = $pType === 'ADT' ? 'var(--blue)' : ($pType === 'INF' ? '#7c3aed' : '#059669');
                                            $activeLt = $pType === 'ADT' ? '#eff6ff' : ($pType === 'INF' ? '#f5f3ff' : '#f0fdf4');
                                            $activeBd = $pType === 'ADT' ? '#bfdbfe' : ($pType === 'INF' ? '#ddd6fe' : '#bbf7d0');
                                        @endphp
                                        <div id="farePax{{ $fbIdx }}" style="display:{{ $fbIdx===0 ? 'block' : 'none' }};padding:16px 18px">

                                            {{-- 3 summary boxes --}}
                                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px">
                                                <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                                                    <div style="font-size:10px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Base Fare</div>
                                                    <div style="font-size:15px;font-weight:800;color:var(--text)">{{ $fb['currency'] }} {{ number_format((float)$fb['subtotal']) }}</div>
                                                </div>
                                                <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:12px;text-align:center">
                                                    <div style="font-size:10px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Taxes</div>
                                                    <div style="font-size:15px;font-weight:800;color:var(--text)">{{ $fb['currency'] }} {{ number_format((float)$fb['taxes']) }}</div>
                                                </div>
                                                <div style="background:{{ $activeLt }};border:1px solid {{ $activeBd }};border-radius:10px;padding:12px;text-align:center">
                                                    <div style="font-size:10px;color:{{ $activeBg }};margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Total</div>
                                                    <div style="font-size:15px;font-weight:800;color:{{ $activeBg }}">{{ $fb['currency'] }} {{ number_format((float)$fb['total']) }}</div>
                                                </div>
                                            </div>

                                            {{-- Badges --}}
                                            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px">
                                                @if(!empty($fb['validating_carrier']))
                                                    <span class="pill pill-blue">Validating: {{ $fb['validating_carrier'] }}</span>
                                                @endif
                                                <span class="pill {{ $fb['pricing_status_code']==='A' ? 'pill-green' : 'pill-amber' }}">{{ $fb['pricing_status_name'] ?? $fb['pricing_status_code'] }}</span>
                                                @if(!empty($fb['fare_construction'][0]['brand_fare_name']))
                                                    <span class="pill pill-gray">{{ $fb['fare_construction'][0]['brand_fare_name'] }}</span>
                                                @endif
                                                @if(!empty($fb['_pricing_method']))
                                                    <span class="pill pill-purple">{{ $fb['_pricing_method'] }}</span>
                                                @endif
                                            </div>

                                            {{-- Tax breakdown --}}
                                            @if(!empty($fb['tax_breakdown']))
                                                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px">Tax Breakdown</div>
                                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:4px;margin-bottom:14px">
                                                    @foreach($fb['tax_breakdown'] as $tx)
                                                        <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 9px;background:#f8fafc;border:1px solid var(--border);border-radius:7px;font-size:11px">
                                                            <div style="display:flex;align-items:center;gap:6px">
                                                                <span class="mono" style="background:#ede9fe;color:#5b21b6;padding:1px 6px;border-radius:4px;font-size:10px">{{ $tx['code'] }}</span>
                                                                <span style="color:var(--muted)">{{ $taxLabels[$tx['code']] ?? 'Misc' }}</span>
                                                            </div>
                                                            <span style="font-weight:700;color:var(--text)">{{ $tx['currency'] }} {{ number_format((float)$tx['amount']) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Fare calc --}}
                                            @if(!empty($fb['fare_calculation']))
                                                <div style="background:#1e293b;border-radius:9px;padding:11px 14px">
                                                    <div style="font-size:10px;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em">Fare Calculation</div>
                                                    <div style="font-size:11px;color:#86efac;font-family:'DM Mono',monospace;word-break:break-all;line-height:1.6">{{ $fb['fare_calculation'] }}</div>
                                                </div>
                                            @endif

                                        </div>
                                    @endforeach
                                </div>
                            @endif


                            {{-- Fare Rules --}}
                            @if(!empty($fareRules))
                                <div id="tab-rules" class="tab-panel {{ empty($fareBreakdowns)?'active':'' }}">
                                    <div style="overflow-x:auto">
                                        <table class="data-table">
                                            <thead>
                                            <tr>
                                                <th>Route</th><th>Airline · Pax</th><th>Refundable</th><th>Changeable</th><th>Cancel Fee</th><th>Change Fee</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($fareRules as $rule)
                                                @php
                                                    $rp = $rule['refund_penalties'][0]   ?? null;
                                                    $cp = $rule['exchange_penalties'][0] ?? null;
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:600">{{ $rule['origin'] ?: '—' }}{{ $rule['origin'] && $rule['destination'] ? ' → ' : '' }}{{ $rule['destination'] ?: '' }}</td>
                                                    <td style="font-size:11px;color:var(--muted)">{{ $rule['airline'] }} · {{ $rule['passenger_code'] }}</td>
                                                    <td><span class="pill {{ $rule['is_refundable'] ? 'pill-green' : 'pill-red' }}">{{ $rule['is_refundable'] ? 'Yes' : 'No' }}</span></td>
                                                    <td><span class="pill {{ $rule['is_changeable'] ? 'pill-green' : 'pill-red' }}">{{ $rule['is_changeable'] ? 'Yes' : 'No' }}</span></td>
                                                    <td>
                                                        @if($rp)
                                                            <span style="font-weight:700;color:var(--red)">{{ $rp['penalty_currency'] }} {{ number_format((float)$rp['penalty_amount']) }}</span>
                                                            <div style="font-size:10px;color:var(--muted)">{{ str_replace('_',' ',$rp['applicability']) }}</div>
                                                        @else <span style="color:var(--muted)">—</span> @endif
                                                    </td>
                                                    <td>
                                                        @if($cp)
                                                            <span style="font-weight:700;color:var(--blue)">{{ $cp['penalty_currency'] }} {{ number_format((float)$cp['penalty_amount']) }}</span>
                                                            <div style="font-size:10px;color:var(--muted)">{{ str_replace('_',' ',$cp['applicability']) }}</div>
                                                        @else <span style="color:var(--muted)">—</span> @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- SSR --}}
                            @if(!empty($specialSvc))
                                <div id="tab-ssr" class="tab-panel">
                                    <div style="overflow-x:auto">
                                        <table class="data-table">
                                            <thead><tr><th>Code</th><th>Service</th><th>Message</th><th>Status</th></tr></thead>
                                            <tbody>
                                            @foreach($specialSvc as $svc)
                                                <tr>
                                                    <td><span class="pill pill-gray mono">{{ $svc['code'] }}</span></td>
                                                    <td style="font-size:12px;color:var(--text)">{{ $svc['name'] }}</td>
                                                    <td class="mono" style="font-size:11px;color:var(--muted);max-width:200px;word-break:break-all">{{ $svc['message'] ?? '—' }}</td>
                                                    <td><span class="pill {{ $svc['status_code']==='HK' ? 'pill-green' : 'pill-amber' }}">{{ $svc['status_name'] ?? $svc['status_code'] }}</span></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Extra Baggage --}}
                            @if(!empty($bagCharges))
                                <div id="tab-baggage" class="tab-panel">
                                    <div style="overflow-x:auto">
                                        <table class="data-table">
                                            <thead><tr><th>Description</th><th>Weight</th><th>Pieces</th><th>Fee</th></tr></thead>
                                            <tbody>
                                            @foreach($bagCharges as $bc)
                                                <tr>
                                                    <td style="font-size:12px">{{ !empty($bc['special_item']) ? $bc['special_item'] : 'Extra Checked Bag' }}</td>
                                                    <td style="font-size:12px;color:var(--muted)">
                                                        {{ $bc['max_weight_kg'] ? $bc['max_weight_kg'].'kg' : '' }}
                                                        {{ $bc['max_size_cm'] ? '· '.($bc['max_size_cm']).'cm' : '' }}
                                                        {{ !$bc['max_weight_kg'] && !$bc['max_size_cm'] ? '—' : '' }}
                                                    </td>
                                                    <td style="font-weight:600;color:var(--text)">{{ $bc['pieces'] }}</td>
                                                    <td style="font-weight:700;color:var(--text)">{{ $bc['fee_currency'] }} {{ $bc['fee_amount'] }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 6. Reminders ─────────────── --}}
                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:14px 18px" class="fu d5">
                        <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                            <i class="fa fa-exclamation-triangle"></i> Important Reminders
                        </div>
                        <ul style="list-style:none;display:flex;flex-direction:column;gap:5px">
                            @foreach([
                                'Arrive at least <strong>3 hours before</strong> departure for international flights.',
                                'Carry a valid <strong>passport & photo ID</strong> for all passengers.',
                                'Checked baggage: <strong>'.($checkedBag ?? '—').'kg</strong>'.($cabinBag ? ' · Cabin: <strong>'.$cabinBag.'kg</strong>' : '').' per passenger.',
                                'Booking confirmation sent to your registered email.',
                                'For changes or cancellations, contact support as soon as possible.',
                            ] as $reminder)
                                <li style="font-size:12px;color:#92400e;display:flex;align-items:flex-start;gap:6px">
                                    <i class="fa fa-circle" style="font-size:5px;margin-top:5px;flex-shrink:0;color:#d97706"></i>
                                    <span>{!! $reminder !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>{{-- /bc-left --}}

                {{-- ════ RIGHT (Sidebar) ════ --}}
                <div class="bc-right">

                    {{-- Price summary --}}
                    <div class="card fu d2">
                        <div class="card-head">
                            <div class="card-icon" style="background:#f0fdf4;color:#16a34a"><i class="fa fa-receipt"></i></div>
                            <div class="card-title">Payment Summary</div>
                        </div>
                        <div style="padding:14px 18px">
                            @if($baseFee > 0)
                                <div class="pr-row"><span class="pr-lbl">Base Fare</span><span class="pr-val">৳{{ number_format($baseFee, 2) }}</span></div>
                            @endif
                            @if($taxFee > 0)
                                <div class="pr-row"><span class="pr-lbl">+ Tax</span><span class="pr-val">৳{{ number_format($taxFee, 2) }}</span></div>
                            @endif
                            @if($baseFee > 0 || $taxFee > 0)
                                <div class="pr-row pr-sub"><span class="pr-lbl">= Gross Fare</span><span class="pr-val">৳{{ number_format($baseFee + $taxFee, 2) }}</span></div>
                            @endif
                            @if($aitFee > 0)
                                <div class="pr-row"><span class="pr-lbl">+ AIT</span><span class="pr-val">৳{{ number_format($aitFee, 2) }}</span></div>
                            @endif
                            @if($serviceFee > 0)
                                <div class="pr-row"><span class="pr-lbl">+ Service Charge</span><span class="pr-val">৳{{ number_format($serviceFee, 2) }}</span></div>
                            @endif
                            @if($aitFee > 0 || $serviceFee > 0)
                                <div class="pr-row pr-sub"><span class="pr-lbl">= Subtotal</span><span class="pr-val">৳{{ number_format($baseFee + $taxFee + $aitFee + $serviceFee, 2) }}</span></div>
                            @endif
                            @if($totalDisc > 0)
                                <div class="pr-row" style="color:var(--green)">
                                    <span><i class="fa fa-tag" style="font-size:10px"></i> Discount</span>
                                    <span style="font-weight:700">-৳{{ number_format($totalDisc, 2) }}</span>
                                </div>
                            @endif
                            @if($penaltyAmt > 0)
                                <div class="pr-row" style="color:var(--red)">
                                    <span><i class="fa fa-ban" style="font-size:10px"></i> Penalty</span>
                                    <span style="font-weight:700">৳{{ number_format($penaltyAmt, 2) }}</span>
                                </div>
                            @endif
                            <div class="pr-row pr-total-row">
                                <span class="pr-lbl">You Pay</span>
                                <span class="pr-val">৳{{ number_format($bookTotal, 2) }}</span>
                            </div>
                            @if($walletUsed > 0)
                                <div class="pr-row" style="color:var(--purple)">
                                    <span><i class="fa fa-wallet" style="font-size:10px"></i> Wallet Used</span>
                                    <span style="font-weight:700">-৳{{ number_format($walletUsed, 2) }}</span>
                                </div>
                                <div style="background:var(--blue-lt);border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;margin-top:10px;text-align:center">
                                    <div style="font-size:11px;color:var(--blue);margin-bottom:3px">Payable</div>
                                    <div style="font-size:24px;font-weight:800;color:var(--blue)">৳{{ number_format($bookTotal - $walletUsed, 2) }}</div>
                                </div>
                            @endif
                            @if(!empty($booking->gateway))
                                <div style="font-size:11px;color:var(--muted);margin-top:10px;display:flex;align-items:center;gap:5px">
                                    <i class="fa fa-credit-card"></i>
                                    Via <strong style="color:var(--text)">{{ ucfirst($booking->gateway) }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Passenger count --}}
                    @if(!empty($passengers))
                        @php
                            $paxCounts = [];
                            foreach($passengers as $p) {
                                $t = $p['traveler_type'] ?? 'ADT';
                                $paxCounts[$t] = ($paxCounts[$t] ?? 0) + 1;
                            }
                        @endphp
                        <div class="card fu d3">
                            <div class="card-head">
                                <div class="card-icon" style="background:#ede9fe;color:#7c3aed"><i class="fa fa-users"></i></div>
                                <div class="card-title">Passengers</div>
                            </div>
                            <div style="padding:12px 18px">
                                @foreach($paxCounts as $type => $cnt)
                                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:13px">
                                        <span style="color:var(--muted)">{{ $paxLabel[$type] ?? $type }}</span>
                                        <span style="font-weight:700;color:var(--text)">× {{ $cnt }}</span>
                                    </div>
                                @endforeach
                                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0 0;font-size:13px;font-weight:700">
                                    <span>Total</span>
                                    <span>{{ count($passengers) }} pax</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Quick info --}}
                    <div class="card fu d3">
                        <div class="card-head">
                            <div class="card-icon" style="background:#f1f5f9;color:var(--muted)"><i class="fa fa-info"></i></div>
                            <div class="card-title">Quick Info</div>
                        </div>
                        <div style="padding:12px 18px;display:flex;flex-direction:column;gap:7px">
                            @foreach([
                                ['label'=>'Airline',      'val'=> $airCode ?? ($segments[0]['airline_name'] ?? null)],
                                ['label'=>'Checked Bag',  'val'=> $checkedBag ? $checkedBag.'kg' : null],
                                ['label'=>'Cabin Bag',    'val'=> $cabinBag ? $cabinBag.'kg' : null],
                                ['label'=>'Source',       'val'=> strtoupper($booking->source ?? '')],
                                ['label'=>'Segments',     'val'=> count($segments).' segment'.(count($segments)>1?'s':'')],
                                ['label'=>'Refundable',   'val'=> !empty($booking->is_refundable) ? 'Yes' : 'No'],
                                ['label'=>'TAU Deadline', 'val'=> $tauDate ? \Carbon\Carbon::parse($tauDate)->format('d M, H:i') : null],
                            ] as $qi)
                                @if($qi['val'])
                                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px">
                                        <span style="color:var(--muted)">{{ $qi['label'] }}</span>
                                        <span style="font-weight:600;color:{{ $qi['label']==='TAU Deadline' ? 'var(--red)' : 'var(--text)' }}">{{ $qi['val'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Support --}}
                    <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);border-radius:var(--radius);padding:16px 18px;text-align:center" class="fu d4 no-print">
                        <i class="fa fa-headset" style="color:#38bdf8;font-size:22px;margin-bottom:8px;display:block"></i>
                        <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:3px">Need Help?</div>
                        <div style="font-size:11px;color:#64748b;margin-bottom:12px">Support available 24/7</div>
                        <a href="#" style="display:block;background:#0ea5e9;color:#fff;text-decoration:none;font-size:12px;font-weight:700;padding:9px;border-radius:9px;transition:background .15s" onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                            Contact Support
                        </a>
                    </div>

                </div>{{-- /bc-right --}}
            </div>{{-- /bc-grid --}}
        </div>{{-- /bc-inner --}}
    </div>{{-- /bc-wrap --}}

    {{-- ── Print Modal ── --}}
    <div id="printModal" class="modal-overlay no-print">
        <div class="modal-box">
            <div class="modal-head">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;background:#0f172a;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px"><i class="fa fa-print"></i></div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--text)">Print Preview</div>
                        <div style="font-size:11px;color:var(--muted)">{{ $booking->code }}</div>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    {{-- Print button in modal header --}}
                    <button id="btnShopnoPrint"
                            style="display:flex;align-items:center;gap:6px;background:#0f172a;color:#fff;border:none;padding:8px 16px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                        <i class="fa fa-print"></i> Print
                    </button>

                    {{-- PDF button in modal header --}}
                    <button id="btnShopnoDownloadPDF"
                            style="display:flex;align-items:center;gap:6px;background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                        <i class="fa fa-file-pdf"></i> Download PDF
                    </button>
                    <button onclick="document.getElementById('printModal').classList.remove('open');document.body.style.overflow=''"
                            style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;border:none;cursor:pointer;font-size:13px;color:var(--muted)">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <div id="printContent" style="font-family: Arial, sans-serif; font-size: 12px; color: #000; background: #fff; padding: 28px; border-radius: 10px; width: 100%;">

                    {{-- ── Agency Header ── --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 24px;">
                        <div>
                            <div style="font-size:16px; font-weight:800; margin-bottom:6px;">SHOPNO TOUR</div>
                            <div style="font-size:11px; color:#333; line-height:1.7;">
                                Address: Green square House # 1/B, Floor 3/A, Road # 08, Gulshan-1 CNS Tower, 2nd Floor, Dhaka-1212.<br>
                                Contact number: 0248815080/0248815081<br>
                                IATA CODE: 42342576<br>
                                Civil Aviation Number: 0014018<br>
                                E-mail : shopnotourbd@gmail.com
                            </div>
                        </div>
                        <div>
                            @php
                                $logoUrl = get_file_url(setting_item('logo_id'), 'full');
                            @endphp
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Shopno Tour" style="height:80px; width:auto; object-fit:contain;">
                            @endif
                        </div>
                    </div>

                    {{-- ── Itinerary Title ── --}}
                    <div style="font-size:16px; font-weight:700; margin-bottom:14px;">Itinerary</div>

                    {{-- ── Passenger Information ── --}}
                    <div style="font-size:14px; font-weight:700; margin-bottom:6px;">Passenger Information</div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
                        <thead>
                        <tr style="background:#b8d4e8;">
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passenger Information</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Date of Birth</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passport Number</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passport Expiry</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Frequent Flyer Number</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Ticket</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($passengers as $pax)
                            @php
                                $fn     = strtoupper(trim($pax['first_name'] ?? ''));
                                $ln     = strtoupper(trim($pax['last_name']  ?? ''));
                                $prefix = strtoupper(trim($pax['prefix']     ?? ''));
                                $pName  = trim($ln.($ln && $fn ? '/' : '').$fn.($prefix ? ' '.$prefix : ''));
                                if(!$pName) $pName = 'PASSENGER';
                                $ffn    = $pax['frequent_flyer_number'] ?? ($pax['ffn'] ?? '');
                                $ticket = $pax['ticket_number'] ?? '';
                                $dob    = !empty($pax['dob']) ? date('d M Y', strtotime($pax['dob'])) : '';
                                $expiry = !empty($pax['passport_expiry']) ? date('d M Y', strtotime($pax['passport_expiry'])) : '';
                            @endphp
                            <tr>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $pName }}</td>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $dob }}</td>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $pax['passport_number'] ?? '' }}</td>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $expiry }}</td>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $ffn }}</td>
                                <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $ticket }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- ── PNR Row ── --}}
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                        <thead>
                        <tr style="background:#b8d4e8;">
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Airline PNR</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">PNR</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Date of Issue</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">
                                {{ $pnrAirline ? $pnrAirline.' ('.$airCode.' - '.($segments[0]['airline_name'] ?? $airCode).')' : ($pnrGDS ?? '—') }}
                            </td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $pnrGDS ?? '—' }}</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ date('dMy') }}</td>
                        </tr>
                        </tbody>
                    </table>

                    {{-- ── Itinerary Information — LEG SYSTEM ── --}}
                    <div style="font-size:14px; font-weight:700; margin-bottom:6px;">Itinerary Information</div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                        <thead>
                        <tr style="background:#b8d4e8;">
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Flight #</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">From</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">To</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Depart</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Arrive</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Seat</th>
                            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Info</th>
                        </tr>
                        </thead>
                        <tbody>
{{--                        <div style="font-size:14px; font-weight:700; margin-bottom:10px;">Itinerary Information</div>--}}

                        @foreach($legGroups as $li => $legGroup)
                            @php
                                $legSegs = $legGroup['segments'];
                                $legJrn  = $legGroup['journey'];
                                $isOut   = $legGroup['type'] === 'outbound';
                                $fcBagKg = null;
                                foreach($fareBreakdowns as $fb) {
                                    $fc = ($fb['fare_construction'] ?? [])[$li] ?? ($fb['fare_construction'][0] ?? null);
                                    if(!empty($fc['checked_bag_kg'])) { $fcBagKg = $fc['checked_bag_kg']; break; }
                                }
                                $legBag = $fcBagKg ? $fcBagKg.'K' : ($checkedBag ? $checkedBag.'K' : '');
                            @endphp

                            {{-- Leg Title --}}
                            <div style="background:#1d4ed8; color:#fff; padding:6px 12px; border-radius:6px 6px 0 0; font-size:11px; font-weight:700; margin-bottom:0;">
                                {{ $isOut ? '✈ OUTBOUND' : '✈ RETURN' }}
                                &nbsp;·&nbsp; {{ $legJrn['first_airport_code'] }} → {{ $legJrn['last_airport_code'] }}
                                &nbsp;·&nbsp; {{ date('D, d M Y', strtotime($legJrn['departure_date'])) }}
{{--                                @if($legBag) &nbsp;·&nbsp; Baggage: {{ $legBag }} @endif--}}
                            </div>

                            <table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
                                <thead>
                                <tr style="background:#b8d4e8;">
                                    <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:130px;">Flight</th>
                                    <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Route</th>
                                    <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:90px;">Depart</th>
                                    <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:90px;">Arrive</th>
                                    <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:170px;">Details</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($legSegs as $si => $seg)
                                    @php
                                        $airlineName = $seg['airline_name'] ?? $seg['carrier'];
                                        $flightNo    = $seg['carrier'].($seg['flight_number'] ?? '');
                                       $depTs = strtotime($seg['departure_time']);
                                        $arrTs = strtotime($seg['arrival_time']);
                                        $dur   = $toHM($seg['travel_time'] ?? 0);
                                        // Raw time from ISO string — no timezone conversion
                                        preg_match('/T(\d{2}:\d{2})/', $seg['departure_time'], $dm);
                                        preg_match('/T(\d{2}:\d{2})/', $seg['arrival_time'], $am);
                                        $depRaw = $dm[1] ?? date('H:i', $depTs);
                                        $arrRaw = $am[1] ?? date('H:i', $arrTs);
                                        $dep12  = date('h:i A', strtotime($depRaw));
                                        $arr12  = date('h:i A', strtotime($arrRaw));
                                        $cbU   = strtoupper(trim($seg['cabin_class'] ?? ''));
                                        if(in_array($cbU,['Y','ECONOMY']))                                $cl = 'Y-Economy';
                                        elseif(in_array($cbU,['E','ECONOMY CLASSIC','PREMIUM ECONOMY'])) $cl = 'E-Economy';
                                        elseif(in_array($cbU,['BUSINESS','C','J']))                       $cl = 'Business';
                                        elseif(in_array($cbU,['FIRST','F']))                              $cl = 'First';
                                        else                                                              $cl = $seg['cabin_class'] ?? '';
                                        $ac = $seg['aircraft_name'] ?? '';
                                        if(empty($ac)||(strlen($ac)<=4&&ctype_alnum($ac))) $ac = $seg['equipment'] ?? '';
                                        if(strlen($ac)<=4&&ctype_alnum($ac)) $ac='';
                                        $status = $seg['status_name'] ?? $seg['status'] ?? 'Confirmed';

                                        // Origin/Dest
                                        $orig = $seg['origin_city'] ?? $seg['origin'];
                                        $origApt = $seg['origin_airport_name'] ?? '';
                                        $fromTxt = $orig.($origApt ? ' - '.$origApt : '').(isset($seg['departure_terminal'])&&$seg['departure_terminal'] ? ' T'.$seg['departure_terminal'] : '');

                                        $dest = $seg['destination_city'] ?? $seg['destination'];
                                        $destApt = $seg['destination_airport_name'] ?? '';
                                        $toTxt = $dest.($destApt ? ' - '.$destApt : '').(isset($seg['arrival_terminal'])&&$seg['arrival_terminal'] ? ' T'.$seg['arrival_terminal'] : '');

                                        // Layover
                                        $isLastSeg = $si === count($legSegs)-1;
                                        $lvText = '';
                                        if(!$isLastSeg) {
                                            $ns = $legSegs[$si+1];
                                            $lvMin = (int)((strtotime($ns['departure_time']) - strtotime($seg['arrival_time'])) / 60);
                                            $overnight = date('Y-m-d',strtotime($seg['arrival_time'])) !== date('Y-m-d',strtotime($ns['departure_time'])) ? ' · Overnight' : '';
                                            $lvText = floor($lvMin/60).'h '.($lvMin%60).'m layover at '.$seg['destination'].$overnight;
                                        }
                                    @endphp

                                    <tr style="vertical-align:top; background:#fff;">
                                        {{-- Flight # --}}
                                        <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px;">
                                            @php
                                                $airlineInfo = \Modules\Flight\Models\Airline::getByCode($seg['carrier'] ?? '');
                                                $airlineImg  = $airlineInfo['image_thumb'] ?? $airlineInfo['image_url'] ?? '';
                                            @endphp
                                            @if($airlineImg)
                                                <img src="{{ $airlineImg }}" style="height:24px;width:auto;object-fit:contain;margin-bottom:4px;display:block;">
                                            @else
                                                <div style="display:inline-block; background:#c0392b; color:#fff; border-radius:50%; width:16px; height:16px; text-align:center; line-height:16px; font-size:9px; margin-bottom:3px;">✈</div>
                                            @endif
                                            <div style="font-weight:700;">{{ $airlineName }}</div>
                                            <div style="font-family:monospace; color:#1d4ed8; font-weight:700;">{{ $flightNo }}</div>
                                        </td>

                                        {{-- Route --}}
                                        <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px;">
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <div style="text-align:center;">
                                                    <div style="font-size:15px; font-weight:800; color:#0f172a;">{{ $seg['origin'] }}</div>
                                                    <div style="font-size:9px; color:#64748b;">{{ $fromTxt }}</div>
                                                </div>
                                                <div style="flex:1; text-align:center; color:#94a3b8; font-size:11px; white-space:nowrap;">
                                                    ——✈——<br>
                                                    <span style="font-size:10px; color:#64748b;">{{ $dur }}</span>
                                                </div>
                                                <div style="text-align:center;">
                                                    <div style="font-size:15px; font-weight:800; color:#0f172a;">{{ $seg['destination'] }}</div>
                                                    <div style="font-size:9px; color:#64748b;">{{ $toTxt }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- NEW --}}
                                        {{-- Depart --}}
                                        <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; white-space:nowrap;">
                                            <div style="font-size:15px; font-weight:800;">{{ $depRaw }}</div>
                                            <div style="font-size:11px; color:#475569;">{{ $dep12 }}</div>
                                            <div style="color:#64748b;">{{ date('D, d M Y', strtotime(substr($seg['departure_time'],0,10))) }}</div>
                                        </td>

                                        {{-- Arrive --}}
                                        <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; white-space:nowrap;">
                                            <div style="font-size:15px; font-weight:800;">{{ $arrRaw }}</div>
                                            <div style="font-size:11px; color:#475569;">{{ $arr12 }}</div>
                                            <div style="color:#64748b;">{{ date('D, d M Y', strtotime(substr($seg['arrival_time'],0,10))) }}</div>
                                        </td>

                                        {{-- Details --}}
                                        <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; line-height:1.8; color:#374151;">
                                            @php
                                                $segBag = '';
                                                // 1. Segment এর নিজের baggage
                                                if(!empty($seg['checked_bag_allowance'])) {
                                                    $segBag = $seg['checked_bag_allowance'];
                                                }
                                                // 2. fare_construction থেকে এই leg ($li) এর baggage
                                                if(empty($segBag)) {
                                                    foreach($fareBreakdowns as $fb) {
                                                        $fcArr = $fb['fare_construction'] ?? [];
                                                        $fc = $fcArr[$li] ?? ($fcArr[0] ?? null);
                                                        if(!empty($fc['checked_bag_kg'])) {
                                                            $segBag = $fc['checked_bag_kg'].'K';
                                                            break;
                                                        }
                                                    }
                                                }
                                                // 3. legBag fallback
                                                if(empty($segBag) && !empty($legBag)) {
                                                    $segBag = $legBag;
                                                }
                                                // 4. Global pricing fallback
                                                if(empty($segBag) && $checkedBag) {
                                                    $segBag = $checkedBag.'K';
                                                }
                                            @endphp
                                            Class : {{ $seg['cabin_class'] ?? '' }}
                                            @if(!empty($seg['class_of_service']))
                                                ({{ $seg['class_of_service'] }})
                                            @endif <br>
                                            Duration : {{ $dur }}<br>
                                            Status : <span style="color:#059669; font-weight:600;">Booking Confirm</span><br>
                                            @if($ac)Aircraft : {{ $ac }}<br>@endif
                                            Special Svc :<br>
                                            @if($segBag)Baggage : {{ $segBag }}@endif
                                        </td>
                                    </tr>

                                    {{-- Layover row --}}
                                    @if($lvText)
                                        <tr>
                                            <td colspan="5" style="border:1px solid #ccc; padding:5px 12px; background:#fff8e1; font-size:11px; font-weight:600; color:#d97706; text-align:center;">
                                                ⏱ {{ $lvText }}
                                            </td>
                                        </tr>
                                    @endif

                                @endforeach
                                </tbody>
                            </table>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- ── Fare Data ── --}}
                    <div style="font-size:14px; font-weight:700; margin-bottom:6px;">Fare Data</div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tbody>
                        @php
                            $gTotal   = $pricing['grand_total'] ?? [];
                            $baseCurr = $gTotal['base_currency'] ?? ($fareBreakdowns[0]['currency'] ?? 'BDT');
                            $baseAmt  = $gTotal['base_amount']   ?? ($fareBreakdowns[0]['subtotal'] ?? 0);
                            $eqCurr   = $gTotal['currency']      ?? 'BDT';
                            $taxStr   = '';
                            foreach(($fareBreakdowns[0]['tax_breakdown'] ?? []) as $tx) {
                                $taxStr .= number_format((float)$tx['amount']).$tx['code'].' ';
                            }
                            $taxStr   = trim($taxStr);
                        @endphp
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; width:160px; background:#f5f5f5; font-weight:600;">Fare</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $baseAmt ? number_format((float)$baseAmt, 2).' '.$baseCurr : '—' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Equivalent Fare</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ number_format((float)($gTotal['subtotal'] ?? $baseFee), 2) }} {{ $eqCurr }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Tax</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $taxStr ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Total</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; font-weight:700;">{{ number_format($bookTotal, 2) }} {{ $eqCurr }}</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Tour Code</td>
                            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $booking->tour_code ?? '' }}</td>
                        </tr>
                        </tbody>
                    </table>

                    <div style="border-top:1px solid #e2e8f0; padding-top:10px; font-size:10px; color:#94a3b8; text-align:center;">
                        Computer-generated booking confirmation. No signature required.
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button onclick="document.getElementById('printModal').classList.remove('open');document.body.style.overflow=''"
                        style="padding:8px 16px;border-radius:9px;border:1px solid var(--border);background:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                    Cancel
                </button>
                <button onclick="doShopnoPrint()" style="padding:8px 20px;border-radius:9px;border:none;background:#0f172a;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
                    <i class="fa fa-print"></i> Print Now
                </button>
            </div>
        </div>
    </div>

{{--    </div>--}}{{-- /bc-wrap --}}

{{--    <script>--}}
{{--        /* ── Modal close ── */--}}
{{--        document.addEventListener('DOMContentLoaded', function() {--}}
{{--            var modal = document.getElementById('printModal');--}}
{{--            if(modal) {--}}
{{--                modal.addEventListener('click', function(e) {--}}
{{--                    if(e.target === this) {--}}
{{--                        this.classList.remove('open');--}}
{{--                        document.body.style.overflow = '';--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}
{{--        });--}}

{{--        document.addEventListener('keydown', function(e) {--}}
{{--            if(e.key === 'Escape') {--}}
{{--                var m = document.getElementById('printModal');--}}
{{--                if(m) { m.classList.remove('open'); document.body.style.overflow = ''; }--}}
{{--            }--}}
{{--        });--}}

{{--        /* ── Tab switch ── */--}}
{{--        function switchTab(id, btn) {--}}
{{--            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });--}}
{{--            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });--}}
{{--            var panel = document.getElementById('tab-' + id);--}}
{{--            if(panel) panel.classList.add('active');--}}
{{--            btn.classList.add('active');--}}
{{--        }--}}

{{--        /* ── Fare pax tab ── */--}}
{{--        function switchFareTab(idx, btn) {--}}
{{--            document.querySelectorAll('[id^="farePax"]').forEach(function(p) { p.style.display = 'none'; });--}}
{{--            document.querySelectorAll('#fareTabBar button').forEach(function(b) {--}}
{{--                b.style.background = '#fff';--}}
{{--                b.style.color = 'var(--muted)';--}}
{{--                b.style.borderColor = 'var(--border)';--}}
{{--                var pill = b.querySelector('span');--}}
{{--                if(pill) { pill.style.background = ''; pill.style.color = ''; }--}}
{{--            });--}}
{{--            var panel = document.getElementById('farePax' + idx);--}}
{{--            if(panel) panel.style.display = 'block';--}}
{{--            var typeMap = {'Adult':'ADT','Child':'CNN','Infant':'INF'};--}}
{{--            var colors  = {'ADT':'var(--blue)','CNN':'#059669','CHD':'#059669','INF':'#7c3aed'};--}}
{{--            var btnText = btn.innerText.trim().split('\n')[0].trim();--}}
{{--            var col = colors[typeMap[btnText]] || 'var(--blue)';--}}
{{--            btn.style.background = col;--}}
{{--            btn.style.color = '#fff';--}}
{{--            btn.style.borderColor = col;--}}
{{--            var pill = btn.querySelector('span');--}}
{{--            if(pill) { pill.style.background = 'rgba(255,255,255,.25)'; pill.style.color = '#fff'; }--}}
{{--        }--}}

{{--        /* ── Print ── */--}}
{{--        function doShopnoPrint() {--}}
{{--            var content = document.getElementById('printContent').innerHTML;--}}
{{--            var iframe = document.getElementById('__pif');--}}
{{--            if(iframe) iframe.remove();--}}
{{--            iframe = document.createElement('iframe');--}}
{{--            iframe.id = '__pif';--}}
{{--            iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;border:none;';--}}
{{--            document.body.appendChild(iframe);--}}
{{--            var doc = iframe.contentDocument || iframe.contentWindow.document;--}}
{{--            doc.open();--}}
{{--            doc.write('<!DOCTYPE html><html><head><meta charset="UTF-8">'--}}
{{--                + '<title>Booking - {{ $booking->code }}</title>'--}}
{{--                + '<style>* { box-sizing:border-box; } body { font-family:Arial,sans-serif; background:#fff; color:#000; font-size:12px; padding:20px; } @page { margin:10mm; size:A4; } * { -webkit-print-color-adjust:exact; print-color-adjust:exact; } table { border-collapse:collapse; width:100%; } img { max-width:100%; }</style>'--}}
{{--                + '</head><body>' + content + '</body></html>');--}}
{{--            doc.close();--}}
{{--            iframe.onload = function() {--}}
{{--                try { iframe.contentWindow.focus(); iframe.contentWindow.print(); }--}}
{{--                catch(e) { window.print(); }--}}
{{--            };--}}
{{--        }--}}

{{--        /* ── PDF Download ── */--}}
{{--        function doShopnoDownloadPDF() {--}}
{{--            var btn = document.getElementById('pdfBtn');--}}
{{--            var orig = btn ? btn.innerHTML : '';--}}
{{--            if(btn) btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';--}}

{{--            function _generate() {--}}
{{--                var el = document.getElementById('printContent');--}}
{{--                var opt = {--}}
{{--                    margin:      [10, 10, 10, 10],--}}
{{--                    filename:    'Booking-{{ $booking->code }}.pdf',--}}
{{--                    image:       { type: 'jpeg', quality: 0.98 },--}}
{{--                    html2canvas: { scale: 2, useCORS: true, logging: false },--}}
{{--                    jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },--}}
{{--                    pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] }--}}
{{--                };--}}
{{--                html2pdf().set(opt).from(el).save().then(function() {--}}
{{--                    if(btn) btn.innerHTML = orig;--}}
{{--                });--}}
{{--            }--}}

{{--            if(typeof html2pdf === 'undefined') {--}}
{{--                var s = document.createElement('script');--}}
{{--                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';--}}
{{--                s.onload = _generate;--}}
{{--                document.head.appendChild(s);--}}
{{--            } else {--}}
{{--                _generate();--}}
{{--            }--}}
{{--        }--}}
{{--    </script>--}}

@endsection


@push('js')
    <script>
        // DOMContentLoaded নয়, সরাসরি
        var modal = document.getElementById('printModal');
        var btnOpen = document.getElementById('btnOpenPrintModal');

        if(btnOpen) {
            btnOpen.addEventListener('click', function() {
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        }

        document.querySelectorAll('.modal-close-btn').forEach(function(b) {
            b.addEventListener('click', function() {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            });
        });

        modal.addEventListener('click', function(e) {
            if(e.target === this) { this.classList.remove('open'); document.body.style.overflow = ''; }
        });

        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') { modal.classList.remove('open'); document.body.style.overflow = ''; }
        });

        function doShopnoPrint() {
            var content = document.getElementById('printContent').innerHTML;
            var iframe = document.getElementById('__pif');
            if(iframe) iframe.remove();
            iframe = document.createElement('iframe');
            iframe.id = '__pif';
            iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;border:none;';
            document.body.appendChild(iframe);
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write('<!DOCTYPE html><html><head><meta charset="UTF-8">'
                + '<title>Booking - {{ $booking->code }}</title>'
                + '<style>* { box-sizing:border-box; } body { font-family:Arial,sans-serif; background:#fff; color:#000; font-size:12px; padding:20px; } @page { margin:10mm; size:A4; } * { -webkit-print-color-adjust:exact; print-color-adjust:exact; } table { border-collapse:collapse; width:100%; } img { max-width:100%; }</style>'
                + '</head><body>' + content + '</body></html>');
            doc.close();
            iframe.onload = function() {
                try { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
                catch(e) { window.print(); }
            };
        }

        var p1 = document.getElementById('btnShopnoPrint');
        var p2 = document.getElementById('btnShopnoPrint2');
        if(p1) p1.addEventListener('click', doShopnoPrint);
        if(p2) p2.addEventListener('click', doShopnoPrint);

        function doShopnoDownloadPDF() {
            var btn = document.getElementById('btnShopnoDownloadPDF');
            var orig = btn ? btn.innerHTML : '';
            if(btn) btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
            function _generate() {
                var el = document.getElementById('printContent');
                var opt = {
                    margin: [10,10,10,10],
                    filename: 'Booking-{{ $booking->code }}.pdf',
                    image: { type:'jpeg', quality:0.98 },
                    html2canvas: { scale:2, useCORS:true, logging:false },
                    jsPDF: { unit:'mm', format:'a4', orientation:'portrait' },
                    pagebreak: { mode:['avoid-all','css','legacy'] }
                };
                html2pdf().set(opt).from(el).save().then(function() {
                    if(btn) btn.innerHTML = orig;
                });
            }
            if(typeof html2pdf === 'undefined') {
                var s = document.createElement('script');
                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                s.onload = _generate;
                document.head.appendChild(s);
            } else { _generate(); }
        }

        var pdfBtn = document.getElementById('btnShopnoDownloadPDF');
        if(pdfBtn) pdfBtn.addEventListener('click', doShopnoDownloadPDF);

        window.switchTab = function(id, btn) {
            document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            var panel = document.getElementById('tab-' + id);
            if(panel) panel.classList.add('active');
            btn.classList.add('active');
        };

        window.switchFareTab = function(idx, btn) {
            document.querySelectorAll('[id^="farePax"]').forEach(function(p) { p.style.display = 'none'; });
            document.querySelectorAll('#fareTabBar button').forEach(function(b) {
                b.style.background = '#fff'; b.style.color = 'var(--muted)'; b.style.borderColor = 'var(--border)';
                var pill = b.querySelector('span');
                if(pill) { pill.style.background = ''; pill.style.color = ''; }
            });
            var panel = document.getElementById('farePax' + idx);
            if(panel) panel.style.display = 'block';
            var typeMap = {'Adult':'ADT','Child':'CNN','Infant':'INF'};
            var colors  = {'ADT':'var(--blue)','CNN':'#059669','CHD':'#059669','INF':'#7c3aed'};
            var btnText = btn.innerText.trim().split('\n')[0].trim();
            var col = colors[typeMap[btnText]] || 'var(--blue)';
            btn.style.background = col; btn.style.color = '#fff'; btn.style.borderColor = col;
            var pill = btn.querySelector('span');
            if(pill) { pill.style.background = 'rgba(255,255,255,.25)'; pill.style.color = '#fff'; }
        };
    </script>
@endpush
