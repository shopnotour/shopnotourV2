@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-3">
            <h1 class="title-bar">{{__("Edit Discount: :name", ['name' => $row->name])}}</h1>
            <a href="{{route('flight.admin.discount.index')}}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{__("Back to List")}}
            </a>
        </div>

        @include('admin.message')

        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-title">{{__("Discount Information")}}</div>
                    <div class="panel-body">
                        <form action="{{route('flight.admin.discount.update', ['id' => $row->id])}}" method="post">
                            @csrf
                            @method('PUT')
                            @include('Flight::admin.discount.form')

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{route('flight.admin.discount.index')}}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> {{__("Cancel")}}
                                </a>
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <i class="fa fa-save"></i> {{__("Update Discount")}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
