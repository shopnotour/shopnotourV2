@php
// Use prepared data from backend
$flightId = $row['id'];
$airlineCode = $row['airline_code'];
$price = $row['price'];
$formattedPrice = $row['formatted_price'];
$formattedTax = $row['formatted_tax'];
$departureCode = $row['departure_code'];
$arrivalCode = $row['arrival_code'];
$departureTime = $row['departure_time'];
$arrivalTime = $row['arrival_time'];
$duration = $row['duration'];
$interval = new DateInterval($row['duration']);

$airlineImageUrl = $row['airline_image_url'];
$isBookable = $row['is_bookable'];
$originalData = $row['original_data']; // Keep for detailed data access

// Prepared detailed data from backend
$flightDetails = $row['flight_details'] ?? [];
$fareDetails = $row['fare_details'] ?? [];
$baggageDetails = $row['baggage_details'] ?? [];
$alldata = $row['alldata'] ?? [];
@endphp

<div class="py-30 px-30 bg-white rounded-4 base-tr mt-30 {{$wrap_class ?? ''}}" data-x="flight-item-{{$flightId}}"
    data-x-toggle="shadow-{{$flightId}}">
    <div class="row y-gap-30 justify-between">
        <div class="col">
            @if(isset($originalData->itineraries))
            @foreach($originalData->itineraries as $itineraryIndex => $itinerary)

            @if($itineraryIndex > 0)
            <!-- Separator for return flight -->
            <div class="my-4 border-t-2 border-dashed border-gray-300"></div>
            @endif

            <!-- Flight direction label -->
            @if(count($originalData->itineraries) > 1)
            <div class="mb-3">
                @php
                $isMultiCity = count($originalData->itineraries) > 2;
                $colors = ['bg-blue-100 text-blue-800', 'bg-green-100 text-green-800', 'bg-purple-100 text-purple-800',
                'bg-orange-100 text-orange-800', 'bg-pink-100 text-pink-800'];
                $colorClass = $colors[$itineraryIndex % count($colors)] ?? 'bg-gray-100 text-gray-800';
                @endphp

                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $colorClass }}">
                    @if($isMultiCity)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    {{ __('Leg') }} {{ $itineraryIndex + 1 }}
                    @elseif($itineraryIndex === 0)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    {{ __('Outbound Flight') }}
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    {{ __('Return Flight') }}
                    @endif
                </span>
            </div>
            @endif

            <div class="row y-gap-10 items-center">
                @php
                // Calculate duration for this specific itinerary
                $itineraryDuration = $itinerary->duration ?? $duration;
                $itineraryInterval = new DateInterval($itineraryDuration);
                @endphp

                <div class="col-sm-auto flex flex-col md:flex-row items-center ">
                    <div
                        class="has-skeleton w-full flex md:flex-col gap-2 md:gap-0 items-center p-2 md:border-r md:border-gray-100">
                        @if($airlineImageUrl)
                        <div class="mb-1 w-[48px] h-[48px]">
                            <img class="shadow rounded-full p-0.5 bg-background shadow-primary/50 object-cover transition-opacity duration-[2s] group-hover:scale-105"
                                style="width:48px;height:48px;" src="{{$airlineImageUrl}}" alt="{{$airlineCode}}">
                        </div>
                        @endif
                        <div class="md:text-center md:max-w-[100px] truncate ">
                            <span title="Emirates"
                                class="text-xs font-medium text-primary text-center w-4 tracking-tight ellipsis">{{$airlineCode}}</span>
                            <div class="flex items-center mt-1 md:justify-center ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-clock h-3 w-3 text-gray-500 mr-1">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <p class="text-xs text-gray-600 font-medium">{{$itineraryInterval->h}}H
                                    {{$itineraryInterval->i}}M</p>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col">
                    <div class="row x-gap-20 items-end">
                        @php
                            dd($alldata);
                            // Handle both array and object formats
                            // Use first segment for departure and last segment for final arrival (end of flight)
                            $segments = $itinerary->segments ?? [];
                            $firstSegment = is_object($segments[0]) ? $segments[0] : (object)$segments[0];
                            $lastSegmentRaw = $segments[count($segments) - 1];
                            $lastSegment = is_object($lastSegmentRaw) ? $lastSegmentRaw : (object)$lastSegmentRaw;

                            $departure = is_object($firstSegment->departure) ? $firstSegment->departure : (object)$firstSegment->departure;
                            $finalArrival = is_object($lastSegment->arrival) ? $lastSegment->arrival : (object)$lastSegment->arrival;
                        @endphp

                        <div class="col-auto">
                            <div class="has-skeleton">
                                <div class="flex flex-col items-start w-auto">
                                    <div class="text-lg font-bold text-blue-950">{{$departure->iataCode}}</div>
                                    <div class="flex items-center text-xs text-gray-600 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-calendar h-3 w-3 mr-1">
                                            <path d="M8 2v4"></path>
                                            <path d="M16 2v4"></path>
                                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                            <path d="M3 10h18"></path>
                                        </svg>{{@date('d M y H:i', strtotime($departure->at))}}
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">
                                            <path
                                                d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                            </path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span class="truncate w-[97px] md:w-[190px]"
                                            title="Hazrat Shahjalal International Airport">{{substr(airport_from_code($departure->iataCode,''),
                                            0, 35) . '...' }}t</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col text-center">
                            <div class="flightLine">
                                <div></div>
                                <div></div>
                            </div>
                        </div>

                        <div class="col-auto">
                            <div class="has-skeleton">
                                <div class="flex flex-col items-start w-auto">
                                    <div class="text-lg font-bold text-blue-950">{{$finalArrival->iataCode}}</div>
                                    <div class="flex items-center text-xs text-gray-600 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-calendar h-3 w-3 mr-1">
                                            <path d="M8 2v4"></path>
                                            <path d="M16 2v4"></path>
                                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                            <path d="M3 10h18"></path>
                                        </svg>{{@date('d M y H:i', strtotime($finalArrival->at))}}
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">
                                            <path
                                                d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                            </path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span class="truncate w-[97px] md:w-[190px]"
                                            title="Hazrat Shahjalal International Airport">{{substr(airport_from_code($finalArrival->iataCode,''),
                                            0, 35) . '...' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <div class="col-md-auto">
            <div class="has-skeleton">
                <div class="d-flex items-center h-full">
                    <div class="pl-30 border-left-light h-full md:d-none"></div>

                    <div
                        class="md:col-span-3 w-full bg-primary/25 p-4 flex md:flex-col items-center md:items-start justify-center">
                        <div class="text-left mb-1 w-full flex-1 md:flex-0">
                            @php
                            $baseFare = $fareDetails['base_fare'] ?? 0;
                            $totalFare = $price;
                            $taxAmount = $fareDetails['tax'] ?? 0;

                            // Only show discount if there's an actual sale_price or promotional discount
                            // For real-time API flights, there's usually no discount
                            $discountAmount = 0;
                            $originalPrice = $totalFare;

                            // Check if row has sale_price or discount_amount from database
                            if (isset($row['sale_price']) && $row['sale_price'] > 0 && $row['sale_price'] < $totalFare) {
                                $discountAmount = $totalFare - $row['sale_price'];
                                $originalPrice = $totalFare;
                            } elseif (isset($row['discount_amount']) && $row['discount_amount'] > 0) {
                                $discountAmount = $row['discount_amount'];
                                $originalPrice = $totalFare + $discountAmount;
                            }

                            $formattedOriginalPrice = format_money($originalPrice);
                            $hasDiscount = $discountAmount > 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                @if($hasDiscount)
                                <div class="text-sm text-gray-500 line-through">{{$formattedOriginalPrice}}</div>
                                <span data-slot="badge"
                                    class="items-center justify-center bg-red-1 rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&amp;&gt;svg]:size-3 gap-1 [&amp;&gt;svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden border-transparent bg-destructive text-white [a&amp;]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60 mx-auto md:mx-0 my-1 hidden lg:flex">
                                    {{__('Save')}} {{format_money($discountAmount)}}
                                </span>
                                @endif
                            </div>

                            <div class="text-xl md:text-2xl font-bold text-blue-900">{{$formattedPrice}}</div>
                        </div>
                        <div class="flex flex-col flex-1 md:flex-0 w-full">
                            <div class="accordion__button">
                                @if($isBookable)
                                @php
                                    $requestData = $row['request_data'] ?? $request ?? [];
                                @endphp
                                <a data-id="{{$flightId}}" data-flightdata='@json($alldata)'
                                    data-requestdata='@json($requestData)' href="#" onclick="event.preventDefault()"
                                    class="button -dark-1 px-30 h-50 bg-red-1 text-white btn-choose-flight">
                                    {{__("Choose")}} <div class="icon-arrow-top-right ml-15"></div>
                                </a>
                                @else
                                <a href="#"
                                    class="button -dark-1 px-30 h-50 bg-warning-2 text-white btn-disabled">{{__("Full
                                    Book")}}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr />

    <div class="row y-gap-30 justify-between">
        <div class="col">
            <div class="row y-gap-10 items-center">
                <div class="col-sm-auto">
                    <div class="has-skeleton">
                        <div class="text-15 lh-15 text-light-1">Refundable</div>
                    </div>
                </div>
                <div class="col">
                    <div class="row x-gap-20 items-end">
                        <div class="col-auto">
                            <div class="has-skeleton">
                                <div class="text-15 lh-15 text-light-1"></div>
                            </div>
                        </div>

                        <div class="col text-center">
                            <div class="has-skeleton">
                                <div class="text-15 lh-15 text-light-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-auto">
                    <div class="text-15 text-light-1 px-20 md:px-0 has-skeleton text-red-1">
                        <a onclick="showHideDetails({{$flightId}})" href="javascript:void(0)"
                            id="showHideDetailsA-{{$flightId}}">{{__('Show Details')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr />

    <div class="row y-gap-30 justify-between" id="showHideDetailsDiv-{{$flightId}}" style="display:none;">
        <div class="col">
            <div class="row y-gap-10 items-center">
                <div data-anim-child="slide-up delay-6"
                    class="shopno-tabs-search-result tabs -underline mt-10 js-tabs is-in-view">
                    <div class="tabs__controls d-flex sm:justify-start js-tabs-controls shopno_tabs_controls">
                        <div class="">
                            <button
                                class="tabs__button text-15 fw-500 text-white pb-4 js-tabs-button is-tab-el-active shopno-flight-details shopno_tabs__button"
                                data-tab-target=".-tab-item-flight-details-{{$flightId}}">
                                {{__('Flight Details')}}
                            </button>
                        </div>
                        <div class="">
                            <button
                                class="tabs__button text-15 fw-500 text-white pb-4 js-tabs-button shopno-fare-details shopno_tabs__button"
                                data-tab-target=".-tab-item-fare-details-{{$flightId}}">
                                {{__('Fare Details')}}
                            </button>
                        </div>
                        <div class="">
                            <button
                                class="tabs__button text-15 fw-500 text-white pb-4 js-tabs-button shopno-fair-policy shopno_tabs__button"
                                data-tab-target=".-tab-item-fair-policy-{{$flightId}}">
                                {{__('Fare Policy')}}
                            </button>
                        </div>
                        <div class="">
                            <button
                                class="tabs__button text-15 fw-500 text-white pb-4 js-tabs-button shopno-beggage shopno_tabs__button"
                                data-tab-target=".-tab-item-baggage-{{$flightId}}">
                                {{__('Baggage')}}
                            </button>
                        </div>
                    </div>

                    <div class="tabs__content mt-30 md:mt-20 js-tabs-content">
                        {{-- Flight Details Tab --}}
                        <div class="tabs__pane -tab-item-flight-details-{{$flightId}} is-tab-el-active">
                            @if(isset($alldata['itineraries']))
                            @foreach($alldata['itineraries'] as $itinIndex => $itinData)
                            @if($itinIndex > 0)
                            <div class="mt-4 mb-3 pt-3 border-t-2 border-dashed border-gray-300">
                                <h5 class="font-bold text-blue-900">
                                    @if($itinIndex === 1)
                                    {{ __('Return Flight') }}
                                    @else
                                    {{ __('Flight') }} {{ $itinIndex + 1 }}
                                    @endif
                                </h5>
                            </div>
                            @else
                            @if(count($alldata['itineraries']) > 1)
                            <h5 class="font-bold text-blue-900 mb-3">{{ __('Outbound Flight') }}</h5>
                            @endif
                            @endif

                            <table cellpadding="5" width="100%" border="1" class="mb-3">
                                <tr>
                                    <th style="border:1px solid #EEE">{{__('Flight')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Departure')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Arrival')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Duration')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Aircraft')}}</th>
                                </tr>
                                @foreach($itinData['segments'] as $segment)
                                <tr>
                                    <td style="border:1px solid #EEE">
                                        @php
                                        $carrierCode = $segment['carrierCode'] ?? $airlineCode;
                                        $flightNumber = $segment['number'] ?? '';
                                        // Ensure we have strings, not arrays
                                        if (is_array($carrierCode)) $carrierCode = $carrierCode[0] ?? $airlineCode;
                                        if (is_array($flightNumber)) $flightNumber = $flightNumber[0] ?? '';
                                        echo $carrierCode . ' ' . $flightNumber;
                                        @endphp
                                    </td>
                                    <td style="border:1px solid #EEE">
                                        @php
                                        $depIataCode = isset($segment['departure']['iataCode']) ?
                                        $segment['departure']['iataCode'] : $departureCode;
                                        if (is_array($depIataCode)) $depIataCode = $depIataCode[0] ?? $departureCode;
                                        $depAt = isset($segment['departure']['at']) ? $segment['departure']['at'] : '';
                                        if (is_array($depAt)) $depAt = $depAt[0] ?? '';
                                        echo $depIataCode . ' - ';
                                        echo $depAt ? date('d M y H:i', strtotime($depAt)) : '';
                                        @endphp
                                    </td>
                                    <td style="border:1px solid #EEE">
                                        @php
                                        $arrIataCode = isset($segment['arrival']['iataCode']) ?
                                        $segment['arrival']['iataCode'] : $arrivalCode;
                                        if (is_array($arrIataCode)) $arrIataCode = $arrIataCode[0] ?? $arrivalCode;
                                        $arrAt = isset($segment['arrival']['at']) ? $segment['arrival']['at'] : '';
                                        if (is_array($arrAt)) $arrAt = $arrAt[0] ?? '';
                                        echo $arrIataCode . ' - ';
                                        echo $arrAt ? date('d M y H:i', strtotime($arrAt)) : '';
                                        @endphp
                                    </td>
                                    <td style="border:1px solid #EEE">
                                        @php
                                        if(isset($segment['duration'])) {
                                        try {
                                        $segDuration = new DateInterval($segment['duration']);
                                        echo $segDuration->h . 'H ' . $segDuration->i . 'M';
                                        } catch (Exception $e) {
                                        echo 'N/A';
                                        }
                                        } else {
                                        echo 'N/A';
                                        }
                                        @endphp
                                    </td>
                                    <td style="border:1px solid #EEE">
                                        @php
                                        $aircraftCode = isset($segment['aircraft']['code']) ?
                                        $segment['aircraft']['code'] : 'N/A';
                                        if (is_array($aircraftCode)) $aircraftCode = $aircraftCode[0] ?? 'N/A';
                                        echo $aircraftCode;
                                        @endphp
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endforeach
                            @else
                            {{-- Fallback to old format --}}
                            <table cellpadding="5" width="100%" border="1">
                                <tr>
                                    <th style="border:1px solid #EEE">{{__('Flight')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Departure')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Arrival')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Duration')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Aircraft')}}</th>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #EEE">{{$flightDetails['carrier_code'] ?? $airlineCode}}
                                        {{$flightDetails['flight_number'] ?? ''}}</td>
                                    <td style="border:1px solid #EEE">{{$flightDetails['departure_iata_code'] ??
                                        $departureCode}} - {{$flightDetails['departure_formatted_date'] ?? date('d M y
                                        H:i', strtotime($departureTime))}}</td>
                                    <td style="border:1px solid #EEE">{{$flightDetails['arrival_iata_code'] ??
                                        $arrivalCode}} - {{$flightDetails['arrival_formatted_date'] ?? date('d M y H:i',
                                        strtotime($arrivalTime))}}</td>
                                    <td style="border:1px solid #EEE">{{$flightDetails['duration_formatted'] ??
                                        $duration}}</td>
                                    <td style="border:1px solid #EEE">{{$flightDetails['aircraft_code'] ?? 'N/A'}}</td>
                                </tr>
                            </table>
                            @endif
                        </div>

                        {{-- Fare Details Tab (UPDATED: Shows breakdown per passenger type) --}}
                        <div class="tabs__pane -tab-item-fare-details-{{$flightId}}">

                            <table cellpadding="8" width="100%" border="0" class="border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th style="border:1px solid #DDD; text-align:left; padding:12px">{{__('Passenger Type')}}</th>
                                        <th style="border:1px solid #DDD; text-align:center">{{__('Count')}}</th>
                                        <th style="border:1px solid #DDD; text-align:right">{{__('Base Fare')}} <small>({{__('Total')}})</small></th>
                                        <th style="border:1px solid #DDD; text-align:right">{{__('Taxes & Fees')}} <small>({{__('Total')}})</small></th>
                                        <th style="border:1px solid #DDD; text-align:right">{{__('Total per Person')}}</th>
                                        <th style="border:1px solid #DDD; text-align:right; background:#f0f9ff">{{__('Subtotal')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $passengerBreakdown = $fareDetails['passenger_breakdown'] ?? $row['passenger_type_pricing'] ?? [];
                                        $grandBaseFare = 0;
                                        $grandTax = 0;
                                        $grandTotal = 0;
                                    @endphp

                                    @if(!empty($passengerBreakdown))
                                        @foreach($passengerBreakdown as $paxType)
                                            @php
                                                $count = $paxType['count'] ?? 1;
                                                $baseFare = $paxType['base_fare'] ?? 0; // Per person
                                                $tax = $paxType['tax'] ?? 0; // Per person
                                                $total = $paxType['total'] ?? 0; // Per person

                                                // Use pre-calculated totals if available, otherwise calculate
                                                $subtotal = $paxType['total_total'] ?? ($total * $count);
                                                $baseFareTotal = $paxType['base_fare_total'] ?? ($baseFare * $count);
                                                $taxTotal = $paxType['tax_total'] ?? ($tax * $count);

                                                $grandBaseFare += $baseFareTotal;
                                                $grandTax += $taxTotal;
                                                $grandTotal += $subtotal;
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td style="border:1px solid #EEE; padding:10px">
                                                    <div class="font-medium text-gray-900">{{ $paxType['type_label'] ?? $paxType['type'] }}</div>
                                                    @if(isset($paxType['type']) && in_array($paxType['type'], ['C03', 'C07']))
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            @if($paxType['type'] === 'C03')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                    {{__('No UT3 Tax')}}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                    {{__('With UT3 Tax')}}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="border:1px solid #EEE; text-align:center; padding:10px">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-semibold">
                                                        {{ $count }}
                                                    </span>
                                                </td>
                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                    {{ format_money($baseFareTotal) }}
                                                </td>
                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                    {{ format_money($taxTotal) }}
                                                </td>
                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                    {{ $paxType['total_formatted'] ?? format_money($total) }}
                                                </td>
                                                <td style="border:1px solid #EEE; text-align:right; padding:10px; background:#f0f9ff; font-weight:600">
                                                    {{ $paxType['subtotal_formatted'] ?? format_money($subtotal) }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Grand Total Row -->
                                        <tr class="bg-blue-50 font-bold">
                                            <td colspan="2" style="border:1px solid #CCC; padding:12px; text-align:right">
                                                {{__('Grand Total')}}
                                            </td>
                                            <td style="border:1px solid #CCC; text-align:right; padding:12px">
                                                {{ format_money($grandBaseFare) }}
                                            </td>
                                            <td style="border:1px solid #CCC; text-align:right; padding:12px">
                                                {{ format_money($grandTax) }}
                                            </td>
                                            <td style="border:1px solid #CCC; text-align:right; padding:12px">
                                                -
                                            </td>
                                            <td style="border:1px solid #CCC; text-align:right; padding:12px; background:#dbeafe; font-size:1.1em">
                                                {{ format_money($grandTotal) }}
                                            </td>
                                        </tr>
                                    @else
                                        <!-- Fallback to old single passenger display -->
                                        <tr>
                                            <td style="border:1px solid #EEE; padding:10px">{{$fareDetails['traveler_type'] ?? 'ADULT'}}</td>
                                            <td style="border:1px solid #EEE; text-align:center; padding:10px">1</td>
                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                {{$fareDetails['base_fare_formatted'] ?? format_money(0)}}
                                            </td>
                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                {{$fareDetails['tax_formatted'] ?? format_money(0)}}
                                            </td>
                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">
                                                {{$fareDetails['total_fare_formatted'] ?? format_money(0)}}
                                            </td>
                                            <td style="border:1px solid #EEE; text-align:right; padding:10px; background:#f0f9ff; font-weight:600">
                                                {{$fareDetails['total_fare_formatted'] ?? format_money(0)}}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>


                        </div>

                        {{-- Fare Policy Tab --}}
                        <div class="tabs__pane -tab-item-fair-policy-{{$flightId}}">
                            <div class="text-15">
                                <p>{{__('Refunds and Date Changes are done as per the following policies.')}}</p>
                                <p>{{__('Refund is calculated by deducting Airline\'s fee and ST fee from the paid
                                    amount.')}}</p>
                                <p>{{__('Date Change fee is calculated by adding Airline\'s fee, fare difference and ST
                                    fee.')}}</p>
                            </div>
                        </div>

                        {{-- Baggage Tab --}}
                        <div class="tabs__pane -tab-item-baggage-{{$flightId}}">
                            <table cellpadding="5" width="100%" border="1">
                                <tr>
                                    <th style="border:1px solid #EEE">{{__('Cabin')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Fare Basis')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Class')}}</th>
                                    <th style="border:1px solid #EEE">{{__('Baggage')}}</th>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #EEE">{{$baggageDetails['cabin'] ?? 'ECONOMY'}}</td>
                                    <td style="border:1px solid #EEE">{{$baggageDetails['fare_basis'] ?? 'N/A'}}</td>
                                    <td style="border:1px solid #EEE">{{$baggageDetails['class'] ?? 'Y'}}</td>
                                    <td style="border:1px solid #EEE">{{$baggageDetails['baggage'] ?? '20KG'}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden card layout (kept for reference) --}}
<div class="card w-100 shadow-hover-3 mb-4 d-none">
    <a href="#" class="d-block mb-0 mx-1 mt-1 p-3" tabindex="0">
        <img class="card-img-top" src="{{$airlineImageUrl}}" alt="{{$airlineCode}}">
    </a>
    <div class="card-body px-3 pt-0 pb-3 my-0 mx-1">
        <div class="row">
            <div class="col-7">
                <a href="#" class="card-title text-dark font-size-17 font-weight-bold"
                    tabindex="0">{{$departureCode}}</a>
            </div>
            <div class="col-5">
                <div class="text-right">
                    <h6 class="font-weight-bold font-size-17 text-gray-3 mb-0">{{$formattedPrice}}</h6>
                    <span class="font-weight-normal font-size-12 d-block text-color-1">{{__('avg/person')}}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class="border-bottom pb-3 mb-3">
            <div class="px-3">
                <div class="d-flex mx-1">
                    <i class="icofont-airplane font-size-30 text-primary mr-3"></i>
                    <div class="d-flex flex-column">
                        <span class="font-weight-normal text-gray-5">{{__('Take off')}}</span>
                        <span class="font-size-14 text-gray-1">{{$departureTime}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-bottom pb-3 mb-3">
            <div class="px-3">
                <div class="d-flex mx-1">
                    <i class="d-block rotate-90 icofont-airplane-alt font-size-30 text-primary mr-3"></i>
                    <div class="d-flex flex-column">
                        <span class="font-weight-normal text-gray-5">{{__('Landing')}}</span>
                        <span class="font-size-14 text-gray-1">{{$arrivalTime}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center pl-3 pr-3">
            @if($isBookable)
            <a data-id="{{$flightId}}" href="#" onclick="event.preventDefault()"
                class="btn btn-primary text-white btn-choose w-100">{{__("Choose")}}</a>
            @else
            <a href="#" class="btn btn-warning btn-disabled">{{__("Full Book")}}</a>
            @endif
        </div>
    </div>
</div>
