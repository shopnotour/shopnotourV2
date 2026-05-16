@extends('admin.layouts.app')

@section('content')
    <form action="{{route('user.admin.wallet.store',['id'=>$row->id])}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    <div class="d-flex justify-content-between mb20">
                        <div class="">
                            <h1 class="title-bar">{{__('Add credit for :name',['name'=>$row->display_name])}}</h1>
                        </div>
                    </div>
                    @include('admin.message')
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('Add credit')}}</strong></div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{__("Current Balance")}}</label>
                                        <input type="number" readonly value="{{$row->balance}}" step="0.1" class="form-control">
                                    </div>
                                    <div class="row">
                                            <div class="form-group col-md-6">
                                                <label>{{__("Credit Amount")}}</label>
                                                <input type="number" name="credit_amount" value="" placeholder="0" step="0.1" class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6" >
                                                <label>{{__("Transaction Type")}}</label>
                                                <select class="form-control" name="transaction_type" required >
                                                    <option value="">Select Transaction Type</option>
                                                    <option value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                    <option value="bank">Bank</option>
                                                    <option value="bkash">Bkash</option>
                                                    <option value="nagad">Nagad</option>
                                                    <option value="rocket">Rocket</option>
                                                    <option value="others">Others</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>{{__("References")}}</label>
                                                <input type="text" name="reference" value=""  class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>{{__("Deposit Date")}}</label>
                                                <input type="date" name="deposit_date" value="today()"  class="form-control" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>{{__("Remarks")}}</label>
                                                <textarea name="remarks" id="" class="form-control" rows="5"></textarea>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>{{__("Image")}}</label>
                                                <div class="">
                                                    <div class="input-group">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-default btn-file">
                                                        {{__("Browse")}}… <input type="file" id="image-upload" name="attachment_id" accept="image/*">
                                                    </span>
                                                </span>
                                                        <input type="text"
                                                               data-error="{{__("Error upload...")}}"
                                                               data-loading="{{__("Loading...")}}"
                                                               class="form-control text-view"
                                                               readonly
                                                               value="{{ get_file_url(old('avatar_id', $row->attachment_id)) ? basename(get_file_url(old('avatar_id', $row->attachment_id))) : __('No Image') }}">
                                                    </div>
                                                    <input type="hidden" class="form-control" name="avatar_id" value="{{ old('avatar_id', $row->attachment_id) ?? '' }}">
                                                    <img class="image-demo"
                                                         src="{{ get_file_url(old('avatar_id', $row->attachment_id)) ?? $row->getAvatarUrl() ?? asset('images/placeholder.png') }}"
                                                         style="max-width: 200px;max-height: 150px; margin-top: 10px; {{ get_file_url(old('avatar_id', $row->attachment_id)) || $row->getAvatarUrl() ? '' : 'display:none;' }}"/>
                                                </div>
                                            </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button class="btn btn-primary" type="submit">{{ __('Add now')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
