@extends('admin.layouts.app')
@section('title', 'Banners')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{ __("All Banners") }}</h1>
            <div class="title-actions">
                <a href="{{ route('banner.admin.create') }}" class="btn btn-primary">{{ __("Add new Banner") }}</a>
            </div>
        </div>

        @include('admin.message')

        <div class="filter-div d-flex justify-content-between">
            <div class="col-left">
                @if(!empty($rows))
                    <form method="post" action="{{ route('banner.admin.bulkEdit') }}"
                          class="filter-form filter-form-left d-flex justify-content-start">
                        {{ csrf_field() }}
                        <select name="action" class="form-control">
                            <option value="">{{ __(" Bulk Actions ") }}</option>
                            <option value="active">{{ __(" Active ") }}</option>
                            <option value="inactive">{{ __(" Inactive ") }}</option>
                            <option value="delete">{{ __(" Delete ") }}</option>
                        </select>
                        <button data-confirm="{{ __("Do you want to delete?") }}"
                                class="btn-info btn btn-icon dungdt-apply-form-btn"
                                type="button">{{ __('Apply') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <form action="" class="bravo-form-item">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="60px"><input type="checkbox" class="check-all"></th>
                                            <th>{{ __('Media') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th width="200px">{{ __('Link') }}</th>
                                            <th width="80px">{{ __('Order') }}</th>
                                            <th width="100px">{{ __('Status') }}</th>
                                            <th width="100px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($rows) && count($rows) > 0)
                                            @foreach($rows as $row)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="check-item" name="ids[]" value="{{ $row->id }}">
                                                    </td>
                                                    <td>
                                                        @if($row->type == 'video' && $row->video)
                                                            <div style="position: relative; width: 90px; height: 50px;">
                                                                <video style="width:100%; height:100%; object-fit:cover; border-radius:4px;" muted>
                                                                    <source src="{{ asset('uploads/video/' . $row->video) }}" type="video/mp4">
                                                                </video>
                                                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 20px;">
                                                                    <i class="fa fa-play-circle"></i>
                                                                </div>
                                                            </div>
                                                        @elseif($row->image_id)
                                                            <img src="{{ get_file_url($row->image_id, 'thumb') }}"
                                                                style="height:50px; width:90px; object-fit:cover; border-radius:4px;">
                                                        @else
                                                            {{ __('No Media') }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $row->title ?? __('No Title') }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $row->type }}">
                                                            {{ ucfirst($row->type) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $row->link ?? '-' }}</td>
                                                    <td>{{ $row->order }}</td>
                                                    <td>
                                                        <form action="{{ route('banner.admin.bulkEdit') }}" method="POST">
                                                            @csrf

                                                            <input type="hidden" name="ids[]" value="{{ $row->id }}">

                                                            <select name="action"
                                                                    class="form-control form-control-sm status-change"
                                                                    onchange="this.form.submit()">

                                                                <option value="active"
                                                                    {{ $row->status == 'active' ? 'selected' : '' }}>
                                                                    Active
                                                                </option>

                                                                <option value="inactive"
                                                                    {{ $row->status == 'inactive' ? 'selected' : '' }}>
                                                                    Inactive
                                                                </option>

                                                            </select>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('banner.admin.edit', ['id' => $row->id]) }}"
                                                        class="btn btn-primary btn-sm">
                                                            <i class="fa fa-edit"></i> {{ __('Edit') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8">{{ __("No data") }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .status-change{
        min-width:110px;
        border-radius:6px;
        font-size:13px;
    }
</style>
