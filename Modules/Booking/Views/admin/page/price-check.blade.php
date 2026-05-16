@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        @php
            $priceData = $priceResponse ?? [];
            $success = $priceData['success'] ?? false;
            $solutions = $priceData['price_result']['solutions'] ?? [];
            $itinerary = $priceData['itinerary'] ?? [];
            $segments = $itinerary['segments'] ?? [];
            $messages = $priceData['messages'] ?? [];

            $booking = $booking ?? null;

            // ✅ NEW: Correct passenger count from pricing_info
            $passengerCount = ['ADT' => 0, 'CNN' => 0, 'INF' => 0];
            $firstSolution = $solutions[0] ?? null;

            if ($firstSolution && isset($firstSolution['pricing_info'])) {
                foreach ($firstSolution['pricing_info'] as $pricingInfo) {
                    $type = $pricingInfo['passenger_type'] ?? 'ADT';

                    // ✅ Type normalize (C07/C03 → CNN)
                    if ($type == 'C07' || $type == 'C03') {
                        $type = 'CNN';
                    }

                    // ✅ Use passenger_count from parsed data
                    $count = $pricingInfo['passenger_count'] ?? 1;
                    $passengerCount[$type] = ($passengerCount[$type] ?? 0) + $count;
                }
            }

            $totalPassengers = array_sum($passengerCount);
        @endphp

        {{-- Header --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="title-bar mb-0">
                            <i class="fa fa-dollar"></i> {{ __('Price Comparison & Selection') }}
                        </h1>
                        @if($booking)
                            <p class="text-muted mb-0 mt-2">
                                <strong>{{ __('Booking:') }}</strong> {{ $booking->code ?? $bookingId }}
                                <span class="ml-3">
                                    <i class="fa fa-users"></i> {{ $totalPassengers }} {{ __('Passengers') }}
                                    @if($passengerCount['ADT'] > 0)
                                        <span class="badge badge-info">{{ $passengerCount['ADT'] }} Adult(s)</span>
                                    @endif
                                    @if($passengerCount['CNN'] > 0)
                                        <span class="badge badge-warning">{{ $passengerCount['CNN'] }} Child(ren)</span>
                                    @endif
                                    @if($passengerCount['INF'] > 0)
                                        <span class="badge badge-secondary">{{ $passengerCount['INF'] }} Infant(s)</span>
                                    @endif
                                </span>
                            </p>
                        @endif
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back to Bookings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.message')

        @if($success && !empty($solutions))
            {{-- System Messages --}}
            @if(!empty($messages))
                <div class="card mb-4 shadow">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-exclamation-triangle"></i> {{ __('System Messages') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($messages as $message)
                            <div class="alert alert-{{ $message['type'] === 'Warning' ? 'warning' : 'info' }} mb-2">
                                <strong>[{{ $message['code'] }}]</strong> {{ $message['message'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Flight Itinerary --}}
            @if(!empty($segments))
                <div class="card mb-4 shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-plane"></i> {{ __('Flight Itinerary') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($segments as $index => $segment)
                                <div class="col-md-6 mb-3">
                                    <div class="flight-summary-card p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="badge badge-primary">Segment {{ $index + 1 }}</span>
                                                <h5 class="mb-1 mt-2">{{ $segment['origin'] }} → {{ $segment['destination'] }}</h5>
                                                <p class="mb-0 text-muted">
                                                    {{ $segment['carrier'] }} {{ $segment['flight_number'] }}
                                                    <span class="badge badge-secondary">{{ $segment['class_of_service'] }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <small>{{ \Carbon\Carbon::parse($segment['departure_time'])->format('d M Y') }}</small><br>
                                                <strong>{{ \Carbon\Carbon::parse($segment['departure_time'])->format('H:i') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Price Solutions --}}
            @foreach($solutions as $solutionIndex => $solution)
                @php
                    $totalPrice = $solution['approximate_total_price']['amount'] ?? 0;
                    $currency = $solution['total_price']['currency'] ?? 'BDT';
                    $pricingInfoArray = $solution['pricing_info'] ?? [];

                    // ✅ Find cheapest solution
                    $allPrices = array_column($solutions, 'approximate_total_price');
                    $allAmounts = array_map(fn($p) => $p['amount'] ?? 0, $allPrices);
                    $minPrice = min($allAmounts);
                    $isCheapest = ($solution['approximate_total_price']['amount'] ?? 0) == $minPrice;

                @endphp

                <div class="card shadow mb-4">
                    <div class="card-header {{ $isCheapest ? 'bg-success' : 'bg-primary' }} text-white">

                    {{--                    <div class="card-header {{ $solutionIndex === 0 ? 'bg-success' : 'bg-primary' }} text-white">--}}
                        <div class="row align-items-center">
                            <div class="col-md-6">
{{--                                <h4 class="mb-0">--}}
{{--                                    <i class="fa fa-tag"></i> {{ __('Option') }} {{ $solutionIndex + 1 }}--}}
{{--                                    @if($solutionIndex === 0)--}}
{{--                                        <span class="badge badge-warning ml-2">--}}
{{--                                            <i class="fa fa-star"></i> {{ __('Recommended') }}--}}
{{--                                        </span>--}}
{{--                                    @endif--}}
{{--                                </h4>--}}
                                <h4 class="mb-0">
                                    <i class="fa fa-tag"></i> {{ __('Option') }} {{ $solutionIndex + 1 }}
                                    @if($isCheapest)
                                        <span class="badge badge-warning ml-2">
                        <i class="fa fa-star"></i> {{ __('Recommended') }}
                    </span>
                                    @endif
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <h3 class="mb-0">
                                    {{ __('Total:') }} {{ number_format($totalPrice, 0) }} {{ $currency }}
                                </h3>
                                <small>{{ __('For all passengers') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Passenger Type Breakdown --}}
                        <div class="row">
                            @foreach($pricingInfoArray as $paxIndex => $pricingInfo)
                                @php
                                    $paxType = $pricingInfo['passenger_type'] ?? 'ADT';
                                    if ($paxType == 'C07' || $paxType == 'C03') {
                                        $paxType = 'CNN';
                                    }

                                    // ✅ Get passenger count from pricing_info
                                    $paxCount = $pricingInfo['passenger_count'] ?? 1;
                                    $allPassengers = $pricingInfo['all_passengers'] ?? [];

                                    $paxAge = $pricingInfo['passenger_age'] ?? null;
                                    $paxTotal = $pricingInfo['approximate_total_price']['amount'] ?? 0;
                                    $paxBase = $pricingInfo['approximate_base_price']['amount'] ?? 0;
                                    $paxTax = $pricingInfo['approximate_taxes']['amount'] ?? 0;

                                    // ✅ Grand total for multiple passengers
                                    $grandTotal = $paxTotal * $paxCount;

                                    $penalties = $pricingInfo['penalties'] ?? [];
                                    $changeFee = $penalties['change']['amount']['amount'] ?? 0;
                                    $cancelFee = $penalties['cancel']['amount']['amount'] ?? 0;

                                    $baggageAllowances = $pricingInfo['baggage_allowances'] ?? [];
                                    $baggageInfo = $baggageAllowances['baggage_allowance_info'][0]['texts'][0] ?? 'N/A';
                                    $carryOnInfo = $baggageAllowances['carry_on_allowance_info'][0]['texts'][0] ?? 'N/A';

                                    $refundable = $pricingInfo['refundable'] ?? false;
                                    $taxInfo = $pricingInfo['tax_info'] ?? [];
                                    $fareInfo = $pricingInfo['fare_info'] ?? [];

                                    $paxTypeLabel = [
                                        'ADT' => 'Adult',
                                        'CNN' => 'Child',
                                        'INF' => 'Infant'
                                    ][$paxType] ?? $paxType;

                                    $paxTypeBadge = [
                                        'ADT' => 'info',
                                        'CNN' => 'warning',
                                        'INF' => 'secondary'
                                    ][$paxType] ?? 'light';
                                @endphp

                                <div class="col-md-4 mb-4">
                                    <div class="passenger-type-card border rounded p-3 h-100">
                                        {{-- Passenger Type Header --}}
                                        <div class="text-center mb-3 pb-3 border-bottom">
                                            <h5 class="mb-2">
                                                <span class="badge badge-{{ $paxTypeBadge }} badge-lg">
                                                    {{ $paxTypeLabel }}
                                                    @if($paxAge) ({{ $paxAge }} yrs) @endif
                                                </span>
                                            </h5>

                                            {{-- ✅ Show all passenger ages if multiple --}}
                                            @if($paxCount > 1 && !empty($allPassengers))
                                                <div class="small text-muted mb-2">
                                                    @foreach($allPassengers as $idx => $pax)
                                                        <span class="badge badge-light">
                                                            Pax {{ $idx + 1 }}: {{ $pax['age'] ?? 'N/A' }} yrs
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="price-display-small">
                                                <h2 class="text-primary mb-0">{{ number_format($paxTotal, 0) }}</h2>
                                                <small class="text-muted">{{ $currency }} / person</small>
                                            </div>

                                            {{-- ✅ Total calculation for multiple passengers --}}
                                            @if($paxCount > 1)
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small class="text-muted d-block mb-1">
                                                        {{ $paxCount }} {{ $paxTypeLabel }}(s)
                                                    </small>
                                                    <strong class="text-success">
                                                        {{ $paxCount }} × {{ number_format($paxTotal, 0) }} = {{ number_format($grandTotal, 0) }} {{ $currency }}
                                                    </strong>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Price Breakdown --}}
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fa fa-list"></i> {{ __('Price Breakdown') }}
                                            </h6>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td>{{ __('Base Fare') }}</td>
                                                    <td class="text-right"><strong>{{ number_format($paxBase, 0) }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>{{ __('Taxes') }}</td>
                                                    <td class="text-right"><strong>{{ number_format($paxTax, 0) }}</strong></td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td><strong>{{ __('Per Person') }}</strong></td>
                                                    <td class="text-right">
                                                        <strong class="text-success">{{ number_format($paxTotal, 0) }}</strong>
                                                    </td>
                                                </tr>
                                                @if($paxCount > 1)
                                                    <tr class="border-top bg-light">
                                                        <td><strong>{{ __('Total') }} (×{{ $paxCount }})</strong></td>
                                                        <td class="text-right">
                                                            <strong class="text-primary">{{ number_format($grandTotal, 0) }}</strong>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>

                                        {{-- Baggage Allowance --}}
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fa fa-suitcase"></i> {{ __('Baggage Allowance') }}
                                            </h6>
                                            <div class="small">
                                                <div class="mb-2">
                                                    <i class="fa fa-check text-success"></i>
                                                    <strong>{{ __('Checked:') }}</strong> {{ $baggageInfo }}
                                                </div>
                                                <div>
                                                    <i class="fa fa-check text-success"></i>
                                                    <strong>{{ __('Cabin:') }}</strong> {{ $carryOnInfo }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Penalties --}}
                                        <div class="mb-3 p-2 bg-light rounded">
                                            <h6 class="text-muted mb-2">
                                                <i class="fa fa-exclamation-circle"></i> {{ __('Penalties') }}
                                            </h6>
                                            <div class="small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>{{ __('Change:') }}</span>
                                                    <strong class="text-warning">{{ number_format($changeFee, 0) }} {{ $currency }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>{{ __('Cancel:') }}</span>
                                                    <strong class="text-danger">{{ number_format($cancelFee, 0) }} {{ $currency }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Additional Info --}}
                                        <div class="mb-3">
                                            <div class="small">
                                                @if($refundable)
                                                    <span class="badge badge-success">
                                                        <i class="fa fa-check"></i> {{ __('Refundable') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="fa fa-times"></i> {{ __('Non-Refundable') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Tax Details Modal Trigger --}}
                                        @if(!empty($taxInfo))
                                            <button type="button" class="btn btn-sm btn-outline-info btn-block"
                                                    data-toggle="modal"
                                                    data-target="#taxModal{{ $solutionIndex }}_{{ $paxIndex }}">
                                                <i class="fa fa-calculator"></i> {{ __('View Tax Details') }}
                                            </button>
                                        @endif

                                        {{-- Fare Details Collapsible --}}
                                        @if(!empty($fareInfo))
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-block mt-2"
                                                    data-toggle="collapse"
                                                    data-target="#fareDetails{{ $solutionIndex }}_{{ $paxIndex }}">
                                                <i class="fa fa-info-circle"></i> {{ __('Fare Basis Details') }}
                                            </button>
                                            <div class="collapse mt-2" id="fareDetails{{ $solutionIndex }}_{{ $paxIndex }}">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="thead-light">
                                                        <tr>
                                                            <th>{{ __('Route') }}</th>
                                                            <th>{{ __('Fare Basis') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($fareInfo as $fare)
                                                            <tr>
                                                                <td>{{ $fare['origin'] }} → {{ $fare['destination'] }}</td>
                                                                <td><code>{{ $fare['fare_basis'] }}</code></td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Tax Modal --}}
                                @if(!empty($taxInfo))
                                    <div class="modal fade" id="taxModal{{ $solutionIndex }}_{{ $paxIndex }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">
                                                        {{ __('Tax Details') }} - {{ $paxTypeLabel }}
                                                        @if($paxCount > 1)
                                                            <span class="badge badge-light text-dark">(×{{ $paxCount }})</span>
                                                        @endif
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="thead-light">
                                                        <tr>
                                                            <th>{{ __('Tax Code') }}</th>
                                                            <th class="text-right">{{ __('Amount') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php $taxTotal = 0; @endphp
                                                        @foreach($taxInfo as $tax)
                                                            @php $taxTotal += $tax['amount']['amount']; @endphp
                                                            <tr>
                                                                <td><strong>{{ $tax['category'] }}</strong></td>
                                                                <td class="text-right">{{ number_format($tax['amount']['amount'], 0) }}</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="table-info">
                                                            <td><strong>{{ __('Per Person Total') }}</strong></td>
                                                            <td class="text-right"><strong>{{ number_format($taxTotal, 0) }}</strong></td>
                                                        </tr>
                                                        @if($paxCount > 1)
                                                            <tr class="table-success">
                                                                <td><strong>{{ __('Total for') }} {{ $paxCount }} {{ $paxTypeLabel }}(s)</strong></td>
                                                                <td class="text-right"><strong>{{ number_format($taxTotal * $paxCount, 0) }}</strong></td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Select Button --}}
                        <div class="text-center mt-4 pt-4 border-top">
                            <form action="{{ route('booking.price.update', $bookingId) }}" method="POST" class="price-select-form">
                                @csrf

                                {{-- Selected Solution Index --}}
                                <input type="hidden" name="selected_solution_index" value="{{ $solutionIndex }}">

                                {{-- Total Price Info --}}
                                <input type="hidden" name="total_price" value="{{ $totalPrice }}">
                                <input type="hidden" name="currency" value="{{ $currency }}">
                                <input type="hidden" name="base_price" value="{{ $solution['approximate_base_price']['amount'] ?? 0 }}">
                                <input type="hidden" name="total_tax" value="{{ $solution['approximate_taxes']['amount'] ?? 0 }}">

                                {{-- Passenger-wise Pricing --}}
                                @foreach($pricingInfoArray as $paxIndex => $pricingInfo)
                                    @php
                                        $paxType = $pricingInfo['passenger_type'] ?? 'ADT';
                                        $paxTotal = $pricingInfo['approximate_total_price']['amount'] ?? 0;
                                        $paxBase = $pricingInfo['approximate_base_price']['amount'] ?? 0;
                                        $paxTax = $pricingInfo['approximate_taxes']['amount'] ?? 0;
                                    @endphp

                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][total]" value="{{ $paxTotal }}">
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][base_fare]" value="{{ $paxBase }}">
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][tax]" value="{{ $paxTax }}">
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][refundable]" value="{{ $pricingInfo['refundable'] ? '1' : '0' }}">

                                    {{-- Baggage Info --}}
                                    @php
                                        $baggageAllowances = $pricingInfo['baggage_allowances'] ?? [];
                                        $baggageInfo = $baggageAllowances['baggage_allowance_info'][0]['texts'][0] ?? 'N/A';
                                        $carryOnInfo = $baggageAllowances['carry_on_allowance_info'][0]['texts'][0] ?? 'N/A';
                                    @endphp
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][baggage]" value="{{ $baggageInfo }}">
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][cabin_bag]" value="{{ $carryOnInfo }}">

                                    {{-- Penalties --}}
                                    @php
                                        $penalties = $pricingInfo['penalties'] ?? [];
                                        $changeFee = $penalties['change']['amount']['amount'] ?? 0;
                                        $cancelFee = $penalties['cancel']['amount']['amount'] ?? 0;
                                    @endphp
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][change_penalty]" value="{{ $changeFee }}">
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][cancel_penalty]" value="{{ $cancelFee }}">

                                    {{-- Fare Basis --}}
                                    @php
                                        $fareInfo = $pricingInfo['fare_info'] ?? [];
                                        $fareBasisCodes = array_column($fareInfo, 'fare_basis');
                                    @endphp
                                    <input type="hidden" name="passenger_prices[{{ $paxType }}][fare_basis]" value="{{ implode(',', $fareBasisCodes) }}">
                                @endforeach

                                {{-- Flight Info --}}
                                @foreach($segments as $segIndex => $segment)
                                    <input type="hidden" name="segments[{{ $segIndex }}][origin]" value="{{ $segment['origin'] }}">
                                    <input type="hidden" name="segments[{{ $segIndex }}][destination]" value="{{ $segment['destination'] }}">
                                    <input type="hidden" name="segments[{{ $segIndex }}][carrier]" value="{{ $segment['carrier'] }}">
                                    <input type="hidden" name="segments[{{ $segIndex }}][flight_number]" value="{{ $segment['flight_number'] }}">
                                    <input type="hidden" name="segments[{{ $segIndex }}][departure_time]" value="{{ $segment['departure_time'] }}">
                                    <input type="hidden" name="segments[{{ $segIndex }}][class]" value="{{ $segment['class_of_service'] }}">
                                @endforeach

                                {{-- Important Keys for later use --}}
                                <input type="hidden" name="solution_key" value="{{ $solution['key'] ?? '' }}">
                                <input type="hidden" name="quote_date" value="{{ $solution['quote_date'] ?? now()->format('Y-m-d') }}">

                                <button type="submit" class="btn btn-lg btn-{{ $isCheapest ? 'success' : 'primary' }} px-5">
                                    <i class="fa fa-check-circle"></i>
                                    {{ __('Select Option') }} {{ $solutionIndex + 1 }}
                                    <span class="ml-2">({{ number_format($totalPrice, 0) }} {{ $currency }})</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

        @else
            {{-- Error State --}}
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fa fa-exclamation-circle fa-5x text-danger mb-4"></i>
                    <h3 class="text-danger">{{ __('No Pricing Options Available') }}</h3>
                    <p class="text-muted">
                        @if(isset($priceData['error']))
                            {{ $priceData['error'] }}
                        @else
                            {{ __('Unable to retrieve pricing information.') }}
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('css')
    <style>
        .passenger-type-card {
            background: #f8f9fa;
            transition: all 0.3s;
        }

        .passenger-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .price-display-small h2 {
            font-size: 2rem;
            font-weight: bold;
        }

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .flight-summary-card {
            background: #f8f9fa;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            $('.price-select-form').on('submit', function(e) {
                const form = $(this);
                const total = form.find('input[name="total_price"]').val();
                const currency = form.find('input[name="currency"]').val();
                const optionIndex = parseInt(form.find('input[name="selected_solution_index"]').val()) + 1;

                const message = `Are You Select Option ${optionIndex} (${parseInt(total).toLocaleString()} ${currency}) নিশ্চিত করতে চান?`;

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush
