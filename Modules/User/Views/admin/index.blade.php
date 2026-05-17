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

        <div class="filter-div d-flex justify-content-between mb-3">
            <div class="col-left">
                <form method="post" action="{{route('user.admin.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start" id="bulkForm">
                    {{csrf_field()}}
                    <select name="action" class="form-control" style="width:auto;">
                        <option value="">{{__(" Bulk Actions ")}}</option>
                        <option value="delete">{{__(" Delete ")}}</option>
                    </select>
                    <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn ml-2" type="button">{{__('Apply')}}</button>
                </form>
            </div>
            <div class="col-right">
                <select class="form-control" id="roleFilter" style="width:180px; display:inline-block;">
                    <option value="">{{ __('-- All Roles --')}}</option>
                    @foreach($roles as $role)
                        <option value="{{$role->name}}">{{ucfirst($role->name)}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                        <tr>
                            <th width="40px"><input type="checkbox" class="check-all"></th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Email')}}</th>
                            <th>{{__('Credit')}}</th>
                            <th>{{__('Phone')}}</th>
                            <th>{{__('Role')}}</th>
                            <th>{{ __('Date')}}</th>
                            <th>{{ __('Reference ID') }}</th>
                            <th width="80px"></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reference Modal -->
    <div class="modal fade" id="referenceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Set Reference User') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('Current User') }}</label>
                        <input type="text" class="form-control" id="current_user_name" readonly>
                        <input type="hidden" id="current_user_id">
                    </div>
                    <div class="form-group">
                        <label>{{ __('Search Reference User') }}</label>
                        <select class="form-control" id="reference_user_search" style="width:100%;"></select>
                    </div>
                    <div class="form-group">
                        <div id="selected_user_info" class="alert alert-info" style="display:none;">
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <style>
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: .25rem; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    </style>
@endpush


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {

            let table = $('#usersTable').DataTable({
                processing : true,
                serverSide : true,
                responsive : true,
                pageLength : 20,
                searching  : true,
                searchDelay: 500,     // ✅ type করার 500ms পরে AJAX যাবে — real-time search
                order      : [[6, 'desc']],
                language   : {
                    search: '{{ __("Search:") }}',
                    searchPlaceholder: '{{ __("Name, email, phone...") }}'
                },
                dom: '<"top d-flex justify-content-between align-items-center mb-3"f>rt<"bottom d-flex justify-content-between align-items-center"ip>',
                ajax: {
                    url  : '{{ route("user.admin.index") }}',
                    type : 'GET',
                    data : function (d) {
                        // ✅ d.search.value DataTables নিজেই set করে
                        // controller এ $request->input('search.value') দিয়ে পাবে
                        d.role = $('#roleFilter').val();
                    }
                },
                columns: [
                    {
                        data: null, orderable: false, searchable: false,
                        render: function (data) {
                            return '<input type="checkbox" name="ids[]" value="' + data.id + '" class="check-item" form="bulkForm">';
                        }
                    },
                    { data: 'name',       orderable: true,  searchable: false },
                    { data: 'email',      orderable: false, searchable: false },
                    { data: 'balance',    orderable: false, searchable: false },
                    { data: 'phone',      orderable: false, searchable: false },
                    { data: 'role',       orderable: false, searchable: false },
                    { data: 'created_at', orderable: true,  searchable: false },
                    { data: 'reference',  orderable: false, searchable: false },
                    { data: 'actions',    orderable: false, searchable: false },
                ]
            });

            // Role filter
            $('#roleFilter').on('change', function () {
                table.ajax.reload();
            });

            // Check All
            $(document).on('change', '.check-all', function () {
                $('.check-item').prop('checked', $(this).is(':checked'));
            });

            // =====================
            // Reference Modal
            // =====================
            let selectedUserId = null;
            let modalUserId    = null;

            $(document).on('click', '.set-reference-btn', function () {
                modalUserId = $(this).data('user-id');
                $('#current_user_name').val($(this).data('user-name'));
                selectedUserId = null;
                $('#selected_user_info').hide();

                if ($('#reference_user_search').hasClass('select2-hidden-accessible')) {
                    $('#reference_user_search').select2('destroy');
                }

                $('#reference_user_search').select2({
                    dropdownParent    : $('#referenceModal .modal-body'),
                    placeholder       : '{{ __("Search users...") }}',
                    minimumInputLength: 1,
                    ajax: {
                        url          : '{{ route("user.admin.getForSelect2") }}',
                        dataType     : 'json',
                        delay        : 250,
                        data         : function (p) { return { q: p.term }; },
                        processResults: function (data) { return { results: data.results }; },
                        cache        : true
                    }
                }).on('select2:select', function (e) {
                    selectedUserId = e.params.data.id;
                    $('#selected_user_name').text(e.params.data.text);
                    $('#selected_user_email').text(e.params.data.email || '');
                    $('#selected_user_info').show();
                });

                $('#referenceModal').modal('show');
            });

            $('#saveReferenceBtn').on('click', function () {
                if (!selectedUserId) { alert('{{ __("Please select a reference user") }}'); return; }
                if (selectedUserId == modalUserId) { alert('{{ __("User cannot refer to themselves") }}'); return; }

                var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                $.ajax({
                    url     : '{{ route("user.admin.setReference", ":id") }}'.replace(':id', modalUserId),
                    method  : 'POST',
                    data    : { _token: '{{ csrf_token() }}', reference_id: selectedUserId },
                    dataType: 'json',
                    success : function (r) {
                        alert(r.message);
                        if (r.status) { $('#referenceModal').modal('hide'); table.ajax.reload(); }
                        else $btn.prop('disabled', false).html('{{ __("Save Reference") }}');
                    },
                    error   : function (xhr) {
                        alert(xhr.responseJSON?.message || '{{ __("An error occurred") }}');
                        $btn.prop('disabled', false).html('{{ __("Save Reference") }}');
                    }
                });
            });

        });
    </script>
@endpush
