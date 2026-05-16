{{--@extends('admin.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container-fluid">--}}
{{--        <div class="d-flex justify-content-between mb20">--}}
{{--            <h1 class="title-bar">{{ __('All Users')}}</h1>--}}
{{--            <div class="title-actions">--}}
{{--                <a href="{{route('user.admin.create')}}" class="btn btn-primary">{{ __('Add new user')}}</a>--}}
{{--                <a href="{{route('user.admin.wallet.transactions')}}" class="btn btn-primary">{{ __('All Top Up')}}</a>--}}
{{--                <a class="btn btn-warning btn-icon" href="{{ route("user.admin.export") }}" target="_blank" title="{{ __("Export to excel") }}">--}}
{{--                    <i class="icon ion-md-cloud-download"></i> {{ __("Export to excel") }}--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        @include('admin.message')--}}
{{--        <div class="filter-div d-flex justify-content-between ">--}}
{{--            <div class="col-left">--}}
{{--                @if(!empty($rows))--}}
{{--                    <form method="post" action="{{route('user.admin.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">--}}
{{--                        {{csrf_field()}}--}}
{{--                        <select name="action" class="form-control">--}}
{{--                            <option value="">{{__(" Bulk Actions ")}}</option>--}}
{{--                            <option value="delete">{{__(" Delete ")}}</option>--}}
{{--                        </select>--}}
{{--                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>--}}
{{--                    </form>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--            <div class="col-left">--}}
{{--                <form method="get" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search">--}}
{{--                    <select class="form-control" name="role">--}}
{{--                        <option value="">{{ __('-- Select --')}}</option>--}}
{{--                        @foreach($roles as $role)--}}
{{--                            <option value="{{$role->name}}" @if(Request()->role == $role->name) selected @endif >{{ucfirst($role->name)}}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                    <input type="text" name="s" value="{{ Request()->s }}" placeholder="{{__('Search by name')}}" class="form-control">--}}
{{--                    <button class="btn-info btn btn-icon btn_search" type="submit">{{__('Search User')}}</button>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="text-right">--}}
{{--            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>--}}
{{--        </div>--}}
{{--        <div class="panel">--}}
{{--            <div class="panel-body">--}}
{{--                <form action="" class="bravo-form-item">--}}
{{--                    <div class="table-responsive">--}}
{{--                    <table class="table table-hover">--}}
{{--                        <thead>--}}
{{--                        <tr>--}}
{{--                            <th width="60px"><input type="checkbox" class="check-all"></th>--}}
{{--                            <th>{{__('Name')}}</th>--}}
{{--                            <th>{{__('Email')}}</th>--}}
{{--                            <th>{{__('Credit')}}</th>--}}
{{--                            <th>{{__('Phone')}}</th>--}}
{{--                            <th>{{__('Role')}}</th>--}}
{{--                            <th class="date">{{ __('Date')}}</th>--}}
{{--                            <th class="status">{{__('Status')}}</th>--}}
{{--                            <th></th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @foreach($rows as $row)--}}
{{--                            <tr>--}}
{{--                                <td><input type="checkbox" name="ids[]" value="{{$row->id}}" class="check-item"></td>--}}
{{--                                <td class="title">--}}
{{--                                    <a href="{{route('user.admin.detail',['id'=>$row->id])}}">{{$row->getDisplayName()}}</a>--}}
{{--                                </td>--}}
{{--                                <td>{{$row->email}}--}}
{{--                                    @if($row->email_verified_at)--}}
{{--                                        <i class="fa fa-check-circle text-success" title="{{__("Verified")}}"></i>--}}
{{--                                    @else--}}
{{--                                        <i class="fa fa-info-circle text-warning" title="{{__("Not Verified")}}"></i>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>{{$row->balance}}</td>--}}
{{--                                <td>{{$row->phone}}</td>--}}
{{--                                <td>--}}
{{--                                    {{$row->role->name ?? ''}}--}}
{{--                                </td>--}}
{{--                                <td>{{ display_date($row->created_at)}}</td>--}}
{{--                                --}}{{--<td class="status">{{$row->status}}</td>--}}
{{--                                <td>--}}
{{--                                    <div class="dropdown">--}}
{{--                                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                            <i class="fa fa-th"></i>--}}
{{--                                        </button>--}}
{{--                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">--}}
{{--                                            <a class="dropdown-item"  href="{{route('user.admin.detail',['id'=>$row->id])}}"><i class="fa fa-edit"></i> {{__('Edit')}}</a>--}}
{{--                                            @if(!$row->hasVerifiedEmail())--}}
{{--                                                <a class="dropdown-item"  href="{{route('user.admin.verifyEmail',$row)}}"><i class="fa fa-edit"></i> {{__('Verify email')}}</a>--}}
{{--                                                @else--}}
{{--                                                <a class="dropdown-item"  href="#" ><i class="fa fa-check"></i> {{__('Email verified')}}</a>--}}
{{--                                            @endif--}}
{{--                                            <a class="dropdown-item" href="{{route('user.admin.password',['id'=>$row->id])}}"><i class="fa fa-lock"></i> {{__('Change Password')}}</a>--}}
{{--                                            <a href="{{route('user.admin.wallet.addCredit',['id'=>$row->id])}}" class="dropdown-item"><i class="fa fa-plus"></i> {{__("Add Credit")}}</a>--}}
{{--                                            <a href="{{route('user.admin.wallet.list',['id'=>$row->id])}}" class="dropdown-item"><i class="fa fa-list"></i> {{__("Credit List")}}</a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforeach--}}
{{--                        </tbody>--}}
{{--                    </table>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--                {{$rows->appends(request()->query())->links()}}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}


@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __('All Users')}}</h1>
            <div class="title-actions">
                <a href="{{route('user.admin.create')}}" class="btn btn-primary">{{ __('Add new user')}}</a>
                <a href="{{route('user.admin.wallet.transactions')}}" class="btn btn-primary">{{ __('All Top Up')}}</a>
                <a class="btn btn-warning btn-icon" href="{{ route("user.admin.export") }}" target="_blank" title="{{ __("Export to excel") }}">
                    <i class="icon ion-md-cloud-download"></i> {{ __("Export to excel") }}
                </a>
            </div>
        </div>
        @include('admin.message')
        <div class="filter-div d-flex justify-content-between ">
            <div class="col-left">
                @if(!empty($rows))
                    <form method="post" action="{{route('user.admin.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">
                        {{csrf_field()}}
                        <select name="action" class="form-control">
                            <option value="">{{__(" Bulk Actions ")}}</option>
                            <option value="delete">{{__(" Delete ")}}</option>
                        </select>
                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>
                    </form>
                @endif
            </div>
            <div class="col-left">
                <form method="get" class="filter-form filter-form-right d-flex justify-content-end flex-column flex-sm-row" role="search" id="searchForm">
                    <select class="form-control" name="role" id="roleFilter">
                        <option value="">{{ __('-- Select --')}}</option>
                        @foreach($roles as $role)
                            <option value="{{$role->name}}" @if(Request()->role == $role->name) selected @endif >{{ucfirst($role->name)}}</option>
                        @endforeach
                    </select>
                    {{-- <input type="text" name="s" value="{{ Request()->s }}" placeholder="{{__('Search by name')}}" class="form-control" id="searchInput"> --}}
                    {{-- <button class="btn-info btn btn-icon btn_search" type="submit" id="searchButton">{{__('Search User')}}</button> --}}
                </form>
            </div>
        </div>
        {{-- <div class="text-right">
            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>
        </div> --}}
        <div class="panel">
            <div class="panel-body">
                <form action="" class="bravo-form-item">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                            <tr>
                                <th width="60px"><input type="checkbox" class="check-all"></th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Credit')}}</th>
                                <th>{{__('Phone')}}</th>
                                <th>{{__('Role')}}</th>
                                <th class="date">{{ __('Date')}}</th>
                                <th>{{ __('Reference ID') }}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{$row->id}}" class="check-item"></td>
                                    <td class="title">
                                        <a href="{{route('user.admin.detail',['id'=>$row->id])}}">{{$row->getDisplayName()}}</a>
                                    </td>
                                    <td>{{$row->email}}
                                        @if($row->email_verified_at)
                                            <i class="fa fa-check-circle text-success" title="{{__("Verified")}}"></i>
                                        @else
                                            <i class="fa fa-info-circle text-warning" title="{{__("Not Verified")}}"></i>
                                        @endif
                                    </td>
                                    <td>{{$row->balance}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>
                                        {{$row->role->name ?? ''}}
                                    </td>
                                    <td>{{ display_date($row->created_at)}}</td>
                                    <td>
                                        @if($row->reference_id)
                                            <span class="badge badge-info">
                                            {{ $row->reference_id }}
                                                @php
                                                    $refUser = \Modules\User\Models\User::find($row->reference_id);
                                                @endphp
                                                @if($refUser)
                                                    ({{ $refUser->getDisplayName() }})
                                                @endif
                                        </span>
                                        @else
                                            <span class="badge badge-secondary">Not Set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-th"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="{{route('user.admin.detail',['id'=>$row->id])}}"><i class="fa fa-edit"></i> {{__('Edit')}}</a>
                                                @if(!$row->hasVerifiedEmail())
                                                    <a class="dropdown-item" href="{{route('user.admin.verifyEmail',$row)}}"><i class="fa fa-edit"></i> {{__('Verify email')}}</a>
                                                @else
                                                    <a class="dropdown-item" href="#"><i class="fa fa-check"></i> {{__('Email verified')}}</a>
                                                @endif
                                                <a class="dropdown-item" href="{{route('user.admin.password',['id'=>$row->id])}}"><i class="fa fa-lock"></i> {{__('Change Password')}}</a>
                                                <a href="{{route('user.admin.wallet.addCredit',['id'=>$row->id])}}" class="dropdown-item"><i class="fa fa-plus"></i> {{__("Add Credit")}}</a>
                                                <a href="{{route('user.admin.wallet.list',['id'=>$row->id])}}" class="dropdown-item"><i class="fa fa-list"></i> {{__("Credit List")}}</a>
                                                <a href="#" class="dropdown-item set-reference-btn" data-user-id="{{$row->id}}" data-user-name="{{$row->getDisplayName()}}" data-toggle="modal" data-target="#referenceModal">
                                                    <i class="fa fa-user"></i> {{__("Set Reference")}}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                {{-- {{$rows->appends(request()->query())->links()}} --}}
            </div>
        </div>
    </div>

    <!-- Reference Modal -->
    <div class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-labelledby="referenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="referenceModalLabel">{{ __('Set Reference User') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="current_user_name">{{ __('Current User') }}</label>
                        <input type="text" class="form-control" id="current_user_name" readonly>
                        <input type="hidden" id="current_user_id">
                    </div>
                    <div class="form-group">
                        <label for="reference_user_search">{{ __('Search Reference User') }}</label>
                        <select class="form-control select2" id="reference_user_search" style="width: 100%;">
                            <option value="">{{ __('Search users...') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Selected Reference User') }}</label>
                        <div id="selected_user_info" class="alert alert-info" style="display: none;">
                            <strong id="selected_user_name"></strong>
                            <small id="selected_user_email" class="d-block"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="saveReferenceBtn">{{ __('Save Reference') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        #searchInput.search-active {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        #searchInput {
            transition: all 0.3s ease;
        }

        #searchButton {
            position: relative;
        }

        .loading-indicator {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
        }

        .dataTables_filter {
            display: flex !important;
            align-items: center;
            gap: 10px;
        }

        .dataTables_filter label {
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #roleFilter {
            width: 180px;
            margin-right: 10px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            let selectedUserId = null;
            let modalUserId = null;

            $('#referenceModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var userId = button.data('user-id');
                var userName = button.data('user-name');

                modalUserId = userId;

                $('#current_user_id').val(userId);
                $('#current_user_name').val(userName);

                selectedUserId = null;
                $('#selected_user_info').hide();
                $('#selected_user_name').text('');
                $('#selected_user_email').text('');

                $('#reference_user_search').val('').trigger('change');

                if ($('#reference_user_search').hasClass('select2-hidden-accessible')) {
                    $('#reference_user_search').select2('destroy');
                }

                $('#reference_user_search').select2({
                    dropdownParent: $('#referenceModal .modal-body'),
                    ajax: {
                        url: '{{ route("user.admin.getForSelect2") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: {
                                    more: data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: '{{ __("Search users...") }}',
                    minimumInputLength: 1
                });

                $('#reference_user_search').off('select2:select').on('select2:select', function(e) {
                    var data = e.params.data;
                    selectedUserId = data.id;
                    $('#selected_user_name').text(data.text);
                    $('#selected_user_email').text(data.email || '');
                    $('#selected_user_info').show();
                });
            });

            $('#saveReferenceBtn').on('click', function() {
                if (!selectedUserId) {
                    alert('{{ __("Please select a reference user") }}');
                    return;
                }

                if (selectedUserId == modalUserId) {
                    alert('{{ __("User cannot refer to themselves") }}');
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> {{ __("Saving...") }}');

                var url = '{{ route("user.admin.setReference", ":id") }}'.replace(':id', modalUserId);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        reference_id: selectedUserId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message);
                            $btn.prop('disabled', false).html('{{ __("Save Reference") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __("An error occurred") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 419) {
                            errorMessage = '{{ __("CSRF token mismatch. Please refresh the page.") }}';
                        }
                        alert(errorMessage);
                        $btn.prop('disabled', false).html('{{ __("Save Reference") }}');
                    }
                });
            });
        });

        $(document).ready(function () {

            let table = $('#usersTable').DataTable({
                processing: true,
                responsive: true,
                pageLength: 20,
                order: [[6, 'desc']],
                dom: '<"top d-flex justify-content-between align-items-center mb-3"fB>rtip',
                buttons: [
                    'copy',
                    'csv',
                    'excel',
                    'print'
                ]
            });

            // Move role filter BEFORE search box
            $('#roleFilter').prependTo('.dataTables_filter');

            // Filter by role column
            $('#roleFilter').on('change', function () {

                let value = $(this).val();

                // Role column index = 5
                table.column(5).search(value).draw();

            });

        });

    </script>
@endpush
