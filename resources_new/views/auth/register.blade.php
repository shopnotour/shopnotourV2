{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">--}}
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}

{{--    <style>--}}
{{--        * { font-family: 'Outfit', sans-serif; }--}}
{{--        @keyframes snFadeUp {--}}
{{--            from { opacity:0; transform:translateY(20px); }--}}
{{--            to   { opacity:1; transform:translateY(0); }--}}
{{--        }--}}
{{--        .sn-fadein { animation: snFadeUp 0.45s ease both; }--}}
{{--        @keyframes snSpin { to { transform:rotate(360deg); } }--}}
{{--        .sn-spin { animation: snSpin .7s linear infinite; }--}}
{{--        .sn-inp:focus { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,.12) !important; background: #fff !important; }--}}
{{--        .sn-inp.err   { border-color: #ef4444 !important; background: #fef2f2 !important; }--}}
{{--        .sn-em  { display:none; }--}}
{{--        .sn-em.on { display:flex; }--}}
{{--        .sn-alert { display:none; }--}}
{{--        .sn-alert.on { display:flex; }--}}
{{--        /* strength bars */--}}
{{--        .sn-sb { flex:1; height:3px; border-radius:99px; background:#e5e7eb; transition:background .3s; }--}}
{{--        .s1 { background:#ef4444; } .s2 { background:#f97316; }--}}
{{--        .s3 { background:#eab308; } .s4 { background:#22c55e; }--}}
{{--    </style>--}}

{{--    <div class="min-h-screen flex items-center justify-center px-4 py-10"--}}
{{--         style="background: linear-gradient(135deg, #eff6ff 0%, #f0f7ff 50%, #e8f4fd 100%);">--}}

{{--        <div class="w-full max-w-3xl sn-fadein">--}}
{{--            <div class="flex w-full rounded-2xl overflow-hidden shadow-2xl">--}}

{{--                --}}{{-- Left Panel --}}
{{--                <div class="hidden md:flex flex-col justify-between w-5/12 p-10 text-white flex-shrink-0"--}}
{{--                     style="background: linear-gradient(150deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);">--}}
{{--                    <div>--}}
{{--                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-6"--}}
{{--                             style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);">--}}
{{--                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">--}}
{{--                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>--}}
{{--                            </svg>--}}
{{--                        </div>--}}
{{--                        <h2 class="text-2xl font-extrabold mb-3 leading-snug">{{ __('Join us today!') }}</h2>--}}
{{--                        <p class="text-sm leading-relaxed mb-7" style="color:rgba(255,255,255,.8)">--}}
{{--                            {{ __('Create your free account and start exploring amazing travel experiences.') }}--}}
{{--                        </p>--}}
{{--                        <div class="flex flex-col gap-3">--}}
{{--                            @foreach([__('Free to join'), __('Best price guarantee'), __('24/7 customer support')] as $f)--}}
{{--                                <div class="flex items-center gap-3 text-sm" style="color:rgba(255,255,255,.88)">--}}
{{--                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"--}}
{{--                                         style="background:rgba(255,255,255,.2)">--}}
{{--                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="3">--}}
{{--                                            <polyline points="20 6 9 17 4 12"/>--}}
{{--                                        </svg>--}}
{{--                                    </div>--}}
{{--                                    {{ $f }}--}}
{{--                                </div>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="text-xs mt-8" style="color:rgba(255,255,255,.45)">© {{ date('Y') }} Shopno Tours</div>--}}
{{--                </div>--}}

{{--                --}}{{-- Right Panel --}}
{{--                <div class="flex-1 bg-white p-8 md:p-10 overflow-y-auto" style="max-height:92vh;">--}}
{{--                    <h1 class="text-3xl font-extrabold text-gray-900 mb-1">{{ __('Create Account') }}</h1>--}}
{{--                    <p class="text-sm text-gray-500 mb-6">--}}
{{--                        {{ __('Already have an account?') }}--}}
{{--                        <a href="{{ url('/login') }}" class="text-blue-600 font-semibold hover:underline">{{ __('Sign in') }}</a>--}}
{{--                    </p>--}}

{{--                    --}}{{-- Alert --}}
{{--                    <div class="sn-alert items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 mb-5" id="sn-alert">--}}
{{--                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>--}}
{{--                        </svg>--}}
{{--                        <span id="sn-alert-t"></span>--}}
{{--                    </div>--}}

{{--                    <form id="sn-form" method="POST" action="{{ url('/register') }}" novalidate class="flex flex-col gap-4">--}}
{{--                        @csrf--}}

{{--                        --}}{{-- Name row --}}
{{--                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">--}}
{{--                            <div class="flex flex-col gap-1.5">--}}
{{--                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('First Name') }} <span class="text-red-500">*</span></div>--}}
{{--                                <input type="text" name="first_name" autocomplete="given-name" placeholder="{{ __('First') }}"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 14px;">--}}
{{--                                <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="first_name">--}}
{{--                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>--}}
{{--                                    <span></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col gap-1.5">--}}
{{--                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Last Name') }}</div>--}}
{{--                                <input type="text" name="last_name" autocomplete="family-name" placeholder="{{ __('Last') }}"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 14px;">--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Phone --}}
{{--                        <div class="flex flex-col gap-1.5">--}}
{{--                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Phone') }}</div>--}}
{{--                            <div class="relative">--}}
{{--                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.11 1.18 2 2 0 012.11 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6z"/>--}}
{{--                                </svg>--}}
{{--                                <input type="text" name="phone" autocomplete="tel" placeholder="+880 1X-XXXXXXXX"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 14px 0 42px;">--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Email --}}
{{--                        <div class="flex flex-col gap-1.5">--}}
{{--                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Email') }} <span class="text-red-500">*</span></div>--}}
{{--                            <div class="relative">--}}
{{--                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>--}}
{{--                                </svg>--}}
{{--                                <input type="email" name="email" autocomplete="email" placeholder="you@example.com"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 14px 0 42px;">--}}
{{--                            </div>--}}
{{--                            <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="email">--}}
{{--                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>--}}
{{--                                <span></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Password --}}
{{--                        <div class="flex flex-col gap-1.5">--}}
{{--                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Password') }} <span class="text-red-500">*</span></div>--}}
{{--                            <div class="relative">--}}
{{--                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>--}}
{{--                                </svg>--}}
{{--                                <input type="password" name="password" id="sn-pw" autocomplete="new-password" placeholder="••••••••"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 44px 0 42px;">--}}
{{--                                <button type="button" onclick="snEye('sn-pw', this)"--}}
{{--                                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">--}}
{{--                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>--}}
{{--                                    </svg>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                            --}}{{-- Strength bars --}}
{{--                            <div style="display:flex; gap:4px; margin-top:2px;">--}}
{{--                                <div class="sn-sb" id="sn-s1"></div>--}}
{{--                                <div class="sn-sb" id="sn-s2"></div>--}}
{{--                                <div class="sn-sb" id="sn-s3"></div>--}}
{{--                                <div class="sn-sb" id="sn-s4"></div>--}}
{{--                            </div>--}}
{{--                            <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="password">--}}
{{--                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>--}}
{{--                                <span></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Confirm Password --}}
{{--                        <div class="flex flex-col gap-1.5">--}}
{{--                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Confirm Password') }} <span class="text-red-500">*</span></div>--}}
{{--                            <div class="relative">--}}
{{--                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                    <polyline points="20 6 9 17 4 12"/>--}}
{{--                                </svg>--}}
{{--                                <input type="password" name="password_confirmation" id="sn-cf" autocomplete="new-password" placeholder="••••••••"--}}
{{--                                       class="sn-inp sn-fi w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 outline-none transition-all"--}}
{{--                                       style="height:48px; padding:0 44px 0 42px;">--}}
{{--                                <button type="button" onclick="snEye('sn-cf', this)"--}}
{{--                                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">--}}
{{--                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">--}}
{{--                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>--}}
{{--                                    </svg>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                            <div id="sn-match" style="font-size:11.5px; display:none;"></div>--}}
{{--                            <div class="sn-em items-center gap-1 text-xs text-red-600" data-field="password_confirmation">--}}
{{--                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>--}}
{{--                                <span></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Terms --}}
{{--                        <div>--}}
{{--                            <div style="display:flex; align-items:flex-start; gap:8px;">--}}
{{--                                <input type="checkbox" name="term" id="sn-term" value="1"--}}
{{--                                       style="width:16px; height:16px; accent-color:#2563eb; cursor:pointer; flex-shrink:0; margin-top:2px;">--}}
{{--                                <label for="sn-term" style="font-size:13.5px; color:#4b5563; cursor:pointer; line-height:1.55;">--}}
{{--                                    {{ __('I accept the') }}--}}
{{--                                    <a href="#" class="text-blue-600 font-semibold hover:underline">{{ __('Terms') }}</a>--}}
{{--                                    {{ __('and') }}--}}
{{--                                    <a href="#" class="text-blue-600 font-semibold hover:underline">{{ __('Privacy Policy') }}</a>--}}
{{--                                </label>--}}
{{--                            </div>--}}
{{--                            <div class="sn-em items-center gap-1 text-xs text-red-600 mt-1" data-field="term">--}}
{{--                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>--}}
{{--                                <span></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        @if(setting_item("user_enable_register_recaptcha"))--}}
{{--                            <div>{{ recaptcha_field($captcha_action ?? 'register') }}</div>--}}
{{--                        @endif--}}

{{--                        <button type="submit" id="sn-btn"--}}
{{--                                class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-60 disabled:pointer-events-none"--}}
{{--                                style="height:52px; font-size:15px;">--}}
{{--                            <svg id="sn-ic" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">--}}
{{--                                <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>--}}
{{--                                <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>--}}
{{--                            </svg>--}}
{{--                            <svg id="sn-sp" class="w-4 h-4 sn-spin" style="display:none" fill="none" viewBox="0 0 24 24">--}}
{{--                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>--}}
{{--                                <path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>--}}
{{--                            </svg>--}}
{{--                            <span id="sn-bt">{{ __('Create Account') }}</span>--}}
{{--                        </button>--}}
{{--                    </form>--}}

{{--                    @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))--}}
{{--                        <div class="flex items-center gap-3 my-4 text-xs text-gray-400">--}}
{{--                            <div class="flex-1 h-px bg-gray-200"></div>--}}
{{--                            {{ __('or sign up with') }}--}}
{{--                            <div class="flex-1 h-px bg-gray-200"></div>--}}
{{--                        </div>--}}
{{--                        <div class="flex gap-2">--}}
{{--                            @if(setting_item('facebook_enable'))--}}
{{--                                <a href="{{ url('/social-login/facebook') }}"--}}
{{--                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">--}}
{{--                                    <i class="fa fa-facebook" style="color:#1877f2"></i> Facebook--}}
{{--                                </a>--}}
{{--                            @endif--}}
{{--                            @if(setting_item('google_enable'))--}}
{{--                                <a href="{{ url('social-login/google') }}"--}}
{{--                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">--}}
{{--                                    <i class="fa fa-google" style="color:#ea4335"></i> Google--}}
{{--                                </a>--}}
{{--                            @endif--}}
{{--                            @if(setting_item('twitter_enable'))--}}
{{--                                <a href="{{ url('social-login/twitter') }}"--}}
{{--                                   class="flex-1 h-10 flex items-center justify-center gap-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all no-underline">--}}
{{--                                    <i class="fa fa-twitter" style="color:#1da1f2"></i> Twitter--}}
{{--                                </a>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <script>--}}
{{--        (function(){--}}
{{--            window.snEye = function(id, btn){--}}
{{--                var inp = document.getElementById(id);--}}
{{--                if(!inp) return;--}}
{{--                var p = inp.type === 'password';--}}
{{--                inp.type = p ? 'text' : 'password';--}}
{{--                btn.querySelector('svg').innerHTML = p--}}
{{--                    ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'--}}
{{--                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';--}}
{{--            };--}}

{{--            /* Strength */--}}
{{--            var pw = document.getElementById('sn-pw');--}}
{{--            var cf = document.getElementById('sn-cf');--}}
{{--            var sc = ['','s1','s2','s3','s4'];--}}

{{--            function chkMatch(){--}}
{{--                var h = document.getElementById('sn-match');--}}
{{--                if(!pw||!cf||!h) return;--}}
{{--                if(!cf.value){ h.style.display='none'; return; }--}}
{{--                var ok = pw.value === cf.value;--}}
{{--                h.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';--}}
{{--                h.style.color = ok ? '#16a34a' : '#dc2626';--}}
{{--                h.style.display = 'flex';--}}
{{--            }--}}

{{--            if(pw){--}}
{{--                pw.addEventListener('input', function(){--}}
{{--                    var v=this.value, s=0;--}}
{{--                    if(v.length>=6) s++; if(/[A-Z]/.test(v)) s++;--}}
{{--                    if(/[0-9]/.test(v)) s++; if(/[^A-Za-z0-9]/.test(v)) s++;--}}
{{--                    ['sn-s1','sn-s2','sn-s3','sn-s4'].forEach(function(id,i){--}}
{{--                        var el=document.getElementById(id);--}}
{{--                        if(el) el.className='sn-sb '+(v.length>0&&i<s?sc[s]:'');--}}
{{--                    });--}}
{{--                    chkMatch();--}}
{{--                });--}}
{{--            }--}}
{{--            if(cf) cf.addEventListener('input', chkMatch);--}}

{{--            var form  = document.getElementById('sn-form');--}}
{{--            var btn   = document.getElementById('sn-btn');--}}
{{--            var btxt  = document.getElementById('sn-bt');--}}
{{--            var icon  = document.getElementById('sn-ic');--}}
{{--            var spin  = document.getElementById('sn-sp');--}}
{{--            var alert = document.getElementById('sn-alert');--}}
{{--            var atxt  = document.getElementById('sn-alert-t');--}}
{{--            var busy  = false;--}}

{{--            function setLoad(on){--}}
{{--                busy=on; btn.disabled=on;--}}
{{--                btxt.textContent = on ? '{{ __("Creating account...") }}' : '{{ __("Create Account") }}';--}}
{{--                icon.style.display = on ? 'none' : '';--}}
{{--                spin.style.display = on ? '' : 'none';--}}
{{--            }--}}

{{--            function showAlert(msg){ atxt.textContent=msg; alert.classList.add('on'); }--}}

{{--            function markErr(field, msg){--}}
{{--                var em = form.querySelector('.sn-em[data-field="'+field+'"]');--}}
{{--                var ip = form.querySelector('[name="'+field+'"]');--}}
{{--                if(em){ em.querySelector('span').textContent=msg; em.classList.add('on'); }--}}
{{--                if(ip) ip.classList.add('err');--}}
{{--            }--}}

{{--            function clearAll(){--}}
{{--                form.querySelectorAll('.sn-em').forEach(function(e){ e.classList.remove('on'); });--}}
{{--                form.querySelectorAll('.sn-fi').forEach(function(i){ i.classList.remove('err'); });--}}
{{--                alert.classList.remove('on');--}}
{{--            }--}}

{{--            form.querySelectorAll('.sn-fi').forEach(function(inp){--}}
{{--                inp.addEventListener('input', function(){--}}
{{--                    var em = form.querySelector('.sn-em[data-field="'+inp.name+'"]');--}}
{{--                    if(em) em.classList.remove('on');--}}
{{--                    inp.classList.remove('err');--}}
{{--                    alert.classList.remove('on');--}}
{{--                });--}}
{{--            });--}}

{{--            form.addEventListener('submit', function(e){--}}
{{--                e.preventDefault();--}}
{{--                if(busy) return;--}}
{{--                setLoad(true); clearAll();--}}
{{--                fetch(form.action, {--}}
{{--                    method: 'POST',--}}
{{--                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },--}}
{{--                    body: new FormData(form)--}}
{{--                })--}}
{{--                    .then(function(r){ return r.json().then(function(d){ return {ok:r.ok, d:d}; }); })--}}
{{--                    .then(function(res){--}}
{{--                        if(res.ok){--}}
{{--                            btxt.textContent = '{{ __("Redirecting...") }}';--}}
{{--                            window.location.href = res.d.redirect || '/';--}}
{{--                            return;--}}
{{--                        }--}}
{{--                        // 419 = CSRF token mismatch — reload page to get fresh token--}}
{{--                        if(res.status === 419){ window.location.reload(); return; }--}}
{{--                        setLoad(false);--}}
{{--                        var errs = res.d.errors || {};--}}
{{--                        Object.keys(errs).forEach(function(f){--}}
{{--                            markErr(f, Array.isArray(errs[f]) ? errs[f][0] : errs[f]);--}}
{{--                        });--}}
{{--                        showAlert(res.d.message || '{{ __("Please fix the errors above.") }}');--}}
{{--                    })--}}
{{--                    .catch(function(){--}}
{{--                        setLoad(false);--}}
{{--                        showAlert('{{ __("Network error. Please try again.") }}');--}}
{{--                    });--}}
{{--            });--}}
{{--        })();--}}
{{--    </script>--}}
{{--@endsection--}}





@extends('layouts.app')

@section('content')

    <style>
        /* ── Page background ── */
        .reg-page {
            min-height: 100vh;
            background: #f1f5f9;
            padding: 40px 16px 60px;
        }

        /* ── Main card ── */
        .reg-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
            max-width: 680px;
            margin: 0 auto;
        }

        /* ── Card header ── */
        .reg-header {
            background: #fff;
            border-bottom: 1px solid #e8edf5;
            padding: 24px 32px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .reg-header-left h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 3px;
            color: #0f172a;
            letter-spacing: -.3px;
        }
        .reg-header-left p {
            font-size: 13px;
            margin: 0;
            color: #64748b;
        }
        .reg-header-left a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .reg-header-left a:hover { text-decoration: underline; }

        /* ── Account type toggle ── */
        .type-toggle {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        .type-btn {
            padding: 8px 16px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all .18s;
            white-space: nowrap;
        }
        .type-btn:hover {
            border-color: #93c5fd;
            color: #1d4ed8;
            background: #eff6ff;
        }
        .type-btn.active {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
            box-shadow: 0 3px 10px rgba(37,99,235,.25);
        }
        .type-btn i { font-size: 15px; }
        .type-badge {
            font-size: 10px;
            padding: 1px 7px;
            border-radius: 999px;
            background: rgba(255,255,255,.22);
            color: inherit;
        }
        .type-btn:not(.active) .type-badge {
            background: #e2e8f0;
            color: #64748b;
        }
        .type-btn.active .type-badge {
            background: rgba(255,255,255,.25);
            color: #fff;
        }

        @media (max-width: 520px) {
            .reg-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }
            .type-toggle { width: 100%; }
            .type-btn { flex: 1; }
        }

        /* ── Stepper ── */
        .stepper-wrap {
            padding: 24px 32px 0;
            background: #fff;
        }
        .stepper {
            display: flex;
            align-items: flex-start;
            position: relative;
        }
        .step-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
            transition: background .3s;
        }
        .step-item.done:not(:last-child)::after { background: #22c55e; }
        .step-item.active:not(:last-child)::after { background: #bfdbfe; }

        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #94a3b8;
            transition: all .25s;
            position: relative;
            z-index: 2;
        }
        .step-item.active .step-circle {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
            box-shadow: 0 0 0 4px #dbeafe;
        }
        .step-item.done .step-circle {
            border-color: #22c55e;
            background: #22c55e;
            color: #fff;
        }
        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            margin-top: 6px;
            text-align: center;
            line-height: 1.3;
            transition: color .2s;
        }
        .step-item.active .step-label { color: #2563eb; }
        .step-item.done   .step-label { color: #16a34a; }

        /* ── Form body ── */
        .reg-body { padding: 24px 32px 32px; }

        /* Step panels */
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeUp .3s ease both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

        /* Section inside a step */
        .form-section {
            border: 1px solid #e8edf5;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .form-section:last-of-type { margin-bottom: 0; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-title i { font-size: 14px; }

        /* Labels */
        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 6px;
        }
        .req { color: #ef4444; }

        /* Inputs */
        .form-control, .form-select {
            height: 46px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: 14px;
            color: #0f172a;
            padding: 0 14px;
            transition: all .15s;
            width: 100%;
        }
        .form-control::placeholder { color: #94a3b8; }
        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            outline: none;
        }
        .form-control.is-invalid { border-color: #ef4444; background: #fef2f2; }
        .input-group .form-control { border-radius: 0 10px 10px 0 !important; }
        .input-group-text {
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            padding: 0 10px;
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        /* Password eye */
        .pw-wrap { position: relative; }
        .pw-wrap .form-control { padding-right: 42px; }
        .pw-eye {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; padding: 4px;
            color: #94a3b8; font-size: 16px;
            line-height: 1;
        }
        .pw-eye:hover { color: #475569; }

        /* Strength bars */
        .str-bars { display: flex; gap: 4px; margin-top: 6px; }
        .str-bar {
            flex: 1; height: 3px;
            border-radius: 99px;
            background: #e2e8f0;
            transition: background .3s;
        }
        .s1{background:#ef4444}.s2{background:#f97316}.s3{background:#eab308}.s4{background:#22c55e}

        /* Password match */
        .pw-match { font-size: 12px; margin-top: 5px; display: none; }
        .pw-match.ok { display: block; color: #16a34a; }
        .pw-match.no { display: block; color: #dc2626; }

        /* Field error */
        .field-msg {
            font-size: 12px;
            color: #dc2626;
            margin-top: 5px;
            display: none;
            align-items: center;
            gap: 4px;
        }
        .field-msg.show { display: flex; }
        .field-msg i { font-size: 13px; }

        /* File upload drop zone */
        .file-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #f8fafc;
        }
        .file-zone:hover, .file-zone.drag { border-color: #2563eb; background: #eff6ff; }
        .file-zone.done { border: 2px solid #22c55e; background: #f0fdf4; cursor: default; }
        .file-zone i { font-size: 28px; color: #94a3b8; margin-bottom: 8px; display: block; }
        .file-zone.done i { color: #22c55e; }
        .file-zone p { font-size: 13px; font-weight: 600; color: #475569; margin: 0 0 3px; }
        .file-zone small { font-size: 12px; color: #94a3b8; }
        .file-done-name { font-size: 13px; font-weight: 600; color: #15803d; margin: 0; }
        .file-clear {
            background: none; border: none; cursor: pointer;
            color: #94a3b8; font-size: 16px; margin-left: 6px;
            vertical-align: middle;
        }
        .file-clear:hover { color: #ef4444; }

        /* Alert */
        .sn-alert {
            display: none;
            align-items: flex-start;
            gap: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #b91c1c;
            margin-bottom: 16px;
        }
        .sn-alert.show { display: flex; }
        .sn-alert i { font-size: 15px; margin-top: 1px; flex-shrink: 0; }

        /* Summary grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .sum-item {
            background: #f8fafc;
            border: 1px solid #e8edf5;
            border-radius: 10px;
            padding: 10px 12px;
        }
        .sum-item .sum-label {
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 3px;
        }
        .sum-item .sum-val {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Terms */
        .terms-wrap {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 4px;
        }
        .terms-wrap input[type=checkbox] {
            width: 16px; height: 16px;
            margin-top: 2px; flex-shrink: 0;
            accent-color: #2563eb; cursor: pointer;
        }
        .terms-wrap label {
            font-size: 13.5px;
            color: #475569;
            cursor: pointer;
            line-height: 1.55;
            margin: 0;
        }
        .terms-wrap a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .terms-wrap a:hover { text-decoration: underline; }

        /* Navigation buttons */
        .nav-btns {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        .btn-back {
            display: flex; align-items: center; gap: 6px;
            padding: 11px 20px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }
        .btn-back:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
        .btn-next, .btn-submit {
            display: flex; align-items: center; gap: 6px;
            padding: 11px 28px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(37,99,235,.25);
            transition: all .15s;
        }
        .btn-next:hover, .btn-submit:hover { box-shadow: 0 5px 16px rgba(37,99,235,.38); }
        .btn-submit:disabled { opacity: .6; cursor: not-allowed; pointer-events: none; }
        .btn-next i, .btn-submit i, .btn-back i { font-size: 15px; }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinning { display: inline-block; animation: spin .7s linear infinite; }

        /* Warning note */
        .warn-note {
            display: flex; align-items: center; gap: 8px;
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 10px; padding: 10px 14px;
            font-size: 12.5px; color: #92400e; margin-top: 12px;
        }
        .warn-note i { font-size: 14px; color: #d97706; flex-shrink: 0; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .reg-page { padding: 16px 8px 40px; }
            .stepper-wrap { padding: 18px 20px 0; }
            .step-label { font-size: 10px; }
            .reg-body { padding: 18px 16px 24px; }
            .form-section { padding: 14px 14px; }
        }
        @media (max-width: 400px) {
            .step-label { display: none; }
            .step-circle { width: 30px; height: 30px; font-size: 12px; }
            .step-item:not(:last-child)::after { top: 15px; }
        }
    </style>

    <div class="reg-page">
        <div class="reg-card">

            {{-- ── Header ── --}}
            <div class="reg-header">
                <div class="reg-header-left">
                    <h1><i class="ti ti-user-plus" style="font-size:18px;vertical-align:-2px;margin-right:6px;color:#2563eb" aria-hidden="true"></i>Create Your Account</h1>
                    <p>Already have an account? <a href="{{ url('/login') }}">Sign in here</a></p>
                </div>

                {{-- Account type toggle — right side --}}
                <div class="type-toggle">
                    <button type="button" class="type-btn active" id="btn-b2c" onclick="switchType('b2c')">
                        <i class="ti ti-user" aria-hidden="true"></i>
                        <span>Personal</span>
                        <span class="type-badge">B2C</span>
                    </button>
                    <button type="button" class="type-btn" id="btn-b2b" onclick="switchType('b2b')">
                        <i class="ti ti-building" aria-hidden="true"></i>
                        <span>Corporate</span>
                        <span class="type-badge">B2B</span>
                    </button>
                </div>
            </div>

            {{-- ── Stepper ── --}}
            <div class="stepper-wrap">
                <div class="stepper">
                    <div class="step-item active" id="si1">
                        <div class="step-circle" id="sc1">1</div>
                        <div class="step-label">Customer<br>Info</div>
                    </div>
                    <div class="step-item" id="si2">
                        <div class="step-circle" id="sc2">2</div>
                        <div class="step-label">Documents</div>
                    </div>
                    <div class="step-item" id="si3">
                        <div class="step-circle" id="sc3">3</div>
                        <div class="step-label">Review &<br>Submit</div>
                    </div>
                </div>
            </div>

            {{-- ── Form ── --}}
            <div class="reg-body">
                <form id="sn-form" method="POST" action="{{ url('/register') }}" novalidate enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_type" id="input-type" value="b2c">

                    {{-- ════════════════════════
                         STEP 1 — Customer Info
                    ════════════════════════ --}}
                    <div class="step-panel active" id="panel1">

                        {{-- Personal Details --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="ti ti-user" aria-hidden="true"></i> Personal Details
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">First Name <span class="req">*</span></label>
                                    <input type="text" name="first_name" class="form-control sn-fi" placeholder="First name" autocomplete="given-name">
                                    <div class="field-msg" data-field="first_name"><i class="ti ti-alert-circle"></i><span></span></div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control sn-fi" placeholder="Last name" autocomplete="family-name">
                                </div>
                                <div class="col-12 b2b-only" style="display:none;">
                                    <label class="form-label">Company Name <span class="req">*</span></label>
                                    <input type="text" name="company_name" class="form-control sn-fi" placeholder="Your company name">
                                    <div class="field-msg" data-field="company_name"><i class="ti ti-alert-circle"></i><span></span></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control sn-fi" placeholder="Your full address" autocomplete="street-address">
                                </div>
                            </div>
                        </div>

                        {{-- Contact & Location --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="ti ti-map-pin" aria-hidden="true"></i> Contact &amp; Location
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">🇧🇩 +880</span>
                                        <input type="text" name="phone" class="form-control sn-fi" placeholder="1X-XXXXXXXX" autocomplete="tel">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Country / Region</label>
                                    <select name="country" class="form-select sn-fi">
                                        <option value="BD" selected>Bangladesh</option>
                                        <option value="IN">India</option>
                                        <option value="PK">Pakistan</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">City / Area</label>
                                    <select name="city" class="form-select sn-fi">
                                        <option value="">Select city</option>
                                        <option>Dhaka</option><option>Chittagong</option><option>Sylhet</option>
                                        <option>Rajshahi</option><option>Khulna</option><option>Comilla</option>
                                        <option>Mymensingh</option><option>Barishal</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Customer Email <span class="req">*</span></label>
                                    <input type="email" name="email" class="form-control sn-fi" placeholder="you@example.com" autocomplete="email">
                                    <div class="field-msg" data-field="email"><i class="ti ti-alert-circle"></i><span></span></div>
                                </div>
                            </div>
                        </div>

                        {{-- Account Credentials --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="ti ti-lock" aria-hidden="true"></i> Account Credentials
                            </div>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Password <span class="req">*</span> <small class="text-muted fw-normal text-lowercase">(min 6 chars)</small></label>
                                    <div class="pw-wrap">
                                        <input type="password" name="password" id="sn-pw" class="form-control sn-fi" placeholder="••••••••" autocomplete="new-password">
                                        <button type="button" class="pw-eye" onclick="toggleEye('sn-pw', this)">
                                            <i class="ti ti-eye eye-s"></i><i class="ti ti-eye-off eye-h" style="display:none"></i>
                                        </button>
                                    </div>
                                    <div class="str-bars">
                                        <div class="str-bar" id="sb1"></div><div class="str-bar" id="sb2"></div>
                                        <div class="str-bar" id="sb3"></div><div class="str-bar" id="sb4"></div>
                                    </div>
                                    <div class="field-msg" data-field="password"><i class="ti ti-alert-circle"></i><span></span></div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Confirm Password <span class="req">*</span></label>
                                    <div class="pw-wrap">
                                        <input type="password" name="password_confirmation" id="sn-cf" class="form-control sn-fi" placeholder="••••••••" autocomplete="new-password">
                                        <button type="button" class="pw-eye" onclick="toggleEye('sn-cf', this)">
                                            <i class="ti ti-eye eye-s"></i><i class="ti ti-eye-off eye-h" style="display:none"></i>
                                        </button>
                                    </div>
                                    <div class="pw-match" id="pw-match"></div>
                                </div>
                            </div>
                        </div>

                        <div class="nav-btns" style="justify-content:flex-end;">
                            <button type="button" class="btn-next" onclick="goStep(2)">
                                Next <i class="ti ti-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ════════════════════════
                         STEP 2 — Documents
                    ════════════════════════ --}}
                    <div class="step-panel" id="panel2">

                        {{-- B2C: only NID --}}
                        <div id="docs-b2c">
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="ti ti-id" aria-hidden="true"></i> Identity Document
                                </div>
                                <label class="form-label mb-2">Upload NID <span class="req">*</span></label>
                                <div class="file-zone" id="dz-nid" onclick="document.getElementById('fi-nid').click()">
                                    <div id="dz-nid-e">
                                        <i class="ti ti-id-badge-2"></i>
                                        <p>Click or drag &amp; drop your NID</p>
                                        <small>PNG, JPG or PDF — max 5MB</small>
                                    </div>
                                    <div id="dz-nid-d" style="display:none;">
                                        <i class="ti ti-circle-check" style="font-size:28px;color:#22c55e;display:block;margin-bottom:6px;"></i>
                                        <p class="file-done-name" id="nid-name"></p>
                                        <button type="button" class="file-clear" onclick="clearFile('nid',event)" title="Remove"><i class="ti ti-x"></i></button>
                                    </div>
                                </div>
                                <input type="file" id="fi-nid" name="nid_document" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>

                        {{-- B2B: NID + Trade License --}}
                        <div id="docs-b2b" style="display:none;">
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="ti ti-files" aria-hidden="true"></i> Business Documents
                                </div>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label mb-2">Upload NID <span class="req">*</span></label>
                                        <div class="file-zone" id="dz-nid-b" onclick="document.getElementById('fi-nid-b').click()">
                                            <div id="dz-nid-b-e">
                                                <i class="ti ti-id-badge-2" style="font-size:22px;"></i>
                                                <p style="font-size:12px;">Click to upload NID</p>
                                                <small>PNG, JPG or PDF</small>
                                            </div>
                                            <div id="dz-nid-b-d" style="display:none;">
                                                <i class="ti ti-circle-check" style="font-size:22px;color:#22c55e;display:block;margin-bottom:4px;"></i>
                                                <p class="file-done-name" id="nid-b-name" style="font-size:12px;"></p>
                                                <button type="button" class="file-clear" onclick="clearFile('nid-b',event)"><i class="ti ti-x"></i></button>
                                            </div>
                                        </div>
                                        <input type="file" id="fi-nid-b" name="nid_document" class="d-none" accept=".jpg,.jpeg,.png,.pdf" disabled>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label mb-2">Trade License <span class="req">*</span></label>
                                        <div class="file-zone" id="dz-tl" onclick="document.getElementById('fi-tl').click()">
                                            <div id="dz-tl-e">
                                                <i class="ti ti-file-certificate" style="font-size:22px;"></i>
                                                <p style="font-size:12px;">Click to upload License</p>
                                                <small>PNG, JPG or PDF</small>
                                            </div>
                                            <div id="dz-tl-d" style="display:none;">
                                                <i class="ti ti-circle-check" style="font-size:22px;color:#22c55e;display:block;margin-bottom:4px;"></i>
                                                <p class="file-done-name" id="tl-name" style="font-size:12px;"></p>
                                                <button type="button" class="file-clear" onclick="clearFile('tl',event)"><i class="ti ti-x"></i></button>
                                            </div>
                                        </div>
                                        <input type="file" id="fi-tl" name="trade_license" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                                    </div>
                                </div>
                                <div class="warn-note">
                                    <i class="ti ti-info-circle"></i>
                                    Must upload a valid and updated Trade License along with your NID.
                                </div>
                            </div>
                        </div>

                        <div class="nav-btns">
                            <button type="button" class="btn-back" onclick="goStep(1)">
                                <i class="ti ti-arrow-left"></i> Back
                            </button>
                            <button type="button" class="btn-next" onclick="goStep(3)">
                                Next <i class="ti ti-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ════════════════════════
                         STEP 3 — Review & Submit
                    ════════════════════════ --}}
                    <div class="step-panel" id="panel3">

                        {{-- Alert --}}
                        <div class="sn-alert" id="sn-alert">
                            <i class="ti ti-alert-circle"></i>
                            <span id="sn-alert-msg"></span>
                        </div>

                        {{-- Summary --}}
                        <div class="form-section">
                            <div class="section-title">
                                <i class="ti ti-checklist" aria-hidden="true"></i> Customer Details
                            </div>
                            <div class="summary-grid" id="summary-grid"></div>

                            {{-- Uploaded docs summary --}}
                            <div id="doc-summary" style="margin-top:4px;"></div>
                        </div>

                        {{-- Terms --}}
                        <div class="form-section">
                            <div class="terms-wrap">
                                <input type="checkbox" name="term" id="sn-term" value="1">
                                <label for="sn-term">
                                    I accept the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                            </div>
                            <div class="field-msg" data-field="term" style="margin-top:4px;">
                                <i class="ti ti-alert-circle"></i><span></span>
                            </div>
                        </div>

                        @if(setting_item("user_enable_register_recaptcha"))
                            <div class="mb-3">{{ recaptcha_field($captcha_action ?? 'register') }}</div>
                        @endif

                        <div class="nav-btns">
                            <button type="button" class="btn-back" onclick="goStep(2)">
                                <i class="ti ti-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn-submit" id="sn-submit">
                                <i class="ti ti-check" id="submit-icon"></i>
                                <i class="ti ti-loader spinning" id="submit-spin" style="display:none;"></i>
                                <span id="submit-txt">Submit</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Social login --}}
            @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
                <div style="padding: 0 32px 28px;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="flex:1;height:1px;background:#e2e8f0;"></div>
                        <span style="font-size:12px;color:#94a3b8;">or sign up with</span>
                        <div style="flex:1;height:1px;background:#e2e8f0;"></div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        @if(setting_item('facebook_enable'))
                            <a href="{{ url('/social-login/facebook') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;height:40px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#93c5fd'" onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0'">
                                <i class="ti ti-brand-facebook" style="color:#1877f2;font-size:16px;"></i>
                                <span class="d-none d-sm-inline">Facebook</span>
                            </a>
                        @endif
                        @if(setting_item('google_enable'))
                            <a href="{{ url('social-login/google') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;height:40px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='#fef2f2';this.style.borderColor='#fca5a5'" onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0'">
                                <i class="ti ti-brand-google" style="color:#ea4335;font-size:16px;"></i>
                                <span class="d-none d-sm-inline">Google</span>
                            </a>
                        @endif
                        @if(setting_item('twitter_enable'))
                            <a href="{{ url('social-login/twitter') }}" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;height:40px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:13px;font-weight:600;color:#374151;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='#f0f9ff';this.style.borderColor='#7dd3fc'" onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0'">
                                <i class="ti ti-brand-twitter" style="color:#1da1f2;font-size:16px;"></i>
                                <span class="d-none d-sm-inline">Twitter</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

        </div>{{-- /reg-card --}}

        <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;">
            By creating an account, you agree to our Terms of Service and Privacy Statement.
        </p>
    </div>{{-- /reg-page --}}

    <script>
        (function () {
            'use strict';

            var curType = 'b2c';
            var curStep = 1;

            /* ═══════════════════════════════
               Account type switch
            ═══════════════════════════════ */
            window.switchType = function (t) {
                curType = t;
                document.getElementById('input-type').value = t;

                var isB2B = t === 'b2b';
                document.getElementById('btn-b2c').classList.toggle('active', !isB2B);
                document.getElementById('btn-b2b').classList.toggle('active', isB2B);

                document.querySelectorAll('.b2b-only').forEach(function (el) {
                    el.style.display = isB2B ? '' : 'none';
                });

                document.getElementById('docs-b2c').style.display = isB2B ? 'none' : '';
                document.getElementById('docs-b2b').style.display = isB2B ? '' : 'none';

                // ── KEY FIX: hidden mode এর input disabled করো ──
                // B2C mode এ: fi-nid active, fi-nid-b disabled
                // B2B mode এ: fi-nid-b active, fi-nid disabled
                document.getElementById('fi-nid').disabled   = isB2B;   // B2B তে B2C input off
                document.getElementById('fi-nid-b').disabled = !isB2B;  // B2C তে B2B input off
            };

            /* ═══════════════════════════════
               Step navigation
            ═══════════════════════════════ */
            window.goStep = function (n) {
                /* hide current panel */
                document.getElementById('panel' + curStep).classList.remove('active');

                /* update stepper circles */
                for (var i = 1; i <= 3; i++) {
                    var si = document.getElementById('si' + i);
                    var sc = document.getElementById('sc' + i);
                    si.classList.remove('active', 'done');
                    if (i < n) {
                        si.classList.add('done');
                        sc.innerHTML = '<i class="ti ti-check" style="font-size:14px;"></i>';
                    } else if (i === n) {
                        si.classList.add('active');
                        sc.textContent = i;
                    } else {
                        sc.textContent = i;
                    }
                }

                curStep = n;
                document.getElementById('panel' + n).classList.add('active');

                if (n === 3) buildSummary();

                /* scroll to top of card */
                document.querySelector('.reg-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            /* ═══════════════════════════════
               Summary builder
            ═══════════════════════════════ */
            function buildSummary() {
                var form = document.getElementById('sn-form');
                var grid = document.getElementById('summary-grid');
                var docSum = document.getElementById('doc-summary');
                if (!form || !grid) return;

                function val(name) {
                    var el = form.querySelector('[name="' + name + '"]');
                    return el && el.value ? el.value : '—';
                }

                var fields = [
                    { l: 'First Name',  v: val('first_name') },
                    { l: 'Last Name',   v: val('last_name') },
                    { l: 'Email',       v: val('email') },
                    { l: 'Phone',       v: val('phone') !== '—' ? '+880 ' + val('phone') : '—' },
                    { l: 'Country',     v: val('country') },
                    { l: 'City / Area', v: val('city') || '—' },
                    { l: 'Address',     v: val('address') || '—' },
                ];

                if (curType === 'b2b') {
                    fields.splice(2, 0, { l: 'Company', v: val('company_name') });
                }

                grid.innerHTML = fields.map(function (f) {
                    return '<div class="sum-item"><div class="sum-label">' + f.l + '</div><div class="sum-val" title="' + f.v + '">' + f.v + '</div></div>';
                }).join('');

                /* document summary */
                var docHtml = '<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">';
                var nidEl = document.getElementById(curType === 'b2b' ? 'nid-b-name' : 'nid-name');
                if (nidEl && nidEl.textContent) {
                    docHtml += '<span style="font-size:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:4px 10px;color:#15803d;"><i class="ti ti-id" style="vertical-align:-2px;margin-right:4px;"></i>' + nidEl.textContent + '</span>';
                }
                var tlEl = document.getElementById('tl-name');
                if (tlEl && tlEl.textContent) {
                    docHtml += '<span style="font-size:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:4px 10px;color:#15803d;"><i class="ti ti-file-certificate" style="vertical-align:-2px;margin-right:4px;"></i>' + tlEl.textContent + '</span>';
                }
                docHtml += '</div>';
                docSum.innerHTML = docHtml;
            }

            /* ═══════════════════════════════
               Password eye toggle
            ═══════════════════════════════ */
            window.toggleEye = function (id, btn) {
                var inp = document.getElementById(id);
                if (!inp) return;
                var isPass = inp.type === 'password';
                inp.type = isPass ? 'text' : 'password';
                btn.querySelector('.eye-s').style.display = isPass ? 'none' : '';
                btn.querySelector('.eye-h').style.display = isPass ? '' : 'none';
            };

            /* ═══════════════════════════════
               Password strength
            ═══════════════════════════════ */
            var pwInp = document.getElementById('sn-pw');
            var cfInp = document.getElementById('sn-cf');
            var sc    = ['', 's1', 's2', 's3', 's4'];

            function checkMatch() {
                var el = document.getElementById('pw-match');
                if (!pwInp || !cfInp || !el) return;
                if (!cfInp.value) { el.className = 'pw-match'; return; }
                var ok = pwInp.value === cfInp.value;
                el.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
                el.className = 'pw-match ' + (ok ? 'ok' : 'no');
            }

            if (pwInp) {
                pwInp.addEventListener('input', function () {
                    var v = this.value, s = 0;
                    if (v.length >= 6) s++;
                    if (/[A-Z]/.test(v)) s++;
                    if (/[0-9]/.test(v)) s++;
                    if (/[^A-Za-z0-9]/.test(v)) s++;
                    ['sb1','sb2','sb3','sb4'].forEach(function (id, i) {
                        var bar = document.getElementById(id);
                        if (bar) bar.className = 'str-bar ' + (v.length > 0 && i < s ? sc[s] : '');
                    });
                    checkMatch();
                });
            }
            if (cfInp) cfInp.addEventListener('input', checkMatch);

            /* ═══════════════════════════════
               File upload zones
            ═══════════════════════════════ */
            var fileMap = {
                'nid':   { input: 'fi-nid',   zone: 'dz-nid',   empty: 'dz-nid-e',   done: 'dz-nid-d',   name: 'nid-name' },
                'nid-b': { input: 'fi-nid-b', zone: 'dz-nid-b', empty: 'dz-nid-b-e', done: 'dz-nid-b-d', name: 'nid-b-name' },
                'tl':    { input: 'fi-tl',    zone: 'dz-tl',    empty: 'dz-tl-e',    done: 'dz-tl-d',    name: 'tl-name' },
            };

            Object.keys(fileMap).forEach(function (key) {
                var m = fileMap[key];
                var inp  = document.getElementById(m.input);
                var zone = document.getElementById(m.zone);
                if (!inp || !zone) return;

                inp.addEventListener('change', function () {
                    if (!this.files || !this.files[0]) return;
                    document.getElementById(m.name).textContent = this.files[0].name;
                    document.getElementById(m.empty).style.display = 'none';
                    document.getElementById(m.done).style.display  = '';
                    zone.classList.add('done');
                });

                zone.addEventListener('dragover',  function (e) { e.preventDefault(); this.classList.add('drag'); });
                zone.addEventListener('dragleave', function ()  { this.classList.remove('drag'); });
                zone.addEventListener('drop', function (e) {
                    e.preventDefault(); this.classList.remove('drag');
                    var files = e.dataTransfer.files;
                    if (!files || !files[0]) return;

                    // DataTransfer দিয়ে file input এ assign করো
                    var dt = new DataTransfer();
                    dt.items.add(files[0]);
                    inp.files = dt.files;  // ✅ এভাবে কাজ করে
                    inp.dispatchEvent(new Event('change'));
                });
            });

            window.clearFile = function (key, e) {
                e.stopPropagation();
                var m = fileMap[key];
                if (!m) return;
                var inp = document.getElementById(m.input);
                if (inp) {
                    // DataTransfer দিয়ে empty করো
                    var dt = new DataTransfer();
                    inp.files = dt.files;  // empty FileList
                    inp.value = '';         // fallback পুরনো browser এর জন্য
                }
                document.getElementById(m.name).textContent = '';
                document.getElementById(m.empty).style.display = '';
                document.getElementById(m.done).style.display  = 'none';
                document.getElementById(m.zone).classList.remove('done');
            };

            /* ═══════════════════════════════
               Clear errors on input
            ═══════════════════════════════ */
            document.querySelectorAll('#sn-form .sn-fi').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    var msg = document.querySelector('.field-msg[data-field="' + inp.name + '"]');
                    if (msg) msg.classList.remove('show');
                    inp.classList.remove('is-invalid');
                    document.getElementById('sn-alert').classList.remove('show');
                });
            });

            /* ═══════════════════════════════
               Form submit (AJAX)
            ═══════════════════════════════ */
            var form       = document.getElementById('sn-form');
            var submitBtn  = document.getElementById('sn-submit');
            var submitTxt  = document.getElementById('submit-txt');
            var submitIcon = document.getElementById('submit-icon');
            var submitSpin = document.getElementById('submit-spin');
            var alertBox   = document.getElementById('sn-alert');
            var alertMsg   = document.getElementById('sn-alert-msg');
            var busy       = false;

            function setLoading(on) {
                busy = on;
                submitBtn.disabled = on;
                submitTxt.textContent = on ? '{{ __("Submitting...") }}' : 'Submit';
                submitIcon.style.display = on ? 'none' : '';
                submitSpin.style.display = on ? '' : 'none';
            }

            function showAlert(msg) {
                alertMsg.textContent = msg;
                alertBox.classList.add('show');
            }

            function markError(field, msg) {
                var msgEl = document.querySelector('.field-msg[data-field="' + field + '"]');
                var inpEl = document.querySelector('[name="' + field + '"]');
                if (msgEl) { msgEl.querySelector('span').textContent = msg; msgEl.classList.add('show'); }
                if (inpEl) inpEl.classList.add('is-invalid');
            }

            function clearErrors() {
                document.querySelectorAll('.field-msg').forEach(function (el) { el.classList.remove('show'); });
                document.querySelectorAll('.sn-fi').forEach(function (el) { el.classList.remove('is-invalid'); });
                alertBox.classList.remove('show');
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (busy) return;
                    setLoading(true);
                    clearErrors();

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: new FormData(form)
                    })
                        .then(function (r) {
                            return r.json().then(function (d) { return { ok: r.ok, status: r.status, d: d }; });
                        })
                        .then(function (res) {
                            if (res.ok) {
                                submitTxt.textContent = '{{ __("Redirecting...") }}';
                                window.location.href = res.d.redirect || '/';
                                return;
                            }
                            if (res.status === 419) { window.location.reload(); return; }

                            setLoading(false);

                            var errs = res.d.errors || {};
                            var step1Fields = ['first_name', 'last_name', 'company_name', 'email', 'password', 'address', 'phone'];
                            var step2Fields = ['nid_document', 'trade_license'];

                            var hasStep1 = Object.keys(errs).some(function (f) { return step1Fields.indexOf(f) !== -1; });
                            var hasStep2 = Object.keys(errs).some(function (f) { return step2Fields.indexOf(f) !== -1; });

                            if (hasStep1) goStep(1);
                            else if (hasStep2) goStep(2);

                            Object.keys(errs).forEach(function (f) {
                                markError(f, Array.isArray(errs[f]) ? errs[f][0] : errs[f]);
                            });
                            showAlert(res.d.message || '{{ __("Please fix the errors above.") }}');
                        })
                        .catch(function () {
                            setLoading(false);
                            showAlert('{{ __("Network error. Please try again.") }}');
                        });
                });
            }

        })();
    </script>

@endsection
