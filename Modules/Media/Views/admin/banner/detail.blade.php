@extends('admin.layouts.app')

@section('content')
    <form action="{{ route('banner.admin.store', ['id' => $row->id ? $row->id : '-1']) }}"
          method="post" class="dungdt-form">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <div>
                    <h1 class="title-bar">
                        {{ $row->id ? __('Edit Banner: ').$row->title : __('Add new Banner') }}
                    </h1>
                </div>
            </div>

            @include('admin.message')

            <div class="row">
                <div class="col-md-9">
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('Banner Content') }}</strong></div>
                        <div class="panel-body">

                            {{-- Title --}}
                            <div class="form-group">
                                <label class="control-label">{{ __('Title') }}</label>
                                <input type="text"
                                       name="title"
                                       value="{{ $row->title }}"
                                       placeholder="{{ __('Enter title (optional)') }}"
                                       class="form-control">
                            </div>

                            {{-- Link --}}
                            <div class="form-group">
                                <label class="control-label">{{ __('Link') }}</label>
                                <input type="text"
                                       name="link"
                                       value="{{ $row->link }}"
                                       placeholder="{{ __('https://... (optional)') }}"
                                       class="form-control">
                            </div>

                            {{-- Order --}}
                            <div class="form-group">
                                <label class="control-label">{{ __('Order') }}</label>
                                <input type="number"
                                       name="order"
                                       value="{{ $row->order ?? 0 }}"
                                       class="form-control"
                                       style="width: 100px">
                                <small class="text-muted">{{ __('ছোট নম্বর আগে দেখাবে') }}</small>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-3">

                    {{-- Publish --}}
                    <div class="panel">
                        <div class="panel-title"><strong>{{ __('Publish') }}</strong></div>
                        <div class="panel-body">
                            <div>
                                <label>
                                    <input type="radio" name="status" value="active"
                                           @if($row->status == 'active' || !$row->id) checked @endif>
                                    {{ __('Active') }}
                                </label>
                            </div>
                            <div>
                                <label>
                                    <input type="radio" name="status" value="inactive"
                                           @if($row->status == 'inactive') checked @endif>
                                    {{ __('Inactive') }}
                                </label>
                            </div>
                            <div class="text-right mt-2">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-save"></i> {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Image --}}
                    <div class="panel">
                        <div class="panel-body">
                            <h3 class="panel-body-title">{{ __('Banner Image') }}</h3>
                            <div class="form-group">
                                {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id', $row->image_id) !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
@endsection
