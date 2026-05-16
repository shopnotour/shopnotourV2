@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-users"></i> Real-Time Visitor Tracking
                        </h2>
                        <p class="text-muted">Monitor your website visitors in real-time</p>
                    </div>
                    <div>
                        <a href="{{ route('visitor.history') }}" class="btn btn-info">
                            <i class="fas fa-history"></i> Visit History
                        </a>
                        <a href="{{ route('visitor.statistics') }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-Time Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-circle blink"></i> Online Now
                                </h6>
                                <h2 class="mb-0" id="total-online">{{ $stats['total_online'] }}</h2>
                                <small>Active Visitors</small>
                            </div>
                            <div><i class="fas fa-users fa-3x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-user-check"></i> Logged In
                                </h6>
                                <h2 class="mb-0" id="online-users">{{ $stats['online_users'] }}</h2>
                                <small>Registered Users</small>
                            </div>
                            <div><i class="fas fa-user-tie fa-3x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-user-secret"></i> Guests
                                </h6>
                                <h2 class="mb-0" id="online-guests">{{ $stats['online_guests'] }}</h2>
                                <small>Anonymous Users</small>
                            </div>
                            <div><i class="fas fa-user-ninja fa-3x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">
                                    <i class="fas fa-calendar-day"></i> Today's Total
                                </h6>
                                <h2 class="mb-0">{{ $stats['today_total'] }}</h2>
                                <small>{{ $stats['today_unique'] }} Unique</small>
                            </div>
                            <div><i class="fas fa-chart-line fa-3x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Online Visitors Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-circle text-success blink"></i>
                            Online Visitors (<span id="visitor-count">{{ count($onlineVisitors) }}</span>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-sm mb-0" id="online-visitors-table">
                                <thead class="thead-light sticky-top">
                                <tr>
                                    <th>User</th>
                                    @if(auth()->user()->hasPermission('visitor_country'))
                                        <th>Location</th>
                                    @endif
                                    <th>Device</th>
                                    <th>Current Page</th>
                                    <th>Time on Page</th>  {{-- নতুন --}}
                                    <th>Duration</th>
                                    <th>Activity</th>      {{-- নতুন --}}
                                    <th>Action</th>        {{-- নতুন --}}
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($onlineVisitors as $visitor)
                                    <tr>
                                        <td>
                                            @if($visitor->user)
                                                <strong class="text-primary">
                                                    <i class="fas fa-user"></i> {{ $visitor->user->name }}
                                                </strong>
                                                <br>
                                                @if(auth()->user()->hasPermission('visitor_country'))
                                                    <small class="text-muted">{{ $visitor->ip_address }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-user-secret"></i> Guest
                                                </span>
                                                <br>
                                                @if(auth()->user()->hasPermission('visitor_country'))
                                                    <small class="text-muted">{{ $visitor->ip_address }}</small>
                                                @endif

                                            @endif
                                        </td>
                                        @if(auth()->user()->hasPermission('visitor_country'))
                                            <td>
                                                @if($visitor->country)
                                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                                    {{ $visitor->city }}, {{ $visitor->country }}
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </td>
                                        @endif

                                        <td>
                                            @if($visitor->device_type == 'mobile')
                                                <i class="fas fa-mobile-alt text-primary"></i>
                                            @elseif($visitor->device_type == 'tablet')
                                                <i class="fas fa-tablet-alt text-info"></i>
                                            @else
                                                <i class="fas fa-desktop text-success"></i>
                                            @endif
                                            {{ ucfirst($visitor->device_type) }}
                                            <br>
                                            <small class="text-muted">{{ $visitor->browser }}</small>
                                        </td>
                                        <td>
                                            <small title="{{ $visitor->current_page }}">
                                                {{ \Str::limit(parse_url($visitor->current_page, PHP_URL_PATH), 30) }}
                                            </small>
                                        </td>

                                        {{-- নতুন: current page এ কতক্ষণ --}}
                                        <td>
                                            @php
                                                $currentPageLog = $visitor->pageLog ? $visitor->pageLog->sortByDesc('entered_at')->first() : null;
                                            @endphp
                                            @if($currentPageLog)
                                                @php
                                                    $timeOnPage = now()->diffInSeconds($currentPageLog->entered_at);
                                                    $m = floor($timeOnPage / 60);
                                                    $s = $timeOnPage % 60;
                                                @endphp
                                                <span class="badge badge-warning">
                                                    @if($m > 0){{ $m }}m @endif{{ $s }}s
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-info">
                                                {{ $visitor->visited_at->diffInMinutes($visitor->last_activity_at) }} min
                                            </span>
                                        </td>

                                        {{-- নতুন: শেষ activity কী ছিল --}}
                                        <td>
                                            @php
                                                $lastActivity = $visitor->activities ? $visitor->activities->sortByDesc('occurred_at')->first() : null;
                                            @endphp
                                            @if($lastActivity)
                                                @if($lastActivity->activity_type == 'flight_search')
                                                    <span class="badge badge-primary">
                                                        <i class="fas fa-plane"></i> Flight Search
                                                    </span>
                                                @elseif($lastActivity->activity_type == 'search')
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-search"></i> Search
                                                    </span>
                                                @elseif($lastActivity->activity_type == 'click')
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-mouse-pointer"></i> Click
                                                    </span>
                                                @else
                                                    <span class="badge badge-light border">
                                                        {{ ucfirst(str_replace('_', ' ', $lastActivity->activity_type)) }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- নতুন: Details button --}}
                                        <td>
                                            <a href="{{ route('visitor.show', $visitor->id) }}"
                                               class="btn btn-xs btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-2"></i>
                                            <p>No online visitors at the moment</p>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- নতুন: Hourly Chart -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Today's Hourly Visitors
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="hourlyChart" height="80"></canvas>
                    </div>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="col-md-4">
                <!-- Location Stats -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-globe"></i> Visitors by Location
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @forelse($locationStats as $location)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-flag"></i> {{ $location->country }}</span>
                                    <span class="badge badge-primary badge-pill">{{ $location->total }}</span>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted">No location data</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Device Stats -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-mobile-alt"></i> Devices
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($deviceStats as $device)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ ucfirst($device->device_type) }}</span>
                                    <strong>{{ $device->total }}</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $stats['total_online'] > 0 ? ($device->total / $stats['total_online']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- নতুন: Top Pages Today -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-fire"></i> Top Pages Today
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                            @forelse($topPages as $page)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <small title="{{ $page->current_page }}">
                                        {{ \Str::limit(parse_url($page->current_page, PHP_URL_PATH) ?: $page->current_page, 30) }}
                                    </small>
                                    <span class="badge badge-info badge-pill">{{ $page->visits }}</span>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted">No data</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- নতুন: Recent Flight Searches -->
                @php
                    use Modules\User\Models\VisitorActivity;
                    $recentFlightSearches = VisitorActivity::where('activity_type', 'flight_search')
                        ->whereDate('created_at', today())
                        ->orderBy('occurred_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                @if($recentFlightSearches->count() > 0)
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-plane"></i> Recent Flight Searches
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($recentFlightSearches as $fs)
                                    @php $d = $fs->activity_data['flight_search'] ?? []; @endphp
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>{{ $d['from'] ?? $d['origin'] ?? '?' }}</strong>
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                <strong>{{ $d['to'] ?? $d['destination'] ?? '?' }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $fs->occurred_at->format('h:i A') }}</small>
                                        </div>
                                        @if(isset($d['departure_date']) || isset($d['depart_date']))
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                {{ $d['departure_date'] ?? $d['depart_date'] }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .blink { animation: blink-animation 1.5s infinite; }
        @keyframes blink-animation {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
        .opacity-50 { opacity: 0.5; }
        .sticky-top { position: sticky; top: 0; z-index: 10; background: #f8f9fa; }
        .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Hourly Chart
        const hourlyData = {!! json_encode($hourlyStats) !!};
        const hours = Array.from({length: 24}, (_, i) => i + ':00');
        const counts = Array(24).fill(0);
        hourlyData.forEach(h => { counts[h.hour] = h.total; });

        new Chart(document.getElementById('hourlyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Visitors',
                    data: counts,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Auto-refresh every 10 seconds
        setInterval(refreshVisitorData, 10000);

        function refreshVisitorData() {
            fetch('{{ route("visitor.realtime") }}')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('total-online').textContent  = data.stats.total_online;
                    document.getElementById('online-users').textContent  = data.stats.online_users;
                    document.getElementById('online-guests').textContent = data.stats.online_guests;
                    document.getElementById('visitor-count').textContent = data.visitors.length;
                    updateVisitorTable(data.visitors);
                })
                .catch(e => console.error('Error:', e));
        }

        function updateVisitorTable(visitors) {
            const tbody = document.querySelector('#online-visitors-table tbody');
            if (visitors.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-2"></i><p>No online visitors at the moment</p></td></tr>`;
                return;
            }
            tbody.innerHTML = visitors.map(v => `
                <tr>
                    <td>
                        ${v.user_name !== 'Guest'
                ? `<strong class="text-primary"><i class="fas fa-user"></i> ${v.user_name}</strong>`
                : `<span class="text-muted"><i class="fas fa-user-secret"></i> Guest</span>`}
                        <br><small class="text-muted">${v.ip_address}</small>
                    </td>
                    <td>${v.country ? `<i class="fas fa-map-marker-alt text-danger"></i> ${v.city}, ${v.country}` : '<span class="text-muted">Unknown</span>'}</td>
                    <td>
                        <i class="fas fa-${v.device_type === 'mobile' ? 'mobile-alt' : 'desktop'}"></i> ${v.device_type}
                        <br><small class="text-muted">${v.browser}</small>
                    </td>
                    <td><small>${v.current_page}</small></td>
                    <td><span class="badge badge-warning">${v.time_on_page ?? '-'}</span></td>
                    <td><span class="badge badge-info">${v.duration}</span></td>
                    <td>${v.last_activity_type
                ? `<span class="badge badge-secondary">${v.last_activity_type}</span>`
                : '<span class="text-muted">-</span>'}</td>
                    <td><a href="/admin/visitor/${v.id}" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
            `).join('');
        }
    </script>
@endsection
