
{{--    <!DOCTYPE html>--}}
{{--<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{$html_class ?? ''}}">--}}
{{--<head>--}}
{{--    <meta charset="utf-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1">--}}
{{--    <!-- CSRF Token -->--}}
{{--    <meta name="csrf-token" content="{{ csrf_token() }}">--}}
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">--}}

{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">--}}

{{--    <!-- Tailwind CSS CDN -->--}}
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}

{{--    @php $favicon = setting_item('site_favicon'); @endphp--}}
{{--    @if($favicon)--}}
{{--        @php--}}
{{--            $file = (new \Modules\Media\Models\MediaFile())->findById($favicon);--}}
{{--        @endphp--}}
{{--        @if(!empty($file))--}}
{{--            <link rel="icon" type="{{$file['file_type']}}" href="{{asset('uploads/'.$file['file_path'])}}" />--}}
{{--        @else:--}}
{{--        <link rel="icon" type="image/png" href="{{url('images/favicon.png')}}" />--}}
{{--        @endif--}}
{{--    @endif--}}

{{--    @include('Layout::parts.seo-meta')--}}

{{--    <!-- Stylesheets -->--}}
{{--    <link href="{{ asset('themes/gotrip/css/vendors.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ asset('themes/gotrip/css/main.css') }}" rel="stylesheet">--}}
{{--    <link href="{{ asset('libs/icofont/icofont.min.css') }}" rel="stylesheet">--}}
{{--    <link rel="stylesheet" type="text/css" href="{{ asset("libs/daterange/daterangepicker.css") }}">--}}
{{--    <link href="{{ asset('libs/carousel-2/owl.carousel.css') }}" rel="stylesheet">--}}

{{--    <!-- Fonts -->--}}
{{--    <link rel="preconnect" href="https://fonts.googleapis.com">--}}
{{--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">--}}
{{--    <link rel="stylesheet" href="{{ asset('themes/gotrip/dist/frontend/css/app.css?_v='.config('app.asset_version')) }}">--}}

{{--    @if(setting_item('cookie_agreement_type')=='cookie_consent')--}}
{{--        <link rel="stylesheet" href="{{asset('libs/cookie-consent/cookieconsent.css')}}" media="print" onload="this.media='all'">--}}
{{--    @endif--}}

{{--    {!! \App\Helpers\Assets::css() !!}--}}
{{--    {!! \App\Helpers\Assets::js() !!}--}}
{{--    @include('Layout::parts.global-script')--}}

{{--    @stack('css')--}}

{{--    <!-- Custom Tailwind Configuration -->--}}
{{--    <script>--}}
{{--        tailwind.config = {--}}
{{--            theme: {--}}
{{--                extend: {--}}
{{--                    animation: {--}}
{{--                        'gradient-shift': 'gradient-shift 8s ease infinite',--}}
{{--                        'scroll-smooth': 'scroll-smooth 40s linear infinite',--}}
{{--                    },--}}
{{--                    keyframes: {--}}
{{--                        'gradient-shift': {--}}
{{--                            '0%, 100%': { backgroundPosition: '0% 50%' },--}}
{{--                            '50%': { backgroundPosition: '100% 50%' },--}}
{{--                        },--}}
{{--                        'scroll-smooth': {--}}
{{--                            '0%': { transform: 'translateX(0)' },--}}
{{--                            '100%': { transform: 'translateX(-50%)' },--}}
{{--                        }--}}
{{--                    }--}}
{{--                }--}}
{{--            }--}}
{{--        }--}}
{{--    </script>--}}

{{--    <!-- Additional Styles for Smooth Scrolling -->--}}
{{--    <style>--}}
{{--        .scrolling-wrapper {--}}
{{--            overflow: hidden;--}}
{{--            position: relative;--}}
{{--        }--}}

{{--        .scrolling-content {--}}
{{--            display: flex;--}}
{{--            width: max-content;--}}
{{--            animation: scroll-smooth 20s linear infinite;--}}
{{--            will-change: transform;--}}
{{--            transform: translate3d(0, 0, 0);--}}
{{--        }--}}

{{--        @keyframes scroll-smooth {--}}
{{--            from { transform: translate3d(0, 0, 0); }--}}
{{--            to { transform: translate3d(-50%, 0, 0); }--}}
{{--        }--}}

{{--        .scrolling-content:hover {--}}
{{--            animation-play-state: paused;--}}
{{--        }--}}

{{--        body {--}}
{{--            padding-top: 3.125rem;--}}
{{--        }--}}

{{--        .bravo_header,--}}
{{--        header,--}}
{{--        .header {--}}
{{--            top: 3.125rem !important;--}}
{{--        }--}}

{{--        @media (max-width: 768px) {--}}
{{--            body {--}}
{{--                padding-top: 7.5rem;--}}
{{--            }--}}

{{--            .bravo_header,--}}
{{--            header,--}}
{{--            .header {--}}
{{--                top: 7.5rem !important;--}}
{{--            }--}}
{{--        }--}}
{{--    </style>--}}

{{--    <link href="{{ route('core.style.customCss') }}" rel="stylesheet">--}}

{{--    @if(setting_item_with_lang('enable_rtl'))--}}
{{--        <link href="{{ asset('themes/gotrip/dist/frontend/css/rtl.css') }}" rel="stylesheet">--}}
{{--    @endif--}}

{{--    @if(!is_demo_mode())--}}
{{--        {!! setting_item('head_scripts') !!}--}}
{{--        {!! setting_item_with_lang_raw('head_scripts') !!}--}}
{{--    @endif--}}
{{--</head>--}}

{{--<body class="frontend-page {{ !empty($row->header_style) ? "header-".$row->header_style : "header-normal" }} {{$body_class ?? ''}} @if(setting_item_with_lang('enable_rtl')) is-rtl @endif @if(is_api()) is_api @endif">--}}

{{--@if(!is_demo_mode())--}}
{{--    {!! setting_item('body_scripts') !!}--}}
{{--    {!! setting_item_with_lang_raw('body_scripts') !!}--}}
{{--@endif--}}

{{--<!-- Modern Announcement Bar with Tailwind CSS -->--}}
{{--<div class="fixed top-0 left-0 right-0 z-[9999] bg-gradient-to-r from-blue-300 via-indigo-800 to-blue-300 bg-[length:200%_100%] animate-gradient-shift text-white shadow-lg border-b-2 border-white/10">--}}
{{--    <div class="container-fluid mx-auto">--}}
{{--        <!-- Desktop Layout -->--}}
{{--        @php--}}
{{--            use Modules\User\Models\Announcement;--}}
{{--            $announcements = Announcement::active()->get();--}}

{{--             $firstAnnouncement = $announcements->first();--}}
{{--             $scrollSpeed = $firstAnnouncement ? $firstAnnouncement->scroll_speed : 30;--}}
{{--        @endphp--}}

{{--        <div class="hidden lg:flex items-center justify-between px-5 py-1 gap-5">--}}

{{--            <!-- Left Side: Date & Time -->--}}
{{--            <div class="flex items-center gap-5 bg-white/10 backdrop-blur-sm px-1 py-2 rounded-lg border border-white/20 shadow-md">--}}
{{--                <div class="flex items-center gap-2">--}}
{{--                    <i class="far fa-calendar-alt text-base opacity-90"></i>--}}
{{--                    <span id="liveDate" class="text-sm font-semibold whitespace-nowrap"></span>--}}
{{--                </div>--}}
{{--                <div class="w-px h-5 bg-white/30"></div>--}}
{{--                <div class="flex items-center gap-2">--}}
{{--                    <i class="far fa-clock text-base opacity-90"></i>--}}
{{--                    <span id="liveTime" class="text-sm font-semibold whitespace-nowrap"></span>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm px-2 py-1.5 rounded-lg border border-white/20 shadow-md">--}}
{{--                <a href="https://mothercompany.com" target="_blank" rel="noopener noreferrer">--}}
{{--                    <img src="{{ asset('Custom_logo/shopnodhora.jpg') }}"--}}
{{--                         alt="Mother Company"--}}
{{--                         style="height: clamp(22px, 3vw, 35px); width: auto; max-width: 180px; min-width: 120px;"--}}
{{--                         class="hover:opacity-80 transition-opacity duration-200 object-contain">--}}
{{--                </a>--}}

{{--                <div class="w-px h-5 bg-white/30"></div>--}}

{{--                <div class="flex items-center gap-1.5">--}}
{{--                    <i class="far fa-clock text-sm opacity-90"></i>--}}
{{--                    <span id="liveTime" class="text-xs sm:text-sm font-semibold whitespace-nowrap"></span>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- Center: Scrolling Text -->--}}
{{--            <div class="flex-1 scrolling-wrapper mx-5">--}}
{{--                <div class="scrolling-content" style="animation-duration: {{ $scrollSpeed }}s;">--}}
{{--                    @if($announcements->count() > 0)--}}
{{--                        --}}{{-- Database Announcements --}}
{{--                        @foreach($announcements as $announcement)--}}
{{--                            <span class="text-base font-semibold tracking-wide drop-shadow-lg whitespace-nowrap {{ !$loop->first ? 'ml-20' : '' }}">--}}
{{--                        {{ $announcement->getFormattedContent() }}--}}
{{--                    </span>--}}
{{--                        @endforeach--}}

{{--                        --}}{{-- Duplicate for seamless loop --}}
{{--                        @foreach($announcements as $announcement)--}}
{{--                            <span class="text-base font-semibold tracking-wide drop-shadow-lg whitespace-nowrap ml-20">--}}
{{--                            {{ $announcement->getFormattedContent() }}--}}
{{--                             </span>--}}
{{--                        @endforeach--}}
{{--                    @else--}}
{{--                        --}}{{-- Fallback Static --}}
{{--                        <span class="text-base font-semibold tracking-wide drop-shadow-lg whitespace-nowrap">--}}
{{--                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস - আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates, Qatar Airways, Turkish Airlines, Malaysia Airlines টিকেটে বিশেষ ছাড়! 🎊 হজ্জ, ওমরাহ ও বিশ্বভ্রমণে আমরা আপনার সাথে আছি! 📞 যোগাযোগ করুন আজই! 💼--}}
{{--                        </span>--}}
{{--                                <span class="text-base font-semibold tracking-wide drop-shadow-lg whitespace-nowrap ml-20">--}}
{{--                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস - আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates, Qatar Airways, Turkish Airlines, Malaysia Airlines টিকেটে বিশেষ ছাড়! 🎊 হজ্জ, ওমরাহ ও বিশ্বভ্রমণে আমরা আপনার সাথে আছি! 📞 যোগাযোগ করুন আজই! 💼--}}
{{--                        </span>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- Right Side: Contact -->--}}
{{--            <div class="flex items-center gap-5 bg-white/10 backdrop-blur-sm px-1 py-2 rounded-lg border border-white/20 shadow-md">--}}
{{--                <a href="tel:+8801958553918" class="flex items-center gap-2 hover:text-white/80 transition-all duration-300">--}}
{{--                    <i class="fas fa-phone-alt text-base opacity-90"></i>--}}
{{--                    <span class="text-sm font-semibold whitespace-nowrap">+880 1958 553918</span>--}}
{{--                </a>--}}
{{--                <div class="w-px h-5 bg-white/30"></div>--}}
{{--                <a href="mailto:info@shopnotours.com" class="flex items-center gap-2 hover:text-white/80 transition-all duration-300">--}}
{{--                    <i class="far fa-envelope text-base opacity-90"></i>--}}
{{--                    <span class="text-sm font-semibold whitespace-nowrap">Email</span>--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <!-- Tablet & Mobile Layout -->--}}
{{--        <div class="flex lg:hidden flex-col items-center gap-3 px-4 py-3">--}}

{{--            <!-- Date & Time -->--}}
{{--            <div class="flex items-center gap-5 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/20 w-full justify-center">--}}
{{--                <div class="flex items-center gap-2">--}}
{{--                    <i class="far fa-calendar-alt text-sm opacity-90"></i>--}}
{{--                    <span id="mobileLiveDate" class="text-xs font-semibold"></span>--}}
{{--                </div>--}}
{{--                <div class="w-px h-4 bg-white/30"></div>--}}
{{--                <div class="flex items-center gap-2">--}}
{{--                    <i class="far fa-clock text-sm opacity-90"></i>--}}
{{--                    <span id="mobileLiveTime" class="text-xs font-semibold"></span>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- Scrolling Text -->--}}
{{--            <div class="w-full scrolling-wrapper">--}}
{{--                <div class="scrolling-content">--}}
{{--                        <span class="text-sm font-semibold tracking-wide drop-shadow-lg whitespace-nowrap">--}}
{{--                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস - আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates, Qatar Airways টিকেটে বিশেষ ছাড়! 📞 যোগাযোগ করুন!--}}
{{--                        </span>--}}
{{--                    <span class="text-sm font-semibold tracking-wide drop-shadow-lg whitespace-nowrap ml-16">--}}
{{--                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস - আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates, Qatar Airways টিকেটে বিশেষ ছাড়! 📞 যোগাযোগ করুন!--}}
{{--                        </span>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <!-- Contact Info -->--}}
{{--            <div class="flex items-center gap-5 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg border border-white/20 w-full justify-center">--}}
{{--                <a href="tel:+8801958553918" class="flex items-center gap-2 hover:text-white/80 transition-all">--}}
{{--                    <i class="fas fa-phone-alt text-sm opacity-90"></i>--}}
{{--                    <span class="text-xs font-semibold">+880 1958 553918</span>--}}
{{--                </a>--}}
{{--                <div class="w-px h-4 bg-white/30"></div>--}}
{{--                <a href="mailto:info@shopnotours.com" class="flex items-center gap-2 hover:text-white/80 transition-all">--}}
{{--                    <i class="far fa-envelope text-sm opacity-90"></i>--}}
{{--                    <span class="text-xs font-semibold">Email</span>--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

{{--<!-- Main Wrapper -->--}}
{{--<div class="bravo_wrap overflow-hidden">--}}

{{--    <!-- Header Margin -->--}}
{{--    @if(!empty($row))--}}
{{--        @php $hideHeaderMargin = ['transparent','transparent_v2','transparent_v3','transparent_v4','transparent_v5','transparent_v6','transparent_v7','transparent_v8','transparent_v9'] @endphp--}}
{{--        @if(!in_array($row->header_style,$hideHeaderMargin))--}}
{{--            <div class="header-margin"></div>--}}
{{--        @endif--}}
{{--    @else--}}
{{--        @if(empty($hide_header_margin))--}}
{{--            <div class="header-margin"></div>--}}
{{--        @endif--}}
{{--    @endif--}}

{{--    @include('Layout::parts.preload')--}}
{{--    @include('Layout::parts.header')--}}

{{--    @yield('content')--}}

{{--    @include('Layout::parts.footer')--}}
{{--</div>--}}

{{--@if(!is_demo_mode())--}}
{{--    {!! setting_item('footer_scripts') !!}--}}
{{--    {!! setting_item_with_lang_raw('footer_scripts') !!}--}}
{{--@endif--}}

{{--<!-- Tawk.to Live Chat -->--}}
{{--<script type="text/javascript">--}}
{{--    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();--}}
{{--    (function(){--}}
{{--        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];--}}
{{--        s1.async=true;--}}
{{--        s1.src='https://embed.tawk.to/6970b70023ac1a197c06be66/1jfg4lmt8';--}}
{{--        s1.charset='UTF-8';--}}
{{--        s1.setAttribute('crossorigin','*');--}}
{{--        s0.parentNode.insertBefore(s1,s0);--}}
{{--    })();--}}
{{--</script>--}}

{{--<!-- WhatsApp Widget -->--}}
{{--<script type="text/javascript">--}}
{{--    (function () {--}}
{{--        var options = {--}}
{{--            whatsapp: "+8801958553918",--}}
{{--            call_to_action: "Message us",--}}
{{--            button_color: "#25D366",--}}
{{--            position: "right",--}}
{{--        };--}}
{{--        var proto = 'https:', host = "getbutton.io", url = proto + '//static.' + host;--}}
{{--        var s = document.createElement('script');--}}
{{--        s.type = 'text/javascript';--}}
{{--        s.async = true;--}}
{{--        s.src = url + '/widget-send-button/js/init.js';--}}
{{--        s.onload = function () { WhWidgetSendButton.init(host, proto, options); };--}}
{{--        var x = document.getElementsByTagName('script')[0];--}}
{{--        x.parentNode.insertBefore(s, x);--}}
{{--    })();--}}
{{--</script>--}}

{{--<!-- Date/Time Update Script -->--}}
{{--<script>--}}
{{--    function updateLiveDateTime() {--}}
{{--        const now = new Date();--}}

{{--        // Bangla Date--}}
{{--        const dateOptions = {--}}
{{--            weekday: 'long',--}}
{{--            year: 'numeric',--}}
{{--            month: 'long',--}}
{{--            day: 'numeric',--}}
{{--            timeZone: 'Asia/Dhaka'--}}
{{--        };--}}

{{--        const banglaDate = now.toLocaleDateString('bn-BD', dateOptions);--}}

{{--        // Update desktop date--}}
{{--        const dateElement = document.getElementById('liveDate');--}}
{{--        if (dateElement) {--}}
{{--            dateElement.textContent = banglaDate;--}}
{{--        }--}}

{{--        // Update mobile date--}}
{{--        const mobileDateElement = document.getElementById('mobileLiveDate');--}}
{{--        if (mobileDateElement) {--}}
{{--            mobileDateElement.textContent = banglaDate;--}}
{{--        }--}}

{{--        // Bangla Time with AM/PM--}}
{{--        const timeOptions = {--}}
{{--            hour: '2-digit',--}}
{{--            minute: '2-digit',--}}
{{--            second: '2-digit',--}}
{{--            hour12: true,--}}
{{--            timeZone: 'Asia/Dhaka'--}}
{{--        };--}}

{{--        const banglaTime = now.toLocaleTimeString('bn-BD', timeOptions);--}}

{{--        // Update desktop time--}}
{{--        const timeElement = document.getElementById('liveTime');--}}
{{--        if (timeElement) {--}}
{{--            timeElement.textContent = banglaTime;--}}
{{--        }--}}

{{--        // Update mobile time--}}
{{--        const mobileTimeElement = document.getElementById('mobileLiveTime');--}}
{{--        if (mobileTimeElement) {--}}
{{--            mobileTimeElement.textContent = banglaTime;--}}
{{--        }--}}
{{--    }--}}

{{--    // Update immediately--}}
{{--    updateLiveDateTime();--}}

{{--    // Update every second--}}
{{--    setInterval(updateLiveDateTime, 1000);--}}

{{--    // Ensure it runs after page load--}}
{{--    document.addEventListener('DOMContentLoaded', updateLiveDateTime);--}}
{{--</script>--}}
{{--<script>--}}
{{--    window.VisitorTrackerConfig = {--}}
{{--        pageEnterUrl : "{{ route('visitor.page.enter') }}",--}}
{{--        pageExitUrl  : "{{ route('visitor.page.exit') }}",--}}
{{--        activityUrl  : "{{ route('visitor.activity') }}",--}}
{{--        csrfToken    : "{{ csrf_token() }}",--}}
{{--        currentPage  : "{{ url()->current() }}",--}}
{{--        sessionData  : {!! json_encode(--}}
{{--            collect(session()->all())--}}
{{--                ->except(['_token', 'password', '_flash', 'login_web'])--}}
{{--                ->filter(fn($v, $k) => !str_starts_with($k, '_'))--}}
{{--                ->toArray()--}}
{{--        ) !!},--}}
{{--    };--}}
{{--</script>--}}
{{--<script>--}}
{{--    document.addEventListener('DOMContentLoaded', function () {--}}
{{--        const btn = document.getElementById('scrollTopBtn');--}}
{{--        if (!btn) return;--}}

{{--        window.addEventListener('scroll', function () {--}}
{{--            if (window.scrollY > 400) {--}}
{{--                btn.classList.add('visible');--}}
{{--            } else {--}}
{{--                btn.classList.remove('visible');--}}
{{--            }--}}
{{--        }, { passive: true });--}}

{{--        btn.addEventListener('click', function () {--}}
{{--            window.scrollTo({ top: 0, behavior: 'smooth' });--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}

{{--<script src="{{ asset('js/visitor-tracker.js') }}"></script>--}}

{{--<!-- Widget Position Adjustment -->--}}
{{--<style>--}}
{{--    .tawk-button {--}}
{{--        bottom: 20px !important;--}}
{{--    }--}}

{{--    div[id^="gb-widget"] {--}}
{{--        bottom: 110px !important;--}}
{{--    }--}}

{{--    body > div:last-child {--}}
{{--        bottom: 110px !important;--}}
{{--    }--}}
{{--</style>--}}

{{--<style>--}}
{{--    #scrollTopBtn {--}}
{{--        position: fixed;--}}
{{--        bottom: 90px;--}}
{{--        right: 150px;--}}
{{--        width: 46px;--}}
{{--        height: 46px;--}}
{{--        background: linear-gradient(135deg, #2563eb, #1d4ed8);--}}
{{--        color: white;--}}
{{--        border: none;--}}
{{--        border-radius: 50%;--}}
{{--        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.45);--}}
{{--        cursor: pointer;--}}
{{--        z-index: 9000;--}}
{{--        display: flex;--}}
{{--        align-items: center;--}}
{{--        justify-content: center;--}}
{{--        padding: 0;--}}
{{--        font-size: 16px;--}}
{{--        opacity: 0;--}}
{{--        visibility: hidden;--}}
{{--        transform: translateY(16px) scale(0.85);--}}
{{--        transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, box-shadow 0.25s ease;--}}
{{--    }--}}

{{--    #scrollTopBtn.visible {--}}
{{--        opacity: 1;--}}
{{--        visibility: visible;--}}
{{--        transform: translateY(0) scale(1);--}}
{{--    }--}}

{{--    #scrollTopBtn:hover {--}}
{{--        transform: translateY(-3px) scale(1.08);--}}
{{--        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.55);--}}
{{--    }--}}

{{--    #scrollTopBtn i {--}}
{{--        display: flex;--}}
{{--        align-items: center;--}}
{{--        justify-content: center;--}}
{{--        width: 100%;--}}
{{--        height: 100%;--}}
{{--        pointer-events: none;--}}
{{--    }--}}

{{--    @media (max-width: 768px) {--}}
{{--        #scrollTopBtn {--}}
{{--            bottom: 90px;--}}
{{--            right: 80px;--}}
{{--            width: 42px;--}}
{{--            height: 42px;--}}
{{--            font-size: 14px;--}}
{{--        }--}}
{{--    }--}}
{{--</style>--}}
{{--<button id="scrollTopBtn" aria-label="Scroll to top" title="Back to top">--}}
{{--    <i class="fas fa-chevron-up"></i>--}}
{{--</button>--}}
{{--</body>--}}
{{--</html>--}}


    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{$html_class ?? ''}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    @php $favicon = setting_item('site_favicon'); @endphp
    @if($favicon)
        @php
            $file = (new \Modules\Media\Models\MediaFile())->findById($favicon);
        @endphp
        @if(!empty($file))
            <link rel="icon" type="{{$file['file_type']}}" href="{{asset('uploads/'.$file['file_path'])}}" />
        @else:
        <link rel="icon" type="image/png" href="{{url('images/favicon.png')}}" />
        @endif
    @endif

    @include('Layout::parts.seo-meta')

    <!-- Stylesheets -->
    <link href="{{ asset('themes/gotrip/css/vendors.css') }}" rel="stylesheet">
    <link href="{{ asset('themes/gotrip/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/icofont/icofont.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/daterange/daterangepicker.css") }}">
    <link href="{{ asset('libs/carousel-2/owl.carousel.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/gotrip/dist/frontend/css/app.css?_v='.config('app.asset_version')) }}">

    @if(setting_item('cookie_agreement_type')=='cookie_consent')
        <link rel="stylesheet" href="{{asset('libs/cookie-consent/cookieconsent.css')}}" media="print" onload="this.media='all'">
    @endif

    {!! \App\Helpers\Assets::css() !!}
    {!! \App\Helpers\Assets::js() !!}
    @include('Layout::parts.global-script')

    @stack('css')

    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'gradient-shift': 'gradient-shift 8s ease infinite',
                        'scroll-smooth': 'scroll-smooth 40s linear infinite',
                    },
                    keyframes: {
                        'gradient-shift': {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        },
                        'scroll-smooth': {
                            '0%': { transform: 'translateX(0)' },
                            '100%': { transform: 'translateX(-50%)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Scrolling ticker ── */
        .scrolling-wrapper {
            overflow: hidden;
            position: relative;
        }
        .scrolling-content {
            display: flex;
            width: max-content;
            animation: scroll-smooth 20s linear infinite;
            will-change: transform;
            transform: translate3d(0, 0, 0);
        }
        @keyframes scroll-smooth {
            from { transform: translate3d(0, 0, 0); }
            to   { transform: translate3d(-50%, 0, 0); }
        }
        .scrolling-content:hover { animation-play-state: paused; }
    </style>

    <link href="{{ route('core.style.customCss') }}" rel="stylesheet">

    @if(setting_item_with_lang('enable_rtl'))
        <link href="{{ asset('themes/gotrip/dist/frontend/css/rtl.css') }}" rel="stylesheet">
    @endif

    @if(!is_demo_mode())
        {!! setting_item('head_scripts') !!}
        {!! setting_item_with_lang_raw('head_scripts') !!}
    @endif
</head>

<body class="frontend-page {{ !empty($row->header_style) ? "header-".$row->header_style : "header-normal" }} {{$body_class ?? ''}} @if(setting_item_with_lang('enable_rtl')) is-rtl @endif @if(is_api()) is_api @endif">

@if(!is_demo_mode())
    {!! setting_item('body_scripts') !!}
    {!! setting_item_with_lang_raw('body_scripts') !!}
@endif

@php
    use Modules\User\Models\Announcement;
    $announcements      = Announcement::active()->get();
    $firstAnnouncement  = $announcements->first();
    $scrollSpeed        = $firstAnnouncement ? $firstAnnouncement->scroll_speed : 30;
@endphp

    <!-- ══════════════════════════════════════════
     ANNOUNCEMENT BAR  (fixed, full-width)
     ══════════════════════════════════════════ -->
<div id="ann-bar"
     class="fixed top-0 left-0 right-0 z-[9999]
            bg-gradient-to-r from-blue-300 via-indigo-800 to-blue-300
            bg-[length:200%_100%] animate-gradient-shift
            text-white shadow-lg border-b border-white/10">

    <div class="h-full flex items-center px-3 lg:px-5 gap-3">

        {{-- ── DESKTOP: date | ticker | contact ── --}}
        <div class="hidden lg:flex items-center justify-between w-full gap-4 h-full">

            {{-- Date & Time --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="flex items-center gap-1.5">
                    <i class="far fa-calendar-alt text-xs opacity-80"></i>
                    <span id="liveDate" class="text-xs font-semibold whitespace-nowrap"></span>
                </div>
                <div class="w-px h-3.5 bg-white/30"></div>
                <div class="flex items-center gap-1.5">
                    <i class="far fa-clock text-xs opacity-80"></i>
                    <span id="liveTime" class="text-xs font-semibold whitespace-nowrap"></span>
                </div>
            </div>

            {{-- Scrolling ticker --}}
            <div class="flex-1 scrolling-wrapper mx-4 overflow-hidden">
                <div class="scrolling-content" style="animation-duration:{{ $scrollSpeed }}s;">
                    @if($announcements->count() > 0)
                        @foreach($announcements as $ann)
                            <span class="text-xs font-semibold tracking-wide whitespace-nowrap {{ !$loop->first ? 'ml-16' : '' }}">
                                {{ $ann->getFormattedContent() }}
                            </span>
                        @endforeach
                        @foreach($announcements as $ann)
                            <span class="text-xs font-semibold tracking-wide whitespace-nowrap ml-16">
                                {{ $ann->getFormattedContent() }}
                            </span>
                        @endforeach
                    @else
                        <span class="text-xs font-semibold tracking-wide whitespace-nowrap">
                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস — আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates · Qatar Airways · Turkish Airlines · Malaysia Airlines — বিশেষ ছাড়! 🎊 হজ্জ · ওমরাহ · বিশ্বভ্রমণ 📞 আজই যোগাযোগ করুন!
                        </span>
                        <span class="text-xs font-semibold tracking-wide whitespace-nowrap ml-16">
                            🌟 স্বপ্ন ট্যুর অ্যান্ড ট্রাভেলস — আপনার বিশ্বস্ত ভ্রমণ সঙ্গী! ✈️ Emirates · Qatar Airways · Turkish Airlines · Malaysia Airlines — বিশেষ ছাড়! 🎊 হজ্জ · ওমরাহ · বিশ্বভ্রমণ 📞 আজই যোগাযোগ করুন!
                        </span>
                    @endif
                </div>
            </div>

            {{-- Contact --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="tel:+8801958553918"
                   class="flex items-center gap-1.5 hover:text-white/80 transition-colors">
                    <i class="fas fa-phone-alt text-xs opacity-80"></i>
                    <span class="text-xs font-semibold whitespace-nowrap">+880 1958 553918</span>
                </a>
                <div class="w-px h-3.5 bg-white/30"></div>
                <a href="/cdn-cgi/l/email-protection#7a13141c153a0912150a14150e150f080954191517" class="flex items-center gap-1.5 hover:text-white/80 transition-colors">
                    <i class="far fa-envelope text-xs opacity-80"></i>
                    <span class="text-xs font-semibold">Email</span>
                </a>
            </div>
        </div>

        {{-- ── MOBILE: single compact row — clock | ticker ── --}}
        <div class="flex lg:hidden items-center w-full gap-2 h-full overflow-hidden">

            {{-- Live time (compact) --}}
            <div class="flex items-center gap-1 shrink-0">
                <i class="far fa-clock text-[10px] opacity-75"></i>
                <span id="mobileLiveTime" class="text-[10px] font-semibold whitespace-nowrap"></span>
            </div>

            <div class="w-px h-3 bg-white/30 shrink-0"></div>

            {{-- Scrolling ticker --}}
            <div class="flex-1 scrolling-wrapper overflow-hidden">
                <div class="scrolling-content" style="animation-duration:{{ $scrollSpeed }}s;">
                    @if($announcements->count() > 0)
                        @foreach($announcements as $ann)
                            <span class="text-[10px] font-semibold whitespace-nowrap {{ !$loop->first ? 'ml-10' : '' }}">
                                {{ $ann->getFormattedContent() }}
                            </span>
                        @endforeach
                        @foreach($announcements as $ann)
                            <span class="text-[10px] font-semibold whitespace-nowrap ml-10">
                                {{ $ann->getFormattedContent() }}
                            </span>
                        @endforeach
                    @else
                        <span class="text-[10px] font-semibold whitespace-nowrap">
                            🌟 স্বপ্ন ট্যুর — ✈️ Emirates · Qatar Airways বিশেষ ছাড়! 📞 যোগাযোগ করুন!
                        </span>
                        <span class="text-[10px] font-semibold whitespace-nowrap ml-10">
                            🌟 স্বপ্ন ট্যুর — ✈️ Emirates · Qatar Airways বিশেষ ছাড়! 📞 যোগাযোগ করুন!
                        </span>
                    @endif
                </div>
            </div>

            {{-- Phone icon only --}}
            <a href="tel:+8801958553918"
               class="shrink-0 flex items-center gap-1 hover:text-white/80 transition-colors">
                <i class="fas fa-phone-alt text-[10px] opacity-80"></i>
                <span class="text-[10px] font-semibold hidden xs:inline whitespace-nowrap">553918</span>
            </a>
        </div>

    </div>
</div>


<!-- Main Wrapper -->
<div class="bravo_wrap overflow-hidden">

    @if(!empty($row))
        @php $hideHeaderMargin = ['transparent','transparent_v2','transparent_v3','transparent_v4','transparent_v5','transparent_v6','transparent_v7','transparent_v8','transparent_v9'] @endphp
        @if(!in_array($row->header_style,$hideHeaderMargin))
            <div class="header-margin"></div>
        @endif
    @else
        @if(empty($hide_header_margin))
            <div class="header-margin"></div>
        @endif
    @endif

    @include('Layout::parts.preload')
    @include('Layout::parts.header')

    @yield('content')

    @include('Layout::parts.footer')
</div>

@if(!is_demo_mode())
    {!! setting_item('footer_scripts') !!}
    {!! setting_item_with_lang_raw('footer_scripts') !!}
@endif

<!-- Tawk.to Live Chat -->


<!-- Date/Time Script -->
<script>
    function updateLiveDateTime() {
        var now = new Date();

        var dateOpts = { weekday:'long', year:'numeric', month:'long', day:'numeric', timeZone:'Asia/Dhaka' };
        var timeOpts = { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true, timeZone:'Asia/Dhaka' };
        var timeOptsShort = { hour:'2-digit', minute:'2-digit', hour12:true, timeZone:'Asia/Dhaka' };

        var banglaDate = now.toLocaleDateString('bn-BD', dateOpts);
        var banglaTime = now.toLocaleTimeString('bn-BD', timeOpts);
        var banglaTimeShort = now.toLocaleTimeString('bn-BD', timeOptsShort);

        var el;
        if((el = document.getElementById('liveDate')))       el.textContent = banglaDate;
        if((el = document.getElementById('liveTime')))       el.textContent = banglaTime;
        if((el = document.getElementById('mobileLiveTime'))) el.textContent = banglaTimeShort;
    }
    updateLiveDateTime();
    setInterval(updateLiveDateTime, 1000);
    document.addEventListener('DOMContentLoaded', updateLiveDateTime);
</script>

<script>
    window.VisitorTrackerConfig = {
        pageEnterUrl : "{{ route('visitor.page.enter') }}",
        pageExitUrl  : "{{ route('visitor.page.exit') }}",
        activityUrl  : "{{ route('visitor.activity') }}",
        csrfToken    : "{{ csrf_token() }}",
        currentPage  : "{{ url()->current() }}",
        sessionData  : {!! json_encode(
            collect(session()->all())
                ->except(['_token', 'password', '_flash', 'login_web'])
                ->filter(fn($v, $k) => !str_starts_with($k, '_'))
                ->toArray()
        ) !!},
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('scrollTopBtn');
        if (!btn) return;
        window.addEventListener('scroll', function () {
            btn.classList.toggle('visible', window.scrollY > 400);
        }, { passive: true });
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>

<script src="{{ asset('js/visitor-tracker.js') }}"></script>

<style>
    /* ── Tawk / WhatsApp widget positions ── */
    .tawk-button          { bottom: 20px !important; }
    div[id^="gb-widget"]  { bottom: 110px !important; }

    /* ── Scroll-to-top button ── */
    #scrollTopBtn {
        position: fixed;
        bottom: 90px; right: 150px;
        width: 42px; height: 42px;
        background: linear-gradient(135deg,#2563eb,#1d4ed8);
        color: white; border: none; border-radius: 50%;
        box-shadow: 0 4px 16px rgba(37,99,235,.45);
        cursor: pointer; z-index: 9000;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        opacity: 0; visibility: hidden;
        transform: translateY(16px) scale(.85);
        transition: opacity .3s, visibility .3s, transform .3s, box-shadow .25s;
    }
    #scrollTopBtn.visible {
        opacity: 1; visibility: visible;
        transform: translateY(0) scale(1);
    }
    #scrollTopBtn:hover {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 8px 20px rgba(37,99,235,.55);
    }
    @media (max-width: 768px) {
        #scrollTopBtn { bottom: 80px; right: 16px; width: 38px; height: 38px; font-size: 13px; }
    }
</style>

{{-- ══════════════════════════════════════════════════
     OVERRIDE CSS — must be LAST so theme cannot override
     ══════════════════════════════════════════════════ --}}
<style>
    /* Gap fix: announcement bar + header positioning */
    #ann-bar {
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
    }
    body {
        padding-top: 36px !important;
        margin-top: 0 !important;
    }
    #site-header-wrapper {
        position: fixed !important;
        top: 36px !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 9998 !important;
    }
    /* Prevent theme from moving the header */
    .bravo_header,
    header.header,
    header.js-header {
        position: relative !important;
        top: auto !important;
    }
    /* Mobile: ann-bar=32px, mobile-topbar=48px → total 80px */
    @media screen and (max-width: 1279px) {
        #ann-bar {
            height: 32px !important;
            min-height: 32px !important;
            max-height: 32px !important;
        }
        body { padding-top: 80px !important; }
        /* Hide theme's header-margin — our body padding-top handles the offset */
        .header-margin { display: none !important; }
    }
</style>

<button id="scrollTopBtn" aria-label="Scroll to top" title="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>
