@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="title-bar m-0">{{__("Flight Commission Management")}}</h1>
            @if(auth()->user()->hasPermission('commission_create'))
                <a href="{{route('flight.admin.discount.create')}}" class="btn btn-primary btn-lg">
                    <i class="fa fa-plus"></i> {{__("Add New Discount")}}
                </a>
            @endif
        </div>

        @include('admin.message')

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-title">{{__("All Discounts")}}</div>
                    <div class="panel-body">
                        <!-- Bulk Action Bar -->
                        <div id="bulkActionBar" style="display: none;" class="alert alert-info mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <strong>
                                        <i class="fa fa-check-circle"></i> {{__("Selected")}} <span id="selectedCount">0</span> {{__("item(s)")}}
                                    </strong>
                                </div>
                                <div class="col-md-8 text-right">
                                    <div class="btn-group" role="group">
                                        @if(auth()->user()->hasPermission('commission_bulk_status'))
                                            <button type="button" class="btn btn-sm btn-warning bulk-action-btn" data-action="status">
                                                <i class="fa fa-toggle-on"></i> {{__("Status")}}
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('commission_bulk_source'))
                                            <button type="button" class="btn btn-sm btn-info bulk-action-btn" data-action="change-source">
                                                <i class="fa fa-exchange"></i> {{__("Source")}}
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('commission_bulk_dates'))
                                            <button type="button" class="btn btn-sm btn-primary bulk-action-btn" data-action="update-valid-dates">
                                                <i class="fa fa-calendar"></i> {{__("Dates")}}
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('commission_bulk_copy'))
                                            <button type="button" class="btn btn-sm btn-success bulk-action-btn" data-action="copy">
                                                <i class="fa fa-copy"></i> {{__("Copy")}}
                                            </button>
                                        @endif

                                        @if(auth()->user()->hasPermission('commission_bulk_delete'))
                                            <button type="button" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete">
                                                <i class="fa fa-trash"></i> {{__("Delete")}}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="discountsTable" class="table table-striped table-hover table-sm">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="min-width: 35px; width: 4%;">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                    </th>
                                    <th style="min-width: 120px; width: 12%;">{{__("Name")}}</th>
                                    <th style="min-width: 60px; width: 5%;">{{__("Airline")}}</th>
                                    <th style="min-width: 70px; width: 6%;">{{__("Source")}}</th>
                                    <th style="min-width: 60px; width: 5%;">{{__("From")}}</th>
                                    <th style="min-width: 60px; width: 5%;">{{__("To")}}</th>
                                    <th style="min-width: 70px; width: 6%;">{{__("Type")}}</th>
                                    <th class="text-right" style="min-width: 60px; width: 6%;" title="Commission for you">{{__("Value")}}</th>
                                    <th class="text-right" style="min-width: 60px; width: 6%;" title="Regular user discount">{{__("User")}}</th>
                                    <th class="text-right" style="min-width: 60px; width: 6%;" title="B2B user discount">{{__("B2B")}}</th>
                                    <th class="text-center" style="min-width: 55px; width: 5%;">{{__("AIT %")}}</th>
                                    <th class="text-center" style="min-width: 60px; width: 5%;">{{__("Service")}}</th>
                                    <th class="text-center" style="min-width: 60px; width: 5%;">{{__("Seg Disc")}}</th>
                                    <th class="text-center" style="min-width: 65px; width: 5%;">{{__("User Seg")}}</th>
                                    <th class="text-center" style="min-width: 100px; width: 8%;">{{__("Valid")}}</th>
                                    <th class="text-center" style="min-width: 60px; width: 5%;">{{__("Status")}}</th>
                                    <th style="min-width: 100px; width: 8%;">{{__("Actions")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{__("Delete Discount")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{__("Are you sure you want to delete this discount?")}}</p>
                    <p class="text-muted"><small>{{__("This action cannot be undone.")}}</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__("Cancel")}}</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{__("Delete")}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div class="modal fade" id="bulkActionModal" tabindex="-1" role="dialog" aria-labelledby="bulkActionModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionModalLabel">{{__("Bulk Action")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="bulkActionForm" method="POST">
                    @csrf
                    <div class="modal-body" id="bulkActionFormContent">
                        <!-- Form content will be loaded here dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__("Cancel")}}</button>
                        <button type="submit" class="btn btn-primary">{{__("Apply")}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .title-bar {
            font-size: 28px;
            font-weight: 600;
            color: #333;
        }

        .dataTables_wrapper {
            margin-top: 0;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-sm {
            table-layout: auto;
            width: 100%;
            min-width: 1200px;
        }

        .table-sm thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            vertical-align: middle;
            padding: 10px 8px;
            font-size: 12px;
        }

        .table-sm tbody td {
            vertical-align: middle;
            padding: 8px 8px;
            font-size: 13px;
        }

        .table-sm tbody tr:hover {
            background-color: #f1f3f5;
        }

        .btn-group {
            display: flex;
            gap: 2px;
            flex-wrap: wrap;
        }

        .btn-group .btn {
            margin: 0;
            padding: 4px 6px;
            font-size: 11px;
            white-space: nowrap;
        }

        .badge {
            padding: 4px 8px;
            font-size: 11px;
        }

        #bulkActionBar {
            padding: 12px 15px;
            border-left: 4px solid #17a2b8;
            border-radius: 4px;
            background-color: #e7f3ff;
        }

        #bulkActionBar strong {
            color: #17a2b8;
            font-weight: 600;
        }

        /* DataTable Styling */
        .dataTables_length {
            margin-bottom: 15px;
        }

        .dataTables_filter {
            margin-bottom: 15px;
            text-align: right;
        }

        .dataTables_filter input {
            width: 250px;
            margin-left: 10px;
        }

        .dataTables_info {
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }

        .dataTables_paginate {
            padding-top: 10px;
        }

        .paginate_button {
            padding: 4px 8px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            background-color: #fff;
            font-size: 12px;
        }

        .paginate_button.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .paginate_button:hover {
            background-color: #e9ecef;
        }

        .panel {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 4px;
            border: 1px solid #e3e6f0;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 600;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 4px 4px 0 0;
        }

        .panel-body {
            padding: 20px;
        }

        .form-check-input {
            cursor: pointer;
            width: 16px;
            height: 16px;
            margin-top: 3px;
        }

        .btn-lg {
            padding: 10px 20px;
            font-size: 14px;
        }

        /* Status Badge Colors */
        .badge-success {
            background-color: #28a745;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 4px;
            border: 1px solid #e3e6f0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
        }

        .modal-title {
            font-weight: 600;
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e3e6f0;
            padding: 15px 20px;
        }

        .form-control {
            border: 1px solid #e3e6f0;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 13px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }

        .alert {
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .title-bar {
                font-size: 20px;
                margin-bottom: 15px;
            }

            .d-flex {
                flex-direction: column;
            }

            .dataTables_filter input {
                width: 100%;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                width: 100%;
            }

            #bulkActionBar .row {
                flex-direction: column;
            }

            #bulkActionBar .col-md-4,
            #bulkActionBar .col-md-8 {
                width: 100%;
                margin-bottom: 10px;
                text-align: left;
            }

            #bulkActionBar .text-right {
                text-align: left !important;
            }
        }

        /* Text alignment for numbers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        // Delete function for individual delete button
        function deleteDiscount(id) {
            if (confirm("{{ __('Are you sure you want to delete this discount?') }}")) {
                $('#deleteForm').attr('action', '{{ route("flight.admin.discount.destroy", "") }}/' + id);
                $('#deleteForm').submit();
            }
        }

        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#discountsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight.admin.discount.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'airline_code', name: 'airline_code', className: 'text-center' },
                    { data: 'gds_type', name: 'gds_type', className: 'text-center' },
                    { data: 'departure_code', name: 'departure_code', className: 'text-center' },
                    { data: 'arrival_code', name: 'arrival_code', className: 'text-center' },
                    { data: 'type', name: 'type', className: 'text-center' },
                    { data: 'value', name: 'value', orderable: false, className: 'text-right' },
                    { data: 'user_value', name: 'user_value', orderable: false, className: 'text-right' },
                    { data: 'b2b_user_value', name: 'b2b_user_value', orderable: false, className: 'text-right' },
                    { data: 'ait_charge', name: 'ait_charge', orderable: false, className: 'text-center' },
                    { data: 'service_charge', name: 'service_charge', orderable: false, className: 'text-center' },
                    { data: 'segment_discount', name: 'segment_discount', orderable: false, className: 'text-center' },
                    { data: 'user_seg_discount', name: 'user_seg_discount', orderable: false, className: 'text-center' },
                    { data: 'valid_period', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'status', name: 'status', orderable: false, className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                pageLength: 20,
                lengthMenu: [[20, 50, 100], [20, 50, 100]],
                order: [[1, 'asc']],
                language: {
                    search: "{{ __('Search:') }}",
                    lengthMenu: "{{ __('Show _MENU_ entries') }}",
                    info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                    infoEmpty: "{{ __('No entries to show') }}",
                    zeroRecords: "{{ __('No matching records found') }}",
                    paginate: {
                        first: "{{ __('First') }}",
                        last: "{{ __('Last') }}",
                        next: "{{ __('Next') }}",
                        previous: "{{ __('Previous') }}"
                    }
                }
            });

            // Check/Uncheck All
            $(document).on('change', '#checkAll', function() {
                $('.check-item').prop('checked', this.checked);
                updateBulkActionBar();
            });

            // Individual checkbox change
            $(document).on('change', '.check-item', function() {
                updateBulkActionBar();
            });

            // Update bulk action bar visibility and count
            function updateBulkActionBar() {
                const selected = $('.check-item:checked').length;
                $('#selectedCount').text(selected);

                if (selected > 0) {
                    $('#bulkActionBar').slideDown(200);
                } else {
                    $('#bulkActionBar').slideUp(200);
                }
            }

            // Get selected IDs
            function getSelectedIds() {
                const ids = [];
                $('.check-item:checked').each(function() {
                    ids.push($(this).val());
                });
                return ids;
            }

            // Bulk action button handler
            $('.bulk-action-btn').click(function() {
                const action = $(this).data('action');
                const ids = getSelectedIds();

                if (ids.length === 0) {
                    alert("{{ __('Please select at least one item') }}");
                    return;
                }

                loadBulkActionForm(action, ids);
            });

            // Load bulk action form dynamically
            function loadBulkActionForm(action, ids) {
                $.ajax({
                    url: "{{ route('flight.admin.discount.show-bulk-form') }}",
                    type: 'GET',
                    data: { action: action, ids: ids.join(',') },
                    success: function(html) {
                        $('#bulkActionFormContent').html(html);
                        $('#bulkActionForm').attr('action', "{{ route('flight.admin.discount.bulk-action') }}");

                        // Clear previous hidden inputs
                        $('#bulkActionForm').find('input[name="action"]').remove();
                        $('#bulkActionForm').find('input[name="ids[]"]').remove();

                        // Add hidden inputs
                        $('#bulkActionForm').append('<input type="hidden" name="action" value="' + action + '">');
                        ids.forEach(id => {
                            $('#bulkActionForm').append('<input type="hidden" name="ids[]" value="' + id + '">');
                        });

                        $('#bulkActionModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            alert("{{ __('You do not have permission to perform this action.') }}");
                        } else {
                            alert("{{ __('Error loading form') }}");
                        }
                    }
                });
            }

            // Form submission
            $('#bulkActionForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#bulkActionModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        alert("{{ __('Error performing action') }}");
                    }
                });
            });

            // Re-initialize checkboxes after redraw
            table.on('draw', function() {
                $('#checkAll').prop('checked', false);
                updateBulkActionBar();
            });
        });
    </script>
@endpush
