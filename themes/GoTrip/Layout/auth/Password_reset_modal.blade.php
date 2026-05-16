{{----}}
{{--    Design B — Split Panel Password Reset Modal--}}
{{--    File: resources/views/Layout/parts/password-reset-modal.blade.php--}}

{{--    Routes used:--}}
{{--      POST shopno.password.email   →  ForgotPasswordController@sendResetLinkEmail  (trait)--}}
{{--      POST shopno.password.update  →  ResetPasswordController@reset                (trait)--}}
{{----}}

{{--<style>--}}
{{--    #sp-reset-modal .modal-dialog { max-width:720px !important; margin:auto !important; display:flex; align-items:center; min-height:calc(100% - 2rem); }--}}
{{--    #sp-reset-modal .modal-content { border:none !important; border-radius:18px !important; overflow:hidden !important; box-shadow:0 32px 80px rgba(0,0,0,0.25) !important; width:100%; }--}}
{{--    #sp-reset-modal .modal-header, #sp-reset-modal .modal-body { padding:0 !important; border:none !important; background:transparent !important; }--}}
{{--    #sp-reset-modal.fade .modal-dialog { transform:translateY(-12px) scale(0.98); transition:transform 0.25s cubic-bezier(0.34,1.28,0.64,1), opacity 0.2s ease; }--}}
{{--    #sp-reset-modal.show .modal-dialog { transform:translateY(0) scale(1); }--}}

{{--    .sp-reset-step { display:none; }--}}
{{--    .sp-reset-step.active { display:block; }--}}

{{--    .sp-back-link {--}}
{{--        display:inline-flex; align-items:center; gap:6px;--}}
{{--        font-size:12.5px; color:#888; text-decoration:none;--}}
{{--        margin-bottom:20px; transition:color 0.15s;--}}
{{--        background:none; border:none; cursor:pointer; padding:0;--}}
{{--        font-family:'Jost',sans-serif;--}}
{{--    }--}}
{{--    .sp-back-link:hover { color:#0f6e56; }--}}
{{--    .sp-back-link svg { width:14px; height:14px; }--}}

{{--    .sp-success-box { text-align:center; padding:16px 0 8px; }--}}
{{--    .sp-success-icon { width:56px; height:56px; background:#e8f8f3; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }--}}
{{--    .sp-success-icon svg { width:26px; height:26px; color:#0f6e56; }--}}
{{--    .sp-success-title { font-size:18px; font-weight:700; color:#0f1a2e; margin-bottom:8px; font-family:'Jost',sans-serif; }--}}
{{--    .sp-success-sub { font-size:13px; color:#888; line-height:1.6; margin-bottom:24px; }--}}

{{--    .sp-hint-box { background:#f0faf6; border:1px solid #c3e8da; border-radius:10px; padding:11px 14px; display:flex; align-items:flex-start; gap:10px; margin-bottom:18px; }--}}
{{--    .sp-hint-box svg { width:15px; height:15px; color:#0f6e56; flex-shrink:0; margin-top:1px; }--}}
{{--    .sp-hint-box span { font-size:12px; color:#2d7a5e; line-height:1.55; }--}}

{{--    .sp-match-hint { font-size:11px; margin-top:5px; min-height:14px; }--}}
{{--    .sp-match-ok { color:#0f6e56; }--}}
{{--    .sp-match-no { color:#e74c3c; }--}}

{{--    .sp-left-reset-icon { width:52px; height:52px; background:rgba(255,255,255,0.15); border-radius:16px; display:flex; align-items:center; justify-content:center; margin-bottom:18px; }--}}
{{--    .sp-left-reset-icon svg { width:26px; height:26px; }--}}

{{--    @media (max-width:600px) {--}}
{{--        #sp-reset-modal .modal-dialog { max-width:100% !important; margin:0 !important; min-height:100%; align-items:flex-end; }--}}
{{--        #sp-reset-modal .modal-content { border-radius:18px 18px 0 0 !important; }--}}
{{--        #sp-reset-modal .sp-left { display:none !important; }--}}
{{--        #sp-reset-modal .sp-right { padding:28px 22px; }--}}
{{--    }--}}
{{--</style>--}}

{{--<div class="modal fade sp-modal" id="sp-reset-modal" tabindex="-1" role="dialog" aria-label="{{ __('পাসওয়ার্ড রিসেট') }}" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-dialog-centered" role="document">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="modal-header" style="display:none;"></div>--}}
{{--            <div class="modal-body">--}}
{{--                <div class="sp-wrap">--}}

{{--                    --}}{{-- LEFT --}}
{{--                    <div class="sp-left">--}}
{{--                        <div>--}}
{{--                            <div class="sp-left-reset-icon">--}}
{{--                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>--}}
{{--                            </div>--}}
{{--                            <div class="sp-left-title">পাসওয়ার্ড রিসেট</div>--}}
{{--                            <div class="sp-left-sub">চিন্তা নেই — পাসওয়ার্ড ভুলে যাওয়া স্বাভাবিক। মাত্র কয়েক ধাপে নতুন পাসওয়ার্ড সেট করুন।</div>--}}
{{--                            <div class="sp-feature-list" style="margin-top:28px;">--}}
{{--                                @foreach(['ইমেইলে রিসেট লিংক পাঠানো হবে','লিংক ৬০ মিনিট সক্রিয় থাকবে','সম্পূর্ণ নিরাপদ প্রক্রিয়া'] as $f)--}}
{{--                                    <div class="sp-feature-item">--}}
{{--                                        <div class="sp-feature-dot"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></div>--}}
{{--                                        <span class="sp-feature-text">{{ $f }}</span>--}}
{{--                                    </div>--}}
{{--                                @endforeach--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="sp-left-footer">© {{ date('Y') }} Shopno Tours & Travels</div>--}}
{{--                    </div>--}}

{{--                    --}}{{-- RIGHT --}}
{{--                    <div class="sp-right" style="min-height:420px;">--}}
{{--                        <button class="sp-close-btn" data-bs-dismiss="modal" aria-label="Close">--}}
{{--                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>--}}
{{--                        </button>--}}

{{--                        --}}{{-- STEP 1: Email --}}
{{--                        <div class="sp-reset-step active" id="sp-step-email">--}}
{{--                            <button type="button" class="sp-back-link" onclick="spBackToLogin()">--}}
{{--                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>--}}
{{--                                {{ __('লগইনে ফিরুন') }}--}}
{{--                            </button>--}}
{{--                            <div class="sp-right-heading">{{ __('পাসওয়ার্ড ভুলে গেছেন?') }}</div>--}}
{{--                            <div class="sp-right-sub" style="margin-bottom:20px;">{{ __('আপনার ইমেইল দিন — আমরা রিসেট লিংক পাঠাবো।') }}</div>--}}
{{--                            <div class="sp-hint-box">--}}
{{--                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>--}}
{{--                                <span>{{ __('অ্যাকাউন্ট খোলার সময় যে ইমেইল দিয়েছিলেন সেটি দিন।') }}</span>--}}
{{--                            </div>--}}

{{--                            --}}{{-- action → new named route --}}
{{--                            <form id="sp-forgot-form" method="POST" action="{{ route('password.email') }}">--}}
{{--                                @csrf--}}
{{--                                <div class="sp-error error message-error invalid-feedback" id="sp-forgot-error" style="margin-bottom:12px;"></div>--}}
{{--                                <div class="sp-field-group">--}}
{{--                                    <label class="sp-field-label">{{ __('ইমেইল অ্যাড্রেস') }}</label>--}}
{{--                                    <div class="sp-field-box">--}}
{{--                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>--}}
{{--                                        <input type="email" name="email" id="sp-forgot-email" autocomplete="email" placeholder="{{ __('example@email.com') }}" required>--}}
{{--                                    </div>--}}
{{--                                    <span class="sp-field-error invalid-feedback error error-email"></span>--}}
{{--                                </div>--}}
{{--                                @if(setting_item("user_enable_login_recaptcha"))--}}
{{--                                    <div class="sp-recaptcha" style="margin-bottom:14px;">{{ recaptcha_field($captcha_action ?? 'password_reset') }}</div>--}}
{{--                                @endif--}}
{{--                                <button type="submit" class="sp-submit-btn" id="sp-forgot-btn">--}}
{{--                                    <span class="sp-btn-text">{{ __('রিসেট লিংক পাঠান') }}</span>--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="width:16px;height:16px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>--}}
{{--                                    <span class="sp-spinner" id="sp-forgot-spinner"></span>--}}
{{--                                </button>--}}
{{--                            </form>--}}
{{--                        </div>--}}

{{--                        --}}{{-- STEP 1 SUCCESS --}}
{{--                        <div class="sp-reset-step" id="sp-step-sent">--}}
{{--                            <div class="sp-success-box">--}}
{{--                                <div class="sp-success-icon">--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>--}}
{{--                                </div>--}}
{{--                                <div class="sp-success-title">{{ __('ইমেইল পাঠানো হয়েছে!') }}</div>--}}
{{--                                <div class="sp-success-sub">{{ __('আপনার ইমেইলে একটি রিসেট লিংক পাঠানো হয়েছে। ইনবক্স চেক করুন — না পেলে স্প্যাম ফোল্ডারও দেখুন।') }}</div>--}}
{{--                                <button type="button" class="sp-submit-btn" style="max-width:220px; margin:0 auto;" onclick="spShowStep('sp-step-email')">--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="width:15px;height:15px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>--}}
{{--                                    <span>{{ __('আবার পাঠান') }}</span>--}}
{{--                                </button>--}}
{{--                                <div style="margin-top:16px;">--}}
{{--                                    <button type="button" class="sp-back-link" style="margin:0 auto;" onclick="spBackToLogin()">--}}
{{--                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>--}}
{{--                                        {{ __('লগইন পেজে যান') }}--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- STEP 2: New password --}}
{{--                        <div class="sp-reset-step" id="sp-step-newpass">--}}
{{--                            <div class="sp-right-heading">{{ __('নতুন পাসওয়ার্ড সেট করুন') }}</div>--}}
{{--                            <div class="sp-right-sub" style="margin-bottom:22px;">{{ __('শক্তিশালী পাসওয়ার্ড বেছে নিন।') }}</div>--}}

{{--                            --}}{{-- action → new named route --}}
{{--                            <form id="sp-reset-form" method="POST" action="{{ route('password.update') }}">--}}
{{--                                @csrf--}}
{{--                                <input type="hidden" name="token" value="{{ request()->route('token') ?? '' }}" id="sp-reset-token">--}}
{{--                                <input type="hidden" name="email" value="{{ request()->query('email') ?? '' }}" id="sp-reset-email-hidden">--}}

{{--                                <div class="sp-error error message-error invalid-feedback" id="sp-reset-error" style="margin-bottom:12px;"></div>--}}

{{--                                <div class="sp-field-group">--}}
{{--                                    <label class="sp-field-label">{{ __('ইমেইল') }}</label>--}}
{{--                                    <div class="sp-field-box" style="background:#f5f5f5;">--}}
{{--                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>--}}
{{--                                        <input type="email" id="sp-reset-email-display" value="{{ request()->query('email') ?? '' }}" readonly style="color:#999; cursor:default;">--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="sp-field-group">--}}
{{--                                    <label class="sp-field-label">{{ __('নতুন পাসওয়ার্ড') }}</label>--}}
{{--                                    <div class="sp-field-box">--}}
{{--                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>--}}
{{--                                        <input type="password" name="password" id="sp-new-password" autocomplete="new-password" placeholder="{{ __('কমপক্ষে ৮ অক্ষর') }}">--}}
{{--                                        <button type="button" class="sp-eye-btn" id="sp-np-toggle">--}}
{{--                                            <svg class="sp-eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>--}}
{{--                                            <svg class="sp-eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>--}}
{{--                                        </button>--}}
{{--                                    </div>--}}
{{--                                    <div class="sp-strength-track">--}}
{{--                                        <div class="sp-strength-bar" id="sp-rp-sb1"></div>--}}
{{--                                        <div class="sp-strength-bar" id="sp-rp-sb2"></div>--}}
{{--                                        <div class="sp-strength-bar" id="sp-rp-sb3"></div>--}}
{{--                                        <div class="sp-strength-bar" id="sp-rp-sb4"></div>--}}
{{--                                    </div>--}}
{{--                                    <div class="sp-strength-hint" id="sp-rp-hint"></div>--}}
{{--                                    <span class="sp-field-error invalid-feedback error error-password"></span>--}}
{{--                                </div>--}}

{{--                                <div class="sp-field-group">--}}
{{--                                    <label class="sp-field-label">{{ __('পাসওয়ার্ড নিশ্চিত করুন') }}</label>--}}
{{--                                    <div class="sp-field-box" id="sp-cp-box">--}}
{{--                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="20 6 9 17 4 12"/></svg>--}}
{{--                                        <input type="password" name="password_confirmation" id="sp-confirm-password" autocomplete="new-password" placeholder="{{ __('পাসওয়ার্ড আবার লিখুন') }}">--}}
{{--                                        <button type="button" class="sp-eye-btn" id="sp-cp-toggle">--}}
{{--                                            <svg class="sp-eye-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>--}}
{{--                                            <svg class="sp-eye-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>--}}
{{--                                        </button>--}}
{{--                                    </div>--}}
{{--                                    <div class="sp-match-hint" id="sp-match-hint"></div>--}}
{{--                                    <span class="sp-field-error invalid-feedback error error-password_confirmation"></span>--}}
{{--                                </div>--}}

{{--                                <button type="submit" class="sp-submit-btn" id="sp-reset-btn">--}}
{{--                                    <span class="sp-btn-text">{{ __('পাসওয়ার্ড সেভ করুন') }}</span>--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="20 6 9 17 4 12"/></svg>--}}
{{--                                    <span class="sp-spinner" id="sp-reset-spinner"></span>--}}
{{--                                </button>--}}
{{--                            </form>--}}
{{--                        </div>--}}

{{--                        --}}{{-- STEP 2 SUCCESS --}}
{{--                        <div class="sp-reset-step" id="sp-step-done">--}}
{{--                            <div class="sp-success-box" style="padding-top:30px;">--}}
{{--                                <div class="sp-success-icon">--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>--}}
{{--                                </div>--}}
{{--                                <div class="sp-success-title">{{ __('পাসওয়ার্ড আপডেট হয়েছে!') }}</div>--}}
{{--                                <div class="sp-success-sub">{{ __('আপনার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে। এখন নতুন পাসওয়ার্ড দিয়ে লগইন করুন।') }}</div>--}}
{{--                                <button type="button" class="sp-submit-btn" style="max-width:200px; margin:0 auto;" onclick="spBackToLogin()">--}}
{{--                                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="width:15px;height:15px;"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>--}}
{{--                                    <span>{{ __('লগইন করুন') }}</span>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                    </div>--}}{{-- /sp-right --}}
{{--                </div>--}}{{-- /sp-wrap --}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}

{{--<script>--}}
{{--    (function(){--}}
{{--        /* ── helpers ── */--}}
{{--        function spShowStep(id){--}}
{{--            document.querySelectorAll('.sp-reset-step').forEach(function(el){ el.classList.remove('active'); });--}}
{{--            var el = document.getElementById(id);--}}
{{--            if(el) el.classList.add('active');--}}
{{--        }--}}
{{--        window.spShowStep = spShowStep;--}}

{{--        window.spBackToLogin = function(){--}}
{{--            var rm = document.getElementById('sp-reset-modal');--}}
{{--            var lm = document.getElementById('login');--}}
{{--            if(rm && typeof bootstrap !== 'undefined'){--}}
{{--                var inst = bootstrap.Modal.getInstance(rm);--}}
{{--                if(inst) inst.hide();--}}
{{--            }--}}
{{--            setTimeout(function(){--}}
{{--                if(lm && typeof bootstrap !== 'undefined') new bootstrap.Modal(lm).show();--}}
{{--            }, 300);--}}
{{--        };--}}

{{--        /* ── open correct step on modal show ── */--}}
{{--        var resetModal = document.getElementById('sp-reset-modal');--}}
{{--        if(resetModal){--}}
{{--            resetModal.addEventListener('show.bs.modal', function(){--}}
{{--                var tk = document.getElementById('sp-reset-token');--}}
{{--                spShowStep((tk && tk.value && tk.value.length > 10) ? 'sp-step-newpass' : 'sp-step-email');--}}
{{--            });--}}
{{--        }--}}

{{--        /* ── eye toggles ── */--}}
{{--        function eyeToggle(btnId, inpId){--}}
{{--            var btn = document.getElementById(btnId);--}}
{{--            if(!btn) return;--}}
{{--            btn.addEventListener('click', function(){--}}
{{--                var inp = document.getElementById(inpId);--}}
{{--                var isP = inp.type === 'password';--}}
{{--                inp.type = isP ? 'text' : 'password';--}}
{{--                btn.querySelector('.sp-eye-show').style.display = isP ? 'none' : '';--}}
{{--                btn.querySelector('.sp-eye-hide').style.display = isP ? '' : 'none';--}}
{{--            });--}}
{{--        }--}}
{{--        eyeToggle('sp-np-toggle','sp-new-password');--}}
{{--        eyeToggle('sp-cp-toggle','sp-confirm-password');--}}

{{--        /* ── strength meter ── */--}}
{{--        var np = document.getElementById('sp-new-password');--}}
{{--        function checkMatch(){--}}
{{--            var cp = document.getElementById('sp-confirm-password');--}}
{{--            var hint = document.getElementById('sp-match-hint');--}}
{{--            var box  = document.getElementById('sp-cp-box');--}}
{{--            if(!cp || !np || !hint) return;--}}
{{--            if(!cp.value){ hint.textContent=''; return; }--}}
{{--            var ok = np.value === cp.value;--}}
{{--            hint.textContent = ok ? '✓ পাসওয়ার্ড মিলেছে' : '✗ পাসওয়ার্ড মিলছে না';--}}
{{--            hint.className = 'sp-match-hint ' + (ok ? 'sp-match-ok' : 'sp-match-no');--}}
{{--            if(box) box.style.borderColor = ok ? '#0f6e56' : '#e74c3c';--}}
{{--        }--}}
{{--        if(np){--}}
{{--            np.addEventListener('input', function(){--}}
{{--                var v=this.value, s=0;--}}
{{--                if(v.length>=8) s++;--}}
{{--                if(/[A-Z]/.test(v)) s++;--}}
{{--                if(/[0-9]/.test(v)) s++;--}}
{{--                if(/[^A-Za-z0-9]/.test(v)) s++;--}}
{{--                ['sp-rp-sb1','sp-rp-sb2','sp-rp-sb3','sp-rp-sb4'].forEach(function(id,i){--}}
{{--                    var el=document.getElementById(id);--}}
{{--                    el.className='sp-strength-bar';--}}
{{--                    if(v.length>0 && i<s) el.classList.add(['','s-weak','s-fair','s-good','s-strong'][s]);--}}
{{--                });--}}
{{--                var h=document.getElementById('sp-rp-hint');--}}
{{--                if(h) h.textContent = v.length ? 'পাসওয়ার্ড শক্তি: '+['','দুর্বল','মোটামুটি','ভালো','শক্তিশালী'][s] : '';--}}
{{--                checkMatch();--}}
{{--            });--}}
{{--        }--}}
{{--        var cp = document.getElementById('sp-confirm-password');--}}
{{--        if(cp) cp.addEventListener('input', checkMatch);--}}

{{--        /* ── forgot form AJAX ── */--}}
{{--        var ff = document.getElementById('sp-forgot-form');--}}
{{--        if(ff){--}}
{{--            ff.addEventListener('submit', function(e){--}}
{{--                e.preventDefault();--}}
{{--                var btn=document.getElementById('sp-forgot-btn');--}}
{{--                var spin=document.getElementById('sp-forgot-spinner');--}}
{{--                var err=document.getElementById('sp-forgot-error');--}}
{{--                btn.querySelector('.sp-btn-text').style.display='none';--}}
{{--                btn.querySelector('svg').style.display='none';--}}
{{--                spin.style.display='inline-block';--}}
{{--                btn.disabled=true; err.style.display='none';--}}
{{--                fetch(ff.action,{ method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body:new FormData(ff) })--}}
{{--                    .then(function(r){ return r.json().then(function(d){ return {ok:r.ok,data:d}; }); })--}}
{{--                    .then(function(res){--}}
{{--                        btn.querySelector('.sp-btn-text').style.display='';--}}
{{--                        btn.querySelector('svg').style.display='';--}}
{{--                        spin.style.display='none'; btn.disabled=false;--}}
{{--                        if(res.ok){ spShowStep('sp-step-sent'); }--}}
{{--                        else{--}}
{{--                            var m=res.data.message||(res.data.errors&&res.data.errors.email&&res.data.errors.email[0])||'কিছু একটা ভুল হয়েছে।';--}}
{{--                            err.textContent=m; err.style.display='block';--}}
{{--                        }--}}
{{--                    }).catch(function(){--}}
{{--                    btn.querySelector('.sp-btn-text').style.display='';--}}
{{--                    spin.style.display='none'; btn.disabled=false;--}}
{{--                    err.textContent='নেটওয়ার্ক সমস্যা। আবার চেষ্টা করুন।'; err.style.display='block';--}}
{{--                });--}}
{{--            });--}}
{{--        }--}}

{{--        /* ── reset password AJAX ── */--}}
{{--        var rf = document.getElementById('sp-reset-form');--}}
{{--        if(rf){--}}
{{--            rf.addEventListener('submit', function(e){--}}
{{--                e.preventDefault();--}}
{{--                var np2=document.getElementById('sp-new-password');--}}
{{--                var cp2=document.getElementById('sp-confirm-password');--}}
{{--                if(np2&&cp2&&np2.value!==cp2.value){--}}
{{--                    var h=document.getElementById('sp-match-hint');--}}
{{--                    if(h){ h.textContent='✗ পাসওয়ার্ড মিলছে না'; h.className='sp-match-hint sp-match-no'; }--}}
{{--                    return;--}}
{{--                }--}}
{{--                var btn=document.getElementById('sp-reset-btn');--}}
{{--                var spin=document.getElementById('sp-reset-spinner');--}}
{{--                var err=document.getElementById('sp-reset-error');--}}
{{--                btn.querySelector('.sp-btn-text').style.display='none';--}}
{{--                btn.querySelector('svg').style.display='none';--}}
{{--                spin.style.display='inline-block'; btn.disabled=true; err.style.display='none';--}}
{{--                fetch(rf.action,{ method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body:new FormData(rf) })--}}
{{--                    .then(function(r){ return r.json().then(function(d){ return {ok:r.ok,data:d}; }); })--}}
{{--                    .then(function(res){--}}
{{--                        btn.querySelector('.sp-btn-text').style.display='';--}}
{{--                        btn.querySelector('svg').style.display='';--}}
{{--                        spin.style.display='none'; btn.disabled=false;--}}
{{--                        if(res.ok){ spShowStep('sp-step-done'); }--}}
{{--                        else{--}}
{{--                            var m=res.data.message||(res.data.errors&&res.data.errors.password&&res.data.errors.password[0])||'কিছু একটা ভুল হয়েছে।';--}}
{{--                            err.textContent=m; err.style.display='block';--}}
{{--                        }--}}
{{--                    }).catch(function(){--}}
{{--                    btn.querySelector('.sp-btn-text').style.display='';--}}
{{--                    spin.style.display='none'; btn.disabled=false;--}}
{{--                    err.textContent='নেটওয়ার্ক সমস্যা। আবার চেষ্টা করুন।'; err.style.display='block';--}}
{{--                });--}}
{{--            });--}}
{{--        }--}}

{{--        /* ── auto-open if URL contains reset token ── */--}}
{{--        (function(){--}}
{{--            var url = new URL(window.location.href);--}}
{{--            var token = url.searchParams.get('token') || (url.pathname.match(/password\/reset\/([^?/]+)/) || [])[1];--}}
{{--            if(!token) return;--}}
{{--            var ti=document.getElementById('sp-reset-token');--}}
{{--            var ei=document.getElementById('sp-reset-email-hidden');--}}
{{--            var ed=document.getElementById('sp-reset-email-display');--}}
{{--            var email=url.searchParams.get('email')||'';--}}
{{--            if(ti) ti.value=token;--}}
{{--            if(ei) ei.value=email;--}}
{{--            if(ed) ed.value=email;--}}
{{--            setTimeout(function(){--}}
{{--                var m=document.getElementById('sp-reset-modal');--}}
{{--                if(m && typeof bootstrap!=='undefined') new bootstrap.Modal(m).show();--}}
{{--            }, 400);--}}
{{--        })();--}}
{{--    })();--}}
{{--</script>--}}



@extends('layouts.app')

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef6ff', 100: '#dbeeff', 200: '#bfddff',
                            300: '#93c5fd', 400: '#5aaaf5', 500: '#2e86e8',
                            600: '#1a6ccc', 700: '#155aa8', 800: '#164a86', 900: '#173f6e',
                        }
                    },
                    animation: {
                        'fade-up': 'fadeUp 0.5s ease both',
                        'shake':   'shake 0.4s ease',
                        'scale-in': 'scaleIn 0.4s ease both',
                    },
                    keyframes: {
                        fadeUp:  { '0%': { opacity: '0', transform: 'translateY(16px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        shake:   { '0%,100%': { transform: 'translateX(0)' }, '20%,60%': { transform: 'translateX(-6px)' }, '40%,80%': { transform: 'translateX(6px)' } },
                        scaleIn: { '0%': { opacity: '0', transform: 'scale(0.9)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <div class="font-sans min-h-screen flex items-center justify-center p-4"
         style="background: linear-gradient(135deg, #eef6ff 0%, #f0f7ff 50%, #e8f4fd 100%);">

        {{-- Background decoration --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #5aaaf5, transparent 70%)"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full opacity-15"
                 style="background: radial-gradient(circle, #1a6ccc, transparent 70%)"></div>
        </div>

        <div class="relative w-full max-w-md animate-fade-up">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-brand-100">

                {{-- Top banner --}}
                <div class="px-8 pt-8 pb-6 text-center" style="background: linear-gradient(135deg, #1a6ccc 0%, #2e86e8 100%);">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4 border border-white/30">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-700 text-white">{{ __('Forgot your password?') }}</h1>
                    <p class="mt-1.5 text-sm text-blue-100 leading-relaxed">
                        {{ __("No worries! Enter your email and we'll send you a reset link.") }}
                    </p>
                </div>

                <div class="px-8 py-7">

                    {{-- Success state --}}
                    @if (session('status'))
                        <div class="flex flex-col items-center text-center py-4 animate-scale-in">
                            <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-700 text-gray-900 mb-2">{{ __('Check your email!') }}</h2>
                            <p class="text-sm text-gray-500 leading-relaxed mb-6">{{ session('status') }}</p>
                            <div class="w-full bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-start gap-2.5 text-left">
                                <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <p class="text-xs text-green-700">{{ __("Didn't receive the email? Check your spam folder or try again.") }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Error Alert --}}
                    <div id="fp-alert" class="hidden mb-5 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span id="fp-alert-msg"></span>
                    </div>

                    {{-- Form --}}
                    <form id="fp-form" method="POST" action="{{ route('password.email') }}" novalidate>
                        @csrf

                        <div class="mb-5">
                            <label for="fp-email" class="block text-xs font-600 text-gray-600 mb-2 uppercase tracking-wider">
                                {{ __('Email address') }}
                            </label>
                            <div class="relative">
                            <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                                <input
                                    id="fp-email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    autocomplete="email"
                                    autofocus
                                    placeholder="you@example.com"
                                    class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border rounded-xl text-gray-900 placeholder-gray-400
                                       focus:outline-none focus:bg-white focus:border-brand-400 focus:ring-2 focus:ring-brand-100
                                       transition-all duration-200
                                       {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}"
                                    required
                                >
                            </div>
                            @if ($errors->has('email'))
                                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $errors->first('email') }}
                                </p>
                            @endif
                            <p id="fp-field-error" class="hidden mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span id="fp-field-error-msg"></span>
                            </p>
                        </div>

                        <button type="submit" id="fp-btn"
                                class="w-full flex items-center justify-center gap-2.5 py-3.5 px-6
                                   bg-brand-600 hover:bg-brand-700 active:bg-brand-800
                                   text-white text-sm font-600 rounded-xl
                                   transition-all duration-200
                                   focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2
                                   disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none
                                   shadow-sm hover:shadow-md">
                            <svg id="fp-btn-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            <span id="fp-btn-text">{{ __('Send reset link') }}</span>
                            <svg id="fp-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </button>
                    </form>

                    {{-- Back to login --}}
                    <div class="mt-5 text-center">
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 transition-colors font-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 5l-7 7 7 7"/>
                            </svg>
                            {{ __('Back to login') }}
                        </a>
                    </div>

                </div>
            </div>

            {{-- Footer note --}}
            <p class="mt-5 text-center text-xs text-gray-400">
                {{ __('The reset link will expire in 60 minutes.') }}
            </p>
        </div>
    </div>

    <script>
        (function () {
            var form     = document.getElementById('fp-form');
            var btn      = document.getElementById('fp-btn');
            var btnText  = document.getElementById('fp-btn-text');
            var btnIcon  = document.getElementById('fp-btn-icon');
            var spinner  = document.getElementById('fp-spinner');
            var alert    = document.getElementById('fp-alert');
            var alertMsg = document.getElementById('fp-alert-msg');
            var fieldErr = document.getElementById('fp-field-error');
            var fieldMsg = document.getElementById('fp-field-error-msg');
            var emailInp = document.getElementById('fp-email');
            var isSubmitting = false;

            function setLoading(state) {
                isSubmitting = state;
                btn.disabled = state;
                btnText.textContent = state ? '{{ __("Sending...") }}' : '{{ __("Send reset link") }}';
                btnIcon.classList.toggle('hidden', state);
                spinner.classList.toggle('hidden', !state);
            }

            function showAlert(msg) {
                alertMsg.textContent = msg;
                alert.classList.remove('hidden');
                alert.classList.add('animate-shake');
                setTimeout(function () { alert.classList.remove('animate-shake'); }, 500);
            }

            if (emailInp) {
                emailInp.addEventListener('input', function () {
                    alert.classList.add('hidden');
                    fieldErr.classList.add('hidden');
                    emailInp.classList.remove('border-red-400', 'bg-red-50');
                });
            }

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (isSubmitting) return;

                    setLoading(true);
                    alert.classList.add('hidden');
                    fieldErr.classList.add('hidden');

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: new FormData(form)
                    })
                        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                        .then(function (res) {
                            setLoading(false);
                            if (res.ok) {
                                // Show inline success
                                form.innerHTML = '<div class="flex flex-col items-center text-center py-2 animate-scale-in">' +
                                    '<div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mb-4">' +
                                    '<svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>' +
                                    '</div>' +
                                    '<h2 class="text-lg font-700 text-gray-900 mb-2">{{ __("Email sent!") }}</h2>' +
                                    '<p class="text-sm text-gray-500 mb-2">{{ __("Please check your inbox and follow the link to reset your password.") }}</p>' +
                                    '<p class="text-xs text-gray-400">{{ __("Do not see it? Check your spam folder.") }}</p>' +
                                    '</div>';
                                return;
                            }
                            var errors = res.data.errors || {};
                            var emailErr = errors.email ? (Array.isArray(errors.email) ? errors.email[0] : errors.email) : null;
                            if (emailErr) {
                                fieldMsg.textContent = emailErr;
                                fieldErr.classList.remove('hidden');
                                if (emailInp) { emailInp.classList.add('border-red-400', 'bg-red-50'); }
                            }
                            showAlert(res.data.message || '{{ __("Something went wrong. Please try again.") }}');
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
