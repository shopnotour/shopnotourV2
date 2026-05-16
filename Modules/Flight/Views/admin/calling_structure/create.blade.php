@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h1 class="title-bar">{{ __('Add Route Calling Structure') }}</h1>
        </div>

        @include('admin.message')

        <div class="panel">
            <div class="panel-body">
                <form action="{{ route('flight.admin.calling.store') }}" method="POST">
                    @csrf

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>{{ __('Tip:') }}</strong>
                        {{ __('Leave departure or arrival empty (*) to create wildcard rules. Example: DAC → * means any flight from Dhaka.') }}
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Departure Airport') }}</label>
                                <select name="departure_code" id="departure_code" class="form-control select2-airport">
                                    <option value="">{{ __('* (Any Departure)') }}</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->code }}" {{ old('departure_code') == $airport->code ? 'selected' : '' }}>
                                            {{ $airport->code }} - {{ $airport->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    {{ __('Select specific airport or leave as wildcard (*)') }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Arrival Airport') }}</label>
                                <select name="arrival_code" id="arrival_code" class="form-control select2-airport">
                                    <option value="">{{ __('* (Any Arrival)') }}</option>
                                    @foreach($airports as $airport)
                                        <option value="{{ $airport->code }}" {{ old('arrival_code') == $airport->code ? 'selected' : '' }}>
                                            {{ $airport->code }} - {{ $airport->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    {{ __('Select specific airport or leave as wildcard (*)') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('GDS') }}</label>
                                <select name="gds" id="gds" class="form-control">
                                    <option value="">{{ __('* (Select a GDS)') }}</option>
                                    @foreach($gdsoptions as $provider => $name)
                                        <option value="{{ $provider }}" {{ old('gds') == $provider ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    {{ __('Select specific GDS (*)') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4>{{ __('Airlines') }} <span class="text-danger">*</span></h4>
                    <p class="text-muted">{{ __('Select airlines in priority order. First selected = highest priority.') }}</p>

                    <div class="form-group">
                        <label>{{ __('Select Airlines (by code)') }}</label>
                        <select name="airline_codes[]" id="airlines" class="form-control select2-airline" multiple required>
                            @foreach($airlines as $airline)
                                <option value="{{ $airline->designator }}"
                                    {{ in_array($airline->designator, old('airline_codes', [])) ? 'selected' : '' }}>
                                    {{ $airline->designator }} - {{ $airline->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-info">
                            <i class="fa fa-lightbulb"></i>
                            {{ __('Select multiple airlines. Order matters - drag to reorder or remove and re-add.') }}
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <input type="number" name="priority" class="form-control"
                                       value="{{ old('priority', 0) }}"
                                       min="0" max="99" required>
                                <small class="form-text text-muted">
                                    {{ __('Lower number = higher priority. 0 = highest priority, 99 = lowest.') }}
                                </small>
                            </div>
                        </div>

{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label>{{ __('Status') }}</label>--}}
{{--                                <select name="status" class="form-control">--}}
{{--                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>--}}
{{--                                        {{ __('Active') }}--}}
{{--                                    </option>--}}
{{--                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>--}}
{{--                                        {{ __('Inactive') }}--}}
{{--                                    </option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>

                    <div class="form-group">
                        <label>{{ __('Notes') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="{{ __('e.g., Summer routes only, Preferred airlines for corporate bookings, etc.') }}">{{ old('notes') }}</textarea>
                        <small class="form-text text-muted">
                            {{ __('Add any notes or comments about this configuration') }}
                        </small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('flight.admin.calling.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ __('Save Route') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Priority Guide -->
        <div class="panel mt-4">
            <div class="panel-header">
                <h5>{{ __('Priority Guide') }}</h5>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">{{ __('Route Matching Order:') }}</h6>
                        <ol>
                            <li>{{ __('Exact match (e.g., DAC → DXB)') }} - Priority 0-5</li>
                            <li>{{ __('Departure wildcard (e.g., DAC → *)') }} - Priority 6-10</li>
                            <li>{{ __('Arrival wildcard (e.g., * → DXB)') }} - Priority 11-15</li>
                            <li>{{ __('Global wildcard (e.g., * → *)') }} - Priority 90+</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">{{ __('Examples:') }}</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge badge-success">Priority 1</span>
                                <strong>DAC → DXB:</strong> BS, BG, G9
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-info">Priority 10</span>
                                <strong>DAC → *:</strong> BS, BG
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-secondary">Priority 99</span>
                                <strong>* → *:</strong> All airlines
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Airport search with Select2
            $('.select2-airport').select2({
                placeholder: '{{ __("Search airport or leave as wildcard (*)...") }}',
                allowClear: true,
                width: '100%'
            });

            // Airline multiple select with Select2
            $('.select2-airline').select2({
                placeholder: '{{ __("Select airlines...") }}',
                allowClear: true,
                width: '100%',
                templateResult: formatAirline,
                templateSelection: formatAirlineSelection
            });

            // Format airline display in dropdown
            function formatAirline(airline) {
                if (!airline.id) {
                    return airline.text;
                }

                var parts = airline.text.split(' - ');
                var $airline = $(
                    '<span><strong>' + parts[0] + '</strong> - ' + (parts[1] || '') + '</span>'
                );
                return $airline;
            }

            // Format selected airline (show only code)
            function formatAirlineSelection(airline) {
                if (!airline.id) {
                    return airline.text;
                }
                return airline.text.split(' - ')[0];
            }

            // Validate: Departure and Arrival cannot be same
            $('form').on('submit', function(e) {
                var departure = $('#departure_code').val();
                var arrival = $('#arrival_code').val();

                if (departure && arrival && departure === arrival) {
                    e.preventDefault();
                    alert('{{ __("Departure and arrival airports cannot be the same!") }}');
                    return false;
                }

                // Check if at least one airline is selected
                if ($('#airlines').val().length === 0) {
                    e.preventDefault();
                    alert('{{ __("Please select at least one airline!") }}');
                    return false;
                }
            });

            // Show warning when leaving page with unsaved changes
            var formChanged = false;
            $('form :input').change(function() {
                formChanged = true;
            });

            $('form').submit(function() {
                formChanged = false;
            });

            window.addEventListener('beforeunload', function (e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>

    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #667eea;
            border-color: #5568d3;
            color: white;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
        }
    </style>
@endsection
