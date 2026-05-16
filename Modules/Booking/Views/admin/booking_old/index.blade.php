@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">
                <i class="fa fa-plane"></i> {{ __('All Bookings') }}
            </h1>
            <div>
                <a href="{{ route('bookings.index') }}" class="btn btn-primary">
                    <i class="fa fa-refresh"></i> {{ __('Refresh') }}
                </a>
            </div>
        </div>

        @include('admin.message')

        {{-- ✅ Bulk Actions --}}
        <div class="filter-div d-flex justify-content-between mt-3">
            <div class="col-left">
                <form method="post" action="{{ route('admin.bookings.bulkAction') }}" class="filter-form filter-form-left d-flex">
                    @csrf
                    <select name="action" class="form-control">
                        <option value="">{{ __('-- Bulk Actions --') }}</option>
                        <option value="paid">{{ __('Mark as Paid') }}</option>
                        <option value="booked">{{ __('Mark as Booked') }}</option>
                        <option value="ticketed">{{ __('Mark as Ticketed') }}</option>
                        <option value="completed">{{ __('Mark as Completed') }}</option>
                        <option value="cancelled">{{ __('Mark as Cancelled') }}</option>
                        <option value="delete">{{ __('Delete') }}</option>
                    </select>
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Apply') }}</button>
                </form>
            </div>

            {{-- ✅ Status Filter + Count --}}
            <div class="col-right d-flex align-items-center">
                <form method="GET" action="{{ route('bookings.index') }}" class="d-flex align-items-center mr-3">
                    <select name="status" class="form-control mr-2" onchange="this.form.submit()" style="min-width: 160px;">
                        <option value="">{{ __('-- All Status --') }}</option>
                        @foreach([
                            'issue_request' => 'Issue Request',
                            'pending'       => 'Pending',
                            'paid'          => 'Paid',
                            'booked'        => 'Booked',
                            'issued'        => 'Issued',
                            'ticketed'      => 'Ticketed',
                            'completed'     => 'Completed',
                            'cancelled'     => 'Cancelled',
                            'failed'        => 'Failed',
                            'refunded'      => 'Refunded',
                        ] as $value => $label)
                            <option value="{{ $value }}" {{ ($selectedStatus ?? '') == $value ? 'selected' : '' }}>
                                {{ __($label) }}
                            </option>
                        @endforeach
                    </select>
                    @if(!empty($selectedStatus))
                        <a href="{{ route('bookings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-times"></i> {{ __('Clear') }}
                        </a>
                    @endif
                </form>
                <p class="mb-0"><i>{{ __('Found :total items', ['total' => $rows->count()]) }}</i></p>
            </div>
        </div>

        {{-- ✅ Bookings Table --}}
        <div class="panel booking-history-manager mt-3">
            <div class="panel-title">{{ __('Bookings') }}</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="bookingsDataTable" class="table table-hover table-bordered" style="width:100%">
                        <thead class="thead-light">
                        <tr>
                            <th width="40px"><input type="checkbox" class="check-all"></th>
                            <th width="50px">{{ __('SL') }}</th>
                            <th width="120px">{{ __('Booking Code') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th width="100px">{{ __('Source') }}</th>
                            <th width="100px">{{ __('Type') }}</th>
                            <th>{{ __('Route') }}</th>
                            <th>{{ __('Airline') }}</th>
                            <th>{{ __('PNR & Tickets') }}</th>
                            <th>{{ __('Payment Info') }}</th>
                            <th width="100px">{{ __('Status') }}</th>
{{--                            <th width="120px">{{ __('Issued Name') }}</th>--}}
{{--                            <th width="120px">{{ __('Issue Date') }}</th>--}}
                            <th width="120px">{{ __('Booking cancel') }}</th>
                            <th width="120px">{{ __('Booking Date') }}</th>
                            <th width="150px">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $booking)
                            @php
                                $statusColors = [
                                    'pending'       => 'warning',
                                    'paid'          => 'info',
                                    'booked'        => 'primary',
                                    'issued'        => 'success',
                                    'issue_request' => 'issue-request',
                                    'ticketed'      => 'success',
                                    'completed'     => 'success',
                                    'cancelled'     => 'danger',
                                    'failed'        => 'danger',
                                    'refunded'      => 'secondary',
                                ];
                                $statusColor = $statusColors[$booking->status] ?? 'secondary';

                                $sourceColors = [
                                    'sabre'      => 'primary',
                                    'travelport' => 'info',
                                    'galileo'    => 'warning',
                                    'amadeus'    => 'success',
                                    'manual'     => 'secondary',
                                ];
                                $sourceColor = $sourceColors[$booking->source] ?? 'secondary';
                            @endphp
                            <tr data-status="{{ $booking->status }}">

                                {{-- Checkbox --}}
                                <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $booking->id }}"></td>

                                {{-- ✅ Serial Number — JS দিয়ে fill হবে --}}
                                <td class="serial-number text-center">—</td>

                                {{-- ✅ Booking Code --}}
                                <td>
                                    <strong class="text-primary">{{ $booking->code ?? '#'.$booking->id }}</strong>
                                </td>

                                {{-- Customer --}}
                                <td>
                                    <strong>{{ $booking->first_name }} {{ $booking->last_name }}</strong><br>
                                    <small><i class="fa fa-envelope"></i> {{ $booking->email }}</small><br>
                                    <small><i class="fa fa-phone"></i> {{ $booking->phone }}</small>
                                    @if($booking->customer_notes)
                                        <br><small class="text-muted"><i class="fa fa-sticky-note"></i> {{ Str::limit($booking->customer_notes, 50) }}</small>
                                    @endif
                                </td>

                                {{-- Source --}}
                                <td>
                                    <span class="badge badge-{{ $sourceColor }}">
                                        {{ ucfirst($booking->source) }}
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td>
                                    <span class="badge badge-light">{{ ucfirst(str_replace('_', ' ', $booking->flight_type)) }}</span>
                                </td>

                                {{-- Route --}}
                                <td>
                                    <strong>{{ $booking->flight_from }}</strong>
                                    <i class="fa fa-arrow-right"></i>
                                    <strong>{{ $booking->flight_to }}</strong>
                                </td>

                                {{-- Airline --}}
                                <td>{{ $booking->airline }}</td>

                                {{-- PNR & Tickets --}}
                                <td>
                                    <div><strong>PNR:</strong> <span class="text-primary">{{ $booking->pnr_id ?? 'N/A' }}</span></div>
                                    @php
                                        $tickets = $booking->ticket_number ? json_decode($booking->ticket_number, true) : [];
                                    @endphp
                                    <div><strong>TKT:</strong> <small>{{ !empty($tickets) ? implode(', ', $tickets) : 'N/A' }}</small></div>
                                </td>

                                {{-- Payment Info --}}
                                <td>
                                    <div><strong>Total:</strong> <span class="text-primary">{{ format_money_main($booking->total) }}</span></div>
                                    <div><strong>Paid:</strong> <span class="text-success">{{ format_money_main($booking->paid) }}</span></div>
                                    <div><strong>Due:</strong> <span class="text-danger">{{ format_money_main($booking->total - $booking->paid) }}</span></div>
                                </td>

                                {{-- Status --}}
                                <td data-order="{{ $booking->status }}">
                                    <span class="badge badge-{{ $statusColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                    </span>
                                </td>

                                {{-- Booking Date --}}
                                <td>
                                    @if($booking->booking_date)
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($booking->booking_date)->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- ✅ Confirmed At --}}
                                <td>
                                    @if($booking->confirmed_at)
                                        <span class="text-success">
                                            {{ \Carbon\Carbon::parse($booking->confirmed_at)->format('d M Y') }}<br>
                                            <small>{{ \Carbon\Carbon::parse($booking->confirmed_at)->format('h:i A') }}</small>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            {{ __('Actions') }}
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">

                                            {{-- View Detail --}}
                                            @if(auth()->user()->hasPermission('booking_details'))
                                                <a class="dropdown-item btn-detail-booking" href="#"
                                                   data-ajax="{{ route('booking.modal', $booking->id) }}"
                                                   data-toggle="modal"
                                                   data-id="{{ $booking->id }}"
                                                   data-target="#modal_booking_detail">
                                                    <i class="fa fa-eye"></i> {{ __('Detail') }}
                                                </a>
                                            @endif

                                            {{-- Set Paid --}}
                                            @if(auth()->user()->hasPermission('booking_setpaid'))
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                   data-target="#modal-paid-{{ $booking->id }}">
                                                    <i class="fa fa-money"></i> {{ __('Set Paid') }}
                                                </a>
                                            @endif

                                            {{-- ✅ PNR & Source & Status Edit — cancelled ছাড়া সব status এ --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_edit'))
                                                @if($booking->status !== 'cancelled')
                                                    <a class="dropdown-item btn-pnr-edit" href="#"
                                                       data-id="{{ $booking->id }}"
                                                       data-pnr="{{ $booking->pnr_id }}"
                                                       data-source="{{ $booking->source }}"
                                                       data-status="{{ $booking->status }}"
                                                       data-code="{{ $booking->code ?? '#'.$booking->id }}">
                                                        <i class="fa fa-edit"></i> {{ __('Edit PNR / Source') }}
                                                    </a>
                                                @endif
                                            @endif

                                            <div class="dropdown-divider"></div>

                                            {{-- Issue Ticket --}}
                                            @if(auth()->user()->hasPermission('booking_issue_ticket'))
                                                @if($booking->pay_now == 0 && $booking->status == 'booked' && $booking->source == 'sabre')
                                                    <a class="dropdown-item"
                                                       href="{{ route('report.admin.booking.issueTicket', ['id' => $booking->id]) }}">
                                                        <i class="fa fa-ticket"></i> {{ __('Issue Ticket') }}
                                                    </a>
                                                @endif
                                            @endif

                                            {{-- PNR Check --}}
                                            @if(auth()->user()->hasPermission('booking_pnr_check'))
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.booking.pnrcheck', ['id' => $booking->id]) }}">
                                                    <i class="fa fa-search"></i> {{ __('PNR Check') }}
                                                </a>
                                            @endif

                                            {{-- Booking Cancel --}}
                                            @if(auth()->user()->hasPermission('booking_cancel'))
                                                @if($booking->status == 'booked')
                                                    <a class="dropdown-item text-warning btn-booking-cancel" href="#"
                                                       data-id="{{ $booking->id }}"
                                                       data-code="{{ $booking->code ?? '#'.$booking->id }}"
                                                       data-url="{{ route('booking.cancel', $booking->id) }}">
                                                        <i class="fa fa-ban"></i> {{ __('Booking Cancel') }}
                                                    </a>
                                                @endif
                                            @endif

                                            {{-- ✅ Ticket Cancel --}}
                                            @if(auth()->user()->hasPermission('booking_cancel_ticket'))
                                                @if(in_array($booking->status, ['ticketed', 'issued', 'completed']))
                                                    <a class="dropdown-item text-danger btn-ticket-cancel" href="#"
                                                       data-id="{{ $booking->id }}"
                                                       data-code="{{ $booking->code ?? '#'.$booking->id }}"
                                                       data-paid="{{ $booking->paid }}">
                                                        <i class="fa fa-times-circle"></i> {{ __('Ticket Cancel') }}
                                                    </a>
                                                @endif
                                            @endif

                                            <div class="dropdown-divider"></div>

                                            {{-- Print Ticket --}}
                                            @if(auth()->user()->hasPermission('booking_print_ticket'))
                                                @if(in_array($booking->status, ['issued', 'completed']))
                                                    <a class="dropdown-item"
                                                       href="{{ route('report.admin.booking.ticket', ['id' => $booking->id]) }}"
                                                       target="_blank">
                                                        <i class="fa fa-print"></i> {{ __('Print Ticket') }}
                                                    </a>
                                                @endif
                                            @endif

                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- ✅ Set Paid Modal --}}
                            <div class="modal fade" id="modal-paid-{{ $booking->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fa fa-money"></i> {{ __('Set Paid — :code', ['code' => $booking->code ?? '#'.$booking->id]) }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="alert alert-info">
                                                        <p class="mb-1"><strong>{{ __('Total Amount') }}</strong></p>
                                                        <h4 class="mb-0">{{ format_money_main($booking->total) }}</h4>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="alert alert-success">
                                                        <p class="mb-1"><strong>{{ __('Already Paid') }}</strong></p>
                                                        <h4 class="mb-0">{{ format_money_main($booking->paid) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning">
                                                <p class="mb-1"><strong>{{ __('Remaining Amount') }}</strong></p>
                                                <h3 class="mb-0 text-danger">{{ format_money_main($booking->total - $booking->paid) }}</h3>
                                            </div>
                                            <hr>
                                            <div class="alert alert-{{ $booking->user && $booking->user->credit_balance > 0 ? 'success' : 'danger' }} mb-3">
                                                <i class="fa fa-wallet"></i>
                                                <strong>{{ __('Credit Balance') }}:</strong>
                                                <span class="font-weight-bold">
                                                    {{ $booking->user ? format_money_main($booking->user->credit_balance) : '৳0.00' }}
                                                </span>
                                                @if(!$booking->user || $booking->user->credit_balance <= 0)
                                                    <small class="d-block mt-1">
                                                        <i class="fa fa-exclamation-triangle"></i> {{ __('Insufficient credit balance') }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="set_paid_input_{{ $booking->id }}">
                                                    <i class="fa fa-money"></i> {{ __('Enter Paid Amount') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="number"
                                                       class="form-control form-control-lg"
                                                       id="set_paid_input_{{ $booking->id }}"
                                                       placeholder="{{ __('Enter amount') }}"
                                                       min="0" step="0.01"
                                                       value="{{ $booking->total - $booking->paid }}"
                                                       required>
                                                <small class="form-text text-muted">{{ __('This amount will be added to the current paid amount') }}</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fa fa-times"></i> {{ __('Cancel') }}
                                            </button>
                                            <button type="button" class="btn btn-primary btn-set-paid" data-id="{{ $booking->id }}">
                                                <i class="fa fa-check"></i> {{ __('Save Payment') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="14" class="text-center">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> {{ __('No bookings found') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         ✅ GLOBAL MODALS (loop এর বাইরে — একটাই instance)
    ═══════════════════════════════════════════════════════ --}}

    {{-- ✅ Booking Detail Modal --}}
    <div class="modal fade" id="modal_booking_detail" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-plane"></i> {{ __('Booking Details') }} — <span class="booking-id-display"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="booking-detail-content">
                    <div class="d-flex justify-content-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">{{ __('Loading...') }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ __('Close') }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printBookingDetail()">
                        <i class="fa fa-print"></i> {{ __('Print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Passenger Edit Modal --}}
    <div class="modal fade" id="modal_passenger_edit" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fa fa-user"></i> {{ __('Edit Passenger') }} — <span id="passenger-edit-name"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="form-passenger-edit" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="passenger_edit_id" name="passenger_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-id-card"></i> {{ __('Passport Number') }}</label>
                                    <input type="text" class="form-control" id="passenger_passport"
                                           name="passport_number" placeholder="e.g. AB1234567">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-calendar"></i> {{ __('Passport Expiry Date') }}</label>
                                    <input type="date" class="form-control" id="passenger_passport_expiry"
                                           name="passport_expiry_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-birthday-cake"></i> {{ __('Date of Birth') }}</label>
                                    <input type="date" class="form-control" id="passenger_dob"
                                           name="dob">
                                </div>
                            </div>
                        </div>
                        <hr>
                        {{-- Passport Image --}}
                        <div class="form-group">
                            <label><i class="fa fa-image"></i> {{ __('Passport Image') }}</label>
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center mb-2">
                                    <img id="passport-image-preview" src="" class="img-thumbnail" style="max-height:150px;display:none;">
                                    <div id="passport-image-placeholder" class="border rounded p-3 text-muted text-center">
                                        <i class="fa fa-image fa-2x"></i><div>{{ __('No Image') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="file" class="form-control-file" id="passport_image_input"
                                           name="passport_image" accept="image/*">
                                    <small class="text-muted">JPG, PNG. Max 2MB.</small>
                                </div>
                            </div>
                        </div>
                        {{-- Visa Image --}}
                        <div class="form-group">
                            <label><i class="fa fa-image"></i> {{ __('Visa Image') }}</label>
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center mb-2">
                                    <img id="visa-image-preview" src="" class="img-thumbnail" style="max-height:150px;display:none;">
                                    <div id="visa-image-placeholder" class="border rounded p-3 text-muted text-center">
                                        <i class="fa fa-image fa-2x"></i><div>{{ __('No Image') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input type="file" class="form-control-file" id="visa_image_input"
                                           name="visa_image" accept="image/*">
                                    <small class="text-muted">JPG, PNG. Max 2MB.</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-warning text-dark" id="btn-save-passenger">
                        <i class="fa fa-save"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Ticket Cancel Modal --}}
    <div class="modal fade" id="modal_ticket_cancel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-times-circle"></i> {{ __('Ticket Cancel') }} — <span id="cancel-booking-code"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cancel_booking_id">

                    {{-- Paid amount info --}}
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{{ __('Paid Amount') }}:</strong>
                        <span id="cancel-booking-paid" class="font-weight-bold text-danger"></span>
                        <div class="mt-1 small">{{ __('This amount will be eligible for refund after cancellation.') }}</div>
                    </div>

                    {{-- Reason --}}
                    <div class="form-group">
                        <label for="cancel_reason">
                            <i class="fa fa-comment"></i>
                            {{ __('Cancellation Reason') }}
                            <span class="text-muted small">({{ __('Optional') }})</span>
                        </label>
                        <textarea class="form-control"
                                  id="cancel_reason"
                                  rows="3"
                                  placeholder="{{ __('Enter reason for cancellation... (will be saved as remarks)') }}"></textarea>
                        <small class="text-muted">{{ __('This reason will be saved in transaction remarks.') }}</small>
                    </div>

                    <div class="alert alert-danger mb-0">
                        <i class="fa fa-warning"></i>
                        <strong>{{ __('Warning:') }}</strong> {{ __('This action will mark the ticket as cancelled. This cannot be undone.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ __('Close') }}
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-ticket-cancel">
                        <i class="fa fa-times-circle"></i> {{ __('Confirm Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Booking Cancel Confirm Modal --}}
    <div class="modal fade" id="modal_booking_cancel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fa fa-ban"></i> {{ __('Booking Cancel') }} — <span id="booking-cancel-code"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="booking_cancel_url">

                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>{{ __('Warning:') }}</strong>
                        {{ __('This will cancel the booking in the GDS system. This action cannot be undone.') }}
                    </div>

                    <p class="mb-0">{{ __('Are you sure you want to cancel booking') }}
                        <strong id="booking-cancel-code-2" class="text-warning"></strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ __('No, Go Back') }}
                    </button>
                    <a href="#" id="btn-confirm-booking-cancel" class="btn btn-warning text-dark">
                        <i class="fa fa-ban"></i> {{ __('Yes, Cancel Booking') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ PNR & Source & Status Edit Modal --}}
    <div class="modal fade" id="modal_pnr_edit" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-edit"></i> {{ __('Edit PNR / Source / Status') }} — <span id="pnr-edit-code"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pnr_edit_booking_id">

                    {{-- PNR --}}
                    <div class="form-group">
                        <label for="pnr_edit_input">
                            <i class="fa fa-barcode"></i> {{ __('PNR / GDS Reference') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="pnr_edit_input"
                               placeholder="e.g. ABC123"
                               maxlength="20"
                               style="text-transform:uppercase; letter-spacing: 2px; font-weight:bold; font-size:16px;">
                        <small class="text-muted">{{ __('Auto uppercase. Max 20 characters.') }}</small>
                    </div>

                    {{-- Source --}}
                    <div class="form-group">
                        <label for="pnr_edit_source">
                            <i class="fa fa-server"></i> {{ __('Source / GDS') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="pnr_edit_source">
                            <option value="sabre">Sabre</option>
                            <option value="travelport">Travelport</option>
                            <option value="galileo">Galileo</option>
                            <option value="amadeus">Amadeus</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label for="pnr_edit_status">
                            <i class="fa fa-flag"></i> {{ __('Booking Status') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="pnr_edit_status">
                            <option value="issue_request">{{ __('Issue Request') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="booked">{{ __('Booked') }}</option>
                            <option value="issued">{{ __('Issued') }}</option>
                            <option value="ticketed">{{ __('Ticketed') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="failed">{{ __('Failed') }}</option>
                            <option value="refunded">{{ __('Refunded') }}</option>
                        </select>
                        <small class="text-muted">{{ __('Default: current status. Change only if needed.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-info text-white" id="btn-save-pnr">
                        <i class="fa fa-save"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Assign Tickets Modals --}}
    @foreach($rows as $booking)
        <div class="modal fade" id="assign_ticket-{{ $booking->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-ticket"></i> Assign Tickets — {{ $booking->code ?? '#'.$booking->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form class="form-assign-tickets" data-booking-id="{{ $booking->id }}">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>PNR:</strong> <span class="text-primary">{{ $booking->pnr_id ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Route:</strong> {{ $booking->flight_from }} → {{ $booking->flight_to }}
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th width="50px">#</th>
                                        <th>Passenger Name</th>
                                        <th width="100px">Type</th>
                                        <th width="150px">Passport</th>
                                        <th width="250px">Ticket Number</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php $modalPassengers = $booking->passengers ?? collect(); @endphp
                                    @forelse($modalPassengers as $index => $passenger)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-success badge-pill">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $passenger->first_name }} {{ $passenger->last_name }}</strong>
                                                <input type="hidden" name="passengers[{{ $index }}][id]" value="{{ $passenger->id }}">
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $passenger->traveler_type ?? 'ADULT' }}</span>
                                            </td>
                                            <td><small class="text-muted">{{ $passenger->passport_number }}</small></td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="passengers[{{ $index }}][ticket_number]"
                                                       placeholder="Enter 13-digit ticket"
                                                       value="{{ $passenger->ticket_number }}" maxlength="13">
                                                @if($passenger->ticket_number)
                                                    <small class="text-success d-block mt-1">
                                                        <i class="fa fa-check-circle"></i> Current: {{ $passenger->ticket_number }}
                                                    </small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No passengers found</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check"></i> Assign Tickets
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection


@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .badge-issue-request {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            position: relative;
            padding-left: 18px;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.6);
            animation: issueGlow 1.5s ease-in-out infinite;
        }
        .badge-issue-request::before {
            content: '';
            position: absolute;
            left: 6px; top: 50%;
            transform: translateY(-50%);
            width: 7px; height: 7px;
            border-radius: 50%;
            background: white;
            animation: issueBlink 1s ease-in-out infinite;
        }
        @keyframes issueBlink { 0%,100%{opacity:1} 50%{opacity:0} }
        @keyframes issueGlow  { 0%,100%{box-shadow:0 0 6px rgba(245,158,11,.5)} 50%{box-shadow:0 0 14px rgba(245,158,11,.9)} }

        .serial-number { font-weight: 600; color: #6c757d; font-size: 13px; }

        #pnr_edit_input { text-transform: uppercase; }

        .passenger-img-thumb {
            width: 70px; height: 50px; object-fit: cover;
            border-radius: 4px; border: 1px solid #dee2e6;
            cursor: pointer; transition: transform 0.2s;
        }
        .passenger-img-thumb:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(0,0,0,0.18); }
        .passenger-img-placeholder {
            width: 70px; height: 50px; background: #f8f9fa;
            border: 1px dashed #ced4da; border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            color: #adb5bd; font-size: 10px; text-align: center;
        }
    </style>
@endpush


@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {

            var selectedStatus = "{{ $selectedStatus ?? '' }}";

            // ✅ Custom status sort order
            $.fn.dataTable.ext.type.order['status-priority-pre'] = function(data) {
                const priority = {
                    'issue_request':1,'booked':2,'paid':3,'pending':4,
                    'ticketed':5,'issued':6,'completed':7,'cancelled':8,'failed':9,'refunded':10
                };
                return priority[data.trim().toLowerCase()] ?? 99;
            };

            // ✅ DataTable Init
            // Col index: 0=check,1=SL,2=code,3=customer,4=source,5=type,6=route,
            //            7=airline,8=pnr,9=payment,10=status,11=booking_date,12=confirmed_at,13=actions
            var table = $('#bookingsDataTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'All']],
                order: selectedStatus ? [[2,'desc']] : [[10,'asc'],[2,'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 1, 13] },
                    { targets: 10, type: 'status-priority' }
                ],
                language: {
                    search: "{{ __('Search:') }}",
                    lengthMenu: "{{ __('Show _MENU_ entries') }}",
                    info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                    infoEmpty: "{{ __('No entries to show') }}",
                    zeroRecords: "{{ __('No matching records found') }}",
                    paginate: {
                        first:"{{ __('First') }}",last:"{{ __('Last') }}",
                        next:"{{ __('Next') }}",previous:"{{ __('Previous') }}"
                    }
                },
                // ✅ Serial number — display order অনুযায়ী সবসময় 1 থেকে শুরু
                drawCallback: function () {
                    var api     = this.api();
                    var pageInfo = api.page.info();
                    // current page এর visible rows গুলো DOM order এ পাবো
                    api.rows({ page: 'current', order: 'applied' }).nodes().each(function (row, i) {
                        $(row).find('.serial-number').text(pageInfo.start + i + 1);
                    });
                }
            });

            // ✅ Check All
            $('.check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            // ─────────────────────────────────────────────────────
            // ✅ Booking Detail Modal
            // ─────────────────────────────────────────────────────
            $(document).on('click', '.btn-detail-booking', function (e) {
                e.preventDefault();
                var bookingId = $(this).data('id');
                var ajaxUrl   = $(this).data('ajax');

                $('.booking-id-display').text(bookingId);
                $('#booking-detail-content').html(loadingSpinner());

                $.ajax({
                    url: ajaxUrl, method: 'GET',
                    success: function (html) { $('#booking-detail-content').html(html); },
                    error:   function ()     {
                        $('#booking-detail-content').html(
                            '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Failed to load details.</div>'
                        );
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // ✅ Set Paid
            // ─────────────────────────────────────────────────────
            $(document).on('click', '.btn-set-paid', function () {
                var btn    = $(this);
                var id     = btn.data('id');
                var amount = $('#set_paid_input_' + id).val();

                if (!amount || parseFloat(amount) <= 0) {
                    alert('{{ __("Please enter a valid amount") }}'); return;
                }
                if (!confirm('Add payment of ৳' + parseFloat(amount).toFixed(2) + ' to this booking?')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Processing...") }}');

                $.ajax({
                    url: '/admin/module/booking/bookings/' + id + '/set-paid',
                    method: 'POST',
                    data: { remain: amount, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === true || res.success === true) {
                            alert('✅ ' + res.message);
                            window.location.reload();
                        } else {
                            alert('❌ ' + (res.message || '{{ __("Failed to update payment") }}'));
                            btn.prop('disabled', false).html('<i class="fa fa-check"></i> {{ __("Save Payment") }}');
                        }
                    },
                    error: function (xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || '{{ __("An error occurred") }}'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> {{ __("Save Payment") }}');
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // ✅ Ticket Cancel — modal খোলা
            // ─────────────────────────────────────────────────────
            $(document).on('click', '.btn-ticket-cancel', function (e) {
                e.preventDefault();
                var id   = $(this).data('id');
                var code = $(this).data('code');
                var paid = $(this).data('paid');

                $('#cancel_booking_id').val(id);
                $('#cancel-booking-code').text(code);
                $('#cancel-booking-paid').text('৳' + parseFloat(paid || 0).toFixed(2));
                $('#cancel_reason').val('');

                $('#modal_ticket_cancel').modal('show');
            });

            // ─────────────────────────────────────────────────────
            // ✅ Booking Cancel — modal খোলা (GET route এ redirect করবে)
            // ─────────────────────────────────────────────────────
            $(document).on('click', '.btn-booking-cancel', function (e) {
                e.preventDefault();
                var code = $(this).data('code');
                var url  = $(this).data('url');

                $('#booking-cancel-code').text(code);
                $('#booking-cancel-code-2').text(code);
                $('#booking_cancel_url').val(url);

                $('#modal_booking_cancel').modal('show');
            });

            // ✅ Booking Cancel — confirm এ click করলে GET route এ redirect
            $('#btn-confirm-booking-cancel').on('click', function (e) {
                e.preventDefault();
                var url = $('#booking_cancel_url').val();
                if (url) {
                    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Cancelling...") }}');
                    window.location.href = url;
                }
            });

            // ✅ Ticket Cancel — confirm submit
            $('#btn-confirm-ticket-cancel').on('click', function () {
                var btn       = $(this);
                var id        = $('#cancel_booking_id').val();
                var reason    = $('#cancel_reason').val().trim();

                if (!id) { alert('Booking not found'); return; }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Processing...") }}');

                $.ajax({
                    url: '/admin/module/booking/bookings/' + id + '/ticket-cancel',
                    method: 'POST',
                    data: { reason: reason, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#modal_ticket_cancel').modal('hide');
                            window.location.reload();
                        } else {
                            alert('❌ ' + (res.message || 'Failed'));
                            btn.prop('disabled', false).html('<i class="fa fa-times-circle"></i> {{ __("Confirm Cancel") }}');
                        }
                    },
                    error: function (xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || '{{ __("An error occurred") }}'));
                        btn.prop('disabled', false).html('<i class="fa fa-times-circle"></i> {{ __("Confirm Cancel") }}');
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // ✅ PNR Edit — modal খোলা
            // ─────────────────────────────────────────────────────
            $(document).on('click', '.btn-pnr-edit', function (e) {
                e.preventDefault();
                var id     = $(this).data('id');
                var pnr    = $(this).data('pnr');
                var source = $(this).data('source');
                var status = $(this).data('status');
                var code   = $(this).data('code');

                $('#pnr_edit_booking_id').val(id);
                $('#pnr-edit-code').text(code);
                $('#pnr_edit_input').val(pnr || '');
                $('#pnr_edit_source').val(source || 'sabre');
                $('#pnr_edit_status').val(status || 'booked');

                $('#modal_pnr_edit').modal('show');
            });

            // ✅ PNR Edit — save
            $('#btn-save-pnr').on('click', function () {
                var btn    = $(this);
                var id     = $('#pnr_edit_booking_id').val();
                var pnr    = $('#pnr_edit_input').val().trim().toUpperCase();
                var source = $('#pnr_edit_source').val();
                var status = $('#pnr_edit_status').val();

                if (!pnr) { alert('{{ __("PNR is required") }}'); return; }
                if (!source) { alert('{{ __("Source is required") }}'); return; }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Saving...") }}');

                $.ajax({
                    url: '/admin/module/booking/bookings/' + id + '/update-pnr',
                    method: 'POST',
                    data: { pnr_id: pnr, source: source, status: status, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            $('#modal_pnr_edit').modal('hide');
                            window.location.reload();
                        } else {
                            alert('❌ ' + (res.message || 'Failed'));
                            btn.prop('disabled', false).html('<i class="fa fa-save"></i> {{ __("Save Changes") }}');
                        }
                    },
                    error: function (xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || '{{ __("An error occurred") }}'));
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i> {{ __("Save Changes") }}');
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // ✅ Passenger Edit Modal open (detail modal থেকে call হবে)
            // ─────────────────────────────────────────────────────
            window.openPassengerEdit = function (passengerId, name, passport, passportExpiry, passportImageUrl, visaImageUrl, dob) {
                $('#passenger_edit_id').val(passengerId);
                $('#passenger-edit-name').text(name);
                $('#passenger_passport').val(passport || '');
                $('#passenger_passport_expiry').val(passportExpiry || '');
                $('#passenger_dob').val(dob || '');
                $('#passport_image_input, #visa_image_input').val('');

                if (passportImageUrl) {
                    $('#passport-image-preview').attr('src', passportImageUrl).show();
                    $('#passport-image-placeholder').hide();
                } else {
                    $('#passport-image-preview').hide();
                    $('#passport-image-placeholder').show();
                }
                if (visaImageUrl) {
                    $('#visa-image-preview').attr('src', visaImageUrl).show();
                    $('#visa-image-placeholder').hide();
                } else {
                    $('#visa-image-preview').hide();
                    $('#visa-image-placeholder').show();
                }

                $('#modal_passenger_edit').modal('show');
            };

            // ✅ Passport / Visa image preview
            $(document).on('change', '#passport_image_input', function () {
                previewImg(this, '#passport-image-preview', '#passport-image-placeholder');
            });
            $(document).on('change', '#visa_image_input', function () {
                previewImg(this, '#visa-image-preview', '#visa-image-placeholder');
            });

            function previewImg(input, previewSel, placeholderSel) {
                var file = input.files[0];
                if (!file) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(previewSel).attr('src', e.target.result).show();
                    $(placeholderSel).hide();
                };
                reader.readAsDataURL(file);
            }

            // ✅ Save Passenger
            $('#btn-save-passenger').on('click', function () {
                var btn         = $(this);
                var passengerId = $('#passenger_edit_id').val();
                if (!passengerId) { alert('{{ __("Passenger not found") }}'); return; }

                var formData = new FormData($('#form-passenger-edit')[0]);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Saving...") }}');

                $.ajax({
                    url: '/admin/module/booking/bookings/passenger/' + passengerId + '/update-passport',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + (res.message || '{{ __("Passenger updated successfully") }}'));
                            $('#modal_passenger_edit').modal('hide');
                            // Detail modal reload
                            var currentId = $('.booking-id-display').text();
                            var ajaxUrl   = $('.btn-detail-booking[data-id="' + currentId + '"]').data('ajax');
                            if (ajaxUrl) { $('#booking-detail-content').load(ajaxUrl); }
                        } else {
                            alert('❌ ' + (res.message || '{{ __("Failed to update") }}'));
                        }
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i> {{ __("Save Changes") }}');
                    },
                    error: function (xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || '{{ __("An error occurred") }}'));
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i> {{ __("Save Changes") }}');
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // ✅ Assign Tickets
            // ─────────────────────────────────────────────────────
            $(document).on('submit', '.form-assign-tickets', function (e) {
                e.preventDefault();
                var form      = $(this);
                var bookingId = form.data('booking-id');
                var submitBtn = form.find('button[type="submit"]');
                var passengers = [];

                $.each(form.serializeArray(), function (i, field) {
                    if (field.name === '_token') return;
                    var match = field.name.match(/passengers\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        var idx = parseInt(match[1]);
                        if (!passengers[idx]) passengers[idx] = {};
                        passengers[idx][match[2]] = field.value;
                    }
                });

                var valid = passengers.filter(function (p) {
                    return p && p.ticket_number && p.ticket_number.trim() !== '';
                });

                if (!valid.length) { alert('⚠️ Please enter at least one ticket number'); return; }
                if (!confirm('✅ Assign ' + valid.length + ' ticket(s) to Booking #' + bookingId + '?')) return;

                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning...');

                $.ajax({
                    url: '{{ route('assign-tickets') }}',
                    method: 'POST',
                    data: { booking_id: bookingId, passengers: valid, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            alert('✅ ' + res.message);
                            form.closest('.modal').modal('hide');
                            setTimeout(function () { window.location.reload(); }, 500);
                        } else {
                            alert('❌ ' + (res.message || 'Failed'));
                            submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Assign Tickets');
                        }
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON?.message || xhr.responseJSON?.errors
                            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                            : 'An error occurred';
                        alert('❌ ' + msg);
                        submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Assign Tickets');
                    }
                });
            });

            // ─────────────────────────────────────────────────────
            // Helper
            // ─────────────────────────────────────────────────────
            function loadingSpinner() {
                return '<div class="d-flex justify-content-center py-5">' +
                    '<div class="spinner-border text-primary" role="status">' +
                    '<span class="sr-only">Loading...</span></div></div>';
            }

        });

        // ✅ Print booking detail
        function printBookingDetail() {
            var content = document.getElementById('booking-detail-content').innerHTML;
            var w = window.open('', '', 'height=600,width=800');
            w.document.write('<html><head><title>Booking Details</title>');
            w.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
            w.document.write('</head><body>' + content + '</body></html>');
            w.document.close();
            w.print();
        }
    </script>
@endpush
