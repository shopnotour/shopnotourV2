

@extends('layouts.user')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('themes/gotrip/dist/frontend/module/booking/css/checkout.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>


    <style>
        .invoice-info p  { margin:0 !important; padding:0 !important; }
        .invoice-info h1, .invoice-info h2, .invoice-info h3,
        .invoice-info h4, .invoice-info h5, .invoice-info h6 {
            margin:0 !important;
            padding:0 !important;
            font-weight:700 !important;
            line-height:1.4 !important;
        }
        .invoice-info h1 { font-size:24px !important; }
        .invoice-info h2 { font-size:20px !important; }
        .invoice-info h3 { font-size:18px !important; }
        .invoice-info h4 { font-size:16px !important; }
        .invoice-info h5 { font-size:14px !important; }
        .invoice-info h6 { font-size:12px !important; }
        .invoice-info strong, .invoice-info b { font-weight:700 !important; }
    </style>


    <style>
        /* ── Base ── */
        *{box-sizing:border-box;}
        .f-display{font-family:'Sora','Segoe UI',sans-serif;}
        .card{background:white;border-radius:18px;box-shadow:0 1px 4px rgba(0,0,0,.06),0 4px 20px rgba(0,0,0,.04);overflow:hidden;margin-bottom:16px;}
        .pill{display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:999px;font-size:11.5px;font-weight:600;line-height:1.7;}
        .pill i{font-size:9px;}
        .fade-up{animation:fadeUp .45s ease both;}
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .d1{animation-delay:.05s}.d2{animation-delay:.1s}.d3{animation-delay:.15s}.d4{animation-delay:.2s}.d5{animation-delay:.25s}

        /* ── Quick Actions ── */
        .qac-wrap{display:grid;gap:8px;grid-template-columns:repeat(5,1fr);}
        .qac{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;border-radius:12px;padding:12px 6px;color:white;font-size:10px;font-weight:700;text-align:center;line-height:1.3;text-decoration:none;cursor:pointer;transition:all .22s cubic-bezier(.34,1.56,.64,1);min-height:72px;border:none;position:relative;}
        .qac:hover{transform:translateY(-3px);color:white;text-decoration:none;box-shadow:0 8px 20px rgba(0,0,0,.2);}
        .qac.disabled-card{opacity:.35;cursor:not-allowed;filter:grayscale(.5);pointer-events:none;}
        .qac-icon{font-size:22px;line-height:1;}
        .qac-label{font-size:9.5px;font-weight:700;letter-spacing:.01em;}

        /* ── Section Cards ── */
        .f-section-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-bottom:14px;box-shadow:0 1px 4px rgba(0,0,0,.05);}
        .f-section-header{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;cursor:pointer;background:#f8fafc;border-bottom:1px solid #f1f5f9;user-select:none;transition:background .15s;}
        .f-section-header:hover{background:#f1f5f9;}
        .f-section-header.collapsed{border-bottom:none;}
        .f-section-title{display:flex;align-items:center;gap:9px;font-size:.875rem;font-weight:700;color:#1e293b;}
        .f-section-meta{font-size:.72rem;font-weight:400;color:#94a3b8;margin-left:2px;}
        .f-section-icon{width:28px;height:28px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.78rem;}
        .f-chevron{font-size:.72rem;color:#94a3b8;transition:transform .25s ease;flex-shrink:0;}

        /* ── Itinerary ── */
        .itin-desktop-v2{padding:14px 16px;overflow-x:auto;}
        .itin-mobile-v2{display:none;padding:12px 14px;}
        .vtl2{position:relative;padding-left:18px;}
        .vtl2-vline{position:absolute;left:5px;top:6px;bottom:6px;width:2px;background:#e2e8f0;border-radius:1px;}
        .vtl2-dot{position:absolute;left:-15px;top:5px;width:10px;height:10px;border-radius:50%;border:2px solid #fff;z-index:1;}
        .vtl2-flight{padding:6px 0;}
        .vtl2-flight-inner{background:#f8fafc;border-radius:8px;padding:8px 10px;}
        .vtl2-lv{position:relative;padding:6px 0;}
        .vtl2-lv-dot{position:absolute;left:-15px;top:50%;transform:translateY(-50%);width:10px;height:10px;border-radius:50%;background:#fde68a;border:2px solid #fff;z-index:1;}
        .vtl2-lv-inner{display:flex;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:7px 10px;}

        /* ── Passenger Table ── */
        .pax-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
        .pax-desktop{display:table;}
        .pax-mobile{display:none;}

        /* ── Passenger Status Badges ── */
        .tkt-open{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
        .tkt-void{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
        .tkt-refund{background:#fef3c7;color:#92400e;border:1px solid #fde68a;}
        .tkt-issued{background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;}
        .tkt-unknown{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;}

        /* ── Pax Row Status highlight ── */
        .pax-row-void{background:#fff5f5 !important;}
        .pax-row-void:hover{background:#fee2e2 !important;}
        .pax-row-refund{background:#fffbeb !important;}
        .pax-row-refund:hover{background:#fef3c7 !important;}

        /* ── Mobile Pax Cards ── */
        .pax-card{border:1px solid #e2e8f0;border-radius:12px;padding:14px;margin-bottom:10px;background:#fff;}
        .pax-card.voided{border-color:#fecaca;background:#fff5f5;}
        .pax-card.refunded{border-color:#fde68a;background:#fffbeb;}

        /* ── Timer ── */
        .tau-timer{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:700;font-family:monospace;letter-spacing:.05em;}
        .tau-timer.warning{background:rgba(239,68,68,.25);border-color:#f87171;animation:pulse 1s infinite;}
        .tau-timer.expired{background:rgba(100,116,139,.2);color:rgba(255,255,255,.4);}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.7}}

        /* ── Mobile Sticky Payment Bar ── */
        .mobile-pay-bar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;background:white;border-top:1px solid #e2e8f0;padding:12px 16px;box-shadow:0 -4px 20px rgba(0,0,0,.1);}

        /* ── Modals ── */
        .cmodal{display:none;position:fixed;z-index:9999;inset:0;background:rgba(0,0,0,.55);align-items:center;justify-content:center;}
        .cmodal.show{display:flex;}
        @keyframes mIn{from{transform:translateY(-30px);opacity:0}to{transform:translateY(0);opacity:1}}
        .mbox{background:white;border-radius:12px;width:92%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:mIn .25s;max-height:90vh;overflow-y:auto;}
        .mbox-wide{max-width:860px!important;width:95%!important;}

        /* ── Select2 ── */
        .select2-container{width:100%!important;}
        .select2-container--default .select2-selection--multiple{border:2px solid #e5e7eb!important;border-radius:8px!important;min-height:42px!important;padding:4px 8px!important;}
        .select2-container--default.select2-container--focus .select2-selection--multiple{border-color:#667eea!important;}
        .select2-container--default .select2-selection--multiple .select2-selection__choice{background:linear-gradient(135deg,#667eea,#764ba2)!important;border:none!important;color:white!important;padding:4px 8px!important;border-radius:5px!important;font-size:12px!important;}
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{color:white!important;border:none!important;background:transparent!important;}
        .select2-container--default .select2-results__option--highlighted[aria-selected]{background:#667eea!important;}
        .select2-dropdown{border:2px solid #667eea!important;border-radius:8px!important;}

        /* ── Misc utils ── */
        .adrp{display:none;}.adrp.show{display:block;}
        .payment-sticky{position:sticky;top:90px;}
        .space-y-3>*+*{margin-top:.75rem;}
        .hidden{display:none;}
        .overflow-x-auto{overflow-x:auto;}

        /* ── Print ── */
        @media print{
            @page{size:A4 portrait;margin:8mm 10mm;}
            body *{visibility:hidden!important;}
            #tpz,#tpz *{visibility:visible!important;}
            #tpz{position:fixed!important;top:0!important;left:0!important;width:190mm!important;margin:0!important;padding:8mm 10mm!important;background:white!important;}
            .no-print{display:none!important;}
        }

        /* ════════════════════════
           RESPONSIVE BREAKPOINTS
        ════════════════════════ */
        @media(max-width:991px){
            .payment-sticky{position:static;}
        }
        @media(max-width:767px){
            .bravo-booking-page{padding-top:16px!important;padding-bottom:80px!important;}
            /* Header */
            .booking-header-inner{flex-direction:column!important;align-items:flex-start!important;}
            .booking-header-code{font-size:16px!important;}
            .pnr-flex-row{flex-direction:column!important;}
            .pnr-flex-row>div{border-right:none!important;border-bottom:1px solid rgba(255,255,255,.1)!important;}
            .pnr-flex-row>div:last-child{border-bottom:none!important;}
            /* Quick actions */
            .qac-wrap{grid-template-columns:repeat(3,1fr)!important;}
            /* Itinerary */
            .itin-desktop-v2{display:none!important;}
            .itin-mobile-v2{display:block!important;}
            /* Passengers */
            .pax-desktop{display:none!important;}
            .pax-mobile{display:block!important;}
            /* Sidebar hide on mobile */
            .col-md-4{display:none;}
            /* Mobile pay bar show */
            .mobile-pay-bar{display:block;}
            /* Section header smaller */
            .f-section-header{padding:11px 14px;}
            .f-section-title{font-size:.82rem;}
            /* Fare tabs scroll */
            .fare-tab-bar{overflow-x:auto;-webkit-overflow-scrolling:touch;flex-wrap:nowrap!important;white-space:nowrap;}
            /* Timer */
            .tau-timer{font-size:11px;}
        }
        @media(max-width:480px){
            .qac-wrap{grid-template-columns:repeat(3,1fr)!important;}
            .qac{min-height:64px;padding:10px 4px;}
            .qac-icon{font-size:18px;}
            .qac-label{font-size:9px;}
        }
    </style>
@endpush

@section('content')
    @php
        $fd           = $flightData ?? [];
        $fdPricing    = $fd['pricing']              ?? [];
        $fdFareRules  = $fd['fare_rules']           ?? [];
        $fdSpecialSvc = $fd['special_services']     ?? [];
        $fdSegments   = $fd['segments']             ?? [];
        $fdPassengers = $fd['passengers']           ?? [];
        $fdJourneys   = $fd['journeys']             ?? [];
        $fdProvRes    = $fd['provider_reservation'] ?? [];
        $fdSupplier   = $fd['supplier_locator']     ?? [];
        $fdActionSt   = $fd['action_status']        ?? [];

        $fareBreakdowns    = $fdPricing['fare_breakdowns']         ?? [];
        $checkedBagCharges = $fdPricing['checked_baggage_charges'] ?? [];
        $checkedBag        = $fdPricing['checked_bag_kg']          ?? null;
        $cabinBag          = $fdPricing['cabin_bag_kg']            ?? null;

        $grandTotal    = $fdPricing['grand_total'] ?? [];
        $totalAmount   = $grandTotal['total']    ?? 0;
        $totalCurrency = $grandTotal['currency'] ?? 'BDT';

        $gfPnr = $fdSupplier['locator_code'] ?? '';

        // TAU Deadline (raw ISO for JS timer)
        $tauDeadline    = '';
        $tauDeadlineIso = '';
        $tauDeadlineIso = '';
            if (!empty($fdActionSt['ticket_date'])) {
                try {
                    $tauCarbon      = \Carbon\Carbon::parse($fdActionSt['ticket_date'])
                                        ->setTimezone('Asia/Dhaka')
                                        ->subMinutes(45);          // ← 45 min আগে
                    $tauDeadline    = $tauCarbon->format('d M Y, h:i A');
                    $tauDeadlineIso = $tauCarbon->toIso8601String();
                } catch(\Exception $e) {}
            }

        $firstJourney = $fdJourneys[0] ?? [];
        $lastJourney  = end($fdJourneys) ?: [];
        $depCity      = $firstJourney['first_airport_code'] ?? ($fdSegments[0]['origin'] ?? '');
        $arrCity      = $lastJourney['last_airport_code']   ?? ($fdSegments[count($fdSegments)-1]['destination'] ?? '');
        $totalSegs    = count($fdSegments);
        $totalPax = $passengers->count();
        $airlineName  = $fdSegments[0]['airline_name']   ?? ($fdSegments[0]['carrier'] ?? '');
        $checkedBag2  = $checkedBag;
        $cabinBag2    = $cabinBag;

        $sc = match($booking->status){
          'booked'    => ['bg'=>'rgba(16,185,129,.2)', 'dot'=>'#10b981','txt'=>'#d1fae5'],
          'ticketed'  => ['bg'=>'rgba(59,130,246,.2)', 'dot'=>'#60a5fa','txt'=>'#dbeafe'],
          'pending'   => ['bg'=>'rgba(245,158,11,.2)', 'dot'=>'#fbbf24','txt'=>'#fef3c7'],
          'cancelled' => ['bg'=>'rgba(239,68,68,.2)',  'dot'=>'#f87171','txt'=>'#fee2e2'],
          default     => ['bg'=>'rgba(255,255,255,.15)','dot'=>'#fff',   'txt'=>'#fff'],
        };

        $taxLabels = ['BD'=>'Airport Dev Fee','UT'=>'Travel Tax','OW'=>'Security Charge','E5'=>'Embarkation Fee','BH'=>'Fuel Surcharge','HM'=>'IATA Misc','ZR'=>'Security Svc','P7'=>'PAX Charge (Dep)','P8'=>'PAX Charge (Arr)','YQ'=>'Fuel Surcharge','YR'=>'Carrier Surcharge','AE'=>'PAX Service','F6'=>'Misc Tax'];
        $paxLabel  = ['ADT'=>'Adult','CNN'=>'Child','CHD'=>'Child','INF'=>'Infant'];

        $fmtDuration = function(int $mins): string {
            $h = intdiv($mins, 60); $m = $mins % 60;
            return ($h>0 ? $h.'h ' : '') . $m.'m';
        };

        // ── Void logic (single, no duplicate) ──
        $isTicketed = !empty($fd['is_ticketed']) && $fd['is_ticketed'] === true;
        $canVoid = false;
        $showVoidDate = false;
        $voidDateDisplay = null;

        if ($isTicketed && !empty($fd['flight_tickets'][0]['date'])) {
            $ticketIssuedDate = \Carbon\Carbon::parse($fd['flight_tickets'][0]['date'])->setTimezone('Asia/Dhaka');
            $voidDeadline     = $ticketIssuedDate->copy()->endOfDay();
            $now              = \Carbon\Carbon::now('Asia/Dhaka');
            $firstDep = !empty($fdSegments[0]['departure_time'])
                ? \Carbon\Carbon::parse($fdSegments[0]['departure_time'])->setTimezone('Asia/Dhaka')
                : null;
            $isDepartureToday = $firstDep && $firstDep->isToday();
            if (!$isDepartureToday && $now->lte($voidDeadline)) {
                $canVoid         = true;
                $showVoidDate    = true;
                $voidDateDisplay = $voidDeadline->format('d M Y');
            }
        }

        // ── Ticket status map ──
        $tktMap = [];
        foreach($fd['flight_tickets'] ?? [] as $t) {
            $idx = (int)($t['traveler_index'] ?? 0);
            $tktMap[$idx] = [
                'number' => $t['number'] ?? '',
                'code'   => strtoupper($t['ticket_status_code'] ?? ''),
                'status' => $t['ticket_status'] ?? '',
            ];
        }

        // Helper: get ticket status badge class
        $tktBadgeClass = function(string $code): string {
            return match(true) {
                in_array($code, ['TV','VOID','VOIDED'])           => 'tkt-void',
                in_array($code, ['OK','OPEN','TK','','HK'])       => 'tkt-open',
                in_array($code, ['RF','RFND','REFUND','REFUNDED'])=> 'tkt-refund',
                in_array($code, ['TI','TKTT','ISSUED'])           => 'tkt-issued',
                default                                            => 'tkt-unknown',
            };
        };

        $tktBadgeLabel = function(string $code, string $status): string {
            return match(true) {
                in_array($code, ['TV','VOID','VOIDED'])            => '🚫 Voided',
                in_array($code, ['RF','RFND','REFUND','REFUNDED']) => '💰 Refunded',
                in_array($code, ['TI','TKTT','ISSUED'])            => '✅ Issued',
                in_array($code, ['OK','OPEN','TK','HK'])           => '✅ Open',
                $code === ''                                        => '—',
                default                                            => $status ?: $code,
            };
        };

        // ── Leg groups ──
        $legGroups2 = [];
        $segIndex2  = 0;
        foreach($fdJourneys as $jIdx => $journey) {
            $count = (int)($journey['number_of_flights'] ?? 1);
            $legGroups2[$jIdx] = [
                'journey'  => $journey,
                'segments' => array_slice($fdSegments, $segIndex2, $count),
                'type'     => $jIdx === 0 ? 'outbound' : 'return',
            ];
            $segIndex2 += $count;
        }

        // Payment vars
        $baseFee    = (float)($booking->base_fee      ?? 0);
        $taxFee     = (float)($booking->total_fee     ?? 0);
        $serviceFee = (float)($booking->ticketing_fee ?? 0);
        $aitFee     = (float)($booking->supplier_fee  ?? 0);
        $flightDisc = (float)($booking->flight_discount  ?? 0);
        $segDisc    = (float)($booking->segment_discount ?? 0);
        $totalDisc  = $flightDisc + $segDisc;
        $bookTotal  = (float)($booking->total ?? 0);
         $voidCharge  = (float)($booking->void_charge ?? 0);
        $displayTotal = $bookTotal + $voidCharge;
        $walletUsed = (float)($booking->wallet_credit_used ?? 0);
        $payable    = max(0, (float)($booking->pay_now ?? 0));
        $penaltyAmt = (float)($booking->penalty_amount ?? 0);
        $penaltyRmk = $booking->penalty_remark ?? null;
        $payNow     = (float)($booking->pay_now ?? 0);
        $payNow     = $payNow + $voidCharge;
        $paxImageJson = collect($passengers)->map(function($p) {
        return [
            'name'           => trim($p->first_name . ' ' . $p->last_name),
            'passport_image' => $p->passportMedia ? $p->passportMedia->view_url : null,
            'visa_image'     => $p->visaMedia     ? $p->visaMedia->view_url     : null,
        ];
    })->values();

                // Raw time extractor — no timezone conversion
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
    @endphp

    <div class="bravo-booking-page padding-content pt-40 pb-40">
        <div class="container">

            @include('Booking::frontend/global/booking-detail-notice')

            {{-- ════════════════════════════════════════
                 BOOKING HEADER
            ════════════════════════════════════════ --}}
            <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border-radius:16px;padding:20px;margin-bottom:10px;overflow:hidden;">

                {{-- Top row --}}
                <div class="booking-header-inner" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
                    <div>
                        <div class="booking-header-code" style="font-size:20px;font-weight:800;letter-spacing:.03em">✈️ {{ $booking->code }}</div>
                        <div style="font-size:10px;opacity:.6;margin-top:2px">
                            Booked {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y, h:i A') }}
                            @if(!empty($fdProvRes['owning_pcc'])) · PCC: {{ $fdProvRes['owning_pcc'] }}@endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        {{-- Status badge --}}
                        <span style="display:inline-flex;align-items:center;gap:5px;background:{{ $sc['bg'] }};color:{{ $sc['txt'] }};padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,.2)">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $sc['dot'] }};display:inline-block"></span>
                            {{ strtoupper($booking->status) }}
                        </span>
                        {{-- TAU Timer --}}
                        @if(!empty($tauDeadlineIso) && !$isTicketed && $booking->status !== 'cancelled')
                            <span class="tau-timer" id="tauTimerBadge" title="Ticket deadline">
                                ⏰ <span id="tauTimerTxt">--:--:--</span>
                            </span>
                        @elseif($isTicketed && $showVoidDate)
                            <span class="tau-timer" style="background:rgba(239,68,68,.25);border-color:#f87171;">
                                🚫 Void by {{ $voidDateDisplay }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- PNR block --}}
                <div class="pnr-flex-row" style="display:flex;gap:0;background:rgba(0,0,0,.2);border-radius:10px;overflow:hidden;">
                    <div style="flex:1;padding:10px 14px;border-right:1px solid rgba(255,255,255,.1);min-width:0">
                        <div style="font-size:8px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">GDS PNR</div>
                        <div style="font-size:16px;font-weight:800;font-family:monospace;letter-spacing:.12em;overflow:hidden;text-overflow:ellipsis;">{{ $fd['booking_id'] ?? $booking->pnr_id ?? '—' }}</div>
                    </div>
                    <div style="flex:1;padding:10px 14px;border-right:1px solid rgba(255,255,255,.1);min-width:0">
                        <div style="font-size:8px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">Airline PNR</div>
                        @if(!empty($gfPnr))
                            <div style="font-size:16px;font-weight:800;font-family:monospace;letter-spacing:.12em;color:#93c5fd;overflow:hidden;text-overflow:ellipsis;">{{ $gfPnr }}</div>
                            <div style="font-size:9px;color:rgba(255,255,255,.35)">{{ $fdSupplier['supplier_code'] ?? '' }}</div>
                        @else
                            <div style="font-size:14px;font-weight:500;color:rgba(255,255,255,.3)">—</div>
                        @endif
                    </div>
                    <div style="flex:1;padding:10px 14px;min-width:0">
                        @if($isTicketed && $showVoidDate)
                            <div style="font-size:8px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">Void Deadline</div>
                            <div style="font-size:13px;font-weight:700;color:#fca5a5">🚫 {{ $voidDateDisplay }}</div>
                        @elseif($isTicketed && !$showVoidDate)
                            <div style="font-size:8px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">Void Status</div>
                            <div style="font-size:12px;font-weight:600;color:rgba(255,255,255,.3)">Not Available</div>
                        @else
                            <div style="font-size:8px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:2px">Ticket Deadline</div>
                            @if(!empty($tauDeadline && $booking->status !== 'cancelled'))
                                <div style="font-size:12px;font-weight:700;color:#fde68a">⏰ {{ $tauDeadline }}</div>
                            @else
                                <div style="font-size:14px;font-weight:500;color:rgba(255,255,255,.3)">—</div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Meta row --}}
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;font-size:9.5px;color:rgba(255,255,255,.4)">
                    @if(isset($fd['is_ticketed']))<span>Ticketed: <strong style="color:{{ $fd['is_ticketed']?'#6ee7b7':'#fca5a5' }}">{{ $fd['is_ticketed']?'Yes':'No' }}</strong></span>@endif
                    @if(isset($fd['is_cancelable']))<span>Cancelable: <strong style="color:rgba(255,255,255,.7)">{{ $fd['is_cancelable']?'Yes':'No' }}</strong></span>@endif
                    @if(!empty($fdProvRes['number_of_updates']))<span>Updates: <strong style="color:rgba(255,255,255,.7)">{{ $fdProvRes['number_of_updates'] }}</strong></span>@endif
                </div>
            </div>

            {{-- ════════════════════════════════════════
                QUICK ACTIONS
           ════════════════════════════════════════ --}}
            <div style="background:white;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,.07);padding:14px 14px 16px;margin-bottom:10px">
                <div style="font-size:9.5px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.12em;margin-bottom:10px">⚡ Quick Actions</div>
                @php
                    $isIssued  = $booking->status === 'issued' || $booking->status === 'partial';
                    $isBooked  = $booking->status === 'booked';
                @endphp
                <div class="qac-wrap">

                    @if($booking->status !== 'cancelled')
                        <a href="#" onclick="openModal('bookingCopyModal');return false"
                           class="qac" style="background:linear-gradient(135deg,#0ea5e9,#0369a1)">
                            <span class="qac-icon">📋</span>
                            <span class="qac-label">{{ $booking->status === 'issued' ? 'Ticket Copy' : 'Booking Copy' }}</span>
                        </a>
                    @endif
                    {{-- Print — শুধু issued হলে দেখাবে --}}
{{--                    @if($isIssued)--}}
{{--                        <a href="#" onclick="openModal('printModal');return false"--}}
{{--                           class="qac" style="background:linear-gradient(135deg,#38bdf8,#0284c7)">--}}
{{--                            <span class="qac-icon">🖨️</span><span class="qac-label">Print Ticket</span>--}}
{{--                        </a>--}}
{{--                    @endif--}}

                    {{-- Void — issued + canVoid (issue date এর মধ্যে) হলে active --}}
                    <a href="#"
                       onclick="{{ ($isIssued && $canVoid) ? 'openModal(\'voidModal\');return false' : 'return false' }}"
                       class="qac {{ (!$isIssued || !$canVoid) ? 'disabled-card' : '' }}"
                       style="background:linear-gradient(135deg,#f87171,#dc2626)">
                        <span class="qac-icon">🚫</span><span class="qac-label">Void</span>
                    </a>

                    {{-- Refund — issued হলে active, না হলে disabled --}}
                    <a href="#"
                       onclick="{{ $isIssued ? 'openModal(\'refundModal\');return false' : 'return false' }}"
                       class="qac {{ !$isIssued ? 'disabled-card' : '' }}"
                       style="background:linear-gradient(135deg,#fbbf24,#d97706)">
                        <span class="qac-icon">💰</span><span class="qac-label">Refund</span>
                    </a>

                    {{-- Reissue — issued হলে active, না হলে disabled --}}
                    <a href="#"
                       onclick="{{ $isIssued ? 'openModal(\'reissueModal\');return false' : 'return false' }}"
                       class="qac {{ !$isIssued ? 'disabled-card' : '' }}"
                       style="background:linear-gradient(135deg,#34d399,#059669)">
                        <span class="qac-icon">🔄</span><span class="qac-label">Reissue</span>
                    </a>

                    {{-- Add SSR — issued হলে active, না হলে disabled --}}
                    <a href="#"
                       onclick="{{ $isIssued ? 'openModal(\'addsrModal\');return false' : 'return false' }}"
                       class="qac {{ !$isIssued ? 'disabled-card' : '' }}"
                       style="background:linear-gradient(135deg,#f472b6,#db2777)">
                        <span class="qac-icon">📝</span><span class="qac-label">Add SSR</span>
                    </a>

                    {{-- Cancel — শুধু booked হলে দেখাবে, issued হলে hide --}}
                    @if($isBooked)
                        <a href="{{ route('booking.cancel',$booking->id) }}"
                           onclick="return confirm('Cancel {{ $booking->code }}?\nThis cannot be undone!')"
                           class="qac" style="background:linear-gradient(135deg,#fca5a5,#ef4444)">
                            <span class="qac-icon">❌</span><span class="qac-label">Cancel</span>
                        </a>
                    @endif

                </div>
            </div>


            {{-- ════════════════════════════════════════
                 MAIN LAYOUT
            ════════════════════════════════════════ --}}
            <div class="row">
                <div class="col-md-8">

                    {{-- ══ 1. Flight Itinerary ══ --}}
                    @if(!empty($fdSegments))
                        <div class="f-section-card fade-up d2">
                            <div class="f-section-header" onclick="toggleSection(this,'sec-itinerary')">
                                <span class="f-section-title">
                                    <span class="f-section-icon" style="background:#f0f9ff;color:#0ea5e9"><i class="fas fa-route"></i></span>
                                    Flight Itinerary
                                    <span class="f-section-meta">{{ $depCity }} → {{ $arrCity }} · {{ count($fdJourneys) }} leg{{ count($fdJourneys)>1?'s':'' }}</span>
                                </span>
                                <i class="fas fa-chevron-up f-chevron"></i>
                            </div>
                            <div class="f-section-body" id="sec-itinerary">
                                @foreach($legGroups2 as $li => $legGroup)
                                    @php
                                        $journey2  = $legGroup['journey'];
                                        $legSegs2  = $legGroup['segments'];
                                        $isOut2    = $legGroup['type'] === 'outbound';
                                        $firstSeg2 = $legSegs2[0] ?? null;
                                        $lastSeg2  = end($legSegs2) ?: null;
                                        if (empty($legSegs2) || !$firstSeg2) continue;

                                        $legDepTs2   = strtotime($firstSeg2['departure_time']);
                                        $legArrTs2   = strtotime($lastSeg2['arrival_time']);
                                        $legMinutes2 = (int)(($legArrTs2 - $legDepTs2) / 60);
                                        $legDur2     = floor($legMinutes2/60).'h '.($legMinutes2%60).'m';

                                        $legBag2 = $fdPricing['per_leg_baggage'][$li] ?? null;
                                        $fcBag2  = null;
                                        foreach($fareBreakdowns as $fb2) {
                                            if (isset($fb2['fare_construction'][$li])) { $fcBag2 = $fb2['fare_construction'][$li]; break; }
                                        }
                                        $thisCheckedBag2 = $legBag2['checked_bag_kg'] ?? $fcBag2['checked_bag_kg'] ?? $checkedBag ?? null;
                                        $thisCabinBag2   = $legBag2['cabin_bag_kg']   ?? $fcBag2['cabin_bag_kg']   ?? $cabinBag  ?? null;
                                        $thisFareBasis2  = $fcBag2['fare_basis']      ?? null;
                                        $thisBrandName2  = $fcBag2['brand_fare_name'] ?? null;

                                        $accentColor2 = $isOut2 ? '#1d4ed8' : '#7c3aed';
                                        $accentLight2 = $isOut2 ? '#eff6ff'  : '#f5f3ff';
                                        $destColor2   = $isOut2 ? '#6366f1'  : '#a78bfa';
                                        $lineGrad2    = $isOut2 ? '#1d4ed8,#6366f1' : '#7c3aed,#a78bfa';
                                    @endphp

                                    <div style="margin:14px 14px {{ !$loop->last ? '8px' : '0' }};border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                        {{-- Leg header --}}
                                        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;">
                                            <div style="width:26px;height:26px;border-radius:6px;background:{{ $accentLight2 }};color:{{ $accentColor2 }};display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0">
                                                <i class="fas fa-{{ $isOut2 ? 'plane-departure' : 'plane-arrival' }}"></i>
                                            </div>
                                            <div style="flex:1;min-width:0">
                                                <div style="font-size:12px;font-weight:700;color:#0f172a">
                                                    <span style="color:{{ $accentColor2 }}">{{ $isOut2 ? '→ Outbound' : '← Return' }}</span>
                                                    <span style="color:#64748b;font-weight:400;margin-left:5px">{{ $journey2['first_airport_code'] }} → {{ $journey2['last_airport_code'] }}</span>
                                                </div>
                                                <div style="font-size:10px;color:#64748b;margin-top:1px">
                                                    {{ date('D, d M Y', strtotime($journey2['departure_date'])) }}
                                                    · {{ count($legSegs2) }} flight{{ count($legSegs2)>1?'s':'' }}
                                                    · {{ $legDur2 }} total
                                                </div>
                                            </div>
                                            <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center">
                                                @if($thisCheckedBag2)<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#ede9fe;color:#5b21b6"><i class="fas fa-suitcase" style="font-size:8px"></i> {{ $thisCheckedBag2 }}kg</span>@endif
                                                @if($thisCabinBag2)<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#ede9fe;color:#5b21b6"><i class="fas fa-briefcase" style="font-size:8px"></i> {{ $thisCabinBag2 }}kg</span>@endif
                                                @if($thisBrandName2)<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:600;background:#ede9fe;color:#5b21b6">{{ $thisBrandName2 }}</span>@endif
                                            </div>
                                        </div>

                                        {{-- Desktop timeline --}}
                                        <div class="itin-desktop-v2">
                                            <div style="display:flex;align-items:center;gap:0;min-width:300px">
                                                @foreach($legSegs2 as $si2 => $seg2)
                                                    @php
                                                        $isLastSeg2 = $si2 === count($legSegs2) - 1;
                                                        $acName2    = $seg2['aircraft_name'] ?? '';
                                                        if (strlen($acName2) <= 4 && ctype_alnum($acName2)) $acName2 = '';
                                                        $lv2 = null;
                                                        if (!$isLastSeg2) {
                                                            $nextSeg2 = $legSegs2[$si2 + 1];
                                                            $aTs2 = strtotime($seg2['arrival_time']);
                                                            $dTs2 = strtotime($nextSeg2['departure_time']);
                                                            $lvMin2 = (int)(($dTs2 - $aTs2) / 60);
                                                           $lv2 = [
                                                                'airport'   => $seg2['destination'],
                                                                'arr_time'  => $rawTime($seg2['arrival_time']),
                                                                'arr_date'  => $rawDate($seg2['arrival_time']),
                                                                'dep_time'  => $rawTime($nextSeg2['departure_time']),
                                                                'dep_date'  => $rawDate($nextSeg2['departure_time']),
                                                                'duration'  => floor($lvMin2/60).'h '.($lvMin2%60).'m',
                                                                'arr_term'  => $seg2['arrival_terminal']      ?? null,
                                                                'dep_term'  => $nextSeg2['departure_terminal'] ?? null,
                                                                'overnight' => substr($seg2['arrival_time'],0,10) !== substr($nextSeg2['departure_time'],0,10),
                                                            ];
                                                        }
                                                    @endphp
                                                    @if($si2 === 0)
                                                        <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                            {{-- NEW --}}
                                                            <div style="font-size:11px;font-weight:600;color:#64748b">{{ $rawTime($seg2['departure_time']) }}</div>
                                                            <div style="font-size:22px;font-weight:800;color:{{ $accentColor2 }};line-height:1.1">{{ $seg2['origin'] }}</div>
                                                            <div style="font-size:10px;color:#64748b">{{ $rawDate($seg2['departure_time']) }}</div>
{{--                                                            <div style="font-size:11px;font-weight:600;color:#64748b">{{ date('H:i', strtotime($seg2['departure_time'])) }}</div>--}}
{{--                                                            <div style="font-size:22px;font-weight:800;color:{{ $accentColor2 }};line-height:1.1">{{ $seg2['origin'] }}</div>--}}
{{--                                                            <div style="font-size:10px;color:#64748b">{{ date('D d M', strtotime($seg2['departure_time'])) }}</div>--}}
                                                            @if(!empty($seg2['departure_terminal']))
                                                                <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:#64748b">
                                                                    T{{ $seg2['departure_terminal'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div style="flex:1;display:flex;flex-direction:column;padding:0 4px;min-width:120px">
                                                        <div style="display:flex;align-items:center;margin-top:18px">
                                                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $si2===0?$accentColor2:'#fde68a' }};flex-shrink:0"></div>
                                                            <div style="flex:1;height:2px;background:linear-gradient(90deg,{{ $lineGrad2 }});position:relative">
                                                                <span style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:0 4px;font-size:11px;color:{{ $accentColor2 }}">✈</span>
                                                            </div>
                                                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $isLastSeg2?$destColor2:'#fde68a' }};flex-shrink:0"></div>
                                                        </div>
                                                        <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:5px;justify-content:center">
                                                            <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569;font-family:monospace">{{ $seg2['carrier'] }}{{ $seg2['flight_number'] }}</span>
                                                            <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $seg2['cabin_class'] }}</span>

                                                            @php
                                                                $dur2 = (int)($seg2['travel_time']??0);
                                                            @endphp
                                                            <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ floor($dur2/60) }}h {{ $dur2%60 }}m</span>
                                                            @if($acName2)
                                                                <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">
                                                                    {{ $acName2 }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($seg2['meals']))
                                                                @foreach($seg2['meals'] as $meal2)
                                                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#fff7ed;color:#c2410c">
                                                                        <i class="fas fa-utensils" style="font-size:7px"></i>
                                                                        {{ $meal2['description']??$meal2['code'] }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                            <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:{{ in_array($seg2['status'],['HK','KK'])?'#dcfce7':'#fef3c7' }};color:{{ in_array($seg2['status'],['HK','KK'])?'#166534':'#92400e' }}">{{ $seg2['status_name']??$seg2['status'] }}</span>
                                                        </div>
                                                    </div>
                                                    @if($lv2)
                                                        <div style="flex-shrink:0;text-align:center;min-width:76px">
                                                            <div style="font-size:10px;font-weight:600;color:#64748b">{{ $lv2['arr_time'] }}</div>
                                                            <div style="font-size:18px;font-weight:800;color:#92400e;line-height:1.1">{{ $lv2['airport'] }}</div>
                                                            <div style="font-size:10px;color:#64748b">{{ $lv2['arr_date'] }}</div>
                                                            @if($lv2['arr_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 4px;display:inline-block;color:#64748b">Arr T{{ $lv2['arr_term'] }}</div>@endif
                                                            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:3px 8px;margin:4px 0;display:inline-block">
                                                                <div style="font-size:10px;font-weight:700;color:#d97706"><i class="fas fa-clock" style="font-size:9px"></i> {{ $lv2['duration'] }}</div>
                                                                @if($lv2['overnight'])<div style="font-size:9px;font-weight:700;color:#dc2626">Overnight</div>@endif
                                                            </div>
                                                            <div style="font-size:10px;font-weight:600;color:#64748b">{{ $lv2['dep_time'] }}</div>
                                                            @if($lv2['dep_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 4px;display:inline-block;color:#64748b">Dep T{{ $lv2['dep_term'] }}</div>@endif
                                                        </div>
                                                    @else
                                                        <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                            {{-- NEW --}}
                                                            <div style="font-size:11px;font-weight:600;color:#64748b">{{ $rawTime($seg2['arrival_time']) }}</div>
                                                            <div style="font-size:22px;font-weight:800;color:{{ $destColor2 }};line-height:1.1">{{ $seg2['destination'] }}</div>
                                                            <div style="font-size:10px;color:#64748b">{{ $rawDate($seg2['arrival_time']) }}</div>
{{--                                                            <div style="font-size:11px;font-weight:600;color:#64748b">{{ date('H:i', strtotime($seg2['arrival_time'])) }}</div>--}}
{{--                                                            <div style="font-size:22px;font-weight:800;color:{{ $destColor2 }};line-height:1.1">{{ $seg2['destination'] }}</div>--}}
{{--                                                            <div style="font-size:10px;color:#64748b">{{ date('D d M', strtotime($seg2['arrival_time'])) }}</div>--}}
                                                            @if(!empty($seg2['arrival_terminal']))
                                                                <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:#64748b">
                                                                    T{{ $seg2['arrival_terminal'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Mobile vertical timeline --}}
                                        <div class="itin-mobile-v2">
                                            <div class="vtl2">
                                                <div class="vtl2-vline"></div>
                                                @foreach($legSegs2 as $si2 => $seg2)
                                                    @php
                                                        $isLastSeg2 = $si2 === count($legSegs2) - 1;
                                                        $acName2m   = $seg2['aircraft_name'] ?? '';
                                                        if (strlen($acName2m) <= 4 && ctype_alnum($acName2m)) $acName2m = '';
                                                        $lv2m = null;
                                                        if (!$isLastSeg2) {
                                                            $ns2m  = $legSegs2[$si2+1];
                                                            $aT2m  = strtotime($seg2['arrival_time']);
                                                            $dT2m  = strtotime($ns2m['departure_time']);
                                                            $lMin2m= (int)(($dT2m-$aT2m)/60);
                                                                $lv2m = [
                                                                    'airport'  => $seg2['destination'],
                                                                    'arr_time' => $rawTime($seg2['arrival_time']),
                                                                    'dep_time' => $rawTime($ns2m['departure_time']),
                                                                    'arr_date' => $rawDate($seg2['arrival_time']),
                                                                    'dep_date' => $rawDate($ns2m['departure_time']),
                                                                    'duration' => floor($lMin2m/60).'h '.($lMin2m%60).'m',
                                                                    'arr_term' => $seg2['arrival_terminal']   ?? null,
                                                                    'dep_term' => $ns2m['departure_terminal'] ?? null,
                                                                    'overnight'=> substr($seg2['arrival_time'],0,10) !== substr($ns2m['departure_time'],0,10),
                                                                ];
                                                        }
                                                    @endphp
                                                    @if($si2 === 0)
                                                        <div style="position:relative;padding:6px 0">
                                                            <div class="vtl2-dot" style="background:{{ $accentColor2 }}"></div>
                                                            {{-- NEW --}}
                                                            <div style="display:flex;align-items:baseline;gap:6px">
                                                                <div style="font-size:20px;font-weight:700;color:{{ $accentColor2 }};line-height:1">{{ $seg2['origin'] }}</div>
                                                                <div style="font-size:12px;font-weight:500;color:#64748b">{{ $rawTime($seg2['departure_time']) }}</div>
                                                            </div>
                                                            <div style="font-size:10px;color:#64748b;margin-top:1px">{{ $rawDateFull($seg2['departure_time']) }}{{ !empty($seg2['departure_terminal'])?' · T'.$seg2['departure_terminal']:'' }}</div>
                                                        </div>
                                                    @endif
                                                    <div class="vtl2-flight">
                                                        <div class="vtl2-flight-inner">
                                                            <div style="font-size:11px;font-weight:600;color:#0f172a;margin-bottom:5px"><span style="color:{{ $accentColor2 }}">✈</span> {{ $seg2['carrier'] }}{{ $seg2['flight_number'] }} · {{ $seg2['airline_name']??$seg2['carrier'] }}</div>
                                                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                                                                <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $seg2['cabin_class'] }}</span>
                                                                @php $d2m=(int)($seg2['travel_time']??0); @endphp
                                                                <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ $seg2['class_of_service'] }} · {{ floor($d2m/60) }}h {{ $d2m%60 }}m</span>
                                                                @if($acName2m)<span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ $acName2m }}</span>@endif
                                                                <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:{{ in_array($seg2['status'],['HK','KK'])?'#dcfce7':'#fef3c7' }};color:{{ in_array($seg2['status'],['HK','KK'])?'#166534':'#92400e' }}">{{ $seg2['status_name']??$seg2['status'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($lv2m)
                                                        <div class="vtl2-lv">
                                                            <div class="vtl2-lv-dot"></div>
                                                            <div class="vtl2-lv-inner">
                                                                <div style="font-size:16px;font-weight:700;color:#92400e">{{ $lv2m['airport'] }}</div>
                                                                <div style="flex:1">
                                                                    <div style="font-size:11px;font-weight:600;color:#d97706"><i class="fas fa-clock" style="font-size:9px"></i> {{ $lv2m['duration'] }} layover @if($lv2m['overnight'])<span style="font-size:9px;color:#dc2626;background:#fee2e2;border-radius:4px;padding:1px 5px;margin-left:3px">Overnight</span>@endif</div>
                                                                    <div style="font-size:10px;color:#a16207;margin-top:1px">Arr {{ $lv2m['arr_time'] }} {{ $lv2m['arr_date'] }}@if($lv2m['arr_term']) · T{{ $lv2m['arr_term'] }}@endif · Dep {{ $lv2m['dep_time'] }}@if($lv2m['dep_term']) T{{ $lv2m['dep_term'] }}@endif</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($isLastSeg2)
                                                        <div style="position:relative;padding:6px 0">
                                                            <div class="vtl2-dot" style="background:{{ $destColor2 }}"></div>
                                                            {{-- NEW --}}
                                                            <div style="display:flex;align-items:baseline;gap:6px">
                                                                <div style="font-size:20px;font-weight:700;color:{{ $destColor2 }};line-height:1">{{ $seg2['destination'] }}</div>
                                                                <div style="font-size:12px;font-weight:500;color:#64748b">{{ $rawTime($seg2['arrival_time']) }}</div>
                                                            </div>
                                                            <div style="font-size:10px;color:#64748b;margin-top:1px">{{ $rawDateFull($seg2['arrival_time']) }}{{ !empty($seg2['arrival_terminal'])?' · T'.$seg2['arrival_terminal']:'' }}</div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if(!empty($checkedBag) || !empty($cabinBag))
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;padding:10px 14px 14px">
                                        @if(!empty($checkedBag))<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:#e0e7ff;color:#4338ca"><i class="fas fa-suitcase" style="font-size:9px"></i> Checked: {{ $checkedBag }}kg</span>@endif
                                        @if(!empty($cabinBag))<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;background:#e0e7ff;color:#4338ca"><i class="fas fa-briefcase" style="font-size:9px"></i> Cabin: {{ $cabinBag }}kg</span>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ══ 2. Passengers ══ --}}
{{--                    @if(!empty($fdPassengers)) ... @endif  --}}

                    @if($passengers->isNotEmpty())
                        <div class="f-section-card fade-up d3">
                            <div class="f-section-header" onclick="toggleSection(this,'sec-passengers')">
            <span class="f-section-title">
                <span class="f-section-icon" style="background:#eef2ff;color:#4f46e5">
                    <i class="fas fa-users"></i>
                </span>
                Passengers
                <span class="f-section-meta">({{ $passengers->count() }})</span>
            </span>
                                <i class="fas fa-chevron-up f-chevron"></i>
                            </div>

                            <div class="f-section-body" id="sec-passengers">

                                {{-- ── DESKTOP TABLE ── --}}
                                <div class="pax-table-wrap pax-desktop">
                                    <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                                        <thead>
                                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">#</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Passenger</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Type</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">DOB</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Passport</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Expiry</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Ticket No.</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Status</th>
                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em">Docs</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($passengers as $pi => $pax)
                                            @php
                                                /* ── type ── */
                                                $dbType   = strtoupper($pax->traveler_type ?? $pax->passenger_type_code ?? 'ADT');
                                                $isAdult  = $dbType === 'ADT';
                                                $isChild  = in_array($dbType, ['CNN','CHD','C07','C05','C03']);
                                                $isInfant = $dbType === 'INF';

                                                $avatarBg   = $isAdult ? '#4F46E5' : ($isChild ? '#059669' : '#F97316');
                                                $typePillBg = $isAdult ? '#eef2ff' : ($isChild ? '#ecfdf5' : '#fff7ed');
                                                $typePillTx = $isAdult ? '#4338ca' : ($isChild ? '#065f46' : '#9a3412');
                                                $typeLabel  = $isAdult ? 'Adult' : ($isChild ? 'Child' : ($isInfant ? 'Infant' : $dbType));

                                                $initial = strtoupper(substr($pax->first_name ?? 'P', 0, 1));
                                                $gender  = strtoupper($pax->gender ?? '');

                                                /* ── passport expiry ── */
                                                $passExp    = $pax->passport_expiry_date ?? null;
                                                $isExpiring = $passExp && strtotime($passExp) < strtotime('+6 months');

                                                /* ── ticket info (DB column) ── */
                                                $tktNo   = $pax->ticket_number ?? '';
                                                $tktStat = strtoupper($pax->status ?? '');

                                                /* ── badge class/label ── */
                                                $badgeCls = match(true) {
                                                    in_array($tktStat, ['TV','VOID','VOIDED'])            => 'tkt-void',
                                                    in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']) => 'tkt-refund',
                                                    in_array($tktStat, ['TI','TKTT','ISSUED','PENDING'])  => 'tkt-issued',
                                                    in_array($tktStat, ['OK','OPEN','TK','HK',''])        => 'tkt-open',
                                                    default                                                => 'tkt-unknown',
                                                };
                                                $badgeLbl = match(true) {
                                                    in_array($tktStat, ['TV','VOID','VOIDED'])            => '🚫 Voided',
                                                    in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']) => '💰 Refunded',
                                                    in_array($tktStat, ['TI','TKTT','ISSUED'])            => '✅ Issued',
                                                    $tktStat === 'PENDING'                                => '⏳ Pending',
                                                    in_array($tktStat, ['OK','OPEN','TK','HK'])           => '✅ Open',
                                                    $tktStat === ''                                        => '—',
                                                    default                                                => ucfirst(strtolower($tktStat)),
                                                };

                                                $isVoided   = in_array($tktStat, ['TV','VOID','VOIDED']);
                                                $isRefunded = in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']);
                                                $rowCls     = $isVoided ? 'pax-row-void' : ($isRefunded ? 'pax-row-refund' : '');
                                            @endphp

                                            <tr class="{{ $rowCls }}" style="border-bottom:1px solid #f1f5f9;">
                                                {{-- # --}}
                                                <td style="padding:11px 14px;color:#94a3b8;font-size:11px;">{{ $pi + 1 }}</td>

                                                {{-- Name --}}
                                                <td style="padding:11px 14px;">
                                                    <div style="display:flex;align-items:center;gap:9px;">
                                                        <div style="width:32px;height:32px;border-radius:50%;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">
                                                            {{ $initial }}
                                                        </div>
                                                        <div>
                                                            <div style="font-weight:600;color:#1e293b;font-size:13px;">
                                                                {{ strtoupper(trim(($pax->title ? $pax->title.' ' : '').($pax->first_name ?? '').' '.($pax->last_name ?? ''))) }}
                                                            </div>
                                                            <div style="font-size:10px;color:#94a3b8;">
                                                                <i class="fas fa-{{ in_array($gender,['F','FEMALE']) ? 'venus' : 'mars' }}"></i>
                                                                {{ in_array($gender,['F','FEMALE']) ? 'Female' : 'Male' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Type --}}
                                                <td style="padding:11px 14px;">
                                <span style="display:inline-flex;align-items:center;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600;background:{{ $typePillBg }};color:{{ $typePillTx }}">
                                    {{ $typeLabel }}
                                </span>
                                                </td>

                                                {{-- DOB --}}
                                                <td style="padding:11px 14px;color:#64748b;font-size:11px;">
                                                    {{ $pax->dob ? \Carbon\Carbon::parse($pax->dob)->format('d M Y') : '—' }}
                                                </td>

                                                {{-- Passport --}}
                                                <td style="padding:11px 14px;">
                                <span style="font-family:monospace;font-size:11px;font-weight:600;color:#334155;letter-spacing:.08em">
                                    {{ $pax->passport_number ?? '—' }}
                                </span>
                                                </td>

                                                {{-- Expiry --}}
                                                <td style="padding:11px 14px;font-size:11px;">
                                                    @if($passExp)
                                                        <span style="color:{{ $isExpiring ? '#dc2626' : '#64748b' }};font-weight:{{ $isExpiring ? '700' : '400' }}">
                                        {{ \Carbon\Carbon::parse($passExp)->format('d M Y') }}
                                    </span>
                                                    @else
                                                        <span style="color:#cbd5e1">—</span>
                                                    @endif
                                                </td>

                                                {{-- Ticket No. --}}
                                                <td style="padding:11px 14px;">
                                                    @if($tktNo)
                                                        <span style="font-family:monospace;font-size:11px;font-weight:700;color:#0369a1;letter-spacing:.05em">
                                        {{ $tktNo }}
                                    </span>
                                                    @else
                                                        <span style="color:#cbd5e1;font-size:11px">—</span>
                                                    @endif
                                                </td>

                                                {{-- Status --}}
                                                <td style="padding:11px 14px;">
                                                    <span class="pill {{ $badgeCls }}" style="font-size:10.5px;">{{ $badgeLbl }}</span>
                                                </td>

                                                {{-- Docs --}}
                                                <td style="padding:11px 14px;">
                                                    <button
                                                        onclick="openPaxDocs({{ $pi }}, '{{ addslashes(strtoupper(trim(($pax->first_name ?? '').' '.($pax->last_name ?? '')))) }}')"
                                                        style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;border:none;background:#ede9fe;color:#5b21b6;font-size:11px;font-weight:600;cursor:pointer;transition:background .15s"
                                                        onmouseover="this.style.background='#ddd6fe'"
                                                        onmouseout="this.style.background='#ede9fe'">
                                                        📎 Docs
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- ── MOBILE CARDS ── --}}
                                <div class="pax-mobile" style="padding:12px">
                                    @foreach($passengers as $pi => $pax)
                                        @php
                                            $dbType   = strtoupper($pax->traveler_type ?? $pax->passenger_type_code ?? 'ADT');
                                            $isAdult  = $dbType === 'ADT';
                                            $isChild  = in_array($dbType, ['CNN','CHD','C07','C05','C03']);
                                            $isInfant = $dbType === 'INF';
                                            $avatarBg  = $isAdult ? '#4F46E5' : ($isChild ? '#059669' : '#F97316');
                                            $typePillBg2 = $isAdult ? '#eef2ff' : ($isChild ? '#ecfdf5' : '#fff7ed');
                                            $typePillTx2 = $isAdult ? '#4338ca' : ($isChild ? '#065f46' : '#9a3412');
                                            $typeLabel = $isAdult ? 'Adult' : ($isChild ? 'Child' : ($isInfant ? 'Infant' : $dbType));
                                            $initial   = strtoupper(substr($pax->first_name ?? 'P', 0, 1));
                                            $gender    = strtoupper($pax->gender ?? '');
                                            $passExp   = $pax->passport_expiry_date ?? null;
                                            $isExpiring= $passExp && strtotime($passExp) < strtotime('+6 months');
                                            $tktNo     = $pax->ticket_number ?? '';
                                            $tktStat   = strtoupper($pax->status ?? '');
                                            $badgeCls  = match(true) {
                                                in_array($tktStat, ['TV','VOID','VOIDED'])            => 'tkt-void',
                                                in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']) => 'tkt-refund',
                                                in_array($tktStat, ['TI','TKTT','ISSUED','PENDING'])  => 'tkt-issued',
                                                default                                                => 'tkt-open',
                                            };
                                            $badgeLbl  = match(true) {
                                                in_array($tktStat, ['TV','VOID','VOIDED'])            => '🚫 Voided',
                                                in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']) => '💰 Refunded',
                                                in_array($tktStat, ['TI','TKTT','ISSUED'])            => '✅ Issued',
                                                $tktStat === 'PENDING'                                => '⏳ Pending',
                                                in_array($tktStat, ['OK','OPEN','TK','HK',''])        => '✅ Open',
                                                default                                                => ucfirst(strtolower($tktStat)),
                                            };
                                            $isVoided   = in_array($tktStat, ['TV','VOID','VOIDED']);
                                            $isRefunded = in_array($tktStat, ['RF','RFND','REFUND','REFUNDED']);
                                            $cardCls    = $isVoided ? 'voided' : ($isRefunded ? 'refunded' : '');
                                        @endphp

                                        <div class="pax-card {{ $cardCls }}">
                                            {{-- Card Header --}}
                                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                                <div style="width:38px;height:38px;border-radius:50%;background:{{ $avatarBg }};display:flex;align-items:center;justify-content:center;color:white;font-size:14px;font-weight:700;flex-shrink:0">
                                                    {{ $initial }}
                                                </div>
                                                <div style="flex:1;min-width:0">
                                                    <div style="font-weight:700;font-size:14px;color:#0f172a">
                                                        {{ strtoupper(trim(($pax->title ? $pax->title.' ' : '').($pax->first_name ?? '').' '.($pax->last_name ?? ''))) }}
                                                    </div>
                                                    <div style="display:flex;gap:6px;align-items:center;margin-top:3px;flex-wrap:wrap">
                                    <span style="display:inline-flex;align-items:center;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:600;background:{{ $typePillBg2 }};color:{{ $typePillTx2 }}">
                                        {{ $typeLabel }}
                                    </span>
                                                        <span style="font-size:10px;color:#64748b">
                                        <i class="fas fa-{{ in_array($gender,['F','FEMALE']) ? 'venus' : 'mars' }}"></i>
                                        {{ in_array($gender,['F','FEMALE']) ? 'Female' : 'Male' }}
                                    </span>
                                                    </div>
                                                </div>
                                                <span class="pill {{ $badgeCls }}" style="font-size:10px;flex-shrink:0">{{ $badgeLbl }}</span>
                                            </div>

                                            {{-- Info Grid --}}
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">

                                                {{-- Docs button --}}
                                                <div style="grid-column:span 2;display:flex;justify-content:flex-end;margin-bottom:2px">
                                                    <button
                                                        onclick="openPaxDocs({{ $pi }}, '{{ addslashes(strtoupper(trim(($pax->first_name ?? '').' '.($pax->last_name ?? '')))) }}')"
                                                        style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;border:none;background:#ede9fe;color:#5b21b6;font-size:11px;font-weight:600;cursor:pointer;">
                                                        📎 View Documents
                                                    </button>
                                                </div>

                                                @if($pax->dob)
                                                    <div style="background:#f8fafc;border-radius:8px;padding:8px 10px">
                                                        <div style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">Date of Birth</div>
                                                        <div style="font-size:12px;font-weight:600;color:#334155">{{ \Carbon\Carbon::parse($pax->dob)->format('d M Y') }}</div>
                                                    </div>
                                                @endif

                                                @if($pax->passport_number)
                                                    <div style="background:#f8fafc;border-radius:8px;padding:8px 10px">
                                                        <div style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">Passport No.</div>
                                                        <div style="font-size:12px;font-weight:700;color:#334155;font-family:monospace;letter-spacing:.06em">{{ $pax->passport_number }}</div>
                                                    </div>
                                                @endif

                                                @if($passExp)
                                                    <div style="background:{{ $isExpiring ? '#fff1f2' : '#f8fafc' }};border-radius:8px;padding:8px 10px;border:1px solid {{ $isExpiring ? '#fecaca' : 'transparent' }}">
                                                        <div style="font-size:9px;color:{{ $isExpiring ? '#dc2626' : '#94a3b8' }};text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">Expiry</div>
                                                        <div style="font-size:12px;font-weight:{{ $isExpiring ? 700 : 600 }};color:{{ $isExpiring ? '#dc2626' : '#334155' }}">{{ \Carbon\Carbon::parse($passExp)->format('d M Y') }}</div>
                                                    </div>
                                                @endif

                                                @if($pax->country)
                                                    <div style="background:#f8fafc;border-radius:8px;padding:8px 10px">
                                                        <div style="font-size:9px;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">Nationality</div>
                                                        <div style="font-size:12px;font-weight:600;color:#334155">{{ strtoupper($pax->country) }}</div>
                                                    </div>
                                                @endif

                                                @if($tktNo)
                                                    <div style="background:#f0f9ff;border-radius:8px;padding:8px 10px;border:1px solid #bae6fd;grid-column:span 2">
                                                        <div style="font-size:9px;color:#0369a1;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px">Ticket Number</div>
                                                        <div style="font-size:13px;font-weight:700;color:#0369a1;font-family:monospace;letter-spacing:.06em">{{ $tktNo }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>{{-- /#sec-passengers --}}
                        </div>
                    @endif

                    {{-- ══ 3. Fare Tabs ══ --}}
                    @if(!empty($fareBreakdowns) || !empty($fdFareRules) || !empty($fdSpecialSvc))
                        <div class="f-section-card fade-up d3">
                            <div class="fare-tab-bar" style="display:flex;gap:2px;border-bottom:1px solid #f1f5f9;padding:10px 14px 0;background:#f8fafc;border-radius:14px 14px 0 0;">
{{--                                <button onclick="switchFareTab2('fare-summary2',this)" class="fare2-tab-btn" id="fare2-btn-summary"--}}
{{--                                        style="padding:7px 13px;font-size:11.5px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid #0ea5e9;color:#0ea5e9;margin-bottom:-1px;border-radius:5px 5px 0 0;white-space:nowrap">--}}
{{--                                    <i class="fas fa-receipt" style="margin-right:4px"></i>Fare Summary--}}
{{--                                </button>--}}
                                @if(!empty($fdFareRules))
                                    <button onclick="switchFareTab2('fare-rules2',this)" class="fare2-tab-btn"
                                            style="padding:7px 13px;font-size:11.5px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#94a3b8;margin-bottom:-1px;border-radius:5px 5px 0 0;white-space:nowrap">
                                        <i class="fas fa-gavel" style="margin-right:4px"></i>Rules
                                    </button>
                                    <button onclick="switchFareTab2('fare-penalties2',this)" class="fare2-tab-btn"
                                            style="padding:7px 13px;font-size:11.5px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#94a3b8;margin-bottom:-1px;border-radius:5px 5px 0 0;white-space:nowrap">
                                        <i class="fas fa-ban" style="margin-right:4px"></i>Penalties
                                    </button>
                                @endif
                                @if(!empty($fdSpecialSvc))
                                    <button onclick="switchFareTab2('fare-ssr2',this)" class="fare2-tab-btn"
                                            style="padding:7px 13px;font-size:11.5px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:#94a3b8;margin-bottom:-1px;border-radius:5px 5px 0 0;white-space:nowrap">
                                        <i class="fas fa-concierge-bell" style="margin-right:4px"></i>SSR
                                        <span style="background:#e0f2fe;color:#0369a1;border-radius:999px;padding:1px 6px;font-size:9px;margin-left:3px">{{ count($fdSpecialSvc) }}</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Fare Summary --}}
{{--                            <div id="fare-summary2" class="fare2-panel">--}}
{{--                                <div style="overflow-x:auto">--}}
{{--                                    <table style="width:100%;border-collapse:collapse;font-size:12.5px">--}}
{{--                                        <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">--}}
{{--                                            <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Passenger</th>--}}
{{--                                            <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Base</th>--}}
{{--                                            <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Tax</th>--}}
{{--                                            <th style="padding:10px 14px;text-align:right;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Total</th>--}}
{{--                                        </tr></thead>--}}
{{--                                        <tbody>--}}
{{--                                        @foreach($fareBreakdowns as $fb)--}}
{{--                                            @php--}}
{{--                                                $fbType = $fb['traveler_type'] ?? 'ADT';--}}
{{--                                                $fbBase = (float)($fb['subtotal']??0);--}}
{{--                                                $fbTax  = (float)($fb['taxes']??0);--}}
{{--                                                $fbTot  = (float)($fb['total']??0);--}}
{{--                                                $fbCur  = $fb['currency'] ?? $totalCurrency;--}}
{{--                                                $paxIdxs= $fb['traveler_indices'] ?? [];--}}
{{--                                                $paxLbl6= ['ADT'=>'Adult','CNN'=>'Child','CHD'=>'Child','C07'=>'Child','INF'=>'Infant'];--}}
{{--                                                $fbPaxList = collect($fdPassengers)->filter(fn($p,$i)=>empty($paxIdxs)||in_array($i+1,$paxIdxs));--}}
{{--                                            @endphp--}}
{{--                                            @foreach($fbPaxList as $bp)--}}
{{--                                                <tr style="border-bottom:1px solid #f8fafc">--}}
{{--                                                    <td style="padding:10px 14px">--}}
{{--                                                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:999px;font-size:10.5px;font-weight:600;background:{{ $fbType==='ADT'?'#eef2ff':($fbType==='INF'?'#fff7ed':'#ecfdf5') }};color:{{ $fbType==='ADT'?'#4338ca':($fbType==='INF'?'#9a3412':'#065f46') }}">{{ $paxLbl6[$fbType]??$fbType }}</span>--}}
{{--                                                        <span style="margin-left:8px;color:#475569;font-size:12px">{{ trim(($bp['first_name']??'').' '.($bp['last_name']??'')) }}</span>--}}
{{--                                                    </td>--}}
{{--                                                    <td style="padding:10px 14px;text-align:right;color:#64748b;font-size:12px">{{ $fbCur }} {{ number_format($fbBase,2) }}</td>--}}
{{--                                                    <td style="padding:10px 14px;text-align:right;color:#64748b;font-size:12px">{{ $fbCur }} {{ number_format($fbTax,2) }}</td>--}}
{{--                                                    <td style="padding:10px 14px;text-align:right;font-weight:700;color:#e11d48">{{ $fbCur }} {{ number_format($fbTot,2) }}</td>--}}
{{--                                                </tr>--}}
{{--                                            @endforeach--}}
{{--                                        @endforeach--}}
{{--                                        </tbody>--}}
{{--                                        <tfoot><tr style="background:#fff1f2">--}}
{{--                                            <td colspan="3" style="padding:11px 14px;text-align:right;font-weight:700;color:#334155">Grand Total</td>--}}
{{--                                            <td style="padding:11px 14px;text-align:right;font-weight:800;color:#e11d48;font-size:14px">{{ $totalCurrency }} {{ number_format((float)$totalAmount,2) }}</td>--}}
{{--                                        </tr></tfoot>--}}
{{--                                    </table>--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            @if(!empty($fdFareRules))
                                <div id="fare-rules2" class="fare2-panel hidden">
                                    <div style="overflow-x:auto">
                                        <table style="width:100%;border-collapse:collapse;font-size:12px">
                                            <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Airline · Pax</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Refundable</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Changeable</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Refund Penalty</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Change Penalty</th>
                                            </tr></thead>
                                            <tbody>
                                            @foreach($fdFareRules as $rule)
                                                @php $rp0=$rule['refund_penalties'][0]??null;$ep0=$rule['exchange_penalties'][0]??null;$pL7=['ADT'=>'Adult','CNN'=>'Child','CHD'=>'Child','INF'=>'Infant']; @endphp
                                                <tr style="border-bottom:1px solid #f8fafc">
                                                    <td style="padding:10px 14px;color:#64748b;font-size:11px">{{ $rule['airline']??'' }} · {{ $pL7[$rule['passenger_code']??'']??($rule['passenger_code']??'') }}</td>
                                                    <td style="padding:10px 14px"><span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:{{ ($rule['is_refundable']??false)?'#dcfce7':'#fee2e2' }};color:{{ ($rule['is_refundable']??false)?'#166534':'#991b1b' }}"><i class="fas fa-{{ ($rule['is_refundable']??false)?'check':'times' }}" style="font-size:9px"></i> {{ ($rule['is_refundable']??false)?'Yes':'No' }}</span></td>
                                                    <td style="padding:10px 14px"><span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:{{ ($rule['is_changeable']??false)?'#dcfce7':'#fee2e2' }};color:{{ ($rule['is_changeable']??false)?'#166534':'#991b1b' }}"><i class="fas fa-{{ ($rule['is_changeable']??false)?'check':'times' }}" style="font-size:9px"></i> {{ ($rule['is_changeable']??false)?'Yes':'No' }}</span></td>
                                                    <td style="padding:10px 14px">@if($rp0)<span style="font-weight:700;color:#dc2626;font-size:12px">{{ $rp0['penalty_currency']??'' }} {{ number_format((float)($rp0['penalty_amount']??0)) }}</span><div style="font-size:10px;color:#94a3b8;margin-top:2px">{{ str_replace('_',' ',$rp0['applicability']??'') }}</div>@else<span style="color:#cbd5e1;font-size:11px">—</span>@endif</td>
                                                    <td style="padding:10px 14px">@if($ep0)<span style="font-weight:700;color:#2563eb;font-size:12px">{{ $ep0['penalty_currency']??'' }} {{ number_format((float)($ep0['penalty_amount']??0)) }}</span><div style="font-size:10px;color:#94a3b8;margin-top:2px">{{ str_replace('_',' ',$ep0['applicability']??'') }}</div>@else<span style="color:#cbd5e1;font-size:11px">—</span>@endif</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="fare-penalties2" class="fare2-panel hidden">
                                    <div style="overflow-x:auto">
                                        <table style="width:100%;border-collapse:collapse;font-size:12px">
                                            <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Type</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">When</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Amount</th>
                                            </tr></thead>
                                            <tbody>
                                            @foreach($fdFareRules as $rule)
                                                @foreach($rule['refund_penalties']??[] as $p)
                                                    <tr style="border-bottom:1px solid #f8fafc"><td style="padding:10px 14px"><span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:#fee2e2;color:#991b1b"><i class="fas fa-undo" style="font-size:8px"></i> Cancel</span></td><td style="padding:10px 14px;font-size:11px;color:#64748b">{{ str_replace('_',' ',$p['applicability']??'') }}</td><td style="padding:10px 14px;font-weight:700;color:#dc2626">{{ $p['penalty_currency']??'' }} {{ number_format((float)($p['penalty_amount']??0)) }}</td></tr>
                                                @endforeach
                                                @foreach($rule['exchange_penalties']??[] as $p)
                                                    <tr style="border-bottom:1px solid #f8fafc"><td style="padding:10px 14px"><span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af"><i class="fas fa-exchange-alt" style="font-size:8px"></i> Change</span></td><td style="padding:10px 14px;font-size:11px;color:#64748b">{{ str_replace('_',' ',$p['applicability']??'') }}</td><td style="padding:10px 14px;font-weight:700;color:#2563eb">{{ $p['penalty_currency']??'' }} {{ number_format((float)($p['penalty_amount']??0)) }}</td></tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($fdSpecialSvc))
                                <div id="fare-ssr2" class="fare2-panel hidden">
                                    <div style="overflow-x:auto">
                                        <table style="width:100%;border-collapse:collapse;font-size:12px">
                                            <thead><tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Code</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Service</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Message</th>
                                                <th style="padding:10px 14px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase">Status</th>
                                            </tr></thead>
                                            <tbody>
                                            @foreach($fdSpecialSvc as $svc)
                                                <tr style="border-bottom:1px solid #f8fafc">
                                                    <td style="padding:10px 14px"><span style="font-family:monospace;font-size:11px;font-weight:700;background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:4px">{{ $svc['code']??'' }}</span></td>
                                                    <td style="padding:10px 14px;font-size:11px;color:#475569">{{ $svc['name']??'' }}</td>
                                                    <td style="padding:10px 14px;font-family:monospace;font-size:10px;color:#94a3b8;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $svc['message']??'—' }}</td>
                                                    <td style="padding:10px 14px"><span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:{{ ($svc['status_code']??'')==='HK'?'#dcfce7':'#fef3c7' }};color:{{ ($svc['status_code']??'')==='HK'?'#166534':'#92400e' }}">{{ $svc['status_name']??($svc['status_code']??'') }}</span></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ══ 4. Important Reminders ══ --}}
                    <div class="f-section-card fade-up d5" style="border-color:#fde68a">
                        <div class="f-section-header" style="background:#fffbeb" onclick="toggleSection(this,'sec-reminders')">
                            <span class="f-section-title">
                                <span class="f-section-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-exclamation-triangle"></i></span>
                                <span style="color:#92400e">Important Reminders</span>
                            </span>
                            <i class="fas fa-chevron-up f-chevron" style="color:#f59e0b"></i>
                        </div>
                        <div class="f-section-body" id="sec-reminders" style="background:#fffbeb">
                            <div style="padding:14px 18px">
                                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px">
                                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#92400e"><i class="fas fa-circle" style="font-size:4px;color:#fbbf24;flex-shrink:0;margin-top:5px"></i>Arrive at least <strong style="margin:0 2px">3 hours before</strong> departure for international flights.</li>
                                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#92400e"><i class="fas fa-circle" style="font-size:4px;color:#fbbf24;flex-shrink:0;margin-top:5px"></i>Carry a valid <strong style="margin:0 2px">passport & photo ID</strong> for all passengers.</li>
                                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#92400e"><i class="fas fa-circle" style="font-size:4px;color:#fbbf24;flex-shrink:0;margin-top:5px"></i>Checked: <strong style="margin:0 2px">{{ $checkedBag??'—' }}kg</strong>@if(!empty($cabinBag)) · Cabin: <strong style="margin:0 2px">{{ $cabinBag }}kg</strong>@endif per passenger.</li>
                                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#92400e"><i class="fas fa-circle" style="font-size:4px;color:#fbbf24;flex-shrink:0;margin-top:5px"></i>For changes or cancellations, contact support promptly.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:center;padding:8px 0 16px">
                        <a href="{{ route('user.booking_history') }}" class="button h-60 px-24 -dark-1 bg-blue-1 text-white">
                            {{ __('Booking History') }}<div class="icon-arrow-top-right ml-15"></div>
                        </a>
                    </div>
                </div>

                {{-- ════════ SIDEBAR ════════ --}}
                <div class="col-md-4">
                    <div class="payment-sticky" style="top:90px">

                        {{-- Payment Summary --}}
                        <div class="card fade-up d2">
                            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:9px">
                                <div style="width:28px;height:28px;border-radius:7px;background:#ecfdf5;display:flex;align-items:center;justify-content:center"><i class="fas fa-receipt" style="color:#059669;font-size:11px"></i></div>
                                <h2 style="font-family:'Sora',sans-serif;font-size:13px;font-weight:700;color:#0f172a;margin:0">Payment Summary</h2>
                            </div>
                            <div style="padding:16px 18px">
                                @php $rows = [
                                        ['icon'=>'fa-plane','label'=>'Base Fare','val'=>$baseFee,'color'=>'#64748b','prefix'=>'৳'],
                                        $taxFee>0 ? ['icon'=>'fa-landmark','label'=>'Taxes & Fees','val'=>$taxFee,'color'=>'#64748b','prefix'=>'৳'] : null,
                                        $serviceFee>0 ? ['icon'=>'fa-concierge-bell','label'=>'Service Charge','val'=>$serviceFee,'color'=>'#64748b','prefix'=>'৳'] : null,
                                        $aitFee>0 ? ['icon'=>'fa-percent','label'=>'AIT Charge','val'=>$aitFee,'color'=>'#64748b','prefix'=>'৳'] : null,
                                        $penaltyAmt>0 ? ['icon'=>'fa-ban','label'=>'Penalty'.($penaltyRmk?' ('.$penaltyRmk.')':''),'val'=>$penaltyAmt,'color'=>'#dc2626','prefix'=>'৳','sign'=>'+'] : null,
                                        // ✅ এই লাইনটা যোগ করো
                                        ($booking->void_charge??0)>0 ? ['icon'=>'fa-times-circle','label'=>'Void Charge','val'=>(float)($booking->void_charge),'color'=>'#dc2626','prefix'=>'৳'] : null,
                                        $totalDisc>0 ? ['icon'=>'fa-tag','label'=>'Discount','val'=>-$totalDisc,'color'=>'#059669','prefix'=>'৳'] : null,
                                    ]; $rows=array_filter($rows); @endphp
                                <div style="display:flex;flex-direction:column;gap:9px">
                                    @foreach($rows as $row)
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="display:flex;align-items:center;gap:7px;font-size:13px;color:{{ $row['color'] }}">
                                                <i class="fas {{ $row['icon'] }}" style="width:12px;color:#cbd5e1;font-size:11px"></i>
                                                {{ $row['label'] }}
                                            </span>
                                            <span style="font-weight:600;font-size:13px;color:{{ $row['color'] }}">
                                                {{ $row['val'] < 0 ? '−' : '' }}{{ $row['prefix'] }}{{ number_format(ceil(abs($row['val']))) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <div style="border-top:2px solid #f1f5f9;margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;align-items:center">
                                    <span style="font-weight:700;font-size:14px;color:#0f172a">Total</span>
                                    <span style="font-family:'Sora',sans-serif;font-weight:800;font-size:20px;color:#0284c7">৳{{ number_format(ceil($displayTotal)) }}</span>
                                </div>
                               @if($walletUsed > 0)
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;font-size:13px">
                                                <span style="color:#7c3aed;display:flex;align-items:center;gap:6px">
                                                    <i class="fas fa-wallet" style="font-size:11px"></i> Wallet Used
                                                </span>
                                                <span style="font-weight:600;color:#7c3aed">−৳{{ number_format($walletUsed,2) }}</span>
                                            </div>
                                        @endif

                                        @if($payable > 0)
                                            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:11px 14px;margin-top:10px">
                                                <p style="font-size:11px;color:#059669;font-weight:600;margin-bottom:3px">Payable Amount</p>
                                                <p style="font-family:'Sora',sans-serif;font-weight:800;font-size:22px;color:#065f46;margin:0">
                                                    ৳{{ number_format(ceil($payable)) }}
                                                </p>
                                            </div>
                                        @else
                                            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:11px 14px;margin-top:10px">
                                                <p style="font-size:11px;color:#0284c7;font-weight:600;margin-bottom:3px">✓ Amount Paid</p>
                                                <p style="font-family:'Sora',sans-serif;font-weight:800;font-size:22px;color:#0369a1;margin:0">
                                                    ৳{{ number_format(ceil($displayTotal)) }}
                                                </p>
                                            </div>
                                        @endif
                                    @if(!empty($booking->gateway))
                                        <p style="font-size:11px;color:#94a3b8;display:flex;align-items:center;gap:6px;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9">
                                            <i class="fas fa-credit-card"></i> Via <strong style="color:#475569">{{ ucfirst(str_replace('_',' ',$booking->gateway)) }}</strong>
                                        </p>
                                    @endif
                            </div>

                            @if($payNow > 0 && (empty($tauDeadlineIso) || \Carbon\Carbon::now('Asia/Dhaka')->lt(\Carbon\Carbon::parse($tauDeadlineIso))) && $booking->status == 'booked')
                                <div style="padding:0 16px 16px">
                                    <div style="border-top:2px dashed #e2e8f0;margin-bottom:14px"></div>
                                    <p style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px">Select Payment</p>
                                    <div style="display:flex;flex-direction:column;gap:8px">
{{--                                        <div onclick="selPay(this,'bkash')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:10px 12px;cursor:pointer;border:2px solid #667eea;background:#eef2ff;transition:all .2s">--}}
{{--                                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #667eea;background:#667eea;display:flex;align-items:center;justify-content:center;flex-shrink:0"><div style="width:5px;height:5px;border-radius:50%;background:white"></div></div>--}}
{{--                                            <div style="width:36px;height:24px;border-radius:5px;background:#e2136e;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:800;flex-shrink:0">bK</div>--}}
{{--                                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">bKash</div><div style="font-size:10px;color:#94a3b8">Mobile · Instant</div></div>--}}
{{--                                            <span style="background:#fff0f7;color:#e2136e;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px">⭐</span>--}}
{{--                                        </div>--}}
{{--                                        <div onclick="selPay(this,'sslcommerz')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:10px 12px;cursor:pointer;border:2px solid #e5e7eb;background:white;transition:all .2s">--}}
{{--                                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>--}}
{{--                                            <div style="width:36px;height:24px;border-radius:5px;background:linear-gradient(135deg,#0ea5e9,#0284c7);display:flex;align-items:center;justify-content:center;color:white;font-size:9px;font-weight:800;flex-shrink:0">SSL</div>--}}
{{--                                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">SSLCommerz</div><div style="font-size:10px;color:#94a3b8">Card · Net Banking</div></div>--}}
{{--                                        </div>--}}
                                        <div onclick="selPay(this,'wallet')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:10px 12px;cursor:pointer;border:2px solid #667eea;background:#eef2ff;transition:all .2s">
                                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #667eea;background:#667eea;display:flex;align-items:center;justify-content:center;flex-shrink:0"><div style="width:5px;height:5px;border-radius:50%;background:white"></div></div>
                                            <div style="width:36px;height:24px;border-radius:5px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:800;flex-shrink:0">WLT</div>
                                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">Wallet</div><div style="font-size:10px;color:#94a3b8">৳{{ number_format(auth()->user()->balance??0,2) }} available</div></div>
                                        </div>

{{--                                        <script>--}}
{{--                                            document.addEventListener('DOMContentLoaded', function() {--}}
{{--                                                document.querySelectorAll('.spay[onclick*="wallet"]').forEach(function(btn) {--}}
{{--                                                    selPay(btn, 'wallet');--}}
{{--                                                });--}}
{{--                                            });--}}
{{--                                        </script>--}}
                                        <a href="{{ route('user.wallet.addcredit') }}"
                                           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border-radius:10px;border:1.5px dashed #8b5cf6;background:#faf5ff;color:#7c3aed;font-size:12px;font-weight:700;text-decoration:none;transition:all .15s;"
                                           onmouseover="this.style.background='#ede9fe'"
                                           onmouseout="this.style.background='#faf5ff'">
                                            <i class="fas fa-plus-circle" style="font-size:13px"></i>
                                            Add Money to Wallet
                                        </a>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;border-radius:8px;padding:9px 12px;margin-top:10px;background:#f0fdf4;border:1px solid #bbf7d0">
                                        <i class="fas fa-lock" style="color:#4ade80;font-size:10px"></i>
                                        <span style="font-size:11px;color:#166534;font-weight:500">SSL Secured · 256-bit encrypted</span>
                                    </div>
                                    <button onclick="doPayment('{{ $booking->id }}')" style="display:flex!important;align-items:center;justify-content:center;gap:8px;width:100%!important;margin-top:10px;padding:14px!important;border-radius:10px!important;border:none!important;background:linear-gradient(135deg,#667eea,#764ba2)!important;color:white!important;font-size:14px!important;font-weight:700!important;cursor:pointer!important;box-shadow:0 4px 14px rgba(102,126,234,.4)!important">
                                        <i class="fas fa-credit-card"></i> Generate Ticket →
                                    </button>
                                    <p style="text-align:center;font-size:10px;color:#94a3b8;margin-top:8px">By paying you agree to our <a href="#" style="color:#667eea">Terms</a></p>
                                </div>
                            @endif
                        </div>

                        {{-- Quick Info --}}
                        <div class="card" style="padding:16px">
                            <h3 style="font-family:'Sora',sans-serif;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px">Quick Info</h3>
                            <div style="display:flex;flex-direction:column;gap:9px">
                                @if(!empty($airlineName))<div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Airline</span><span style="font-weight:600;font-size:12px;color:#334155">{{ $airlineName }}</span></div>@endif
                                @if($checkedBag)<div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Checked Bag</span><span style="font-weight:600;font-size:12px;color:#334155">{{ $checkedBag }}kg</span></div>@endif
                                @if($cabinBag)<div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Cabin Bag</span><span style="font-weight:600;font-size:12px;color:#334155">{{ $cabinBag }}kg</span></div>@endif
                                @if(!empty($fareBreakdowns[0]['fare_construction'][0]['brand_fare_name']))<div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Fare Brand</span><span style="font-weight:600;font-size:12px;color:#334155">{{ $fareBreakdowns[0]['fare_construction'][0]['brand_fare_name'] }}</span></div>@endif
                                @if(!empty($tauDeadline) && !$isTicketed)<div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">TAU Deadline</span><span style="font-weight:700;font-size:12px;color:#dc2626">{{ $tauDeadline }}</span></div>@endif
                                <div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Segments</span><span style="font-weight:600;font-size:12px;color:#334155">{{ $totalSegs }}</span></div>
                                <div style="display:flex;justify-content:space-between"><span style="color:#94a3b8;font-size:12px">Ticketed</span><span style="font-weight:600;font-size:12px;color:{{ $isTicketed?'#059669':'#94a3b8' }}">{{ $isTicketed?'Yes':'No' }}</span></div>
                            </div>
                        </div>

                        {{-- Support --}}
                        <div class="card" style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:18px;text-align:center">
                            <i class="fas fa-headset" style="color:#38bdf8;font-size:24px;margin-bottom:8px;display:block"></i>
                            <p style="color:white;font-size:13px;font-weight:600;margin-bottom:4px">Need Help?</p>
                            <p style="color:#94a3b8;font-size:11px;margin-bottom:12px">Support available 24/7</p>
                            <a href="#" style="display:block;color:white;font-size:11px;font-weight:700;padding:9px 16px;border-radius:10px;background:#0ea5e9;text-decoration:none"><i class="fas fa-comments" style="margin-right:5px"></i> Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════ MOBILE STICKY PAYMENT BAR ════════ --}}
    <div class="mobile-pay-bar no-print">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="flex:1">
                <div style="font-size:10px;color:#94a3b8;font-weight:500">Total Amount</div>
                <div style="font-size:18px;font-weight:800;color:#0369a1;font-family:'Sora',sans-serif">৳{{ number_format(ceil($displayTotal)) }}</div>
            </div>

            @if($payNow > 0 && (empty($tauDeadlineIso) || \Carbon\Carbon::now('Asia/Dhaka')->lt(\Carbon\Carbon::parse($tauDeadlineIso))) && $booking->status == 'booked')
                <button onclick="openModal('mobilePayModal')" style="padding:11px 20px!important;border-radius:10px!important;border:none!important;background:linear-gradient(135deg,#667eea,#764ba2)!important;color:white!important;font-size:13px!important;font-weight:700!important;cursor:pointer!important;box-shadow:0 4px 14px rgba(102,126,234,.4)!important">
                    <i class="fas fa-credit-card" style="margin-right:6px"></i>Generate Ticket
                </button>
            @else
                <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;padding:7px 14px;display:flex;align-items:center;gap:5px">
                    <i class="fas fa-check-circle" style="color:#16a34a;font-size:13px"></i>
                    <span style="font-size:12px;font-weight:700;color:#166534">Paid</span>
                </div>
            @endif
            {{-- TAU warning on mobile --}}
            @if(!empty($tauDeadlineIso) && !$isTicketed && $booking->status !== 'cancelled')
                <div id="mobileTimerBadge" style="display:none;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:5px 10px;text-align:center">
                    <div style="font-size:9px;color:#991b1b;font-weight:600">DEADLINE</div>
                    <div style="font-size:12px;font-weight:800;color:#dc2626;font-family:monospace" id="mobileTimerTxt">--:--:--</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Pay Modal --}}
    @if($payNow > 0 && (empty($tauDeadlineIso) || \Carbon\Carbon::now('Asia/Dhaka')->lt(\Carbon\Carbon::parse($tauDeadlineIso))) && $booking->status == 'booked')
        <div id="mobilePayModal" class="cmodal">
            <div class="mbox">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #e5e7eb">
                    <div style="font-size:14px;font-weight:700;color:#1f2937">💳 Complete Payment</div>
                    <button onclick="closeModal('mobilePayModal')" style="border:none;background:#f3f4f6;border-radius:6px;padding:5px 10px;cursor:pointer;font-size:13px">✕</button>
                </div>
                <div style="padding:16px">
                    <div style="background:#f0f9ff;border-radius:10px;padding:12px;margin-bottom:14px;text-align:center">
                        <div style="font-size:11px;color:#0284c7;font-weight:600;margin-bottom:2px">Amount Due</div>
                        <div style="font-size:28px;font-weight:800;color:#0369a1;font-family:'Sora',sans-serif">৳{{ number_format(ceil($payNow)) }}</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
{{--                        <div onclick="selPay(this,'bkash')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:11px 13px;cursor:pointer;border:2px solid #667eea;background:#eef2ff;transition:all .2s">--}}
{{--                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #667eea;background:#667eea;display:flex;align-items:center;justify-content:center;flex-shrink:0"><div style="width:5px;height:5px;border-radius:50%;background:white"></div></div>--}}
{{--                            <div style="width:38px;height:26px;border-radius:5px;background:#e2136e;display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:800;flex-shrink:0">bK</div>--}}
{{--                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">bKash</div><div style="font-size:10px;color:#94a3b8">Mobile Banking · Instant</div></div>--}}
{{--                            <span style="background:#fff0f7;color:#e2136e;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px">⭐</span>--}}
{{--                        </div>--}}
{{--                        <div onclick="selPay(this,'sslcommerz')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:11px 13px;cursor:pointer;border:2px solid #e5e7eb;background:white;transition:all .2s">--}}
{{--                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>--}}
{{--                            <div style="width:38px;height:26px;border-radius:5px;background:linear-gradient(135deg,#0ea5e9,#0284c7);display:flex;align-items:center;justify-content:center;color:white;font-size:9px;font-weight:800;flex-shrink:0">SSL</div>--}}
{{--                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">SSLCommerz</div><div style="font-size:10px;color:#94a3b8">Credit / Debit Card · Net Banking</div></div>--}}
{{--                        </div>--}}
                        <div onclick="selPay(this,'wallet')" class="spay" style="display:flex;align-items:center;gap:10px;border-radius:10px;padding:11px 13px;cursor:pointer;border:2px solid #667eea;background:#eef2ff;transition:all .2s">
                            <div class="spdot" style="width:16px;height:16px;border-radius:50%;border:2px solid #667eea;background:#667eea;display:flex;align-items:center;justify-content:center;flex-shrink:0"><div style="width:5px;height:5px;border-radius:50%;background:white"></div></div>
                            <div style="width:38px;height:26px;border-radius:5px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:800;flex-shrink:0">WLT</div>
                            <div style="flex:1"><div style="font-size:13px;font-weight:600;color:#1e293b">Wallet Balance</div><div style="font-size:10px;color:#94a3b8">৳{{ number_format(auth()->user()->balance??0,2) }} available</div></div>
                        </div>
                        <a href="{{ route('user.wallet.addcredit') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border-radius:10px;border:1.5px dashed #8b5cf6;background:#faf5ff;color:#7c3aed;font-size:12px;font-weight:700;text-decoration:none;transition:all .15s;"
                           onmouseover="this.style.background='#ede9fe'"
                           onmouseout="this.style.background='#faf5ff'">
                            <i class="fas fa-plus-circle" style="font-size:13px"></i>
                            Add Money to Wallet
                        </a>
                    </div>
                    <button onclick="doPayment('{{ $booking->id }}')" style="display:flex!important;align-items:center;justify-content:center;gap:8px;width:100%!important;margin-top:14px;padding:14px!important;border-radius:10px!important;border:none!important;background:linear-gradient(135deg,#667eea,#764ba2)!important;color:white!important;font-size:14px!important;font-weight:700!important;cursor:pointer!important;box-shadow:0 4px 14px rgba(102,126,234,.4)!important">
                        <i class="fas fa-lock"></i> Generate Ticket
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ════════ MODALS ════════ --}}

    {{-- Print Modal --}}
    <div id="printModal" class="cmodal">
        <div class="mbox mbox-wide">
            <div class="no-print" style="display:flex;justify-content:flex-end;gap:8px;padding:10px 14px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:#fff;z-index:10">
                <button onclick="closeModal('printModal')" style="padding:6px 14px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">✕ Close</button>
                <button onclick="printTicket()" style="padding:6px 14px;border-radius:7px;border:none;background:#003580;color:#fff;font-size:12px;font-weight:600;cursor:pointer">🖨️ Print</button>
            </div>
            <div id="tpz" style="background:#fff;font-family:'Segoe UI',Arial,sans-serif;width:210mm;max-width:100%;margin:0 auto;padding:8mm 10mm 6mm;color:#111;box-sizing:border-box">
                @php
                    $paxTypeLabel = ['ADT'=>'Adult(s)','CNN'=>'Child','CHD'=>'Child','INF'=>'Infant'];
                    $firstSeg = $fdSegments[0] ?? [];
                    $lastSeg  = $fdSegments[count($fdSegments)-1] ?? [];
                    $orig = $firstSeg['origin'] ?? '';
                    $dest = $lastSeg['destination'] ?? '';
                    $routeLabel = $orig.' ➜ '.$dest;
                    $grandBase = collect($fareBreakdowns??[])->sum(fn($f)=>(float)($f['subtotal']??0));
                    $grandTax  = collect($fareBreakdowns??[])->sum(fn($f)=>(float)($f['taxes']??0));
                    $grandTot  = (float)($totalAmount??($grandBase+$grandTax));
                    $cur       = $totalCurrency ?? 'BDT';
                    $allTaxes=[];
                    foreach($fareBreakdowns??[] as $fb2p){ foreach($fb2p['tax_breakdown']??[] as $tx){ $c=$tx['code']??''; $allTaxes[$c]=($allTaxes[$c]??0)+(float)($tx['amount']??0); } }
                    $taxStr = collect($allTaxes)->map(fn($a,$c)=>$c.' : '.number_format($a))->implode('; ');
                    $tktMapP=[];
                    foreach($fd['flight_tickets']??[] as $t){ $tktMapP[$t['traveler_index']??0]=$t['number']??''; }
                @endphp
                {{-- Header --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:60px;height:60px;background:linear-gradient(135deg,#003580,#0055b3);border-radius:8px;display:flex;align-items:center;justify-content:center"><span style="font-size:26px;color:#fff">✈</span></div>
                        <div>
                            <div style="font-size:20px;font-weight:900;color:#003580;line-height:1">{{ config('app.name','Shopno Tour') }}</div>
                            <div style="font-size:7px;color:#555;margin-top:2px;text-transform:uppercase;letter-spacing:.05em">Authorized Travel Agency · IATA Accredited</div>
                            <div style="font-size:7px;color:#777;margin-top:1px">{{ config('app.address','Gulshan-1, Dhaka-1212, Bangladesh') }}</div>
                            <div style="font-size:7px;color:#777">{{ config('app.email','support@shopnotour.com') }} · {{ config('app.phone','01958553918') }}</div>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:28px;font-weight:900;color:#003580;line-height:1">e-ticket</div>
                        @if(!empty($gfPnr))<div style="font-size:13px;color:#555;margin-top:3px">Airline PNR: <strong style="font-family:monospace;color:#003580">{{ $gfPnr }}</strong></div>@endif
                        @if(!empty($fd['booking_id']))<div style="font-size:13px;color:#555">GDS Ref: <strong style="font-family:monospace;color:#003580">{{ $fd['booking_id'] }}</strong></div>@endif
                    </div>
                </div>
                <div style="border-top:2px solid #003580;margin-bottom:8px"></div>
                {{-- Passengers --}}
                <table style="width:100%;border-collapse:collapse;font-size:8px;margin-bottom:2px">
                    <thead><tr style="background:#f0f4fa"><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Passenger Name</th><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Ticket Number</th><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Status</th><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Pax. type</th><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Contact</th><th style="padding:5px 7px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Passport</th></tr></thead>
                    <tbody>
                    @foreach($fdPassengers??[] as $pi=>$p)
                        @php $tP=$tktMapP[$pi+1]??''; @endphp
                        <tr><td style="padding:5px 7px;border:1px solid #dde6f0;font-weight:700">{{ strtoupper(($p['first_name']??'').' '.($p['last_name']??'')) }}</td><td style="padding:5px 7px;border:1px solid #dde6f0;font-family:monospace;font-weight:700;color:#003580">{{ $tP?:'—' }}</td><td style="padding:5px 7px;border:1px solid #dde6f0;font-size:7px">{{ isset($tktMap[$pi+1])?($tktMap[$pi+1]['status']??'—'):'—' }}</td><td style="padding:5px 7px;border:1px solid #dde6f0">{{ $paxTypeLabel[$p['traveler_type']??'ADT']??'Adult' }}</td><td style="padding:5px 7px;border:1px solid #dde6f0">{{ $p['phone']??'—' }}</td><td style="padding:5px 7px;border:1px solid #dde6f0;font-family:monospace">{{ $p['passport_number']??'—' }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
                <div style="font-size:7px;font-weight:700;color:#003580;margin-bottom:6px">e‑ticket</div>
                {{-- Travel Note --}}
                <div style="border:1px solid #c5d5e8;border-radius:3px;padding:6px 9px;margin-bottom:9px;font-size:7.5px;color:#333;line-height:1.7">
                    <div style="font-weight:700;margin-bottom:2px">Travel Note:</div>
                    <div>- Check-in counter opens 1:30h (Domestic) and 3:00h (International) before departure. Counter closes 30min (Domestic) and 60min (International) before departure.</div>
                    <div>- Carry valid photo ID (Domestic) or passport with 6-month validity (International).</div>
                    <div>- Boarding gate closes 20min (Domestic) / 30min (International) before departure.</div>
                    <div>- {{ config('app.name','Shopno Tour') }} · {{ config('app.website','www.shopnotour.com') }}</div>
                </div>
                {{-- Itinerary --}}
                <div style="margin-bottom:9px">
                    <div style="font-size:10px;font-weight:800;color:#003580;border-bottom:2px solid #003580;padding-bottom:3px;margin-bottom:5px">Travel Itinerary</div>
                    <table style="width:100%;border-collapse:collapse;font-size:8px">
                        <thead><tr style="background:#f0f4fa"><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Flight</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">From</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">To</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Departure</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Arrival</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">T-Dep</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">T-Arr</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Cabin</th><th style="padding:4px 5px;text-align:left;color:#003580;font-weight:700;border:1px solid #c5d5e8">Status</th></tr></thead>
                        <tbody>
                        @foreach($fdSegments??[] as $si=>$seg)
                            @php $dDt=!empty($seg['departure_time'])?\Carbon\Carbon::parse($seg['departure_time']):null;$aDt=!empty($seg['arrival_time'])?\Carbon\Carbon::parse($seg['arrival_time']):null; @endphp
                            <tr style="background:{{ $si%2===0?'#fff':'#f8fafd' }}">
                                <td style="padding:4px 5px;border:1px solid #dde6f0;font-weight:800;color:#003580">{{ ($seg['carrier']??'') }}{{ $seg['flight_number']??'' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0;font-weight:700">{{ $seg['origin']??'' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0;font-weight:700">{{ $seg['destination']??'' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0">@if($dDt)<strong>{{ $dDt->format('d-M-y') }}</strong> {{ $dDt->format('H:i') }}@else—@endif</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0">@if($aDt)<strong>{{ $aDt->format('d-M-y') }}</strong> {{ $aDt->format('H:i') }}@else—@endif</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0">{{ $seg['departure_terminal']??'—' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0">{{ $seg['arrival_terminal']??'—' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0">{{ $seg['cabin_class']??'Economy' }}</td>
                                <td style="padding:4px 5px;border:1px solid #dde6f0;font-weight:700;color:{{ ($seg['status']??'')==='HK'?'#166534':'#c2410c' }}">{{ $seg['status']??'OK' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Fare --}}
                <div style="margin-bottom:9px">
                    <div style="font-size:10px;font-weight:800;color:#003580;border-bottom:2px solid #003580;padding-bottom:3px;margin-bottom:5px">Fare Details</div>
                    <table style="width:100%;border-collapse:collapse;font-size:8px">
                        <thead><tr style="background:#f0f4fa"><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:left">Segment</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:left">Pax Type</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:left">Name</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8">Baggage</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:right">Base</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:right">Tax</th><th style="padding:4px 5px;color:#003580;font-weight:700;border:1px solid #c5d5e8;text-align:right">Total</th></tr></thead>
                        <tbody>
                        @foreach($fareBreakdowns??[] as $fi=>$fb2p)
                            @php $fbType2=$fb2p['traveler_type']??'ADT';$fbBase2=(float)($fb2p['subtotal']??0);$fbTax2=(float)($fb2p['taxes']??0);$fbTot2=(float)($fb2p['total']??0);$fbCur2=$fb2p['currency']??$cur;$bagKg2=$fb2p['fare_construction'][0]['checked_bag_kg']??($checkedBag??'—');$paxIdxs2=$fb2p['traveler_indices']??[];$breakPax2=collect($fdPassengers??[])->filter(fn($p,$i)=>empty($paxIdxs2)||in_array($i+1,$paxIdxs2)); @endphp
                            @foreach($breakPax2 as $bp2)
                                <tr><td style="padding:4px 5px;border:1px solid #dde6f0">{{ $routeLabel }}</td><td style="padding:4px 5px;border:1px solid #dde6f0">{{ $paxTypeLabel[$fbType2]??$fbType2 }}</td><td style="padding:4px 5px;border:1px solid #dde6f0;font-weight:700">{{ strtoupper(($bp2['first_name']??'').' '.($bp2['last_name']??'')) }}</td><td style="padding:4px 5px;border:1px solid #dde6f0;text-align:center">{{ $bagKg2 }} Kg</td><td style="padding:4px 5px;border:1px solid #dde6f0;text-align:right">{{ number_format($fbBase2) }}</td><td style="padding:4px 5px;border:1px solid #dde6f0;text-align:right">{{ number_format($fbTax2) }}</td><td style="padding:4px 5px;border:1px solid #dde6f0;text-align:right;font-weight:700">{{ number_format($fbTot2) }}</td></tr>
                            @endforeach
                        @endforeach
                        </tbody>
                        <tfoot><tr style="background:#f0f4fa"><td colspan="6" style="padding:5px 8px;text-align:right;font-weight:700;border:1px solid #c5d5e8">Total {{ $cur }}</td><td style="padding:5px 8px;text-align:right;font-weight:800;color:#003580;border:1px solid #c5d5e8">{{ number_format($grandTot) }}</td></tr></tfoot>
                    </table>
                </div>
                {{-- Footer --}}
                <div style="border-top:1px solid #c5d5e8;padding-top:5px;display:flex;align-items:center;justify-content:space-between;font-size:7px;color:#555">
                    <div><strong style="color:#003580">{{ config('app.name','Shopno Tour') }}</strong> · {{ config('app.email','support@shopnotour.com') }} · {{ config('app.phone','01958553918') }}</div>
                    <div style="font-size:8px;font-weight:700;color:#003580;font-family:monospace;letter-spacing:.15em">*{{ $booking->code }}*</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Void Modal --}}
    <div id="voidModal" class="cmodal">
        <div class="mbox">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e5e7eb">
                <div style="width:38px;height:38px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🚫</div>
                <div style="font-size:15px;font-weight:700;color:#1f2937">Void Ticket</div>
            </div>
            <form onsubmit="doModal(event,'void')">
                <div style="padding:16px 18px">
                    <p style="font-size:12px;font-weight:600;color:#dc2626;margin-bottom:12px">⚠️ Voiding is irreversible and only allowed on ticket issue date</p>
                    <input type="hidden" name="pnr_number" value="{{ $fd['booking_id'] ?? $booking->pnr_id }}">
                    <div><label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Reason</label><textarea name="reason" rows="3" required style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;resize:vertical;box-sizing:border-box"></textarea></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 18px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeModal('voidModal')" style="padding:8px 16px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">Cancel</button>
                    <button type="submit" style="padding:8px 16px;border-radius:7px;border:none;background:#dc2626;color:white;font-size:12px;font-weight:600;cursor:pointer">Void Ticket</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Refund Modal --}}
    <div id="refundModal" class="cmodal">
        <div class="mbox">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e5e7eb">
                <div style="width:38px;height:38px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">💰</div>
                <div style="font-size:15px;font-weight:700;color:#1f2937">Process Refund</div>
            </div>
            <form onsubmit="doModal(event,'refund')">
                <div style="padding:16px 18px">
                    <input type="hidden" name="pnr_number" value="{{ $fd['booking_id'] ?? $booking->pnr_id }}">
                    <div style="margin-bottom:10px">
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Select Passengers</label>
                        <select name="passenger_ids[]" class="select2-pax" multiple required style="width:100%">
                            <option value="all">All Passengers ({{ $totalPax }})</option>
                            @foreach($passengers as $p)<option value="{{ $p->id }}">{{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }}</option>@endforeach
                        </select>
                    </div>
                    <div><label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Reason</label><textarea name="refund_reason" rows="3" required style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;resize:vertical;box-sizing:border-box"></textarea></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 18px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeModal('refundModal')" style="padding:8px 16px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">Cancel</button>
                    <button type="submit" style="padding:8px 16px;border-radius:7px;border:none;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:12px;font-weight:600;cursor:pointer">Process Refund</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Reissue Modal --}}
    <div id="reissueModal" class="cmodal">
        <div class="mbox" style="max-width:560px">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e5e7eb">
                <div style="width:38px;height:38px;border-radius:50%;background:#ddd6fe;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🔄</div>
                <div style="font-size:15px;font-weight:700;color:#1f2937">Reissue Ticket</div>
            </div>
            <form onsubmit="doModal(event,'reissue')">
                <div style="padding:16px 18px;display:flex;flex-direction:column;gap:14px">

                    {{-- Warning --}}
                    <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:8px;padding:10px 13px;font-size:11.5px;color:#92400e;line-height:1.6">
                        ⚠️ <strong>দয়া করে মনোযোগ দিন:</strong><br>
                        রি-ইস্যু প্রক্রিয়ায় যে মূল্য প্রদর্শিত হবে তা <strong>প্রকৃত চূড়ান্ত মূল্য নয়।</strong>
                        আমাদের টিম পর্যালোচনা করে প্রকৃত মূল্য জানাবে।
                    </div>

                    <input type="hidden" name="pnr_number" value="{{ $fd['booking_id'] ?? $booking->pnr_id }}">

                    {{-- Passengers --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Passengers</label>
                        <select name="passenger_ids[]" class="select2-pax" multiple required style="width:100%">
                            <option value="all">All ({{ $totalPax }})</option>
                            @foreach($passengers as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Leg Selection --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px">
                            Select Leg(s) to Reissue
                        </label>
                        <div style="display:flex;flex-direction:column;gap:8px">
                            @foreach($legGroups2 as $li => $legGroup)
                                @php
                                    $lgJrn  = $legGroup['journey'];
                                    $lgSegs = $legGroup['segments'];
                                    $lgFirst = $lgSegs[0] ?? null;
                                    $lgLast  = end($lgSegs) ?: null;
                                    $lgLabel = ($lgJrn['first_airport_code'] ?? '') . ' → ' . ($lgJrn['last_airport_code'] ?? '');
                                    $lgDate  = !empty($lgJrn['departure_date']) ? date('d M Y', strtotime($lgJrn['departure_date'])) : '';
                                    $lgMin   = !empty($lgJrn['departure_date']) ? $lgJrn['departure_date'] : date('Y-m-d');
                                @endphp
                                <div style="border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden;transition:border-color .2s" id="leg-card-{{ $li }}">
                                    {{-- Leg header / checkbox --}}
                                    <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;background:#f8fafc;user-select:none">
                                        <input type="checkbox"
                                               name="legs[{{ $li }}][selected]"
                                               value="1"
                                               onchange="toggleLegDate({{ $li }}, this.checked)"
                                               style="width:16px;height:16px;cursor:pointer;accent-color:#667eea">
                                        <div style="flex:1">
                                            <div style="font-size:13px;font-weight:700;color:#1e293b">
                                                {{ $li === 0 ? '✈ Outbound' : '← Return' }}
                                                <span style="font-size:12px;font-weight:400;color:#64748b;margin-left:4px">{{ $lgLabel }}</span>
                                            </div>
                                            <div style="font-size:11px;color:#94a3b8;margin-top:1px">
                                                {{ $lgDate }}
                                                · {{ count($lgSegs) }} flight{{ count($lgSegs) > 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:{{ $li === 0 ? '#eff6ff' : '#f5f3ff' }};color:{{ $li === 0 ? '#1d4ed8' : '#7c3aed' }}">
                                        Leg {{ $li + 1 }}
                                    </span>
                                    </label>

                                    {{-- Date input — hidden by default --}}
                                    <div id="leg-date-{{ $li }}" style="display:none;padding:10px 14px;border-top:1px solid #e2e8f0;background:#fff">
                                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">
                                            New Date for {{ $lgLabel }}
                                        </label>
                                        <input type="text"
                                               name="legs[{{ $li }}][new_date]"
                                               id="leg-date-input-{{ $li }}"
                                               placeholder="Select date"
                                               readonly
                                               style="width:100%!important;padding:8px 10px!important;border:1.5px solid #e5e7eb!important;border-radius:7px!important;font-size:13px!important;box-sizing:border-box!important;color:#1e293b!important;background:white!important;cursor:pointer!important;">
                                        <input type="hidden" name="legs[{{ $li }}][route]" value="{{ $lgLabel }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Remarks</label>
                        <textarea name="remarks" rows="2"
                                  style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;resize:vertical;box-sizing:border-box"></textarea>
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 18px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeModal('reissueModal')"
                            style="padding:8px 16px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:8px 16px;border-radius:7px;border:none;background:linear-gradient(135deg,#667eea,#764ba2)!important;color:white!important;font-size:12px;font-weight:600;cursor:pointer">
                        Submit Reissue
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Passenger Documents Modal --}}
    <div id="paxDocsModal" class="cmodal">
        <div class="mbox" style="max-width:580px">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e5e7eb;">
                <div>
                    <div style="font-size:15px;font-weight:700;color:#1f2937">📎 Passenger Documents</div>
                    <div id="paxDocsName" style="font-size:12px;color:#64748b;margin-top:2px;font-weight:600;"></div>
                </div>
                <button onclick="closeModal('paxDocsModal')"
                        style="border:none;background:#f3f4f6;border-radius:6px;padding:5px 10px;cursor:pointer;font-size:13px;color:#374151;">✕</button>
            </div>
            <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                {{-- Passport --}}
                <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;text-align:center;">
                    <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                        🛂 Passport
                    </div>
                    <div id="paxPassportImg"></div>
                </div>
                {{-- Visa --}}
                <div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px;text-align:center;">
                    <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                        🗂️ Visa
                    </div>
                    <div id="paxVisaImg"></div>
                </div>
            </div>
            <div style="padding:0 16px 14px;text-align:center;font-size:10px;color:#94a3b8;">
                Image-এ click করলে full size নতুন tab-এ খুলবে
            </div>
        </div>
    </div>

    {{-- Add SSR Modal --}}
    {{-- Add SSR Modal --}}
    <div id="addsrModal" class="cmodal">
        <div class="mbox" style="max-width:520px">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid #e5e7eb">
                <div style="width:38px;height:38px;border-radius:50%;background:#ccfbf1;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">📝</div>
                <div style="font-size:15px;font-weight:700;color:#1f2937">Add Special Service Request</div>
            </div>
            <form onsubmit="doModal(event,'addssr')">
                <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px">

                    <input type="hidden" name="pnr_number" value="{{ $fd['booking_id'] ?? $booking->pnr_id }}">

                    {{-- Passenger --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Passenger</label>
                        <select name="passenger_id" required
                                style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:white">
                            <option value="">Select passenger...</option>
                            @foreach($passengers as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name ?? '' }} {{ $p->last_name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SSR Type --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">Service Type</label>
                        <select name="ssr_type" required id="ssrTypeSelect"
                                onchange="onSSRTypeChange(this.value)"
                                style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:white">
                            <option value="">Select type...</option>
                            <option value="meal">🍽️ Meal Preference</option>
                            <option value="wheelchair">♿ Wheelchair Assistance</option>
                            <option value="baggage">🧳 Extra Baggage</option>
                            <option value="seat">💺 Seat Preference</option>
                            <option value="infant">👶 Infant Request</option>
                            <option value="medical">🏥 Medical Assistance</option>
                            <option value="other">📋 Other</option>
                        </select>
                    </div>

                    {{-- Dynamic Info Card --}}
                    <div id="ssrInfoCard" style="display:none"></div>

                    {{-- Details --}}
                    <div>
                        <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px">
                            Details / Special Instructions
                        </label>
                        <textarea name="ssr_details" id="ssrDetailsField" rows="3" required
                                  style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;resize:vertical;box-sizing:border-box"
                                  placeholder="Please describe your request..."></textarea>
                    </div>

                    {{-- Note --}}
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:10px 12px;font-size:11px;color:#0369a1;line-height:1.6">
                        ℹ️ SSR request টি admin review করবে। Approval এর পর charge (যদি থাকে) জানানো হবে।
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;padding:12px 18px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeModal('addsrModal')"
                            style="padding:8px 16px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:8px 16px;border-radius:7px;border:none;background:linear-gradient(135deg,#667eea,#764ba2)!important;color:white!important;font-size:12px;font-weight:600;cursor:pointer">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Payment Result Modal --}}
    {{-- Payment Confirm Modal --}}
    <div id="payConfirmModal" class="cmodal">
        <div class="mbox" style="max-width:360px">
            <div style="padding:24px;text-align:center">
                <div style="font-size:40px;margin-bottom:10px">💳</div>
                <div style="font-size:15px;font-weight:700;color:#1f2937;margin-bottom:6px">Confirm Payment</div>
                <div style="font-size:13px;color:#64748b;margin-bottom:20px">
                    আপনি কি <strong>৳{{ number_format(ceil($payNow)) }}</strong> পরিশোধ করতে চান?
                </div>
                <div style="display:flex;gap:8px;justify-content:center">
                    <button onclick="closeModal('payConfirmModal')"
                            style="padding:10px 24px;border-radius:8px;border:none;background:#f3f4f6;color:#374151;font-size:13px;font-weight:700;cursor:pointer">
                        ✕ Cancel
                    </button>
                    <button onclick="doPaymentConfirmed()"
                            style="padding:10px 24px;border-radius:8px;border:none;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:13px;font-weight:700;cursor:pointer">
                        ✓ OK, Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Copy Modal --}}
    {{-- Booking Copy Modal --}}
    {{-- Booking Copy Modal --}}
    <div id="bookingCopyModal" class="cmodal">
        <div class="mbox" style="max-width:720px!important;width:96%!important;">
            <div class="no-print" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:#fff;z-index:10">
                <div style="font-size:14px;font-weight:700;color:#1f2937">📋 Booking/Ticket Copy</div>
                <div style="display:flex;gap:8px">
                    <div class="no-print" style="display:flex;justify-content:flex-end;gap:8px;padding:10px 14px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:#fff;z-index:10">
                        <button onclick="closeModal('bookingCopyModal')" style="padding:6px 14px;border-radius:7px;border:none;background:#f3f4f6;color:#374151;font-size:12px;font-weight:600;cursor:pointer">✕ Close</button>
                        <button onclick="downloadBookingCopyPDF()" style="padding:6px 14px;border-radius:7px;border:none;background:#059669;color:#fff;font-size:12px;font-weight:600;cursor:pointer">📥 Download PDF</button>
                        <button onclick="printBookingCopy()" style="padding:6px 14px;border-radius:7px;border:none;background:#003580;color:#fff;font-size:12px;font-weight:600;cursor:pointer">🖨️ Print</button>
                    </div>
                </div>
            </div>

            <div id="bookingCopyContent" style="padding:20px;background:#fff;font-family:Arial,sans-serif;font-size:12px;color:#111;">

                {{-- ── Agency Header ── --}}
                @php
                    $invoiceLogoId  = setting_item('logo_invoice_id');
                    $invoiceLogoUrl = $invoiceLogoId
                        ? \Modules\Media\Helpers\FileHelper::url($invoiceLogoId, 'full')
                        : get_file_url(setting_item('logo_id'), 'full');

                    $invoiceLogoSrc = '';
                    if ($invoiceLogoUrl) {
                        $invoiceLogoPath = public_path(parse_url($invoiceLogoUrl, PHP_URL_PATH));
                        if (file_exists($invoiceLogoPath)) {
                            $invoiceMime    = mime_content_type($invoiceLogoPath);
                            $invoiceLogoSrc = 'data:' . $invoiceMime . ';base64,' . base64_encode(file_get_contents($invoiceLogoPath));
                        } else {
                            $invoiceLogoSrc = $invoiceLogoUrl;
                        }
                    }
                    $invoiceCompanyInfo = setting_item('invoice_company_info') ?? '';

                    // ✅ শুধু booked status এ Fare Data দেখাবে
                    $showFareData = $booking->status === 'booked';
                @endphp

                {{-- ── Agency Header — table layout যাতে logo সবসময় top-right এ থাকে ── --}}
                <table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
                    <tr>
                        {{-- Left: Company Info --}}
                        <td style="vertical-align:top;padding:0;">
                            <style>
                                .invoice-info p { margin:0 !important; padding:0 !important; }
                                .invoice-info strong, .invoice-info b { font-weight:700 !important; }
                                .invoice-info span[style] { font-size:inherit; }
                            </style>
                            <div class="invoice-info" style="font-family:Arial,sans-serif;">
                                {!! $invoiceCompanyInfo !!}
                            </div>
                        </td>
                        {{-- Right: Logo — সবসময় top-right, content যাই হোক ── --}}
                        @if($invoiceLogoSrc)
                            <td style="vertical-align:middle;text-align:right;padding:0;width:140px;padding-left:20px;">
                                <img src="{{ $invoiceLogoSrc }}" alt="Logo"
                                     style="max-height:80px;max-width:130px;width:auto;object-fit:contain;display:inline-block;">
                            </td>
                        @endif
                    </tr>
                </table>

                {{-- ── Title ── --}}
                <div style="font-size:16px;font-weight:700;margin-bottom:14px;">
                    {{ $booking->status === 'booked' ? 'Booking Confirmation' : 'E-Ticket / Itinerary' }}
                </div>

                {{-- ── Passenger Information ── --}}
                <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Passenger Information</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
                    <thead>
                    <tr style="background:#b8d4e8;">
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Passenger Name</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Date of Birth</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Passport Number</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Passport Expiry</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Frequent Flyer</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Ticket</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($passengers as $pi => $pax)
                        @php
                            $fn3    = strtoupper(trim($pax->first_name ?? ''));
                            $ln3    = strtoupper(trim($pax->last_name  ?? ''));
                            $ttl3   = strtoupper(trim($pax->title      ?? ''));
                            $pName3 = trim($ln3.($ln3 && $fn3 ? '/' : '').$fn3.($ttl3 ? ' '.$ttl3 : ''));
                            if (!$pName3) $pName3 = 'PASSENGER';

                            $dob3 = $pax->dob
                                ? \Carbon\Carbon::parse($pax->dob)->format('d M Y') : '';
                            $exp3 = $pax->passport_expiry_date
                                ? \Carbon\Carbon::parse($pax->passport_expiry_date)->format('d M Y') : '';

                            $meta3 = is_string($pax->meta) ? json_decode($pax->meta, true) : (array)($pax->meta ?? []);
                            $ffn3  = $meta3['frequent_flyer_number']
                                ?? ($meta3['ffn'] ?? null)
                                ?? (collect($meta3['loyalty_programs'] ?? [])->first()['program_number'] ?? '');

                            $tktNo3   = $pax->ticket_number ?? '';
                            $tktStat3 = strtoupper($pax->status ?? '');

                            // ✅ issued/ticket copy হলে শুধু issued pax দেখাবে
                            // booked হলে সবাই দেখাবে
                            $isIssuedPax = !empty($tktNo3)
                                && !in_array($tktStat3, ['TV','VOID','VOIDED','RF','RFND','REFUND','REFUNDED']);

                            if (!$showFareData && !$isIssuedPax) continue; // ticket copy তে unissued skip
                        @endphp
                        <tr>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;font-weight:700;">{{ $pName3 }}</td>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">{{ $dob3 }}</td>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;font-family:monospace;font-weight:700;">{{ $pax->passport_number ?? '' }}</td>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">{{ $exp3 }}</td>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">{{ $ffn3 }}</td>
                            <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;font-family:monospace;font-weight:700;color:#003580;">{{ $tktNo3 ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                {{-- ── PNR Row ── --}}
                <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                    <thead>
                    <tr style="background:#b8d4e8;">
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Airline PNR</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">GDS PNR</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Date of Issue</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">
                            {{ !empty($gfPnr) ? $gfPnr.' ('.(($fdSupplier['supplier_code']??'')).' - '.($fdSegments[0]['airline_name']??'').')' : '—' }}
                        </td>
                        <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;font-family:monospace;font-weight:700;">
                            {{ $fd['booking_id'] ?? $booking->pnr_id ?? '—' }}
                        </td>
                        <td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">{{ date('dMy') }}</td>
                    </tr>
                    </tbody>
                </table>

                {{-- ── Itinerary Information ── --}}
                <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Itinerary Information</div>

                {{-- Single table for ALL legs --}}
                <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
                    <thead>
                    <tr style="background:#b8d4e8;">
                        <th style="border:1px solid #999;padding:6px 8px;text-align:left;font-size:11px;">Flight</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:center;font-size:11px;">From</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:center;font-size:11px;">Depart</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:center;font-size:11px;">To</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:center;font-size:11px;">Arrive</th>
                        <th style="border:1px solid #999;padding:6px 8px;text-align:center;font-size:11px;">Details</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($legGroups2 as $li => $legGroup)
                        @php
                            $lgSegs = $legGroup['segments'];
                            $lgJrn  = $legGroup['journey'];
                            $lgOut  = $legGroup['type'] === 'outbound';
                            $lgFcBag = null;
                            foreach($fareBreakdowns as $fb) {
                                $fc = ($fb['fare_construction'] ?? [])[$li] ?? ($fb['fare_construction'][0] ?? null);
                                if(!empty($fc['checked_bag_kg'])) { $lgFcBag = $fc['checked_bag_kg']; break; }
                            }
                            $lgBagDisplay = $lgFcBag ? $lgFcBag.'kg' : ($checkedBag ? $checkedBag.'kg' : '');
                        @endphp

                        {{-- Leg separator row --}}
                        <tr>
                            <td colspan="6" style="background:#f1f5f9;border:1px solid #cbd5e1;padding:5px 10px;font-size:10px;font-weight:700;color:#475569;">
                                {{ $lgOut ? '✈ Outbound' : '✈ Return' }}
                                &nbsp;·&nbsp; {{ $lgJrn['first_airport_code'] }} → {{ $lgJrn['last_airport_code'] }}
                                &nbsp;·&nbsp; {{ date('D, d M Y', strtotime($lgJrn['departure_date'])) }}
                                @if($lgBagDisplay) &nbsp;·&nbsp; 🧳 {{ $lgBagDisplay }} @endif
                            </td>
                        </tr>

                        @foreach($lgSegs as $si => $seg)
                            @php
                                $lgAirline = $seg['airline_name'] ?? $seg['carrier'];
                                $lgFlight  = $seg['carrier'].($seg['flight_number'] ?? '');

                                preg_match('/T(\d{2}:\d{2})/', $seg['departure_time'], $lgDm);
                                preg_match('/T(\d{2}:\d{2})/', $seg['arrival_time'],   $lgAm);
                                preg_match('/^(\d{4}-\d{2}-\d{2})/', $seg['departure_time'], $lgDd);
                                preg_match('/^(\d{4}-\d{2}-\d{2})/', $seg['arrival_time'],   $lgAd);
                                $lgDepRaw  = $lgDm[1] ?? '00:00';
                                $lgArrRaw  = $lgAm[1] ?? '00:00';
                                $lgDepDate = !empty($lgDd[1]) ? date('d M y', strtotime($lgDd[1])) : '';
                                $lgArrDate = !empty($lgAd[1]) ? date('d M y', strtotime($lgAd[1])) : '';
                                $lgDur     = floor(($seg['travel_time']??0)/60).'h '.(($seg['travel_time']??0)%60).'m';

                                $lgAc = $seg['aircraft_name'] ?? '';
                                if(empty($lgAc)||(strlen($lgAc)<=4&&ctype_alnum($lgAc))) $lgAc = $seg['equipment'] ?? '';
                                if(strlen($lgAc)<=4&&ctype_alnum($lgAc)) $lgAc = '';

                                $lgStatus  = $seg['status_name'] ?? $seg['status'] ?? 'Confirmed';
                                $lgClass   = $seg['cabin_class'] ?? '';
                                $lgCos     = $seg['class_of_service'] ?? '';

                                $lgOrigCity = $seg['origin_city'] ?? $seg['origin'];
                                $lgOrigTerm = isset($seg['departure_terminal']) && $seg['departure_terminal'] ? ' T'.$seg['departure_terminal'] : '';
                                $lgDestCity = $seg['destination_city'] ?? $seg['destination'];
                                $lgDestTerm = isset($seg['arrival_terminal']) && $seg['arrival_terminal'] ? ' T'.$seg['arrival_terminal'] : '';

                                $lgSegBag = '';
                                if(!empty($seg['checked_bag_allowance'])) {
                                    $lgSegBag = $seg['checked_bag_allowance'];
                                } elseif(!empty($fareBreakdowns)) {
                                    foreach($fareBreakdowns as $fb) {
                                        $fcArr = $fb['fare_construction'] ?? [];
                                        $fc = $fcArr[$li] ?? ($fcArr[0] ?? null);
                                        if(!empty($fc['checked_bag_kg'])) { $lgSegBag = $fc['checked_bag_kg'].'kg'; break; }
                                    }
                                }
                                if(empty($lgSegBag) && !empty($lgBagDisplay)) $lgSegBag = $lgBagDisplay;
                                if(empty($lgSegBag) && $checkedBag) $lgSegBag = $checkedBag.'kg';

                                // Airline logo
                                $lgAirlineInfo   = \Modules\Flight\Models\Airline::getByCode($seg['carrier'] ?? '');
                                $lgAirlineRawImg = $lgAirlineInfo['image_thumb'] ?? $lgAirlineInfo['image_url'] ?? '';
                                $lgAirlineImg    = '';
                                if ($lgAirlineRawImg) {
                                    $lgImgPath = public_path(parse_url($lgAirlineRawImg, PHP_URL_PATH));
                                    if (file_exists($lgImgPath)) {
                                        $lgMime       = mime_content_type($lgImgPath);
                                        $lgAirlineImg = 'data:'.$lgMime.';base64,'.base64_encode(file_get_contents($lgImgPath));
                                    } else {
                                        $lgAirlineImg = $lgAirlineRawImg;
                                    }
                                }

                                // Layover
                                $lgIsLast = $si === count($lgSegs)-1;
                                $lgLvText = '';
                                if(!$lgIsLast) {
                                    $lgNs    = $lgSegs[$si+1];
                                    $lgLvMin = (int)((strtotime($lgNs['departure_time']) - strtotime($seg['arrival_time'])) / 60);
                                    $lgOvn   = date('Y-m-d',strtotime($seg['arrival_time'])) !== date('Y-m-d',strtotime($lgNs['departure_time'])) ? ' · Overnight' : '';
                                    $lgLvText = floor($lgLvMin/60).'h '.($lgLvMin%60).'m layover at '.$seg['destination'].$lgOvn;
                                }
                            @endphp

                            {{-- Flight row --}}
                            <tr style="background:#fff;vertical-align:middle;">

                                {{-- Airline --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;white-space:nowrap;">
                                    @if($lgAirlineImg)
                                        <img src="{{ $lgAirlineImg }}" style="height:18px;width:auto;object-fit:contain;display:block;margin-bottom:3px;">
                                    @endif
                                    <div style="font-size:11px;font-weight:700;color:#1e293b;">{{ $lgAirline }}</div>
                                    <div style="font-size:10px;font-family:monospace;color:#1d4ed8;font-weight:700;">{{ $lgFlight }}</div>
                                    <div style="font-size:9px;color:#94a3b8;margin-top:2px;">✈ {{ $lgDur }}</div>
                                </td>

                                {{-- Origin --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;text-align:center;">
                                    <div style="font-size:18px;font-weight:800;color:#1e293b;line-height:1;">{{ $seg['origin'] }}</div>
                                    <div style="font-size:9px;color:#64748b;margin-top:2px;">{{ $lgOrigCity }}{{ $lgOrigTerm }}</div>
                                </td>

                                {{-- Depart time --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;text-align:center;white-space:nowrap;">
                                    <div style="font-size:16px;font-weight:800;color:#0f172a;line-height:1;">{{ $lgDepRaw }}</div>
                                    <div style="font-size:9px;color:#64748b;margin-top:2px;">{{ $lgDepDate }}</div>
                                </td>

                                {{-- Destination --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;text-align:center;">
                                    <div style="font-size:18px;font-weight:800;color:#1e293b;line-height:1;">{{ $seg['destination'] }}</div>
                                    <div style="font-size:9px;color:#64748b;margin-top:2px;">{{ $lgDestCity }}{{ $lgDestTerm }}</div>
                                </td>

                                {{-- Arrive time --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;text-align:center;white-space:nowrap;">
                                    <div style="font-size:16px;font-weight:800;color:#0f172a;line-height:1;">{{ $lgArrRaw }}</div>
                                    <div style="font-size:9px;color:#64748b;margin-top:2px;">{{ $lgArrDate }}</div>
                                </td>

                                {{-- Info --}}
                                <td style="border:1px solid #ccc;padding:7px 10px;text-align:center;">
                                    <div style="display:inline-flex;flex-direction:column;gap:3px;align-items:center;">
                                    <span style="background:#f1f5f9;color:#334155;font-size:9px;font-weight:600;padding:2px 7px;border-radius:3px;white-space:nowrap;">
                                        {{ $lgClass }}@if($lgCos) ({{ $lgCos }})@endif
                                    </span>
                                        <span style="background:#f1f5f9;color:#059669;font-size:9px;font-weight:600;padding:2px 7px;border-radius:3px;">
                                        ✓ {{ $lgStatus }}
                                    </span>
                                        @if($lgSegBag)
                                            <span style="background:#f1f5f9;color:#334155;font-size:9px;padding:2px 7px;border-radius:3px;white-space:nowrap;">
                                            🧳 {{ $lgSegBag }}
                                        </span>
                                        @endif
                                        @if($lgAc)
                                            <span style="background:#f1f5f9;color:#64748b;font-size:9px;padding:2px 7px;border-radius:3px;white-space:nowrap;">
                                            {{ $lgAc }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Layover row --}}
                            @if($lgLvText)
                                <tr>
                                    <td colspan="6" style="border:1px solid #fde68a;padding:5px 12px;background:#fffbeb;font-size:10px;font-weight:600;color:#d97706;text-align:center;">
                                        ⏱ {{ $lgLvText }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                    @endforeach

                    </tbody>
                </table>

                {{-- ══════════════════════════════════════
                     FARE DATA — শুধু booked status এ দেখাবে
                ══════════════════════════════════════ --}}
{{--                @if($showFareData)--}}
                    @php
                        $bcGTotal   = $fdPricing['grand_total'] ?? [];
                        $bcBaseCurr = $bcGTotal['base_currency'] ?? ($fareBreakdowns[0]['currency'] ?? 'BDT');
                        $bcBaseAmt  = $bcGTotal['base_amount']   ?? ($fareBreakdowns[0]['subtotal'] ?? 0);
                        $bcEqCurr   = $bcGTotal['currency']      ?? 'BDT';
                        $bcTaxStr   = '';
                        foreach(($fareBreakdowns[0]['tax_breakdown'] ?? []) as $tx) {
                            $bcTaxStr .= number_format((float)$tx['amount']).$tx['code'].' ';
                        }
                        $bcTaxStr = trim($bcTaxStr);

                        // Payment summary rows
                        $pmRows = array_filter([
                            ['Base Fare',     $baseFee,    ''],
                            $taxFee > 0     ? ['Taxes & Fees',   $taxFee,    '']      : null,
                            $serviceFee > 0 ? ['Service Charge', $serviceFee,'']      : null,
                            $aitFee > 0     ? ['AIT Charge',     $aitFee,    '']      : null,
                            $penaltyAmt > 0 ? ['Penalty'.($penaltyRmk?' ('.$penaltyRmk.')':''), $penaltyAmt, 'red'] : null,
                            ($booking->void_charge??0)>0 ? ['Void Charge', (float)($booking->void_charge), 'red'] : null,
                            $totalDisc > 0  ? ['Discount',       -$totalDisc,'green'] : null,
                        ]);
                    @endphp

                    <div style="font-size:14px;font-weight:700;margin-bottom:8px;">Payment Summary</div>
                    <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
                        <tbody>

                        {{-- Base rows --}}
                        @foreach($pmRows as $pmRow)
                            <tr>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;background:#f8fafc;font-weight:600;width:180px;">
                                    {{ $pmRow[0] }}
                                </td>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;
                                color:{{ $pmRow[2]==='red'?'#dc2626':($pmRow[2]==='green'?'#059669':'#374151') }};
                                font-weight:{{ $pmRow[2]!==''?'700':'400' }};">
                                    {{ $pmRow[1] < 0 ? '−' : '' }}৳{{ number_format(ceil(abs($pmRow[1]))) }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Tax breakdown --}}
                        @if($bcTaxStr)
                            <tr>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;background:#f8fafc;font-weight:600;">Tax Breakdown</td>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:10px;color:#64748b;">{{ $bcTaxStr }}</td>
                            </tr>
                        @endif

                        {{-- Tour Code --}}
                        @if(!empty($booking->tour_code))
                            <tr>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;background:#f8fafc;font-weight:600;">Tour Code</td>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;">{{ $booking->tour_code }}</td>
                            </tr>
                        @endif

                        {{-- Total --}}
                        <tr style="background:#f0f9ff;">
                            <td style="border:1px solid #bae6fd;padding:8px 10px;font-size:12px;font-weight:700;color:#0369a1;">
                                Total Payable
                            </td>
                            <td style="border:1px solid #bae6fd;padding:8px 10px;font-size:15px;font-weight:800;color:#0369a1;">
                                ৳{{ number_format(ceil($displayTotal)) }}
                            </td>
                        </tr>

                        {{-- Wallet Used --}}
                        @if($walletUsed > 0)
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;font-size:13px">
        <span style="color:#7c3aed;display:flex;align-items:center;gap:6px">
            <i class="fas fa-wallet" style="font-size:11px"></i> Wallet Used
        </span>
        <span style="font-weight:600;color:#7c3aed">−৳{{ number_format($walletUsed,2) }}</span>
    </div>
@endif

@if($payable > 0)
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:11px 14px;margin-top:10px">
        <p style="font-size:11px;color:#059669;font-weight:600;margin-bottom:3px">Payable Amount</p>
        <p style="font-family:'Sora',sans-serif;font-weight:800;font-size:22px;color:#065f46;margin:0">
            ৳{{ number_format(ceil($payable)) }}
        </p>
    </div>
@else
    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:11px 14px;margin-top:10px">
        <p style="font-size:11px;color:#0284c7;font-weight:600;margin-bottom:3px">✓ Amount Paid</p>
        <p style="font-family:'Sora',sans-serif;font-weight:800;font-size:22px;color:#0369a1;margin:0">
            ৳{{ number_format(ceil($displayTotal)) }}
        </p>
    </div>
@endif

                        {{-- Gateway --}}
                        @if(!empty($booking->gateway))
                            <tr>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;background:#f8fafc;font-weight:600;">Payment Via</td>
                                <td style="border:1px solid #e2e8f0;padding:6px 10px;font-size:11px;color:#475569;">{{ ucfirst(str_replace('_',' ',$booking->gateway)) }}</td>
                            </tr>
                        @endif

                        </tbody>
                    </table>
{{--                @endif--}}
                {{-- ── /Fare Data ── --}}


                {{-- ══════════════════════════════════════
                     IMPORTANT REMINDERS — সব ক্ষেত্রেই দেখাবে
                ══════════════════════════════════════ --}}
                <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:700;color:#92400e;margin-bottom:8px;">
                        ⚠️ Important Reminders
                    </div>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            Arrive at least <strong style="margin:0 2px;">3 hours before</strong> departure for international flights.
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            Carry a valid <strong style="margin:0 2px;">passport &amp; photo ID</strong> for all passengers.
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            Checked baggage: <strong style="margin:0 2px;">{{ $checkedBag ?? '—' }}kg</strong>
                            @if(!empty($cabinBag)) · Cabin: <strong style="margin:0 2px;">{{ $cabinBag }}kg</strong>@endif per passenger.
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            Boarding gate closes <strong style="margin:0 2px;">30 minutes</strong> before departure. Please be at gate on time.
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            For changes or cancellations, contact support promptly.
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:7px;font-size:11px;color:#92400e;">
                            <span style="color:#fbbf24;flex-shrink:0;margin-top:1px;">•</span>
                            This is a <strong style="margin:0 2px;">computer-generated</strong> document. No signature required.
                        </li>
                    </ul>
                </div>

                {{-- ── Footer ── --}}
                <div style="border-top:1px solid #e2e8f0;padding-top:10px;display:flex;align-items:center;justify-content:space-between;font-size:10px;color:#94a3b8;">
                    <div>
                        <strong style="color:#003580;">{{ config('app.name','Shopno Tour') }}</strong>
                        · {{ config('app.email','support@shopnotour.com') }}
                        · {{ config('app.phone','01958553918') }}
                    </div>
                    <div style="font-size:11px;font-weight:700;color:#003580;font-family:monospace;letter-spacing:.15em;">
                        *{{ $booking->code }}*
                    </div>
                </div>

            </div>{{-- /#bookingCopyContent --}}
        </div>
    </div>
    {{-- /#bookingCopyModal --}}
    {{-- /#bookingCopyModal --}}

    {{-- TAU 30min Warning Modal --}}
    <div id="tauWarnModal" class="cmodal">
        <div class="mbox" style="border-top:4px solid #f59e0b">
            <div style="padding:20px;text-align:center">
                <div style="font-size:40px;margin-bottom:10px">⏰</div>
                <div style="font-size:16px;font-weight:800;color:#92400e;margin-bottom:6px">Ticket Deadline in 30 Minutes!</div>
                <div style="font-size:13px;color:#b45309;margin-bottom:16px">Your ticket must be issued before: <strong>{{ $tauDeadline }}</strong></div>
                <div style="font-size:28px;font-weight:900;color:#d97706;font-family:monospace;background:#fffbeb;border-radius:10px;padding:10px;margin-bottom:16px" id="warnTimerTxt">--:--:--</div>
                <button onclick="closeModal('tauWarnModal')" style="padding:10px 24px;border-radius:8px;border:none;background:linear-gradient(135deg,#667eea,#764ba2);color:white;font-size:13px;font-weight:700;cursor:pointer">Got it</button>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const paxImageData = @json($paxImageJson);

        /* ══════════════════════════════════════
                REISSUE LEG TOGGLE
            ══════════════════════════════════════ */
        function toggleLegDate(legIndex, isChecked) {
            const dateDiv = document.getElementById('leg-date-' + legIndex);
            const card    = document.getElementById('leg-card-' + legIndex);
            const input   = document.getElementById('leg-date-input-' + legIndex);

            if (dateDiv) dateDiv.style.display = isChecked ? 'block' : 'none';
            if (card)    card.style.borderColor = isChecked ? '#667eea' : '#e2e8f0';

            if (isChecked && input) {
                // ✅ Flatpickr init — already initialized হলে skip
                if (!input._flatpickr) {
                    flatpickr(input, {
                        dateFormat:  'Y-m-d',
                        minDate:     'today',
                        disableMobile: true,
                        appendTo:    document.body, // ✅ modal overflow issue fix
                        onChange: function(selectedDates, dateStr) {
                            // hidden input এ value set
                            input.value = dateStr;
                        }
                    });
                }
                // ✅ Open picker
                setTimeout(() => input._flatpickr && input._flatpickr.open(), 100);
            }

            if (input) input.required = isChecked;
        }

        function printBookingCopy() {
            const content = document.getElementById('bookingCopyContent').innerHTML;
            const win = window.open('', '_blank', 'width=800,height=600');
            win.document.write(`<!DOCTYPE html><html><head><title>Booking Copy - {{ $booking->code }}</title>
            <style>
                *{box-sizing:border-box;margin:0;padding:0}
                body{font-family:'Segoe UI',Arial,sans-serif;font-size:12px;color:#111;padding:16px;background:#fff}
                table{border-collapse:collapse;width:100%}
                @media print{@page{size:A4;margin:10mm 12mm}body{padding:0}}
            </style></head><body>`);
            win.document.write(content);
            win.document.write('</body></html>');
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 400);
        }

        function downloadBookingCopyPDF() {
            var btn = document.querySelector('button[onclick="downloadBookingCopyPDF()"]');
            var orig = btn ? btn.innerHTML : '';
            if(btn) btn.innerHTML = '⏳ Generating...';

            function _generate() {
                var el = document.getElementById('bookingCopyContent');

                // Logo image কে base64 করো
                var images = el.querySelectorAll('img');
                var promises = Array.from(images).map(function(img) {
                    return new Promise(function(resolve) {
                        if(!img.src || img.src.startsWith('data:')) { resolve(); return; }
                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');
                        var tempImg = new Image();
                        tempImg.crossOrigin = 'anonymous';
                        tempImg.onload = function() {
                            canvas.width = tempImg.naturalWidth;
                            canvas.height = tempImg.naturalHeight;
                            ctx.drawImage(tempImg, 0, 0);
                            try { img.src = canvas.toDataURL('image/png'); }
                            catch(e) { img.style.display = 'none'; }
                            resolve();
                        };
                        tempImg.onerror = function() { img.style.display = 'none'; resolve(); };
                        tempImg.src = img.src;
                    });
                });

                Promise.all(promises).then(function() {
                    var opt = {
                        margin:      [8, 8, 8, 8],
                        filename:    'Booking-Copy-{{ $booking->code }}.pdf',
                        image:       { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true, allowTaint: true, logging: false },
                        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
                        pagebreak:   { mode: ['avoid-all', 'css', 'legacy'] }
                    };
                    html2pdf().set(opt).from(el).save().then(function() {
                        if(btn) btn.innerHTML = orig;
                    });
                });
            }

            if(typeof html2pdf === 'undefined') {
                var s = document.createElement('script');
                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                s.onload = _generate;
                document.head.appendChild(s);
            } else {
                _generate();
            }
        }
        /* ══════════════════════════════════════
           SECTION TOGGLE
        ══════════════════════════════════════ */
        function toggleSection(header, id) {
            const body    = document.getElementById(id);
            const chevron = header.querySelector('.f-chevron');
            const isOpen  = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            header.classList.toggle('collapsed', isOpen);
            if (chevron) {
                chevron.classList.toggle('fa-chevron-up',   !isOpen);
                chevron.classList.toggle('fa-chevron-down',  isOpen);
            }
            header.style.borderBottom = isOpen ? 'none' : '1px solid #f1f5f9';
        }

        /* ══════════════════════════════════════
           MODALS
        ══════════════════════════════════════ */
        $(function(){
            window.openModal = function(id) {
                document.getElementById(id).classList.add('show');
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    $('#'+id).find('.select2-pax').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible'))
                            $(this).select2({ placeholder:'Select...', allowClear:true, width:'100%', dropdownParent:$('#'+id) });
                    });
                }, 150);
            };
            window.closeModal = function(id) {
                $('#'+id).find('.select2-pax').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                });
                document.getElementById(id).classList.remove('show');
                document.body.style.overflow = 'auto';
            };
        });
        window.addEventListener('click', e => {
            if (e.target.classList.contains('cmodal')) closeModal(e.target.id);
        });

        /* ══════════════════════════════════════
           MODAL FORM SUBMIT
        ══════════════════════════════════════ */
        function doModal(event, action) {
            event.preventDefault();
            const fd   = new FormData(event.target);

            for (let [k,v] of fd.entries()) console.log(k, '=', v);
            let pids   = fd.getAll('passenger_ids[]');
            if (pids.includes('all')) pids = ['all'];

            const data = {
                booking_id:   '{{ $booking->id }}',
                booking_code: '{{ $booking->code }}',
                action
            };

            // ✅ Legs nested object build করো
            const legs = {};
            for (let [k, v] of fd.entries()) {
                if (k === 'passenger_ids[]') continue;

                // legs[0][selected], legs[0][new_date] এগুলো parse করো
                const legMatch = k.match(/^legs\[(\d+)\]\[(\w+)\]$/);
                if (legMatch) {
                    const idx   = legMatch[1];
                    const field = legMatch[2];
                    if (!legs[idx]) legs[idx] = {};
                    legs[idx][field] = v;
                } else {
                    data[k] = v;
                }
            }

            // ✅ Legs object to array
            if (Object.keys(legs).length > 0) {
                data.legs = Object.values(legs);
            }

            if (pids.length) data.passenger_ids = pids;

            fetch('{{ route("booking.action") }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        if (res.redirect_url) { alert(res.message); window.location.href = res.redirect_url; }
                        else { alert(res.message); closeModal(event.target.closest('.cmodal').id); location.reload(); }
                    } else alert('Error: ' + (res.message || 'Something went wrong'));
                })
                .catch(() => alert('Request failed'));
        }

        /* ══════════════════════════════════════
           PAYMENT
        ══════════════════════════════════════ */
        window._pm = 'wallet';
        function selPay(el, m) {
            document.querySelectorAll('.spay').forEach(x => {
                x.style.borderColor = '#e5e7eb'; x.style.background = 'white';
                const d = x.querySelector('.spdot');
                if (d) { d.style.background = ''; d.style.borderColor = '#d1d5db'; d.innerHTML = ''; }
            });
            el.style.borderColor = '#667eea'; el.style.background = '#eef2ff';
            const d = el.querySelector('.spdot');
            if (d) { d.style.background='#667eea'; d.style.borderColor='#667eea'; d.innerHTML='<div style="width:5px;height:5px;border-radius:50%;background:white"></div>'; }
            window._pm = m;
        }
        function doPayment(id) {
            // booking id টা modal এ save করি
            document.getElementById('payConfirmModal').dataset.bookingId = id;
            openModal('payConfirmModal');
        }

        function doPaymentConfirmed() {

            var id = document.getElementById('payConfirmModal').dataset.bookingId;
            closeModal('payConfirmModal');

            fetch('{{ route("booking.payment.initiate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ booking_id: id, payment_method: window._pm })
            })
                .then(r => r.json())
                .then(d => {
                    if (d.redirect_url) {
                        window.location.href = d.redirect_url;
                    } else if (d.success) {
                        alert(d.message || 'Payment successful!');
                        location.reload();
                    } else {
                        alert('Error: ' + (d.message || 'Something went wrong'));
                    }
                })
                .catch(() => alert('Payment request failed'));
        }

        /* ══════════════════════════════════════
   PASSENGER DOCUMENTS
══════════════════════════════════════ */
        const _notUploaded = `
    <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:8px;
                padding:28px 12px;color:#94a3b8;font-size:12px;line-height:1.6">
        <div style="font-size:22px;margin-bottom:6px">📷</div>
        Not uploaded
    </div>`;

        const _makeImg = (url) => `
    <img src="${url}"
         style="max-width:100%;max-height:200px;object-fit:contain;border-radius:8px;
                cursor:zoom-in;border:1px solid #e2e8f0;display:block;margin:0 auto;"
         onclick="window.open('${url}','_blank')"
         onerror="this.parentElement.innerHTML=_notUploaded">`;

        function openPaxDocs(idx, name) {
            document.getElementById('paxDocsName').textContent = name;

            const pax = paxImageData[idx] ?? null;

            document.getElementById('paxPassportImg').innerHTML =
                pax?.passport_image ? _makeImg(pax.passport_image) : _notUploaded;

            document.getElementById('paxVisaImg').innerHTML =
                pax?.visa_image ? _makeImg(pax.visa_image) : _notUploaded;

            openModal('paxDocsModal');
        }
        /* ══════════════════════════════════════
           FARE TABS
        ══════════════════════════════════════ */
        function switchFareTab2(id, btn) {
            document.querySelectorAll('.fare2-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.fare2-tab-btn').forEach(b => { b.style.borderBottomColor='transparent'; b.style.color='#94a3b8'; });
            document.getElementById(id).classList.remove('hidden');
            btn.style.borderBottomColor = '#0ea5e9';
            btn.style.color = '#0ea5e9';
        }

        /* ══════════════════════════════════════
           PRINT
        ══════════════════════════════════════ */
        function printTicket() {
            const c  = document.getElementById('tpz').cloneNode(true);
            const ob = document.body.innerHTML;
            document.body.innerHTML = c.outerHTML;
            const s = document.createElement('style');
            s.innerHTML = '@media print{body{margin:0;padding:20px;background:white;}@page{margin:.5cm;}}';
            document.head.appendChild(s);
            window.print();
            document.body.innerHTML = ob;
            location.reload();
        }

        /* ══════════════════════════════════════
           TAU COUNTDOWN TIMER
        ══════════════════════════════════════ */
        @if(!empty($tauDeadlineIso) && !$isTicketed)
        (function() {
            const deadline     = new Date('{{ $tauDeadlineIso }}').getTime();
            const badgeTxt     = document.getElementById('tauTimerTxt');
            const badgeEl      = document.getElementById('tauTimerBadge');
            const mobileBadge  = document.getElementById('mobileTimerBadge');
            const mobileTxt    = document.getElementById('mobileTimerTxt');
            const warnTxt      = document.getElementById('warnTimerTxt');
            let warnShown      = false;
            let expiredShown   = false;

            function pad(n) { return n < 10 ? '0'+n : n; }

            function tick() {
                const now  = Date.now();
                const diff = deadline - now;

                if (diff <= 0) {
                    const s = '00:00:00';
                    if (badgeTxt)    badgeTxt.textContent    = s;
                    if (mobileTxt)   mobileTxt.textContent   = s;
                    if (warnTxt)     warnTxt.textContent     = s;
                    if (badgeEl)     badgeEl.classList.add('expired');
                    if (!expiredShown) {
                        expiredShown = true;
                        alert('⏰ Ticket deadline has passed! Please contact support immediately.');
                    }
                    return;
                }

                const hrs  = Math.floor(diff / 3600000);
                const mins = Math.floor((diff % 3600000) / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                const str  = pad(hrs) + ':' + pad(mins) + ':' + pad(secs);

                if (badgeTxt)    badgeTxt.textContent  = str;
                if (mobileTxt)   mobileTxt.textContent = str;
                if (warnTxt)     warnTxt.textContent   = str;

                // Warning coloring
                const isWarning = diff <= 30 * 60 * 1000; // 30 minutes
                if (badgeEl) {
                    badgeEl.classList.toggle('warning', isWarning);
                }

                // Show warning modal once at 30 min mark
                if (isWarning && !warnShown) {
                    warnShown = true;
                    if (mobileBadge) mobileBadge.style.display = 'block';
                    openModal('tauWarnModal');
                }

                setTimeout(tick, 1000);
            }

            tick();
        })();
        @endif

        // document.addEventListener('DOMContentLoaded', function() {
        //     document.querySelectorAll('.spay[onclick*="wallet"]').forEach(function(btn) {
        //         selPay(btn, 'wallet');
        //     });
        // });

        /* ══════════════════════════════════════
   SSR TYPE INFO
══════════════════════════════════════ */

        // Baggage charges from PHP
        const baggageCharges = @json($checkedBagCharges ?? []);
        const airlineName    = @json($airlineName ?? '');
        const checkedBagKg   = @json($checkedBag ?? 0);
        const cabinBagKg     = @json($cabinBag ?? 0);

        function onSSRTypeChange(type) {
            const card        = document.getElementById('ssrInfoCard');
            const detailField = document.getElementById('ssrDetailsField');

            if (!type) { card.style.display = 'none'; return; }

            let html      = '';
            let placeholder = 'Please describe your request...';

            switch(type) {

                case 'baggage':
                    placeholder = 'e.g. 1 extra bag of 23kg for DAC-DXB flight';
                    let bagHtml = `
                <div style="background:#faf5ff;border:1.5px solid #ddd6fe;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#5b21b6;margin-bottom:8px">
                        🧳 Extra Baggage Info — ${airlineName || 'Airline'}
                    </div>
                    <div style="font-size:11px;color:#374151;margin-bottom:6px">
                        <span style="background:#ede9fe;color:#5b21b6;padding:2px 8px;border-radius:999px;font-weight:600">
                            Included: ${checkedBagKg}kg checked + ${cabinBagKg}kg cabin
                        </span>
                    </div>`;

                    if (baggageCharges && baggageCharges.length > 0) {
                        bagHtml += `<div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Extra Bag Charges:</div>
                <div style="display:flex;flex-direction:column;gap:4px">`;
                        baggageCharges.forEach(charge => {
                            const weight = charge.max_weight_kg ? charge.max_weight_kg + 'kg' : (charge.special_item || 'Special');
                            const fee    = charge.fee_amount + ' ' + charge.fee_currency;
                            const pieces = charge.pieces ? charge.pieces + ' pc' : '';
                            bagHtml += `
                        <div style="display:flex;justify-content:space-between;align-items:center;background:white;border:1px solid #e9d5ff;border-radius:6px;padding:5px 10px">
                            <span style="font-size:11px;color:#374151">
                                ${pieces ? pieces + ' · ' : ''}${weight}
                                ${charge.max_size_cm ? ' · ' + charge.max_size_cm + 'cm' : ''}
                            </span>
                            <span style="font-size:12px;font-weight:700;color:#5b21b6">${fee}</span>
                        </div>`;
                        });
                        bagHtml += `</div>`;
                    } else {
                        bagHtml += `<div style="font-size:11px;color:#94a3b8">Charge info available upon request. Admin will confirm.</div>`;
                    }

                    bagHtml += `<div style="font-size:10px;color:#7c3aed;margin-top:8px">
                ⚠️ Actual charge airline policy অনুযায়ী admin confirm করবে।
            </div></div>`;
                    html = bagHtml;
                    break;

                case 'meal':
                    placeholder = 'e.g. Vegetarian meal (VGML), Diabetic meal (DBML), Halal meal (MOML)';
                    html = `
                <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#c2410c;margin-bottom:6px">🍽️ Common Meal Codes</div>
                    <div style="display:flex;flex-wrap:wrap;gap:5px">
                        ${[['VGML','Vegetarian'],['MOML','Halal'],['KSML','Kosher'],['DBML','Diabetic'],['LFML','Low Fat'],['CHML','Child Meal'],['BBML','Baby Meal'],['SFML','Seafood']].map(([code,label]) =>
                        `<span onclick="document.getElementById('ssrDetailsField').value='${label} (${code})'"
                                   style="background:#ffedd5;border:1px solid #fed7aa;border-radius:5px;padding:3px 8px;font-size:10px;font-weight:600;color:#c2410c;cursor:pointer"
                                   title="Click to select">
                                ${code} — ${label}
                            </span>`
                    ).join('')}
                    </div>
                    <div style="font-size:10px;color:#9a3412;margin-top:6px">👆 Click করলে auto-fill হবে</div>
                </div>`;
                    break;

                case 'wheelchair':
                    placeholder = 'e.g. WCHR - can walk short distance, WCHS - cannot climb stairs, WCHC - fully immobile';
                    html = `
                <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#166534;margin-bottom:6px">♿ Wheelchair Types</div>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        ${[
                        ['WCHR','Can walk short distances, needs wheelchair for long distances'],
                        ['WCHS','Cannot climb stairs, needs wheelchair'],
                        ['WCHC','Completely immobile, needs full assistance'],
                    ].map(([code, desc]) =>
                        `<div onclick="document.getElementById('ssrDetailsField').value='${code} - ${desc}'"
                                  style="background:white;border:1px solid #bbf7d0;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:11px">
                                <strong style="color:#166534">${code}</strong>
                                <span style="color:#374151"> — ${desc}</span>
                            </div>`
                    ).join('')}
                    </div>
                    <div style="font-size:10px;color:#166534;margin-top:6px">👆 Click করলে auto-fill হবে</div>
                </div>`;
                    break;

                case 'seat':
                    placeholder = 'e.g. Window seat preferred, Aisle seat, Exit row, Bulkhead seat';
                    html = `
                <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#1d4ed8;margin-bottom:6px">💺 Seat Options</div>
                    <div style="display:flex;flex-wrap:wrap;gap:5px">
                        ${['Window Seat','Aisle Seat','Bulkhead Seat','Exit Row','Front of Cabin','Extra Legroom'].map(s =>
                        `<span onclick="document.getElementById('ssrDetailsField').value='${s}'"
                                   style="background:#dbeafe;border:1px solid #bfdbfe;border-radius:5px;padding:3px 8px;font-size:10px;font-weight:600;color:#1d4ed8;cursor:pointer">
                                ${s}
                            </span>`
                    ).join('')}
                    </div>
                    <div style="font-size:10px;color:#1d4ed8;margin-top:6px">👆 Click করলে auto-fill হবে · Seat guarantee নয়, request হিসেবে পাঠানো হবে</div>
                </div>`;
                    break;

                case 'infant':
                    placeholder = 'e.g. Bassinet/Crib request for infant, infant age: 8 months';
                    html = `
                <div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#be123c;margin-bottom:6px">👶 Infant Services</div>
                    <div style="font-size:11px;color:#374151;line-height:1.7">
                        • <strong>BSCT</strong> — Bassinet/Crib (bulkhead seat প্রয়োজন)<br>
                        • <strong>UMNR</strong> — Unaccompanied Minor service<br>
                        • Infant এর age ও weight details দিন
                    </div>
                </div>`;
                    break;

                case 'medical':
                    placeholder = 'e.g. Oxygen required, stretcher needed, specific medical condition';
                    html = `
                <div style="background:#fef9c3;border:1.5px solid #fde047;border-radius:10px;padding:12px 14px">
                    <div style="font-size:11px;font-weight:700;color:#854d0e;margin-bottom:6px">🏥 Medical Request</div>
                    <div style="font-size:11px;color:#374151;line-height:1.7">
                        • Medical condition সংক্ষেপে বর্ণনা করুন<br>
                        • প্রয়োজনীয় equipment উল্লেখ করুন (oxygen, stretcher, etc.)<br>
                        • Doctor's certificate প্রয়োজন হতে পারে
                    </div>
                    <div style="font-size:10px;color:#92400e;margin-top:6px;font-weight:600">
                        ⚠️ Medical SSR এর জন্য airline approval mandatory
                    </div>
                </div>`;
                    break;

                default:
                    html = '';
                    break;
            }

            card.innerHTML       = html;
            card.style.display   = html ? 'block' : 'none';
            detailField.placeholder = placeholder;
        }
    </script>

@endpush
