@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-3">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="title-bar mb-0">
                <i class="fa fa-check-circle text-success"></i> {{ __('paid Bookings') }}
                <span class="badge badge-success ml-2">{{ $rows->total() }} {{ __('bookings') }}</span>
            </h1>
            <div>
                <a href="{{ route('all-booking.paid') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-refresh"></i> {{ __('Refresh') }}
                </a>
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-info btn-sm">
                    <i class="fa fa-list"></i> {{ __('All Bookings') }}
                </a>
            </div>
        </div>

        @include('admin.message')

        {{-- Filters --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <form method="GET" action="{{ route('all-booking.paid') }}" id="filter-form">
                    <div class="row g-2 align-items-end">

                        {{-- Bulk Action --}}
                        <div class="col-12 col-md-auto">
                            <label class="form-label small mb-1 fw-semibold">{{ __('Bulk Action') }}</label>
                            <div class="input-group input-group-sm">
                                <select name="bulk_action" id="bulk_action" class="form-select">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    <option value="issue_request">{{ __('Mark Issue Request') }}</option>
                                    <option value="paid">{{ __('Mark Paid') }}</option>
                                    <option value="cancelled">{{ __('Mark Cancelled') }}</option>
                                </select>
                                <button type="button" id="apply-bulk" class="btn btn-info btn-sm">
                                    <i class="fa fa-check"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Date Column --}}
                        <div class="col-6 col-md-auto">
                            <label class="form-label small mb-1 fw-semibold">{{ __('Date Field') }}</label>
                            <select name="date_column" class="form-select form-select-sm" style="min-width:160px">
                                @foreach([
                                    'booking_date'     => 'Booking Date',
                                    'confirmed_at'     => 'Confirmed Date',
                                    'ticket_issued_at' => 'Ticket Issued Date',
                                ] as $col => $lbl)
                                    <option value="{{ $col }}" {{ ($dateColumnSel ?? 'booking_date') == $col ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div class="col-6 col-md-auto">
                            <label class="form-label small mb-1 fw-semibold">{{ __('From') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}">
                        </div>

                        {{-- Date To --}}
                        <div class="col-6 col-md-auto">
                            <label class="form-label small mb-1 fw-semibold">{{ __('To') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}">
                        </div>

                        {{-- Search --}}
                        {{-- <div class="col-12 col-md-auto">
                            <label class="form-label small mb-1 fw-semibold">{{ __('Search') }}</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Code, PNR, Name, Email..." value="{{ $searchTerm ?? '' }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div> --}}

                        {{-- Buttons --}}
                        <div class="col-auto">
                            <label class="form-label small mb-1 d-block">&nbsp;</label>
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-filter"></i> {{ __('Filter') }}
                                </button>
                                @if(!empty($dateFrom) || !empty($dateTo) || !empty($searchTerm))
                                    <a href="{{ route('all-booking.paid') }}" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Count --}}
                        {{-- <div class="col-auto ms-auto">
                            <label class="form-label small mb-1 d-block">&nbsp;</label>
                            <span class="text-muted small">
                                <i class="fa fa-calendar"></i>
                                {{ __('Showing :from–:to of :total', ['from' => $rows->firstItem() ?? 0, 'to' => $rows->lastItem() ?? 0, 'total' => $rows->total()]) }}
                            </span>
                        </div> --}}

                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="bookings-table" class="table table-hover table-bordered table-sm mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="32px" class="text-center">
                                    <input type="checkbox" id="check-all" title="Select All">
                                </th>
                                <th width="44px" class="text-center">#</th>
                                <th width="110px">{{ __('Code') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th width="80px">{{ __('Source') }}</th>
                                <th width="80px">{{ __('Type') }}</th>
                                <th>{{ __('Route') }}</th>
                                <th>{{ __('Airline') }}</th>
                                <th>{{ __('PNR') }}</th>
                                <th>{{ __('Payment') }}</th>
                                <th width="100px">{{ __('Status') }}</th>
                                <th width="115px">{{ __('Booking Date') }}</th>
                                <th width="115px">{{ __('Ticket Issued') }}</th>
                                <th width="80px" class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $i => $booking)
                            @php
                                $sourceColors = [
                                    'sabre'       => 'primary',
                                    'travelport'  => 'info',
                                    'galileo'     => 'warning',
                                    'amadeus'     => 'success',
                                    'manual'      => 'secondary',
                                ];
                                $sourceColor  = $sourceColors[$booking->source] ?? 'secondary';
                                $isFullyPaid  = $booking->paid >= $booking->total;
                                $hasTicket    = !empty($booking->ticket_number);
                            @endphp
                            <tr>
                                {{-- Checkbox --}}
                                <td class="text-center">
                                    <input type="checkbox" class="check-item" value="{{ $booking->id }}">
                                </td>

                                {{-- Serial --}}
                                <td class="text-center text-muted small">
                                    {{ ($rows->currentPage() - 1) * $rows->perPage() + $i + 1 }}
                                </td>

                                {{-- Code --}}
                                <td>
                                    <strong class="text-success" style="font-size:12px">
                                        <i class="fa fa-ticket"></i> {{ $booking->code ?? '#' . $booking->id }}
                                    </strong>
                                    @if($booking->pnr_id)
                                        <br><small class="text-muted">PNR: {{ $booking->pnr_id }}</small>
                                    @endif
                                </td>

                                {{-- Customer --}}
                                <td>
                                    <div class="fw-semibold" style="font-size:13px">{{ $booking->first_name }} {{ $booking->last_name }}</div>
                                    <div class="text-muted" style="font-size:11px">
                                        <i class="fa fa-envelope fa-fw"></i>{{ $booking->email }}<br>
                                        <i class="fa fa-phone fa-fw"></i>{{ $booking->phone }}
                                    </div>
                                </td>

                                {{-- Source --}}
                                <td>
                                    <span class="badge badge-{{ $sourceColor }} badge-sm">{{ ucfirst($booking->source) }}</span>
                                </td>

                                {{-- Type --}}
                                <td>
                                    <span class="badge badge-light badge-sm" style="font-size:10px">
                                        {{ ucfirst(str_replace('_', ' ', $booking->flight_type)) }}
                                    </span>
                                </td>

                                {{-- Route --}}
                                <td class="fw-semibold" style="font-size:13px">
                                    @if($booking->flight_from && $booking->flight_to)
                                        {{ $booking->flight_from }}
                                        <i class="fa fa-long-arrow-right text-muted mx-1"></i>
                                        {{ $booking->flight_to }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Airline --}}
                                <td style="font-size:12px">{{ $booking->airline ?? '—' }}</td>

                                {{-- PNR --}}
                                <td style="font-size:12px">
                                    <span class="badge badge-secondary">{{ $booking->pnr_id ?? 'N/A' }}</span>
                                </td>

                                {{-- Payment --}}
                                <td style="font-size:12px">
                                    <div><span class="text-muted">Total:</span> <strong class="text-primary">{{ format_money_main($booking->total) }}</strong></div>
                                    <div><span class="text-muted">Paid:</span>
                                        <span class="{{ $isFullyPaid ? 'text-success' : 'text-danger' }}">
                                            {{ format_money_main($booking->paid) }}
                                        </span>
                                    </div>
                                    @if(!$isFullyPaid)
                                        <div><span class="text-muted">Due:</span>
                                            <span class="text-danger fw-bold">{{ format_money_main($booking->total - $booking->paid) }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge badge-paid" style="font-size:11px">
                                        <i class="fa fa-check-circle"></i> paid
                                    </span>
                                    @if($hasTicket)
                                        <br><small class="text-success"><i class="fa fa-ticket"></i> Ticketed</small>
                                    @else
                                        <br><small class="text-muted">No Ticket</small>
                                    @endif
                                </td>

                                {{-- Booking Date --}}
                                <td style="font-size:11px">
                                    @if($booking->booking_date)
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                                        <br><small>{{ \Carbon\Carbon::parse($booking->booking_date)->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Ticket Issued At --}}
                                <td style="font-size:11px">
                                    @if($booking->ticket_issued_at)
                                        {{ \Carbon\Carbon::parse($booking->ticket_issued_at)->format('d M Y') }}
                                        <br><small>{{ \Carbon\Carbon::parse($booking->ticket_issued_at)->format('h:i A') }}</small>
                                        @if($booking->issuedBy)
                                            <br><small class="text-muted">by {{ $booking->issuedBy->name }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-success dropdown-toggle px-2 py-1"
                                                type="button" data-toggle="dropdown">
                                            <i class="fa fa-bolt"></i> Action
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right shadow" style="min-width:190px">

                                            {{-- View Detail --}}
                                            @if(auth()->user()->hasPermission('booking_details'))
                                                <a class="dropdown-item btn-detail-booking" href="#"
                                                data-ajax="{{ route('booking.modal', $booking->id) }}"
                                                data-toggle="modal" data-id="{{ $booking->id }}"
                                                data-target="#modal_booking_detail">
                                                    <i class="fa fa-eye text-info"></i> {{ __('View Detail') }}
                                                </a>
                                            @endif

                                            {{-- Edit Booking --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))
                                                <a class="dropdown-item" href="{{ route('admin.bookings.edit', $booking->id) }}">
                                                    <i class="fa fa-edit text-warning"></i> {{ __('Edit Booking') }}
                                                </a>
                                            @endif

                                            {{-- Duplicate --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))
                                                <a class="dropdown-item btn-duplicate-booking" href="#"
                                                data-id="{{ $booking->id }}"
                                                data-code="{{ $booking->code ?? '#' . $booking->id }}">
                                                    <i class="fa fa-copy text-primary"></i> {{ __('Duplicate') }}
                                                </a>
                                            @endif

                                            <div class="dropdown-divider"></div>

                                            {{-- Edit PNR --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))
                                                <a class="dropdown-item btn-pnr-edit" href="#"
                                                data-id="{{ $booking->id }}"
                                                data-pnr="{{ $booking->pnr_id }}"
                                                data-source="{{ $booking->source }}"
                                                data-status="{{ $booking->status }}"
                                                data-code="{{ $booking->code ?? '#' . $booking->id }}">
                                                    <i class="fa fa-barcode"></i> {{ __('Edit PNR/Source') }}
                                                </a>
                                            @endif

                                            {{-- Set Paid --}}
                                            @if(auth()->user()->hasPermission('booking_setpaid'))
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                   data-target="#modal-paid-{{ $booking->id }}">
                                                    <i class="fa fa-money text-success"></i> {{ __('Set Paid') }}
                                                </a>
                                            @endif

                                            {{-- PNR Check --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_check'))
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.booking.pnrcheck', ['id'=>$booking->id]) }}">
                                                    <i class="fa fa-search"></i> {{ __('PNR Check') }}
                                                </a>
                                            @endif

                                            <div class="dropdown-divider"></div>

                                            {{-- Cancel Booking --}}
                                            @if(auth()->user()->hasPermission('booking_cancel'))
                                                <a class="dropdown-item text-danger btn-booking-cancel" href="#"
                                                data-id="{{ $booking->id }}"
                                                data-code="{{ $booking->code ?? '#' . $booking->id }}"
                                                data-url="{{ route('booking.cancel', $booking->id) }}">
                                                    <i class="fa fa-ban"></i> {{ __('Cancel Booking') }}
                                                </a>
                                            @endif

                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Set Paid Modal --}}
                            <div class="modal fade" id="modal-paid-{{ $booking->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white py-2">
                                            <h6 class="modal-title"><i class="fa fa-money"></i> {{ __('Set Paid') }} — {{ $booking->code ?? '#'.$booking->id }}</h6>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-2 mb-3">
                                                <div class="col-4">
                                                    <div class="border rounded p-2 text-center">
                                                        <div class="text-muted small">Total</div>
                                                        <div class="fw-bold">{{ format_money_main($booking->total) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2 text-center bg-success bg-opacity-10">
                                                        <div class="text-muted small">Paid</div>
                                                        <div class="fw-bold text-success">{{ format_money_main($booking->paid) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="border rounded p-2 text-center bg-danger bg-opacity-10">
                                                        <div class="text-muted small">Due</div>
                                                        <div class="fw-bold text-danger">{{ format_money_main($booking->total - $booking->paid) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-{{ $booking->user && $booking->user->credit_balance > 0 ? 'success' : 'danger' }} py-2 mb-3">
                                                <i class="fa fa-wallet"></i>
                                                <strong>Credit Balance:</strong>
                                                {{ $booking->user ? format_money_main($booking->user->credit_balance) : '৳0.00' }}
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small fw-semibold">Amount to Add <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control"
                                                       id="set_paid_input_{{ $booking->id }}"
                                                       min="0" step="0.01"
                                                       value="{{ $booking->total - $booking->paid }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-primary btn-sm btn-set-paid" data-id="{{ $booking->id }}">
                                                <i class="fa fa-check"></i> Save Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                        {{ __('No paid bookings found') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if(false && $rows->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top flex-wrap gap-2">
                        <div class="text-muted small">
                            Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }} &mdash; {{ number_format($rows->total()) }} total
                        </div>
                        {{ $rows->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Modals --}}
    @include('Booking::admin.all-booking.partials.modals')

@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .badge-paid {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            padding-left: 16px;
            position: relative;
        }
        .badge-paid::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #fff;
        }
        .dropdown-item i {
            width: 18px;
        }
        @media (max-width: 767px) {
            .card-body.p-0 { overflow-x: auto; }
            table { min-width: 1000px; }
        }
    </style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {

    // DataTable Init
    $('#bookings-table').DataTable({
        responsive: true,
        pageLength: 25,
        ordering: true,
        searching: true,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        columnDefs: [
            { orderable: false, targets: [0, 13] }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Quick search bookings...",
        }
    });

    // Check All
    $('#check-all').on('change', function() {
        $('.check-item').prop('checked', $(this).prop('checked'));
    });

    // Bulk Action
    $('#apply-bulk').on('click', function() {
        var action = $('#bulk_action').val();
        var ids = $('.check-item:checked').map(function(){ return $(this).val(); }).get();
        if (!action) { alert('Please select an action'); return; }
        if (!ids.length) { alert('Please select at least one booking'); return; }
        if (!confirm('Apply "' + action + '" to ' + ids.length + ' booking(s)?')) return;

        var form = $('<form method="POST" action="{{ route('admin.bookings.bulkAction') }}">' +
            '<input name="_token" value="{{ csrf_token() }}">' +
            '<input name="action" value="' + action + '">' +
            ids.map(function(id){ return '<input name="ids[]" value="' + id + '">'; }).join('') +
            '</form>');
        $('body').append(form);
        form.submit();
    });

    // Booking Detail
    $(document).on('click', '.btn-detail-booking', function(e) {
        e.preventDefault();
        var id  = $(this).data('id');
        var url = $(this).data('ajax');
        $('.booking-id-display').text(id);
        $('#booking-detail-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $.get(url, function(html){ $('#booking-detail-content').html(html); })
            .fail(function(){ $('#booking-detail-content').html('<div class="alert alert-danger">Failed to load.</div>'); });
        $('#modal_booking_detail').modal('show');
    });

    // Duplicate
    $(document).on('click', '.btn-duplicate-booking', function(e) {
        e.preventDefault();
        $('#dup_booking_id').val($(this).data('id'));
        $('#dup-code').text($(this).data('code'));
        $('#modal_duplicate').modal('show');
    });

    // ── Set Paid ──
    $(document).on('click', '.btn-set-paid', function () {
        var btn = $(this), id = btn.data('id');
        var amount = parseFloat($('#set_paid_input_' + id).val());
        if (!amount || amount <= 0) { alert('Enter a valid amount'); return; }
        if (!confirm('Add payment of ৳' + amount.toFixed(2) + '?')) return;
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.ajax({
            url: '/admin/module/booking/bookings/' + id + '/set-paid',
            method: 'POST',
            data: { remain: amount, _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(res) {
                if (res.status || res.success) { alert('✅ ' + res.message); location.reload(); }
                else { alert('❌ ' + (res.message || 'Failed')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Save'); }
            },
            error: function(xhr) {
                alert('❌ ' + (xhr.responseJSON?.message || 'Error')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Save');
            }
        });
    });

    $('#btn-confirm-duplicate').on('click', function() {
        var btn = $(this), id = $('#dup_booking_id').val();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Duplicating...');
        $.ajax({
            url: '/admin/module/booking/bookings/' + id + '/duplicate',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('✅ ' + res.message + '\nNew Code: ' + res.new_code);
                    if (res.edit_url) window.location.href = res.edit_url;
                    else location.reload();
                } else {
                    alert('❌ ' + (res.message || 'Failed'));
                    btn.prop('disabled', false).html('<i class="fa fa-copy"></i> Confirm Duplicate');
                }
            },
            error: function(xhr) {
                alert('❌ ' + (xhr.responseJSON?.message || 'Error'));
                btn.prop('disabled', false).html('<i class="fa fa-copy"></i> Confirm Duplicate');
            }
        });
    });

    // PNR Edit
    $(document).on('click', '.btn-pnr-edit', function(e) {
        e.preventDefault();
        $('#pnr_edit_booking_id').val($(this).data('id'));
        $('#pnr-edit-code').text($(this).data('code'));
        $('#pnr_edit_input').val($(this).data('pnr') || '');
        $('#pnr_edit_source').val($(this).data('source') || 'sabre');
        $('#pnr_edit_status').val($(this).data('status') || 'paid');
        $('#modal_pnr_edit').modal('show');
    });

    $('#btn-save-pnr').on('click', function() {
        var btn = $(this), id = $('#pnr_edit_booking_id').val();
        var pnr = $('#pnr_edit_input').val().trim().toUpperCase();
        if (!pnr) { alert('PNR is required'); return; }
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.ajax({
            url: '/admin/module/booking/bookings/' + id + '/update-pnr',
            method: 'POST',
            data: { pnr_id: pnr, source: $('#pnr_edit_source').val(), status: $('#pnr_edit_status').val(), _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('✅ ' + res.message);
                    $('#modal_pnr_edit').modal('hide');
                    location.reload();
                } else {
                    alert('❌ ' + (res.message || 'Failed'));
                    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                }
            },
            error: function(xhr) {
                alert('❌ ' + (xhr.responseJSON?.message || 'Error'));
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            }
        });
    });

    // Booking Cancel
    $(document).on('click', '.btn-booking-cancel', function(e) {
        e.preventDefault();
        $('#booking-cancel-code').text($(this).data('code'));
        $('#booking_cancel_url').val($(this).data('url'));
        $('#modal_booking_cancel').modal('show');
    });

    $('#btn-confirm-booking-cancel').on('click', function(e) {
        e.preventDefault();
        var url = $('#booking_cancel_url').val();
        if (url) { $(this).prop('disabled', true); window.location.href = url; }
    });

});

function printBookingDetail() {
    var c = document.getElementById('booking-detail-content').innerHTML;
    var w = window.open('', '', 'height=600,width=800');
    w.document.write('<html><head><title>Booking</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head><body>' + c + '</body></html>');
    w.document.close(); w.print();
}
</script>
@endpush