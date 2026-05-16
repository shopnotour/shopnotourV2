@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h1 class="title-bar">{{ __('Edit Route Calling Structure') }}</h1>
        </div>

        @include('admin.message')

        <div class="panel">
            <div class="panel-body">
                <form action="{{ route('flight.admin.calling.update', $row->id) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Departure Airport') }}</label>
                                <input type="text" class="form-control"
                                       value="{{ $row->departure_code ? ($row->departure_code . ' - ' . ($row->departureAirport->name ?? '')) : '* (Any Departure)' }}"
                                       readonly>
                                @if(!$row->departure_code)
                                    <small class="text-warning">
                                        <i class="fa fa-info-circle"></i> {{ __('Wildcard: applies to all departure airports') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('Arrival Airport') }}</label>
                                <input type="text" class="form-control"
                                       value="{{ $row->arrival_code ? ($row->arrival_code . ' - ' . ($row->arrivalAirport->name ?? '')) : '* (Any Arrival)' }}"
                                       readonly>
                                @if(!$row->arrival_code)
                                    <small class="text-warning">
                                        <i class="fa fa-info-circle"></i> {{ __('Wildcard: applies to all arrival airports') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('GDS') }}</label>
                                <select name="gds" id="gds" class="form-control">
                                    <option value="">{{ __('* (Select a GDS)') }}</option>
                                    @foreach($gdsoptions as $provider => $name)
                                        <option value="{{ $provider }}"
                                            {{ old('gds', $row->gds ?? '') == $provider ? 'selected' : '' }}>
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
                                    {{ in_array($airline->designator, $row->airline_codes ?? []) ? 'selected' : '' }}>
                                    {{ $airline->designator }} - {{ $airline->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-info">
                            <i class="fa fa-lightbulb"></i>
                            {{ __('Drag to reorder, or remove and re-add. Order matters for priority.') }}
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <input type="number" name="priority" class="form-control"
                                       value="{{ old('priority', $row->priority) }}"
                                       min="0" max="99" required>
                                <small class="form-text text-muted">
                                    {{ __('Lower number = higher priority (0 = highest)') }}
                                </small>
                            </div>
                        </div>

{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label>{{ __('Status') }}</label>--}}
{{--                                <select name="status" class="form-control">--}}
{{--                                    <option value="active" {{ $row->status == 'active' ? 'selected' : '' }}>--}}
{{--                                        {{ __('Active') }}--}}
{{--                                    </option>--}}
{{--                                    <option value="inactive" {{ $row->status == 'inactive' ? 'selected' : '' }}>--}}
{{--                                        {{ __('Inactive') }}--}}
{{--                                    </option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>

                    <div class="form-group">
                        <label>{{ __('Notes') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="{{ __('e.g., Summer routes only, Preferred airlines for corporate bookings, etc.') }}">{{ old('notes', $row->notes) }}</textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('flight.admin.calling.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ __('Update Route') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Configuration Preview -->
        <div class="panel mt-4">
            <div class="panel-header">
                <h5>{{ __('Current Configuration') }}</h5>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>{{ __('Route:') }}</strong>
                        <p class="text-primary">
                            {{ $row->departure_code ?? '*' }} → {{ $row->arrival_code ?? '*' }}
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('Current Airlines:') }}</strong>
                        <p>
                            @if(!empty($row->airline_codes))
                                @foreach($row->airline_codes as $code)
                                    <span class="badge badge-secondary">{{ $code }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">{{ __('None') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4">
                        <strong>{{ __('Priority:') }}</strong>
                        <p>
                            <span class="badge badge-{{ $row->priority < 5 ? 'success' : 'info' }}">
                                {{ $row->priority }}
                            </span>
                        </p>
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

            // Format selected airline
            function formatAirlineSelection(airline) {
                if (!airline.id) {
                    return airline.text;
                }
                return airline.text.split(' - ')[0]; // Show only code
            }

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
