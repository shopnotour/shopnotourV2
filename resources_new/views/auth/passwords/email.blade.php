{{--@extends('layouts.app')--}}
{{--@section('content')--}}
{{--<div class="container">--}}
{{--    <div class="row justify-content-center bravo-login-form-page bravo-login-page">--}}
{{--        <div class="col-md-8">--}}
{{--            <div class="card">--}}
{{--                <div class="card-header">{{ __('Reset Password') }}</div>--}}
{{--                <div class="card-body">--}}
{{--                    @if (session('status'))--}}
{{--                        <div class="alert alert-success" role="alert">--}}
{{--                            {{ session('status') }}--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                    <form method="POST" action="{{ route('password.email') }}">--}}
{{--                        @csrf--}}
{{--                        <div class="form-group row">--}}
{{--                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>--}}
{{--                            <div class="col-md-6">--}}
{{--                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>--}}
{{--                                @if ($errors->has('email'))--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $errors->first('email') }}</strong>--}}
{{--                                    </span>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="form-group row mb-0">--}}
{{--                            <div class="col-md-6 offset-md-4">--}}
{{--                                <button type="submit" class="btn btn-primary">--}}
{{--                                    {{ __('Send Password Reset Link') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--@endsection--}}


@extends('layouts.app')

@section('content')
    <style>
        .reset-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: #f8f9fa;
        }
        .reset-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .reset-card-top {
            background: #eaf3fb;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }
        .lock-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(55,138,221,0.12);
            border: 1.5px solid rgba(55,138,221,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .lock-icon svg {
            width: 22px;
            height: 22px;
            stroke: #185FA5;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .reset-card-top h2 {
            font-size: 20px;
            font-weight: 600;
            color: #185FA5;
            margin-bottom: 6px;
        }
        .reset-card-top p {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.5;
            margin: 0;
        }
        .reset-card-body {
            padding: 1.75rem 2rem;
        }
        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: #adb5bd;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
        }
        .input-icon-wrap .form-control {
            padding-left: 38px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            font-size: 14px;
            height: 44px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .input-icon-wrap .form-control:focus {
            border-color: #378ADD;
            box-shadow: 0 0 0 3px rgba(55,138,221,0.15);
        }
        .btn-reset {
            width: 100%;
            padding: 11px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            background: #378ADD;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s;
            cursor: pointer;
        }
        .btn-reset:hover {
            background: #185FA5;
            color: #fff;
        }
        .btn-reset svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: #fff;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .back-link {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 13px;
            color: #6c757d;
        }
        .back-link a {
            color: #378ADD;
            font-weight: 500;
            text-decoration: none;
        }
        .back-link a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .reset-card-body { padding: 1.5rem 1.25rem; }
            .reset-card-top { padding: 1.5rem 1.25rem 1.25rem; }
        }
    </style>

    <div class="reset-page">
        <div class="reset-card">
            <div class="reset-card-top">
                <div class="lock-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h2>{{ __('Reset your password') }}</h2>
                <p>{{ __("Enter your email and we'll send you a link to reset your password.") }}</p>
            </div>

            <div class="reset-card-body">
                @if (session('status'))
                    <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="email" class="form-label fw-500" style="font-size:13px; color:#495057;">
                            {{ __('Email address') }}
                        </label>
                        <div class="input-icon-wrap">
                            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <input
                                id="email"
                                type="email"
                                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="you@example.com"
                                required
                                autocomplete="email"
                                autofocus
                            >
                            @if ($errors->has('email'))
                                <div class="invalid-feedback">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn-reset mt-1">
                        <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        {{ __('Send password reset link') }}
                    </button>
                </form>

                <p class="back-link">
                    <a href="{{ route('login') }}">← {{ __('Back to login') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
