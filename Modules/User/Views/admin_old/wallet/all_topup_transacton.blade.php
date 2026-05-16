@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-4 py-3">

        {{-- PAGE HEADER --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-wallet me-2 text-primary"></i>Credit Transactions
                </h2>
                <p class="text-muted mb-0 small">Top Up Logs</p>
            </div>
        </div>

        @include('admin.message')

        {{-- ✅ Status Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
                <select name="status" class="form-control" style="min-width:160px;" onchange="this.form.submit()">
                    <option value="">-- All Status --</option>
                    @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'payment' => 'Payment'] as $val => $label)
                        <option value="{{ $val }}" {{ ($selectedStatus ?? '') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($selectedStatus))
                    <a href="{{ route('user.admin.wallet.transactions') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Clear
                    </a>
                @endif
            </form>
            <span class="text-muted small">
            <i>Found {{ $rows->count() }} items</i>
        </span>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-list me-2 text-primary"></i>Top Up Logs
                    <span class="badge bg-secondary ms-1">{{ $rows->count() }}</span>
                </h6>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="transactionTable" style="width:100%">
                        <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width:45px">#</th>
                            <th style="min-width:130px">Name</th>
                            <th style="min-width:110px">Date</th>
                            <th class="text-end" style="min-width:110px">Receivable</th>
                            <th class="text-end" style="min-width:110px">Payable</th>
                            <th style="min-width:120px">Created By</th>
                            <th style="min-width:120px">Approved By</th>
                            <th style="min-width:90px">Type</th>
                            <th style="min-width:130px">Payment Method</th>
                            <th style="min-width:120px">Reference</th>
                            <th class="text-center" style="width:80px">Image</th>
                            <th class="text-center" style="width:100px">Status</th>
                            <th style="min-width:130px">Remarks</th>
                            <th class="text-center" style="width:130px">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            @php
                                $isDeposit  = in_array($row->type, ['deposit', 'credit', 'topup']);
                                $isWithdraw = in_array($row->type, ['withdraw', 'debit', 'payment']);
                                $badge = match($row->status) {
                                    'pending'   => 'warning',
                                    'confirmed' => 'success',
                                    'payment'   => 'danger',
                                    default     => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('user.admin.wallet.list', $row->user_id) }}"
                                       class="fw-semibold text-decoration-none text-dark">
                                        {{ $row->author->name ?? 'N/A' }}
                                    </a>
                                    <br>
                                    <small class="text-muted">MR-{{ $row->id }}</small>
                                </td>
                                <td>{{ $row->deposit_date }}</td>

                                {{-- ✅ Receivable (deposit) --}}
                                <td class="text-end">
                                    @if($isDeposit)
                                        <span class="fw-semibold text-success">
                                        &#2547;{{ number_format($row->amount, 2) }}
                                    </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- ✅ Payable (withdraw) --}}
                                <td class="text-end">
                                    @if($isWithdraw)
                                        <span class="fw-semibold text-danger">
                                        &#2547;{{ number_format($row->amount, 2) }}
                                    </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>{{ $row->creator->name ?? '—' }}</td>
                                <td>{{ $row->update_user ? ($row->updater->name ?? '—') : '—' }}</td>
                                <td>
                                    @if($row->type)
                                        @php
                                            $typeBadge = $isDeposit ? 'success' : ($isWithdraw ? 'danger' : 'info');
                                        @endphp
                                        <span class="badge bg-{{ $typeBadge }}">{{ ucfirst($row->type) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $row->transaction_type ?? '—' }}</td>
                                <td class="text-muted small">{{ $row->reference ?? '—' }}</td>
                                <td class="text-center">
                                    @if($row->attachment_id)
                                        <img src="{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb') }}"
                                             alt="Image"
                                             style="width:50px;height:40px;object-fit:cover;cursor:pointer;border-radius:4px;"
                                             class="img-thumbnail p-0"
                                             onclick="showBigImage('{{ \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full') }}')">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $badge }} px-2 py-1">{{ ucfirst($row->status) }}</span>
                                </td>

                                {{-- ✅ Remarks - click করলে edit হবে --}}
                                <td>
                                <span class="remarks-text text-muted small d-block"
                                      style="cursor:pointer;"
                                      title="Click to edit"
                                      @if(auth()->user()->hasPermission('transaction_edit_remarks'))
                                          onclick="openRemarksModal({{ $row->id }}, '{{ addslashes($row->remarks ?? '') }}')"
                                      @endif>
                                    {{ $row->remarks ?? '—' }}
                                    @if(auth()->user()->hasPermission('transaction_edit_remarks'))
                                        <i class="fa fa-pencil fa-xs text-muted ms-1"></i>
                                    @endif
                                </span>
                                </td>

                                {{-- ✅ Action --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(auth()->user()->hasPermission('transaction_approved'))
                                            @if($row->status == 'pending')
                                                <a href="{{ route('user.admin.wallet.update', ['id' => $row->id]) }}"
                                                   class="btn btn-success btn-sm px-2"
                                                   onclick="return confirm('Approve this transaction?')">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            @else
                                                <span class="badge bg-light text-muted border">Done</span>
                                            @endif
                                        @endif

                                        @if(auth()->user()->hasPermission('transaction_edit_amount'))
                                                <button class="btn btn-warning btn-sm px-2"
                                                        onclick="openAmountModal({{ $row->id }}, {{ $row->amount }})"
                                                        title="Edit Amount">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('transaction_delete'))
                                                <button class="btn btn-danger btn-sm px-2 btn-delete-transaction"
                                                        data-id="{{ $row->id }}"
                                                        title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                        @endif

                                            @if(auth()->user()->hasPermission('transaction_print_receipt'))
                                                @if($row->status == 'confirmed')
                                                    <button class="btn btn-info btn-sm px-2"
                                                            onclick='openReceiptModal(
                                                                    {{ $row->id }},
                                                                    "{{ addslashes($row->author->name ?? "N/A") }}",
                                                                    "{{ $row->deposit_date }}",
                                                                    "{{ number_format($row->amount, 2) }}",
                                                                    "{{ addslashes($row->transaction_type ?? "") }}",
                                                                    "{{ addslashes($row->reference ?? "") }}",
                                                                    "{{ addslashes($row->remarks ?? "") }}",
                                                                    "{{ addslashes($row->type ?? "") }}"
                                                                )'
                                                            title="Print Receipt">
                                                        <i class="fa fa-print"></i>
                                                    </button>
                                                @endif
                                            @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Image Overlay --}}
    <div id="imageOverlay" onclick="closeBigImage()"
         style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);z-index:99999;cursor:pointer;">
        <span style="position:absolute;top:20px;right:40px;color:#fff;font-size:36px;font-weight:bold;">&times;</span>
        <img id="bigImage" src=""
             style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:90%;max-height:90%;border:3px solid #fff;border-radius:8px;">
    </div>

    {{-- ✅ Money Receipt Modal --}}
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-print"></i> Money Receipt
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div id="receipt-print-area">
                        <style>
                            #receipt-print-area {
                                font-family: 'Arial', sans-serif;
                                background: #fff;
                                color: #222;
                            }
                            .receipt-wrapper {
                                max-width: 680px;
                                margin: 0 auto;
                                border: 2px solid #e85d24;
                                border-top: 6px solid #e85d24;
                            }
                            .receipt-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 18px 24px 14px;
                                border-bottom: 3px solid #1a2a5e;
                                background: #fff;
                            }
                            .receipt-header .logo-area img {
                                height: 52px;
                                object-fit: contain;
                            }
                            .receipt-header .receipt-title-area {
                                text-align: right;
                            }
                            .receipt-title-area h2 {
                                font-size: 22px;
                                font-weight: 800;
                                color: #e85d24;
                                letter-spacing: 2px;
                                margin: 0;
                                text-transform: uppercase;
                            }
                            .receipt-title-area .mr-no {
                                font-size: 13px;
                                color: #1a2a5e;
                                font-weight: 600;
                                margin-top: 4px;
                            }
                            .receipt-address {
                                background: #1a2a5e;
                                color: #fff;
                                font-size: 11px;
                                padding: 6px 24px;
                                text-align: center;
                                letter-spacing: 0.3px;
                            }
                            .receipt-body {
                                padding: 20px 24px;
                            }
                            .receipt-thanks {
                                font-size: 13px;
                                color: #555;
                                margin-bottom: 14px;
                                border-bottom: 1px dashed #ddd;
                                padding-bottom: 10px;
                            }
                            .receipt-row {
                                display: flex;
                                margin-bottom: 10px;
                                font-size: 13px;
                                align-items: flex-start;
                            }
                            .receipt-row .r-label {
                                min-width: 180px;
                                font-weight: 600;
                                color: #333;
                            }
                            .receipt-row .r-dots {
                                flex: 1;
                                border-bottom: 1px dashed #bbb;
                                margin: 0 8px 4px;
                                min-width: 40px;
                            }
                            .receipt-row .r-value {
                                min-width: 160px;
                                color: #222;
                                text-align: left;
                            }
                            .receipt-amount-box {
                                background: #f8f8f8;
                                border: 1px solid #e85d24;
                                border-radius: 6px;
                                padding: 12px 18px;
                                margin: 16px 0;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .receipt-amount-box .amount-label {
                                font-size: 13px;
                                font-weight: 700;
                                color: #1a2a5e;
                                text-transform: uppercase;
                                letter-spacing: 1px;
                            }
                            .receipt-amount-box .amount-value {
                                font-size: 22px;
                                font-weight: 800;
                                color: #e85d24;
                            }
                            .receipt-note {
                                font-size: 10px;
                                color: #e85d24;
                                font-style: italic;
                                margin-top: 4px;
                            }
                            .receipt-footer {
                                display: flex;
                                justify-content: space-between;
                                padding: 16px 24px;
                                border-top: 2px solid #1a2a5e;
                                margin-top: 10px;
                            }
                            .receipt-footer .sign-box {
                                text-align: center;
                                min-width: 120px;
                            }
                            .receipt-footer .sign-box .sign-line {
                                border-top: 1px solid #333;
                                padding-top: 4px;
                                margin-top: 28px;
                                font-size: 11px;
                                font-weight: 700;
                                color: #333;
                                text-transform: uppercase;
                                letter-spacing: 1px;
                            }
                            .receipt-taka-box {
                                position: relative;
                                display: inline-block;
                                border: 1px solid #ccc;
                                padding: 4px 14px;
                                min-width: 80px;
                                text-align: center;
                                font-size: 15px;
                                font-weight: 700;
                                color: #e85d24;
                                border-radius: 3px;
                            }
                            @media print {
                                body * { visibility: hidden; }
                                #receipt-print-area, #receipt-print-area * { visibility: visible; }
                                #receipt-print-area { position: fixed; left: 0; top: 0; width: 100%; }
                                .modal-footer { display: none !important; }
                            }
                        </style>
                        <div class="receipt-wrapper">
                            {{-- Header --}}
                            <div class="receipt-header">
                                <div class="logo-area">
                                    <img id="receipt-logo" src="" alt="Logo"
                                         style="height:52px;object-fit:contain;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                                    <strong style="display:none;font-size:20px;color:#1a2a5e;font-weight:800;">SHOPNO TOUR</strong>
                                </div>
                                <div class="receipt-title-area">
                                    <h2>Money Receipt</h2>
                                    <div class="mr-no">
                                        MR No: <span id="receipt-mr-no" style="color:#e85d24;">—</span>
                                        &nbsp;&nbsp; Date: <span id="receipt-date">—</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Address bar --}}
                            <div class="receipt-address">
                                1/8, Green Square (Level 3A), Road-8, Gulshan-1, Dhaka-1212 &nbsp;|&nbsp;
                                Phone: 09639101030 &nbsp;|&nbsp; shopnotourbd@gmail.com &nbsp;|&nbsp; www.shopnotour.com
                            </div>

                            {{-- Body --}}
                            <div class="receipt-body">
                                <div class="receipt-thanks">
                                    Received with thanks from
                                </div>

                                <div class="receipt-row">
                                    <span class="r-label">Local Client / B2B Name</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-name">—</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="r-label">Payment Method</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-payment-method">—</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="r-label">Reference / Cheque No</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-reference">—</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="r-label">Sells Date</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-sells-date">—</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="r-label">Remarks</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-remarks">—</span>
                                </div>
                                <div class="receipt-row">
                                    <span class="r-label">Type</span>
                                    <span class="r-dots"></span>
                                    <span class="r-value" id="receipt-type">—</span>
                                </div>

                                {{-- Amount box --}}
                                <div class="receipt-amount-box">
                                    <div>
                                        <div class="amount-label">Total Amount (Taka)</div>
                                        <div class="receipt-note">&#9733; Visa fee & All booking amount non-refundable</div>
                                    </div>
                                    <div class="receipt-taka-box" id="receipt-amount">0.00</div>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="receipt-footer">
                                <div class="sign-box">
                                    <div class="sign-line">Received By</div>
                                </div>
                                <div class="sign-box">
                                    <div class="sign-line">Accounts By</div>
                                </div>
                                <div class="sign-box">
                                    <div class="sign-line">Authorized By</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    @if(auth()->user()->hasPermission('transaction_print_receipt'))
                        <button type="button" class="btn btn-danger" id="btn-download-pdf" onclick="downloadReceiptPDF(this)">
                            <i class="fa fa-file-pdf-o"></i> Download PDF
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary" onclick="printReceipt()">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Remarks Edit Modal --}}
    <div class="modal fade" id="remarksModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-pencil"></i> Edit Remarks</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="remarks_transaction_id">
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea id="remarks_input" class="form-control" rows="4"
                                  placeholder="Enter remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-save-remarks">
                        <i class="fa fa-check"></i> Save Remarks
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Amount Edit Modal --}}
    <div class="modal fade" id="amountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Amount</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="amount_transaction_id">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" id="amount_input" class="form-control form-control-lg"
                               min="0" step="0.01" placeholder="Enter amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="btn-save-amount">
                        <i class="fa fa-check"></i> Save Amount
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        #transactionTable thead th { font-size:.78rem;letter-spacing:.03em;white-space:nowrap;vertical-align:middle;padding:10px 12px; }
        #transactionTable tbody td { font-size:.85rem;vertical-align:middle;padding:10px 12px;line-height:1.5; }
        #transactionTable tbody tr:hover td { background:#eef6ff !important; }
        #transactionTable .badge { font-size:.78rem; }
        .dt-buttons .btn { margin-right:4px; }
        .remarks-text:hover { color: #0056b3 !important; }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        $(document).ready(function () {

            // ✅ DataTable Init
            $('#transactionTable').DataTable({
                dom: '<"row mb-2 align-items-center"<"col-sm-2"l><"col-sm-7"B><"col-sm-3"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [
                    { extend:'excelHtml5', text:'<i class="fas fa-file-excel"></i> Excel', className:'btn btn-success btn-sm', title:'Credit Transactions', exportOptions:{columns:':not(:last-child)'} },
                    { extend:'csvHtml5',   text:'<i class="fas fa-file-csv"></i> CSV',     className:'btn btn-info btn-sm',    exportOptions:{columns:':not(:last-child)'} },
                    { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf"></i> PDF',     className:'btn btn-danger btn-sm',  orientation:'landscape', pageSize:'A4', exportOptions:{columns:':not(:last-child)'} },
                    { extend:'print',      text:'<i class="fas fa-print"></i> Print',      className:'btn btn-secondary btn-sm', exportOptions:{columns:':not(:last-child)'} },
                ],
                lengthMenu : [[10,25,50,100,-1],[10,25,50,100,'All']],
                pageLength : 25,
                ordering   : true,
                searching  : true,
                autoWidth  : false,
                columnDefs : [
                    { orderable: false, targets: [10, 13] },
                    { className: 'text-end',    targets: [3, 4] },
                    { className: 'text-center', targets: [0, 10, 11, 13] },
                ]
            });

            // ✅ Remarks Save
            $('#btn-save-remarks').on('click', function () {
                var id      = $('#remarks_transaction_id').val();
                var remarks = $('#remarks_input').val().trim();

                if (!remarks) { alert('Please enter remarks'); return; }

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("user.admin.wallet.update.remarks", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method : 'POST',
                    data   : { remarks: remarks, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#remarksModal').modal('hide');
                            location.reload();
                        } else {
                            alert('❌ ' + res.message);
                        }
                    },
                    error  : function (xhr) { alert('❌ ' + (xhr.responseJSON?.message || 'Error occurred')); },
                    complete: function () { $('#btn-save-remarks').prop('disabled', false).html('<i class="fa fa-check"></i> Save Remarks'); }
                });
            });

            // ✅ Amount Save
            $('#btn-save-amount').on('click', function () {
                var id     = $('#amount_transaction_id').val();
                var amount = $('#amount_input').val();

                if (!amount || parseFloat(amount) <= 0) { alert('Please enter valid amount'); return; }
                if (!confirm('Update amount to ৳' + parseFloat(amount).toFixed(2) + '?')) return;

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("user.admin.wallet.update.amount", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method : 'POST',
                    data   : { amount: amount, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#amountModal').modal('hide');
                            location.reload();
                        } else {
                            alert('❌ ' + res.message);
                        }
                    },
                    error  : function (xhr) { alert('❌ ' + (xhr.responseJSON?.message || 'Error occurred')); },
                    complete: function () { $('#btn-save-amount').prop('disabled', false).html('<i class="fa fa-check"></i> Save Amount'); }
                });
            });

            // ✅ Delete
            $(document).on('click', '.btn-delete-transaction', function () {
                var id = $(this).data('id');
                if (!confirm('Are you sure you want to delete this transaction?')) return;

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '{{ route("user.admin.wallet.delete", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id),
                    method : 'POST',
                    data   : { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            location.reload();
                        } else {
                            alert('❌ ' + res.message);
                            btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                        }
                    },
                    error: function (xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || 'Error occurred'));
                        btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    }
                });
            });

        });

        // ✅ Open Remarks Modal
        function openRemarksModal(id, remarks) {
            $('#remarks_transaction_id').val(id);
            $('#remarks_input').val(remarks);
            $('#remarksModal').modal('show');
        }

        // ✅ Open Amount Modal
        function openAmountModal(id, amount) {
            $('#amount_transaction_id').val(id);
            $('#amount_input').val(amount);
            $('#amountModal').modal('show');
        }

        // ✅ Image Overlay
        function showBigImage(src) {
            document.getElementById('bigImage').src = src;
            document.getElementById('imageOverlay').style.display = 'block';
        }
        function closeBigImage() {
            document.getElementById('imageOverlay').style.display = 'none';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeBigImage();
        });
        // ✅ Logo setting থেকে নেওয়া
        var siteLogo = '{{ setting_item("site_logo") ? \Modules\Media\Helpers\FileHelper::url(setting_item("site_logo"), "full") : asset("images/logo.png") }}';

        function openReceiptModal(id, name, date, amount, paymentMethod, reference, remarks, txType) {
            document.getElementById('receipt-mr-no').textContent          = id;
            document.getElementById('receipt-date').textContent           = date;
            document.getElementById('receipt-name').textContent           = name || '—';
            document.getElementById('receipt-sells-date').textContent     = date;
            document.getElementById('receipt-amount').textContent         = '৳' + amount;
            document.getElementById('receipt-payment-method').textContent = paymentMethod || '—';
            document.getElementById('receipt-reference').textContent      = reference || '—';
            document.getElementById('receipt-remarks').textContent        = remarks || '—';
            document.getElementById('receipt-type').textContent           = txType || '—';

            var logoImg = document.getElementById('receipt-logo');
            if (logoImg) logoImg.src = siteLogo;

            $('#receiptModal').modal('show');
        }

        function printReceipt() {
            var content = document.getElementById('receipt-print-area').innerHTML;
            var printWindow = window.open('', '_blank', 'width=900,height=700');
            printWindow.document.write(`<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <title>Money Receipt</title>
                        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
                        <style>
                            * { margin:0; padding:0; box-sizing:border-box; }
                            body { background:#fff; font-family:Arial,sans-serif; }
                            @page { size: A5 landscape; margin: 15mm; }
                            .receipt-wrapper {
                                max-width: 100%;
                                border: 2px solid #e85d24;
                                border-top: 6px solid #e85d24;
                                margin-top: 10px;
                            }
                            .receipt-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 14px 20px 10px;
                                border-bottom: 3px solid #1a2a5e;
                            }
                            .receipt-header .logo-area img { height: 48px; object-fit: contain; }
                            .receipt-title-area { text-align: right; }
                            .receipt-title-area h2 { font-size: 20px; font-weight: 800; color: #e85d24; letter-spacing: 2px; margin: 0; text-transform: uppercase; }
                            .receipt-title-area .mr-no { font-size: 12px; color: #1a2a5e; font-weight: 600; margin-top: 3px; }
                            .receipt-address { background: #1a2a5e; color: #fff; font-size: 10px; padding: 5px 20px; text-align: center; }
                            .receipt-body { padding: 14px 20px; }
                            .receipt-thanks { font-size: 12px; color: #555; margin-bottom: 10px; border-bottom: 1px dashed #ddd; padding-bottom: 8px; }
                            .receipt-row { display: flex; margin-bottom: 7px; font-size: 12px; align-items: flex-end; }
                            .receipt-row .r-label { min-width: 170px; font-weight: 600; color: #333; }
                            .receipt-row .r-dots { flex: 1; border-bottom: 1px dashed #bbb; margin: 0 6px 3px; min-width: 30px; }
                            .receipt-row .r-value { min-width: 150px; color: #222; }
                            .receipt-amount-box { border: 1px solid #e85d24; border-radius: 5px; padding: 10px 16px; margin: 12px 0 6px; display: flex; justify-content: space-between; align-items: center; background: #f8f8f8; }
                            .amount-label { font-size: 12px; font-weight: 800; color: #1a2a5e; text-transform: uppercase; letter-spacing: 1px; }
                            .receipt-note { font-size: 9px; color: #e85d24; font-style: italic; margin-top: 2px; }
                            .receipt-taka-box { border: 1px solid #ccc; padding: 3px 12px; font-size: 15px; font-weight: 800; color: #e85d24; border-radius: 3px; }
                            .receipt-footer { display: flex; justify-content: space-between; padding: 12px 20px; border-top: 2px solid #1a2a5e; margin-top: 6px; }
                            .sign-box { text-align: center; min-width: 110px; }
                            .sign-line { border-top: 1px solid #333; padding-top: 3px; margin-top: 22px; font-size: 10px; font-weight: 800; color: #333; text-transform: uppercase; letter-spacing: 1px; }
                        </style>
                    </head>
                    <body>
                        ${content}
                    </body>
                    </html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() { printWindow.print(); printWindow.close(); }, 800);
        }

        // ✅ PDF Download
        function downloadReceiptPDF(btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';

            // ✅ Modal hide করলে element অদৃশ্য হয়, তাই clone করে body-তে রাখি
            var original = document.getElementById('receipt-print-area');
            var clone    = original.cloneNode(true);
            clone.id     = 'receipt-clone-area';
            clone.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:680px;background:#fff;z-index:-1;';
            document.body.appendChild(clone);

            html2canvas(clone, {
                scale      : 2,
                useCORS    : true,
                backgroundColor : '#ffffff',
                logging    : false,
                width      : 680,
            }).then(function(canvas) {
                document.body.removeChild(clone);

                var imgData   = canvas.toDataURL('image/png');
                var { jsPDF } = window.jspdf;
                var pdf       = new jsPDF('l', 'mm', 'a5');

                var pdfW  = pdf.internal.pageSize.getWidth();
                var pdfH  = pdf.internal.pageSize.getHeight();
                var ratio = Math.min(pdfW / canvas.width, pdfH / canvas.height);
                var imgX  = (pdfW - canvas.width  * ratio) / 2;
                var imgY  = (pdfH - canvas.height * ratio) / 2;

                pdf.addImage(imgData, 'PNG', imgX, imgY, canvas.width * ratio, canvas.height * ratio);

                var mrNo = document.getElementById('receipt-mr-no').textContent;
                pdf.save('money-receipt-' + mrNo + '.pdf');

                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Download PDF';
            }).catch(function(err) {
                document.body.removeChild(clone);
                console.error(err);
                alert('PDF generation failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Download PDF';
            });
        }
    </script>
@endpush
