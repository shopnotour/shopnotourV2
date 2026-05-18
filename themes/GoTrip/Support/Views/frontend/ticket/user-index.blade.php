@extends('layouts.user')
@section('content')
    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">
        <div class="col-auto">
            <h1 class="text-30 lh-14 fw-600">{{$page_title}}</h1>
        </div>
        <div class="col-auto">
            @if(auth()->user()->hasPermission('support_ticket_create'))
                <a href="{{route('user.support.ticket.create')}}" class="button -sm -dark-1 bg-blue-1 text-white">
                    <i class="fa fa-plus mr-1"></i> {{__("Create Ticket")}}
                </a>
            @endif
        </div>
    </div>
    @include('admin.message')
    <div class="row">
        <div class="col-12">
            <div class="rounded-4 bg-white shadow-3">
                <div class="py-30 px-30">
                    @if($rows->total() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>{{ __('Ticket')}}</th>
                                    <th>{{ __('Category')}}</th>
                                    <th>{{ __('Last Reply')}}</th>
                                    <th>{{ __('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rows as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{route('user.support.ticket.detail',['id'=>$ticket->id])}}" class="text-blue-1 fw-500">
                                                {{$ticket->title}}
                                            </a>
                                            @if($ticket->last_reply_by != auth()->id())
                                                <i class="fa fa-info-circle text-warning ml-1" title="{{__("Need Response")}}"></i>
                                            @endif
                                        </td>
                                        <td>{{$ticket->cat->name ?? ''}}</td>
                                        <td>
                                            @if($ticket->last_reply_at)
                                                {{human_time_diff($ticket->last_reply_at)}} ago
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{$ticket->status_badge_class}}">{{$ticket->status_text}}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-20">
                            {{$rows->appends(request()->query())->links()}}
                        </div>
                    @else
                        <div class="alert alert-warning">{{__("No ticket found")}}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).on('click', '.alert .close', function() {
            $(this).closest('.alert').fadeOut();
        });
    </script>
@endpush
