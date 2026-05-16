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
        .mk-header h1 .icon-wrap { width:38px; height:38px; background:var(--primary-lt); border-radius:9px; display:grid; place-items:center; color:var(--primary); font-size:15px; }

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

        .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        @media(max-width:992px){ .stat-grid{ grid-template-columns:repeat(2,1fr); } }
        @media(max-width:576px){ .stat-grid{ grid-template-columns:1fr; } }

        .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; box-shadow:var(--shadow); display:flex; align-items:center; gap:14px; text-decoration:none; transition:box-shadow .18s,transform .18s; position:relative; overflow:hidden; }
        .stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); transform:translateY(-2px); text-decoration:none; }
        .stat-card::before { content:''; position:absolute; top:0; left:0; width:3px; height:100%; background:var(--card-accent, var(--primary)); border-radius:var(--radius) 0 0 var(--radius); }
        .stat-icon { width:46px; height:46px; border-radius:12px; display:grid; place-items:center; font-size:18px; flex-shrink:0; }
        .stat-icon.violet { background:#EEEDFE; color:#534AB7; --card-accent:#534AB7; }
        .stat-icon.green  { background:#E1F5EE; color:#0F6E56; --card-accent:#0F6E56; }
        .stat-icon.blue   { background:#eff6ff; color:#2563eb; --card-accent:#2563eb; }

        .stat-content { min-width:0; }
        .stat-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px; }
        .stat-value { font-size:22px; font-weight:700; color:var(--text); line-height:1.1; font-family:'DM Mono',monospace; }
        .stat-sub { font-size:11px; margin-top:4px; display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:4px; }
        .stat-sub.success { background:var(--success-lt); color:var(--success); }

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
                <span class="icon-wrap"><i class="fa fa-search"></i></span>
                {{ __('Search Sessions') }}
            </h1>
            <a href="{{ route('admin.marketing.dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> {{ __('Back') }}
            </a>
        </div>

        @include('admin.message')

        
        

        {{-- ✅ Search Box --}}
        {{-- <div class="card border-0 shadow-sm mb-4 fade-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text"
                                   id="cardSearch"
                                   class="form-control"
                                   placeholder="{{ __('Search by name, email, phone...') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- Date Filter --}}
        <div class="filter-card fade-up">
            <span class="filter-label"><i class="fa fa-calendar"></i> Date Range</span>
            <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="filter-group">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="filter-group">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}">
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
                                   placeholder="{{ __('Search by name, email, phone...') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="filter-date-range">
                <i class="fa fa-calendar-o"></i>
                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                @if($dateFrom !== $dateTo)
                    — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                @endif
            </span>
        </div>

        {{-- ✅ User Cards — 4 columns --}}
        <div class="row" id="userCardsContainer">
            @forelse($users as $user)
                <div class="col-md-3 col-sm-6 mb-4 user-card-item"
                     data-name="{{ strtolower($user->first_name . ' ' . $user->last_name) }}"
                     data-email="{{ strtolower($user->email) }}"
                     data-phone="{{ $user->phone }}">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">

                            {{-- Avatar + Name --}}
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width:42px;height:42px;background:#EEEDFE;flex-shrink:0;">
                                    <span style="color:#534AB7;font-weight:500;font-size:15px;">
                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                    </span>
                                </div>
                                <div style="overflow:hidden;">
                                    <p class="mb-0 font-weight-bold" style="font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </p>
                                    <small class="text-muted" style="font-size:12px;">{{ $user->email }}</small>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="mb-3">
                                @if($user->phone)
                                    <div class="mb-1">
                                        <small class="text-muted"><i class="fa fa-phone"></i> {{ $user->phone }}</small>
                                    </div>
                                @endif
                                <div class="mb-1">
                                    <small class="text-muted">
                                        <i class="fa fa-clock-o"></i>
                                        {{ \Carbon\Carbon::parse($user->last_search_at)->format('d M Y, h:i A') }}
                                    </small>
                                </div>
                            </div>

                            {{-- Search Count + Action --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">{{ __('Total Searches') }}</small>
                                    <span class="badge badge-primary" style="font-size:13px;padding:4px 10px;">
                                        {{ number_format($user->search_count) }}
                                    </span>
                                </div>
                                <a href="{{ route('admin.marketing.search.session.detail', ['userId' => $user->user_id, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-eye"></i> {{ __('Details') }}
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> {{ __('No search sessions found') }}
                    </div>
                </div>
            @endforelse
        </div>

        {{-- No results message --}}
        <div id="noResults" class="text-center py-4" style="display:none;">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> {{ __('No matching users found') }}
            </div>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-end mt-2">
            {{ $users->links() }}
        </div>

    </div>
@endsection

@push('js')
    <script>
        // ✅ Client-side card search
        $('#cardSearch').on('keyup', function () {
            const query = $(this).val().toLowerCase().trim();
            let visibleCount = 0;

            $('.user-card-item').each(function () {
                const name  = $(this).data('name');
                const email = $(this).data('email');
                const phone = $(this).data('phone') || '';

                if (name.includes(query) || email.includes(query) || phone.includes(query)) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            $('#noResults').toggle(visibleCount === 0);
        });
    </script>
@endpush
