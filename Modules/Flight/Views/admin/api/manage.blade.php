@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h1 class="title-bar">{{ __('Manage Flight APIs') }}</h1>
        </div>

        @include('admin.message')

        <div class="panel">
            <div class="panel-body">
                <form action="{{ route('flight.admin.api.updateSettings') }}" method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th width="100px">{{ __('Enable') }}</th>
                                <th>{{ __('API Name') }}</th>
                                <th>{{ __('Provider') }}</th>
                                <th width="150px">{{ __('Priority') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($apis as $api)
                                <tr>
                                    <td>
                                        <label class="switch">
                                            <input type="checkbox"
                                                   name="apis[{{ $api->id }}][is_enabled]"
                                                   value="1"
                                                {{ $api->is_enabled ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <strong>{{ $api->name }}</strong>
                                    </td>
                                    <td>
                                        {{ $api->provider }}
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="apis[{{ $api->id }}][priority]"
                                               class="form-control"
                                               value="{{ $api->priority ?? 0 }}"
                                               min="0"
                                               style="width: 100px;">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #28a745;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
@endsection
