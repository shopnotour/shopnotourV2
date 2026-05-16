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
            padding: 22px 14px 8px;
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
        .sn-field label {
            position: absolute;
            left: 14px; top: 8px;
            font-size: 10px; font-weight: 700;
            color: #6b7280;
            text-transform: uppercase; letter-spacing: .06em;
            pointer-events: none;
        }
    </style>

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-900">{{ __('Bonus & Points') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('Manage your bonus balance and points') }}</p>
    </div>

    @include('admin.message')

    {{-- Balance Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 sn-fadein">

        {{-- Bonus Balance --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                     style="background:#eff6ff; border:1.5px solid #bfdbfe;">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-700 text-gray-500 uppercase tracking-wider mb-1">{{ __('Bonus Balance') }}</div>
                    <div class="text-2xl font-extrabold text-gray-900">{{ format_money($row->bonus_balance ?? 0) }}</div>
                    @if(!setting_item('bonus_enabled'))
                        <div class="text-xs text-gray-400 mt-1">{{ __('Bonus not enabled') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Points Balance --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                     style="background:#f0fdf4; border:1.5px solid #bbf7d0;">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-700 text-gray-500 uppercase tracking-wider mb-1">{{ __('Points Balance') }}</div>
                    <div class="text-2xl font-extrabold text-gray-900">
                        {{ number_format($row->bonus_points ?? 0) }}
                        <span class="text-sm font-500 text-gray-400">pts</span>
                    </div>
                    @if(setting_item('point_value') && ($row->bonus_points ?? 0) > 0)
                        <div class="text-xs text-gray-400 mt-1">
                            ≈ {{ format_money(($row->bonus_points ?? 0) * setting_item('point_value', 1)) }}
                        </div>
                    @elseif(!setting_item('point_enabled'))
                        <div class="text-xs text-gray-400 mt-1">{{ __('Points not enabled') }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sn-fadein">

        {{-- Apply Bonus Code --}}
        @if(setting_item('bonus_enabled') && setting_item('bonus_code'))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-700 text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ __('Apply Bonus Code') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Enter your bonus code to get free balance') }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('user.bonus.applyCode') }}" method="POST">
                        @csrf
                        <div class="sn-field mb-4">
                            <input type="text" name="bonus_code" placeholder="e.g. WELCOME100"
                                   style="text-transform:uppercase; letter-spacing:.08em;"
                                   value="{{ old('bonus_code') }}">
                            <label>{{ __('Bonus Code') }}</label>
                        </div>
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 text-white font-700 rounded-xl transition-all"
                                style="height:48px; font-size:14px; background:#2563eb; border:none; cursor:pointer; box-shadow:0 2px 8px rgba(37,99,235,.3);">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            {{ __('Apply Code') }}
                        </button>
                    </form>

                    @if(setting_item('bonus_amount'))
                        <div class="mt-4 flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-700">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ __('You will receive :amount bonus on successful code application.', ['amount' => format_money(setting_item('bonus_amount'))]) }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- How it works --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h2 class="font-700 text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ __('How it works') }}
                </h2>
            </div>
            <div class="p-6 flex flex-col gap-4">
                @if(setting_item('bonus_enabled'))
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-700 text-white" style="background:#2563eb;">1</div>
                        <div>
                            <div class="text-sm font-600 text-gray-800">{{ __('Apply Bonus Code') }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ __('Enter a valid bonus code to get :amount credited to your bonus balance.', ['amount' => format_money(setting_item('bonus_amount', 0))]) }}</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-700 text-white" style="background:#2563eb;">2</div>
                        <div>
                            <div class="text-sm font-600 text-gray-800">{{ __('Use on Booking') }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ __(':amount will be deducted from your bonus balance per :type.', [
                                    'amount' => format_money(setting_item('bonus_per_deduct', 0)),
                                    'type'   => setting_item('bonus_per_type') == 'ticket' ? __('ticket') : __('booking')
                                ]) }}
                            </div>
                        </div>
                    </div>
                    @if(setting_item('bonus_expire_days'))
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-700 text-white" style="background:#f97316;">3</div>
                            <div>
                                <div class="text-sm font-600 text-gray-800">{{ __('Expiry') }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ __('Bonus balance expires after :days days.', ['days' => setting_item('bonus_expire_days')]) }}</div>
                            </div>
                        </div>
                    @endif
                @endif

                @if(setting_item('point_enabled'))
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-700 text-white" style="background:#16a34a;">
                            <svg class="w-3.5 h-3.5" fill="white" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <div>
                            <div class="text-sm font-600 text-gray-800">{{ __('Earn Points') }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ __('Earn :count points per :type. 1 point = :value.', [
                                    'count' => setting_item('point_per_count', 0),
                                    'type'  => setting_item('point_per_type') == 'ticket' ? __('ticket') : __('booking'),
                                    'value' => format_money(setting_item('point_value', 1))
                                ]) }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Transaction History --}}
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sn-fadein">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h2 class="font-700 text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('Transaction History') }}
            </h2>
        </div>

        @if($transactions->count())
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                    <tr style="background:#f9fafb; border-bottom:1px solid #f3f4f6;">
                        <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">{{ __('Date') }}</th>
                        <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">{{ __('Type') }}</th>
                        <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">{{ __('Amount') }}</th>
                        <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">{{ __('Remarks') }}</th>
                        <th style="padding:12px 20px; text-align:left; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.06em;">{{ __('Status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transactions as $tx)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:14px 20px; font-size:13px; color:#374151;">{{ display_datetime($tx->created_at) }}</td>
                            <td style="padding:14px 20px;">
                                @if($tx->type == 'bonus_credit')
                                    <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; background:#dcfce7; color:#15803d;">
                                    ↑ {{ __('Credit') }}
                                </span>
                                @elseif($tx->type == 'bonus_debit')
                                    <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; background:#fee2e2; color:#dc2626;">
                                    ↓ {{ __('Debit') }}
                                </span>
                                @else
                                    <span style="display:inline-flex; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; background:#f3f4f6; color:#6b7280;">
                                    {{ ucfirst($tx->type) }}
                                </span>
                                @endif
                            </td>
                            <td style="padding:14px 20px; font-size:13px; font-weight:600; color:{{ $tx->type == 'bonus_credit' ? '#16a34a' : '#dc2626' }};">
                                {{ $tx->type == 'bonus_credit' ? '+' : '-' }}{{ format_money($tx->amount) }}
                            </td>
                            <td style="padding:14px 20px; font-size:13px; color:#6b7280;">{{ $tx->remarks ?? '-' }}</td>
                            <td style="padding:14px 20px;">
                            <span style="padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600;
                                background:{{ $tx->status == 'confirmed' ? '#dcfce7' : '#fef9c3' }};
                                color:{{ $tx->status == 'confirmed' ? '#15803d' : '#a16207' }};">
                                {{ ucfirst($tx->status) }}
                            </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <svg class="w-10 h-10 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <div class="text-sm font-500">{{ __('No transactions yet') }}</div>
            </div>
        @endif
    </div>

@endsection
