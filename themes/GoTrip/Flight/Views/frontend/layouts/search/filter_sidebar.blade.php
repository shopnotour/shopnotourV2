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
    .col-lg-3 {
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
    .col-lg-9 {
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 15px;
    }

    /* Custom scrollbar for list items */
    .col-lg-9::-webkit-scrollbar {
        width: 8px;
    }

    .col-lg-9::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .col-lg-9::-webkit-scrollbar-thumb {
        background: #3b82f6;
        border-radius: 10px;
    }

    .col-lg-9::-webkit-scrollbar-thumb:hover {
        background: #2563eb;
    }

    /* ===== RESPONSIVE - Mobile ===== */
    @media (max-width: 991px) {
        .col-lg-3,
        .col-lg-9 {
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
        width: 18px;
        height: 18px;
        border: 2px solid #ddd;
        background: white;
        position: relative;
    }

    .form-checkbox input[type="radio"] + .form-checkbox__mark .form-checkbox__icon {
        border-radius: 50%;
        width: 8px;
        height: 8px;
        background-color: white;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        opacity: 0;
        transition: all 0.2s ease;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }

    .form-checkbox input[type="radio"]:checked + .form-checkbox__mark .form-checkbox__icon {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    /* Checkbox styling */
    .form-checkbox input[type="checkbox"] + .form-checkbox__mark {
        width: 18px;
        height: 18px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 4px;
        position: relative;
    }

    .form-checkbox input[type="checkbox"]:checked + .form-checkbox__mark {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }

    .form-checkbox input[type="checkbox"]:checked + .form-checkbox__mark .form-checkbox__icon {
        opacity: 1;
    }

    /* Icon colors */
    .text-success {
        color: #10b981 !important;
    }

    .text-danger {
        color: #ef4444 !important;
    }

    .text-primary {
        color: #3b82f6 !important;
    }

    .text-warning {
        color: #f59e0b !important;
    }

    .text-info {
        color: #06b6d4 !important;
    }

    /* Spacing */
    .sidebar__item .row {
        margin-bottom: 0;
    }

    .sidebar__item .row + .row {
        margin-top: 0;
    }
</style>

{{--<div class="col-lg-3">--}}
    <div class="sidebar py-20 px-20 rounded-4 bg-white bravo_filter" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <!-- Search Timer -->
        <div class="sidebar__item pb-15 -no-border">
            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3">
                <h5 class="mb-0 text-14 fw-600">Flight Search</h5>
                <div class="d-flex align-items-center text-danger">
                    <i class="fa-regular fa-clock me-2"></i>
                    <span id="timer" class="text-14 fw-500">15m 00s</span>
                </div>
            </div>
        </div>

        <!-- Price Filter -->
        <div class="sidebar__item pb-15 g-filter-item">
            <h5 class="text-18 fw-500 mb-10">Price Range</h5>

            @php
                $minPrice = collect($flights['flights'] ?? [])->min('price.total') ?? 0;
                $maxPrice = collect($flights['flights'] ?? [])->max('price.total') ?? 100000;
            @endphp

            <div class="mb-3">
                <span id="priceRangeText" class="text-15 fw-500">৳{{ number_format($minPrice) }} - ৳{{ number_format($maxPrice) }}</span>
            </div>
            <input type="range"
                   class="form-range"
                   min="{{ $minPrice }}"
                   max="{{ $maxPrice }}"
                   value="{{ $maxPrice }}"
                   id="priceRange"
                   data-min="{{ $minPrice }}"
                   data-max="{{ $maxPrice }}"
                   style="width: 100%;">
        </div>

        <!-- Airlines Filter (Checkbox - Multiple Select) -->
        <div class="sidebar__item g-filter-item">
            <h5 class="text-18 fw-500 mb-10">Airlines</h5>

            @php
                $airlines = [];
                foreach($flights['flights'] ?? [] as $flight) {
                    $carrier = $flight['validating_carrier'] ?? null;
                    $carrierName = $flight['legs'][0]['segments'][0]['carrier_name'] ?? 'Unknown';
                    if ($carrier) {
                        if (!isset($airlines[$carrier])) {
                            $airlines[$carrier] = [
                                'name' => $carrierName,
                                'count' => 0
                            ];
                        }
                        $airlines[$carrier]['count']++;
                    }
                }
                ksort($airlines);
            @endphp

            <div class="sidebar-checkbox">
                @if(!empty($airlines))
                    @foreach($airlines as $code => $airline)
                        <div class="row y-gap-10 items-center justify-between mb-10">
                            <div class="col-auto">
                                <label class="d-flex align-items-center cursor-pointer">
                                    <div class="form-checkbox d-flex align-items-center">
                                        <input type="checkbox"
                                               name="airline_codes[]"
                                               value="{{ $code }}"
                                               class="airline-filter"
                                               style="display: none;">
                                        <div class="form-checkbox__mark">
                                            <div class="form-checkbox__icon icon-check"></div>
                                        </div>
                                        <div class="text-15 ms-2">{{ $airline['name'] }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-auto">
                                <div class="text-15 text-secondary">({{ $airline['count'] }})</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-secondary py-3">
                        <i class="fa fa-info-circle"></i>
                        <p class="text-14 mt-2 mb-0">No airlines available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stops Filter (Radio - Single Select) -->
        <div class="sidebar__item g-filter-item">
            <h5 class="text-18 fw-500 mb-10">Number of Stops</h5>

            @php
                $stopsCount = ['all' => 0, 'direct' => 0, 'one_stop' => 0, 'two_plus' => 0];

                foreach($flights['flights'] ?? [] as $flight) {
                    $maxStops = 0;
                    foreach($flight['legs'] as $leg) {
                        $maxStops = max($maxStops, $leg['stops']);
                    }

                    $stopsCount['all']++;
                    if ($maxStops == 0) $stopsCount['direct']++;
                    elseif ($maxStops == 1) $stopsCount['one_stop']++;
                    else $stopsCount['two_plus']++;
                }
            @endphp

            <div class="sidebar-checkbox">
                <!-- All Stops -->
                <div class="row y-gap-10 items-center justify-between mb-10">
                    <div class="col-12">
                        <label class="d-flex align-items-center cursor-pointer">
                            <div class="form-checkbox d-flex align-items-center">
                                <input type="radio"
                                       name="stopsFilter"
                                       value="all"
                                       class="stops-filter"
                                       checked>
                                <div class="form-checkbox__mark">
                                    <div class="form-checkbox__icon"></div>
                                </div>
                                <div class="text-15 ms-2 d-flex align-items-center">
                                    <span>All Stops</span>
                                    <span class="text-secondary ms-auto ps-2">({{ $stopsCount['all'] }})</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                @if($stopsCount['direct'] > 0)
                    <!-- Non-Stop -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex align-items-center cursor-pointer">
                                <div class="form-checkbox d-flex align-items-center">
                                    <input type="radio"
                                           name="stopsFilter"
                                           value="0"
                                           class="stops-filter">
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon"></div>
                                    </div>
                                    <div class="text-15 ms-2 d-flex align-items-center">
                                        <span>Non-Stop</span>
                                        <i class="fa fa-plane text-primary ms-1" style="font-size: 12px;"></i>
                                        <span class="text-secondary ms-auto ps-2">({{ $stopsCount['direct'] }})</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif

                @if($stopsCount['one_stop'] > 0)
                    <!-- 1 Stop -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex align-items-center cursor-pointer">
                                <div class="form-checkbox d-flex align-items-center">
                                    <input type="radio"
                                           name="stopsFilter"
                                           value="1"
                                           class="stops-filter">
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon"></div>
                                    </div>
                                    <div class="text-15 ms-2 d-flex align-items-center">
                                        <span>1 Stop</span>
                                        <i class="fa fa-clock text-warning ms-1" style="font-size: 12px;"></i>
                                        <span class="text-secondary ms-auto ps-2">({{ $stopsCount['one_stop'] }})</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif

                @if($stopsCount['two_plus'] > 0)
                    <!-- 2+ Stops -->
                    <div class="row y-gap-10 items-center justify-between mb-10">
                        <div class="col-12">
                            <label class="d-flex align-items-center cursor-pointer">
                                <div class="form-checkbox d-flex align-items-center">
                                    <input type="radio"
                                           name="stopsFilter"
                                           value="2"
                                           class="stops-filter">
                                    <div class="form-checkbox__mark">
                                        <div class="form-checkbox__icon"></div>
                                    </div>
                                    <div class="text-15 ms-2 d-flex align-items-center">
                                        <span>2+ Stops</span>
                                        <i class="fa fa-route text-info ms-1" style="font-size: 12px;"></i>
                                        <span class="text-secondary ms-auto ps-2">({{ $stopsCount['two_plus'] }})</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Departure Time Filter (Multi-Select with Visual Buttons) -->
        <div class="sidebar__item g-filter-item">
            <h5 class="text-18 fw-500 mb-10">Flight Schedules</h5>

            <p class="text-14 text-secondary mb-10">Departure Time</p>

            <div class="row g-2">
                <div class="col-6">
                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"
                         data-time-slot="early_morning"
                         style="transition: all 0.3s ease;">
                        <i class="fa-solid fa-moon text-secondary"></i>
                        <p class="text-12 mt-1 mb-0">00-06</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"
                         data-time-slot="morning"
                         style="transition: all 0.3s ease;">
                        <i class="fa-solid fa-cloud-sun text-warning"></i>
                        <p class="text-12 mt-1 mb-0">06-12</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"
                         data-time-slot="afternoon"
                         style="transition: all 0.3s ease;">
                        <i class="fa-solid fa-sun text-warning"></i>
                        <p class="text-12 mt-1 mb-0">12-18</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center border rounded-3 py-2 cursor-pointer time-slot-filter"
                         data-time-slot="evening"
                         style="transition: all 0.3s ease;">
                        <i class="fa-solid fa-moon text-primary"></i>
                        <p class="text-12 mt-1 mb-0">18-00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clear All Filters Button -->
        <div class="bravo-clear-filter">
            <button type="button" class="btn btn-danger w-100 d-flex align-items-center justify-content-center clear-all-filters">
                <i class="fa fa-redo me-2"></i>
                <span class="fw-500">Clear All Filters</span>
            </button>
        </div>
    </div>
{{--</div>--}}

<div id="noResultsMessage" style="display: none;">
    <div class="alert alert-warning text-center">
        <i class="fa fa-exclamation-triangle me-2"></i>
        No flights match your filters. Try adjusting your search criteria.
    </div>
</div>

{{--@push('scripts')--}}
{{--    <script>--}}
{{--        (function() {--}}
{{--            'use strict';--}}

{{--            // ===== TIMER =====--}}
{{--            const TIMER_DURATION = 15 * 60;--}}
{{--            let timeLeft = TIMER_DURATION;--}}
{{--            const timerElement = document.getElementById('timer');--}}
{{--            let timerInterval;--}}

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
{{--                }--}}
{{--            }--}}

{{--            function startTimer() {--}}
{{--                timerInterval = setInterval(updateTimer, 1000);--}}
{{--            }--}}
{{--            startTimer();--}}

{{--            // ===== FILTER LOGIC =====--}}
{{--            const allFlightCards = document.querySelectorAll('.flight-card');--}}

{{--            function applyFilters() {--}}
{{--                console.log('🔍 Applying filters...');--}}

{{--                const maxPrice = parseInt(document.getElementById('priceRange').value);--}}
{{--                const selectedAirlines = Array.from(document.querySelectorAll('.airline-filter:checked')).map(el => el.value);--}}
{{--                const selectedStop = document.querySelector('.stops-filter:checked')?.value || 'all';--}}

{{--                // Get selected time slots--}}
{{--                const selectedTimes = Array.from(document.querySelectorAll('.time-slot-filter.active')).map(el => el.dataset.timeSlot);--}}

{{--                console.log('Filters:', {--}}
{{--                    maxPrice,--}}
{{--                    selectedAirlines,--}}
{{--                    selectedStop,--}}
{{--                    selectedTimes--}}
{{--                });--}}

{{--                let visibleCount = 0;--}}

{{--                allFlightCards.forEach(card => {--}}
{{--                    const flightDataStr = card.dataset.flight;--}}
{{--                    if (!flightDataStr) {--}}
{{--                        console.warn('No flight data on card');--}}
{{--                        return;--}}
{{--                    }--}}

{{--                    const flightData = JSON.parse(flightDataStr);--}}
{{--                    let show = true;--}}

{{--                    // 🔹 INITIAL STATE: Show all if no filters selected--}}
{{--                    const hasAnyFilter = selectedAirlines.length > 0 ||--}}
{{--                        selectedStop !== 'all' ||--}}
{{--                        selectedTimes.length > 0;--}}

{{--                    // Price filter (always applied)--}}
{{--                    if (flightData.price && flightData.price.total > maxPrice) {--}}
{{--                        show = false;--}}
{{--                    }--}}

{{--                    // Airline filter - only apply if at least one airline is selected--}}
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

{{--                    // Time filter - only apply if at least one time slot is selected--}}
{{--                    if (selectedTimes.length > 0 && flightData.legs && flightData.legs[0].departure) {--}}
{{--                        const departureTime = flightData.legs[0].departure.time;--}}
{{--                        const hour = new Date(departureTime).getHours();--}}

{{--                        let timeMatch = false;--}}
{{--                        selectedTimes.forEach(timeSlot => {--}}
{{--                            if (timeSlot === 'early_morning' && hour >= 0 && hour < 6) timeMatch = true;--}}
{{--                            if (timeSlot === 'morning' && hour >= 6 && hour < 12) timeMatch = true;--}}
{{--                            if (timeSlot === 'afternoon' && hour >= 12 && hour < 18) timeMatch = true;--}}
{{--                            if (timeSlot === 'evening' && hour >= 18) timeMatch = true;--}}
{{--                        });--}}

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

{{--                // Update filter count badge (optional)--}}
{{--                updateFilterBadges();--}}
{{--            }--}}

{{--            // Update visual feedback for active filters--}}
{{--            function updateFilterBadges() {--}}
{{--                const selectedAirlines = document.querySelectorAll('.airline-filter:checked').length;--}}
{{--                const selectedStop = document.querySelector('.stops-filter:checked')?.value;--}}
{{--                const selectedTimes = document.querySelectorAll('.time-slot-filter.active').length;--}}

{{--                // Add visual feedback to sections with active filters--}}
{{--                if (selectedAirlines > 0) {--}}
{{--                    document.querySelector('.airline-filter').closest('.sidebar__item').classList.add('has-filter');--}}
{{--                } else {--}}
{{--                    document.querySelector('.airline-filter').closest('.sidebar__item').classList.remove('has-filter');--}}
{{--                }--}}

{{--                if (selectedStop && selectedStop !== 'all') {--}}
{{--                    document.querySelector('.stops-filter').closest('.sidebar__item').classList.add('has-filter');--}}
{{--                } else {--}}
{{--                    document.querySelector('.stops-filter').closest('.sidebar__item').classList.remove('has-filter');--}}
{{--                }--}}

{{--                if (selectedTimes > 0) {--}}
{{--                    document.querySelectorAll('.time-slot-filter')[0].closest('.sidebar__item').classList.add('has-filter');--}}
{{--                } else {--}}
{{--                    document.querySelectorAll('.time-slot-filter')[0].closest('.sidebar__item').classList.remove('has-filter');--}}
{{--                }--}}
{{--            }--}}

{{--            // Price range--}}
{{--            const priceRange = document.getElementById('priceRange');--}}
{{--            const priceRangeText = document.getElementById('priceRangeText');--}}

{{--            priceRange.addEventListener('input', function() {--}}
{{--                const min = this.dataset.min;--}}
{{--                const max = this.value;--}}
{{--                priceRangeText.textContent = `৳${parseInt(min).toLocaleString()} - ৳${parseInt(max).toLocaleString()}`;--}}
{{--            });--}}

{{--            priceRange.addEventListener('change', applyFilters);--}}

{{--            // Airlines--}}
{{--            document.querySelectorAll('.airline-filter').forEach(cb => cb.addEventListener('change', applyFilters));--}}

{{--            // Stops--}}
{{--            document.querySelectorAll('.stops-filter').forEach(rb => rb.addEventListener('change', applyFilters));--}}

{{--            // Time slots (multi-select with visual toggle)--}}
{{--            // document.querySelectorAll('.time-slot-filter').forEach(slot => {--}}
{{--            //     slot.addEventListener('click', function() {--}}
{{--            //         this.classList.toggle('active');--}}
{{--            //         applyFilters();--}}
{{--            //     });--}}
{{--            // });--}}
{{--            document.querySelectorAll('.time-slot-filter').forEach(slot => {--}}
{{--                slot.addEventListener('click', function() {--}}
{{--                    console.log('⏰ Time slot clicked:', this.dataset.timeSlot);--}}
{{--                    this.classList.toggle('active');--}}

{{--                    // Visual feedback--}}
{{--                    if (this.classList.contains('active')) {--}}
{{--                        console.log('✅ Time slot activated');--}}
{{--                    } else {--}}
{{--                        console.log('❌ Time slot deactivated');--}}
{{--                    }--}}

{{--                    applyFilters();--}}
{{--                });--}}
{{--            });--}}

{{--            // Clear all filters--}}
{{--            document.querySelector('.clear-all-filters')?.addEventListener('click', function() {--}}
{{--                console.log('🗑️ Clearing all filters');--}}

{{--                // Reset price--}}
{{--                priceRange.value = priceRange.max;--}}
{{--                priceRangeText.textContent = `৳${parseInt(priceRange.dataset.min).toLocaleString()} - ৳${parseInt(priceRange.max).toLocaleString()}`;--}}

{{--                // Uncheck all airlines--}}
{{--                document.querySelectorAll('.airline-filter').forEach(cb => cb.checked = false);--}}

{{--                // Reset stops to "All"--}}
{{--                document.querySelector('.stops-filter[value="all"]').checked = true;--}}

{{--                // Clear all time slots--}}
{{--                document.querySelectorAll('.time-slot-filter').forEach(slot => slot.classList.remove('active'));--}}

{{--                // Remove all filter badges--}}
{{--                document.querySelectorAll('.sidebar__item').forEach(item => item.classList.remove('has-filter'));--}}

{{--                applyFilters();--}}
{{--            });--}}

{{--            // Initial state - show all flights--}}
{{--            console.log('📊 Filter initialized. Total flight cards:', allFlightCards.length);--}}
{{--        })();--}}
{{--    </script>--}}
{{--@endpush--}}
