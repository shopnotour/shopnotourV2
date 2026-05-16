<div class="form-group">
    <label>{{__("Name")}}</label>
    <input type="text" value="{{$row->name??''}}" placeholder="{{__("Name")}}" name="name" class="form-control">
</div>
<div class="form-group">
    <label>{{__("Designator")}}</label>
    <input type="text" value="{{$row->designator??''}}" placeholder="{{__("Degignator")}}" name="designator" class="form-control">
</div>
<div class="form-group">
    <label>{{__("Commission")}}</label>
    <input type="text" value="{{$row->airline_commission??''}}" placeholder="{{__("Commission")}}" name="airline_commission" class="form-control">
</div>
<div class="form-group">
    <label class="control-label">{{__("Logo")}}</label>
    <div class="form-group-image">
        {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id??'') !!}
    </div>
</div>