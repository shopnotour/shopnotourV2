@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #reissueTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #reissueTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
        }
        #reissueTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #reissueTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
        }
        #reissueTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #reissueTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #reissueTable_info,
        #reissueTable_length label,
        #reissueTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #reissueTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #reissueTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #reissueTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
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
            transition: background .12s;
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

        .filter-btn-group { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; align-items: center; }
        .filter-btn-group .btn-filter {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #d1d5db;
            background: #fff;
            cursor: pointer;
            transition: all .15s;
        }
        .filter-btn-group .btn-filter.active,
        .filter-btn-group .btn-filter:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .filter-btn-group .btn-filter.type-btn.active,
        .filter-btn-group .btn-filter.type-btn:hover { background: #7c3aed; border-color: #7c3aed; color: #fff; }

        .badge-waiting_user_approval { background-color: #17a2b8; color: white; }
        .badge-user_approved { background-color: #007bff; color: white; }
        .badge-user_rejected { background-color: #dc3545; color: white; }
        .badge-completed { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: black; }
        .badge-rejected { background-color: #dc3545; color: white; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Reissue Requests') }}</h1>
        </div>

        @include('admin.message')

        {{-- Bulk Action --}}
        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="post" action="{{ route('admin.reissues.bulkAction') }}" class="filter-form filter-form-left d-flex" id="bulkActionForm">
                    @csrf
                    <select name="action" class="form-control">
                        <option value="">{{ __('-- Bulk Actions --') }}</option>
                        <option value="delete">{{ __('Delete') }}</option>
                    </select>
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Apply') }}</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">{{ __('Reissue Requests') }}</div>
            <div class="panel-body">

                {{-- Status Filter --}}
                <div class="filter-btn-group mb-1">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Status:</span>
                    <button class="btn-filter active" data-col="9" data-val="">All</button>
                    <button class="btn-filter" data-col="9" data-val="pending">Pending</button>
                    <button class="btn-filter" data-col="9" data-val="waiting user approval">Waiting User Approval</button>
                    <button class="btn-filter" data-col="9" data-val="user approved">User Approved</button>
                    <button class="btn-filter" data-col="9" data-val="user rejected">User Rejected</button>
                    <button class="btn-filter" data-col="9" data-val="completed">Completed</button>
                    <button class="btn-filter" data-col="9" data-val="rejected">Rejected</button>
                </div>

                {{-- Type Filter --}}
                <div class="filter-btn-group mb-3">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Type:</span>
                    <button class="btn-filter type-btn active" data-col="7" data-val="">All</button>
                    <button class="btn-filter type-btn" data-col="7" data-val="date">Date Change</button>
                    <button class="btn-filter type-btn" data-col="7" data-val="route">Route Change</button>
                    <button class="btn-filter type-btn" data-col="7" data-val="passenger">Passenger</button>
                    <button class="btn-filter type-btn" data-col="7" data-val="upgrade">Upgrade</button>
                </div>

                <table id="reissueTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" class="check-all"></th>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Old Booking') }}</th>
                        <th>{{ __('New Booking') }}</th>
                        <th>{{ __('Old PNR') }}</th>
                        <th>{{ __('New PNR') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $reissue)
                        @php
                            $statusColors = [
                                'pending'               => 'warning',
                                'waiting_user_approval' => 'info',
                                'user_approved'         => 'primary',
                                'user_rejected'         => 'danger',
                                'completed'             => 'success',
                                'rejected'              => 'danger',
                            ];
                            $statusColor = $statusColors[$reissue->status] ?? 'secondary';
                            $statusLabel = ucfirst(str_replace('_', ' ', $reissue->status));
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $reissue->id }}" form="bulkActionForm"></td>
                            <td>{{ $reissue->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $reissue->booking_id) }}" target="_blank">
                                    #{{ $reissue->booking_id }}
                                </a>
                            </td>
                            <td>
                                @if($reissue->new_booking_id)
                                    <a href="{{ route('admin.bookings.show', $reissue->new_booking_id) }}" target="_blank">
                                        #{{ $reissue->new_booking_id }}
                                    </a>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td><strong>{{ $reissue->old_pnr }}</strong></td>
                            <td>
                                @if($reissue->new_pnr)
                                    <strong class="text-success">{{ $reissue->new_pnr }}</strong>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($reissue->booking && $reissue->booking->user)
                                    {{ $reissue->booking->user->name ?? $reissue->booking->first_name }}<br>
                                    <small class="text-muted">{{ $reissue->booking->user->email ?? $reissue->booking->email }}</small>
                                @else
                                    {{ $reissue->booking->first_name ?? 'N/A' }} {{ $reissue->booking->last_name ?? '' }}<br>
                                    <small class="text-muted">{{ $reissue->booking->email ?? '' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($reissue->reissue_type) }}</span>
                            </td>
                            <td>
                                @if($reissue->total_amount)
                                    <strong class="text-primary">{{ format_money_main($reissue->total_amount) }}</strong>
                                    @if($reissue->fare_difference)
                                        <br><small class="text-muted">Diff: {{ format_money_main($reissue->fare_difference) }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td data-order="{{ $reissue->status }}">
                                <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-order="{{ $reissue->created_at->timestamp }}" style="white-space:nowrap">
                                {{ $reissue->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $reissue->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        {{ __('Actions') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="javascript:void(0)"
                                           data-toggle="modal" data-target="#modal-reissue-{{ $reissue->id }}">
                                            <i class="fa fa-eye"></i> {{ __('View Details') }}
                                        </a>
                                        @if($reissue->status == 'pending' || $reissue->status == 'selected')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-set-amount-{{ $reissue->id }}">
                                                <i class="fa fa-money text-primary"></i> {{ __('Set Amount') }}
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-reissue-{{ $reissue->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Reject') }}
                                            </a>
                                        @endif
                                        @if($reissue->status == 'user_approved')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-approve-reissue-{{ $reissue->id }}">
                                                <i class="fa fa-check text-success"></i> {{ __('Final Approve') }}
                                            </a>
                                        @endif
                                        @if($reissue->status == 'waiting_user_approval')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-reissue-{{ $reissue->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Cancel & Reject') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">{{ __('No reissue requests found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- ══════════════════ MODALS (unchanged) ══════════════════ --}}
    @foreach($rows as $reissue)
        @php
            $statusColors = [
                'pending'               => 'warning',
                'waiting_user_approval' => 'info',
                'user_approved'         => 'primary',
                'user_rejected'         => 'danger',
                'completed'             => 'success',
                'rejected'              => 'danger',
            ];
            $statusColor   = $statusColors[$reissue->status] ?? 'secondary';
            $statusLabel   = ucfirst(str_replace('_', ' ', $reissue->status));
            $customer      = $reissue->booking->user ?? null;
            $walletBalance = $customer ? ($customer->credit_balance ?? 0) : 0;
            $totalAmount   = $reissue->total_amount ?? 0;
        @endphp

        {{-- Set Amount Modal --}}
        <div class="modal fade" id="modal-set-amount-{{ $reissue->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-calculator"></i> {{ __('Set Reissue Amount #') }}{{ $reissue->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><strong><i class="fa fa-plane"></i> Old Booking Details</strong></h6>
                                    <strong>Booking:</strong> #{{ $reissue->booking_id }}<br>
                                    <strong>PNR:</strong> {{ $reissue->old_pnr }}<br>
                                    <strong>Total:</strong> {{ format_money_main($reissue->booking->total ?? 0) }}<br>
                                    <strong>Paid:</strong> <span class="text-success font-weight-bold">{{ format_money_main($reissue->booking->paid ?? 0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><strong><i class="fa fa-calendar"></i> New Booking Details</strong></h6>
                                    @php $newBooking = \Modules\Booking\Models\Booking::find($reissue->new_booking_id); @endphp
                                    <strong>Booking:</strong> #{{ $reissue->new_booking_id ?: 'N/A' }}<br>
                                    <strong>Current Total:</strong> {{ format_money_main($newBooking->total ?? 0) }}<br>
                                    <strong>Current Paid:</strong> {{ format_money_main($newBooking->paid ?? 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note:</strong> After setting the amount, new booking total will be updated and user will be notified for approval.
                        </div>
                        <h6><strong><i class="fa fa-users"></i> Passenger-wise Fare Details:</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                <tr>
                                    <th width="20%">Passenger Name</th>
                                    <th width="8%">Type</th>
                                    <th width="14%">Old Fare (BDT)</th>
                                    <th width="14%">New Fare (BDT)</th>
                                    <th width="12%">Difference</th>
                                    <th width="12%">Reissue Charge</th>
                                    <th width="12%">Service Charge</th>
                                    <th width="12%">Passenger Total</th>
                                </tr>
                                </thead>
                                <tbody id="passenger-details-set-{{ $reissue->id }}">
                                @php
                                    $passengerIds = $reissue->passenger_ids ?? [];
                                    if (!is_array($passengerIds)) { $passengerIds = json_decode($passengerIds, true) ?? []; }
                                    $passengers = \Modules\Booking\Models\BookingPassenger::whereIn('id', $passengerIds)->get();
                                @endphp
                                @forelse($passengers as $passenger)
                                    <tr>
                                        <td>
                                          <span style="font-size:0.78rem;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;"
                                                title="{{ $passenger->first_name }} {{ $passenger->last_name }}">
        {{ $passenger->first_name }} {{ $passenger->last_name }}
    </span>
                                            <input type="hidden" class="passenger-id-set" value="{{ $passenger->id }}">
                                           </td>
                                        <td>
                                            @php $typeColor = $passenger->traveler_type == 'ADULT' ? 'primary' : ($passenger->traveler_type == 'CHILD' ? 'info' : 'warning'); @endphp
                                            <span class="badge badge-{{ $typeColor }}">{{ $passenger->traveler_type }}</span>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm old-fare-set"
                                                   id="old_fare_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   data-reissue-id="{{ $reissue->id }}" data-passenger-id="{{ $passenger->id }}"
                                                   value="{{ $passenger->total ?? 0 }}" step="0.01" readonly style="background-color:#f8f9fa;">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm new-fare-set"
                                                   id="new_fare_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   data-reissue-id="{{ $reissue->id }}" data-passenger-id="{{ $passenger->id }}"
                                                   value="0" step="0.01" min="0" placeholder="Enter new fare" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm fare-diff-set text-center"
                                                   id="fare_diff_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   readonly style="background-color:#f8f9fa;">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm reissue-charge-set"
                                                   id="reissue_charge_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   data-reissue-id="{{ $reissue->id }}" data-passenger-id="{{ $passenger->id }}"
                                                   value="500" step="0.01" min="0" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm service-charge-set"
                                                   id="service_charge_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   data-reissue-id="{{ $reissue->id }}" data-passenger-id="{{ $passenger->id }}"
                                                   value="0" step="0.01" min="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm passenger-total-set text-center"
                                                   id="passenger_total_set_{{ $reissue->id }}_{{ $passenger->id }}"
                                                   readonly style="background-color:#e8f5e9;font-weight:700;font-size:0.9rem;color:#155724;">
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No passengers found</td></tr>
                                @endforelse
                                </tbody>
                                <tfoot class="thead-dark">
                                <tr>
                                    <td colspan="2"><strong><i class="fa fa-calculator"></i> Total:</strong></td>
                                    <td><strong id="total_old_fare_set_{{ $reissue->id }}">BDT 0.00</strong></td>
                                    <td><strong id="total_new_fare_set_{{ $reissue->id }}">BDT 0.00</strong></td>
                                    <td><strong id="total_fare_diff_set_{{ $reissue->id }}">BDT 0.00</strong></td>

                                    <td><strong id="total_reissue_charges_set_{{ $reissue->id }}">BDT 0.00</strong></td>
                                    <td><strong id="total_service_charges_set_{{ $reissue->id }}">BDT 0.00</strong></td>
                                    <td><strong class="text-success" id="grand_total_set_{{ $reissue->id }}">BDT 0.00</strong></td>
                                   </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-ticket"></i> {{ __('New PNR (Optional)') }}</label>
                                    <input type="text" id="new_pnr_set_{{ $reissue->id }}" class="form-control"
                                           placeholder="Enter new PNR if available" value="{{ $reissue->new_pnr }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-comment"></i> {{ __('Airline Response (Optional)') }}</label>
                                    <input type="text" id="airline_response_set_{{ $reissue->id }}" class="form-control" placeholder="Any notes...">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <h6><strong><i class="fa fa-calculator"></i> Payment Summary:</strong></h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td>Old Booking Paid:</td><td class="text-right"><strong>{{ format_money_main($reissue->booking->paid ?? 0) }}</strong></td></tr>
                                        <tr><td>New Booking Total:</td><td class="text-right"><strong id="summary_new_total_{{ $reissue->id }}">BDT 0.00</strong></td></tr>
                                        <tr><td>Reissue Charges:</td><td class="text-right"><strong id="summary_charges_{{ $reissue->id }}">BDT 0.00</strong></td></tr>
                                        <tr><td>Service Charges:</td><td class="text-right"><strong id="summary_service_{{ $reissue->id }}">BDT 0.00</strong></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td>Total Needed:</td><td class="text-right"><strong id="summary_total_needed_{{ $reissue->id }}">BDT 0.00</strong></td></tr>
                                        <tr class="border-top">
                                            <td><strong>Customer Will:</strong></td>
                                            <td class="text-right"><strong id="summary_action_{{ $reissue->id }}" class="text-primary">-</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> {{ __('Cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-lg btn-set-amount-reissue"
                                data-id="{{ $reissue->id }}" data-old-paid="{{ $reissue->booking->paid ?? 0 }}">
                            <i class="fa fa-paper-plane"></i> {{ __('Send to User for Approval') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Final Approve Modal --}}
        {{-- Final Approve Modal --}}
        {{-- Final Approve Modal --}}
        <div class="modal fade" id="modal-approve-reissue-{{ $reissue->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-check-circle"></i> {{ __('Final Approve Reissue #') }}{{ $reissue->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <strong>User has approved this reissue!</strong>
                        </div>

                        {{-- Summary --}}
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Old Booking:</strong>
                                    #{{ $reissue->booking_id }} (PNR: {{ $reissue->old_pnr }})<br>
                                    <strong>New PNR:</strong>
                                    {{ $reissue->new_pnr ?: 'None (Same PNR)' }}<br>
                                    <strong>Fare Difference:</strong>
                                    {{ format_money_main($reissue->fare_difference ?? 0) }}<br>
                                    <strong>Reissue Charges:</strong>
                                    {{ format_money_main($reissue->reissue_charges ?? 0) }}<br>
                                    <strong>Total Extra (to deduct):</strong>
                                    <span class="text-primary font-weight-bold">
                                {{ format_money_main($totalAmount) }}
                            </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Customer Wallet Balance:</strong>
                                    <span class="{{ $walletBalance >= $totalAmount ? 'text-success' : 'text-danger' }} font-weight-bold">
                                {{ format_money_main($walletBalance) }}
                            </span><br>
                                    @if($walletBalance < $totalAmount)
                                        <small class="text-danger">
                                            <i class="fa fa-warning"></i>
                                            Insufficient! Required: {{ format_money_main($totalAmount) }}
                                        </small>
                                    @else
                                        <small class="text-success">
                                            <i class="fa fa-check"></i> Sufficient balance
                                        </small>
                                    @endif
                                    <br><br>
                                    <div class="alert alert-warning mb-0 p-2">
                                        <i class="fa fa-info-circle"></i>
                                        <strong>BDT {{ number_format($totalAmount, 2) }}</strong>
                                        will be deducted from customer wallet.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Passenger Ticket Input --}}
                        @php
                            $savedData         = json_decode($reissue->passenger_fare_details, true) ?? [];
                            $paxDetails        = $savedData['passenger_details'] ?? [];
                            $paxIds            = array_map('intval', array_column($paxDetails, 'passenger_id'));
                            $approvePassengers = !empty($paxIds)
                                                ? \Modules\Booking\Models\BookingPassenger::whereIn('id', $paxIds)
                                                    ->get()->keyBy('id')
                                                : collect();

                            // Debug — ঠিক হলে সরিয়ে দিও
                            // dd($paxIds, $paxDetails, $approvePassengers);
                        @endphp

                        <h6 class="mt-3">
                            <strong><i class="fa fa-ticket"></i> Passenger Ticket Numbers:</strong>
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                <tr>
                                    <th>Passenger</th>
                                    <th>Type</th>
                                    <th>Old Fare</th>
                                    <th>New Fare</th>
                                    <th>Reissue Charge</th>
                                    <th>Service Charge</th>
                                    <th>Extra Total</th>
                                    <th width="22%">
                                        Ticket Number <span class="text-danger">*</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($paxDetails as $pax)
                                    @php
                                        $paxId = (int)($pax['passenger_id']);
                                        $p     = $approvePassengers->get($paxId);
                                        $fareDiff   = ($pax['new_fare'] - $pax['old_fare']);
                                        $extra      = $fareDiff + $pax['reissue_charge'] + ($pax['service_charge'] ?? 0);
                                        $typeColor  = !$p ? 'secondary' :
                                                      ($p->traveler_type == 'ADULT' ? 'primary' :
                                                      ($p->traveler_type == 'CHILD' ? 'info' : 'warning'));
                                    @endphp
                                    @if($p)
                                        <tr>
                                            <td>
                                                <strong>{{ $p->first_name }} {{ $p->last_name }}</strong>
                                                <input type="hidden"
                                                       class="approve-passenger-id-{{ $reissue->id }}"
                                                       value="{{ $p->id }}">
                                            </td>
                                            <td>
                                        <span class="badge badge-{{ $typeColor }}">
                                            {{ $p->traveler_type }}
                                        </span>
                                            </td>
                                            <td>{{ format_money_main($pax['old_fare']) }}</td>
                                            <td>{{ format_money_main($pax['new_fare']) }}</td>
                                            <td>{{ format_money_main($pax['reissue_charge']) }}</td>
                                            <td>{{ format_money_main($pax['service_charge'] ?? 0) }}</td>
                                            <td>
                                                <strong class="text-primary">
                                                    {{ format_money_main($extra) }}
                                                </strong>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm approve-ticket-{{ $reissue->id }}"
                                                       data-passenger-id="{{ $p->id }}"
                                                       placeholder="e.g. 157-1234567890"
                                                       required>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                                <tfoot class="thead-dark">
                                <tr>
                                    <td colspan="6"><strong>Total Extra Charge:</strong></td>
                                    <td colspan="2">
                                        <strong class="text-success">
                                            {{ format_money_main($totalAmount) }}
                                        </strong>
                                        <small class="text-muted d-block">
                                            Will be deducted from wallet
                                        </small>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> {{ __('Cancel') }}
                        </button>
                        <button type="button"
                                class="btn btn-success btn-lg btn-final-approve-reissue"
                                data-id="{{ $reissue->id }}"
                            {{ $walletBalance < $totalAmount ? 'disabled' : '' }}>
                            <i class="fa fa-check"></i>
                            {{ __('Approve & Deduct BDT ') }} {{ number_format($totalAmount, 2) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="modal-reject-reissue-{{ $reissue->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-times-circle"></i> {{ __('Reject Reissue Request #') }}{{ $reissue->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This will reject the reissue request and cancel the new booking.
                        </div>
                        <div class="form-group">
                            <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_reissue_{{ $reissue->id }}" class="form-control" rows="4"
                                      placeholder="Enter detailed reason for rejection..." required></textarea>
                            <small class="form-text text-muted">Minimum 10 characters</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-arrow-left"></i> {{ __('Cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger btn-reject-reissue" data-id="{{ $reissue->id }}">
                            <i class="fa fa-times"></i> {{ __('Reject Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Modal --}}
        <div class="modal fade" id="modal-reissue-{{ $reissue->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-file-text"></i> {{ __('Reissue Request Details #') }}{{ $reissue->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        {{-- Basic Information --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <strong><i class="fa fa-info-circle"></i> Basic Information</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-sm">
                                            <tr><th width="40%">Reissue ID</th><td>{{ $reissue->id }}</td></tr>
                                            <tr><th>Old Booking</th><td><a href="{{ route('admin.bookings.show', $reissue->booking_id) }}" target="_blank">#{{ $reissue->booking_id }}</a></td></tr>
                                            <tr><th>New Booking</th><td>@if($reissue->new_booking_id)<a href="{{ route('admin.bookings.show', $reissue->new_booking_id) }}" target="_blank">#{{ $reissue->new_booking_id }}</a>@else Not set @endif</td></tr>
                                            <tr><th>Old PNR</th><td><strong>{{ $reissue->old_pnr }}</strong></td></tr>
                                            <tr><th>New PNR</th><td><strong class="text-success">{{ $reissue->new_pnr ?: 'Not set' }}</strong></td></tr>
                                            <tr><th>Status</th><td><span class="badge badge-{{ $statusColors[$reissue->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $reissue->status)) }}</span></td></tr>
                                            <tr><th>Requested At</th><td>{{ $reissue->created_at->format('d M Y, h:i A') }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <strong><i class="fa fa-money"></i> Financial Details</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-sm">
                                            <tr><th width="40%">Fare Difference</th><td>{{ $reissue->fare_difference ? format_money_main($reissue->fare_difference) : 'Not set' }}</td></tr>
                                            <tr><th>Reissue Charges</th><td>{{ $reissue->reissue_charges ? format_money_main($reissue->reissue_charges) : 'Not set' }}</td></tr>
                                            <tr><th>Service Charge</th><td>{{ $reissue->service_charge ? format_money_main($reissue->service_charge) : 'Not set' }}</td></tr>
                                            <tr><th>Total Amount</th><td><strong class="text-primary">{{ $reissue->total_amount ? format_money_main($reissue->total_amount) : 'Not calculated' }}</strong></td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Old Flight Details --}}
                        @if($reissue->old_flight_details)
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <strong><i class="fa fa-plane-departure"></i> Old Flight Details</strong>
                            </div>
                            <div class="card-body">
                                @php $oldFlight = $reissue->old_flight_details; @endphp
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>From:</strong> {{ $oldFlight['from'] ?? 'N/A' }}<br>
                                        <strong>To:</strong> {{ $oldFlight['to'] ?? 'N/A' }}<br>
                                        <strong>Airline:</strong> {{ $oldFlight['airline'] ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Trip Type:</strong> {{ ucfirst(str_replace('_', ' ', $oldFlight['trip_type'] ?? 'N/A')) }}<br>
                                        <strong>Departure Date:</strong> {{ isset($oldFlight['departure_date']) ? date('d M Y', strtotime($oldFlight['departure_date'])) : 'N/A' }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Return Date:</strong> {{ isset($oldFlight['return_date']) ? date('d M Y', strtotime($oldFlight['return_date'])) : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- New Flight Details --}}
                        @if($reissue->new_flight_details)
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <strong><i class="fa fa-plane-arrival"></i> New Flight Details</strong>
                            </div>
                            <div class="card-body">
                                @php 
                                    $newFlight = $reissue->new_flight_details;
                                    $provider = $newFlight['provider'] ?? 'N/A';
                                    $source = $newFlight['source'] ?? 'N/A';
                                @endphp
                                
                                {{-- Flight Provider Info --}}
                                <div class="alert alert-info mb-3">
                                    <strong><i class="fa fa-info-circle"></i> Provider:</strong> {{ $provider }} (Source: {{ $source }})
                                </div>

                                {{-- Price Information --}}
                                @if(isset($newFlight['price']))
                                <div class="alert alert-dark mt-3">
                                    <strong><i class="fa fa-tags"></i> Price Breakdown:</strong>
                                    <div class="row mt-2">
                                        <div class="col-md-3">
                                            <small>Base Fare:</small><br>
                                            <strong>{{ $newFlight['price']['currency'] ?? 'BDT' }} {{ number_format($newFlight['price']['api_base_fare'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small>Tax:</small><br>
                                            <strong>{{ $newFlight['price']['currency'] ?? 'BDT' }} {{ number_format($newFlight['price']['api_tax'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small>Subtotal:</small><br>
                                            <strong>{{ $newFlight['price']['currency'] ?? 'BDT' }} {{ number_format($newFlight['price']['api_subtotal'] ?? 0, 2) }}</strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small>Total:</small><br>
                                            <strong class="text-success">{{ $newFlight['price']['currency'] ?? 'BDT' }} {{ number_format($newFlight['price']['total'] ?? 0, 2) }}</strong>
                                        </div>
                                    </div>
                                    @if(isset($newFlight['price']['taxes_breakdown']) && count($newFlight['price']['taxes_breakdown']) > 0)
                                        <hr>
                                        <strong>Taxes Breakdown:</strong>
                                        <div class="row mt-2">
                                            @foreach($newFlight['price']['taxes_breakdown'] as $tax)
                                            <div class="col-md-4">
                                                <small>{{ $tax['code'] }} - {{ $tax['description'] }}:</small>
                                                <strong>{{ number_format($tax['amount'], 2) }} {{ $tax['currency'] }}</strong>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @endif

                                {{-- Passenger Information --}}
                                @if(isset($newFlight['passengers']) && count($newFlight['passengers']) > 0)
                                <div class="mt-3">
                                    <strong><i class="fa fa-users"></i> Passenger Information:</strong>
                                    <table class="table table-sm table-bordered mt-2">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Type</th>
                                                <th>Count</th>
                                                <th>Total Fare</th>
                                                <th>Baggage</th>
                                                <th>Refundable</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($newFlight['passengers'] as $passenger)
                                            <tr>
                                                <td>{{ $passenger['type_label'] ?? $passenger['type'] }}</td>
                                                <td>{{ $passenger['count'] }}</td>
                                                <td>{{ number_format($passenger['total_fare'], 2) }} {{ $newFlight['price']['currency'] ?? 'BDT' }}</td>
                                                <td>
                                                    @if(isset($passenger['baggage']))
                                                        {{ $passenger['baggage']['weight'] ?? 0 }} {{ $passenger['baggage']['unit'] ?? 'kg' }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($passenger['refundable']))
                                                        <span class="badge badge-{{ $passenger['refundable'] ? 'success' : 'danger' }}">
                                                            {{ $passenger['refundable'] ? 'Yes' : 'No' }}
                                                        </span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif

                                {{-- Additional Info --}}
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <small><strong>Validating Carrier:</strong></small><br>
                                        {{ $newFlight['validating_carrier'] ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-4">
                                        <small><strong>eTicketable:</strong></small><br>
                                        <span class="badge badge-{{ ($newFlight['eTicketable'] ?? false) ? 'success' : 'warning' }}">
                                            {{ ($newFlight['eTicketable'] ?? false) ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <small><strong>Refundable:</strong></small><br>
                                        <span class="badge badge-{{ ($newFlight['refundable'] ?? false) ? 'success' : 'danger' }}">
                                            {{ ($newFlight['refundable'] ?? false) ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                </div>
                                @if(isset($newFlight['last_ticket_date']))
                                <div class="mt-2">
                                    <small><strong>Last Ticket Date:</strong></small><br>
                                    {{ date('d M Y', strtotime($newFlight['last_ticket_date'])) }} {{ $newFlight['last_ticket_time'] ?? '' }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Reason & Airline Response --}}
                        @if($reissue->reason || $reissue->airline_response)
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <strong><i class="fa fa-comment"></i> Additional Information</strong>
                            </div>
                            <div class="card-body">
                                @if($reissue->reason)
                                    <div class="alert alert-warning">
                                        <strong>Reason for Reissue:</strong><br>
                                        {{ $reissue->reason }}
                                    </div>
                                @endif
                                @if($reissue->airline_response)
                                    <div class="alert alert-info">
                                        <strong>Airline Response:</strong><br>
                                        {{ $reissue->airline_response }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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

            // ── DataTable ──────────────────────────────────────────
            var table = $('#reissueTable').DataTable({
                dom:
                    "<'d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'d-flex flex-wrap align-items-center justify-content-between mt-3'i<'ml-auto'p>>",
                pageLength: 20,
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
                order: [[10, 'desc']], // Requested
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1,  targets: 4  },                   // Old PNR
                    { responsivePriority: 2,  targets: 9  },                   // Status
                    { responsivePriority: 3,  targets: 10 },                   // Requested
                    { responsivePriority: 4,  targets: 11, orderable: false }, // Actions
                    { responsivePriority: 5,  targets: 5  },                   // New PNR
                    { responsivePriority: 6,  targets: 8  },                   // Amount
                    { responsivePriority: 7,  targets: 7  },                   // Type
                    { responsivePriority: 8,  targets: 1  },                   // ID
                    { responsivePriority: 9,  targets: 2  },                   // Old Booking
                    { responsivePriority: 10, targets: 3  },                   // New Booking
                    { responsivePriority: 11, targets: 6  },                   // Customer
                    { responsivePriority: 12, targets: 0, orderable: false },  // Checkbox
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',         exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',           exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',     exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',        exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search PNR, booking, customer...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests found',
                    emptyTable: 'No reissue requests found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // ── Status & Type Filter ───────────────────────────────
            $(document).on('click', '.btn-filter', function () {
                var col = $(this).data('col');
                var val = $(this).data('val');
                $('.btn-filter[data-col="' + col + '"]').removeClass('active');
                $(this).addClass('active');
                table.column(col).search(val, false, false).draw();
            });

            // ── Check All ─────────────────────────────────────────
            $('.check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            // ── Per Passenger Calculation ──────────────────────────
            $(document).on('input', '.new-fare-set, .reissue-charge-set, .service-charge-set', function () {
                var reissueId   = $(this).data('reissue-id');
                var passengerId = $(this).data('passenger-id');

                var oldFare       = parseFloat($('#old_fare_set_' + reissueId + '_' + passengerId).val()) || 0;
                var newFare       = parseFloat($('#new_fare_set_' + reissueId + '_' + passengerId).val()) || 0;
                var reissueCharge = parseFloat($('#reissue_charge_set_' + reissueId + '_' + passengerId).val()) || 0;
                var serviceCharge = parseFloat($('#service_charge_set_' + reissueId + '_' + passengerId).val()) || 0;

                var diff  = newFare - oldFare;
                var total = diff + reissueCharge + serviceCharge; // passenger এর extra amount

                $('#fare_diff_set_' + reissueId + '_' + passengerId)
                    .val('BDT ' + diff.toFixed(2))
                    .removeClass('text-danger text-success')
                    .addClass(diff >= 0 ? 'text-danger' : 'text-success');

                $('#passenger_total_set_' + reissueId + '_' + passengerId)
                    .val('BDT ' + total.toFixed(2));

                calculateGrandTotalsSet(reissueId);
            });

            function calculateGrandTotalsSet(reissueId) {
                var totalOld = 0, totalNew = 0, totalCharges = 0, totalService = 0;

                $('#passenger-details-set-' + reissueId + ' tr').each(function () {
                    var pid = $(this).find('.passenger-id-set').val();
                    if (pid) {
                        totalOld     += parseFloat($('#old_fare_set_'      + reissueId + '_' + pid).val()) || 0;
                        totalNew     += parseFloat($('#new_fare_set_'      + reissueId + '_' + pid).val()) || 0;
                        totalCharges += parseFloat($('#reissue_charge_set_'+ reissueId + '_' + pid).val()) || 0;
                        totalService += parseFloat($('#service_charge_set_'+ reissueId + '_' + pid).val()) || 0;
                    }
                });

                // মূল fix: old paid এর সাথে পুরো new fare compare করা হবে না
                // শুধু extra amount (fare diff + charges) customer কে দিতে হবে
                var fareDiff   = totalNew - totalOld;
                var totalExtra = fareDiff + totalCharges + totalService; // customer কে মোট extra দিতে হবে
                var grandTotal = totalExtra; // grand total = extra amount
                //
                // var oldPaid      = parseFloat($('.btn-set-amount-reissue[data-id="' + reissueId + '"]').data('old-paid')) || 0;
                // var customerPays = totalExtra - oldPaid; // positive = pay, negative = refund, zero = even

                var customerPays = totalExtra;
                // Table footer update
                $('#total_old_fare_set_'       + reissueId).text('BDT ' + totalOld.toFixed(2));
                $('#total_new_fare_set_'       + reissueId).text('BDT ' + totalNew.toFixed(2));
                $('#total_fare_diff_set_'      + reissueId).text('BDT ' + fareDiff.toFixed(2));
                $('#total_reissue_charges_set_'+ reissueId).text('BDT ' + totalCharges.toFixed(2));
                $('#total_service_charges_set_'+ reissueId).text('BDT ' + totalService.toFixed(2));
                $('#grand_total_set_'          + reissueId).text('BDT ' + grandTotal.toFixed(2));

                // Payment summary panel update
                $('#summary_new_total_'    + reissueId).text('BDT ' + totalNew.toFixed(2));
                $('#summary_charges_'      + reissueId).text('BDT ' + totalCharges.toFixed(2));
                $('#summary_service_'      + reissueId).text('BDT ' + totalService.toFixed(2));
                $('#summary_total_needed_' + reissueId).text('BDT ' + totalExtra.toFixed(2));

                // Customer action label
                var actionText, actionClass;
                if (customerPays > 0.009) {
                    actionText  = 'Pay: BDT ' + customerPays.toFixed(2);
                    actionClass = 'text-danger';
                } else if (customerPays < -0.009) {
                    actionText  = 'Get Refund: BDT ' + Math.abs(customerPays).toFixed(2);
                    actionClass = 'text-success';
                } else {
                    actionText  = 'No Payment (Exact Match)';
                    actionClass = 'text-info';
                }

                $('#summary_action_' + reissueId)
                    .text(actionText)
                    .removeClass('text-success text-danger text-info text-primary')
                    .addClass(actionClass);
            }

            $('.modal[id^="modal-set-amount-"]').on('shown.bs.modal', function () {
                $(this).find('.new-fare-set').each(function () { $(this).trigger('input'); });
            });

            // ── Set Amount ─────────────────────────────────────────
            $(document).on('click', '.btn-set-amount-reissue', function (e) {
                e.preventDefault();
                var btn = $(this), reissueId = btn.data('id');
                var oldPaid = parseFloat(btn.data('old-paid')) || 0;
                var newPnr   = $('#new_pnr_set_' + reissueId).val();
                var airline  = $('#airline_response_set_' + reissueId).val();

                var passengerDetails = [];
                var totalOld = 0, totalNew = 0, totalCharges = 0, totalService = 0;
                var hasError = false;

                $('#passenger-details-set-' + reissueId + ' tr').each(function () {
                    var pid = $(this).find('.passenger-id-set').val();
                    if (pid) {
                        var oldFare = parseFloat($('#old_fare_set_'       + reissueId + '_' + pid).val()) || 0;
                        var newFare = parseFloat($('#new_fare_set_'       + reissueId + '_' + pid).val()) || 0;
                        var charge  = parseFloat($('#reissue_charge_set_' + reissueId + '_' + pid).val()) || 0;
                        var service = parseFloat($('#service_charge_set_' + reissueId + '_' + pid).val()) || 0;

                        if (newFare <= 0) {
                            hasError = true;
                            $('#new_fare_set_' + reissueId + '_' + pid).addClass('is-invalid');
                        } else {
                            $('#new_fare_set_' + reissueId + '_' + pid).removeClass('is-invalid');
                        }

                        passengerDetails.push({
                            passenger_id   : parseInt(pid),
                            old_fare       : oldFare,
                            new_fare       : newFare,
                            reissue_charge : charge,
                            service_charge : service
                        });

                        totalOld     += oldFare;
                        totalNew     += newFare;
                        totalCharges += charge;
                        totalService += service;
                    }
                });

                if (hasError) { alert('Please enter valid new fare for all passengers'); return; }
                if (passengerDetails.length === 0) { alert('No passenger data found'); return; }

                // মূল fix: extra amount দিয়ে confirm message
                var fareDiff    = totalNew - totalOld;
                var totalExtra  = fareDiff + totalCharges + totalService;
                // var customerPays = totalExtra - oldPaid;
                var customerPays = totalExtra;
                var paymentLine;
                if (customerPays > 0.009) {
                    paymentLine = 'Customer will PAY extra: BDT ' + customerPays.toFixed(2);
                } else if (customerPays < -0.009) {
                    paymentLine = 'Customer will get REFUND: BDT ' + Math.abs(customerPays).toFixed(2);
                } else {
                    paymentLine = 'No additional payment required (exact match)';
                }

                var msg = 'Fare Difference: BDT ' + fareDiff.toFixed(2) +
                    '\nReissue Charges: BDT ' + totalCharges.toFixed(2) +
                    '\nService Charges: BDT ' + totalService.toFixed(2) +
                    '\nTotal Extra Needed: BDT ' + totalExtra.toFixed(2) +
                    '\n\n' + paymentLine;

                if (!confirm(msg)) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url    : '/admin/module/booking/reissues/' + reissueId + '/set-amount',
                    method : 'POST',
                    data   : {
                        new_pnr               : newPnr,
                        airline_response      : airline,
                        passenger_details     : passengerDetails,
                        total_old_fare        : totalOld,
                        total_new_fare        : totalNew,
                        total_reissue_charges : totalCharges,
                        total_service_charges : totalService,
                        fare_difference       : fareDiff,
                        total_extra           : totalExtra,
                        _token                : '{{ csrf_token() }}'
                    },
                    dataType : 'json',
                    success  : function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    },
                    error : function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    }
                });
            });

            // ── Final Approve ──────────────────────────────────────
            // ── Final Approve ──────────────────────────────────────
            $(document).on('click', '.btn-final-approve-reissue', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');

                // Ticket numbers collect
                var ticketNumbers = [];
                var hasTicketError = false;

                $('.approve-ticket-' + id).each(function () {
                    var tkt = $(this).val().trim();
                    var pid = $(this).data('passenger-id');
                    if (!tkt) {
                        hasTicketError = true;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                        ticketNumbers.push({ passenger_id: pid, ticket_number: tkt });
                    }
                });

                if (hasTicketError) {
                    alert('Please enter ticket number for all passengers.');
                    return;
                }

                if (!confirm('Are you sure? This will process the wallet transaction and complete the reissue.')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url    : '/admin/module/booking/reissues/' + id + '/approve',
                    method : 'POST',
                    data   : {
                        ticket_numbers : ticketNumbers,
                        _token         : '{{ csrf_token() }}'
                    },
                    dataType : 'json',
                    success  : function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Process');
                    },
                    error : function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Process');
                    }
                });
            });

            // ── Reject ─────────────────────────────────────────────
            $(document).on('click', '.btn-reject-reissue', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var reason = $('#rejection_reason_reissue_' + id).val();
                if (!reason || reason.length < 10) { alert('Please enter rejection reason (minimum 10 characters)'); return; }
                if (!confirm('Reject this reissue request? Old booking will remain active.')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/reissues/' + id + '/reject',
                    method: 'POST',
                    data: { rejection_reason: reason, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                    }
                });
            });
        });
    </script>
@endpush
