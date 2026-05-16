@extends('layouts.user')

@push('css')
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <style>
        #ssrRequestsTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #ssrRequestsTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
            border-color: #f3f4f6;
            color: #374151;
        }
        #ssrRequestsTable tbody tr:nth-child(even) td { background-color: #f8fafc; }
        #ssrRequestsTable tbody tr:hover td           { background-color: #eff6ff !important; }
        #ssrRequestsTable tbody tr.selected td        { background-color: #dbeafe !important; }
        #ssrRequestsTable tbody tr                    { cursor: pointer; }

        #ssrRequestsTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
            transition: border .15s, box-shadow .15s;
        }
        #ssrRequestsTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #ssrRequestsTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #ssrRequestsTable_info,
        #ssrRequestsTable_length label,
        #ssrRequestsTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #ssrRequestsTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #ssrRequestsTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #ssrRequestsTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
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
            transition: background .12s, border .12s;
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

        .r-badge {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 9999px;
            font-size: 0.71rem; font-weight: 600; white-space: nowrap;
        }
        .b-warning   { background:#fef3c7; color:#92400e; }
        .b-info      { background:#dbeafe; color:#1e40af; }
        .b-success   { background:#d1fae5; color:#065f46; }
        .b-danger    { background:#fee2e2; color:#991b1b; }
        .b-dark      { background:#e5e7eb; color:#111827; }
        .b-primary   { background:#ede9fe; color:#5b21b6; }
        .b-secondary { background:#f3f4f6; color:#374151; }

        @media (max-width: 600px) {
            #ssrRequestsTable_wrapper > div { display: flex; flex-direction: column; gap: 8px; }
            #ssrRequestsTable_filter, #ssrRequestsTable_length,
            #ssrRequestsTable_info,   #ssrRequestsTable_paginate {
                width: 100%; text-align: left !important; float: none !important;
            }
            #ssrRequestsTable thead th,
            #ssrRequestsTable tbody td { padding: 7px 8px; font-size: 0.76rem; }
        }
    </style>
@endpush

@section('content')
    <div class="px-3 py-4 sm:px-5 md:px-6">

        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">
            {{ __('SSR Requests') }}
        </h2>

        @if(session('success'))
            <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-4">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-4">
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full overflow-hidden">
            <div class="p-4 sm:p-5 w-full min-w-0">

                <table id="ssrRequestsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>{{ __('Booking Code') }}</th>
                        <th>{{ __('Passenger') }}</th>
                        <th>{{ __('SSR Type') }}</th>
                        <th>{{ __('SSR Code') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested At') }}</th>
                        <th>{{ __('Action') }}</th>  {{-- class="none" নেই — সবসময় দেখাবে --}}
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td class="font-semibold">{{ $request->booking->code ?? 'N/A' }}</td>
                            <td>{{ $request->passenger_id ?? 'N/A' }}</td>
                            <td>
                                <span class="r-badge b-info">{{ ucfirst($request->ssr_type) }}</span>
                            </td>
                            <td>{{ $request->ssr_code ?? 'N/A' }}</td>
                            <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $request->description ?? 'N/A' }}
                            </td>
                            <td>
                                    <span style="font-weight:700;color:#065f46">
                                        {{ number_format($request->amount, 2) }}
                                    </span>
                            </td>
                            <td>
                                @php
                                    $map = [
                                        'pending'               => 'b-warning',
                                        'confirmed'             => 'b-success',
                                        'failed'                => 'b-danger',
                                        'cancelled'             => 'b-dark',
                                        'waiting_user_approval' => 'b-primary',
                                        'user_approved'         => 'b-success',
                                        'user_rejected'         => 'b-danger',
                                    ];
                                    $cls = $map[$request->status] ?? 'b-secondary';
                                @endphp
                                <span class="r-badge {{ $cls }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                            </td>
                            <td data-order="{{ $request->created_at->timestamp }}"
                                style="white-space:nowrap">
                                {{ $request->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($request->status === 'waiting_user_approval')
                                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                                        <a href="{{ route('user.ssr.approve', $request->id) }}"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#16a34a;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"
                                           onclick="return confirm('Approve this SSR request?\nAmount: {{ number_format($request->amount, 2) }}')">
                                            <i class="fa fa-check"></i> Approve
                                        </a>
                                        <a href="{{ route('user.ssr.reject', $request->id) }}"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"
                                           onclick="return confirm('Are you sure you want to reject this SSR request?')">
                                            <i class="fa fa-times"></i> Reject
                                        </a>
                                    </div>
                                @else
                                    <span style="color:#9ca3af;font-size:.8rem">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#9ca3af">
                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                                {{ __('No SSR requests found.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#ssrRequestsTable').DataTable({

                dom:
                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",

                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[7, 'desc']],   // Requested At (col index 7)

                responsive: {
                    details: {
                        type: 'inline',
                        renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes()
                    }
                },

                columnDefs: [
                    { responsivePriority: 1,  targets: 0 },                   // Booking Code  — সবসময়
                    { responsivePriority: 2,  targets: 6 },                   // Status        — সবসময়
                    { responsivePriority: 3,  targets: 7 },                   // Requested At  — সবসময়
                    { responsivePriority: 4,  targets: 8, orderable: false }, // Action        — সবসময়
                    { responsivePriority: 5,  targets: 5 },                   // Amount
                    { responsivePriority: 6,  targets: 2 },                   // SSR Type
                    { responsivePriority: 7,  targets: 3 },                   // SSR Code
                    { responsivePriority: 8,  targets: 1 },                   // Passenger
                    { responsivePriority: 9,  targets: 4 },                   // Description — আগে লুকাবে
                ],

                select: { style: 'multi', selector: 'td:not(:last-child)' },

                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',       exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csv',   text: '<i class="fa fa-file-csv"></i> CSV',     exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf"></i> PDF',     exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',      exportOptions: { columns: ':not(:last-child)' } },
                ],

                language: {
                    search: '', searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    infoEmpty: 'No requests found',
                    emptyTable: '{{ __("No SSR requests found.") }}',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                    select: { rows: { _: '%d selected', 0: '', 1: '1 selected' } }
                },
            });
        });
    </script>
@endpush
