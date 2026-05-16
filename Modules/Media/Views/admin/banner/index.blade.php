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
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Title') }}</th>
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
                                                    @if($row->image_id)
                                                        <img src="{{ get_file_url($row->image_id, 'thumb') }}"
                                                             style="height:50px; width:90px; object-fit:cover; border-radius:4px;">
                                                    @else
                                                        {{ __('No Image') }}
                                                    @endif
                                                </td>
                                                <td>{{ $row->title ?? __('No Title') }}</td>
                                                <td>{{ $row->link ?? '-' }}</td>
                                                <td>{{ $row->order }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $row->status }}">{{ $row->status }}</span>
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
                                            <td colspan="7">{{ __("No data") }}</td>
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
