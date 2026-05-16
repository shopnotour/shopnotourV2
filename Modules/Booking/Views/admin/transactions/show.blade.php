@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Transaction Details</h2>
                        <p class="text-muted">View detailed information about this transaction</p>
                    </div>
                    <div>
                        <a href="{{ route('transactions.index', ['user_id' => $transaction->user_id]) }}" class="btn btn-info mr-2">
                            <i class="fas fa-list"></i> User's Transactions
                        </a>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to All
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Wallet Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-arrow-down"></i> Total Credit
                        </h6>
                        <h3 class="mb-0">৳{{ number_format($totalCredit, 2) }}</h3>
                        <small>All time earnings</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-arrow-up"></i> Total Debit
                        </h6>
                        <h3 class="mb-0">৳{{ number_format($totalDebit, 2) }}</h3>
                        <small>All time spending</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card {{ $walletBalance >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                    <div class="card-body">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-wallet"></i> Calculated Balance
                        </h6>
                        <h3 class="mb-0">৳{{ number_format($walletBalance, 2) }}</h3>
                        <small>From transactions</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title mb-2">
                            <i class="fas fa-list"></i> Total Transactions
                        </h6>
                        <h3 class="mb-0">{{ $transactionCount }}</h3>
                        <small>All records</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Transaction Details Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice"></i> Transaction Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%" class="bg-light">Transaction ID</th>
                                <td><strong>{{ $transaction->id }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">User ID</th>
                                <td>{{ $transaction->user_id }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Booking ID</th>
                                <td>
                                    @if($transaction->booking_id)
                                        <span class="badge badge-secondary badge-lg">{{ $transaction->booking_id }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Reference ID</th>
                                <td>{{ $transaction->ref_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Type</th>
                                <td>
                                    @if($transaction->type === 'credit')
                                        <span class="badge badge-success badge-lg">
                                        <i class="fas fa-arrow-down"></i> CREDIT (আয়)
                                    </span>
                                    @else
                                        <span class="badge badge-danger badge-lg">
                                        <i class="fas fa-arrow-up"></i> DEBIT (খরচ)
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Amount</th>
                                <td>
                                    <h3 class="mb-0 {{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                                    </h3>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Transaction Type</th>
                                <td>
                                    <span class="badge badge-info">{{ $transaction->transaction_type ?? 'N/A' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Status</th>
                                <td>
                                    @if($transaction->status === 'confirmed')
                                        <span class="badge badge-success badge-lg">Confirmed</span>
                                    @elseif($transaction->status === 'payment')
                                        <span class="badge badge-warning badge-lg">Payment</span>
                                    @elseif($transaction->status === 'completed')
                                        <span class="badge badge-info badge-lg">Completed</span>
                                    @else
                                        <span class="badge badge-secondary badge-lg">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Reference</th>
                                <td>{{ $transaction->reference ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Deposit Date</th>
                                <td>
                                    @if($transaction->deposit_date)
                                        <i class="far fa-calendar"></i> {{ date('d M Y', strtotime($transaction->deposit_date)) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Created At</th>
                                <td>
                                    <i class="far fa-clock"></i> {{ date('d M Y h:i:s A', strtotime($transaction->created_at)) }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Updated At</th>
                                <td>
                                    <i class="far fa-clock"></i> {{ date('d M Y h:i:s A', strtotime($transaction->updated_at)) }}
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Create User</th>
                                <td>{{ $transaction->create_user ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Update User</th>
                                <td>{{ $transaction->update_user ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Attachment ID</th>
                                <td>{{ $transaction->attachment_id ?? 'N/A' }}</td>
                            </tr>
                            @if($transaction->remarks)
                                <tr>
                                    <th class="bg-light">Remarks</th>
                                    <td>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-comment"></i> {{ $transaction->remarks }}
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Meta Information -->
                @if($transaction->meta)
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> Meta Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0 p-3 bg-light border rounded">{{ $transaction->meta }}</pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- User Information Card -->
            <div class="col-md-4">
                @if($user)
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user"></i> User Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @if($user->avatar_id)
                                    <img src="/path/to/avatar/{{ $user->avatar_id }}" class="rounded-circle" width="80" height="80" alt="Avatar">
                                @else
                                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                                @endif
                            </div>

                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="100">User ID</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td><strong>{{ $user->name ?? ($user->first_name . ' ' . $user->last_name) }}</strong></td>
                                </tr>
                                @if($user->business_name)
                                    <tr>
                                        <th>Business</th>
                                        <td><strong>{{ $user->business_name }}</strong></td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    </td>
                                </tr>
                                @if($user->phone)
                                    <tr>
                                        <th>Phone</th>
                                        <td>
                                            <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if($user->address)
                                    <tr>
                                        <th>Address</th>
                                        <td>
                                            {{ $user->address }}
                                            @if($user->address2)<br>{{ $user->address2 }}@endif
                                        </td>
                                    </tr>
                                @endif
                                @if($user->city || $user->state || $user->country)
                                    <tr>
                                        <th>Location</th>
                                        <td>
                                            @if($user->city){{ $user->city }}@endif
                                            @if($user->state), {{ $user->state }}@endif
                                            @if($user->zip_code) - {{ $user->zip_code }}@endif
                                            @if($user->country)<br>{{ $user->country }}@endif
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($user->status == 1)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($user->credit_balance)
                                    <tr>
                                        <th>DB Balance</th>
                                        <td>
                                            <strong class="text-primary">৳{{ number_format($user->credit_balance, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Member Since</th>
                                    <td>{{ date('d M Y', strtotime($user->created_at)) }}</td>
                                </tr>
                            </table>

                            <div class="mt-3">
                                <a href="{{ route('transactions.index', ['user_id' => $user->id]) }}"
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-list"></i> View All Transactions
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i> Quick Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Total Credit</small>
                            <h5 class="text-success mb-0">+৳{{ number_format($totalCredit, 2) }}</h5>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Total Debit</small>
                            <h5 class="text-danger mb-0">-৳{{ number_format($totalDebit, 2) }}</h5>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block">Total Transactions</small>
                            <h5 class="text-info mb-0">{{ $transactionCount }}</h5>
                        </div>
                        <div>
                            <small class="text-muted d-block">Net Balance</small>
                            <h4 class="{{ $walletBalance >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                ৳{{ number_format($walletBalance, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <!-- Balance Comparison (if DB balance exists) -->
                @if($user && $user->credit_balance)
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-balance-scale"></i> Balance Comparison
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Database Balance:</td>
                                    <td class="text-right"><strong>৳{{ number_format($user->credit_balance, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Calculated Balance:</td>
                                    <td class="text-right"><strong>৳{{ number_format($walletBalance, 2) }}</strong></td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Difference:</strong></td>
                                    <td class="text-right">
                                        <strong class="{{ abs($user->credit_balance - $walletBalance) < 1 ? 'text-success' : 'text-danger' }}">
                                            ৳{{ number_format(abs($user->credit_balance - $walletBalance), 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                            @if(abs($user->credit_balance - $walletBalance) >= 1)
                                <div class="alert alert-warning mt-2 mb-0">
                                    <small><i class="fas fa-exclamation-triangle"></i> Balances don't match!</small>
                                </div>
                            @else
                                <div class="alert alert-success mt-2 mb-0">
                                    <small><i class="fas fa-check-circle"></i> Balances match!</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }

        .table th {
            font-weight: 600;
        }

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        pre {
            font-size: 0.875rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
@endsection
