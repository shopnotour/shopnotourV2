@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">
                <i class="fa fa-bar-chart"></i> {{ __('Marketing Dashboard') }}
            </h1>
        </div>

        @include('admin.message')

        {{-- ✅ Stats Cards --}}
        <div class="row">

            {{-- Total B2C Users --}}
            <div class="col-md-3 col-sm-6 mb-4">
                <a href="{{ route('admin.users.index') }}" style="text-decoration:none;">
                    <div class="card border-0 shadow-sm h-100" style="cursor:pointer;transition:box-shadow 0.2s;"
                         onmouseover="this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.boxShadow=''">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1" style="font-size:13px;">{{ __('Total B2C Users') }}</p>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalB2CUsers) }}</h4>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#EEEDFE;">
                                <i class="fa fa-users" style="color:#534AB7;font-size:18px;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Logged In B2C Users --}}
            <div class="col-md-3 col-sm-6 mb-4">
                <a href="{{ route('admin.users.index') }}" style="text-decoration:none;">
                    <div class="card border-0 shadow-sm h-100" style="cursor:pointer;transition:box-shadow 0.2s;"
                         onmouseover="this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.boxShadow=''">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1" style="font-size:13px;">{{ __('Logged In B2C Users') }}</p>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($loggedInB2CUsers) }}</h4>
                                <span class="badge mt-1"
                                      style="background:#E1F5EE;color:#0F6E56;font-size:11px;padding:3px 8px;border-radius:6px;">
                                    {{ __('Current Logged In') }}
                                </span>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#E1F5EE;">
                                <i class="fa fa-sign-in" style="color:#0F6E56;font-size:18px;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Deposit Count --}}
            <div class="col-md-3 col-sm-6 mb-4">
                <a href="{{ route('user.admin.wallet.transactions') }}" style="text-decoration:none;">
                    <div class="card border-0 shadow-sm h-100" style="cursor:pointer;transition:box-shadow 0.2s;"
                         onmouseover="this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.boxShadow=''">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1" style="font-size:13px;">{{ __('Deposit') }}</p>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalDeposit) }}</h4>
                                @if($pendingDeposit > 0)
                                    <small class="text-warning">
                                        <i class="fa fa-clock-o"></i>
                                        {{ __('Pending:') }} {{ number_format($pendingDeposit) }}
                                    </small>
                                @endif
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#EAF3DE;">
                                <i class="fa fa-arrow-down" style="color:#3B6D11;font-size:18px;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Withdraw Count --}}
            <div class="col-md-3 col-sm-6 mb-4">
                <a href="{{ route('user.admin.wallet.transactions') }}" style="text-decoration:none;">
                    <div class="card border-0 shadow-sm h-100" style="cursor:pointer;transition:box-shadow 0.2s;"
                         onmouseover="this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)'"
                         onmouseout="this.style.boxShadow=''">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1" style="font-size:13px;">{{ __('Withdraw') }}</p>
                                <h4 class="mb-0 font-weight-bold text-dark">{{ number_format($totalWithdraw) }}</h4>
                                @if($pendingWithdraw > 0)
                                    <small class="text-warning">
                                        <i class="fa fa-clock-o"></i>
                                        {{ __('Pending:') }} {{ number_format($pendingWithdraw) }}
                                    </small>
                                @endif
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#FAECE7;">
                                <i class="fa fa-arrow-up" style="color:#993C1D;font-size:18px;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>
        {{-- END Stats Cards --}}


        {{-- ✅ Flight Activities Table --}}
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">{{ __('Flight Activities') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Activity') }}</th>
                                    <th width="100px" class="text-center">{{ __('Count') }}</th>
                                    <th width="120px" class="text-center">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                {{-- Search --}}
                                <tr>
                                    <td>{{ __('Search') }}</td>
                                    <td class="text-center"><strong>{{ number_format($searchCount) }}</strong></td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.marketing.search.sessions') }}"
                                           class="btn btn-primary btn-sm">
                                            {{ __('Details') }}
                                        </a>
                                    </td>
                                </tr>

                                {{-- Price Checked --}}
                                <tr>
                                    <td>{{ __('Price Checked') }}</td>
                                    <td class="text-center"><strong>{{ number_format($priceCheckedCount) }}</strong></td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.marketing.select.sessions') }}"
                                           class="btn btn-primary btn-sm">
                                            {{ __('Details') }}
                                        </a>
                                    </td>
                                </tr>

                                {{-- Dynamic Booking Statuses --}}
                                @foreach($bookingActivities as $activity)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</td>
                                        <td class="text-center"><strong>{{ number_format($activity->total) }}</strong></td>
                                        <td class="text-center">
                                            <a href="{{ route('bookings.index', ['status' => $activity->status]) }}"
                                               class="btn btn-primary btn-sm">
                                                {{ __('Details') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END Flight Activities --}}

    </div>
@endsection
