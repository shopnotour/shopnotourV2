
{{--<style>--}}
{{--    .password-field { position: relative; }--}}
{{--    .password-field input { padding-right: 42px !important; }--}}
{{--    .toggle-password {--}}
{{--        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);--}}
{{--        background: none; border: none; cursor: pointer;--}}
{{--        color: #aaa; display: flex; align-items: center;--}}
{{--        transition: color 0.15s; padding: 4px;--}}
{{--    }--}}
{{--    .toggle-password:hover { color: #333; }--}}
{{--</style>--}}

{{--<form action="#" class="form bravo-form-register" method="post">--}}
{{--    @csrf--}}
{{--    <div class="row y-gap-20">--}}
{{--        <div class="col-12">--}}
{{--            <h1 class="text-22 fw-500">{{ __('Sign in or create an account') }}</h1>--}}
{{--            <p class="mt-10">{{ __('Already have an account?') }} <a data-bs-toggle="modal" href="#login" class="text-blue-1">{{ __('Log in') }}</a></p>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-input">--}}
{{--                <input type="text" name="first_name" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('First Name') }}</label>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-first_name"></span>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-input">--}}
{{--                <input type="text" name="last_name" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('Last Name') }}</label>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-last_name"></span>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-input">--}}
{{--                <input type="text" name="phone" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('Phone') }}</label>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-phone"></span>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-input">--}}
{{--                <input type="email" name="email" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('Email address') }}</label>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-email"></span>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-input password-field">--}}
{{--                <input type="password" name="password" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('Password') }}</label>--}}
{{--                <button type="button" class="toggle-password">--}}
{{--                    <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">--}}
{{--                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>--}}
{{--                    </svg>--}}
{{--                    <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none">--}}
{{--                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" fill="none"/><line x1="1" y1="1" x2="23" y2="23"/>--}}
{{--                    </svg>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-password"></span>--}}
{{--        </div>--}}
{{--        <div class="col-12">--}}
{{--            <div class="d-flex">--}}
{{--                <div class="form-checkbox" style="margin-top: 3px">--}}
{{--                    <input type="checkbox" name="term" id="register-term">--}}
{{--                    <div class="form-checkbox__mark">--}}
{{--                        <div class="form-checkbox__icon icon-check"></div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <label class="text-15 lh-15 text-light-1 ml-10" for="register-term">{{ __('I have read and accept the Terms and Privacy Policy?') }}</label>--}}
{{--            </div>--}}
{{--            <span class="invalid-feedback error error-term"></span>--}}
{{--        </div>--}}
{{--        @if(setting_item("user_enable_register_recaptcha"))--}}
{{--            <div class="form-group">--}}
{{--                {{recaptcha_field($captcha_action ?? 'register')}}--}}
{{--            </div>--}}
{{--            <div><span class="invalid-feedback error error-g-recaptcha-response"></span></div>--}}
{{--        @endif--}}
{{--        <div class="error message-error invalid-feedback"></div>--}}
{{--        <div class="col-12">--}}
{{--            <button type="submit" class="button py-20 -dark-1 bg-blue-1 text-white w-100">--}}
{{--                {{ __('Sign Up') }} <div class="icon-arrow-top-right ml-15"></div>--}}
{{--            </button>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))--}}
{{--        <div class="row y-gap-20 pt-30">--}}
{{--            <div class="col-12">--}}
{{--                <div class="text-center">{{ __('or sign in with') }}</div>--}}
{{--            </div>--}}
{{--            @if(setting_item('facebook_enable'))--}}
{{--                <a href="{{url('/social-login/facebook')}}" class="button col-12 -outline-blue-1 text-blue-1 py-15 rounded-8 mt-10" data-channel="facebook">--}}
{{--                    <i class="fa fa-facebook text-15 mr-10"></i> {{ __('Login with Facebook') }}--}}
{{--                </a>--}}
{{--            @endif--}}
{{--            @if(setting_item('google_enable'))--}}
{{--                <a href="{{url('social-login/google')}}" class="button col-12 -outline-red-1 text-red-1 py-15 rounded-8 mt-15">--}}
{{--                    <i class="fa fa-google text-15 mr-10"></i> {{ __('Login with Google') }}--}}
{{--                </a>--}}
{{--            @endif--}}
{{--            @if(setting_item('twitter_enable'))--}}
{{--                <a href="{{url('social-login/twitter')}}" class="button col-12 -outline-dark-2 text-dark-2 py-15 rounded-8 mt-15">--}}
{{--                    <i class="fa fa-twitter text-15 mr-10"></i> {{ __('Login with Twitter') }}--}}
{{--                </a>--}}
{{--            @endif--}}
{{--        </div>--}}
{{--    @endif--}}
{{--</form>--}}

{{--<script>--}}
{{--    document.querySelectorAll('.toggle-password').forEach(function(btn) {--}}
{{--        btn.addEventListener('click', function () {--}}
{{--            var input = this.closest('.password-field').querySelector('input');--}}
{{--            var show = input.type === 'password';--}}
{{--            input.type = show ? 'text' : 'password';--}}
{{--            this.querySelector('.eye-show').style.display = show ? 'none' : '';--}}
{{--            this.querySelector('.eye-hide').style.display = show ? '' : 'none';--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}



<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Outfit', 'sans-serif'] },
                colors: {
                    brand: {
                        50:  '#eef6ff', 100: '#dbeeff', 200: '#bfddff',
                        300: '#93c5fd', 400: '#5aaaf5', 500: '#2e86e8',
                        600: '#1a6ccc', 700: '#155aa8', 800: '#164a86', 900: '#173f6e',
                    }
                },
                animation: {
                    'fade-up': 'fadeUp 0.4s ease both',
                    'shake':   'shake 0.4s ease',
                },
                keyframes: {
                    fadeUp: { '0%': { opacity: '0', transform: 'translateY(12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                    shake:  { '0%,100%': { transform: 'translateX(0)' }, '20%,60%': { transform: 'translateX(-6px)' }, '40%,80%': { transform: 'translateX(6px)' } }
                }
            }
        }
    }
</script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="font-sans animate-fade-up" id="shopno-register-wrapper">

    {{-- Header --}}
    <div class="mb-6">
        <h3 class="text-2xl font-700 text-gray-900 leading-tight">{{ __('Create an account') }}</h3>
        <p class="mt-1.5 text-sm text-gray-500">
            {{ __('Already have an account?') }}
            <a data-bs-toggle="modal" href="#login"
               class="text-brand-600 font-500 hover:text-brand-700 hover:underline transition-colors">
                {{ __('Log in') }}
            </a>
        </p>
    </div>

    {{-- Error Alert --}}
    <div id="sn-reg-alert" class="hidden mb-4 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="sn-reg-alert-msg"></span>
    </div>

    <form id="sn-reg-form" method="POST" action="{{ url('/register') }}" class="space-y-4" novalidate>
        @csrf

        {{-- Name row --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                    {{ __('First Name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="first_name" autocomplete="given-name"
                       placeholder="{{ __('First name') }}"
                       class="w-full px-3.5 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
                <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="first_name">
                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span></span>
                </p>
            </div>
            <div>
                <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                    {{ __('Last Name') }}
                </label>
                <input type="text" name="last_name" autocomplete="family-name"
                       placeholder="{{ __('Last name') }}"
                       class="w-full px-3.5 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
                <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="last_name">
                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span></span>
                </p>
            </div>
        </div>

        {{-- Phone --}}
        <div>
            <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                {{ __('Phone') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.11 1.18 2 2 0 012.11 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/>
                    </svg>
                </span>
                <input type="text" name="phone" autocomplete="tel"
                       placeholder="+880 1X XX-XXXXXX"
                       class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
            </div>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="phone">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                {{ __('Email address') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input type="email" name="email" autocomplete="email"
                       placeholder="you@example.com"
                       class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
            </div>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="email">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                {{ __('Password') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input type="password" name="password" id="sn-reg-pass" autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full pl-10 pr-11 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
                <button type="button" id="sn-reg-eye1"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors px-1">
                    <svg class="sn-eye-show w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="sn-eye-hide w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            {{-- Strength bar --}}
            <div class="mt-2 flex gap-1" id="sn-strength-bars">
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="sn-sb1"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="sn-sb2"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="sn-sb3"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" id="sn-sb4"></div>
            </div>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="password">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                {{ __('Confirm Password') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                <input type="password" name="password_confirmation" id="sn-reg-pass-confirm" autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                              focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                              transition-all duration-200 sn-input">
            </div>
            <p id="sn-pass-match" class="mt-1.5 text-xs hidden"></p>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="password_confirmation">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Terms --}}
        <div>
            <div class="flex items-start gap-2.5">
                <input type="checkbox" name="term" id="sn-reg-term" value="1"
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer flex-shrink-0">
                <label for="sn-reg-term" class="text-sm text-gray-600 cursor-pointer leading-relaxed">
                    {{ __('I have read and accept the') }}
                    <a href="#" class="text-brand-600 hover:underline">{{ __('Terms') }}</a>
                    {{ __('and') }}
                    <a href="#" class="text-brand-600 hover:underline">{{ __('Privacy Policy') }}</a>
                </label>
            </div>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="term">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- reCAPTCHA --}}
        @if(setting_item("user_enable_register_recaptcha"))
            <div>{{ recaptcha_field($captcha_action ?? 'register') }}</div>
        @endif

        {{-- Submit --}}
        <button type="submit" id="sn-reg-btn"
                class="w-full flex items-center justify-center gap-2.5 py-3.5 px-6
                       bg-brand-600 hover:bg-brand-700 active:bg-brand-800
                       text-white text-sm font-600 rounded-xl
                       transition-all duration-200
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none
                       shadow-sm hover:shadow-md">
            <span id="sn-reg-btn-text">{{ __('Sign Up') }}</span>
            <svg id="sn-reg-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
            <svg id="sn-reg-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </button>
    </form>

    {{-- Social Login --}}
    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
        <div class="mt-5">
            <div class="relative flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-500">{{ __('or sign up with') }}</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div class="grid gap-2.5
                {{ (setting_item('facebook_enable') && setting_item('google_enable') && setting_item('twitter_enable')) ? 'grid-cols-3' : 'grid-cols-2' }}">
                @if(setting_item('facebook_enable'))
                    <a href="{{ url('/social-login/facebook') }}"
                       class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-gray-200
                              bg-white hover:bg-blue-50 hover:border-blue-300 text-gray-700 text-sm font-500
                              transition-all duration-200 shadow-sm">
                        <i class="fa fa-facebook text-blue-600"></i>
                        <span class="hidden sm:inline">Facebook</span>
                    </a>
                @endif
                @if(setting_item('google_enable'))
                    <a href="{{ url('social-login/google') }}"
                       class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-gray-200
                              bg-white hover:bg-red-50 hover:border-red-300 text-gray-700 text-sm font-500
                              transition-all duration-200 shadow-sm">
                        <i class="fa fa-google text-red-500"></i>
                        <span class="hidden sm:inline">Google</span>
                    </a>
                @endif
                @if(setting_item('twitter_enable'))
                    <a href="{{ url('social-login/twitter') }}"
                       class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-gray-200
                              bg-white hover:bg-sky-50 hover:border-sky-300 text-gray-700 text-sm font-500
                              transition-all duration-200 shadow-sm">
                        <i class="fa fa-twitter text-sky-500"></i>
                        <span class="hidden sm:inline">Twitter</span>
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        /* ── Eye toggles ── */
        function eyeToggle(btnId, inputId) {
            var btn = document.getElementById(btnId);
            if (!btn) return;
            btn.addEventListener('click', function () {
                var inp = document.getElementById(inputId);
                var isPass = inp.type === 'password';
                inp.type = isPass ? 'text' : 'password';
                btn.querySelector('.sn-eye-show').classList.toggle('hidden', isPass);
                btn.querySelector('.sn-eye-hide').classList.toggle('hidden', !isPass);
            });
        }
        eyeToggle('sn-reg-eye1', 'sn-reg-pass');

        /* ── Password strength ── */
        var passInp = document.getElementById('sn-reg-pass');
        var bars = ['sn-sb1','sn-sb2','sn-sb3','sn-sb4'];
        var strengthColors = ['', 'bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];

        function updateStrength() {
            var v = passInp ? passInp.value : '';
            var score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            bars.forEach(function (id, i) {
                var el = document.getElementById(id);
                el.className = 'h-1 flex-1 rounded-full transition-colors duration-300 ' +
                    (v.length > 0 && i < score ? strengthColors[score] : 'bg-gray-200');
            });
            checkMatch();
        }

        /* ── Password match ── */
        function checkMatch() {
            var p1 = document.getElementById('sn-reg-pass');
            var p2 = document.getElementById('sn-reg-pass-confirm');
            var hint = document.getElementById('sn-pass-match');
            if (!p1 || !p2 || !hint || !p2.value) { if(hint) hint.classList.add('hidden'); return; }
            var ok = p1.value === p2.value;
            hint.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
            hint.className = 'mt-1.5 text-xs ' + (ok ? 'text-green-600' : 'text-red-600');
            hint.classList.remove('hidden');
        }

        if (passInp) passInp.addEventListener('input', updateStrength);
        var confInp = document.getElementById('sn-reg-pass-confirm');
        if (confInp) confInp.addEventListener('input', checkMatch);

        /* ── Clear errors on input ── */
        document.querySelectorAll('#sn-reg-form .sn-input').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var errEl = document.querySelector('#sn-reg-form .sn-field-error[data-field="' + inp.name + '"]');
                if (errEl) errEl.classList.add('hidden');
                inp.classList.remove('border-red-400', 'bg-red-50');
                document.getElementById('sn-reg-alert').classList.add('hidden');
            });
        });

        /* ── Form submit ── */
        var form    = document.getElementById('sn-reg-form');
        var btn     = document.getElementById('sn-reg-btn');
        var btnTxt  = document.getElementById('sn-reg-btn-text');
        var icon    = document.getElementById('sn-reg-icon');
        var spin    = document.getElementById('sn-reg-spinner');
        var alert   = document.getElementById('sn-reg-alert');
        var alertMsg= document.getElementById('sn-reg-alert-msg');
        var isSubmitting = false;

        function setLoading(state) {
            isSubmitting = state;
            btn.disabled = state;
            btnTxt.textContent = state ? '{{ __("Creating account...") }}' : '{{ __("Sign Up") }}';
            icon.classList.toggle('hidden', state);
            spin.classList.toggle('hidden', !state);
        }

        function showAlert(msg) {
            alertMsg.textContent = msg;
            alert.classList.remove('hidden');
            alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showFieldError(field, msg) {
            var errEl = document.querySelector('#sn-reg-form .sn-field-error[data-field="' + field + '"]');
            var inp   = document.querySelector('#sn-reg-form [name="' + field + '"]');
            if (errEl) { errEl.querySelector('span').textContent = msg; errEl.classList.remove('hidden'); }
            if (inp) { inp.classList.add('border-red-400', 'bg-red-50'); }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (isSubmitting) return;

                setLoading(true);
                alert.classList.add('hidden');
                document.querySelectorAll('#sn-reg-form .sn-field-error').forEach(function (el) { el.classList.add('hidden'); });
                document.querySelectorAll('#sn-reg-form .sn-input').forEach(function (inp) { inp.classList.remove('border-red-400', 'bg-red-50'); });

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                    .then(function (res) {
                        if (res.ok) {
                            btnTxt.textContent = '{{ __("Redirecting...") }}';
                            window.location.href = res.data.redirect || '/';
                            return;
                        }
                        setLoading(false);
                        var errors = res.data.errors || {};
                        Object.keys(errors).forEach(function (field) {
                            var msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                            showFieldError(field, msg);
                        });
                        showAlert(res.data.message || '{{ __("Please fix the errors above.") }}');
                    })
                    .catch(function () {
                        setLoading(false);
                        showAlert('{{ __("Network error. Please try again.") }}');
                    });
            });
        }
    })();
</script>
