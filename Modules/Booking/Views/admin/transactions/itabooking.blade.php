@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid px-4 py-3">

        {{-- PAGE HEADER --}}
        <div class="d-flex align-items-center justify-content-between mb-3 no-print-actions">
            <div>
                <h2 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-plane me-2 text-primary"></i>IATA Report
                </h2>
                <p class="text-muted mb-0 small">Passenger wise booking details</p>
            </div>
        </div>

        {{-- FILTER --}}
        {{-- FILTER --}}
        {{-- FILTER --}}
        <div class="card border-0 shadow-sm mb-4 no-print-actions">
            <div class="card-body py-3">
                <form action="{{ route('admin.itabooking-report.index') }}" method="GET" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">From</label>
                            <input type="date" name="start_date" id="startDate"
                                   class="form-control form-control-sm" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">To</label>
                            <input type="date" name="end_date" id="endDate"
                                   class="form-control form-control-sm" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Source</label>
                            <select name="source" class="form-select form-select-sm">
                                <option value="">All Sources</option>
                                @foreach($sources as $src)
                                    <option value="{{ $src }}" {{ $source == $src ? 'selected' : '' }}>
                                        {{ ucfirst($src) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">User</label>
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">All Users</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                                        {{ trim($u->first_name . ' ' . $u->last_name) }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                                <a href="{{ route('admin.itabooking-report.index') }}"
                                   class="btn btn-outline-secondary btn-sm px-3">
                                    <i class="fas fa-rotate-left me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-top d-flex align-items-center gap-3 flex-wrap">
                        <small class="text-muted">
                            <i class="fas fa-calendar-check me-1 text-primary"></i>
                            <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                            — <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                        </small>
                        <span class="badge bg-primary rounded-pill">
                    <i class="fas fa-users me-1"></i>{{ $passengers->count() }} passengers
                </span>
                        @if($source)
                            <span class="badge bg-secondary rounded-pill">
                        <i class="fas fa-filter me-1"></i>Source: {{ ucfirst($source) }}
                    </span>
                        @endif
                        @if($userId)
                            @php $selUser = $users->find($userId); @endphp
                            @if($selUser)
                                <span class="badge bg-info text-dark rounded-pill">
                            <i class="fas fa-user me-1"></i>{{ trim($selUser->first_name . ' ' . $selUser->last_name) }}
                        </span>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-table me-2 text-primary"></i>
                    IATA Report
                    <span class="badge bg-secondary ms-1">{{ $passengers->count() }}</span>
                </h6>
                <span class="text-muted small">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>

            <div class="card-body p-2">
                <table class="table table-bordered table-hover table-sm mb-0" id="reportTable" style="width:100%">
                    <thead>
                    <tr style="font-size:.68rem;text-align:center;">
                        <th rowspan="2" class="align-middle text-center" style="background:#1e3a5f;color:#fff;min-width:32px">#</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:130px">User</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:100px">Ticketed By</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:120px">Passenger</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:50px">Type</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:60px">Source</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:70px">Airline</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:90px">Route</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:80px">Departure</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:70px">PNR</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:100px">Ticket No</th>
                        <th rowspan="2" class="align-middle" style="background:#1e3a5f;color:#fff;min-width:80px">Issued</th>
                        <th rowspan="2" class="align-middle text-center" style="background:#1e3a5f;color:#fff;min-width:80px">Bk Status</th>
                        <th rowspan="2" class="align-middle text-center" style="background:#1e3a5f;color:#fff;min-width:80px">Pax Status</th>
                        <th colspan="5" style="background:#154360;color:#fff;border-bottom:2px solid #85c1e9;">Fare & Charges</th>
                        <th colspan="3" style="background:#1d6a3e;color:#fff;border-bottom:2px solid #a9dfbf;">Segment</th>
                        <th colspan="2" style="background:#6e2f8a;color:#fff;border-bottom:2px solid #d7bde2;">Discount</th>
                        <th rowspan="2" class="align-middle text-center" style="background:#1a5276;color:#fff;min-width:95px">Receivable</th>
                        <th rowspan="2" class="align-middle text-center" style="background:#7d3c17;color:#fff;min-width:95px">Cost</th>
                        <th rowspan="2" class="align-middle text-center" style="background:#145a32;color:#fff;min-width:95px">Profit</th>
                    </tr>
                    <tr style="font-size:.68rem;text-align:center;">
                        <th style="background:#1a5276;color:#fff;min-width:90px">Base</th>
                        <th style="background:#1a5276;color:#fff;min-width:90px">Tax</th>
                        <th style="background:#1a5276;color:#fff;min-width:90px">Gross</th>
                        <th style="background:#1a5276;color:#fff;min-width:90px">AIT</th>
                        <th style="background:#1a5276;color:#fff;min-width:90px">Svc Charge</th>
                        <th style="background:#1d6a3e;color:#fff;min-width:50px">Count</th>
                        <th style="background:#1d6a3e;color:#fff;min-width:90px">Seg Disc</th>
                        <th style="background:#1d6a3e;color:#fff;min-width:90px">Own Seg Disc</th>
                        <th style="background:#6e2f8a;color:#fff;min-width:90px">User Disc</th>
                        <th style="background:#6e2f8a;color:#fff;min-width:90px">Own Disc</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($passengers as $pax)
                        @php
                            $segCount = $routeCounts[$pax->booking_id] ?? 0;
                            $profit   = $pax->profit ?? 0;
                            $sc = match(strtolower($pax->booking_status ?? '')) {
                                'confirmed','completed','success' => 'success',
                                'pending','processing'           => 'warning',
                                'cancelled','failed'             => 'danger',
                                default                          => 'secondary',
                            };
                            $pc = match(strtolower($pax->passenger_status ?? '')) {
                                'ticketed','issued' => 'success',
                                'pending'          => 'warning',
                                'cancelled'        => 'danger',
                                default            => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="text-center text-muted" data-export="{{ $loop->iteration }}">{{ $loop->iteration }}</td>
                            <td data-export="{{ trim($pax->user_first_name . ' ' . $pax->user_last_name) ?: 'N/A' }}">
                                <strong class="d-block" style="font-size:.75rem">{{ trim($pax->user_first_name . ' ' . $pax->user_last_name) ?: 'N/A' }}</strong>
                                <small class="text-muted">{{ $pax->user_email }}</small>
                            </td>
                            <td data-export="{{ $pax->updated_by_name ?? '-' }}"><small>{{ $pax->updated_by_name ?? '-' }}</small></td>
                            <td data-export="{{ $pax->first_name }} {{ $pax->last_name }}">
                                <strong style="font-size:.75rem">{{ $pax->first_name }} {{ $pax->last_name }}</strong>
                            </td>
                            <td class="text-center" data-export="{{ $pax->passenger_type_code ?? $pax->traveler_type ?? 'ADT' }}">
                                <span class="badge bg-info text-dark">{{ $pax->passenger_type_code ?? $pax->traveler_type ?? 'ADT' }}</span>
                            </td>
                            <td class="text-center" data-export="{{ ucfirst($pax->source ?? '') ?: '—' }}">
                                @if($pax->source) <span class="badge bg-secondary">{{ ucfirst($pax->source) }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center" data-export="{{ $pax->airline ?? '—' }}"><small>{{ $pax->airline ?? '—' }}</small></td>
                            <td class="text-center" data-export="{{ ($pax->flight_from && $pax->flight_to) ? $pax->flight_from . ' > ' . $pax->flight_to : '—' }}">
                                @if($pax->flight_from && $pax->flight_to)
                                    <span class="fw-semibold">{{ $pax->flight_from }}</span>
                                    <i class="fas fa-arrow-right fa-xs text-muted mx-1"></i>
                                    <span class="fw-semibold">{{ $pax->flight_to }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center" data-export="{{ $pax->departure_date ? \Carbon\Carbon::parse($pax->departure_date)->format('d M Y') : '—' }}">
                                <small>{{ $pax->departure_date ? \Carbon\Carbon::parse($pax->departure_date)->format('d M Y') : '—' }}</small>
                            </td>
                            <td class="text-center" data-export="{{ $pax->pnr ?? '—' }}">
                                @if($pax->pnr) <span class="badge bg-light text-dark border">{{ $pax->pnr }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center" data-export="{{ $pax->ticket_number ?? '—' }}">
                                @if($pax->ticket_number) <span class="badge bg-light text-dark border">{{ $pax->ticket_number }}</span>
                                @else <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center" data-export="{{ $pax->ticket_issued_at ? \Carbon\Carbon::parse($pax->ticket_issued_at)->format('d M Y') : '—' }}">
                                <small>{{ $pax->ticket_issued_at ? \Carbon\Carbon::parse($pax->ticket_issued_at)->format('d M Y') : '—' }}</small>
                            </td>
                            <td class="text-center" data-export="{{ ucfirst($pax->booking_status ?? '—') }}">
                                <span class="badge bg-{{ $sc }}">{{ ucfirst($pax->booking_status ?? '—') }}</span>
                            </td>
                            <td class="text-center" data-export="{{ ucfirst($pax->passenger_status ?? '—') }}">
                                <span class="badge bg-{{ $pc }}">{{ ucfirst($pax->passenger_status ?? '—') }}</span>
                            </td>
                            {{-- FARE --}}
                            <td class="text-end" data-col="base" data-order="{{ $pax->base ?? 0 }}" data-export="{{ number_format($pax->base ?? 0, 2) }}" style="background:#eaf4fb;white-space:nowrap">৳{{ number_format($pax->base ?? 0, 2) }}</td>
                            <td class="text-end" data-col="tax" data-order="{{ $pax->tax ?? 0 }}" data-export="{{ number_format($pax->tax ?? 0, 2) }}" style="background:#eaf4fb;white-space:nowrap">৳{{ number_format($pax->tax ?? 0, 2) }}</td>
                            <td class="text-end fw-semibold" data-col="gross" data-order="{{ $pax->gross_fare ?? 0 }}" data-export="{{ number_format($pax->gross_fare ?? 0, 2) }}" style="background:#d6eaf8;white-space:nowrap">৳{{ number_format($pax->gross_fare ?? 0, 2) }}</td>
                            <td class="text-end" data-col="ait" data-order="{{ $pax->ait_amount ?? 0 }}" data-export="{{ number_format($pax->ait_amount ?? 0, 2) }}" style="background:#eaf4fb;white-space:nowrap">৳{{ number_format($pax->ait_amount ?? 0, 2) }}</td>
                            <td class="text-end" data-col="svc" data-order="{{ $pax->service_charge ?? 0 }}" data-export="{{ number_format($pax->service_charge ?? 0, 2) }}" style="background:#eaf4fb;white-space:nowrap">৳{{ number_format($pax->service_charge ?? 0, 2) }}</td>
                            {{-- SEGMENT --}}
                            <td class="text-center" data-col="seg" data-order="{{ $segCount }}" data-export="{{ $segCount }}" style="background:#eafaf1"><span class="badge bg-primary">{{ $segCount }}</span></td>
                            <td class="text-end text-danger" data-col="segdisc" data-order="{{ $pax->user_seg_discount ?? 0 }}" data-export="{{ number_format($pax->user_seg_discount ?? 0, 2) }}" style="background:#eafaf1;white-space:nowrap">-৳{{ number_format($pax->user_seg_discount ?? 0, 2) }}</td>
                            <td class="text-end text-danger" data-col="osegdisc" data-order="{{ $pax->own_seg_discount ?? 0 }}" data-export="{{ number_format($pax->own_seg_discount ?? 0, 2) }}" style="background:#eafaf1;white-space:nowrap">-৳{{ number_format($pax->own_seg_discount ?? 0, 2) }}</td>
                            {{-- DISCOUNT --}}
                            <td class="text-end text-danger" data-col="udisc" data-order="{{ $pax->user_discount ?? 0 }}" data-export="{{ number_format($pax->user_discount ?? 0, 2) }}" style="background:#f5eef8;white-space:nowrap">-৳{{ number_format($pax->user_discount ?? 0, 2) }}</td>
                            <td class="text-end text-danger" data-col="odisc" data-order="{{ $pax->own_discount ?? 0 }}" data-export="{{ number_format($pax->own_discount ?? 0, 2) }}" style="background:#f5eef8;white-space:nowrap">-৳{{ number_format($pax->own_discount ?? 0, 2) }}</td>
                            {{-- RECEIVABLE / COST / PROFIT --}}
                            <td class="text-end fw-bold" data-col="receivable" data-order="{{ $pax->user_payable ?? 0 }}" data-export="{{ number_format($pax->user_payable ?? 0, 2) }}" style="background:#d4e6f1;white-space:nowrap">৳{{ number_format($pax->user_payable ?? 0, 2) }}</td>
                            <td class="text-end fw-bold" data-col="cost" data-order="{{ $pax->own_cost ?? 0 }}" data-export="{{ number_format($pax->own_cost ?? 0, 2) }}" style="background:#fde8d8;white-space:nowrap">৳{{ number_format($pax->own_cost ?? 0, 2) }}</td>
                            <td class="text-end fw-bold" data-col="profit" data-order="{{ $profit }}" data-export="{{ number_format($profit, 2) }}" style="background:{{ $profit >= 0 ? '#d5f5e3' : '#fdf0f0' }};white-space:nowrap">
                                <span class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($profit, 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="27" class="text-center py-5">
                                <i class="fas fa-search fa-2x text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">No passengers found.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                    {{-- NO tfoot here - summary shown below table --}}
                </table>
            </div>

            {{-- SUMMARY TABLE --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" style="font-size:.78rem;">
                            <thead>
                            <tr style="font-size:.7rem;text-align:center;">
                                <th style="background:#1e3a5f;color:#fff;min-width:130px">Summary</th>
                                <th style="background:#1a5276;color:#fff;min-width:90px">Base</th>
                                <th style="background:#1a5276;color:#fff;min-width:90px">Tax</th>
                                <th style="background:#1a5276;color:#fff;min-width:90px">Gross</th>
                                <th style="background:#1a5276;color:#fff;min-width:90px">AIT</th>
                                <th style="background:#1a5276;color:#fff;min-width:90px">Svc Charge</th>
                                <th style="background:#1d6a3e;color:#fff;min-width:50px">Seg</th>
                                <th style="background:#1d6a3e;color:#fff;min-width:90px">Seg Disc</th>
                                <th style="background:#1d6a3e;color:#fff;min-width:90px">Own Seg Disc</th>
                                <th style="background:#6e2f8a;color:#fff;min-width:90px">User Disc</th>
                                <th style="background:#6e2f8a;color:#fff;min-width:90px">Own Disc</th>
                                <th style="background:#1a5276;color:#fff;min-width:95px">Receivable</th>
                                <th style="background:#7d3c17;color:#fff;min-width:95px">Cost</th>
                                <th style="background:#145a32;color:#fff;min-width:95px">Profit</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="fw-bold" style="background:#eafaf1;">
                                <td style="background:#2d6a4f;color:#fff;white-space:nowrap;">
                                    <i class="fas fa-file me-1"></i> Current Page Total
                                </td>
                                <td class="text-end" id="page-base">৳0.00</td>
                                <td class="text-end" id="page-tax">৳0.00</td>
                                <td class="text-end" id="page-gross">৳0.00</td>
                                <td class="text-end" id="page-ait">৳0.00</td>
                                <td class="text-end" id="page-svc">৳0.00</td>
                                <td class="text-center" id="page-seg">0</td>
                                <td class="text-end" id="page-segdisc">৳0.00</td>
                                <td class="text-end" id="page-osegdisc">৳0.00</td>
                                <td class="text-end" id="page-udisc">৳0.00</td>
                                <td class="text-end" id="page-odisc">৳0.00</td>
                                <td class="text-end" id="page-receivable">৳0.00</td>
                                <td class="text-end" id="page-cost">৳0.00</td>
                                <td class="text-end" id="page-profit">৳0.00</td>
                            </tr>
                            <tr class="fw-bold" style="background:#eaf4fb;">
                                <td style="background:#1e3a5f;color:#fff;white-space:nowrap;">
                                    <i class="fas fa-calculator me-1"></i> Grand Total (All)
                                </td>
                                <td class="text-end" id="footer-base">৳0.00</td>
                                <td class="text-end" id="footer-tax">৳0.00</td>
                                <td class="text-end" id="footer-gross">৳0.00</td>
                                <td class="text-end" id="footer-ait">৳0.00</td>
                                <td class="text-end" id="footer-svc">৳0.00</td>
                                <td class="text-center" id="footer-seg">0</td>
                                <td class="text-end" id="footer-segdisc">৳0.00</td>
                                <td class="text-end" id="footer-osegdisc">৳0.00</td>
                                <td class="text-end" id="footer-udisc">৳0.00</td>
                                <td class="text-end" id="footer-odisc">৳0.00</td>
                                <td class="text-end" id="footer-receivable">৳0.00</td>
                                <td class="text-end" id="footer-cost">৳0.00</td>
                                <td class="text-end" id="footer-profit">৳0.00</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('styles')
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
            <style>
                #reportTable thead th { font-size:.68rem; white-space:nowrap; vertical-align:middle; text-align:center; padding:4px 6px; }
                #reportTable tbody td { font-size:.75rem; vertical-align:middle; padding:4px 6px; }
                #reportTable tfoot td { font-size:.75rem; padding:5px 6px; white-space:nowrap; }
                #reportTable tbody tr:hover td { background:#eef6ff !important; }
                .dt-button-collection { background:#fff !important; z-index:9999 !important; padding:8px !important; border:1px solid #dee2e6 !important; border-radius:6px !important; box-shadow:0 4px 12px rgba(0,0,0,.15) !important; }
                .dt-button-collection .dt-button { background:#f8f9fa !important; color:#333 !important; border:1px solid #dee2e6 !important; border-radius:4px !important; margin:2px !important; padding:4px 10px !important; font-size:.8rem !important; display:inline-block !important; }
                .dt-button-collection .dt-button.active { background:#0d6efd !important; color:#fff !important; border-color:#0d6efd !important; }
                .dt-button-collection .dt-button:hover { background:#e9ecef !important; }
                .dt-button-collection .dt-button.active:hover { background:#0b5ed7 !important; color:#fff !important; }
                @media print {
                    .no-print-actions, nav, aside, .sidebar { display:none !important; }
                    body { font-size:7px !important; }
                    #reportTable thead th, #reportTable tbody td, #reportTable tfoot td { font-size:6px !important; padding:2px 3px !important; }
                }
            </style>
        @endpush

        @push('js')
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

            <script>
                $(document).ready(function () {

                    // ✅ DataTable error popup off
                    $.fn.dataTable.ext.errMode = 'none';

                    // ✅ Quick date range buttons
                {{--    $('.date-sc').on('click', function () {--}}
                {{--        var range = $(this).data('range');--}}
                {{--        var today = new Date();--}}
                {{--        var from, to = today.toISOString().slice(0, 10);--}}

                {{--        if (range === 'today') {--}}
                {{--            from = to;--}}
                {{--            $('#hiddenRange').val('today');--}}
                {{--        } else if (range === 'week') {--}}
                {{--            var day = today.getDay() || 7;--}}
                {{--            var mon = new Date(today);--}}
                {{--            mon.setDate(today.getDate() - day + 1);--}}
                {{--            from = mon.toISOString().slice(0, 10);--}}
                {{--            $('#hiddenRange').val('this_week');--}}
                {{--        } else if (range === 'month') {--}}
                {{--            from = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';--}}
                {{--            $('#hiddenRange').val('this_month');--}}
                {{--        } else if (range === 'year') {--}}
                {{--            from = today.getFullYear() + '-01-01';--}}
                {{--            $('#hiddenRange').val('this_year');--}}
                {{--        }--}}

                {{--        $('#startDate').val(from);--}}
                {{--        $('#endDate').val(to);--}}
                {{--        $('#filterForm').submit();--}}
                {{--    });--}}

                {{--    // ✅ Form submit এ date empty থাকলে আজকের date বসাও--}}
                    $('#filterForm').on('submit', function () {
                        var today = new Date().toISOString().slice(0, 10);
                        if (!$('#startDate').val()) $('#startDate').val(today);
                        if (!$('#endDate').val())   $('#endDate').val(today);
                    });

                {{--    // ✅ Format number--}}
                    var fmt = function (n) {
                        return '৳' + parseFloat(n || 0).toLocaleString('en', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    };

                {{--    // ✅ data-col দিয়ে totals calculate--}}
                    function calcTotals(rows) {
                        var t = {base:0,tax:0,gross:0,ait:0,svc:0,seg:0,segdisc:0,osegdisc:0,udisc:0,odisc:0,receivable:0,cost:0,profit:0};
                        rows.nodes().each(function (row) {
                            $(row).find('td[data-col]').each(function () {
                                var col = $(this).data('col');
                                if (t.hasOwnProperty(col)) {
                                    t[col] += parseFloat($(this).data('order')) || 0;
                                }
                            });
                        });
                        return t;
                    }

                    function setFooter(prefix, t) {
                        $('#' + prefix + '-base').text(fmt(t.base));
                        $('#' + prefix + '-tax').text(fmt(t.tax));
                        $('#' + prefix + '-gross').text(fmt(t.gross));
                        $('#' + prefix + '-ait').text(fmt(t.ait));
                        $('#' + prefix + '-svc').text(fmt(t.svc));
                        $('#' + prefix + '-seg').text(Math.round(t.seg));
                        $('#' + prefix + '-segdisc').text(fmt(t.segdisc));
                        $('#' + prefix + '-osegdisc').text(fmt(t.osegdisc));
                        $('#' + prefix + '-udisc').text(fmt(t.udisc));
                        $('#' + prefix + '-odisc').text(fmt(t.odisc));
                        $('#' + prefix + '-receivable').text(fmt(t.receivable));
                        $('#' + prefix + '-cost').text(fmt(t.cost));
                        $('#' + prefix + '-profit').text(fmt(t.profit));
                    }

                    function updateFooterTotals(api) {
                        setFooter('footer', calcTotals(api.rows({ search: 'applied' })));
                        setFooter('page',   calcTotals(api.rows({ search: 'applied', page: 'current' })));
                    }

                    var exportFormat = {
                        body: function (data, row, column, node) {
                            var exp = $(node).data('export');
                            if (exp !== undefined && exp !== null && exp !== '') return exp;
                            var ord = $(node).data('order');
                            if (ord !== undefined) return ord;
                            return $('<div>').html(data).text().trim();
                        }
                    };

                    var exportOptions = {
                        columns : ':visible',
                        modifier: { search: 'applied', order: 'applied' },
                        format  : {
                            body: function(data, row, column, node) {
                                var exp = $(node).data('export');
                                if (exp !== undefined && exp !== null && exp !== '') return exp;
                                var ord = $(node).data('order');
                                if (ord !== undefined) return ord;
                                return $('<div>').html(data).text().trim();
                            }
                        }
                    };

                    var table = $('#reportTable').DataTable({
                        dom: '<"row mb-2 align-items-center"<"col-sm-2"l><"col-sm-6"B><"col-sm-4"f>>' +
                            '<"row"<"col-sm-12"tr>>' +
                            '<"row mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                        buttons: [
                            {
                                extend   : 'colvis',
                                text     : '<i class="fas fa-columns"></i> Columns',
                                className: 'btn btn-outline-dark btn-sm',
                                columns  : ':not(:first-child)'
                            },
                            {
                                extend       : 'excelHtml5',
                                text         : '<i class="fas fa-file-excel"></i> Excel',
                                className    : 'btn btn-success btn-sm',
                                title        : 'IATA Report - {{ $startDate }} to {{ $endDate }}',
                                footer       : false,
                                exportOptions: exportOptions,
                                customize: function (xlsx) {
                                    var api      = table;
                                    var pt       = calcTotals(api.rows({ search: 'applied', page: 'current' }));
                                    var gt       = calcTotals(api.rows({ search: 'applied' }));
                                    var fmtN     = function (n) { return parseFloat(n || 0).toFixed(2); };
                                    var visCols  = api.columns(':visible').count();
                                    var sheet    = xlsx.xl.worksheets['sheet1.xml'];
                                    var sheetData = sheet.querySelector('sheetData');
                                    var rows     = sheetData.querySelectorAll('row');
                                    var lastRowNum = parseInt(rows[rows.length - 1].getAttribute('r'));

                                    function makeCell(col, rowNum, value) {
                                        var cell = sheet.createElementNS('http://www.openxmlformats.org/spreadsheetml/2006/main', 'c');
                                        cell.setAttribute('r', col + rowNum);
                                        cell.setAttribute('t', 'inlineStr');
                                        var is = sheet.createElementNS('http://www.openxmlformats.org/spreadsheetml/2006/main', 'is');
                                        var t  = sheet.createElementNS('http://www.openxmlformats.org/spreadsheetml/2006/main', 't');
                                        t.textContent = value;
                                        is.appendChild(t);
                                        cell.appendChild(is);
                                        return cell;
                                    }

                                    function makeRow(rowNum, labelText, totals) {
                                        var row  = sheet.createElementNS('http://www.openxmlformats.org/spreadsheetml/2006/main', 'row');
                                        row.setAttribute('r', rowNum);
                                        var cols = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
                                        var vals = [
                                            labelText,
                                            fmtN(totals.base), fmtN(totals.tax), fmtN(totals.gross),
                                            fmtN(totals.ait),  fmtN(totals.svc), totals.seg,
                                            fmtN(totals.segdisc), fmtN(totals.osegdisc),
                                            fmtN(totals.udisc),   fmtN(totals.odisc),
                                            fmtN(totals.receivable), fmtN(totals.cost), fmtN(totals.profit)
                                        ];
                                        for (var i = 0; i < Math.min(vals.length, visCols); i++) {
                                            row.appendChild(makeCell(cols[i], rowNum, String(vals[i])));
                                        }
                                        sheetData.appendChild(row);
                                    }

                                    makeRow(lastRowNum + 1, 'Current Page Total', pt);
                                    makeRow(lastRowNum + 2, 'Grand Total (All)',   gt);
                                }
                            },
                            {
                                extend       : 'csvHtml5',
                                text         : '<i class="fas fa-file-csv"></i> CSV',
                                className    : 'btn btn-info btn-sm',
                                footer       : true,
                                exportOptions: exportOptions
                            },
                            {
                                extend       : 'pdfHtml5',
                                text         : '<i class="fas fa-file-pdf"></i> PDF',
                                className    : 'btn btn-danger btn-sm',
                                title        : 'IATA Report - {{ $startDate }} to {{ $endDate }}',
                                orientation  : 'landscape',
                                pageSize     : 'A3',
                                footer       : true,
                                exportOptions: exportOptions,
                                customize: function (doc) {
                                    doc.defaultStyle.fontSize       = 6;
                                    doc.styles.tableHeader.fontSize = 6;
                                    doc.styles.tableHeader.fillColor = '#1e3a5f';
                                    doc.styles.tableHeader.color    = '#ffffff';

                                    var api  = table;
                                    var pt   = calcTotals(api.rows({ search: 'applied', page: 'current' }));
                                    var gt   = calcTotals(api.rows({ search: 'applied' }));
                                    var fmtN = function (n) { return parseFloat(n || 0).toFixed(2); };

                                    var summaryLabels = ['Summary','Base','Tax','Gross','AIT','Svc Charge','Seg','Seg Disc','Own Seg Disc','User Disc','Own Disc','Receivable','Cost','Profit'];
                                    var ptRow = ['Current Page Total', fmtN(pt.base), fmtN(pt.tax), fmtN(pt.gross), fmtN(pt.ait), fmtN(pt.svc), pt.seg, fmtN(pt.segdisc), fmtN(pt.osegdisc), fmtN(pt.udisc), fmtN(pt.odisc), fmtN(pt.receivable), fmtN(pt.cost), fmtN(pt.profit)];
                                    var gtRow = ['Grand Total (All)',  fmtN(gt.base), fmtN(gt.tax), fmtN(gt.gross), fmtN(gt.ait), fmtN(gt.svc), gt.seg, fmtN(gt.segdisc), fmtN(gt.osegdisc), fmtN(gt.udisc), fmtN(gt.odisc), fmtN(gt.receivable), fmtN(gt.cost), fmtN(gt.profit)];

                                    function styleRow(row, bg) {
                                        return row.map(function (v, i) {
                                            return { text: String(v), fillColor: bg, color: '#fff', bold: true, fontSize: 6, alignment: i === 0 ? 'left' : 'right' };
                                        });
                                    }

                                    doc.content.push({
                                        margin: [0, 10, 0, 0],
                                        table: {
                                            widths: Array(summaryLabels.length).fill('*'),
                                            body: [
                                                summaryLabels.map(function (h) {
                                                    return { text: h, fillColor: '#1e3a5f', color: '#fff', bold: true, fontSize: 6, alignment: 'center' };
                                                }),
                                                styleRow(ptRow, '#2d6a4f'),
                                                styleRow(gtRow, '#1e3a5f')
                                            ]
                                        },
                                        layout: 'lightHorizontalLines'
                                    });
                                }
                            },
                            {
                                extend       : 'print',
                                text         : '<i class="fas fa-print"></i> Print',
                                className    : 'btn btn-secondary btn-sm',
                                footer       : true,
                                exportOptions: exportOptions,
                                customize: function (win) {
                                    var $b = $(win.document.body);
                                    $b.css('font-size', '7px');
                                    $b.find('h1').css('font-size', '11px');
                                    $b.find('table').css({ 'font-size': '6px', 'border-collapse': 'collapse', 'width': '100%' });
                                    $b.find('table td, table th').css({ 'padding': '2px 3px', 'border': '1px solid #aaa' });

                                    var api  = table;
                                    var pt   = calcTotals(api.rows({ search: 'applied', page: 'current' }));
                                    var gt   = calcTotals(api.rows({ search: 'applied' }));
                                    var fmtN = function (n) { return parseFloat(n || 0).toFixed(2); };

                                    var headers = ['Summary','Base','Tax','Gross','AIT','Svc Charge','Seg','Seg Disc','Own Seg Disc','User Disc','Own Disc','Receivable','Cost','Profit'];
                                    var ptVals  = ['Current Page Total', fmtN(pt.base), fmtN(pt.tax), fmtN(pt.gross), fmtN(pt.ait), fmtN(pt.svc), pt.seg, fmtN(pt.segdisc), fmtN(pt.osegdisc), fmtN(pt.udisc), fmtN(pt.odisc), fmtN(pt.receivable), fmtN(pt.cost), fmtN(pt.profit)];
                                    var gtVals  = ['Grand Total (All)',  fmtN(gt.base), fmtN(gt.tax), fmtN(gt.gross), fmtN(gt.ait), fmtN(gt.svc), gt.seg, fmtN(gt.segdisc), fmtN(gt.osegdisc), fmtN(gt.udisc), fmtN(gt.odisc), fmtN(gt.receivable), fmtN(gt.cost), fmtN(gt.profit)];

                                    function buildRow(vals, bg, color) {
                                        return '<tr>' + vals.map(function (v, i) {
                                            return '<td style="background:' + bg + ';color:' + color + ';font-weight:bold;padding:2px 4px;border:1px solid #aaa;text-align:' + (i === 0 ? 'left' : 'right') + '">' + v + '</td>';
                                        }).join('') + '</tr>';
                                    }

                                    var summaryHtml = '<br><table style="font-size:6px;border-collapse:collapse;width:100%;">' +
                                        '<thead><tr>' + headers.map(function (h) {
                                            return '<th style="background:#1e3a5f;color:#fff;padding:2px 4px;border:1px solid #aaa;text-align:center;">' + h + '</th>';
                                        }).join('') + '</tr></thead>' +
                                        '<tbody>' +
                                        buildRow(ptVals, '#2d6a4f', '#fff') +
                                        buildRow(gtVals, '#1e3a5f', '#fff') +
                                        '</tbody></table>';

                                    $b.append(summaryHtml);
                                }
                            }
                        ],
                        lengthMenu  : [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                        pageLength  : 25,
                        ordering    : true,
                        searching   : true,
                        autoWidth   : false,
                        scrollX     : true,
                        language    : { search: 'Search:', lengthMenu: 'Show _MENU_ entries' },
                        drawCallback: function () { updateFooterTotals(this.api()); },
                        initComplete: function () { updateFooterTotals(this.api()); }
                    });

                    table.on('search.dt', function () {
                        updateFooterTotals(table);
                    });
                });
            </script>
    @endpush
