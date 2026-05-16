
@php
    $flightData = $reissue->new_flight_details;
    if (is_string($flightData)) {
        $flightData = json_decode($flightData, true);
    }
@endphp

@if(!empty($flightData))
    <hr>
    <h6 class="mb-3">
        <strong><i class="fa fa-plane text-primary"></i> {{ __('New Flight Details') }}</strong>
    </h6>

    {{-- ── Price Summary ── --}}
    @php $price = $flightData['price'] ?? []; @endphp
    @if(!empty($price))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-info py-2">
                    <div class="row">
                        <div class="col-md-3 text-center border-right">
                            <small class="text-muted d-block">Base Fare</small>
                            <strong>{{ number_format($price['api_base_fare'] ?? 0) }} BDT</strong>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <small class="text-muted d-block">Tax</small>
                            <strong>{{ number_format($price['api_tax'] ?? 0) }} BDT</strong>
                        </div>
                        <div class="col-md-3 text-center border-right">
                            <small class="text-muted d-block">Service Charge</small>
                            <strong>{{ number_format($price['service_charge'] ?? 0) }} BDT</strong>
                        </div>
                        <div class="col-md-3 text-center">
                            <small class="text-muted d-block">Total</small>
                            <strong class="text-success" style="font-size:1.1rem">
                                {{ number_format($price['total'] ?? 0) }} {{ $price['currency'] ?? 'BDT' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Legs (Outbound / Return) ── --}}
    @foreach($flightData['legs'] ?? [] as $leg)
        @php
            $legLabel = $leg['leg_type'] === 'return' ? 'Return Flight' : 'Outbound Flight';
            $legColor = $leg['leg_type'] === 'return' ? '#0f766e' : '#1e3a5f';
            $dep      = $leg['departure'];
            $arr      = $leg['arrival'];
        @endphp

        <div class="card mb-3" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
            {{-- Leg Header --}}
            <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between"
                 style="background:{{ $legColor }};color:#fff">
                <span style="font-weight:600;font-size:.88rem">
                    <i class="fa fa-plane"></i>
                    {{ $legLabel }}:
                    {{ $dep['airport_code'] }} → {{ $arr['airport_code'] }}
                </span>
                <span style="font-size:.8rem">
                    {{ $leg['duration_formatted'] }}
                    &nbsp;|&nbsp;
                    {{ $leg['stops'] == 0 ? 'Direct' : $leg['stops'] . ' Stop' . ($leg['stops'] > 1 ? 's' : '') }}
                </span>
            </div>

            <div class="card-body p-3">
                {{-- Departure / Arrival Summary --}}
                <div class="row mb-3">
                    <div class="col-md-4 text-center">
                        <div style="font-size:1.4rem;font-weight:700;color:#1e3a5f">{{ $dep['airport_code'] }}</div>
                        <div style="font-size:.85rem;font-weight:600">{{ $dep['time_12h'] }}</div>
                        <div style="font-size:.78rem;color:#6b7280">{{ $dep['date'] }}</div>
                        <div style="font-size:.75rem;color:#9ca3af">{{ $dep['airport_name'] }}</div>
                        @if($dep['terminal'])
                            <span class="badge badge-secondary" style="font-size:.7rem">Terminal {{ $dep['terminal'] }}</span>
                        @endif
                    </div>
                    <div class="col-md-4 text-center" style="display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <div style="font-size:.75rem;color:#6b7280;margin-bottom:4px">{{ $leg['duration_formatted'] }}</div>
                        <div style="width:100%;height:2px;background:#e5e7eb;position:relative">
                            <i class="fa fa-plane" style="position:absolute;top:-7px;left:50%;transform:translateX(-50%);color:#3b82f6;font-size:.85rem"></i>
                        </div>
                        @if($leg['stops'] > 0)
                            <div style="font-size:.72rem;color:#f59e0b;margin-top:4px">
                                <i class="fa fa-circle" style="font-size:.5rem"></i>
                                {{ $leg['stops'] }} stop via {{ implode(', ', array_column($leg['stops_detail'] ?? [], 'airport_code')) }}
                            </div>
                        @else
                            <div style="font-size:.72rem;color:#16a34a;margin-top:4px">Direct</div>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <div style="font-size:1.4rem;font-weight:700;color:#1e3a5f">{{ $arr['airport_code'] }}</div>
                        <div style="font-size:.85rem;font-weight:600">{{ $arr['time_12h'] }}</div>
                        <div style="font-size:.78rem;color:#6b7280">{{ $arr['date'] }}</div>
                        <div style="font-size:.75rem;color:#9ca3af">{{ $arr['airport_name'] }}</div>
                        @if($arr['terminal'] ?? null)
                            <span class="badge badge-secondary" style="font-size:.7rem">Terminal {{ $arr['terminal'] }}</span>
                        @endif
                    </div>
                </div>

                {{-- Stop Details --}}
                @foreach($leg['stops_detail'] ?? [] as $stop)
                    <div class="alert py-2 px-3 mb-2"
                         style="background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;font-size:.8rem">
                        <i class="fa fa-map-marker text-warning"></i>
                        <strong>Layover at {{ $stop['airport_name'] }} ({{ $stop['airport_code'] }})</strong>
                        &nbsp;—&nbsp;{{ $stop['layover_formatted'] }}
                        @if($stop['is_overnight'])
                            <span class="badge badge-warning" style="font-size:.68rem">Overnight</span>
                        @endif
                        <br>
                        <span class="text-muted">
                            Arrives: {{ $stop['arrival_time_12h'] }} {{ $stop['arrival_date'] }}
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            Departs: {{ $stop['departure_time_12h'] }} {{ $stop['departure_date'] }}
                        </span>
                    </div>
                @endforeach

                {{-- Segments --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.8rem">
                        <thead style="background:#f8fafc">
                        <tr>
                            <th>Flight</th>
                            <th>Aircraft</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Duration</th>
                            <th>Class</th>
                            <th>Seats</th>
                            <th>Meal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($leg['segments'] ?? [] as $segment)
                            @php
                                $fareInfo = $segment['fare_info'] ?? [];
                                $cabinColor = [
                                    'F' => '#7c3aed', 'C' => '#1e40af',
                                    'W' => '#0f766e', 'Y' => '#374151',
                                ][$fareInfo['cabin_code'] ?? 'Y'] ?? '#374151';
                            @endphp
                            <tr>
                                <td>
                                    @if(!empty($segment['carrier_images']['thumb']))
                                        <img src="{{ $segment['carrier_images']['thumb'] }}"
                                             alt="{{ $segment['carrier_name'] }}"
                                             style="height:18px;width:auto;margin-right:4px;vertical-align:middle">
                                    @endif
                                    <strong>{{ $segment['full_flight_number'] }}</strong>
                                    @if($segment['is_codeshare'])
                                        <br><small class="text-muted">Operated by {{ $segment['operating_carrier_name'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $segment['aircraft_name'] ?? $segment['aircraft'] ?? '—' }}
                                </td>
                                <td>
                                    <strong>{{ $segment['departure']['airport_code'] }}</strong><br>
                                    <small class="text-muted">{{ $segment['departure']['city'] }}</small>
                                    @if($segment['departure']['terminal'] ?? null)
                                        <br><small>T{{ $segment['departure']['terminal'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $segment['arrival']['airport_code'] }}</strong><br>
                                    <small class="text-muted">{{ $segment['arrival']['city'] }}</small>
                                    @if($segment['arrival']['terminal'] ?? null)
                                        <br><small>T{{ $segment['arrival']['terminal'] }}</small>
                                    @endif
                                </td>
                                <td style="white-space:nowrap">
                                    {{ $segment['departure']['time_12h'] }}<br>
                                    <small class="text-muted">{{ $segment['departure']['date'] }}</small>
                                </td>
                                <td style="white-space:nowrap">
                                    {{ $segment['arrival']['time_12h'] }}<br>
                                    <small class="text-muted">{{ $segment['arrival']['date'] }}</small>
                                </td>
                                <td>{{ $segment['duration_formatted'] }}</td>
                                <td>
                                    <span style="font-size:.72rem;font-weight:600;color:{{ $cabinColor }}">
                                        {{ $fareInfo['cabin_name'] ?? '—' }}<br>
                                        ({{ $fareInfo['booking_code'] ?? '—' }})
                                    </span>
                                </td>
                                <td>
                                    @php $seats = $fareInfo['seats_available'] ?? 0; @endphp
                                    <span style="color:{{ $seats <= 3 ? '#dc2626' : '#16a34a' }};font-weight:600">
                                        {{ $seats }}
                                    </span>
                                </td>
                                <td>
                                    {{ $segment['meal_description'] ?? '—' }}
                                </td>
                            </tr>
                            {{-- Layover row --}}
                            @if(!empty($segment['layover_after']))
                                @php $lay = $segment['layover_after']; @endphp
                                <tr style="background:#fffbeb">
                                    <td colspan="10" class="text-center" style="font-size:.75rem;color:#92400e;padding:5px">
                                        <i class="fa fa-clock-o"></i>
                                        <strong>Layover at {{ $lay['airport_name'] ?? $lay['airport_code'] }}:</strong>
                                        {{ $lay['formatted'] }}
                                        @if($lay['is_overnight']) <span class="badge badge-warning" style="font-size:.65rem">Overnight</span> @endif
                                        @if($lay['terminal_change']) <span class="badge badge-danger" style="font-size:.65rem">Terminal Change</span> @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    {{-- ── Passenger Price Breakdown ── --}}
    @if(!empty($flightData['passenger_price_breakdown']))
        <h6 class="mb-2"><strong><i class="fa fa-users text-primary"></i> Passenger Price Breakdown</strong></h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered" style="font-size:.8rem">
                <thead style="background:#f8fafc">
                <tr>
                    <th>Type</th>
                    <th>Count</th>
                    <th>Base Fare</th>
                    <th>Tax</th>
                    <th>AIT</th>
                    <th>Service Charge</th>
                    <th>Total Payable</th>
                    <th>Refundable</th>
                    <th>Baggage</th>
                </tr>
                </thead>
                <tbody>
                @foreach($flightData['passenger_price_breakdown'] as $pax)
                    @php
                        $paxInfo = collect($flightData['passengers'] ?? [])->firstWhere('type', $pax['passenger_type']);
                    @endphp
                    <tr>
                        <td><strong>{{ $pax['passenger_type'] }}</strong></td>
                        <td>{{ $pax['passenger_count'] }}</td>
                        <td>{{ number_format($pax['per_pax']['base_fare'] ?? 0) }}</td>
                        <td>{{ number_format($pax['per_pax']['tax'] ?? 0) }}</td>
                        <td>{{ number_format($pax['per_pax']['ait_amount'] ?? 0) }}</td>
                        <td>{{ number_format($pax['per_pax']['service_charge'] ?? 0) }}</td>
                        <td><strong class="text-success">{{ number_format($pax['per_pax']['user_payable'] ?? 0) }} BDT</strong></td>
                        <td>
                            @if($paxInfo['refundable'] ?? false)
                                <span class="badge badge-success" style="font-size:.7rem">Yes</span>
                            @else
                                <span class="badge badge-danger" style="font-size:.7rem">No</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($paxInfo['baggage']))
                                {{ $paxInfo['baggage']['weight'] }}{{ $paxInfo['baggage']['unit'] }}
                                <small class="text-muted">({{ $paxInfo['baggage']['airline'] }})</small>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Tax Breakdown ── --}}
    @if(!empty($flightData['taxes_breakdown']))
        <h6 class="mb-2">
            <strong>
                <i class="fa fa-list text-secondary"></i> Tax Breakdown
            </strong>
            <a class="btn btn-xs btn-link p-0 ml-2" data-toggle="collapse" data-target="#taxBreakdown_{{ $reissue->id }}">
                <small>show/hide</small>
            </a>
        </h6>
        <div class="collapse" id="taxBreakdown_{{ $reissue->id }}">
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered" style="font-size:.78rem">
                    <thead style="background:#f8fafc">
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Amount (BDT)</th>
                        <th>Published</th>
                        <th>Station</th>
                        <th>Country</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($flightData['taxes_breakdown'] as $tax)
                        <tr>
                            <td><strong>{{ $tax['code'] }}</strong></td>
                            <td style="max-width:220px">{{ $tax['description'] }}</td>
                            <td>{{ number_format($tax['amount']) }}</td>
                            <td>{{ $tax['published_amount'] }} {{ $tax['published_currency'] }}</td>
                            <td>{{ $tax['station'] }}</td>
                            <td>{{ $tax['country'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot style="background:#f8fafc">
                    <tr>
                        <td colspan="2"><strong>Total Tax</strong></td>
                        <td><strong>{{ number_format(collect($flightData['taxes_breakdown'])->sum('amount')) }} BDT</strong></td>
                        <td colspan="3"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Meta Info ── --}}
    <div class="row">
        <div class="col-md-12">
            <small class="text-muted">
                <i class="fa fa-info-circle"></i>
                Provider: <strong>{{ $flightData['provider'] ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Validating Carrier: <strong>{{ $flightData['validating_carrier'] ?? '—' }}</strong>
                &nbsp;|&nbsp;
                Last Ticket Date: <strong>{{ $flightData['last_ticket_date'] ?? '—' }}</strong>
                &nbsp;|&nbsp;
                eTicketable: <strong>{{ ($flightData['eTicketable'] ?? false) ? 'Yes' : 'No' }}</strong>
            </small>
        </div>
    </div>

@else
    <hr>
    <p class="text-muted text-center">
        <i class="fa fa-info-circle"></i> {{ __('No new flight details available.') }}
    </p>
@endif




























