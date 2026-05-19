{{--@extends('admin.layouts.app')--}}

{{--@section('content')--}}
{{--    <form action="{{route('user.admin.store',['id'=>$row->id ?? -1])}}" method="post" class="needs-validation" novalidate>--}}
{{--        @csrf--}}
{{--        <div class="container">--}}
{{--            <div class="d-flex justify-content-between mb20">--}}
{{--                <div class="">--}}
{{--                    <h1 class="title-bar">{{$row->id ? 'Edit: '.$row->getDisplayName() : 'Add new user'}}</h1>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            @include('admin.message')--}}
{{--            <div class="row">--}}
{{--                <div class="col-md-12">--}}
{{--                    <div class="panel">--}}
{{--                        <div class="panel-title"><strong>{{ __('User Info')}}</strong></div>--}}
{{--                        <div class="panel-body">--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("Business name")}}</label>--}}
{{--                                        <input type="text" value="{{old('business_name',$row->business_name)}}" required name="business_name" placeholder="{{__("Business name")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('E-mail')}}</label>--}}
{{--                                        <input type="email" required value="{{old('email',$row->email)}}" placeholder="{{ __('Email')}}" name="email" class="form-control"  >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("User name")}}</label>--}}
{{--                                        <input type="text" name="user_name" required value="{{old('user_name',$row->user_name)}}" placeholder="{{__("User name")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="row">--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("First name")}}</label>--}}
{{--                                        <input type="text" required value="{{old('first_name',$row->first_name)}}" name="first_name" placeholder="{{__("First name")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("Last name")}}</label>--}}
{{--                                        <input type="text" required value="{{old('last_name',$row->last_name)}}" name="last_name" placeholder="{{__("Last name")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Phone Number')}}</label>--}}
{{--                                        <input type="text" value="{{old('phone',$row->phone)}}" placeholder="{{ __('Phone')}}" name="phone" class="form-control" required   >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('IATA Number')}}</label>--}}
{{--                                        <input type="text" value="{{old('iata_number',$row->iata_number)}}" placeholder="{{ __('IATA Number')}}" name="iata_number" class="form-control" required   >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Civil Aviation Number')}}</label>--}}
{{--                                        <input type="text" value="{{old('civil_aviation_number',$row->civil_aviation_number)}}" placeholder="{{ __('Civil Aviation Number')}}" name="civil_aviation_number" class="form-control" required   >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Trade License Number')}}</label>--}}
{{--                                        <input type="text" value="{{old('trade_license_number',$row->trade_license_number)}}" placeholder="{{ __('Trade License Number')}}" name="trade_license_number" class="form-control" required   >--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Birthday')}}</label>--}}
{{--                                        <input type="text" value="{{ old('birthday',$row->birthday ? date("Y/m/d",strtotime($row->birthday)) :'') }}" placeholder="{{ __('Birthday')}}" name="birthday" class="form-control has-datepicker input-group date">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Address Line 1')}}</label>--}}
{{--                                        <input type="text" value="{{old('address',$row->address)}}" placeholder="{{ __('Address')}}" name="address" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{ __('Address Line 2')}}</label>--}}
{{--                                        <input type="text" value="{{old('address2',$row->address2)}}" placeholder="{{ __('Address 2')}}" name="address2" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("City")}}</label>--}}
{{--                                        <input type="text" value="{{old('city',$row->city)}}" name="city" placeholder="{{__("City")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("State")}}</label>--}}
{{--                                        <input type="text" value="{{old('state',$row->state)}}" name="state" placeholder="{{__("State")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label class="">{{__("Country")}}</label>--}}
{{--                                        <select name="country" class="form-control" id="country-sms-testing" required>--}}
{{--                                            <option value="">{{__('-- Select --')}}</option>--}}
{{--                                            @foreach(get_country_lists() as $id=>$name)--}}
{{--                                                <option @if($row->country==$id) selected @endif value="{{$id}}">{{$name}}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__("Zip Code")}}</label>--}}
{{--                                        <input type="text" value="{{old('zip_code',$row->zip_code)}}" name="zip_code" placeholder="{{__("Zip Code")}}" class="form-control">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="form-group">--}}
{{--                                <label class="control-label">{{ __('Biographical')}}</label>--}}
{{--                                <div class="">--}}
{{--                                    <textarea name="bio" class="d-none has-ckeditor" cols="30" rows="10">{{old('bio',$row->bio)}}</textarea>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="row">--}}
{{--                                <div class="col-md-4">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <div class="panel-title"><strong>{{ __('IATA File') }}</strong></div>--}}
{{--                                        <div class="panel-body">--}}
{{--                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('iata_file_id', old('iata_file_id', $row->iata_file_id)) !!}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-4">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <div class="panel-title"><strong>{{ __('Civil Aviation File') }}</strong></div>--}}
{{--                                        <div class="panel-body">--}}
{{--                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('civil_aviation_file_id', old('civil_aviation_file_id', $row->civil_aviation_file_id)) !!}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-4">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <div class="panel-title"><strong>{{ __('Trade License File') }}</strong></div>--}}
{{--                                        <div class="panel-body">--}}
{{--                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('trade_license_file_id', old('trade_license_file_id', $row->trade_license_file_id)) !!}--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                --}}{{-- <div class="col-md-3">--}}
{{--                    <div class="panel">--}}
{{--                        <div class="panel-title"><strong>{{ __('Publish')}}</strong></div>--}}
{{--                        <div class="panel-body">--}}
{{--                            <div class="form-group">--}}
{{--                                <label>{{__('Status')}}</label>--}}
{{--                                <select required class="custom-select" name="status">--}}
{{--                                    <option @if(old('status',$row->status) =='publish') selected @endif value="publish">{{ __('Publish')}}</option>--}}
{{--                                    <option @if(old('status',$row->status) =='blocked') selected @endif value="blocked">{{ __('Blocked')}}</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                            @if(is_admin())--}}
{{--                                @if(empty($user_type) or $user_type != 'vendor')--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label>{{__('Role')}} <span class="text-danger">*</span></label>--}}
{{--                                        <select required class="form-control" name="role_id">--}}
{{--                                            <option value="">{{ __('-- Select --')}}</option>--}}
{{--                                            @foreach($roles as $role)--}}
{{--                                                <option value="{{$role->id}}" @if(old('role_id',$row->role_id) == $role->id) selected @elseif(old('role_id')  == $role->id ) selected @elseif(request()->input("user_type")  == strtolower($role->name) ) selected @endif >{{ucfirst($role->name)}}</option>--}}
{{--                                            @endforeach--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                                <div class="form-group">--}}
{{--                                    <label>{{__('Email Verified?')}}</label>--}}
{{--                                    <select  class="form-control" name="is_email_verified">--}}
{{--                                        <option value="">{{ __('No')}}</option>--}}
{{--                                        <option @if(old('is_email_verified',$row->email_verified_at ? 1 : 0)) selected @endif value="1">{{__('Yes')}}</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="panel">--}}
{{--                        <div class="panel-title"><strong>{{ __('Vendor')}}</strong></div>--}}
{{--                        <div class="panel-body">--}}
{{--                            <div class="form-group">--}}
{{--                                <label>{{__('Vendor Commission Type')}}</label>--}}
{{--                                <div class="form-controls">--}}
{{--                                    <select name="vendor_commission_type" class="form-control">--}}
{{--                                        <option value="">{{__("Default")}}</option>--}}
{{--                                        <option value="percent" {{old("vendor_commission_type",($row->vendor_commission_type ?? '')) == 'percent' ? 'selected' : ''  }}>{{__('Percent')}}</option>--}}
{{--                                        <option value="amount" {{old("vendor_commission_type",($row->vendor_commission_type ?? '')) == 'amount' ? 'selected' : ''  }}>{{__('Amount')}}</option>--}}
{{--                                        <option value="disable" {{old("vendor_commission_type",($row->vendor_commission_type ?? '')) == 'disable' ? 'selected' : ''  }}>{{__('Disable Commission')}}</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group">--}}
{{--                                <label>{{__('Vendor commission value')}}</label>--}}
{{--                                <div class="form-controls">--}}
{{--                                    <input type="text" class="form-control" name="vendor_commission_amount" value="{{old("vendor_commission_amount",($row->vendor_commission_amount ?? '')) }}">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="panel">--}}
{{--                        <div class="panel-title"><strong>{{ __('Avatar')}}</strong></div>--}}
{{--                        <div class="panel-body">--}}
{{--                            <div class="form-group">--}}
{{--                                {!! \Modules\Media\Helpers\FileHelper::fieldUpload('avatar_id',old('avatar_id',$row->avatar_id)) !!}--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div> --}}
{{--            </div>--}}
{{--            <hr>--}}
{{--            <div class="d-flex justify-content-between">--}}
{{--                <span></span>--}}
{{--                @if(Auth::user()->hasPermission('user_update'))--}}
{{--                    <button class="btn btn-primary" type="submit">{{ __('Save Change')}}</button>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </form>--}}
{{--@endsection--}}


@extends('admin.layouts.app')

@section('content')

    {{-- File Viewer Modal --}}
    <div class="modal fade" id="fileViewerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileViewerTitle">Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="fileViewerBody" style="min-height:400px;"></div>
                <div class="modal-footer">
                    <a href="#" id="fileViewerDownload" target="_blank" class="btn btn-info">
                        <i class="fa fa-external-link"></i> {{ __('Open in new tab') }}
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('user.admin.store', ['id' => $row->id ?? -1]) }}" method="post" class="needs-validation" novalidate>
        @csrf
        <div class="container">
            <div class="d-flex justify-content-between mb20">
                <h1 class="title-bar">{{ $row->id ? 'Edit: '.$row->getDisplayName() : 'Add new user' }}</h1>
            </div>

            @include('admin.message')

            {{-- Vendor Request Status Bar --}}
{{--            @php--}}
{{--                $vendorRequest = \Modules\Vendor\Models\VendorRequest::where('user_id', $row->id)--}}
{{--                    ->orderBy('id','desc')--}}
{{--                    ->first();--}}
{{--            @endphp--}}

            @if(!empty($vendorRequest))
                <div class="panel">
                    <div class="panel-title"><strong>{{ __('Vendor Request') }}</strong></div>
                    <div class="panel-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <label>{{ __('Status') }}</label>
                                <div>
                            <span class="badge badge-{{ $vendorRequest->status }}">
                                {{ ucfirst($vendorRequest->status) }}
                            </span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Role Request') }}</label>
                                <div>{{ $vendorRequest->role ? ucfirst($vendorRequest->role->name) : '-' }}</div>
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Date Request') }}</label>
                                <div>{{ display_date($vendorRequest->created_at) }}</div>
                            </div>
                            <div class="col-md-2">
                                <label>{{ __('Approved By') }}</label>
                                <div>{{ optional($vendorRequest->approvedBy)->getDisplayName() ?? '-' }}</div>
                            </div>
                            <div class="col-md-4 text-right">
                                @if($vendorRequest->status !== 'approved' && $vendorRequest->status !== 'cancelled')
                                    <a href="#"
                                       class="btn btn-success approve-vendor-btn"
                                       data-id="{{ $vendorRequest->id }}">
                                        <i class="fa fa-check"></i> {{ __('Approve') }}
                                    </a>
                                @endif

                                @if($vendorRequest->status !== 'cancelled')
                                    @php
                                        $cancelConfirm = $vendorRequest->status === 'approved'
                                            ? __('This will reset user role to customer and deduct bonus. Are you sure?')
                                            : __('Are you sure you want to delete this request?');
                                    @endphp
                                    <a href="{{ route('user.admin.upgradeCancel', ['id' => $vendorRequest->id]) }}"
                                       class="btn btn-danger cancel-request ml-2"
                                       data-confirm="{{ $cancelConfirm }}">
                                        <i class="fa fa-times"></i>
                                        {{ $vendorRequest->status === 'approved' ? __('Cancel') : __('Delete') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- User Info --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('User Info') }}</strong></div>
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("Business name") }}</label>
                                        <input type="text" value="{{ old('business_name', $row->business_name) }}"
                                               required name="business_name"
                                               placeholder="{{ __("Business name") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('E-mail') }}</label>
                                        <input type="email" required
                                               value="{{ old('email', $row->email) }}"
                                               placeholder="{{ __('Email') }}"
                                               name="email" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("User name") }}</label>
                                        <input type="text" name="user_name" required
                                               value="{{ old('user_name', $row->user_name) }}"
                                               placeholder="{{ __("User name") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("First name") }}</label>
                                        <input type="text" required
                                               value="{{ old('first_name', $row->first_name) }}"
                                               name="first_name"
                                               placeholder="{{ __("First name") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("Last name") }}</label>
                                        <input type="text" required
                                               value="{{ old('last_name', $row->last_name) }}"
                                               name="last_name"
                                               placeholder="{{ __("Last name") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Phone Number') }}</label>
                                        <input type="text"
                                               value="{{ old('phone', $row->phone) }}"
                                               placeholder="{{ __('Phone') }}"
                                               name="phone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('IATA Number') }}</label>
                                        <input type="text"
                                               value="{{ old('iata_number', $row->iata_number) }}"
                                               placeholder="{{ __('IATA Number') }}"
                                               name="iata_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Civil Aviation Number') }}</label>
                                        <input type="text"
                                               value="{{ old('civil_aviation_number', $row->civil_aviation_number) }}"
                                               placeholder="{{ __('Civil Aviation Number') }}"
                                               name="civil_aviation_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Trade License Number') }}</label>
                                        <input type="text"
                                               value="{{ old('trade_license_number', $row->trade_license_number) }}"
                                               placeholder="{{ __('Trade License Number') }}"
                                               name="trade_license_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Birthday') }}</label>
                                        <input type="text"
                                               value="{{ old('birthday', $row->birthday ? date('Y/m/d', strtotime($row->birthday)) : '') }}"
                                               placeholder="{{ __('Birthday') }}"
                                               name="birthday"
                                               class="form-control has-datepicker input-group date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Address Line 1') }}</label>
                                        <input type="text"
                                               value="{{ old('address', $row->address) }}"
                                               placeholder="{{ __('Address') }}"
                                               name="address" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Address Line 2') }}</label>
                                        <input type="text"
                                               value="{{ old('address2', $row->address2) }}"
                                               placeholder="{{ __('Address 2') }}"
                                               name="address2" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("City") }}</label>
                                        <input type="text"
                                               value="{{ old('city', $row->city) }}"
                                               name="city"
                                               placeholder="{{ __("City") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("State") }}</label>
                                        <input type="text"
                                               value="{{ old('state', $row->state) }}"
                                               name="state"
                                               placeholder="{{ __("State") }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("Country") }}</label>
                                        <select name="country" class="form-control">
                                            <option value="">{{ __('-- Select --') }}</option>
                                            @foreach(get_country_lists() as $cid => $cname)
                                                <option @if($row->country == $cid) selected @endif value="{{ $cid }}">{{ $cname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __("Zip Code") }}</label>
                                        <input type="text"
                                               value="{{ old('zip_code', $row->zip_code) }}"
                                               name="zip_code"
                                               placeholder="{{ __("Zip Code") }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">{{ __('Biographical') }}</label>
                                <textarea name="bio" class="d-none has-ckeditor" cols="30" rows="10">{{ old('bio', $row->bio) }}</textarea>
                            </div>

                            {{-- Files --}}
                            <div class="row">
                                {{-- IATA File --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="panel-title"><strong>{{ __('IATA File') }}</strong></div>
                                        <div class="panel-body">
                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('iata_file_id', old('iata_file_id', $row->iata_file_id)) !!}
                                            @if($row->iata_file_id)
                                                @php $iataFile = \Modules\Media\Models\MediaFile::find($row->iata_file_id); @endphp
                                                @if(!empty($iataFile))
                                                    {{-- Debug line --}}
                                                    <small class="text-muted">Path: {{ $iataFile->file_path }}</small>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary mt-2 preview-file-btn"
                                                            data-url="{{ $iataFile->file_path }}"
                                                            data-name="{{ __('IATA File') }}">
                                                        <i class="fa fa-eye"></i> {{ __('Preview') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Civil Aviation File --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="panel-title"><strong>{{ __('Civil Aviation File') }}</strong></div>
                                        <div class="panel-body">
                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('civil_aviation_file_id', old('civil_aviation_file_id', $row->civil_aviation_file_id)) !!}
                                            @if($row->civil_aviation_file_id)
                                                @php $civilFile = \Modules\Media\Models\MediaFile::find($row->civil_aviation_file_id); @endphp
                                                @if(!empty($civilFile))
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary mt-2 preview-file-btn"
                                                            data-url="{{ $civilFile->file_path }}"
                                                            data-name="{{ __('Civil Aviation File') }}">
                                                        <i class="fa fa-eye"></i> {{ __('Preview') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Trade License File --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="panel-title"><strong>{{ __('Trade License File') }}</strong></div>
                                        <div class="panel-body">
                                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload('trade_license_file_id', old('trade_license_file_id', $row->trade_license_file_id)) !!}
                                            @if($row->trade_license_file_id)
                                                @php $tradeFile = \Modules\Media\Models\MediaFile::find($row->trade_license_file_id); @endphp
                                                @if(!empty($tradeFile))
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary mt-2 preview-file-btn"
                                                            data-url="{{ $tradeFile->file_path }}"
                                                            data-name="{{ __('Trade License File') }}">
                                                        <i class="fa fa-eye"></i> {{ __('Preview') }}
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- End Files --}}

                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between">
                <a href="{{ route('user.admin.upgrade') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                </a>

                <div class="d-flex" style="gap:8px;">

                    {{-- Approve button --}}
{{--                    @if(!empty($vendorRequest) && $vendorRequest->status !== 'approved' && $vendorRequest->status !== 'cancelled')--}}
                        <a href="#"
                           class="btn btn-success approve-vendor-btn"
                           data-id="{{ $vendorRequest->id }}">
                            <i class="fa fa-check"></i> {{ __('Approve') }}
                        </a>
{{--                    @endif--}}

                    {{-- Cancel/Delete button --}}
{{--                    @if(!empty($vendorRequest) && $vendorRequest->status !== 'cancelled')--}}
                        @php
                            $cancelConfirm = $vendorRequest->status === 'approved'
                                ? __('This will reset user role to customer and deduct bonus. Are you sure?')
                                : __('Are you sure you want to delete this request?');
                        @endphp
                        <a href="{{ route('user.admin.upgradeCancel', ['id' => $vendorRequest->id]) }}"
                           class="btn btn-danger cancel-request"
                           data-confirm="{{ $cancelConfirm }}">
                            <i class="fa fa-times"></i>
                            {{ $vendorRequest->status === 'approved' ? __('Cancel') : __('Delete') }}
                        </a>
{{--                    @endif--}}

                    {{-- Save button --}}
                    @if(Auth::user()->hasPermission('user_update'))
                        <button class="btn btn-primary" type="submit">
                            <i class="fa fa-save"></i> {{ __('Save Change') }}
                        </button>
                    @endif

                </div>
            </div>

        </div>
    </form>
@endsection

@push('js')
    <script>
        $(document).ready(function () {

            // ── File Preview ───────────────────────────────────────────
            $(document).on('click', '.preview-file-btn', function () {
                var url  = $(this).data('url');
                var name = $(this).data('name');
                var ext  = url.split('.').pop().toLowerCase().split('?')[0];

                // ✅ Relative path → full URL
                if (url.indexOf('http') !== 0) {
                    url = '{{ rtrim(asset('/'), '/') }}/' + url.replace(/^\//, '');
                }

                $('#fileViewerTitle').text(name);
                $('#fileViewerDownload').attr('href', url);
                $('#fileViewerBody').html(
                    '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>'
                );
                $('#fileViewerModal').modal('show');

                var content = '';

                if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) {
                    content = '<img src="' + url + '" class="img-fluid" style="max-height:70vh;" '
                        + 'onerror="$(\'#fileViewerBody\').html(\'<p class=\\\"text-danger\\\">Image could not be loaded.</p>\')">';
                } else if (ext === 'pdf') {
                    content = '<iframe src="' + url + '" width="100%" height="550px" style="border:none;"></iframe>';
                } else {
                    content = '<div class="p-4">'
                        + '<p class="text-muted mb-3">{{ __("Preview not available for this file type.") }}</p>'
                        + '<a href="' + url + '" target="_blank" class="btn btn-primary">'
                        + '<i class="fa fa-download"></i> {{ __("Download file") }}</a>'
                        + '</div>';
                }

                $('#fileViewerBody').html(content);
            });

            // Modal close এ content clear
            $('#fileViewerModal').on('hidden.bs.modal', function () {
                $('#fileViewerBody').html('');
            });

            // ── Cancel / Delete ────────────────────────────────────────
            $(document).on('click', '.cancel-request', function (e) {
                e.preventDefault();
                if (!confirm($(this).data('confirm'))) return;
                window.location.href = $(this).attr('href');
            });

            // ── Approve ────────────────────────────────────────────────
            $(document).on('click', '.approve-vendor-btn', function (e) {
                e.preventDefault();
                if (!confirm('{{ __("Are you sure you want to approve?") }}')) return;

                var id = $(this).data('id');
                $('<form method="POST" action="{{ route('user.admin.userUpgradeRequestApproved') }}">')
                    .append('{{ csrf_field() }}')
                    .append('<input type="hidden" name="action" value="approved">')
                    .append('<input type="hidden" name="ids[]" value="' + id + '">')
                    .appendTo('body')
                    .submit();
            });

        });
    </script>
@endpush
