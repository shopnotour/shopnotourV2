
@extends('layouts.user')
@section('content')
    <h2 class="title-bar no-border-bottom">
        {{__("Booking History")}}
    </h2>
    @include('admin.message')

    <div class="booking-history-manager">
        <div class="tabbable">
            {{-- Navigation Tabs --}}
            <ul class="nav nav-tabs ht-nav-tabs">
                <?php $status_type = Request::query('status'); ?>
                <li class="@if(empty($status_type)) active @endif">
{{--                    <a href="{{route("user.booking_history")}}">{{__("All Booking")}}</a>--}}
                </li>
{{--                @if(!empty($statues))--}}
{{--                    @foreach($statues as $status)--}}
{{--                        <li class="@if(!empty($status_type) && $status_type == $status) active @endif">--}}
{{--                            <a href="{{route("user.booking_history",['status'=>$status])}}">{{booking_status_to_text($status)}}</a>--}}
{{--                        </li>--}}
{{--                    @endforeach--}}
{{--                @endif--}}
            </ul>

            {{-- Booking List --}}
            @if(!empty($bookings) && $bookings->total() > 0)
                <div class="tab-content">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-booking-history">
                            <thead>
                            <tr>
                                <th width="2%">{{__("Type")}}</th>
                                <th>{{__("Title")}}</th>
                                <th>{{__("Order Date")}}</th>
                                <th>{{__("Execution Time")}}</th>
                                <th>{{__("Total")}}</th>
                                <th>{{__("Paid")}}</th>
                                <th>{{__("Remain")}}</th>
                                <th>{{__("Status")}}</th>
                                <th width="15%">{{__("Action")}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bookings as $booking)
                                <tr>
                                    <td class="booking-history-type text-center">
                                        @if($service = $booking->service)
                                            <i class="{{$service->getServiceIconFeatured()}}"></i>
                                            <br><small>{{$booking->object_model}}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($service = $booking->service)
                                            <strong>{!! clean($service->title) !!}</strong>
                                            @if(!empty($service->code))
                                                <br><small class="text-muted">{{__("Code")}}: {!! clean($service->code) !!}</small>
                                            @endif
                                            @if(!empty($booking->pnr))
                                                <br><small class="text-primary">{{__("PNR")}}: {{$booking->pnr}}</small>
                                            @endif
                                        @else
                                            <span class="text-danger">{{__("[Deleted]")}}</span>
                                        @endif
                                    </td>
                                    <td>{{display_date($booking->created_at)}}</td>
                                    <td class="lh-16">
                                        <small>
                                            <strong>{{__("Departure")}}:</strong> {{display_datetime($booking->start_date)}}<br>
                                            <strong>{{__("Arrival")}}:</strong> {{display_datetime($booking->end_date)}}<br>
                                            @if(!empty($booking->service->duration))
                                                <strong>{{__("Duration")}}:</strong> {{__(':duration hrs',['duration'=>$booking->service->duration])}}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="fw-500">{{format_money($booking->total)}}</td>
                                    <td class="text-success">{{format_money($booking->paid)}}</td>
                                    <td class="text-danger">{{format_money($booking->total - $booking->paid)}}</td>
                                    <td class="text-center">
                                            <span class="badge badge-{{$booking->status}} status-badge">
                                                {{$booking->statusName}}
                                            </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            {{-- View Details Button --}}
                                            @if($service = $booking->service)
                                                <a class="btn btn-sm btn-info btn-info-booking mb-1"
                                                   data-ajax="{{route('booking.modal',['booking'=>$booking])}}"
                                                   data-toggle="modal"
                                                   data-id="{{$booking->id}}"
                                                   data-target="#modal_booking_detail">
                                                    <i class="fa fa-eye"></i> {{__("Details")}}
                                                </a>
                                            @endif

                                            {{-- Invoice Button --}}
                                            <a href="{{route('user.booking.invoice',['code'=>$booking->code])}}"
                                               class="btn btn-sm btn-secondary mb-1"
                                               onclick="window.open(this.href); return false;">
                                                <i class="fa fa-print"></i> {{__("Invoice")}}
                                            </a>

                                            {{-- Pay Now Button (for unpaid bookings) --}}
                                            @if($booking->status == 'unpaid')
                                                <a href="{{route('booking.checkout',['code'=>$booking->code])}}"
                                                   class="btn btn-sm btn-success mb-1">
                                                    <i class="fa fa-credit-card"></i> {{__("Pay Now")}}
                                                </a>
                                            @endif

                                            {{-- PNR-based Actions --}}
                                            @if(!empty($booking->pnr))

                                                {{-- Reissue - for confirmed bookings with PNR --}}
                                                @if(in_array($booking->status, ['confirmed', 'paid', 'completed']))
                                                    <a href="{{route('booking.reissue',['code'=>$booking->code])}}"
                                                       class="btn btn-sm btn-warning mb-1">
                                                        <i class="fa fa-refresh"></i> {{__("Reissue")}}
                                                    </a>
                                                @endif

                                                {{-- Void - for recent bookings (within 24 hours) --}}
                                                @if(in_array($booking->status, ['confirmed', 'paid']) &&
                                                    $booking->created_at->diffInHours(now()) < 24)
                                                    <a href="{{route('booking.void',['code'=>$booking->code])}}"
                                                       class="btn btn-sm btn-danger mb-1"
                                                       onclick="return confirm('{{__("Are you sure you want to void this booking? This action cannot be undone.")}}');">
                                                        <i class="fa fa-ban"></i> {{__("Void")}}
                                                    </a>
                                                @endif

                                                {{-- Refund - for paid bookings --}}
                                                @if(in_array($booking->status, ['paid', 'completed', 'confirmed']) &&
                                                    $booking->paid > 0)
                                                    <a href="{{route('booking.refund',['code'=>$booking->code])}}"
                                                       class="btn btn-sm btn-info mb-1">
                                                        <i class="fa fa-undo"></i> {{__("Refund")}}
                                                    </a>
                                                @endif

                                                {{-- Add SSR (Special Service Request) --}}
                                                @if(in_array($booking->status, ['confirmed', 'paid']) &&
                                                    $booking->start_date > now())
                                                    <a href="{{route('booking.add_ssr',['code'=>$booking->code])}}"
                                                       class="btn btn-sm btn-primary mb-1">
                                                        <i class="fa fa-plus-circle"></i> {{__("Add SSR")}}
                                                    </a>
                                                @endif

                                            @endif

                                            {{-- Cancel - for bookings that can be cancelled --}}
                                            @if(in_array($booking->status, ['pending', 'confirmed', 'processing', 'unpaid']) &&
                                                $booking->start_date > now())
                                                <a href="{{route('booking.cancel',['code'=>$booking->code])}}"
                                                   class="btn btn-sm btn-danger mb-1"
                                                   onclick="return confirm('{{__("Are you sure you want to cancel this booking?")}}');">
                                                    <i class="fa fa-times"></i> {{__("Cancel")}}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="bravo-pagination">
                        {{$bookings->appends(request()->query())->links()}}
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-3">
                    <i class="fa fa-info-circle"></i> {{__("No Booking History")}}
                </div>
            @endif
        </div>

        {{-- Booking Detail Modal --}}
        <div class="modal fade" tabindex="-1" id="modal_booking_detail">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{__('Booking ID: #')}} <span class="booking_id"></span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fa fa-spinner fa-spin fa-3x"></i>
                            <p class="mt-2">{{__("Loading...")}}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            {{__('Close')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles --}}
    <style>
        .booking-history-manager {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .ht-nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .ht-nav-tabs li {
            margin-right: 5px;
        }

        .ht-nav-tabs li a {
            padding: 10px 20px;
            border-radius: 4px 4px 0 0;
            transition: all 0.3s ease;
        }

        .ht-nav-tabs li.active a {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .table-booking-history {
            font-size: 13px;
        }

        .table-booking-history th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .booking-history-type i {
            font-size: 24px;
            color: #007bff;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .action-buttons .btn {
            flex: 1 1 auto;
            min-width: 80px;
            font-size: 11px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-confirmed { background: #28a745; color: #fff; }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-cancelled { background: #dc3545; color: #fff; }
        .badge-completed { background: #17a2b8; color: #fff; }
        .badge-unpaid { background: #fd7e14; color: #fff; }
        .badge-paid { background: #20c997; color: #fff; }
        .badge-processing { background: #6f42c1; color: #fff; }

        .lh-16 { line-height: 1.6; }
        .fw-500 { font-weight: 500; }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Handle booking detail modal
            $('.btn-info-booking').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                var modal = $("#modal_booking_detail");

                // Set booking ID
                modal.find('.booking_id').html(btn.data('id'));

                // Reset modal body with loading indicator
                modal.find('.modal-body').html(`
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-2">{{__("Loading...")}}</p>
                    </div>
                `);

                // Fetch booking details
                $.get(btn.data('ajax'), function(html) {
                    modal.find('.modal-body').html(html);
                }).fail(function() {
                    modal.find('.modal-body').html(`
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-triangle"></i>
                            {{__("Failed to load booking details. Please try again.")}}
                    </div>
`);
                });
            });

            // Confirmation for void action
            $('a[href*="booking.void"]').on('click', function(e) {
                if (!confirm('{{__("Are you sure you want to void this booking? This action cannot be undone.")}}')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Confirmation for cancel action
            $('a[href*="booking.cancel"]').on('click', function(e) {
                if (!confirm('{{__("Are you sure you want to cancel this booking?")}}')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush
