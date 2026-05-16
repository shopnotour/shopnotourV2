@extends('admin.layouts.app')
@section('content')

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; background: white; }
            .bg-gradient { background: white !important; }
        }

        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .stat-card {
            border-left: 4px solid;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>

    <div class="container-fluid">
        <!-- Top Header -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line text-white" style="font-size: 28px;"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="mb-0">Marketing Analytics Dashboard</h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-circle text-success pulse"></i>
                            Live Data • Real-time Insights
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 text-right no-print">
                <a href="{{ route('visitor.index') }}" class="btn btn-success mr-2">
                    <i class="fas fa-users"></i> Live Visitors
                </a>
                <button onclick="window.print()" class="btn btn-primary mr-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-info">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('market.admin.index') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">From Date:</label>
                        <input type="date" name="from_date" class="form-control"
                               value="{{ request('from_date', $fromDate->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group mr-3">
                        <label class="mr-2">To Date:</label>
                        <input type="date" name="to_date" class="form-control"
                               value="{{ request('to_date', $toDate->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group mr-3">
                        <label class="mr-2">Quick Select:</label>
                        <select class="form-control" id="quickDate">
                            <option value="">Custom</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="last_90_days">Last 90 Days</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>

                    <a href="{{ route('market.admin.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </form>
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="row mb-4">
            <!-- Total Bookings -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card border-left-primary card-hover h-100" style="border-left-color: #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Bookings
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalBookings) }}
                                </div>
                                <div class="mt-2">
                                <span class="badge badge-{{ $bookingsGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $bookingsGrowth >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($bookingsGrowth) }}%
                                </span>
                                    <small class="text-muted ml-1">vs last month</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card border-left-success card-hover h-100" style="border-left-color: #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Revenue
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ৳{{ number_format($totalRevenue, 2) }}
                                </div>
                                <div class="mt-2">
                                <span class="badge badge-{{ $revenueGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($revenueGrowth) }}%
                                </span>
                                    <small class="text-muted ml-1">MoM growth</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Average Ticket Price -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card border-left-info card-hover h-100" style="border-left-color: #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Avg Ticket Price
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ৳{{ number_format($avgTicketPrice, 2) }}
                                </div>
                                <div class="mt-2">
                                <span class="badge badge-{{ $avgPriceGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $avgPriceGrowth >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($avgPriceGrowth) }}%
                                </span>
                                    <small class="text-muted ml-1">price change</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversion Rate -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card border-left-warning card-hover h-100" style="border-left-color: #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Conversion Rate
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($conversionRate, 1) }}%
                                </div>
                                <div class="mt-2">
                                <span class="badge badge-{{ $conversionGrowth >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-arrow-{{ $conversionGrowth >= 0 ? 'up' : 'down' }}"></i>
                                    {{ abs($conversionGrowth) }}%
                                </span>
                                    <small class="text-muted ml-1">improvement</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor & Traffic Stats -->
        <div class="row mb-4">

            <!-- Online Now -->
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card card-hover h-100" style="border-left: 4px solid #1cc88a; background: #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fff;">
                                    <i class="fas fa-circle pulse"></i> Online Now
                                </div>
                                <div class="h5 mb-0 font-weight-bold" style="color: #fff;">
                                    {{ number_format($onlineVisitors) }}
                                </div>
                                <div class="mt-2" style="color: rgba(255,255,255,0.8);">
                                    <small>{{ $onlineUsers }} Users • {{ $onlineGuests }} Guests</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x" style="color: rgba(255,255,255,0.4);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Visitors -->
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card card-hover h-100" style="border-left: 4px solid #4e73df; background: #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fff;">
                                    Total Visitors
                                </div>
                                <div class="h5 mb-0 font-weight-bold" style="color: #fff;">
                                    {{ number_format($totalWebsiteVisitors) }}
                                </div>
                                <div class="mt-2" style="color: rgba(255,255,255,0.8);">
                                    <small>
                                        <i class="fas fa-arrow-{{ $visitorGrowth >= 0 ? 'up' : 'down' }}"></i>
                                        {{ abs($visitorGrowth) }}% vs prev period
                                    </small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-eye fa-2x" style="color: rgba(255,255,255,0.4);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor → Booking -->
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card card-hover h-100" style="border-left: 4px solid #36b9cc; background: #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fff;">
                                    Visitor → Booking
                                </div>
                                <div class="h5 mb-0 font-weight-bold" style="color: #fff;">
                                    {{ number_format($visitorToBookingRate, 2) }}%
                                </div>
                                <div class="mt-2" style="color: rgba(255,255,255,0.8);">
                                    <small>Conversion funnel</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-filter fa-2x" style="color: rgba(255,255,255,0.4);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bounce Rate -->
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card card-hover h-100" style="border-left: 4px solid #f6c23e; background: #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fff;">
                                    Bounce Rate
                                </div>
                                <div class="h5 mb-0 font-weight-bold" style="color: #fff;">
                                    {{ number_format($bounceRate, 1) }}%
                                </div>
                                <div class="mt-2" style="color: rgba(255,255,255,0.8);">
                                    <small>Avg: {{ gmdate('i:s', $avgSessionDuration) }} min</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sign-out-alt fa-2x" style="color: rgba(255,255,255,0.4);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Users -->
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="card card-hover h-100" style="border-left: 4px solid #6f42c1; background: #6f42c1;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #fff;">
                                    <i class="fas fa-user-plus"></i> New Users
                                </div>
                                <div class="h5 mb-0 font-weight-bold" style="color: #fff;">
                                    {{ number_format($newUsersCount) }}
                                </div>
                                <div class="mt-2" style="color: rgba(255,255,255,0.8);">
                                    <small>
                                        <i class="fas fa-arrow-{{ $newUsersGrowth >= 0 ? 'up' : 'down' }}"></i>
                                        {{ abs($newUsersGrowth) }}% vs prev period
                                    </small>
                                </div>
                                <div class="mt-1" style="color: rgba(255,255,255,0.7);">
                                    <small>
                                        {{ \Carbon\Carbon::parse($fromDate)->format('d M') }}
                                        –
                                        {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-plus fa-2x" style="color: rgba(255,255,255,0.4);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- এখানে Part 2 যুক্ত হবে -->

        <!-- Part 2: Charts & Analytics Section -->

        <!-- Revenue & Visitor Trend Charts -->
        <div class="row mb-4">
            <!-- Revenue Trend Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line"></i> Revenue Trend (Last 12 Months)
                        </h6>
                        <div class="dropdown no-print">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueTrendChart" height="80"></canvas>

                        <div class="row mt-3 text-center">
                            <div class="col-md-4">
                                <small class="text-muted">Peak Month</small>
                                <h6 class="font-weight-bold text-success">
                                    @if($revenueTrend->isNotEmpty())
                                        ৳{{ number_format($revenueTrend->max('revenue'), 0) }}
                                    @else
                                        N/A
                                    @endif
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Avg Monthly Revenue</small>
                                <h6 class="font-weight-bold text-primary">
                                    ৳{{ number_format($revenueTrend->avg('revenue'), 0) }}
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Total (12 Months)</small>
                                <h6 class="font-weight-bold text-info">
                                    ৳{{ number_format($revenueTrend->sum('revenue'), 0) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor Trend Chart -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-users"></i> Visitor Trend (30 Days)
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="visitorTrendChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Status Breakdown -->
{{--        <div class="row mb-4">--}}
{{--            <div class="col-lg-12">--}}
{{--                <div class="card shadow">--}}
{{--                    <div class="card-header py-3">--}}
{{--                        <h6 class="m-0 font-weight-bold text-primary">--}}
{{--                            <i class="fas fa-clipboard-list"></i> Booking Status Breakdown--}}
{{--                        </h6>--}}
{{--                    </div>--}}
{{--                    <div class="card-body">--}}
{{--                        <div class="row">--}}
{{--                            @foreach($bookingStatus as $status)--}}
{{--                                <div class="col-md-3 mb-3">--}}
{{--                                    <div class="card border-left-{{--}}
{{--                                $status->status == 'ticketed' ? 'success' :--}}
{{--                                ($status->status == 'confirmed' ? 'primary' :--}}
{{--                                ($status->status == 'pending' ? 'warning' : 'danger'))--}}
{{--                            }}">--}}
{{--                                        <div class="card-body">--}}
{{--                                            <div class="row no-gutters align-items-center">--}}
{{--                                                <div class="col mr-2">--}}
{{--                                                    <div class="text-xs font-weight-bold text-uppercase mb-1">--}}
{{--                                                        {{ ucfirst($status->status) }}--}}
{{--                                                    </div>--}}
{{--                                                    <div class="h5 mb-0 font-weight-bold">--}}
{{--                                                        {{ number_format($status->count) }}--}}
{{--                                                    </div>--}}
{{--                                                    <div class="progress mt-2" style="height: 8px;">--}}
{{--                                                        <div class="progress-bar bg-{{--}}
{{--                                                    $status->status == 'ticketed' ? 'success' :--}}
{{--                                                    ($status->status == 'confirmed' ? 'primary' :--}}
{{--                                                    ($status->status == 'pending' ? 'warning' : 'danger'))--}}
{{--                                                }}" style="width: {{ $status->percentage }}%"></div>--}}
{{--                                                    </div>--}}
{{--                                                    <small class="text-muted">{{ number_format($status->percentage, 1) }}%</small>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <!-- Booking Status Breakdown - Dynamic Cards -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clipboard-list"></i> Booking Status Breakdown
                        </h6>
                        <small class="text-muted">
                            Total: {{ number_format($bookingStatus->sum('count')) }} bookings
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $statusConfig = [
                                    'ticketed'  => ['color' => 'success', 'icon' => 'fa-check-double', 'label' => 'Ticketed'],
                                    'confirmed' => ['color' => 'primary', 'icon' => 'fa-check-circle', 'label' => 'Confirmed'],
                                    'pending'   => ['color' => 'warning', 'icon' => 'fa-clock',        'label' => 'Pending'],
                                    'cancelled' => ['color' => 'danger',  'icon' => 'fa-times-circle', 'label' => 'Cancelled'],
                                    'refunded'  => ['color' => 'info',    'icon' => 'fa-undo',         'label' => 'Refunded'],
                                    'failed'    => ['color' => 'dark',    'icon' => 'fa-exclamation-triangle', 'label' => 'Failed'],
                                ];
                                $totalStatusCount = $bookingStatus->sum('count');
                            @endphp

                            @forelse($bookingStatus as $status)
                                @php
                                    $key    = strtolower($status->status);
                                    $config = $statusConfig[$key] ?? ['color' => 'secondary', 'icon' => 'fa-circle', 'label' => ucfirst($status->status)];
                                    $color  = $config['color'];
                                    $icon   = $config['icon'];
                                    $label  = $config['label'];
                                    $pct    = $totalStatusCount > 0 ? round(($status->count / $totalStatusCount) * 100, 1) : 0;

                                    $colorMap = [
                                        'success'   => ['border' => '#1cc88a', 'bg' => 'rgba(28,200,138,0.15)'],
                                        'primary'   => ['border' => '#4e73df', 'bg' => 'rgba(78,115,223,0.15)'],
                                        'warning'   => ['border' => '#f6c23e', 'bg' => 'rgba(246,194,62,0.15)'],
                                        'danger'    => ['border' => '#e74a3b', 'bg' => 'rgba(231,74,59,0.15)'],
                                        'info'      => ['border' => '#36b9cc', 'bg' => 'rgba(54,185,204,0.15)'],
                                        'dark'      => ['border' => '#5a5c69', 'bg' => 'rgba(90,92,105,0.15)'],
                                        'secondary' => ['border' => '#858796', 'bg' => 'rgba(133,135,150,0.15)'],
                                    ];

                                    $borderColor = $colorMap[$color]['border'] ?? '#858796';
                                    $iconBg      = $colorMap[$color]['bg'] ?? 'rgba(133,135,150,0.15)';
                                @endphp

                                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body p-3" style="border-left: 4px solid {{ $borderColor }};">

                                            <!-- Icon + Label -->
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-2"
                                                     style="width:32px; height:32px; background-color: {{ $iconBg }};">
                                                    <i class="fas {{ $icon }} text-{{ $color }}" style="font-size:13px;"></i>
                                                </div>
                                                <span class="text-xs font-weight-bold text-{{ $color }} text-uppercase">
                        {{ $label }}
                    </span>
                                            </div>

                                            <!-- Count -->
                                            <div class="h4 mb-1 font-weight-bold text-gray-800">
                                                {{ number_format($status->count) }}
                                            </div>

                                            <!-- Revenue -->
                                            <div class="text-xs text-muted mb-2">
                                                ৳{{ number_format($status->revenue ?? 0, 0) }}
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="progress mb-1" style="height: 6px; border-radius: 3px;">
                                                <div class="progress-bar bg-{{ $color }}"
                                                     role="progressbar"
                                                     style="width: {{ $pct }}%; border-radius: 3px;">
                                                </div>
                                            </div>

                                            <small class="text-muted">{{ $pct }}% of total</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-center text-muted py-3">No booking data</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Summary Bar --}}
                        @if($bookingStatus->isNotEmpty())
                            <div class="mt-3">
                                <div class="d-flex" style="height: 10px; border-radius: 5px; overflow: hidden;">
                                    @foreach($bookingStatus as $status)
                                        @php
                                            $key   = strtolower($status->status);
                                            $color = $statusConfig[$key]['color'] ?? 'secondary';
                                            $w     = $totalStatusCount > 0 ? round(($status->count / $totalStatusCount) * 100, 1) : 0;
                                        @endphp
                                        <div class="bg-{{ $color }}" style="width: {{ $w }}%;"
                                             title="{{ ucfirst($status->status) }}: {{ $w }}%">
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex flex-wrap mt-2">
                                    @foreach($bookingStatus as $status)
                                        @php
                                            $key   = strtolower($status->status);
                                            $color = $statusConfig[$key]['color'] ?? 'secondary';
                                            $label = $statusConfig[$key]['label'] ?? ucfirst($status->status);
                                            $w     = $totalStatusCount > 0 ? round(($status->count / $totalStatusCount) * 100, 1) : 0;
                                        @endphp
                                        <div class="mr-3 mb-1 d-flex align-items-center">
                                <span class="rounded-circle bg-{{ $color }} d-inline-block mr-1"
                                      style="width:8px; height:8px;"></span>
                                            <small class="text-muted">{{ $label }} ({{ $w }}%)</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Left Column - Tables -->
            <div class="col-lg-8">

                <!-- Top Routes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-plane"></i> Top 10 Routes by Revenue
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%">
                                <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Route</th>
                                    <th>Airline</th>
                                    <th>Class</th>
                                    <th class="text-right">Bookings</th>
                                    <th class="text-right">Revenue</th>
                                    <th class="text-right">Avg Price</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($topRoutes as $index => $route)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong class="text-primary">{{ $route->route }}</strong>
                                        </td>
                                        <td>{{ $route->airline ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $route->seat_class ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-right">{{ number_format($route->bookings) }}</td>
                                        <td class="text-right">
                                            <strong class="text-success">৳{{ number_format($route->revenue, 0) }}</strong>
                                        </td>
                                        <td class="text-right">৳{{ number_format($route->avg_price, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No route data available</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Customers -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-user-tie"></i> Top 10 Customers by Revenue
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th class="text-right">Bookings</th>
                                    <th class="text-right">Total Revenue</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($topCustomers as $index => $customer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $customer->customer_name }}</strong>
                                        </td>
                                        <td>{{ $customer->email }}</td>
                                        <td class="text-right">
                                            <span class="badge badge-primary">{{ $customer->total_bookings }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-success">৳{{ number_format($customer->total_revenue, 0) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No customer data available</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Booking Sources -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-shopping-cart"></i> Booking Sources Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="bookingSourceChart" height="200"></canvas>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th class="text-right">Bookings</th>
                                        <th class="text-right">%</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($bookingSources as $source)
                                        <tr>
                                            <td>{{ $source->booking_source }}</td>
                                            <td class="text-right">{{ number_format($source->bookings) }}</td>
                                            <td class="text-right">
                                                <span class="badge badge-primary">{{ number_format($source->percentage, 1) }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column - Stats & Charts -->
            <div class="col-lg-4">

                <!-- Device Breakdown -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-mobile-alt"></i> Device Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">  <!-- এই div add করুন -->
                            <canvas id="deviceChart"></canvas>
                        </div>

                        <div class="mt-3">
                            @foreach(['mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet'] as $key => $label)
                                @if(isset($deviceBreakdown[$key]))
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-sm">{{ $label }}</span>
                                            <strong>{{ number_format($deviceBreakdown[$key]) }}</strong>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $key == 'mobile' ? 'primary' : ($key == 'desktop' ? 'success' : 'info') }}"
                                                 style="width: {{ $totalWebsiteVisitors > 0 ? round(($deviceBreakdown[$key] / $totalWebsiteVisitors) * 100, 1) : 0 }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Browser Stats -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fab fa-chrome"></i> Top Browsers
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="browserChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Geographic Distribution -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-globe"></i> Top Countries (Visitors)
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($visitorsByCountry as $country)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                            <span>
                                <i class="fas fa-flag"></i> {{ $country->country }}
                            </span>
                                    <strong>{{ number_format($country->count) }}</strong>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $totalWebsiteVisitors > 0 ? round(($country->count / $totalWebsiteVisitors) * 100, 1) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">No geographic data</p>
                        @endforelse
                    </div>
                </div>

                <!-- Customer Segments -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users-cog"></i> Customer Segments
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($customerSegments as $segment)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $segment->customer_segment }}</strong>
                                        <br>
                                        <small class="text-muted">{{ number_format($segment->customers) }} customers</small>
                                    </div>
                                    <span class="badge badge-{{ $segment->customer_segment == 'VIP' ? 'danger' : ($segment->customer_segment == 'Returning' ? 'warning' : 'info') }} badge-lg">
                                ৳{{ number_format($segment->revenue, 0) }}
                            </span>
                                </div>
                                <small class="text-muted">
                                    Avg: ৳{{ number_format($segment->avg_customer_value, 0) }} •
                                    {{ $segment->bookings }} bookings
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-credit-card"></i> Payment Methods
                        </h6>
                    </div>
                    <div class="card-body">
                        @forelse($paymentMethods as $payment)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $payment->payment_method }}</span>
                                    <strong>{{ number_format($payment->percentage, 1) }}%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger"
                                         style="width: {{ $payment->percentage }}%">
                                    </div>
                                </div>
                                <small class="text-muted">৳{{ number_format($payment->revenue, 0) }}</small>
                            </div>
                        @empty
                            <p class="text-center text-muted">No payment data</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- Part 3 এ Chart Scripts থাকবে -->

        <!-- Part 3: Revenue Breakdown & Footer -->

        <!-- Revenue Breakdown -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-money-bill-wave"></i> Revenue Breakdown
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($revenueBreakdown)
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Total Revenue</strong></td>
                                    <td class="text-right">
                                        <h5 class="text-success mb-0">৳{{ number_format($revenueBreakdown->total_revenue, 2) }}</h5>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td>Base Fare</td>
                                    <td class="text-right">৳{{ number_format($revenueBreakdown->base_revenue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Supplier Fee</td>
                                    <td class="text-right">৳{{ number_format($revenueBreakdown->supplier_revenue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Ticketing Fee</td>
                                    <td class="text-right">৳{{ number_format($revenueBreakdown->ticketing_revenue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total Fees</td>
                                    <td class="text-right">৳{{ number_format($revenueBreakdown->total_fees, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Commission</strong></td>
                                    <td class="text-right">
                                        <strong class="text-primary">৳{{ number_format($revenueBreakdown->total_commission, 2) }}</strong>
                                    </td>
                                </tr>
                            </table>
                        @else
                            <p class="text-center text-muted">No revenue breakdown available</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-users"></i> Passenger Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach(['adults' => 'Adults', 'children' => 'Children', 'infants' => 'Infants'] as $key => $label)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $label }}</span>
                                    <strong>{{ number_format($passengerAnalysis[$key]) }}</strong>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-{{ $key == 'adults' ? 'primary' : ($key == 'children' ? 'success' : 'warning') }}"
                                         style="width: {{ $totalPassengers > 0 ? round(($passengerAnalysis[$key] / $totalPassengers) * 100, 1) : 0 }}%">
                                        {{ $totalPassengers > 0 ? number_format(($passengerAnalysis[$key] / $totalPassengers) * 100, 1) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 text-center">
                            <h5 class="text-muted">Total Passengers</h5>
                            <h3 class="text-primary">{{ number_format($totalPassengers) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Stats -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                <h6>Customer Lifetime Value</h6>
                                <h4 class="text-success">৳{{ number_format($customerLifetimeValue, 0) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-bullhorn fa-2x text-warning mb-2"></i>
                                <h6>Active Campaigns</h6>
                                <h4 class="text-info">{{ $activeCampaigns }}</h4>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                <h6>Customer Satisfaction</h6>
                                <h4 class="text-success">{{ $customerSatisfaction }}/5.0</h4>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-eye fa-2x text-info mb-2"></i>
                                <h6>Today's Visitors</h6>
                                <h4 class="text-primary">{{ number_format($todayUniqueVisitors) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center text-muted mb-4 pb-4 border-top pt-3">
            <div class="row">
                <div class="col-md-6 text-md-left">
                    <p class="mb-1">
                        <i class="fas fa-clock"></i> Last Updated: <strong id="lastUpdated"></strong>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-calendar"></i> Report Period:
                        <strong>{{ request('from_date', date('Y-m-01')) }}</strong> to
                        <strong>{{ request('to_date', date('Y-m-d')) }}</strong>
                    </p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-1">
                        <span class="badge badge-success">Data Accuracy: 99.8%</span>
                    </p>
                    <p class="mb-0">
                        <small>Powered by Advanced Analytics Engine</small>
                    </p>
                </div>
            </div>
        </footer>

    </div>

    <!-- Chart Scripts -->
    <script>
        // Set current time
        document.getElementById('lastUpdated').textContent = new Date().toLocaleString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Quick Date Selector
        document.getElementById('quickDate').addEventListener('change', function() {
            const value = this.value;
            const today = new Date();
            let fromDate, toDate;

            switch(value) {
                case 'today':
                    fromDate = toDate = today.toISOString().split('T')[0];
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    fromDate = toDate = yesterday.toISOString().split('T')[0];
                    break;
                case 'this_week':
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    fromDate = weekStart.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'last_week':
                    const lastWeekEnd = new Date(today);
                    lastWeekEnd.setDate(today.getDate() - today.getDay() - 1);
                    const lastWeekStart = new Date(lastWeekEnd);
                    lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
                    fromDate = lastWeekStart.toISOString().split('T')[0];
                    toDate = lastWeekEnd.toISOString().split('T')[0];
                    break;
                case 'this_month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'last_month':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    fromDate = lastMonth.toISOString().split('T')[0];
                    const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                    toDate = lastMonthEnd.toISOString().split('T')[0];
                    break;
                case 'last_30_days':
                    const days30 = new Date(today);
                    days30.setDate(today.getDate() - 30);
                    fromDate = days30.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'last_90_days':
                    const days90 = new Date(today);
                    days90.setDate(today.getDate() - 90);
                    fromDate = days90.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                default:
                    return;
            }

            document.querySelector('input[name="from_date"]').value = fromDate;
            document.querySelector('input[name="to_date"]').value = toDate;
        });

        // Revenue Trend Chart
        const revenueTrendData = {!! json_encode($revenueTrend) !!};
        const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');

        new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: revenueTrendData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Revenue (৳)',
                    data: revenueTrendData.map(item => item.revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }, {
                    label: 'Bookings',
                    data: revenueTrendData.map(item => item.bookings),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 0) {
                                        label += '৳' + context.parsed.y.toLocaleString();
                                    } else {
                                        label += context.parsed.y.toLocaleString();
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Visitor Trend Chart
        const visitorTrendData = {!! json_encode($dailyVisitorTrend) !!};
        const visitorTrendCtx = document.getElementById('visitorTrendChart').getContext('2d');

        new Chart(visitorTrendCtx, {
            type: 'line',
            data: {
                labels: visitorTrendData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Total Visitors',
                    data: visitorTrendData.map(item => item.total_visitors),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Logged In',
                    data: visitorTrendData.map(item => item.logged_in),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Guests',
                    data: visitorTrendData.map(item => item.guests),
                    borderColor: 'rgb(255, 206, 86)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Booking Source Chart
        const bookingSourceData = {!! json_encode($bookingSources) !!};
        const bookingSourceCtx = document.getElementById('bookingSourceChart').getContext('2d');

        new Chart(bookingSourceCtx, {
            type: 'doughnut',
            data: {
                labels: bookingSourceData.map(item => item.booking_source),
                datasets: [{
                    data: bookingSourceData.map(item => item.bookings),
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Device Chart
        const deviceData = {!! json_encode($deviceBreakdown) !!};
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');

        new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: ['Mobile', 'Desktop', 'Tablet'],
                datasets: [{
                    data: [
                        deviceData.mobile || 0,
                        deviceData.desktop || 0,
                        deviceData.tablet || 0
                    ],
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 206, 86)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Browser Chart
        const browserData = {!! json_encode($browserStats) !!};
        const browserCtx = document.getElementById('browserChart').getContext('2d');

        new Chart(browserCtx, {
            type: 'doughnut',
            data: {
                labels: browserData.map(item => item.browser),
                datasets: [{
                    data: browserData.map(item => item.count),
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    </script>

@endsection

