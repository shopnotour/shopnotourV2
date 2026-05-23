{{--@extends('admin.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container-fluid px-3">--}}

{{--        --}}{{-- ── Page Header ── --}}
{{--        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">--}}
{{--            <h1 class="title-bar mb-0">--}}
{{--                <i class="fa fa-plane"></i> {{ __('All Bookings') }}--}}
{{--            </h1>--}}
{{--            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">--}}
{{--                <i class="fa fa-refresh"></i> {{ __('Refresh') }}--}}
{{--            </a>--}}
{{--        </div>--}}

{{--        @include('admin.message')--}}

{{--        --}}{{-- ── Filters ── --}}
{{--        <div class="card shadow-sm mb-3">--}}
{{--            <div class="card-body py-2 px-3">--}}
{{--                <form method="GET" action="{{ route('bookings.index') }}" id="filter-form">--}}
{{--                    <div class="row g-2 align-items-end">--}}

{{--                        --}}{{-- Bulk Action --}}
{{--                        <div class="col-12 col-md-auto">--}}
{{--                            <label class="form-label small mb-1 fw-semibold">{{ __('Bulk Action') }}</label>--}}
{{--                            <div class="input-group input-group-sm">--}}
{{--                                <select name="bulk_action" id="bulk_action" class="form-select">--}}
{{--                                    <option value="">{{ __('-- Select --') }}</option>--}}
{{--                                    <option value="paid">{{ __('Mark Paid') }}</option>--}}
{{--                                    <option value="booked">{{ __('Mark Booked') }}</option>--}}
{{--                                    <option value="ticketed">{{ __('Mark Ticketed') }}</option>--}}
{{--                                    <option value="completed">{{ __('Mark Completed') }}</option>--}}
{{--                                    <option value="cancelled">{{ __('Mark Cancelled') }}</option>--}}
{{--                                    <option value="delete">{{ __('Delete') }}</option>--}}
{{--                                </select>--}}
{{--                                <button type="button" id="apply-bulk" class="btn btn-info btn-sm">--}}
{{--                                    <i class="fa fa-check"></i>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Status --}}
{{--                        <div class="col-6 col-md-auto">--}}
{{--                            <label class="form-label small mb-1 fw-semibold">{{ __('Status') }}</label>--}}
{{--                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:130px">--}}
{{--                                <option value="">{{ __('All') }}</option>--}}
{{--                                @foreach([--}}
{{--                                    'issue_request'=>'Issue Request','pending'=>'Pending','paid'=>'Paid',--}}
{{--                                    'booked'=>'Booked','issued'=>'Issued','ticketed'=>'Ticketed',--}}
{{--                                    'completed'=>'Completed','cancelled'=>'Cancelled','failed'=>'Failed','refunded'=>'Refunded',--}}
{{--                                ] as $v => $l)--}}
{{--                                    <option value="{{ $v }}" {{ ($selectedStatus??'')==$v?'selected':'' }}>{{ $l }}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Date Column --}}
{{--                        <div class="col-6 col-md-auto">--}}
{{--                            <label class="form-label small mb-1 fw-semibold">{{ __('Date Field') }}</label>--}}
{{--                            <select name="date_column" class="form-select form-select-sm" style="min-width:150px">--}}
{{--                                @foreach([--}}
{{--                                    'booking_date'=>'Cancel Date','confirmed_at'=>'Booking Date',--}}
{{--                                    'ticket_issued_at'=>'Ticket Issued At',--}}
{{--                                ] as $col=>$lbl)--}}
{{--                                    <option value="{{ $col }}" {{ ($dateColumnSel??'booking_date')==$col?'selected':'' }}>{{ $lbl }}</option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Date From --}}
{{--                        <div class="col-6 col-md-auto">--}}
{{--                            <label class="form-label small mb-1 fw-semibold">{{ __('From') }}</label>--}}
{{--                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom??'' }}">--}}
{{--                        </div>--}}

{{--                        --}}{{-- Date To --}}
{{--                        <div class="col-6 col-md-auto">--}}
{{--                            <label class="form-label small mb-1 fw-semibold">{{ __('To') }}</label>--}}
{{--                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo??'' }}">--}}
{{--                        </div>--}}

{{--                        --}}{{-- Buttons --}}
{{--                        <div class="col-auto">--}}
{{--                            <label class="form-label small mb-1 d-block">&nbsp;</label>--}}
{{--                            <div class="d-flex gap-1">--}}
{{--                                <button type="submit" class="btn btn-primary btn-sm">--}}
{{--                                    <i class="fa fa-filter"></i> {{ __('Filter') }}--}}
{{--                                </button>--}}
{{--                                @if(!empty($selectedStatus)||!empty($dateFrom)||!empty($dateTo))--}}
{{--                                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary btn-sm">--}}
{{--                                        <i class="fa fa-times"></i>--}}
{{--                                    </a>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- Count --}}
{{--                        <div class="col-auto ms-auto">--}}
{{--                            <label class="form-label small mb-1 d-block">&nbsp;</label>--}}
{{--                            <span class="text-muted small">--}}
{{--                            <i>{{ __('Showing :from–:to of :total', ['from'=>$rows->firstItem()??0,'to'=>$rows->lastItem()??0,'total'=>$rows->total()]) }}</i>--}}
{{--                        </span>--}}
{{--                        </div>--}}

{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        --}}{{-- ── Table ── --}}
{{--        <div class="card shadow-sm">--}}
{{--            <div class="card-body p-0">--}}
{{--                <div class="table-responsive">--}}
{{--                    <table id="bookingsDataTable" class="table table-hover table-bordered table-sm mb-0" style="width:100%">--}}
{{--                        <thead class="table-light">--}}
{{--                        <tr>--}}
{{--                            <th width="32px" class="text-center">--}}
{{--                                <input type="checkbox" id="check-all" title="Select All">--}}
{{--                            </th>--}}
{{--                            <th width="44px" class="text-center">#</th>--}}
{{--                            <th width="110px">{{ __('Code') }}</th>--}}
{{--                            <th>{{ __('Customer') }}</th>--}}
{{--                            <th width="80px">{{ __('Source') }}</th>--}}
{{--                            <th width="80px">{{ __('Type') }}</th>--}}
{{--                            <th>{{ __('Route') }}</th>--}}
{{--                            <th>{{ __('Airline') }}</th>--}}
{{--                            <th>{{ __('PNR / TKT') }}</th>--}}
{{--                            <th>{{ __('Payment') }}</th>--}}
{{--                            <th width="100px">{{ __('Status') }}</th>--}}
{{--                            <th width="115px">{{ __('Ticket Issued') }}</th>--}}
{{--                            <th width="110px">{{ __('Issued By') }}</th>--}}
{{--                            <th width="110px">{{ __('Cancel Date') }}</th>--}}
{{--                            <th width="110px">{{ __('Booking Date') }}</th>--}}
{{--                            <th width="80px" class="text-center">{{ __('Actions') }}</th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @forelse($rows as $i => $booking)--}}
{{--                            @php--}}
{{--                                $statusColors = [--}}
{{--                                    'pending'=>'warning','paid'=>'info','booked'=>'primary',--}}
{{--                                    'issued'=>'success','issue_request'=>'issue-request',--}}
{{--                                    'ticketed'=>'success','completed'=>'success',--}}
{{--                                    'cancelled'=>'danger','failed'=>'danger','refunded'=>'secondary',--}}
{{--                                    'pnr_pending'=>'secondary',--}}
{{--                                ];--}}
{{--                                $statusColor = $statusColors[$booking->status] ?? 'secondary';--}}
{{--                                $sourceColors = [--}}
{{--                                    'sabre'=>'primary','travelport'=>'info','galileo'=>'warning',--}}
{{--                                    'amadeus'=>'success','manual'=>'secondary',--}}
{{--                                ];--}}
{{--                                $sourceColor = $sourceColors[$booking->source] ?? 'secondary';--}}
{{--                                $tickets = $booking->ticket_number ? json_decode($booking->ticket_number, true) : [];--}}
{{--                            @endphp--}}
{{--                            <tr>--}}
{{--                                --}}{{-- Checkbox --}}
{{--                                <td class="text-center">--}}
{{--                                    <input type="checkbox" class="check-item" value="{{ $booking->id }}">--}}
{{--                                </td>--}}

{{--                                --}}{{-- Serial --}}
{{--                                <td class="text-center text-muted small">--}}
{{--                                    {{ ($rows->currentPage()-1)*$rows->perPage() + $i + 1 }}--}}
{{--                                </td>--}}

{{--                                --}}{{-- Code --}}
{{--                                <td>--}}
{{--                                    <strong class="text-primary" style="font-size:12px">--}}
{{--                                        {{ $booking->code ?? '#'.$booking->id }}--}}
{{--                                    </strong>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Customer --}}
{{--                                <td>--}}
{{--                                    <div class="fw-semibold" style="font-size:13px">{{ $booking->first_name }} {{ $booking->last_name }}</div>--}}
{{--                                    <div class="text-muted" style="font-size:11px">--}}
{{--                                        <i class="fa fa-envelope fa-fw"></i>{{ $booking->email }}<br>--}}
{{--                                        <i class="fa fa-phone fa-fw"></i>{{ $booking->phone }}--}}
{{--                                    </div>--}}
{{--                                    @if($booking->customer_notes)--}}
{{--                                        <div class="text-muted" style="font-size:11px">--}}
{{--                                            <i class="fa fa-sticky-note fa-fw"></i>{{ Str::limit($booking->customer_notes,40) }}--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Source --}}
{{--                                <td>--}}
{{--                                    <span class="badge badge-{{ $sourceColor }} badge-sm">{{ ucfirst($booking->source) }}</span>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Type --}}
{{--                                <td>--}}
{{--                                <span class="badge badge-light badge-sm" style="font-size:10px">--}}
{{--                                    {{ ucfirst(str_replace('_',' ',$booking->flight_type)) }}--}}
{{--                                </span>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Route --}}
{{--                                <td class="fw-semibold" style="font-size:13px">--}}
{{--                                    {{ $booking->flight_from }}--}}
{{--                                    <i class="fa fa-long-arrow-right text-muted mx-1"></i>--}}
{{--                                    {{ $booking->flight_to }}--}}
{{--                                </td>--}}

{{--                                --}}{{-- Airline --}}
{{--                                <td style="font-size:12px">{{ $booking->airline }}</td>--}}

{{--                                --}}{{-- PNR & Tickets --}}
{{--                                <td style="font-size:12px">--}}
{{--                                    <div><span class="text-muted">PNR:</span> <strong class="text-primary">{{ $booking->pnr_id ?? 'N/A' }}</strong></div>--}}
{{--                                    <div><span class="text-muted">TKT:</span>--}}
{{--                                        @if(!empty($tickets))--}}
{{--                                            <small>{{ implode(', ', array_slice($tickets,0,2)) }}{{ count($tickets)>2?'…':'' }}</small>--}}
{{--                                        @else--}}
{{--                                            <span class="text-muted">—</span>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Payment --}}
{{--                                <td style="font-size:12px">--}}
{{--                                    <div><span class="text-muted">Total:</span> <span class="text-primary fw-semibold">{{ format_money_main($booking->total) }}</span></div>--}}
{{--                                    <div><span class="text-muted">Paid:</span> <span class="text-success">{{ format_money_main($booking->paid) }}</span></div>--}}
{{--                                    @if(($booking->total - $booking->paid) > 0)--}}
{{--                                        <div><span class="text-muted">Due:</span> <span class="text-danger">{{ format_money_main($booking->total - $booking->paid) }}</span></div>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Status --}}
{{--                                <td>--}}
{{--                                <span class="badge badge-{{ $statusColor }}" style="font-size:11px">--}}
{{--                                    {{ ucfirst(str_replace('_',' ',$booking->status)) }}--}}
{{--                                </span>--}}
{{--                                </td>--}}

{{--                                --}}{{-- Ticket Issued At --}}
{{--                                <td style="font-size:11px">--}}
{{--                                    @if($booking->ticket_issued_at)--}}
{{--                                        <span class="text-success">--}}
{{--                                        {{ \Carbon\Carbon::parse($booking->ticket_issued_at)->format('d M Y') }}<br>--}}
{{--                                        <small>{{ \Carbon\Carbon::parse($booking->ticket_issued_at)->format('h:i A') }}</small>--}}
{{--                                    </span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Issued By --}}
{{--                                <td style="font-size:11px">--}}
{{--                                    @if($booking->issuedBy)--}}
{{--                                        <span class="badge badge-light"><i class="fa fa-user"></i> {{ $booking->issuedBy->name }}</span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Cancel Date (booking_date field) --}}
{{--                                <td style="font-size:11px">--}}
{{--                                    @if($booking->booking_date)--}}
{{--                                        <span class="text-danger">--}}
{{--                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}<br>--}}
{{--                                        <small>{{ \Carbon\Carbon::parse($booking->booking_date)->format('h:i A') }}</small>--}}
{{--                                    </span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Booking Date (confirmed_at field) --}}
{{--                                <td style="font-size:11px">--}}
{{--                                    @if($booking->confirmed_at)--}}
{{--                                        <span class="text-success">--}}
{{--                                        {{ \Carbon\Carbon::parse($booking->confirmed_at)->format('d M Y') }}<br>--}}
{{--                                        <small>{{ \Carbon\Carbon::parse($booking->confirmed_at)->format('h:i A') }}</small>--}}
{{--                                    </span>--}}
{{--                                    @else--}}
{{--                                        <span class="text-muted">—</span>--}}
{{--                                    @endif--}}
{{--                                </td>--}}

{{--                                --}}{{-- Actions --}}
{{--                                <td class="text-center">--}}
{{--                                    <div class="dropdown">--}}
{{--                                        <button class="btn btn-sm btn-secondary dropdown-toggle px-2 py-1"--}}
{{--                                                type="button" data-toggle="dropdown">--}}
{{--                                            <i class="fa fa-ellipsis-v"></i>--}}
{{--                                        </button>--}}
{{--                                        <div class="dropdown-menu dropdown-menu-right shadow" style="min-width:190px">--}}

{{--                                            --}}{{-- Detail --}}
{{--                                            @if(auth()->user()->hasPermission('booking_details'))--}}
{{--                                                <a class="dropdown-item btn-detail-booking" href="#"--}}
{{--                                                   data-ajax="{{ route('booking.modal', $booking->id) }}"--}}
{{--                                                   data-toggle="modal" data-id="{{ $booking->id }}"--}}
{{--                                                   data-target="#modal_booking_detail">--}}
{{--                                                    <i class="fa fa-eye text-info"></i> {{ __('View Detail') }}--}}
{{--                                                </a>--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- Edit Booking --}}
{{--                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))--}}
{{--                                            <a class="dropdown-item"--}}
{{--                                               href="{{ route('admin.bookings.edit', $booking->id) }}">--}}
{{--                                                <i class="fa fa-edit text-warning"></i> {{ __('Edit Booking') }}--}}
{{--                                            </a>--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- Duplicate --}}
{{--                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))--}}
{{--                                            <a class="dropdown-item btn-duplicate-booking" href="#"--}}
{{--                                               data-id="{{ $booking->id }}"--}}
{{--                                               data-code="{{ $booking->code ?? '#'.$booking->id }}">--}}
{{--                                                <i class="fa fa-copy text-primary"></i> {{ __('Duplicate') }}--}}
{{--                                            </a>--}}
{{--                                            @endif--}}

{{--                                            <div class="dropdown-divider"></div>--}}

{{--                                            --}}{{-- Set Paid --}}
{{--                                            @if(auth()->user()->hasPermission('booking_setpaid'))--}}
{{--                                                <a class="dropdown-item" href="#" data-toggle="modal"--}}
{{--                                                   data-target="#modal-paid-{{ $booking->id }}">--}}
{{--                                                    <i class="fa fa-money text-success"></i> {{ __('Set Paid') }}--}}
{{--                                                </a>--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- PNR Edit --}}
{{--                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))--}}
{{--                                                <a class="dropdown-item btn-pnr-edit" href="#"--}}
{{--                                                   data-id="{{ $booking->id }}"--}}
{{--                                                   data-pnr="{{ $booking->pnr_id }}"--}}
{{--                                                   data-source="{{ $booking->source }}"--}}
{{--                                                   data-status="{{ $booking->status }}"--}}
{{--                                                   data-code="{{ $booking->code ?? '#'.$booking->id }}">--}}
{{--                                                    <i class="fa fa-barcode"></i> {{ __('Edit PNR/Source') }}--}}
{{--                                                </a>--}}
{{--                                            @endif--}}

{{--                                            <div class="dropdown-divider"></div>--}}

{{--                                            --}}{{-- Issue Ticket --}}
{{--                                            @if(auth()->user()->hasPermission('booking_issue_ticket'))--}}
{{--                                                @if($booking->pay_now == 0 && $booking->status == 'booked' && $booking->source == 'sabre')--}}
{{--                                                    <a class="dropdown-item"--}}
{{--                                                       href="{{ route('report.admin.booking.issueTicket', ['id'=>$booking->id]) }}">--}}
{{--                                                        <i class="fa fa-ticket text-success"></i> {{ __('Issue Ticket') }}--}}
{{--                                                    </a>--}}
{{--                                                @endif--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- PNR Check --}}
{{--                                            @if(auth()->user()->hasPermission('booking_pnr_check'))--}}
{{--                                                <a class="dropdown-item"--}}
{{--                                                   href="{{ route('admin.booking.pnrcheck', ['id'=>$booking->id]) }}">--}}
{{--                                                    <i class="fa fa-search"></i> {{ __('PNR Check') }}--}}
{{--                                                </a>--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- Booking Cancel --}}
{{--                                            @if(auth()->user()->hasPermission('booking_cancel') && $booking->status == 'booked')--}}
{{--                                                <a class="dropdown-item text-warning btn-booking-cancel" href="#"--}}
{{--                                                   data-id="{{ $booking->id }}"--}}
{{--                                                   data-code="{{ $booking->code ?? '#'.$booking->id }}"--}}
{{--                                                   data-url="{{ route('booking.cancel', $booking->id) }}">--}}
{{--                                                    <i class="fa fa-ban"></i> {{ __('Booking Cancel') }}--}}
{{--                                                </a>--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- Ticket Cancel --}}
{{--                                            @if(auth()->user()->hasPermission('booking_cancel_ticket'))--}}
{{--                                                @if(in_array($booking->status, ['ticketed','issued','completed']))--}}
{{--                                                    <a class="dropdown-item text-danger btn-ticket-cancel" href="#"--}}
{{--                                                       data-id="{{ $booking->id }}"--}}
{{--                                                       data-code="{{ $booking->code ?? '#'.$booking->id }}"--}}
{{--                                                       data-paid="{{ $booking->paid }}">--}}
{{--                                                        <i class="fa fa-times-circle"></i> {{ __('Ticket Cancel') }}--}}
{{--                                                    </a>--}}
{{--                                                @endif--}}
{{--                                            @endif--}}

{{--                                            --}}{{-- Print Ticket --}}
{{--                                            @if(auth()->user()->hasPermission('booking_print_ticket'))--}}
{{--                                                @if(in_array($booking->status, ['issued','completed']))--}}
{{--                                                    <div class="dropdown-divider"></div>--}}
{{--                                                    <a class="dropdown-item"--}}
{{--                                                       href="{{ route('report.admin.booking.ticket', ['id'=>$booking->id]) }}"--}}
{{--                                                       target="_blank">--}}
{{--                                                        <i class="fa fa-print"></i> {{ __('Print Ticket') }}--}}
{{--                                                    </a>--}}
{{--                                                @endif--}}
{{--                                            @endif--}}

{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}

{{--                            --}}{{-- Set Paid Modal --}}
{{--                            <div class="modal fade" id="modal-paid-{{ $booking->id }}" tabindex="-1">--}}
{{--                                <div class="modal-dialog">--}}
{{--                                    <div class="modal-content">--}}
{{--                                        <div class="modal-header bg-primary text-white py-2">--}}
{{--                                            <h6 class="modal-title"><i class="fa fa-money"></i> {{ __('Set Paid') }} — {{ $booking->code ?? '#'.$booking->id }}</h6>--}}
{{--                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                                        </div>--}}
{{--                                        <div class="modal-body">--}}
{{--                                            <div class="row g-2 mb-3">--}}
{{--                                                <div class="col-4">--}}
{{--                                                    <div class="border rounded p-2 text-center">--}}
{{--                                                        <div class="text-muted small">Total</div>--}}
{{--                                                        <div class="fw-bold">{{ format_money_main($booking->total) }}</div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-4">--}}
{{--                                                    <div class="border rounded p-2 text-center bg-success bg-opacity-10">--}}
{{--                                                        <div class="text-muted small">Paid</div>--}}
{{--                                                        <div class="fw-bold text-success">{{ format_money_main($booking->paid) }}</div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="col-4">--}}
{{--                                                    <div class="border rounded p-2 text-center bg-danger bg-opacity-10">--}}
{{--                                                        <div class="text-muted small">Due</div>--}}
{{--                                                        <div class="fw-bold text-danger">{{ format_money_main($booking->total - $booking->paid) }}</div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            <div class="alert alert-{{ $booking->user && $booking->user->credit_balance > 0 ? 'success' : 'danger' }} py-2 mb-3">--}}
{{--                                                <i class="fa fa-wallet"></i>--}}
{{--                                                <strong>Credit Balance:</strong>--}}
{{--                                                {{ $booking->user ? format_money_main($booking->user->credit_balance) : '৳0.00' }}--}}
{{--                                            </div>--}}
{{--                                            <div class="form-group mb-0">--}}
{{--                                                <label class="small fw-semibold">Amount to Add <span class="text-danger">*</span></label>--}}
{{--                                                <input type="number" class="form-control"--}}
{{--                                                       id="set_paid_input_{{ $booking->id }}"--}}
{{--                                                       min="0" step="0.01"--}}
{{--                                                       value="{{ $booking->total - $booking->paid }}">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="modal-footer py-2">--}}
{{--                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>--}}
{{--                                            <button type="button" class="btn btn-primary btn-sm btn-set-paid" data-id="{{ $booking->id }}">--}}
{{--                                                <i class="fa fa-check"></i> Save Payment--}}
{{--                                            </button>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                        @empty--}}
{{--                            <tr>--}}
{{--                                <td colspan="16" class="text-center py-4">--}}
{{--                                    <div class="text-muted"><i class="fa fa-inbox fa-2x d-block mb-2"></i>{{ __('No bookings found') }}</div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforelse--}}
{{--                        </tbody>--}}
{{--                    </table>--}}
{{--                </div>--}}

{{--                --}}{{-- Pagination --}}
{{--                @if($rows->hasPages())--}}
{{--                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top flex-wrap gap-2">--}}
{{--                        <div class="text-muted small">--}}
{{--                            Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }} &mdash; {{ number_format($rows->total()) }} total--}}
{{--                        </div>--}}
{{--                        {{ $rows->links('pagination::bootstrap-4') }}--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}

{{--    --}}{{-- ══════════════════════════════════════════--}}
{{--         GLOBAL MODALS--}}
{{--    ══════════════════════════════════════════ --}}

{{--    --}}{{-- Booking Detail Modal --}}
{{--    <div class="modal fade" id="modal_booking_detail" tabindex="-1">--}}
{{--        <div class="modal-dialog modal-xl">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header bg-primary text-white py-2">--}}
{{--                    <h6 class="modal-title"><i class="fa fa-plane"></i> Booking Details — <span class="booking-id-display"></span></h6>--}}
{{--                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                </div>--}}
{{--                <div class="modal-body" id="booking-detail-content">--}}
{{--                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>--}}
{{--                </div>--}}
{{--                <div class="modal-footer py-2">--}}
{{--                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>--}}
{{--                    <button type="button" class="btn btn-primary btn-sm" onclick="printBookingDetail()">--}}
{{--                        <i class="fa fa-print"></i> Print--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- Duplicate Confirm Modal --}}
{{--    <div class="modal fade" id="modal_duplicate" tabindex="-1">--}}
{{--        <div class="modal-dialog">--}}
{{--            <div class="modal-content border-primary">--}}
{{--                <div class="modal-header bg-primary text-white py-2">--}}
{{--                    <h6 class="modal-title"><i class="fa fa-copy"></i> Duplicate Booking — <span id="dup-code"></span></h6>--}}
{{--                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <input type="hidden" id="dup_booking_id">--}}
{{--                    <div class="alert alert-info py-2 mb-3">--}}
{{--                        <i class="fa fa-info-circle"></i>--}}
{{--                        A new booking will be created with <strong>all passengers and routes copied</strong>.--}}
{{--                        The new booking will have status <strong>booked</strong>.--}}
{{--                    </div>--}}
{{--                    <div class="alert alert-warning py-2 mb-0">--}}
{{--                        <i class="fa fa-exclamation-triangle"></i>--}}
{{--                        You can edit prices, passengers, and routes after duplication.--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="modal-footer py-2">--}}
{{--                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>--}}
{{--                    <button type="button" class="btn btn-primary btn-sm" id="btn-confirm-duplicate">--}}
{{--                        <i class="fa fa-copy"></i> Confirm Duplicate--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- Ticket Cancel Modal --}}
{{--    <div class="modal fade" id="modal_ticket_cancel" tabindex="-1">--}}
{{--        <div class="modal-dialog">--}}
{{--            <div class="modal-content border-danger">--}}
{{--                <div class="modal-header bg-danger text-white py-2">--}}
{{--                    <h6 class="modal-title"><i class="fa fa-times-circle"></i> Ticket Cancel — <span id="cancel-booking-code"></span></h6>--}}
{{--                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <input type="hidden" id="cancel_booking_id">--}}
{{--                    <div class="alert alert-warning py-2 mb-3">--}}
{{--                        <strong>Paid Amount:</strong> <span id="cancel-booking-paid" class="text-danger fw-bold"></span>--}}
{{--                        <div class="small mt-1">This amount will be refunded to credit balance.</div>--}}
{{--                    </div>--}}
{{--                    <div class="form-group mb-3">--}}
{{--                        <label class="small fw-semibold">Reason <span class="text-muted">(optional)</span></label>--}}
{{--                        <textarea class="form-control form-control-sm" id="cancel_reason" rows="3"--}}
{{--                                  placeholder="Cancellation reason..."></textarea>--}}
{{--                    </div>--}}
{{--                    <div class="alert alert-danger py-2 mb-0 small">--}}
{{--                        <i class="fa fa-warning"></i> This action cannot be undone.--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="modal-footer py-2">--}}
{{--                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>--}}
{{--                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirm-ticket-cancel">--}}
{{--                        <i class="fa fa-times-circle"></i> Confirm Cancel--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- Booking Cancel Modal --}}
{{--    <div class="modal fade" id="modal_booking_cancel" tabindex="-1">--}}
{{--        <div class="modal-dialog">--}}
{{--            <div class="modal-content border-warning">--}}
{{--                <div class="modal-header bg-warning py-2">--}}
{{--                    <h6 class="modal-title"><i class="fa fa-ban"></i> Booking Cancel — <span id="booking-cancel-code"></span></h6>--}}
{{--                    <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <input type="hidden" id="booking_cancel_url">--}}
{{--                    <div class="alert alert-warning py-2 mb-0">--}}
{{--                        <i class="fa fa-exclamation-triangle"></i>--}}
{{--                        This will cancel the booking in GDS. This action <strong>cannot be undone</strong>.--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="modal-footer py-2">--}}
{{--                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">No, Go Back</button>--}}
{{--                    <a href="#" id="btn-confirm-booking-cancel" class="btn btn-warning btn-sm">--}}
{{--                        <i class="fa fa-ban"></i> Yes, Cancel--}}
{{--                    </a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- PNR Edit Modal --}}
{{--    <div class="modal fade" id="modal_pnr_edit" tabindex="-1">--}}
{{--        <div class="modal-dialog">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header bg-info text-white py-2">--}}
{{--                    <h6 class="modal-title"><i class="fa fa-edit"></i> Edit PNR / Source / Status — <span id="pnr-edit-code"></span></h6>--}}
{{--                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    <input type="hidden" id="pnr_edit_booking_id">--}}
{{--                    <div class="form-group">--}}
{{--                        <label class="small fw-semibold">PNR / GDS Reference <span class="text-danger">*</span></label>--}}
{{--                        <input type="text" class="form-control" id="pnr_edit_input"--}}
{{--                               style="text-transform:uppercase;letter-spacing:2px;font-weight:bold"--}}
{{--                               maxlength="20" placeholder="e.g. ABC123">--}}
{{--                    </div>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="small fw-semibold">Source / GDS <span class="text-danger">*</span></label>--}}
{{--                                <select class="form-control" id="pnr_edit_source">--}}
{{--                                    <option value="sabre">Sabre</option>--}}
{{--                                    <option value="travelport">Travelport</option>--}}
{{--                                    <option value="galileo">Galileo</option>--}}
{{--                                    <option value="amadeus">Amadeus</option>--}}
{{--                                    <option value="manual">Manual</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="col-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="small fw-semibold">Status <span class="text-danger">*</span></label>--}}
{{--                                <select class="form-control" id="pnr_edit_status">--}}
{{--                                    <option value="issue_request">Issue Request</option>--}}
{{--                                    <option value="pending">Pending</option>--}}
{{--                                    <option value="paid">Paid</option>--}}
{{--                                    <option value="booked">Booked</option>--}}
{{--                                    <option value="issued">Issued</option>--}}
{{--                                    <option value="ticketed">Ticketed</option>--}}
{{--                                    <option value="completed">Completed</option>--}}
{{--                                    <option value="failed">Failed</option>--}}
{{--                                    <option value="refunded">Refunded</option>--}}
{{--                                    <option value="cancelled">Cancelled</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="modal-footer py-2">--}}
{{--                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>--}}
{{--                    <button type="button" class="btn btn-info btn-sm text-white" id="btn-save-pnr">--}}
{{--                        <i class="fa fa-save"></i> Save Changes--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    --}}{{-- Assign Ticket Modals (per booking) --}}
{{--    @foreach($rows as $booking)--}}
{{--        <div class="modal fade" id="assign_ticket-{{ $booking->id }}" tabindex="-1">--}}
{{--            <div class="modal-dialog modal-lg">--}}
{{--                <div class="modal-content">--}}
{{--                    <div class="modal-header bg-success text-white py-2">--}}
{{--                        <h6 class="modal-title"><i class="fa fa-ticket"></i> Assign Tickets — {{ $booking->code ?? '#'.$booking->id }}</h6>--}}
{{--                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>--}}
{{--                    </div>--}}
{{--                    <form class="form-assign-tickets" data-booking-id="{{ $booking->id }}">--}}
{{--                        @csrf--}}
{{--                        <div class="modal-body">--}}
{{--                            <div class="table-responsive">--}}
{{--                                <table class="table table-bordered table-sm mb-0">--}}
{{--                                    <thead class="table-light">--}}
{{--                                    <tr>--}}
{{--                                        <th width="40">#</th>--}}
{{--                                        <th>Passenger</th>--}}
{{--                                        <th width="80">Type</th>--}}
{{--                                        <th width="200">Ticket Number</th>--}}
{{--                                    </tr>--}}
{{--                                    </thead>--}}
{{--                                    <tbody>--}}
{{--                                    @forelse($booking->passengers ?? collect() as $idx => $pax)--}}
{{--                                        <tr>--}}
{{--                                            <td class="text-center"><span class="badge badge-success">{{ $idx+1 }}</span></td>--}}
{{--                                            <td>--}}
{{--                                                <strong>{{ $pax->first_name }} {{ $pax->last_name }}</strong>--}}
{{--                                                <input type="hidden" name="passengers[{{ $idx }}][id]" value="{{ $pax->id }}">--}}
{{--                                            </td>--}}
{{--                                            <td><span class="badge badge-primary" style="font-size:10px">{{ $pax->traveler_type ?? 'ADT' }}</span></td>--}}
{{--                                            <td>--}}
{{--                                                <input type="text" class="form-control form-control-sm"--}}
{{--                                                       name="passengers[{{ $idx }}][ticket_number]"--}}
{{--                                                       value="{{ $pax->ticket_number }}" maxlength="13"--}}
{{--                                                       placeholder="13-digit ticket">--}}
{{--                                            </td>--}}
{{--                                        </tr>--}}
{{--                                    @empty--}}
{{--                                        <tr><td colspan="4" class="text-center text-muted py-3">No passengers</td></tr>--}}
{{--                                    @endforelse--}}
{{--                                    </tbody>--}}
{{--                                </table>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="modal-footer py-2">--}}
{{--                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>--}}
{{--                            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Assign</button>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endforeach--}}

{{--@endsection--}}


{{--@push('css')--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">--}}
{{--    <style>--}}
{{--        /* Issue Request badge */--}}
{{--        .badge-issue-request {--}}
{{--            background: linear-gradient(135deg,#f59e0b,#d97706);--}}
{{--            color:#fff;--}}
{{--            padding-left:16px;--}}
{{--            position:relative;--}}
{{--            animation: issueGlow 1.5s ease-in-out infinite;--}}
{{--        }--}}
{{--        .badge-issue-request::before {--}}
{{--            content:'';position:absolute;left:5px;top:50%;transform:translateY(-50%);--}}
{{--            width:6px;height:6px;border-radius:50%;background:#fff;--}}
{{--            animation:issueBlink 1s ease-in-out infinite;--}}
{{--        }--}}
{{--        @keyframes issueBlink{0%,100%{opacity:1}50%{opacity:0}}--}}
{{--        @keyframes issueGlow{0%,100%{box-shadow:0 0 4px rgba(245,158,11,.5)}50%{box-shadow:0 0 10px rgba(245,158,11,.9)}}--}}

{{--        /* Table tweaks */--}}
{{--        #bookingsDataTable td { vertical-align: middle; }--}}
{{--        #bookingsDataTable th { font-size: 12px; white-space: nowrap; }--}}

{{--        /* Compact dropdown */--}}
{{--        .dropdown-item { font-size: 13px; padding: 5px 14px; }--}}
{{--        .dropdown-item i { width: 16px; }--}}

{{--        /* Pagination compact */--}}
{{--        .pagination { margin-bottom: 0; }--}}
{{--        .page-link { font-size: 13px; padding: 4px 10px; }--}}

{{--        /* Responsive table on mobile */--}}
{{--        @media (max-width: 767px) {--}}
{{--            .card-body.p-0 { overflow-x: auto; }--}}
{{--            #bookingsDataTable { min-width: 900px; }--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}


{{--@push('js')--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>--}}
{{--    <script>--}}
{{--        $(document).ready(function () {--}}

{{--            // ── DataTable (client-side search + sort only, Laravel handles pagination) ──--}}
{{--            var table = $('#bookingsDataTable').DataTable({--}}
{{--                paging:    false,--}}
{{--                info:      false,--}}
{{--                searching: true,--}}
{{--                ordering:  true,--}}
{{--                order:     [[2, 'desc']],--}}
{{--                columnDefs: [--}}
{{--                    { orderable: false, targets: [0, 1, 15] }--}}
{{--                ],--}}
{{--                language: {--}}
{{--                    search: "{{ __('Search current page:') }}",--}}
{{--                    zeroRecords: "{{ __('No matching records') }}"--}}
{{--                }--}}
{{--            });--}}

{{--            // ── Check All ──--}}
{{--            $('#check-all').on('change', function () {--}}
{{--                $('.check-item').prop('checked', $(this).prop('checked'));--}}
{{--            });--}}

{{--            // ── Bulk Action ──--}}
{{--            $('#apply-bulk').on('click', function () {--}}
{{--                var action = $('#bulk_action').val();--}}
{{--                var ids    = $('.check-item:checked').map(function(){ return $(this).val(); }).get();--}}
{{--                if (!action) { alert('Please select an action'); return; }--}}
{{--                if (!ids.length) { alert('Please select at least one booking'); return; }--}}
{{--                if (!confirm('Apply "' + action + '" to ' + ids.length + ' booking(s)?')) return;--}}

{{--                var form = $('<form method="POST" action="{{ route('admin.bookings.bulkAction') }}">' +--}}
{{--                    '<input name="_token" value="{{ csrf_token() }}">' +--}}
{{--                    '<input name="action" value="' + action + '">' +--}}
{{--                    ids.map(function(id){ return '<input name="ids[]" value="' + id + '">'; }).join('') +--}}
{{--                    '</form>');--}}
{{--                $('body').append(form);--}}
{{--                form.submit();--}}
{{--            });--}}

{{--            // ── Booking Detail Modal ──--}}
{{--            $(document).on('click', '.btn-detail-booking', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                var id  = $(this).data('id');--}}
{{--                var url = $(this).data('ajax');--}}
{{--                $('.booking-id-display').text(id);--}}
{{--                $('#booking-detail-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');--}}
{{--                $.get(url, function(html){ $('#booking-detail-content').html(html); })--}}
{{--                    .fail(function(){ $('#booking-detail-content').html('<div class="alert alert-danger">Failed to load.</div>'); });--}}
{{--            });--}}

{{--            // ── Set Paid ──--}}
{{--            $(document).on('click', '.btn-set-paid', function () {--}}
{{--                var btn = $(this), id = btn.data('id');--}}
{{--                var amount = parseFloat($('#set_paid_input_' + id).val());--}}
{{--                if (!amount || amount <= 0) { alert('Enter a valid amount'); return; }--}}
{{--                if (!confirm('Add payment of ৳' + amount.toFixed(2) + '?')) return;--}}
{{--                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');--}}
{{--                $.ajax({--}}
{{--                    url: '/admin/module/booking/bookings/' + id + '/set-paid',--}}
{{--                    method: 'POST',--}}
{{--                    data: { remain: amount, _token: '{{ csrf_token() }}' },--}}
{{--                    dataType: 'json',--}}
{{--                    success: function(res) {--}}
{{--                        if (res.status || res.success) { alert('✅ ' + res.message); location.reload(); }--}}
{{--                        else { alert('❌ ' + (res.message || 'Failed')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Save'); }--}}
{{--                    },--}}
{{--                    error: function(xhr) {--}}
{{--                        alert('❌ ' + (xhr.responseJSON?.message || 'Error')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Save');--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}

{{--            // ── Duplicate ──--}}
{{--            $(document).on('click', '.btn-duplicate-booking', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                $('#dup_booking_id').val($(this).data('id'));--}}
{{--                $('#dup-code').text($(this).data('code'));--}}
{{--                $('#modal_duplicate').modal('show');--}}
{{--            });--}}

{{--            $('#btn-confirm-duplicate').on('click', function () {--}}
{{--                var btn = $(this), id = $('#dup_booking_id').val();--}}
{{--                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Duplicating...');--}}
{{--                $.ajax({--}}
{{--                    url: '/admin/module/booking/bookings/' + id + '/duplicate',--}}
{{--                    method: 'POST',--}}
{{--                    data: { _token: '{{ csrf_token() }}' },--}}
{{--                    dataType: 'json',--}}
{{--                    success: function(res) {--}}
{{--                        if (res.success) {--}}
{{--                            alert('✅ ' + res.message + '\nNew Code: ' + res.new_code);--}}
{{--                            if (res.edit_url) window.location.href = res.edit_url;--}}
{{--                            else location.reload();--}}
{{--                        } else {--}}
{{--                            alert('❌ ' + (res.message || 'Failed'));--}}
{{--                            btn.prop('disabled',false).html('<i class="fa fa-copy"></i> Confirm Duplicate');--}}
{{--                        }--}}
{{--                    },--}}
{{--                    error: function(xhr) {--}}
{{--                        alert('❌ ' + (xhr.responseJSON?.message || 'Error'));--}}
{{--                        btn.prop('disabled',false).html('<i class="fa fa-copy"></i> Confirm Duplicate');--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}

{{--            // ── Ticket Cancel ──--}}
{{--            $(document).on('click', '.btn-ticket-cancel', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                $('#cancel_booking_id').val($(this).data('id'));--}}
{{--                $('#cancel-booking-code').text($(this).data('code'));--}}
{{--                $('#cancel-booking-paid').text('৳' + parseFloat($(this).data('paid')||0).toFixed(2));--}}
{{--                $('#cancel_reason').val('');--}}
{{--                $('#modal_ticket_cancel').modal('show');--}}
{{--            });--}}

{{--            $('#btn-confirm-ticket-cancel').on('click', function () {--}}
{{--                var btn = $(this), id = $('#cancel_booking_id').val();--}}
{{--                if (!id) return;--}}
{{--                btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');--}}
{{--                $.ajax({--}}
{{--                    url: '/admin/module/booking/bookings/' + id + '/ticket-cancel',--}}
{{--                    method: 'POST',--}}
{{--                    data: { reason: $('#cancel_reason').val().trim(), _token: '{{ csrf_token() }}' },--}}
{{--                    dataType: 'json',--}}
{{--                    success: function(res) {--}}
{{--                        if (res.success) { alert('✅ ' + res.message); $('#modal_ticket_cancel').modal('hide'); location.reload(); }--}}
{{--                        else { alert('❌ ' + (res.message||'Failed')); btn.prop('disabled',false).html('<i class="fa fa-times-circle"></i> Confirm Cancel'); }--}}
{{--                    },--}}
{{--                    error: function(xhr) {--}}
{{--                        alert('❌ ' + (xhr.responseJSON?.message||'Error')); btn.prop('disabled',false).html('<i class="fa fa-times-circle"></i> Confirm Cancel');--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}

{{--            // ── Booking Cancel ──--}}
{{--            $(document).on('click', '.btn-booking-cancel', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                $('#booking-cancel-code').text($(this).data('code'));--}}
{{--                $('#booking_cancel_url').val($(this).data('url'));--}}
{{--                $('#modal_booking_cancel').modal('show');--}}
{{--            });--}}
{{--            $('#btn-confirm-booking-cancel').on('click', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                var url = $('#booking_cancel_url').val();--}}
{{--                if (url) { $(this).prop('disabled',true); window.location.href = url; }--}}
{{--            });--}}

{{--            // ── PNR Edit ──--}}
{{--            $(document).on('click', '.btn-pnr-edit', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                $('#pnr_edit_booking_id').val($(this).data('id'));--}}
{{--                $('#pnr-edit-code').text($(this).data('code'));--}}
{{--                $('#pnr_edit_input').val($(this).data('pnr')||'');--}}
{{--                $('#pnr_edit_source').val($(this).data('source')||'sabre');--}}
{{--                $('#pnr_edit_status').val($(this).data('status')||'booked');--}}
{{--                $('#modal_pnr_edit').modal('show');--}}
{{--            });--}}

{{--            $('#btn-save-pnr').on('click', function () {--}}
{{--                var btn = $(this), id = $('#pnr_edit_booking_id').val();--}}
{{--                var pnr = $('#pnr_edit_input').val().trim().toUpperCase();--}}
{{--                if (!pnr) { alert('PNR is required'); return; }--}}
{{--                btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');--}}
{{--                $.ajax({--}}
{{--                    url: '/admin/module/booking/bookings/' + id + '/update-pnr',--}}
{{--                    method: 'POST',--}}
{{--                    data: { pnr_id:pnr, source:$('#pnr_edit_source').val(), status:$('#pnr_edit_status').val(), _token:'{{ csrf_token() }}' },--}}
{{--                    dataType: 'json',--}}
{{--                    success: function(res) {--}}
{{--                        if (res.success) { alert('✅ ' + res.message); $('#modal_pnr_edit').modal('hide'); location.reload(); }--}}
{{--                        else { alert('❌ ' + (res.message||'Failed')); btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save'); }--}}
{{--                    },--}}
{{--                    error: function(xhr) {--}}
{{--                        alert('❌ ' + (xhr.responseJSON?.message||'Error')); btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');--}}
{{--                    }--}}
{{--                });--}}
{{--            });--}}

{{--            // ── Assign Tickets ──--}}
{{--            $(document).on('submit', '.form-assign-tickets', function (e) {--}}
{{--                e.preventDefault();--}}
{{--                var form = $(this), id = form.data('booking-id'), btn = form.find('[type=submit]');--}}
{{--                var passengers = [];--}}
{{--                $.each(form.serializeArray(), function(i,f){--}}
{{--                    var m = f.name.match(/passengers\[(\d+)\]\[(\w+)\]/);--}}
{{--                    if(m){ var idx=parseInt(m[1]); if(!passengers[idx]) passengers[idx]={}; passengers[idx][m[2]]=f.value; }--}}
{{--                });--}}
{{--                var valid = passengers.filter(function(p){ return p && p.ticket_number && p.ticket_number.trim(); });--}}
{{--                if(!valid.length){ alert('Enter at least one ticket number'); return; }--}}
{{--                if(!confirm('Assign ' + valid.length + ' ticket(s)?')) return;--}}
{{--                btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');--}}
{{--                $.ajax({--}}
{{--                    url: '{{ route('assign-tickets') }}', method:'POST',--}}
{{--                    data: { booking_id:id, passengers:valid, _token:'{{ csrf_token() }}' },--}}
{{--                    dataType: 'json',--}}
{{--                    success: function(res){--}}
{{--                        if(res.success){ alert('✅ '+res.message); form.closest('.modal').modal('hide'); setTimeout(function(){ location.reload(); },500); }--}}
{{--                        else{ alert('❌ '+(res.message||'Failed')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Assign'); }--}}
{{--                    },--}}
{{--                    error: function(xhr){ alert('❌ '+(xhr.responseJSON?.message||'Error')); btn.prop('disabled',false).html('<i class="fa fa-check"></i> Assign'); }--}}
{{--                });--}}
{{--            });--}}

{{--        });--}}

{{--        function printBookingDetail() {--}}
{{--            var c = document.getElementById('booking-detail-content').innerHTML;--}}
{{--            var w = window.open('','','height=600,width=800');--}}
{{--            w.document.write('<html><head><title>Booking</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></head><body>'+c+'</body></html>');--}}
{{--            w.document.close(); w.print();--}}
{{--        }--}}
{{--    </script>--}}
{{--@endpush--}}



@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-3">

        {{-- ── Page Header ── --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="title-bar mb-0">
                <i class="fa fa-plane"></i> {{ __('All Bookings') }}
            </h1>
            <button class="btn btn-outline-secondary btn-sm" id="btn-reload-table">
                <i class="fa fa-refresh"></i> {{ __('Refresh') }}
            </button>
        </div>

        @include('admin.message')

        {{-- ── Filters ── --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-end" id="filter-bar">

                    {{-- Bulk Action --}}
                    <div class="col-12 col-md-auto">
                        <label class="form-label small mb-1 fw-semibold">{{ __('Bulk Action') }}</label>
                        <div class="input-group input-group-sm">
                            <select name="bulk_action" id="bulk_action" class="form-select">
                                <option value="">{{ __('-- Select --') }}</option>
                                <option value="paid">{{ __('Mark Paid') }}</option>
                                <option value="booked">{{ __('Mark Booked') }}</option>
                                <option value="ticketed">{{ __('Mark Ticketed') }}</option>
                                <option value="completed">{{ __('Mark Completed') }}</option>
                                <option value="cancelled">{{ __('Mark Cancelled') }}</option>
                                <option value="delete">{{ __('Delete') }}</option>
                            </select>
                            <button type="button" id="apply-bulk" class="btn btn-info btn-sm">
                                <i class="fa fa-check"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-6 col-md-auto">
                        <label class="form-label small mb-1 fw-semibold">{{ __('Status') }}</label>
                        <select id="filter-status" class="form-select form-select-sm" style="min-width:130px">
                            <option value="">{{ __('All') }}</option>
                            @foreach([
                                'issue_request'=>'Issue Request','pending'=>'Pending','paid'=>'Paid',
                                'booked'=>'Booked','issued'=>'Issued','ticketed'=>'Ticketed',
                                'completed'=>'Completed','cancelled'=>'Cancelled',
                                'failed'=>'Failed','refunded'=>'Refunded',
                            ] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Column --}}
                    <div class="col-6 col-md-auto">
                        <label class="form-label small mb-1 fw-semibold">{{ __('Date Field') }}</label>
                        <select id="filter-date-col" class="form-select form-select-sm" style="min-width:155px">
                            <option value="created_at">Created At</option>
                            <option value="confirmed_at">Booking Date</option>
                            <option value="ticket_issued_at">Ticket Issued At</option>
                            <option value="booking_date">Cancel Date</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div class="col-6 col-md-auto">
                        <label class="form-label small mb-1 fw-semibold">{{ __('From') }}</label>
                        <input type="date" id="filter-date-from" class="form-control form-control-sm"
                               value="{{ now()->subMonths(2)->toDateString() }}">
                    </div>

                    {{-- Date To --}}
                    <div class="col-6 col-md-auto">
                        <label class="form-label small mb-1 fw-semibold">{{ __('To') }}</label>
                        <input type="date" id="filter-date-to" class="form-control form-control-sm"
                               value="{{ now()->toDateString() }}">
                    </div>

                    {{-- Filter / Reset Buttons --}}
                    <div class="col-auto">
                        <label class="form-label small mb-1 d-block">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="button" id="btn-apply-filter" class="btn btn-primary btn-sm">
                                <i class="fa fa-filter"></i> {{ __('Filter') }}
                            </button>
                            <button type="button" id="btn-reset-filter" class="btn btn-secondary btn-sm">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- PNR / Code Search --}}
                    <div class="col-12 col-md-auto ms-md-auto">
                        <label class="form-label small mb-1 fw-semibold">
                            <i class="fa fa-search"></i> {{ __('Search Full DB (PNR / Code / Ticket)') }}
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="pnr-search-input" class="form-control"
                                   placeholder="e.g. ABC123 or BK-00123"
                                   style="min-width:220px;text-transform:uppercase;letter-spacing:1px">
                            <button type="button" id="btn-pnr-search" class="btn btn-warning btn-sm">
                                <i class="fa fa-search"></i>
                            </button>
                            <button type="button" id="btn-pnr-clear" class="btn btn-outline-secondary btn-sm d-none">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div id="pnr-search-notice" class="text-warning small mt-1 d-none">
                            <i class="fa fa-info-circle"></i>
                            Searching entire DB — date &amp; status filters bypassed.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">

                {{-- DataTable built-in search box is hidden; we use the filter bar above --}}
                <div class="table-responsive">
                    <table id="bookingsDataTable"
                           class="table table-hover table-bordered table-sm mb-0"
                           style="width:100%">
                        <thead class="table-light">
                        <tr>
                            <th width="32px" class="text-center">
                                <input type="checkbox" id="check-all" title="Select All">
                            </th>
                            <th width="44px"  class="text-center">#</th>
                            <th width="110px">{{ __('Code') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th width="80px">{{ __('Source') }}</th>
                            <th width="80px">{{ __('Type') }}</th>
                            <th>{{ __('Route') }}</th>
                            <th>{{ __('Airline') }}</th>
                            <th>{{ __('PNR / TKT') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th width="100px">{{ __('Status') }}</th>
                            <th width="115px">{{ __('Ticket Issued') }}</th>
                            <th width="110px">{{ __('Issued By') }}</th>
                            <th width="110px">{{ __('Cancel Date') }}</th>
                            <th width="110px">{{ __('Booking Date') }}</th>
                            <th width="80px" class="text-center no-sort">{{ __('Actions') }}</th>
                        </tr>

                        {{-- ── Per-column search row ── --}}
                        <tr id="column-search-row" class="table-secondary">
                            <th></th>{{-- checkbox --}}
                            <th></th>{{-- serial --}}
                            <th><input class="form-control form-control-sm col-search" data-col="2" placeholder="Code"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="3" placeholder="Name / Email / Phone"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="4" placeholder="Source"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="5" placeholder="Type"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="6" placeholder="Route"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="7" placeholder="Airline"></th>
                            <th><input class="form-control form-control-sm col-search" data-col="8" placeholder="PNR / TKT"></th>
                            <th></th>{{-- payment --}}
                            <th><input class="form-control form-control-sm col-search" data-col="10" placeholder="Status"></th>
                            <th></th>{{-- issued at --}}
                            <th></th>{{-- issued by --}}
                            <th></th>{{-- cancel date --}}
                            <th></th>{{-- booking date --}}
                            <th></th>{{-- actions --}}
                        </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         GLOBAL MODALS
    ══════════════════════════════════════════ --}}

    {{-- Booking Detail Modal --}}
    <div class="modal fade" id="modal_booking_detail" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-plane"></i> Booking Details —
                        <span class="booking-id-display"></span>
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="booking-detail-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="printBookingDetail()">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Set Paid Modal (shared, filled dynamically) --}}
    <div class="modal fade" id="modal_set_paid" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-money"></i> Set Paid — <span id="paid-modal-code"></span>
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="paid_booking_id">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Total</div>
                                <div class="fw-bold" id="paid-modal-total">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 text-center bg-success bg-opacity-10">
                                <div class="text-muted small">Paid</div>
                                <div class="fw-bold text-success" id="paid-modal-paid">—</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 text-center bg-danger bg-opacity-10">
                                <div class="text-muted small">Due</div>
                                <div class="fw-bold text-danger" id="paid-modal-due">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="alert py-2 mb-3" id="paid-modal-credit-alert">
                        <i class="fa fa-wallet"></i>
                        <strong>Credit Balance:</strong>
                        <span id="paid-modal-credit">—</span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small fw-semibold">
                            Amount to Add <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="paid_amount_input"
                               min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-set-paid-confirm">
                        <i class="fa fa-check"></i> Save Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Duplicate Confirm Modal --}}
    <div class="modal fade" id="modal_duplicate" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-copy"></i> Duplicate Booking — <span id="dup-code"></span>
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="dup_booking_id">
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa fa-info-circle"></i>
                        A new booking will be created with <strong>all passengers and routes copied</strong>.
                        The new booking will have status <strong>booked</strong>.
                    </div>
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fa fa-exclamation-triangle"></i>
                        You can edit prices, passengers, and routes after duplication.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-confirm-duplicate">
                        <i class="fa fa-copy"></i> Confirm Duplicate
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Ticket Cancel Modal --}}
    <div class="modal fade" id="modal_ticket_cancel" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-times-circle"></i> Ticket Cancel —
                        <span id="cancel-booking-code"></span>
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cancel_booking_id">
                    <div class="alert alert-warning py-2 mb-3">
                        <strong>Paid Amount:</strong>
                        <span id="cancel-booking-paid" class="text-danger fw-bold"></span>
                        <div class="small mt-1">This amount will be refunded to credit balance.</div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small fw-semibold">
                            Reason <span class="text-muted">(optional)</span>
                        </label>
                        <textarea class="form-control form-control-sm" id="cancel_reason"
                                  rows="3" placeholder="Cancellation reason..."></textarea>
                    </div>
                    <div class="alert alert-danger py-2 mb-0 small">
                        <i class="fa fa-warning"></i> This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-confirm-ticket-cancel">
                        <i class="fa fa-times-circle"></i> Confirm Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Cancel Modal --}}
    <div class="modal fade" id="modal_booking_cancel" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-ban"></i> Booking Cancel —
                        <span id="booking-cancel-code"></span>
                    </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="booking_cancel_url">
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fa fa-exclamation-triangle"></i>
                        This will cancel the booking in GDS.
                        This action <strong>cannot be undone</strong>.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-dismiss="modal">No, Go Back</button>
                    <a href="#" id="btn-confirm-booking-cancel" class="btn btn-warning btn-sm">
                        <i class="fa fa-ban"></i> Yes, Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- PNR Edit Modal --}}
    <div class="modal fade" id="modal_pnr_edit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white py-2">
                    <h6 class="modal-title">
                        <i class="fa fa-edit"></i> Edit PNR / Source / Status —
                        <span id="pnr-edit-code"></span>
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pnr_edit_booking_id">
                    <div class="form-group">
                        <label class="small fw-semibold">
                            PNR / GDS Reference <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="pnr_edit_input"
                               style="text-transform:uppercase;letter-spacing:2px;font-weight:bold"
                               maxlength="20" placeholder="e.g. ABC123">
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small fw-semibold">
                                    Source / GDS <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="pnr_edit_source">
                                    <option value="sabre">Sabre</option>
                                    <option value="travelport">Travelport</option>
                                    <option value="galileo">Galileo</option>
                                    <option value="amadeus">Amadeus</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="small fw-semibold">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="pnr_edit_status">
                                    <option value="issue_request">Issue Request</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="booked">Booked</option>
                                    <option value="issued">Issued</option>
                                    <option value="ticketed">Ticketed</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="pnr_pending">PNR Pending</option>
                                    <option value="voided">Voided</option>
                                    <option value="partial_refunded">Partial Refunded</option>
                                    <option value="partial_reissued">Partial Reissued</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info btn-sm text-white" id="btn-save-pnr">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('css')
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>

        /* ── Issue Request badge ─────────────────────────── */
        .badge-issue-request {
            background: linear-gradient(135deg,#f59e0b,#d97706);
            color: #fff;
            padding-left: 16px;
            position: relative;
            animation: issueGlow 1.5s ease-in-out infinite;
        }
        .badge-issue-request::before {
            content:''; position:absolute; left:5px; top:50%;
            transform:translateY(-50%);
            width:6px; height:6px; border-radius:50%; background:#fff;
            animation: issueBlink 1s ease-in-out infinite;
        }
        @keyframes issueBlink  { 0%,100%{opacity:1} 50%{opacity:0} }
        @keyframes issueGlow   { 0%,100%{box-shadow:0 0 4px rgba(245,158,11,.5)}
            50%{box-shadow:0 0 10px rgba(245,158,11,.9)} }

        /* ── Table tweaks ────────────────────────────────── */
        #bookingsDataTable td  { vertical-align: middle; }
        #bookingsDataTable th  { font-size: 12px; white-space: nowrap; }

        /* ── Column-search row inputs ────────────────────── */
        #column-search-row input {
            font-size: 11px;
            padding: 2px 5px;
            height: 26px;
        }

        /* ── DataTable: hide default search + length ──────── */
        #bookingsDataTable_wrapper .dataTables_filter,
        #bookingsDataTable_wrapper .dataTables_length { display: none !important; }

        /* ── Compact dropdown ────────────────────────────── */
        .dropdown-item { font-size: 13px; padding: 5px 14px; }
        .dropdown-item i { width: 16px; }

        /* ── Pagination compact ──────────────────────────── */
        .pagination { margin-bottom: 0; }
        .page-link  { font-size: 13px; padding: 4px 10px; }

        /* ── PNR search highlight ────────────────────────── */
        #pnr-search-input:not(:placeholder-shown) {
            border-color: #ffc107;
            background: #fffbf0;
            font-weight: bold;
        }

        /* ── Mobile ──────────────────────────────────────── */
        @media (max-width: 767px) {
            .card-body.p-0  { overflow-x: auto; }
            #bookingsDataTable { min-width: 900px; }
        }

        .dropdown-menu-up {
            top: auto !important;
            bottom: 100% !important;
            margin-bottom: 2px;
        }

        /* Table overflow fix */
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
        }
        .card-body.p-0 {
            overflow: visible !important;
        }
    </style>
@endpush


@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {


// ✅ এটা দাও — dropdown body তে move করে
            $(document).on('show.bs.dropdown', function (e) {
                var dropdown = $(e.target);
                var menu     = dropdown.find('.dropdown-menu');
                var toggle   = dropdown.find('[data-toggle="dropdown"]');

                $('body').append(menu.detach());

                var offset = toggle.offset();
                var toggleH = toggle.outerHeight();
                var menuW   = menu.outerWidth();
                var spaceBelow = $(window).height() - (offset.top - $(window).scrollTop()) - toggleH;

                if (spaceBelow < 220) {
                    menu.css({
                        position : 'fixed',
                        top      : (offset.top - $(window).scrollTop() - menu.outerHeight()) + 'px',
                        left     : (offset.left + toggle.outerWidth() - menuW) + 'px',
                        zIndex   : 9999,
                        display  : 'block',
                    });
                } else {
                    menu.css({
                        position : 'fixed',
                        top      : (offset.top - $(window).scrollTop() + toggleH) + 'px',
                        left     : (offset.left + toggle.outerWidth() - menuW) + 'px',
                        zIndex   : 9999,
                        display  : 'block',
                    });
                }
            }).on('hide.bs.dropdown', function (e) {
                var menu = $(e.target).find('.dropdown-menu');
                if (!menu.length) {
                    // body তে আছে
                    $('body > .dropdown-menu').remove();
                }
                menu.css('display', '');
            });

            /* ═══════════════════════════════════════════════════
               1. SERVER-SIDE DATATABLE
            ═══════════════════════════════════════════════════ */
            var table = $('#bookingsDataTable').DataTable({
                processing : true,
                serverSide : true,
                searching  : true,   // global search goes to server
                ordering   : true,
                order      : [[1, 'desc']],
                pageLength : 50,
                lengthMenu : [25, 50, 100, 200],

                ajax: {
                    url  : '{{ route("admin.bookings.datatable") }}',
                    type : 'GET',
                    data : function (d) {
                        d.status      = $('#filter-status').val();
                        d.date_column = $('#filter-date-col').val();
                        d.date_from   = $('#filter-date-from').val();
                        d.date_to     = $('#filter-date-to').val();
                        d.pnr_search  = $('#pnr-search-input').val().trim().toUpperCase();

                        // ✅ এটা যোগ করো — column search values
                        d.col_code     = $('.col-search[data-col="2"]').val();
                        d.col_customer = $('.col-search[data-col="3"]').val();
                        d.col_source   = $('.col-search[data-col="4"]').val();
                        d.col_type     = $('.col-search[data-col="5"]').val();
                        d.col_route    = $('.col-search[data-col="6"]').val();
                        d.col_airline  = $('.col-search[data-col="7"]').val();
                        d.col_pnr      = $('.col-search[data-col="8"]').val();
                        d.col_status   = $('.col-search[data-col="10"]').val();
                    },
                },

                columns: [
                    { data: 'checkbox',     orderable: false, searchable: false, className: 'text-center' },
                    { data: 'serial',       orderable: false, searchable: false, className: 'text-center text-muted small' },
                    { data: 'code' },
                    { data: 'customer',     searchable: true },
                    { data: 'source',       searchable: true },
                    { data: 'type',         searchable: true },
                    { data: 'route',        searchable: true },
                    { data: 'airline',      searchable: true },
                    { data: 'pnr_tkt',      searchable: true },
                    { data: 'payment',      orderable: false, searchable: false },
                    { data: 'status',       searchable: true },
                    { data: 'issued_at',    orderable: false, searchable: false },
                    { data: 'issued_by',    orderable: false, searchable: false },
                    { data: 'cancel_date',  orderable: false, searchable: false },
                    { data: 'booking_date', orderable: false, searchable: false },
                    { data: 'actions',      orderable: false, searchable: false, className: 'text-center' },
                ],

                columnDefs: [
                    { targets: [0, 1, 15], orderable: false }
                ],

                language: {
                    processing    : '<div class="spinner-border text-primary" style="width:1.5rem;height:1.5rem"></div>',
                    zeroRecords   : '<div class="text-muted py-3"><i class="fa fa-inbox fa-2x d-block mb-2"></i>No bookings found</div>',
                    info          : 'Showing _START_–_END_ of _TOTAL_ bookings',
                    infoFiltered  : '(filtered from _MAX_ total)',
                    paginate      : { previous: '«', next: '»' },
                },

                drawCallback: function () {
                    // Re-attach check-all state after every draw
                    $('#check-all').prop('checked', false);
                }
            });


            /* ═══════════════════════════════════════════════════
               2. FILTER BAR
            ═══════════════════════════════════════════════════ */

            // Apply filter button
            $('#btn-apply-filter').on('click', function () {
                // Clear PNR search when using filters
                $('#pnr-search-input').val('');
                hidePnrNotice();
                table.ajax.reload();
            });

            // Reset filter
            $('#btn-reset-filter').on('click', function () {
                $('#filter-status').val('');
                $('#filter-date-col').val('created_at');
                $('#filter-date-from').val('{{ now()->subMonths(2)->toDateString() }}');
                $('#filter-date-to').val('{{ now()->toDateString() }}');
                $('#pnr-search-input').val('');
                hidePnrNotice();
                table.search('').columns().search('').ajax.reload();
            });

            // Refresh button
            $('#btn-reload-table').on('click', function () {
                table.ajax.reload(null, false); // false = keep current page
            });


            /* ═══════════════════════════════════════════════════
               3. PNR / CODE FULL-DB SEARCH
               Bypasses date & status filters in controller
            ═══════════════════════════════════════════════════ */

            function triggerPnrSearch() {
                var val = $('#pnr-search-input').val().trim();
                if (val === '') {
                    hidePnrNotice();
                } else {
                    showPnrNotice();
                }
                table.ajax.reload();
            }

            function showPnrNotice() {
                $('#pnr-search-notice').removeClass('d-none');
                $('#btn-pnr-clear').removeClass('d-none');
            }
            function hidePnrNotice() {
                $('#pnr-search-notice').addClass('d-none');
                $('#btn-pnr-clear').addClass('d-none');
            }

            $('#btn-pnr-search').on('click', triggerPnrSearch);

            $('#pnr-search-input').on('keypress', function (e) {
                if (e.which === 13) triggerPnrSearch();
            });

            // Auto-uppercase
            $('#pnr-search-input').on('input', function () {
                this.value = this.value.toUpperCase();
            });

            $('#btn-pnr-clear').on('click', function () {
                $('#pnr-search-input').val('');
                hidePnrNotice();
                table.ajax.reload();
            });


            /* ═══════════════════════════════════════════════════
               4. PER-COLUMN SEARCH (searches entire DB via server)
               Each input maps to its column index → DataTable
               sends column[N][search][value] to server
               Controller must handle per-column search if needed,
               OR use the global search box below for simplicity.
               Here we route per-column input → DataTable global
               search so the controller's single search block works.
            ═══════════════════════════════════════════════════ */

            // ✅ এটা দাও
            var colSearchTimer;
            $(document).on('keyup', '.col-search', function () {
                clearTimeout(colSearchTimer);
                colSearchTimer = setTimeout(function () {
                    table.ajax.reload();
                }, 500);
            });

            // Note: for column search to actually filter on the server,
            // add this to your datatable() controller method:
            //
            // $colSearches = $request->input('columns', []);
            // foreach ($colSearches as $col) {
            //     $sv = $col['search']['value'] ?? '';
            //     if ($sv === '') continue;
            //     $name = $col['name'] ?? '';
            //     // map col name → db column and apply ->where(...)
            // }
            //
            // Or keep client-side only (works fine for small pages).


            /* ═══════════════════════════════════════════════════
               5. CHECK ALL / BULK ACTION
            ═══════════════════════════════════════════════════ */

            $('#check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            $('#apply-bulk').on('click', function () {
                var action = $('#bulk_action').val();
                var ids    = $('.check-item:checked').map(function(){ return $(this).val(); }).get();
                if (!action)     { alert('Please select an action'); return; }
                if (!ids.length) { alert('Please select at least one booking'); return; }
                if (!confirm('Apply "' + action + '" to ' + ids.length + ' booking(s)?')) return;

                var form = $('<form method="POST" action="{{ route('admin.bookings.bulkAction') }}">'
                    + '<input name="_token" value="{{ csrf_token() }}">'
                    + '<input name="action" value="' + action + '">'
                    + ids.map(function(id){ return '<input name="ids[]" value="'+id+'">'; }).join('')
                    + '</form>');
                $('body').append(form);
                form.submit();
            });


            /* ═══════════════════════════════════════════════════
               6. BOOKING DETAIL MODAL
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-detail-booking', function (e) {
                e.preventDefault();
                var id  = $(this).data('id');
                var url = $(this).data('ajax');
                $('.booking-id-display').text(id);
                $('#booking-detail-content').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>'
                );
                $.get(url, function(html) {
                    $('#booking-detail-content').html(html);
                }).fail(function() {
                    $('#booking-detail-content').html(
                        '<div class="alert alert-danger">Failed to load.</div>'
                    );
                });
            });


            /* ═══════════════════════════════════════════════════
               7. SET PAID (shared modal, filled from row data)
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-set-paid-open', function (e) {
                e.preventDefault();
                var id      = $(this).data('id');
                var code    = $(this).data('code');
                var total   = parseFloat($(this).data('total') || 0);
                var paid    = parseFloat($(this).data('paid')  || 0);
                var credit  = parseFloat($(this).data('credit')|| 0);
                var due     = total - paid;

                $('#paid_booking_id').val(id);
                $('#paid-modal-code').text(code);
                $('#paid-modal-total').text('৳' + total.toFixed(2));
                $('#paid-modal-paid').text('৳'  + paid.toFixed(2));
                $('#paid-modal-due').text('৳'   + due.toFixed(2));
                $('#paid-modal-credit').text('৳' + credit.toFixed(2));
                $('#paid-modal-credit-alert')
                    .removeClass('alert-success alert-danger')
                    .addClass(credit > 0 ? 'alert-success' : 'alert-danger');
                $('#paid_amount_input').val(due > 0 ? due.toFixed(2) : '');
                $('#modal_set_paid').modal('show');
            });

            $('#btn-set-paid-confirm').on('click', function () {
                var btn    = $(this);
                var id     = $('#paid_booking_id').val();
                var amount = parseFloat($('#paid_amount_input').val());
                if (!amount || amount <= 0) { alert('Enter a valid amount'); return; }
                if (!confirm('Add payment of ৳' + amount.toFixed(2) + '?')) return;
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url    : '/admin/module/booking/bookings/' + id + '/set-paid',
                    method : 'POST',
                    data   : { remain: amount, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status || res.success) {
                            alert('✅ ' + res.message);
                            $('#modal_set_paid').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            alert('❌ ' + (res.message || 'Failed'));
                            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                        }
                    },
                    error: function(xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || 'Error'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save');
                    }
                });
            });


            /* ═══════════════════════════════════════════════════
               8. DUPLICATE
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-duplicate-booking', function (e) {
                e.preventDefault();
                $('#dup_booking_id').val($(this).data('id'));
                $('#dup-code').text($(this).data('code'));
                $('#modal_duplicate').modal('show');
            });

            $('#btn-confirm-duplicate').on('click', function () {
                var btn = $(this);
                var id  = $('#dup_booking_id').val();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Duplicating...');

                $.ajax({
                    url    : '/admin/module/booking/bookings/' + id + '/duplicate',
                    method : 'POST',
                    data   : { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            alert('✅ ' + res.message + '\nNew Code: ' + res.new_code);
                            if (res.edit_url) window.location.href = res.edit_url;
                            else { $('#modal_duplicate').modal('hide'); table.ajax.reload(null, false); }
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


            /* ═══════════════════════════════════════════════════
               9. TICKET CANCEL
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-ticket-cancel', function (e) {
                e.preventDefault();
                $('#cancel_booking_id').val($(this).data('id'));
                $('#cancel-booking-code').text($(this).data('code'));
                $('#cancel-booking-paid').text('৳' + parseFloat($(this).data('paid') || 0).toFixed(2));
                $('#cancel_reason').val('');
                $('#modal_ticket_cancel').modal('show');
            });

            $('#btn-confirm-ticket-cancel').on('click', function () {
                var btn = $(this);
                var id  = $('#cancel_booking_id').val();
                if (!id) return;
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url    : '/admin/module/booking/bookings/' + id + '/ticket-cancel',
                    method : 'POST',
                    data   : { reason: $('#cancel_reason').val().trim(), _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#modal_ticket_cancel').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            alert('❌ ' + (res.message || 'Failed'));
                            btn.prop('disabled', false).html('<i class="fa fa-times-circle"></i> Confirm Cancel');
                        }
                    },
                    error: function(xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || 'Error'));
                        btn.prop('disabled', false).html('<i class="fa fa-times-circle"></i> Confirm Cancel');
                    }
                });
            });


            /* ═══════════════════════════════════════════════════
               10. BOOKING CANCEL
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-booking-cancel', function (e) {
                e.preventDefault();
                $('#booking-cancel-code').text($(this).data('code'));
                $('#booking_cancel_url').val($(this).data('url'));
                $('#modal_booking_cancel').modal('show');
            });

            $('#btn-confirm-booking-cancel').on('click', function (e) {
                e.preventDefault();
                var url = $('#booking_cancel_url').val();
                if (url) { $(this).prop('disabled', true); window.location.href = url; }
            });


            /* ═══════════════════════════════════════════════════
               11. PNR EDIT
            ═══════════════════════════════════════════════════ */

            $(document).on('click', '.btn-pnr-edit', function (e) {
                e.preventDefault();
                $('#pnr_edit_booking_id').val($(this).data('id'));
                $('#pnr-edit-code').text($(this).data('code'));
                $('#pnr_edit_input').val($(this).data('pnr') || '');
                $('#pnr_edit_source').val($(this).data('source') || 'sabre');
                $('#pnr_edit_status').val($(this).data('status') || 'booked');
                $('#modal_pnr_edit').modal('show');
            });

            $('#btn-save-pnr').on('click', function () {
                var btn = $(this);
                var id  = $('#pnr_edit_booking_id').val();
                var pnr = $('#pnr_edit_input').val().trim().toUpperCase();
                if (!pnr) { alert('PNR is required'); return; }
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url    : '/admin/module/booking/bookings/' + id + '/update-pnr',
                    method : 'POST',
                    data   : {
                        pnr_id : pnr,
                        source : $('#pnr_edit_source').val(),
                        status : $('#pnr_edit_status').val(),
                        _token : '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#modal_pnr_edit').modal('hide');
                            table.ajax.reload(null, false);
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

        });

        /* ── Print helper ───────────────────────────────────────── */
        function printBookingDetail() {
            var c = document.getElementById('booking-detail-content').innerHTML;
            var w = window.open('', '', 'height=600,width=800');
            w.document.write(
                '<html><head><title>Booking</title>'
                + '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">'
                + '</head><body>' + c + '</body></html>'
            );
            w.document.close();
            w.print();
        }
    </script>
@endpush
