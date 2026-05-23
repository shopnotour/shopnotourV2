@extends('layouts.app')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="{{ asset('dist/frontend/module/flight/css/flight.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>

    <style>
        /* Tailwind এ নেই এই কয়টা মাত্র */
        .bg-slide { 
            flex: 0 0 100%; 
            position: relative;
            overflow: hidden;
        }
        
        .bg-slide video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }
        
        .bg-slide .slide-image {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
        }
        
        .slider-dot.active { 
            width: 24px; 
            border-radius: 4px; 
            background: white; 
        }
        
        /* Video overlay for better text readability */
        .slide-overlay {
            position: absolute;
            inset: 0;
            /* background: rgba(0, 0, 0, 0.1); */
            z-index: 1;
        }
        
        /* Pause button for video slides */
        .video-control {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 25;
            /* background: rgba(0,0,0,0.5); */
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .video-control:hover {
            background: rgba(0,0,0,0.7);
            transform: scale(1.1);
        }

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
            @if(!empty($flightBgItems))
                {{-- slider wrap: overflow hidden শুধু এখানে, parent এ নয় --}}
                <div class="absolute inset-0 overflow-hidden">
                    <div id="bgSlider"
                         class="absolute inset-0 flex will-change-transform"
                         style="transition: transform 0.8s cubic-bezier(0.77,0,0.175,1)">
                        @foreach($flightBgItems as $index => $item)
                            <div class="bg-slide h-full flex-shrink-0 relative">
                                @if($item['type'] == 'video')
                                    <video 
                                        id="slide-video-{{ $index }}"
                                        class="slide-video"
                                        autoplay 
                                        muted 
                                        loop 
                                        playsinline
                                        data-playing="true">
                                        <source src="{{ $item['url'] }}" type="video/mp4">
                                        {{ __('Your browser does not support the video tag.') }}
                                    </video>
                                    <div class="slide-overlay"></div>
                                @else
                                    <div class="slide-image"
                                         style="background-image: url('{{ $item['url'] }}')">
                                    </div>
                                @endif
                                
                                {{-- Optional: Slide Title Overlay --}}
                                @if(!empty($item['title']))
                                    <div class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                                        <div class="text-center text-white px-4">
                                            <h2 class="text-3xl md:text-5xl font-bold mb-4 drop-shadow-lg">
                                                {{ $item['title'] }}
                                            </h2>
                                            @if(!empty($item['link']))
                                                <a href="{{ $item['link'] }}" 
                                                   class="inline-block bg-white/20 backdrop-blur-sm px-6 py-3 rounded-lg 
                                                          hover:bg-white/30 transition-all pointer-events-auto">
                                                    {{ __('Learn More') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
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

                {{-- Video Control Button --}}
                <button id="videoControlBtn"
                        class="video-control z-25 hidden"
                        style="display: none;">
                    <i class="fas fa-pause"></i>
                </button>

                {{-- Dots --}}
                <div id="sliderDots"
                     class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    @foreach($flightBgItems as $index => $item)
                        <button data-index="{{ $index }}"
                                class="slider-dot h-2 w-2 rounded-full border-0 p-0 cursor-pointer transition-all
                                       {{ $index === 0 ? 'active' : 'bg-white/40' }}">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Overlay (only for image slides, video has its own overlay) --}}
            {{-- <div class="absolute inset-0 z-10 pointer-events-none
                bg-gradient-to-br from-slate-900/30 via-blue-900/20 to-blue-800/20"></div> --}}

            {{-- Content --}}
            <div class="absolute inset-0 z-10 pointer-events-none"></div>
            <div class="relative z-20 h-full flex flex-col items-center justify-center px-4">

                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-lg">
                        {{ setting_item_with_lang("flight_page_search_title") ?? 'Search' }}
                    </h1>
                    {{-- <p class="text-white/90 mt-2 text-lg">Find the best deals on flights worldwide</p> --}}
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
            var videoControlBtn = document.getElementById('videoControlBtn');
            
            if (!slider) return;

            var slides = slider.querySelectorAll('.bg-slide');
            var total = slides.length;
            var current = 0;
            var timer = null;
            var currentVideo = null;
            
            // Store video elements and their states
            var videos = [];
            slides.forEach(function(slide, index) {
                var video = slide.querySelector('video');
                if (video) {
                    videos[index] = {
                        element: video,
                        isPlaying: true
                    };
                }
            });

            if (total <= 1) {
                if (btnPrev) btnPrev.style.display = 'none';
                if (btnNext) btnNext.style.display = 'none';
                if (dotsEl)  dotsEl.style.display  = 'none';
                if (videoControlBtn) videoControlBtn.style.display = 'none';
                return;
            }
            
            // Function to pause all videos
            function pauseAllVideos() {
                videos.forEach(function(videoObj) {
                    if (videoObj && videoObj.element && !videoObj.element.paused) {
                        videoObj.element.pause();
                        videoObj.isPlaying = false;
                    }
                });
            }
            
            // Function to play current video
            function playCurrentVideo() {
                if (videos[current] && videos[current].element) {
                    var video = videos[current].element;
                    if (video.paused) {
                        video.play().catch(function(e) {
                            console.log('Video play error:', e);
                        });
                        videos[current].isPlaying = true;
                    }
                }
            }
            
            // Function to show/hide video control button
            function updateVideoControlButton() {
                if (videos[current] && videos[current].element) {
                    videoControlBtn.style.display = 'flex';
                    var icon = videoControlBtn.querySelector('i');
                    if (videos[current].isPlaying) {
                        icon.className = 'fas fa-pause';
                    } else {
                        icon.className = 'fas fa-play';
                    }
                } else {
                    videoControlBtn.style.display = 'none';
                }
            }
            
            // Toggle video play/pause
            if (videoControlBtn) {
                videoControlBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (videos[current] && videos[current].element) {
                        var video = videos[current].element;
                        if (video.paused) {
                            video.play();
                            videos[current].isPlaying = true;
                        } else {
                            video.pause();
                            videos[current].isPlaying = false;
                        }
                        updateVideoControlButton();
                    }
                });
            }

            function goTo(index) {
                index = ((index % total) + total) % total;
                
                // Pause all videos before switching
                pauseAllVideos();
                
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
                
                // Play video if current slide has video
                playCurrentVideo();
                
                // Update video control button visibility
                updateVideoControlButton();
            }

            function startTimer() {
                clearInterval(timer);
                timer = setInterval(function () { 
                    goTo(current + 1); 
                }, 8000);
            }

            if (btnPrev) btnPrev.addEventListener('click', function () { 
                goTo(current - 1); 
                startTimer(); 
            });
            
            if (btnNext) btnNext.addEventListener('click', function () { 
                goTo(current + 1); 
                startTimer(); 
            });
            
            var dots = dotsEl ? dotsEl.querySelectorAll('.slider-dot') : [];
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () { 
                    goTo(i); 
                    startTimer(); 
                });
            });

            var tx = 0;
            slider.addEventListener('touchstart', function (e) { 
                tx = e.touches[0].clientX; 
            }, { passive: true });
            
            slider.addEventListener('touchend', function (e) {
                var diff = tx - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) { 
                    goTo(diff > 0 ? current + 1 : current - 1); 
                    startTimer(); 
                }
            });
            
            // Handle page visibility (pause video when tab is not visible)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (videos[current] && videos[current].element) {
                        videos[current].element.pause();
                    }
                } else {
                    if (videos[current] && videos[current].element && videos[current].isPlaying) {
                        videos[current].element.play();
                    }
                }
            });
            
            // Initialize: play first video if exists
            playCurrentVideo();
            updateVideoControlButton();
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