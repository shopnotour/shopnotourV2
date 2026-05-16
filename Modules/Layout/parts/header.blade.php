{{--<div class="bravo_header {{ setting_item('enable_header_sticky',0) == 1 ? "has_sticky" :""  }}">--}}
{{--    <div class="{{$container_class ?? 'container'}}">--}}
{{--        <div class="content">--}}
{{--            <div class="header-left">--}}
{{--                <a href="{{url(app_get_locale(false,'/'))}}" class="bravo-logo">--}}
{{--                    @php--}}
{{--                        $logo_id = setting_item("logo_id");--}}
{{--                        if(!empty($row->custom_logo)){--}}
{{--                            $logo_id = $row->custom_logo;--}}
{{--                        }--}}
{{--                    @endphp--}}
{{--                    @if($logo_id)--}}
{{--                        <?php $logo = get_file_url($logo_id,'full') ?>--}}
{{--                        <img src="{{$logo}}" alt="{{setting_item("site_title")}}">--}}
{{--                    @endif--}}
{{--                </a>--}}
{{--                <div class="bravo-menu">--}}
{{--                    <?php generate_menu('primary') ?>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="header-right">--}}
{{--                @if(!empty($header_right_menu))--}}
{{--                    <ul class="topbar-items">--}}
{{--                        @include('Core::frontend.currency-switcher')--}}
{{--                        @include('Language::frontend.switcher')--}}
{{--                        @if(!Auth::check())--}}
{{--                            <li class="login-item">--}}
{{--                                <a href="#login" data-toggle="modal" data-target="#login" class="login">{{__('Login')}}</a>--}}
{{--                            </li>--}}
{{--                            @if(is_enable_registration())--}}
{{--                                <li class="signup-item">--}}
{{--                                    <a href="#register" data-toggle="modal" data-target="#register" class="signup">{{__('Sign Up')}}</a>--}}
{{--                                </li>--}}
{{--                            @endif--}}
{{--                        @else--}}
{{--                            <li class="login-item dropdown">--}}
{{--                                <a href="#" data-toggle="dropdown" class="is_login">--}}
{{--                                    @if($avatar_url = Auth::user()->getAvatarUrl())--}}
{{--                                        <img class="avatar" src="{{$avatar_url}}" alt="{{ Auth::user()->getDisplayName()}}">--}}
{{--                                    @else--}}
{{--                                        <span class="avatar-text">{{ucfirst( Auth::user()->getDisplayName()[0])}}</span>--}}
{{--                                    @endif--}}
{{--                                    {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}--}}
{{--                                    <i class="fa fa-angle-down"></i>--}}
{{--                                </a>--}}
{{--                                <ul class="dropdown-menu text-left">--}}

