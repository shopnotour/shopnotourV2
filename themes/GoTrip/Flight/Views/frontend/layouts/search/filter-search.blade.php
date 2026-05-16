

<!-- ✅ Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- ✅ Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .sidebar__item.has-filter {
        border-left: 3px solid #1d4ed8;
    }
    .sidebar__item.has-filter .text-18 {
        color: #1d4ed8;
        font-weight: 600;
    }
    .airline-filter:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
    }
</style>
<!-- ✅ Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- ✅ Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    /* ===== MAIN CONTAINER LAYOUT ===== */
    .layout-pt-md.layout-pb-md {
        padding-top: 30px !important;
        padding-bottom: 30px !important;
    }

    /* ===== FILTER SIDEBAR - FIXED + SCROLLABLE ===== */
    .col-xl-3.col-lg-4 {
        position: sticky;
        top: 20px;
        height: calc(100vh - 40px);
        overflow: visible;
    }

    .bravo_filter {
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 80px;
    }

    /* Custom scrollbar for sidebar */
    .bravo_filter::-webkit-scrollbar {
        width: 6px;
    }

    .bravo_filter::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .bravo_filter::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .bravo_filter::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* ===== LIST ITEM - INDEPENDENT SCROLLABLE ===== */
    .col-xl-9.col-lg-8 {
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 15px;
    }

    /* Custom scrollbar for list items */
    .col-xl-9.col-lg-8::-webkit-scrollbar {
        width: 8px;
    }

    .col-xl-9.col-lg-8::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .col-xl-9.col-lg-8::-webkit-scrollbar-thumb {
        background: #3b82f6;
        border-radius: 10px;
    }

    .col-xl-9.col-lg-8::-webkit-scrollbar-thumb:hover {
        background: #2563eb;
    }

    /* ===== RESPONSIVE - Mobile ===== */
    @media (max-width: 991px) {
        .col-xl-3.col-lg-4,
        .col-xl-9.col-lg-8 {
            position: relative;
            height: auto;
            max-height: none;
            overflow: visible;
        }

        .bravo_filter {
            max-height: none;
            overflow: visible;
        }
    }

    /* ===== CLEAR ALL BUTTON ===== */
    .bravo-clear-filter {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 15px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 10;
        margin-top: 20px;
        border-radius: 8px;
    }

    .bravo-clear-filter .button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .bravo-clear-filter .button:hover {
        background-color: #dc2626 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    @media (max-width: 991px) {
        .bravo-clear-filter {
            position: relative;
        }
    }

    /* Filter active state */
    .sidebar__item.has-filter {
        border-left: 3px solid #1d4ed8;
    }

    .sidebar__item.has-filter .text-18 {
        color: #1d4ed8;
        font-weight: 600;
    }

    .airline-filter:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
    }

    /* Time slot active state */
    .time-slot-filter.active {
        background-color: #fee2e2 !important;
        border-color: #dc2626 !important;
        font-weight: 600;
    }

    /* Radio button styling */
    .form-checkbox input[type="radio"] {
        display: none;
    }

    .form-checkbox input[type="radio"] + .form-checkbox__mark {
        border-radius: 50% !important;
    }

    .form-checkbox input[type="radio"] + .form-checkbox__mark .form-checkbox__icon {
        border-radius: 50%;
        width: 8px;
        height: 8px;
        background-color: white;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark .form-checkbox__icon {
        opacity: 1;
        transform: scale(1);
    }

    /* Icon colors */
    .text-success {
        color: #10b981 !important;
    }

    .text-danger {
        color: #ef4444 !important;
    }

    .text-blue-1 {
        color: #3b82f6 !important;
    }

    .bg-blue-1-05 {
        background-color: rgba(59, 130, 246, 0.05) !important;
    }

    /* Spacing fix */
    .sidebar__item .sidebar-checkbox > .row {
        margin-bottom: 0;
    }

    .sidebar__item .sidebar-checkbox .row + .row {
        margin-top: 0;
    }
</style>
{{--<style>--}}
{{--    /* ===== SIDEBAR FIXED + SCROLLABLE ===== */--}}
{{--    .bravo_filter {--}}
{{--        position: sticky;--}}
{{--        top: 100px; /* Adjust based on your header height */--}}
{{--        max-height: calc(100vh - 120px); /* Full viewport height minus top offset */--}}
{{--        overflow-y: auto;--}}
{{--        overflow-x: hidden;--}}
{{--    }--}}

{{--    /* Custom scrollbar for sidebar */--}}
{{--    .bravo_filter::-webkit-scrollbar {--}}
{{--        width: 6px;--}}
{{--    }--}}

{{--    .bravo_filter::-webkit-scrollbar-track {--}}
{{--        background: #f1f1f1;--}}
{{--        border-radius: 10px;--}}
{{--    }--}}

{{--    .bravo_filter::-webkit-scrollbar-thumb {--}}
{{--        background: #888;--}}
{{--        border-radius: 10px;--}}
{{--    }--}}

{{--    .bravo_filter::-webkit-scrollbar-thumb:hover {--}}
{{--        background: #555;--}}
{{--    }--}}

{{--    /* ===== CLEAR ALL BUTTON ===== */--}}
{{--    .bravo-clear-filter {--}}
{{--        position: sticky;--}}
{{--        bottom: 0;--}}
{{--        background: white;--}}
{{--        padding: 15px;--}}
{{--        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);--}}
{{--        z-index: 10;--}}
{{--        margin-top: 20px;--}}
{{--        border-radius: 8px;--}}
{{--    }--}}

{{--    .bravo-clear-filter .button {--}}
{{--        width: 100%;--}}
{{--        display: flex;--}}
{{--        align-items: center;--}}
{{--        justify-content: center;--}}
{{--        gap: 8px;--}}
{{--        transition: all 0.3s ease;--}}
{{--    }--}}

{{--    .bravo-clear-filter .button:hover {--}}
{{--        background-color: #dc2626 !important;--}}
{{--        transform: translateY(-2px);--}}
{{--        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);--}}
{{--    }--}}

{{--    /* Hide on mobile initially */--}}
{{--    @media (max-width: 991px) {--}}
{{--        .bravo-clear-filter {--}}
{{--            display: none;--}}
{{--        }--}}
{{--    }--}}

{{--    /* Filter active state */--}}
{{--    .sidebar__item.has-filter {--}}
{{--        border-left: 3px solid #1d4ed8;--}}
{{--    }--}}

{{--    .sidebar__item.has-filter .text-18 {--}}
{{--        color: #1d4ed8;--}}
{{--        font-weight: 600;--}}
{{--    }--}}

{{--    /* Time slot active state */--}}
{{--    .time-slot-filter.active {--}}
{{--        background-color: #fee2e2 !important;--}}
{{--        border-color: #dc2626 !important;--}}
{{--        font-weight: 600;--}}
{{--    }--}}
{{--</style>--}}

<div class="sidebar py-20 px-20 rounded-4 bg-gray bravo_filter">
{{--    <form action="{{url(app_get_locale(false,false,'/').config('flight.flight_route_prefix'))}}" class="bravo_form_filter row y-gap-40">--}}
    <form action="{{route('flight.search')}}" method="get" class="bravo_form_filter">
{{--         Preserve search parameters--}}
        <input type="hidden" name="trip_type" value="{{ request('trip_type') }}">
        <input type="hidden" name="adults" value="{{ request('adults') }}">
        <input type="hidden" name="children" value="{{ request('children') }}">
        <input type="hidden" name="infants" value="{{ request('infants') }}">
        <input type="hidden" name="travel_class" value="{{ request('travel_class') }}">
        <input type="hidden" name="travelClass" value="{{ request('travelClass') }}">

        @if(request('segments'))
            @foreach(request('segments') as $index => $segment)
                <input type="hidden" name="segments[{{ $index }}][from]" value="{{ $segment['from'] ?? '' }}">
                <input type="hidden" name="segments[{{ $index }}][to]" value="{{ $segment['to'] ?? '' }}">
                <input type="hidden" name="segments[{{ $index }}][departure]" value="{{ $segment['departure'] ?? '' }}">
            @endforeach
        @endif

        @if(request('return_date'))
            <input type="hidden" name="return_date" value="{{ request('return_date') }}">
        @endif

        <div class="sidebar__item pb-15 -no-border">
            <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow">
                <h4 class="text-sm font-semibold text-gray-800">Flight Search</h4>
                <div class="flex items-center space-x-2 text-red-600 text-sm font-medium">
                    <i class="fa-regular fa-clock"></i>
                    <span id="timer">14m 53s</span>
                </div>
            </div>
        </div>
        <div class="sidebar__item pb-15 -no-border">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-18 fw-500 mb-10">{{__('Price')}}</h5>
                <div class="row x-gap-10 y-gap-30">
                    <div class="col-12">
                        <div class="js-price-searchPage">
                            @include('Flight::frontend.layouts.search.price-filter', ['flight_min_max_price' => $flight_min_max_price ?? [0, 10000]])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar__item g-filter-item">
            <div class="bg-white rounded-lg shadow p-4 -no-border">
                <h5 class="text-18 fw-500 mb-10">Airlines</h5>
                <div class="sidebar-checkbox ">
                    @php
                        // Extract unique airlines from current search results
                        $airlines = [];
                        if (isset($rows) && isset($rows->data) && is_array($rows->data)) {
                            foreach ($rows->data as $flight) {
                                $airlineCode = $flight['airline_code'] ?? null;
                                if ($airlineCode && $airlineCode !== 'N/A') {
                                    // Get airline name from designator (not 'code')
                                    $airlineModel = \Modules\Flight\Models\Airline::where('designator', $airlineCode)->first();
                                    $displayName = $airlineModel ? $airlineModel->name : $airlineCode;

                                    // Count occurrences
                                    if (!isset($airlines[$airlineCode])) {
                                        $airlines[$airlineCode] = [
                                            'name' => $displayName,
                                            'code' => $airlineCode,
                                            'count' => 0
                                        ];
                                    }
                                    $airlines[$airlineCode]['count']++;
                                }
                            }
                        }

                        // Sort airlines by count (descending) then by name
                        uasort($airlines, function($a, $b) {
                            if ($a['count'] === $b['count']) {
                                return strcmp($a['name'], $b['name']);
                            }
                            return $b['count'] - $a['count'];
                        });
                    @endphp

                    @if(!empty($airlines))
                        @foreach($airlines as $airline)
                            <div class="row y-gap-10 items-center justify-between">
                                <div class="col-auto">
                                    <label class="cursor-pointer">
                                        <div class="form-checkbox d-flex items-center">
                                            <input type="checkbox"
                                                   name="airline_codes[]"
                                                   value="{{ $airline['code'] }}"
                                                   class="has-value airline-filter"
                                                {{ in_array($airline['code'], request()->input('airline_codes', [])) ? 'checked' : '' }}>
                                            <div class="form-checkbox__mark">
                                                <div class="form-checkbox__icon icon-check"></div>
                                            </div>
                                            <div class="text-15 ml-10">{{ $airline['name'] }}</div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <div class="text-15 text-light-1">({{ $airline['count'] }})</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-light-1 py-3">
                            <i class="fa fa-info-circle"></i>
                            <p class="text-14 mt-2">No airlines available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="sidebar__item g-filter-item">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-18 fw-500 mb-10">{{__('Refund Policy')}}</h5>

                <div class="sidebar-checkbox">
                    <!-- Refundable Option -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="refund_type"
                                           value="refundable"
                                           class="refund-filter">
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10 d-flex items-center">
                                        <span>{{__('Refundable')}}</span>
                                        <i class="fa fa-check-circle text-success ml-5" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Non-Refundable Option -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="refund_type"
                                           value="non-refundable"
                                           class="refund-filter"
                                           >
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10 d-flex items-center">
                                        <span>{{__('Non-Refundable')}}</span>
                                        <i class="fa fa-times-circle text-danger ml-5" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- All Flights -->
                    <div class="row y-gap-10 items-center justify-between">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="refund_type"
                                           value=""
                                           class="refund-filter"
                                           checked
                                    >
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10">
                                        <span>{{__('All Flights')}}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

{{--                <!-- Info Badge -->--}}
{{--                <div class="mt-15 p-10 bg-blue-1-05 rounded-4">--}}
{{--                    <div class="d-flex items-center text-12 text-blue-1">--}}
{{--                        <i class="fa fa-info-circle mr-5"></i>--}}
{{--                        <span>{{__('Refundable tickets allow cancellation with refund')}}</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>

        <div class="sidebar__item g-filter-item">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-18 fw-500 mb-10">{{__('Number of Stops')}}</h5>

                <div class="sidebar-checkbox">
                    <!-- Non-Stop -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="stop_type"
                                           value="non-stop"
                                           class="stop-filter"
                                        {{ request('stop_type') == 'non-stop' ? 'checked' : '' }}>
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10 d-flex items-center">
                                        <span>{{__('Non-Stop')}}</span>
                                        <i class="fa fa-plane text-primary ml-5" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- One Stop -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="stop_type"
                                           value="one-stop"
                                           class="stop-filter"
                                        {{ request('stop_type') == 'one-stop' ? 'checked' : '' }}>
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10 d-flex items-center">
                                        <span>{{__('1 Stop')}}</span>
                                        <i class="fa fa-clock text-warning ml-5" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Two or More Stops -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="stop_type"
                                           value="two-stop"
                                           class="stop-filter"
                                        {{ request('stop_type') == 'two-stop' ? 'checked' : '' }}>
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10 d-flex items-center">
                                        <span>{{__('2+ Stops')}}</span>
                                        <i class="fa fa-route text-info ml-5" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- All Flights -->
                    <div class="row y-gap-10 items-center justify-between">
                        <div class="col-12">
                            <label class="d-flex items-center cursor-pointer">
                                <div class="form-checkbox d-flex items-center">
                                    <input type="radio"
                                           name="stop_type"
                                           value=""
                                           class="stop-filter"
                                        {{ !request('stop_type') ? 'checked' : '' }}>
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon icon-check"></div>
                                    </div>
                                    <div class="text-15 ml-10">
                                        <span>{{__('All Flights')}}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>





{{--        @include('Layout::global.search.filters.attrs')--}}

        <div class="sidebar__item pb-15 -no-border">
            <div class="bg-white rounded-lg shadow p-4">
                <h5 class="text-18 fw-500 mb-10">{{__('Flight Schedules')}}</h5>

                <!-- Hidden input for time slots -->
                <input type="hidden" name="time_slots" value="">

                <div class="flex border rounded-lg overflow-hidden text-sm font-medium mb-15">
                    <button type="button" class="w-1/2 bg-red-600 text-white py-1 schedule-tab active" data-tab="departure">Departure</button>
                    <button type="button" class="w-1/2 bg-gray-100 text-gray-700 py-1 schedule-tab" data-tab="arrival">Arrival</button>
                </div>

                <p class="text-sm text-gray-500 mt-2 mb-10">Departure from DAC</p>

                <div class="grid grid-cols-4 gap-2">
                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50 time-slot-filter transition" data-time-slot="00-06">
                        <i class="fa-solid fa-moon text-gray-600"></i>
                        <p class="text-xs mt-1">00-06</p>
                    </div>
                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50 time-slot-filter transition" data-time-slot="06-12">
                        <i class="fa-solid fa-cloud-sun text-orange-400"></i>
                        <p class="text-xs mt-1">06-12</p>
                    </div>
                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50 time-slot-filter transition" data-time-slot="12-18">
                        <i class="fa-solid fa-sun text-yellow-500"></i>
                        <p class="text-xs mt-1">12-18</p>
                    </div>
                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50 time-slot-filter transition" data-time-slot="18-24">
                        <i class="fa-solid fa-moon text-blue-600"></i>
                        <p class="text-xs mt-1">18-00</p>
                    </div>
                </div>
            </div>
        </div>


{{--        <div class="sidebar__item pb-15 -no-border">--}}
{{--            <div class="bg-white rounded-lg shadow p-4">--}}
{{--                <h5 class="text-18 fw-500 mb-10">{{__('Flight Schedules')}}</h5>--}}
{{--                <div class="flex border rounded-lg overflow-hidden text-sm font-medium">--}}
{{--                    <button class="w-1/2 bg-red-600 text-white py-1">Departure</button>--}}
{{--                    <button class="w-1/2 bg-gray-100 text-gray-700 py-1">Arrival</button>--}}
{{--                </div>--}}

{{--                <p class="text-sm text-gray-500 mt-2">Departure from DAC</p>--}}

{{--                <div class="grid grid-cols-4 gap-2 mt-3">--}}
{{--                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50">--}}
{{--                        <i class="fa-solid fa-sun text-yellow-500"></i>--}}
{{--                        <p class="text-xs mt-1">00-06</p>--}}
{{--                    </div>--}}
{{--                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50">--}}
{{--                        <i class="fa-solid fa-cloud-sun text-orange-400"></i>--}}
{{--                        <p class="text-xs mt-1">06-12</p>--}}
{{--                    </div>--}}
{{--                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50">--}}
{{--                        <i class="fa-solid fa-sun text-blue-500"></i>--}}
{{--                        <p class="text-xs mt-1">12-18</p>--}}
{{--                    </div>--}}
{{--                    <div class="text-center border rounded-lg py-2 cursor-pointer hover:bg-red-50">--}}
{{--                        <i class="fa-solid fa-moon text-gray-600"></i>--}}
{{--                        <p class="text-xs mt-1">18-00</p>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="bravo-clear-filter">--}}
{{--            <button type="button" class="button px-15 py-10 -dark-1 bg-blue-1 text-white clear-all-filters">--}}
{{--                <i class="icon-loop-2 text-16"></i>--}}
{{--                <span class="fw-500">{{__('Clear All Filters')}}</span>--}}
{{--            </button>--}}
{{--        </div>--}}

        <div class="bravo-clear-filter">
            <button type="button" class="button px-15 py-10 -dark-1 bg-blue-1 text-white clear-all-filters">
                <i class="icon-loop-2 text-16"></i>
                <span class="fw-500">{{__('Clear All Filters')}}</span>
            </button>
        </div>
    </form>
</div>

<style>
    /* Radio button styling */
    /*.form-checkbox input[type="radio"] {*/
    /*    display: none;*/
    /*}*/

    .form-checkbox input[type="radio"] + .form-checkbox__mark {
        border-radius: 50% !important;
    }

    .form-checkbox input[type="radio"] + .form-checkbox__mark .form-checkbox__icon {
        border-radius: 50%;
        width: 8px;
        height: 8px;
        background-color: white;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark .form-checkbox__icon {
        opacity: 1;
        transform: scale(1);
    }

    /* Icon colors */
    .text-success {
        color: #10b981 !important;
    }

    .text-danger {
        color: #ef4444 !important;
    }

    .text-blue-1 {
        color: #3b82f6 !important;
    }

    .bg-blue-1-05 {
        background-color: rgba(59, 130, 246, 0.05) !important;
    }

    /* Spacing fix */
    .sidebar__item .sidebar-checkbox > .row {
        margin-bottom: 0;
    }

    .sidebar__item .sidebar-checkbox .row + .row {
        margin-top: 0;
    }
</style>
{{--@push('js')--}}
{{--    <script>--}}
{{--        (function($) {--}}
{{--            'use strict';--}}

{{--            // ✅ Airline filter change handler--}}
{{--            $('.airline-filter').on('change', function(e) {--}}
{{--                e.preventDefault();--}}
{{--                e.stopImmediatePropagation();--}}

{{--                // Get form and serialize data--}}
{{--                var $form = $(this).closest('.bravo_form_filter');--}}
{{--                var formData = $form.serialize() + '&_ajax=1';--}}

{{--                // Get selected airlines for visual feedback--}}
{{--                var selectedAirlines = [];--}}
{{--                $('.airline-filter:checked').each(function() {--}}
{{--                    selectedAirlines.push({--}}
{{--                        code: $(this).val(),--}}
{{--                        name: $(this).closest('label').find('.text-15').text().trim()--}}
{{--                    });--}}
{{--                });--}}

{{--                // Visual feedback--}}
{{--                if (selectedAirlines.length > 0) {--}}
{{--                    $('.airline-filter').closest('.g-filter-item').addClass('has-filter');--}}
{{--                } else {--}}
{{--                    $('.airline-filter').closest('.g-filter-item').removeClass('has-filter');--}}
{{--                }--}}

{{--                // ✅ Make AJAX request--}}
{{--                $.ajax({--}}
{{--                    url: $form.attr('action'),--}}
{{--                    method: 'GET',--}}
{{--                    data: formData,--}}
{{--                    dataType: 'json',--}}
{{--                    beforeSend: function() {--}}

{{--                        // Show loading overlay--}}
{{--                        if (!$('.ajax-search-result').find('.loading-overlay').length) {--}}
{{--                            $('.ajax-search-result').css('position', 'relative');--}}
{{--                            $('.ajax-search-result').append(--}}
{{--                                '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.9);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +--}}
{{--                                '<div style="text-align:center;">' +--}}
{{--                                '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +--}}
{{--                                '<p class="text-15 fw-500">Filtering flights...</p>' +--}}
{{--                                '</div>' +--}}
{{--                                '</div>'--}}
{{--                            );--}}
{{--                        }--}}
{{--                    },--}}
{{--                    success: function(response) {--}}

{{--                        if (response.status === 1 && response.fragments) {--}}

{{--                            // Update search results--}}
{{--                            if (response.fragments['.ajax-search-result']) {--}}
{{--                                $('.ajax-search-result').html(response.fragments['.ajax-search-result']);--}}
{{--                            }--}}

{{--                            // Update result count--}}
{{--                            if (response.fragments['.result-count']) {--}}
{{--                                $('.result-count').html(response.fragments['.result-count']);--}}
{{--                            }--}}

{{--                            // Update count string--}}
{{--                            if (response.fragments['.count-string']) {--}}
{{--                                $('.count-string').html(response.fragments['.count-string']);--}}
{{--                            }--}}

{{--                            // Update price filter if exists--}}
{{--                            if (response.fragments['.js-price-searchPage']) {--}}
{{--                                $('.js-price-searchPage').html(response.fragments['.js-price-searchPage']);--}}
{{--                            }--}}

{{--                            // Scroll to results (smooth)--}}
{{--                            $('html, body').animate({--}}
{{--                                scrollTop: $('.ajax-search-result').offset().top - 100--}}
{{--                            }, 500);--}}

{{--                        } else {--}}
{{--                            console.warn('⚠️ Invalid response format:', response);--}}
{{--                            alert('Filter failed. Please try again.');--}}
{{--                        }--}}
{{--                    },--}}
{{--                    error: function(xhr, status, error) {--}}

{{--                        // Show error message--}}
{{--                        $('.loading-overlay').html(--}}
{{--                            '<div style="text-align:center;">' +--}}
{{--                            '<i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>' +--}}
{{--                            '<p class="text-15 fw-500">Filter failed. Please try again.</p>' +--}}
{{--                            '<button class="btn btn-primary mt-2" onclick="location.reload()">Refresh Page</button>' +--}}
{{--                            '</div>'--}}
{{--                        );--}}
{{--                    },--}}
{{--                    complete: function() {--}}

{{--                        // Remove loading after short delay for smooth transition--}}
{{--                        setTimeout(function() {--}}
{{--                            $('.loading-overlay').fadeOut(300, function() {--}}
{{--                                $(this).remove();--}}
{{--                            });--}}
{{--                        }, 300);--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}

{{--            // ✅ Handle other filter checkboxes (stops, etc.)--}}
{{--            $('.bravo_form_filter input[type=checkbox]').not('.airline-filter').on('change', function(e) {--}}
{{--                e.preventDefault();--}}
{{--                e.stopImmediatePropagation();--}}

{{--                console.log('🔍 Other filter changed:', $(this).attr('name'));--}}

{{--                // Trigger the same AJAX logic--}}
{{--                $('.airline-filter').first().trigger('change');--}}
{{--            });--}}

{{--        })(jQuery);--}}

{{--        (function($) {--}}
{{--            'use strict';--}}

{{--            // ... existing airline filter code ...--}}

{{--            // ✅ Refund filter handler--}}
{{--            $('.refund-filter').on('change', function() {--}}
{{--                console.log('🔄 Refund filter changed:', $(this).val());--}}

{{--                // Visual feedback--}}
{{--                const refundType = $('input[name="refund_type"]:checked').val();--}}
{{--                if (refundType && refundType !== '') {--}}
{{--                    $('.refund-filter').closest('.g-filter-item').addClass('has-filter');--}}
{{--                } else {--}}
{{--                    $('.refund-filter').closest('.g-filter-item').removeClass('has-filter');--}}
{{--                }--}}

{{--                // Trigger AJAX search (reuse airline filter logic)--}}
{{--                var $form = $(this).closest('.bravo_form_filter');--}}
{{--                var formData = $form.serialize() + '&_ajax=1';--}}

{{--                console.log('📋 Refund Filter Data:', formData);--}}

{{--                // Make AJAX request (same as airline filter)--}}
{{--                submitFilterForm();--}}
{{--            });--}}

{{--            // Helper function for AJAX submission--}}
{{--            function submitFilterForm() {--}}
{{--                var $form = $('.bravo_form_filter');--}}
{{--                var formData = $form.serialize() + '&_ajax=1';--}}

{{--                $.ajax({--}}
{{--                    url: $form.attr('action'),--}}
{{--                    method: 'GET',--}}
{{--                    data: formData,--}}
{{--                    dataType: 'json',--}}
{{--                    beforeSend: function() {--}}
{{--                        if (!$('.ajax-search-result').find('.loading-overlay').length) {--}}
{{--                            $('.ajax-search-result').css('position', 'relative');--}}
{{--                            $('.ajax-search-result').append(--}}
{{--                                '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.9);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +--}}
{{--                                '<div style="text-align:center;">' +--}}
{{--                                '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +--}}
{{--                                '<p class="text-15 fw-500">Filtering flights...</p>' +--}}
{{--                                '</div>' +--}}
{{--                                '</div>'--}}
{{--                            );--}}
{{--                        }--}}
{{--                    },--}}
{{--                    success: function(response) {--}}
{{--                        console.log('✅ Filter Response:', response);--}}

{{--                        if (response.status === 1 && response.fragments) {--}}
{{--                            if (response.fragments['.ajax-search-result']) {--}}
{{--                                $('.ajax-search-result').html(response.fragments['.ajax-search-result']);--}}
{{--                            }--}}
{{--                            if (response.fragments['.result-count']) {--}}
{{--                                $('.result-count').html(response.fragments['.result-count']);--}}
{{--                            }--}}
{{--                            if (response.fragments['.count-string']) {--}}
{{--                                $('.count-string').html(response.fragments['.count-string']);--}}
{{--                            }--}}

{{--                            $('html, body').animate({--}}
{{--                                scrollTop: $('.ajax-search-result').offset().top - 100--}}
{{--                            }, 500);--}}
{{--                        }--}}
{{--                    },--}}
{{--                    error: function(xhr, status, error) {--}}
{{--                        console.error('❌ Filter Error:', error);--}}
{{--                    },--}}
{{--                    complete: function() {--}}
{{--                        setTimeout(function() {--}}
{{--                            $('.loading-overlay').fadeOut(300, function() {--}}
{{--                                $(this).remove();--}}
{{--                            });--}}
{{--                        }, 300);--}}
{{--                    }--}}
{{--                });--}}
{{--            }--}}

{{--        })(jQuery);--}}
{{--        // (function($) {--}}
{{--        //     'use strict';--}}
{{--        //--}}
{{--        //     // Handle airline filter changes--}}
{{--        //     $('.airline-filter').on('change', function() {--}}
{{--        //         // Get selected airlines--}}
{{--        //         const selectedAirlines = [];--}}
{{--        //         $('.airline-filter:checked').each(function() {--}}
{{--        //             selectedAirlines.push($(this).val());--}}
{{--        //         });--}}
{{--        //--}}
{{--        //         // Visual feedback--}}
{{--        //         if (selectedAirlines.length > 0) {--}}
{{--        //             $('.airline-filter').closest('.sidebar__item').addClass('has-filter');--}}
{{--        //         } else {--}}
{{--        //             $('.airline-filter').closest('.sidebar__item').removeClass('has-filter');--}}
{{--        //         }--}}
{{--        //     });--}}
{{--        //--}}
{{--        //     // Trigger initial state check--}}
{{--        //     $('.airline-filter:checked').trigger('change');--}}
{{--        // })(jQuery);--}}



{{--        let timerDuration = 15 * 60; // 15 minutes in seconds--}}
{{--        let timerDisplay = document.getElementById('timer');--}}

{{--        function startTimer() {--}}
{{--            timerDuration = 15 * 60; // reset to 15 minutes--}}

{{--            let timerInterval = setInterval(() => {--}}
{{--                let minutes = Math.floor(timerDuration / 60);--}}
{{--                let seconds = timerDuration % 60;--}}

{{--                timerDisplay.textContent = `${minutes}m ${seconds < 10 ? '0' : ''}${seconds}s`;--}}

{{--                if (timerDuration <= 0) {--}}
{{--                    clearInterval(timerInterval);--}}
{{--                    startTimer(); // 🔁 restart timer again from 15 minutes--}}
{{--                }--}}

{{--                timerDuration--;--}}
{{--            }, 1000);--}}
{{--        }--}}

{{--        startTimer();--}}

{{--    </script>--}}
{{--@endpush--}}


@push('js')
    <script>
        (function($) {
            'use strict';

            // ===== CLEAR ALL FILTERS BUTTON =====
            $('.clear-all-filters').on('click', function(e) {
                e.preventDefault();

                console.log('🗑️ Clearing all filters...');

                // 1. Uncheck all airline checkboxes
                $('.airline-filter').prop('checked', false);

                // 2. Reset all radio buttons to default "All Flights"
                $('input[name="refund_type"][value=""]').prop('checked', true);
                $('input[name="stop_type"][value=""]').prop('checked', true);

                // 3. Clear time slots
                $('.time-slot-filter').removeClass('active bg-red-50');
                $('input[name="time_slots"]').val('');

                // 4. Reset price slider (if exists)
                if (typeof priceSlider !== 'undefined') {
                    priceSlider.reset();
                }

                // 5. Remove all visual "has-filter" states
                $('.g-filter-item').removeClass('has-filter');

                // 6. Build clean URL preserving only search parameters (not filters)
                var cleanUrl = $('.bravo_form_filter').attr('action');
                var searchParams = new URLSearchParams();

                // Preserve ONLY essential search parameters (NOT filter params)
                var essentialParams = [
                    'trip_type', 'adults', 'children', 'infants',
                    'travel_class', 'travelClass', 'return_date'
                ];

                essentialParams.forEach(function(param) {
                    var value = $('input[name="' + param + '"]').val();
                    if (value) {
                        searchParams.append(param, value);
                    }
                });

                // Preserve segments (multi-city routes)
                $('input[name^="segments"]').each(function() {
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if (value) {
                        searchParams.append(name, value);
                    }
                });

                // Build final URL
                var finalUrl = cleanUrl + '?' + searchParams.toString();

                console.log('🔄 Redirecting to:', finalUrl);

                // Show loading overlay
                if (!$('.loading-overlay').length) {
                    $('.ajax-search-result').css('position', 'relative');
                    $('.ajax-search-result').append(
                        '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.95);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +
                        '<div style="text-align:center;">' +
                        '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +
                        '<p class="text-15 fw-500">Clearing filters...</p>' +
                        '</div>' +
                        '</div>'
                    );
                }

                // Redirect to clean URL (without filter params)
                // setTimeout(function() {
                //     window.location.href = finalUrl;
                // }, 300);
            });

            // ===== Show/Hide Clear Button Based on Active Filters =====
            function updateClearButtonVisibility() {
                var hasActiveFilters = false;

                // Check if any filter is active
                if ($('.airline-filter:checked').length > 0) hasActiveFilters = true;
                if ($('input[name="refund_type"]:checked').val() !== '') hasActiveFilters = true;
                if ($('input[name="stop_type"]:checked').val() !== '') hasActiveFilters = true;
                if ($('.time-slot-filter.active').length > 0) hasActiveFilters = true;

                // Always show the button (remove this if you want to hide when no filters)
                // $('.bravo-clear-filter').toggle(hasActiveFilters);
            }

            // Update visibility on page load
            updateClearButtonVisibility();

            // Update visibility when filters change
            $('.airline-filter, .refund-filter, .stop-filter, .time-slot-filter').on('change click', function() {
                updateClearButtonVisibility();
            });

        })(jQuery);
    </script>
    <script>
        (function($) {
            'use strict';

            // ===== COMMON AJAX SUBMIT FUNCTION =====
            function submitFilterForm() {
                var $form = $('.bravo_form_filter');
                var formData = $form.serialize() + '&_ajax=1';

                console.log('🔍 Submitting filter:', formData);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'GET',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        // Show loading overlay
                        if (!$('.loading-overlay').length) {
                            $('.ajax-search-result').css('position', 'relative');
                            $('.ajax-search-result').append(
                                '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.9);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +
                                '<div style="text-align:center;">' +
                                '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +
                                '<p class="text-15 fw-500">Filtering flights...</p>' +
                                '</div>' +
                                '</div>'
                            );
                        }
                    },
                    success: function(response) {
                        console.log('✅ Filter Success:', response);

                        if (response.status === 1 && response.fragments) {
                            // Update search results
                            if (response.fragments['.ajax-search-result']) {
                                $('.ajax-search-result').html(response.fragments['.ajax-search-result']);
                            }

                            // Update result count
                            if (response.fragments['.result-count']) {
                                $('.result-count').html(response.fragments['.result-count']);
                            }

                            // Update count string
                            if (response.fragments['.count-string']) {
                                $('.count-string').html(response.fragments['.count-string']);
                            }

                            // Update price slider if exists
                            if (response.fragments['.js-price-searchPage']) {
                                $('.js-price-searchPage').html(response.fragments['.js-price-searchPage']);
                            }

                            // Smooth scroll to results
                            $('html, body').animate({
                                scrollTop: $('.ajax-search-result').offset().top - 100
                            }, 500);
                        } else {
                            console.warn('⚠️ Invalid response:', response);
                            alert('Filter failed. Please try again.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX Error:', error);

                        $('.loading-overlay').html(
                            '<div style="text-align:center;">' +
                            '<i class="fa fa-exclamation-triangle fa-3x text-danger mb-3"></i>' +
                            '<p class="text-15 fw-500">Filter failed. Please refresh.</p>' +
                            '<button class="btn btn-primary mt-2" onclick="location.reload()">Refresh</button>' +
                            '</div>'
                        );
                    },
                    complete: function() {
                        // Remove loading
                        setTimeout(function() {
                            $('.loading-overlay').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 300);
                    }
                });
            }

            // ===== 1. AIRLINE FILTER =====
            $('.airline-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                console.log('✈️ Airline filter changed');

                // Visual feedback
                var selectedCount = $('.airline-filter:checked').length;
                if (selectedCount > 0) {
                    $('.airline-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.airline-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                // Submit filter
                submitFilterForm();
            });

            // ===== 2. REFUND POLICY FILTER =====
            $('.refund-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                console.log('💰 Refund filter changed:', $(this).val());

                // Visual feedback
                var refundType = $('input[name="refund_type"]:checked').val();
                if (refundType && refundType !== '') {
                    $('.refund-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.refund-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                // Submit filter
                submitFilterForm();
            });

            // ===== 3. STOPS FILTER =====
            $('.stop-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                console.log('🛬 Stops filter changed:', $(this).val());

                // Visual feedback
                var stopType = $('input[name="stop_type"]:checked').val();
                if (stopType && stopType !== '') {
                    $('.stop-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.stop-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                // Submit filter
                submitFilterForm();
            });

            // ===== 4. PRICE RANGE FILTER (if using ion.rangeSlider) =====
            // This will be triggered by the APPLY button in price section
            $(document).on('click', '.js-price-searchPage .button', function(e) {
                e.preventDefault();
                console.log('💵 Price filter applied');
                submitFilterForm();
            });

            // ===== 5. FLIGHT SCHEDULES FILTER =====
            // Add time slot filter handlers
            $('.time-slot-filter').on('click', function(e) {
                e.preventDefault();

                var timeSlot = $(this).data('time-slot'); // e.g., "00-06", "06-12"

                // Toggle active state
                $(this).toggleClass('active bg-red-50');

                // Collect all active time slots
                var activeSlots = [];
                $('.time-slot-filter.active').each(function() {
                    activeSlots.push($(this).data('time-slot'));
                });

                // Update hidden input
                $('input[name="time_slots"]').val(activeSlots.join(','));

                console.log('🕐 Time slots:', activeSlots);

                // Submit filter
                submitFilterForm();
            });

            // ===== INITIAL STATE =====
            console.log('🚀 All filters initialized');

            // Set initial visual feedback for checked items
            if ($('.airline-filter:checked').length > 0) {
                $('.airline-filter').closest('.g-filter-item').addClass('has-filter');
            }

            if ($('input[name="refund_type"]:checked').val()) {
                $('.refund-filter').closest('.g-filter-item').addClass('has-filter');
            }

            if ($('input[name="stop_type"]:checked').val()) {
                $('.stop-filter').closest('.g-filter-item').addClass('has-filter');
            }

        })(jQuery);

        // ===== TIMER (keep existing) =====
        let timerDuration = 15 * 60;
        let timerDisplay = document.getElementById('timer');

        function startTimer() {
            timerDuration = 15 * 60;
            let timerInterval = setInterval(() => {
                let minutes = Math.floor(timerDuration / 60);
                let seconds = timerDuration % 60;
                timerDisplay.textContent = `${minutes}m ${seconds < 10 ? '0' : ''}${seconds}s`;

                if (timerDuration <= 0) {
                    clearInterval(timerInterval);
                    startTimer();
                }
                timerDuration--;
            }, 1000);
        }
        startTimer();
    </script>

    <script>
        (function($) {
            'use strict';

            // ===== COMMON AJAX SUBMIT FUNCTION =====
            function submitFilterForm() {
                var $form = $('.bravo_form_filter');
                var formData = $form.serialize() + '&_ajax=1';

                console.log('🔍 Submitting filter:', formData);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'GET',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        if (!$('.loading-overlay').length) {
                            $('.ajax-search-result').css('position', 'relative');
                            $('.ajax-search-result').append(
                                '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.9);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +
                                '<div style="text-align:center;">' +
                                '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +
                                '<p class="text-15 fw-500">Filtering flights...</p>' +
                                '</div>' +
                                '</div>'
                            );
                        }
                    },
                    success: function(response) {
                        if (response.status === 1 && response.fragments) {
                            if (response.fragments['.ajax-search-result']) {
                                $('.ajax-search-result').html(response.fragments['.ajax-search-result']);
                            }
                            if (response.fragments['.result-count']) {
                                $('.result-count').html(response.fragments['.result-count']);
                            }
                            if (response.fragments['.count-string']) {
                                $('.count-string').html(response.fragments['.count-string']);
                            }
                            if (response.fragments['.js-price-searchPage']) {
                                $('.js-price-searchPage').html(response.fragments['.js-price-searchPage']);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX Error:', error);
                    },
                    complete: function() {
                        setTimeout(function() {
                            $('.loading-overlay').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 300);
                    }
                });
            }

            // ===== 1. AIRLINE FILTER =====
            $('.airline-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var selectedCount = $('.airline-filter:checked').length;
                if (selectedCount > 0) {
                    $('.airline-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.airline-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                submitFilterForm();
            });

            // ===== 2. REFUND POLICY FILTER =====
            $('.refund-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var refundType = $('input[name="refund_type"]:checked').val();
                if (refundType && refundType !== '') {
                    $('.refund-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.refund-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                submitFilterForm();
            });

            // ===== 3. STOPS FILTER =====
            $('.stop-filter').on('change', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var stopType = $('input[name="stop_type"]:checked').val();
                if (stopType && stopType !== '') {
                    $('.stop-filter').closest('.g-filter-item').addClass('has-filter');
                } else {
                    $('.stop-filter').closest('.g-filter-item').removeClass('has-filter');
                }

                submitFilterForm();
            });

            // ===== 4. TIME SLOTS FILTER =====
            $('.time-slot-filter').on('click', function(e) {
                e.preventDefault();

                $(this).toggleClass('active bg-red-50');

                var activeSlots = [];
                $('.time-slot-filter.active').each(function() {
                    activeSlots.push($(this).data('time-slot'));
                });

                $('input[name="time_slots"]').val(activeSlots.join(','));

                submitFilterForm();
            });

            // ===== CLEAR ALL FILTERS BUTTON =====
            $('.clear-all-filters').on('click', function(e) {
                e.preventDefault();

                console.log('🗑️ Clearing all filters...');

                // Clear all filters
                $('.airline-filter').prop('checked', false);
                $('input[name="refund_type"][value=""]').prop('checked', true);
                $('input[name="stop_type"][value=""]').prop('checked', true);
                $('.time-slot-filter').removeClass('active bg-red-50');
                $('input[name="time_slots"]').val('');

                if (typeof priceSlider !== 'undefined') {
                    priceSlider.reset();
                }

                $('.g-filter-item').removeClass('has-filter');

                // Build AJAX request
                var $form = $('.bravo_form_filter');
                var searchParams = new URLSearchParams();

                var essentialParams = [
                    'trip_type', 'adults', 'children', 'infants',
                    'travel_class', 'travelClass', 'return_date'
                ];

                essentialParams.forEach(function(param) {
                    var value = $('input[name="' + param + '"]').val();
                    if (value) {
                        searchParams.append(param, value);
                    }
                });

                $('input[name^="segments"]').each(function() {
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if (value) {
                        searchParams.append(name, value);
                    }
                });

                searchParams.append('_ajax', '1');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'GET',
                    data: searchParams.toString(),
                    dataType: 'json',
                    beforeSend: function() {
                        if (!$('.loading-overlay').length) {
                            $('.ajax-search-result').append(
                                '<div class="loading-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.95);z-index:9999;display:flex;align-items:center;justify-content:center;min-height:400px;">' +
                                '<div style="text-align:center;">' +
                                '<i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>' +
                                '<p class="text-15 fw-500">Clearing filters...</p>' +
                                '</div></div>'
                            );
                        }
                    },
                    success: function(response) {
                        if (response.status === 1 && response.fragments) {
                            if (response.fragments['.ajax-search-result']) {
                                $('.ajax-search-result').html(response.fragments['.ajax-search-result']);
                            }
                            if (response.fragments['.result-count']) {
                                $('.result-count').html(response.fragments['.result-count']);
                            }
                            if (response.fragments['.count-string']) {
                                $('.count-string').html(response.fragments['.count-string']);
                            }
                            if (response.fragments['.js-price-searchPage']) {
                                $('.js-price-searchPage').html(response.fragments['.js-price-searchPage']);
                            }
                        }
                    },
                    complete: function() {
                        setTimeout(function() {
                            $('.loading-overlay').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 300);
                    }
                });
            });

        })(jQuery);

        // ===== TIMER =====
        let timerDuration = 15 * 60;
        let timerDisplay = document.getElementById('timer');

        function startTimer() {
            timerDuration = 15 * 60;
            let timerInterval = setInterval(() => {
                let minutes = Math.floor(timerDuration / 60);
                let seconds = timerDuration % 60;
                timerDisplay.textContent = `${minutes}m ${seconds < 10 ? '0' : ''}${seconds}s`;

                if (timerDuration <= 0) {
                    clearInterval(timerInterval);
                    startTimer();
                }
                timerDuration--;
            }, 1000);
        }
        startTimer();
    </script>
@endpush
