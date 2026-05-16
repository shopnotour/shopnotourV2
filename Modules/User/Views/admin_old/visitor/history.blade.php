@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-history"></i> Visitor History
                        </h2>
                        <p class="text-muted">Complete visitor activity log</p>
                    </div>
                    <div>
                        <a href="{{ route('visitor.index') }}" class="btn btn-success">
                            <i class="fas fa-circle blink"></i> Live Visitors
                        </a>
                        <a href="{{ route('visitor.statistics') }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('visitor.history') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control"
                                       value="{{ request('from_date', date('Y-m-d', strtotime('-7 days'))) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control"
                                       value="{{ request('to_date', date('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>User Type</label>
                                <select name="user_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="logged_in" {{ request('user_type') == 'logged_in' ? 'selected' : '' }}>Logged In</option>
                                    <option value="guest"     {{ request('user_type') == 'guest'     ? 'selected' : '' }}>Guest</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Country</label>
                                <select name="country" class="form-control">
                                    <option value="">All Countries</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- নতুন: Activity filter --}}
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Has Activity</label>
                                <select name="has_activity" class="form-control">
                                    <option value="">All</option>
                                    <option value="flight_search" {{ request('has_activity') == 'flight_search' ? 'selected' : '' }}>Flight Search</option>
                                    <option value="search"        {{ request('has_activity') == 'search'        ? 'selected' : '' }}>Search</option>
                                    <option value="form_submit"   {{ request('has_activity') == 'form_submit'   ? 'selected' : '' }}>Form Submit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Visitor History Table -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Visitor Records ({{ $visitors->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            @if(auth()->user()->hasPermission('visitor_ip'))
                                <th>IP Address</th>
                            @endif
                            @if(auth()->user()->hasPermission('visitor_country'))
                                <th>Location</th>
                            @endif
                            <th>Device</th>
                            <th>Browser</th>
                            <th>Visited At</th>
                            <th>Duration</th>
                            <th>Pages</th>
                            <th>Activities</th>  {{-- নতুন --}}
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($visitors as $visitor)
                            <tr>
                                <td>{{ $visitor->id }}</td>
                                <td>
                                    @if($visitor->user)
                                        <strong class="text-primary">
                                            <i class="fas fa-user"></i> {{ $visitor->user->name }}
                                        </strong>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-user-secret"></i> Guest
                                        </span>
                                    @endif
                                </td>
                                @if(auth()->user()->hasPermission('visitor_ip'))
                                <td>
                                    @if(auth()->user()->hasPermission('visitor_ip'))
                                        <code>{{ $visitor->ip_address }}</code>
                                    @else
                                        <code>***.***.***.***</code>
                                    @endif
                                </td>
                                @endif
                                @if(auth()->user()->hasPermission('visitor_country'))
                                    <td>
                                        @if($visitor->country)
                                            <i class="fas fa-flag"></i>
                                            @if(auth()->user()->hasPermission('visitor_country'))
                                                {{ $visitor->city }}, {{ $visitor->country }}
                                            @else
                                                <code>***.***.***.***</code>
                                            @endif
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
                                </td>
                                <td><small>{{ $visitor->browser }}</small></td>
                                <td>
                                    {{ $visitor->visited_at->format('d M Y') }}<br>
                                    <small class="text-muted">{{ $visitor->visited_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($visitor->duration > 0)
                                        @php
                                            $minutes = floor($visitor->duration / 60);
                                            $seconds = $visitor->duration % 60;
                                        @endphp
                                        <span class="badge badge-info">{{ $minutes }}m {{ $seconds }}s</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $visitor->page_views }}</span>
                                </td>

                                {{-- নতুন: Activity badges --}}
                                <td>
                                    @if($visitor->activities && $visitor->activities->count() > 0)
                                        @if($visitor->activities->where('activity_type', 'flight_search')->count() > 0)
                                            <span class="badge badge-primary" title="Flight Searches">
                                                <i class="fas fa-plane"></i>
                                                {{ $visitor->activities->where('activity_type', 'flight_search')->count() }}
                                            </span>
                                        @endif
                                        @if($visitor->activities->where('activity_type', 'search')->count() > 0)
                                            <span class="badge badge-info" title="Searches">
                                                <i class="fas fa-search"></i>
                                                {{ $visitor->activities->where('activity_type', 'search')->count() }}
                                            </span>
                                        @endif
                                        @if($visitor->activities->where('activity_type', 'click')->count() > 0)
                                            <span class="badge badge-secondary" title="Clicks">
                                                <i class="fas fa-mouse-pointer"></i>
                                                {{ $visitor->activities->where('activity_type', 'click')->count() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($visitor->is_online)
                                        <span class="badge badge-success"><i class="fas fa-circle"></i> Online</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-circle"></i> Offline</span>
                                    @endif
                                </td>
                                <td>
{{--                                    <a href="{{ route('visitor.show', $visitor->id) }}"--}}
{{--                                       class="btn btn-sm btn-primary" title="View Details">--}}
{{--                                        <i class="fas fa-eye"></i>--}}
{{--                                    </a>--}}

                                    <a href="{{ route('visitor.show', ['id' => $visitor->id, 'user_id' => $visitor->user_id ?? null]) }}"
                                       class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-2"></i>
                                    <p>No visitor records found</p>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($visitors->hasPages())
                <div class="card-footer">{{ $visitors->links() }}</div>
            @endif
        </div>

        <!-- Cleanup Section -->
        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-trash"></i> Database Cleanup</h5>
            </div>
            <div class="card-body">
                <p>Remove old visitor records to keep database clean and fast.</p>
                <form action="{{ route('visitor.cleanup') }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete old records?')">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Delete records older than:</label>
                                <select name="days" class="form-control">
                                    <option value="30">30 Days</option>
                                    <option value="60">60 Days</option>
                                    <option value="90">90 Days</option>
                                    <option value="180">180 Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash"></i> Clean Up
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .blink { animation: blink-animation 1.5s infinite; }
        @keyframes blink-animation {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.5; }
        }
    </style>
@endsection
