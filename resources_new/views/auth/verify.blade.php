@extends('layouts.app')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { font-family: 'Outfit', sans-serif; }
        @keyframes snFadeUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .sn-fadein { animation: snFadeUp 0.45s ease both; }
        @keyframes snSpin { to { transform:rotate(360deg); } }
        .sn-spin { animation: snSpin .7s linear infinite; }
    </style>

    <div class="min-h-screen flex items-center justify-center px-4 py-10"
         style="background: linear-gradient(135deg, #eff6ff 0%, #f0f7ff 50%, #e8f4fd 100%);">

        <div class="w-full max-w-md sn-fadein">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">

                {{-- Top banner --}}
                <div class="px-8 pt-10 pb-8 text-center text-white"
                     style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5"
                         style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.8">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-extrabold mb-2">{{ __('Verify Your Email') }}</h1>
                    <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,.8)">
                        {{ __('One more step to complete your registration.') }}
                    </p>
                </div>

                <div class="px-8 py-8">

                    {{-- Success alert --}}
                    @if(session('resent'))
                        <div class="flex items-start gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 mb-6">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <p class="text-sm text-green-700">{{ __('A fresh verification link has been sent to your email address.') }}</p>
                        </div>
                    @endif

                    {{-- Info box --}}
                    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-6">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p class="text-sm text-blue-700 leading-relaxed">
                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email, click the button below to request another.') }}
                        </p>
                    </div>

                    {{-- Resend button --}}
                    <form action="{{ route('verification.send') }}" method="post" id="sn-resend-form">
                        @csrf
                        <button type="submit" id="sn-resend-btn"
                                class="w-full flex items-center justify-center gap-2 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-60 disabled:pointer-events-none"
                                style="height:52px; font-size:15px; background:#2563eb;">
                            <svg id="sn-resend-ic" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
                            </svg>
                            <svg id="sn-resend-sp" class="w-4 h-4 sn-spin" style="display:none" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
                                <path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span id="sn-resend-bt">{{ __('Resend Verification Email') }}</span>
                        </button>
                    </form>

                    {{-- Tips --}}
                    <div class="mt-6 flex flex-col gap-2.5">
                        @foreach([
                            __('Check your spam or junk folder'),
                            __('Make sure you entered the correct email'),
                            __('The link expires after 60 minutes'),
                        ] as $tip)
                            <div class="flex items-center gap-2.5 text-sm text-gray-500">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></div>
                                {{ $tip }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Back to login --}}
                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-600 transition-colors font-medium no-underline">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 5l-7 7 7 7"/>
                            </svg>
                            {{ __('Back to login') }}
                        </a>
                    </div>

                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-5">© {{ date('Y') }} Shopno Tours & Travels</p>
        </div>
    </div>

    <script>
        (function(){
            var form = document.getElementById('sn-resend-form');
            var btn  = document.getElementById('sn-resend-btn');
            var btxt = document.getElementById('sn-resend-bt');
            var icon = document.getElementById('sn-resend-ic');
            var spin = document.getElementById('sn-resend-sp');
            var busy = false;

            if(form){
                form.addEventListener('submit', function(){
                    if(busy) return;
                    busy = true; btn.disabled = true;
                    btxt.textContent = '{{ __("Sending...") }}';
                    icon.style.display = 'none';
                    spin.style.display = '';
                });
            }
        })();
    </script>
@endsection
