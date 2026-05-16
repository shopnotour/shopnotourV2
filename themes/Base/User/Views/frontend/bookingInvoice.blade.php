@extends('Layout::empty')

@push('css')
    <style type="text/css">
        html, body {
            background: #f0f0f0;
        }
        .bravo_topbar, .bravo_header, .bravo_footer {
            display: none;
        }
        .invoice-amount {
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 20px;
            display: inline-block;
            text-align: center;
        }
        .email_new_booking .b-table {
            width: 100%;
        }
        .email_new_booking .val {
            text-align: right;
        }
        .email_new_booking td,
        .email_new_booking th {
            padding: 5px;
        }
        .email_new_booking .val table {
            text-align: left;
        }
        .email_new_booking .b-panel-title,
        .email_new_booking .booking-number,
        .email_new_booking .booking-status,
        .email_new_booking .manage-booking-btn {
            display: none;
        }
        .email_new_booking .fsz21 {
            font-size: 21px;
        }
        .table-service-head {
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .table-service-head th {
            padding: 5px 15px;
        }
        #invoice-print-zone {
            background: white;
            padding: 15px;
            margin: 90px auto 40px auto;
            max-width: 1025px;
        }
        .invoice-company-info{
            margin-top: 15px;
        }
        .invoice-company-info p{
            margin-bottom: 2px;
            font-weight: normal;
        }
    </style>
    <link href="{{ asset('module/user/css/user.css') }}" rel="stylesheet">
    <script>
        window.print();
    </script>
    @php $lang_local = app()->getLocale() @endphp

    <div id="invoice-print-zone">
        <table width="100%" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th width="50%">
                    @if( !empty($logo = setting_item('logo_invoice_id') ?? setting_item('logo_id') ))
                        <img style="max-width: 200px;" src="{{get_file_url( $logo ,"full")}}" alt="{{setting_item("site_title")}}">
                    @endif
                    <div class="invoice-company-info">
                        {!! setting_item_with_lang("invoice_company_info") !!}
                    </div>
                </th>
                <th width="50%" align="right" class="text-right">
                    <h2 class="invoice-text-title">{{__("INVOICE")}}</h2>
                    {{__('Invoice #: :number',['number'=>$booking->id])}}
                    <br>
                    @if(!empty($booking->ticket_number))

                                                      @php
    $tickets = $booking->ticket_number ? json_decode($booking->ticket_number, true) : [];
@endphp

<div><strong>TKT:</strong> 
    {{ !empty($tickets) ? implode(', ', $tickets) : 'N/A' }}
</div>

                 
                    <br>
                    @endif
                    {{__('Created: :date',['date'=>display_date($booking->created_at)])}}
                </th>
            </tr>
            <tr>
                <th width="50%">
                    {!! nl2br(setting_item('invoice_company')) !!}
                </th>
                <th width="50%" align="right" class="text-right">
                    <div class="invoice-amount">
                        <div class="label">{{__("Amount due:")}}</div>
                        <div class="amount" style="font-size: 24px;"><strong>{{format_money($booking->total - $booking->paid)}}</strong>
                        </div>
                    </div>
                </th>
            </tr>
            </thead>
        </table>
        <hr>
        <div class="customer-info">
            <h5><strong>{{__('Billing to:')}}</strong></h5>
            <span>{{$booking->first_name.' '.$booking->last_name}}</span>
            <br>
            <span>{{$booking->email}}</span><br>
            <span>{{$booking->phone}}</span><br>
            <span>{{$booking->address}}</span><br>
            <span>{{implode(', ',[$booking->city,$booking->state,$booking->zip_code,get_country_name($booking->country)])}}</span><br>
        </div>
        <hr>
        <div class="mt-2">
            <h3 class="service-name">FLIGHT ITINERARIES</h3>
        </div>
        <table width="100%" cellpadding="5" >
            <tr style="border-bottom: 1px solid grey;">
            <td style="border: 1px solid #DDD; font-size:11px">Flight</td>
                <td style="border: 1px solid #DDD; font-size:11px">Departure</td>
                <td style="border: 1px solid #DDD; font-size:11px">Arrival</td>
                <td style="border: 1px solid #DDD; font-size:11px">Departure AT</td>
                <td style="border: 1px solid #DDD; font-size:11px">Arrival AT</td>
                <td style="border: 1px solid #DDD; font-size:11px">Info</td>
            </tr>
            @foreach($booking['routes'] as $route)
                <tr>
                    <td style="border: 1px solid #DDD; font-size:11px">{{airline_from_code($booking->airline)}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px">{{airport_from_code($route->departure_iata_code)}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px"><span class="font-size-10 font-weight-normal text-gray-1">{{airport_from_code($route->arrival_iata_code)}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px">{{date('d M, h:i',strtotime($route->departure_at))}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px"><span class="font-size-10 font-weight-normal text-gray-1">{{date('d M, h:i',strtotime($route->arrival_at))}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px">{{$route->duration}}</td>
                </tr>
            @endforeach
        </table>
        <div class="mt-2">
            <h3 class="service-name">PASSENGER DETAILS</h3>
        </div>
        <table width="100%" cellpadding="5" >
            <tr style="border-bottom: 1px solid grey;">
                <td style="border: 1px solid #DDD; font-size:11px">Name</td>
                <td style="border: 1px solid #DDD; font-size:11px">Gender</td>
                <td style="border: 1px solid #DDD; font-size:11px">passenger Type</td>
          <td style="border: 1px solid #DDD; font-size:11px">Ticket #</td>
            </tr>
            @foreach($booking['passengers'] as $key => $passenger)
                <tr>
                    <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->first_name}} {{$passenger->last_name}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->gender}}</td>
                    <td style="border: 1px solid #DDD; font-size:11px">{{$passenger->traveler_type}}</td>
                   <td style="border: 1px solid #DDD; font-size:11px">@if(!empty($booking->ticket_number))
                    {{__($tickets[$key])}}
                    <br>
                    @endif</td> 
                </tr>
            @endforeach
        </table>
        <div class="mt-2">
            <h3 class="service-name">PRICE BREAKDOWN</h3>
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
        </table>
{{--        {{$service->email_new_booking_file}}--}}
        @if(!empty($service->email_new_booking_file))
            <div class="email_new_booking">
                @include($service->email_new_booking_file ?? '')
            </div>
        @endif
    </div>
@endpush
@push('js')
    <script type="text/javascript" src="{{ asset("module/user/js/user.js") }}"></script>
@endpush
