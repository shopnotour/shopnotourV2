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
            #bookingTable thead { display: none; }
            #bookingTable tbody tr {
                display: block;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                margin-bottom: 12px;
                padding: 12px;
                background: #fff;
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }
            #bookingTable tbody td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding: 6px 0;
                border: none;
                font-size: 13px;
                border-bottom: 1px solid #f3f4f6;
            }
            #bookingTable tbody td:last-child {
                border-bottom: none;
            }
            #bookingTable tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6b7280;
                font-size: 12px;
                min-width: 110px;
                flex-shrink: 0;
            }
        }

        /* Flatpickr custom */
        .flatpickr-input {
            background: #fff !important;
        }

        /* Status tab pills */
        .status-tab {
            transition: all 0.15s;
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

        {{-- Status Filter Tabs — colorful pills --}}
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
        <div class="flex flex-wrap gap-2 mb-6">
            <button
                class="status-tab active-tab whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-800 text-white"
                data-status="">
                {{ __("All") }}
            </button>
            @foreach($statues as $status)
                @php
                    $colorClass = $tabColors[strtolower($status)] ?? $tabColors['default'];
                @endphp
                <button
                    class="status-tab whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-semibold {{ $colorClass }}"
                    data-status="{{ $status }}">
                    {{ booking_status_to_text($status) }}
                </button>
            @endforeach
        </div>

        {{-- Search & Filter Bar + Show entries — same row --}}
        <div class="flex flex-wrap items-center gap-2 mb-6">

            {{-- Show entries --}}
            <div class="flex items-center gap-2 text-sm text-gray-500 shrink-0">
                <span>{{ __('Show') }}</span>
                <select id="lengthSelect" class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:border-blue-400">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {{-- Divider --}}
            <div class="w-px h-8 bg-gray-200 shrink-0 hidden sm:block"></div>

            {{-- Global Search --}}
            <div class="relative flex-1 min-w-[160px]">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </span>
                <input type="text" id="globalSearch"
                       placeholder="{{ __('Search code, PNR...') }}"
                       class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:border-blue-400 transition">
            </div>

            {{-- From Date --}}
            <div class="relative min-w-[140px]">
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

            {{-- To Date --}}
            <div class="relative min-w-[140px]">
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

            {{-- Clear Button — icon only with tooltip --}}
            <button id="clearFilters" title="{{ __('Clear filters') }}"
                    class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-400 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        {{-- Table --}}
        <div class="table-responsive-wrapper">
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
                    <tr class="booking-row">
                        <td class="px-4 py-3" data-label="Code">
                            <span class="code-link font-semibold text-gray-800 hover:text-blue-600 transition-colors"
                                  data-url="{{ $detailUrl }}" style="cursor:pointer;">
                                {{ $booking->code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600" data-label="Order Date">
                            {{ display_date($booking->created_at) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 leading-relaxed" data-label="Flight Time">
                            <span class="text-xs text-gray-400">{{ __("Dep") }}:</span> {{ display_datetime($booking->start_date) }}<br>
                            <span class="text-xs text-gray-400">{{ __("Arr") }}:</span> {{ display_datetime($booking->end_date) }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700" data-label="PNR">
                            {{ $booking->pnr_id }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800" data-label="Total">
                            {{ format_money($booking->total) }}
                        </td>
                        <td class="px-4 py-3 text-green-600 font-medium" data-label="Paid">
                            {{ format_money($booking->paid) }}
                        </td>
                        <td class="px-4 py-3 text-red-500 font-medium" data-label="Remain">
                            {{ format_money($booking->total - $booking->paid) }}
                        </td>
                        <td class="px-4 py-3" data-label="Status">
                            <span class="status-badge {{ $statusClass }}">
                                {{ $booking->statusName }}
                            </span>
                        </td>
                        <td class="px-4 py-3" data-label="Action">
                            <a href="{{ $detailUrl }}"
                               class="view-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-600 hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ __("View") }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-16 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm">{{ __("No Booking History") }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- DataTable pagination will render here --}}
        <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-3 text-sm text-gray-500">
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
            $('.status-tab').on('click', function () {
                // Remove active from all
                $('.status-tab').removeClass('active-tab');

                // Restore original color classes (remove solid overrides)
                $('.status-tab').each(function() {
                    $(this).removeClass('bg-gray-800 text-white bg-emerald-600 bg-amber-600 bg-blue-600 bg-indigo-600 bg-teal-600 bg-red-600 bg-rose-600 bg-orange-600 bg-purple-600 bg-cyan-600 bg-gray-600');
                });

                $(this).addClass('active-tab');

                var status = $(this).data('status');

                // Make active tab solid/dark
                if (status === '') {
                    $(this).addClass('bg-gray-800 text-white');
                } else {
                    // Replace light bg with solid version
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
            });

        });
    </script>
@endpush
