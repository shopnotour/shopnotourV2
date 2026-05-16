@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #voidTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #voidTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
        }
        #voidTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #voidTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
        }
        #voidTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #voidTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #voidTable_info,
        #voidTable_length label,
        #voidTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #voidTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #voidTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #voidTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
            background: #eff6ff !important;
            border-color: #3b82f6 !important;
            color: #1d4ed8 !important;
        }

        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 2px; }
        .dt-button {
            font-size: 0.75rem !important;
            padding: 5px 11px !important;
            border-radius: 5px !important;
            border: 1px solid #d1d5db !important;
            background: #fff !important;
            color: #374151 !important;
            cursor: pointer;
            transition: background .12s;
        }
        .dt-button:hover { background: #f3f4f6 !important; border-color: #9ca3af !important; }

        table.dataTable > tbody > tr.child ul.dtr-details { width: 100%; }
        table.dataTable > tbody > tr.child ul.dtr-details li {
            border-bottom: 1px solid #f3f4f6;
            padding: 6px 4px;
            font-size: 0.82rem;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        table.dataTable > tbody > tr.child ul.dtr-details li:last-child { border-bottom: none; }
        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before {
            background-color: #1e3a5f !important;
            border-color: #1e3a5f !important;
        }

        /* Status filter buttons */
        .status-filter-btns { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
        .status-filter-btns .btn-filter {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
            transition: all .15s;
        }
        .status-filter-btns .btn-filter.active,
        .status-filter-btns .btn-filter:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }

        .badge-waiting_user_approval { background-color: #17a2b8; color: white; }
        .badge-user_approved { background-color: #007bff; color: white; }
        .badge-user_rejected { background-color: #dc3545; color: white; }
        .badge-approved { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: black; }
        .badge-rejected { background-color: #dc3545; color: white; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Void Requests') }}</h1>
        </div>

        @include('admin.message')

        {{-- Bulk Action Form --}}
        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="post" action="{{ route('admin.voids.bulkAction') }}" class="filter-form filter-form-left d-flex" id="bulkActionForm">
                    @csrf
                    <select name="action" class="form-control">
                        <option value="">{{ __('-- Bulk Actions --') }}</option>
                        <option value="delete">{{ __('Delete') }}</option>
                    </select>
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Apply') }}</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">{{ __('Void Requests') }}</div>
            <div class="panel-body">

                {{-- Status Quick Filter Buttons --}}
                <div class="status-filter-btns">
                    <button class="btn-filter active" data-status="">All</button>
                    <button class="btn-filter" data-status="pending">Pending</button>
                    <button class="btn-filter" data-status="waiting user approval">Waiting User Approval</button>
                    <button class="btn-filter" data-status="user approved">User Approved</button>
                    <button class="btn-filter" data-status="user rejected">User Rejected</button>
                    <button class="btn-filter" data-status="approved">Approved</button>
                    <button class="btn-filter" data-status="rejected">Rejected</button>
                </div>

                <table id="voidTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" class="check-all"></th>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Booking') }}</th>
                        <th>{{ __('PNR') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Paid') }}</th>
                        <th>{{ __('Charges') }}</th>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $void)
                        @php
                            $statusColors = [
                                'pending'               => 'warning',
                                'waiting_user_approval' => 'info',
                                'user_approved'         => 'primary',
                                'user_rejected'         => 'danger',
                                'approved'              => 'success',
                                'rejected'              => 'danger',
                            ];
                            $statusColor  = $statusColors[$void->status] ?? 'secondary';
                            $paidAmount   = $void->booking->paid ?? 0;
                            $voidCharges  = $void->void_charges ?? 0;
                            $refundAmount = $paidAmount > 0 ? ($paidAmount - $voidCharges) : 0;
                            $statusLabel  = ucfirst(str_replace('_', ' ', $void->status));
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $void->id }}" form="bulkActionForm"></td>
                            <td>{{ $void->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $void->booking_id) }}">
                                    #{{ $void->booking_id }}
                                </a>
                            </td>
                            <td><strong>{{ $void->pnr }}</strong></td>
                            <td>
                                @if($void->booking && $void->booking->user)
                                    {{ $void->booking->user->name ?? $void->booking->first_name }}<br>
                                    <small class="text-muted">{{ $void->booking->user->email ?? $void->booking->email }}</small>
                                @else
                                    {{ $void->booking->first_name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $void->booking->email ?? '' }}</small>
                                @endif
                            </td>
                            <td><strong>{{ format_money_main($paidAmount) }}</strong></td>
                            <td>
                                @if($voidCharges > 0)
                                    {{ format_money_main($voidCharges) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($voidCharges > 0 && $paidAmount > 0)
                                    <strong class="text-success">{{ format_money_main($refundAmount) }}</strong>
                                @elseif($voidCharges > 0 && $paidAmount == 0)
                                    <span class="text-danger">-{{ format_money_main($voidCharges) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-order="{{ $void->status }}">
                                <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-order="{{ $void->created_at->timestamp }}" style="white-space:nowrap">
                                {{ $void->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $void->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        {{ __('Actions') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="javascript:void(0)"
                                           data-toggle="modal" data-target="#modal-void-{{ $void->id }}">
                                            <i class="fa fa-eye"></i> {{ __('View Details') }}
                                        </a>
                                        @if($void->status == 'pending')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-set-amount-{{ $void->id }}">
                                                <i class="fa fa-money text-primary"></i> {{ __('Set Charges') }}
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-{{ $void->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Reject') }}
                                            </a>
                                        @endif
                                        @if($void->status == 'user_approved')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-approve-{{ $void->id }}">
                                                <i class="fa fa-check text-success"></i> {{ __('Final Approve') }}
                                            </a>
                                        @endif
                                        @if($void->status == 'waiting_user_approval')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-{{ $void->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Cancel & Reject') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">{{ __('No void requests found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- ══════════════════ MODALS (unchanged) ══════════════════ --}}
    @foreach($rows as $void)
        @php
            $paidAmount    = $void->booking->paid ?? 0;
            $customer      = $void->booking->user ?? null;
            $walletBalance = $customer ? ($customer->credit_balance ?? 0) : 0;
            $statusColors  = [
                'pending'               => 'warning',
                'waiting_user_approval' => 'info',
                'user_approved'         => 'primary',
                'user_rejected'         => 'danger',
                'approved'              => 'success',
                'rejected'              => 'danger',
            ];
        @endphp

        {{-- Set Amount Modal --}}
        <div class="modal fade" id="modal-set-amount-{{ $void->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-calculator"></i> {{ __('Set Void Charges #') }}{{ $void->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $void->booking_id }}<br>
                            <strong>PNR:</strong> {{ $void->pnr }}<br>
                            <strong>Paid Amount:</strong> <span class="text-success font-weight-bold">{{ format_money_main($paidAmount) }}</span>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i> After setting charges, user will be notified for approval.
                        </div>
                        <div class="form-group">
                            <label>{{ __('Void Charges') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">BDT</span>
                                </div>
                                <input type="number"
                                       id="void_charges_set_{{ $void->id }}"
                                       class="form-control void-charges-input"
                                       data-id="{{ $void->id }}"
                                       data-paid="{{ $paidAmount }}"
                                       value="500" step="0.01" min="0"
                                       max="{{ $paidAmount > 0 ? $paidAmount : '' }}" required>
                            </div>
                            @if($paidAmount > 0)
                                <small class="form-text text-muted">Maximum: {{ format_money_main($paidAmount) }}</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ __('Customer Will') }}</label>
                            <div id="customer_action_{{ $void->id }}" class="alert alert-secondary">—</div>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Airline Response (Optional)') }}</label>
                            <textarea id="airline_response_set_{{ $void->id }}" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary btn-set-amount-void" data-id="{{ $void->id }}">
                            <i class="fa fa-paper-plane"></i> {{ __('Send to User for Approval') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Final Approve Modal --}}
        <div class="modal fade" id="modal-approve-{{ $void->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-check-circle"></i> {{ __('Final Approve Void #') }}{{ $void->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> <strong>User has approved this void!</strong>
                        </div>
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $void->booking_id }}<br>
                            <strong>PNR:</strong> {{ $void->pnr }}<br>
                            <strong>Paid Amount:</strong> {{ format_money_main($paidAmount) }}<br>
                            <strong>Void Charges:</strong> {{ format_money_main($void->void_charges ?? 0) }}<br>
                            @if($paidAmount > 0)
                                <strong>Refund Amount:</strong>
                                <span class="text-success font-weight-bold">{{ format_money_main($paidAmount - ($void->void_charges ?? 0)) }}</span>
                            @else
                                <strong>To Deduct:</strong>
                                <span class="text-danger font-weight-bold">{{ format_money_main($void->void_charges ?? 0) }}</span>
                            @endif
                        </div>
                        <div class="alert {{ $paidAmount > 0 ? 'alert-success' : ($walletBalance >= ($void->void_charges ?? 0) ? 'alert-success' : 'alert-danger') }}">
                            <strong>Customer Wallet Balance:</strong> {{ format_money_main($walletBalance) }}
                            @if($paidAmount == 0 && $walletBalance < ($void->void_charges ?? 0))
                                <br><small class="text-danger"><i class="fa fa-warning"></i> Insufficient balance!</small>
                            @endif
                        </div>
                        @if($paidAmount > 0)
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> Clicking "Approve" will refund
                                <strong>{{ format_money_main($paidAmount - ($void->void_charges ?? 0)) }}</strong> to customer's wallet.
                            </p>
                        @else
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i> Clicking "Approve" will deduct
                                <strong>{{ format_money_main($void->void_charges ?? 0) }}</strong> from customer's wallet.
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success btn-final-approve-void" data-id="{{ $void->id }}"
                            {{ $paidAmount == 0 && $walletBalance < ($void->void_charges ?? 0) ? 'disabled' : '' }}>
                            <i class="fa fa-check"></i> {{ __('Approve & Process') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="modal-reject-{{ $void->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-times-circle"></i> {{ __('Reject Void Request #') }}{{ $void->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This will reject the void request.
                        </div>
                        <div class="form-group">
                            <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_{{ $void->id }}" class="form-control" rows="4" required
                                      placeholder="Enter reason for rejection..."></textarea>
                            <small class="form-text text-muted">Minimum 10 characters</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger btn-reject-void" data-id="{{ $void->id }}">
                            <i class="fa fa-times"></i> {{ __('Reject Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Modal --}}
        <div class="modal fade" id="modal-void-{{ $void->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Void Request Details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <tr><th width="30%">{{ __('Void ID') }}</th><td>{{ $void->id }}</td></tr>
                            <tr><th>{{ __('Booking ID') }}</th><td>#{{ $void->booking_id }}</td></tr>
                            <tr><th>{{ __('PNR') }}</th><td>{{ $void->pnr }}</td></tr>
                            <tr><th>{{ __('Paid Amount') }}</th><td>{{ format_money_main($paidAmount) }}</td></tr>
                            <tr>
                                <th>{{ __('Void Charges') }}</th>
                                <td>{{ $void->void_charges ? format_money_main($void->void_charges) : 'Not set' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <td>
                                    <span class="badge badge-{{ $statusColors[$void->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $void->status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr><th>{{ __('Reason') }}</th><td>{{ $void->reason }}</td></tr>
                            <tr><th>{{ __('Requested At') }}</th><td>{{ $void->created_at->format('d M Y, h:i A') }}</td></tr>
                            @if($void->airline_response)
                                <tr><th>{{ __('Airline Response') }}</th><td>{{ $void->airline_response }}</td></tr>
                            @endif
                            @if($void->rejection_reason)
                                <tr><th>{{ __('Rejection Reason') }}</th><td class="text-danger">{{ $void->rejection_reason }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            // ── DataTable ──────────────────────────────────────────
            var table = $('#voidTable').DataTable({
                dom:
                    "<'d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'d-flex flex-wrap align-items-center justify-content-between mt-3'i<'ml-auto'p>>",
                pageLength: 20,
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
                order: [[9, 'desc']], // Requested At
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1,  targets: 3 },                   // PNR
                    { responsivePriority: 2,  targets: 8 },                   // Status
                    { responsivePriority: 3,  targets: 9 },                   // Requested
                    { responsivePriority: 4,  targets: 10, orderable: false },// Actions
                    { responsivePriority: 5,  targets: 1 },                   // ID
                    { responsivePriority: 6,  targets: 2 },                   // Booking
                    { responsivePriority: 7,  targets: 5 },                   // Paid
                    { responsivePriority: 8,  targets: 6 },                   // Charges
                    { responsivePriority: 9,  targets: 7 },                   // Refund
                    { responsivePriority: 10, targets: 4 },                   // Customer
                    { responsivePriority: 11, targets: 0, orderable: false }, // Checkbox
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',       exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',         exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel',exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',   exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',      exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search PNR, booking, customer...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests found',
                    emptyTable: 'No void requests found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // ── Status Quick Filter ────────────────────────────────
            $('.btn-filter').on('click', function () {
                $('.btn-filter').removeClass('active');
                $(this).addClass('active');
                var status = $(this).data('status');
                table.column(8).search(status, false, false).draw();
            });

            // ── Check All ─────────────────────────────────────────
            $('.check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            // ── Void Charges Calculator ────────────────────────────
            $(document).on('input', '.void-charges-input', function () {
                var id = $(this).data('id');
                var paidAmount = parseFloat($(this).data('paid')) || 0;
                var voidCharges = parseFloat($(this).val()) || 0;
                var actionDiv = $('#customer_action_' + id);

                if (paidAmount > 0) {
                    var refund = paidAmount - voidCharges;
                    if (refund > 0) {
                        actionDiv.attr('class', 'alert alert-success').html('<strong>Get Refund:</strong> BDT ' + refund.toFixed(2));
                    } else if (refund == 0) {
                        actionDiv.attr('class', 'alert alert-info').html('<strong>No Refund</strong> (charges equal to paid amount)');
                    } else {
                        actionDiv.attr('class', 'alert alert-danger').html('<strong>Error:</strong> Charges cannot exceed paid amount');
                    }
                } else {
                    actionDiv.attr('class', 'alert alert-danger').html('<strong>Pay:</strong> BDT ' + voidCharges.toFixed(2) + ' from wallet');
                }
            });

            $('.modal[id^="modal-set-amount-"]').on('shown.bs.modal', function () {
                $(this).find('.void-charges-input').trigger('input');
            });

            // ── Set Amount ─────────────────────────────────────────
            $(document).on('click', '.btn-set-amount-void', function (e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data('id');
                var voidCharges = parseFloat($('#void_charges_set_' + id).val()) || 0;
                var airlineResponse = $('#airline_response_set_' + id).val();
                var paidAmount = parseFloat($('#void_charges_set_' + id).data('paid')) || 0;

                if (voidCharges <= 0) { alert('Please enter valid void charges'); return; }
                if (paidAmount > 0 && voidCharges > paidAmount) { alert('Void charges cannot exceed paid amount'); return; }

                var msg = 'Set void charges of BDT ' + voidCharges.toFixed(2) + '?\n\n';
                msg += paidAmount > 0
                    ? 'Customer will get refund: BDT ' + (paidAmount - voidCharges).toFixed(2)
                    : 'Customer will pay: BDT ' + voidCharges.toFixed(2);

                if (!confirm(msg)) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/voids/' + id + '/set-amount',
                    method: 'POST',
                    data: { void_charges: voidCharges, airline_response: airlineResponse, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    }
                });
            });

            // ── Final Approve ──────────────────────────────────────
            $(document).on('click', '.btn-final-approve-void', function (e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data('id');
                if (!confirm('Are you sure? This will process the wallet transaction and complete the void.')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/voids/' + id + '/approve',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Process');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Process');
                    }
                });
            });

            // ── Reject ─────────────────────────────────────────────
            $(document).on('click', '.btn-reject-void', function (e) {
                e.preventDefault();
                var btn = $(this);
                var id = btn.data('id');
                var reason = $('#rejection_reason_' + id).val();

                if (!reason || reason.length < 10) { alert('Please enter rejection reason (minimum 10 characters)'); return; }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/voids/' + id + '/reject',
                    method: 'POST',
                    data: { rejection_reason: reason, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                    }
                });
            });
        });
    </script>
@endpush
