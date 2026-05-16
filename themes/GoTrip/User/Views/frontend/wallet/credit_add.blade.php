@extends('layouts.user')

@push('css')
    <style>
        /* ── Reset & base ── */
        * {
            box-sizing: border-box;
        }

        /* ── Page ── */
        .wallet-page {
            padding: 16px;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ── Balance Hero ── */
        .balance-hero {
            background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%);
            border-radius: 16px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(29, 78, 216, .25);
        }

        .balance-label {
            font-size: 11px;
            color: rgba(255, 255, 255, .6);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 4px;
        }

        .balance-amount {
            font-size: 32px;
            font-weight: 800;
            color: white;
            line-height: 1;
            font-family: monospace;
        }

        .balance-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, .5);
            margin-top: 4px;
        }

        /* ── Layout ── */
        .wallet-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 20px;
            align-items: start;
        }

        /* ── Card ── */
        .w-card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .w-card-hd {
            padding: 14px 18px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .w-card-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .w-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .w-card-body {
            padding: 18px;
        }

        /* ── Form controls ── */
        .f-group {
            margin-bottom: 14px;
        }

        .f-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .f-control {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 13.5px;
            color: #1e293b;
            transition: border .15s, box-shadow .15s;
            outline: none;
            background: white;
        }

        .f-control:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, .12);
        }

        .f-control[readonly] {
            background: #f8fafc;
            color: #64748b;
        }

        .f-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .f-col-full {
            grid-column: span 2;
        }

        /* Action tabs */
        .action-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .action-tab {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: white;
            cursor: pointer;
            text-align: center;
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            transition: all .18s;
        }

        .action-tab.active {
            border-color: #1d4ed8;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .action-tab .tab-icon {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
        }

        /* Submit btn */
        .submit-btn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #1e3a5f, #1d4ed8);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            opacity: .9;
        }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: border .15s, background .15s;
            position: relative;
        }

        .upload-zone:hover {
            border-color: #1d4ed8;
            background: #f8fafc;
        }

        .upload-zone input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-icon {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .upload-txt {
            font-size: 12px;
            color: #64748b;
        }

        /* ── Payment Details ── */
        .pay-section-hd {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            user-select: none;
            transition: background .12s;
        }

        .pay-section-hd:hover {
            background: #f8fafc;
        }

        .pay-section-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .pay-section-name {
            flex: 1;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .pay-section-num {
            font-size: 12px;
            color: #64748b;
            font-family: monospace;
        }

        .pay-chevron {
            font-size: 11px;
            color: #94a3b8;
            transition: transform .22s;
        }

        .pay-section-hd.open .pay-chevron {
            transform: rotate(180deg);
        }

        .pay-section-body {
            display: none;
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .pay-section-body.open {
            display: block;
        }

        /* Detail rows inside sections */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12.5px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .detail-val {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
            max-width: 55%;
        }

        .detail-val.mono {
            font-family: monospace;
            letter-spacing: .04em;
        }

        /* Copy btn */
        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 11px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            border: none;
            margin-top: 10px;
        }

        /* Note bar */
        .note-bar {
            background: #fffbeb;
            border-top: 1px solid #fde68a;
            padding: 11px 16px;
            font-size: 11.5px;
            color: #92400e;
        }

        /* Mobile sticky balance */
        .mobile-balance-bar {
            display: none;
            position: sticky;
            top: 0;
            z-index: 100;
            background: linear-gradient(135deg, #1e3a5f, #1d4ed8);
            padding: 10px 16px;
            margin: -16px -16px 16px;
            box-shadow: 0 2px 10px rgba(29, 78, 216, .3);
        }

        /* ── RESPONSIVE ── */
        @media(max-width:900px) {
            .wallet-grid {
                grid-template-columns: 1fr;
            }

            /* On mobile: payment details comes first */
            .payment-col {
                order: -1;
            }
        }

        @media(max-width:600px) {
            .wallet-page {
                padding: 12px;
            }

            .balance-hero {
                padding: 16px;
                border-radius: 12px;
            }

            .balance-amount {
                font-size: 26px;
            }

            .f-row {
                grid-template-columns: 1fr;
            }

            .f-col-full {
                grid-column: span 1;
            }

            .action-tabs {
                gap: 6px;
            }

            .action-tab {
                padding: 8px 6px;
                font-size: 11.5px;
            }

            .action-tab .tab-icon {
                font-size: 17px;
            }
        }
    </style>
@endpush

@section('content')
    @php

        // Mobile methods — config/payment.php 'mobile' key থেকে
        $mobileMethods = collect(
            config('payment.mobile', [
                [
                    'id' => 'bkash',
                    'name' => 'bKash',
                    'number' => '01XXXXXXXXX',
                    'icon' => '',
                    'bg' => '#fff0f7',
                    'color' => '#db2777',
                    'border' => '#fbcfe8',
                    'sub' => 'Send Money / Personal',
                ],
                [
                    'id' => 'nagad',
                    'name' => 'Nagad',
                    'number' => '01XXXXXXXXX',
                    'icon' => '',
                    'bg' => '#fff7ed',
                    'color' => '#ea580c',
                    'border' => '#fed7aa',
                    'sub' => 'Send Money / Personal',
                ],
                [
                    'id' => 'rocket',
                    'name' => 'Rocket',
                    'number' => '01XXXXXXXXX',
                    'icon' => '',
                    'bg' => '#f5f3ff',
                    'color' => '#7c3aed',
                    'border' => '#ddd6fe',
                    'sub' => 'Send Money / Personal',
                ],
            ]),
        )->map(
            fn($m) => array_merge($m, [
                'type' => 'mobile',
                'fields' => [],
                'sub' => $m['sub'] ?? 'Send Money / Personal',
            ]),
        );

        // Bank methods — config/payment.php 'banks' key থেকে
        $bankMethods = collect(
            config('payment.banks', [
                [
                    'id' => 'bank1',
                    'name' => 'Dutch Bangla Bank',
                    'icon' => '🏦',
                    'bg' => '#eff6ff',
                    'color' => '#1d4ed8',
                    'border' => '#bfdbfe',
                    'fields' => [
                        ['label' => 'Account Name', 'value' => 'Shopno Tour', 'mono' => false],
                        ['label' => 'Account No.', 'value' => 'XXXXXXXXXXXXXXX', 'mono' => true],
                        ['label' => 'Branch', 'value' => 'Gulshan Branch', 'mono' => false],
                        ['label' => 'Routing No.', 'value' => 'XXXXXXXXX', 'mono' => true],
                    ],
                ],
            ]),
        )->map(
            fn($b) => array_merge($b, [
                'type' => 'bank',
                'sub' => 'Bank Transfer',
                'number' => collect($b['fields'])->firstWhere('label', 'Account No.')['value'] ?? '—',
            ]),
        );

        // সব মিলিয়ে — mobile first, then banks
        $paymentMethods = $mobileMethods->merge($bankMethods)->values()->toArray();
    @endphp

    <div class="wallet-page">

        {{-- ── Header ── --}}
        <div
            style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px">
            <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0">💰 Wallet</h1>
            <a href="{{ route('user.wallet.creditTransaction') }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;background:#f1f5f9;color:#475569;font-size:12.5px;font-weight:600;text-decoration:none;border:1px solid #e2e8f0">
                <i class="fas fa-list" style="font-size:11px"></i> Transactions
            </a>
        </div>

        @include('admin.message')

        {{-- ── Balance Hero ── --}}
        <div class="balance-hero">
            <div>
                <div class="balance-label">Current Balance</div>
                <div class="balance-amount">৳{{ number_format($users->credit_balance, 2) }}</div>
                <div class="balance-sub">Available to use</div>
            </div>
            <div style="text-align:right">
                <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:10px 16px;display:inline-block">
                    <div
                        style="font-size:10px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px">
                        Account</div>
                    <div style="font-size:13px;font-weight:700;color:white">{{ auth()->user()->name ?? 'User' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="wallet-grid">

            {{-- ════════ FORM COLUMN ════════ --}}
            <div class="form-col">
                <div class="w-card">
                    <div class="w-card-hd">
                        <div class="w-card-icon" style="background:#eff6ff;color:#1d4ed8"><i class="fas fa-wallet"></i>
                        </div>
                        <div class="w-card-title" id="form_card_title">Add Credit</div>
                    </div>
                    <div class="w-card-body">

                        {{-- Action Tabs --}}
                        <div class="action-tabs">
                            <button type="button" class="action-tab active" id="tab_deposit"
                                    onclick="switchTab('deposit')">
                                <span class="tab-icon">💳</span>
                                Add Credit
                            </button>
                            <button type="button" class="action-tab" id="tab_withdraw" onclick="switchTab('withdraw')">
                                <span class="tab-icon">💸</span>
                                Withdraw
                            </button>
                        </div>

                        <form action="{{ route('user.wallet.storecredit') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="type" id="type" value="deposit">

                            {{-- Amount --}}
                            <div class="f-group">
                                <label class="f-label" id="amount_label">Credit Amount (৳)</label>
                                <input type="number" name="amount" id="amount" placeholder="0.00" step="0.01"
                                       min="1" class="f-control" style="font-size:18px;font-weight:700;">
                            </div>

                            {{-- ── DEPOSIT FIELDS ── --}}
                            <div id="deposit_fields">
                                <div class="f-row">
                                    <div class="f-group">
                                        <label class="f-label">Transaction Type</label>
                                        <select class="f-control" name="transaction_type" id="transaction_type_select">
                                            <option value="">Select Type</option>
                                            <option value="cash">Cash</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="bkash">bKash</option>
                                            <option value="nagad">Nagad</option>
                                            <option value="rocket">Rocket</option>
                                            <option value="others">Others</option>
                                        </select>
                                    </div>

                                    {{-- ✅ নতুন: Bank name dropdown --}}
                                    <div class="f-group" id="bank_name_group" style="display:none">
                                        <label class="f-label">Bank Name</label>
                                        <select class="f-control" name="bank_name" id="bank_name_select">
                                            <option value="">Select Bank</option>
                                            <option value="dutch_bangla_bank">Dutch Bangla Bank</option>
                                            <option value="islami_bank">Islami Bank Bangladesh</option>
                                            <option value="brac_bank">BRAC Bank</option>
                                            {{--                                            <option value="sonali_bank">Sonali Bank</option> --}}
                                            <option value="city_bank">City Bank</option>
                                            {{--                                            <option value="ab_bank">AB Bank</option> --}}
                                            <option value="eastern_bank">Eastern Bank</option>
                                            {{--                                            <option value="prime_bank">Prime Bank</option> --}}
                                            {{--                                            <option value="standard_chartered">Standard Chartered</option> --}}
                                            {{--                                            <option value="others_bank">Others</option> --}}
                                        </select>
                                    </div>
                                    <div class="f-group">
                                        <label class="f-label">Reference / TrxID</label>
                                        <input type="text" name="reference" class="f-control"
                                               placeholder="Transaction ID">
                                    </div>
                                    {{--                                    <div class="f-group"> --}}
                                    {{--                                        <label class="f-label">Deposit Date</label> --}}
                                    {{--                                        <input type="date" name="deposit_date" class="f-control"> --}}
                                    {{--                                    </div> --}}
                                    <div class="f-group f-col-full">
                                        <label class="f-label">Remarks (optional)</label>
                                        <textarea name="remarks" class="f-control" rows="2" placeholder="Any notes..."></textarea>
                                    </div>
                                    <div class="f-group f-col-full">
                                        <label class="f-label">Payment Screenshot</label>
                                        <div class="upload-zone" id="upload_zone">
                                            <input type="file" id="image-upload" name="attachment_id" accept="image/*">
                                            <div class="upload-icon">📸</div>
                                            <div class="upload-txt" id="upload_txt">Tap to upload screenshot</div>
                                        </div>
                                        <img class="image-demo" id="image_preview"
                                             style="max-height:140px;max-width:100%;border-radius:8px;border:1px solid #e2e8f0;margin-top:8px;display:none;">
                                    </div>
                                </div>
                            </div>

                            {{-- ── WITHDRAW FIELDS ── --}}
                            <div id="withdraw_fields" style="display:none">
                                <div
                                    style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:11px 14px;margin-bottom:14px;font-size:12px;color:#92400e;">
                                    ⚠️ Withdraw requests are processed within <strong>1–3 official work days</strong>.
                                </div>
                                <div class="f-group">
                                    <label class="f-label">Withdraw Method</label>
                                    <select name="withdraw_method" class="f-control">
                                        {{--                                        <option value="bkash"> bKash</option> --}}
                                        {{--                                        <option value="nagad"> Nagad</option> --}}
                                        {{--                                        <option value="rocket"> Rocket</option> --}}
                                        <option value="bank"> Bank </option>
                                    </select>
                                </div>
                                <div class="f-group">
                                    <label class="f-label">Your Account Number</label>
                                    <input type="text" name="account_number" class="f-control"
                                           placeholder="01XXXXXXXXX">
                                </div>
                                <div class="f-group">
                                    <label class="f-label">Note (optional)</label>
                                    <textarea name="note" class="f-control" rows="2" placeholder="Any special instructions..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="submit-btn" id="submit_btn">
                                <i class="fas fa-arrow-up-from-bracket"></i>
                                <span id="submit_txt">Submit Top Up</span>
                            </button>

                        </form>
                    </div>
                </div>
            </div>

            {{-- ════════ PAYMENT DETAILS COLUMN ════════ --}}
            <div class="payment-col">
                <div class="w-card">

                    {{-- Header --}}
                    <div style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);padding:14px 18px;">
                        <div style="font-size:13px;font-weight:700;color:white;letter-spacing:.02em;">💳 Send Payment To
                        </div>
                        <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;">Tap any method to expand
                            details</div>
                    </div>

                    {{-- Payment method sections --}}
                    @foreach ($paymentMethods as $idx => $method)
                        {{-- Section Header (clickable) --}}
                        <div class="pay-section-hd" onclick="togglePaySection('paysec_{{ $method['id'] }}', this)">
                            <div class="pay-section-icon" style="background:{{ $method['bg'] }}">
                                <img src="{{ asset($method['icon']) }}" alt="{{ $method['name'] }}"
                                     style="width:40px;height:40px;object-fit:contain;">

                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="pay-section-name">{{ $method['name'] }}</div>
                                <div class="pay-section-num">{{ $method['number'] }}</div>
                            </div>
                            {{-- @if ($method['type'] === 'mobile')
                                <button onclick="event.stopPropagation();copyText('{{ $method['number'] }}', this)"
                                    style="background:{{ $method['bg'] }};border:1px solid {{ $method['border'] }};color:{{ $method['color'] }};border-radius:7px;padding:5px 11px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;margin-right:8px;transition:all .15s;"
                                    onmouseover="this.style.background='{{ $method['color'] }}';this.style.color='white'"
                                    onmouseout="this.style.background='{{ $method['bg'] }}';this.style.color='{{ $method['color'] }}'">
                                    <i class="fa fa-copy"></i> Copy
                                </button>
                            @endif --}}
                            <i class="fa fa-chevron-down pay-chevron"></i>
                        </div>

                        {{-- Section Body --}}
                        <div class="pay-section-body" id="paysec_{{ $method['id'] }}">
                            @if ($method['type'] === 'mobile')
                                {{-- Mobile money --}}
                                <div
                                    style="padding-left:50px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                    <div>

                                        <div
                                            style="font-size:22px;font-weight:800;color:#1e293b;font-family:monospace;letter-spacing:.05em;">
                                            {{ $method['number'] }}</div>
                                        <div
                                            style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">
                                            {{ $method['sub'] }}</div>
                                    </div>
                                    <button onclick="copyText('{{ $method['number'] }}', this)"
                                            style="background:{{ $method['bg'] }};border:1.5px solid {{ $method['border'] }};color:{{ $method['color'] }};border-radius:8px;padding:8px 18px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;"
                                            onmouseover="this.style.background='{{ $method['color'] }}';this.style.color='white'"
                                            onmouseout="this.style.background='{{ $method['bg'] }}';this.style.color='{{ $method['color'] }}'">
                                        <i class="fa fa-copy"></i> Copy
                                    </button>
                                </div>
                            @else
                                {{-- Bank --}}
                                <div style="display:flex;flex-direction:column;gap:0">
                                    @foreach ($method['fields'] as $field)
                                        <div class="detail-row">
                                            <span class="detail-label">{{ $field['label'] }}</span>
                                            <span
                                                class="detail-val {{ $field['mono'] ? 'mono' : '' }}">{{ $field['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <button
                                    onclick="copyText('{{ collect($method['fields'])->firstWhere('label', 'Account No.')['value'] ?? $method['number'] }}', this)"
                                    style="background:{{ $method['bg'] }};border:1.5px solid {{ $method['border'] }};color:{{ $method['color'] }};border-radius:8px;padding:7px 16px;font-size:11.5px;font-weight:700;cursor:pointer;margin-top:10px;transition:all .15s;"
                                    onmouseover="this.style.background='{{ $method['color'] }}';this.style.color='white'"
                                    onmouseout="this.style.background='{{ $method['bg'] }}';this.style.color='{{ $method['color'] }}'">
                                    <i class="fa fa-copy"></i> Copy Account No.
                                </button>
                            @endif
                        </div>
                    @endforeach

                    {{-- Note --}}
                    <div class="note-bar">
                        ⚠️ After payment, submit the form with your <strong>Transaction ID</strong> and screenshot.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const amountInput = document.getElementById('amount');
            const amountLabel = document.getElementById('amount_label');
            const submitTxt = document.getElementById('submit_txt');
            const typeInput = document.getElementById('type');
            const formTitle = document.getElementById('form_card_title');
            const balance = parseFloat("{{ $users->credit_balance }}");

            // ── Tab switch ──
            window.switchTab = function(tab) {
                const isWithdraw = tab === 'withdraw';
                document.getElementById('deposit_fields').style.display = isWithdraw ? 'none' : 'block';
                document.getElementById('withdraw_fields').style.display = isWithdraw ? 'block' : 'none';
                typeInput.value = tab;

                document.getElementById('tab_deposit').classList.toggle('active', !isWithdraw);
                document.getElementById('tab_withdraw').classList.toggle('active', isWithdraw);

                amountLabel.innerText = isWithdraw ? 'Withdraw Amount (৳)' : 'Credit Amount (৳)';
                submitTxt.innerText = isWithdraw ? 'Request Withdraw' : 'Submit Top Up';
                formTitle.innerText = isWithdraw ? 'Withdraw Request' : 'Add Credit';
                amountInput.value = '';
            };

            // ── Amount validation ──
            amountInput.addEventListener('input', function() {
                if (typeInput.value === 'withdraw' && parseFloat(this.value) > balance) {
                    this.value = balance;
                    this.style.borderColor = '#dc2626';
                    this.style.boxShadow = '0 0 0 3px rgba(220,38,38,.12)';
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }, 1500);
                }
            });

            // ── Image preview ──
            const fileInput = document.getElementById('image-upload');
            const imgPreview = document.getElementById('image_preview');
            const uploadTxt = document.getElementById('upload_txt');

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            imgPreview.src = ev.target.result;
                            imgPreview.style.display = 'block';
                            uploadTxt.innerText = '✅ ' + file.name;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        imgPreview.style.display = 'none';
                        uploadTxt.innerText = 'Tap to upload screenshot';
                    }
                });
            }
        });

        // ── Collapsible payment sections (accordion) ──
        function togglePaySection(id, header) {
            const body = document.getElementById(id);
            const isOpen = body.classList.contains('open');
            document.querySelectorAll('.pay-section-body.open').forEach(el => el.classList.remove('open'));
            document.querySelectorAll('.pay-section-hd.open').forEach(el => el.classList.remove('open'));
            if (!isOpen) {
                body.classList.add('open');
                header.classList.add('open');
            }
        }

        // ── Copy to clipboard ──
        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(function() {
                const orig = btn.innerHTML;
                const origBg = btn.style.background;
                const origColor = btn.style.color;
                const origBorder = btn.style.borderColor;

                btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                btn.style.background = '#16a34a';
                btn.style.color = 'white';
                btn.style.borderColor = '#16a34a';

                setTimeout(function() {
                    btn.innerHTML = orig;
                    btn.style.background = origBg;
                    btn.style.color = origColor;
                    btn.style.borderColor = origBorder;
                }, 2000);
            }).catch(function() {
                // Fallback for older browsers
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = orig;
                }, 2000);
            });
        }

        const txTypeSelect = document.getElementById('transaction_type_select');
        const bankNameGroup = document.getElementById('bank_name_group');
        const bankNameSelect = document.getElementById('bank_name_select');

        if (txTypeSelect) {
            txTypeSelect.addEventListener('change', function() {
                const isBank = this.value === 'bank';
                bankNameGroup.style.display = isBank ? 'block' : 'none';
                if (!isBank) bankNameSelect.value = '';
            });
        }
    </script>
@endsection
