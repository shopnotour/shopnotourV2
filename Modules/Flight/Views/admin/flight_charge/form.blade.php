<div class="form-group">
    <label>{{__("Type")}}</label>
    <input type="text" value="{{ucfirst($row->type ?? '')}}" class="form-control" readonly>
    <small class="form-text text-muted">{{__("Type cannot be changed")}}</small>
</div>

<div class="form-group">
    <label>{{__("AIT Charge")}} <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="ait_charge" class="form-control" value="{{old('ait_charge', $row->ait_charge ?? 0)}}" placeholder="{{__('Enter AIT charge amount')}}" required>
</div>

<div class="form-group">
    <label>{{__("Service Charge")}} <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="service_charge" class="form-control" value="{{old('service_charge', $row->service_charge ?? 0)}}" placeholder="{{__('Enter service charge amount')}}" required>
</div>

<div class="form-group">
    <label>{{__("Segment Discount")}} <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="segment_discount" class="form-control" value="{{old('segment_discount', $row->segment_discount ?? 0)}}" placeholder="{{__('Enter segment discount amount')}}" required>
</div>

<div class="form-group">
    <label>{{__("Status")}}</label>
    <select name="status" class="form-control">
        <option value="active" {{($row->status ?? '') == 'active' ? 'selected' : ''}}>{{__("Active")}}</option>
        <option value="inactive" {{($row->status ?? '') == 'inactive' ? 'selected' : ''}}>{{__("Inactive")}}</option>
    </select>
</div>
