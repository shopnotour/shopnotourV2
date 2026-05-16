@extends ('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('All Bookings') }}</h1>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                @if (!empty($booking_update))
                    <form method="post" action="{{ route('report.admin.booking.bulkEdit') }}"
                        class="filter-form filter-form-left d-flex justify-content-start">
                        @csrf
                        <select name="action" class="form-control">
                            <option value="">{{ __('-- Bulk Actions --') }}</option>
                            @if (!empty($statues))
                                @foreach ($statues as $status)
                                    <option value="{{ $status }}">
                                        {{ __('Mark as: :name', ['name' => booking_status_to_text($status)]) }}
                                    </option>
                                @endforeach
                            @endif
                            <option value="delete">{{ __('DELETE booking') }}</option>
                        </select>
                        <button data-confirm="{{ __(' Do you want to delete?') }}"
                            class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{ __('Apply') }}</button>
                    </form>
                @endif
            </div>
            <div class="col-left">
                <form method="get" action="" class="filter-form filter-form-right d-flex justify-content-end">
                    @csrf
                    @if (!empty($booking_manage_others))
                        <?php
                        $user = !empty(Request()->vendor_id) ? App\User::find(Request()->vendor_id) : false;
                        \App\Helpers\AdminForm::select2(
                            'vendor_id',
                            [
                                'configs' => [
                                    'ajax' => [
                                        'url' => route('user.admin.getForSelect2'),
                                        'dataType' => 'json',
                                    ],
                                    'allowClear' => true,
                                    'placeholder' => __('-- Vendor --'),
                                ],
                            ],
                            !empty($user->id) ? [$user->id, $user->name_or_email . ' (#' . $user->id . ')'] : false,
                        );
                        ?>
                    @endif
                    <input type="text" name="s" value="{{ Request()->s }}"
                        placeholder="{{ __('Search by name or ID') }}" class="form-control">
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Filter') }}</button>
                </form>
            </div>
        </div>
        <div class="text-right">
            <p><i>{{ __('Found :total items', ['total' => $rows->total()]) }}</i></p>
        </div>
        <div class="panel booking-history-manager">
            <div class="panel-title">{{ __('Bookings') }}</div>
            <div class="panel-body">
                <form action="" class="bravo-form-item">
                    <table class="table table-hover bravo-list-item">
                        <thead>
                            <tr>
                                <th width="80px"><input type="checkbox" class="check-all"></th>
                                <th>{{ __('Booking#') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('System') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Route') }}</th>
                                <th>{{ __('Airline') }}</th>
                                <th>{{ __('PNR & Ticket') }}</th>


                                <th>{{ __('Payment Information') }}</th>
                                <th width="80px">{{ __('Commission') }}</th>
                                <th width="80px">{{ __('Status') }}</th>
                                <th width="150px">{{ __('Payment Method') }}</th>
                                <th width="120px">{{ __('Booking Date') }}</th>
                                <th width="80px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php $booking = $row; @endphp
                                <tr>
                                    <td><input type="checkbox" class="check-item" name="ids[]"
                                            value="{{ $row->id }}">
                                    </td>
                                    <td>
                                        {{ $row->id }}
                                    </td>
                                    <td>
                                        {{ $row->first_name }} {{ $row->last_name }}
                                        <br />{{ $row->email }}, {{ $row->phone }}
                                        <br />Note: {{ $row->customer_notes }}
                                    </td>
                                    <td>{{ $row->source }}</td>
                                    <td>{{ $row->flight_type }}</td>
                                    <td>{{ $row->flight_from }}-{{ $row->flight_to }}</td>
{{--                                    <td>{{ airline_from_code($row->airline) }}</td>--}}
                                    <td>{{ $row->airline }}</td>
                                    <td>
                                        <div><strong>PNR:</strong> {{ $row->pnr_id ?? 'N/A' }}</div>

                                    @php
                                            $tickets = $row->ticket_number ? json_decode($row->ticket_number, true) : [];
                                        @endphp

                                        <div><strong>TKT:</strong>
                                            {{ !empty($tickets) ? implode(', ', $tickets) : 'N/A' }}
                                        </div>
                                    </td>
                                    <td>{{ __('Total') }} : {{ format_money_main($row->total) }}<br />
                                        {{ __('Paid') }} : {{ format_money_main($row->paid) }}<br />
                                        {{ __('Remain') }} :
                                        {{ format_money_main($booking->total - $booking->paid) }}<br />
                                    </td>
                                    <td>
                                        {{ format_money_main($booking->commission) }}
                                    </td>
                                    <td>
                                        <span class="label label-{{ $row->status }}">{{ $row->statusName }}</span>
                                    </td>
                                    <td>
                                        {{ $row->gateway }}<br />{{ $row->gatewayObj ? $row->gatewayObj->getDisplayName() : '' }}
                                    </td>
                                    <td>{{ display_datetime($row->updated_at) }}</td>
                                    <td>
{{--                                        @if ($service = $row->service)--}}
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                    data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">{{ __('Actions') }}
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right"
                                                    aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item btn-detail-booking" href="#modal_booking_detail"
                                                        data-ajax="{{ route('booking.modal', ['booking' => $booking]) }}"
                                                        data-toggle="modal" data-id="{{ $booking->id }}"
                                                        data-target="#modal_booking_detail">{{ __('Detail') }}</a>
                                                    <a class="dropdown-item" href="#" data-toggle="modal"
                                                        data-target="#modal-paid-{{ $booking->id }}">{{ __('Set Paid') }}</a>
{{--                                                    @if ($booking->status == 'paid' || $booking->status == 'booked failed' || $booking->status == 'failed')--}}
                                                        <a class="dropdown-item"
                                                            href="{{ route('report.admin.booking.invoice.booking', ['id' => $row->id]) }}">{{ __('Booking') }}</a>
{{--                                                    @elseif ($booking->status == 'booked')--}}
                                                        <a class="dropdown-item"
                                                            href="{{ route('report.admin.booking.issueTicket', ['id' => $row->id]) }}">{{ __('Issue Ticket') }}</a>

{{--                                                    @elseif ($booking->status == 'ticketed')--}}
                                                        <a class="dropdown-item"
                                                            href="{{ route('report.admin.booking.cancelTicket', ['id' => $row->id]) }}">{{ __('Cancel Ticket') }}</a>
{{--                                                    @endif--}}
{{--                                                    @if(isset($booking->pnr_id))--}}
                                                        <a class="dropdown-item"
                                                           href="{{ route('report.admin.booking.booking_details', ['id' => $booking->id]) }}">
                                                            <i class="fa fa-file-text"></i> {{ __('View Booking') }}
                                                        </a>
{{--                                                    @endif--}}
                                                    @if(isset($booking->pnr_id))
                                                        <a class="dropdown-item"
                                                           href="{{ route('booking.pnr.view', ['pnr' => $booking->pnr_id]) }}">
                                                            <i class="fa fa-file-text"></i> {{ __('View pnr') }}
                                                        </a>
                                                    @endif

{{--                                                    <a class="dropdown-item"--}}
{{--                                                        href="{{ route('report.admin.booking.invoice', ['id' => $row->id]) }}">{{ __('Invoce') }}</a>--}}
{{--                                                    <a class="dropdown-item"--}}
{{--                                                        href="{{ route('report.admin.booking.ticket', ['id' => $row->id]) }}">{{ __('Ticket') }}</a>--}}
{{--                                                    <a class="dropdown-item"--}}
{{--                                                        href="{{ route('report.admin.booking.email_preview', ['id' => $row->id]) }}">{{ __('Email--}}
{{--                                                                                                    Preview') }}</a>--}}
                                                </div>
                                            </div>
{{--                                            @include ($service->set_paid_modal_file ?? '')--}}
{{--                                        @endif--}}
                                    </td>
                                </tr>
                                {{-- ✅ ADD MODAL HERE (inside loop, after each row) --}}
                                <div class="modal fade" id="modal-paid-{{ $booking->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fa fa-money"></i> {{ __('Set Paid Amount - Booking #:id', ['id' => $booking->id]) }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>{{ __('Total Amount') }}:</strong></p>
                                                        <h4 class="text-primary">{{ format_money_main($booking->total) }}</h4>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>{{ __('Already Paid') }}:</strong></p>
                                                        <h4 class="text-success">{{ format_money_main($booking->paid) }}</h4>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-12">
                                                        <p><strong>{{ __('Remaining') }}:</strong></p>
                                                        <h4 class="text-danger">{{ format_money_main($booking->total - $booking->paid) }}</h4>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="form-group">
                                                    <label for="set_paid_input">{{ __('Enter Paid Amount') }}</label>
                                                    <input type="number"
                                                           class="form-control form-control-lg"
                                                           id="set_paid_input"
                                                           placeholder="{{ __('Enter amount') }}"
                                                           min="0"
                                                           step="0.01"
                                                           value="{{ $booking->total - $booking->paid }}"
                                                           required>
                                                    <small class="form-text text-muted">
                                                        {{ __('Enter the amount that has been paid') }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    <i class="fa fa-times"></i> {{ __('Cancel') }}
                                                </button>
                                                <button type="button"
                                                        class="btn btn-primary"
                                                        id="set_paid_btn"
                                                        data-id="{{ $booking->id }}">
                                                    <i class="fa fa-check"></i> {{ __('Save Payment') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </form>

                <div class="modal" tabindex="-1" id="modal_booking_detail">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Booking ID: #') }} <span class="user_id"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-center">{{ __('Loading...') }}</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{ __('Close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            {{ $rows->links() }}
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).on('click', '#set_paid_btn', function(e) {
            e.preventDefault();

            var btn = $(this);
            var id = btn.data('id');
            var modal = $('#modal-paid-' + id);
            var amount = modal.find('#set_paid_input').val();

            // Validation
            if (!amount ) {
                alert('{{ __("Please enter a valid amount") }}');
                return;
            }

            // Disable button
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Processing...") }}');

            $.ajax({
                url: bookingCore.url + '/booking/setPaidAmount',
                data: {
                    id: id,
                    remain: amount,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                type: 'post',
                success: function(res) {
                    console.log(res);
                    if (res.status || res.message) {
                        alert(res.message || '{{ __("Payment updated successfully") }}');
                        window.location.reload();
                    } else {
                        alert('{{ __("Failed to update payment") }}');
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> {{ __("Save Payment") }}');
                    }
                },
                error: function(xhr) {
                    alert('{{ __("An error occurred. Please try again.") }}');
                    console.error(xhr);
                    btn.prop('disabled', false).html('<i class="fa fa-check"></i> {{ __("Save Payment") }}');
                }
            });
        });
        {{--$(document).on('click', '#set_paid_btn', function(e) {--}}
        {{--    console.log($('#modal-paid-{{ $booking->id }}').length);--}}
        {{--    var id = $(this).data('id');--}}
        {{--    $.ajax({--}}
        {{--        url: bookingCore.url + '/booking/setPaidAmount',--}}
        {{--        data: {--}}
        {{--            id: id,--}}
        {{--            remain: $('#modal-paid-' + id + ' #set_paid_input').val(),--}}
        {{--        },--}}
        {{--        dataType: 'json',--}}
        {{--        type: 'post',--}}
        {{--        success: function(res) {--}}
        {{--            alert(res.message);--}}
        {{--            window.location.reload();--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}
        $('.btn-detail-booking').on('click', function(e) {
            var btn = $(this);
            $(this).find('.user_id').html(btn.data('id'));
            $(this).find('.modal-body').html(
                '<div class="d-flex justify-content-center">{{ __('Loading...') }}</div>');
            var modal = $("#modal_booking_detail");
            $.get(btn.data('ajax'), function(html) {
                modal.find('.modal-body').html(html);
            })
        })
    </script>
@endpush
