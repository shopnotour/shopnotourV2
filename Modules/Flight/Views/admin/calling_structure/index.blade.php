@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #callingTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #callingTable tbody td { padding: 9px 12px; font-size: 0.83rem; vertical-align: middle; }
        #callingTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #callingTable_filter input {
            border: 1px solid #d1d5db; border-radius: 6px;
            padding: 5px 10px; font-size: 0.82rem; outline: none;
        }
        #callingTable_filter input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        #callingTable_length select { border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }
        #callingTable_info, #callingTable_length label, #callingTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #callingTable_paginate .paginate_button {
            border-radius: 5px !important; padding: 4px 9px !important;
            font-size: 0.78rem !important; border: 1px solid #e5e7eb !important;
            margin: 0 1px; color: #374151 !important;
        }
        #callingTable_paginate .paginate_button.current { background: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important; }
        #callingTable_paginate .paginate_button:hover:not(.current):not(.disabled) { background: #eff6ff !important; border-color: #3b82f6 !important; color: #1d4ed8 !important; }

        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 2px; }
        .dt-button {
            font-size: 0.75rem !important; padding: 5px 11px !important;
            border-radius: 5px !important; border: 1px solid #d1d5db !important;
            background: #fff !important; color: #374151 !important; cursor: pointer;
        }
        .dt-button:hover { background: #f3f4f6 !important; }

        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before { background-color: #1e3a5f !important; }
        table.dataTable > tbody > tr.child ul.dtr-details li { border-bottom: 1px solid #f3f4f6; padding: 6px 4px; font-size: 0.82rem; }

        .filter-btn-group { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; align-items: center; }
        .filter-btn-group .btn-filter {
            padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
            border: 1px solid #d1d5db; background: #fff; cursor: pointer; transition: all .15s; color: #374151;
        }
        .filter-btn-group .btn-filter.active,
        .filter-btn-group .btn-filter:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .filter-btn-group .btn-filter.gds-btn.active,
        .filter-btn-group .btn-filter.gds-btn:hover { background: #7c3aed; border-color: #7c3aed; color: #fff; }

        .route-arrow { color: #3b82f6; font-weight: 700; margin: 0 4px; }
        .wildcard-badge { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; font-size: .7rem; padding: 1px 6px; border-radius: 10px; font-weight: 600; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="title-bar">{{ __('Flight Calling Structure') }}</h1>
            <a href="{{ route('flight.admin.calling.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> {{ __('Add Route') }}
            </a>
        </div>

        @include('admin.message')

        {{-- Bulk Actions --}}
        <div class="filter-div d-flex justify-content-between mb-3">
            <form action="{{ route('flight.admin.calling.bulkEdit') }}" method="post" class="bravo-form-item d-flex" style="gap:6px">
                @csrf
                <input type="hidden" name="action" class="action-input">
                <button type="button" class="btn btn-info btn-sm" onclick="bulkEdit('activate')">
                    <i class="fa fa-check"></i> {{ __('Activate') }}
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="bulkEdit('deactivate')">
                    <i class="fa fa-ban"></i> {{ __('Deactivate') }}
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkEdit('delete')">
                    <i class="fa fa-trash"></i> {{ __('Delete') }}
                </button>
            </form>
        </div>

        <div class="panel">
            <div class="panel-title">{{ __('Route Configurations') }}</div>
            <div class="panel-body">

                {{-- Status Filter --}}
                <div class="filter-btn-group mb-1">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Status:</span>
                    <button class="btn-filter active" data-col="5" data-val="">All</button>
                    <button class="btn-filter" data-col="5" data-val="active">Active</button>
                    <button class="btn-filter" data-col="5" data-val="inactive">Inactive</button>
                </div>

                {{-- GDS Filter --}}
                <div class="filter-btn-group mb-3">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">GDS:</span>
                    <button class="btn-filter gds-btn active" data-col="3" data-val="">All</button>
                    @foreach($gdsoptions as $provider => $name)
                        <button class="btn-filter gds-btn" data-col="3" data-val="{{ strtolower($name) }}">{{ $name }}</button>
                    @endforeach
                </div>

                <table id="callingTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" class="check-all"></th>
                        <th>{{ __('Route') }}</th>
                        <th>{{ __('Airlines') }}</th>
                        <th>{{ __('GDS') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $item)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="check-item" form="bulkForm"></td>
                            <td>
                                <strong style="font-size:.9rem">
                                    @if($item->departure_code)
                                        <span class="text-primary">{{ $item->departure_code }}</span>
                                    @else
                                        <span class="wildcard-badge">*</span>
                                    @endif
                                    <span class="route-arrow">→</span>
                                    @if($item->arrival_code)
                                        <span class="text-primary">{{ $item->arrival_code }}</span>
                                    @else
                                        <span class="wildcard-badge">*</span>
                                    @endif
                                </strong><br>
                                <small class="text-muted">
                                    {{ $item->departureAirport?->name ?? ($item->departure_code ? $item->departure_code : 'Any Departure') }}
                                    →
                                    {{ $item->arrivalAirport?->name ?? ($item->arrival_code ? $item->arrival_code : 'Any Arrival') }}
                                </small>
                                @if($item->notes)
                                    <br><small class="text-info"><i class="fa fa-info-circle"></i> {{ $item->notes }}</small>
                                @endif
                            </td>
                            <td>
                                @if(!empty($item->airline_codes))
                                    @php $airlineModels = $item->airlines(); @endphp
                                    @foreach($item->airline_codes as $index => $code)
                                        @php $airline = $airlineModels->firstWhere('code', $code); @endphp
                                        <span class="badge badge-secondary mb-1" style="font-size:11px">
                                            {{ $code }}@if($airline) - {{ $airline->name }}@endif
                                        </span>
                                        @if(($index + 1) % 2 == 0)<br>@endif
                                    @endforeach
                                    <br><small class="text-muted">({{ count($item->airline_codes) }} {{ __('airlines') }})</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item->gds)
                                    @php $gdsName = $gdsoptions[$item->gds] ?? ucfirst($item->gds); @endphp
                                    <span class="badge" style="background:#7c3aed;color:#fff;font-size:.75rem">
                                        {{ $gdsName }}
                                    </span>
                                    <br><small class="text-muted" style="font-size:.72rem">{{ $item->gds }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $item->priority < 5 ? 'success' : ($item->priority < 10 ? 'info' : 'secondary') }}">
                                    {{ $item->priority }}
                                </span>
                            </td>
                            <td>
                                @if($item->status == 'active')
                                    <span class="badge badge-success"><i class="fa fa-check"></i> Active</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fa fa-ban"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex" style="gap:4px">
                                    <a class="btn btn-sm btn-primary"
                                       href="{{ route('flight.admin.calling.edit', $item->id) }}" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteSingle({{ $item->id }})" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    @if(auth()->user()->hasPermission('flight_calling_status'))
                                        <a class="btn btn-sm btn-info"
                                           href="{{ route('flight.admin.calling.status', $item->id) }}" title="Toggle Status">
                                            <i class="fa fa-toggle-on"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('No route configurations found') }}</p>
                                <a href="{{ route('flight.admin.calling.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> {{ __('Add First Route') }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- Hidden bulk form --}}
    <form id="bulkForm" action="{{ route('flight.admin.calling.bulkEdit') }}" method="post" style="display:none">
        @csrf
        <input type="hidden" name="action" class="action-input">
    </form>
@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            var table = $('#callingTable').DataTable({
                dom:
                    "<'d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'d-flex flex-wrap align-items-center justify-content-between mt-3'i<'ml-auto'p>>",
                pageLength: 25,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                order: [[4, 'asc']], // Priority
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1,  targets: 1  }, // Route
                    { responsivePriority: 2,  targets: 5  }, // Status
                    { responsivePriority: 3,  targets: 3  }, // GDS
                    { responsivePriority: 4,  targets: 6, orderable: false }, // Actions
                    { responsivePriority: 5,  targets: 4  }, // Priority
                    { responsivePriority: 6,  targets: 2  }, // Airlines
                    { responsivePriority: 7,  targets: 0, orderable: false }, // Checkbox
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',         exportOptions: { columns: [1,2,3,4,5] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',           exportOptions: { columns: [1,2,3,4,5] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel', exportOptions: { columns: [1,2,3,4,5] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',     exportOptions: { columns: [1,2,3,4,5] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',        exportOptions: { columns: [1,2,3,4,5] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search route, airline, GDS...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ routes',
                    infoEmpty: 'No routes found',
                    emptyTable: 'No route configurations found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // Status & GDS filter
            $(document).on('click', '.btn-filter', function () {
                var col = $(this).data('col'), val = $(this).data('val');
                $('.btn-filter[data-col="' + col + '"]').removeClass('active');
                $(this).addClass('active');
                table.column(col).search(val, false, false).draw();
            });

            // Check all
            $('.check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });
        });

        function deleteSingle(id) {
            if (!confirm('{{ __("Are you sure you want to delete this route configuration?") }}')) return;

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("flight.admin.calling.destroy", ":id") }}'.replace(':id', id);
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }

        function bulkEdit(action) {
            if (!$('.check-item:checked').length) {
                alert('{{ __("Please select at least one item") }}');
                return;
            }
            if (action === 'delete' && !confirm('{{ __("Are you sure you want to delete selected items?") }}')) return;

            $('#bulkForm .action-input').val(action);
            // copy checked ids into bulk form
            $('#bulkForm input[name="ids[]"]').remove();
            $('.check-item:checked').each(function () {
                $('#bulkForm').append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
            });
            $('#bulkForm').submit();
        }
    </script>
@endpush
