{{--@extends('layouts.user')--}}

{{--@push('css')--}}
{{--    --}}{{-- Tailwind CSS CDN --}}
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}

{{--    --}}{{-- DataTables CSS (vanilla, no Bootstrap dependency) --}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">--}}

{{--    <style>--}}
{{--        /* ── Table header ─────────────────────────────────────── */--}}
{{--        #voidRequestsTable thead th {--}}
{{--            background-color: #1e3a5f;--}}
{{--            color: #fff;--}}
{{--            font-weight: 600;--}}
{{--            padding: 10px 12px;--}}
{{--            font-size: 0.82rem;--}}
{{--            white-space: nowrap;--}}
{{--            border-bottom: none !important;--}}
{{--        }--}}
{{--        /* ── Table body ───────────────────────────────────────── */--}}
{{--        #voidRequestsTable tbody td {--}}
{{--            padding: 9px 12px;--}}
{{--            font-size: 0.83rem;--}}
{{--            vertical-align: middle;--}}
{{--            border-color: #f3f4f6;--}}
{{--            color: #374151;--}}
{{--        }--}}
{{--        #voidRequestsTable tbody tr:nth-child(even) td { background-color: #f8fafc; }--}}
{{--        #voidRequestsTable tbody tr:hover td           { background-color: #eff6ff !important; }--}}
{{--        #voidRequestsTable tbody tr.selected td        { background-color: #dbeafe !important; }--}}
{{--        #voidRequestsTable tbody tr                    { cursor: pointer; }--}}

{{--        /* ── Search input ─────────────────────────────────────── */--}}
{{--        #voidRequestsTable_filter input {--}}
{{--            border: 1px solid #d1d5db;--}}
{{--            border-radius: 6px;--}}
{{--            padding: 5px 10px;--}}
{{--            font-size: 0.82rem;--}}
{{--            outline: none;--}}
{{--            transition: border .15s, box-shadow .15s;--}}
{{--        }--}}
{{--        #voidRequestsTable_filter input:focus {--}}
{{--            border-color: #3b82f6;--}}
{{--            box-shadow: 0 0 0 3px rgba(59,130,246,.15);--}}
{{--        }--}}
{{--        #voidRequestsTable_length select {--}}
{{--            border: 1px solid #d1d5db;--}}
{{--            border-radius: 6px;--}}
{{--            padding: 4px 8px;--}}
{{--            font-size: 0.82rem;--}}
{{--        }--}}
{{--        #voidRequestsTable_info,--}}
{{--        #voidRequestsTable_length label,--}}
{{--        #voidRequestsTable_filter label {--}}
{{--            font-size: 0.8rem;--}}
{{--            color: #6b7280;--}}
{{--        }--}}

{{--        /* ── Pagination ───────────────────────────────────────── */--}}
{{--        #voidRequestsTable_paginate .paginate_button {--}}
{{--            border-radius: 5px !important;--}}
{{--            padding: 4px 9px !important;--}}
{{--            font-size: 0.78rem !important;--}}
{{--            border: 1px solid #e5e7eb !important;--}}
{{--            margin: 0 1px;--}}
{{--            color: #374151 !important;--}}
{{--        }--}}
{{--        #voidRequestsTable_paginate .paginate_button.current {--}}
{{--            background: #1e3a5f !important;--}}
{{--            color: #fff !important;--}}
{{--            border-color: #1e3a5f !important;--}}
{{--        }--}}
{{--        #voidRequestsTable_paginate .paginate_button:hover:not(.current):not(.disabled) {--}}
{{--            background: #eff6ff !important;--}}
{{--            border-color: #3b82f6 !important;--}}
{{--            color: #1d4ed8 !important;--}}
{{--        }--}}

{{--        /* ── Export buttons ───────────────────────────────────── */--}}
{{--        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; }--}}
{{--        .dt-button {--}}
{{--            font-size: 0.75rem !important;--}}
{{--            padding: 5px 11px !important;--}}
{{--            border-radius: 5px !important;--}}
{{--            border: 1px solid #d1d5db !important;--}}
{{--            background: #fff !important;--}}
{{--            color: #374151 !important;--}}
{{--            cursor: pointer;--}}
{{--            transition: background .12s, border .12s;--}}
{{--        }--}}
{{--        .dt-button:hover { background: #f3f4f6 !important; border-color: #9ca3af !important; }--}}

{{--        /* ── Responsive child row ─────────────────────────────── */--}}
{{--        table.dataTable > tbody > tr.child ul.dtr-details { width: 100%; }--}}
{{--        table.dataTable > tbody > tr.child ul.dtr-details li {--}}
{{--            border-bottom: 1px solid #f3f4f6;--}}
{{--            padding: 6px 4px;--}}
{{--            font-size: 0.82rem;--}}
{{--            display: flex;--}}
{{--            gap: 8px;--}}
{{--            align-items: flex-start;--}}
{{--        }--}}
{{--        table.dataTable > tbody > tr.child ul.dtr-details li:last-child { border-bottom: none; }--}}
{{--        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before {--}}
{{--            background-color: #1e3a5f !important;--}}
{{--            border-color: #1e3a5f !important;--}}
{{--        }--}}

{{--        /* ── Status badges ────────────────────────────────────── */--}}
{{--        .void-badge {--}}
{{--            display: inline-flex; align-items: center;--}}
{{--            padding: 2px 10px; border-radius: 9999px;--}}
{{--            font-size: 0.71rem; font-weight: 600; white-space: nowrap;--}}
{{--        }--}}
{{--        .b-warning   { background:#fef3c7; color:#92400e; }--}}
{{--        .b-info      { background:#dbeafe; color:#1e40af; }--}}
{{--        .b-success   { background:#d1fae5; color:#065f46; }--}}
{{--        .b-danger    { background:#fee2e2; color:#991b1b; }--}}
{{--        .b-dark      { background:#e5e7eb; color:#111827; }--}}
{{--        .b-primary   { background:#ede9fe; color:#5b21b6; }--}}
{{--        .b-secondary { background:#f3f4f6; color:#374151; }--}}

{{--        /* ── Mobile: stack toolbar full-width ─────────────────── */--}}
{{--        @media (max-width: 600px) {--}}
{{--            #voidRequestsTable_wrapper > div { display: flex; flex-direction: column; gap: 8px; }--}}
{{--            #voidRequestsTable_filter, #voidRequestsTable_length,--}}
{{--            #voidRequestsTable_info,   #voidRequestsTable_paginate {--}}
{{--                width: 100%; text-align: left !important; float: none !important;--}}
{{--            }--}}
{{--            #voidRequestsTable thead th,--}}
{{--            #voidRequestsTable tbody td { padding: 7px 8px; font-size: 0.76rem; }--}}
{{--        }--}}
{{--    </style>--}}
{{--@endpush--}}

{{--@section('content')--}}
{{--    <div class="px-3 py-4 sm:px-5 md:px-6">--}}

{{--        --}}{{-- Page title --}}
{{--        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">--}}
{{--            {{ __('Void Requests') }}--}}
{{--        </h2>--}}

{{--        --}}{{-- Flash messages --}}
{{--        @if(session('success'))--}}
{{--            <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 mb-4">--}}
{{--                <i class="fa fa-check-circle"></i> {{ session('success') }}--}}
{{--            </div>--}}
{{--        @endif--}}
{{--        @if(session('error'))--}}
{{--            <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 mb-4">--}}
{{--                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        --}}{{-- Card --}}
{{--        <div class="bg-white rounded-xl shadow-sm border border-gray-100 w-full overflow-hidden">--}}
{{--            <div class="p-4 sm:p-5 w-full min-w-0">--}}

{{--                <table id="voidRequestsTable" style="width:100%">--}}
{{--                    <thead>--}}
{{--                    <tr>--}}
{{--                        <th>{{ __('Booking Code') }}</th>--}}
{{--                        <th>{{ __('PNR') }}</th>--}}
{{--                        <th>{{ __('Void Charges') }}</th>--}}
{{--                        <th>{{ __('Reason') }}</th>--}}
{{--                        <th>{{ __('Status') }}</th>--}}
{{--                        <th>{{ __('Requested At') }}</th>--}}
{{--                        <th class="none">{{ __('Action') }}</th>--}}
{{--                    </tr>--}}
{{--                    </thead>--}}
{{--                    <tbody>--}}
{{--                    @forelse($requests as $request)--}}
{{--                        <tr>--}}
{{--                            <td class="font-semibold">{{ $request->booking->code ?? 'N/A' }}</td>--}}
{{--                            <td>{{ $request->pnr ?? 'N/A' }}</td>--}}
{{--                            <td>{{ number_format($request->void_charges, 2) }}</td>--}}
{{--                            <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">--}}
{{--                                {{ $request->reason ?? 'N/A' }}--}}
{{--                            </td>--}}
{{--                            <td>--}}
{{--                                @php--}}
{{--                                    $map = [--}}
{{--                                        'pending'               => 'b-warning',--}}
{{--                                        'processing'            => 'b-info',--}}
{{--                                        'completed'             => 'b-success',--}}
{{--                                        'failed'                => 'b-danger',--}}
{{--                                        'voided'                => 'b-dark',--}}
{{--                                        'rejected'              => 'b-danger',--}}
{{--                                        'approved'              => 'b-success',--}}
{{--                                        'waiting_user_approval' => 'b-primary',--}}
{{--                                        'user_approved'         => 'b-success',--}}
{{--                                        'user_rejected'         => 'b-danger',--}}
{{--                                    ];--}}
{{--                                    $cls = $map[$request->status] ?? 'b-secondary';--}}
{{--                                @endphp--}}
{{--                                <span class="void-badge {{ $cls }}">--}}
{{--                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}--}}
{{--                                </span>--}}
{{--                            </td>--}}
{{--                            <td data-order="{{ $request->created_at->timestamp }}" style="white-space:nowrap">--}}
{{--                                {{ $request->created_at->format('d M Y, h:i A') }}--}}
{{--                            </td>--}}
{{--                            <td>--}}
{{--                                @if($request->status == 'waiting_user_approval')--}}
{{--                                    <div style="display:flex;flex-wrap:wrap;gap:4px">--}}
{{--                                        <a href="{{ route('user.void.approve', $request->id) }}"--}}
{{--                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#16a34a;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"--}}
{{--                                           onclick="return confirm('Approve this void request?')">--}}
{{--                                            <i class="fa fa-check"></i> Approve--}}
{{--                                        </a>--}}
{{--                                        <a href="{{ route('user.void.reject', $request->id) }}"--}}
{{--                                           style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:5px;background:#dc2626;color:#fff;font-size:.75rem;font-weight:600;text-decoration:none"--}}
{{--                                           onclick="return confirm('Reject this void request?')">--}}
{{--                                            <i class="fa fa-times"></i> Reject--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
{{--                                @else--}}
{{--                                    <span style="color:#9ca3af;font-size:.8rem">—</span>--}}
{{--                                @endif--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    @empty--}}
{{--                        <tr>--}}
{{--                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">--}}
{{--                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>--}}
{{--                                {{ __('No void requests found.') }}--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    @endforelse--}}
{{--                    </tbody>--}}
{{--                </table>--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}

{{--@push('js')--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>--}}
{{--    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>--}}

{{--    <script>--}}
{{--        $(document).ready(function () {--}}
{{--            $('#voidRequestsTable').DataTable({--}}
{{--                dom:--}}
{{--                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'B<'ml-auto'f>>" +--}}
{{--                    "tr" +--}}
{{--                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",--}}

{{--                pageLength: 10,--}}
{{--                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],--}}
{{--                order: [[5, 'desc']],--}}

{{--                // Responsive: collapse hidden columns into expandable child rows--}}
{{--                responsive: {--}}
{{--                    details: {--}}
{{--                        type: 'inline',--}}
{{--                        renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes()--}}
{{--                    }--}}
{{--                },--}}

{{--                columnDefs: [--}}
{{--                    { responsivePriority: 1, targets: 0 },                     // Booking Code — always visible--}}
{{--                    { responsivePriority: 2, targets: 4 },                     // Status--}}
{{--                    { responsivePriority: 3, targets: 5 },                     // Requested At--}}
{{--                    { responsivePriority: 4, targets: 1 },                     // PNR--}}
{{--                    { responsivePriority: 5, targets: 2 },                     // Void Charges--}}
{{--                    { responsivePriority: 6, targets: 3 },                     // Reason (hidden first)--}}
{{--                    { responsivePriority: 1, targets: 6, orderable: false },   // Action--}}
{{--                ],--}}

{{--                select: { style: 'multi', selector: 'td:not(:last-child)' },--}}

{{--                buttons: [--}}
{{--                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',  exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'csv',   text: '<i class="fa fa-file-csv"></i> CSV',   exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel', exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf"></i> PDF',   exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                    { extend: 'print', text: '<i class="fa fa-print"></i> Print', exportOptions: { columns: ':not(:last-child)' } },--}}
{{--                ],--}}

{{--                language: {--}}
{{--                    search: '', searchPlaceholder: 'Search...',--}}
{{--                    lengthMenu: 'Show _MENU_',--}}
{{--                    info: 'Showing _START_–_END_ of _TOTAL_',--}}
{{--                    infoEmpty: 'No requests found',--}}
{{--                    emptyTable: '{{ __("No void requests found.") }}',--}}
{{--                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },--}}
{{--                    select: { rows: { _: '%d selected', 0: '', 1: '1 selected' } }--}}
{{--                },--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}
{{--@endpush--}}


@extends('layouts.user')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        #voidRequestsTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #voidRequestsTable tbody td {
            padding: 10px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
            border-color: #f3f4f6;
            color: #374151;
        }
        #voidRequestsTable tbody tr:nth-child(even) td { background-color: #f9fafb; }
        #voidRequestsTable tbody tr:hover td { background-color: #eff6ff !important; }
        #voidRequestsTable tbody tr.needs-action td { background-color: #fffbeb !important; }
        #voidRequestsTable tbody tr.needs-action td:first-child { border-left: 3px solid #f59e0b; }

        #voidRequestsTable_filter input {
            border: 1px solid #d1d5db; border-radius: 6px;
            padding: 5px 10px; font-size: 0.82rem; outline: none;
        }
        #voidRequestsTable_filter input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.15); }
        #voidRequestsTable_length select { border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }
        #voidRequestsTable_info, #voidRequestsTable_length label, #voidRequestsTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #voidRequestsTable_paginate .paginate_button { border-radius: 5px !important; padding: 4px 9px !important; font-size: 0.78rem !important; border: 1px solid #e5e7eb !important; margin: 0 1px; color: #374151 !important; }
        #voidRequestsTable_paginate .paginate_button.current { background: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important; }
        #voidRequestsTable_paginate .paginate_button:hover:not(.current):not(.disabled) { background: #eff6ff !important; border-color: #3b82f6 !important; color: #1d4ed8 !important; }

        .void-badge { display:inline-flex;align-items:center;padding:2px 9px;border-radius:999px;font-size:0.71rem;font-weight:600;white-space:nowrap; }
        .b-warning   { background:#fef3c7;color:#92400e; }
        .b-info      { background:#dbeafe;color:#1e40af; }
        .b-success   { background:#dcfce7;color:#166534; }
        .b-danger    { background:#fee2e2;color:#991b1b; }
        .b-dark      { background:#e5e7eb;color:#374151; }
        .b-primary   { background:#ede9fe;color:#5b21b6; }
        .b-secondary { background:#f3f4f6;color:#6b7280; }

        table.dataTable>tbody>tr.child ul.dtr-details li { border-bottom:1px solid #f3f4f6;padding:6px 4px;font-size:0.82rem;display:flex;gap:8px; }
        table.dataTable>tbody>tr.child ul.dtr-details li:last-child { border-bottom:none; }
        table.dtr-inline.collapsed>tbody>tr>td.dtr-control::before { background-color:#1e3a5f !important;border-color:#1e3a5f !important; }

        @media(max-width:600px) {
            #voidRequestsTable thead th, #voidRequestsTable tbody td { padding:7px 8px;font-size:0.76rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $statusMap = [
            'pending'               => ['cls'=>'b-warning',  'label'=>'Pending'],
            'processing'            => ['cls'=>'b-info',     'label'=>'Processing'],
            'completed'             => ['cls'=>'b-success',  'label'=>'Completed'],
            'failed'                => ['cls'=>'b-danger',   'label'=>'Failed'],
            'voided'                => ['cls'=>'b-dark',     'label'=>'Voided'],
            'rejected'              => ['cls'=>'b-danger',   'label'=>'Rejected'],
            'approved'              => ['cls'=>'b-success',  'label'=>'Approved'],
            'waiting_user_approval' => ['cls'=>'b-primary',  'label'=>'Awaiting Your Approval'],
            'user_approved'         => ['cls'=>'b-success',  'label'=>'You Approved'],
            'user_rejected'         => ['cls'=>'b-danger',   'label'=>'You Rejected'],
        ];
    @endphp

    <div class="px-3 py-4 sm:px-5 md:px-6">

        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Void Requests') }}</h2>

        @if(session('success'))
            <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px">
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="display:flex;align-items:center;gap:8px;background:#fff1f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px">
                <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 sm:p-5">
                <table id="voidRequestsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>{{ __('Booking Code') }}</th>
                        <th>{{ __('PNR') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Void Charges') }}</th>
                        <th>{{ __('Requested At') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $req)
                        @php
                            $st = $statusMap[$req->status] ?? ['cls'=>'b-secondary','label'=>ucfirst(str_replace('_',' ',$req->status))];
                            $needsAction = $req->status === 'waiting_user_approval';
                        @endphp
                        <tr class="{{ $needsAction ? 'needs-action' : '' }}">

                            {{-- Booking Code --}}
                            <td class="font-semibold">{{ $req->booking->code ?? 'N/A' }}</td>

                            {{-- PNR --}}
                            <td style="font-family:monospace;color:#475569">{{ $req->pnr ?? '—' }}</td>

                            {{-- Reason --}}
                            <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                                title="{{ $req->reason ?? '' }}">
                                {{ $req->reason ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="void-badge {{ $st['cls'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </td>

                            {{-- Void Charges --}}
                            <td data-order="{{ $req->void_charges }}">
                                @if($req->void_charges > 0)
                                    <span style="font-weight:700;color:#dc2626;">৳{{ number_format($req->void_charges, 2) }}</span>
                                @else
                                    <span style="color:#16a34a;font-weight:600;">No charge</span>
                                @endif
                            </td>

                            {{-- Requested At --}}
                            <td data-order="{{ $req->created_at->timestamp }}" style="white-space:nowrap">
                                {{ $req->created_at->format('d M Y, h:i A') }}
                            </td>

                            {{-- Action --}}
                            <td>
                                @if($needsAction)
                                    <div style="display:flex;flex-wrap:wrap;gap:5px">
                                        <a href="#"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:6px;background:#16a34a;color:#fff;font-size:0.75rem;font-weight:600;text-decoration:none"
                                           onclick="if(confirm('Approve this void request?\nVoid charges: ৳{{ number_format($req->void_charges, 2) }}')) { window.location.href='{{ route('user.void.approve', $req->id) }}'; } return false;">
                                            <i class="fa fa-check"></i> Approve
                                        </a>
                                        <a href="#"
                                           style="display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:6px;background:#dc2626;color:#fff;font-size:0.75rem;font-weight:600;text-decoration:none"
                                           onclick="if(confirm('Reject this void request?')) { window.location.href='{{ route('user.void.reject', $req->id) }}'; } return false;">
                                            <i class="fa fa-times"></i> Reject
                                        </a>
                                    </div>
                                @else
                                    <span style="color:#9ca3af;font-size:0.8rem">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af">
                                <i class="fa fa-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                                {{ __('No void requests found.') }}
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
    <script>
        $(document).ready(function () {
            $('#voidRequestsTable').DataTable({
                dom:
                    "<'flex flex-wrap items-center justify-between gap-y-2 mb-3'<'ml-auto'f>>" +
                    "tr" +
                    "<'flex flex-wrap items-center justify-between gap-y-2 mt-3'i<'ml-auto'p>>",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[4, 'desc']],
                responsive: {
                    details: {
                        type: 'inline',
                        renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes()
                    }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },                    // Booking Code
                    { responsivePriority: 1, targets: 6, orderable: false },  // Action — always visible
                    { responsivePriority: 2, targets: 3 },                    // Status
                    { responsivePriority: 3, targets: 4 },                    // Void Charges
                    { responsivePriority: 4, targets: 5 },                    // Date
                    { responsivePriority: 5, targets: 1 },                    // PNR
                    { responsivePriority: 6, targets: 2 },                    // Reason
                ],
                language: {
                    search: '', searchPlaceholder: 'Search...',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    infoEmpty: 'No requests',
                    emptyTable: '{{ __("No void requests found.") }}',
                    paginate: { first:'«', previous:'‹', next:'›', last:'»' },
                },
            });
        });
    </script>
@endpush
