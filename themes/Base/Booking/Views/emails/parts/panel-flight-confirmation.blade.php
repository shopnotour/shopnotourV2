<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #fff; color: #000; font-size: 12px; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        img { max-width: 100%; }
    </style>
</head>
<body>
@php
    use Modules\Flight\Models\Airline;

    $toHM = fn(int $m): string => floor($m/60).'h '.($m%60).'m';

    $segments       = $flightData['segments']    ?? [];
    $passengers     = $flightData['passengers']  ?? [];
    $journeys       = $flightData['journeys']    ?? [];
    $pricing        = $flightData['pricing']     ?? [];
    $fareBreakdowns = $pricing['fare_breakdowns'] ?? [];
    $checkedBag     = $pricing['checked_bag_kg'] ?? null;
    $pnrGDS         = $flightData['air_reservation']['locator_code']  ?? null;
    $pnrAirline     = $flightData['supplier_locator']['locator_code'] ?? null;
    $airCode        = $flightData['supplier_locator']['supplier_code'] ?? null;

    // Logo — base64 embed for email clients
    $logoUrl = $logoUrl ?? get_file_url(setting_item('logo_id'), 'full');
    $logoSrc = '';
    if ($logoUrl) {
        $logoPath = public_path(parse_url($logoUrl, PHP_URL_PATH));
        if (file_exists($logoPath)) {
            $mime    = mime_content_type($logoPath);
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        } else {
            $logoSrc = $logoUrl;
        }
    }

    // Helper: URL → base64 data URI
    $toBase64 = function(string $url): string {
        if (empty($url)) return '';
        $path = public_path(parse_url($url, PHP_URL_PATH));
        if (!file_exists($path)) return $url;
        $mime = mime_content_type($path);
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    // leg groups
    $legGroups = [];
    foreach ($journeys as $ji => $journey) {
        $legGroups[$ji] = [
            'journey'  => $journey,
            'segments' => [],
            'type'     => $ji === 0 ? 'outbound' : 'return',
        ];
    }
    usort($segments, fn($a, $b) => ($a['travel_order'] ?? 0) - ($b['travel_order'] ?? 0));
    $segmentPool = $segments;
    foreach ($legGroups as $ji => &$lg) {
        $needed = $lg['journey']['number_of_flights'] ?? 1;
        $lg['segments'] = array_splice($segmentPool, 0, $needed);
    }
    unset($lg);
@endphp

{{-- ── Agency Header ── --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:24px; border-bottom:2px solid #e2e8f0; padding-bottom:16px;">
    <tr>
        <td style="vertical-align:top;">
            <div style="font-size:16px; font-weight:800; margin-bottom:6px;">SHOPNO TOUR</div>
            <div style="font-size:11px; color:#333; line-height:1.8;">
                Address: Green square House # 1/B, Floor 3/A, Road # 08, Gulshan-1 CNS Tower, 2nd Floor, Dhaka-1212.<br>
                Contact number: 0248815080/0248815081<br>
                IATA CODE: 42342576<br>
                Civil Aviation Number: 0014018<br>
                E-mail: shopnotourbd@gmail.com
            </div>
        </td>
        <td style="vertical-align:top; text-align:right; width:120px;">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Shopno Tour" style="height:70px; width:auto; object-fit:contain;">
            @endif
        </td>
    </tr>
</table>

{{-- ── Booking Confirmation Title ── --}}
<div style="font-size:16px; font-weight:700; margin-bottom:14px;">
    ✈ Flight Booking Confirmation
    <span style="font-size:13px; font-weight:400; color:#64748b; margin-left:8px;">Ref: {{ $booking->code }}</span>
</div>

{{-- ── Passenger Information ── --}}
<div style="font-size:13px; font-weight:700; margin-bottom:6px;">Passenger Information</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
    <thead>
    <tr style="background:#b8d4e8;">
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passenger</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Date of Birth</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passport Number</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Passport Expiry</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Frequent Flyer</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Ticket</th>
    </tr>
    </thead>
    <tbody>
    @foreach($passengers as $pax)
        @php
            $fn     = strtoupper(trim($pax['first_name'] ?? ''));
            $ln     = strtoupper(trim($pax['last_name']  ?? ''));
            $prefix = strtoupper(trim($pax['prefix']     ?? ''));
            $pName  = trim($ln . ($ln && $fn ? '/' : '') . $fn . ($prefix ? ' ' . $prefix : ''));
            if (!$pName) $pName = 'PASSENGER';
            $dob    = !empty($pax['dob'])             ? date('d M Y', strtotime($pax['dob']))             : '';
            $expiry = !empty($pax['passport_expiry']) ? date('d M Y', strtotime($pax['passport_expiry'])) : '';
            $ffn    = $pax['frequent_flyer_number']   ?? ($pax['ffn'] ?? '');
            $ticket = $pax['ticket_number']           ?? '';
        @endphp
        <tr>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $pName }}</td>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $dob }}</td>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; font-family:monospace; font-weight:700;">{{ $pax['passport_number'] ?? '' }}</td>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $expiry }}</td>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $ffn }}</td>
            <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $ticket }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{-- ── PNR ── --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
    <thead>
    <tr style="background:#b8d4e8;">
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Airline PNR</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">GDS PNR</th>
        <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Date of Issue</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">
            {{ $pnrAirline ? $pnrAirline . ' (' . $airCode . ' - ' . ($segments[0]['airline_name'] ?? $airCode) . ')' : ($pnrGDS ?? '—') }}
        </td>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; font-weight:700; font-family:monospace; letter-spacing:.1em;">{{ $pnrGDS ?? '—' }}</td>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ date('d M Y') }}</td>
    </tr>
    </tbody>
</table>

{{-- ── Itinerary ── --}}
<div style="font-size:13px; font-weight:700; margin-bottom:10px;">Itinerary Information</div>

@foreach($legGroups as $li => $legGroup)
    @php
        $legSegs = $legGroup['segments'];
        $legJrn  = $legGroup['journey'];
        $isOut   = $legGroup['type'] === 'outbound';
        $fcBagKg = null;
        foreach ($fareBreakdowns as $fb) {
            $fcArr = $fb['fare_construction'] ?? [];
            $fc    = $fcArr[$li] ?? ($fcArr[0] ?? null);
            if (!empty($fc['checked_bag_kg'])) { $fcBagKg = $fc['checked_bag_kg']; break; }
        }
        $legBag = $fcBagKg ? $fcBagKg . 'K' : ($checkedBag ? $checkedBag . 'K' : '');
    @endphp

    {{-- Leg title --}}
    <div style="background:#1d4ed8; color:#fff; padding:6px 12px; border-radius:4px 4px 0 0; font-size:11px; font-weight:700; margin-bottom:0;">
        {{ $isOut ? '✈ OUTBOUND' : '✈ RETURN' }}
        &nbsp;·&nbsp; {{ $legJrn['first_airport_code'] }} → {{ $legJrn['last_airport_code'] }}
        &nbsp;·&nbsp; {{ date('D, d M Y', strtotime($legJrn['departure_date'])) }}
    </div>

    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
        <thead>
        <tr style="background:#e8f0fb;">
            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:130px;">Flight</th>
            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px;">Route</th>
            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:100px;">Depart</th>
            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:100px;">Arrive</th>
            <th style="border:1px solid #999; padding:6px 8px; text-align:left; font-size:11px; width:170px;">Details</th>
        </tr>
        </thead>
        <tbody>
        @foreach($legSegs as $si => $seg)
            @php
                $airlineName = $seg['airline_name'] ?? $seg['carrier'];
                $flightNo    = $seg['carrier'] . ($seg['flight_number'] ?? '');

                // Raw time from ISO — no timezone conversion
                preg_match('/T(\d{2}:\d{2})/', $seg['departure_time'], $dm);
                preg_match('/T(\d{2}:\d{2})/', $seg['arrival_time'],   $am);
                $depRaw = $dm[1] ?? '00:00';
                $arrRaw = $am[1] ?? '00:00';
                $dep12  = date('h:i A', strtotime($depRaw));
                $arr12  = date('h:i A', strtotime($arrRaw));

                // Date from ISO
                preg_match('/^(\d{4}-\d{2}-\d{2})/', $seg['departure_time'], $dd);
                preg_match('/^(\d{4}-\d{2}-\d{2})/', $seg['arrival_time'],   $ad);
                $depDate = !empty($dd[1]) ? date('D, d M Y', strtotime($dd[1])) : '';
                $arrDate = !empty($ad[1]) ? date('D, d M Y', strtotime($ad[1])) : '';

                $dur  = $toHM($seg['travel_time'] ?? 0);
                $cbU  = strtoupper(trim($seg['cabin_class'] ?? ''));
                if (in_array($cbU, ['Y', 'ECONOMY']))                                 $cl = 'Y-Economy';
                elseif (in_array($cbU, ['E', 'ECONOMY CLASSIC', 'PREMIUM ECONOMY']))  $cl = 'E-Economy';
                elseif (in_array($cbU, ['BUSINESS', 'C', 'J']))                       $cl = 'Business';
                elseif (in_array($cbU, ['FIRST', 'F']))                               $cl = 'First';
                else                                                                   $cl = $seg['cabin_class'] ?? '';

                $ac = $seg['aircraft_name'] ?? '';
                if (empty($ac) || (strlen($ac) <= 4 && ctype_alnum($ac))) $ac = $seg['equipment'] ?? '';
                if (strlen($ac) <= 4 && ctype_alnum($ac)) $ac = '';

                $status = $seg['status_name'] ?? $seg['status'] ?? 'Confirmed';

                $orig    = $seg['origin_city']         ?? $seg['origin'];
                $origApt = $seg['origin_airport_name'] ?? '';
                $fromTxt = $orig . ($origApt ? ' - ' . $origApt : '') . (isset($seg['departure_terminal']) && $seg['departure_terminal'] ? ' T' . $seg['departure_terminal'] : '');

                $dest    = $seg['destination_city']         ?? $seg['destination'];
                $destApt = $seg['destination_airport_name'] ?? '';
                $toTxt   = $dest . ($destApt ? ' - ' . $destApt : '') . (isset($seg['arrival_terminal']) && $seg['arrival_terminal'] ? ' T' . $seg['arrival_terminal'] : '');

                $isLastSeg = $si === count($legSegs) - 1;
                $lvText    = '';
                if (!$isLastSeg) {
                    $ns        = $legSegs[$si + 1];
                    preg_match('/^(\d{4}-\d{2}-\d{2})/', $seg['arrival_time'],  $aDate);
                    preg_match('/^(\d{4}-\d{2}-\d{2})/', $ns['departure_time'], $nDate);
                    $lvMin     = (int)((strtotime($ns['departure_time']) - strtotime($seg['arrival_time'])) / 60);
                    $overnight = (!empty($aDate[1]) && !empty($nDate[1]) && $aDate[1] !== $nDate[1]) ? ' · Overnight' : '';
                    $lvText    = floor($lvMin / 60) . 'h ' . ($lvMin % 60) . 'm layover at ' . $seg['destination'] . $overnight;
                }

                // Baggage
                $segBag = '';
                if (!empty($seg['checked_bag_allowance'])) $segBag = $seg['checked_bag_allowance'];
                if (empty($segBag)) {
                    foreach ($fareBreakdowns as $fb) {
                        $fcArr = $fb['fare_construction'] ?? [];
                        $fc    = $fcArr[$li] ?? ($fcArr[0] ?? null);
                        if (!empty($fc['checked_bag_kg'])) { $segBag = $fc['checked_bag_kg'] . 'K'; break; }
                    }
                }
                if (empty($segBag) && !empty($legBag)) $segBag = $legBag;
                if (empty($segBag) && $checkedBag)     $segBag = $checkedBag . 'K';

                // Airline image — base64 embed
                $airlineInfo   = Airline::getByCode($seg['carrier'] ?? '');
                $airlineRawImg = $airlineInfo['image_thumb'] ?? $airlineInfo['image_url'] ?? '';
                $airlineImg    = $airlineRawImg ? $toBase64($airlineRawImg) : '';
            @endphp

            <tr style="vertical-align:top; background:#fff;">

                {{-- Flight --}}
                <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px;">
                    @if($airlineImg)
                        <img src="{{ $airlineImg }}" style="height:22px; width:auto; object-fit:contain; margin-bottom:4px; display:block;">
                    @else
                        <span style="display:inline-block; background:#c0392b; color:#fff; border-radius:50%; width:16px; height:16px; text-align:center; line-height:16px; font-size:9px; margin-bottom:3px;">✈</span>
                    @endif
                    <div style="font-weight:700;">{{ $airlineName }}</div>
                    <div style="font-family:monospace; color:#1d4ed8; font-weight:700;">{{ $flightNo }}</div>
                </td>

                {{-- Route --}}
                <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="text-align:center; width:60px;">
                                <div style="font-size:15px; font-weight:800; color:#0f172a;">{{ $seg['origin'] }}</div>
                                <div style="font-size:9px; color:#64748b;">{{ $fromTxt }}</div>
                            </td>
                            <td style="text-align:center; color:#94a3b8; font-size:10px; white-space:nowrap; padding:0 6px;">
                                ——✈——<br>
                                <span style="font-size:10px; color:#64748b;">{{ $dur }}</span>
                            </td>
                            <td style="text-align:center; width:60px;">
                                <div style="font-size:15px; font-weight:800; color:#0f172a;">{{ $seg['destination'] }}</div>
                                <div style="font-size:9px; color:#64748b;">{{ $toTxt }}</div>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Depart --}}
                <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; white-space:nowrap;">
                    <div style="font-size:14px; font-weight:800;">{{ $depRaw }}</div>
                    <div style="font-size:11px; color:#475569;">{{ $dep12 }}</div>
                    <div style="font-size:10px; color:#64748b;">{{ $depDate }}</div>
                </td>

                {{-- Arrive --}}
                <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; white-space:nowrap;">
                    <div style="font-size:14px; font-weight:800;">{{ $arrRaw }}</div>
                    <div style="font-size:11px; color:#475569;">{{ $arr12 }}</div>
                    <div style="font-size:10px; color:#64748b;">{{ $arrDate }}</div>
                </td>

                {{-- Details --}}
                <td style="border:1px solid #ccc; padding:7px 8px; font-size:11px; line-height:1.8; color:#374151;">
                    Class : {{ $seg['cabin_class'] ?? '' }}
                    @if(!empty($seg['class_of_service']))
                        ({{ $seg['class_of_service'] }})
                    @endif <br>
                    Duration : {{ $dur }}<br>
                    Status : <span style="color:#059669; font-weight:600;">{{ $status }}</span><br>
                    @if($ac)Aircraft : {{ $ac }}<br>@endif
                    @if($segBag)Baggage : {{ $segBag }}<br>@endif
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

