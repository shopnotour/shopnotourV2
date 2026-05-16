@extends('layouts.app')
@push('css')
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons (এটা রাখুন, icon এর জন্য) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>
    <style>
        .bravo_wrap .bravo_search_flight .bravo_form_search{
            margin-bottom: 0px;
        }
    </style>
@endpush
@section('content')
    <div class="bravo_search_flight">
        <div class="container">
            <div class=" pt-40 pb-40">
                <div class="text-center">
                    <h1 class="text-30 fw-600">
                        {{setting_item_with_lang("flight_page_search_title")}}
                    </h1>
                </div>

                @include('Flight::frontend.layouts.search.form-search')

            </div>
        </div>
{{--         🔥 Vue Test Area --}}
{{--        <div id="flight-search-app" class="layout-pt-md layout-pb-md bg-light-2">--}}
{{--             Vue will render here--}}
{{--        </div>--}}
{{--        <div id="flight-search-app"--}}
{{--             class="layout-pt-md layout-pb-md bg-light-2"--}}
{{--             >--}}
{{--            Vue will render here--}}
{{--        </div>--}}
        <div class="layout-pt-md layout-pb-md bg-light-2">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-4">
                        @include('Flight::frontend.layouts.search.filter-search')
                    </div>
                    <div class="col-xl-9 col-lg-8">
                        @include('Flight::frontend.layouts.search.list-item')
                    </div>
                </div>
            </div>
        </div>
    </div>
{{--    @include('Flight::frontend.layouts.search.modal-form-book')--}}
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>
    <script type="text/javascript" src="{{ asset('js/filter.js?_ver='.config('app.asset_version')) }}"></script>
    <script type="text/javascript" src="{{ asset('themes/gotrip/module/flight/js/flight.js?_ver='.config('app.asset_version')) }}"></script>
@endpush



{{--@extends('layouts.app')--}}

