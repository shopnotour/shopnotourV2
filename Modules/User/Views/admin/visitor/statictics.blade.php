@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Visitor Statistics
                        </h2>
                        <p class="text-muted">Last 30 days analytics and insights</p>
                    </div>
                    <div>
                        <a href="{{ route('visitor.index') }}" class="btn btn-success">
                            <i class="fas fa-circle blink"></i> Live Visitors
                        </a>
                        <a href="{{ route('visitor.history') }}" class="btn btn-info">
                            <i class="fas fa-history"></i> History
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Visitors Chart -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Daily Visitors (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="dailyVisitorsChart" height="80"></canvas>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Top Countries -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-globe"></i> Top 10 Countries
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Country</th>
                                    <th>Code</th>
                                    <th class="text-right">Visitors</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($topCountries as $index => $country)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <i class="fas fa-flag"></i> {{ $country->country }}
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $country->country_code }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong>{{ number_format($country->total) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No country data available
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Browser Statistics -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-browser"></i> Browser Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="browserChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Device Statistics -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-mobile-alt"></i> Device Breakdown
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="deviceChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Platform Statistics -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-laptop"></i> Operating Systems
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="platformChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Daily Breakdown
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Total Visitors</th>
                            <th class="text-right">Logged In Users</th>
                            <th class="text-right">Guests</th>
                            <th class="text-right">Unique IPs</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($dailyVisitors as $day)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($day->date)->format('l') }}</small>
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-primary badge-lg">{{ number_format($day->total) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-success">{{ number_format($day->logged_in) }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-warning">{{ number_format($day->guests) }}</span>
                                </td>
                                <td class="text-right">
                                    {{ number_format($day->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No statistics data available
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .blink {
            animation: blink-animation 1.5s infinite;
        }

        @keyframes blink-animation {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }
    </style>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        // Daily Visitors Chart
        const dailyCtx = document.getElementById('dailyVisitorsChart').getContext('2d');
        const dailyData = {!! json_encode($dailyVisitors) !!};

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Total Visitors',
                    data: dailyData.map(d => d.total),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Logged In Users',
                    data: dailyData.map(d => d.logged_in),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Guests',
                    data: dailyData.map(d => d.guests),
                    borderColor: 'rgb(255, 206, 86)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Browser Chart
        const browserCtx = document.getElementById('browserChart').getContext('2d');
        const browserData = {!! json_encode($browserStats) !!};

        new Chart(browserCtx, {
            type: 'doughnut',
            data: {
                labels: browserData.map(b => b.browser),
                datasets: [{
                    data: browserData.map(b => b.total),
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
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Device Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceData = {!! json_encode($deviceStats) !!};

        new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: deviceData.map(d => d.device_type.charAt(0).toUpperCase() + d.device_type.slice(1)),
                datasets: [{
                    data: deviceData.map(d => d.total),
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Platform Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformData = {!! json_encode($platformStats) !!};

        new Chart(platformCtx, {
            type: 'bar',
            data: {
                labels: platformData.map(p => p.platform),
                datasets: [{
                    label: 'Visitors',
                    data: platformData.map(p => p.total),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
