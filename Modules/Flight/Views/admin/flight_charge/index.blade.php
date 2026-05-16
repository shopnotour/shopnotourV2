@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Booking Charges Management")}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-title">{{__("All Booking Charges")}}</div>
                    <div class="panel-body">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{__("Type")}}</th>
                                <th>{{__("AIT Charge")}}</th>
                                <th>{{__("Service Charge")}}</th>
                                <th>{{__("Segment Discount")}}</th>
                                <th>{{__("Status")}}</th>
                                <th class="date">{{__("Updated At")}}</th>
                                <th>{{__("Action")}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rows as $item)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{$item->type == 'domestic' ? 'info' : 'primary'}}">
                                            {{ucfirst($item->type)}}
                                        </span>
                                    </td>
                                    <td>{{$item->ait_charge}}</td>
                                    <td>{{format_money($item->service_charge)}}</td>
                                    <td>{{format_money($item->segment_discount)}}</td>
                                    <td>
                                        <span class="badge badge-{{$item->status == 'active' ? 'success' : 'secondary'}}">
                                            {{ucfirst($item->status)}}
                                        </span>
                                    </td>
                                    <td>{{display_date($item->updated_at)}}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" href="{{route('flight.admin.flight_charges.edit',['id'=>$item->id])}}">
                                            <i class="fa fa-edit"></i> {{__('Edit')}}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{__("No data")}}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
