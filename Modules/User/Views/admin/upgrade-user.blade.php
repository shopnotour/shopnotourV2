{{--@extends('admin.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container-fluid">--}}
{{--        <div class="d-flex justify-content-between mb20">--}}
{{--            <h1 class="title-bar">{{__("Vendor Requests")}}</h1>--}}
{{--        </div>--}}
{{--        @include('admin.message')--}}
{{--        <div class="filter-div d-flex justify-content-between ">--}}
{{--            <div class="col-left">--}}
{{--                @if(!empty($rows))--}}
{{--                    <form method="post" action="{{route('user.admin.userUpgradeRequestApproved')}}" class="filter-form filter-form-left d-flex justify-content-start">--}}
{{--                        {{csrf_field()}}--}}
{{--                        <select name="action" class="form-control">--}}
{{--                            <option value="">{{__(" Bulk Actions ")}}</option>--}}
{{--                            <option value="approved">{{__(" Approved ")}}</option>--}}
{{--                            <option value="delete">{{__(" Delete ")}}</option>--}}
{{--                        </select>--}}
{{--                        <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>--}}
{{--                    </form>--}}
{{--                @endif--}}
{{--            </div>--}}

{{--        </div>--}}
{{--        <div class="text-right">--}}
{{--            <p><i>{{__('Found :total items',['total'=>$rows->total()])}}</i></p>--}}
{{--        </div>--}}
{{--        <div class="panel">--}}
{{--            <div class="panel-body">--}}
{{--                <form action="" class="bravo-form-item">--}}
{{--                    <div class="table-responsive">--}}
{{--                        --}}{{-- modify modify by rahat start --}}
{{--                        <div class="col-right" style="margin-bottom:10px;">--}}
{{--                            <label>{{ __('Search') }}:</label>--}}
{{--                            <input type="text" id="upgradeSearch" class="form-control" placeholder="{{ __('Search by name, email, role, status...') }}" style="width:300px;display:inline-block;">--}}
{{--                        </div>--}}
{{--                        --}}{{-- modify end --}}
{{--                    <table class="table table-hover">--}}
{{--                        <thead>--}}
{{--                        <tr>--}}
{{--                            <th width="60px"><input type="checkbox" class="check-all"></th>--}}
{{--                            <th>{{__('Name')}}</th>--}}
{{--                            <th>{{__('Email')}}</th>--}}
{{--                            <th>{{ __('Role request')}}</th>--}}
{{--                            <th class="date">{{ __('Date request')}}</th>--}}
{{--                            <th class="date">{{ __('Date approved')}}</th>--}}
{{--                            <th>{{ __('Approved By')}}</th>--}}
{{--                            <th class="status">{{__('Status')}}</th>--}}
{{--                            <th></th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @if($rows->total() > 0)--}}
{{--                            @foreach($rows as $row)--}}
{{--                                <tr>--}}
{{--                                    <td><input type="checkbox" name="ids[]" value="{{$row->id}}" class="check-item"></td>--}}
{{--                                    <td class="title">--}}
{{--                                        <a href="{{route('user.admin.details',['id'=>@$row->user->id])}}">{{@$row->user->getDisplayName()}}</a>--}}
{{--                                    </td>--}}
{{--                                    <td>{{$row->user->email}}</td>--}}
{{--                                    <td>--}}
{{--                                        @php $role = $row->role;--}}
{{--                                    if(!empty($role)){--}}
{{--                                        echo e(ucfirst($role->name));--}}
{{--                                    }--}}
{{--                                        @endphp--}}
{{--                                    </td>--}}
{{--                                    <td>{{ display_date($row->created_at)}}</td>--}}
{{--                                    <td>{{ $row->approved_time ? display_date($row->approved_time) : ''}}</td>--}}
{{--                                    <td>{{ $row->approvedBy->getDisplayName()}}</td>--}}
{{--                                    <td class="status"><span class="badge badge-{{ $row->status }}">{{ $row->status }}</span></td>--}}
{{--                                    <td>--}}
{{--                                        @if($row->status!='approved')--}}
{{--                                            <a class="btn btn-sm btn-info approve-user" data-id="{{$row->id}}"  href="{{route('user.admin.upgradeId',['id' => $row->id])}}">{{__('Approve')}}</a>--}}
{{--                                        @endif--}}
{{--                                    </td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
{{--                        @else--}}
{{--                            <tr>--}}
{{--                                <td colspan="8">{{__("No data")}}</td>--}}
{{--                            </tr>--}}
{{--                        @endif--}}
{{--                        </tbody>--}}
{{--                    </table>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--                {{$rows->appends(request()->query())->links()}}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endsection--}}

{{--@push('js')--}}
{{--    <script>--}}
{{--        $(document).ready(function () {--}}
{{--            $('.approve-user').click(function (e) {--}}
{{--                e.preventDefault();--}}
{{--                if(confirm('Are you sure approve?')){--}}
{{--                    ids = '<input type="hidden" name="ids[]" value="'+$(this).data('id')+'">';--}}
{{--                    form = $('.dungdt-apply-form-btn').closest('form');--}}
{{--                    form.append(ids);--}}
{{--                    form.find('select').val('approved');--}}
{{--                    form.submit();--}}
{{--                }--}}
{{--                // modify by rahat start --}}
{{--            });--}}

{{--            $('#upgradeSearch').on('keyup', function () {--}}
{{--                const query = $(this).val().toLowerCase().trim();--}}
{{--                let visibleCount = 0;--}}

{{--                $('.table tbody tr').each(function () {--}}
{{--                    const text = $(this).text().toLowerCase();--}}
{{--                    if (text.includes(query)) {--}}
{{--                        $(this).show();--}}
{{--                        visibleCount++;--}}
{{--                    } else {--}}
{{--                        $(this).hide();--}}
{{--                    }--}}
{{--                });--}}

{{--                const $noResults = $('#upgradeNoResults');--}}
{{--                if (visibleCount === 0) {--}}
{{--                    if (!$noResults.length) {--}}
{{--                        $('.table tbody').append(--}}
{{--                            '<tr id="upgradeNoResults"><td colspan="9" class="text-center">{{ __("No matching records found") }}</td></tr>'--}}
{{--                        );--}}
{{--                    }--}}
{{--                    $noResults.show();--}}
{{--                } else {--}}
{{--                    $noResults.hide();--}}
{{--                }--}}
{{--            });--}}
{{--            // modify end--}}
{{--        })--}}
{{--    </script>--}}
{{--@endpush--}}



@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __("Vendor Requests") }}</h1>
        </div>
        @include('admin.message')

        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                <form method="post"
                      action="{{ route('user.admin.userUpgradeRequestApproved') }}"
                      class="filter-form filter-form-left d-flex justify-content-start"
                      id="bulkForm">
                    {{ csrf_field() }}
                    <select name="action" id="bulkActionSelect" class="form-control">
                        <option value="">{{ __(" Bulk Actions ") }}</option>
                        <option value="approved">{{ __(" Approved ") }}</option>
                        <option value="delete">{{ __(" Delete ") }}</option>
                    </select>
                    <button data-confirm="{{ __("Do you want to delete?") }}"
                            class="btn-info btn btn-icon dungdt-apply-form-btn"
                            type="button">{{ __('Apply') }}</button>
                </form>
            </div>
        </div>

        <div class="panel mt-3">
            <div class="panel-body">
                <table id="upgradeTable" class="table table-hover" style="width:100%">
                    <thead>
                    <tr>
                        <th width="40px"><input type="checkbox" id="checkAll"></th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Role request') }}</th>
                        <th>{{ __('Date request') }}</th>
                        <th>{{ __('Date approved') }}</th>
                        <th>{{ __('Approved By') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>

        $(document).on('click', '.cancel-request', function (e) {
            e.preventDefault();
            if (!confirm($(this).data('confirm'))) return;
            window.location.href = $(this).attr('href');
        });
        $(document).ready(function () {

            var table = $('#upgradeTable').DataTable({
                processing : true,
                serverSide : true,
                ajax: {
                    url  : '{{ route('user.admin.upgrade') }}',
                    type : 'GET',
                    error: function (xhr) {
                        console.error('DataTable AJAX error:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'checkbox',      orderable: false, searchable: false, width: '40px' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'role',          orderable: false },
                    { data: 'created_at' },
                    { data: 'approved_time', orderable: false },
                    { data: 'approved_by',   orderable: false },
                    { data: 'status',        orderable: false },
                    { data: 'actions',       orderable: false, searchable: false }
                ],
                order     : [[4, 'desc']],
                pageLength: 20,
                language  : {
                    search      : '{{ __("Search") }}:',
                    lengthMenu  : '{{ __("Show") }} _MENU_ {{ __("entries") }}',
                    info        : '{{ __("Showing") }} _START_ {{ __("to") }} _END_ {{ __("of") }} _TOTAL_ {{ __("entries") }}',
                    zeroRecords : '{{ __("No matching records found") }}',
                    processing  : '{{ __("Loading...") }}'
                }
            });

            // Check all
            $(document).on('change', '#checkAll', function () {
                $('.check-item').prop('checked', $(this).is(':checked'));
            });

            // Single approve
            $(document).on('click', '.approve-user', function (e) {
                e.preventDefault();
                if (!confirm('{{ __("Are you sure you want to approve?") }}')) return;

                var id = $(this).data('id');
                $('<form method="POST" action="{{ route('user.admin.userUpgradeRequestApproved') }}">')
                    .append('{{ csrf_field() }}')
                    .append('<input type="hidden" name="action" value="approved">')
                    .append('<input type="hidden" name="ids[]" value="' + id + '">')
                    .appendTo('body')
                    .submit();
            });

            // Bulk apply
            $('.dungdt-apply-form-btn').on('click', function () {
                var action = $('#bulkActionSelect').val();
                if (!action) {
                    alert('{{ __("Select an Action!") }}');
                    return;
                }
                if (action === 'delete') {
                    if (!confirm($(this).data('confirm'))) return;
                }

                var checked = $('.check-item:checked');
                if (checked.length === 0) {
                    alert('{{ __("Select at least 1 item!") }}');
                    return;
                }

                var form = $('#bulkForm');
                form.find('input[name="ids[]"]').remove();
                checked.each(function () {
                    form.append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
                });
                form.submit();
            });

        });
    </script>
@endpush
