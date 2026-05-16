<div class="booking-detail-container">
    {{-- Booking Overview --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-ticket"></i> {{ __('Booking Information') }}
                        </h5>
                        <span class="badge badge-light badge-lg">{{ strtoupper($booking->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-2"><i class="fa fa-hashtag text-primary"></i> <strong>{{ __('Booking ID') }}:</strong></p>
                            <h5 class="text-primary">#{{ $booking->id }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><i class="fa fa-barcode text-success"></i> <strong>{{ __('PNR Code') }}:</strong></p>
                            <h5 class="text-success">{{ $booking->pnr_id ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><i class="fa fa-calendar text-info"></i> <strong>{{ __('Booking Date') }}:</strong></p>
                            <p>{{ $booking->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-2"><i class="fa fa-desktop text-warning"></i> <strong>{{ __('Source') }}:</strong></p>
                            <p>{{ ucfirst($booking->source ?? 'N/A') }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fa fa-map-marker text-danger"></i> <strong>{{ __('Route') }}:</strong></p>
                            <h6>{{ $booking->flight_from }} <i class="fa fa-arrow-right"></i> {{ $booking->flight_to }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fa fa-exchange text-primary"></i> <strong>{{ __('Trip Type') }}:</strong></p>
                            <h6>{{ ucfirst($booking->flight_type) }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><i class="fa fa-plane text-info"></i> <strong>{{ __('Airline') }}:</strong></p>
                            <h6>{{ $booking->airline ?? 'N/A' }}</h6>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fa fa-calendar-check-o text-success"></i> <strong>{{ __('Departure Date') }}:</strong></p>
                            <p>{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fa fa-calendar-times-o text-danger"></i> <strong>{{ __('Return Date') }}:</strong></p>
                            <p>{{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('d M Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Information --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fa fa-user"></i> {{ __('Customer Information') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%"><i class="fa fa-user text-muted"></i> <strong>{{ __('Name') }}:</strong></td>
                            <td>{{ $booking->first_name }} {{ $booking->last_name }}</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-envelope text-muted"></i> <strong>{{ __('Email') }}:</strong></td>
                            <td>{{ $booking->email }}</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-phone text-muted"></i> <strong>{{ __('Phone') }}:</strong></td>
                            <td>{{ $booking->phone }}</td>
                        </tr>
                        <tr>
                            <td><i class="fa fa-map-marker text-muted"></i> <strong>{{ __('Country') }}:</strong></td>
                            <td>{{ $booking->country ?? 'N/A' }}</td>
                        </tr>
                        @if($booking->customer_notes)
                            <tr>
                                <td><i class="fa fa-sticky-note text-muted"></i> <strong>{{ __('Notes') }}:</strong></td>
                                <td>{{ $booking->customer_notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa fa-money"></i> {{ __('Payment Summary') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>{{ __('Base Fare') }}:</strong></td>
                            <td class="text-right">{{ format_money_main($booking->base_fee) }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Taxes & Fees') }}:</strong></td>
                            <td class="text-right">{{ format_money_main($booking->total_fee) }}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>{{ __('Total Amount') }}:</strong></td>
                            <td class="text-right"><h5 class="mb-0 text-primary">{{ format_money_main($booking->total) }}</h5></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>{{ __('Paid Amount') }}:</strong></td>
                            <td class="text-right"><h5 class="mb-0 text-success">{{ format_money_main($booking->paid) }}</h5></td>
                        </tr>
                        <tr class="table-danger">
                            <td><strong>{{ __('Due Amount') }}:</strong></td>
                            <td class="text-right"><h5 class="mb-0 text-danger">{{ format_money_main($booking->total - $booking->paid) }}</h5></td>
                        </tr>
                    </table>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted">{{ __('Adults') }}</small>
                            <h6 class="mb-0">{{ $booking->adult_count ?? 0 }}</h6>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Children') }}</small>
                            <h6 class="mb-0">{{ $booking->child_count ?? 0 }}</h6>
                        </div>
                        <div class="col-4">
                            <small class="text-muted">{{ __('Infants') }}</small>
                            <h6 class="mb-0">{{ $booking->infant_count ?? 0 }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Passengers Information — images + edit button যোগ করা হয়েছে --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fa fa-users"></i> {{ __('Passengers') }}
                        <span class="badge badge-light ml-2">{{ $booking->passengers->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>{{ __('Name') }}</th>
                                <th width="80">{{ __('Type') }}</th>
                                <th width="60">{{ __('Gender') }}</th>
                                <th width="90">{{ __('DOB') }}</th>
                                <th>{{ __('Passport') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th width="80" class="text-right">{{ __('Fare') }}</th>
                                <th width="110">{{ __('Ticket') }}</th>
                                {{-- ✅ নতুন columns --}}
                                <th width="80" class="text-center">{{ __('Passport Img') }}</th>
                                <th width="80" class="text-center">{{ __('Visa Img') }}</th>
                                @if(auth()->user()->hasPermission('booking_passengers_edit'))
                                    <th width="70" class="text-center">{{ __('Edit') }}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($booking->passengers as $index => $passenger)
                                @php
                                    // ✅ MediaFile relation থেকে URL নেওয়া হচ্ছে
                                    $passportImageUrl = $passenger->passportMedia
                                        ? get_file_url($passenger->passportMedia->id)
                                        : null;
                                    $visaImageUrl = $passenger->visaMedia
                                        ? get_file_url($passenger->visaMedia->id)
                                        : null;

                                    $passengerName  = $passenger->first_name . ' ' . $passenger->last_name;
                                    $passportExpiry = $passenger->passport_expiry_date ?? '';

                                    $typeColors = [
                                        'ADT' => 'success', 'ADULT' => 'success',
                                        'CNN' => 'warning', 'CHILD' => 'warning',
                                        'INF' => 'info',    'INFANT' => 'info',
                                    ];
                                    $typeColor = $typeColors[$passenger->passenger_type_code ?? ''] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-primary badge-pill">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $passengerName }}</strong>
                                        @if($passenger->status)
                                            <br><span class="badge badge-sm badge-{{ $passenger->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($passenger->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $typeColor }}">
                                            {{ $passenger->traveler_type }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($passenger->gender === 'M')
                                            <i class="fa fa-male text-primary fa-lg"></i>
                                        @elseif($passenger->gender === 'F')
                                            <i class="fa fa-female text-danger fa-lg"></i>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $passenger->dob ? \Carbon\Carbon::parse($passenger->dob)->format('d M Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>No:</strong> {{ $passenger->passport_number ?? 'N/A' }}<br>
                                            @if($passenger->passport_expiry_date)
                                                @php
                                                    $expDate   = \Carbon\Carbon::parse($passenger->passport_expiry_date);
                                                    $isExpired = $expDate->isPast();
                                                    $isSoon    = !$isExpired && $expDate->diffInDays(now()) < 180;
                                                @endphp
                                                <strong>Exp:</strong>
                                                <span class="text-{{ $isExpired ? 'danger' : ($isSoon ? 'warning' : 'success') }}">
                                                    {{ $expDate->format('d M Y') }}
                                                    @if($isExpired) <i class="fa fa-exclamation-circle" title="Expired"></i>
                                                    @elseif($isSoon) <i class="fa fa-clock-o" title="Expiring soon"></i>
                                                    @endif
                                                </span>
                                            @else
                                                <strong>Exp:</strong> <span class="text-muted">N/A</span>
                                            @endif
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
                                        <strong class="text-success">{{ format_money_main($passenger->total) }}</strong>
                                    </td>
                                    <td>
                                        <small>
                                            @if($passenger->ticket_number)
                                                <strong>TKT:</strong> {{ $passenger->ticket_number }}<br>
                                            @endif
                                            @if($passenger->ticket_issued_at)
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($passenger->ticket_issued_at)->format('d M Y') }}</small>
                                            @else
                                                <span class="badge badge-warning">Not Issued</span>
                                            @endif
                                        </small>
                                    </td>

                                    {{-- ✅ Passport Image --}}
                                    <td class="text-center">
                                        @if($passportImageUrl)
                                            <a href="{{ $passportImageUrl }}" target="_blank" title="{{ __('View Passport') }}">
                                                <img src="{{ $passportImageUrl }}"
                                                     alt="Passport"
                                                     class="passenger-doc-thumb">
                                            </a>
                                        @else
                                            <div class="passenger-doc-placeholder" title="{{ __('No passport image') }}">
                                                <i class="fa fa-id-card"></i><br>
                                                <small>None</small>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- ✅ Visa Image --}}
                                    <td class="text-center">
                                        @if($visaImageUrl)
                                            <a href="{{ $visaImageUrl }}" target="_blank" title="{{ __('View Visa') }}">
                                                <img src="{{ $visaImageUrl }}"
                                                     alt="Visa"
                                                     class="passenger-doc-thumb">
                                            </a>
                                        @else
                                            <div class="passenger-doc-placeholder" title="{{ __('No visa image') }}">
                                                <i class="fa fa-file"></i><br>
                                                <small>None</small>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- ✅ Edit Button — permission চেক সহ --}}
                                    @if(auth()->user()->hasPermission('booking_passengers_edit'))
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-warning btn-sm px-2"
                                                    title="{{ __('Edit Passport & Images') }}"
                                                    onclick="openPassengerEdit(
                                                        {{ $passenger->id }},
                                                        '{{ addslashes($passengerName) }}',
                                                        '{{ addslashes($passenger->passport_number ?? '') }}',
                                                        '{{ $passenger->passport_expiry_date ? \Carbon\Carbon::parse($passenger->passport_expiry_date)->format('Y-m-d') : '' }}',
                                                        '{{ $passportImageUrl ?? '' }}',
                                                        '{{ $visaImageUrl ?? '' }}',
                                                        '{{ $passenger->dob ? \Carbon\Carbon::parse($passenger->dob)->format('Y-m-d') : '' }}'
                                                    )">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </td>
                                    @endif
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
    @if($booking->routes && $booking->routes->count() > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fa fa-plane"></i> {{ __('Flight Itinerary') }}
                            <span class="badge badge-light ml-2">{{ $booking->routes->count() }} {{ __('Segments') }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($booking->routes as $index => $route)
                            <div class="flight-segment-mini {{ $index > 0 ? 'mt-3 pt-3 border-top' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        <span class="badge badge-primary badge-pill">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="mb-0 text-primary">{{ $route->departure_iata_code }}</h6>
                                        <small class="text-muted">
                                            <i class="fa fa-calendar"></i> {{ $route->departure_at ? \Carbon\Carbon::parse($route->departure_at)->format('d M Y, h:i A') : 'N/A' }}
                                        </small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <i class="fa fa-plane fa-2x text-primary"></i>
                                        <br><strong>{{ $route->carrier_code }}{{ $route->flight_number }}</strong>
                                        <br><small>{{ $route->duration }} min</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="mb-0 text-success">{{ $route->arrival_iata_code }}</h6>
                                        <small class="text-muted">
                                            <i class="fa fa-calendar"></i> {{ $route->arrival_at ? \Carbon\Carbon::parse($route->arrival_at)->format('d M Y, h:i A') : 'N/A' }}
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <small>
                                            <i class="fa fa-plane"></i> <strong>Aircraft:</strong> {{ $route->aircraft_code }}<br>
                                            <i class="fa fa-ticket"></i> <strong>Class:</strong> <span class="badge badge-info">{{ $route->class }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tickets Information --}}
    @if($booking->ticket_number)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fa fa-ticket"></i> {{ __('Ticket Information') }}</h6>
                    </div>
                    <div class="card-body">
                        @php $tickets = json_decode($booking->ticket_number, true); @endphp
                        @if(is_array($tickets))
                            <div class="row">
                                @foreach($tickets as $ticket)
                                    <div class="col-md-4 mb-2">
                                        <div class="alert alert-success mb-0">
                                            <i class="fa fa-ticket"></i> <strong>{{ $ticket }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mb-0"><i class="fa fa-ticket"></i> <strong>{{ $booking->ticket_number }}</strong></p>
                        @endif
                        @if($booking->ticket_issued_at)
                            <p class="mb-0 mt-2">
                                <small class="text-muted">
                                    <i class="fa fa-clock-o"></i> Issued: {{ $booking->ticket_issued_at }}
                                </small>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .flight-segment-mini {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    .flight-segment-mini:hover {
        background: #e9ecef;
    }

    /* ✅ Passenger document image thumbnail */
    .passenger-doc-thumb {
        width: 64px;
        height: 46px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        display: block;
        margin: 0 auto;
    }
    .passenger-doc-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    /* ✅ Placeholder when no image */
    .passenger-doc-placeholder {
        width: 64px;
        height: 46px;
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        font-size: 10px;
        margin: 0 auto;
        line-height: 1.2;
    }
    .passenger-doc-placeholder i {
        font-size: 14px;
        margin-bottom: 2px;
    }
</style>
