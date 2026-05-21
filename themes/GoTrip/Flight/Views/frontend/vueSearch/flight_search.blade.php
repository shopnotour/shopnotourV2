@extends('layouts.app')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>

    <style>
        /* ─────────────────────────────────────────────
           GLOBAL RESETS
        ───────────────────────────────────────────── */
        .bravo_wrap .bravo_search_flight .bravo_form_search { margin-bottom: 0; }

        #flight-search-form,
        #flight-search-app { max-width: 100%; }

        /* ─────────────────────────────────────────────
           BACKGROUND SLIDER
           position:absolute — section এর overflow
           কে affect করবে না
        ───────────────────────────────────────────── */
        .bg-slider {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            /* ✅ slider নিজে overflow:hidden — background clip করবে */
            overflow: hidden;
            border-radius: inherit;
        }

        .bg-slide {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }
        .bg-slide.active { opacity: 1; z-index: 1; }

        #searchFormSection {
            /* collapse state */
            max-height: 0;
            opacity: 0;
            pointer-events: none;        /* collapse এ click বন্ধ */

            /* ✅ NO overflow hidden — sidebar clip হবে না */
            overflow: visible !important;

            /* position + z-index */
            position: relative;
            z-index: 500;                  /* results (z:10) এর নিচে */

            margin-top: 0;
            margin-bottom: 80px;

            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1),
            opacity     0.4s ease;
        }

        #searchFormSection.active {
            z-index: 500;
            max-height: 2000px;          /* ✅ sidebar সহ যথেষ্ট */
            opacity: 1;
            pointer-events: all;
            overflow: visible !important;
        }

        /* ─────────────────────────────────────────────
           FLIGHT RESULTS — search form এর উপরে
        ───────────────────────────────────────────── */
        #flight-search-app {
            position: relative;
            z-index: 10;
            background-color: #f8fafc;
            padding: 32px 0;
        }

        #sidebar-trigger-point {
            position: relative;
            z-index: 1;
        }

        /* ─────────────────────────────────────────────
           DESKTOP SIDEBAR — overflow visible
           sticky apply button এর জন্য দরকার
        ───────────────────────────────────────────── */
        .fsp-desktop-col,
        .fsp-dsk-sidebar {
            overflow: visible !important;
        }

        /* fixed sidebar তে scroll context দরকার */
        .fsp-dsk-sidebar.fixed {
            overflow-y: auto !important;
            overflow-x: visible !important;
        }

        /* ─────────────────────────────────────────────
           SEARCH FORM INNER — min height যাতে
           background দেখা যায়
        ───────────────────────────────────────────── */
        .search-form-inner {
            /* min-height: 420px; */
            position: relative;
        }

        @media (max-width: 768px) {
            .search-form-inner { min-height: 520px; }
            #searchFormSection { margin-bottom: 40px; }
        }
        /* Flatpickr calendar — flight cards এর উপরে */
        .flatpickr-calendar {
            z-index: 99999 !important;
        }
    </style>
@endpush

