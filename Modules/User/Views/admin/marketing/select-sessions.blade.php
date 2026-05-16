@extends('admin.layouts.app')

@push('css')
    <style>
        :root {
            --primary:    #2563eb;
            --primary-lt: #eff6ff;
            --success:    #059669;
            --success-lt: #ecfdf5;
            --warn:       #d97706;
            --warn-lt:    #fffbeb;
            --danger:     #dc2626;
            --danger-lt:  #fef2f2;
            --muted:      #6b7280;
            --border:     #e5e7eb;
            --surface:    #ffffff;
            --bg:         #f3f4f6;
            --text:       #111827;
            --radius:     10px;
            --shadow:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        }

        .mk-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
        .mk-header h1 { font-size:22px; font-weight:600; color:var(--text); margin:0; display:flex; align-items:center; gap:10px; }
        .mk-header h1 .icon-wrap { width:38px; height:38px; background:var(--warn-lt); border-radius:9px; display:grid; place-items:center; color:var(--warn); font-size:15px; }

        .filter-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-label { font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; display:flex; align-items:center; gap:6px; }
        .filter-group { display:flex; align-items:center; gap:6px; }
        .filter-group label { font-size:12px; color:var(--muted); margin:0; white-space:nowrap; }
        .filter-group input[type="date"] { height:32px; font-size:12px; border:1px solid var(--border); border-radius:7px; padding:0 10px; color:var(--text); background:var(--bg); outline:none; font-family:'DM Mono',monospace; transition:border-color .15s,box-shadow .15s; width:140px; }
        .filter-group input[type="date"]:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); background:#fff; }
        .btn-filter { height:32px; padding:0 14px; font-size:12px; font-weight:600; border-radius:7px; border:none; background:var(--primary); color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background .15s; }
        .btn-filter:hover { background:#1d4ed8; }
        .btn-clear { height:32px; padding:0 12px; font-size:12px; font-weight:500; border-radius:7px; border:1px solid var(--border); background:transparent; color:var(--muted); cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; transition:all .15s; }
        .btn-clear:hover { background:var(--bg); color:var(--text); text-decoration:none; }
        .filter-date-range { margin-left:auto; font-size:12px; color:var(--muted); font-family:'DM Mono',monospace; background:var(--bg); padding:5px 12px; border-radius:6px; border:1px solid var(--border); white-space:nowrap; }

        .fade-up { opacity:0; transform:translateY(12px); animation:fadeUp .35s ease forwards; }
        @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
        .fade-up:nth-child(1){animation-delay:.04s}
        .fade-up:nth-child(2){animation-delay:.08s}
        .fade-up:nth-child(3){animation-delay:.12s}
        .fade-up:nth-child(4){animation-delay:.16s}
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">

        <div class="mk-header fade-up">
            <h1>
                <span class="icon-wrap"><i class="fa fa-check-square"></i></span>
                {{ __('Price Check Sessions') }}
            </h1>
            <a href="{{ route('admin.marketing.dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @include('admin.message')

        {{-- Date Filter --}}
        <div class="filter-card fade-up">
            <span class="filter-label"><i class="fa fa-calendar"></i> Date Range</span>
            <form method="GET" action="{{ url()->current() }}" id="dateFilterForm" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? date('Y-m-d') }}">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? date('Y-m-d') }}">
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fa fa-search"></i> Apply
                </button>
                <a href="{{ url()->current() }}" class="btn-clear">
                    <i class="fa fa-times"></i> Today
                </a>
            </form>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text"
                                   id="cardSearch"
                                   class="form-control"
                                   placeholder="{{ __('Search by name, email, phone...') }}"
                                   value="{{ request('s') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="filter-date-range">
                <i class="fa fa-calendar-o"></i>
                {{ \Carbon\Carbon::parse($dateFrom ?? date('Y-m-d'))->format('d M Y') }}
                @if(($dateFrom ?? date('Y-m-d')) !== ($dateTo ?? date('Y-m-d')))
                    — {{ \Carbon\Carbon::parse($dateTo ?? date('Y-m-d'))->format('d M Y') }}
                @endif
            </span>
        </div>

        {{-- Search Box --}}
        {{-- <div class="card border-0 shadow-sm mb-4 fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text"
                                   id="cardSearch"
                                   class="form-control"
                                   placeholder="{{ __('Search by name, email, phone...') }}"
                                   value="{{ request('s') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- Cards Container --}}
        <div id="cardsContainer">
            @include('User::admin.marketing.select_session_cards', compact('users'))
        </div>

        {{-- Loading spinner --}}
        <div id="loadingSpinner" class="text-center py-4" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">{{ __('Loading...') }}</span>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            let searchTimeout;

            function getUrlParams() {
                return {
                    s: $('#cardSearch').val(),
                    date_from: $('input[name="date_from"]').val(),
                    date_to: $('input[name="date_to"]').val()
                };
            }

            function performSearch() {
                const params = getUrlParams();

                $('#loadingSpinner').show();
                $('#cardsContainer').css('opacity', '0.5');

                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: params,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (html) {
                        $('#cardsContainer').html(html);

                        const qs = $.param(params);
                        const newUrl = qs
                            ? `${window.location.pathname}?${qs}`
                            : window.location.pathname;
                        window.history.pushState({}, '', newUrl);
                    },
                    error: function () {
                        alert('{{ __("Search failed. Please try again.") }}');
                    },
                    complete: function () {
                        $('#loadingSpinner').hide();
                        $('#cardsContainer').css('opacity', '1');
                    }
                });
            }

            $('#searchBtn').on('click', function () {
                performSearch();
            });

            $('#cardSearch').on('keyup', function (e) {
                if (e.keyCode === 13) {
                    performSearch();
                } else {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        performSearch();
                    }, 500);
                }
            });

            $('#dateFilterForm').on('submit', function (e) {
                e.preventDefault();
                performSearch();
            });
        });
    </script>
@endpush
