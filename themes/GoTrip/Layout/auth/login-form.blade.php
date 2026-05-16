
{{--<style>--}}
{{--    .password-field { position: relative; }--}}
{{--    .password-field .form-input { position: relative; }--}}
{{--    .password-field input { padding-right: 42px !important; }--}}
{{--    .toggle-password {--}}
{{--        position: absolute;--}}
{{--        right: 12px;--}}
{{--        bottom: 14px;--}}
{{--        background: none;--}}
{{--        border: none;--}}
{{--        cursor: pointer;--}}
{{--        color: #aaa;--}}
{{--        display: flex;--}}
{{--        align-items: center;--}}
{{--        z-index: 10;--}}
{{--        padding: 4px;--}}
{{--        line-height: 1;--}}
{{--        transition: color 0.15s;--}}
{{--    }--}}
{{--    .toggle-password:hover { color: #555; }--}}
{{--    .toggle-password svg { display: block; }--}}
{{--</style>--}}

{{--<form class="bravo-theme-gotrip-login-form y-gap-20" method="POST" action="{{ route('login') }}">--}}
{{--    <input type="hidden" name="redirect" value="{{request()->query('redirect')}}">--}}
{{--    @csrf--}}
{{--    <div class="col-12">--}}
{{--        <h4 class="form-title text-22 fw-500">{{ __('Welcome back') }}</h4>--}}
{{--        @if(is_enable_registration())--}}
{{--            <p class="mt-10">{{ __("Don't have an account yet?") }} <a data-bs-toggle="modal" href="#register" class="text-blue-1">{{ __('Sign up for free') }}</a></p>--}}
{{--        @endif--}}
{{--    </div>--}}
{{--    <div class="col-12">--}}
{{--        <div class="form-input">--}}
{{--            <input type="text" name="email" autocomplete="off">--}}
{{--            <label class="lh-1 text-14 text-light-1">{{ __('Email') }}</label>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="col-12">--}}
{{--        <div class="password-field">--}}
{{--            <div class="form-input">--}}
{{--                <input type="password" name="password" autocomplete="off">--}}
{{--                <label class="lh-1 text-14 text-light-1">{{ __('Password') }}</label>--}}
{{--            </div>--}}
{{--            <button type="button" class="toggle-password">--}}
{{--                <svg class="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">--}}
{{--                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>--}}
{{--                </svg>--}}
{{--                <svg class="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none">--}}
{{--                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" fill="none"/><line x1="1" y1="1" x2="23" y2="23"/>--}}
{{--                </svg>--}}
{{--            </button>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="col-12 d-flex justify-content-between">--}}
{{--        <div class="d-flex">--}}
{{--            <div class="form-checkbox" style="margin-top: 3px">--}}
{{--                <input type="checkbox" name="remember" id="remember-me" value="1">--}}
{{--                <div class="form-checkbox__mark">--}}
{{--                    <div class="form-checkbox__icon icon-check"></div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="text-15 lh-15 text-light-1 ml-10">{{__('Remember me')}}</div>--}}
{{--        </div>--}}
{{--        <a href="{{ route("password.request") }}">{{__('Forgot Password?')}}</a>--}}
{{--    </div>--}}
{{--    @if(setting_item("user_enable_login_recaptcha"))--}}
{{--        <div class="col-12">--}}
{{--            <div class="form-group">--}}
{{--                {{recaptcha_field($captcha_action ?? 'login')}}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--    <div class="error message-error invalid-feedback"></div>--}}
{{--    <div class="col-12">--}}
{{--        <button class="button py-20 -dark-1 bg-blue-1 text-white w-100 form-submit" type="submit">--}}
{{--            {{ __('Sign In') }}--}}
{{--            <div class="icon-arrow-top-right ml-15"></div>--}}
{{--            <span class="spinner-grow spinner-grow-sm icon-loading ml-15 d-none" role="status" aria-hidden="true"></span>--}}
{{--        </button>--}}
{{--    </div>--}}
{{--    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))--}}
{{--        <div class="advanced y-gap-20">--}}
{{--            <div class="col-12">--}}
{{--                <div class="text-center">{{__('or sign in with')}}</div>--}}
{{--                @if(setting_item('facebook_enable'))--}}
{{--                    <a href="{{url('/social-login/facebook')}}" class="button col-12 -outline-blue-1 text-blue-1 py-15 rounded-8 mt-10 cursor-pointer">--}}
{{--                        <i class="fa fa-facebook text-15 mr-10"></i> {{__('Facebook')}}--}}
{{--                    </a>--}}
{{--                @endif--}}
{{--                @if(setting_item('google_enable'))--}}
{{--                    <a href="{{url('social-login/google')}}" class="button col-12 -outline-red-1 text-red-1 py-15 rounded-8 mt-15 cursor-pointer">--}}
{{--                        <i class="fa fa-google text-15 mr-10"></i> {{__('Google')}}--}}
{{--                    </a>--}}
{{--                @endif--}}
{{--                @if(setting_item('twitter_enable'))--}}
{{--                    <a href="{{url('social-login/twitter')}}" class="button col-12 -outline-dark-2 text-dark-2 py-15 rounded-8 mt-15 cursor-pointer">--}}
{{--                        <i class="fa fa-twitter text-15 mr-10"></i> {{__('Twitter')}}--}}
{{--                    </a>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--    <div class="col-12">--}}
{{--        <div class="text-center px-30">{{ __('By creating an account, you agree to our Terms of Service and Privacy Statement.') }}</div>--}}
{{--    </div>--}}
{{--</form>--}}

{{--<script>--}}
{{--    $(document).on('click', '.toggle-password', function () {--}}
{{--        var $btn = $(this);--}}
{{--        var $input = $btn.closest('.password-field').find('input');--}}
{{--        var isPass = $input.attr('type') === 'password';--}}
{{--        $input.attr('type', isPass ? 'text' : 'password');--}}
{{--        $btn.find('.eye-show').toggle(!isPass);--}}
{{--        $btn.find('.eye-hide').toggle(isPass);--}}
{{--    });--}}
{{--</script>--}}



<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Outfit', 'sans-serif'],
                },
                colors: {
                    brand: {
                        50:  '#eef6ff',
                        100: '#dbeeff',
                        200: '#bfddff',
                        300: '#93c5fd',
                        400: '#5aaaf5',
                        500: '#2e86e8',
                        600: '#1a6ccc',
                        700: '#155aa8',
                        800: '#164a86',
                        900: '#173f6e',
                    }
                },
                animation: {
                    'fade-up': 'fadeUp 0.4s ease both',
                    'shake': 'shake 0.4s ease',
                },
                keyframes: {
                    fadeUp: {
                        '0%': { opacity: '0', transform: 'translateY(12px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' },
                    },
                    shake: {
                        '0%,100%': { transform: 'translateX(0)' },
                        '20%,60%': { transform: 'translateX(-6px)' },
                        '40%,80%': { transform: 'translateX(6px)' },
                    }
                }
            }
        }
    }
</script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="font-sans animate-fade-up" id="shopno-login-wrapper">

    {{-- Header --}}
    <div class="mb-7">
        <h3 class="text-2xl font-700 text-gray-900 leading-tight">{{ __('Welcome back') }}</h3>
        @if(is_enable_registration())
            <p class="mt-1.5 text-sm text-gray-500">
                {{ __("Don't have an account yet?") }}
{{--                <a data-bs-toggle="modal" href="#register"--}}
{{--                   class="text-brand-600 font-500 hover:text-brand-700 hover:underline transition-colors">--}}
{{--                    {{ __('Sign up for free') }}--}}
{{--                </a>--}}
                <a data-bs-toggle="modal" href="{{ url('/register') }}"
                   class="text-brand-600 font-500 hover:text-brand-700 hover:underline transition-colors">
                    {{ __('Sign up for free') }}
                </a>
            </p>
        @endif
    </div>

    {{-- Error Alert --}}
    <div id="sn-login-alert" class="hidden mb-4 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 animate-fade-up">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="sn-login-alert-msg"></span>
    </div>

    <form id="sn-login-form" method="POST" action="{{ url('/login') }}" class="space-y-4" novalidate>
        <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
        @csrf

        {{-- Email --}}
        <div>
            <label for="sn-login-email" class="block text-xs font-600 text-gray-600 mb-1.5 uppercase tracking-wider">
                {{ __('Email') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input
                    id="sn-login-email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                           focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                           transition-all duration-200 sn-input"
                >
            </div>
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="email">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Password --}}
        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label for="sn-login-password" class="block text-xs font-600 text-gray-600 uppercase tracking-wider">
                    {{ __('Password') }}
                </label>
                <a href="{{ url('/forgot-password') }}"
                   class="text-xs text-brand-600 hover:text-brand-700 hover:underline transition-colors">
                    {{ __('Forgot Password?') }}
                </a>
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input
                    id="sn-login-password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-11 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400
                           focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                           transition-all duration-200 sn-input"
                >
                <button type="button" id="sn-login-eye"
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
            <p class="sn-field-error hidden mt-1.5 text-xs text-red-600 flex items-center gap-1" data-field="password">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span></span>
            </p>
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2.5">
            <input type="checkbox" name="remember" id="sn-remember"
                   class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
            <label for="sn-remember" class="text-sm text-gray-600 cursor-pointer select-none">
                {{ __('Remember me') }}
            </label>
        </div>

        {{-- reCAPTCHA --}}
        @if(setting_item("user_enable_login_recaptcha"))
            <div>{{ recaptcha_field($captcha_action ?? 'login') }}</div>
        @endif

        {{-- Submit --}}
        <button type="submit" id="sn-login-btn"
                class="w-full flex items-center justify-center gap-2.5 py-3.5 px-6
                       bg-brand-600 hover:bg-brand-700 active:bg-brand-800
                       text-white text-sm font-600 rounded-xl
                       transition-all duration-200
                       focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none
                       shadow-sm hover:shadow-md">
            <span id="sn-login-btn-text">{{ __('Sign In') }}</span>
            <svg id="sn-login-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
            <svg id="sn-login-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </button>
    </form>

    {{-- Social Login --}}
    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
        <div class="mt-6">
            <div class="relative flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-500">{{ __('or sign in with') }}</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div class="grid gap-2.5
                {{ (setting_item('facebook_enable') && setting_item('google_enable') && setting_item('twitter_enable')) ? 'grid-cols-3' : (( setting_item('facebook_enable') && setting_item('google_enable')) || (setting_item('facebook_enable') && setting_item('twitter_enable')) || (setting_item('google_enable') && setting_item('twitter_enable')) ? 'grid-cols-2' : 'grid-cols-1') }}">
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

    <p class="mt-5 text-center text-xs text-gray-400 leading-relaxed">
        {{ __('By creating an account, you agree to our Terms of Service and Privacy Statement.') }}
    </p>
</div>

<script>
    (function () {
        /* ── Eye toggle ── */
        var eyeBtn = document.getElementById('sn-login-eye');
        if (eyeBtn) {
            eyeBtn.addEventListener('click', function () {
                var inp = document.getElementById('sn-login-password');
                var isPass = inp.type === 'password';
                inp.type = isPass ? 'text' : 'password';
                eyeBtn.querySelector('.sn-eye-show').classList.toggle('hidden', isPass);
                eyeBtn.querySelector('.sn-eye-hide').classList.toggle('hidden', !isPass);
            });
        }

        /* ── Clear field errors on input ── */
        document.querySelectorAll('#sn-login-form .sn-input').forEach(function (inp) {
            inp.addEventListener('input', function () {
                var name = inp.name;
                var errEl = document.querySelector('#sn-login-form .sn-field-error[data-field="' + name + '"]');
                if (errEl) {
                    errEl.classList.add('hidden');
                    inp.classList.remove('border-red-400', 'bg-red-50');
                }
                document.getElementById('sn-login-alert').classList.add('hidden');
            });
        });

        /* ── Form submit ── */
        var form   = document.getElementById('sn-login-form');
        var btn    = document.getElementById('sn-login-btn');
        var btnTxt = document.getElementById('sn-login-btn-text');
        var icon   = document.getElementById('sn-login-icon');
        var spin   = document.getElementById('sn-login-spinner');
        var alert  = document.getElementById('sn-login-alert');
        var alertMsg = document.getElementById('sn-login-alert-msg');

        var isSubmitting = false;

        function setLoading(state) {
            isSubmitting = state;
            btn.disabled = state;
            btnTxt.textContent = state ? '{{ __("Signing in...") }}' : '{{ __("Sign In") }}';
            icon.classList.toggle('hidden', state);
            spin.classList.toggle('hidden', !state);
        }

        function showAlert(msg) {
            alertMsg.textContent = msg;
            alert.classList.remove('hidden');
            alert.classList.add('animate-shake');
            setTimeout(function () { alert.classList.remove('animate-shake'); }, 500);
        }

        function showFieldError(field, msg) {
            var errEl = document.querySelector('#sn-login-form .sn-field-error[data-field="' + field + '"]');
            var inp   = document.querySelector('#sn-login-form [name="' + field + '"]');
            if (errEl) {
                errEl.querySelector('span').textContent = msg;
                errEl.classList.remove('hidden');
            }
            if (inp) {
                inp.classList.add('border-red-400', 'bg-red-50');
                inp.classList.remove('border-gray-200', 'bg-gray-50');
            }
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (isSubmitting) return; // double-click guard

                setLoading(true);
                alert.classList.add('hidden');

                // Clear previous errors
                document.querySelectorAll('#sn-login-form .sn-field-error').forEach(function (el) {
                    el.classList.add('hidden');
                });
                document.querySelectorAll('#sn-login-form .sn-input').forEach(function (inp) {
                    inp.classList.remove('border-red-400', 'bg-red-50');
                });

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
                    .then(function (res) {
                        if (res.ok) {
                            // Success — redirect
                            btnTxt.textContent = '{{ __("Redirecting...") }}';
                            window.location.href = res.data.redirect || '/';
                            return; // keep loading state
                        }

                        setLoading(false);

                        // Field errors
                        var errors = res.data.errors || {};
                        var hasFieldError = false;
                        Object.keys(errors).forEach(function (field) {
                            var msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                            showFieldError(field, msg);
                            hasFieldError = true;
                        });

                        // General alert
                        var msg = res.data.message || '{{ __("Something went wrong. Please try again.") }}';
                        showAlert(msg);

                        // Focus first errored field
                        var firstErrInp = document.querySelector('#sn-login-form .border-red-400');
                        if (firstErrInp) firstErrInp.focus();
                    })
                    .catch(function () {
                        setLoading(false);
                        showAlert('{{ __("Network error. Please try again.") }}');
                    });
            });
        }
    })();
</script>
