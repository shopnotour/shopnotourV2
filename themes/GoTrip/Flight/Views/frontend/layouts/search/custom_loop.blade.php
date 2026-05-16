{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0">--}}
{{--    <title>Flight Search Results</title>--}}
{{--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">--}}
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">--}}
<!-- Bootstrap CSS -->
{{--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">--}}

{{--<!-- Bootstrap Icons -->--}}
{{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">--}}

{{--</head>--}}

{{--@push('css')--}}
    <!-- Bootstrap Icons -->
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">--}}

{{--    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>--}}

<link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>

<style>
    /* ===== EXISTING THEME STYLE ===== */
    .bravo_wrap .bravo_search_flight .bravo_form_search {
        margin-bottom: 0px;
    }

    /* ===== FLIGHT LOOP SPECIFIC STYLES ===== */

    /* Flight Card Container */
    .bravo_search_flight .flight-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: box-shadow 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .bravo_search_flight .flight-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        border-color: #dc3545;
    }

    /* Flight Route Layout */
    .bravo_search_flight .flight-route {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .bravo_search_flight .airport-info {
        flex: 1;
        text-align: center;
    }

    .bravo_search_flight .airport-code {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }

    .bravo_search_flight .airport-name {
        font-size: 12px;
        color: #666;
    }

    .bravo_search_flight .flight-time {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-top: 5px;
    }

    .bravo_search_flight .flight-date {
        font-size: 11px;
        color: #999;
    }

    /* Duration Line */
    .bravo_search_flight .flight-duration {
        flex: 2;
        text-align: center;
        position: relative;
    }

    .bravo_search_flight .duration-line {
        height: 2px;
        background: #ddd;
        position: relative;
        margin: 10px 40px;
    }

    .bravo_search_flight .duration-line::before {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: #ddd;
        border-radius: 50%;
        left: -4px;
        top: -3px;
    }

    .bravo_search_flight .duration-line::after {
        content: '';
        position: absolute;
        border-left: 8px solid #ddd;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        right: -8px;
        top: -4px;
    }

    .bravo_search_flight .duration-text {
        font-size: 13px;
        color: #666;
        font-weight: 500;
    }

    .bravo_search_flight .flight-class {
        font-size: 11px;
        color: #999;
        text-transform: uppercase;
    }

    /* Airline Info */
    .bravo_search_flight .airline-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
        border-radius: 6px;
        padding: 4px;
        background: #f8f9fa;
    }

    .bravo_search_flight .airline-name {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    /* Badges */
    .bravo_search_flight .badge-meal {
        background: #d4edda;
        color: #155724;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 12px;
    }

    .bravo_search_flight .badge-stop {
        background: #d1ecf1;
        color: #0c5460;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 12px;
    }

    /* Price Section */
    .bravo_search_flight .price-section {
        text-align: right;
        border-left: 1px solid #e9ecef;
        padding-left: 20px;
    }

    .bravo_search_flight .price-label {
        font-size: 13px;
        color: #666;
    }

    .bravo_search_flight .base-price {
        font-size: 14px;
        color: #999;
        text-decoration: line-through;
    }

    .bravo_search_flight .total-price {
        font-size: 28px;
        font-weight: 700;
        color: #1976d2;
        margin: 5px 0;
    }

    .bravo_search_flight .price-details {
        font-size: 12px;
        color: #666;
    }

    .bravo_search_flight .discount-badge {
        background: #d4edda;
        color: #155724;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    /* Buttons */
    .bravo_search_flight .btn-select {
        background: #dc3545;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
    }

    .bravo_search_flight .btn-select:hover {
        background: #c82333;
        color: white;
    }

    .bravo_search_flight .show-details-btn {
        background: transparent;
        border: none;
        color: #dc3545;
        font-size: 13px;
        cursor: pointer;
        padding: 5px 0;
        margin-top: 10px;
    }

    .bravo_search_flight .show-details-btn:hover {
        text-decoration: underline;
    }

    /* Flight Details */
    .bravo_search_flight .flight-details {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #e9ecef;
    }

    .bravo_search_flight .layover-alert {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px;
        margin: 10px 0;
        border-radius: 4px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .bravo_search_flight .flight-route {
            flex-direction: column;
        }

        .bravo_search_flight .price-section {
            border-left: none;
            border-top: 1px solid #e9ecef;
            padding-left: 0;
            padding-top: 15px;
            margin-top: 15px;
            text-align: left;
        }

        .bravo_search_flight .airport-code {
            font-size: 20px;
        }

        .bravo_search_flight .total-price {
            font-size: 24px;
        }
    }

    /* ===== FLIGHT DETAILS TABLES - COMPACT & MODERN ===== */

    .bravo_search_flight .flight-details {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #e9ecef;
    }

    /* Tabs Styling */
    .bravo_search_flight .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 20px;
    }

    .bravo_search_flight .nav-tabs .nav-link {
        color: #666;
        border: none;
        padding: 8px 16px;
        font-weight: 500;
        font-size: 13px;
        transition: all 0.3s;
    }

    .bravo_search_flight .nav-tabs .nav-link:hover {
        color: #dc3545;
    }

    .bravo_search_flight .nav-tabs .nav-link.active {
        color: #dc3545;
        border-bottom: 3px solid #dc3545;
        background: transparent;
    }

    .bravo_search_flight .nav-tabs .nav-link i {
        font-size: 14px;
        margin-right: 5px;
    }

    /* Tab Content */
    .bravo_search_flight .tab-content {
        padding: 20px 0;
    }

    /* Tables - Compact Design */
    .bravo_search_flight .details-table {
        margin-top: 15px;
    }

    .bravo_search_flight .details-table h6 {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }

    .bravo_search_flight .details-table table {
        font-size: 12px;
        margin-bottom: 20px;
        width: 100%; /* ✅ Full width */
    }

    .bravo_search_flight .details-table thead {
        background: #f8f9fa;
    }

    .bravo_search_flight .details-table th {
        font-weight: 600;
        color: #495057;
        padding: 10px 12px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #dee2e6;
    }

    .bravo_search_flight .details-table td {
        padding: 10px 12px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }

    .bravo_search_flight .details-table td strong {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }

    .bravo_search_flight .details-table td small {
        font-size: 15px;
        display: block;
        margin-top: 2px;
    }

    .bravo_search_flight .details-table .text-muted {
        color: #6c757d;
    }

    .bravo_search_flight .details-table .text-danger {
        color: #dc3545;
        font-weight: 600;
    }

    .bravo_search_flight .details-table .text-success {
        color: #28a745;
    }

    /* Table Row Hover Effect */
    .bravo_search_flight .details-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Special Rows */
    .bravo_search_flight .details-table .table-light {
        background-color: #f8f9fa;
    }

    .bravo_search_flight .details-table .table-secondary {
        background-color: #e9ecef;
    }

    .bravo_search_flight .details-table .table-warning {
        background-color: #fff3cd;
    }

    .bravo_search_flight .details-table .table-success {
        background-color: #d4edda;
    }

    .bravo_search_flight .details-table .table-primary {
        background-color: #cfe2ff;
    }

    /* Layover Alert */
    .bravo_search_flight .details-table .table-warning td {
        padding: 8px 12px;
        font-size: 12px;
    }

    .bravo_search_flight .details-table .table-warning i {
        color: #856404;
        margin-right: 5px;
    }

    /* Badges in Tables */
    .bravo_search_flight .details-table .badge {
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 600;
    }

    .bravo_search_flight .details-table .badge.bg-secondary {
        background-color: #6c757d;
    }

    .bravo_search_flight .details-table .badge.bg-success {
        background-color: #28a745;
    }

    .bravo_search_flight .details-table .badge.bg-danger {
        background-color: #dc3545;
    }

    .bravo_search_flight .details-table .badge.bg-warning {
        background-color: #ffc107;
        color: #000;
    }

    .bravo_search_flight .details-table .badge.bg-info {
        background-color: #17a2b8;
    }

    /* Alert Boxes - Compact */
    .bravo_search_flight .details-table .alert {
        padding: 10px 12px;
        margin-top: 15px;
        margin-bottom: 15px;
        border-radius: 6px;
        font-size: 12px;
    }

    .bravo_search_flight .details-table .alert-heading {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .bravo_search_flight .details-table .alert hr {
        margin: 8px 0;
        opacity: 0.3;
    }

    .bravo_search_flight .details-table .alert i {
        margin-right: 6px;
    }

    .bravo_search_flight .details-table .alert p {
        margin-bottom: 6px;
    }

    .bravo_search_flight .details-table .alert code {
        background: rgba(0,0,0,0.05);
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
    }

    .bravo_search_flight .details-table .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .bravo_search_flight .details-table .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    .bravo_search_flight .details-table .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .bravo_search_flight .details-table .alert-secondary {
        background-color: #e2e3e5;
        border-color: #d6d8db;
        color: #383d41;
    }

    /* Airline Logo in Table */
    .bravo_search_flight .details-table td img {
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    /* Text Alignment */
    .bravo_search_flight .details-table .text-end {
        text-align: right;
    }

    /* Price Calculation Table Specific */
    .bravo_search_flight .details-table table tbody tr:last-child td {
        font-weight: 700;
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .bravo_search_flight .details-table table {
            font-size: 11px;
            margin-bottom: 20px;
            width: 100%; /* ✅ Full width */
            table-layout: auto; /* ✅ Auto adjust columns */
        }

        .bravo_search_flight .details-table th,
        .bravo_search_flight .details-table td {
            padding: 8px;
        }

        .bravo_search_flight .nav-tabs .nav-link {
            padding: 6px 10px;
            font-size: 11px;
        }

        .bravo_search_flight .nav-tabs .nav-link i {
            display: none;
        }
    }

    /* Scrollable Table for Mobile */
    @media (max-width: 576px) {
        .bravo_search_flight .details-table {
            overflow-x: auto;
        }

        .bravo_search_flight .details-table table {
            min-width: 600px;
            width: 100%; /* ✅ Full width */
            table-layout: auto; /* ✅ A  uto adjust columns */
        }
    }

    /* ===== PRICE SECTION - ENHANCED ===== */

    .bravo_search_flight .price-section .price-details small {
        font-size: 12px; /* Bigger font */
        font-weight: 500; /* Semi-bold */
    }

    .bravo_search_flight .price-section .price-details .fw-semibold,
    .bravo_search_flight .price-section .price-details .fw-bold {
        font-size: 13px; /* Even bigger for bold items */
        font-weight: 600;
    }

    /* Icons in price section */
    .bravo_search_flight .price-section .price-details i {
        font-size: 11px;
        margin-right: 3px;
    }

    /* Success text (discounts) */
    .bravo_search_flight .price-section .text-success small {
        font-weight: 600; /* Bold green text */
    }

    /* Border lines */
    .bravo_search_flight .price-section .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }

</style>
{{--@endpush--}}
<body>
{{--<div class="container-fluid py-4">--}}
        <!-- Right Side - Flight Results -->
{{--        <div class="col-lg-9">--}}
            <!-- No Results Message -->
{{--            <div id="noResultsMessage" class="alert alert-warning" style="display: none;">--}}
{{--                <i class="bi bi-funnel"></i> No flights match your current filters. Try adjusting your filter criteria.--}}
{{--            </div>--}}
            <!-- Flight Card 1 -->
            @forelse($flights['flights'] ?? [] as $flight)
                @php
                    $flightFilterData = [
                        'id' => $flight['id'],
                        'validating_carrier' => $flight['validating_carrier'] ?? null,
                        'price' => ['total' => $flight['price']['total']],
                        'legs' => array_map(function($leg) {
                            return [
                                'stops' => $leg['stops'],
                                'departure' => ['time' => $leg['departure']['time']]
                            ];
                        }, $flight['legs'])
                    ];
                @endphp

                <div class="flight-card" data-flight='@json($flightFilterData)'>
                    @foreach($flight['legs'] as $legIndex => $leg)
                        {{-- Flight Header --}}
                        <div class="flight-header">
                    <span class="flight-type-badge
                        @if($leg['leg_type'] === 'outbound')
                            " style="background: #e3f2fd; color: #1976d2;
                        @elseif($leg['leg_type'] === 'return')
                            " style="background: #fff3cd; color: #856404;
                        @else
                            " style="background: #e1f5fe; color: #0288d1;
                        @endif
                        ">
                        @if($leg['leg_type'] === 'outbound')
                            <i class="bi bi-arrow-right"></i> Outbound Flight
                        @elseif($leg['leg_type'] === 'return')
                            <i class="bi bi-arrow-left"></i> Return Flight
                        @else
                            <i class="bi bi-geo-alt"></i> Leg {{ $leg['leg_number'] }}
                        @endif
                    </span>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-9">
                                {{-- Flight Route --}}
                                <div class="flight-route">
                                    {{-- Departure --}}
                                    <div class="airport-info">
                                        <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                                            @if(!empty($leg['segments'][0]['carrier_images']['thumb']))
                                                <img src="{{ $leg['segments'][0]['carrier_images']['thumb'] }}"
                                                     alt="{{ $leg['segments'][0]['carrier_name'] }}"
                                                     class="airline-logo">
                                            @else
                                                <div class="airline-logo bg-secondary text-white d-flex align-items-center justify-content-center">
                                                    {{ substr($leg['segments'][0]['carrier'] ?? '?', 0, 2) }}
                                                </div>
                                            @endif
                                            <div class="text-start">
                                                <div class="airline-name">{{ $leg['segments'][0]['carrier_name'] ?? 'Unknown' }}</div>
                                            </div>
                                        </div>

                                        <div class="airport-code">{{ $leg['departure']['airport_code'] }}</div>
                                        <div class="airport-name">{{ $leg['departure']['airport_name'] ?? $leg['departure']['city'] }}</div>
                                        <div class="flight-time">{{ $leg['departure']['time_12h'] }}</div>
                                        <div class="flight-date">{{ \Carbon\Carbon::parse($leg['departure']['date'])->format('d M Y') }}</div>

                                        @if(!empty($leg['departure']['terminal']))
                                            <div class="flight-date">Terminal {{ $leg['departure']['terminal'] }}</div>
                                        @endif
                                    </div>

                                    {{-- Duration & Stops --}}
                                    <div class="flight-duration">
                                        <div class="duration-text">{{ $leg['duration_formatted'] }}</div>
                                        <div class="duration-line"></div>
                                        <div class="flight-class">
                                            {{ $leg['segments'][0]['fare_info']['cabin_name'] ?? 'Economy' }}
                                        </div>

                                        <div class="badge-group justify-content-center mt-2">
                                            @if(!empty($leg['segments'][0]['meal_description']))
                                                <span class="badge-meal">
                                            <i class="bi bi-egg-fried"></i> {{ $leg['segments'][0]['meal_description'] }}
                                        </span>
                                            @else
                                                <span class="badge-meal">
                                            <i class="bi bi-egg-fried"></i> Meal Included
                                        </span>
                                            @endif

                                            <span class="badge-stop">
                                        @if($leg['is_direct'])
                                                    Non-Stop
                                                @else
                                                    {{ $leg['stops'] }} Stop{{ $leg['stops'] > 1 ? 's' : '' }}
                                                @endif
                                    </span>
                                        </div>
                                    </div>

                                    {{-- Arrival --}}
                                    <div class="airport-info">
                                        <div class="airport-code">{{ $leg['arrival']['airport_code'] }}</div>
                                        <div class="airport-name">{{ $leg['arrival']['airport_name'] ?? $leg['arrival']['city'] }}</div>
                                        <div class="flight-time">{{ $leg['arrival']['time_12h'] }}</div>
                                        <div class="flight-date">
                                            {{ \Carbon\Carbon::parse($leg['arrival']['date'])->format('d M Y') }}
                                            @if($leg['arrival']['date_adjustment'] > 0)
                                                <span class="text-danger ms-1">+{{ $leg['arrival']['date_adjustment'] }}</span>
                                            @endif
                                        </div>

                                        @if(!empty($leg['arrival']['terminal']))
                                            <div class="flight-date">Terminal {{ $leg['arrival']['terminal'] }}</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Layover Alert --}}
                                @if(!$leg['is_direct'] && !empty($leg['stops_detail']))
                                    @foreach($leg['stops_detail'] as $stop)
                                        <div class="layover-alert">
                                            <strong><i class="bi bi-clock-history"></i> Layover in {{ $stop['airport_name'] }} ({{ $stop['airport_code'] }}):</strong>
                                            {{ $stop['layover_formatted'] }} wait time

                                            @if($stop['is_overnight'])
                                                <span class="badge bg-warning ms-2">Overnight</span>
                                            @endif

                                            @if($stop['terminal_change'])
                                                <span class="badge bg-danger ms-2">Terminal Change Required</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            {{-- Price Section (Only show on first leg) --}}
                            @if($legIndex === 0)
                                <div class="col-md-3">
                                    <div class="price-section">
                                        <div class="price-label">Airline Price:</div>

                                        {{-- Original price before any discounts --}}
                                        <div class="base-price">৳{{ number_format($flight['price']['subtotal_before_discount'] ?? 0) }}</div>

                                        <div class="price-details">
                                            {{-- API Original Prices --}}
                                            <div class="d-flex justify-content-between mb-1">
                                                <small>Base Fare</small>
                                                <small>৳{{ number_format($flight['price']['api_base_fare'] ?? 0) }}</small>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <small>+ Tax</small>
                                                <small>৳{{ number_format($flight['price']['api_tax'] ?? 0) }}</small>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1 pb-1 border-bottom">
                                                <small class="fw-semibold">API Subtotal</small>
                                                <small class="fw-semibold">৳{{ number_format($flight['price']['api_subtotal'] ?? 0) }}</small>
                                            </div>

                                            {{-- Additional Charges --}}
                                            @if(!empty($flight['charges_details']))
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>+ AIT ({{ $flight['charges_details']['ait_charge_percentage'] }}%)</small>
                                                    <small>৳{{ number_format($flight['charges_details']['ait_amount']) }}</small>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <small>+ Service Charge</small>
                                                    <small>৳{{ number_format($flight['charges_details']['service_charge']) }}</small>
                                                </div>
                                            @endif

                                            <div class="d-flex justify-content-between fw-bold mb-2 pb-2 border-bottom">
                                                <small>Subtotal</small>
                                                <small>৳{{ number_format($flight['price']['subtotal_before_discount'] ?? 0) }}</small>
                                            </div>

                                            {{-- Discounts --}}
                                            @if(!empty($flight['flight_discount_details']) && $flight['flight_discount_details']['applicable'])
                                                <div class="d-flex justify-content-between mb-1 text-success">
                                                    <small>
                                                        <i class="bi bi-tag"></i> Flight Discount
                                                        @if($flight['flight_discount_details']['discount_type'] === 'percentage')
                                                            ({{ $flight['flight_discount_details']['discount_value'] }}%)
                                                        @endif
                                                    </small>
                                                    <small>-৳{{ number_format($flight['flight_discount_details']['discount_amount']) }}</small>
                                                </div>
                                            @endif

                                            @if(!empty($flight['charges_details']) && $flight['charges_details']['segment_discount_total'] > 0)
                                                <div class="d-flex justify-content-between mb-1 text-success">
                                                    <small>
                                                        <i class="bi bi-ticket-perforated"></i> Segment Discount
                                                        ({{ $flight['charges_details']['total_segments'] }} × ৳{{ number_format($flight['charges_details']['segment_discount_per_segment']) }})
                                                    </small>
                                                    <small>-৳{{ number_format($flight['charges_details']['segment_discount_total']) }}</small>
                                                </div>
                                            @endif

                                            @if(!empty($flight['price']['total_discounts']) && $flight['price']['total_discounts'] > 0)
                                                <div class="d-flex justify-content-between fw-bold text-success mb-2 pb-2 border-bottom">
                                                    <small>Total Savings</small>
                                                    <small>-৳{{ number_format($flight['price']['total_discounts']) }}</small>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Discount badges --}}
                                        @if(!empty($flight['flight_discount_details']) && $flight['flight_discount_details']['applicable'])
                                            <div class="discount-badge mb-2">
                                                <i class="bi bi-check-circle"></i>
                                                {{ $flight['flight_discount_details']['discount_name'] ?? 'Discount' }} Applied!
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center fw-bold text-success mb-2 pb-2 border-bottom">
                                            <div class="price-label">You Pay:</div>
                                            <div class="total-price">৳{{ number_format($flight['price']['total']) }}</div>
                                        </div>

                                        {{-- Savings highlight --}}
                                        @if(!empty($flight['price']['total_discounts']) && $flight['price']['total_discounts'] > 0)
                                            <div class="text-success small mt-1 mb-2">
                                                <i class="bi bi-piggy-bank"></i> You save ৳{{ number_format($flight['price']['total_discounts']) }}
                                            </div>
                                        @endif

                                        <button class="btn btn-select" onclick="selectFlight('{{ $flight['id'] }}')">
                                            Choose This Flight <i class="bi bi-arrow-right"></i>
                                        </button>

                                        <button class="show-details-btn" onclick="toggleDetails('{{ $flight['id'] }}')">
                                            Show Details <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Refundable Badge --}}
                        @if($legIndex === 0)
                            <div class="text-center mt-2">
                                @if($flight['refundable'])
                                    <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Refundable
                            </span>
                                @else
                                    <span class="badge-refund">
                                <i class="bi bi-x-circle"></i> Non-Refundable
                            </span>
                                @endif

                                @if($flight['eTicketable'])
                                    <span class="badge bg-info ms-2">
                                <i class="bi bi-ticket-perforated"></i> E-Ticketable
                            </span>
                                @endif
                            </div>
                        @endif

                        {{-- Separator between legs --}}
                        @if(!$loop->last)
                            <div class="mt-4 pt-4" style="border-top: 2px solid #f0f0f0;"></div>
                        @endif
                    @endforeach

                    {{-- Expandable Details Section --}}
                    <div class="flight-details" id="details{{ $flight['id'] }}" style="display: none;">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#flight-info-{{ $flight['id'] }}">
                                    <i class="bi bi-airplane"></i> Flight Details
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fare-info-{{ $flight['id'] }}">
                                    <i class="bi bi-currency-dollar"></i> Fare Breakdown
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#baggage-info-{{ $flight['id'] }}">
                                    <i class="bi bi-bag"></i> Baggage Policy
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#passenger-info-{{ $flight['id'] }}">
                                    <i class="bi bi-people"></i> Passenger Details
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- Flight Details Tab --}}
                            <div class="tab-pane fade show active" id="flight-info-{{ $flight['id'] }}">
                                <div class="details-table">
                                    @foreach($flight['legs'] as $leg)
                                        <h6 class="fw-bold mb-3 mt-3">
                                            @if($leg['leg_type'] === 'outbound')
                                                Outbound Journey:
                                            @elseif($leg['leg_type'] === 'return')
                                                Return Journey:
                                            @else
                                                Leg {{ $leg['leg_number'] }}:
                                            @endif
                                            {{ $leg['departure']['airport_code'] }} → {{ $leg['arrival']['airport_code'] }}
                                        </h6>

                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Segment</th>
                                                <th>Flight</th>
                                                <th>Departure</th>
                                                <th>Arrival</th>
                                                <th>Duration</th>
                                                <th>Aircraft</th>
                                                <th>Class</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($leg['segments'] as $segment)
                                                <tr>
                                                    <td>{{ $segment['segment_number'] }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if(!empty($segment['carrier_images']['thumb']))
                                                                <img src="{{ $segment['carrier_images']['thumb'] }}" alt="{{ $segment['carrier_name'] }}" style="width: 30px;">
                                                            @endif
                                                            <div>
                                                                <strong>{{ $segment['full_flight_number'] }}</strong><br>
                                                                <small class="text-muted">{{ $segment['carrier_name'] }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $segment['departure']['airport_code'] }}</strong><br>
                                                        <small>{{ $segment['departure']['time_12h'] }}</small><br>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($segment['departure']['date'])->format('d M Y') }}</small>
                                                        @if(!empty($segment['departure']['terminal']))
                                                            <br><small class="text-muted">Terminal {{ $segment['departure']['terminal'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <strong>{{ $segment['arrival']['airport_code'] }}</strong><br>
                                                        <small>{{ $segment['arrival']['time_12h'] }}</small><br>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($segment['arrival']['date'])->format('d M Y') }}</small>
                                                        @if($segment['arrival']['date_adjustment'] > 0)
                                                            <span class="text-danger">+{{ $segment['arrival']['date_adjustment'] }}</span>
                                                        @endif
                                                        @if(!empty($segment['arrival']['terminal']))
                                                            <br><small class="text-muted">Terminal {{ $segment['arrival']['terminal'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $segment['duration_formatted'] }}</td>
                                                    <td>{{ $segment['aircraft_name'] ?? $segment['aircraft'] }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $segment['fare_info']['cabin_name'] ?? 'Economy' }}</span><br>
                                                        @if(!empty($segment['fare_info']['booking_code']))
                                                            <small>Booking: {{ $segment['fare_info']['booking_code'] }}</small>
                                                        @endif
                                                    </td>
                                                </tr>

                                                {{-- Layover row --}}
                                                @if(!empty($segment['layover_after']))
                                                    <tr class="table-warning">
                                                        <td colspan="7">
                                                            <i class="bi bi-clock-history"></i>
                                                            <strong>Layover at {{ $segment['layover_after']['airport_code'] }}</strong>:
                                                            {{ $segment['layover_after']['formatted'] }}
                                                            @if($segment['layover_after']['is_overnight'])
                                                                <span class="badge bg-warning ms-2">Overnight</span>
                                                            @endif
                                                            @if($segment['layover_after']['terminal_change'])
                                                                <span class="badge bg-danger ms-2">Terminal Change</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Fare Breakdown Tab --}}
                            <div class="tab-pane fade" id="fare-info-{{ $flight['id'] }}">
                                <div class="details-table">
                                    <h6 class="fw-bold mb-3">Fare Breakdown by Passenger</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Passenger Type</th>
                                            <th>Count</th>
                                            <th>Base Fare</th>
                                            <th>Tax</th>
                                            <th>Total per Passenger</th>
                                            <th>Subtotal</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php $grandTotal = 0; @endphp
                                        @foreach($flight['passengers'] as $passenger)
                                            @php
                                                $passengerSubtotal = $passenger['total_fare'] * $passenger['count'];
                                                $grandTotal += $passengerSubtotal;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $passenger['type_label'] }}</strong></td>
                                                <td>{{ $passenger['count'] }}</td>
                                                <td>{{ $passenger['base_fare_currency'] }} {{ number_format($passenger['base_fare']) }}</td>
                                                <td>৳{{ number_format($passenger['tax_amount']) }}</td>
                                                <td>৳{{ number_format($passenger['total_fare']) }}</td>
                                                <td>৳{{ number_format($passengerSubtotal) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light">
                                            <td colspan="5"><strong>API Subtotal (Base + Tax)</strong></td>
                                            <td><strong>৳{{ number_format($flight['price']['api_subtotal'] ?? $grandTotal) }}</strong></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    {{-- Complete Price Calculation --}}
                                    <h6 class="fw-bold mb-3 mt-4">Complete Price Calculation</h6>
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr class="table-secondary">
                                            <td colspan="2"><strong>Starting Price (from API)</strong></td>
                                            <td class="text-end"><strong>৳{{ number_format($flight['price']['api_subtotal']) }}</strong></td>
                                        </tr>

                                        {{-- Additional Charges --}}
                                        @if(!empty($flight['charges_details']))
                                            <tr class="table-warning">
                                                <td colspan="3"><strong>Additional Charges</strong></td>
                                            </tr>
                                            <tr>
                                                <td width="50%">AIT (Advance Income Tax)</td>
                                                <td width="30%">
                                                    {{ $flight['charges_details']['ait_charge_percentage'] }}% on ৳{{ number_format($flight['price']['api_subtotal']) }}
                                                </td>
                                                <td class="text-end">+ ৳{{ number_format($flight['charges_details']['ait_amount']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Service Charge</td>
                                                <td>Fixed amount</td>
                                                <td class="text-end">+ ৳{{ number_format($flight['charges_details']['service_charge']) }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="2"><strong>Subtotal (before discounts)</strong></td>
                                                <td class="text-end"><strong>৳{{ number_format($flight['price']['subtotal_before_discount']) }}</strong></td>
                                            </tr>
                                        @endif

                                        {{-- Discounts --}}
                                        @if(!empty($flight['flight_discount_details']) || !empty($flight['charges_details']['segment_discount_total']))
                                            <tr class="table-success">
                                                <td colspan="3"><strong>Discounts Applied</strong></td>
                                            </tr>

                                            @if(!empty($flight['flight_discount_details']) && $flight['flight_discount_details']['applicable'])
                                                <tr class="text-success">
                                                    <td>
                                                        <i class="bi bi-tag-fill"></i> Route Discount
                                                        <br><small class="text-muted">{{ $flight['flight_discount_details']['discount_name'] }}</small>
                                                    </td>
                                                    <td>
                                                        @if($flight['flight_discount_details']['discount_type'] === 'percentage')
                                                            {{ $flight['flight_discount_details']['discount_value'] }}% on Base Fare (৳{{ number_format($flight['price']['api_base_fare']) }})
                                                        @else
                                                            Fixed discount
                                                        @endif
                                                    </td>
                                                    <td class="text-end">- ৳{{ number_format($flight['flight_discount_details']['discount_amount']) }}</td>
                                                </tr>
                                            @endif

                                            @if(!empty($flight['charges_details']) && $flight['charges_details']['segment_discount_total'] > 0)
                                                <tr class="text-success">
                                                    <td>
                                                        <i class="bi bi-ticket-perforated-fill"></i> Segment Discount
                                                        <br><small class="text-muted">Multi-segment journey bonus</small>
                                                    </td>
                                                    <td>
                                                        {{ $flight['charges_details']['total_segments'] }} segments × ৳{{ number_format($flight['charges_details']['segment_discount_per_segment']) }}
                                                    </td>
                                                    <td class="text-end">- ৳{{ number_format($flight['charges_details']['segment_discount_total']) }}</td>
                                                </tr>
                                            @endif

                                            @if(!empty($flight['price']['total_discounts']))
                                                <tr class="table-success">
                                                    <td colspan="2"><strong>Total Savings</strong></td>
                                                    <td class="text-end"><strong>- ৳{{ number_format($flight['price']['total_discounts']) }}</strong></td>
                                                </tr>
                                            @endif
                                        @endif

                                        {{-- Final Price --}}
                                        <tr class="table-primary">
                                            <td colspan="2"><strong>Final Price (You Pay)</strong></td>
                                            <td class="text-end"><strong>৳{{ number_format($flight['price']['total']) }}</strong></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    {{-- Calculation Summary --}}
                                    @if(!empty($flight['charges_details']))
                                        <div class="alert alert-info mt-3">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-calculator"></i> Calculation Summary
                                            </h6>
                                            <hr>
                                            <p class="mb-1">
                                                <strong>Flight Type:</strong>
                                                <span class="badge bg-info">{{ ucfirst($flight['charges_details']['type']) }}</span>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Total Journey Segments:</strong> {{ $flight['charges_details']['total_segments'] }}
                                            </p>
                                            <p class="mb-0">
                                                <strong>Formula:</strong>
                                                <code>
                                                    Final = (API Subtotal + AIT + Service) - (Flight Discount + Segment Discount)
                                                </code>
                                            </p>
                                        </div>
                                    @endif

                                    @if(!empty($flight['passengers'][0]['exchange_rate']))
                                        <div class="alert alert-secondary mt-3">
                                            <i class="bi bi-currency-exchange"></i>
                                            <strong>Exchange Rate:</strong> 1 {{ $flight['passengers'][0]['exchange_from'] }} = {{ $flight['passengers'][0]['exchange_rate'] }} {{ $flight['passengers'][0]['exchange_to'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Baggage Policy Tab --}}
                            <div class="tab-pane fade" id="baggage-info-{{ $flight['id'] }}">
                                <div class="details-table">
                                    <h6 class="fw-bold mb-3">Baggage Allowance</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Passenger Type</th>
                                            <th>Count</th>
                                            <th>Check-in Baggage</th>
                                            <th>Airline</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($flight['passengers'] as $passenger)
                                            <tr>
                                                <td><strong>{{ $passenger['type_label'] }}</strong></td>
                                                <td>{{ $passenger['count'] }}</td>
                                                <td>
                                                    @if(!empty($passenger['baggage']))
                                                        @if($passenger['baggage']['weight'])
                                                            {{ $passenger['baggage']['weight'] }} {{ $passenger['baggage']['unit'] }}
                                                        @endif
                                                        @if($passenger['baggage']['piece_count'])
                                                            ({{ $passenger['baggage']['piece_count'] }} piece{{ $passenger['baggage']['piece_count'] > 1 ? 's' : '' }})
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Check with airline</span>
                                                    @endif
                                                </td>
                                                <td>{{ $passenger['baggage']['airline'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    @if(!empty($flight['passengers'][0]['baggage']['note']))
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Note:</strong> {{ $flight['passengers'][0]['baggage']['note'] }}
                                        </div>
                                    @endif

                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Cabin Baggage:</strong> Usually 7 kg (1 piece) per passenger. Please check with airline for specific allowances.
                                    </div>
                                </div>
                            </div>

                            {{-- Passenger Details Tab --}}
                            <div class="tab-pane fade" id="passenger-info-{{ $flight['id'] }}">
                                <div class="details-table">
                                    <h6 class="fw-bold mb-3">Passenger Summary</h6>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Count</th>
                                            <th>Refundable</th>
                                            <th>Fare</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($flight['passengers'] as $passenger)
                                            <tr>
                                                <td><strong>{{ $passenger['type_label'] }}</strong></td>
                                                <td>{{ $passenger['count'] }}</td>
                                                <td>
                                                    @if($passenger['refundable'])
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                </td>
                                                <td>৳{{ number_format($passenger['total_fare']) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Important:</strong> Ticket changes and cancellations are subject to airline fees and fare difference. Terms and conditions apply.
                                    </div>

                                    @if(!empty($flight['last_ticket_date']))
                                        <div class="alert alert-danger mt-3">
                                            <i class="bi bi-clock"></i>
                                            <strong>Last Ticketing Date:</strong> {{ \Carbon\Carbon::parse($flight['last_ticket_date'])->format('d M Y') }}
                                            @if(!empty($flight['last_ticket_time']))
                                                at {{ $flight['last_ticket_time'] }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No flights found for your search criteria.
                </div>
            @endforelse

            <!-- No Results Message (for filtering) -->
            <div id="noResultsMessage" class="alert alert-warning" style="display: none;">
                <i class="bi bi-funnel"></i> No flights match your current filters. Try adjusting your filter criteria.
            </div>

{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle flight details
    function toggleDetails(flightId) {
        const detailsDiv = document.getElementById('details' + flightId);
        const button = event.target;

        if (detailsDiv.style.display === 'none') {
            detailsDiv.style.display = 'block';
            button.innerHTML = 'Hide Details <i class="bi bi-chevron-up"></i>';
        } else {
            detailsDiv.style.display = 'none';
            button.innerHTML = 'Show Details <i class="bi bi-chevron-down"></i>';
        }
    }

    function selectFlight(flightId) {
        console.log('Selected flight:', flightId);
        alert('Flight ' + flightId + ' selected! Proceeding to booking...');
    }
</script>
</body>
{{--</html>--}}
