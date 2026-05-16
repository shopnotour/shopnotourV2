@extends('admin.layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <style>
        #ssrTable thead th {
            background-color: #1e3a5f;
            color: #fff;
            font-weight: 600;
            padding: 10px 12px;
            font-size: 0.82rem;
            white-space: nowrap;
            border-bottom: none !important;
        }
        #ssrTable tbody td {
            padding: 9px 12px;
            font-size: 0.83rem;
            vertical-align: middle;
        }
        #ssrTable tbody tr:hover td { background-color: #f0f4ff !important; }

        #ssrTable_filter input {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 0.82rem;
            outline: none;
        }
        #ssrTable_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        #ssrTable_length select {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.82rem;
        }
        #ssrTable_info,
        #ssrTable_length label,
        #ssrTable_filter label { font-size: 0.8rem; color: #6b7280; }

        #ssrTable_paginate .paginate_button {
            border-radius: 5px !important;
            padding: 4px 9px !important;
            font-size: 0.78rem !important;
            border: 1px solid #e5e7eb !important;
            margin: 0 1px;
            color: #374151 !important;
        }
        #ssrTable_paginate .paginate_button.current {
            background: #1e3a5f !important;
            color: #fff !important;
            border-color: #1e3a5f !important;
        }
        #ssrTable_paginate .paginate_button:hover:not(.current):not(.disabled) {
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
            color: #374151;
        }
        .filter-btn-group .btn-filter.active,
        .filter-btn-group .btn-filter:hover { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .filter-btn-group .btn-filter.type-btn.active,
        .filter-btn-group .btn-filter.type-btn:hover { background: #7c3aed; border-color: #7c3aed; color: #fff; }

        .badge-waiting_user_approval { background-color: #17a2b8; color: white; }
        .badge-user_approved { background-color: #007bff; color: white; }
        .badge-user_rejected { background-color: #dc3545; color: white; }
        .badge-confirmed { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: black; }
        .badge-failed { background-color: #dc3545; color: white; }
        .badge-cancelled { background-color: #6c757d; color: white; }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('SSR Requests') }}</h1>
        </div>

        @include('admin.message')

        {{-- Bulk Action --}}
        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="post" action="{{ route('admin.ssrs.bulkAction') }}" class="filter-form filter-form-left d-flex" id="bulkActionForm">
                    @csrf
                    <select name="action" class="form-control">
                        <option value="">{{ __('-- Bulk Actions --') }}</option>
                        <option value="approve">{{ __('Approve') }}</option>
                        <option value="reject">{{ __('Reject') }}</option>
                        <option value="delete">{{ __('Delete') }}</option>
                    </select>
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Apply') }}</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">{{ __('SSR Requests') }}</div>
            <div class="panel-body">

                {{-- Status Filter --}}
                <div class="filter-btn-group mb-1">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Status:</span>
                    <button class="btn-filter active" data-col="8" data-val="">All</button>
                    <button class="btn-filter" data-col="8" data-val="pending">Pending</button>
                    <button class="btn-filter" data-col="8" data-val="waiting user approval">Waiting User Approval</button>
                    <button class="btn-filter" data-col="8" data-val="user approved">User Approved</button>
                    <button class="btn-filter" data-col="8" data-val="user rejected">User Rejected</button>
                    <button class="btn-filter" data-col="8" data-val="confirmed">Confirmed</button>
                    <button class="btn-filter" data-col="8" data-val="failed">Failed</button>
                    <button class="btn-filter" data-col="8" data-val="cancelled">Cancelled</button>
                </div>

                {{-- SSR Type Filter --}}
                <div class="filter-btn-group mb-3">
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;margin-right:4px">Type:</span>
                    <button class="btn-filter type-btn active" data-col="5" data-val="">All</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="baggage">Baggage</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="meal">Meal</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="seat">Seat</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="insurance">Insurance</button>
                    <button class="btn-filter type-btn" data-col="5" data-val="wheelchair">Wheelchair</button>
                </div>

                <table id="ssrTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" class="check-all"></th>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Booking') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Passenger') }}</th>
                        <th>{{ __('SSR Type') }}</th>
                        <th>{{ __('SSR Code') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $ssr)
                        @php
                            $typeColors = [
                                'baggage'   => 'primary',
                                'meal'      => 'success',
                                'seat'      => 'info',
                                'insurance' => 'warning',
                                'wheelchair'=> 'secondary',
                            ];
                            $statusColors = [
                                'pending'               => 'warning',
                                'waiting_user_approval' => 'info',
                                'user_approved'         => 'primary',
                                'user_rejected'         => 'danger',
                                'confirmed'             => 'success',
                                'failed'                => 'danger',
                                'cancelled'             => 'secondary',
                            ];
                            $typeColor   = $typeColors[$ssr->ssr_type] ?? 'secondary';
                            $statusColor = $statusColors[$ssr->status] ?? 'secondary';
                            $statusLabel = ucfirst(str_replace('_', ' ', $ssr->status));
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $ssr->id }}" form="bulkActionForm"></td>
                            <td>{{ $ssr->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $ssr->booking_id) }}" target="_blank">
                                    #{{ $ssr->booking_id }}
                                </a>
                            </td>
                            <td>
                                @if($ssr->booking && $ssr->booking->customer)
                                    {{ $ssr->booking->customer->name ?? $ssr->booking->customer->first_name }}<br>
                                    <small class="text-muted">{{ $ssr->booking->customer->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($ssr->passenger)
                                    {{ $ssr->passenger->first_name }} {{ $ssr->passenger->last_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $typeColor }}">{{ ucfirst($ssr->ssr_type) }}</span>
                            </td>
                            <td>
                                @if($ssr->ssr_code)
                                    <code>{{ $ssr->ssr_code }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($ssr->amount > 0)
                                    <strong class="text-primary">{{ format_money_main($ssr->amount) }}</strong>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td data-order="{{ $ssr->status }}">
                                <span class="badge badge-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-order="{{ $ssr->created_at->timestamp }}" style="white-space:nowrap">
                                {{ $ssr->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $ssr->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        {{ __('Actions') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="javascript:void(0)"
                                           data-toggle="modal" data-target="#modal-ssr-{{ $ssr->id }}">
                                            <i class="fa fa-eye"></i> {{ __('View Details') }}
                                        </a>
                                        @if($ssr->status == 'pending')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-set-amount-{{ $ssr->id }}">
                                                <i class="fa fa-money text-primary"></i> {{ __('Set Amount') }}
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-reject-ssr-{{ $ssr->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Reject') }}
                                            </a>
                                        @endif
                                        @if($ssr->status == 'user_approved')
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               data-toggle="modal" data-target="#modal-approve-ssr-{{ $ssr->id }}">
                                                <i class="fa fa-check text-success"></i> {{ __('Final Approve') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">{{ __('No SSR requests found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- ══════════════════ MODALS ══════════════════ --}}
    @foreach($rows as $ssr)
        @php
            $typeColors = [
                'baggage'   => 'primary',
                'meal'      => 'success',
                'seat'      => 'info',
                'insurance' => 'warning',
                'wheelchair'=> 'secondary',
            ];
            $statusColors = [
                'pending'               => 'warning',
                'waiting_user_approval' => 'info',
                'user_approved'         => 'primary',
                'user_rejected'         => 'danger',
                'confirmed'             => 'success',
                'failed'                => 'danger',
                'cancelled'             => 'secondary',
            ];
            $customer      = $ssr->booking->user ?? $ssr->booking->customer ?? null;
            $walletBalance = $customer ? ($customer->credit_balance ?? 0) : 0;
        @endphp

        {{-- Set Amount Modal --}}
        <div class="modal fade" id="modal-set-amount-{{ $ssr->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-money"></i> {{ __('Set SSR Amount #') }}{{ $ssr->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $ssr->booking_id }}<br>
                            <strong>SSR Type:</strong> {{ ucfirst($ssr->ssr_type) }}<br>
                            <strong>SSR Code:</strong> {{ $ssr->ssr_code ?? 'N/A' }}<br>
                            @if($ssr->passenger)
                                <strong>Passenger:</strong> {{ $ssr->passenger->first_name }} {{ $ssr->passenger->last_name }}
                            @endif
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            Amount সেট করলে User কে notification যাবে। User approve করলে আপনি Final Approve দিতে পারবেন।
                        </div>
                        <div class="form-group">
                            <label>{{ __('SSR Available?') }} <span class="text-danger">*</span></label>
                            <select id="ssr_available_{{ $ssr->id }}" class="form-control" required>
                                <option value="">{{ __('-- Select --') }}</option>
                                <option value="yes">{{ __('Yes, Available') }}</option>
                                <option value="no">{{ __('No, Not Available') }}</option>
                            </select>
                        </div>
                        <div class="form-group" id="amount_group_{{ $ssr->id }}" style="display:none">
                            <label>{{ __('SSR Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" id="ssr_amount_{{ $ssr->id }}" class="form-control"
                                       value="0" step="0.01" min="0">
                            </div>
                            <small class="form-text text-muted">এই amount User কে দেখানো হবে approval এর জন্য</small>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Description / Note (Optional)') }}</label>
                            <textarea id="ssr_description_{{ $ssr->id }}" class="form-control" rows="3"
                                      placeholder="যেমন: 20kg extra baggage">{{ $ssr->description }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary btn-set-amount" data-id="{{ $ssr->id }}">
                            <i class="fa fa-paper-plane"></i> {{ __('Send to User') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Final Approve Modal --}}
        <div class="modal fade" id="modal-approve-ssr-{{ $ssr->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-check-circle"></i> {{ __('Final Approve SSR #') }}{{ $ssr->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> <strong>User এই SSR approve করেছে!</strong>
                        </div>
                        <div class="alert alert-info">
                            <strong>Booking:</strong> #{{ $ssr->booking_id }}<br>
                            <strong>SSR Type:</strong> {{ ucfirst($ssr->ssr_type) }}<br>
                            <strong>SSR Code:</strong> {{ $ssr->ssr_code ?? 'N/A' }}<br>
                            @if($ssr->passenger)
                                <strong>Passenger:</strong> {{ $ssr->passenger->first_name }} {{ $ssr->passenger->last_name }}<br>
                            @endif
                            <strong>Amount:</strong>
                            <span class="text-success font-weight-bold">{{ format_money_main($ssr->amount) }}</span>
                        </div>
                        <div class="alert {{ $walletBalance >= $ssr->amount ? 'alert-success' : 'alert-danger' }}">
                            <strong>Customer Wallet Balance:</strong> {{ format_money_main($walletBalance) }}
                            @if($walletBalance < $ssr->amount)
                                <br><small class="text-danger">
                                    <i class="fa fa-warning"></i> Insufficient balance!
                                </small>
                            @else
                                <br><small class="text-success">
                                    <i class="fa fa-check"></i> Sufficient balance
                                </small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ __('Airline Reference (Optional)') }}</label>
                            <input type="text" id="airline_reference_{{ $ssr->id }}" class="form-control"
                                   placeholder="Airline confirmation code">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success btn-final-approve"
                                data-id="{{ $ssr->id }}" {{ $walletBalance < $ssr->amount ? 'disabled' : '' }}>
                            <i class="fa fa-check"></i> {{ __('Approve & Deduct Wallet') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="modal-reject-ssr-{{ $ssr->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-times-circle"></i> {{ __('Reject SSR Request #') }}{{ $ssr->id }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> এটি SSR request reject করবে।
                        </div>
                        <div class="form-group">
                            <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_ssr_{{ $ssr->id }}" class="form-control" rows="4" required
                                      placeholder="যেমন: এই SSR এখন available নেই"></textarea>
                            <small class="form-text text-muted">Minimum 5 characters</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger btn-reject-ssr" data-id="{{ $ssr->id }}">
                            <i class="fa fa-times"></i> {{ __('Reject Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Modal --}}
        <div class="modal fade" id="modal-ssr-{{ $ssr->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-file-text"></i> {{ __('SSR Request Details #') }}{{ $ssr->id }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr><th width="40%">{{ __('SSR ID') }}</th><td>{{ $ssr->id }}</td></tr>
                                    <tr><th>{{ __('Booking ID') }}</th>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $ssr->booking_id) }}" target="_blank">
                                                #{{ $ssr->booking_id }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('SSR Type') }}</th>
                                        <td>
                                            <span class="badge badge-{{ $typeColors[$ssr->ssr_type] ?? 'secondary' }}">
                                                {{ ucfirst($ssr->ssr_type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr><th>{{ __('SSR Code') }}</th><td>{{ $ssr->ssr_code ?? 'N/A' }}</td></tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            <span class="badge badge-{{ $statusColors[$ssr->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ssr->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ __('Amount') }}</th>
                                        <td>
                                            @if($ssr->amount > 0)
                                                <strong class="text-success">{{ format_money_main($ssr->amount) }}</strong>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>{{ __('Airline Reference') }}</th><td>{{ $ssr->airline_reference ?? 'N/A' }}</td></tr>
                                    <tr><th>{{ __('Requested At') }}</th><td>{{ $ssr->created_at->format('d M Y, h:i A') }}</td></tr>
                                    @if($ssr->confirmed_at)
                                        <tr><th>{{ __('Confirmed At') }}</th><td>{{ $ssr->confirmed_at->format('d M Y, h:i A') }}</td></tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>{{ __('Customer Information') }}</strong></h6>
                                @if($ssr->booking && $ssr->booking->customer)
                                    <p>
                                        <strong>Name:</strong> {{ $ssr->booking->customer->name ?? $ssr->booking->customer->first_name }}<br>
                                        <strong>Email:</strong> {{ $ssr->booking->customer->email }}<br>
                                        <strong>Wallet:</strong> {{ format_money_main($ssr->booking->customer->wallet->balance ?? 0) }}
                                    </p>
                                @else
                                    <p class="text-muted">N/A</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6><strong>{{ __('Passenger Information') }}</strong></h6>
                                @if($ssr->passenger)
                                    <p>
                                        <strong>Name:</strong> {{ $ssr->passenger->first_name }} {{ $ssr->passenger->last_name }}<br>
                                        <strong>Type:</strong> {{ ucfirst($ssr->passenger->type ?? 'Adult') }}
                                    </p>
                                @else
                                    <p class="text-muted">N/A</p>
                                @endif
                            </div>
                        </div>

                        @if($ssr->description)
                            <hr>
                            <h6><strong>{{ __('Description') }}</strong></h6>
                            <p>{{ $ssr->description }}</p>
                        @endif

                        @if($ssr->ssr_details)
                            <hr>
                            <h6><strong>{{ __('SSR Details (Raw)') }}</strong></h6>
                            <pre class="bg-light p-3" style="font-size:.78rem;border-radius:6px">{{ json_encode(is_string($ssr->ssr_details) ? json_decode($ssr->ssr_details, true) : $ssr->ssr_details, JSON_PRETTY_PRINT) }}</pre>
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
            var table = $('#ssrTable').DataTable({
                dom:
                    "<'d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3'B<'ml-auto'f>>" +
                    "tr" +
                    "<'d-flex flex-wrap align-items-center justify-content-between mt-3'i<'ml-auto'p>>",
                pageLength: 20,
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
                order: [[9, 'desc']], // Requested
                responsive: {
                    details: { type: 'inline', renderer: $.fn.dataTable.Responsive.renderer.listHiddenNodes() }
                },
                columnDefs: [
                    { responsivePriority: 1,  targets: 5  },                   // SSR Type
                    { responsivePriority: 2,  targets: 8  },                   // Status
                    { responsivePriority: 3,  targets: 9  },                   // Requested
                    { responsivePriority: 4,  targets: 10, orderable: false }, // Actions
                    { responsivePriority: 5,  targets: 7  },                   // Amount
                    { responsivePriority: 6,  targets: 6  },                   // SSR Code
                    { responsivePriority: 7,  targets: 2  },                   // Booking
                    { responsivePriority: 8,  targets: 4  },                   // Passenger
                    { responsivePriority: 9,  targets: 3  },                   // Customer
                    { responsivePriority: 10, targets: 1  },                   // ID
                    { responsivePriority: 11, targets: 0, orderable: false },  // Checkbox
                ],
                buttons: [
                    { extend: 'copy',  text: '<i class="fa fa-copy"></i> Copy',         exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'csv',   text: '<i class="fa fa-file"></i> CSV',           exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i> Excel', exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'pdf',   text: '<i class="fa fa-file-pdf-o"></i> PDF',     exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                    { extend: 'print', text: '<i class="fa fa-print"></i> Print',        exportOptions: { columns: [1,2,3,4,5,6,7,8,9] } },
                ],
                language: {
                    search: '', searchPlaceholder: 'Search booking, passenger, code...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_ requests',
                    infoEmpty: 'No requests found',
                    emptyTable: 'No SSR requests found.',
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

            // ── Toggle amount field ────────────────────────────────
            $(document).on('change', '[id^="ssr_available_"]', function () {
                var id = $(this).attr('id').replace('ssr_available_', '');
                $('#amount_group_' + id).toggle($(this).val() === 'yes');
            });

            // ── Set Amount ─────────────────────────────────────────
            $(document).on('click', '.btn-set-amount', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var available   = $('#ssr_available_' + id).val();
                var amount      = $('#ssr_amount_' + id).val();
                var description = $('#ssr_description_' + id).val();

                if (!available) { alert('Please select if SSR is available'); return; }
                if (available === 'yes' && (!amount || parseFloat(amount) <= 0)) {
                    alert('Please enter valid amount'); return;
                }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '{{ route("admin.ssrs.setAmount", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    data: { available: available, amount: amount, description: description, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true || r.success === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send to User');
                    }
                });
            });

            // ── Final Approve ──────────────────────────────────────
            $(document).on('click', '.btn-final-approve', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var ref = $('#airline_reference_' + id).val();

                if (!confirm('আপনি কি নিশ্চিত? Customer এর wallet থেকে টাকা কেটে নেওয়া হবে।')) return;

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '{{ route("admin.ssrs.approve", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    data: { airline_reference: ref, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true || r.success === true) window.location.reload();
                        else btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Deduct Wallet');
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve & Deduct Wallet');
                    }
                });
            });

            // ── Reject ─────────────────────────────────────────────
            $(document).on('click', '.btn-reject-ssr', function (e) {
                e.preventDefault();
                var btn = $(this), id = btn.data('id');
                var reason = $('#rejection_reason_ssr_' + id).val();

                if (!reason || reason.length < 5) {
                    alert('Please enter rejection reason (minimum 5 characters)'); return;
                }

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: '{{ route("admin.ssrs.reject", ":id") }}'.replace(':id', id),
                    method: 'POST',
                    data: { rejection_reason: reason, _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (r) {
                        alert(r.message);
                        if (r.status === true || r.success === true) window.location.reload();
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
