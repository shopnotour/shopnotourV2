@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                {{-- Page Header --}}
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-{{ $pageTitle === 'Issued Tickets' ? 'ticket-alt' : 'list' }} text-primary"></i>
                    {{ $pageTitle ?? 'Bookings' }}
                </h1>

{{--                --}}{{-- Breadcrumb --}}
{{--                ... <strong>{{ $pageTitle ?? 'Bookings' }}</strong>--}}

{{--                --}}{{-- Table header --}}
{{--                <h6 class="m-0 font-weight-bold text-primary">--}}
{{--                    {{ $pageTitle ?? 'All Bookings' }}--}}
{{--                </h6>--}}
                <p class="text-muted mb-0">
                    <a href="{{ route('admin.users.index') }}" class="text-muted">Users</a>
                    <i class="fas fa-chevron-right mx-1" style="font-size:10px"></i>
                    <strong>{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}</strong>
                    <i class="fas fa-chevron-right mx-1" style="font-size:10px"></i>
                    Bookings
                </p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        @include('admin.message')

        {{-- User Info Card --}}
        <div class="card shadow mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-size:18px;font-weight:700;">
                            {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="font-weight-bold text-gray-800" style="font-size:15px;">
                            {{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) }}
                        </div>
                        <div class="text-muted small">{{ $user->email ?? '' }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Total Bookings</div>
                        <div class="h4 mb-0 font-weight-bold text-primary">{{ $bookings->count() }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Total Spent</div>
                        <div class="h4 mb-0 font-weight-bold text-success">৳{{ number_format($bookings->sum('total'), 2) }}</div>
                    </div>
                    <div class="col-auto text-right">
                        <div class="text-xs text-muted text-uppercase font-weight-bold mb-1">Issued</div>
                        <div class="h4 mb-0 font-weight-bold text-info">{{ $bookings->where('status', 'issued')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bookings Table --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list mr-1"></i> All Bookings
                </h6>
            </div>
            <div class="card-body">
                @if($bookings->count() > 0)
                    <div class="table-responsive">
                        <table id="bookingsTable" class="table table-bordered table-hover" width="100%">
                            <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Booking Code</th>
                                <th>Route</th>
                                <th>Airline</th>
                                <th width="80">Pax</th>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Travel Date</th>
                                <th>Total</th>
                                <th width="110">Status</th>
                                <th>Booked At</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($bookings as $i => $booking)
                                @php
                                    $statusConfig = match($booking->status) {
                                        'issued'      => ['badge-success',  'Issued'],
                                        'booked'      => ['badge-primary',  'Booked'],
                                        'pnr_pending' => ['badge-warning',  'PNR Pending'],
                                        'pending'     => ['badge-warning',  'Pending'],
                                        'cancelled'   => ['badge-danger',   'Cancelled'],
                                        'refunded'    => ['badge-info',     'Refunded'],
                                        default       => ['badge-secondary', ucfirst($booking->status ?? '—')],
                                    };
                                    $typeLabel = match($booking->flight_type ?? '') {
                                        'one_way'    => ['badge-light text-dark border', 'One Way'],
                                        'round_trip' => ['badge-light text-dark border', 'Round Trip'],
                                        default      => ['badge-secondary', ucfirst($booking->flight_type ?? '—')],
                                    };
                                    $paxTotal = (int)($booking->adult_count ?? 0)
                                              + (int)($booking->child_count ?? 0)
                                              + (int)($booking->infant_count ?? 0);
                                @endphp
                                <tr>
                                    {{-- # --}}
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>

                                    {{-- Booking Code --}}
                                    <td>
                                        <a href="{{ route('admin.users.booking.detail', [$user->id, $booking->id]) }}"
                                           class="font-weight-bold text-primary"
                                           style="font-family:monospace;letter-spacing:.05em;">
                                            {{ $booking->code }}
                                        </a>
                                        @if(!empty($booking->pnr_id))
                                            <br>
                                            <small class="text-muted font-weight-bold" style="font-family:monospace;">
                                                PNR: {{ $booking->pnr_id }}
                                            </small>
                                        @endif
                                        @if(!empty($booking->ticket_number))
                                            @php
                                                $tickets = is_string($booking->ticket_number)
                                                    ? json_decode($booking->ticket_number, true)
                                                    : $booking->ticket_number;
                                            @endphp
                                            @if(!empty($tickets))
                                                <br>
                                                <small class="text-success" style="font-size:10px;">
                                                    <i class="fas fa-ticket-alt"></i>
                                                    {{ implode(', ', (array)$tickets) }}
                                                </small>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Route --}}
                                    <td>
                                        <span class="font-weight-bold text-gray-800">
                                            {{ $booking->flight_from ?? '—' }}
                                        </span>
                                        <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px"></i>
                                        <span class="font-weight-bold text-gray-800">
                                            {{ $booking->flight_to ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Airline --}}
                                    <td class="text-muted small">{{ $booking->airline ?? '—' }}</td>

                                    {{-- Pax --}}
                                    <td class="text-center">
                                        <span class="font-weight-bold">{{ $paxTotal }}</span>
                                        <br>
                                        <small class="text-muted" style="font-size:10px;">
                                            @if($booking->adult_count > 0) {{ $booking->adult_count }}A @endif
                                            @if($booking->child_count > 0) {{ $booking->child_count }}C @endif
                                            @if($booking->infant_count > 0) {{ $booking->infant_count }}I @endif
                                        </small>
                                    </td>

                                    {{-- Source --}}
                                    <td>
                                        <span class="badge badge-light text-dark border" style="font-size:11px;">
                                            {{ strtoupper($booking->source ?? '—') }}
                                        </span>
                                    </td>

                                    {{-- Type --}}
                                    <td>
                                        <span class="badge {{ $typeLabel[0] }}" style="font-size:11px;">
                                            {{ $typeLabel[1] }}
                                        </span>
                                    </td>

                                    {{-- Travel Date --}}
                                    <td class="small text-muted">
                                        {{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('d M Y') : '—' }}
                                        @if($booking->end_date && $booking->end_date != $booking->start_date)
                                            <br>
                                            <span style="font-size:10px;">
                                                → {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Total --}}
                                    <td>
                                        <div class="font-weight-bold text-gray-800">
                                            {{ $booking->currency ?? 'BDT' }} {{ number_format($booking->total ?? 0, 2) }}
                                        </div>
                                        @if(!empty($booking->base_fee))
                                            <small class="text-muted" style="font-size:10px;">
                                                Base: {{ number_format($booking->base_fee, 2) }}
                                            </small>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        <span class="badge {{ $statusConfig[0] }}">
                                            {{ $statusConfig[1] }}
                                        </span>
                                        @if(!empty($booking->is_paid) && $booking->is_paid)
                                            <br>
                                            <span class="badge badge-success mt-1" style="font-size:10px;">
                                                <i class="fas fa-check"></i> Paid
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Booked At --}}
                                    <td class="small text-muted">
                                        {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('d M Y') : '—' }}
                                        <br>
                                        <span style="font-size:10px;">
                                            {{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('h:i A') : '' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No bookings found for this user.</p>
                    </div>
                @endif
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
            $('#bookingsTable').DataTable({
                pageLength: 25,
                order: [[10, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search bookings...',
                    lengthMenu: 'Show _MENU_ bookings',
                    info: 'Showing _START_ to _END_ of _TOTAL_ bookings',
                    paginate: {
                        previous: '&laquo; Prev',
                        next: 'Next &raquo;',
                    }
                },
            });
        });
    </script>
@endpush
