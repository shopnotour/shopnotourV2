{{-- resources/views/admin/cancellations/index.blade.php --}}
@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('Booking Cancellation Requests') }}</h1>
        </div>

        @include('admin.message')

        <div class="filter-div d-flex justify-content-between">
{{--            <div class="col-left">--}}
{{--                <form method="post" action="{{ route('admin.cancellations.bulkAction') }}" class="filter-form filter-form-left d-flex">--}}
{{--                    @csrf--}}
{{--                    <select name="action" class="form-control">--}}
{{--                        <option value="">{{ __('-- Bulk Actions --') }}</option>--}}
{{--                        <option value="delete">{{ __('Delete') }}</option>--}}
{{--                    </select>--}}
{{--                    <button class="btn-info btn btn-icon" type="submit">{{ __('Apply') }}</button>--}}
{{--                </form>--}}
{{--            </div>--}}

            <div class="col-right">
                <form method="get" action="" class="filter-form filter-form-right d-flex">
                    <select name="status" class="form-control">
                        <option value="">{{ __('-- All Status --') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <input type="text" name="s" value="{{ request('s') }}" placeholder="{{ __('Search Booking Code') }}" class="form-control">
                    <button class="btn-info btn btn-icon" type="submit">{{ __('Filter') }}</button>
                </form>
            </div>
        </div>

{{--        <div class="text-right">--}}
{{--            <p><i>{{ __('Found :total items', ['total' => $requests->total()]) }}</i></p>--}}
{{--        </div>--}}

        <div class="panel">
            <div class="panel-title">{{ __('Cancellation Requests') }}</div>
            <div class="panel-body">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th width="60px"><input type="checkbox" class="check-all"></th>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Booking Code') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th width="150px">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $req)
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            ];
                            $statusColor = $statusColors[$req->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{ $req->id }}"></td>
                            <td>{{ $req->id }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $req->booking_id) }}">
                                    {{ $req->booking->code ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                {{ $req->user->display_name ?? $req->user->first_name ?? 'N/A' }}<br>
                                <small>{{ $req->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst(str_replace('_', ' ', $req->cancellation_type)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $statusColor }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                                @if($req->reviewed_by && $req->status != 'pending')
                                    <br><small class="text-muted">by {{ $req->reviewer->display_name ?? 'Admin' }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $req->created_at->format('d M Y') }}<br>
                                <small>{{ $req->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                        {{ __('Actions') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#modal-cancel-{{ $req->id }}">
                                            <i class="fa fa-eye"></i> {{ __('View Details') }}
                                        </a>
                                        @if($req->status == 'pending')
                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#modal-approve-{{ $req->id }}">
                                                <i class="fa fa-check text-success"></i> {{ __('Approve') }}
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#modal-reject-{{ $req->id }}">
                                                <i class="fa fa-times text-danger"></i> {{ __('Reject') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ __('No cancellation requests found') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

{{--        <div class="d-flex justify-content-end">--}}
{{--            {{ $requests->links() }}--}}
{{--        </div>--}}
    </div>

    {{-- MODALS --}}
    @foreach($requests as $req)
        {{-- Approve Modal --}}
        <div class="modal fade" id="modal-approve-{{ $req->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Approve Cancellation Request #') }}{{ $req->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Booking:</strong> {{ $req->booking->code ?? 'N/A' }}<br>
                            <strong>Customer:</strong> {{ $req->user->display_name ?? 'N/A' }}<br>
                            <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $req->cancellation_type)) }}
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            Approving this will allow user to manually cancel their booking.
                        </div>

                        <div class="form-group">
                            <label>{{ __('Cancellation Reason') }}</label>
                            <p class="form-control-static">{{ $req->cancellation_reason }}</p>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Admin Note (Optional)') }}</label>
                            <textarea id="admin_note_approve_{{ $req->id }}" class="form-control" rows="3" placeholder="Add any note..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-success btn-approve-cancel" data-id="{{ $req->id }}">
                            <i class="fa fa-check"></i> {{ __('Approve Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="modal-reject-{{ $req->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Reject Cancellation Request #') }}{{ $req->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This will reject the cancellation request.
                        </div>

                        <div class="form-group">
                            <label>{{ __('Rejection Reason') }} <span class="text-danger">*</span></label>
                            <textarea id="admin_note_reject_{{ $req->id }}" class="form-control" rows="4" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger btn-reject-cancel" data-id="{{ $req->id }}">
                            <i class="fa fa-times"></i> {{ __('Reject Request') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Modal --}}
        <div class="modal fade" id="modal-cancel-{{ $req->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Cancellation Request Details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ __('Request ID') }}</th>
                                        <td>{{ $req->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Booking Code') }}</th>
                                        <td>{{ $req->booking->code ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Customer') }}</th>
                                        <td>{{ $req->user->display_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        <td>{{ ucfirst(str_replace('_', ' ', $req->cancellation_type)) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            <span class="badge badge-{{ $statusColors[$req->status] ?? 'secondary' }}">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">{{ __('Requested At') }}</th>
                                        <td>{{ $req->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                    @if($req->reviewed_at)
                                        <tr>
                                            <th>{{ __('Reviewed At') }}</th>
                                            <td>{{ $req->reviewed_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Reviewed By') }}</th>
                                            <td>{{ $req->reviewer->display_name ?? 'Admin' }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h6><strong>{{ __('Cancellation Reason') }}</strong></h6>
                                <p>{{ $req->cancellation_reason }}</p>
                            </div>
                        </div>
                        @if($req->admin_note)
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6><strong>{{ __('Admin Note') }}</strong></h6>
                                    <p class="{{ $req->status == 'rejected' ? 'text-danger' : '' }}">{{ $req->admin_note }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            console.log('Cancellation Management Loaded');

            // Approve Cancellation
            $(document).on('click', '.btn-approve-cancel', function(e) {
                e.preventDefault();

                var btn = $(this);
                var id = btn.data('id');
                var adminNote = $('#admin_note_approve_' + id).val();

                console.log('Approving cancellation:', { id: id, note: adminNote });

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: "{{ route('cancellations.updateStatus', ['id' => ':id']) }}".replace(':id', id),
                    method: 'POST',
                    data: {
                        status: 'approved',
                        admin_note: adminNote,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);

                        if (response.success === true) {
                            alert(response.message);
                            window.location.reload();
                        } else {
                            alert(response.message);
                            btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve Request');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve Request');
                    }
                });
            });

            // Reject Cancellation
            $(document).on('click', '.btn-reject-cancel', function(e) {
                e.preventDefault();

                var btn = $(this);
                var id = btn.data('id');
                var adminNote = $('#admin_note_reject_' + id).val();

                if (!adminNote || adminNote.length < 10) {
                    alert('Please enter rejection reason (minimum 10 characters)');
                    return false;
                }

                console.log('Rejecting cancellation:', { id: id, note: adminNote });

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: '/admin/cancel/cancellations/' + id + '/update-status',
                    method: 'POST',
                    data: {
                        status: 'rejected',
                        admin_note: adminNote,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response:', response);

                        if (response.success === true) {
                            alert(response.message);
                            window.location.reload();
                        } else {
                            alert(response.message);
                            btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                        btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject Request');
                    }
                });
            });

            // Check All
            $('.check-all').on('change', function() {
                $('.check-item').prop('checked', $(this).prop('checked'));
            });
        });
    </script>
@endpush

@push('css')
    <style>
        .badge-pending { background-color: #ffc107; color: black; }
        .badge-approved { background-color: #28a745; color: white; }
        .badge-rejected { background-color: #dc3545; color: white; }
    </style>
@endpush
