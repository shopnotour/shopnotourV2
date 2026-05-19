@extends('layouts.app')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>

    <style>
        /* Tailwind এ নেই এই কয়টা মাত্র */
        .bg-slide { flex: 0 0 100%; background-size: cover; background-position: center; }
        .slider-dot.active { width: 24px; border-radius: 4px; background: white; }

        /* Traveler dropdown z-index fix */
        .bravo_form_search { position: relative; z-index: 30; }
        .bravo_form_search .dropdown-menu,
        .bravo_form_search .traveler-dropdown,
        .bravo_form_search .passengers-dropdown,
        .bravo_form_search [class*="dropdown"] { z-index: 999 !important; }
        
        /* Ensure popup is above Vue components */
        #userPopupOverlay {
            z-index: 99999 !important;
        }
    </style>
@endpush

@section('content')
    {{-- Move popup outside Vue's mount point --}}
    <div id="popup-container">
        @include('Popup::frontend.partials.popup', ['pageKey' => 'dashboard'])
    </div>
    
    <div class="bravo_search_flight">

        {{-- ── Search Section ── --}}
        <div class="relative mt-20 h-[600px]" style="overflow:visible">


            {{-- Slider Track --}}
            @if(!empty($flightBgImages))
                {{-- slider wrap: overflow hidden শুধু এখানে, parent এ নয় --}}
                <div class="absolute inset-0 overflow-hidden">
                    <div id="bgSlider"
                         class="absolute inset-0 flex will-change-transform"
                         style="transition: transform 0.8s cubic-bezier(0.77,0,0.175,1)">
                        @foreach($flightBgImages as $image)

                            <div class="bg-slide h-full flex-shrink-0"
                                 style="background-image: url('{{ $image }}')">
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Arrows --}}
                <button id="sliderPrev"
                        class="absolute left-4 top-1/2 -translate-y-1/2 z-20
                               w-10 h-10 rounded-full flex items-center justify-center
                               bg-white/20 hover:bg-white/35 border border-white/35
                               text-white text-lg transition-all backdrop-blur-sm">
                    &#8592;
                </button>
                <button id="sliderNext"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-20
                               w-10 h-10 rounded-full flex items-center justify-center
                               bg-white/20 hover:bg-white/35 border border-white/35
                               text-white text-lg transition-all backdrop-blur-sm">
                    &#8594;
                </button>

                {{-- Dots --}}
                <div id="sliderDots"
                     class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    @foreach($flightBgImages as $index => $image)
                        <button data-index="{{ $index }}"
                                class="slider-dot h-2 w-2 rounded-full border-0 p-0 cursor-pointer transition-all
                                       {{ $index === 0 ? 'active' : 'bg-white/40' }}">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Overlay --}}
            <div class="absolute inset-0 z-10 pointer-events-none
                bg-gradient-to-br from-slate-900/30 via-blue-900/20 to-blue-800/20"></div>

            {{-- Content --}}
            <div class="relative z-20 h-full flex flex-col items-center justify-center px-4">

                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-lg">
                        {{ setting_item_with_lang("flight_page_search_title") ?? 'Search for Flights' }}
                    </h1>
                    <p class="text-white/90 mt-2 text-lg">Find the best deals on flights worldwide</p>
                </div>

                {{-- Form — col-8 centered --}}
                <div class="container mx-auto">
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div id="flight-search-form"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>{{-- /search --}}

        <div id="flight-homepage-sections"></div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        window.flightData         = { searchUrl: '{{ route("flight.search") }}' };
        window.flightHomepageData = {
            services:     @json($services ?? []),
            partners:     @json($partners ?? []),
            features:     @json($features ?? []),
            destinations: @json($destinations ?? []),
            testimonials: @json($testimonials ?? [])
        };
        
        // Add a flag to prevent popup from showing multiple times
        window.popupShown = false;
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var slider  = document.getElementById('bgSlider');
            var dotsEl  = document.getElementById('sliderDots');
            var btnPrev = document.getElementById('sliderPrev');
            var btnNext = document.getElementById('sliderNext');

            if (!slider) return;

            var dots    = dotsEl ? dotsEl.querySelectorAll('.slider-dot') : [];
            var total   = slider.querySelectorAll('.bg-slide').length;
            var current = 0;
            var timer   = null;

            if (total <= 1) {
                if (btnPrev) btnPrev.style.display = 'none';
                if (btnNext) btnNext.style.display = 'none';
                if (dotsEl)  dotsEl.style.display  = 'none';
                return;
            }

            function goTo(index) {
                index = ((index % total) + total) % total;
                slider.style.transform = 'translateX(-' + (index * 100) + '%)';
                dots.forEach(function (d, i) {
                    if (i === index) {
                        d.classList.add('active');
                        d.classList.remove('bg-white/40');
                    } else {
                        d.classList.remove('active');
                        d.classList.add('bg-white/40');
                    }
                });
                current = index;
            }

            function startTimer() {
                clearInterval(timer);
                timer = setInterval(function () { goTo(current + 1); }, 8000);
            }

            if (btnPrev) btnPrev.addEventListener('click', function () { goTo(current - 1); startTimer(); });
            if (btnNext) btnNext.addEventListener('click', function () { goTo(current + 1); startTimer(); });
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () { goTo(i); startTimer(); });
            });

            var tx = 0;
            slider.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
            slider.addEventListener('touchend', function (e) {
                var diff = tx - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { goTo(diff > 0 ? current + 1 : current - 1); startTimer(); }
            });

            startTimer();
        });
    </script>


    @if(app()->environment('local'))
        @vite(['resources/js/flight-search-app.js'])
    @else
        <script type="module" src="{{ asset('build/assets/flight-search-app-CZkkz6Ln.js ') }}"></script>
        <link rel="stylesheet" href="{{ asset('build/assets/flight-search-app-Dhd6jE9w.css') }}">
    @endif

    <script src="{{ asset('themes/gotrip/module/flight/js/flight.js') }}" defer></script>
@endpush