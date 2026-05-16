@extends('layouts.user')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #reissueRequestsTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #reissueRequestsTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
            border-color: #f3f4f6;
            color: #374151;
        }
        #reissueRequestsTable tbody tr:nth-child(even) td { background-color: #f8fafc; }
        #reissueRequestsTable tbody tr:hover td           { background-color: #eff6ff !important; }
        #reissueRequestsTable tbody tr.selected td        { background-color: #dbeafe !important; }
        #reissueRequestsTable tbody tr                    { cursor: pointer; }

        #reissueRequestsTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
            transition: border .15s, box-shadow .15s;
        }
        #reissueRequestsTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #reissueRequestsTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #reissueRequestsTable_info,
        #reissueRequestsTable_length label,
        #reissueRequestsTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #reissueRequestsTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #reissueRequestsTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #reissueRequestsTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
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

        /* Responsive child row */
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

        /* Badges */
        .r-badge {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 9999px;
            font-size: 0.71rem; font-weight: 600; white-space: nowrap;
        }
        .b-warning   { background:#fef3c7; color:#92400e; }
        .b-info      { background:#dbeafe; color:#1e40af; }
        .b-success   { background:#d1fae5; color:#065f46; }
        .b-danger    { background:#fee2e2; color:#991b1b; }
        .b-primary   { background:#ede9fe; color:#5b21b6; }
        .b-secondary { background:#f3f4f6; color:#374151; }

        @media (max-width: 600px) {
            #reissueRequestsTable_wrapper > div { display: flex; flex-direction: column; gap: 8px; }
            #reissueRequestsTable_filter, #reissueRequestsTable_length,
            #reissueRequestsTable_info,   #reissueRequestsTable_paginate {
                width: 100%; text-align: left !important; float: none !important;
            }
            #reissueRequestsTable thead th,
            #reissueRequestsTable tbody td { padding: 7px 8px; font-size: 0.76rem; }
        }
    </style>
@endpush

@section('content')
    <div class="px-3 py-4 sm:px-5 md:px-6">

        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">
            {{ __('Reissue Requests') }}
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

                <table id="reissueRequestsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>{{ __('Booking Code') }}</th>
                        <th>{{ __('Old PNR') }}</th>
                        <th>{{ __('New PNR') }}</th>
                        <th>{{ __('Reissue Type') }}</th>
                        <th>{{ __('Reissue Charges') }}</th>
                        <th>{{ __('Service Charges') }}</th>
                        <th>{{ __('Fare Difference') }}</th>
                        <th>{{ __('Total Amount') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested At') }}</th>
                        <th>{{ __('Action') }}</th>  {{-- NO class="none" — সবসময় দেখাবে --}}
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td class="font-semibold">{{ $request->booking->code ?? 'N/A' }}</td>
                            <td>{{ $request->old_pnr ?? 'N/A' }}</td>
                            <td>{{ $request->new_pnr ?? 'N/A' }}</td>
                            <td>
                                <span class="r-badge b-info">{{ ucfirst($request->reissue_type) }}</span>
                            </td>
                            <td>{{ number_format($request->reissue_charges, 2) }}</td>
                            <td>{{ number_format($request->service_charge, 2) }}</td>
                            <td>{{ number_format($request->fare_difference, 2) }}</td>
                            <td>
                                <span style="font-weight:700;color:#065f46">
                                    {{ number_format($request->total_amount, 2) }}
                                </span>
                            </td>
                            <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $request->reason ?? 'N/A' }}
                            </td>
                            <td>
                                @php
                                    $map = [
                                        'pending'               => 'b-warning',
                                        'processing'            => 'b-info',
                                        'completed'             => 'b-success',
                                        'failed'                => 'b-danger',
                                        'rejected'              => 'b-danger',
                                        'approved'              => 'b-success',
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
                            <td data-order="{{ $request->requested_at ? $request->requested_at->timestamp : 0 }}"
                                style="white-space:nowrap">
                                {{ $request->requested_at ? $request->requested_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                            <td>
                                @if($request->status === 'waiting_user_approval')
                                    <div style="display:flex;flex-wrap:wrap;gap:4px">
                                        <a href="{{ route('user.reissue.approve', $request->id) }}"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#16a34a;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"
                                           onclick="return confirm('Approve this reissue request?\nTotal amount: {{ number_format($request->total_amount, 2) }}')">
                                            <i class="fa fa-check"></i> Approve
                                        </a>
                                        <a href="{{ route('user.reissue.reject', $request->id) }}"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"
                                           onclick="return confirm('Reject this reissue request?')">
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
                            <td colspan="11" style="text-align:center;padding:40px;color:#9ca3af">
                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                                {{ __('No reissue requests found.') }}
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
            $('#reissueRequestsTable').DataTable({

                dom:
                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",

                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                order: [[9, 'desc']], // Requested At desc

                // ── Responsive: hidden columns collapse into child row ──
                responsive: {
                    details: {
                        type: 'inline',
                        renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes()
                    }
                },

                // ── Column priority (1 = সবসময় দেখাবে, বেশি = আগে লুকাবে) ──
                columnDefs: [
                    { responsivePriority: 1,  targets: 0  },  // Booking Code
                    { responsivePriority: 2,  targets: 8  },  // Status
                    { responsivePriority: 3,  targets: 9  },  // Requested At
                    { responsivePriority: 4,  targets: 6  },  // Total Amount
                    { responsivePriority: 5,  targets: 10, orderable: false }, // Action — সবসময় দেখাবে
                    { responsivePriority: 6,  targets: 1  },  // Old PNR
                    { responsivePriority: 7,  targets: 2  },  // New PNR
                    { responsivePriority: 8,  targets: 3  },  // Reissue Type
                    { responsivePriority: 9,  targets: 4  },  // Reissue Charges
                    { responsivePriority: 10, targets: 5  },  // Fare Difference
                    { responsivePriority: 11, targets: 7  },  // Reason (সবার আগে লুকাবে)
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
                    emptyTable: '{{ __("No reissue requests found.") }}',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                    select: { rows: { _: '%d selected', 0: '', 1: '1 selected' } }
                },
            });
        });
    </script>
@endpush
