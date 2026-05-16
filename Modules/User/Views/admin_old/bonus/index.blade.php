@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Manual Bonus') }}</h1>
            <div class="title-actions">
                @if(Auth::user()->hasPermission('bonus_transactions'))
                    <a href="{{ route('user.admin.bonus.transactions') }}" class="btn btn-info">
                        <i class="fa fa-list"></i> {{ __('Bonus Transactions') }}
                    </a>
                @endif
            </div>
        </div>

        @include('admin.message')

        <div class="row">
            <div class="col-md-7">
                @if(Auth::user()->hasPermission('bonus_give'))
                    <div class="panel">
                        <div class="panel-heading"><strong>{{ __('Give Bonus / Points') }}</strong></div>
                        <div class="panel-body">
                            <form action="{{ route('user.admin.bonus.store') }}" method="POST">
                                @csrf

                                {{-- Target Type --}}
                                <div class="form-group">
                                    <label>{{ __('Target') }} <span class="text-danger">*</span></label>
                                    <div class="form-controls">
                                        <select name="target_type" id="sn-target-type" class="form-control"
                                                onchange="snTargetChange(this.value)">
                                            <option value="all">{{ __('All Users') }}</option>
                                            <option value="role">{{ __('By Role') }}</option>
                                            <option value="specific">{{ __('Specific Users') }}</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Role Select --}}
                                <div class="form-group" id="sn-role-row" style="display:none;">
                                    <label>{{ __('Select Role') }} <span class="text-danger">*</span></label>
                                    <div class="form-controls">
                                        <select name="role_id" class="form-control">
                                            <option value="">{{ __('-- Select Role --') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Specific Users --}}
                                <div class="form-group" id="sn-user-row" style="display:none;">
                                    <label>{{ __('Select Users') }} <span class="text-danger">*</span></label>
                                    <div class="form-controls">
                                        <div style="width:100%;">
                                            <select name="user_ids[]" id="sn-user-select"
                                                    multiple="multiple"
                                                    class="form-control"
                                                    style="width:100%;"
                                                    data-placeholder="{{ __('Search users...') }}">
                                            </select>
                                        </div>
                                        <p><i>{{ __('You can select multiple users') }}</i></p>
                                    </div>
                                </div>

                                {{-- Bonus Type --}}
                                <div class="form-group">
                                    <label>{{ __('Bonus Type') }} <span class="text-danger">*</span></label>
                                    <div class="form-controls">
                                        <select name="bonus_type" class="form-control">
                                            <option value="bonus_balance">{{ __('Bonus Balance (Money)') }}</option>
                                            <option value="bonus_points">{{ __('Bonus Points') }}</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Amount --}}
                                <div class="form-group">
                                    <label>{{ __('Amount / Points') }} <span class="text-danger">*</span></label>
                                    <div class="form-controls">
                                        <input type="number" name="amount" step="0.01" min="0.01"
                                               class="form-control" placeholder="e.g. 100"
                                               value="{{ old('amount') }}">
                                    </div>
                                </div>

                                {{-- Remarks --}}
                                <div class="form-group">
                                    <label>{{ __('Remarks') }}</label>
                                    <div class="form-controls">
                                        <input type="text" name="remarks" class="form-control"
                                               placeholder="{{ __('e.g. Eid bonus 2026') }}"
                                               value="{{ old('remarks') }}">
                                    </div>
                                </div>

                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-gift"></i> {{ __('Give Bonus') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-5">
                <div class="panel">
                    <div class="panel-heading"><strong>{{ __('Current Bonus Settings') }}</strong></div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr>
                                <td>{{ __('Bonus Enabled') }}</td>
                                <td>
                                    @if(setting_item('bonus_enabled'))
                                        <span class="badge badge-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('No') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Bonus Amount') }}</td>
                                <td>{{ format_money(setting_item('bonus_amount', 0)) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Deduct Per') }} {{ setting_item('bonus_per_type') == 'ticket' ? __('Ticket') : __('Booking') }}</td>
                                <td>{{ format_money(setting_item('bonus_per_deduct', 0)) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Bonus Code') }}</td>
                                <td><code>{{ setting_item('bonus_code') ?: '-' }}</code></td>
                            </tr>
                            <tr>
                                <td>{{ __('Points Enabled') }}</td>
                                <td>
                                    @if(setting_item('point_enabled'))
                                        <span class="badge badge-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('No') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Point Value') }}</td>
                                <td>1 pt = {{ format_money(setting_item('point_value', 1)) }}</td>
                            </tr>
                        </table>
                        <a href="{{ route('core.admin.settings.index', ['group' => 'bonus']) }}"
                           class="btn btn-sm btn-default">
                            <i class="fa fa-cog"></i> {{ __('Edit Bonus Settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function snTargetChange(val) {
            document.getElementById('sn-role-row').style.display = val === 'role'     ? '' : 'none';
            document.getElementById('sn-user-row').style.display = val === 'specific' ? '' : 'none';
            if (val === 'specific') {
                setTimeout(function() {
                    if (window.jQuery && !jQuery('#sn-user-select').data('select2')) {
                        jQuery('#sn-user-select').select2({
                            width: '100%',
                            placeholder: '{{ __("Search users...") }}',
                            ajax: {
                                url: '{{ route("user.admin.getForSelect2") }}',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return { q: params.term };
                                },
                                processResults: function(data) {
                                    return { results: data.results };
                                }
                            }
                        });
                    }
                }, 50);
            }
        }
    </script>
@endsection
