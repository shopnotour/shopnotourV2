@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-3">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="title-bar mb-0">
                <i class="fa fa-edit"></i>
                {{ __('Edit Booking') }} —
                <span class="text-primary">{{ $booking->code ?? '#'.$booking->id }}</span>
                @if($isDuplicate ?? false)
                    <span class="badge badge-warning ml-2">Duplicated</span>
                @endif
            </h1>
            <div class="d-flex gap-2">
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to List') }}
                </a>
            </div>
        </div>

        @include('admin.message')

        <form method="POST" action="{{ route('admin.bookings.update', $booking->id) }}" id="form-edit-booking">
            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- ════════════════════════════════
                     LEFT COLUMN — Booking + Payment
                ════════════════════════════════ --}}
                <div class="col-12 col-lg-8">

                    {{-- ── Booking Info ── --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-plane text-primary"></i> {{ __('Booking Information') }}</h6>
                            <span class="badge badge-{{ ['pending'=>'warning','paid'=>'info','booked'=>'primary','ticketed'=>'success','completed'=>'success','cancelled'=>'danger'][$booking->status]??'secondary' }}">
                            {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                        </span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Status') }} <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control form-control-sm">
                                        @foreach([
                                            'issue_request'=>'Issue Request','pending'=>'Pending','paid'=>'Paid',
                                            'booked'=>'Booked','issued'=>'Issued','ticketed'=>'Ticketed',
                                            'completed'=>'Completed','cancelled'=>'Cancelled','failed'=>'Failed','refunded'=>'Refunded',
                                        ] as $v=>$l)
                                            <option value="{{ $v }}" {{ $booking->status==$v?'selected':'' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Source / GDS') }} <span class="text-danger">*</span></label>
                                    <select name="source" class="form-control form-control-sm">
                                        @foreach(['sabre'=>'Sabre','travelport'=>'Travelport','galileo'=>'Galileo','amadeus'=>'Amadeus','manual'=>'Manual'] as $v=>$l)
                                            <option value="{{ $v }}" {{ $booking->source==$v?'selected':'' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('PNR ID') }}</label>
                                    <input type="text" name="pnr_id" class="form-control form-control-sm"
                                           value="{{ $booking->pnr_id }}"
                                           style="text-transform:uppercase;letter-spacing:2px;font-weight:bold"
                                           placeholder="e.g. ABC123">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Flight From') }}</label>
                                    <input type="text" name="flight_from" class="form-control form-control-sm"
                                           value="{{ $booking->flight_from }}" maxlength="3"
                                           style="text-transform:uppercase" placeholder="DAC">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Flight To') }}</label>
                                    <input type="text" name="flight_to" class="form-control form-control-sm"
                                           value="{{ $booking->flight_to }}" maxlength="3"
                                           style="text-transform:uppercase" placeholder="DXB">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Airline') }}</label>
                                    <input type="text" name="airline" class="form-control form-control-sm"
                                           value="{{ $booking->airline }}" placeholder="Air India">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Flight Type') }}</label>
                                    <select name="flight_type" class="form-control form-control-sm">
                                        <option value="one_way" {{ $booking->flight_type=='one_way'?'selected':'' }}>One Way</option>
                                        <option value="round_trip" {{ $booking->flight_type=='round_trip'?'selected':'' }}>Round Trip</option>
                                        <option value="multi_city" {{ $booking->flight_type=='multi_city'?'selected':'' }}>Multi City</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Seat Class') }}</label>
                                    <select name="seat_class" class="form-control form-control-sm">
                                        @foreach(['Economy'=>'Economy','Premium Economy'=>'Premium Economy','Business'=>'Business','First'=>'First'] as $v=>$l)
                                            <option value="{{ $v }}" {{ $booking->seat_class==$v?'selected':'' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Booking Date') }}</label>
                                    <input type="datetime-local" name="booking_date" class="form-control form-control-sm"
                                           value="{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Start Date') }}</label>
                                    <input type="datetime-local" name="start_date" class="form-control form-control-sm"
                                           value="{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('End Date') }}</label>
                                    <input type="datetime-local" name="end_date" class="form-control form-control-sm"
                                           value="{{ $booking->end_date ? \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">{{ __('Confirmed At') }}</label>
                                    <input type="datetime-local" name="confirmed_at" class="form-control form-control-sm"
                                           value="{{ $booking->confirmed_at ? \Carbon\Carbon::parse($booking->confirmed_at)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Price / Payment ── --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fa fa-money text-success"></i> {{ __('Price & Payment') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Base Fare') }}</label>
                                    <input type="number" name="base_fee" class="form-control form-control-sm price-input"
                                           value="{{ $booking->base_fee }}" min="0" step="0.01" id="inp_base_fee">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Total Tax') }}</label>
                                    <input type="number" name="total_fee" class="form-control form-control-sm price-input"
                                           value="{{ $booking->total_fee }}" min="0" step="0.01" id="inp_total_fee">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Supplier Fee') }}</label>
                                    <input type="number" name="supplier_fee" class="form-control form-control-sm price-input"
                                           value="{{ $booking->supplier_fee }}" min="0" step="0.01" id="inp_supplier_fee">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Ticketing Fee') }}</label>
                                    <input type="number" name="ticketing_fee" class="form-control form-control-sm price-input"
                                           value="{{ $booking->ticketing_fee }}" min="0" step="0.01" id="inp_ticketing_fee">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Coupon Discount') }}</label>
                                    <input type="number" name="coupon_amount" class="form-control form-control-sm price-input"
                                           value="{{ $booking->coupon_amount }}" min="0" step="0.01" id="inp_coupon">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-primary">{{ __('Grand Total') }}</label>
                                    <input type="number" name="total" class="form-control form-control-sm fw-bold text-primary"
                                           id="inp_total" value="{{ $booking->total }}" min="0" step="0.01">
                                    <small class="text-muted">{{ __('Or auto-calculate') }} →
                                        <a href="#" id="btn-auto-calc" class="text-primary">Calc</a>
                                    </small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-success">{{ __('Already Paid') }}</label>
                                    <input type="number" name="paid" class="form-control form-control-sm text-success"
                                           value="{{ $booking->paid }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">{{ __('Currency') }}</label>
                                    <input type="text" name="currency" class="form-control form-control-sm"
                                           value="{{ $booking->currency ?? 'BDT' }}" maxlength="3"
                                           style="text-transform:uppercase">
                                </div>
                            </div>

                            {{-- ✅ Wallet Deduction Option --}}
                            <div class="mt-3 p-3 border rounded bg-light">
                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="deduct_wallet" id="deduct_wallet" value="1">
                                        <label class="form-check-label fw-semibold" for="deduct_wallet">
                                            {{ __('Deduct from customer wallet') }}
                                        </label>
                                    </div>
                                    <div class="text-muted small mt-1" id="wallet-info">
                                        @if($booking->user)
                                            <i class="fa fa-wallet"></i>
                                            {{ __('Available balance:') }}
                                            <strong class="{{ $booking->user->credit_balance > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ format_money_main($booking->user->credit_balance) }}
                                            </strong>
                                        @else
                                            <span class="text-muted">No user linked</span>
                                        @endif
                                    </div>
                                </div>
                                <div id="wallet-deduct-section" style="display:none;" class="mt-2">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small">{{ __('Amount to deduct') }}</label>
                                            <input type="number" name="wallet_deduct_amount" id="wallet_deduct_amount"
                                                   class="form-control form-control-sm"
                                                   min="0" step="0.01"
                                                   value="{{ $booking->total - $booking->paid }}"
                                                   placeholder="Amount">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label small">{{ __('Transaction Remarks') }}</label>
                                            <input type="text" name="wallet_deduct_remarks" class="form-control form-control-sm"
                                                   placeholder="{{ __('e.g. Payment for booking') }}"
                                                   value="{{ __('Payment for booking :code', ['code' => $booking->code ?? '#'.$booking->id]) }}">
                                        </div>
                                    </div>
                                    <div class="alert alert-warning py-2 mt-2 mb-0 small">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        {{ __('A debit transaction will be created and deducted from credit balance.') }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Routes / Segments ── --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-map-marker text-danger"></i> {{ __('Flight Segments / Routes') }}</h6>
                            <button type="button" class="btn btn-success btn-sm" id="btn-add-route">
                                <i class="fa fa-plus"></i> {{ __('Add Segment') }}
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="routes-table">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="30">#</th>
                                        <th>{{ __('From') }}</th>
                                        <th>{{ __('To') }}</th>
                                        <th>{{ __('Flight No') }}</th>
                                        <th>{{ __('Departure') }}</th>
                                        <th>{{ __('Arrival') }}</th>
                                        <th>{{ __('Cabin') }}</th>
                                        <th width="36"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="routes-body">
                                    @foreach($booking->routes ?? [] as $ri => $route)
                                        <tr data-route-index="{{ $ri }}">
                                            <td class="text-center text-muted small">{{ $ri+1 }}</td>
                                            <td>
                                                <input type="hidden" name="routes[{{ $ri }}][id]" value="{{ $route->id }}">
                                                <input type="text" name="routes[{{ $ri }}][departure_iata_code]"
                                                       class="form-control form-control-sm iata-upper"
                                                       value="{{ $route->departure_iata_code }}" maxlength="3" style="width:60px">
                                            </td>
                                            <td>
                                                <input type="text" name="routes[{{ $ri }}][arrival_iata_code]"
                                                       class="form-control form-control-sm iata-upper"
                                                       value="{{ $route->arrival_iata_code }}" maxlength="3" style="width:60px">
                                            </td>
                                            <td>
                                                <input type="text" name="routes[{{ $ri }}][flight_number]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $route->flight_number }}" style="width:80px">
                                            </td>
                                            <td>
                                                <input type="datetime-local" name="routes[{{ $ri }}][departure_at]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $route->departure_at ? \Carbon\Carbon::parse($route->departure_at)->format('Y-m-d\TH:i') : '' }}"
                                                       style="width:160px">
                                            </td>
                                            <td>
                                                <input type="datetime-local" name="routes[{{ $ri }}][arrival_at]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $route->arrival_at ? \Carbon\Carbon::parse($route->arrival_at)->format('Y-m-d\TH:i') : '' }}"
                                                       style="width:160px">
                                            </td>
                                            <td>
                                                <select name="routes[{{ $ri }}][cabin]" class="form-control form-control-sm" style="width:110px">
                                                    @foreach(['Economy','Premium Economy','Business','First'] as $cab)
                                                        <option value="{{ $cab }}" {{ $route->cabin==$cab?'selected':'' }}>{{ $cab }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm px-1 py-0 btn-remove-route"
                                                        title="Remove">
                                                    <i class="fa fa-trash fa-fw" style="font-size:11px"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Passengers ── --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fa fa-users text-info"></i> {{ __('Passengers') }}</h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach($booking->passengers ?? [] as $pi => $pax)
                                <div class="passenger-block border-bottom p-3" data-pax-index="{{ $pi }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-semibold">
                                        <span class="badge badge-{{ ['ADT'=>'primary','CNN'=>'info','INF'=>'warning'][$pax->passenger_type_code??'ADT']??'secondary' }}">
                                            {{ $pax->passenger_type_code ?? 'ADT' }}
                                        </span>
                                            {{ $pax->first_name }} {{ $pax->last_name }}
                                        </h6>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-muted btn-toggle-pax"
                                                data-target="#pax-detail-{{ $pi }}">
                                            <i class="fa fa-chevron-down"></i>
                                        </button>
                                    </div>

                                    <div id="pax-detail-{{ $pi }}">
                                        <input type="hidden" name="passengers[{{ $pi }}][id]" value="{{ $pax->id }}">
                                        <div class="row g-2">
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">{{ __('Title') }}</label>
                                                <select name="passengers[{{ $pi }}][title]" class="form-control form-control-sm">
                                                    @foreach(['Mr','Mrs','Ms','Miss','Master','Dr'] as $t)
                                                        <option value="{{ $t }}" {{ $pax->title==$t?'selected':'' }}>{{ $t }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">{{ __('Type') }}</label>
                                                <select name="passengers[{{ $pi }}][passenger_type_code]" class="form-control form-control-sm">
                                                    <option value="ADT" {{ ($pax->passenger_type_code??'')==='ADT'?'selected':'' }}>Adult</option>
                                                    <option value="CNN" {{ ($pax->passenger_type_code??'')==='CNN'?'selected':'' }}>Child</option>
                                                    <option value="INF" {{ ($pax->passenger_type_code??'')==='INF'?'selected':'' }}>Infant</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">{{ __('First Name') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][first_name]"
                                                       class="form-control form-control-sm" value="{{ $pax->first_name }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">{{ __('Last Name') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][last_name]"
                                                       class="form-control form-control-sm" value="{{ $pax->last_name }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Date of Birth') }}</label>
                                                <input type="date" name="passengers[{{ $pi }}][dob]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $pax->dob ? \Carbon\Carbon::parse($pax->dob)->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Gender') }}</label>
                                                <select name="passengers[{{ $pi }}][gender]" class="form-control form-control-sm">
                                                    <option value="male" {{ $pax->gender=='male'?'selected':'' }}>Male</option>
                                                    <option value="female" {{ $pax->gender=='female'?'selected':'' }}>Female</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Passport No') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][passport_number]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $pax->passport_number }}" placeholder="AB1234567">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Passport Expiry') }}</label>
                                                <input type="date" name="passengers[{{ $pi }}][passport_expiry_date]"
                                                       class="form-control form-control-sm"
                                                       value="{{ $pax->passport_expiry_date ? \Carbon\Carbon::parse($pax->passport_expiry_date)->format('Y-m-d') : '' }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Email') }}</label>
                                                <input type="email" name="passengers[{{ $pi }}][email]"
                                                       class="form-control form-control-sm" value="{{ $pax->email }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-semibold">{{ __('Phone') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][phone]"
                                                       class="form-control form-control-sm" value="{{ $pax->phone }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-semibold">{{ __('Country') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][country]"
                                                       class="form-control form-control-sm" value="{{ $pax->country }}"
                                                       maxlength="2" style="text-transform:uppercase">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-semibold">{{ __('Ticket Number') }}</label>
                                                <input type="text" name="passengers[{{ $pi }}][ticket_number]"
                                                       class="form-control form-control-sm" value="{{ $pax->ticket_number }}"
                                                       maxlength="13" placeholder="13-digit e-ticket">
                                            </div>

                                            {{-- Pricing per passenger --}}
                                            <div class="col-12">
                                                <div class="bg-light rounded p-2 mt-1">
                                                    <div class="small fw-semibold text-muted mb-2">
                                                        <i class="fa fa-money"></i> {{ __('Passenger Fare') }}
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label x-small">{{ __('Base') }}</label>
                                                            <input type="number" name="passengers[{{ $pi }}][base]"
                                                                   class="form-control form-control-sm"
                                                                   value="{{ $pax->base }}" min="0" step="0.01">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label x-small">{{ __('Tax') }}</label>
                                                            <input type="number" name="passengers[{{ $pi }}][tax]"
                                                                   class="form-control form-control-sm"
                                                                   value="{{ $pax->tax }}" min="0" step="0.01">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label x-small">{{ __('Total') }}</label>
                                                            <input type="number" name="passengers[{{ $pi }}][total]"
                                                                   class="form-control form-control-sm"
                                                                   value="{{ $pax->total }}" min="0" step="0.01">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label x-small">{{ __('Cabin') }}</label>
                                                            <select name="passengers[{{ $pi }}][cabin]" class="form-control form-control-sm">
                                                                @foreach(['Economy','Premium Economy','Business','First'] as $cab)
                                                                    <option value="{{ $cab }}" {{ $pax->cabin==$cab?'selected':'' }}>{{ $cab }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- ════════════════════════════════
                     RIGHT COLUMN — Summary + Contact
                ════════════════════════════════ --}}
                <div class="col-12 col-lg-4">

                    {{-- Booking Summary Card --}}
                    <div class="card shadow-sm mb-3 sticky-top" style="top:80px">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fa fa-info-circle text-info"></i> {{ __('Summary') }}</h6>
                        </div>
                        <div class="card-body">

                            {{-- Code + Source --}}
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Code</span>
                                <strong class="text-primary">{{ $booking->code ?? '#'.$booking->id }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Route</span>
                                <strong>{{ $booking->flight_from }} → {{ $booking->flight_to }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Passengers</span>
                                <span>
                                @if($booking->adult_count) <span class="badge badge-primary">{{ $booking->adult_count }}A</span> @endif
                                    @if($booking->child_count) <span class="badge badge-info">{{ $booking->child_count }}C</span> @endif
                                    @if($booking->infant_count) <span class="badge badge-warning">{{ $booking->infant_count }}I</span> @endif
                            </span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Total</span>
                                <strong>{{ format_money_main($booking->total) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Paid</span>
                                <strong class="text-success">{{ format_money_main($booking->paid) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted small">Due</span>
                                <strong class="text-danger">{{ format_money_main($booking->total - $booking->paid) }}</strong>
                            </div>

                            {{-- Save Button --}}
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> {{ __('Save Changes') }}
                            </button>
                            <a href="{{ route('bookings.index') }}" class="btn btn-secondary btn-block mt-2">
                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>

                    {{-- Customer Info --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="fa fa-user text-warning"></i> {{ __('Customer / Contact') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="form-label small fw-semibold">{{ __('First Name') }}</label>
                                <input type="text" name="first_name" class="form-control form-control-sm"
                                       value="{{ $booking->first_name }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label small fw-semibold">{{ __('Last Name') }}</label>
                                <input type="text" name="last_name" class="form-control form-control-sm"
                                       value="{{ $booking->last_name }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label small fw-semibold">{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control form-control-sm"
                                       value="{{ $booking->email }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="form-label small fw-semibold">{{ __('Phone') }}</label>
                                <input type="text" name="phone" class="form-control form-control-sm"
                                       value="{{ $booking->phone }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label small fw-semibold">{{ __('Notes') }}</label>
                                <textarea name="customer_notes" class="form-control form-control-sm" rows="2">{{ $booking->customer_notes }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Hidden new route index tracker --}}
            <input type="hidden" id="route_count" value="{{ count($booking->routes ?? []) }}">

        </form>

    </div>
@endsection


@push('css')
    <style>
        .passenger-block { transition: background 0.2s; }
        .passenger-block:hover { background: #f8f9fa; }
        .x-small { font-size: 11px; font-weight: 600; color: #6c757d; margin-bottom: 2px; }
        .form-label.small { font-size: 12px; margin-bottom: 3px; }
        .btn-toggle-pax { font-size: 12px; }
        .iata-upper { text-transform: uppercase; letter-spacing: 1px; font-weight: bold; text-align: center; }
    </style>
@endpush


@push('js')
    <script>
        $(document).ready(function () {

            // ── Auto calculate total ──
            $('#btn-auto-calc').on('click', function (e) {
                e.preventDefault();
                var base   = parseFloat($('#inp_base_fee').val()) || 0;
                var tax    = parseFloat($('#inp_total_fee').val()) || 0;
                var supp   = parseFloat($('#inp_supplier_fee').val()) || 0;
                var tkt    = parseFloat($('#inp_ticketing_fee').val()) || 0;
                var coupon = parseFloat($('#inp_coupon').val()) || 0;
                var total  = base + tax + supp + tkt - coupon;
                $('#inp_total').val(total.toFixed(2));
            });

            // ── Wallet checkbox toggle ──
            $('#deduct_wallet').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#wallet-deduct-section').slideDown(200);
                } else {
                    $('#wallet-deduct-section').slideUp(200);
                }
            });

            // ── Passenger collapse toggle ──
            $(document).on('click', '.btn-toggle-pax', function () {
                var target = $($(this).data('target'));
                target.slideToggle(200);
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // ── IATA uppercase ──
            $(document).on('input', '.iata-upper', function () {
                $(this).val($(this).val().toUpperCase());
            });

            // ── Add Route Row ──
            $('#btn-add-route').on('click', function () {
                var idx = parseInt($('#route_count').val());
                var row = `<tr data-route-index="${idx}">
            <td class="text-center text-muted small">${idx+1}</td>
            <td>
                <input type="hidden" name="routes[${idx}][id]" value="">
                <input type="text" name="routes[${idx}][departure_iata_code]"
                       class="form-control form-control-sm iata-upper" maxlength="3" style="width:60px" placeholder="DAC">
            </td>
            <td>
                <input type="text" name="routes[${idx}][arrival_iata_code]"
                       class="form-control form-control-sm iata-upper" maxlength="3" style="width:60px" placeholder="DXB">
            </td>
            <td>
                <input type="text" name="routes[${idx}][flight_number]"
                       class="form-control form-control-sm" style="width:80px" placeholder="AI501">
            </td>
            <td>
                <input type="datetime-local" name="routes[${idx}][departure_at]"
                       class="form-control form-control-sm" style="width:160px">
            </td>
            <td>
                <input type="datetime-local" name="routes[${idx}][arrival_at]"
                       class="form-control form-control-sm" style="width:160px">
            </td>
            <td>
                <select name="routes[${idx}][cabin]" class="form-control form-control-sm" style="width:110px">
                    <option>Economy</option><option>Premium Economy</option>
                    <option>Business</option><option>First</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm px-1 py-0 btn-remove-route">
                    <i class="fa fa-trash fa-fw" style="font-size:11px"></i>
                </button>
            </td>
        </tr>`;
                $('#routes-body').append(row);
                $('#route_count').val(idx + 1);
            });

            // ── Remove Route Row ──
            $(document).on('click', '.btn-remove-route', function () {
                if (!confirm('Remove this segment?')) return;
                $(this).closest('tr').remove();
            });

            // ── Form submit confirm ──
            $('#form-edit-booking').on('submit', function (e) {
                var deduct = $('#deduct_wallet').is(':checked');
                if (deduct) {
                    var amount = parseFloat($('#wallet_deduct_amount').val()) || 0;
                    if (!confirm('Save changes and deduct ৳' + amount.toFixed(2) + ' from customer wallet?')) {
                        e.preventDefault();
                    }
                }
            });

        });
    </script>
@endpush
