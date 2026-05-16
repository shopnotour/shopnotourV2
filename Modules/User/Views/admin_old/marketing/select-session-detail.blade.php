@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">
                <i class="fa fa-check-square"></i> {{ __('Price Check Details') }} —
                <small>{{ $user->first_name }} {{ $user->last_name }}</small>
            </h1>
            <a href="{{ route('admin.marketing.select.sessions') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @include('admin.message')

        {{-- User Info Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">{{ __('Name') }}</small>
                        <p class="mb-0 font-weight-bold">{{ $user->first_name }} {{ $user->last_name }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">{{ __('Email') }}</small>
                        <p class="mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">{{ __('Phone') }}</small>
                        <p class="mb-0">{{ $user->phone }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">{{ __('Total Price Checks') }}</small>
                        <p class="mb-0 font-weight-bold text-warning">{{ number_format($sessions->total()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sessions Table --}}
        <div class="panel mt-3">
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Session ID') }}</th>
                            <th>{{ __('Data') }}</th>
                            <th class="text-center">{{ __('Date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($sessions as $session)
                            @php
                                $data = json_decode($session->data, true) ?? [];
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><small class="text-muted">{{ $session->session_id }}</small></td>
                                <td>
                                    @if(!empty($data))
                                        <small><pre class="mb-0" style="font-size:11px;">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($session->created_at)->format('d M Y') }}<br>
                                    <small>{{ \Carbon\Carbon::parse($session->created_at)->format('h:i A') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fa fa-info-circle"></i> {{ __('No sessions found') }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
