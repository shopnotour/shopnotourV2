@extends('layouts.user')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="title-bar">
                {{__('Cancellation Requests')}}
            </h2>
        </div>

        <div class="panel">
            <div class="panel-body">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{__('Booking Code')}}</th>
                                <th>{{__('Cancellation Type')}}</th>
                                <th>{{__('Cancellation Reason')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Admin Note')}}</th>
                                <th>{{__('Requested At')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>{{ $request->booking->code ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $request->cancellation_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->cancellation_reason ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $badgeClass = $statusClasses[$request->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->admin_note ?? '-' }}</td>
                                    <td>{{ $request->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $requests->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        {{__('No cancellation requests found.')}}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
