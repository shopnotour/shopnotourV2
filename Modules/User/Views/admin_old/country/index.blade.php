@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #countryTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #countryTable tbody td { padding: 9px 12px; font-size: 0.83rem; vertical-align: middle; }
        #countryTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #countryTable_filter input {
            border: 1px solid #d1d5db; border-radius: 6px;
            padding: 5px 10px; font-size: 0.82rem; outline: none;
        }
        #countryTable_filter input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        #countryTable_length select { border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }
        #countryTable_info, #countryTable_length label, #countryTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #countryTable_paginate .paginate_button {
            border-radius: 5px !important; padding: 4px 9px !important;
            font-size: 0.78rem !important; border: 1px solid #e5e7eb !important;
            margin: 0 1px; color: #374151 !important;
        }
        #countryTable_paginate .paginate_button.current { background: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important; }
        #countryTable_paginate .paginate_button:hover:not(.current):not(.disabled) { background: #eff6ff !important; border-color: #3b82f6 !important; color: #1d4ed8 !important; }

        .dt-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 2px; }
        .dt-button {
            font-size: 0.75rem !important; padding: 5px 11px !important;
            border-radius: 5px !important; border: 1px solid #d1d5db !important;
            background: #fff !important; color: #374151 !important; cursor: pointer;
        }
        .dt-button:hover { background: #f3f4f6 !important; }

        table.dtr-inline.collapsed > tbody > tr > td.dtr-control::before { background-color: #1e3a5f !important; }

        .filter-btn-group { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; align-items: center; }
        .filter-btn-group .btn-filter {
            padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
            border: 1px solid #d1d5db; background: #fff; cursor: pointer; transition: all .15s; color: #374151;
        }
        .filter-btn-group .btn-filter.active,
        .filter-btn-group .btn-filter:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }

        .flag-cell { font-size: 1.4rem; line-height: 1; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb20">
            <h1 class="title-bar">{{ __('Countries') }}</h1>
            <a href="{{ route('admin.countries.create') }}" class="btn btn-primary btn-icon">
                <i class="fa fa-plus"></i> {{ __('Add Country') }}
            </a>
        </div>

        @include('admin.message')

        <div class="panel">
            <div class="panel-title">{{ __('Country List') }}</div>
            <div class="panel-body">

                {{-- Status Filter --}}
                <div class="filter-btn-group">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Status:</span>
                    <button class="btn-filter active" data-col="9" data-val="">All</button>
                    <button class="btn-filter" data-col="9" data-val="active">Active</button>
                    <button class="btn-filter" data-col="9" data-val="inactive">Inactive</button>
                </div>

                <table id="countryTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>{{ __('Flag') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Code3') }}</th>
                        <th>{{ __('Capital') }}</th>
                        <th>{{ __('Phone Code') }}</th>
                        <th>{{ __('Passport Min') }}</th>
                        <th>{{ __('Passport Max') }}</th>
                        <th>{{ __('Passport Pattern') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $country)
                        <tr>
                            <td class="flag-cell text-center">{{ $country->flag_emoji ?? '🏳' }}</td>
                            <td><strong>{{ $country->name }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $country->code }}</span></td>
                            <td><span class="badge badge-light border">{{ $country->code3 ?? '—' }}</span></td>
                            <td>{{ $country->capital ?? '—' }}</td>
                            <td>
                                @if($country->phone_code)
                                    <code>+{{ $country->phone_code }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $country->passport_min ?? '—' }}</td>
                            <td class="text-center">{{ $country->passport_max ?? '—' }}</td>
                            <td>
                                @if($country->passport_pattern)
                                    <code style="font-size:.75rem">{{ $country->passport_pattern }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($country->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1" style="gap:4px">
                                    <a href="{{ route('admin.countries.edit', ['country' => $country->id]) }}"
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-country"
                                            data-id="{{ $country->id }}" data-name="{{ $country->name }}" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">{{ __('No countries found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- Delete form (hidden, reused for all rows) --}}
    <form id="deleteForm" method="POST" style="display:none">
        @csrf
        @method('DELETE')
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

            var table = $('#countryTable').DataTable({
                dom:
                    "<'d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'d-flex flex-wrap align-items-center justify-content-between mt-3'i<'ml-auto'p>>",
                pageLength: 25,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                order: [[1, 'asc']], // Name A→Z
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1,  targets: 1  }, // Name
                    { responsivePriority: 2,  targets: 2  }, // Code
                    { responsivePriority: 3,  targets: 9  }, // Status
                    { responsivePriority: 4,  targets: 10, orderable: false }, // Actions
                    { responsivePriority: 5,  targets: 0, orderable: false }, // Flag
                    { responsivePriority: 6,  targets: 5  }, // Phone
                    { responsivePriority: 7,  targets: 4  }, // Capital
                    { responsivePriority: 8,  targets: 3  }, // Code3
                    { responsivePriority: 9,  targets: 6  }, // PP Min
                    { responsivePriority: 10, targets: 7  }, // PP Max
                    { responsivePriority: 11, targets: 8  }, // PP Pattern
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',         exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',           exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',     exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search country, code, capital...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ countries',
                    infoEmpty: 'No countries found',
                    emptyTable: 'No countries found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // Status filter
            $(document).on('click', '.btn-filter', function () {
                var col = $(this).data('col'), val = $(this).data('val');
                $('.btn-filter[data-col="' + col + '"]').removeClass('active');
                $(this).addClass('active');
                table.column(col).search(val, false, false).draw();
            });

            // Delete
            $(document).on('click', '.btn-delete-country', function () {
                var id   = $(this).data('id');
                var name = $(this).data('name');
                if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;

                var form = $('#deleteForm');
                form.attr('action', '{{ route("admin.countries.index") }}/' + id);
                form.submit();
            });
        });
    </script>
@endpush
