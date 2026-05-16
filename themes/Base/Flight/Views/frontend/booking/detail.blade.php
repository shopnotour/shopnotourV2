@php $lang_local = app()->getLocale() @endphp
<div class="booking-review">
    <h4 class="booking-review-title">{{__("Your Booking")}}</h4>
    <div class="booking-review-content">
{{--        @php--}}
{{--        dd($service->attributes);--}}
{{--        dd($booking)--}}
{{-- @endphp--}}
        <div class="review-section">
            <div class="service-info">
                <div>
                    @if($airline->airlineImage?->file_path)
                        @php
                            $image_url = asset('uploads/'.$airline->airlineImage->file_path);
                        @endphp

                        @if(!empty($disable_lazyload))
                            <img src="{{ $image_url }}" class="img-responsive" alt="{{ $airline->name }}">
                        @else
                            {!! get_image_tag($airline->image_id,'medium',['class'=>'img-responsive','alt'=>$airline->name]) !!}
                        @endif
                    @endif
                </div>

                {{--                <div>--}}
{{--                    @if($image_url = $service->airline->image_url)--}}
{{--                        @if($image_url = $airline->airlineImage->file_path)--}}
{{--                        @if(!empty($disable_lazyload))--}}
{{--                            <img src="{{$image_url}}" class="img-responsive" alt="{!! clean($airline->name) !!}">--}}
{{--                        @else--}}
{{--                            {!! get_image_tag($service->airline->image_id,'medium',['class'=>'img-responsive','alt'=>airline_from_code($booking->airline)]) !!}--}}
{{--                        @endif--}}
{{--                    @endif--}}
{{--                </div>--}}
                <div class="mt-2">
                    <h3 class="service-name">{!! clean($airline->name) !!}</h3>
                </div>
                <div class="font-weight-medium  mb-3">
                    <p class="mb-1">
                        {{__(':from to :to',['from'=>airport_from_code($booking->flight_from),'to'=>airport_from_code($booking->flight_to)])}}
                    </p>
                </div>
                <div class="mt-2">
                    <h3 class="service-name">Routes:</h3>
                </div>
                <table width="100%" cellpadding="5" >
                    <tr style="border-bottom: 1px solid grey;">
                        <td style="border: 1px solid #DDD; font-size:11px">Departure</td>
                        <td style="border: 1px solid #DDD; font-size:11px">Arrival</td>
                        <td style="border: 1px solid #DDD; font-size:11px">Time</td>
                    </tr>
                    @foreach($booking['routes'] as $route)
                        <tr>
                            <td style="border: 1px solid #DDD; font-size:11px">{{airport_from_code($route->departure_iata_code)}}<br/>{{date('d M, h:i',strtotime($route->departure_at))}}</td>
                            <td style="border: 1px solid #DDD; font-size:11px"><span class="font-size-10 font-weight-normal text-gray-1">{{airport_from_code($route->arrival_iata_code)}}<br/>{{date('d M, h:i',strtotime($route->arrival_at))}}</td>
                            <td style="border: 1px solid #DDD; font-size:11px">{{$route->duration}}</td>
                        </tr>
                    @endforeach
                </table>
                <div class="mt-2">
                    <h3 class="service-name">Passengers:</h3>
                </div>
                <table width="100%" cellpadding="5" >
                    <tr style="border-bottom: 1px solid grey;">
                        <td style="border: 1px solid #DDD; font-size:11px">Type</td>
                        <td style="border: 1px solid #DDD; font-size:11px">fare</td>
                        <td style="border: 1px solid #DDD; font-size:11px">Tax</td>
                        <td style="border: 1px solid #DDD; font-size:11px">Total</td>
                    </tr>
                    @foreach($booking['passengers'] as $passenger)
                        <tr>
                            <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->traveler_type}}</td>
                            <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->base}} {{$passenger->currency}}</td>
                            <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->total-$passenger->base}} {{$passenger->currency}}</td>
                            <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->total}} {{$passenger->currency}}</td>
                        </tr>
                    @endforeach
                    @if($booking->coupon_amount > 0)
                        <tr>
                            <td ></td>
                            <td ></td>
                            <td style="border: 1px solid #DDD; font-size:15px; color: red">Discount Amount</td>
                            <td style="border: 1px solid #DDD; font-size:15px; color: red" >{{$booking->coupon_amount}} {{$booking->currency}}</td>
                        </tr>
                    @endif

                </table>

            </div>
        </div>
        <div class="review-section">
            <ul class="review-list">
                @if($booking->start_date)
                    <!--<li>
                        <div class="label">{{ __("Start Date") }}</div>
                        <div class="val">
                            {{display_date($booking->start_date)}}
                        </div>
                    </li>
                    <li>
                        <div class="label">{{ __("Duration") }}</div>
                        <div class="val">{{human_time_diff($booking->end_date,$booking->start_date)}}</div>
                    </li>-->
                @endif
                @php
                    $flight_seat = $booking->getJsonMeta('flight_seat')@endphp
                @if(!empty($flight_seat))
                    @foreach($flight_seat as $type)
                        @if(!empty($type['number']))
                            <li>
                                <div class="label">{{$type['seat_type']['name']}}:</div>
                                <div class="val">
                                    {{$type['number']}}
                                </div>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
        <div class="review-section total-review">
            <ul class="review-list">
                @php $flight_seat = $booking->getJsonMeta('flight_seat')@endphp
                @php $person_types = $booking->getJsonMeta('person_types') @endphp
                @if(!empty($flight_seat))
                    @foreach($flight_seat as $type)
                        @if(!empty($type['number']))
                            <li>
                                <div class="label">{{ $type['seat_type']['name']}}: {{$type['number']}} * {{format_money($type['price'])}}</div>
                                <div class="val">
                                    {{format_money($type['price'] * $type['number'])}}
                                </div>
                            </li>
                        @endif
                    @endforeach
                @endif
                @php $extra_price = $booking->getJsonMeta('extra_price') @endphp
                @if(!empty($extra_price))
                    <li>
                        <div>
                            {{__("Extra Prices:")}}
                        </div>
                    </li>
                    @foreach($extra_price as $type)
                        <li>
                            <div class="label">{{$type['name_'.$lang_local] ?? __($type['name'])}}:</div>
                            <div class="val">
                                {{format_money($type['total'] ?? 0)}}
                            </div>
                        </li>
                    @endforeach
                @endif
                @php
                    $list_all_fee = [];
                    if(!empty($booking->buyer_fees)){
                        $buyer_fees = json_decode($booking->buyer_fees , true);
                        $list_all_fee = $buyer_fees;
                    }
                    if(!empty($vendor_service_fee = $booking->vendor_service_fee)){
                        $list_all_fee = array_merge($list_all_fee , $vendor_service_fee);
                    }
                @endphp
                @if(!empty($list_all_fee))
                    @foreach ($list_all_fee as $item)
                        @php
                            $fee_price = $item['price'];
                            if(!empty($item['unit']) and $item['unit'] == "percent"){
                                $fee_price = ( $booking->total_before_fees / 100 ) * $item['price'];
                            }
                        @endphp
                        <li>
                            <div class="label">
                                {{$item['name_'.$lang_local] ?? $item['name']}}
                                <i class="icofont-info-circle" data-toggle="tooltip" data-placement="top" title="{{ $item['desc_'.$lang_local] ?? $item['desc'] }}"></i>
                                @if(!empty($item['per_person']) and $item['per_person'] == "on")
                                    : {{$booking->total_guests}} * {{format_money( $fee_price )}}
                                @endif
                            </div>
                            <div class="val">
                                @if(!empty($item['per_person']) and $item['per_person'] == "on")
                                    {{ format_money( $fee_price * $booking->total_guests ) }}
                                @else
                                    {{ format_money( $fee_price ) }}
                                @endif
                            </div>
                        </li>
                    @endforeach
                @endif
{{--                @includeIf('Coupon::frontend/booking/checkout-coupon')--}}
                <li class="final-total d-block">
                    <div class="d-flex justify-content-between">
                        <div class="label">{{__("Total:")}}</div>
                        <div class="val">{{format_money($booking->total)}}</div>
                    </div>
                    @if($booking->status !='draft')
                        <div class="d-flex justify-content-between">
                            <div class="label">{{__("Paid:")}}</div>
                            <div class="val">{{format_money($booking->paid)}}</div>
                        </div>
                        @if($booking->paid < $booking->total )
                            <div class="d-flex justify-content-between">
                                <div class="label">{{__("Remain:")}}</div>
                                <div class="val">{{format_money($booking->total - $booking->paid)}}</div>
                            </div>
                        @endif
                    @endif
                </li>
                @include ('Booking::frontend/booking/checkout-deposit-amount')
            </ul>
        </div>
    </div>
</div>
