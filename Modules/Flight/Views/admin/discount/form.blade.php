<style>
    /* Base Styles */
    .form-section {
        margin-bottom: 25px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .section-header i {
        font-size: 16px;
        color: #007bff;
        width: 20px;
        text-align: center;
    }

    .section-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #2c3e50;
    }

    /* Form Field */
    .form-field {
        margin-bottom: 15px;
    }

    .form-field label {
        display: block;
        font-weight: 600;
        font-size: 12px;
        color: #2c3e50;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-field label small {
        font-weight: 400;
        color: #6c757d;
        text-transform: none;
        font-size: 11px;
    }

    .form-field small {
        display: block;
        font-size: 11px;
        color: #6c757d;
        margin-top: 4px;
    }

    .required {
        color: #dc3545;
        font-weight: 700;
    }

    /* Input Styling */
    .form-control {
        height: 36px;
        padding: 6px 10px;
        font-size: 13px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.08);
        outline: none;
    }

    select.form-control {
        height: 36px;
        padding: 5px 10px;
    }

    /* Input Group */
    .input-group {
        display: flex;
        height: 36px;
    }

    .input-group .form-control {
        border-radius: 4px 0 0 4px;
        margin: 0;
    }

    .input-group-append {
        display: flex;
        align-items: center;
    }

    .input-group-text {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-left: none;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        border-radius: 0 4px 4px 0;
    }

    /* Row Spacing */
    .form-group {
        margin-bottom: 0 !important;
    }

    .form-group .row {
        margin-right: -6px;
        margin-left: -6px;
    }

    .form-group .row > [class*='col-'] {
        padding-left: 6px;
        padding-right: 6px;
    }

    /* Info Box */
    .info-box {
        background: #e7f3ff;
        border-left: 3px solid #17a2b8;
        padding: 10px 12px;
        border-radius: 4px;
        margin-top: 12px;
        font-size: 12px;
    }

    .info-box i {
        color: #17a2b8;
        margin-right: 6px;
        font-weight: 600;
    }

    .info-box strong {
        color: #17a2b8;
        font-size: 12px;
    }

    .info-box ul {
        margin: 6px 0 0 0;
        padding-left: 18px;
    }

    .info-box li {
        margin-bottom: 3px;
        line-height: 1.3;
        font-size: 11px;
    }

    .badge {
        display: inline-block;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 600;
        border-radius: 3px;
        margin-right: 4px;
        vertical-align: middle;
    }

    .badge-primary { background: #007bff; color: white; }
    .badge-info { background: #17a2b8; color: white; }
    .badge-success { background: #28a745; color: white; }

    /* Smart Search */
    .smart-search-container {
        position: relative;
    }

    .suggestions-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 150px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        margin-top: -1px;
    }

    .suggestions-list.active {
        display: block;
    }

    .suggestion-item {
        padding: 6px 10px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        font-size: 12px;
        transition: background 0.15s;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover {
        background: #f8f9fa;
    }

    .selected-item {
        display: inline-block;
        margin-top: 4px;
        padding: 3px 6px;
        background: #d4edda;
        color: #155724;
        border-radius: 3px;
        font-weight: 600;
        font-size: 11px;
    }

    /* Usage Info */
    .usage-info {
        background: #d4edda;
        border-left: 3px solid #28a745;
        padding: 8px 10px;
        border-radius: 4px;
        margin-top: 10px;
        font-size: 12px;
        color: #155724;
    }

    .usage-badge {
        background: #28a745;
        color: white;
        padding: 2px 4px;
        border-radius: 2px;
        font-weight: 600;
        margin: 0 2px;
        font-size: 11px;
    }

    .info-icon {
        cursor: help;
        color: #17a2b8;
        font-size: 11px;
        margin-left: 3px;
    }

    /* Divider */
    .section-divider {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 20px 0;
    }

    /* Date Input Compact */
    input[type="datetime-local"] {
        height: 36px;
        padding: 6px 10px;
        font-size: 13px;
    }

    /* Textarea */
    textarea.form-control {
        height: auto;
        min-height: 70px;
        font-size: 13px;
        padding: 8px 10px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .form-field {
            margin-bottom: 12px;
        }
    }

    @media (max-width: 992px) {
        .form-section {
            margin-bottom: 20px;
        }

        .section-header {
            margin-bottom: 12px;
            gap: 8px;
        }

        .section-header h5 {
            font-size: 14px;
        }

        .form-group .row {
            margin-right: -5px;
            margin-left: -5px;
        }

        .form-group .row > [class*='col-'] {
            padding-left: 5px;
            padding-right: 5px;
            margin-bottom: 12px;
        }
    }

    @media (max-width: 768px) {
        .form-field {
            margin-bottom: 10px;
        }

        .form-control {
            font-size: 12px;
            height: 34px;
        }

        input[type="datetime-local"] {
            height: 34px;
            font-size: 12px;
        }

        .section-header h5 {
            font-size: 13px;
        }

        .form-field label {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .form-field small {
            font-size: 10px;
        }
    }
</style>

<div class="form-wrapper">

    <!-- Discount Information Section -->
    <div class="form-section">
        <div class="section-header">
            <i class="fa fa-info-circle"></i>
            <h5>Discount Information</h5>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-field">
                        <label>Discount Name <span class="required">*</span></label>
                        <input type="text"
                               value="{{old('name',$row->name ?? '')}}"
                               placeholder="e.g., Summer Sale"
                               name="name"
                               class="form-control"
                               required>
                        <small>Internal reference</small>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-field">
                        <label>Promo Code</label>
                        <input type="text"
                               value="{{old('code',$row->code ?? '')}}"
                               placeholder="e.g., SUMMER2025"
                               name="code"
                               class="form-control"
                               style="text-transform: uppercase;">
                        <small>Customer-facing code</small>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-field">
                        <label>Source (GDS)</label>
                        <select name="gds_type" class="form-control">
                            <option value="">-- All Sources --</option>
                            @if(!empty($gdsOptions))
                                @foreach($gdsOptions as $provider => $name)
                                    <option value="{{ $provider }}"
                                        {{ old('gds_type', $row->gds_type ?? '') == $provider ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small>Leave empty for all</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="section-divider">

    <!-- Commission Section -->
    <div class="form-section">
        <div class="section-header">
            <i class="fa fa-dollar"></i>
            <h5>Commission & Discount</h5>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-field">
                        <label>Type <span class="required">*</span></label>
                        <select name="type" id="discount_type" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="percentage" {{old('type',$row->type ?? '') == 'percentage' ? 'selected' : ''}}>
                                Percentage (%)
                            </option>
                            <option value="fixed" {{old('type',$row->type ?? '') == 'fixed' ? 'selected' : ''}}>
                                Fixed (BDT)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-field">
                        <label>Commission <span class="required">*</span> <i class="fa fa-info-circle info-icon" title="Your commission"></i></label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   value="{{old('value',$row->value ?? '')}}"
                                   placeholder="0"
                                   name="value"
                                   class="form-control"
                                   required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="value_unit">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-field">
                        <label>Regular User <i class="fa fa-info-circle info-icon" title="Regular customer discount"></i></label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   value="{{old('user_value',$row->user_value ?? '')}}"
                                   placeholder="0"
                                   name="user_value"
                                   class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text" id="user_value_unit">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="form-field">
                        <label>B2B User <i class="fa fa-info-circle info-icon" title="B2B customer discount"></i></label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   value="{{old('b2b_user_value',$row->b2b_user_value ?? '')}}"
                                   placeholder="0"
                                   name="b2b_user_value"
                                   class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text" id="b2b_value_unit">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6" id="max_amount_group">
                    <div class="form-field">
                        <label>Max Limit <small>(% only)</small></label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   value="{{old('max_amount',$row->max_amount ?? '')}}"
                                   placeholder="0"
                                   name="max_amount"
                                   class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-field">
                        <label>Min Purchase</label>
                        <div class="input-group">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   value="{{old('min_purchase',$row->min_purchase ?? 0)}}"
                                   placeholder="0"
                                   name="min_purchase"
                                   class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text">BDT</span>
                            </div>
                        </div>
                        <small>Minimum ticket price</small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Routes Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fa fa-map"></i>
                <h5>Apply To Routes</h5>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>Airline</label>
                            <div class="smart-search-container">
                                <input type="text"
                                       id="airline_search"
                                       class="form-control"
                                       placeholder="Search airline..."
                                       autocomplete="off"
                                       value="@if(!empty($row->airline_code)){{ ($airlines[$row->airline_code] ?? '') }} ({{ $row->airline_code }})@endif">
                                <input type="hidden" name="airline_code" id="airline_code" value="{{old('airline_code', $row->airline_code ?? '')}}">
                                <div id="airline_suggestions" class="suggestions-list"></div>
                            </div>
                            <small id="airline_selected" class="selected-item" style="display: @if(!empty($row->airline_code)) inline-block @else none @endif;">
                                @if(!empty($row->airline_code))
                                    {{ ($airlines[$row->airline_code] ?? '') }} ({{ $row->airline_code }})
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>From Airport</label>
                            <div class="smart-search-container">
                                <input type="text"
                                       id="departure_search"
                                       class="form-control"
                                       placeholder="Search airport..."
                                       autocomplete="off"
                                       value="@if(!empty($row->departure_code)){{ ($airports[$row->departure_code] ?? '') }} ({{ $row->departure_code }})@endif">
                                <input type="hidden" name="departure_code" id="departure_code" value="{{old('departure_code', $row->departure_code ?? '')}}">
                                <div id="departure_suggestions" class="suggestions-list"></div>
                            </div>
                            <small id="departure_selected" class="selected-item" style="display: @if(!empty($row->departure_code)) inline-block @else none @endif;">
                                @if(!empty($row->departure_code))
                                    {{ ($airports[$row->departure_code] ?? '') }} ({{ $row->departure_code }})
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>To Airport</label>
                            <div class="smart-search-container">
                                <input type="text"
                                       id="arrival_search"
                                       class="form-control"
                                       placeholder="Search airport..."
                                       autocomplete="off"
                                       value="@if(!empty($row->arrival_code)){{ ($airports[$row->arrival_code] ?? '') }} ({{ $row->arrival_code }})@endif">
                                <input type="hidden" name="arrival_code" id="arrival_code" value="{{old('arrival_code', $row->arrival_code ?? '')}}">
                                <div id="arrival_suggestions" class="suggestions-list"></div>
                            </div>
                            <small id="arrival_selected" class="selected-item" style="display: @if(!empty($row->arrival_code)) inline-block @else none @endif;">
                                @if(!empty($row->arrival_code))
                                    {{ ($airports[$row->arrival_code] ?? '') }} ({{ $row->arrival_code }})
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <i class="fa fa-lightbulb"></i>
                <strong>Route Examples:</strong>
                <ul>
                    <li><span class="badge badge-primary">All</span> Leave all empty</li>
                    <li><span class="badge badge-info">From Only</span> All flights from airport</li>
                    <li><span class="badge badge-info">To Only</span> All flights to airport</li>
                    <li><span class="badge badge-success">Specific</span> Both selected</li>
                </ul>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Validity Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fa fa-calendar"></i>
                <h5>Validity Period</h5>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-field">
                            <label>Valid From</label>
                            <input type="datetime-local"
                                   value="{{old('valid_from', $row->valid_from ?? '')}}"
                                   name="valid_from"
                                   class="form-control">
                            <small>Leave empty for immediate start</small>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-field">
                            <label>Valid To</label>
                            <input type="datetime-local"
                                   value="{{old('valid_to', $row->valid_to ?? '')}}"
                                   name="valid_to"
                                   class="form-control">
                            <small>Leave empty for no expiry</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Limits Section -->
        <div class="form-section">
            <div class="section-header">
                <i class="fa fa-shield"></i>
                <h5>Usage Limits</h5>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>Total Uses</label>
                            <input type="number"
                                   min="0"
                                   value="{{old('usage_limit',$row->usage_limit ?? '')}}"
                                   placeholder="Unlimited"
                                   name="usage_limit"
                                   class="form-control">
                            <small>Total times usable</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>Per User</label>
                            <input type="number"
                                   min="0"
                                   value="{{old('per_user_limit',$row->per_user_limit ?? '')}}"
                                   placeholder="Unlimited"
                                   name="per_user_limit"
                                   class="form-control">
                            <small>Max per customer</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-field">
                            <label>Priority <i class="fa fa-info-circle info-icon" title="Higher = applies first"></i></label>
                            <input type="number"
                                   min="0"
                                   max="100"
                                   value="{{old('priority',$row->priority ?? 0)}}"
                                   name="priority"
                                   class="form-control">
                            <small>0-100 (higher first)</small>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($row->usage_count))
                <div class="usage-info">
                    <i class="fa fa-check-circle"></i>
                    Used: <span class="usage-badge">{{$row->usage_count}}</span>
                    @if($row->usage_limit)
                        / {{$row->usage_limit}}
                    @endif
                </div>
            @endif
        </div>

        <hr class="section-divider">

        <!-- Additional Charges -->
        <div class="form-section">
            <div class="section-header">
                <i class="fa fa-plus"></i>
                <h5>Additional Charges</h5>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-field">
                            <label>AIT Charge</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       value="{{old('ait_charge', $row->ait_charge ?? 0.3)}}"
                                       name="ait_charge"
                                       class="form-control"
                                       placeholder="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-field">
                            <label>Service Charge</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       value="{{old('service_charge', $row->service_charge ?? 200)}}"
                                       name="service_charge"
                                       class="form-control"
                                       placeholder="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-field">
                            <label>Segment Discount</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       value="{{old('segment_discount', $row->segment_discount ?? 0)}}"
                                       name="segment_discount"
                                       class="form-control"
                                       placeholder="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-field">
                            <label>User Seg Discount</label>
                            <div class="input-group">
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       value="{{old('user_seg_discount', $row->user_seg_discount ?? 0)}}"
                                       name="user_seg_discount"
                                       class="form-control"
                                       placeholder="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">BDT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="section-divider">

        <!-- Status & Description -->
        <div class="form-section">
            <div class="section-header">
                <i class="fa fa-cog"></i>
                <h5>Status & Notes</h5>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-field">
                            <label>Status <span class="required">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{old('status',$row->status ?? 'active') == 'active' ? 'selected' : ''}}>
                                    ✓ Active
                                </option>
                                <option value="inactive" {{old('status',$row->status ?? '') == 'inactive' ? 'selected' : ''}}>
                                    ✗ Inactive
                                </option>
                                <option value="expired" {{old('status',$row->status ?? '') == 'expired' ? 'selected' : ''}}>
                                    ⏱ Expired
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label>Description</label>
                <textarea name="description"
                          class="form-control"
                          rows="2"
                          placeholder="Internal notes...">{{old('description',$row->description ?? '')}}</textarea>
                <small>For internal reference only</small>
            </div>
        </div>

    </div> <!-- .form-wrapper -->
    </div> <!-- .form-wrapper -->

    <!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const discountType = document.getElementById('discount_type');
        const maxAmountGroup = document.getElementById('max_amount_group');
        const valueUnit = document.getElementById('value_unit');
        const userValueUnit = document.getElementById('user_value_unit');
        const b2bValueUnit = document.getElementById('b2b_value_unit');

        function updateDiscountFields() {
            const type = discountType.value;

            if (type === 'percentage') {
                maxAmountGroup.style.display = 'block';
                valueUnit.textContent = '%';
                userValueUnit.textContent = '%';
                b2bValueUnit.textContent = '%';
            } else if (type === 'fixed') {
                maxAmountGroup.style.display = 'none';
                valueUnit.textContent = 'BDT';
                userValueUnit.textContent = 'BDT';
                b2bValueUnit.textContent = 'BDT';
            }
        }

        discountType.addEventListener('change', updateDiscountFields);
        updateDiscountFields();

        // Smart Search Setup with Caching
        const airlines = @json($airlines ?? []);
        const airports = @json($airports ?? []);

        // Cache filtered results
        const cache = {};

        setupSmartSearch('airline_search', 'airline_code', 'airline_selected', airlines);
        setupSmartSearch('departure_search', 'departure_code', 'departure_selected', airports);
        setupSmartSearch('arrival_search', 'arrival_code', 'arrival_selected', airports);

        function setupSmartSearch(inputId, hiddenId, selectedId, dataList) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const selected = document.getElementById(selectedId);
            const suggestionsId = inputId.replace('_search', '_suggestions');
            const suggestions = document.getElementById(suggestionsId);

            if (!input) return;

            let debounceTimer;
            let lastQuery = '';

            input.addEventListener('input', function(e) {
                // Clear previous timer
                clearTimeout(debounceTimer);

                const query = e.target.value.toLowerCase().trim();

                if (query.length < 1) {
                    suggestions.classList.remove('active');
                    return;
                }

                // Debounce search by 200ms
                debounceTimer = setTimeout(() => {
                    // Check cache first
                    const cacheKey = inputId + '_' + query;
                    if (cache[cacheKey]) {
                        renderSuggestions(cache[cacheKey], dataList);
                        return;
                    }

                    // Convert to array for faster filtering
                    const dataArray = Object.entries(dataList);
                    const filtered = dataArray
                        .filter(([key, value]) => {
                            return key.toLowerCase().includes(query) ||
                                value.toLowerCase().includes(query);
                        })
                        .slice(0, 8)
                        .map(([key, value]) => ({ key, value }));

                    // Cache the result
                    cache[cacheKey] = filtered;

                    if (filtered.length === 0) {
                        suggestions.classList.remove('active');
                        return;
                    }

                    renderSuggestions(filtered, dataList);
                }, 200);

                function renderSuggestions(filteredItems, dataList) {
                    suggestions.innerHTML = '';

                    filteredItems.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = item.value + ' (' + item.key + ')';
                        div.onclick = function() {
                            input.value = item.value + ' (' + item.key + ')';
                            hidden.value = item.key;
                            selected.textContent = item.value + ' (' + item.key + ')';
                            selected.style.display = 'inline-block';
                            suggestions.classList.remove('active');
                        };
                        suggestions.appendChild(div);
                    });

                    suggestions.classList.add('active');
                }
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.classList.remove('active');
                }
            });
        }
    });
</script>
