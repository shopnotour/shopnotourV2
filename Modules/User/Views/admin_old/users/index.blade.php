@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users text-primary"></i> User Management
                </h1>
                <p class="text-muted mb-0">Manage all registered users</p>
            </div>
        </div>

        @include('admin.message')

        {{-- Stats Row --}}
        @php
            $totalUsers   = $users->count();
            $activeUsers  = $users->where('status', 'publish')->count();
            $totalBalance = $users->sum('credit_balance');
            $newThisMonth = $users->filter(fn($u) => \Carbon\Carbon::parse($u->created_at)->gte(now()->startOfMonth()))->count();
        @endphp

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeUsers }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Credit Balance</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">৳{{ number_format($totalBalance, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New This Month</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newThisMonth }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-1"></i> All Users
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-hover" width="100%">
                        <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Business</th>
                            <th width="130">Credit Balance</th>
                            <th width="90">Status</th>
                            <th width="110">Joined</th>
                            <th width="200">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $i => $user)
                            @php
                                $roleLabel = match((int)$user->role_id) {
                                    1 => ['Admin',    'badge-danger'],
                                    2 => ['Agent',    'badge-info'],
                                    3 => ['Customer', 'badge-primary'],
                                    default => ['User', 'badge-secondary'],
                                };
                            @endphp
                            <tr>
                                {{-- # --}}
                                <td class="text-center text-muted">{{ $i + 1 }}</td>

                                {{-- Name --}}
                                <td>
                                    <div class="font-weight-bold text-gray-800">
                                        {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—' }}
                                    </div>
                                    <span class="badge {{ $roleLabel[1] }}">{{ $roleLabel[0] }}</span>
                                </td>

                                {{-- Email --}}
                                <td class="text-muted small">{{ $user->email ?? '—' }}</td>

                                {{-- Phone --}}
                                <td class="text-muted small">{{ $user->phone ?? '—' }}</td>

                                {{-- Business --}}
                                <td class="text-muted small">{{ $user->business_name ?? '—' }}</td>

                                {{-- Credit Balance --}}
                                <td>
                                    <span class="font-weight-bold {{ ($user->credit_balance ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                        ৳{{ number_format($user->credit_balance ?? 0, 2) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    @if($user->status === 'publish')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Active
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-times"></i> Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Joined --}}
                                <td class="text-muted small">
                                    {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.users.bookings', $user->id) }}"
                                           class="btn btn-primary" title="Bookings">
                                            <i class="fas fa-ticket-alt"></i> Bookings
                                        </a>
                                        <a href="{{ route('admin.users.transactions', $user->id) }}"
                                           class="btn btn-success" title="Transactions">
                                            <i class="fas fa-exchange-alt"></i> Txns
                                        </a>
                                        <a href="{{ route('admin.users.tickets', $user->id) }}"
                                           class="btn btn-info" title="Tickets">
                                            <i class="fas fa-print"></i> Tickets
                                        </a>
                                        <a href="{{route('user.admin.password',$user->id)}}"
                                           class="btn btn-info" title="password">
                                            <i class="fas fa-print"></i> Pass Change
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                pageLength: 25,
                order: [[7, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 8] },
                    { searchable: false, targets: [0, 5, 6, 7, 8] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search users...',
                    lengthMenu: 'Show _MENU_ users',
                    info: 'Showing _START_ to _END_ of _TOTAL_ users',
                    paginate: {
                        previous: '&laquo; Prev',
                        next: 'Next &raquo;',
                    }
                },
            });
        });
    </script>
@endpush
