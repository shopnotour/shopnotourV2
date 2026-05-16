@extends('layouts.app')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Outfit', sans-serif; }
        .sn-inp { padding-left: 42px !important; padding-right: 14px !important; }
        .sn-inp.has-eye { padding-right: 44px !important; }
        @keyframes snFadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .sn-fadein { animation: snFadeUp 0.45s ease both; }
        @keyframes snSpin { to { transform:rotate(360deg); } }
        .sn-spin { animation: snSpin .7s linear infinite; }
        .sn-inp:focus { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,.12) !important; background: #fff !important; }
        .sn-inp.err   { border-color: #ef4444 !important; background: #fef2f2 !important; }
        .sn-em { display:none; }
        .sn-em.on { display:flex; }
        .sn-alert { display:none; }
        .sn-alert.on { display:flex; }
    </style>

    <div class="min-h-screen flex items-center justify-center px-4 py-10"
         style="background: linear-gradient(135deg, #eff6ff 0%, #f0f7ff 50%, #e8f4fd 100%);">

        <div class="w-full max-w-3xl sn-fadein">
            <div class="flex w-full rounded-2xl overflow-hidden shadow-2xl">

                {{-- Left Panel --}}
                <div class="hidden md:flex flex-col justify-between w-5/12 p-10 text-white flex-shrink-0"
                     style="background: linear-gradient(150deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);">
                    <div>
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-6"
                             style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold mb-3 leading-snug">{{ __('Welcome back!') }}</h2>
                        <p class="text-sm leading-relaxed mb-7" style="color:rgba(255,255,255,.8)">
                            {{ __('Sign in to manage your bookings, trips, and travel preferences.') }}
                        </p>
                        <div class="flex flex-col gap-3">
                            @foreach([__('Instant booking confirmation'), __('Manage all your trips'), __('Exclusive member offers')] as $f)
                                <div class="flex items-center gap-3 text-sm" style="color:rgba(255,255,255,.88)">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                                         style="background:rgba(255,255,255,.2)">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </div>
                                    {{ $f }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-xs mt-8" style="color:rgba(255,255,255,.45)">© {{ date('Y') }} Shopno Tours & Travels</div>
                </div>

                {{-- Right Panel --}}
                <div class="flex-1 bg-white p-8 md:p-12">
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-1">{{ __('Sign In') }}</h1>
                    @if(is_enable_registration())
                        <p class="text-sm text-gray-500 mb-6">
                            {{ __("Don't have an account?") }}
                            <a href="{{ url('/register') }}" class="text-blue-600 font-semibold hover:underline">{{ __('Sign up free') }}</a>
                        </p>
                    @endif

                    {{-- Alert --}}
                    <div class="sn-alert items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 mb-5" id="sn-alert">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span id="sn-alert-t"></span>
                    </div>

                    <form id="sn-form" method="POST" action="{{ url('/login') }}" novalidate class="flex flex-col gap-5">
                        <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Email') }}</div>
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <input type="email" name="email" autocomplete="email" placeholder="you@example.com"
                                       class="sn-inp sn-fi w-full h-12 pl-10 pr-4 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all">
                            </div>
                            <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="email">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span></span>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex justify-between items-center">
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Password') }}</div>
                                <a href="{{ url('/forgot-password') }}" class="text-xs text-blue-600 font-semibold hover:underline">{{ __('Forgot?') }}</a>
                            </div>
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                                <input type="password" name="password" id="sn-pw" autocomplete="current-password" placeholder="••••••••"
                                       class="sn-inp has-eye sn-fi w-full h-12 pl-10 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all">
                                <button type="button" onclick="snEye('sn-pw', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="password">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span></span>
                            </div>
                        </div>

                        {{-- Remember --}}
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="remember" id="sn-rem"
                                   style="width:16px; height:16px; accent-color:#2563eb; cursor:pointer; flex-shrink:0; margin:0;">
                            <label for="sn-rem" style="font-size:14px; color:#4b5563; cursor:pointer; margin:0; line-height:1;">{{ __('Remember me') }}</label>
                        </div>

                        @if(setting_item("user_enable_login_recaptcha"))
                            <div>{{ recaptcha_field($captcha_action ?? 'login') }}</div>
                        @endif

                        <button type="submit" id="sn-btn"
                                class="w-full h-13 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-60 disabled:pointer-events-none"
                                style="height:52px;">
                            <svg id="sn-ic" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                            </svg>
                            <svg id="sn-sp" class="w-4 h-4 sn-spin" style="display:none" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
                                <path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span id="sn-bt">{{ __('Sign In') }}</span>
                        </button>
                    </form>

                    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
                        <div class="flex items-center gap-3 my-5 text-xs text-gray-400">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            {{ __('or sign in with') }}
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>
                        <div class="flex gap-2">
                            @if(setting_item('facebook_enable'))
                                <a href="{{ url('/social-login/facebook') }}"
                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">
                                    <i class="fa fa-facebook" style="color:#1877f2"></i> Facebook
                                </a>
                            @endif
                            @if(setting_item('google_enable'))
                                <a href="{{ url('social-login/google') }}"
                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">
                                    <i class="fa fa-google" style="color:#ea4335"></i> Google
                                </a>
                            @endif
                            @if(setting_item('twitter_enable'))
                                <a href="{{ url('social-login/twitter') }}"
                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">
                                    <i class="fa fa-twitter" style="color:#1da1f2"></i> Twitter
                                </a>
                            @endif
                        </div>
                    @endif

                    <p class="text-center text-xs text-gray-400 mt-5">
                        {{ __('By signing in, you agree to our Terms & Privacy Policy.') }}
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        (function(){
            window.snEye = function(id, btn){
                var inp = document.getElementById(id);
                if(!inp) return;
                var p = inp.type === 'password';
                inp.type = p ? 'text' : 'password';
                btn.querySelector('svg').innerHTML = p
                    ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            };

            var form  = document.getElementById('sn-form');
            var btn   = document.getElementById('sn-btn');
            var btxt  = document.getElementById('sn-bt');
            var icon  = document.getElementById('sn-ic');
            var spin  = document.getElementById('sn-sp');
            var alert = document.getElementById('sn-alert');
            var atxt  = document.getElementById('sn-alert-t');
            var busy  = false;

            function setLoad(on){
                busy = on; btn.disabled = on;
                btxt.textContent = on ? '{{ __("Signing in...") }}' : '{{ __("Sign In") }}';
                icon.style.display = on ? 'none' : '';
                spin.style.display = on ? '' : 'none';
            }

            function showAlert(msg){ atxt.textContent = msg; alert.classList.add('on'); }

            function markErr(field, msg){
                var em = form.querySelector('.sn-em[data-field="'+field+'"]');
                var ip = form.querySelector('[name="'+field+'"]');
                if(em){ em.querySelector('span').textContent = msg; em.classList.add('on'); }
                if(ip) ip.classList.add('err');
            }

            function clearAll(){
                form.querySelectorAll('.sn-em').forEach(function(e){ e.classList.remove('on'); });
                form.querySelectorAll('.sn-fi').forEach(function(i){ i.classList.remove('err'); });
                alert.classList.remove('on');
            }

            form.querySelectorAll('.sn-fi').forEach(function(inp){
                inp.addEventListener('input', function(){
                    var em = form.querySelector('.sn-em[data-field="'+inp.name+'"]');
                    if(em) em.classList.remove('on');
                    inp.classList.remove('err');
                    alert.classList.remove('on');
                });
            });

            form.addEventListener('submit', function(e){
                e.preventDefault();
                if(busy) return;
                setLoad(true); clearAll();
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                })
                    .then(function(r){ return r.json().then(function(d){ return {ok:r.ok, d:d}; }); })
                    .then(function(res){
                        if(res.ok){
                            btxt.textContent = '{{ __("Redirecting...") }}';
                            window.location.href = res.d.redirect || '/';
                            return;
                        }
                        // 419 = CSRF token mismatch — reload page to get fresh token
                        if(res.status === 419){ window.location.reload(); return; }
                        setLoad(false);
                        var errs = res.d.errors || {};
                        Object.keys(errs).forEach(function(f){
                            markErr(f, Array.isArray(errs[f]) ? errs[f][0] : errs[f]);
                        });
                        showAlert(res.d.message || '{{ __("Something went wrong.") }}');
                    })
                    .catch(function(){
                        setLoad(false);
                        showAlert('{{ __("Network error. Please try again.") }}');
                    });
            });
        })();
    </script>
@endsection
