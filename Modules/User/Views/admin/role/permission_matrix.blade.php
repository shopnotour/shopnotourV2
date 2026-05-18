@extends('admin.layouts.app')
@section('content')
    <form action="{{route('user.admin.role.save_permissions')}}" method="post">
        @csrf
        <div class="container-fluid" style="padding-bottom:80px;">
            <div class="d-flex justify-content-between mb20">
                <h1 class="title-bar">{{ __('Permission Matrix')}}</h1>
            </div>
            @include('admin.message')
            <div class="panel">
                <div class="panel-body" style="padding:0;">
                    <div style="max-height:calc(100vh - 220px); overflow-y:auto; padding-bottom:70px;">
                        <table class="table table-hover" style="margin-bottom:0;">
                            <thead style="position:sticky; top:0; background:#fff; z-index:5;">
                            <tr>
                                <th style="position:sticky; left:0; z-index:6; background:#fff; min-width:220px; border-bottom:2px solid #dee2e6;">{{ __('Permission')}}</th>
                                @foreach($roles as $role)
                                    <th style="text-align:center; min-width:110px; border-bottom:2px solid #dee2e6;">{{ucfirst($role->name)}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($permissions_group as $gName=>$permissions)
                                <tr class="active" data-group="{{$gName}}">
                                    <td style="position:sticky; left:0; z-index:3; background:#f5f5f5; font-weight:700;">
                                        {{ucfirst($gName)}}
                                    </td>
                                    @foreach($roles as $role)
                                        <td style="text-align:center; background:#f5f5f5;">
                                            <input type="checkbox" class="group-checkbox" data-group="{{$gName}}" data-role="{{$role->id}}">
                                        </td>
                                    @endforeach
                                </tr>
                                @if(!empty($permissions))
                                    @foreach($permissions as $permission)
                                        <tr>
                                            <td style="position:sticky; left:0; z-index:2; background:#fff; padding-left:30px!important;">
                                                {{$permission}}
                                            </td>
                                            @foreach($roles as $role)
                                                <td style="text-align:center;">
                                                    <input type="checkbox" class="perm-checkbox" data-group="{{$gName}}" data-role="{{$role->id}}" @if(in_array($permission,$selectedIds[$role->id])) checked @endif name="matrix[{{$role->id}}][]" value="{{$permission}}">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="save-bar">
            <div class="d-flex justify-content-between">
                <span></span>
                <button class="btn btn-primary" type="submit">{{ __('Save Changes')}}</button>
            </div>
        </div>
    </form>

    <style>
        .save-bar {
            position: fixed;
            bottom: 0;
            left: 240px;
            right: 0;
            z-index: 999;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 12px 30px;
        }
        .save-bar .btn {
            padding: 8px 35px;
        }
        @media (max-width: 991px) {
            .save-bar { left: 0; }
        }
    </style>
@endsection

@push('js')
    <script>
        document.querySelectorAll('.group-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var group = this.dataset.group;
                var role = this.dataset.role;
                var checked = this.checked;
                document.querySelectorAll('.perm-checkbox[data-group="' + group + '"][data-role="' + role + '"]').forEach(function(pcb) {
                    pcb.checked = checked;
                });
            });
        });

        document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var group = this.dataset.group;
                var role = this.dataset.role;
                var groupCb = document.querySelector('.group-checkbox[data-group="' + group + '"][data-role="' + role + '"]');
                var perms = document.querySelectorAll('.perm-checkbox[data-group="' + group + '"][data-role="' + role + '"]');
                var allChecked = true;
                var anyChecked = false;
                perms.forEach(function(p) {
                    if (p.checked) anyChecked = true;
                    else allChecked = false;
                });
                if (groupCb) {
                    groupCb.checked = allChecked;
                    groupCb.indeterminate = anyChecked && !allChecked;
                }
            });
        });

        document.querySelectorAll('.group-checkbox').forEach(function(cb) {
            var group = cb.dataset.group;
            var role = cb.dataset.role;
            var perms = document.querySelectorAll('.perm-checkbox[data-group="' + group + '"][data-role="' + role + '"]');
            var allChecked = true;
            var anyChecked = false;
            perms.forEach(function(p) {
                if (p.checked) anyChecked = true;
                else allChecked = false;
            });
            cb.checked = allChecked;
            cb.indeterminate = anyChecked && !allChecked;
        });
    </script>
@endpush