{{--                                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))--}}
{{--                                        <li><a href="{{route('vendor.dashboard')}}"><i class="icon ion-md-analytics"></i> {{__("Vendor Dashboard")}}</a></li>--}}
{{--                                    @endif--}}
{{--                                    <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) menu-hr @endif">--}}
{{--                                        <a href="{{route('user.profile.index')}}"><i class="icon ion-md-construct"></i> {{__("My profile")}}</a>--}}
{{--                                    </li>--}}
{{--                                    @if(setting_item('inbox_enable'))--}}
{{--                                    <li class="menu-hr"><a href="{{route('user.chat')}}"><i class="fa fa-comments"></i> {{__("Messages")}}</a></li>--}}
{{--                                    @endif--}}
{{--                                    <li class="menu-hr"><a href="{{route('user.booking_history')}}"><i class="fa fa-clock-o"></i> {{__("Booking History")}}</a></li>--}}
{{--                                    <li class="menu-hr"><a href="{{route('user.change_password')}}"><i class="fa fa-lock"></i> {{__("Change password")}}</a></li>--}}
{{--                                    @if(Auth::user()->hasPermission('dashboard_access'))--}}
{{--                                        <li class="menu-hr"><a href="{{route('admin.index')}}"><i class="icon ion-ios-ribbon"></i> {{__("Admin Dashboard")}}</a></li>--}}
{{--                                    @endif--}}
{{--                                    <li class="menu-hr">--}}
{{--                                        <a  href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out"></i> {{__('Logout')}}</a>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">--}}
{{--                                    {{ csrf_field() }}--}}
{{--                                </form>--}}
{{--                            </li>--}}
{{--                        @endif--}}
{{--                    </ul>--}}
{{--                @endif--}}
{{--                <button class="bravo-more-menu">--}}
{{--                    <i class="fa fa-bars"></i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="bravo-menu-mobile" style="display:none;">--}}
{{--        <div class="user-profile">--}}
{{--            <div class="b-close"><i class="icofont-scroll-left"></i></div>--}}
{{--            <div class="avatar"></div>--}}
{{--            <ul>--}}
{{--                @if(!Auth::check())--}}
{{--                    <li>--}}
{{--                        <a href="#login" data-toggle="modal" data-target="#login" class="login">{{__('Login')}}</a>--}}
{{--                    </li>--}}
{{--                    @if(is_enable_registration())--}}
{{--                        <li>--}}
{{--                            <a href="#register" data-toggle="modal" data-target="#register" class="signup">{{__('Sign Up')}}</a>--}}
{{--                        </li>--}}
{{--                    @endif--}}
{{--                @else--}}
{{--                    <li>--}}
{{--                        <a href="{{route('user.profile.index')}}">--}}
{{--                            <i class="icofont-user-suited"></i> {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))--}}
{{--                        <li><a href="{{route('vendor.dashboard')}}"><i class="icon ion-md-analytics"></i> {{__("Vendor Dashboard")}}</a></li>--}}
{{--                    @endif--}}
{{--                    @if(Auth::user()->hasPermission('dashboard_access'))--}}
{{--                        <li>--}}
{{--                            <a href="{{route('admin.index')}}"><i class="icon ion-ios-ribbon"></i> {{__("Admin Dashboard")}}</a>--}}
{{--                        </li>--}}
{{--                    @endif--}}
{{--                    <li>--}}
{{--                        <a href="{{route('user.profile.index')}}">--}}
{{--                            <i class="icon ion-md-construct"></i> {{__("My profile")}}--}}
{{--                        </a>--}}
{{--                    </li>--}}
{{--                    <li>--}}
{{--                        <a  href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">--}}
{{--                            <i class="fa fa-sign-out"></i> {{__('Logout')}}--}}
{{--                        </a>--}}
{{--                        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display: none;">--}}
{{--                            {{ csrf_field() }}--}}
{{--                        </form>--}}
{{--                    </li>--}}

{{--                @endif--}}
{{--            </ul>--}}
{{--            <ul class="multi-lang">--}}
{{--                @include('Core::frontend.currency-switcher')--}}
{{--            </ul>--}}
{{--            <ul class="multi-lang">--}}
{{--                @include('Language::frontend.switcher')--}}
{{--            </ul>--}}
{{--        </div>--}}
{{--        <div class="g-menu">--}}
{{--            <?php generate_menu('primary') ?>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

<!-- Tailwind CSS CDN - Add this in app.blade.php head section if not already added -->
{{--<script src="https://cdn.tailwindcss.com"></script>--}}

<!-- Modern Header with Tailwind CSS -->
{{--<div class="bravo_header {{ setting_item('enable_header_sticky',0) == 1 ? "has_sticky" :""  }}">--}}
<div class="bravo_header has_sticky">

    <!-- Top Info Bar (Date/Time & Contact) -->
{{--    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 text-white">--}}
{{--        <div class="{{$container_class ?? 'container'}} mx-auto">--}}
{{--            <div class="flex flex-wrap items-center justify-between py-3 px-4">--}}
{{--                <!-- Left: Date & Time -->--}}
{{--                <div class="flex items-center space-x-6">--}}
{{--                    <div class="flex items-center space-x-2">--}}
{{--                        <i class="far fa-calendar-alt text-white/90"></i>--}}
{{--                        <span id="currentDate" class="text-sm font-medium"></span>--}}
{{--                    </div>--}}
{{--                    <div class="flex items-center space-x-2">--}}
{{--                        <i class="far fa-clock text-white/90"></i>--}}
{{--                        <span id="currentTime" class="text-sm font-medium"></span>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <!-- Right: Contact Info -->--}}
{{--                <div class="flex items-center space-x-6">--}}
{{--                    <a href="tel:+8801958553918" class="flex items-center space-x-2 hover:text-white/80 transition-all duration-300">--}}
{{--                        <i class="fas fa-phone-alt"></i>--}}
{{--                        <span class="text-sm font-medium">+880 1958 553918</span>--}}
{{--                    </a>--}}
{{--                    <a href="mailto:info@shopnotours.com" class="flex items-center space-x-2 hover:text-white/80 transition-all duration-300">--}}
{{--                        <i class="far fa-envelope"></i>--}}
{{--                        <span class="text-sm font-medium">info@shopnotours.com</span>--}}
{{--                    </a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

    <!-- Main Header -->
    <div class="{{$container_class ?? 'container'}} mx-auto">
        <div class="content">
            <div class="header-left">
                <a href="{{url(app_get_locale(false,'/'))}}" class="bravo-logo">
                    @php
                        $logo_id = setting_item("logo_id");
                        if(!empty($row->custom_logo)){
                            $logo_id = $row->custom_logo;
                        }
                    @endphp
                    @if($logo_id)
                            <?php $logo = get_file_url($logo_id,'full') ?>
                        <img src="{{$logo}}" alt="{{setting_item("site_title")}}" class="transition-transform duration-300 hover:scale-105">
                    @endif
                </a>
                <div class="bravo-menu">
                    <?php generate_menu('primary') ?>
                </div>
            </div>

            <div class="header-right">
                @if(!empty($header_right_menu))
                    <ul class="topbar-items">
                        @include('Core::frontend.currency-switcher')
                        @include('Language::frontend.switcher')

                        @if(!Auth::check())
                            <li class="login-item">
                                <a href="#login" data-toggle="modal" data-target="#login"
                                   class="login inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-300">
                                    {{__('Login')}}
                                </a>
                            </li>
                            @if(is_enable_registration())
                                <li class="signup-item">
                                    <a href="#register" data-toggle="modal" data-target="#register"
                                       class="signup inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-300">
                                        {{__('Sign Up')}}
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="login-item dropdown">
                                <a href="#" data-toggle="dropdown" class="is_login flex items-center space-x-2 px-4 py-2 bg-white rounded-lg hover:bg-gray-50 transition-all duration-300">
                                    @if($avatar_url = Auth::user()->getAvatarUrl())
                                        <img class="avatar w-8 h-8 rounded-full ring-2 ring-indigo-500"
                                             src="{{$avatar_url}}"
                                             alt="{{ Auth::user()->getDisplayName()}}">
                                    @else
                                        <span class="avatar-text w-8 h-8 flex items-center justify-center bg-indigo-600 text-white rounded-full font-semibold">
                                            {{ucfirst( Auth::user()->getDisplayName()[0])}}
                                        </span>
                                    @endif
                                    <span class="font-medium">{{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}</span>
                                    <i class="fa fa-angle-down"></i>
                                </a>

                                <ul class="dropdown-menu text-left bg-white rounded-lg shadow-lg border border-gray-200 mt-2">
                                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))
                                        <li>
                                            <a href="{{route('vendor.dashboard')}}"
                                               class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                                <i class="icon ion-md-analytics mr-2 text-indigo-600"></i>
                                                {{__("Vendor Dashboard")}}
                                            </a>
                                        </li>
                                    @endif

                                    <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) border-t border-gray-100 @endif">
                                        <a href="{{route('user.profile.index')}}"
                                           class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                            <i class="icon ion-md-construct mr-2 text-blue-600"></i>
                                            {{__("My profile")}}
                                        </a>
                                    </li>

                                    @if(setting_item('inbox_enable'))
                                        <li class="border-t border-gray-100">
                                            <a href="{{route('user.chat')}}"
                                               class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                                <i class="fa fa-comments mr-2 text-green-600"></i>
                                                {{__("Messages")}}
                                            </a>
                                        </li>
                                    @endif

                                    <li class="border-t border-gray-100">
                                        <a href="{{route('user.booking_history')}}"
                                           class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                            <i class="fa fa-clock-o mr-2 text-orange-600"></i>
                                            {{__("Booking History")}}
                                        </a>
                                    </li>

                                    <li class="border-t border-gray-100">
                                        <a href="{{route('user.change_password')}}"
                                           class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                            <i class="fa fa-lock mr-2 text-purple-600"></i>
                                            {{__("Change password")}}
                                        </a>
                                    </li>

                                    @if(Auth::user()->hasPermission('dashboard_access'))
                                        <li class="border-t border-gray-100">
                                            <a href="{{route('admin.index')}}"
                                               class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                                                <i class="icon ion-ios-ribbon mr-2 text-yellow-600"></i>
                                                {{__("Admin Dashboard")}}
                                            </a>
                                        </li>
                                    @endif

                                    <li class="border-t border-gray-100">
                                        <a href="#"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                           class="flex items-center px-4 py-2 hover:bg-red-50 text-red-600 transition-colors duration-200">
                                            <i class="fa fa-sign-out mr-2"></i>
                                            {{__('Logout')}}
                                        </a>
                                    </li>
                                </ul>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        @endif
                    </ul>
                @endif

                <button class="bravo-more-menu p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <i class="fa fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="bravo-menu-mobile bg-white shadow-xl" style="display:none;">
        <div class="user-profile">
            <div class="b-close p-4 hover:bg-gray-100 cursor-pointer transition-colors duration-200">
                <i class="icofont-scroll-left text-2xl"></i>
            </div>
            <div class="avatar"></div>

            <ul class="divide-y divide-gray-100">
                @if(!Auth::check())
                    <li>
                        <a href="#login" data-toggle="modal" data-target="#login"
                           class="login flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-sign-in-alt mr-3 text-indigo-600"></i>
                            {{__('Login')}}
                        </a>
                    </li>
                    @if(is_enable_registration())
                        <li>
                            <a href="#register" data-toggle="modal" data-target="#register"
                               class="signup flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-user-plus mr-3 text-green-600"></i>
                                {{__('Sign Up')}}
                            </a>
                        </li>
                    @endif
                @else
                    <li>
                        <a href="{{route('user.profile.index')}}"
                           class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                            <i class="icofont-user-suited mr-3 text-indigo-600"></i>
                            {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}
                        </a>
                    </li>

                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))
                        <li>
                            <a href="{{route('vendor.dashboard')}}"
                               class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <i class="icon ion-md-analytics mr-3 text-blue-600"></i>
                                {{__("Vendor Dashboard")}}
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->hasPermission('dashboard_access'))
                        <li>
                            <a href="{{route('admin.index')}}"
                               class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                <i class="icon ion-ios-ribbon mr-3 text-yellow-600"></i>
                                {{__("Admin Dashboard")}}
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{route('user.profile.index')}}"
                           class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                            <i class="icon ion-md-construct mr-3 text-purple-600"></i>
                            {{__("My profile")}}
                        </a>
                    </li>

                    <li>
                        <a href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();"
                           class="flex items-center px-4 py-3 hover:bg-red-50 text-red-600 transition-colors duration-200">
                            <i class="fa fa-sign-out mr-3"></i>
                            {{__('Logout')}}
                        </a>
                        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                @endif
            </ul>

            <ul class="multi-lang border-t border-gray-200 mt-4 pt-4">
                @include('Core::frontend.currency-switcher')
            </ul>
            <ul class="multi-lang">
                @include('Language::frontend.switcher')
            </ul>
        </div>

        <div class="g-menu">
            <?php generate_menu('primary') ?>
        </div>
    </div>
</div>

<!-- Date/Time Update Script -->
{{--<script>--}}
{{--    function updateDateTime() {--}}
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
{{--        const dateElement = document.getElementById('currentDate');--}}
{{--        if (dateElement) {--}}
{{--            dateElement.textContent = banglaDate;--}}
{{--        }--}}

{{--        // Bangla Time--}}
{{--        const timeOptions = {--}}
{{--            hour: '2-digit',--}}
{{--            minute: '2-digit',--}}
{{--            second: '2-digit',--}}
{{--            hour12: true,--}}
{{--            timeZone: 'Asia/Dhaka'--}}
{{--        };--}}

{{--        const banglaTime = now.toLocaleTimeString('bn-BD', timeOptions);--}}
{{--        const timeElement = document.getElementById('currentTime');--}}
{{--        if (timeElement) {--}}
{{--            timeElement.textContent = banglaTime;--}}
{{--        }--}}
{{--    }--}}

{{--    // Update immediately and then every second--}}
{{--    updateDateTime();--}}
{{--    setInterval(updateDateTime, 1000);--}}
{{--</script>--}}

<!-- Additional Tailwind Utilities -->
<style type="text/tailwindcss">
    @layer utilities {
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    }
</style>

<!-- Responsive Adjustments -->
<style>
    @media (max-width: 768px) {
        .bg-gradient-to-r > div > .flex {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
        }

        .bg-gradient-to-r > div > .flex > div {
            justify-content: center;
        }
    }
</style>