{{-- ── Fare Data ── --}}
@php
    $gTotal  = $pricing['grand_total'] ?? [];
    $eqCurr  = $gTotal['currency'] ?? 'BDT';
    $taxStr  = '';
    foreach (($fareBreakdowns[0]['tax_breakdown'] ?? []) as $tx) {
        $taxStr .= number_format((float)$tx['amount']) . $tx['code'] . ' ';
    }
    $taxStr = trim($taxStr);
@endphp

<div style="font-size:13px; font-weight:700; margin-bottom:6px;">Fare Data</div>
<table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
    <tbody>
    <tr>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; width:160px; background:#f5f5f5; font-weight:600;">Equivalent Fare</td>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ number_format((float)($gTotal['subtotal'] ?? 0), 2) }} {{ $eqCurr }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Tax</td>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px;">{{ $taxStr ?: '—' }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; background:#f5f5f5; font-weight:600;">Total</td>
        <td style="border:1px solid #ccc; padding:5px 8px; font-size:11px; font-weight:700; color:#1d4ed8;">{{ number_format((float)($booking->total ?? 0), 2) }} {{ $eqCurr }}</td>
    </tr>
    </tbody>
</table>

{{-- ── Footer ── --}}
<div style="border-top:1px solid #e2e8f0; padding-top:10px; font-size:10px; color:#94a3b8; text-align:center;">
    Computer-generated booking confirmation. No signature required.
</div>

</body>
</html>