@section('content')
    @include('Popup::frontend.partials.popup', ['pageKey' => 'home'])

    <div class="bravo_search_flight">

        {{-- ── Flight Summary Section ── --}}
        <div class="flight-summary-wrapper bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
            <div class="container mx-auto px-4">
                <div id="flight-search-summary"></div>
            </div>
        </div>

        {{-- ── Toggle Button ── --}}
        <div class="container hidden mx-auto px-4 pt-4 pb-2">
            <button
                id="searchToggleBtn"
                class="group mx-auto flex items-center justify-center gap-2 px-6 py-3 md:px-8 md:py-4
                       bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                       text-white font-semibold rounded-full shadow-lg hover:shadow-xl
                       transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-search text-lg md:text-xl group-hover:rotate-12 transition-transform duration-300"></i>
                <span class="text-sm md:text-base">Search Flights</span>
                <i class="fas fa-chevron-down text-sm transition-transform duration-300" id="chevronIcon"></i>
            </button>
        </div>

        {{-- ── Search Form Section ── --}}

        <div id="searchFormSection" class="relative">

            {{-- Background slider — absolute, নিজে overflow:hidden --}}
            <div class="bg-slider">
                @if(!empty($flightBgImages))
                    @foreach($flightBgImages as $index => $image)
                        <div class="bg-slide {{ $index === 0 ? 'active' : '' }}"
                             style="background-image: url('{{ $image }}')"></div>
                    @endforeach
                @endif
            </div>

            {{-- Overlay — absolute --}}
            <div class="absolute inset-0 bg-gradient-to-br from-blue-900/60 via-blue-800/50 to-blue-700/60"
                 style="z-index:1; pointer-events:none"></div>

            {{-- Content — relative z-index:2, overflow:visible --}}
            <div class="search-form-inner" style="overflow:visible; position:relative; z-index:2;">
                <div class="container mx-auto px-4 py-8">
                    {{-- <div class="text-center mb-6">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white drop-shadow-lg">
                            {{ setting_item_with_lang("flight_page_search_title") ?? 'Search for Flights' }}
                        </h1>
                        <p class="text-white/90 mt-2 text-base md:text-lg">
                            Find the best deals on flights worldwide
                        </p>
                    </div> --}}
                    <div class="container mx-auto justify-center items-center">
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <div id="flight-search-form"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /#searchFormSection --}}

        {{-- Trigger point for sticky sidebar --}}
        <div id="sidebar-trigger-point"></div>

        {{-- Flight results --}}
        <div id="flight-search-app"></div>

    </div>
@endsection


@push('js')

    <script>
        window.isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        window.userRoleId = {{ Auth::check() ? Auth::user()->role_id : 'null' }};
        window.flightData = {
            flights:        @json($flights ?? []),
            searchParams:   @json($searchParams ?? []),
            searchUrl:      '{{ route("flight.search") }}',
            isReissue:      @json($isReissue ?? false),
            reissueAirline: @json($reissueAirline ?? null),
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ── Background slider ── */
            const slides = document.querySelectorAll('.bg-slide');
            if (slides.length > 1) {
                let cur = 0;
                setInterval(() => {
                    slides[cur].classList.remove('active');
                    cur = (cur + 1) % slides.length;
                    slides[cur].classList.add('active');
                }, 5000);
            }

            /* ── Search form toggle ── */
            const btn            = document.getElementById('searchToggleBtn');
            const section        = document.getElementById('searchFormSection');
            const chevron        = document.getElementById('chevronIcon');
            const btnText        = btn?.querySelector('span');

            if (btn && section) {
                btn.addEventListener('click', function () {
                    const isActive = section.classList.toggle('active');

                    /* chevron rotate */
                    if (chevron)
                        chevron.style.transform = isActive
                            ? 'rotate(180deg)'
                            : 'rotate(0deg)';

                    /* button text */
                    if (btnText)
                        btnText.textContent = isActive
                            ? 'Hide Search'
                            : 'Search Flights';

                    /* Dispatch event for summary component */
                    const toggleEvent = new CustomEvent('searchFormToggled', {
                        detail: { isVisible: isActive }
                    });

                    document.dispatchEvent(toggleEvent);

                    /* close flatpickr calendars when collapsing */
                    if (!isActive) {
                        document.querySelectorAll('.flatpickr-calendar')
                            .forEach(cal => {
                                cal.style.display = 'none';
                            });
                    }
                });
            }
        });
    </script>

    @if(app()->environment('local'))
        @vite(['resources/js/flight-search-app.js'])
    @else
       <script type="module" src="{{ asset('build/assets/flight-search-app-DNXWwMR6.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('build/assets/flight-search-app-Dhd6jE9w.css') }}">

    @endif



    <script src="{{ asset('themes/gotrip/module/flight/js/flight.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>
    <script type="text/javascript" src="{{ asset('js/filter.js?_ver='.config('app.asset_version')) }}"></script>
@endpush