{{--@push('css')--}}
{{--    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>--}}
{{--    <style>--}}
{{--        .bravo_wrap .bravo_search_flight .bravo_form_search{--}}
{{--            margin-bottom: 0px;--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--@section('content')--}}
{{--    <div class="bravo_search_flight">--}}
{{--        --}}{{-- Search Form --}}
{{--        <div class="container">--}}
{{--            <div class="pt-40 pb-40">--}}
{{--                <div class="text-center">--}}
{{--                    <h1 class="text-30 fw-600">--}}
{{--                        {{setting_item_with_lang("flight_page_search_title")}}--}}
{{--                    </h1>--}}
{{--                </div>--}}
{{--                @include('Flight::frontend.layouts.search.form-search')--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- Flight Results --}}
{{--        <div class="container py-4">--}}
{{--            <div class="row">--}}
{{--                <div class="col-lg-3">--}}
{{--                @include('Flight::frontend.layouts.search.filter_sidebar', [--}}
{{--                    'flights' => $flights,--}}
{{--                    'searchParams' => $searchParams ?? []--}}
{{--                ])--}}
{{--                </div>--}}

{{--                --}}{{-- Use the HTML from document5 here for flight cards --}}
{{--                <div class="col-lg-9">--}}
{{--                    @include('Flight::frontend.layouts.search.custom_loop',['flights' => $flights])--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}

{{--@push('js')--}}
{{--    <script type="text/javascript" src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>--}}
{{--    <script type="text/javascript" src="{{ asset('js/filter.js?_ver='.config('app.asset_version')) }}"></script>--}}
{{--    <script type="text/javascript" src="{{ asset('themes/gotrip/module/flight/js/flight.js?_ver='.config('app.asset_version')) }}"></script>--}}

{{--    --}}{{--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>--}}
{{--    <script>--}}
{{--        window.flightData = {--}}
{{--            flights: @json($flights ?? []),--}}
{{--            searchParams: @json($searchParams ?? [])--}}
{{--        };--}}

{{--        console.log('✅ Main page JS loaded');--}}
{{--        console.log('Flight cards:', document.querySelectorAll('.flight-card').length);--}}
{{--    </script>--}}

{{--    --}}{{-- Filter Script --}}
{{--    <script>--}}
{{--        (function() {--}}
{{--            'use strict';--}}

{{--            console.log('🚀 Filter script loading...');--}}

{{--            const TIMER_DURATION = 15*60;--}}
{{--            let timeLeft = TIMER_DURATION;--}}
{{--            const timerElement = document.getElementById('timer');--}}
{{--            let timerInterval;--}}
{{--            const searchParams = @json($searchParams ?? []);--}}

{{--            function updateTimer() {--}}
{{--                const minutes = Math.floor(timeLeft / 60);--}}
{{--                const seconds = timeLeft % 60;--}}
{{--                timerElement.textContent = `${minutes}m ${seconds.toString().padStart(2, '0')}s`;--}}

{{--                if (timeLeft > 0) {--}}
{{--                    timeLeft--;--}}
{{--                    if (timeLeft === 120) timerElement.parentElement.classList.add('text-warning');--}}
{{--                    if (timeLeft === 60) {--}}
{{--                        timerElement.parentElement.classList.remove('text-warning');--}}
{{--                        timerElement.parentElement.classList.add('text-danger');--}}
{{--                    }--}}
{{--                } else {--}}
{{--                    clearInterval(timerInterval);--}}
{{--                    timerElement.textContent = 'Expired';--}}
{{--                    timerElement.parentElement.classList.add('text-danger');--}}
{{--                    showTimerExpiredModal();--}}
{{--                }--}}
{{--            }--}}

{{--            function showTimerExpiredModal() {--}}
{{--                if (confirm('⏰ Search session expired! Prices may have changed.\n\nClick OK to refresh search, or Cancel to go home.')) {--}}
{{--                    reloadSearch();--}}
{{--                } else {--}}
{{--                    window.location.href = '/';--}}
{{--                }--}}
{{--            }--}}

{{--            function reloadSearch() {--}}
{{--                const form = document.createElement('form');--}}
{{--                form.method = 'GET';--}}
{{--                form.action = '{{ route("flight.search") }}';--}}

{{--                const csrfInput = document.createElement('input');--}}
{{--                csrfInput.type = 'hidden';--}}
{{--                csrfInput.name = '_token';--}}
{{--                csrfInput.value = '{{ csrf_token() }}';--}}
{{--                form.appendChild(csrfInput);--}}

{{--                Object.keys(searchParams).forEach(key => {--}}
{{--                    const value = searchParams[key];--}}

{{--                    if (Array.isArray(value)) {--}}
{{--                        value.forEach((item, index) => {--}}
{{--                            if (typeof item === 'object') {--}}
{{--                                Object.keys(item).forEach(subKey => {--}}
{{--                                    const input = document.createElement('input');--}}
{{--                                    input.type = 'hidden';--}}
{{--                                    input.name = `${key}[${index}][${subKey}]`;--}}
{{--                                    input.value = item[subKey];--}}
{{--                                    form.appendChild(input);--}}
{{--                                });--}}
{{--                            } else {--}}
{{--                                const input = document.createElement('input');--}}
{{--                                input.type = 'hidden';--}}
{{--                                input.name = `${key}[${index}]`;--}}
{{--                                input.value = item;--}}
{{--                                form.appendChild(input);--}}
{{--                            }--}}
{{--                        });--}}
{{--                    } else {--}}
{{--                        const input = document.createElement('input');--}}
{{--                        input.type = 'hidden';--}}
{{--                        input.name = key;--}}
{{--                        input.value = value;--}}
{{--                        form.appendChild(input);--}}
{{--                    }--}}
{{--                });--}}

{{--                document.body.appendChild(form);--}}
{{--                form.submit();--}}
{{--            }--}}

{{--            function startTimer() {--}}
{{--                timerInterval = setInterval(updateTimer, 1000);--}}
{{--            }--}}

{{--            startTimer();--}}
{{--            const allFlightCards = document.querySelectorAll('.flight-card');--}}
{{--            console.log('📊 Total flight cards found:', allFlightCards.length);--}}

{{--            function applyFilters() {--}}
{{--                console.log('🔍 Applying filters...');--}}

{{--                const maxPrice = parseInt(document.getElementById('priceRange').value);--}}
{{--                const selectedAirlines = Array.from(document.querySelectorAll('.airline-filter:checked')).map(el => el.value);--}}
{{--                const selectedStop = document.querySelector('.stops-filter:checked')?.value || 'all';--}}

{{--                // ✅ FIX: Get selected time slots from visual buttons--}}
{{--                const selectedTimes = Array.from(document.querySelectorAll('.time-slot-filter.active')).map(el => el.dataset.timeSlot);--}}

{{--                console.log('Active Filters:', {--}}
{{--                    price: maxPrice,--}}
{{--                    airlines: selectedAirlines.length,--}}
{{--                    stop: selectedStop,--}}
{{--                    times: selectedTimes--}}
{{--                });--}}

{{--                let visibleCount = 0;--}}

{{--                allFlightCards.forEach((card) => {--}}
{{--                    const flightDataStr = card.dataset.flight;--}}

{{--                    if (!flightDataStr) return;--}}

{{--                    let flightData;--}}
{{--                    try {--}}
{{--                        flightData = JSON.parse(flightDataStr);--}}
{{--                    } catch (e) {--}}
{{--                        return;--}}
{{--                    }--}}

{{--                    let show = true;--}}

{{--                    // Price filter--}}
{{--                    if (flightData.price && flightData.price.total > maxPrice) {--}}
{{--                        show = false;--}}
{{--                    }--}}

{{--                    // Airline filter (only if selected)--}}
{{--                    if (selectedAirlines.length > 0) {--}}
{{--                        if (!selectedAirlines.includes(flightData.validating_carrier)) {--}}
{{--                            show = false;--}}
{{--                        }--}}
{{--                    }--}}

{{--                    // Stops filter--}}
{{--                    if (selectedStop !== 'all' && flightData.legs) {--}}
{{--                        const maxStops = Math.max(...flightData.legs.map(leg => leg.stops));--}}
{{--                        const stopFilter = parseInt(selectedStop);--}}

{{--                        if (stopFilter === 0 && maxStops !== 0) show = false;--}}
{{--                        if (stopFilter === 1 && maxStops !== 1) show = false;--}}
{{--                        if (stopFilter === 2 && maxStops < 2) show = false;--}}
{{--                    }--}}

{{--                    // ✅ FIX: Time filter (multi-select)--}}
{{--                    // if (selectedTimes.length > 0 && flightData.legs && flightData.legs[0].departure) {--}}
{{--                    //     const departureTime = flightData.legs[0].departure.time;--}}
{{--                    //     const hour = new Date(departureTime).getHours();--}}
{{--                    //--}}
{{--                    //     let timeMatch = false;--}}
{{--                    //     selectedTimes.forEach(timeSlot => {--}}
{{--                    //         if (timeSlot === 'early_morning' && hour >= 0 && hour < 6) timeMatch = true;--}}
{{--                    //         if (timeSlot === 'morning' && hour >= 6 && hour < 12) timeMatch = true;--}}
{{--                    //         if (timeSlot === 'afternoon' && hour >= 12 && hour < 18) timeMatch = true;--}}
{{--                    //         if (timeSlot === 'evening' && hour >= 18) timeMatch = true;--}}
{{--                    //     });--}}
{{--                    //--}}
{{--                    //     if (!timeMatch) show = false;--}}
{{--                    // }--}}
{{--                    if (selectedTimes.length > 0 && flightData.legs && flightData.legs[0].departure) {--}}
{{--                        const departureTime = flightData.legs[0].departure.time;--}}

{{--                        // Parse hour from time string (handles "17:00:00+06:00" format)--}}
{{--                        let hour;--}}
{{--                        if (departureTime.includes('T')) {--}}
{{--                            // ISO format: "2025-12-30T17:00:00+06:00"--}}
{{--                            hour = new Date(departureTime).getHours();--}}
{{--                        } else if (departureTime.includes(':')) {--}}
{{--                            // Time only format: "17:00:00+06:00"--}}
{{--                            hour = parseInt(departureTime.split(':')[0]);--}}
{{--                        } else {--}}
{{--                            hour = 0;--}}
{{--                        }--}}

{{--                        console.log('Flight hour:', hour, 'from', departureTime);--}}

{{--                        let timeMatch = false;--}}
{{--                        selectedTimes.forEach(timeSlot => {--}}
{{--                            if (timeSlot === 'early_morning' && hour >= 0 && hour < 6) timeMatch = true;--}}
{{--                            if (timeSlot === 'morning' && hour >= 6 && hour < 12) timeMatch = true;--}}
{{--                            if (timeSlot === 'afternoon' && hour >= 12 && hour < 18) timeMatch = true;--}}
{{--                            if (timeSlot === 'evening' && hour >= 18) timeMatch = true;--}}
{{--                        });--}}

{{--                        console.log('Time match:', timeMatch, 'for slot:', selectedTimes);--}}

{{--                        if (!timeMatch) show = false;--}}
{{--                    }--}}
{{--                    card.style.display = show ? 'block' : 'none';--}}
{{--                    if (show) visibleCount++;--}}
{{--                });--}}

{{--                console.log('✅ Visible flights:', visibleCount);--}}

{{--                const noResultsMsg = document.getElementById('noResultsMessage');--}}
{{--                if (noResultsMsg) {--}}
{{--                    noResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';--}}
{{--                }--}}
{{--            }--}}

{{--            const priceRange = document.getElementById('priceRange');--}}
{{--            const priceRangeText = document.getElementById('priceRangeText');--}}

{{--            if (priceRange) {--}}
{{--                priceRange.addEventListener('input', function() {--}}
{{--                    const min = this.dataset.min;--}}
{{--                    const max = this.value;--}}
{{--                    priceRangeText.textContent = `৳${parseInt(min).toLocaleString()} - ৳${parseInt(max).toLocaleString()}`;--}}
{{--                });--}}

{{--                priceRange.addEventListener('change', applyFilters);--}}
{{--            }--}}

{{--            // Airline checkboxes--}}
{{--            document.querySelectorAll('.airline-filter').forEach(cb => {--}}
{{--                cb.addEventListener('change', applyFilters);--}}
{{--            });--}}

{{--            // Stops radio buttons--}}
{{--            document.querySelectorAll('.stops-filter').forEach(rb => {--}}
{{--                rb.addEventListener('change', applyFilters);--}}
{{--            });--}}

{{--            // ✅ FIX: Time slot visual buttons--}}
{{--            document.querySelectorAll('.time-slot-filter').forEach(slot => {--}}
{{--                slot.addEventListener('click', function() {--}}
{{--                    console.log('⏰ Time slot clicked:', this.dataset.timeSlot);--}}
{{--                    this.classList.toggle('active');--}}

{{--                    if (this.classList.contains('active')) {--}}
{{--                        console.log('✅ Activated');--}}
{{--                    } else {--}}
{{--                        console.log('❌ Deactivated');--}}
{{--                    }--}}

{{--                    applyFilters();--}}
{{--                });--}}
{{--            });--}}



{{--            // ✅ FIX: Reset/Clear button--}}
{{--            const resetBtn = document.querySelector('.clear-all-filters');--}}
{{--            if (resetBtn) {--}}
{{--                resetBtn.addEventListener('click', function() {--}}
{{--                    console.log('🗑️ Clearing all filters');--}}

{{--                    // Reset price--}}
{{--                    priceRange.value = priceRange.max;--}}
{{--                    priceRangeText.textContent = `৳${parseInt(priceRange.dataset.min).toLocaleString()} - ৳${parseInt(priceRange.max).toLocaleString()}`;--}}

{{--                    // Uncheck all airlines--}}
{{--                    document.querySelectorAll('.airline-filter').forEach(cb => cb.checked = false);--}}

{{--                    // Reset stops to "All"--}}
{{--                    const stopAll = document.querySelector('.stops-filter[value="all"]');--}}
{{--                    if (stopAll) stopAll.checked = true;--}}

{{--                    // Clear all time slots--}}
{{--                    document.querySelectorAll('.time-slot-filter').forEach(slot => {--}}
{{--                        slot.classList.remove('active');--}}
{{--                    });--}}

{{--                    applyFilters();--}}
{{--                });--}}
{{--            }--}}

{{--            console.log('✅ Filter script loaded successfully');--}}
{{--        })();--}}
{{--    </script>--}}
{{--@endpush--}}
