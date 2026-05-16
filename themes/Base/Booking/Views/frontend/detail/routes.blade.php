<?php
$routes = $booking->routes;
if(!count($routes)) return;
?>
<h4 class="form-section-title">{{__("Route Information:")}}</h4>
<div class="accordion gateways-table my-3" id="passengers_info">
    @foreach($routes as $i=>$route)
        <div class="card">
            <div class="card-header c-pointer" id="passenger_heading_{{$i + 1}}">
                <h4 class="mb-0 " style="font-size: 16px" data-toggle="collapse" data-target="#passenger_{{$i + 1}}" data-bs-toggle="collapse" data-bs-target="#passenger_{{$i + 1}}" aria-expanded="true"
                    aria-controls="passenger_{{$i + 1}}">
                    {{__("Route #:number",['number'=>$i + 1])}}: {{$route->departure_iata_code}} to {{$route->arrival_iata_code}}
                </h4>
            </div>

            <div id="passenger_{{$i + 1}}" class="collapse @if($i + 1 == 1) show @endif"
                 aria-labelledby="passenger_heading_{{$i + 1}}" data-parent="#passengers_info">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Departure")}}: </label>
                                 {{airport_from_code($route->departure_iata_code)}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Departure Time ")}}:</label>

                                {{date('d M, h:i',strtotime($route->departure_at))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Arrival")}}: </label>
                                 {{airport_from_code($route->arrival_iata_code)}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Arrival Time ")}}:</label>

                                {{date('d M, h:i',strtotime($route->departure_at))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Carrier")}}: </label>
                                {{airline_from_code($route->carrier_code)}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Aircraft")}}: </label>
                                 {{$route->aircraft_code}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{__("Duration")}}: </label>
                                 {{$route->duration}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
