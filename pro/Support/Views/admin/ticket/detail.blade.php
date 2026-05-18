@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{$row->title}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-9">
                <div class="panel">
                    <div class="panel-body">
                        <div class="d-flex align-items-center mb-3">
                            <h4 class="mb-0 mr-3">
                                <i class="fa fa-file-text-o"></i> {{$row->title}}</h4>
                            <span class="badge badge-{{$row->status_badge_class}}">{{$row->status_text}}</span>
                        </div>
                        <div class="mb-3 text-muted">
                            @if($row->customer)
                                <span class="mr-3">
                                    <i class="fa fa-user-o mr-1"></i> {{$row->customer->display_name ?? ''}}
                                </span>
                            @endif
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
                            <h4>All Replies</h4>
                            <div class="replies-scroll">
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
                                    <form action="{{route('support.ticket.reply_store',['id'=>$row->id])}}" method="post">
                                        @csrf
                                        <div class="form-group">
                                            <h6>Reply Content</h6>
                                            <textarea class="form-control" rows="5" name="content" placeholder="Enter Your Text ..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-info">Add Reply</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel">
                    <div class="panel-body">
                        <div class="card mb-3">
                            <div class="card-header text-center font-weight-bold">Ticket Status</div>
                            <form action="{{route('support.ticket.action',['id'=>$row->id])}}" method="post">
                                @csrf
                                <div class="card-body">
                                    <label class="d-block">
                                        <input type="radio" value="open" name="status" @if($row->status == 'open') checked @endif> Open
                                    </label>
                                    <label class="d-block">
                                        <input type="radio" value="closed" name="status" @if($row->status == 'closed') checked @endif> Closed
                                    </label>
                                    <button type="submit" class="btn btn-primary btn-block" name="action" value="status">Save Status</button>
                                </div>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header text-center font-weight-bold">User Notes</div>
                            <div class="card-body">
                                <form action="{{route('support.ticket.action',['id'=>$row->id])}}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <label>Add note</label>
                                        <textarea name="note_content" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block" name="action" value="user_note">Add user note</button>
                                </form>
                            </div>
                            @if($row->customer && $row->customer->notes)
                                <ul class="list-group list-group-flush">
                                    @foreach($row->customer->notes as $note)
                                        <li class="list-group-item">
                                            <div class="note-content">{!! nl2br($note->content) !!}</div>
                                            <small><i>{{display_datetime($note->created_at)}}</i></small>
                                            <div class="d-flex justify-content-between mt-2">
                                                <a data-toggle="modal" data-target="#edit_note_{{$note->id}}" href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form onsubmit="return confirm('Do you want to delete this note')" action="{{route('support.note.delete',['id'=>$note->id])}}" method="post">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-link text-danger">Delete</button>
                                                </form>
                                            </div>
                                            <form action="{{route('support.note.update',['id'=>$note->id])}}" method="post">
                                                @csrf
                                                <div class="modal" tabindex="-1" id="edit_note_{{$note->id}}">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Note</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <h6>Old</h6>
                                                                <p>{!! nl2br($note->content) !!}</p>
                                                                <h6>New</h6>
                                                                <textarea rows="5" class="form-control" name="content">{{$note->content}}</textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
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

@push('js')
    <script src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script>
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
