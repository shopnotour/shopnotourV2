{{-- Login & Register Modal --}}
{{-- File: resources/views/Layout/parts/login-register-modal.blade.php --}}

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ── Reset everything inside modals ── */
    #sn-login-modal *,
    #sn-register-modal * {
        box-sizing: border-box;
        font-family: 'Outfit', sans-serif;
    }

    /* ── z-index (above sticky header) ── */
    #sn-login-modal,
    #sn-register-modal { z-index: 999999 !important; }
    .modal-backdrop    { z-index: 999998 !important; }

    /* ── Dialog ── */
    #sn-login-modal .modal-dialog,
    #sn-register-modal .modal-dialog {
        max-width: 820px !important;
        width: 95vw !important;
        margin: auto !important;
        display: flex !important;
        align-items: center !important;
        min-height: calc(100% - 2rem) !important;
    }

    /* ── Content ── */
    #sn-login-modal .modal-content,
    #sn-register-modal .modal-content {
        border: none !important;
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 40px 100px rgba(0,0,0,0.25) !important;
        background: transparent !important;
        padding: 0 !important;
        width: 100% !important;
    }
    #sn-login-modal .modal-header,
    #sn-login-modal .modal-body,
    #sn-register-modal .modal-header,
    #sn-register-modal .modal-body {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        display: block !important;
    }

    /* ── Slide-in animation ── */
    #sn-login-modal.fade .modal-dialog,
    #sn-register-modal.fade .modal-dialog {
        transform: translateY(-16px) scale(0.97);
        transition: transform 0.28s cubic-bezier(0.34,1.28,0.64,1), opacity 0.2s ease;
    }
    #sn-login-modal.show .modal-dialog,
    #sn-register-modal.show .modal-dialog { transform: none; }

    /* ── Wrap ── */
    .sn-wrap { display: flex; width: 100%; }

    /* ── Left panel ── */
    .sn-left {
        width: 38%; flex-shrink: 0;
        padding: 40px 32px;
        display: flex; flex-direction: column; justify-content: space-between;
        color: #fff;
    }
    .sn-left-a { background: linear-gradient(150deg,#1d4ed8 0%,#2563eb 50%,#3b82f6 100%); }
    .sn-left-b { background: linear-gradient(150deg,#1e3a8a 0%,#1d4ed8 50%,#2563eb 100%); }

    .sn-l-icon {
        width:44px; height:44px; border-radius:14px;
        background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
        display:flex; align-items:center; justify-content:center; margin-bottom:20px;
    }
    .sn-l-icon svg { width:20px; height:20px; stroke:#fff; fill:none; stroke-width:2; }
    .sn-left h2 { font-size:22px; font-weight:800; margin:0 0 10px; line-height:1.3; }
    .sn-left p  { font-size:13px; color:rgba(255,255,255,.8); line-height:1.6; margin:0 0 22px; }
    .sn-l-feats { display:flex; flex-direction:column; gap:10px; }
    .sn-l-feat  { display:flex; align-items:center; gap:10px; font-size:13px; color:rgba(255,255,255,.85); }
    .sn-l-dot {
        width:20px; height:20px; border-radius:50%; flex-shrink:0;
        background:rgba(255,255,255,.2);
        display:flex; align-items:center; justify-content:center;
    }
    .sn-l-dot svg { width:10px; height:10px; stroke:#fff; fill:none; stroke-width:3; }
    .sn-l-footer { font-size:11px; color:rgba(255,255,255,.45); margin-top:28px; }

    /* ── Right panel ── */
    .sn-right {
        flex:1; background:#fff;
        border-radius:0 20px 20px 0;
        padding:36px 36px 28px;
        position:relative;
        overflow-y:auto;
        max-height:92vh;
    }

    /* ── Close btn ── */
    .sn-x {
        position:absolute; top:14px; right:14px;
        width:32px; height:32px; border-radius:50%;
        background:none; border:none; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        color:#9ca3af; transition:background .15s,color .15s;
    }
    .sn-x:hover { background:#f3f4f6; color:#374151; }
    .sn-x svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2.5; }

    /* ── Headings ── */
    .sn-h1 { font-size:28px; font-weight:800; color:#111827; margin:0 0 5px; line-height:1.2; }
    .sn-h2 { font-size:14px; color:#6b7280; margin:0 0 22px; }
    .sn-h2 a { color:#2563eb; font-weight:600; text-decoration:none; }
    .sn-h2 a:hover { text-decoration:underline; }

    /* ── Alert ── */
    .sn-alert {
        display:none; align-items:flex-start; gap:8px;
        background:#fef2f2; border:1px solid #fecaca;
        border-radius:11px; padding:10px 14px;
        font-size:13px; color:#b91c1c; margin-bottom:16px;
    }
    .sn-alert.on { display:flex; }
    .sn-alert svg { width:15px; height:15px; flex-shrink:0; margin-top:1px; stroke:currentColor; fill:none; stroke-width:2; }

    /* ── Form ── */
    .sn-form { display:flex; flex-direction:column; gap:15px; }
    .sn-row  { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .sn-field { display:flex; flex-direction:column; gap:5px; }

    .sn-lbl {
        font-size:11px; font-weight:700; color:#6b7280;
        text-transform:uppercase; letter-spacing:.07em;
        display:flex; align-items:center; gap:3px;
    }
    .sn-lbl .req { color:#ef4444; }

    /* Label + right-link row */
    .sn-lbl-row { display:flex; justify-content:space-between; align-items:center; }
    .sn-lbl-row a { font-size:12px; color:#2563eb; font-weight:600; text-decoration:none; }
    .sn-lbl-row a:hover { text-decoration:underline; }

    /* Input wrapper */
    .sn-iw { position:relative; }
    .sn-iw .ico {
        position:absolute; left:13px; top:50%; transform:translateY(-50%);
        width:16px; height:16px; color:#9ca3af; pointer-events:none;
        stroke:currentColor; fill:none; stroke-width:1.8;
    }

    /* Base input */
    .sn-inp {
        width:100%; height:48px;
        background:#f9fafb; border:1.5px solid #e5e7eb; border-radius:12px;
        font-size:14px; font-family:'Outfit',sans-serif; color:#111827;
        outline:none; transition:border-color .15s, background .15s, box-shadow .15s;
    }
    .sn-inp::placeholder { color:#9ca3af; }
    .sn-inp:focus { background:#fff; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .sn-inp.has-ico { padding:0 44px 0 42px; }
    .sn-inp.no-ico  { padding:0 14px; }
    .sn-inp.err     { border-color:#ef4444; background:#fef2f2; }

    /* Eye toggle */
    .sn-eye {
        position:absolute; right:12px; top:50%; transform:translateY(-50%);
        background:none; border:none; cursor:pointer; padding:4px;
        color:#9ca3af; display:flex; align-items:center; transition:color .15s;
    }
    .sn-eye:hover { color:#374151; }
    .sn-eye svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:1.8; }

    /* Error message */
    .sn-em {
        font-size:11.5px; color:#dc2626;
        display:none; align-items:center; gap:4px;
    }
    .sn-em.on { display:flex; }
    .sn-em svg { width:11px; height:11px; flex-shrink:0; }

    /* Strength bars */
    .sn-str { display:flex; gap:4px; }
    .sn-sb  { flex:1; height:3px; border-radius:99px; background:#e5e7eb; transition:background .3s; }
    .s1 { background:#ef4444; } .s2 { background:#f97316; }
    .s3 { background:#eab308; } .s4 { background:#22c55e; }

    /* Checkbox rows */
    .sn-chk { display:flex; align-items:center; gap:9px; }
    .sn-chk input[type=checkbox] { width:16px; height:16px; flex-shrink:0; accent-color:#2563eb; cursor:pointer; margin:0; }
    .sn-chk label { font-size:14px; color:#4b5563; cursor:pointer; margin:0; line-height:1; }

    .sn-terms { display:flex; align-items:flex-start; gap:9px; }
    .sn-terms input[type=checkbox] { width:16px; height:16px; flex-shrink:0; accent-color:#2563eb; cursor:pointer; margin-top:2px; }
    .sn-terms label { font-size:13.5px; color:#4b5563; cursor:pointer; line-height:1.55; }
    .sn-terms a { color:#2563eb; font-weight:600; text-decoration:none; }

    /* Submit button */
    .sn-btn {
        width:100%; height:52px;
        display:flex; align-items:center; justify-content:center; gap:8px;
        background:#2563eb; color:#fff;
        font-size:15px; font-weight:700; font-family:'Outfit',sans-serif;
        border:none; border-radius:13px; cursor:pointer;
        transition:background .15s, box-shadow .15s;
        box-shadow:0 2px 8px rgba(37,99,235,.28);
    }
    .sn-btn:hover   { background:#1d4ed8; box-shadow:0 4px 16px rgba(37,99,235,.38); }
    .sn-btn:active  { background:#1e40af; }
    .sn-btn:disabled{ opacity:.6; cursor:not-allowed; pointer-events:none; }
    .sn-btn svg { width:17px; height:17px; stroke:#fff; fill:none; stroke-width:2.5; }

    /* Spinner */
    @keyframes snSpin { to { transform:rotate(360deg); } }
    .sn-spin { animation:snSpin .7s linear infinite; }

    /* Divider */
    .sn-div {
        display:flex; align-items:center; gap:12px;
        font-size:12px; color:#9ca3af; margin:6px 0;
    }
    .sn-div::before,.sn-div::after { content:''; flex:1; height:1px; background:#e5e7eb; }

    /* Social */
    .sn-soc { display:flex; gap:8px; }
    .sn-soc a {
        flex:1; height:40px;
        display:flex; align-items:center; justify-content:center; gap:6px;
        border:1.5px solid #e5e7eb; border-radius:11px;
        background:#fff; font-size:13px; font-weight:500;
        color:#374151; text-decoration:none;
        transition:background .15s;
    }
    .sn-soc a:hover { background:#f3f4f6; }

    /* Footer note */
    .sn-note { text-align:center; font-size:11.5px; color:#9ca3af; margin-top:14px; line-height:1.5; }

    /* Match hint */
    .sn-match { font-size:11.5px; display:none; }
    .sn-match.ok { display:flex; color:#16a34a; }
    .sn-match.no { display:flex; color:#dc2626; }

    /* ── MOBILE ── */
    @media (max-width:640px){
        #sn-login-modal .modal-dialog,
        #sn-register-modal .modal-dialog {
            max-width:100% !important; width:100% !important;
            margin:0 !important; min-height:100% !important;
            align-items:flex-end !important;
        }
        #sn-login-modal .modal-content,
        #sn-register-modal .modal-content { border-radius:20px 20px 0 0 !important; }
        .sn-left  { display:none !important; }
        .sn-right { border-radius:20px 20px 0 0 !important; padding:26px 18px 22px !important; }
        .sn-row   { grid-template-columns:1fr !important; }
        .sn-h1    { font-size:23px !important; }
    }
</style>

{{-- ══════════════ LOGIN MODAL ══════════════ --}}
<div class="modal fade" id="sn-login-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-body">
                <div class="sn-wrap">

                    <div class="sn-left sn-left-a">
                        <div>
                            <div class="sn-l-icon">
                                <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                            </div>
                            <h2>{{ __('Welcome back!') }}</h2>
                            <p>{{ __('Sign in to manage your bookings, trips, and travel preferences.') }}</p>
                            <div class="sn-l-feats">
                                @foreach([__('Instant booking confirmation'),__('Manage all your trips'),__('Exclusive member offers')] as $f)
                                    <div class="sn-l-feat">
                                        <div class="sn-l-dot"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
                                        {{ $f }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="sn-l-footer">© {{ date('Y') }} Shopno Tours & Travels</div>
                    </div>

                    <div class="sn-right">
                        <button type="button" class="sn-x" data-bs-dismiss="modal">
                            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>

                        <div class="sn-h1">{{ __('Sign In') }}</div>
                        @if(is_enable_registration())
{{--                            <div class="sn-h2">--}}
{{--                                {{ __("Don't have an account?") }}--}}
{{--                                <a href="#" onclick="snSwitch('register');return false;">{{ __('Sign up free') }}</a>--}}
{{--                            </div>--}}

                            <div class="sn-h2">
                                {{ __("Don't have an account?") }}
                                <a href="{{ url('/register') }}">{{ __('Sign up free') }}</a>
                            </div>
                        @endif

                        <div class="sn-alert" id="sn-la">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span id="sn-la-t"></span>
                        </div>

                        <form id="sn-lf" method="POST" action="{{ url('/login') }}" class="sn-form" novalidate>
                            <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
                            @csrf

                            {{-- Email --}}
                            <div class="sn-field">
                                <div class="sn-lbl">{{ __('Email') }}</div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <input type="email" name="email" autocomplete="email" placeholder="you@example.com" class="sn-inp has-ico sn-fi">
                                </div>
                                <div class="sn-em" data-field="email">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="sn-field">
                                <div class="sn-lbl-row">
                                    <div class="sn-lbl">{{ __('Password') }}</div>
                                    <a href="{{ url('/forgot-password') }}">{{ __('Forgot?') }}</a>
                                </div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    <input type="password" name="password" id="sn-lpw" autocomplete="current-password" placeholder="••••••••" class="sn-inp has-ico sn-fi">
                                    <button type="button" class="sn-eye" onclick="snEye('sn-lpw',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div class="sn-em" data-field="password">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            {{-- Remember --}}
                            <div class="sn-chk">
                                <input type="checkbox" name="remember" id="sn-rem">
                                <label for="sn-rem">{{ __('Remember me') }}</label>
                            </div>

                            @if(setting_item("user_enable_login_recaptcha"))
                                <div>{{ recaptcha_field($captcha_action ?? 'login') }}</div>
                            @endif

                            <button type="submit" class="sn-btn" id="sn-lb">
                                <svg id="sn-li" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                                <svg id="sn-ls" class="sn-spin" style="display:none" viewBox="0 0 24 24">
                                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
                                    <path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <span id="sn-lt">{{ __('Sign In') }}</span>
                            </button>
                        </form>

                        @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
                            <div class="sn-div" style="margin-top:16px;">{{ __('or sign in with') }}</div>
                            <div class="sn-soc">
                                @if(setting_item('facebook_enable'))
                                    <a href="{{ url('/social-login/facebook') }}"><i class="fa fa-facebook" style="color:#1877f2"></i> Facebook</a>
                                @endif
                                @if(setting_item('google_enable'))
                                    <a href="{{ url('social-login/google') }}"><i class="fa fa-google" style="color:#ea4335"></i> Google</a>
                                @endif
                                @if(setting_item('twitter_enable'))
                                    <a href="{{ url('social-login/twitter') }}"><i class="fa fa-twitter" style="color:#1da1f2"></i> Twitter</a>
                                @endif
                            </div>
                        @endif

                        <div class="sn-note">{{ __('By signing in, you agree to our Terms & Privacy Policy.') }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ REGISTER MODAL ══════════════ --}}
<div class="modal fade" id="sn-register-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-body">
                <div class="sn-wrap">

                    <div class="sn-left sn-left-b">
                        <div>
                            <div class="sn-l-icon">
                                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <h2>{{ __('Join us today!') }}</h2>
                            <p>{{ __('Create your free account and start exploring amazing travel experiences.') }}</p>
                            <div class="sn-l-feats">
                                @foreach([__('Free to join'),__('Best price guarantee'),__('24/7 customer support')] as $f)
                                    <div class="sn-l-feat">
                                        <div class="sn-l-dot"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
                                        {{ $f }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="sn-l-footer">© {{ date('Y') }} Shopno Tours & Travels</div>
                    </div>

                    <div class="sn-right">
                        <button type="button" class="sn-x" data-bs-dismiss="modal">
                            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>

                        <div class="sn-h1">{{ __('Create Account') }}</div>
                        <div class="sn-h2">
                            {{ __('Already have an account?') }}
                            <a href="#" onclick="snSwitch('login');return false;">{{ __('Sign in') }}</a>
                        </div>

                        <div class="sn-alert" id="sn-ra">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span id="sn-ra-t"></span>
                        </div>

                        <form id="sn-rf" method="POST" action="{{ url('/register') }}" class="sn-form" novalidate>
                            @csrf

                            <div class="sn-row">
                                <div class="sn-field">
                                    <div class="sn-lbl">{{ __('First Name') }} <span class="req">*</span></div>
                                    <input type="text" name="first_name" autocomplete="given-name" placeholder="{{ __('First') }}" class="sn-inp no-ico sn-fi">
                                    <div class="sn-em" data-field="first_name">
                                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="sn-field">
                                    <div class="sn-lbl">{{ __('Last Name') }}</div>
                                    <input type="text" name="last_name" autocomplete="family-name" placeholder="{{ __('Last') }}" class="sn-inp no-ico sn-fi">
                                </div>
                            </div>

                            <div class="sn-field">
                                <div class="sn-lbl">{{ __('Phone') }}</div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.11 1.18 2 2 0 012.11 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6z"/></svg>
                                    <input type="text" name="phone" autocomplete="tel" placeholder="+880 1X-XXXXXXXX" class="sn-inp has-ico sn-fi">
                                </div>
                            </div>

                            <div class="sn-field">
                                <div class="sn-lbl">{{ __('Email') }} <span class="req">*</span></div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <input type="email" name="email" autocomplete="email" placeholder="you@example.com" class="sn-inp has-ico sn-fi">
                                </div>
                                <div class="sn-em" data-field="email">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <div class="sn-field">
                                <div class="sn-lbl">{{ __('Password') }} <span class="req">*</span></div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    <input type="password" name="password" id="sn-rpw" autocomplete="new-password" placeholder="••••••••" class="sn-inp has-ico sn-fi">
                                    <button type="button" class="sn-eye" onclick="snEye('sn-rpw',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div class="sn-str">
                                    <div class="sn-sb" id="sn-s1"></div><div class="sn-sb" id="sn-s2"></div>
                                    <div class="sn-sb" id="sn-s3"></div><div class="sn-sb" id="sn-s4"></div>
                                </div>
                                <div class="sn-em" data-field="password">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            <div class="sn-field">
                                <div class="sn-lbl">{{ __('Confirm Password') }} <span class="req">*</span></div>
                                <div class="sn-iw">
                                    <svg class="ico" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    <input type="password" name="password_confirmation" id="sn-rcf" autocomplete="new-password" placeholder="••••••••" class="sn-inp has-ico sn-fi">
                                    <button type="button" class="sn-eye" onclick="snEye('sn-rcf',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div class="sn-match" id="sn-pm"></div>
                            </div>

                            <div class="sn-field">
                                <div class="sn-terms">
                                    <input type="checkbox" name="term" id="sn-trm" value="1">
                                    <label for="sn-trm">
                                        {{ __('I accept the') }}
                                        <a href="#">{{ __('Terms') }}</a>
                                        {{ __('and') }}
                                        <a href="#">{{ __('Privacy Policy') }}</a>
                                    </label>
                                </div>
                                <div class="sn-em" data-field="term">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    <span></span>
                                </div>
                            </div>

                            @if(setting_item("user_enable_register_recaptcha"))
                                <div>{{ recaptcha_field($captcha_action ?? 'register') }}</div>
                            @endif

                            <button type="submit" class="sn-btn" id="sn-rb">
                                <svg id="sn-ri" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                                <svg id="sn-rs" class="sn-spin" style="display:none" viewBox="0 0 24 24">
                                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
                                    <path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <span id="sn-rt">{{ __('Create Account') }}</span>
                            </button>
                        </form>

                        @if(setting_item('facebook_enable') or setting_item('google_enable') or setting_item('twitter_enable'))
                            <div class="sn-div" style="margin-top:16px;">{{ __('or sign up with') }}</div>
                            <div class="sn-soc">
                                @if(setting_item('facebook_enable'))
                                    <a href="{{ url('/social-login/facebook') }}"><i class="fa fa-facebook" style="color:#1877f2"></i> Facebook</a>
                                @endif
                                @if(setting_item('google_enable'))
                                    <a href="{{ url('social-login/google') }}"><i class="fa fa-google" style="color:#ea4335"></i> Google</a>
                                @endif
                                @if(setting_item('twitter_enable'))
                                    <a href="{{ url('social-login/twitter') }}"><i class="fa fa-twitter" style="color:#1da1f2"></i> Twitter</a>
                                @endif
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- end of file --}}

<script>
    document.addEventListener('DOMContentLoaded', function(){
        (function(){

            /* ── Switch modals ── */
            window.snSwitch = function(to){
                var h = to==='login' ? 'sn-register-modal' : 'sn-login-modal';
                var s = to==='login' ? 'sn-login-modal'    : 'sn-register-modal';
                if(typeof bootstrap==='undefined') return;
                var hi = bootstrap.Modal.getInstance(document.getElementById(h));
                if(hi) hi.hide();
                setTimeout(function(){ new bootstrap.Modal(document.getElementById(s)).show(); }, 280);
            };

            /* ── Backdrop cleanup ── */
            ['sn-login-modal','sn-register-modal'].forEach(function(id){
                var el = document.getElementById(id);
                if(!el) return;
                el.addEventListener('hidden.bs.modal', function(){
                    document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                });
            });

            /* ── Old #login / #register links ── */
            document.addEventListener('click', function(e){
                var a = e.target.closest('[data-bs-toggle="modal"],[data-toggle="modal"]');
                if(!a) return;
                var href = (a.getAttribute('href')||a.getAttribute('data-bs-target')||'').trim();
                if(href==='#login'||href==='#register'){
                    e.preventDefault();
                    var id = href==='#login' ? 'sn-login-modal' : 'sn-register-modal';
                    if(typeof bootstrap!=='undefined') new bootstrap.Modal(document.getElementById(id)).show();
                }
            });

            /* ── Eye toggle ── */
            window.snEye = function(id, btn){
                var inp = document.getElementById(id);
                if(!inp) return;
                var p = inp.type==='password';
                inp.type = p ? 'text' : 'password';
                btn.querySelector('svg').innerHTML = p
                    ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            };

            /* ── Strength & match ── */
            var rpw = document.getElementById('sn-rpw');
            var rcf = document.getElementById('sn-rcf');
            var sc  = ['','s1','s2','s3','s4'];

            function chkMatch(){
                var h = document.getElementById('sn-pm');
                if(!rpw||!rcf||!h) return;
                if(!rcf.value){ h.className='sn-match'; return; }
                var ok = rpw.value===rcf.value;
                h.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
                h.className = 'sn-match '+(ok?'ok':'no');
            }
            if(rpw){
                rpw.addEventListener('input', function(){
                    var v=this.value, s=0;
                    if(v.length>=8) s++; if(/[A-Z]/.test(v)) s++;
                    if(/[0-9]/.test(v)) s++; if(/[^A-Za-z0-9]/.test(v)) s++;
                    ['sn-s1','sn-s2','sn-s3','sn-s4'].forEach(function(id,i){
                        var el=document.getElementById(id);
                        if(el) el.className='sn-sb '+(v.length>0&&i<s?sc[s]:'');
                    });
                    chkMatch();
                });
            }
            if(rcf) rcf.addEventListener('input', chkMatch);

            /* ── AJAX form handler ── */
            function handle(c){
                var form=document.getElementById(c.f);
                var btn=document.getElementById(c.b);
                var btxt=document.getElementById(c.bt);
                var icon=document.getElementById(c.ic);
                var spin=document.getElementById(c.sp);
                var alrt=document.getElementById(c.al);
                var atxt=document.getElementById(c.at);
                if(!form||!btn) return;
                var busy=false;

                function load(on){
                    busy=on; btn.disabled=on;
                    btxt.textContent=on?c.loading:c.idle;
                    icon.style.display=on?'none':'';
                    spin.style.display=on?'':'none';
                }
                function alert_(msg){ atxt.textContent=msg; alrt.classList.add('on'); }
                function markErr(field,msg){
                    var em=form.querySelector('.sn-em[data-field="'+field+'"]');
                    var ip=form.querySelector('[name="'+field+'"]');
                    if(em){ em.querySelector('span').textContent=msg; em.classList.add('on'); }
                    if(ip) ip.classList.add('err');
                }
                function clearAll(){
                    form.querySelectorAll('.sn-em').forEach(function(e){ e.classList.remove('on'); });
                    form.querySelectorAll('.sn-fi').forEach(function(i){ i.classList.remove('err'); });
                    alrt.classList.remove('on');
                }
                form.querySelectorAll('.sn-fi').forEach(function(inp){
                    inp.addEventListener('input', function(){
                        var em=form.querySelector('.sn-em[data-field="'+inp.name+'"]');
                        if(em) em.classList.remove('on');
                        inp.classList.remove('err');
                        alrt.classList.remove('on');
                    });
                });
                form.addEventListener('submit', function(e){
                    e.preventDefault(); if(busy) return;
                    load(true); clearAll();
                    fetch(form.action,{
                        method:'POST',
                        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                        body:new FormData(form)
                    })
                        .then(function(r){ return r.json().then(function(d){ return {ok:r.ok,status:r.status,d:d}; }); })
                        .then(function(res){
                            if(res.ok){ btxt.textContent='{{ __("Redirecting...") }}'; window.location.href=res.d.redirect||'/'; return; }
                            if(res.status===419){ window.location.reload(); return; }
                            load(false);
                            var errs=res.d.errors||{};
                            Object.keys(errs).forEach(function(f){ markErr(f,Array.isArray(errs[f])?errs[f][0]:errs[f]); });
                            alert_(res.d.message||'{{ __("Please fix the errors above.") }}');
                        })
                        .catch(function(){ load(false); alert_('{{ __("Network error. Please try again.") }}'); });
                });
            }

            handle({f:'sn-lf',b:'sn-lb',bt:'sn-lt',ic:'sn-li',sp:'sn-ls',al:'sn-la',at:'sn-la-t',
                idle:'{{ __("Sign In") }}',loading:'{{ __("Signing in...") }}'});
            handle({f:'sn-rf',b:'sn-rb',bt:'sn-rt',ic:'sn-ri',sp:'sn-rs',al:'sn-ra',at:'sn-ra-t',
                idle:'{{ __("Create Account") }}',loading:'{{ __("Creating account...") }}'});

        })();
    }); // DOMContentLoaded
</script>
