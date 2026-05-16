{{-- ── BONUS SECTION ── --}}
<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __("Bonus Settings") }}</h3>
        <p class="form-group-desc">{{ __('Configure bonus amount for users') }}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                <div class="form-group">
                    <label>{{ __("Enable Bonus") }}</label>
                    <div class="form-controls">
                        <label>
                            <input type="checkbox" name="bonus_enabled" value="1"
                                   @if(setting_item('bonus_enabled') == 1) checked @endif>
                            {{ __('Enable') }}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Bonus Amount") }}</label>
                    <div class="form-controls">
                        <input type="number" step="0.01" class="form-control" name="bonus_amount"
                               value="{{ setting_item('bonus_amount', 0) }}">
                        <p><i>{{ __("Amount to credit as bonus") }}</i></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Apply Per") }}</label>
                    <div class="form-controls">
                        <select name="bonus_per_type" class="form-control">
                            <option value="booking" @if(setting_item('bonus_per_type') == 'booking') selected @endif>{{ __('Per Booking') }}</option>
                            <option value="ticket"  @if(setting_item('bonus_per_type') == 'ticket')  selected @endif>{{ __('Per Ticket') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Deduct Per Booking/Ticket") }}</label>
                    <div class="form-controls">
                        <input type="number" step="0.01" class="form-control" name="bonus_per_deduct"
                               value="{{ setting_item('bonus_per_deduct', 0) }}">
                        <p><i>{{ __("How much bonus to deduct on each booking/ticket") }}</i></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Bonus Code") }}</label>
                    <div class="form-controls">
                        <input type="text" class="form-control" name="bonus_code"
                               value="{{ setting_item('bonus_code') }}"
                               placeholder="e.g. WELCOME100">
                        <p><i>{{ __("Users applying this code will receive the bonus amount") }}</i></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Bonus Expire Days") }}</label>
                    <div class="form-controls">
                        <input type="number" class="form-control" name="bonus_expire_days"
                               value="{{ setting_item('bonus_expire_days') }}"
                               placeholder="{{ __('Leave empty for no expiry') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Apply to Roles") }}</label>
                    <div class="form-controls">
                        @php
                            $bonusRoles = setting_item_array('bonus_roles');
                            $allRoles   = \Modules\User\Models\Role::query()->get();
                        @endphp
                        @foreach($allRoles as $role)
                            <label class="d-block">
                                <input type="checkbox" name="bonus_roles[]" value="{{ $role->id }}"
                                       @if(in_array($role->id, $bonusRoles)) checked @endif>
                                {{ $role->display_name ?? $role->name }}
                            </label>
                        @endforeach
                        <p><i>{{ __("Leave unchecked to apply to all roles") }}</i></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

{{-- ── POINTS SECTION ── --}}
<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __("Points Settings") }}</h3>
        <p class="form-group-desc">{{ __('Configure points system for users') }}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                <div class="form-group">
                    <label>{{ __("Enable Points") }}</label>
                    <div class="form-controls">
                        <label>
                            <input type="checkbox" name="point_enabled" value="1"
                                   @if(setting_item('point_enabled') == 1) checked @endif>
                            {{ __('Enable') }}
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Earn Points Per") }}</label>
                    <div class="form-controls">
                        <select name="point_per_type" class="form-control">
                            <option value="booking" @if(setting_item('point_per_type') == 'booking') selected @endif>{{ __('Per Booking') }}</option>
                            <option value="ticket"  @if(setting_item('point_per_type') == 'ticket')  selected @endif>{{ __('Per Ticket') }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Points Per Booking/Ticket") }}</label>
                    <div class="form-controls">
                        <input type="number" class="form-control" name="point_per_count"
                               value="{{ setting_item('point_per_count', 0) }}">
                        <p><i>{{ __("How many points to give per booking/ticket") }}</i></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Point Value") }}</label>
                    <div class="form-controls">
                        <input type="number" step="0.01" class="form-control" name="point_value"
                               value="{{ setting_item('point_value', 1) }}">
                        <p><i>{{ __("1 point = how much money") }}</i></p>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Points Expire Days") }}</label>
                    <div class="form-controls">
                        <input type="number" class="form-control" name="point_expire_days"
                               value="{{ setting_item('point_expire_days') }}"
                               placeholder="{{ __('Leave empty for no expiry') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __("Apply to Roles") }}</label>
                    <div class="form-controls">
                        @php
                            $pointRoles = setting_item_array('point_roles');
                        @endphp
                        @foreach($allRoles as $role)
                            <label class="d-block">
                                <input type="checkbox" name="point_roles[]" value="{{ $role->id }}"
                                       @if(in_array($role->id, $pointRoles)) checked @endif>
                                {{ $role->display_name ?? $role->name }}
                            </label>
                        @endforeach
                        <p><i>{{ __("Leave unchecked to apply to all roles") }}</i></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
