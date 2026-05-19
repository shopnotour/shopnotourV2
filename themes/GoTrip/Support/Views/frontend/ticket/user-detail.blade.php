@extends('layouts.user')
@push('css')
    <link rel="stylesheet" href="{{asset('dist/frontend/module/support/css/support.css?_v='.config('app.asset_version'))}}">
    <style>
        .replies-scroll {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }
        .replies-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .replies-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .replies-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        .replies-scroll::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        .replies-scroll .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
@endpush
@section('content')
    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">
        <div class="col-auto">
            <h1 class="text-30 lh-14 fw-600">{{$row->title}}</h1>
        </div>
        <div class="col-auto">
            <a href="{{route('user.support.ticket.index')}}" class="button -sm -dark-1 bg-light-2 text-dark-1">
                <i class="fa fa-arrow-left mr-1"></i> {{__("Back to Tickets")}}
            </a>
        </div>
    </div>
    @include('admin.message')
    <div class="row">
        <div class="col-md-8">
            <div class="rounded-4 bg-white shadow-3 py-30 px-30">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="mb-0 mr-3">
                        <i class="fa fa-file-text-o"></i> {{$row->title}}
                    </h4>
                    <span class="badge badge-{{$row->status_badge_class}}">{{$row->status_text}}</span>
                </div>
                <div class="mb-3 text-muted">
                    @if($row->cat)
                        <span class="mr-3">
                            <i class="fa fa-folder-o mr-1"></i> {{$row->cat->name ?? ''}}
                        </span>
                    @endif
                    @if($row->created_at)
                        <span>
                            <i class="fa fa-clock-o"></i> {{human_time_diff(strtotime($row->created_at))}} ago
                        </span>
                    @endif
                </div>
                <div class="py-3 border-top border-bottom mb-3">
                    {!! clean($row->content) !!}
                </div>
                @php
                    $replies = $row->replies()->orderBy('id')->get();
                @endphp
                <div class="all-answers mt-4">
                    <h4>{{__("All Replies")}}</h4>
                    <div class="replies-scroll mt-3">
                        <div class="list-group">
                            @foreach($replies as $reply)
                                <div class="list-group-item py-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="mr-3 flex-shrink-0">
                                            <img style="width:40px;height:40px;border-radius:50%;" src="{{$reply->user->avatar_url ?? ''}}" alt="">
                                        </div>
                                        <div>
                                            <strong>{{$reply->user->display_name ?? ''}}</strong>
                                            <div class="text-muted small">
                                                <span class="badge @if($reply->user_id === $row->customer_id) badge-info @else badge-warning @endif mr-2">
                                                    {{ucfirst($reply->user->role_name ?? '')}}
                                                </span>
                                                <i class="fa fa-clock-o"></i> {{human_time_diff(strtotime($reply->created_at))}} ago
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pl-5">
                                        {!! clean($reply->content) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($row->status == 'open')
                    <hr>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6>{{__("Reply")}}</h6>
                            <form action="{{route('support.ticket.reply_store',['id'=>$row->id])}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <textarea class="form-control" rows="5" name="content" placeholder="{{__('Enter Your Text ...')}}"></textarea>
                                </div>
                                <button type="submit" class="button -sm -dark-1 bg-blue-1 text-white">{{__("Add Reply")}}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <div class="rounded-4 bg-white shadow-3 py-30 px-30">
                <h5>{{__("Ticket Info")}}</h5>
                <hr>
                <p><strong>{{__("Status")}}:</strong> <span class="badge badge-{{$row->status_badge_class}}">{{$row->status_text}}</span></p>
                <p><strong>{{__("Category")}}:</strong> {{$row->cat->name ?? ''}}</p>
                @if($row->customer)
                    <p><strong>{{__("Customer")}}:</strong> {{$row->customer->display_name ?? ''}}</p>
                @endif
                <p><strong>{{__("Created")}}:</strong> {{display_date($row->created_at)}}</p>
                @if($row->agent)
                    <p><strong>{{__("Agent")}}:</strong> {{$row->agent->display_name ?? ''}}</p>
                @endif

            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        $(document).on('click', '.alert .close', function() {
            $(this).closest('.alert').fadeOut();
        });
        $('form').on('submit', function(e) {
            tinymce.activeEditor.uploadImages(function() {
                tinymce.activeEditor.save();
            });
            return true;
        });
        var options = {
            menubar: false,
            plugins: 'image link codesample table hr lists',
            toolbar: 'bold italic strikethrough permanentpen formatpainter | link image media | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | pagebreak codesample code | removeformat',
            image_advtab: false,
            image_caption: false,
            toolbar_drawer: 'sliding',
            relative_urls: false,
            height: 300,
            file_picker_types: 'image',
            paste_data_images: true,
            images_upload_handler: function(blobInfo, success, failure) {
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('is_private', 1);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                $.ajax({
                    url: '{{route('media.store')}}',
                    data: formData,
                    dataType: 'json',
                    method: 'post',
                    processData: false,
                    contentType: false,
                    success: function(json) { success(json.url); },
                });
            },
        };
        var tmp = Object.assign({}, options);
        tmp.selector = 'textarea[name="content"]';
        tinymce.init(tmp);

        var repliesEl = document.querySelector('.replies-scroll');
        if (repliesEl) {
            repliesEl.scrollTop = repliesEl.scrollHeight;
        }
    </script>
@endpush
