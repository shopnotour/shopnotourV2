@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Header with PNR Search --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="title-bar mb-0">
                            <i class="fa fa-ticket"></i> {{ __('Booking Details') }}
                        </h1>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('booking.pnr.search') }}" class="d-flex">
                            <input type="text"
                                   name="pnr"
                                   class="form-control form-control-lg"
                                   placeholder="{{ __('Enter PNR Code (e.g., TQKSYF)') }}"
                                   value="{{ request('pnr') }}"
                                   required>
                            <button type="submit" class="btn btn-primary btn-lg ml-2">
                                <i class="fa fa-search"></i> {{ __('Search') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.message')

        @if($booking)
            {{-- Booking Overview --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">
                                    <i class="fa fa-plane"></i>
                                    {{ __('Booking #:id - PNR: :pnr', ['id' => $booking->id, 'pnr' => $booking->pnr_id]) }}
                                </h4>
                                <span class="badge badge-light badge-lg">
                                    {{ strtoupper($booking->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-calendar text-primary"></i>
                                        <p class="mb-0"><strong>{{ __('Booking Date') }}</strong></p>
                                        <p class="text-muted mb-0">{{ $booking->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-map-marker text-success"></i>
                                        <p class="mb-0"><strong>{{ __('Route') }}</strong></p>
                                        <p class="text-muted mb-0">{{ $booking->flight_from }} → {{ $booking->flight_to }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-exchange text-info"></i>
                                        <p class="mb-0"><strong>{{ __('Trip Type') }}</strong></p>
                                        <p class="text-muted mb-0">{{ ucfirst($booking->flight_type) }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-users text-warning"></i>
                                        <p class="mb-0"><strong>{{ __('Passengers') }}</strong></p>
                                        <p class="text-muted mb-0">
                                            {{ $booking->adult_count }}A
                                            @if($booking->child_count > 0) + {{ $booking->child_count }}C @endif
                                            @if($booking->infant_count > 0) + {{ $booking->infant_count }}I @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-desktop text-secondary"></i>
                                        <p class="mb-0"><strong>{{ __('Source') }}</strong></p>
                                        <p class="text-muted mb-0">{{ $booking->source ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="info-box">
                                        <i class="fa fa-globe text-danger"></i>
                                        <p class="mb-0"><strong>{{ __('Country') }}</strong></p>
                                        <p class="text-muted mb-0">{{ $booking->country ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fa fa-calendar-check-o text-success"></i>
                                        <strong>{{ __('Departure') }}:</strong>
                                        {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M Y') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fa fa-calendar-times-o text-danger"></i>
                                        <strong>{{ __('Return') }}:</strong>
                                        {{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d M Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Passengers Information --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-info text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="fa fa-users"></i> {{ __('Passenger Information') }}
                                        <span class="badge badge-light ml-2">{{ $passengers->count() }} {{ __('Travelers') }}</span>
                                    </h5>
                                    <h6 class="mb-0">
                                        <i class="fa fa-ticket"></i> {{ __('PNR: ') }} <strong>{{ $booking->pnr }}</strong>
                                    </h6>
                                </div>
                                <div>
                                    <span class="mr-2 text-white">{{ __('Full PNR Actions:') }}</span>
{{--                                    <a href="{{ route('booking.void.all', $booking->id) }}"--}}
                                    <a href="#"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Are you sure you want to VOID all passengers in this PNR?')">
                                        <i class="fa fa-ban"></i> {{ __('Void All') }}
                                    </a>
{{--                                    <a href="{{ route('booking.refund.all', $booking->id) }}"--}}
                                    <a href="#"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to REFUND all passengers in this PNR?')">
                                        <i class="fa fa-undo"></i> {{ __('Refund All') }}
                                    </a>
                                    <a href="#"
{{--                                    <a href="{{ route('booking.reissue.all', $booking->id) }}"--}}
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-exchange"></i> {{ __('Reissue All') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>{{ __('Passenger Name') }}</th>
                                        <th width="100">{{ __('Type') }}</th>
                                        <th width="120">{{ __('DOB') }}</th>
                                        <th width="80">{{ __('Gender') }}</th>
                                        <th>{{ __('Passport Details') }}</th>
                                        <th>{{ __('Contact') }}</th>
                                        <th width="100">{{ __('Price') }}</th>
                                        <th width="150">{{ __('Action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($passengers as $index => $passenger)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-primary badge-pill">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-dark">
                                                    {{ $passenger->first_name }} {{ $passenger->last_name }}
                                                </strong>
                                                @if($passenger->current_status)
                                                    <br>
                                                    <span class="badge badge-sm
                                            @if($passenger->current_status == 'voided') badge-warning
                                            @elseif($passenger->current_status == 'refunded') badge-danger
                                            @elseif($passenger->current_status == 'reissued') badge-info
                                            @else badge-success
                                            @endif">
                                            {{ ucfirst($passenger->current_status) }}
                                        </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'ADT' => 'success',
                                                        'ADULT' => 'success',
                                                        'CNN' => 'warning',
                                                        'CHILD' => 'warning',
                                                        'INF' => 'info',
                                                        'INFANT' => 'info'
                                                    ];
                                                    $color = $typeColors[$passenger->passenger_type_code] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $color }}">
                                        {{ $passenger->traveler_type }}
                                    </span>
                                            </td>
                                            <td>{{ $passenger->dob ? \Carbon\Carbon::parse($passenger->dob)->format('d M Y') : 'N/A' }}</td>
                                            <td class="text-center">
                                                @if($passenger->gender === 'M')
                                                    <i class="fa fa-male text-primary fa-lg"></i>
                                                @elseif($passenger->gender === 'F')
                                                    <i class="fa fa-female text-danger fa-lg"></i>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>{{ __('No') }}:</strong> {{ $passenger->passport_number ?? 'N/A' }}<br>
                                                    <strong>{{ __('Expiry') }}:</strong>
                                                    {{ $passenger->passport_expiry_date ? \Carbon\Carbon::parse($passenger->passport_expiry_date)->format('d M Y') : 'N/A' }}<br>
                                                    <strong>{{ __('Country') }}:</strong> {{ $passenger->country ?? 'N/A' }}
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    @if($passenger->email)
                                                        <i class="fa fa-envelope"></i> {{ $passenger->email }}<br>
                                                    @endif
                                                    @if($passenger->phone)
                                                        <i class="fa fa-phone"></i> {{ $passenger->phone }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">
                                                    {{ number_format($passenger->total, 2) }} {{ $booking->currency }}
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    Base: {{ number_format($passenger->base, 2) }}
                                                </small>
                                            </td>
                                            <td>
                                                @if(!$passenger->status || $passenger->status == 'active')
                                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-warning btn-sm mb-1"
                                                                onclick="openTransactionModal('void', {{ $passenger->id }}, '{{ $passenger->first_name }} {{ $passenger->last_name }}', {{ $passenger->total }})">
                                                            <i class="fa fa-ban"></i> Void
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm mb-1"
                                                                onclick="openTransactionModal('refund', {{ $passenger->id }}, '{{ $passenger->first_name }} {{ $passenger->last_name }}', {{ $passenger->total }})">
                                                            <i class="fa fa-undo"></i> Refund
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                                onclick="openTransactionModal('reissue', {{ $passenger->id }}, '{{ $passenger->first_name }} {{ $passenger->last_name }}', {{ $passenger->total }})">
                                                            <i class="fa fa-exchange"></i> Reissue
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($passenger->status) }}</span>
                                                @endif
                                            </td>
{{--                                            <td>--}}
{{--                                                @if(!$passenger->current_status || $passenger->current_status == 'active')--}}
{{--                                                    <div class="btn-group-vertical btn-group-sm" role="group">--}}
{{--                                                        <a href="{{ route('passenger.void', $passenger->id) }}"--}}
{{--                                                        <a href="#"--}}
{{--                                                           class="btn btn-warning btn-sm mb-1"--}}
{{--                                                           onclick="return confirm('Void this passenger?')">--}}
{{--                                                            <i class="fa fa-ban"></i> Void--}}
{{--                                                        </a>--}}
{{--                                                        <a href="{{ route('passenger.refund', $passenger->id) }}"--}}
{{--                                                        <a href="#"--}}
{{--                                                           class="btn btn-danger btn-sm mb-1"--}}
{{--                                                           onclick="return confirm('Refund this passenger?')">--}}
{{--                                                            <i class="fa fa-undo"></i> Refund--}}
{{--                                                        </a>--}}
{{--                                                        <a href="{{ route('passenger.reissue', $passenger->id) }}"--}}
{{--                                                        <a href="#"--}}
{{--                                                           class="btn btn-primary btn-sm">--}}
{{--                                                            <i class="fa fa-exchange"></i> Reissue--}}
{{--                                                        </a>--}}
{{--                                                    </div>--}}
{{--                                                @else--}}
{{--                                                    <span class="badge badge-secondary">{{ ucfirst($passenger->current_status) }}</span>--}}
{{--                                                @endif--}}
{{--                                            </td>--}}
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flight Routes --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-plane"></i> {{ __('Flight Itinerary') }}
                                <span class="badge badge-light ml-2">{{ $routes->count() }} {{ __('Segments') }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($routes as $index => $route)
                                <div class="flight-segment {{ $index > 0 ? 'mt-4 pt-4 border-top' : '' }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-1 text-center">
                                            <div class="segment-number">
                                                <span class="badge badge-primary badge-lg">{{ $index + 1 }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="airport-info">
                                                <h4 class="mb-0 text-primary">{{ $route->departure_iata_code }}</h4>
                                                <p class="mb-0 text-muted">
                                                    <i class="fa fa-calendar"></i>
                                                    {{ $route->departure_at ? \Carbon\Carbon::parse($route->departure_at)->format('d M Y') : 'N/A' }}
                                                </p>
                                                <p class="mb-0 text-muted">
                                                    <i class="fa fa-clock-o"></i>
                                                    {{ $route->departure_at ? \Carbon\Carbon::parse($route->departure_at)->format('h:i A') : 'N/A' }}
                                                </p>
                                                @if($route->departure_terminal)
                                                    <small class="text-muted">Terminal: {{ $route->departure_terminal }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-2 text-center">
                                            <div class="flight-info">
                                                <i class="fa fa-plane fa-3x text-primary mb-2"></i>
                                                <p class="mb-0">
                                                    <strong>{{ $route->carrier_code }}{{ $route->flight_number }}</strong>
                                                </p>
                                                <p class="mb-0 small text-muted">
                                                    <i class="fa fa-clock-o"></i> {{ $route->duration }} min
                                                </p>
                                                <p class="mb-0 small">
                                                    <span class="badge badge-secondary">{{ $route->class }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="airport-info">
                                                <h4 class="mb-0 text-success">{{ $route->arrival_iata_code }}</h4>
                                                <p class="mb-0 text-muted">
                                                    <i class="fa fa-calendar"></i>
                                                    {{ $route->arrival_at ? \Carbon\Carbon::parse($route->arrival_at)->format('d M Y') : 'N/A' }}
                                                </p>
                                                <p class="mb-0 text-muted">
                                                    <i class="fa fa-clock-o"></i>
                                                    {{ $route->arrival_at ? \Carbon\Carbon::parse($route->arrival_at)->format('h:i A') : 'N/A' }}
                                                </p>
                                                @if($route->arrival_terminal)
                                                    <small class="text-muted">Terminal: {{ $route->arrival_terminal }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="flight-details">
                                                <p class="mb-1">
                                                    <i class="fa fa-plane text-secondary"></i>
                                                    <strong>{{ __('Aircraft') }}:</strong> {{ $route->aircraft_code }}
                                                </p>
                                                <p class="mb-1">
                                                    <i class="fa fa-ticket text-info"></i>
                                                    <strong>{{ __('Class') }}:</strong>
                                                    <span class="badge badge-info">{{ $route->class }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fare & Payment Summary --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-money"></i> {{ __('Payment Summary') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                <tr>
                                    <td><i class="fa fa-ticket text-muted"></i> {{ __('Base Fare') }}</td>
                                    <td class="text-right">
                                        <strong>{{ number_format($booking->base_fee, 2) }} {{ $booking->currency }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="fa fa-calculator text-muted"></i> {{ __('Taxes & Fees') }}</td>
                                    <td class="text-right">
                                        <strong>{{ number_format($booking->total - $booking->base_fee, 2) }} {{ $booking->currency }}</strong>
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong><i class="fa fa-money text-success"></i> {{ __('Total Amount') }}</strong></td>
                                    <td class="text-right">
                                        <h4 class="mb-0 text-success">
                                            {{ number_format($booking->total, 2) }} {{ $booking->currency }}
                                        </h4>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <hr>

                            <div class="row text-center">
                                <div class="col-4">
                                    <p class="mb-0 text-muted small">{{ __('Adults') }}</p>
                                    <h5 class="mb-0">{{ $booking->adult_count }}</h5>
                                </div>
                                <div class="col-4">
                                    <p class="mb-0 text-muted small">{{ __('Children') }}</p>
                                    <h5 class="mb-0">{{ $booking->child_count }}</h5>
                                </div>
                                <div class="col-4">
                                    <p class="mb-0 text-muted small">{{ __('Infants') }}</p>
                                    <h5 class="mb-0">{{ $booking->infant_count }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="fa fa-info-circle"></i> {{ __('Booking Information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <i class="fa fa-hashtag text-primary"></i>
                                    <div>
                                        <p class="mb-0"><strong>{{ __('PNR Code') }}</strong></p>
                                        <p class="mb-0 text-muted">{{ $booking->pnr_id }}</p>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa fa-calendar text-success"></i>
                                    <div>
                                        <p class="mb-0"><strong>{{ __('Travel Dates') }}</strong></p>
                                        <p class="mb-0 text-muted">
                                            {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M Y') : 'N/A' }}
                                            -
                                            {{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d M Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa fa-exchange text-info"></i>
                                    <div>
                                        <p class="mb-0"><strong>{{ __('Trip Type') }}</strong></p>
                                        <p class="mb-0 text-muted">{{ ucfirst($booking->flight_type) }}</p>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa fa-desktop text-secondary"></i>
                                    <div>
                                        <p class="mb-0"><strong>{{ __('Booking Source') }}</strong></p>
                                        <p class="mb-0 text-muted">{{ $booking->source ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fa fa-flag text-danger"></i>
                                    <div>
                                        <p class="mb-0"><strong>{{ __('Country') }}</strong></p>
                                        <p class="mb-0 text-muted">{{ $booking->country ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body text-center py-4">
                            <a href="{{ route('report.admin.booking') }}" class="btn btn-secondary btn-lg mr-2">
                                <i class="fa fa-arrow-left"></i> {{ __('Back to List') }}
                            </a>
                            <button class="btn btn-primary btn-lg mr-2">
                                <i class="fa fa-print"></i> {{ __('Print Booking') }}
                            </button>
                            <button class="btn btn-info btn-lg mr-2">
                                <i class="fa fa-download"></i> {{ __('Download PDF') }}
                            </button>
                            <button class="btn btn-success btn-lg mr-2">
                                <i class="fa fa-envelope"></i> {{ __('Email to Customer') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="empty-state">
                        <i class="fa fa-search fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted">{{ __('No Booking Found') }}</h3>
                        <p class="text-muted lead">{{ __('Enter a PNR code in the search box above to view booking details') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Single Universal Transaction Modal --}}
    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="transactionForm" method="POST" action="{{ route('passenger.transaction.store') }}">
                    @csrf
                    <input type="hidden" name="passenger_id" id="trans_passenger_id">
                    <input type="hidden" name="booking_id" value="{{ $booking->id ?? '' }}">
                    <input type="hidden" name="transaction_type" id="trans_type">

                    <div class="modal-header" id="modalHeader">
                        <h5 class="modal-title" id="modalTitle">
                            <i class="fa fa-ban" id="modalIcon"></i> <span id="modalTitleText">Transaction</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Passenger:</strong> <span id="trans_passenger_name"></span><br>
                            <strong>Original Amount:</strong> <span id="trans_original_amount"></span> {{ $booking->currency ?? 'BDT' }}
                        </div>

                        <div class="form-group">
                            <label>Penalty Amount <span class="text-danger">*</span></label>
                            <input type="number" name="penalty_amount" id="trans_penalty_amount"
                                   class="form-control" step="0.01" min="0" value="0" required>
                            <small class="form-text text-muted">Enter 0 for full refund (no penalty)</small>
                        </div>

                        {{-- Reissue specific fields (hidden by default) --}}
                        <div id="reissueFields" style="display: none;">
                            <div class="form-group">
                                <label>New Ticket Number <span class="text-danger">*</span></label>
                                <input type="text" name="new_ticket_number" id="new_ticket_number"
                                       class="form-control" placeholder="Enter new ticket number">
                            </div>

                            <div class="form-group">
                                <label>New Flight Date <span class="text-danger">*</span></label>
                                <input type="date" name="new_flight_date" id="new_flight_date"
                                       class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Fare Difference <span class="text-danger">*</span></label>
                                <input type="number" name="fare_difference" id="fare_difference"
                                       class="form-control" step="0.01" value="0">
                                <small class="form-text text-muted">Positive if fare increased, negative if decreased</small>
                            </div>
                        </div>

                        <div class="alert" id="calculationAlert">
                            <strong>Calculation:</strong><br>
                            Original: <span id="calc_original">0.00</span><br>
                            Penalty: <span id="calc_penalty">0.00</span><br>
                            <span id="fareRow" style="display: none;">Fare Difference: <span id="calc_fare">0.00</span><br></span>
                            <hr>
                            <strong><span id="resultLabel">Refund Amount</span>: <span id="calc_result">0.00</span></strong>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fa fa-check"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Transaction Modal (for Void/Refund All) --}}
    <div class="modal fade" id="bulkTransactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('booking.transaction.bulk') }}">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id ?? '' }}">
                    <input type="hidden" name="transaction_type" id="bulk_trans_type">

                    <div class="modal-header" id="bulkModalHeader">
                        <h5 class="modal-title" id="bulkModalTitle">
                            <i class="fa fa-ban" id="bulkModalIcon"></i> <span id="bulkModalTitleText">Bulk Transaction</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning!</strong> You are about to process all active passengers in this PNR.
                        </div>

                        <div class="alert alert-info">
                            <strong>Total Passengers:</strong> <span id="bulk_passenger_count">{{ $passengers->where('status', 'active')->count() ?? 0 }}</span><br>
                            <strong>Total Amount:</strong> <span id="bulk_total_amount">{{ number_format($passengers->where('status', 'active')->sum('total') ?? 0, 2) }}</span> {{ $booking->currency ?? 'BDT' }}
                        </div>

                        <div class="form-group">
                            <label>Total Penalty Amount <span class="text-danger">*</span></label>
                            <input type="number" name="penalty_amount" id="bulk_penalty_amount"
                                   class="form-control" step="0.01" min="0" value="0" required>
                            <small class="form-text text-muted">This will be distributed among all passengers</small>
                        </div>

                        <div class="alert alert-success">
                            <strong>Calculation:</strong><br>
                            Total Original: <span id="bulk_calc_original">{{ number_format($passengers->where('status', 'active')->sum('total') ?? 0, 2) }}</span><br>
                            Total Penalty: <span id="bulk_calc_penalty">0.00</span><br>
                            <hr>
                            <strong>Total Refund: <span id="bulk_calc_refund">{{ number_format($passengers->where('status', 'active')->sum('total') ?? 0, 2) }}</span></strong>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="bulkSubmitBtn">
                            <i class="fa fa-check"></i> Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .info-box {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            height: 100%;
        }

        .info-box i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .badge-lg {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .flight-segment {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .flight-segment:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .airport-info h4 {
            font-weight: bold;
            font-size: 1.8rem;
        }

        .flight-info {
            padding: 15px;
        }

        .segment-number .badge {
            width: 40px;
            height: 40px;
            line-height: 30px;
            border-radius: 50%;
            font-size: 1.2rem;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-item i {
            font-size: 1.5rem;
            width: 30px;
            text-align: center;
        }

        .empty-state {
            padding: 50px 20px;
        }

        .shadow {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
@endpush

@push('js')
{{--    <script>--}}
{{--        // Print functionality--}}
{{--        document.querySelector('.btn-primary').addEventListener('click', function() {--}}
{{--            window.print();--}}
{{--        });--}}
{{--    </script>--}}
@endpush
