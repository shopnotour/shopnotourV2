@extends('layouts.user')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Hide default DataTable controls we're replacing */
        #bookingTable_filter,
        #bookingTable_info { display: none !important; }

        #bookingTable_wrapper .dataTables_length select {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 13px;
            outline: none;
        }

        #bookingTable_paginate {
            margin-top: 16px;
        }

        #bookingTable_paginate .paginate_button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            color: #374151;
            border: 1px solid #e5e7eb;
            margin: 0 2px;
            transition: all 0.15s;
        }

        #bookingTable_paginate .paginate_button:hover:not(.disabled) {
            background: #3b82f6;
            color: #fff !important;
            border-color: #3b82f6;
        }

        #bookingTable_paginate .paginate_button.current {
            background: #3b82f6;
            color: #fff !important;
            border-color: #3b82f6;
        }

        #bookingTable_paginate .paginate_button.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Row default cursor - not clickable */
        #bookingTable tbody tr {
            cursor: default;
        }

        /* Only code cell and view button are clickable */
        .code-link, .view-btn {
            cursor: pointer;
        }

        .code-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Status badge colors */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .status-paid    { background: #dcfce7; color: #16a34a; }
        .status-unpaid  { background: #fef3c7; color: #d97706; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .status-confirmed { background: #dbeafe; color: #2563eb; }
        .status-processing { background: #ede9fe; color: #7c3aed; }

        /* Responsive table wrapper */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile card-like rows */
        @media (max-width: 768px) {
            .table-responsive-wrapper {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            #bookingTable {
                width: auto !important;
                min-width: 320px;
                max-width: 420px;
            }
            #bookingTable thead { display: none; }
            #bookingTable tbody tr {
                display: block;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                margin-bottom: 6px;
                padding: 6px 8px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            }
            #bookingTable tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 2px 0;
                border: none;
                font-size: 11px;
                border-bottom: 1px solid #f3f4f6;
                line-height: 1.3;
            }
            #bookingTable tbody td:last-child {
                border-bottom: none;
            }
            #bookingTable tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6b7280;
                font-size: 10px;
                min-width: 70px;
                flex-shrink: 0;
            }
            #bookingTable tbody td .code-link {
                font-size: 11px;
            }
            #bookingTable tbody td .view-btn {
                font-size: 9px;
                padding: 1px 5px;
                gap: 1px;
                border-radius: 4px;
            }
            #bookingTable tbody td .view-btn svg {
                width: 10px;
                height: 10px;
            }

            #bookingTable_wrapper #bookingTable_paginate {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 3px;
            }
            #bookingTable_wrapper .paginate_button {
                min-width: 26px;
                height: 26px;
                font-size: 11px;
                padding: 0 4px;
                margin: 0;
            }
            #bookingTable_wrapper .dataTables_length select {
                font-size: 11px;
                padding: 2px 6px;
            }
            #tableInfo {
                font-size: 10px !important;
            }

            #mobilePaginate {
                display: flex;
                flex-wrap: nowrap;
                align-items: center;
                justify-content: center;
                gap: 2px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                white-space: nowrap;
                padding: 4px 0;
                max-width: 100%;
            }
            #mobilePaginate .mob-page-btn {
                min-width: 24px !important;
                height: 24px !important;
                padding: 0 3px !important;
                font-size: 10px !important;
                text-align: center !important;
                line-height: 1 !important;
                flex-shrink: 0;
            }
            #mobilePaginate span {
                flex-shrink: 0;
                font-size: 10px;
            }
        }
    

        /* Flatpickr custom */
        .flatpickr-input {
            background: #fff !important;
        }

        /* Status tab pills */
        .status-tab {
            transition: all 0.15s;
            cursor: pointer;
        }
        .status-tab.active-tab {
            opacity: 1 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        .status-tab:not(.active-tab) {
            opacity: 0.55;
        }
        .status-tab:not(.active-tab):hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="flex justify-between items-end pb-10 mb-6 border-b border-gray-100">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">{{ __("Booking History") }}</h1>
            <p class="text-sm text-gray-400 mt-1">{{ auth()->user()->name }} — {{ __("All Bookings") }}</p>
        </div>
    </div>

    @include('admin.message')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        {{-- Status Filter — desktop tabs, mobile dropdown --}}
        @php
            $tabColors = [
                'default'     => 'bg-gray-100 text-gray-600',
                'paid'        => 'bg-emerald-100 text-emerald-700',
                'unpaid'      => 'bg-amber-100 text-amber-700',
                'pending'     => 'bg-amber-100 text-amber-700',
                'booked'      => 'bg-blue-100 text-blue-700',
                'confirmed'   => 'bg-indigo-100 text-indigo-700',
                'ticketed'    => 'bg-teal-100 text-teal-700',
                'ticket_failed' => 'bg-red-100 text-red-600',
                'cancelled'   => 'bg-rose-100 text-rose-700',
                'pnr_pending' => 'bg-orange-100 text-orange-700',
                'processing'  => 'bg-purple-100 text-purple-700',
                'refunded'    => 'bg-cyan-100 text-cyan-700',
            ];
        @endphp

        {{-- Desktop status tabs --}}
        <div class="hidden md:flex flex-wrap items-center gap-3 mb-6">
            <button type="button"
                class="status-tab active-tab group relative overflow-hidden whitespace-nowrap px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-gray-900 to-gray-700 text-white shadow-md transition-all duration-300 hover:scale-105 hover:shadow-lg"
                data-status="">
                <span class="relative z-10 text-white flex items-center justify-center gap-2">
                    <i class="fa fa-th-large text-xs"></i>
                    {{ __("All") }}
                </span>
            </button>
            @foreach($statues as $status)
                @php
                    $colorClass = $tabColors[strtolower($status)] ?? $tabColors['default'];
                @endphp
                <button type="button"
                    class="status-tab group relative overflow-hidden whitespace-nowrap px-6 py-2.5 rounded-xl text-sm font-semibold {{ $colorClass }} shadow-sm transition-all duration-300 hover:scale-105 hover:shadow-md"
                    data-status="{{ $status }}">
                    <span class="relative z-10 flex items-center justify-center gap-2">
                        {{ booking_status_to_text($status) }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Mobile status dropdown + Search --}}
        <div class="flex md:hidden items-center gap-2 mb-4">
            <div class="flex-1">
                <select id="mobileStatusSelect"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-400 bg-white">
                    <option value="">{{ __("All Status") }}</option>
                    @foreach($statues as $status)
                        <option value="{{ $status }}">{{ booking_status_to_text($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative flex-1 min-w-0">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input type="text" id="globalSearchMobile"
                       placeholder="{{ __('Search...') }}"
                       class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400 transition">
            </div>
        </div>


        {{-- Search & Filter Bar --}}
        <div class="flex flex-wrap items-center gap-2 mb-6">

            {{-- Show entries (desktop only) --}}
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500 shrink-0">
                <span>{{ __('Show') }}</span>
                <select id="lengthSelect" class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:border-blue-400">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Divider (desktop only) --}}
            <div class="w-px h-8 bg-gray-200 shrink-0 hidden sm:block"></div>

            {{-- Global Search (desktop only) --}}
            <div class="hidden md:block relative flex-1 min-w-[160px]">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </span>
                <input type="text" id="globalSearch"
                       placeholder="{{ __('Search code, PNR...') }}"
                       class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400 transition">
            </div>

            {{-- From Date (desktop only) --}}
            <div class="hidden sm:block relative min-w-[140px]">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
                <input type="text" id="fromDate"
                       placeholder="{{ __('From date') }}"
                       class="flatpickr w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400 transition"
                       readonly>
            </div>

            {{-- To Date (desktop only) --}}
            <div class="hidden sm:block relative min-w-[140px]">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
                <input type="text" id="toDate"
                       placeholder="{{ __('To date') }}"
                       class="flatpickr w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400 transition"
                       readonly>
            </div>

            {{-- Clear Button (desktop only) --}}
            <button id="clearFilters" title="{{ __('Clear filters') }}"
                    class="hidden sm:flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-400 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        {{-- Table --}}
        {{-- Desktop Table --}}
<div class="hidden lg:block overflow-x-auto">
    <table id="bookingTable" class="w-full text-sm text-left">
        <thead>
        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <th class="px-4 py-3 rounded-l-lg font-semibold">{{ __("Code") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Order Date") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Flight Time") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("PNR") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Total") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Paid") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Remain") }}</th>
            <th class="px-4 py-3 font-semibold">{{ __("Status") }}</th>
            <th class="px-4 py-3 rounded-r-lg font-semibold">{{ __("Action") }}</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-gray-50">
        @forelse($bookings as $booking)

            @php
                $detailUrl = route('booking.details', $booking->id);
                $statusClass = 'status-' . $booking->status;
            @endphp

            <tr class="booking-row hover:bg-gray-50 transition">
                <td class="px-4 py-4">
                    <span class="font-semibold text-gray-800 hover:text-blue-600 cursor-pointer">
                        {{ $booking->code }}
                    </span>
                </td>

                <td class="px-4 py-4 text-gray-600">
                    {{ display_date($booking->created_at) }}
                </td>

                <td class="px-4 py-4 text-gray-600 leading-relaxed">
                    <span class="text-xs text-gray-400">{{ __("Dep") }}:</span>
                    {{ display_datetime($booking->start_date) }}

                    <br>

                    <span class="text-xs text-gray-400">{{ __("Arr") }}:</span>
                    {{ display_datetime($booking->end_date) }}
                </td>

                <td class="px-4 py-4 font-mono text-gray-700">
                    {{ $booking->pnr_id }}
                </td>

                <td class="px-4 py-4 font-semibold text-gray-800">
                    {{ format_money($booking->total) }}
                </td>

                <td class="px-4 py-4 text-green-600 font-medium">
                    {{ format_money($booking->paid) }}
                </td>

                <td class="px-4 py-4 text-red-500 font-medium">
                    {{ format_money($booking->total - $booking->paid) }}
                </td>

                <td class="px-4 py-4">
                    <span class="status-badge {{ $statusClass }}">
                        {{ $booking->statusName }}
                    </span>
                </td>

                <td class="px-4 py-4">
                    <a href="{{ $detailUrl }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-600 hover:text-white transition">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-3.5 h-3.5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>

                        {{ __("View") }}
                    </a>
                </td>
            </tr>

        @empty

            <tr>
                <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                    {{ __("No Booking History") }}
                </td>
            </tr>

        @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile Card View --}}
<div class="lg:hidden space-y-4" data-mobile-cards>

    @forelse($bookings as $booking)

        @php
            $detailUrl = route('booking.details', $booking->id);
            $statusClass = 'status-' . $booking->status;
        @endphp

        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm cursor-pointer active:scale-[0.99] transition-transform" data-card data-status="{{ $booking->status }}" data-created="{{ $booking->created_at }}" onclick="window.location='{{ $detailUrl }}'">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-4">

                <div>
                    <p class="text-xs text-gray-400 mb-1">
                        {{ __("PNR") }}
                    </p>

                    <h3 class="text-base font-bold text-gray-800">
                        {{ $booking->pnr_id }}
                    </h3>
                </div>

                <span class="status-badge {{ $statusClass }}">
                    {{ $booking->statusName }}
                </span>
            </div>

            {{-- Body --}}
            <div class="space-y-3 text-sm">
                <div class="border-t border-dashed border-gray-100 pt-2 mt-2">

                    <div class="grid grid-cols-3 gap-2 text-center">

                        {{-- Total --}}
                        <div class="bg-gray-50 rounded-lg py-2 px-1">
                            <p class="text-[10px] text-gray-400 leading-none mb-1">
                                {{ __("Total") }}
                            </p>

                            <h4 class="text-xs font-bold text-gray-800 leading-none">
                                {{ format_money($booking->total) }}
                            </h4>
                        </div>

                        {{-- Paid --}}
                        <div class="bg-green-50 rounded-lg py-2 px-1">
                            <p class="text-[10px] text-green-500 leading-none mb-1">
                                {{ __("Paid") }}
                            </p>

                            <h4 class="text-xs font-bold text-green-600 leading-none">
                                {{ format_money($booking->paid) }}
                            </h4>
                        </div>

                        {{-- Remain --}}
                        <div class="bg-red-50 rounded-lg py-2 px-1">
                            <p class="text-[10px] text-red-400 leading-none mb-1">
                                {{ __("Remain") }}
                            </p>

                            <h4 class="text-xs font-bold text-red-500 leading-none">
                                {{ format_money($booking->total - $booking->paid) }}
                            </h4>
                        </div>

                    </div>

                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">{{ __("Order Date") }}</span>

                    <span class="font-medium text-gray-700">
                        {{ display_date($booking->created_at) }}
                    </span>
                </div>
            </div>

            {{-- Button --}}
            <a href="{{ $detailUrl }}" onclick="event.stopPropagation()"
               class="mt-5 w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-black text-md font-semibold hover:bg-blue-700 transition" style="background: #03c5ff">

                {{ __("View Details") }}
            </a>

        </div>

    @empty

        <div class="bg-white border border-gray-100 rounded-2xl p-10 text-center">
            <span class="text-sm text-gray-400">
                {{ __("No Booking History") }}
            </span>
        </div>

    @endforelse

</div>

{{-- Mobile Pagination --}}
<div class="lg:hidden flex flex-col items-center gap-3 mt-4">
    <div id="mobilePageInfo" class="text-xs text-gray-400"></div>
    <div id="mobilePaginate" class="flex flex-wrap items-center justify-center gap-1"></div>
</div>

        {{-- DataTable pagination will render here --}}
        <div class="hidden lg:flex flex-col sm:flex-row justify-between items-center mt-4 gap-3 text-sm text-gray-500">
            <div id="tableInfo" class="text-xs"></div>
            <div id="bookingTable_paginate"></div>
        </div>

    </div>

@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function () {

            // ─── DataTable Init ───────────────────────────────────────────
            var table = $('#bookingTable').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[1, 'desc']],
                pagingType: 'simple_numbers',
                dom: 'rtip', // no default length/search
                language: {
                    paginate: { previous: "‹", next: "›" },
                    info: "Showing _START_–_END_ of _TOTAL_ bookings",
                    infoEmpty: "No bookings found",
                    emptyTable: "No booking history"
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var info = api.page.info();
                    var start = info.recordsDisplay === 0 ? 0 : info.start + 1;
                    $('#tableInfo').text(
                        'Showing ' + start + '–' + (info.end) + ' of ' + info.recordsDisplay + ' bookings'
                    );
                }
            });

            // Custom length select
            $('#lengthSelect').on('change', function() {
                table.page.len($(this).val()).draw();
            });

            // ─── Code cell click → Details Page ──────────────────────────
            $('#bookingTable tbody').on('click', '.code-link', function (e) {
                e.stopPropagation();
                var url = $(this).data('url');
                if (url) window.location.href = url;
            });

            // ─── Global Search ────────────────────────────────────────────
            $('#globalSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // ─── Date Range Filter (custom) ───────────────────────────────
            var fromDate = null, toDate = null;

            flatpickr('#fromDate', {
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr) {
                    fromDate = dateStr || null;
                    table.draw();
                }
            });

            flatpickr('#toDate', {
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr) {
                    toDate = dateStr || null;
                    table.draw();
                }
            });

            // Custom date range search plugin
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (!fromDate && !toDate) return true;

                // Column index 1 = Order Date (created_at)
                var rawDate = $(table.row(dataIndex).node()).find('td:eq(1)').text().trim();
                // Try parsing via the row's data attribute for accuracy
                var rowDate = table.row(dataIndex).node()
                    ? $(table.row(dataIndex).node()).data('created') || rawDate
                    : rawDate;

                var date = new Date(rowDate);
                if (isNaN(date)) return true;

                var dateStr = date.toISOString().slice(0, 10); // YYYY-MM-DD

                if (fromDate && dateStr < fromDate) return false;
                if (toDate   && dateStr > toDate)   return false;
                return true;
            });

            // ─── Status Tab Filter ────────────────────────────────────────
            $('.status-tab').on('click', function (e) {
                e.preventDefault();

                // Remove active from all + restore original light classes
                $('.status-tab').removeClass('active-tab').each(function() {
                    $(this).removeClass('bg-gray-800 text-white bg-emerald-600 bg-amber-600 bg-blue-600 bg-indigo-600 bg-teal-600 bg-red-600 bg-rose-600 bg-orange-600 bg-purple-600 bg-cyan-600 bg-gray-600');
                });

                $(this).addClass('active-tab');

                var status = $(this).data('status');

                // Make active tab solid/dark
                if (status === '') {
                    $(this).addClass('bg-gray-800 text-white');
                } else {
                    var solidMap = {
                        'bg-emerald-100': 'bg-emerald-600',
                        'bg-amber-100':   'bg-amber-600',
                        'bg-blue-100':    'bg-blue-600',
                        'bg-indigo-100':  'bg-indigo-600',
                        'bg-teal-100':    'bg-teal-600',
                        'bg-red-100':     'bg-red-600',
                        'bg-rose-100':    'bg-rose-600',
                        'bg-orange-100':  'bg-orange-600',
                        'bg-purple-100':  'bg-purple-600',
                        'bg-cyan-100':    'bg-cyan-600',
                        'bg-gray-100':    'bg-gray-600',
                    };
                    var classes = $(this).attr('class').split(' ');
                    classes.forEach(function(cls) {
                        if (solidMap[cls]) {
                            $(this).removeClass(cls).addClass(solidMap[cls] + ' text-white');
                        }
                    }.bind(this));
                }

                if (status === '') {
                    table.column(7).search('').draw();
                } else {
                    table.column(7).search(status, false, false).draw();
                }
            });

            // ─── Clear All Filters ────────────────────────────────────────
            $('#clearFilters').on('click', function () {
                fromDate = null;
                toDate = null;

                $('#globalSearch').val('');
                document.querySelector('#fromDate')._flatpickr.clear();
                document.querySelector('#toDate')._flatpickr.clear();

                // Reset all tabs - remove active + solid overrides
                $('.status-tab').removeClass('active-tab bg-gray-800 text-white bg-emerald-600 bg-amber-600 bg-blue-600 bg-indigo-600 bg-teal-600 bg-red-600 bg-rose-600 bg-orange-600 bg-purple-600 bg-cyan-600 bg-gray-600');
                // Re-apply white text to "All" tab active state
                $('.status-tab[data-status=""]').addClass('active-tab bg-gray-800 text-white');

                table.search('').column(7).search('').draw();

                // Reset mobile status dropdown and search
                $('#mobileStatusSelect').val('');
                $('#globalSearchMobile').val('');
            });

            // ─── Mobile Card Pagination & Search/Filter ─────────────────
            var mobPerPage = 10;
            var mobContainer = $('[data-mobile-cards]');
            var mobCards = mobContainer.children('[data-card]');
            var mobTotal = mobCards.length;
            var mobCurrentPage = 1;
            var mobFiltered = null;

            function getMobileStatusVal() {
                var sel = $('#mobileStatusSelect').val();
                if (sel && sel !== '') return sel;
                return $('.status-tab.active-tab').data('status') || '';
            }

            function filterMobileCards() {
                var searchVal = $('#globalSearch').val().toLowerCase();
                var statusVal = getMobileStatusVal();
                var fromVal = fromDate;
                var toVal = toDate;

                mobCards.each(function() {
                    var card = $(this);
                    var text = card.text().toLowerCase();
                    var cardStatus = card.data('status') || '';
                    var show = true;

                    if (searchVal && text.indexOf(searchVal) === -1) show = false;
                    if (statusVal && cardStatus !== statusVal) show = false;
                    if (fromVal || toVal) {
                        var dateStr = card.data('created') || '';
                        if (fromVal && dateStr < fromVal) show = false;
                        if (toVal && dateStr > toVal) show = false;
                    }

                    card.toggle(show);
                });

                mobFiltered = mobCards.filter(function() { return $(this).css('display') !== 'none'; });
                mobTotal = mobFiltered.length;
                renderMobilePagination(1);
            }

            function renderMobilePagination(page) {
                var totalPages = Math.ceil(mobTotal / mobPerPage) || 1;
                mobCurrentPage = Math.max(1, Math.min(page, totalPages));

                mobCards.hide();
                if (mobFiltered) {
                    mobFiltered.slice((mobCurrentPage - 1) * mobPerPage, mobCurrentPage * mobPerPage).show();
                }

                var start = mobTotal === 0 ? 0 : (mobCurrentPage - 1) * mobPerPage + 1;
                var end = Math.min(mobCurrentPage * mobPerPage, mobTotal);
                $('#mobilePageInfo').text(mobTotal > 0
                    ? 'Showing ' + start + '–' + end + ' of ' + mobTotal + ' bookings'
                    : 'No bookings found');

                var html = '';
                if (totalPages > 1) {
                    html += '<button class="mob-page-btn px-3 py-1.5 rounded-lg border text-xs font-medium ' + (mobCurrentPage === 1 ? 'opacity-40 cursor-not-allowed bg-gray-100' : 'hover:bg-blue-50 hover:border-blue-300 bg-white text-gray-700') + '" data-page="' + (mobCurrentPage - 1) + '" ' + (mobCurrentPage === 1 ? 'disabled' : '') + '>‹</button>';
                    var rangeStart = 1, rangeEnd = totalPages;
                    if (totalPages > 7) {
                        rangeStart = Math.max(1, mobCurrentPage - 2);
                        rangeEnd = Math.min(totalPages, mobCurrentPage + 2);
                        if (mobCurrentPage <= 3) { rangeStart = 1; rangeEnd = 5; }
                        if (mobCurrentPage >= totalPages - 2) { rangeStart = totalPages - 4; rangeEnd = totalPages; }
                    }
                    if (rangeStart > 1) {
                        html += '<button class="mob-page-btn px-3 py-1.5 rounded-lg border text-xs font-medium hover:bg-gray-50 bg-white text-gray-600" data-page="1">1</button>';
                        if (rangeStart > 2) html += '<span class="px-1 text-gray-400 text-xs">…</span>';
                    }
                    for (var i = rangeStart; i <= rangeEnd; i++) {
                        html += '<button class="mob-page-btn px-3 py-1.5 rounded-lg border text-xs font-medium ' + (i === mobCurrentPage ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'hover:bg-gray-50 bg-white text-gray-600') + '" data-page="' + i + '">' + i + '</button>';
                    }
                    if (rangeEnd < totalPages) {
                        if (rangeEnd < totalPages - 1) html += '<span class="px-1 text-gray-400 text-xs">…</span>';
                        html += '<button class="mob-page-btn px-3 py-1.5 rounded-lg border text-xs font-medium hover:bg-gray-50 bg-white text-gray-600" data-page="' + totalPages + '">' + totalPages + '</button>';
                    }
                    html += '<button class="mob-page-btn px-3 py-1.5 rounded-lg border text-xs font-medium ' + (mobCurrentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-gray-100' : 'hover:bg-blue-50 hover:border-blue-300 bg-white text-gray-700') + '" data-page="' + (mobCurrentPage + 1) + '" ' + (mobCurrentPage === totalPages ? 'disabled' : '') + '>›</button>';
                }
                $('#mobilePaginate').html(html);
            }

            $(document).on('click', '.mob-page-btn:not([disabled])', function() {
                renderMobilePagination(parseInt($(this).data('page')));
            });

            // Wire search/filter to mobile
            $('#globalSearch').on('keyup', function() {
                $('#globalSearchMobile').val($(this).val());
                filterMobileCards();
            });

            $('#globalSearchMobile').on('keyup', function() {
                $('#globalSearch').val($(this).val());
                filterMobileCards();
            });

            $('#mobileStatusSelect').on('change', function() {
                filterMobileCards();
            });

            $('#lengthSelect').on('change', function() {
                mobPerPage = parseInt($(this).val()) || 10;
                if (window.innerWidth < 1024) renderMobilePagination(1);
            });

            $('.status-tab').on('click', function() {
                setTimeout(filterMobileCards, 50);
            });

            // Override date filter draw to also update mobile
            var origDraw = table.draw.bind(table);
            table.draw = function() {
                origDraw();
                if (window.innerWidth < 1024) filterMobileCards();
            };

            filterMobileCards();
        });
    </script>
@endpush
