@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-4 py-3">

        {{-- PAGE HEADER --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="mb-0 fw-bold text-dark">
                    <i class="fa fa-user-circle me-2 text-primary"></i>
                    {{ $users->name }}
                </h2>
                <p class="text-muted mb-0 small">Transaction History</p>
            </div>
            <a href="{{ route('user.admin.wallet.transactions') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>

        @include('admin.message')

        {{-- ✅ Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:#e8f4fd;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-credit-card text-primary" style="font-size:20px;"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Credit Balance</div>
                            <div class="fw-bold fs-5 text-primary">&#2547;{{ number_format($users->credit_balance, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:#e8fdf0;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-check-circle text-success" style="font-size:20px;"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Paid</div>
                            <div class="fw-bold fs-5 text-success">&#2547;{{ number_format($totalPaid, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:#fff8e1;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-clock-o text-warning" style="font-size:20px;"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Pending</div>
                            <div class="fw-bold fs-5 text-warning">{{ $totalPending }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;background:#f0e8fd;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-list text-purple" style="font-size:20px;color:#7c3aed;"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Confirmed</div>
                            <div class="fw-bold fs-5" style="color:#7c3aed;">{{ $totalConfirmed }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Status Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
                <select name="status" class="form-control" style="min-width:160px;" onchange="this.form.submit()">
                    <option value="">-- All Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ ($selectedStatus ?? '') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($selectedStatus))
                    <a href="{{ route('user.admin.wallet.list', $users->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Clear
                    </a>
                @endif
            </form>
            <span class="text-muted small"><i>Found {{ $rows->count() }} transactions</i></span>
        </div>

        {{-- ✅ Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fa fa-history me-2 text-primary"></i>Transaction History
                    <span class="badge bg-secondary ms-1">{{ $rows->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="creditListTable" style="width:100%">
                        <thead class="table-dark">
                        <tr>
                            <th style="width:45px" class="text-center">#</th>
                            <th style="min-width:110px">Date</th>
                            <th style="min-width:110px" class="text-end">Receivable</th>
                            <th style="min-width:110px" class="text-end">Payable</th>
                            <th style="min-width:120px">Created By</th>
                            <th style="min-width:120px">Approved By</th>
                            <th style="min-width:90px">Type</th>
                            <th style="min-width:130px">Payment Method</th>
                            <th style="min-width:120px">Reference</th>
                            <th style="width:80px" class="text-center">Image</th>
                            <th style="width:100px" class="text-center">Status</th>
                            <th style="min-width:130px">Remarks</th>
                            <th style="width:80px" class="text-center">Action</th>
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
                                    {{ $row->deposit_date }}
                                    <br><small class="text-muted">MR-{{ $row->id }}</small>
                                </td>

                                {{-- Receivable --}}
                                <td class="text-end">
                                    @if($isDeposit)
                                        <span class="fw-semibold text-success">&#2547;{{ number_format($row->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Payable --}}
                                <td class="text-end">
                                    @if($isWithdraw)
                                        <span class="fw-semibold text-danger">&#2547;{{ number_format($row->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>{{ $row->creator->name ?? '—' }}</td>
                                <td>{{ $row->update_user ? ($row->updater->name ?? '—') : '—' }}</td>
                                <td>
                                    @if($row->type)
                                        @php $typeBadge = $isDeposit ? 'success' : ($isWithdraw ? 'danger' : 'info'); @endphp
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
                                <td class="text-muted small">{{ $row->remarks ?? '—' }}</td>
                                <td class="text-center">
                                    @if(auth()->user()->hasPermission('transaction_delete'))
                                        <button class="btn btn-danger btn-sm px-2 btn-delete-transaction"
                                                data-id="{{ $row->id }}"
                                                title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Overlay --}}
    <div id="imageOverlay" onclick="closeBigImage()"
         style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);z-index:99999;cursor:pointer;">
        <span style="position:absolute;top:20px;right:40px;color:#fff;font-size:36px;font-weight:bold;">&times;</span>
        <img id="bigImage" src=""
             style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:90%;max-height:90%;border:3px solid #fff;border-radius:8px;">
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <style>
        #creditListTable thead th { font-size:.78rem;letter-spacing:.03em;white-space:nowrap;vertical-align:middle;padding:10px 12px; }
        #creditListTable tbody td { font-size:.85rem;vertical-align:middle;padding:10px 12px;line-height:1.5; }
        #creditListTable tbody tr:hover td { background:#eef6ff !important; }
        #creditListTable .badge { font-size:.78rem; }
        .dt-buttons .btn { margin-right:4px; }
        .fs-5 { font-size: 1.1rem !important; }
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

    <script>
        $(document).ready(function () {
            $('#creditListTable').DataTable({
                dom: '<"row mb-2 align-items-center"<"col-sm-2"l><"col-sm-7"B><"col-sm-3"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: '{{ $users->name }} - Transactions',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa fa-file-text-o"></i> CSV',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: '{{ $users->name }} - Transactions',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Print',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: { columns: ':visible' }
                    },
                ],
                lengthMenu : [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                pageLength : 25,
                ordering   : true,
                searching  : true,
                autoWidth  : false,
                order      : [[0, 'asc']],
                columnDefs : [
                    { orderable: false, targets: [9, 10, 12] }, // image, status, action
                    { className: 'text-end',    targets: [2, 3] },
                    { className: 'text-center', targets: [0, 9, 10, 12] },
                ]
            });
        });

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

        // ✅ Delete
        $(document).on('click', '.btn-delete-transaction', function () {
            var id  = $(this).data('id');
            if (!confirm('Are you sure you want to delete this transaction?\n\nIf confirmed, credit balance will be adjusted automatically.')) return;

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
                        btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                    }
                },
                error: function (xhr) {
                    alert('❌ ' + (xhr.responseJSON?.message || 'Error occurred'));
                    btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                }
            });
        });
    </script>
@endpush
