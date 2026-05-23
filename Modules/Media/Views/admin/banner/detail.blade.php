@extends('admin.layouts.app')

@section('content')
    <form action="{{ route('banner.admin.store', ['id' => $row->id ? $row->id : '-1']) }}"
          method="post" class="dungdt-form" enctype="multipart/form-data">
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
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

                    {{-- Media Type Selection --}}
                    <div class="panel">
                        <div class="panel-body">
                            <h3 class="panel-body-title">{{ __('Media Type') }}</h3>
                            <div class="form-group">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="image" 
                                               id="type_image" 
                                               @if($row->type == 'image' || !$row->id) checked @endif>
                                        {{ __('Image Banner') }}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="type" value="video" 
                                               id="type_video"
                                               @if($row->type == 'video') checked @endif>
                                        {{ __('Video Banner') }}
                                    </label>
                                </div>
                                @if($row->type == 'video')
                                    <div class="alert alert-info mt-2">
                                        <i class="fa fa-info-circle"></i> 
                                        {{ __('Only one video banner can be active at a time.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="panel" id="image_panel">
                        <div class="panel-body">
                            <h3 class="panel-body-title">{{ __('Banner Image') }}</h3>
                            <div class="form-group">
                                {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id', $row->image_id) !!}
                            </div>
                        </div>
                    </div>

                    {{-- Video Upload --}}
                    <div class="panel" id="video_panel" style="display: none;">
                        <div class="panel-body">
                            <h3 class="panel-body-title">{{ __('Banner Video') }}</h3>
                            <div class="form-group">
                                <label class="control-label">{{ __('Upload Video') }}</label>
                                <input type="file" 
                                       name="video" 
                                       accept="video/mp4,video/avi,video/mov,video/wmv,video/flv,video/mkv"
                                       class="form-control">
                                <small class="text-muted">
                                    {{ __('Supported formats: MP4, AVI, MOV, WMV, FLV, MKV. Max size: 100MB') }}
                                </small>
                                
                                @if($row->video)
                                    <div class="mt-2">
                                        <label>{{ __('Current Video:') }}</label>
                                        <div class="mt-1">
                                            <video width="100%" controls>
                                                <source src="{{ asset('uploads/video/' . $row->video) }}" type="video/mp4">
                                                {{ __('Your browser does not support the video tag.') }}
                                            </video>
                                            <p class="text-muted mt-1">
                                                <small>{{ $row->video }}</small>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeImage = document.getElementById('type_image');
            const typeVideo = document.getElementById('type_video');
            const imagePanel = document.getElementById('image_panel');
            const videoPanel = document.getElementById('video_panel');
            
            function toggleMediaPanels() {
                if (typeImage.checked) {
                    imagePanel.style.display = 'block';
                    videoPanel.style.display = 'none';
                } else {
                    imagePanel.style.display = 'none';
                    videoPanel.style.display = 'block';
                }
            }
            
            typeImage.addEventListener('change', toggleMediaPanels);
            typeVideo.addEventListener('change', toggleMediaPanels);
            
            // Initial state
            toggleMediaPanels();
        });
    </script>
@endsection