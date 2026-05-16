@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ $row->id ? __('Edit API') : __('Add New API') }}</h1>
        </div>

        @include('admin.message')

        <div class="panel">
            <div class="panel-body">
                <form action="{{ $row->id ? route('flight.admin.api.update', $row->id) : route('flight.admin.api.store') }}" method="POST">
                    @csrf
                    @if($row->id)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('API Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $row->name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Provider') }} <span class="text-danger">*</span></label>
                                <select name="provider" class="form-control" required>
                                    <option value="">{{ __('Select Provider') }}</option>
                                    <option value="Sabre" {{ old('provider', $row->provider) == 'Sabre' ? 'selected' : '' }}>Sabre</option>
                                    <option value="Amadeus" {{ old('provider', $row->provider) == 'Amadeus' ? 'selected' : '' }}>Amadeus</option>
                                    <option value="Travelport" {{ old('provider', $row->provider) == 'Travelport' ? 'selected' : '' }}>Travelport</option>
                                    <option value="Airarabia" {{ old('provider', $row->provider) == 'Airarabia' ? 'selected' : '' }}>AirArabia</option>
                                    <option value="Custom" {{ old('provider', $row->provider) == 'Custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('API Key') }}</label>
                                <input type="text" name="api_key" class="form-control" value="{{ old('api_key', $row->api_key) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('API Secret') }}</label>
                                <input type="password" name="api_secret" class="form-control" value="{{ old('api_secret', $row->api_secret) }}">
                                <small class="form-text text-muted">{{ __('Leave blank to keep current secret') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('API URL') }}</label>
                                <input type="url" name="api_url" class="form-control" value="{{ old('api_url', $row->api_url) }}" placeholder="https://api.example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Endpoint') }}</label>
                                <input type="text" name="endpoint" class="form-control" value="{{ old('endpoint', $row->endpoint) }}" placeholder="/api/v1/flights">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="active" {{ old('status', $row->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ old('status', $row->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Priority') }}</label>
                                <input type="number" name="priority" class="form-control" value="{{ old('priority', $row->priority ?? 0) }}" min="0">
                                <small class="form-text text-muted">{{ __('Lower number = higher priority') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Configuration (JSON)') }}</label>
                                <textarea name="configuration" class="form-control" rows="5" placeholder='{"timeout": 30, "retry": 3}'>{{ old('configuration', is_array($row->configuration) ? json_encode($row->configuration, JSON_PRETTY_PRINT) : $row->configuration) }}</textarea>
                                <small class="form-text text-muted">{{ __('Additional configuration in JSON format') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $row->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('flight.admin.api.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
