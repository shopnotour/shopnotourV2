
{{--@php--}}
{{--    $headerStyle = (!empty($row->header_style)) ? $row->header_style : 'normal' ;--}}
{{--    $dataBg = 'bg-dark-1';--}}
{{--    $navTextStyle =  'text-white';--}}
{{--    switch ($headerStyle){--}}
{{--        case 'transparent': $headerClass = 'bg-green is-sticky'; break;--}}
{{--        case 'transparent_v2': $headerClass = 'header_2'; break;--}}
{{--        case 'transparent_v3': $headerClass = '-type-2'; break;--}}
{{--        case 'transparent_v4':{--}}
{{--            $headerClass = '-type-5 transparent_v4';--}}
{{--            $dataBg = '-header-5-sticky';--}}
{{--            $navTextStyle = 'text-dark-1';--}}
{{--            break;--}}
{{--        }--}}
{{--        case 'transparent_v5':{--}}
{{--            $headerClass = '';--}}
{{--            $dataBg = 'bg-white';--}}
{{--            $navTextStyle = 'text-dark-1';--}}
{{--            break;--}}
{{--        }--}}
{{--        case 'transparent_v6':--}}
{{--        case 'transparent_v9':{--}}
{{--            $headerClass = '';--}}
{{--            $dataBg = 'bg-white';--}}
{{--            $container_class = 'header__container header__container-1500 mx-auto';--}}
{{--            $navTextStyle = 'text-dark-1';--}}
{{--            break;--}}
{{--        }--}}
{{--        case 'transparent_v7':{--}}
{{--            $headerClass = '';--}}
{{--            $dataBg = 'bg-dark-1';--}}
{{--            $container_class = 'header__container';--}}
{{--            break;--}}
{{--        }--}}
{{--        case 'normal_white':{--}}
{{--            $headerClass = '';--}}
{{--            $navTextStyle = 'text-dark-1';--}}
{{--            $dataBg = 'bg-white';--}}
{{--            break;--}}
{{--        }--}}
{{--        default: $headerClass = '-fixed bg-dark-3';--}}
{{--    }--}}

