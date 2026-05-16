{{--@extends('layouts.user')--}}
{{--@section('content')--}}
{{--    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">--}}
{{--        <div class="col-auto">--}}
{{--            <h1 class="text-30 lh-14 fw-600">{{ __("Booking History") }}</h1>--}}
{{--            <div class="text-15 text-light-1">{{auth()->user()->name}}{{ __(" All Bookings") }}</div>--}}
{{--        </div>--}}
{{--        <div class="col-auto"></div>--}}
{{--    </div>--}}
{{--    @include('admin.message')--}}
{{--    <div class="py-30 px-30 rounded-4 bg-white shadow-3 booking-history-manager">--}}
{{--        <div class="tabs -underline-2 js-tabs">--}}
{{--            <div class="tabs__controls row x-gap-40 y-gap-10 lg:x-gap-20 js-tabs-controls">--}}
{{--                <?php $status_type = Request::query('status'); ?>--}}
{{--                <div class="col-auto">--}}
{{--                    <a href="{{route("user.booking_history")}}" class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 @if(empty($status_type)) is-tab-el-active @endif">--}}
{{--                        {{__("All Booking")}}--}}
{{--                    </a>--}}
{{--                </div>--}}
{{--                @if(!empty($statues))--}}
{{--                    @foreach($statues as $status)--}}
{{--                        <div class="col-auto">--}}
{{--                            <a href="{{route("user.booking_history",['status'=>$status])}}" class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 @if(!empty($status_type) && $status_type == $status) is-tab-el-active @endif" >--}}
{{--                                {{booking_status_to_text($status)}}--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                @endif--}}
{{--            </div>--}}
{{--            <div class="tabs__content pt-30 js-tabs-content">--}}
{{--                <div class="tabs__pane -tab-item-1 is-tab-el-active">--}}
{{--                    --}}{{-- Search Form --}}
{{--                    <form method="GET" action="{{ route('user.booking_history') }}" class="pb-20">--}}
{{--                        @if(!empty($status_type))--}}
{{--                            <input type="hidden" name="status" value="{{ $status_type }}">--}}
{{--                        @endif--}}
{{--                        <div class="row y-gap-10 items-end">--}}

{{--                            --}}{{-- PNR --}}
{{--                            <div class="col-md-3 col-12">--}}
{{--                                <label class="text-13 fw-500 text-dark-1 mb-5">{{ __("PNR") }}</label>--}}
{{--                                <input type="text"--}}
{{--                                       name="pnr"--}}
{{--                                       class="form-control border-light text-dark-1"--}}
{{--                                       placeholder="e.g. TYOCJS"--}}
{{--                                       value="{{ request('pnr') }}">--}}
{{--                            </div>--}}

{{--                            --}}{{-- From Date --}}
{{--                            <div class="col-md-3 col-12">--}}
{{--                                <label class="text-13 fw-500 text-dark-1 mb-5">{{ __("From Date") }}</label>--}}
{{--                                <input type="date"--}}
{{--                                       name="from_date"--}}
{{--                                       class="form-control border-light text-dark-1"--}}
{{--                                       value="{{ request('from_date') }}">--}}
{{--                            </div>--}}

{{--                            --}}{{-- To Date --}}
{{--                            <div class="col-md-3 col-12">--}}
{{--                                <label class="text-13 fw-500 text-dark-1 mb-5">{{ __("To Date") }}</label>--}}
{{--                                <input type="date"--}}
{{--                                       name="to_date"--}}
{{--                                       class="form-control border-light text-dark-1"--}}
{{--                                       value="{{ request('to_date') }}">--}}
{{--                            </div>--}}

{{--                            --}}{{-- Buttons --}}
{{--                            <div class="col-md-3 col-12">--}}
{{--                                <div class="d-flex gap-10">--}}
{{--                                    <button type="submit" class="button -md -dark-1 bg-accent-1 text-white">--}}
{{--                                        <i class="icon-search mr-5"></i> {{ __("Search") }}--}}
{{--                                    </button>--}}
{{--                                    @if(request('pnr') || request('from_date') || request('to_date'))--}}
{{--                                        <a href="{{ route('user.booking_history', array_filter(['status' => $status_type])) }}"--}}
{{--                                           class="button -md -outline-accent-1 text-accent-1">--}}
{{--                                            {{ __("Clear") }}--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                    <div class="overflow-scroll scroll-bar-1">--}}
{{--                        @if(!empty($bookings) and $bookings->total() > 0)--}}
{{--                            <table class="table-3 -border-bottom col-12">--}}
{{--                                <thead class="bg-light-2">--}}
{{--                                    <tr>--}}
{{--                                        <th >{{__("Code")}}</th>--}}
{{--                                        <th>{{__("Order Date")}}</th>--}}
{{--                                        <th class="a-hidden">{{__("Order Date")}}</th>--}}
{{--                                        <th class="a-hidden">{{__("Execution Time")}}</th>--}}
{{--                                        <th>{{__("PNR")}}</th>--}}
{{--                                        <th>{{__("Total")}}</th>--}}
{{--                                        <th>{{__("Paid")}}</th>--}}
{{--                                        <th>{{__("Remain")}}</th>--}}
{{--                                        <th class="a-hidden">{{__("Status")}}</th>--}}
{{--                                        <th>{{__("Action")}}</th>--}}
{{--                                    </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody class="tbody">--}}
{{--                                    @foreach($bookings as $key => $booking)--}}
{{--                                        @include(ucfirst($booking->object_model).'::frontend.bookingHistory.loop', ['key' => $key])--}}
{{--                                    @endforeach--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                            <div class="bravo-pagination pt-30">--}}
{{--                                {{$bookings->appends(request()->query())->links()}}--}}
{{--                            </div>--}}
{{--                        @else--}}
{{--                            {{__("No Booking History")}}--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}
{{--@push('js')--}}
{{--    <script>--}}
{{--        $('.btn-info-booking').on('click',function (e){--}}
{{--            var btn = $(this);--}}
{{--            $(this).find('.user_id').html(btn.data('id'));--}}
{{--            $(this).find('.modal-body').html('<div class="d-flex justify-content-center">{{__("Loading...")}}</div>');--}}
{{--            var modal = $("#modal_booking_detail");--}}
{{--            $.get(btn.data('ajax'), function (html){--}}
{{--                    modal.find('.modal-body').html(html);--}}
{{--                }--}}
{{--            )--}}
{{--        })--}}
{{--    </script>--}}
{{--@endpush--}}


@extends('layouts.user')
@section('content')

    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">
        <div class="col-auto">
            <h1 class="text-30 lh-14 fw-600">{{ __("Booking History") }}</h1>
            <div class="text-15 text-light-1">{{ auth()->user()->name }}{{ __(" All Bookings") }}</div>
        </div>
        <div class="col-auto"></div>
    </div>

    @include('admin.message')

    <div class="py-30 px-30 rounded-4 bg-white shadow-3">

        {{-- Status Filter Tabs --}}
        <div class="tabs -underline-2 js-tabs mb-30">
            <div class="tabs__controls row x-gap-40 y-gap-10 lg:x-gap-20 js-tabs-controls">
                <div class="col-auto">
                    <button class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 status-filter is-tab-el-active" data-status="">
                        {{ __("All Booking") }}
                    </button>
                </div>
                @foreach($statues as $status)
                    <div class="col-auto">
                        <button class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 status-filter" data-status="{{ $status }}">
                            {{ booking_status_to_text($status) }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- DataTable --}}
        <div class="overflow-scroll scroll-bar-1">
            <table id="bookingTable" class="table-3 -border-bottom col-12">
                <thead class="bg-light-2">
                <tr>
                    <th>{{ __("Code") }}</th>
                    <th>{{ __("Order Date") }}</th>
                    <th>{{ __("Execution Time") }}</th>
                    <th>{{ __("PNR") }}</th>
                    <th>{{ __("Total") }}</th>
                    <th>{{ __("Paid") }}</th>
                    <th>{{ __("Remain") }}</th>
                    <th>{{ __("Status") }}</th>
                    <th>{{ __("Action") }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($bookings as $booking)
                    @include(ucfirst($booking->object_model).'::frontend.bookingHistory.loop')
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        #bookingTable_filter { display: none; } /* DataTable default search hide - আমরা custom করব */
        #bookingTable_length { margin-bottom: 15px; }
        #bookingTable_paginate { margin-top: 20px; }

        /* Custom search box style */
        .dt-custom-search input {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 14px;
            outline: none;
            width: 250px;
        }
        .dt-custom-search input:focus {
            border-color: #6366f1;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {

            // DataTable init
            var table = $('#bookingTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[1, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "{{ __('Search PNR, Code...') }}",
                    lengthMenu: "Show _MENU_ entries",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    }
                },
                // Custom search box আমাদের নিজের input এ connect করব
                initComplete: function() {
                    // DataTable এর search টা custom input এ move করব
                    var searchInput = $('<div class="dt-custom-search mb-20"><input type="text" placeholder="{{ __("Search PNR, Code, Status...") }}" id="dtSearch"></div>');
                    $('#bookingTable_wrapper').prepend(searchInput);

                    $('#dtSearch').on('keyup', function() {
                        table.search(this.value).draw();
                    });
                }
            });

            // Status Tab Filter
            $('.status-filter').on('click', function() {
                // Active class toggle
                $('.status-filter').removeClass('is-tab-el-active');
                $(this).addClass('is-tab-el-active');

                var status = $(this).data('status');

                // DataTable column filter (Status column index: 7)
                if (status === '') {
                    table.column(7).search('').draw();
                } else {
                    table.column(7).search(status, false, false).draw();
                }
            });

        });
    </script>
@endpush
