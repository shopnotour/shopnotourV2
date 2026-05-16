@extends('layouts.app')

@section('content')
    <style>
        .reset-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;background:#f8f9fa}
        .reset-card{background:#fff;border:1px solid #e9ecef;border-radius:16px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06)}
        .reset-card-top{background:#eaf3fb;padding:2rem 2rem 1.5rem;text-align:center}
        .shield-icon{width:52px;height:52px;border-radius:50%;background:rgba(55,138,221,0.12);border:1.5px solid rgba(55,138,221,0.35);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem}
        .shield-icon svg{width:22px;height:22px;stroke:#185FA5;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
        .reset-card-top h2{font-size:20px;font-weight:600;color:#185FA5;margin-bottom:4px}
        .reset-card-top p{font-size:13px;color:#6c757d;line-height:1.5;margin:0}
        .reset-card-body{padding:1.75rem 2rem}
        .input-icon-wrap{position:relative}
        .input-icon-wrap .pre-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:#adb5bd;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;pointer-events:none}
        .input-icon-wrap .form-control{padding-left:38px;padding-right:40px;border-radius:10px;border:1px solid #dee2e6;font-size:14px;height:44px;transition:border-color 0.15s,box-shadow 0.15s}
        .input-icon-wrap .form-control:focus{border-color:#378ADD;box-shadow:0 0 0 3px rgba(55,138,221,0.15)}
        .toggle-pass{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;display:flex;align-items:center;color:#adb5bd}
        .toggle-pass svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
        .strength-bar{display:flex;gap:4px;margin-top:8px}
        .strength-bar span{flex:1;height:3px;border-radius:2px;background:#dee2e6;transition:background 0.2s}
        .strength-label{font-size:11px;margin-top:4px;color:#adb5bd}
        .btn-reset-submit{width:100%;padding:11px;font-size:14px;font-weight:600;border:none;border-radius:10px;background:#378ADD;color:#fff;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.15s;cursor:pointer;margin-top:4px}
        .btn-reset-submit:hover{background:#185FA5;color:#fff}
        .btn-reset-submit svg{width:15px;height:15px;fill:none;stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
        .back-link{text-align:center;margin-top:1.25rem;font-size:13px;color:#6c757d}
        .back-link a{color:#378ADD;font-weight:500;text-decoration:none}
        .back-link a:hover{text-decoration:underline}
        @media(max-width:480px){.reset-card-body{padding:1.5rem 1.25rem}.reset-card-top{padding:1.5rem 1.25rem 1.25rem}}
    </style>

    <div class="reset-page">
        <div class="reset-card">
            <div class="reset-card-top">
                <div class="shield-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h2>{{ __('নতুন পাসওয়ার্ড সেট করুন') }}</h2>
                <p>{{ __('নিচে আপনার নতুন পাসওয়ার্ড দিন।') }}</p>
            </div>

            <div class="reset-card-body">
                @include('Layout::admin.message')

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ request()->route('token') }}">

                    {{-- Email --}}
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-size:13px;color:#495057;">{{ __('ইমেইল ঠিকানা') }}</label>
                        <div class="input-icon-wrap">
                            <svg class="pre-icon" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email', request()->email) }}" placeholder="you@example.com" required autofocus autocomplete="email" style="padding-right:12px">
                            @if($errors->has('email'))
                                <div class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></div>
                            @endif
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-size:13px;color:#495057;">{{ __('নতুন পাসওয়ার্ড') }}</label>
                        <div class="input-icon-wrap">
                            <svg class="pre-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __('কমপক্ষে ৪ অক্ষর') }}" required autocomplete="new-password" oninput="checkStrength(this.value)">
                            <button type="button" class="toggle-pass" onclick="togglePass('password',this)">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            @if($errors->has('password'))
                                <div class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></div>
                            @endif
                        </div>
                        <div class="strength-bar">
                            <span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span>
                        </div>
                        <div class="strength-label" id="slabel"></div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-size:13px;color:#495057;">{{ __('পাসওয়ার্ড নিশ্চিত করুন') }}</label>
                        <div class="input-icon-wrap">
                            <svg class="pre-icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" placeholder="{{ __('আবার লিখুন') }}" required autocomplete="new-password">
                            <button type="button" class="toggle-pass" onclick="togglePass('password_confirmation',this)">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="match-err" style="display:none!important"></div>
                    </div>

                    <button type="submit" class="btn-reset-submit">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ __('পাসওয়ার্ড রিসেট করুন') }}
                    </button>
                </form>

                <p class="back-link">
                    <a href="{{ route('login') }}">← {{ __('লগইন পেজে ফিরে যান') }}</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePass(id, btn) {
            const inp = document.getElementById(id);
            const isText = inp.type === 'text';
            inp.type = isText ? 'password' : 'text';
            btn.querySelector('svg').innerHTML = isText
                ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
                : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
        }
        function checkStrength(val) {
            const bars = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
            const label = document.getElementById('slabel');
            bars.forEach(b => b.style.background = '#dee2e6');
            if (!val) { label.textContent = ''; return; }
            let score = 0;
            if (val.length >= 4) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const colors = ['#E24B4A','#EF9F27','#378ADD','#1D9E75'];
            const labels = ['দুর্বল','মোটামুটি','ভালো','শক্তিশালী'];
            for (let i = 0; i < score; i++) bars[i].style.background = colors[score-1];
            label.textContent = labels[score-1] || '';
            label.style.color = colors[score-1];
        }
    </script>
@endsection
