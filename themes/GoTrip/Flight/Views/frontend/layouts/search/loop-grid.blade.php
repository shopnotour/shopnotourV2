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
//dd($alldata['itineraries']);
//dd($alldata['passengerInfoList'][0]['passengerInfo']['fareComponents'][0]['segments']);
@endphp


{{--@if($mealCode)--}}
{{--    <div>--}}
{{--        <strong>Meal:</strong> {{ $mealCode == 'M' ? 'Meal Included' : $mealCode }}--}}
{{--    </div>--}}
{{--@endif--}}
{{--@php--}}
{{--    $alldata = $row['alldata'];--}}
{{--    $mealCodes = [];--}}

{{--    if(isset($alldata['passengerInfoList'])) {--}}
{{--        foreach($alldata['passengerInfoList'] as $passengerItem) {--}}
{{--            if(isset($passengerItem['passengerInfo']['fareComponents'])) {--}}
{{--                foreach($passengerItem['passengerInfo']['fareComponents'] as $fareComponent) {--}}
{{--                    if(isset($fareComponent['segments'])) {--}}
{{--                        foreach($fareComponent['segments'] as $segmentItem) {--}}
{{--                            if(isset($segmentItem['segment']['mealCode'])) {--}}
{{--                                $mealCodes[] = $segmentItem['segment']['mealCode'];--}}
{{--                            }--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            }--}}
{{--        }--}}
{{--    }--}}
{{--@endphp--}}

{{--@if(!empty($mealCodes))--}}
{{--    <div class="meal-info">--}}
{{--        <strong>Meal:</strong>--}}
{{--        @foreach($mealCodes as $code)--}}
{{--            <span>{{ $code }}</span>@if(!$loop->last), @endif--}}
{{--        @endforeach--}}
{{--    </div>--}}
{{--@else--}}
{{--    <div class="meal-info">--}}
{{--        <strong>Meal:</strong> Not specified--}}
{{--    </div>--}}
{{--@endif--}}
{{-- Display All Itineraries --}}
{{--@foreach($allFlightData as $flightData)--}}
{{--    <div class="itinerary-card" style="border: 2px solid #ddd; padding: 20px; margin-bottom: 30px; border-radius: 10px;">--}}

{{--        --}}{{-- Summary Header --}}
{{--        <div class="summary-header" style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">--}}
{{--            <h3>{{ $flightData['summary']['departure_airport'] }} → {{ $flightData['summary']['arrival_airport'] }}</h3>--}}
{{--            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 10px;">--}}
{{--                <div>--}}
{{--                    <strong>Stops:</strong> {{ $flightData['summary']['total_segments'] - 1 }}--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <strong>Flight Time:</strong> {{ $flightData['summary']['total_flight_time_formatted'] }}--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <strong>Layover Time:</strong> {{ $flightData['summary']['total_layover_time_formatted'] }}--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <strong>Total Duration:</strong> {{ $flightData['summary']['total_journey_time_formatted'] }}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Journey Details --}}
{{--        <div class="journey-details">--}}
{{--            @foreach($flightData['journey'] as $step)--}}

{{--                @if($step['type'] === 'departure')--}}
{{--                    <div style="padding: 10px; border-left: 4px solid #28a745; margin-bottom: 15px;">--}}
{{--                        <strong>🛫 Departure</strong><br>--}}
{{--                        <span style="font-size: 20px; font-weight: bold;">{{ $step['airport'] }}</span>--}}
{{--                        @if($step['terminal'])--}}
{{--                            <span style="color: #666;"> - Terminal {{ $step['terminal'] }}</span>--}}
{{--                        @endif--}}
{{--                        <br>--}}
{{--                        <span style="color: #666;">{{ $step['formatted_time'] }}</span>--}}
{{--                    </div>--}}

{{--                @elseif($step['type'] === 'flight')--}}
{{--                    <div style="padding: 15px; background: #e3f2fd; border-radius: 5px; margin-bottom: 15px;">--}}
{{--                        <strong>✈️ Flight {{ $step['segment_number'] }}</strong><br>--}}
{{--                        <div style="margin-top: 8px;">--}}
{{--                            <span style="font-size: 18px; font-weight: bold;">--}}
{{--                                {{ $step['carrier'] }} {{ $step['flight_number'] }}--}}
{{--                            </span>--}}
{{--                            <span style="color: #666; margin-left: 10px;">{{ $step['aircraft'] }}</span>--}}
{{--                        </div>--}}
{{--                        <div style="margin-top: 8px;">--}}
{{--                            {{ $step['from_airport'] }} → {{ $step['to_airport'] }}--}}
{{--                        </div>--}}
{{--                        <div style="margin-top: 5px; color: #666;">--}}
{{--                            Depart: {{ $step['departure_formatted'] }}<br>--}}
{{--                            Arrive: {{ $step['arrival_formatted'] }}--}}
{{--                        </div>--}}
{{--                        <div style="margin-top: 5px;">--}}
{{--                            <strong>Duration:</strong> {{ $step['duration_formatted'] }}--}}
{{--                            <span style="margin-left: 15px;"><strong>Class:</strong> {{ $step['booking_class'] }}</span>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                @elseif($step['type'] === 'layover')--}}
{{--                    <div style="padding: 10px; border-left: 4px solid #ff9800; margin-bottom: 15px; background: #fff3e0;">--}}
{{--                        <strong>⏱️ Layover at {{ $step['airport'] }}</strong><br>--}}
{{--                        <span style="color: #e65100; font-weight: bold;">{{ $step['duration_formatted'] }}</span>--}}
{{--                    </div>--}}

{{--                @elseif($step['type'] === 'arrival')--}}
{{--                    <div style="padding: 10px; border-left: 4px solid #dc3545; margin-bottom: 15px;">--}}
{{--                        <strong>🛬 Arrival</strong><br>--}}
{{--                        <span style="font-size: 20px; font-weight: bold;">{{ $step['airport'] }}</span>--}}
{{--                        @if($step['terminal'])--}}
{{--                            <span style="color: #666;"> - Terminal {{ $step['terminal'] }}</span>--}}
{{--                        @endif--}}
{{--                        <br>--}}
{{--                        <span style="color: #666;">{{ $step['formatted_time'] }}</span>--}}
{{--                    </div>--}}
{{--                @endif--}}

{{--            @endforeach--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endforeach--}}



<div class="py-30 px-30 bg-white rounded-4 base-tr mt-30 {{$wrap_class ?? ''}}" data-x="flight-item-{{$flightId}}"
    data-x-toggle="shadow-{{$flightId}}">
    <div class="row y-gap-30 justify-between">
        @php
        $totalSegments=0;
 @endphp
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

{{--                <div class="col-sm-auto flex flex-col md:flex-row items-center ">--}}
{{--                    <div--}}
{{--                        class="has-skeleton w-full flex md:flex-col gap-2 md:gap-0 items-center p-2 md:border-r md:border-gray-100">--}}
{{--                        @if($airlineImageUrl)--}}
{{--                        <div class="mb-1 w-[48px] h-[48px]">--}}
{{--                            <img class="shadow rounded-full p-0.5 bg-background shadow-primary/50 object-cover transition-opacity duration-[2s] group-hover:scale-105"--}}
{{--                                style="width:48px;height:48px;" src="{{$airlineImageUrl}}" alt="{{$airlineCode}}">--}}
{{--                        </div>--}}
{{--                        @endif--}}
{{--                        <div class="md:text-center md:max-w-[100px] truncate ">--}}
{{--                            <span title="Emirates"--}}
{{--                                class="text-xs font-medium text-primary text-center w-4 tracking-tight ellipsis">{{$airlineCode}}</span>--}}
{{--                            <div class="flex items-center mt-1 md:justify-center ">--}}
{{--                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"--}}
{{--                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"--}}
{{--                                    stroke-linejoin="round" class="lucide lucide-clock h-3 w-3 text-gray-500 mr-1">--}}
{{--                                    <circle cx="12" cy="12" r="10"></circle>--}}
{{--                                    <polyline points="12 6 12 12 16 14"></polyline>--}}
{{--                                </svg>--}}
{{--                                <p class="text-xs text-gray-600 font-medium">{{$itineraryInterval->h}}H--}}
{{--                                    {{$itineraryInterval->i}}M</p>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                    </div>--}}
{{--                </div>--}}
                <div class="col">
                    <div class="row x-gap-20 items-end">
{{--                        @php--}}
{{--                        // Handle both array and object formats--}}
{{--                        $firstSegment = is_object($itinerary->segments[0]) ? $itinerary->segments[0] :--}}
{{--                        (object)$itinerary->segments[0];--}}
{{--                        $departure = is_object($firstSegment->departure) ? $firstSegment->departure :--}}
{{--                        (object)$firstSegment->departure;--}}
{{--                        $arrival = is_object($firstSegment->arrival) ? $firstSegment->arrival :--}}
{{--                        (object)$firstSegment->arrival;--}}
{{--                        @endphp--}}

{{--                        <div class="col-auto">--}}
{{--                            <div class="has-skeleton">--}}
{{--                                <div class="flex flex-col items-start w-auto">--}}
{{--                                    <div class="text-lg font-bold text-blue-950">{{$departure->iataCode}}</div>--}}
{{--                                    <div class="flex items-center text-xs text-gray-600 mt-1">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                            stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                            class="lucide lucide-calendar h-3 w-3 mr-1">--}}
{{--                                            <path d="M8 2v4"></path>--}}
{{--                                            <path d="M16 2v4"></path>--}}
{{--                                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>--}}
{{--                                            <path d="M3 10h18"></path>--}}
{{--                                        </svg>{{@date('d M y H:i', strtotime($departure->at))}}--}}
{{--                                    </div>--}}
{{--                                    <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                            stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                            class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">--}}
{{--                                            <path--}}
{{--                                                d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">--}}
{{--                                            </path>--}}
{{--                                            <circle cx="12" cy="10" r="3"></circle>--}}
{{--                                        </svg>--}}
{{--                                        <span class="truncate w-[97px] md:w-[190px]"--}}
{{--                                            title="Hazrat Shahjalal International Airport">{{substr(airport_from_code($departure->iataCode,''),--}}
{{--                                            0, 35) . '...' }}t</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}




{{--                        <div class="col text-center">--}}
{{--                            <div class="flightLine">--}}
{{--                                <div></div>--}}
{{--                                <div></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        <div class="col-auto">--}}
{{--                            <div class="has-skeleton">--}}
{{--                                <div class="flex flex-col items-start w-auto">--}}
{{--                                    <div class="text-lg font-bold text-blue-950">{{$arrival->iataCode}}</div>--}}
{{--                                    <div class="flex items-center text-xs text-gray-600 mt-1">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                            stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                            class="lucide lucide-calendar h-3 w-3 mr-1">--}}
{{--                                            <path d="M8 2v4"></path>--}}
{{--                                            <path d="M16 2v4"></path>--}}
{{--                                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>--}}
{{--                                            <path d="M3 10h18"></path>--}}
{{--                                        </svg>{{@date('d M y H:i', strtotime($arrival->at))}}--}}
{{--                                    </div>--}}
{{--                                    <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                            stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                            class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">--}}
{{--                                            <path--}}
{{--                                                d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">--}}
{{--                                            </path>--}}
{{--                                            <circle cx="12" cy="10" r="3"></circle>--}}
{{--                                        </svg>--}}
{{--                                        <span class="truncate w-[97px] md:w-[190px]"--}}
{{--                                            title="Hazrat Shahjalal International Airport">{{substr(airport_from_code($arrival->iataCode,''),--}}
{{--                                            0, 35) . '...' }}t</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        =================================--}}
                        {{-- Replace the section from line ~200 onwards --}}
                        {{-- Keep everything above this, just replace the design part inside existing loop --}}

                        <div class="col">
                            <div class="row x-gap-20 items-end">
                                @php
                                    $segments = $itinerary->segments ?? [];

                                    // Get departure info (first segment's departure)
                                    $firstSegment = is_object($segments[0]) ? $segments[0] : (object)$segments[0];
                                    $departure = is_object($firstSegment->departure) ? $firstSegment->departure : (object)$firstSegment->departure;
                                    $departureAirport = $departure->iataCode ?? '';
                                    $departureTime = $firstSegment->departure_at ?? $departure->at ?? '';
                                    $departureTerminal = $departure->terminal ?? '';

                                    // Get arrival info (last segment's arrival)
                                    $lastSegment = is_object(end($segments)) ? end($segments) : (object)end($segments);
                                    $arrival = is_object($lastSegment->arrival) ? $lastSegment->arrival : (object)$lastSegment->arrival;
                                    $arrivalAirport = $arrival->iataCode ?? '';
                                    $arrivalTime = $lastSegment->arrival_at ?? $arrival->at ?? '';
                                    $arrivalTerminal = $arrival->terminal ?? '';
//dd($lastSegment);
                                    // Calculate total stops
                                    $totalStops = count($segments) - 1;
                                    $totalSegments += count($segments);
                                    // Collect layover information
                                    $layovers = [];
                                    foreach ($segments as $index => $segment) {
//                                        $totalSegments = $totalSegments+1;
                                        $seg = is_object($segment) ? $segment : (object)$segment;
                                        if ($index > 0) {
                                            $prevSegment = is_object($segments[$index - 1]) ? $segments[$index - 1] : (object)$segments[$index - 1];
                                            $prevArrivalTime = $prevSegment->arrival_at ?? (is_object($prevSegment->arrival) ? $prevSegment->arrival->at : $prevSegment->arrival['at']);
                                            $currentDepartureTime = $seg->departure_at ?? (is_object($seg->departure) ? $seg->departure->at : $seg->departure['at']);

                                            $prevArrival = new DateTime($prevArrivalTime);
                                            $currentDeparture = new DateTime($currentDepartureTime);

                                            $layoverDuration = $prevArrival->diff($currentDeparture);
                                            $layoverMinutes = ($layoverDuration->days * 24 * 60) + ($layoverDuration->h * 60) + $layoverDuration->i;

                                            $layovers[] = [
                                                'airport' => is_object($seg->departure) ? $seg->departure->iataCode : $seg->departure['iataCode'],
                                                'duration' => sprintf('%dh %dm', floor($layoverMinutes / 60), $layoverMinutes % 60),
                                                'duration_minutes' => $layoverMinutes
                                            ];
                                        }
                                    }
                                @endphp

                                {{-- Route Header --}}
{{--                                <div class="col-12 mb-3">--}}
{{--                                    <div class="flex items-center justify-between pb-3 border-b">--}}
{{--                                        <div class="flex items-center gap-3">--}}
{{--                                            <span class="text-lg font-bold text-blue-950">{{ $departureAirport }}</span>--}}
{{--                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"--}}
{{--                                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"--}}
{{--                                                 stroke-linejoin="round" class="text-gray-400 h-5 w-5">--}}
{{--                                                <path d="M5 12h14"></path>--}}
{{--                                                <path d="m12 5 7 7-7 7"></path>--}}
{{--                                            </svg>--}}
{{--                                            <span class="text-lg font-bold text-blue-950">{{ $arrivalAirport }}</span>--}}
{{--                                        </div>--}}

{{--                                        @if($totalStops > 0)--}}
{{--                                            <div class="text-sm text-orange-600 font-semibold">--}}
{{--                                                {{ $totalStops }} {{ $totalStops > 1 ? 'Stops' : 'Stop' }}--}}
{{--                                            </div>--}}
{{--                                        @else--}}
{{--                                            <div class="text-sm text-green-600 font-semibold">--}}
{{--                                                Non-Stop--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </div>--}}


                                {{-- Route Header --}}
                                <div class="flex items-center justify-between mb-4 pb-3 border-b">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-bold text-blue-950">{{ $departureAirport }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round" class="text-gray-400 h-5 w-5">
                                            <path d="M5 12h14"></path>
                                            <path d="m12 5 7 7-7 7"></path>
                                        </svg>
                                        <span class="text-lg font-bold text-blue-950">{{ $arrivalAirport }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        {{-- Meal Code Badge (NEW) --}}
{{--                                        @php--}}
{{--                                            // alldata থেকে access করুন--}}
{{--                                            $alldata = $row['alldata'];--}}
{{--                                            $mealCode = null;--}}
{{--                                        --}}
{{--                                            if(isset($alldata['passengerInfoList'][0]['passengerInfo']['fareComponents'][0]['segments'][0]['segment']['mealCode'])) {--}}
{{--                                                $mealCode = $alldata['passengerInfoList'][0]['passengerInfo']['fareComponents'][0]['segments'][0]['segment']['mealCode'];--}}
{{--                                            }--}}
{{--                                        @endphp--}}
                                        @php
                                            $alldata = $row['alldata'] ?? [];
                                            $mealCodes = [];

                                            // Collect all meal codes from all segments
                                            if(isset($alldata['passengerInfoList'])) {
                                                foreach($alldata['passengerInfoList'] as $passengerItem) {
                                                    if(isset($passengerItem['passengerInfo']['fareComponents'])) {
                                                        foreach($passengerItem['passengerInfo']['fareComponents'] as $fareComponent) {
                                                            if(isset($fareComponent['segments'])) {
                                                                foreach($fareComponent['segments'] as $segmentItem) {
                                                                    if(isset($segmentItem['segment']['mealCode'])) {
                                                                        $mealCodes[] = $segmentItem['segment']['mealCode'];
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // Remove duplicates
                                            $mealCodes = array_unique($mealCodes);

                                            // Meal code mapping function
                                            $getMealInfo = function($code) {
                                                return match($code) {
                                                    'M' => ['label' => 'Meal Included', 'icon' => '🍽️', 'color' => 'bg-green-100 text-green-700 border-green-200'],
                                                    'S' => ['label' => 'Snack', 'icon' => '🥪', 'color' => 'bg-blue-100 text-blue-700 border-blue-200'],
                                                    'N' => ['label' => 'No Meal', 'icon' => '🚫', 'color' => 'bg-gray-100 text-gray-700 border-gray-200'],
                                                    'B' => ['label' => 'Breakfast', 'icon' => '🍳', 'color' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                                    'L' => ['label' => 'Lunch', 'icon' => '🍱', 'color' => 'bg-orange-100 text-orange-700 border-orange-200'],
                                                    'D' => ['label' => 'Dinner', 'icon' => '🍽️', 'color' => 'bg-purple-100 text-purple-700 border-purple-200'],
                                                    'R' => ['label' => 'Refreshment', 'icon' => '☕', 'color' => 'bg-teal-100 text-teal-700 border-teal-200'],
                                                    'C' => ['label' => 'Cold Meal', 'icon' => '🥗', 'color' => 'bg-cyan-100 text-cyan-700 border-cyan-200'],
                                                    'H' => ['label' => 'Hot Meal', 'icon' => '🍲', 'color' => 'bg-red-100 text-red-700 border-red-200'],
                                                    default => null
                                                };
                                            };
                                        @endphp

                                        @if(!empty($mealCodes))
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($mealCodes as $code)
                                                    @php $mealInfo = $getMealInfo($code); @endphp
                                                    @if($mealInfo)
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border {{ $mealInfo['color'] }}">
                                                            <span class="text-sm">{{ $mealInfo['icon'] }}</span>
                                                            <span>{{ $mealInfo['label'] }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
{{--                                            <div class="flex flex-wrap gap-2">--}}
{{--                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border">--}}
{{--                                                            <span class="text-sm">{{ }}</span>--}}
{{--                                                            <span>No Meal</span>--}}
{{--                                                        </div>--}}
{{--                                            </div>--}}
                                        @endif

                                        {{-- Stop/Non-stop Badge --}}
                                        @if($totalStops > 0)
                                            <div class="text-sm text-orange-600 font-semibold px-3 py-1 rounded-full bg-orange-50 border border-orange-200">
                                                {{ $totalStops }} {{ $totalStops > 1 ? 'Stops' : 'Stop' }}
                                            </div>
                                        @else
                                            <div class="text-sm text-green-600 font-semibold px-3 py-1 rounded-full bg-green-50 border border-green-200">
                                                Non-Stop
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                {{-- Flight Route Timeline --}}
                                <div class="col-12">
                                    <div class="flex items-start justify-between gap-4">

                                        {{-- Airline Logo (NEW) --}}
                                        <div class="flex-shrink-0 hidden md:flex flex-col items-center">
                                            @if($airlineImageUrl)
                                                <div class="w-16 h-16 rounded-lg border border-gray-200 p-2 bg-white shadow-sm mb-2">
                                                    <img class="w-full h-full object-contain"
                                                         src="{{$airlineImageUrl}}"
                                                         alt="{{$airlineCode}}"
                                                         title="{{$airlineCode}}">
                                                </div>

                                                {{-- Total Duration below logo --}}
                                                <span class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                                                    {{$itineraryInterval->h}}h {{$itineraryInterval->i}}m
                                                </span>

                                                {{-- Airline code below duration --}}
                                                <span class="text-[10px] text-blue-600 font-medium mt-1">
                                                    {{$airlineCode}}
                                                </span>
                                            @endif
                                        </div>
{{--                                        <div class="flex-shrink-0 hidden md:flex items-center">--}}
{{--                                            @if($airlineImageUrl)--}}
{{--                                                <div class="w-16 h-16 rounded-lg border border-gray-200 p-2 bg-white shadow-sm">--}}
{{--                                                    <img class="w-full h-full object-contain"--}}
{{--                                                         src="{{$airlineImageUrl}}"--}}
{{--                                                         alt="{{$airlineCode}}"--}}
{{--                                                         title="{{$airlineCode}}">--}}
{{--                                                </div>--}}
{{--                                            @endif--}}
{{--                                        </div>--}}

                                        {{-- Departure Info --}}
                                        <div class="flex-shrink-0" style="width: 120px;">
                                            <div class="text-center">
                                                {{-- Mobile: Show airline logo here --}}
                                                @if($airlineImageUrl)
                                                    <div class="md:hidden mb-2 flex justify-center">
                                                        <img class="w-10 h-10 object-contain"
                                                             src="{{$airlineImageUrl}}"
                                                             alt="{{$airlineCode}}">
                                                    </div>
                                                @endif

                                                <div class="text-2xl font-bold text-blue-950">{{ $departureAirport }}</div>
                                                <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                    {{ airport_from_code($departureAirport, 'Departure') }}
                                                </div>
                                                <div class="flex items-center justify-center text-sm text-gray-700 mt-2 gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    {{ date('H:i', strtotime($departureTime)) }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ date('d M Y', strtotime($departureTime)) }}
                                                </div>
                                                @if($departureTerminal)
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        Terminal {{ $departureTerminal }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Flight Path --}}
                                        <div class="flex-1 flex flex-col items-center justify-center py-4">
                                            {{-- Flight Line with Stops --}}
                                            <div class="w-full relative">
                                                <div class="flex items-center justify-center">
                                                    {{-- Line --}}
                                                    <div class="flex-1 h-0.5 bg-blue-200 relative">
                                                        {{-- Plane Icon --}}
                                                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white px-2 flex flex-col items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                 class="text-blue-500 rotate-90">
                                                                <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"></path>
                                                            </svg>

                                                        </div>

                                                        {{-- Stop Dots --}}
                                                        @foreach($layovers as $index => $layover)
                                                            <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-orange-500 rounded-full border-2 border-white"
                                                                 style="left: {{ (($index + 1) / (count($layovers) + 1)) * 100 }}%"
                                                                 title="{{ $layover['airport'] }} - {{ $layover['duration'] }} layover">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Layover Details --}}
                                            @if(count($layovers) > 0)
                                                <div class="mt-3 flex flex-wrap gap-2 justify-center">
                                                    @foreach($layovers as $layover)
                                                        <div class="text-xs bg-orange-50 text-orange-700 px-3 py-1 rounded-full font-semibold border border-orange-200">
                                                            {{ $layover['airport'] }}: {{ $layover['duration'] }} layover
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Arrival Info --}}
                                        <div class="flex-shrink-0" style="width: 120px;">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-950">{{ $arrivalAirport }}</div>
                                                <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                    {{ airport_from_code($arrivalAirport, 'Arrival') }}
                                                </div>
                                                <div class="flex items-center justify-center text-sm text-gray-700 mt-2 gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    {{ date('H:i', strtotime($arrivalTime)) }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ date('d M Y', strtotime($arrivalTime)) }}
                                                </div>
                                                @if($arrivalTerminal)
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        Terminal {{ $arrivalTerminal }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .line-clamp-2 {
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            }
                        </style>
{{--                        @php--}}
{{--                        dd($alldata);--}}
{{--                            $itineraries = $alldata['itineraries'] ?? [];--}}

{{--                            // Process first itinerary (you can loop for all)--}}
{{--                            $itinerary = $itineraries[0] ?? [];--}}
{{--                            $segments = $itinerary['segments'] ?? [];--}}

{{--                            // Collect all airports with timing info--}}
{{--                            $airports = [];--}}
{{--                            $flightTimes = [];--}}

{{--                            foreach ($segments as $index => $segment) {--}}
{{--                                // Add departure airport (only for first segment)--}}
{{--                                if ($index === 0) {--}}
{{--                                    $airports[] = [--}}
{{--                                        'code' => $segment['departure']['iataCode'],--}}
{{--                                        'time' => $segment['departure_at'],--}}
{{--                                        'terminal' => $segment['departure']['terminal'] ?? '',--}}
{{--                                        'type' => 'departure',--}}
{{--                                    ];--}}
{{--                                }--}}

{{--                                // Calculate flight duration--}}
{{--                                $departureTimet = new DateTime($segment['departure_at']);--}}
{{--                                $arrivalTimet = new DateTime($segment['arrival_at']);--}}
{{--                                $flightDuration = $departureTimet->diff($arrivalTimet);--}}
{{--                                $flightMinutes = ($flightDuration->days * 24 * 60) + ($flightDuration->h * 60) + $flightDuration->i;--}}

{{--                                $flightTimes[] = [--}}
{{--                                    'type' => 'flight',--}}
{{--                                    'duration_minutes' => $flightMinutes,--}}
{{--                                    'duration_formatted' => sprintf('%dh %dm', floor($flightMinutes / 60), $flightMinutes % 60),--}}
{{--                                ];--}}

{{--                                // Calculate layover time if not first segment--}}
{{--                                if ($index > 0) {--}}
{{--                                    $prevSegment = $segments[$index - 1];--}}
{{--                                    $prevArrivalTime = new DateTime($prevSegment['arrival_at']);--}}

{{--                                    $layoverDuration = $prevArrivalTime->diff($departureTimet);--}}
{{--                                    $layoverMinutes = ($layoverDuration->days * 24 * 60) + ($layoverDuration->h * 60) + $layoverDuration->i;--}}

{{--                                    $flightTimes[] = [--}}
{{--                                        'type' => 'layover',--}}
{{--                                        'duration_minutes' => $layoverMinutes,--}}
{{--                                        'duration_formatted' => sprintf('%dh %dm', floor($layoverMinutes / 60), $layoverMinutes % 60),--}}
{{--                                    ];--}}
{{--                                }--}}

{{--                                // Add arrival airport (will be layover if not last segment)--}}
{{--                                $isLastSegment = ($index === count($segments) - 1);--}}
{{--                                $airports[] = [--}}
{{--                                    'code' => $segment['arrival']['iataCode'],--}}
{{--                                    'time' => $segment['arrival_at'],--}}
{{--                                    'terminal' => $segment['arrival']['terminal'] ?? '',--}}
{{--                                    'type' => $isLastSegment ? 'arrival' : 'layover',--}}
{{--                                ];--}}
{{--                            }--}}
{{--                        @endphp--}}

{{--                        <div class="row y-gap-10 items-center">--}}
{{--                            @php $timeIndex = 0; @endphp--}}

{{--                            @foreach($airports as $index => $airport)--}}
{{--                                @php--}}
{{--                                    // Get current flight time--}}
{{--                                    $currentTime = $flightTimes[$timeIndex] ?? null;--}}
{{--                                    $timeIndex++;--}}

{{--                                    // Check if next is layover--}}
{{--                                    $nextTime = isset($flightTimes[$timeIndex]) && $flightTimes[$timeIndex]['type'] === 'layover'--}}
{{--                                        ? $flightTimes[$timeIndex]--}}
{{--                                        : null;--}}

{{--                                    if ($nextTime) {--}}
{{--                                        $timeIndex++; // Skip layover for next iteration--}}
{{--                                    }--}}
{{--                                @endphp--}}
{{--                                --}}{{-- Airport Info --}}
{{--                                <div class="col-auto">--}}
{{--                                    <div class="has-skeleton">--}}
{{--                                        <div class="flex flex-col items-start w-auto {{ $airport['type'] === 'layover' ? 'relative' : '' }}">--}}

{{--                                            --}}{{-- Layover Badge --}}
{{--                                            @if($airport['type'] === 'layover')--}}
{{--                                                <div class="absolute -top-2 -right-2 bg-orange-500 text-white text-[10px] px-2 py-0.5 rounded-full font-semibold">--}}
{{--                                                    Stop--}}
{{--                                                </div>--}}
{{--                                            @endif--}}

{{--                                            <div class="text-lg font-bold {{ $airport['type'] === 'layover' ? 'text-orange-600' : 'text-blue-950' }}">--}}
{{--                                                {{ $airport['code'] }}--}}
{{--                                                <div class="absolute -top-2 -right-2 bg-orange-500 text-white text-[10px] px-2 py-0.5 rounded-full font-semibold">--}}
{{--                                                    Stop--}}

{{--                                                </div>--}}

{{--                                                @if($nextTime && $nextTime['type'] === 'layover')--}}
{{--                                                    <div class="text-xs text-orange-600 font-semibold mt-1">--}}
{{--                                                        ⏱️ {{ $nextTime['duration_formatted'] }} layover--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                            </div>--}}
{{--                                            <div class="flex items-center text-xs text-gray-600 mt-1">--}}
{{--                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                                     stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                                     class="lucide lucide-calendar h-3 w-3 mr-1">--}}
{{--                                                    <path d="M8 2v4"></path>--}}
{{--                                                    <path d="M16 2v4"></path>--}}
{{--                                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>--}}
{{--                                                    <path d="M3 10h18"></path>--}}
{{--                                                </svg>--}}
{{--                                                {{ date('d M y H:i', strtotime($airport['time'])) }}--}}
{{--                                            </div>--}}
{{--                                            <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">--}}
{{--                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                                     stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                                     class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">--}}
{{--                                                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>--}}
{{--                                                    <circle cx="12" cy="10" r="3"></circle>--}}
{{--                                                </svg>--}}
{{--                                                <span class="truncate w-[97px] md:w-[190px]"--}}
{{--                                                      title="{{ airport_from_code($airport['code'], '') }}">--}}
{{--                                                    {{ substr(airport_from_code($airport['code'], ''), 0, 35) . '...' }}--}}
{{--                                                </span>--}}

{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                --}}{{-- Flight Line with Duration --}}
{{--                                @if($index < count($airports) - 1)--}}


{{--                                    <div class="col text-center">--}}

{{--                                        --}}{{-- Layover Duration (if exists at this airport) --}}
{{--                                        @if($nextTime && $nextTime['type'] === 'layover')--}}
{{--                                            <div class="text-xs text-orange-600 font-semibold mt-1">--}}
{{--                                                ⏱️ {{ $nextTime['duration_formatted'] }} layover--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                        <div class="flightLine {{ $airport['type'] === 'layover' ? 'layover-line' : '' }}">--}}
{{--                                            <div></div>--}}
{{--                                            <div></div>--}}
{{--                                        </div>--}}

{{--                                        --}}{{-- Flight Duration --}}
{{--                                        @if($currentTime && $currentTime['type'] === 'flight')--}}
{{--                                            <div class="text-xs text-gray-600 font-semibold mt-1">--}}
{{--                                                ✈️ {{ $currentTime['duration_formatted'] }}--}}
{{--                                            </div>--}}
{{--                                        @endif--}}

{{--                                        --}}{{-- Layover Duration (if exists at this airport) --}}
{{--                                        @if($nextTime && $nextTime['type'] === 'layover')--}}
{{--                                            <div class="text-xs text-orange-600 font-semibold mt-1">--}}
{{--                                                ⏱️ {{ $nextTime['duration_formatted'] }} layover--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                @endif--}}

{{--                            @endforeach--}}
{{--                        </div>--}}

{{--                        =================================--}}

{{--                        @php--}}
{{--                            $itineraries = $alldata['itineraries'] ?? [];--}}

{{--                            // Process first itinerary (you can loop for all)--}}
{{--                            $itinerary = $itineraries[0] ?? [];--}}
{{--                            $segments = $itinerary['segments'] ?? [];--}}

{{--                            // Collect all unique airports in order--}}
{{--                            $airports = [];--}}
{{--                            foreach ($segments as $index => $segment) {--}}
{{--                                // Add departure airport (only for first segment)--}}
{{--                                if ($index === 0) {--}}
{{--                                    $airports[] = [--}}
{{--                                        'code' => $segment['departure']['iataCode'],--}}
{{--                                        'time' => $segment['departure_at'],--}}
{{--                                        'terminal' => $segment['departure']['terminal'] ?? '',--}}
{{--                                        'type' => 'departure',--}}
{{--                                    ];--}}
{{--                                }--}}

{{--                                // Add arrival airport (will be layover if not last segment)--}}
{{--                                $isLastSegment = ($index === count($segments) - 1);--}}
{{--                                $airports[] = [--}}
{{--                                    'code' => $segment['arrival']['iataCode'],--}}
{{--                                    'time' => $segment['arrival_at'],--}}
{{--                                    'terminal' => $segment['arrival']['terminal'] ?? '',--}}
{{--                                    'type' => $isLastSegment ? 'arrival' : 'layover',--}}
{{--                                ];--}}
{{--                            }--}}
{{--                        @endphp--}}

{{--                        <div class="row y-gap-10 items-center">--}}
{{--                            @foreach($airports as $index => $airport)--}}

{{--                                --}}{{-- Airport Info --}}
{{--                                <div class="col-auto">--}}
{{--                                    <div class="has-skeleton">--}}
{{--                                        <div class="flex flex-col items-start w-auto">--}}
{{--                                            <div class="text-lg font-bold text-blue-950">--}}
{{--                                                {{ $airport['code'] }}--}}
{{--                                                @if($airport['type'] === 'layover')--}}
{{--                                                    <span class="text-xs text-orange-600 ml-1">(Layover)</span>--}}
{{--                                                @endif--}}
{{--                                            </div>--}}
{{--                                            <div class="flex items-center text-xs text-gray-600 mt-1">--}}
{{--                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                                     stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                                     class="lucide lucide-calendar h-3 w-3 mr-1">--}}
{{--                                                    <path d="M8 2v4"></path>--}}
{{--                                                    <path d="M16 2v4"></path>--}}
{{--                                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>--}}
{{--                                                    <path d="M3 10h18"></path>--}}
{{--                                                </svg>--}}
{{--                                                {{ date('d M y H:i', strtotime($airport['time'])) }}--}}
{{--                                            </div>--}}
{{--                                            <div class="flex items-center text-xs text-gray-600 mt-1 max-w-xs truncate">--}}
{{--                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                                     stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                                     class="lucide lucide-map-pin h-3 w-3 mr-1 flex-shrink-0">--}}
{{--                                                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>--}}
{{--                                                    <circle cx="12" cy="10" r="3"></circle>--}}
{{--                                                </svg>--}}
{{--                                                <span class="truncate w-[97px] md:w-[190px]"--}}
{{--                                                      title="{{ airport_from_code($airport['code'], '') }}">--}}
{{--                            {{ substr(airport_from_code($airport['code'], ''), 0, 35) . '...' }}--}}
{{--                        </span>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                --}}{{-- Flight Line (Don't show after last airport) --}}
{{--                                @if($index < count($airports) - 1)--}}
{{--                                    <div class="col text-center">--}}
{{--                                        <div class="flightLine">--}}
{{--                                            <div></div>--}}
{{--                                            <div></div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}

{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                        ==============================--}}

{{--                        <div class="flight-route flex items-center flex-wrap gap-3">--}}
{{--                            @foreach($airports as $index => $airport)--}}

{{--                                --}}{{-- Airport Code --}}
{{--                                <div class="flex flex-col items-center">--}}
{{--                                    <div class="text-xl font-bold--}}
{{--                @if($airport['type'] === 'departure') text-green-600--}}
{{--                @elseif($airport['type'] === 'arrival') text-red-600--}}
{{--                @else text-orange-600--}}
{{--                @endif">--}}
{{--                                        {{ $airport['code'] }}--}}
{{--                                    </div>--}}
{{--                                    <div class="text-xs text-gray-500">--}}
{{--                                        {{ date('H:i', strtotime($airport['time'])) }}--}}
{{--                                    </div>--}}
{{--                                    @if($airport['type'] === 'layover')--}}
{{--                                        <span class="text-xs text-orange-600 font-medium">Layover</span>--}}
{{--                                    @endif--}}
{{--                                </div>--}}

{{--                                --}}{{-- Connector --}}
{{--                                @if($index < count($airports) - 1)--}}
{{--                                    <div class="flex items-center gap-1">--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-300"></div>--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-200"></div>--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"--}}
{{--                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"--}}
{{--                                             stroke-width="2" class="text-blue-500">--}}
{{--                                            <path d="M5 12h14"></path>--}}
{{--                                            <path d="m12 5 7 7-7 7"></path>--}}
{{--                                        </svg>--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-200"></div>--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-300"></div>--}}
{{--                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>--}}
{{--                                    </div>--}}
{{--                                @endif--}}

{{--                            @endforeach--}}
{{--                        </div>--}}
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

{{--        @php--}}
{{--            // ===== DISCOUNT CALCULATION =====--}}
{{--//    if (!isset($flight_discount) || empty($flight_discount)) {--}}
{{--        $flight_discounts = \Modules\Flight\Models\FlightDiscount::where('status','active')->get();--}}
{{--        $flight_charge = \Modules\Flight\Models\FlightCharge::where('status','active')->first();--}}
{{--//    } else {--}}
{{--//        $flight_discounts = $flight_discount;--}}
{{--//    }--}}
{{--//            $flight_discounts = $flight_discount ?? [];--}}
{{--//            $chargetype=$flight_charge->$type;--}}
{{--            $ait_charge=($fareDetails['total_fare'] * $flight_charge->ait_charge) / 100;--}}
{{--            $appliedDiscount = null;--}}
{{--            $discountAmount = 0;--}}
{{--            $originalPrice = $price;--}}
{{--            $finalPrice = $price;--}}
{{--             $type='percentage';--}}
{{--             $dis_val=0;--}}
{{--             $dis_id=0;--}}


{{--//dd(count($segments));--}}

{{--//            dd($fareDetails);--}}
{{--            // Check if any discount applies to this flight--}}
{{--            foreach ($flight_discounts as $discount) {--}}
{{--                // Check if discount matches flight criteria--}}
{{--                $matchesAirline = empty($discount->airline_code) || $discount->airline_code === $airlineCode;--}}
{{--                $matchesDeparture = empty($discount->departure_code) || $discount->departure_code === $departureCode;--}}
{{--                $matchesArrival = empty($discount->arrival_code) || $discount->arrival_code === $arrivalCode;--}}

{{--                // Check if discount is active and valid--}}
{{--                $isActive = $discount->status === 'active';--}}
{{--                $isValid = (empty($discount->valid_from) || strtotime($discount->valid_from) <= time()) &&--}}
{{--                           (empty($discount->valid_to) || strtotime($discount->valid_to) >= time());--}}

{{--                // If all conditions match, apply discount--}}
{{--                if ($matchesAirline && $matchesDeparture && $matchesArrival && $isActive && $isValid) {--}}
{{--                    $appliedDiscount = $discount;--}}
{{--                    $dis_id=$discount->id;--}}

{{--                    // Calculate discount amount--}}
{{--                    if ($discount->type === 'percentage') {--}}
{{--                        $discountAmount = ($fareDetails['base_fare'] * $discount->value) / 100;--}}
{{--                        $type='percentage';--}}
{{--                        $dis_val=$discount->value;--}}
{{--                    } else {--}}
{{--                        $discountAmount = $discount->value;--}}
{{--                        $type='fixed';--}}
{{--                        $dis_val=$discount->value;--}}
{{--                    }--}}

{{--                    $finalPrice = ($fareDetails['base_fare'] - $discountAmount) + $fareDetails['tax'];--}}
{{--//                    dd($fareDetails);--}}
{{--                    break; // Apply first matching discount only--}}
{{--                }--}}

{{--            }--}}
{{--            $finalPrice=($finalPrice+$ait_charge+$flight_charge->service_charge) - ($totalSegments * $flight_charge->segment_discount);--}}

{{--            $hasDiscount = $discountAmount > 0;--}}
{{--        @endphp--}}
{{--        <div class="col-md-auto">--}}
{{--            <div class="has-skeleton">--}}
{{--                <div class="d-flex items-center h-full">--}}
{{--                    <div class="pl-30 border-left-light h-full md:d-none"></div>--}}

{{--                    <div class="md:col-span-3 w-full bg-primary/25 p-4 flex md:flex-col items-center md:items-start justify-center">--}}
{{--                        <div class="text-left mb-1 w-full flex-1 md:flex-0">--}}

{{--                            --}}{{-- Discount Badge --}}
{{--                            @if($hasDiscount)--}}
{{--                                <div class="mb-2">--}}
{{--                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white">--}}
{{--                                @if($type == 'percentage')--}}
{{--                                    🎉 {{ $dis_val }}% OFF--}}
{{--                                @else--}}
{{--                                    🎉 {{ $dis_val }} taka OFF--}}
{{--                                @endif--}}

{{--                            </span>--}}
{{--                                    @if($appliedDiscount->name)--}}
{{--                                        <div class="text-xs text-blue-600 mt-1">{{ $appliedDiscount->name }}</div>--}}
{{--                                    @endif--}}
{{--                                </div>--}}

{{--                                --}}{{-- Price Breakdown Card --}}
{{--                                <div class="bg-blue-50 p-3 rounded-lg mb-2">--}}
{{--                                    <div class="flex justify-between mb-1 text-xs">--}}
{{--                                        <span class="text-gray-600">Original:</span>--}}
{{--                                        <span class="line-through text-gray-500">{{ format_money($originalPrice) }}</span>--}}
{{--                                    </div>--}}
{{--                                    <div class="flex justify-between mb-1 text-xs text-red-600">--}}
{{--                                        <span>Discount:</span>--}}
{{--                                        <span class="font-bold">-{{ format_money($discountAmount) }}</span>--}}
{{--                                    </div>--}}
{{--                                    <div class="flex justify-between pt-2 border-t">--}}
{{--                                        <span class="text-sm font-semibold">Final Price:</span>--}}
{{--                                        <span class="text-xl font-bold text-green-600">{{ format_money($finalPrice) }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @else--}}
{{--                                --}}{{-- No Discount - Show Regular Price --}}
{{--                                <div class="text-xl md:text-2xl font-bold text-blue-900">{{$formattedPrice}}</div>--}}
{{--                            @endif--}}

{{--                        </div>--}}

{{--                        --}}{{-- Choose Button --}}
{{--                        <div class="flex flex-col flex-1 md:flex-0 w-full">--}}
{{--                            <div class="accordion__button">--}}
{{--                                @if($isBookable)--}}
{{--                                    @php--}}
{{--                                        $requestData = $row['request_data'] ?? $request ?? [];--}}
{{--                                    @endphp--}}
{{--                                    <a data-id="{{$flightId}}"--}}
{{--                                       data-flightdata='@json($alldata)'--}}
{{--                                       data-requestdata='@json($requestData)'--}}
{{--                                       data-final-price="{{$finalPrice}}"--}}
{{--                                       data-discount="{{$discountAmount}}"--}}
{{--                                       href="#"--}}
{{--                                       onclick="event.preventDefault()"--}}
{{--                                       class="button -dark-1 px-30 h-50 {{ $hasDiscount ? 'bg-green-600' : 'bg-red-1' }} text-white btn-choose-flight">--}}
{{--                                        {{__("Choose")}} <div class="icon-arrow-top-right ml-15"></div>--}}
{{--                                    </a>--}}
{{--                                @else--}}
{{--                                    <a href="#" class="button -dark-1 px-30 h-50 bg-warning-2 text-white btn-disabled">{{__("Full Book")}}</a>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--==================================================================--}}
        @php
            // ===== DISCOUNT CALCULATION =====
            $flight_discounts = \Modules\Flight\Models\FlightDiscount::where('status','active')->get();
            $flight_charge = \Modules\Flight\Models\FlightCharge::where('status','active')->first();

            // Original airline price (base + tax)
            $airlineOriginalPrice = $fareDetails['base_fare'] + $fareDetails['tax'];

            $appliedDiscount = null;
            $discountAmount = 0;
            $totaldiscount = 0;
            $type = 'percentage';
            $dis_val = 0;
            $dis_id = 0;

            // Check if any discount applies to this flight
            foreach ($flight_discounts as $discount) {
                $matchesAirline = empty($discount->airline_code) || $discount->airline_code === $airlineCode;
                $matchesDeparture = empty($discount->departure_code) || $discount->departure_code === $departureCode;
                $matchesArrival = empty($discount->arrival_code) || $discount->arrival_code === $arrivalCode;
                $isActive = $discount->status === 'active';
                $isValid = (empty($discount->valid_from) || strtotime($discount->valid_from) <= time()) &&
                           (empty($discount->valid_to) || strtotime($discount->valid_to) >= time());

                if ($matchesAirline && $matchesDeparture && $matchesArrival && $isActive && $isValid) {
                    $appliedDiscount = $discount;
                    $dis_id = $discount->id;

                    if ($discount->type === 'percentage') {
                        $discountAmount = ($fareDetails['base_fare'] * $discount->value) / 100;
                        $type = 'percentage';
                        $dis_val = $discount->value;
                    } else {
                        $discountAmount = $discount->value;
                        $type = 'fixed';
                        $dis_val = $discount->value;
                    }
                    break;
                }
            }

            // Calculate charges
            $baseFareAfterDiscount = $fareDetails['base_fare'] - $discountAmount;
            $ait_charge = ($fareDetails['total_fare'] * $flight_charge->ait_charge) / 100;
            $service_charge = $flight_charge->service_charge;
            $segment_discount = $totalSegments * $flight_charge->segment_discount;
            $subtotal = $fareDetails['base_fare'] + $fareDetails['tax']+ $ait_charge + $service_charge;
            $totaldiscount=$segment_discount + $discountAmount;
            // Final price calculation
            $finalPrice = $subtotal - $totaldiscount;

            $hasDiscount = $discountAmount > 0;
        @endphp

        <div class="col-md-auto">
            <div class="has-skeleton">
                <div class="d-flex items-center h-full">
                    <div class="pl-30 border-left-light h-full md:d-none"></div>

                    <div class="md:col-span-3 w-full bg-primary/25 p-4 flex md:flex-col items-center md:items-start justify-center">
                        <div class="text-left mb-1 w-full flex-1 md:flex-0">

                            {{-- Discount Badge --}}
                            @if($hasDiscount)
                                <div class="mb-3 text-center">
{{--                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white animate-pulse">--}}
{{--                                @if($type == 'percentage')--}}
{{--                                    🎉 SAVE {{ $dis_val }}% NOW!--}}
{{--                                @else--}}
{{--                                    🎉 SAVE {{ format_money($dis_val) }} NOW!--}}
{{--                                @endif--}}
{{--                            </span>--}}
{{--                                    @if($appliedDiscount->name)--}}
{{--                                        <div class="text-xs text-blue-700 mt-1 font-medium">{{ $appliedDiscount->name }}</div>--}}
{{--                                    @endif--}}
                                </div>
                            @endif

                            {{-- Price Breakdown Card --}}
                            <div class="bg-white border-2 border-gray-200 rounded-lg p-4 shadow-md">

                                {{-- Airline Original Price (Strikethrough) --}}
                                <div class="flex justify-between items-center mb-3 pb-3 border-b-2 border-dashed border-gray-300">
                                    <span class="text-sm font-semibold text-gray-500">Airline Price:</span>
                                    <span class="text-lg font-bold text-gray-400 line-through">
                                {{ format_money($airlineOriginalPrice) }}
                            </span>
                                </div>

                                {{-- Calculation Steps --}}
                                <div class="space-y-2 mb-3">

                                    {{-- Base Fare --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700">Base Fare</span>
                                        <span class="text-sm font-medium text-gray-900">{{ format_money($fareDetails['base_fare']) }}</span>
                                    </div>

                                    {{-- Tax --}}
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700">+ Tax</span>
                                        <span class="text-sm font-medium text-gray-900">{{ format_money($fareDetails['tax']) }}</span>
                                    </div>
                                    {{-- AIT Charge --}}
                                    @if($ait_charge > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-700">+ AIT ({{ $flight_charge->ait_charge }}% on Airline Price)</span>
                                            <span class="text-sm font-medium text-gray-900">{{ format_money($ait_charge) }}</span>
                                        </div>
                                    @endif

                                    {{-- Service Charge --}}
                                    @if($service_charge > 0)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-700">+ Service Charge</span>
                                            <span class="text-sm font-medium text-gray-900">{{ format_money($service_charge) }}</span>
                                        </div>
                                    @endif

                                    {{-- Subtotal --}}
                                    <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                        <span class="text-sm font-medium text-gray-700">Subtotal</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ format_money($subtotal) }}</span>
                                    </div>
                                    {{-- Discount (if applicable) --}}
                                    @if($hasDiscount)
                                        <div class="flex justify-between items-center bg-red-50 -mx-2 px-2 py-1 rounded">
                                    <span class="text-sm font-semibold text-red-600">
                                        - Discount
                                        @if($type == 'percentage')
                                            ({{ $dis_val }}% on Base)
                                        @endif
                                    </span>
                                            <span class="text-sm font-bold text-red-600">-{{ format_money($discountAmount) }}</span>
                                        </div>
                                    @endif


                                    {{-- Segment Discount --}}
                                    @if($segment_discount > 0)
                                        <div class="flex justify-between items-center bg-green-50 -mx-2 px-2 py-1 rounded">
                                    <span class="text-sm font-semibold text-green-700">
                                        - Segment Discount ( {{ $totalSegments }} × {{ format_money($flight_charge->segment_discount ) }})
                                    </span>
                                            <span class="text-sm font-bold text-green-600">-{{ format_money($segment_discount) }}</span>
                                        </div>
                                    @endif
                                    @if($totaldiscount)
                                        {{-- Subtotal --}}
                                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                            <span class="text-sm font-medium text-gray-700">Total Discount</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ format_money($totaldiscount) }}</span>
                                        </div>
                                    @endif

                                </div>

                                {{-- Final Price --}}
                                <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-gray-400">
                                    <span class="text-base font-bold text-gray-900">You Pay:</span>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-{{ $hasDiscount ? 'green' : 'blue' }}-600">
                                            {{ format_money($finalPrice) }}
                                        </div>
{{--                                        @if($hasDiscount)--}}
{{--                                            <div class="text-xs text-green-600 font-medium">--}}
{{--                                                You saved {{ format_money($discountAmount) }}! 🎉--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
                                    </div>
                                </div>

                            </div>

                        </div>

                        {{-- Choose Button --}}
                        <div class="flex flex-col flex-1 md:flex-0 w-full mt-3">
                            <div class="accordion__button">
                                @if($isBookable)
                                    @php
                                        $requestData = $row['request_data'] ?? $request ?? [];
                                    @endphp
                                    <a data-id="{{$flightId}}"
                                       data-flightdata='@json($alldata)'
                                       data-requestdata='@json($requestData)'
                                       data-final-price="{{$finalPrice}}"
                                       data-discount="{{$totaldiscount}}"
                                       href="#"
                                       onclick="event.preventDefault()"
                                       class="button -dark-1 px-30 h-50 w-100 {{ $hasDiscount ? 'bg-green-600 hover:bg-green-700' : 'bg-red-1 hover:bg-red-2' }} text-white btn-choose-flight transition-all">
                                        <span class="font-bold">{{__("Choose This Flight")}}</span>
                                        <div class="icon-arrow-top-right ml-15"></div>
                                    </a>
                                @else
                                    <a href="#" class="button -dark-1 px-30 h-50 w-100 bg-warning-2 text-white btn-disabled">{{__("Fully Booked")}}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--==================================================================--}}
{{--        <div class="col-md-auto">--}}
{{--            <div class="has-skeleton">--}}
{{--                <div class="d-flex items-center h-full">--}}
{{--                    <div class="pl-30 border-left-light h-full md:d-none"></div>--}}

{{--                    <div--}}
{{--                        class="md:col-span-3 w-full bg-primary/25 p-4 flex md:flex-col items-center md:items-start justify-center">--}}
{{--                        <div class="text-left mb-1 w-full flex-1 md:flex-0">--}}
{{--                            @php--}}
{{--                                $baseFare = $fareDetails['base_fare'] ?? 0;--}}
{{--                                $totalFare = $price;--}}
{{--                                $taxAmount = $fareDetails['tax'] ?? 0;--}}

{{--                                // Only show discount if there's an actual sale_price or promotional discount--}}
{{--                                // For real-time API flights, there's usually no discount--}}
{{--                                $discountAmount = 0;--}}
{{--                                $originalPrice = $totalFare;--}}

{{--                                // Check if row has sale_price or discount_amount from database--}}
{{--                                if (isset($row['sale_price']) && $row['sale_price'] > 0 && $row['sale_price'] < $totalFare) {--}}
{{--                                    $discountAmount = $totalFare - $row['sale_price'];--}}
{{--                                    $originalPrice = $totalFare;--}}
{{--                                } elseif (isset($row['discount_amount']) && $row['discount_amount'] > 0) {--}}
{{--                                    $discountAmount = $row['discount_amount'];--}}
{{--                                    $originalPrice = $totalFare + $discountAmount;--}}
{{--                                }--}}

{{--                                $formattedOriginalPrice = format_money($originalPrice);--}}
{{--                                $hasDiscount = $discountAmount > 0;--}}
{{--                            @endphp--}}
{{--                            <div class="flex items-center gap-3">--}}
{{--                                @if($hasDiscount)--}}
{{--                                <div class="text-sm text-gray-500 line-through">{{$formattedOriginalPrice}}</div>--}}
{{--                                <span data-slot="badge"--}}
{{--                                    class="items-center justify-center bg-red-1 rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&amp;&gt;svg]:size-3 gap-1 [&amp;&gt;svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-hidden border-transparent bg-destructive text-white [a&amp;]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60 mx-auto md:mx-0 my-1 hidden lg:flex">--}}
{{--                                    {{__('Save')}} {{format_money($discountAmount)}}--}}
{{--                                </span>--}}
{{--                                @endif--}}
{{--                            </div>--}}

{{--                            <div class="text-xl md:text-2xl font-bold text-blue-900">{{$formattedPrice}}</div>--}}
{{--                        </div>--}}
{{--                        <div class="flex flex-col flex-1 md:flex-0 w-full">--}}
{{--                            <div class="accordion__button">--}}
{{--                                @if($isBookable)--}}
{{--                                @php--}}
{{--                                    $requestData = $row['request_data'] ?? $request ?? [];--}}
{{--                                @endphp--}}
{{--                                <a data-id="{{$flightId}}" data-flightdata='@json($alldata)'--}}
{{--                                    data-requestdata='@json($requestData)' href="#" onclick="event.preventDefault()"--}}
{{--                                    class="button -dark-1 px-30 h-50 bg-red-1 text-white btn-choose-flight">--}}
{{--                                    {{__("Choose")}} <div class="icon-arrow-top-right ml-15"></div>--}}
{{--                                </a>--}}
{{--                                @else--}}
{{--                                <a href="#"--}}
{{--                                    class="button -dark-1 px-30 h-50 bg-warning-2 text-white btn-disabled">{{__("Full--}}
{{--                                    Book")}}</a>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

    <hr />

    <div class="row y-gap-30 justify-between">
        <div class="col">
            <div class="row y-gap-10 items-center">
                <div class="col-sm-auto">
                    <div class="has-skeleton">
                        @php
                            $nonRefundable = $alldata['passengerInfoList'][0]['passengerInfo']['nonRefundable'] ?? true;
                        @endphp

                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium
                                     {{ $nonRefundable ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        @if($nonRefundable)
                                            ❌ Non-Refundable
                                        @else
                                            ✅ Refundable
                                        @endif
                        </span>
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
{{--                        <div class="tabs__pane -tab-item-fare-details-{{$flightId}}">--}}

{{--                            <table cellpadding="8" width="100%" border="0" class="border border-gray-200 rounded-lg overflow-hidden">--}}
{{--                                <thead class="bg-gray-100">--}}
{{--                                    <tr>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:left; padding:12px">{{__('Passenger Type')}}</th>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:center">{{__('Count')}}</th>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:right">{{__('Base Fare')}} <small>({{__('Total')}})</small></th>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:right">{{__('Taxes & Fees')}} <small>({{__('Total')}})</small></th>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:right">{{__('Total per Person')}}</th>--}}
{{--                                        <th style="border:1px solid #DDD; text-align:right; background:#f0f9ff">{{__('Subtotal')}}</th>--}}
{{--                                    </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody>--}}
{{--                                    @php--}}
{{--                                        $passengerBreakdown = $fareDetails['passenger_breakdown'] ?? $row['passenger_type_pricing'] ?? [];--}}
{{--                                        $grandBaseFare = 0;--}}
{{--                                        $grandTax = 0;--}}
{{--                                        $grandTotal = 0;--}}
{{--                                    @endphp--}}

{{--                                    @if(!empty($passengerBreakdown))--}}
{{--                                        @foreach($passengerBreakdown as $paxType)--}}
{{--                                            @php--}}
{{--                                                $count = $paxType['count'] ?? 1;--}}
{{--                                                $baseFare = $paxType['base_fare'] ?? 0; // Per person--}}
{{--                                                $tax = $paxType['tax'] ?? 0; // Per person--}}
{{--                                                $total = $paxType['total'] ?? 0; // Per person--}}

{{--                                                // Use pre-calculated totals if available, otherwise calculate--}}
{{--                                                $subtotal = $paxType['total_total'] ?? ($total * $count);--}}
{{--                                                $baseFareTotal = $paxType['base_fare_total'] ?? ($baseFare * $count);--}}
{{--                                                $taxTotal = $paxType['tax_total'] ?? ($tax * $count);--}}

{{--                                                $grandBaseFare += $baseFareTotal;--}}
{{--                                                $grandTax += $taxTotal;--}}
{{--                                                $grandTotal += $subtotal;--}}
{{--                                            @endphp--}}
{{--                                            <tr class="hover:bg-gray-50">--}}
{{--                                                <td style="border:1px solid #EEE; padding:10px">--}}
{{--                                                    <div class="font-medium text-gray-900">{{ $paxType['type_label'] ?? $paxType['type'] }}</div>--}}
{{--                                                    @if(isset($paxType['type']) && in_array($paxType['type'], ['C03', 'C07']))--}}
{{--                                                        <div class="text-xs text-gray-500 mt-1">--}}
{{--                                                            @if($paxType['type'] === 'C03')--}}
{{--                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">--}}
{{--                                                                    {{__('No UT3 Tax')}}--}}
{{--                                                                </span>--}}
{{--                                                            @else--}}
{{--                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">--}}
{{--                                                                    {{__('With UT3 Tax')}}--}}
{{--                                                                </span>--}}
{{--                                                            @endif--}}
{{--                                                        </div>--}}
{{--                                                    @endif--}}
{{--                                                </td>--}}
{{--                                                <td style="border:1px solid #EEE; text-align:center; padding:10px">--}}
{{--                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-semibold">--}}
{{--                                                        {{ $count }}--}}
{{--                                                    </span>--}}
{{--                                                </td>--}}
{{--                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                           {{ format_money($taxTotal) }}--}}
{{--                                                </td>--}}
{{--                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                                 {{ format_money($baseFareTotal) }}--}}
{{--                                                </td>--}}
{{--                                                <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                    {{ $paxType['total_formatted'] ?? format_money($total) }}--}}
{{--                                                </td>--}}
{{--                                                <td style="border:1px solid #EEE; text-align:right; padding:10px; background:#f0f9ff; font-weight:600">--}}
{{--                                                    {{ $paxType['subtotal_formatted'] ?? format_money($subtotal) }}--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        @endforeach--}}

{{--                                        <!-- Grand Total Row -->--}}
{{--                                        <tr class="bg-blue-50 font-bold">--}}
{{--                                            <td colspan="2" style="border:1px solid #CCC; padding:12px; text-align:right">--}}
{{--                                                {{__('Grand Total')}}--}}
{{--                                            </td>--}}

{{--                                            <td style="border:1px solid #CCC; text-align:right; padding:12px">--}}
{{--                                                {{ format_money($grandTax) }}--}}
{{--                                            </td>--}}
{{--                                             <td style="border:1px solid #CCC; text-align:right; padding:12px">--}}
{{--                                                {{ format_money($grandBaseFare) }}--}}
{{--                                            </td>--}}
{{--                                            <td style="border:1px solid #CCC; text-align:right; padding:12px">--}}
{{--                                                ---}}
{{--                                            </td>--}}
{{--                                            <td style="border:1px solid #CCC; text-align:right; padding:12px; background:#dbeafe; font-size:1.1em">--}}
{{--                                                {{ format_money($grandTotal) }}--}}
{{--                                            </td>--}}
{{--                                        </tr>--}}
{{--                                    @else--}}
{{--                                        <!-- Fallback to old single passenger display -->--}}
{{--                                        <tr>--}}
{{--                                            <td style="border:1px solid #EEE; padding:10px">{{$fareDetails['traveler_type'] ?? 'ADULT'}}</td>--}}
{{--                                            <td style="border:1px solid #EEE; text-align:center; padding:10px">1</td>--}}
{{--                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                {{$fareDetails['base_fare_formatted'] ?? format_money(0)}}--}}
{{--                                            </td>--}}
{{--                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                {{$fareDetails['tax_formatted'] ?? format_money(0)}}--}}
{{--                                            </td>--}}
{{--                                            <td style="border:1px solid #EEE; text-align:right; padding:10px">--}}
{{--                                                {{$fareDetails['total_fare_formatted'] ?? format_money(0)}}--}}
{{--                                            </td>--}}
{{--                                            <td style="border:1px solid #EEE; text-align:right; padding:10px; background:#f0f9ff; font-weight:600">--}}
{{--                                                {{$fareDetails['total_fare_formatted'] ?? format_money(0)}}--}}
{{--                                            </td>--}}
{{--                                        </tr>--}}
{{--                                    @endif--}}
{{--                                </tbody>--}}
{{--                            </table>--}}


{{--                        </div>--}}

                        <div class="tabs__pane -tab-item-fare-details-{{$flightId}}">
                            <table cellpadding="8" width="100%" border="0" class="border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-100">
                                <tr>
                                    <th style="border:1px solid #DDD; text-align:left; padding:12px">{{__('Passenger Type')}}</th>
                                    <th style="border:1px solid #DDD; text-align:center">{{__('Count')}}</th>
                                    <th style="border:1px solid #DDD; text-align:right">{{__('Base Fare')}}</th>
                                    <th style="border:1px solid #DDD; text-align:right">{{__('Taxes & Fees')}}</th>
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
                                            $baseFare = $paxType['base_fare'] ?? 0;
                                            $tax = $paxType['tax'] ?? 0;
                                            $total = $paxType['total'] ?? 0;

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
                                                {{ format_money($total) }}
                                            </td>
                                            <td style="border:1px solid #EEE; text-align:right; padding:10px; background:#f0f9ff; font-weight:600">
                                                {{ format_money($subtotal) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Subtotal Row --}}
                                    <tr class="bg-gray-50 font-semibold">
                                        <td colspan="2" style="border:1px solid #CCC; padding:12px; text-align:right">
                                            {{__('Subtotal')}}
                                        </td>
                                        <td style="border:1px solid #CCC; text-align:right; padding:12px">
                                            {{ format_money($grandBaseFare) }}
                                        </td>
                                        <td style="border:1px solid #CCC; text-align:right; padding:12px">
                                            {{ format_money($grandTax) }}
                                        </td>
                                        <td style="border:1px solid #CCC; text-align:right; padding:12px">-</td>
                                        <td style="border:1px solid #CCC; text-align:right; padding:12px; background:#e0f2fe">
                                            {{ format_money($grandTotal) }}
                                        </td>
                                    </tr>

                                    {{-- Discount Row (if applicable) --}}
                                    @if($totaldiscount)
                                        <tr class="bg-green-50">
                                            <td colspan="5" style="border:1px solid #CCC; padding:12px; text-align:right; color:#16a34a; font-weight:600">
                                                {{__('Discount Applied')}}
{{--                                                @if($appliedDiscount->name)--}}
{{--                                                    <span class="text-xs font-normal">({{ $appliedDiscount->name }})</span>--}}
{{--                                                @endif--}}
                                            </td>
                                            <td style="border:1px solid #CCC; text-align:right; padding:12px; color:#dc2626; font-weight:700">
                                                -{{ format_money($totaldiscount) }}
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- Grand Total Row --}}
                                    <tr class="bg-blue-100 font-bold text-lg">
                                        <td colspan="5" style="border:2px solid #3b82f6; padding:14px; text-align:right">
                                            {{__('Final Amount')}}
                                        </td>
                                        <td style="border:2px solid #3b82f6; text-align:right; padding:14px; background:#dbeafe; font-size:1.2em; color:#1e40af">
                                            {{ format_money($finalPrice) }}
                                        </td>
                                    </tr>
                                @else
                                    {{-- Fallback --}}
                                    <tr>
                                        <td colspan="6" style="padding:20px; text-align:center; color:#666">
                                            {{__('No fare breakdown available')}}
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
