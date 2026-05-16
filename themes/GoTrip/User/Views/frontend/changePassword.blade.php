@extends('layouts.user')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Outfit', sans-serif; box-sizing: border-box; }
        @keyframes snFadeUp {
            from { opacity:0; transform:translateY(10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .sn-fadein { animation: snFadeUp .35s ease both; }
        .sn-field { position: relative; }
        .sn-field input {
            width: 100%;
            padding: 22px 44px 8px 14px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            color: #111827;
            outline: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
        }
        .sn-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
            background: #fff;
        }
        .sn-field input.err { border-color: #ef4444; background: #fef2f2; }
        .sn-field label {
            position: absolute;
            left: 14px; top: 8px;
            font-size: 10px; font-weight: 700;
            color: #6b7280;
            text-transform: uppercase; letter-spacing: .06em;
            pointer-events: none;
        }
        .sn-eye {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; padding: 4px; color: #9ca3af;
        }
        .sn-eye:hover { color: #2563eb; }
        /* strength bars */
        .sn-sb { flex:1; height:3px; border-radius:99px; background:#e5e7eb; transition:background .3s; }
        .s1 { background:#ef4444; } .s2 { background:#f97316; }
        .s3 { background:#eab308; } .s4 { background:#22c55e; }
    </style>

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Change Password') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Keep your account secure with a strong password') }}</p>
    </div>

    @include('admin.message')

    <div class="max-w-lg sn-fadein">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">

            {{-- Icon --}}
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-6"
                 style="background:#eff6ff; border:1.5px solid #bfdbfe;">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
            </div>

            <form action="{{ route('user.change_password.update') }}" method="post" class="flex flex-col gap-5">
                @csrf

                {{-- Current Password --}}
                <div class="sn-field">
                    <input type="password" name="current-password" id="sn-cp" required placeholder="••••••••">
                    <label>{{ __('Current Password') }}</label>
                    <button type="button" class="sn-eye" onclick="snEye('sn-cp', this)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- New Password --}}
                <div class="sn-field">
                    <input type="password" name="new-password" id="sn-np" required minlength="8" placeholder="••••••••"
                           oninput="snStrength(this.value)">
                    <label>{{ __('New Password') }}</label>
                    <button type="button" class="sn-eye" onclick="snEye('sn-np', this)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                {{-- Strength bars --}}
                <div style="display:flex; gap:4px; margin-top:-12px;">
                    <div class="sn-sb" id="sn-s1"></div>
                    <div class="sn-sb" id="sn-s2"></div>
                    <div class="sn-sb" id="sn-s3"></div>
                    <div class="sn-sb" id="sn-s4"></div>
                </div>

                {{-- Requirements --}}
                <div class="bg-gray-50 rounded-xl px-4 py-3" style="margin-top:-8px;">
                    <div class="text-xs font-600 text-gray-500 mb-2">{{ __('Password must contain:') }}</div>
                    <div class="grid grid-cols-2 gap-1">
                        @foreach([
                            ['id'=>'r-len',  'text'=> __('At least 8 characters')],
                            ['id'=>'r-up',   'text'=> __('One uppercase letter')],
                            ['id'=>'r-num',  'text'=> __('One number')],
                            ['id'=>'r-sym',  'text'=> __('One symbol')],
                        ] as $r)
                            <div class="flex items-center gap-1.5" id="{{ $r['id'] }}">
                                <svg class="w-3 h-3 text-gray-300 flex-shrink-0 req-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
                                <span class="text-xs text-gray-400 req-text">{{ $r['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="sn-field">
                    <input type="password" name="new-password_confirmation" id="sn-cf" required minlength="8"
                           placeholder="••••••••" oninput="snMatch()">
                    <label>{{ __('Confirm New Password') }}</label>
                    <button type="button" class="sn-eye" onclick="snEye('sn-cf', this)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div id="sn-match-msg" style="display:none; font-size:12px; margin-top:-12px;"></div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 text-white font-700 rounded-xl transition-all shadow-md hover:shadow-lg"
                        style="height:52px; font-size:15px; background:#2563eb; border:none; cursor:pointer;">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                    {{ __('Change Password') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        function snEye(id, btn) {
            var inp = document.getElementById(id);
            if (!inp) return;
            var p = inp.type === 'password';
            inp.type = p ? 'text' : 'password';
            btn.querySelector('svg').innerHTML = p
                ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }

        function snStrength(v) {
            var s = 0;
            if (v.length >= 8) s++; if (/[A-Z]/.test(v)) s++;
            if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
            var cls = ['','s1','s2','s3','s4'];
            ['sn-s1','sn-s2','sn-s3','sn-s4'].forEach(function(id, i) {
                var el = document.getElementById(id);
                if (el) el.className = 'sn-sb ' + (v.length > 0 && i < s ? cls[s] : '');
            });

            // Requirements
            function req(id, ok) {
                var el = document.getElementById(id);
                if (!el) return;
                el.querySelector('.req-icon').setAttribute('stroke', ok ? '#16a34a' : '#d1d5db');
                el.querySelector('.req-icon').innerHTML = ok
                    ? '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'
                    : '<circle cx="12" cy="12" r="10"/>';
                el.querySelector('.req-text').style.color = ok ? '#16a34a' : '#9ca3af';
            }
            req('r-len', v.length >= 8);
            req('r-up',  /[A-Z]/.test(v));
            req('r-num', /[0-9]/.test(v));
            req('r-sym', /[^A-Za-z0-9]/.test(v));

            snMatch();
        }

        function snMatch() {
            var np  = document.getElementById('sn-np');
            var cf  = document.getElementById('sn-cf');
            var msg = document.getElementById('sn-match-msg');
            if (!cf.value) { msg.style.display = 'none'; return; }
            var ok = np.value === cf.value;
            msg.textContent = ok ? '✓ {{ __("Passwords match") }}' : '✗ {{ __("Passwords do not match") }}';
            msg.style.color   = ok ? '#16a34a' : '#dc2626';
            msg.style.display = 'block';
        }
    </script>
@endsection
