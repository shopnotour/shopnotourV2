{{--@extends('admin.layouts.app')--}}
{{--@section('content')--}}
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">--}}

{{--    <style>--}}
{{--        .stat-badge {--}}
{{--            font-size: 16px;                  /* Badge বড় */--}}
{{--            padding: 8px 14px;                /* Badge padding বেশি */--}}
{{--        }--}}
{{--        .stat-item {--}}
{{--            padding: 18px 15px !important;--}}
{{--            font-size: 17px;--}}
{{--        }--}}
{{--        .stat-item i {--}}
{{--            font-size: 20px;--}}
{{--            margin-right: 6px;--}}
{{--        }--}}
{{--        .stat-count {--}}
{{--            font-weight: bold;--}}
{{--            font-size: 15px;--}}
{{--            padding: 3px 8px;--}}
{{--            border-radius: 5px;--}}
{{--        }--}}
{{--        .count-primary { background:#007bff22; color:#0056b3; }--}}
{{--        .count-warning { background:#ffc10733; color:#b38300; }--}}
{{--        .count-success { background:#28a74533; color:#1f7a33; }--}}
{{--        .count-danger { background:#dc354533; color:#8a1f28; }--}}
{{--        .count-info { background:#17a2b833; color:#0f6674; }--}}
{{--        .count-secondary { background:#6c757d33; color:#4a5054; }--}}

{{--        .daterangepicker {--}}
{{--            width: auto !important;--}}
{{--        }--}}
{{--        .daterangepicker .drp-calendar {--}}
{{--            float: left !important;--}}
{{--        }--}}

{{--    </style>--}}
{{--    <div class="container-fluid">--}}
{{--        <div class="d-flex justify-content-between mb20">--}}
{{--            <h1>Credit Transactions</h1>--}}
{{--            --}}{{----}}{{--            <h1 class="title-bar text-warning">{{$users->business_name}} =><span> Balance: {{$users->credit_balance}}  </span> </h1>--}}
{{--        </div>--}}
{{--        @include('admin.message')--}}
{{--        <div class="filter-div d-flex justify-content-between">--}}
{{--            <div class="col-left">--}}
{{--                @if(!empty($rows))--}}
{{--                    --}}{{----}}{{--                            <form method="post" action="{{route('user.admin.wallet.reportBulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">--}}
{{--                    --}}{{----}}{{--                                {{csrf_field()}}--}}
{{--                    --}}{{----}}{{--                                <select name="action" class="form-control">--}}
{{--                    --}}{{----}}{{--                                    <option value="">{{__(" Bulk Actions ")}}</option>--}}
{{--                    --}}{{----}}{{--                                    <option value="completed">{{__("Mark as completed")}}</option>--}}
{{--                    --}}{{----}}{{--                                </select>--}}
{{--                    --}}{{----}}{{--                                <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>--}}
{{--                    --}}{{----}}{{--                            </form>--}}
{{--                @endif--}}
{{--            </div>--}}
{{--            <div class="col-left">--}}
{{--                <form method="get" action="{{route('user.admin.wallet.status_filter')}}" class="filter-form filter-form-right d-flex justify-content-end">--}}
{{--                    <select name="status" class="form-control">--}}
{{--                        <option value="">{{__("-- Status --")}}</option>--}}
{{--                        <option value="pending">{{__("-- Pending --")}}</option>--}}
{{--                        <option value="confirmed">{{__("-- Confirmed --")}}</option>--}}
{{--                        --}}{{----}}{{--                                <option @if(request()->query('status') == 'fail') selected @endif value="fail">{{__("Failed")}}</option>--}}
{{--                        --}}{{----}}{{--                                <option @if(request()->query('status') == 'processing') selected @endif value="processing">{{__("Processing")}}</option>--}}
{{--                        --}}{{----}}{{--                                <option @if(request()->query('status') == 'completed') selected @endif value="completed">{{__("Completed")}}</option>--}}
{{--                    </select>--}}
{{--                    @csrf--}}
{{--                    --}}{{----}}{{--                            <?php--}}
{{--                    --}}{{----}}{{--                            $user = !empty(Request()->user_id) ? App\User::find(Request()->user_id) : false;--}}
{{--                    --}}{{----}}{{--                            \App\Helpers\AdminForm::select2('user_id', [--}}
{{--                    --}}{{----}}{{--                                'configs' => [--}}
{{--                    --}}{{----}}{{--                                    'ajax'        => [--}}
{{--                    --}}{{----}}{{--                                        'url'      => route('user.admin.getForSelect2'),--}}
{{--                    --}}{{----}}{{--                                        'dataType' => 'json'--}}
{{--                    --}}{{----}}{{--                                    ],--}}
{{--                    --}}{{----}}{{--                                    'allowClear'  => true,--}}
{{--                    --}}{{----}}{{--                                    'placeholder' => __('-- User --')--}}
{{--                    --}}{{----}}{{--                                ]--}}
{{--                    --}}{{----}}{{--                            ], !empty($user->id) ? [--}}
{{--                    --}}{{----}}{{--                                $user->id,--}}
{{--                    --}}{{----}}{{--                                $user->name_or_email . ' (#' . $user->id . ')'--}}
{{--                    --}}{{----}}{{--                            ] : false)--}}
{{--                    --}}{{----}}{{--                            ?>--}}
{{--                    <button class="btn-info btn btn-icon" type="submit">{{__('Filter')}}</button>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="text-right">--}}
{{--            <p><i>{{__('Found :total items',['total'])}}</i></p>--}}
{{--        </div>--}}
{{--        @php--}}
{{--            $totalDeposit = $deposits->sum('amount');--}}
{{--            $totalWithdraw = $withdraws->sum('amount');--}}
{{--        @endphp--}}

{{--        <div class="panel booking-history-manager">--}}
{{--            <div class="panel-title d-flex justify-content-between align-items-center w-100">--}}

{{--    <span class="font-weight-bold h5 mb-0">--}}
{{--        <i class="fa fa-wallet text-primary mr-1"></i> {{ __('Wallet Report') }}--}}
{{--    </span>--}}

{{--                <!-- Filter Box -->--}}
{{--                <div class="card shadow-sm border-0" style="min-width: 350px;">--}}
{{--                    <div class="card-body py-2">--}}

{{--                        <form action="" method="GET" class="form-inline justify-content-end">--}}

{{--                            <!-- Date Label -->--}}
{{--                            <label class="font-weight-bold mr-2 mb-0"--}}
{{--                                   style="background: linear-gradient(45deg, #4e73df, #1cc88a);--}}
{{--              color: #fff; padding: 4px 10px;--}}
{{--              border-radius: 4px;">--}}
{{--                                <i class="fa fa-calendar mr-1"></i> Date Range:--}}
{{--                            </label>--}}
{{--                            <label class="font-weight-bold mr-2 mb-0 badge badge-primary p-2">--}}
{{--                                <i class="fa fa-calendar mr-1"></i> Date Range:--}}
{{--                            </label>--}}

{{--                            <!-- Date Input -->--}}
{{--                            <input type="text"--}}
{{--                                   name="date_range"--}}
{{--                                   id="daterange"--}}
{{--                                   class="form-control form-control-sm mr-2"--}}
{{--                                   style="min-width: 200px;"--}}
{{--                                   placeholder="Select Date Range"--}}
{{--                                   value="{{ request('from') && request('to') ? request('from').' - '.request('to') : '' }}">--}}

{{--                            <!-- Hidden Fields -->--}}
{{--                            <input type="hidden" name="from" value="{{ request('from') }}">--}}
{{--                            <input type="hidden" name="to" value="{{ request('to') }}">--}}

{{--                            <!-- Filter Button -->--}}
{{--                            <button type="submit" class="btn btn-primary btn-sm mr-2">--}}
{{--                                <i class="fa fa-search"></i>--}}
{{--                            </button>--}}

{{--                            <!-- Reset Button -->--}}
{{--                            <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm">--}}
{{--                                <i class="fa fa-sync"></i>--}}
{{--                            </a>--}}

{{--                        </form>--}}

{{--                    </div>--}}
{{--                </div>--}}

{{--            </div>--}}


{{--            <div class="panel-body">--}}
{{--                <div class="row">--}}

{{--                    <!-- Deposit + Withdraw -->--}}
{{--                    <div class="col-md-8">--}}
{{--                        <div class="row">--}}

{{--                            <!-- Deposit Box -->--}}
{{--                            <div class="col-md-6">--}}
{{--                                <div class="card shadow-sm mb-4">--}}
{{--                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">--}}
{{--                                        <div>--}}
{{--                                            <i class="fa fa-arrow-down mr-2"></i> Deposit Logs--}}
{{--                                        </div>--}}
{{--                                        <span class="badge badge-light total-amount-badge">--}}
{{--                        {{ number_format($totalDeposit, 2) }}--}}
{{--                    </span>--}}
{{--                                    </div>--}}

{{--                                    <div class="card-body p-0">--}}
{{--                                        <table class="table table-hover mb-0">--}}
{{--                                            <thead class="bg-light">--}}
{{--                                            <tr>--}}
{{--                                                <th>Name</th>--}}
{{--                                                <th>Date</th>--}}
{{--                                                <th class="text-right">Amount</th>--}}
{{--                                            </tr>--}}
{{--                                            </thead>--}}
{{--                                            <tbody>--}}
{{--                                            @foreach($deposits as $row)--}}
{{--                                                <tr>--}}
{{--                                                    <td>--}}
{{--                                                        <i class="fa fa-user text-success mr-1"></i>--}}
{{--                                                        <a href="{{route('user.admin.wallet.list',$row->user_id)}}">--}}
{{--                                                            {{$row->author->name}}--}}
{{--                                                        </a>--}}
{{--                                                    </td>--}}
{{--                                                    <td><span class="badge badge-info">{{ $row->deposit_date }}</span></td>--}}
{{--                                                    <td class="text-right">--}}
{{--                                                        <span class="badge badge-success">{{ number_format($row->amount, 2) }}</span>--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                            @endforeach--}}
{{--                                            </tbody>--}}
{{--                                        </table>--}}

{{--                                        <!-- Pagination -->--}}
{{--                                        <div class="pagination-box">--}}
{{--                                            {{ $deposits->links('pagination::bootstrap-4') }}--}}
{{--                                        </div>--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <!-- Withdraw Box -->--}}
{{--                            <div class="col-md-6">--}}
{{--                                <div class="card shadow-sm mb-4">--}}
{{--                                    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">--}}
{{--                                        <div>--}}
{{--                                            <i class="fa fa-arrow-up mr-2"></i> Withdraw Logs--}}
{{--                                        </div>--}}
{{--                                        <span class="badge badge-light total-amount-badge">--}}
{{--                        {{ number_format($totalWithdraw, 2) }}--}}
{{--                    </span>--}}
{{--                                    </div>--}}

{{--                                    <div class="card-body p-0">--}}
{{--                                        <table class="table table-hover mb-0">--}}
{{--                                            <thead class="bg-light">--}}
{{--                                            <tr>--}}
{{--                                                <th>Name</th>--}}
{{--                                                <th>Date</th>--}}
{{--                                                <th class="text-right">Amount</th>--}}
{{--                                            </tr>--}}
{{--                                            </thead>--}}
{{--                                            <tbody>--}}
{{--                                            @foreach($withdraws as $row)--}}
{{--                                                <tr>--}}
{{--                                                    <td>--}}
{{--                                                        <i class="fa fa-user text-danger mr-1"></i>--}}
{{--                                                        <a href="{{route('user.admin.wallet.list',$row->user_id)}}">--}}
{{--                                                            {{$row->author->name}}--}}
{{--                                                        </a>--}}
{{--                                                    </td>--}}
{{--                                                    <td><span class="badge badge-warning">{{ $row->deposit_date }}</span></td>--}}
{{--                                                    <td class="text-right">--}}
{{--                                                        <span class="badge badge-danger">{{ number_format($row->amount, 2) }}</span>--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}
{{--                                            @endforeach--}}
{{--                                            </tbody>--}}
{{--                                        </table>--}}

{{--                                        <!-- Pagination -->--}}
{{--                                        <div class="pagination-box">--}}
{{--                                            {{ $withdraws->links('pagination::bootstrap-4') }}--}}
{{--                                        </div>--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}


{{--                    <!-- Right Sidebar Stats -->--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="card shadow-sm border-0">--}}
{{--                            <div class="card-header bg-primary text-white font-weight-bold">--}}
{{--                                <i class="fa fa-chart-bar"></i> {{ __("Detail Statistics") }}--}}
{{--                            </div>--}}

{{--                            @php--}}
{{--                                // Calculations--}}
{{--                                $totalTransactions = $alls->count();--}}
{{--                                $totalAmount = number_format($alls->sum('amount'), 2);--}}

{{--                                $pending = $alls->where('status', 'pending')->count();--}}
{{--                                $pendingAmount = number_format($alls->where('status','pending')->sum('amount'), 2);--}}

{{--                                $approved = $alls->where('status', 'confirmed')->count();--}}
{{--                                $approvedAmount = number_format($alls->where('status','confirmed')->sum('amount'), 2);--}}

{{--                                $depositCount = $depositAll->count();--}}
{{--                                $depositAmount = number_format($depositAll->sum('amount'), 2);--}}

{{--                                $withdrawCount = $withdrawAll->count();--}}
{{--                                $withdrawAmount = number_format($withdrawAll->sum('amount'), 2);--}}
{{--                            @endphp--}}

{{--                            <div class="card-body">--}}

{{--                                <ul class="list-group list-group-flush">--}}

{{--                                    <li class="list-group-item d-flex justify-content-between stat-item">--}}
{{--                                        <span>--}}
{{--                                            <i class="fa fa-exchange-alt text-secondary"></i>--}}
{{--                                            Total Transactions--}}
{{--                                            <span class="stat-count count-secondary">({{ $totalTransactions }})</span>--}}
{{--                                        </span>--}}
{{--                                        <span class="badge badge-secondary stat-badge">{{ number_format($alls->sum('amount'), 2) }}</span>--}}
{{--                                    </li>--}}


{{--                                    <li class="list-group-item d-flex justify-content-between stat-item">--}}
{{--                                        <span>--}}
{{--                                            <i class="fa fa-clock text-warning"></i>--}}
{{--                                            Pending--}}
{{--                                            <span class="stat-count count-warning">({{ $pending }})</span>--}}
{{--                                        </span>--}}
{{--                                        <span class="badge badge-warning stat-badge">{{ $pendingAmount }}</span>--}}
{{--                                    </li>--}}


{{--                                    <li class="list-group-item d-flex justify-content-between stat-item">--}}
{{--                                        <span>--}}
{{--                                            <i class="fa fa-check-circle text-success"></i>--}}
{{--                                            Approved--}}
{{--                                            <span class="stat-count count-success">({{ $approved }})</span>--}}
{{--                                        </span>--}}
{{--                                        <span class="badge badge-success stat-badge">{{ $approvedAmount }}</span>--}}
{{--                                    </li>--}}


{{--                                    <li class="list-group-item d-flex justify-content-between stat-item">--}}
{{--                                        <span>--}}
{{--                                            <i class="fa fa-arrow-down text-info"></i>--}}
{{--                                            Deposits--}}
{{--                                            <span class="stat-count count-info">({{ $depositCount }})</span>--}}
{{--                                        </span>--}}
{{--                                        <span class="badge badge-info stat-badge">{{ $depositAmount }}</span>--}}
{{--                                    </li>--}}


{{--                                    <li class="list-group-item d-flex justify-content-between stat-item">--}}
{{--                                        <span>--}}
{{--                                            <i class="fa fa-arrow-up text-danger"></i>--}}
{{--                                            Withdraws--}}
{{--                                            <span class="stat-count count-danger">({{ $withdrawCount }})</span>--}}
{{--                                        </span>--}}
{{--                                        <span class="badge badge-danger stat-badge">{{ $withdrawAmount }}</span>--}}
{{--                                    </li>--}}

{{--                                </ul>--}}

{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}


{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </div>--}}


{{--    <!-- Image Overlay -->--}}
{{--    <div id="imageOverlay" onclick="closeBigImage()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:99999; cursor:pointer;">--}}
{{--        <span style="position:absolute; top:20px; right:40px; color:white; font-size:40px; font-weight:bold;">&times;</span>--}}
{{--        <img id="bigImage" src="" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); max-width:90%; max-height:90%; border:3px solid white; box-shadow:0 0 30px rgba(0,0,0,0.8);">--}}
{{--    </div>--}}

{{--    <script>--}}
{{--        function showBigImage(imageSrc) {--}}
{{--            document.getElementById('bigImage').src = imageSrc;--}}
{{--            document.getElementById('imageOverlay').style.display = 'block';--}}
{{--        }--}}

{{--        function closeBigImage() {--}}
{{--            document.getElementById('imageOverlay').style.display = 'none';--}}
{{--        }--}}

{{--        // ESC key চাপলে close হবে--}}
{{--        document.addEventListener('keydown', function(e) {--}}
{{--            if (e.key === 'Escape') {--}}
{{--                closeBigImage();--}}
{{--            }--}}
{{--        });--}}
{{--    </script>--}}
{{--@endsection--}}
{{--<!-- JS -->--}}
{{--<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>--}}

{{--<script>--}}
{{--    $(function () {--}}

{{--        let savedFrom = "{{ request('from') }}";--}}
{{--        let savedTo = "{{ request('to') }}";--}}

{{--        // ONLY "To" pre-selected--}}
{{--        let defaultTo   = savedTo ? moment(savedTo) : moment();--}}
{{--        let defaultFrom = savedFrom ? moment(savedFrom) : null; // unselected--}}

{{--        $('#daterange').daterangepicker({--}}
{{--            singleDatePicker: false,--}}
{{--            showDropdowns: true,--}}
{{--            autoApply: false,--}}
{{--            maxDate: moment(),        // block future date--}}
{{--            startDate: defaultFrom ?? defaultTo,   // first selected = to date--}}
{{--            endDate: defaultTo,--}}
{{--            opens: "center",--}}
{{--            locale: {--}}
{{--                format: "YYYY-MM-DD",--}}
{{--                cancelLabel: 'Clear'--}}
{{--            }--}}
{{--        }, function(start, end){--}}
{{--            // update visible input--}}
{{--            $('#daterange').val(start.format("YYYY-MM-DD") + " - " + end.format("YYYY-MM-DD"));--}}

{{--            // hidden fields--}}
{{--            $('input[name="from"]').val(start.format("YYYY-MM-DD"));--}}
{{--            $('input[name="to"]').val(end.format("YYYY-MM-DD"));--}}
{{--        });--}}

{{--        // Show default text (only To date)--}}
{{--        if(defaultFrom){--}}
{{--            $('#daterange').val(defaultFrom.format("YYYY-MM-DD") + " - " + defaultTo.format("YYYY-MM-DD"));--}}
{{--        } else {--}}
{{--            $('#daterange').val(defaultTo.format("YYYY-MM-DD") + " - " + defaultTo.format("YYYY-MM-DD"));--}}
{{--        }--}}

{{--    });--}}
{{--</script>--}}


@extends('admin.layouts.app')

@section('content')

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <div class="p-4 bg-gray-50 min-h-screen">

        @include('admin.message')

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa fa-wallet text-blue-600"></i> Wallet Statistics
                </h2>
                <p class="text-sm text-gray-500 mt-1">Deposit &middot; Withdraw &middot; Summary</p>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fa fa-calendar-check text-blue-400 mr-1"></i>
                <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong>
                — <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">

            {{-- Total --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-gray-500 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Total</p>
                <p class="text-xl font-bold text-gray-700">&#2547;{{ number_format($stats['total_amount'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['total_count'] }} transactions</p>
            </div>

            {{-- Deposits --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Deposits</p>
                <p class="text-xl font-bold text-green-600">&#2547;{{ number_format($stats['deposit_amount'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['deposit_count'] }} entries</p>
            </div>

            {{-- Withdraws --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-500 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Withdraws</p>
                <p class="text-xl font-bold text-red-600">&#2547;{{ number_format($stats['withdraw_amount'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['withdraw_count'] }} entries</p>
            </div>

            {{-- Pending --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Pending</p>
                <p class="text-xl font-bold text-yellow-600">&#2547;{{ number_format($stats['pending_amount'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['pending_count'] }} entries</p>
            </div>

            {{-- Confirmed --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Confirmed</p>
                <p class="text-xl font-bold text-blue-600">&#2547;{{ number_format($stats['confirmed_amount'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['confirmed_count'] }} entries</p>
            </div>

        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form action="{{ route('report.admin.statistic.wallet_statistic') }}" method="GET" id="filterForm">
                <div class="flex flex-wrap gap-3 items-end">

                    {{-- From --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1"><i class="fa fa-calendar mr-1"></i>From</label>
                        <input type="date" name="start_date" id="startDate"
                               value="{{ $startDate }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    {{-- To --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">To</label>
                        <input type="date" name="end_date" id="endDate"
                               value="{{ $endDate }}"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    {{-- User --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">User</label>
                        <select name="user_id"
                                onchange="document.getElementById('filterForm').submit()"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 min-w-40">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                        <select name="status"
                                onchange="document.getElementById('filterForm').submit()"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">All Status</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fa fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('report.admin.statistic.wallet_statistic') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>

                    {{-- Quick shortcuts --}}
                    <div class="flex gap-1 ml-2 flex-wrap">
                        <button type="button" class="date-sc px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 hover:bg-gray-100 transition" data-range="today">Today</button>
                        <button type="button" class="date-sc px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 hover:bg-gray-100 transition" data-range="week">This Week</button>
                        <button type="button" class="date-sc px-3 py-2 rounded-lg text-xs font-semibold border {{ $dateRange==='this_month' ? 'border-blue-500 bg-blue-50 text-blue-600' : 'border-gray-300 hover:bg-gray-100' }} transition" data-range="month">This Month</button>
                        <button type="button" class="date-sc px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 hover:bg-gray-100 transition" data-range="last_month">Last Month</button>
                        <button type="button" class="date-sc px-3 py-2 rounded-lg text-xs font-semibold border border-gray-300 hover:bg-gray-100 transition" data-range="year">This Year</button>
                    </div>

                </div>
                <input type="hidden" name="date_range" id="hiddenRange" value="{{ $dateRange }}">
            </form>
        </div>

        {{-- TWO TABLES SIDE BY SIDE --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- DEPOSIT TABLE --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 bg-green-600 text-white">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa fa-arrow-down"></i> Deposit Logs
                        <span class="bg-white text-green-700 text-xs font-bold px-2 py-0.5 rounded-full ml-1">
                        {{ $deposits->count() }}
                    </span>
                    </h3>
                    <span class="text-sm font-semibold bg-green-700 px-3 py-1 rounded-full">
                    &#2547;{{ number_format($stats['deposit_amount'], 2) }}
                </span>
                </div>
                <div class="p-4">
                    <table id="depositTable" class="w-full text-sm" style="width:100%">
                        <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($deposits as $row)
                            <tr class="border-t border-gray-100 hover:bg-gray-50">
                                <td class="px-3 py-2.5 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('user.admin.wallet.list', $row->user_id) }}"
                                       class="font-semibold text-gray-800 hover:text-blue-600 transition">
                                        <i class="fa fa-user text-green-500 mr-1 text-xs"></i>
                                        {{ $row->author->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="bg-blue-50 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ $row->deposit_date }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @php
                                        $sc = match($row->status ?? '') {
                                            'confirmed' => 'bg-green-100 text-green-700',
                                            'pending'   => 'bg-yellow-100 text-yellow-700',
                                            default     => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc }}">{{ ucfirst($row->status ?? '—') }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                    &#2547;{{ number_format($row->amount, 2) }}
                                </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- WITHDRAW TABLE --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 bg-red-600 text-white">
                    <h3 class="font-bold text-base flex items-center gap-2">
                        <i class="fa fa-arrow-up"></i> Withdraw Logs
                        <span class="bg-white text-red-700 text-xs font-bold px-2 py-0.5 rounded-full ml-1">
                        {{ $withdraws->count() }}
                    </span>
                    </h3>
                    <span class="text-sm font-semibold bg-red-700 px-3 py-1 rounded-full">
                    &#2547;{{ number_format($stats['withdraw_amount'], 2) }}
                </span>
                </div>
                <div class="p-4">
                    <table id="withdrawTable" class="w-full text-sm" style="width:100%">
                        <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($withdraws as $row)
                            <tr class="border-t border-gray-100 hover:bg-gray-50">
                                <td class="px-3 py-2.5 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2.5">
                                    <a href="{{ route('user.admin.wallet.list', $row->user_id) }}"
                                       class="font-semibold text-gray-800 hover:text-blue-600 transition">
                                        <i class="fa fa-user text-red-500 mr-1 text-xs"></i>
                                        {{ $row->author->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="bg-yellow-50 text-yellow-700 text-xs px-2 py-0.5 rounded-full">{{ $row->deposit_date }}</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @php
                                        $sc = match($row->status ?? '') {
                                            'confirmed' => 'bg-green-100 text-green-700',
                                            'pending'   => 'bg-yellow-100 text-yellow-700',
                                            default     => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $sc }}">{{ ucfirst($row->status ?? '—') }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                    &#2547;{{ number_format($row->amount, 2) }}
                                </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function () {

            // ── Date shortcuts ────────────────────────────────────────────────────
            $('.date-sc').on('click', function () {
                var range = $(this).data('range');
                var today = new Date();
                var to    = today.toISOString().slice(0, 10);
                var from;

                if (range === 'today') {
                    from = to; $('#hiddenRange').val('today');
                } else if (range === 'week') {
                    var day = today.getDay() || 7;
                    var mon = new Date(today); mon.setDate(today.getDate() - day + 1);
                    from = mon.toISOString().slice(0, 10);
                    $('#hiddenRange').val('this_week');
                } else if (range === 'month') {
                    from = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';
                    $('#hiddenRange').val('this_month');
                } else if (range === 'last_month') {
                    var d  = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    var d2 = new Date(today.getFullYear(), today.getMonth(), 0);
                    from = d.toISOString().slice(0, 10);
                    to   = d2.toISOString().slice(0, 10);
                    $('#hiddenRange').val('last_month');
                } else if (range === 'year') {
                    from = today.getFullYear() + '-01-01';
                    $('#hiddenRange').val('this_year');
                }

                $('#startDate').val(from);
                $('#endDate').val(to);
                document.getElementById('filterForm').submit();
            });

            // ── DataTable config (shared) ─────────────────────────────────────────
            var dtConfig = {
                dom: '<"flex justify-between items-center mb-2"<"flex gap-1"B><"ml-auto"f>>' +
                    'tr' +
                    '<"flex justify-between items-center mt-2"<"text-xs text-gray-500"i><"text-xs"p>>',
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fa fa-file-excel mr-1"></i>Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'csvHtml5',   text: '<i class="fa fa-file-csv mr-1"></i>CSV',     className: 'btn btn-info btn-sm' },
                    { extend: 'pdfHtml5',   text: '<i class="fa fa-file-pdf mr-1"></i>PDF',     className: 'btn btn-danger btn-sm', orientation: 'landscape' },
                    { extend: 'print',      text: '<i class="fa fa-print mr-1"></i>Print',      className: 'btn btn-secondary btn-sm' },
                ],
                lengthMenu : [[10, 25, 50, -1], [10, 25, 50, 'All']],
                pageLength : 10,
                ordering   : true,
                searching  : true,
                paging     : true,
                info       : true,
                autoWidth  : false,
                language: {
                    search     : '',
                    searchPlaceholder: 'Search...',
                    lengthMenu : 'Show _MENU_',
                    paginate   : { previous: '‹', next: '›' }
                },
            };

            $('#depositTable').DataTable(dtConfig);
            $('#withdrawTable').DataTable(dtConfig);

        });
    </script>
@endpush
