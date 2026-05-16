@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Edit Booking Charges: :type",['type'=>ucfirst($row->type)])}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="panel">
                    <div class="panel-title">{{__("Edit Charges")}}</div>
                    <div class="panel-body">
                        <form action="{{route('flight.admin.flight_charges.update',['id'=>$row->id])}}" method="post">
                            @csrf
                            @method('PUT')
                            @include('Flight::admin.flight_charge.form')
                            <hr>
                            <div class="d-flex justify-content-between">
                                <a href="{{route('flight.admin.flight_charges.index')}}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> {{__("Back")}}
                                </a>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-save"></i> {{__("Update")}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