{{--//    --}}{{-- Credit balance --}}
{{--    $creditBalance = Auth::check() ? (Auth::user()->credit_balance ?? Auth::user()->balance ?? 0) : 0;--}}
{{--@endphp--}}

{{-- ════ FIXED HEADER WRAPPER ════ --}}
{{--<div id="site-header-wrapper" style="position:fixed;top:0;left:0;right:0;z-index:9999;--}}
{{--     box-shadow:0 2px 12px rgba(0,0,0,0.18)">--}}

{{--    @switch($headerStyle)--}}
{{--        @case("transparent_v8")--}}
{{--            @include("Layout::parts.header-style.transparent_v8")--}}
{{--            @break--}}
{{--        @default--}}
{{--            <header data-add-bg="{{ $dataBg }}"--}}
{{--                    class="header {{ $headerClass }} js-header bravo_header style-{{ $headerStyle }}"--}}
{{--                    data-x="header" data-x-toggle="is-menu-opened"--}}
{{--                    style="position:relative;top:auto;">--}}

{{--                <div data-anim="fade"--}}
{{--                     class="{{ $container_class ?? 'container' }} px-30 sm:px-20--}}
{{--                        @if($headerStyle == 'transparent_v2') container @endif is-in-view">--}}

{{--                    <div class="row justify-between items-center">--}}

{{--                        --}}{{-- ── Logo column ── --}}
{{--                        <div class="col-auto @if($headerStyle == 'transparent_v7') col-left @endif">--}}
{{--                            @php--}}
{{--                                $logo = setting_item('logo_id');--}}
{{--                                $logoDark = setting_item('logo_id_dark');--}}
{{--                                if($headerStyle == 'transparent_v9') $logo = $logoDark;--}}
{{--                            @endphp--}}
{{--                            @if($headerStyle == 'transparent_v2')--}}
{{--                                @include("Layout::parts.header-style.$headerStyle")--}}
{{--                                <div class="d-none xl:d-block">--}}
{{--                                    @include("Layout::parts.header-style.normal")--}}
{{--                                </div>--}}
{{--                            @elseif($headerStyle == 'transparent_v4')--}}
{{--                                @include("Layout::parts.header-style.transparent_v2",['textColor' => 'text-dark-1'])--}}
{{--                            @elseif($headerStyle == 'normal_white')--}}
{{--                                @include("Layout::parts.header-style.normal",['textColor' => 'text-dark-1'])--}}
{{--                            @else--}}
{{--                                @include("Layout::parts.header-style.normal")--}}
{{--                            @endif--}}
{{--                        </div>--}}

{{--                        @if($headerStyle == 'transparent_v3')--}}
{{--                            <div class="col-auto xl:d-none">--}}
{{--                                <a href="{{url(app_get_locale(false,'/'))}}" class="header-logo mr-20"--}}
{{--                                   data-x="header-logo" data-x-toggle="is-logo-dark">--}}
{{--                                    @if($logo)--}}
{{--                                        <img class="logo-light" src="{{get_file_url($logo,'full')}}" alt="{{setting_item('site_title')}}">--}}
{{--                                    @endif--}}
{{--                                    @if($logoDark)--}}
{{--                                        <img class="logo-dark" src="{{get_file_url($logoDark,'full')}}" alt="{{setting_item('site_title')}}">--}}
{{--                                    @endif--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        @endif--}}

{{--                        --}}{{-- ── Right side nav ── --}}
{{--                        <div class="col-auto">--}}
{{--                            <div class="d-flex items-center">--}}
{{--                                <div class="header-menu menu-right">--}}
{{--                                    <div class="mobile-overlay"></div>--}}
{{--                                    <div class="header-menu__content">--}}
{{--                                        <div class="menu js-navList">--}}
{{--                                            <ul class="menu__nav {{$navTextStyle}} -is-active"--}}
{{--                                                style="display:flex;align-items:center;gap:8px">--}}

{{--                                                --}}{{-- ════ CREDIT BALANCE BADGE ════ --}}
{{--                                                @if(Auth::check())--}}
{{--                                                    <li style="list-style:none">--}}
{{--                                                        <a href="{{ route('user.wallet.addcredit') }}"--}}
{{--                                                           style="display:flex;align-items:center;gap:8px;--}}
{{--                                                          background:linear-gradient(135deg,rgba(255,255,255,.15),rgba(255,255,255,.05));--}}
{{--                                                          border:1px solid rgba(255,255,255,.25);--}}
{{--                                                          border-radius:10px;padding:6px 14px;--}}
{{--                                                          text-decoration:none;--}}
{{--                                                          transition:all .2s ease;--}}
{{--                                                          backdrop-filter:blur(6px)"--}}
{{--                                                           onmouseover="this.style.background='linear-gradient(135deg,rgba(255,255,255,.25),rgba(255,255,255,.12))';--}}
{{--                                                                this.style.transform='translateY(-1px)';--}}
{{--                                                                this.style.boxShadow='0 4px 12px rgba(0,0,0,.2)'"--}}
{{--                                                           onmouseout="this.style.background='linear-gradient(135deg,rgba(255,255,255,.15),rgba(255,255,255,.05))';--}}
{{--                                                               this.style.transform='translateY(0)';--}}
{{--                                                               this.style.boxShadow='none'">--}}
{{--                                                            --}}{{-- Coin icon --}}
{{--                                                            <div style="width:28px;height:28px;--}}
{{--                                                                background:linear-gradient(135deg,#f59e0b,#d97706);--}}
{{--                                                                border-radius:50%;display:flex;--}}
{{--                                                                align-items:center;justify-content:center;--}}
{{--                                                                box-shadow:0 2px 6px rgba(245,158,11,.4);--}}
{{--                                                                flex-shrink:0;font-size:14px">--}}
{{--                                                                💰--}}
{{--                                                            </div>--}}
{{--                                                            --}}{{-- Text --}}
{{--                                                            <div style="line-height:1.2">--}}
{{--                                                                <div style="font-size:9px;color:rgba(255,255,255,.65);--}}
{{--                                                                    text-transform:uppercase;letter-spacing:.06em;--}}
{{--                                                                    font-weight:600">--}}
{{--                                                                    Credit Balance--}}
{{--                                                                </div>--}}
{{--                                                                <div style="font-size:13px;font-weight:800;color:#fde68a;--}}
{{--                                                                    font-family:'Segoe UI',sans-serif;letter-spacing:.01em">--}}
{{--                                                                    ৳{{ number_format($creditBalance, 2) }}--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        </a>--}}
{{--                                                    </li>--}}
{{--                                                @endif--}}
{{--                                                --}}{{-- ══════════════════════════════ --}}

{{--                                                --}}{{-- Mother Company Logo --}}
{{--                                                <li style="list-style:none">--}}
{{--                                                    <a href="https://shopnodhora.com.bd/"--}}
{{--                                                       target="_blank" rel="noopener noreferrer"--}}
{{--                                                       class="ml-20">--}}
{{--                                                        <img src="{{ asset('Custom_logo/shopnodhora.png') }}"--}}
{{--                                                             alt="Mother Company"--}}
{{--                                                             style="height:auto;width:auto;--}}
{{--                                                                max-width:150px;object-fit:contain">--}}
{{--                                                    </a>--}}
{{--                                                </li>--}}

{{--                                                @include('Core::frontend.currency-switcher')--}}

{{--                                                @if(!Auth::check())--}}
{{--                                                    <div class="d-flex items-center ml-20 is-menu-opened-hide md:d-none">--}}
{{--                                                        @php--}}
{{--                                                            $btn_expert = '-white bg-white text-dark-1';--}}
{{--                                                            $btn_login  = 'border-white -outline-white text-white';--}}
{{--                                                            if ($headerStyle == 'transparent_v6'){--}}
{{--                                                                $btn_expert = '-blue-1 bg-dark-1 text-white';--}}
{{--                                                                $btn_login  = 'border-dark-1 -blue-1 text-dark-1';--}}
{{--                                                            } elseif ($headerStyle == 'normal_white'){--}}
{{--                                                                $btn_expert = '-white bg-blue-1 text-white';--}}
{{--                                                                $btn_login  = 'border-dark-1 -blue-1 text-dark-1';--}}
{{--                                                            } elseif ($headerStyle == 'transparent_v9'){--}}
{{--                                                                $btn_expert = '-blue-1 bg-dark-4 text-white';--}}
{{--                                                                $btn_login  = 'border-dark-1 -blue-1 text-dark-1';--}}
{{--                                                            } elseif ($headerStyle == 'transparent_v5'){--}}
{{--                                                                $btn_expert = '-blue-1 bg-dark-4 text-white';--}}
{{--                                                                $btn_login  = 'border-dark-4 -blue-1 h-50 text-dark-4';--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        @if(!empty($page_vendor = get_page_url(setting_item('vendor_page_become_an_expert'))))--}}
{{--                                                            <a href="{{ $page_vendor }}"--}}
{{--                                                               class="{{$btn_expert}} button px-30 fw-400 text-14 h-50 bg-red-1">--}}
{{--                                                                <span class="text-white">{{ __('Become an Agent') }}</span>--}}
{{--                                                            </a>--}}
{{--                                                        @endif--}}
{{--                                                        <a data-bs-toggle="modal" href="#login"--}}
{{--                                                           class="{{$btn_login}} button px-30 fw-400 text-14 h-50 ml-20 bg-blue-1">--}}
{{--                                                            {{ __('Sign In / Register') }}--}}
{{--                                                        </a>--}}
{{--                                                    </div>--}}
{{--                                                @else--}}
{{--                                                    <li class="login-item menu-item-has-children" style="list-style:none">--}}
{{--                                                        <a href="#" class="bg-blue-1 is_login">--}}
{{--                                                        <span class="mr-10">--}}
{{--                                                            @if($avatar_url = Auth::user()->getAvatarUrl())--}}
{{--                                                                <img class="avatar rounded-circle"--}}
{{--                                                                     src="{{$avatar_url}}"--}}
{{--                                                                     alt="{{ Auth::user()->getDisplayName()}}"--}}
{{--                                                                     width="20" height="20">--}}
{{--                                                            @else--}}
{{--                                                                <span class="avatar-text rounded-circle">--}}
{{--                                                                    {{ucfirst(Auth::user()->getDisplayName()[0])}}--}}
{{--                                                                </span>--}}
{{--                                                            @endif--}}
{{--                                                            {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}--}}
{{--                                                        </span>--}}
{{--                                                            <i class="icon icon-chevron-sm-down"></i>--}}
{{--                                                        </a>--}}
{{--                                                        <ul class="subnav">--}}
{{--                                                            @if(Auth::user()->hasPermission('dashboard_vendor_access'))--}}
{{--                                                                <li><a href="{{route('vendor.dashboard')}}" class="dropdown-item">--}}
{{--                                                                        <i class="fa fa-line-chart mr-10"></i> {{__("Vendor Dashboard")}}--}}
{{--                                                                    </a></li>--}}
{{--                                                            @endif--}}
{{--                                                            <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) menu-hr @endif">--}}
{{--                                                                <a href="{{route('user.profile.index')}}" class="dropdown-item">--}}
{{--                                                                    <i class="fa fa-address-card mr-10"></i> {{__("My profile")}}--}}
{{--                                                                </a>--}}
{{--                                                            </li>--}}
{{--                                                            @if(setting_item('inbox_enable'))--}}
{{--                                                                <li class="menu-hr">--}}
{{--                                                                    <a href="{{route('user.chat')}}" class="dropdown-item">--}}
{{--                                                                        <i class="fa fa-comments mr-10"></i> {{__("Messages")}}--}}
{{--                                                                    </a>--}}
{{--                                                                </li>--}}
{{--                                                            @endif--}}
{{--                                                            <li class="menu-hr">--}}
{{--                                                                <a href="{{route('user.booking_history')}}" class="dropdown-item">--}}
{{--                                                                    <i class="fa fa-clock-o mr-10"></i> {{__("Booking History")}}--}}
{{--                                                                </a>--}}
{{--                                                            </li>--}}
{{--                                                            <li class="menu-hr">--}}
{{--                                                                <a href="{{route('user.change_password')}}" class="dropdown-item">--}}
{{--                                                                    <i class="fa fa-lock mr-10"></i> {{__("Change password")}}--}}
{{--                                                                </a>--}}
{{--                                                            </li>--}}
{{--                                                            @if(Auth::user()->hasPermission('dashboard_access'))--}}
{{--                                                                <li class="menu-hr">--}}
{{--                                                                    <a href="{{route('admin.index')}}" class="dropdown-item">--}}
{{--                                                                        <i class="fa fa-dashboard mr-10"></i> {{__("Admin Dashboard")}}--}}
{{--                                                                    </a>--}}
{{--                                                                </li>--}}
{{--                                                            @endif--}}
{{--                                                            <li class="menu-hr">--}}
{{--                                                                <a class="dropdown-item" href="#"--}}
{{--                                                                   onclick="event.preventDefault();--}}
{{--                                                                        document.getElementById('logout-form').submit();">--}}
{{--                                                                    <i class="fa fa-sign-out mr-10"></i> {{__('Logout')}}--}}
{{--                                                                </a>--}}
{{--                                                            </li>--}}
{{--                                                        </ul>--}}
{{--                                                        <form id="logout-form" action="{{ route('logout') }}"--}}
{{--                                                              method="POST" style="display:none;">--}}
{{--                                                            {{ csrf_field() }}--}}
{{--                                                        </form>--}}
{{--                                                    </li>--}}
{{--                                                @endif--}}

{{--                                                --}}{{-- Mobile icons --}}
{{--                                                <div class="d-none xl:d-flex x-gap-20 items-center pl-30 text-white"--}}
{{--                                                     data-x="header-mobile-icons" data-x-toggle="text-white">--}}
{{--                                                    <div>--}}
{{--                                                        @if(!Auth::check())--}}
{{--                                                            <a href="{{ url('/login') }}"--}}
{{--                                                               class="d-flex items-center icon-user text-inherit text-22"></a>--}}
{{--                                                        @else--}}
{{--                                                            <div class="login-mobile-item dropdown ml-20">--}}
{{--                                                                <a href="#" data-bs-toggle="dropdown"--}}
{{--                                                                   class="icon-user text-inherit text-22 is_login"></a>--}}
{{--                                                                <ul class="dropdown-menu text-left">--}}
{{--                                                                    <li>--}}
{{--                                                                        <a href="#" class="dropdown-item">--}}
{{--                                                                            @if($avatar_url = Auth::user()->getAvatarUrl())--}}
{{--                                                                                <img class="avatar" src="{{$avatar_url}}"--}}
{{--                                                                                     alt="{{ Auth::user()->getDisplayName()}}"--}}
{{--                                                                                     width="30" height="30">--}}
{{--                                                                            @else--}}
{{--                                                                                <span class="avatar-text">--}}
{{--                                                                                {{ucfirst(Auth::user()->getDisplayName()[0])}}--}}
{{--                                                                            </span>--}}
{{--                                                                            @endif--}}
{{--                                                                            {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}--}}
{{--                                                                        </a>--}}
{{--                                                                    </li>--}}
{{--                                                                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))--}}
{{--                                                                        <li>--}}
{{--                                                                            <a href="{{route('vendor.dashboard')}}" class="dropdown-item">--}}
{{--                                                                                <i class="icon ion-md-analytics"></i> {{__("Vendor Dashboard")}}--}}
{{--                                                                            </a>--}}
{{--                                                                        </li>--}}
{{--                                                                    @endif--}}
{{--                                                                    <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) menu-hr @endif">--}}
{{--                                                                        <a href="{{route('user.profile.index')}}" class="dropdown-item">--}}
{{--                                                                            <i class="icon ion-md-construct"></i> {{__("My profile")}}--}}
{{--                                                                        </a>--}}
{{--                                                                    </li>--}}
{{--                                                                    @if(setting_item('inbox_enable'))--}}
{{--                                                                        <li class="menu-hr">--}}
{{--                                                                            <a href="{{route('user.chat')}}" class="dropdown-item">--}}
{{--                                                                                <i class="fa fa-comments"></i> {{__("Messages")}}--}}
{{--                                                                            </a>--}}
{{--                                                                        </li>--}}
{{--                                                                    @endif--}}
{{--                                                                    <li class="menu-hr">--}}
{{--                                                                        <a href="{{route('user.booking_history')}}" class="dropdown-item">--}}
{{--                                                                            <i class="fa fa-clock-o"></i> {{__("Booking History")}}--}}
{{--                                                                        </a>--}}
{{--                                                                    </li>--}}
{{--                                                                    <li class="menu-hr">--}}
{{--                                                                        <a href="{{route('user.change_password')}}" class="dropdown-item">--}}
{{--                                                                            <i class="fa fa-lock"></i> {{__("Change password")}}--}}
{{--                                                                        </a>--}}
{{--                                                                    </li>--}}
{{--                                                                    @if(Auth::user()->hasPermission('dashboard_access'))--}}
{{--                                                                        <li class="menu-hr">--}}
{{--                                                                            <a href="{{route('admin.index')}}" class="dropdown-item">--}}
{{--                                                                                <i class="icon ion-ios-ribbon"></i> {{__("Admin Dashboard")}}--}}
{{--                                                                            </a>--}}
{{--                                                                        </li>--}}
{{--                                                                    @endif--}}
{{--                                                                    <li class="menu-hr">--}}
{{--                                                                        <a class="dropdown-item" href="#"--}}
{{--                                                                           onclick="event.preventDefault();--}}
{{--                                                                                document.getElementById('logout-form').submit();">--}}
{{--                                                                            <i class="fa fa-sign-out"></i> {{__('Logout')}}--}}
{{--                                                                        </a>--}}
{{--                                                                    </li>--}}
{{--                                                                </ul>--}}
{{--                                                                <form id="logout-form" action="{{ route('logout') }}"--}}
{{--                                                                      method="POST" style="display:none;">--}}
{{--                                                                    {{ csrf_field() }}--}}
{{--                                                                </form>--}}
{{--                                                            </div>--}}
{{--                                                        @endif--}}
{{--                                                    </div>--}}
{{--                                                    <div>--}}
{{--                                                        <button class="d-flex items-center icon-menu text-inherit text-20"--}}
{{--                                                                data-x-click="header, header-logo, header-mobile-icons, mobile-menu">--}}
{{--                                                        </button>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

{{--                                            </ul>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="d-none xl:d-flex x-gap-20 items-center pl-30 {{$navTextStyle}}"--}}
{{--                                     data-x="header-mobile-icons" data-x-toggle="text-white">--}}

{{--                                    --}}{{-- ════ MOBILE: Shopnodhora Logo ════ --}}
{{--                                    <div>--}}
{{--                                        <a href="https://shopnodhora.com.bd/"--}}
{{--                                           target="_blank" rel="noopener noreferrer">--}}
{{--                                            <img src="{{ asset('Custom_logo/shopnodhora.jpg') }}"--}}
{{--                                                 alt="Mother Company"--}}
{{--                                                 style="height:28px;width:auto;--}}
{{--                                                    max-width:90px;object-fit:contain">--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
{{--                                    --}}{{-- ════ MOBILE: Credit Balance ════ --}}
{{--                                    @if(Auth::check())--}}
{{--                                        <div>--}}
{{--                                            <a href="{{ route('user.wallet.addcredit') }}"--}}
{{--                                               style="display:flex;align-items:center;gap:6px;--}}
{{--                                              background:linear-gradient(135deg,rgba(255,255,255,.15),rgba(255,255,255,.05));--}}
{{--                                              border:1px solid rgba(255,255,255,.25);--}}
{{--                                              border-radius:8px;padding:5px 10px;--}}
{{--                                              text-decoration:none">--}}
{{--                                                <div style="width:24px;height:24px;--}}
{{--                                                    background:linear-gradient(135deg,#f59e0b,#d97706);--}}
{{--                                                    border-radius:50%;display:flex;--}}
{{--                                                    align-items:center;justify-content:center;--}}
{{--                                                    flex-shrink:0;font-size:12px">--}}
{{--                                                    💰--}}
{{--                                                </div>--}}
{{--                                                <div style="line-height:1.2">--}}
{{--                                                    <div style="font-size:8px;color:rgba(255,255,255,.65);--}}
{{--                                                        text-transform:uppercase;letter-spacing:.05em;--}}
{{--                                                        font-weight:600">--}}
{{--                                                        Balance--}}
{{--                                                    </div>--}}
{{--                                                    <div style="font-size:12px;font-weight:800;color:#fde68a">--}}
{{--                                                        ৳{{ number_format($creditBalance, 0) }}--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                    --}}{{-- ══════════════════════════════════ --}}

{{--                                    <div>--}}
{{--                                        <a href="@if(!Auth::check()) {{ url('/login') }} @else {{ route('user.profile.index') }} @endif"--}}
{{--                                           class="d-flex items-center icon-user text-inherit text-22"></a>--}}
{{--                                    </div>--}}
{{--                                    @if($headerStyle !== 'transparent_v4')--}}
{{--                                        <div>--}}
{{--                                            <button class="d-flex items-center icon-menu text-inherit text-20"--}}
{{--                                                    data-x-click="header, header-logo, header-mobile-icons, mobile-menu">--}}
{{--                                            </button>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                    </div>--}}

{{--                    <div class="d-none xl:d-flex x-gap-20 items-center pl-30"--}}
{{--                         data-x="header-mobile-icons" data-x-toggle="text-white">--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </header>--}}
{{--            @break--}}
{{--    @endswitch--}}

{{--</div>--}}{{-- end fixed wrapper --}}

{{-- ════ Page body padding (fixed header height compensation) ════ --}}
{{--<div style="height:80px" class="header-spacer"></div>--}}


@php
    $headerStyle = (!empty($row->header_style)) ? $row->header_style : 'normal' ;
    $dataBg = 'bg-dark-1';
    $navTextStyle =  'text-white';
    switch ($headerStyle){
        case 'transparent': $headerClass = 'bg-green is-sticky'; break;
        case 'transparent_v2': $headerClass = 'header_2'; break;
        case 'transparent_v3': $headerClass = '-type-2'; break;
        case 'transparent_v4':{
            $headerClass = '-type-5 transparent_v4';
            $dataBg = '-header-5-sticky';
            $navTextStyle = 'text-dark-1';
            break;
        }
        case 'transparent_v5':{
            $headerClass = '';
            $dataBg = 'bg-white';
            $navTextStyle = 'text-dark-1';
            break;
        }
        case 'transparent_v6':
        case 'transparent_v9':{
            $headerClass = '';
            $dataBg = 'bg-white';
            $container_class = 'header__container header__container-1500 mx-auto';
            $navTextStyle = 'text-dark-1';
            break;
        }
        case 'transparent_v7':{
            $headerClass = '';
            $dataBg = 'bg-dark-1';
            $container_class = 'header__container';
            break;
        }
        case 'normal_white':{
            $headerClass = '';
            $navTextStyle = 'text-dark-1';
            $dataBg = 'bg-white';
            break;
        }
        default: $headerClass = '-fixed bg-dark-3';
    }

    $creditBalance = Auth::check() ? (Auth::user()->credit_balance ?? Auth::user()->balance ?? 0) : 0;
@endphp

{{-- ══════════════════════════════════════════════
     FIXED HEADER WRAPPER
     Position is controlled by CSS var --ann-bar-h
     set dynamically in app.blade.php
     ══════════════════════════════════════════════ --}}
<div id="site-header-wrapper"
     style="position:fixed;top:36px;left:0;right:0;z-index:9998;
            box-shadow:0 2px 12px rgba(0,0,0,.18)">

    @switch($headerStyle)
        @case("transparent_v8")
            @include("Layout::parts.header-style.transparent_v8")
            @break

        @default
            <header data-add-bg="{{ $dataBg }}"
                    class="header {{ $headerClass }} js-header bravo_header style-{{ $headerStyle }}"
                    data-x="header" data-x-toggle="is-menu-opened"
                    style="position:relative;top:auto;">

                <div data-anim="fade"
                     class="{{ $container_class ?? 'container' }} px-30 sm:px-20
                        @if($headerStyle == 'transparent_v2') container @endif is-in-view">

                    <div class="row justify-between items-center">

                        {{-- ── Logo ── --}}
                        <div class="col-auto @if($headerStyle == 'transparent_v7') col-left @endif">
                            @php
                                $logo     = setting_item('logo_id');
                                $logoDark = setting_item('logo_id_dark');
                                if($headerStyle == 'transparent_v9') $logo = $logoDark;
                            @endphp
                            @if($headerStyle == 'transparent_v2')
                                @include("Layout::parts.header-style.$headerStyle")
                                <div class="d-none xl:d-block">
                                    @include("Layout::parts.header-style.normal")
                                </div>
                            @elseif($headerStyle == 'transparent_v4')
                                @include("Layout::parts.header-style.transparent_v2",['textColor'=>'text-dark-1'])
                            @elseif($headerStyle == 'normal_white')
                                @include("Layout::parts.header-style.normal",['textColor'=>'text-dark-1'])
                            @else
                                @include("Layout::parts.header-style.normal")
                            @endif
                        </div>

                        @if($headerStyle == 'transparent_v3')
                            <div class="col-auto xl:d-none">
                                <a href="{{url(app_get_locale(false,'/'))}}"
                                   class="header-logo mr-20"
                                   data-x="header-logo" data-x-toggle="is-logo-dark">
                                    @if($logo)
                                        <img class="logo-light" src="{{get_file_url($logo,'full')}}"
                                             alt="{{setting_item('site_title')}}">
                                    @endif
                                    @if($logoDark)
                                        <img class="logo-dark" src="{{get_file_url($logoDark,'full')}}"
                                             alt="{{setting_item('site_title')}}">
                                    @endif
                                </a>
                            </div>
                        @endif

                        {{-- ── Right side nav (DESKTOP only) ── --}}
                        <div class="col-auto">
                            <div class="d-flex items-center">
                                <div class="header-menu menu-right">
                                    <div class="mobile-overlay"></div>
                                    <div class="header-menu__content">
                                        <div class="menu js-navList">
                                            <ul class="menu__nav {{$navTextStyle}} -is-active"
                                                style="display:flex;align-items:center;gap:8px">

                                                {{-- Credit Balance Badge (desktop) --}}
                                                @if(Auth::check())
                                                    <li style="list-style:none">
                                                        <a href="{{ route('user.wallet.addcredit') }}"
                                                           style="display:flex;align-items:center;gap:8px;
                                                                  background:linear-gradient(135deg,rgba(255,255,255,.15),rgba(255,255,255,.05));
                                                                  border:1px solid rgba(255,255,255,.25);
                                                                  border-radius:10px;padding:6px 14px;
                                                                  text-decoration:none;
                                                                  transition:all .2s ease;
                                                                  backdrop-filter:blur(6px)"
                                                           onmouseover="this.style.background='linear-gradient(135deg,rgba(255,255,255,.25),rgba(255,255,255,.12))';this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.2)'"
                                                           onmouseout="this.style.background='linear-gradient(135deg,rgba(255,255,255,.15),rgba(255,255,255,.05))';this.style.transform='none';this.style.boxShadow='none'">
                                                            <div style="width:28px;height:28px;
                                                                        background:linear-gradient(135deg,#f59e0b,#d97706);
                                                                        border-radius:50%;display:flex;
                                                                        align-items:center;justify-content:center;
                                                                        box-shadow:0 2px 6px rgba(245,158,11,.4);
                                                                        flex-shrink:0;font-size:14px">💰</div>
                                                            <div style="line-height:1.2">
                                                                <div style="font-size:9px;color:rgba(255,255,255,.65);
                                                                            text-transform:uppercase;letter-spacing:.06em;font-weight:600">
                                                                    Credit Balance
                                                                </div>
                                                                <div style="font-size:13px;font-weight:800;color:#fde68a;
                                                                            font-family:'Segoe UI',sans-serif">
                                                                    ৳{{ number_format($creditBalance, 2) }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                @endif

                                                {{-- Shopnodhora Logo (desktop) --}}
                                                <li style="list-style:none">
                                                    <a href="https://shopnodhora.com.bd/"
                                                       target="_blank" rel="noopener noreferrer"
                                                       class="ml-20">
                                                        <img src="{{ asset('Custom_logo/shopnodhora.png') }}"
                                                             alt="Mother Company"
                                                             style="height:auto;width:auto;max-width:150px;object-fit:contain">
                                                    </a>
                                                </li>

                                                {{-- Sign In / Register --}}
                                                @if(!Auth::check())
                                                    <div class="d-flex items-center ml-20 is-menu-opened-hide md:d-none">
                                                        @php
                                                            $btn_login = 'border-white -outline-white text-white';
                                                            if(in_array($headerStyle,['transparent_v6','transparent_v4','transparent_v9'])) $btn_login = 'border-dark-1 -blue-1 text-dark-1';
                                                            elseif($headerStyle=='normal_white') $btn_login = 'border-dark-1 -blue-1 text-dark-1';
                                                            elseif($headerStyle=='transparent_v5') $btn_login = 'border-dark-4 -blue-1 h-50 text-dark-4';
                                                        @endphp
                                                        <a data-bs-toggle="modal" href="#login"
                                                           class="{{$btn_login}} button px-30 fw-400 text-14 h-50 ml-20 bg-blue-1">
                                                            {{ __('Sign In / Register') }}
                                                        </a>
                                                    </div>
                                                @else
                                                    <li class="login-item menu-item-has-children" style="list-style:none">
                                                        <a href="#" class="bg-blue-1 is_login">
                                                            <span class="mr-10">
                                                                @if($avatar_url = Auth::user()->getAvatarUrl())
                                                                    <img class="avatar rounded-circle"
                                                                         src="{{$avatar_url}}"
                                                                         alt="{{ Auth::user()->getDisplayName()}}"
                                                                         width="20" height="20">
                                                                @else
                                                                    <span class="avatar-text rounded-circle">
                                                                        {{ucfirst(Auth::user()->getDisplayName()[0])}}
                                                                    </span>
                                                                @endif
                                                                {{__("Hi, :Name",['name'=>Auth::user()->getDisplayName()])}}
                                                            </span>
                                                            <i class="icon icon-chevron-sm-down"></i>
                                                        </a>
                                                        <ul class="subnav">
                                                            @if(Auth::user()->hasPermission('dashboard_vendor_access'))
                                                                <li><a href="{{route('vendor.dashboard')}}" class="dropdown-item">
                                                                        <i class="fa fa-line-chart mr-10"></i> {{__("Vendor Dashboard")}}
                                                                    </a></li>
                                                            @endif
                                                            <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) menu-hr @endif">
                                                                <a href="{{route('user.profile.index')}}" class="dropdown-item">
                                                                    <i class="fa fa-address-card mr-10"></i> {{__("My profile")}}
                                                                </a>
                                                            </li>
                                                            @if(setting_item('inbox_enable'))
                                                                <li class="menu-hr">
                                                                    <a href="{{route('user.chat')}}" class="dropdown-item">
                                                                        <i class="fa fa-comments mr-10"></i> {{__("Messages")}}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li class="menu-hr">
                                                                <a href="{{route('user.booking_history')}}" class="dropdown-item">
                                                                    <i class="fa fa-clock-o mr-10"></i> {{__("Booking History")}}
                                                                </a>
                                                            </li>
                                                            <li class="menu-hr">
                                                                <a href="{{route('user.change_password')}}" class="dropdown-item">
                                                                    <i class="fa fa-lock mr-10"></i> {{__("Change password")}}
                                                                </a>
                                                            </li>
                                                            @if(Auth::user()->hasPermission('dashboard_access'))
                                                                <li class="menu-hr">
                                                                    <a href="{{route('admin.index')}}" class="dropdown-item">
                                                                        <i class="fa fa-dashboard mr-10"></i> {{__("Admin Dashboard")}}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li class="menu-hr">
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                                                    <i class="fa fa-sign-out mr-10"></i> {{__('Logout')}}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                        <form id="logout-form" action="{{ route('logout') }}"
                                                              method="POST" style="display:none;">
                                                            {{ csrf_field() }}
                                                        </form>
                                                    </li>
                                                @endif

                                                {{-- desktop only: hamburger hidden on mobile since we have separate bar --}}

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- end right col --}}

                    </div>
                </div>
            </header>
            @break
    @endswitch

</div>
{{-- end #site-header-wrapper --}}

{{-- ══════════════════════════════════════════════════════
     MOBILE TOPBAR — সম্পূর্ণ আলাদা fixed div
     থিমের JS/CSS এর বাইরে, তাই override হবে না
     শুধু ≤1279px এ দেখাবে
     ══════════════════════════════════════════════════════ --}}
<div id="mobile-topbar"
     style="display:none;
            position:fixed;
            top:32px;
            left:0;right:0;
            z-index:9997;
            background:#1e3a5f;
            height:48px;
            align-items:center;
            justify-content:space-between;
            padding:0 12px;
            box-shadow:0 2px 8px rgba(0,0,0,.25)">

    {{-- Site Logo --}}
    <a href="{{url(app_get_locale(false,'/'))}}" style="flex-shrink:0">
        @php $logo = setting_item('logo_id'); @endphp
        @if($logo)
            <img src="{{get_file_url($logo,'full')}}"
                 alt="{{setting_item('site_title')}}"
                 style="height:32px;width:auto;object-fit:contain;max-width:110px">
        @else
            <span style="color:#fff;font-weight:700;font-size:15px">{{ setting_item('site_title') }}</span>
        @endif
    </a>

    {{-- Right side items --}}
    <div style="display:flex;align-items:center;gap:8px">

        {{-- Shopnodhora Logo --}}
        <a href="https://shopnodhora.com.bd/" target="_blank" rel="noopener noreferrer">
            <img src="{{ asset('Custom_logo/shopnodhora.jpg') }}"
                 alt="Shopnodhora"
                 style="height:26px;width:auto;max-width:75px;object-fit:contain;border-radius:3px">
        </a>

        {{-- Credit Balance --}}
        @if(Auth::check())
            <a href="{{ route('user.wallet.addcredit') }}"
               style="display:flex;align-items:center;gap:4px;
                      background:rgba(255,255,255,.15);
                      border:1px solid rgba(255,255,255,.3);
                      border-radius:7px;padding:3px 8px;
                      text-decoration:none;color:#fff">
                <span style="font-size:12px">💰</span>
                <span style="font-size:11px;font-weight:800;color:#fde68a;white-space:nowrap">
                    ৳{{ number_format($creditBalance, 0) }}
                </span>
            </a>
        @endif

        {{-- User / Login --}}
        @if(!Auth::check())
            <a href="{{ url('/login') }}"
               style="display:flex;align-items:center;justify-content:center;
                      width:34px;height:34px;color:#fff;font-size:18px;
                      background:rgba(255,255,255,.1);border-radius:50%"
               aria-label="Login">
                <i class="fa fa-user"></i>
            </a>
        @else
            <div class="dropdown" style="position:relative">
                <button data-bs-toggle="dropdown"
                        style="display:flex;align-items:center;justify-content:center;
                               width:34px;height:34px;border:none;cursor:pointer;
                               background:rgba(255,255,255,.15);border-radius:50%;
                               color:#fff;font-size:17px"
                        aria-label="Account">
                    @if($avatar_url = Auth::user()->getAvatarUrl())
                        <img src="{{$avatar_url}}"
                             alt="{{ Auth::user()->getDisplayName() }}"
                             style="width:30px;height:30px;border-radius:50%;object-fit:cover">
                    @else
                        <i class="fa fa-user"></i>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end text-left"
                    style="min-width:180px;right:0;left:auto">
                    <li>
                        <span class="dropdown-item" style="font-weight:700;color:#333">
                            {{ Auth::user()->getDisplayName() }}
                        </span>
                    </li>
                    @if(Auth::user()->hasPermission('dashboard_vendor_access'))
                        <li><a href="{{route('vendor.dashboard')}}" class="dropdown-item">
                                <i class="fa fa-line-chart mr-5"></i> {{__("Vendor Dashboard")}}
                            </a></li>
                    @endif
                    <li class="@if(Auth::user()->hasPermission('dashboard_vendor_access')) menu-hr @endif">
                        <a href="{{route('user.profile.index')}}" class="dropdown-item">
                            <i class="fa fa-address-card mr-5"></i> {{__("My profile")}}
                        </a>
                    </li>
                    @if(setting_item('inbox_enable'))
                        <li class="menu-hr">
                            <a href="{{route('user.chat')}}" class="dropdown-item">
                                <i class="fa fa-comments mr-5"></i> {{__("Messages")}}
                            </a>
                        </li>
                    @endif
                    <li class="menu-hr">
                        <a href="{{route('user.booking_history')}}" class="dropdown-item">
                            <i class="fa fa-clock-o mr-5"></i> {{__("Booking History")}}
                        </a>
                    </li>
                    <li class="menu-hr">
                        <a href="{{route('user.change_password')}}" class="dropdown-item">
                            <i class="fa fa-lock mr-5"></i> {{__("Change password")}}
                        </a>
                    </li>
                    @if(Auth::user()->hasPermission('dashboard_access'))
                        <li class="menu-hr">
                            <a href="{{route('admin.index')}}" class="dropdown-item">
                                <i class="fa fa-dashboard mr-5"></i> {{__("Admin Dashboard")}}
                            </a>
                        </li>
                    @endif
                    <li class="menu-hr">
                        <a class="dropdown-item" href="#"
                           onclick="event.preventDefault();document.getElementById('logout-form-mob2').submit();">
                            <i class="fa fa-sign-out mr-5"></i> {{__('Logout')}}
                        </a>
                    </li>
                </ul>
                <form id="logout-form-mob2" action="{{ route('logout') }}"
                      method="POST" style="display:none;">
                    {{ csrf_field() }}
                </form>
            </div>
        @endif

        {{-- Hamburger for mobile menu --}}
{{--        <button data-x-click="header, header-logo, header-mobile-icons, mobile-menu"--}}
{{--                style="display:flex;align-items:center;justify-content:center;--}}
{{--                       width:34px;height:34px;border:none;cursor:pointer;--}}
{{--                       background:rgba(255,255,255,.1);border-radius:6px;--}}
{{--                       color:#fff;font-size:18px"--}}
{{--                aria-label="Menu">--}}
{{--            <i class="fa fa-bars"></i>--}}
{{--        </button>--}}

    </div>
</div>

<style>
    /* Mobile topbar: show only on ≤1279px */
    @media screen and (max-width: 1279px) {
        /* Show our custom mobile topbar */
        #mobile-topbar {
            display: flex !important;
        }
        /* Hide original header on mobile — we replace it with mobile-topbar */
        #site-header-wrapper {
            display: none !important;
        }
        /* Body needs extra padding: ann-bar (32px) + mobile-topbar (48px) = 80px */
        body {
            padding-top: 80px !important;
        }
    }
</style>
