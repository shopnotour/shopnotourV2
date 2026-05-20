@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #refundTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #refundTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
        }
        #refundTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #refundTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
        }
        #refundTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #refundTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #refundTable_info,
        #refundTable_length label,
        #refundTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #refundTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #refundTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #refundTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
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

        /* Filter buttons */
        .filter-btn-group { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
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
        .filter-btn-group .btn-filter.type-btn:hover { background: #0f766e; border-color: #0f766e; color: #fff; }

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
            <h1 class="title-bar">{{ __('Refund Requests') }}</h1>
        </div>

        @include('admin.message')

        {{-- Bulk Action --}}
        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="post" action="{{ route('admin.refunds.bulkAction') }}" class="filter-form filter-form-left d-flex" id="bulkActionForm">
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
            <div class="panel-title">{{ __('Refund Requests') }}</div>
            <div class="panel-body">

                {{-- Status Filter --}}
                <div class="filter-btn-group mb-1">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px;line-height:2">Status:</span>
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
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px;line-height:2">Type:</span>
                    <button class="btn-filter type-btn active" data-col="5" data-val="">All</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="full">Full</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="partial">Partial</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="cancellation">Cancellation</button>
                </div>

                <table id="refundTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" class="check-all"></th>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Booking') }}</th>
                        <th>{{ __('PNR') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Charges') }}</th>
                        <th>{{ __('Net Refund') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $refund)
                        @php
                            $statusColors = [
                                'pending'               => 'warning',
                                'waiting_user_approval' => 'info',
                                'user_approved'         => 'primary',
                                'user_rejected'         => 'danger',
                                'completed'             => 'success',
                                'rejected'              => 'danger',
                            ];
                            $statusColor = $statusColors[$refund->status] ?? 'secondary';
                            $statusLabel = ucfirst(str_replace('_', ' ', $refund->status));
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $refund->id }}" form="bulkActionForm"></td>
                            <td>{{ $refund->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $refund->booking_id) }}">
                                    #{{ $refund->booking_id }}
                                </a>
                            </td>
                            <td><strong>{{ $refund->pnr }}</strong></td>
                            <td>
                                @if($refund->booking && $refund->booking->user)
                                    {{ $refund->booking->user->name ?? $refund->booking->first_name }}<br>
                                    <small class="text-muted">{{ $refund->booking->user->email ?? $refund->booking->email }}</small>
                                @else
                                    {{ $refund->booking->first_name ?? 'N/A' }} {{ $refund->booking->last_name ?? '' }}<br>
                                    <small class="text-muted">{{ $refund->booking->email ?? '' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($refund->refund_type) }}</span>
                            </td>
                            <td>
                                @if($refund->refund_amount)
                                    <strong>{{ format_money_main($refund->refund_amount) }}</strong>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($refund->refund_charges)
                                    {{ format_money_main($refund->refund_charges) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($refund->net_refund_amount)
                                    <strong class="text-success">{{ format_money_main($refund->net_refund_amount) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-order="{{ $refund->status }}">
                                <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-order="{{ $refund->created_at->timestamp }}" style="white-space:nowrap">
                                {{ $refund->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $refund->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        {{ __('Actions') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="javascript:void(0)"
                                           data-toggle="modal" data-target="#modal-refund-{{ $refund->id }}">
                                            <i class="fa fa-eye"></i> {{ __('View Details') }}
                                        </a>
                                        @if($refund->status == 'pending')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-set-amount-{{ $refund->id }}">
                                                <i class="fa fa-money text-primary"></i> {{ __('Set Amount') }}
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-refund-{{ $refund->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Reject') }}
                                            </a>
                                        @endif
                                        @if($refund->status == 'user_approved')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-approve-refund-{{ $refund->id }}">
                                                <i class="fa fa-check text-success"></i> {{ __('Final Approve') }}
                                            </a>
                                        @endif
                                        @if($refund->status == 'waiting_user_approval')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-refund-{{ $refund->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Cancel & Reject') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">{{ __('No refund requests found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- ══════════════════ MODALS (unchanged) ══════════════════ --}}
    @foreach($rows as $refund)
        @php
            $statusColors = [
                'pending'               => 'warning',
                'waiting_user_approval' => 'info',
                'user_approved'         => 'primary',
                'user_rejected'         => 'danger',
                'completed'             => 'success',
                'rejected'              => 'danger',
            ];
            $customer      = $refund->booking->user ?? null;
            $walletBalance = $customer ? ($customer->credit_balance ?? 0) : 0;
        @endphp

        {{-- Set Amount Modal --}}
        <div class="modal fade" id="modal-set-amount-{{ $refund->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Set Refund Amount #') }}{{ $refund->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $refund->booking_id }}<br>
                            <strong>PNR:</strong> {{ $refund->pnr }}<br>
                            <strong>Booking Total:</strong> {{ format_money_main($refund->booking->total ?? 0) }}<br>
                            <strong>Paid Amount:</strong> {{ format_money_main($refund->booking->paid ?? 0) }}
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i> After setting the amount, user will be notified for approval.
                        </div>
                        @php
                            $segData = $refund->segment ?? [];
                            if (!is_array($segData)) $segData = json_decode($segData, true) ?? [];
                        @endphp
                        @if(!empty($segData))
                            <div class="form-group">
                                <label>{{ __('Selected Route(s)') }}</label>
                                <div style="display:flex;flex-direction:column;gap:4px;">
                                    @foreach($segData as $leg)
                                        @php
                                            $isOut = ($leg['type'] ?? '') === 'outbound';
                                            $icon = $isOut ? '✈' : '←';
                                            $color = $isOut ? '#1d4ed8' : '#7c3aed';
                                            $label = $leg['label'] ?? '';
                                            $dateRaw = $leg['date'] ?? '';
                                            $dateStr = !empty($dateRaw) ? date('d M Y', strtotime($dateRaw)) : '';
                                        @endphp
                                        <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;">
                                            <span style="font-size:12px;color:{{ $color }}">{{ $icon }}</span>
                                            <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $label }}</span>
                                            @if($dateStr)
                                                <span style="font-size:11px;color:#64748b;margin-left:auto">{{ $dateStr }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>{{ __('Passenger Amount') }}</label>
                            <div class="input-group">
                                <input type="text" id="passenger_amount_display_{{ $refund->id }}" class="form-control" readonly value="Loading...">
                                <input type="hidden" id="passenger_amount_{{ $refund->id }}" value="0">
                            </div>
                            <small class="form-text text-muted">Total price of selected passengers</small>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Refund Charges') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">BDT</span></div>
                                <input type="number" id="refund_charges_set_{{ $refund->id }}"
                                       class="form-control refund-charges-set"
                                       data-id="{{ $refund->id }}" value="500" step="0.01" min="0">
                            </div>
                            <small class="form-text text-muted">Default: 500 BDT</small>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Service Charge') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">BDT</span></div>
                                <input type="number" id="service_charge_set_{{ $refund->id }}"
                                       class="form-control service-charge-set"
                                       data-id="{{ $refund->id }}" value="0" step="0.01" min="0">
                            </div>
                            <small class="form-text text-muted">Platform / agency service charge</small>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Net Refund Amount') }}</label>
                            <input type="text" id="net_refund_set_{{ $refund->id }}" class="form-control font-weight-bold text-success" readonly>
                            <small class="form-text text-muted">Passenger Amount - Refund Charges - Service Charge</small>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Airline Response (Optional)') }}</label>
                            <textarea id="airline_response_set_{{ $refund->id }}" class="form-control" rows="3" placeholder="Any response from airline..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary btn-set-amount" data-id="{{ $refund->id }}">
                            <i class="fa fa-paper-plane"></i> {{ __('Send to User for Approval') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Final Approve Modal --}}
        <div class="modal fade" id="modal-approve-refund-{{ $refund->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Final Approve Refund #') }}{{ $refund->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> <strong>User has approved this refund!</strong>
                        </div>
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $refund->booking_id }}<br>
                            <strong>PNR:</strong> {{ $refund->pnr }}<br>
                            <strong>Refund Amount:</strong> {{ format_money_main($refund->refund_amount ?? 0) }}<br>
                            <strong>Charges:</strong> {{ format_money_main($refund->refund_charges ?? 0) }}<br>
                            <strong>Net Refund:</strong>
                            <span class="text-success font-weight-bold">{{ format_money_main($refund->net_refund_amount ?? 0) }}</span>
                        </div>
                        <div class="alert alert-info">
                            <strong>Customer Wallet Balance:</strong> {{ format_money_main($walletBalance) }}<br>
                            <strong>After Refund:</strong> {{ format_money_main($walletBalance + ($refund->net_refund_amount ?? 0)) }}
                        </div>
                        <p class="text-muted">
                            <i class="fa fa-info-circle"></i> Clicking "Approve & Add to Wallet" will add
                            <strong>{{ format_money_main($refund->net_refund_amount ?? 0) }}</strong> to customer's wallet.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success btn-final-approve" data-id="{{ $refund->id }}">
                            <i class="fa fa-check"></i> {{ __('Approve & Add to Wallet') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="modal-reject-refund-{{ $refund->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Reject Refund Request #') }}{{ $refund->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This will reject the refund request.
                        </div>
                        <div class="form-group">
                            <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_refund_{{ $refund->id }}" class="form-control" rows="4"
                                      required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger btn-reject-refund" data-id="{{ $refund->id }}">
                            <i class="fa fa-times"></i> {{ __('Reject Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Modal --}}
        <div class="modal fade" id="modal-refund-{{ $refund->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Refund Request Details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr><th width="40%">{{ __('Refund ID') }}</th><td>{{ $refund->id }}</td></tr>
                                    <tr><th>{{ __('Booking ID') }}</th><td>#{{ $refund->booking_id }}</td></tr>
                                    <tr><th>{{ __('PNR') }}</th><td><strong>{{ $refund->pnr }}</strong></td></tr>
                                    <tr><th>{{ __('Type') }}</th><td>{{ ucfirst($refund->refund_type) }}</td></tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            <span class="badge badge-{{ $statusColors[$refund->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $refund->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ __('Refund Amount') }}</th>
                                        <td>{{ $refund->refund_amount ? format_money_main($refund->refund_amount) : 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Charges') }}</th>
                                        <td>{{ $refund->refund_charges ? format_money_main($refund->refund_charges) : 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Net Refund') }}</th>
                                        <td><strong class="text-success">{{ $refund->net_refund_amount ? format_money_main($refund->net_refund_amount) : 'Not calculated' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Requested At') }}</th>
                                        <td>{{ $refund->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        
                        {{-- Segment / Leg Itinerary --}}
                        @php
                            $segData = $refund->segment ?? [];
                            if (!is_array($segData)) $segData = json_decode($segData, true) ?? [];
                        @endphp
                        @if(!empty($segData))
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6><strong>{{ __('Selected Leg(s) for Refund') }}</strong></h6>
                                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-top:8px;">
                                        @foreach($segData as $sg => $leg)
                                            @php
                                                $isOut = ($leg['type'] ?? '') === 'outbound';
                                                $accentColor = $isOut ? '#1d4ed8' : '#7c3aed';
                                                $accentLight = $isOut ? '#eff6ff' : '#f5f3ff';
                                                $destColor   = $isOut ? '#6366f1' : '#a78bfa';
                                                $lineGrad    = $isOut ? '#1d4ed8,#6366f1' : '#7c3aed,#a78bfa';
                                                $segments    = $leg['segments'] ?? [];
                                                $firstSeg    = $segments[0] ?? null;
                                                $lastSeg     = !empty($segments) ? end($segments) : null;
                                                $legLabel    = $leg['label'] ?? '';
                                                $legDateRaw  = $leg['date'] ?? '';
                                                $legDate     = !empty($legDateRaw) ? date('D, d M Y', strtotime($legDateRaw)) : '';
                                                $fltCount    = $leg['flight_count'] ?? count($segments);

                                                $legMinutes = 0;
                                                if ($firstSeg && $lastSeg) {
                                                    $dTs = strtotime($firstSeg['departure_time'] ?? $firstSeg['departure'] ?? 'now');
                                                    $aTs = strtotime($lastSeg['arrival_time'] ?? $lastSeg['arrival'] ?? 'now');
                                                    $legMinutes = (int)(($aTs - $dTs) / 60);
                                                }
                                                $legDur = floor($legMinutes/60).'h '.($legMinutes%60).'m';

                                                $rawTime = function($dt) {
                                                    preg_match('/T(\d{2}:\d{2})/', $dt, $m);
                                                    return $m[1] ?? (preg_match('/\d{2}:\d{2}/', $dt, $m2) ? $m2[0] : '00:00');
                                                };
                                                $rawDate = function($dt) {
                                                    preg_match('/^(\d{4}-\d{2}-\d{2})/', $dt, $m);
                                                    return !empty($m[1]) ? date('D d M', strtotime($m[1])) : date('D d M', strtotime($dt));
                                                };
                                            @endphp
                                            <div style="margin:0;border-bottom:{{ !$loop->last ? '1px solid #e2e8f0' : 'none' }};">
                                                {{-- Leg header --}}
                                                <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;">
                                                    <div style="width:26px;height:26px;border-radius:6px;background:{{ $accentLight }};color:{{ $accentColor }};display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0">
                                                        <i class="fas fa-{{ $isOut ? 'plane-departure' : 'plane-arrival' }}"></i>
                                                    </div>
                                                    <div style="flex:1;min-width:0">
                                                        <div style="font-size:12px;font-weight:700;color:#0f172a">
                                                            <span style="color:{{ $accentColor }}">{{ $isOut ? '→ Outbound' : '← Return' }}</span>
                                                            <span style="color:#64748b;font-weight:400;margin-left:5px">{{ $legLabel }}</span>
                                                        </div>
                                                        <div style="font-size:10px;color:#64748b;margin-top:1px">
                                                            {{ $legDate ?: $legDateRaw }}
                                                            · {{ $fltCount }} flight{{ $fltCount > 1 ? 's' : '' }}
                                                            · {{ $legDur }} total
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Desktop timeline --}}
                                                <div style="padding:14px 16px;overflow-x:auto;">
                                                    <div style="display:flex;align-items:center;gap:0;min-width:300px">
                                                        @foreach($segments as $si => $seg)
                                                            @php
                                                                $isLastSeg = $si === count($segments) - 1;
                                                                $acName = $seg['aircraft_name'] ?? $seg['equipment'] ?? '';
                                                                if (strlen($acName) <= 4 && ctype_alnum($acName)) $acName = '';

                                                                $depTime = $rawTime($seg['departure_time'] ?? $seg['departure'] ?? '');
                                                                $arrTime = $rawTime($seg['arrival_time'] ?? $seg['arrival'] ?? '');
                                                                $depDate = $rawDate($seg['departure_time'] ?? $seg['departure'] ?? '');
                                                                $arrDate = $rawDate($seg['arrival_time'] ?? $seg['arrival'] ?? '');
                                                                $origin = $seg['origin'] ?? '';
                                                                $destination = $seg['destination'] ?? '';
                                                                $flightNum = ($seg['carrier'] ?? '') . ($seg['flight_number'] ?? '');
                                                                $cabin = $seg['cabin_class'] ?? 'Economy';
                                                                $durMin = (int)($seg['travel_time'] ?? 0);
                                                                $durStr = $durMin ? floor($durMin/60).'h '.($durMin%60).'m' : '';

                                                                $statusName = $seg['status_name'] ?? $seg['status'] ?? 'Confirmed';
                                                                $isHK = in_array($statusName, ['HK','KK','Confirmed']);
                                                                $meals = $seg['meals'] ?? [];
                                                                $depTerm = $seg['departure_terminal'] ?? null;
                                                                $arrTerm = $seg['arrival_terminal'] ?? null;

                                                                $lv = null;
                                                                if (!$isLastSeg) {
                                                                    $nextSeg = $segments[$si + 1];
                                                                    $aTs = strtotime($seg['arrival_time'] ?? $seg['arrival'] ?? 'now');
                                                                    $dTs = strtotime($nextSeg['departure_time'] ?? $nextSeg['departure'] ?? 'now');
                                                                    $lvMin = (int)(($dTs - $aTs) / 60);
                                                                    $lv = [
                                                                        'airport'   => $destination,
                                                                        'arr_time'  => $arrTime,
                                                                        'arr_date'  => $arrDate,
                                                                        'dep_time'  => $rawTime($nextSeg['departure_time'] ?? $nextSeg['departure'] ?? ''),
                                                                        'dep_date'  => $rawDate($nextSeg['departure_time'] ?? $nextSeg['departure'] ?? ''),
                                                                        'duration'  => floor($lvMin/60).'h '.($lvMin%60).'m',
                                                                        'arr_term'  => $arrTerm,
                                                                        'dep_term'  => $nextSeg['departure_terminal'] ?? null,
                                                                        'overnight' => substr($seg['arrival_time'] ?? $seg['arrival'] ?? '',0,10) !== substr($nextSeg['departure_time'] ?? $nextSeg['departure'] ?? '',0,10),
                                                                    ];
                                                                }
                                                            @endphp
                                                            @if($si === 0)
                                                                <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                                    <div style="font-size:11px;font-weight:600;color:#64748b">{{ $depTime }}</div>
                                                                    <div style="font-size:22px;font-weight:800;color:{{ $accentColor }};line-height:1.1">{{ $origin }}</div>
                                                                    <div style="font-size:10px;color:#64748b">{{ $depDate }}</div>
                                                                    @if($depTerm)
                                                                        <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:#64748b">T{{ $depTerm }}</div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            <div style="flex:1;display:flex;flex-direction:column;padding:0 4px;min-width:120px">
                                                                <div style="display:flex;align-items:center;margin-top:18px">
                                                                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $si===0 ? $accentColor : '#fde68a' }};flex-shrink:0"></div>
                                                                    <div style="flex:1;height:2px;background:linear-gradient(90deg,{{ $lineGrad }});position:relative">
                                                                        <span style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;padding:0 4px;font-size:11px;color:{{ $accentColor }}">✈</span>
                                                                    </div>
                                                                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $isLastSeg ? $destColor : '#fde68a' }};flex-shrink:0"></div>
                                                                </div>
                                                                <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:5px;justify-content:center">
                                                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569;font-family:monospace">{{ $flightNum }}</span>
                                                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#dbeafe;color:#1e40af">{{ $cabin }}</span>
                                                                    @if($durStr)
                                                                        <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ $durStr }}</span>
                                                                    @endif
                                                                    @if($acName)
                                                                        <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#f1f5f9;color:#475569">{{ $acName }}</span>
                                                                    @endif
                                                                    @foreach($meals as $meal)
                                                                        <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:#fff7ed;color:#c2410c">
                                                                            <i class="fas fa-utensils" style="font-size:7px"></i>
                                                                            {{ $meal['description'] ?? $meal['code'] ?? $meal }}
                                                                        </span>
                                                                    @endforeach
                                                                    <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;background:{{ $isHK ? '#dcfce7' : '#fef3c7' }};color:{{ $isHK ? '#166534' : '#92400e' }}">{{ $statusName }}</span>
                                                                </div>
                                                            </div>
                                                            @if($lv)
                                                                <div style="flex-shrink:0;text-align:center;min-width:76px">
                                                                    <div style="font-size:10px;font-weight:600;color:#64748b">{{ $lv['arr_time'] }}</div>
                                                                    <div style="font-size:18px;font-weight:800;color:#92400e;line-height:1.1">{{ $lv['airport'] }}</div>
                                                                    <div style="font-size:10px;color:#64748b">{{ $lv['arr_date'] }}</div>
                                                                    @if($lv['arr_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 4px;display:inline-block;color:#64748b">Arr T{{ $lv['arr_term'] }}</div>@endif
                                                                    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:3px 8px;margin:4px 0;display:inline-block">
                                                                        <div style="font-size:10px;font-weight:700;color:#d97706"><i class="fas fa-clock" style="font-size:9px"></i> {{ $lv['duration'] }}</div>
                                                                        @if($lv['overnight'])<div style="font-size:9px;font-weight:700;color:#dc2626">Overnight</div>@endif
                                                                    </div>
                                                                    <div style="font-size:10px;font-weight:600;color:#64748b">{{ $lv['dep_time'] }}</div>
                                                                    @if($lv['dep_term'])<div style="font-size:9px;background:#f1f5f9;border-radius:4px;padding:1px 4px;display:inline-block;color:#64748b">Dep T{{ $lv['dep_term'] }}</div>@endif
                                                                </div>
                                                            @else
                                                                <div style="flex-shrink:0;text-align:center;min-width:64px">
                                                                    <div style="font-size:11px;font-weight:600;color:#64748b">{{ $arrTime }}</div>
                                                                    <div style="font-size:22px;font-weight:800;color:{{ $destColor }};line-height:1.1">{{ $destination }}</div>
                                                                    <div style="font-size:10px;color:#64748b">{{ $arrDate }}</div>
                                                                    @if($arrTerm)
                                                                        <div style="font-size:10px;background:#f1f5f9;border-radius:4px;padding:1px 5px;display:inline-block;margin-top:2px;color:#64748b">T{{ $arrTerm }}</div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6><strong>{{ __('Passengers') }}</strong></h6>
                                @php
                                    $passengerIds = $refund->passenger_id ?? [];
                                    if (!is_array($passengerIds)) {
                                        $passengerIds = json_decode($passengerIds, true) ?? [];
                                    }
                                    $passengers = \Modules\Booking\Models\BookingPassenger::whereIn('id', $passengerIds)->get();
                                @endphp
                                @if($passengers->count() > 0)
                                    <ul>
                                        @foreach($passengers as $passenger)
                                            <li>{{ $passenger->first_name }} {{ $passenger->last_name }} — {{ format_money_main($passenger->total ?? 0) }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">All passengers</p>
                                @endif
                            </div>
                        </div>
                        @if($refund->reason)
                            <hr>
                            <div class="row"><div class="col-md-12"><h6><strong>{{ __('Reason') }}</strong></h6><p>{{ $refund->reason }}</p></div></div>
                        @endif
                        @if($refund->airline_response)
                            <hr>
                            <div class="row"><div class="col-md-12"><h6><strong>{{ __('Airline Response') }}</strong></h6><p>{{ $refund->airline_response }}</p></div></div>
                        @endif
                        @if($refund->rejection_reason)
                            <hr>
                            <div class="row"><div class="col-md-12"><h6><strong>{{ __('Rejection Reason') }}</strong></h6><p class="text-danger">{{ $refund->rejection_reason }}</p></div></div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
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
            var table = $('#refundTable').DataTable({
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
                    { responsivePriority: 1,  targets: 3  },                   // PNR
                    { responsivePriority: 2,  targets: 9  },                   // Status
                    { responsivePriority: 3,  targets: 10 },                   // Requested
                    { responsivePriority: 4,  targets: 11, orderable: false }, // Actions
                    { responsivePriority: 5,  targets: 8  },                   // Net Refund
                    { responsivePriority: 6,  targets: 5  },                   // Type
                    { responsivePriority: 7,  targets: 1  },                   // ID
                    { responsivePriority: 8,  targets: 2  },                   // Booking
                    { responsivePriority: 9,  targets: 6  },                   // Amount
                    { responsivePriority: 10, targets: 7  },                   // Charges
                    { responsivePriority: 11, targets: 4  },                   // Customer
                    { responsivePriority: 12, targets: 0, orderable: false },  // Checkbox
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',        exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',          exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel',exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',    exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',       exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search PNR, booking, customer...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests found',
                    emptyTable: 'No refund requests found.',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // ── Status & Type Filter Buttons ───────────────────────
            $(document).on('click', '.btn-filter', function () {
                var col = $(this).data('col');
                var val = $(this).data('val');

                // Active state — শুধু same col এর buttons এ
                $('.btn-filter[data-col="' + col + '"]').removeClass('active');
                $(this).addClass('active');

                table.column(col).search(val, false, false).draw();
            });

            // ── Check All ─────────────────────────────────────────
            $('.check-all').on('change', function () {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });

            // ── Load Passenger Amount ──────────────────────────────
            $('.modal[id^="modal-set-amount-"]').on('shown.bs.modal', function () {
                var id = $(this).attr('id').replace('modal-set-amount-', '');
                $('#passenger_amount_display_' + id).val('Loading...');

                $.ajax({
                    url: '/admin/module/booking/refunds/' + id + '/passenger-amount',
                    method: 'GET',
                    dataType: 'json',
                    success: function (r) {
                        if (r.status === true) {
                            $('#passenger_amount_' + id).val(r.passenger_amount);
                            $('#passenger_amount_display_' + id).val(r.formatted_amount);
                            calculateNetRefund(id);
                        } else {
                            $('#passenger_amount_display_' + id).val('Error loading');
                        }
                    },
                    error: function () {
                        $('#passenger_amount_display_' + id).val('Error');
                    }
                });
            });

            // ── Net Refund Calculator ──────────────────────────────
            // $(document).on('input', '.refund-charges-set', function () {
            //     calculateNetRefund($(this).data('id'));
            // });

            $(document).on('input', '.refund-charges-set, .service-charge-set', function () {
                calculateNetRefund($(this).data('id'));
            });

            function calculateNetRefund(id) {
                var passenger = parseFloat($('#passenger_amount_' + id).val()) || 0;
                var charges   = parseFloat($('#refund_charges_set_' + id).val()) || 0;
                var service   = parseFloat($('#service_charge_set_' + id).val()) || 0;
                $('#net_refund_set_' + id).val('BDT ' + (passenger - charges - service).toFixed(2));
            }

            // function calculateNetRefund(id) {
            //     var passenger = parseFloat($('#passenger_amount_' + id).val()) || 0;
            //     var charges   = parseFloat($('#refund_charges_set_' + id).val()) || 0;
            //     $('#net_refund_set_' + id).val('BDT ' + (passenger - charges).toFixed(2));
            // }

            // ── Set Amount ─────────────────────────────────────────
            $(document).on('click', '.btn-set-amount', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var passenger  = parseFloat($('#passenger_amount_' + id).val()) || 0;
                var charges    = parseFloat($('#refund_charges_set_' + id).val()) || 0;
                var service    = parseFloat($('#service_charge_set_' + id).val()) || 0;
                var net        = passenger - charges - service;
                var airline    = $('#airline_response_set_' + id).val();
                // var passenger  = parseFloat($('#passenger_amount_' + id).val()) || 0;
                // var charges    = parseFloat($('#refund_charges_set_' + id).val()) || 0;
                // var net        = passenger - charges;
                // var airline    = $('#airline_response_set_' + id).val();

                if (passenger <= 0) { alert('Passenger amount not loaded. Please try again.'); return; }
                if (charges < 0)    { alert('Please enter valid refund charges'); return; }
                if (net <= 0)       { alert('Net refund amount must be greater than 0'); return; }
                if (!confirm('Send refund of BDT ' + net.toFixed(2) + ' to user for approval?')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/refunds/' + id + '/set-amount',
                    method: 'POST',
                    data: { refund_amount: passenger, refund_charges: charges, service_charge: service, airline_response: airline, _token: '{{ csrf_token() }}' },
                    {{--data: { refund_amount: passenger, refund_charges: charges, airline_response: airline, _token: '{{ csrf_token() }}' },--}}
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User for Approval');
                    }
                });
            });

            // ── Final Approve ──────────────────────────────────────
            $(document).on('click', '.btn-final-approve', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                if (!confirm('Are you sure? This will add the refund amount to customer wallet.')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/refunds/' + id + '/approve',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Add to Wallet');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Add to Wallet');
                    }
                });
            });

            // ── Reject ─────────────────────────────────────────────
            $(document).on('click', '.btn-reject-refund', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var reason = $('#rejection_reason_refund_' + id).val();
                if (!reason || reason.length < 10) { alert('Please enter rejection reason (minimum 10 characters)'); return; }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '/admin/module/booking/refunds/' + id + '/reject',
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
