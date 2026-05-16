@extends('admin.layouts.app')

@push('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
    <style>
        :root {
            --primary:#2563eb; --primary-lt:#eff6ff;
            --success:#059669; --success-lt:#ecfdf5;
            --warn:#d97706;    --warn-lt:#fffbeb;
            --danger:#dc2626;  --danger-lt:#fef2f2;
            --muted:#6b7280;   --border:#e5e7eb;
            --surface:#ffffff; --bg:#f3f4f6;
            --text:#111827;    --radius:10px;
            --shadow:0 1px 3px rgba(0,0,0,.08);
        }
        body, .content-area { background:var(--bg) !important; font-family:'DM Sans',sans-serif; }
        .pg-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .pg-title { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .pg-title .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; }
        .filter-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:10px 16px; margin-bottom:20px; box-shadow:var(--shadow); display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .btn-clear { height:32px; padding:0 10px; font-size:12px; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; }
        .btn-clear:hover { background:var(--bg); color:var(--text); }
        .filter-loading { display:none; font-size:12px; color:var(--muted); }
        /* Search input */
        #dt-search-input { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; outline:none; width:160px; background:var(--bg); }
        #dt-search-input:focus { border-color:var(--primary); background:#fff; }
        /* Export buttons */
        .btn-excel  { background:#059669 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }
        .btn-pdf    { background:#dc2626 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }
        .btn-print2 { background:#475569 !important; color:#fff !important; height:32px !important; padding:0 12px !important; font-size:12px !important; font-weight:600 !important; border-radius:7px !important; display:inline-flex !important; align-items:center !important; gap:5px !important; border:none !important; }
        .tx-panel { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
        .table-scroll { overflow-x:auto; }
        #transactionTable { width:100% !important; }
        #transactionTable thead th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); padding:10px 12px; border-bottom:2px solid var(--border); border-top:none; white-space:nowrap; }
        #transactionTable tbody tr { border-bottom:1px solid var(--border); transition:background .1s; }
        #transactionTable tbody tr:last-child { border-bottom:none; }
        #transactionTable tbody tr:hover { background:#f8fafc; }
        #transactionTable tbody td { padding:10px 12px; vertical-align:middle; border:none; font-size:13px; }
        .tx-name a { color:var(--primary); text-decoration:none; font-weight:600; border-bottom:1px dashed rgba(37,99,235,.3); }
        .tx-name a:hover { color:#1d4ed8; }
        .amount-pos { color:var(--success); font-weight:700; font-family:'DM Mono',monospace; }
        .amount-neg { color:var(--danger); font-weight:700; font-family:'DM Mono',monospace; }
        .type-chip { display:inline-flex; align-items:center; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; }
        .type-deposit  { background:var(--success-lt); color:var(--success); }
        .type-withdraw { background:var(--danger-lt);  color:var(--danger); }
        .type-debit    { background:#f5f3ff;            color:#7c3aed; }
        .type-credit   { background:var(--primary-lt); color:var(--primary); }
        .type-other    { background:#f1f5f9;            color:#475569; }
        .status-chip { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
        .s-pending   { background:var(--warn-lt);    color:var(--warn); }
        .s-confirmed { background:var(--success-lt); color:var(--success); }
        .s-payment   { background:var(--danger-lt);  color:var(--danger); }
        .s-completed { background:var(--primary-lt); color:var(--primary); }
        .s-default   { background:#f1f5f9;            color:#475569; }
        .row-num { font-family:'DM Mono',monospace; font-size:11px; color:var(--muted); }
        .date-main { font-size:12px; font-weight:600; font-family:'DM Mono',monospace; }
        .remarks-text:hover { color:var(--primary) !important; }
        #transactionTable_wrapper .dataTables_info { font-size:12px; color:var(--muted); }
        .fade-up { opacity:0; transform:translateY(10px); animation:fadeUp .3s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.03s}.fade-up:nth-child(2){animation-delay:.06s}
        .fade-up:nth-child(3){animation-delay:.09s}.fade-up:nth-child(4){animation-delay:.12s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        <div class="pg-header fade-up">
            <h1 class="pg-title">
                <span class="icon-wrap"><i class="fa fa-exchange"></i></span>
                Credit Transactions
            </h1>
        </div>

        @include('admin.message')

        {{-- Filter --}}
        <div class="filter-card fade-up">
            <input type="date" id="filter-date-from" value="{{ $dateFrom }}"
                   onchange="onDateChange()"
                   style="height:32px;font-size:12px;border:1px solid var(--border);border-radius:7px;padding:0 10px;color:var(--text);background:var(--bg);outline:none;font-family:'DM Mono',monospace;width:140px;">
            <span style="font-size:12px;color:var(--muted);">—</span>
            <input type="date" id="filter-date-to" value="{{ $dateTo }}"
                   onchange="onDateChange()"
                   style="height:32px;font-size:12px;border:1px solid var(--border);border-radius:7px;padding:0 10px;color:var(--text);background:var(--bg);outline:none;font-family:'DM Mono',monospace;width:140px;">
            <button type="button" class="btn-clear" id="btn-clear-filter">
                <i class="fa fa-times"></i>
            </button>
            <div class="filter-loading" id="filter-loading">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            {{-- Export buttons — injected once by JS --}}
            <div id="export-btn-slot" style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap;"></div>
            {{-- Search — static, never moved --}}
            <input type="text" id="dt-search-input" placeholder="🔍 Search...">
        </div>

        {{-- Table --}}
        <div class="tx-panel fade-up">
            <div class="table-scroll">
                <table id="transactionTable" class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:45px">#</th>
                        <th style="width:90px">MR No</th>
                        <th>Name</th>
                        <th style="width:110px">Date</th>
                        <th style="width:130px" class="text-right">Receivable</th>
                        <th style="width:130px" class="text-right">Payable</th>
                        <th>Created By</th>
                        <th>Approved By</th>
                        <th style="width:90px">Type</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th style="width:80px" class="text-center">Image</th>
                        <th style="width:100px" class="text-center">Status</th>
                        <th>Remarks</th>
                        <th style="width:140px" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody id="table-body">
                    @forelse($rows as $row)
                        @php
                            $isDeposit  = in_array($row->type, ['deposit','topup']);
                            $isWithdraw = in_array($row->type, ['withdraw','debit','payment','credit']);
                            $typeClass  = match($row->type) { 'deposit','topup'=>'type-deposit','withdraw'=>'type-withdraw','debit'=>'type-debit','credit'=>'type-credit',default=>'type-other' };
                            $statusClass = match($row->status) { 'pending'=>'s-pending','confirmed'=>'s-confirmed','payment'=>'s-payment','completed'=>'s-completed',default=>'s-default' };
                        @endphp
                        <tr>
                            <td><span class="row-num">{{ $row->id }}</span></td>
                            <td><span style="font-family:'DM Mono',monospace;font-size:12px;font-weight:600;color:var(--primary);">MR-{{ $row->id }}</span></td>
                            <td><div class="tx-name"><a href="{{ route('user.admin.wallet.list', $row->user_id) }}">{{ $row->author->name ?? 'N/A' }}</a></div></td>
                            <td><div class="date-main">{{ $row->deposit_date ?? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}</div></td>
                            <td class="text-right">
                                @if($isDeposit)<span class="amount-pos">৳{{ number_format($row->amount, 2) }}</span>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                            <td class="text-right">
                                @if($isWithdraw)<span class="amount-neg">৳{{ number_format($row->amount, 2) }}</span>
                                @else<span class="text-muted">—</span>@endif
                            </td>
                            <td style="font-size:12px;">{{ $row->creator->name ?? '—' }}</td>
                            <td style="font-size:12px;">{{ $row->update_user ? ($row->updater->name ?? '—') : '—' }}</td>
                            <td><span class="type-chip {{ $typeClass }}">{{ ucfirst($row->type) }}</span></td>
                            <td style="font-size:12px;">{{ $row->transaction_type ?? '—' }}</td>
                            <td style="font-size:12px;color:var(--muted);">{{ $row->reference ?? '—' }}</td>
                            <td class="text-center">
                                @if($row->attachment_id)
                                    <img src="{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb') }}"
                                         style="width:46px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid var(--border);"
                                         onclick="showBigImage('{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full') }}')">
                                @else<span class="text-muted">—</span>@endif
                            </td>
                            <td class="text-center"><span class="status-chip {{ $statusClass }}">{{ ucfirst($row->status) }}</span></td>
                            <td>
                            <span class="remarks-text text-muted" style="font-size:12px;cursor:pointer;"
                                  @if(auth()->user()->hasPermission('transaction_edit_remarks'))
                                      onclick="openRemarksModal({{ $row->id }}, '{{ addslashes($row->remarks ?? '') }}')"
                                  @endif>
                                {{ $row->remarks ?? '—' }}
                                @if(auth()->user()->hasPermission('transaction_edit_remarks'))
                                    <i class="fa fa-pencil fa-xs text-muted ml-1"></i>
                                @endif
                            </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center" style="gap:4px;">
                                    @if(auth()->user()->hasPermission('transaction_approved'))
                                        @if($row->status == 'pending')
                                            <a href="{{ route('user.admin.wallet.update', ['id' => $row->id]) }}"
                                               class="btn btn-success btn-sm px-2"
                                               onclick="return confirm('Approve this transaction?')">
                                                <i class="fa fa-check"></i>
                                            </a>
                                        @else
                                            <span class="badge badge-light text-muted border px-2">Done</span>
                                        @endif
                                    @endif
                                    @if(auth()->user()->hasPermission('transaction_edit_amount'))
                                        <button class="btn btn-warning btn-sm px-2" onclick="openAmountModal({{ $row->id }}, {{ $row->amount }})">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('transaction_delete'))
                                        <button class="btn btn-danger btn-sm px-2 btn-delete-transaction" data-id="{{ $row->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->hasPermission('transaction_print_receipt'))
                                        @if($row->status == 'confirmed')
                                            <button class="btn btn-info btn-sm px-2 btn-print-receipt"
                                                    data-id="{{ $row->id }}"
                                                    data-name="{{ $row->author->name ?? 'N/A' }}"
                                                    data-date="{{ $row->deposit_date ?? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}"
                                                    data-amount="{{ number_format($row->amount, 2) }}"
                                                    data-method="{{ $row->transaction_type ?? '' }}"
                                                    data-reference="{{ $row->reference ?? '' }}"
                                                    data-remarks="{{ $row->remarks ?? '' }}"
                                                    data-type="{{ $row->type ?? '' }}"
                                                    data-phone="{{ $row->author->phone ?? '' }}"
                                                    data-email="{{ $row->author->email ?? '' }}"
                                                    data-words="{{ \App\Helpers\AmountHelper::inWords($row->amount) }}">
                                                <i class="fa fa-print"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-5">
                                <i class="fa fa-inbox" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;color:var(--muted);"></i>
                                <span style="font-size:13px;color:var(--muted);">No transactions found for this date range</span>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top" id="dt-footer-slot" style="font-size:12px;color:var(--muted);"></div>
        </div>

    </div>

    {{-- Image Overlay --}}
    <div id="imageOverlay" onclick="closeBigImage()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);z-index:99999;cursor:pointer;">
        <span style="position:absolute;top:20px;right:40px;color:#fff;font-size:36px;">&times;</span>
        <img id="bigImage" src="" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:90%;max-height:90%;border:3px solid #fff;border-radius:8px;">
    </div>

    {{-- Receipt Modal --}}
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width:1100px;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-print"></i> Money Receipt</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div id="receipt-print-area">
                        <style>
                            #receipt-print-area{font-family:'Arial',sans-serif;background:#fff;color:#222;padding:12px;}
                            .receipts-row{display:flex;gap:12px;}.receipt-col{flex:1;min-width:0;}
                            .receipt-divider{width:1px;background:repeating-linear-gradient(to bottom,#ccc 0,#ccc 6px,transparent 6px,transparent 12px);flex-shrink:0;}
                            .receipt-wrapper{border:2px solid #e85d24;border-top:6px solid #e85d24;}
                            .copy-label{text-align:center;font-size:10px;font-weight:800;letter-spacing:.15em;text-transform:uppercase;padding:4px;margin-bottom:6px;border-radius:4px;}
                            .copy-label.client{background:#fff3e0;color:#e85d24;border:1px solid #e85d24;}
                            .copy-label.office{background:#e8eaf6;color:#1a2a5e;border:1px solid #1a2a5e;}
                            .receipt-header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px 10px;border-bottom:2px solid #1a2a5e;background:#fff;}
                            .receipt-header .logo-area img{height:42px;object-fit:contain;}
                            .receipt-title-area{text-align:right;}
                            .receipt-title-area h2{font-size:16px;font-weight:800;color:#e85d24;letter-spacing:2px;margin:0;text-transform:uppercase;}
                            .receipt-title-area .mr-no{font-size:11px;color:#1a2a5e;font-weight:600;margin-top:3px;}
                            .receipt-address{background:#1a2a5e;color:#fff;font-size:9px;padding:4px 16px;text-align:center;}
                            .receipt-body{padding:12px 16px;}
                            .receipt-thanks{font-size:11px;color:#555;margin-bottom:10px;border-bottom:1px dashed #ddd;padding-bottom:8px;}
                            .receipt-row{display:flex;margin-bottom:7px;font-size:11px;align-items:flex-end;}
                            .receipt-row .r-label{min-width:140px;font-weight:600;color:#333;white-space:nowrap;}
                            .receipt-row .r-dots{flex:1;border-bottom:1px dashed #bbb;margin:0 6px 3px;min-width:20px;}
                            .receipt-row .r-value{min-width:100px;color:#222;font-size:11px;}
                            .receipt-amount-box{background:#f8f8f8;border:1px solid #e85d24;border-radius:4px;padding:8px 12px;margin:10px 0 6px;display:flex;justify-content:space-between;align-items:center;}
                            .receipt-amount-box .amount-label{font-size:11px;font-weight:700;color:#1a2a5e;text-transform:uppercase;letter-spacing:1px;}
                            .amount-words-line{font-size:10px;color:#1a2a5e;font-weight:600;margin-top:3px;font-style:italic;}
                            .receipt-note{font-size:9px;color:#e85d24;font-style:italic;margin-top:3px;}
                            .receipt-taka-box{border:1px solid #ccc;padding:3px 10px;font-size:14px;font-weight:800;color:#e85d24;border-radius:3px;white-space:nowrap;}
                            .receipt-footer{display:flex;justify-content:space-between;padding:10px 16px;border-top:2px solid #1a2a5e;margin-top:6px;}
                            .receipt-footer .sign-box{text-align:center;min-width:80px;}
                            .receipt-footer .sign-box .sign-line{border-top:1px solid #333;padding-top:3px;margin-top:20px;font-size:9px;font-weight:700;color:#333;text-transform:uppercase;letter-spacing:1px;}
                            @media print{body *{visibility:hidden;}#receipt-print-area,#receipt-print-area *{visibility:visible;}#receipt-print-area{position:fixed;left:0;top:0;width:100%;padding:8px;}.modal-footer{display:none !important;}@page{size:A4 landscape;margin:8mm;}}
                        </style>
                        <div class="receipts-row">
                            <div class="receipt-col">
                                <div class="copy-label client">✂ Client Copy</div>
                                <div class="receipt-wrapper">
                                    <div class="receipt-header"><div class="logo-area"><img class="receipt-logo-img" src="" alt="Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"><strong style="display:none;font-size:15px;color:#1a2a5e;font-weight:800;">SHOPNO TOUR</strong></div><div class="receipt-title-area"><h2>Money Receipt</h2><div class="mr-no">MR No: <span class="receipt-mr-no" style="color:#e85d24;">—</span> &nbsp; Date: <span class="receipt-date">—</span></div></div></div>
                                    <div class="receipt-address">1/8, Green Square (Level 3A), Road-8, Gulshan-1, Dhaka-1212 &nbsp;|&nbsp; Phone: 09639101030 &nbsp;|&nbsp; shopnotourbd@gmail.com &nbsp;|&nbsp; www.shopnotour.com</div>
                                    <div class="receipt-body">
                                        <div class="receipt-thanks">Received with thanks from <strong class="receipt-name-thanks">—</strong></div>
                                        <div class="receipt-row"><span class="r-label">Client / B2B Name</span><span class="r-dots"></span><span class="r-value receipt-name">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Payment Method</span><span class="r-dots"></span><span class="r-value receipt-payment-method">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Reference / Cheque No</span><span class="r-dots"></span><span class="r-value receipt-reference">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Date</span><span class="r-dots"></span><span class="r-value receipt-sells-date">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Remarks</span><span class="r-dots"></span><span class="r-value receipt-remarks">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Type</span><span class="r-dots"></span><span class="r-value receipt-type">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Contact</span><span class="r-dots"></span><span class="r-value receipt-contact">—</span></div>
                                        <div class="receipt-amount-box"><div><div class="amount-label">Total Amount (Taka)</div><div class="amount-words-line">In Words: <span class="receipt-amount-words">—</span></div><div class="receipt-note">&#9733; Visa fee & All booking amount non-refundable</div></div><div class="receipt-taka-box receipt-amount">0.00</div></div>
                                    </div>
                                    <div class="receipt-footer"><div class="sign-box"><div class="sign-line">Received By</div></div><div class="sign-box"><div class="sign-line">Accounts By</div></div><div class="sign-box"><div class="sign-line">Authorized By</div></div></div>
                                </div>
                            </div>
                            <div class="receipt-divider"></div>
                            <div class="receipt-col">
                                <div class="copy-label office">🏢 Office Copy</div>
                                <div class="receipt-wrapper">
                                    <div class="receipt-header"><div class="logo-area"><img class="receipt-logo-img" src="" alt="Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"><strong style="display:none;font-size:15px;color:#1a2a5e;font-weight:800;">SHOPNO TOUR</strong></div><div class="receipt-title-area"><h2>Money Receipt</h2><div class="mr-no">MR No: <span class="receipt-mr-no" style="color:#e85d24;">—</span> &nbsp; Date: <span class="receipt-date">—</span></div></div></div>
                                    <div class="receipt-address">1/8, Green Square (Level 3A), Road-8, Gulshan-1, Dhaka-1212 &nbsp;|&nbsp; Phone: 09639101030 &nbsp;|&nbsp; shopnotourbd@gmail.com &nbsp;|&nbsp; www.shopnotour.com</div>
                                    <div class="receipt-body">
                                        <div class="receipt-thanks">Received with thanks from <strong class="receipt-name-thanks">—</strong></div>
                                        <div class="receipt-row"><span class="r-label">Client / B2B Name</span><span class="r-dots"></span><span class="r-value receipt-name">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Payment Method</span><span class="r-dots"></span><span class="r-value receipt-payment-method">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Reference / Cheque No</span><span class="r-dots"></span><span class="r-value receipt-reference">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Date</span><span class="r-dots"></span><span class="r-value receipt-sells-date">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Remarks</span><span class="r-dots"></span><span class="r-value receipt-remarks">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Type</span><span class="r-dots"></span><span class="r-value receipt-type">—</span></div>
                                        <div class="receipt-row"><span class="r-label">Contact</span><span class="r-dots"></span><span class="r-value receipt-contact">—</span></div>
                                        <div class="receipt-amount-box"><div><div class="amount-label">Total Amount (Taka)</div><div class="amount-words-line">In Words: <span class="receipt-amount-words">—</span></div><div class="receipt-note">&#9733; Visa fee & All booking amount non-refundable</div></div><div class="receipt-taka-box receipt-amount">0.00</div></div>
                                    </div>
                                    <div class="receipt-footer"><div class="sign-box"><div class="sign-line">Received By</div></div><div class="sign-box"><div class="sign-line">Accounts By</div></div><div class="sign-box"><div class="sign-line">Authorized By</div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                    @if(auth()->user()->hasPermission('transaction_print_receipt'))
                        <button type="button" class="btn btn-danger" id="btn-download-pdf" onclick="downloadReceiptPDF(this)"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
                    @endif
                    <button type="button" class="btn btn-primary" onclick="printReceipt()"><i class="fa fa-print"></i> Print Both Copies</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Remarks Modal --}}
    <div class="modal fade" id="remarksModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fa fa-pencil"></i> Edit Remarks</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><input type="hidden" id="remarks_transaction_id"><div class="form-group"><label>Remarks</label><textarea id="remarks_input" class="form-control" rows="4"></textarea></div></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="btn-save-remarks"><i class="fa fa-check"></i> Save</button></div>
            </div></div></div>

    {{-- Amount Modal --}}
    <div class="modal fade" id="amountModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                <div class="modal-header bg-warning"><h5 class="modal-title"><i class="fa fa-edit"></i> Edit Amount</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body"><input type="hidden" id="amount_transaction_id"><div class="form-group"><label>Amount</label><input type="number" id="amount_input" class="form-control form-control-lg" min="0" step="0.01"></div></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-warning" id="btn-save-amount"><i class="fa fa-check"></i> Save</button></div>
            </div></div></div>

@endsection
<script>
    var permissions = {
        canApprove     : {{ auth()->user()->hasPermission('transaction_approved')      ? 'true' : 'false' }},
        canEditAmount  : {{ auth()->user()->hasPermission('transaction_edit_amount')   ? 'true' : 'false' }},
        canDelete      : {{ auth()->user()->hasPermission('transaction_delete')        ? 'true' : 'false' }},
        canPrintReceipt: {{ auth()->user()->hasPermission('transaction_print_receipt') ? 'true' : 'false' }},
        canEditRemarks : {{ auth()->user()->hasPermission('transaction_edit_remarks')  ? 'true' : 'false' }},
    };
</script>

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        var dtTable    = null;
        var filterUrl  = '{{ url()->current() }}';
        var csrfToken  = '{{ csrf_token() }}';
        var filterTimer = null;

        /* ── Date filter ── */
        function onDateChange() {
            var df = $('#filter-date-from').val();
            var dt = $('#filter-date-to').val();
            if (df && dt) {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function () { applyFilterGlobal(df, dt); }, 300);
            }
        }

        function applyFilterGlobal(df, dt) {
            $('#filter-loading').show();
            $.ajax({
                url:     filterUrl,
                method:  'GET',
                data:    { date_from: df, date_to: dt },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (res) {
                    if ($.fn.DataTable.isDataTable('#transactionTable')) {
                        $('#transactionTable').DataTable().destroy();
                        dtTable = null;
                    }
                    $('#table-body').html(buildRows(res.rows));
                    if (res.rows && res.rows.length > 0) {
                        setTimeout(function () { initDT(); }, 100);
                    }
                },
                error: function (xhr) {
                    console.error('Filter error', xhr.status, xhr.responseText);
                    alert('Filter failed (' + xhr.status + '). Check console.');
                },
                complete: function () { $('#filter-loading').hide(); }
            });
        }

        /* ── DataTable init ── */
        function initDT() {
            $.fn.dataTable.ext.errMode = 'none';
            if ($.fn.DataTable.isDataTable('#transactionTable')) {
                $('#transactionTable').DataTable().destroy();
            }
            var exportOpts = {
                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13],
                format:  { body: function (d, r, c, n) { return $(n).text().replace(/\s+/g, ' ').trim(); } }
            };
            dtTable = $('#transactionTable').DataTable({
                paging:   false,
                ordering: true,
                info:     false,
                searching: true,
                order:    [],
                dom:      '<"d-none"B>t',
                buttons: [
                    { extend: 'excelHtml5', className: 'buttons-excel', title: 'Credit Transactions', exportOptions: exportOpts },
                    { extend: 'pdfHtml5',   className: 'buttons-pdf',   title: 'Credit Transactions', orientation: 'landscape', pageSize: 'A4', exportOptions: exportOpts },
                    { extend: 'print',      className: 'buttons-print', title: 'Credit Transactions', exportOptions: exportOpts }
                ],
                columnDefs: [
                    { orderable: false, targets: [11, 14] },
                    { className: 'text-right',  targets: [4, 5] },
                    { className: 'text-center', targets: [0, 1, 11, 12, 14] },
                ],
            });

            $('#dt-search-input').off('keyup input').on('keyup input', function () {
                dtTable.search(this.value).draw();
            });
        }

        /* ── Build rows ── */
        function buildRows(rows) {
            if (!rows || rows.length === 0) {
                return '<tr><td colspan="15" class="text-center py-5">'
                    + '<i class="fa fa-inbox" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;color:#6b7280;"></i>'
                    + '<span style="font-size:13px;color:#6b7280;">No transactions found for this date range</span>'
                    + '</td></tr>';
            }

            var walletBase  = '{{ route("user.admin.wallet.list",   ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', '');
            var approveBase = '{{ route("user.admin.wallet.update", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', '');
            var typeMap   = { deposit: 'type-deposit', topup: 'type-deposit', withdraw: 'type-withdraw', debit: 'type-debit', payment: 'type-debit', credit: 'type-credit' };
            var statusMap = { pending: 's-pending', confirmed: 's-confirmed', payment: 's-payment', completed: 's-completed' };

            var html = '';
            $.each(rows, function (i, r) {
                var tc  = typeMap[r.type]     || 'type-other';
                var sc  = statusMap[r.status] || 's-default';
                var rec = r.is_deposit  ? '<span class="amount-pos">৳' + r.amount_formatted + '</span>' : '<span class="text-muted">—</span>';
                var pay = r.is_withdraw ? '<span class="amount-neg">৳' + r.amount_formatted + '</span>' : '<span class="text-muted">—</span>';
                var e   = function (v) { return $('<div>').text(v || '').html(); };

                /* ── Action buttons (permission-gated) ── */
                var act = '';

                if (permissions.canApprove) {
                    if (r.status === 'pending') {
                        act += '<a href="' + approveBase + r.id + '" class="btn btn-success btn-sm px-2" onclick="return confirm(\'Approve this transaction?\')"><i class="fa fa-check"></i></a>';
                    } else {
                        act += '<span class="badge badge-light text-muted border px-2">Done</span>';
                    }
                }

                if (permissions.canEditAmount) {
                    act += '<button class="btn btn-warning btn-sm px-2" onclick="openAmountModal(' + r.id + ',' + r.amount + ')"><i class="fa fa-pencil"></i></button>';
                }

                if (permissions.canDelete) {
                    act += '<button class="btn btn-danger btn-sm px-2 btn-delete-transaction" data-id="' + r.id + '"><i class="fa fa-trash"></i></button>';
                }

                if (permissions.canPrintReceipt && r.status === 'confirmed') {
                    act += '<button class="btn btn-info btn-sm px-2 btn-print-receipt"'
                        + ' data-id="'        + r.id                    + '"'
                        + ' data-name="'      + e(r.author_name)        + '"'
                        + ' data-date="'      + e(r.deposit_date)       + '"'
                        + ' data-amount="'    + e(r.amount_formatted)   + '"'
                        + ' data-method="'    + e(r.transaction_type)   + '"'
                        + ' data-reference="' + e(r.reference)          + '"'
                        + ' data-remarks="'   + e(r.remarks)            + '"'
                        + ' data-type="'      + e(r.type)               + '"'
                        + ' data-phone="'     + e(r.author_phone)       + '"'
                        + ' data-email="'     + e(r.author_email)       + '"'
                        + ' data-words="'     + e(r.amount_words || '') + '">'
                        + '<i class="fa fa-print"></i></button>';
                }

                /* ── Remarks cell (permission-gated) ── */
                var remarksHtml = '';
                if (permissions.canEditRemarks) {
                    remarksHtml = '<span class="remarks-text text-muted" style="font-size:12px;cursor:pointer;"'
                        + ' onclick="openRemarksModal(' + r.id + ', \'' + (r.remarks || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'") + '\')">'
                        + e(r.remarks || '—')
                        + ' <i class="fa fa-pencil fa-xs text-muted ml-1"></i></span>';
                } else {
                    remarksHtml = '<span class="remarks-text text-muted" style="font-size:12px;">' + e(r.remarks || '—') + '</span>';
                }

                /* ── Row HTML ── */
                html += '<tr>'
                    + '<td><span class="row-num">' + r.id + '</span></td>'
                    + '<td><span style="font-family:\'DM Mono\',monospace;font-size:12px;font-weight:600;color:var(--primary);">MR-' + r.id + '</span></td>'
                    + '<td><div class="tx-name"><a href="' + walletBase + r.user_id + '">' + e(r.author_name) + '</a></div></td>'
                    + '<td><div class="date-main">' + e(r.deposit_date) + '</div></td>'
                    + '<td class="text-right">'  + rec + '</td>'
                    + '<td class="text-right">'  + pay + '</td>'
                    + '<td style="font-size:12px;">'                    + e(r.creator_name)     + '</td>'
                    + '<td style="font-size:12px;">'                    + e(r.updater_name)     + '</td>'
                    + '<td><span class="type-chip ' + tc + '">'         + e(r.type.charAt(0).toUpperCase() + r.type.slice(1)) + '</span></td>'
                    + '<td style="font-size:12px;">'                    + e(r.transaction_type) + '</td>'
                    + '<td style="font-size:12px;color:var(--muted);">' + e(r.reference)        + '</td>'
                    + '<td class="text-center">' + (r.attachment_thumb
                        ? '<img src="' + r.attachment_thumb + '" style="width:46px;height:36px;object-fit:cover;border-radius:4px;cursor:pointer;border:1px solid var(--border);" onclick="showBigImage(\'' + r.attachment_url + '\')" onerror="this.parentElement.innerHTML=\'<span class=text-muted>\u2014</span>\'">'
                        : '<span class="text-muted">—</span>') + '</td>'
                    + '<td class="text-center"><span class="status-chip ' + sc + '">' + e(r.status.charAt(0).toUpperCase() + r.status.slice(1)) + '</span></td>'
                    + '<td>' + remarksHtml + '</td>'
                    + '<td class="text-center"><div class="d-flex justify-content-center" style="gap:4px;">' + act + '</div></td>'
                    + '</tr>';
            });
            return html;
        }

        /* ── Document ready ── */
        $(document).ready(function () {

            /* Export buttons */
            $('#export-btn-slot').prepend(
                '<div class="dt-buttons" style="display:inline-flex;gap:6px;">'
                + '<button type="button" class="btn btn-excel btn-export-excel"><i class="fa fa-file-excel-o"></i> Excel</button>'
                + '<button type="button" class="btn btn-pdf btn-export-pdf"><i class="fa fa-file-pdf-o"></i> PDF</button>'
                + '<button type="button" class="btn btn-print2 btn-export-print"><i class="fa fa-print"></i> Print</button>'
                + '</div>'
            );

            $(document).on('click', '.btn-export-excel', function () { dtTable ? dtTable.button('.buttons-excel').trigger() : alert('No data to export'); });
            $(document).on('click', '.btn-export-pdf',   function () { dtTable ? dtTable.button('.buttons-pdf').trigger()   : alert('No data to export'); });
            $(document).on('click', '.btn-export-print', function () { dtTable ? dtTable.button('.buttons-print').trigger() : alert('No data to export'); });

            /* Clear / today filter */
            $('#btn-clear-filter').on('click', function () {
                $('#filter-date-from').val('{{ date("Y-m-d") }}');
                $('#filter-date-to').val('{{ date("Y-m-d") }}');
                applyFilterGlobal('{{ date("Y-m-d") }}', '{{ date("Y-m-d") }}');
            });

            /* Date change */
            $('#filter-date-from, #filter-date-to').on('change input', function () { onDateChange(); });

            /* Print receipt */
            $(document).on('click', '.btn-print-receipt', function () {
                var b = $(this);
                openReceiptModal(b.data('id'), b.data('name'), b.data('date'), b.data('amount'),
                    b.data('method'), b.data('reference'), b.data('remarks'), b.data('type'),
                    b.data('phone'), b.data('email'), b.data('words'));
            });

            /* Save remarks */
            $('#btn-save-remarks').on('click', function () {
                var id = $('#remarks_transaction_id').val(), remarks = $('#remarks_input').val().trim();
                if (!remarks) { alert('Please enter remarks'); return; }
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: '{{ route("user.admin.wallet.update.remarks", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method: 'POST', data: { remarks: remarks, _token: csrfToken },
                    success:  function (r) { if (r.success) { alert('✅ ' + r.message); $('#remarksModal').modal('hide'); location.reload(); } else alert('❌ ' + r.message); },
                    complete: function ()  { $('#btn-save-remarks').prop('disabled', false).html('<i class="fa fa-check"></i> Save'); }
                });
            });

            /* Save amount */
            $('#btn-save-amount').on('click', function () {
                var id = $('#amount_transaction_id').val(), amount = $('#amount_input').val();
                if (!amount || parseFloat(amount) <= 0) { alert('Please enter valid amount'); return; }
                if (!confirm('Update amount to ৳' + parseFloat(amount).toFixed(2) + '?')) return;
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: '{{ route("user.admin.wallet.update.amount", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method: 'POST', data: { amount: amount, _token: csrfToken },
                    success:  function (r) { if (r.success) { alert('✅ ' + r.message); $('#amountModal').modal('hide'); location.reload(); } else alert('❌ ' + r.message); },
                    complete: function ()  { $('#btn-save-amount').prop('disabled', false).html('<i class="fa fa-check"></i> Save'); }
                });
            });

            /* Delete transaction */
            $(document).on('click', '.btn-delete-transaction', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this transaction?')) return;
                var btn = $(this); btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: '{{ route("user.admin.wallet.delete", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method: 'POST', data: { _method: 'DELETE', _token: csrfToken },
                    success: function (r) {
                        if (r.success) { alert('✅ ' + r.message); location.reload(); }
                        else { alert('❌ ' + r.message); btn.prop('disabled', false).html('<i class="fa fa-trash"></i>'); }
                    },
                    error: function () { btn.prop('disabled', false).html('<i class="fa fa-trash"></i>'); }
                });
            });
        });

        /* ── Helpers ── */
        function printReceipt() {
            var ps = document.createElement('style'); ps.id = 'receipt-print-style';
            ps.innerHTML = '@media print{body>*:not(#receipt-modal-wrapper){display:none!important;}#receipt-modal-wrapper{display:block!important;position:fixed;top:0;left:0;width:100%;z-index:99999;background:#fff;}.modal,.modal-dialog,.modal-content,.modal-body{display:block!important;position:static!important;width:100%!important;max-width:100%!important;margin:0!important;padding:0!important;border:none!important;box-shadow:none!important;overflow:visible!important;}.modal-header,.modal-footer{display:none!important;}#receipt-print-area{display:block!important;padding:5px;}.receipts-row{display:flex!important;gap:10px!important;}.receipt-col{flex:1!important;}.receipt-divider{width:1px!important;}@page{size:A4 landscape;margin:8mm;}}';
            document.head.appendChild(ps);
            var wr = document.createElement('div'); wr.id = 'receipt-modal-wrapper';
            wr.innerHTML = document.getElementById('receipt-print-area').outerHTML;
            document.body.appendChild(wr);
            window.print();
            setTimeout(function () {
                var e = document.getElementById('receipt-print-style'); if (e) e.remove();
                var w = document.getElementById('receipt-modal-wrapper'); if (w) w.remove();
            }, 500);
        }

        function downloadReceiptPDF(btn) {
            btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
            var clone = document.getElementById('receipt-print-area').cloneNode(true);
            clone.id = 'receipt-clone-pdf';
            clone.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1050px;background:#fff;z-index:-1;padding:10px;';
            document.body.appendChild(clone);
            html2canvas(clone, { scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false, width: 1050 }).then(function (canvas) {
                var el = document.getElementById('receipt-clone-pdf'); if (el) document.body.removeChild(el);
                var imgData = canvas.toDataURL('image/png');
                var { jsPDF } = window.jspdf;
                var pdf = new jsPDF('l', 'mm', 'a4');
                var pdfW = pdf.internal.pageSize.getWidth(), pdfH = pdf.internal.pageSize.getHeight();
                var ratio = Math.min(pdfW / canvas.width, pdfH / canvas.height);
                pdf.addImage(imgData, 'PNG', (pdfW - canvas.width * ratio) / 2, (pdfH - canvas.height * ratio) / 2, canvas.width * ratio, canvas.height * ratio);
                pdf.save('money-receipt-' + ($('.receipt-mr-no').first().text() || 'receipt') + '.pdf');
                btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Download PDF';
            }).catch(function () {
                var el = document.getElementById('receipt-clone-pdf'); if (el) document.body.removeChild(el);
                alert('PDF generation failed.');
                btn.disabled = false; btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Download PDF';
            });
        }

        function openAmountModal(id, amount) { $('#amount_transaction_id').val(id); $('#amount_input').val(amount); $('#amountModal').modal('show'); }
        function openRemarksModal(id, remarks) { $('#remarks_transaction_id').val(id); $('#remarks_input').val(remarks); $('#remarksModal').modal('show'); }
        function showBigImage(src) { document.getElementById('bigImage').src = src; document.getElementById('imageOverlay').style.display = 'block'; }
        function closeBigImage()   { document.getElementById('imageOverlay').style.display = 'none'; }
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeBigImage(); });

        var siteLogo = '{{ setting_item("site_logo") ? \Modules\Media\Helpers\FileHelper::url(setting_item("site_logo"), "full") : asset("images/logo.png") }}';
        function openReceiptModal(id, name, date, amount, pm, ref, remarks, type, phone, email, words) {
            $('.receipt-mr-no').text(id);
            $('.receipt-date').text(date);
            $('.receipt-name, .receipt-name-thanks').text(name || '—');
            $('.receipt-sells-date').text(date);
            $('.receipt-amount').text('৳' + amount);
            $('.receipt-amount-words').text(words || '—');
            $('.receipt-payment-method').text(pm || '—');
            $('.receipt-reference').text(ref || '—');
            $('.receipt-remarks').text(remarks || '—');
            $('.receipt-type').text(type || '—');
            var c = []; if (phone) c.push(phone); if (email) c.push(email);
            $('.receipt-contact').text(c.length ? c.join(' | ') : '—');
            $('.receipt-logo-img').attr('src', siteLogo);
            $('#receiptModal').modal('show');
        }
    </script>
@endpush
