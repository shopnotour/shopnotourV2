@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="dashboard-page">
            <h4 class="welcome-title text-uppercase">{{__('Welcome :name!',['name'=>Auth::user()->nameOrEmail])}}</h4>
        </div>
        <br>
        <div class="row">
            @if(!empty($top_cards))
                @foreach($top_cards as $card)
                    <div class="col-sm-{{$card['size']}} col-md-{{$card['size_md']}}">
                        <div class="dashboard-report-card card {{$card['class']}}">
                            <div class="card-content">
                                <span class="card-title">{{$card['title']}}</span>
                                <span class="card-amount">{{$card['amount']}}</span>
                                <span class="card-desc">{{$card['desc']}}</span>
                            </div>
                            <div class="card-media">
                                <i class="{{$card['icon']}}"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-6 mb-3">
                <div class="panel">
                    <div class="panel-title d-flex justify-content-between align-items-center">
                        <strong>{{__('Earning statistics')}}</strong>
                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
                            <i class="fa fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                        </div>
                    </div>
                    <div class="panel-body">
                        <canvas id="earning_chart"></canvas>
                        <script>
                            var earning_chart_data = {!! json_encode($earning_chart_data) !!};
                        </script>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <div class="panel">
                    <div class="panel-title d-flex justify-content-between">
                        <strong>{{__('Recent Bookings')}}</strong>
                        <a href="{{route('report.admin.booking')}}" class="btn-link">{{__("More")}}
                            <i class="icon ion-ios-arrow-forward"></i></a>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th width="60px">#</th>
                                    <th>{{__('Item')}}</th>
                                    <th width="100px">{{__("Total")}}</th>
                                    <th width="100px">{{__("Paid")}}</th>
                                    <th width="100px">{{__("Status")}}</th>
                                    <th width="100px">{{__("Created At")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($recent_bookings) > 0)
                                    @foreach($recent_bookings as $booking)
                                        <tr>
                                            <td>#{{$booking->id}}</td>
                                            <td>
                                                @if(get_bookable_service_by_id($booking->object_model) and $service = $booking->service)
                                                    <a href="{{$service->getDetailUrl()}}" target="_blank">{{$service->title}}</a>
                                                @else
                                                    {{__("[Deleted]")}}
                                                @endif
                                            </td>
                                            <td>{{format_money_main($booking->total)}}</td>
                                            <td>{{format_money_main($booking->paid)}}</td>
                                            <td>
                                                <span class="badge badge-{{$booking->status_class}}">{{$booking->status_name}}</span>
                                            </td>
                                            <td>{{display_datetime($booking->created_at)}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5">{{__("No data")}}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>

        {{-- ✅ Two Calendars --}}
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="panel">
                    <div class="panel-title d-flex justify-content-between align-items-center">
                        <strong>
                            <i class="fa fa-calendar" style="color:#534AB7;"></i>
                            {{ __('Booked Calendar') }}
                        </strong>
                        <span class="badge" style="background:#534AB7;color:#fff;">{{ __('By confirmed_at') }}</span>
                    </div>
                    <div class="panel-body" style="padding:12px;">
                        <div id="bookedCalendar"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="panel">
                    <div class="panel-title d-flex justify-content-between align-items-center">
                        <strong>
                            <i class="fa fa-calendar-check-o" style="color:#0F6E56;"></i>
                            {{ __('Issued Calendar') }}
                        </strong>
                        <span class="badge" style="background:#0F6E56;color:#fff;">{{ __('By ticket_issued_at') }}</span>
                    </div>
                    <div class="panel-body" style="padding:12px;">
                        <div id="issuedCalendar"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ PNR Dropdown --}}
        <div id="pnrDropdown"
             style="display:none;position:fixed;z-index:99999;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.15);min-width:240px;max-width:300px;overflow:hidden;">
            <div id="pnrDropdownHeader"
                 style="padding:10px 14px;font-size:13px;font-weight:500;color:#fff;display:flex;justify-content:space-between;align-items:center;">
                <span id="pnrDropdownDate"></span>
                <span id="pnrDropdownCount" style="font-size:11px;opacity:0.85;"></span>
            </div>
            <ul id="pnrList" style="list-style:none;margin:0;padding:4px 0;max-height:300px;overflow-y:auto;"></ul>
        </div>

        <div class="row"></div>
    </div>
@endsection

@push('js')
    <script src="{{url('libs/chart_js/Chart.min.js')}}"></script>
    <script src="{{url('libs/daterange/moment.min.js')}}"></script>

    <script>
        var bookedCalendarData = {!! json_encode($booked_calendar) !!};
        var issuedCalendarData = {!! json_encode($issued_calendar) !!};
        var bookingIndexUrl    = '{{ route("bookings.index") }}';

        // ✅ Global maps
        var calendarEntriesMap = {};
        // ✅ color টা data-attribute এ না রেখে JS map এ রাখছি — # sign issue fix
        var calendarColorMap   = {
            'bookedCalendar': '#534AB7',
            'issuedCalendar': '#0F6E56'
        };
    </script>

    <script>
        // ✅ Earning Chart
        var ctx = document.getElementById('earning_chart').getContext('2d');
        window.myMixedChart = new Chart(ctx, {
            type: 'bar',
            data: earning_chart_data,
            options: {
                responsive: true,
                tooltips: { mode: 'index', intersect: true },
                scales: {
                    xAxes: [{ stacked: true, display: true, scaleLabel: { display: true, labelString: '{{__("Timeline")}}' } }],
                    yAxes: [{ stacked: true, display: true, scaleLabel: { display: true, labelString: '{{__("Currency: :currency_main",["currency_main"=>setting_item("currency_main")])}}' }, ticks: { beginAtZero: true } }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (label) label += ': ';
                            label += tooltipItem.yLabel + " ({{setting_item('currency_main')}})";
                            return label;
                        }
                    }
                }
            }
        });
        var start = moment().startOf('week');
        var end   = moment();
        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('#reportrange').daterangepicker({
            startDate: start, endDate: end,
            alwaysShowCalendars: true, opens: 'left', showDropdowns: true,
            ranges: {
                '{{__("Today")}}': [moment(), moment()],
                '{{__("Yesterday")}}': [moment().subtract(1,'days'), moment().subtract(1,'days')],
                '{{__("Last 7 Days")}}': [moment().subtract(6,'days'), moment()],
                '{{__("Last 30 Days")}}': [moment().subtract(29,'days'), moment()],
                '{{__("This Month")}}': [moment().startOf('month'), moment().endOf('month')],
                '{{__("Last Month")}}': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
                '{{__("This Year")}}': [moment().startOf('year'), moment().endOf('year')],
                '{{__("This Week")}}': [moment().startOf('week'), end]
            }
        }, cb).on('apply.daterangepicker', function (ev, picker) {
            $.ajax({
                url: '{{route("report.admin.statistic.reloadChart")}}',
                data: { chart: 'earning', from: picker.startDate.format('YYYY-MM-DD'), to: picker.endDate.format('YYYY-MM-DD') },
                dataType: 'json', type: 'post',
                success: function (res) {
                    if (res.status) { window.myMixedChart.data = res.chart_data; window.myMixedChart.update(); }
                }
            });
        });
        cb(start, end);
    </script>

    <script>
        function buildCalendar(containerId, calendarData, accentColor) {
            var today        = new Date();
            var currentYear  = today.getFullYear();
            var currentMonth = today.getMonth();

            // ✅ Entries global map এ store
            Object.keys(calendarData).forEach(function(dateKey) {
                calendarEntriesMap[containerId + '_' + dateKey] = calendarData[dateKey];
            });

            function pad(n) { return n < 10 ? '0' + n : '' + n; }

            function renderCalendar(year, month) {
                var container   = document.getElementById(containerId);
                var firstDay    = new Date(year, month, 1).getDay();
                var daysInMonth = new Date(year, month + 1, 0).getDate();
                var monthNames  = ['January','February','March','April','May','June','July','August','September','October','November','December'];

                var html = '<div>';

                // Header
                html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">';
                html += '<button class="cal-nav-btn" data-cal="' + containerId + '" data-dir="-1" style="background:' + accentColor + ';color:#fff;border:none;border-radius:6px;width:30px;height:30px;cursor:pointer;font-size:18px;line-height:1;">&#8249;</button>';
                html += '<strong style="font-size:15px;">' + monthNames[month] + ' ' + year + '</strong>';
                html += '<button class="cal-nav-btn" data-cal="' + containerId + '" data-dir="1" style="background:' + accentColor + ';color:#fff;border:none;border-radius:6px;width:30px;height:30px;cursor:pointer;font-size:18px;line-height:1;">&#8250;</button>';
                html += '</div>';

                // Day headers
                var dayLabels = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:4px;">';
                dayLabels.forEach(function(d) {
                    html += '<div style="text-align:center;font-size:11px;font-weight:500;color:#888;padding:3px 0;">' + d + '</div>';
                });
                html += '</div>';

                // Date cells
                html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;">';

                for (var i = 0; i < firstDay; i++) {
                    html += '<div style="min-height:58px;"></div>';
                }

                for (var day = 1; day <= daysInMonth; day++) {
                    var dateKey = year + '-' + pad(month + 1) + '-' + pad(day);
                    var mapKey  = containerId + '_' + dateKey;
                    var entries = calendarData[dateKey] || [];
                    var count   = entries.length;
                    var isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());

                    var cellStyle = 'min-height:58px;border-radius:6px;border:1px solid #eee;padding:4px;position:relative;';
                    if (isToday)   cellStyle += 'border-color:' + accentColor + ';background:#f8f7ff;';
                    if (count > 0) cellStyle += 'cursor:pointer;';

                    html += '<div style="' + cellStyle + '"'
                        // ✅ data-color বাদ, শুধু data-cal আর data-mapkey রাখছি
                        + (count > 0 ? ' class="cal-day-cell" data-cal="' + containerId + '" data-mapkey="' + mapKey + '" data-date="' + dateKey + '"' : '')
                        + '>';

                    html += '<div style="font-size:11px;font-weight:' + (isToday ? '700' : '400') + ';color:' + (isToday ? accentColor : '#555') + ';margin-bottom:3px;">' + day + '</div>';

                    if (count > 0) {
                        var firstPnr = entries[0].pnr_id || ('#' + entries[0].id);
                        html += '<div style="background:' + accentColor + ';color:#fff;font-size:9px;padding:2px 4px;border-radius:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + firstPnr + '</div>';
                        if (count > 1) {
                            html += '<div style="font-size:9px;color:' + accentColor + ';font-weight:600;margin-top:2px;">+' + (count - 1) + ' more</div>';
                        }
                    }

                    html += '</div>';
                }

                html += '</div></div>';
                container.innerHTML = html;
            }

            window['calNav_' + containerId] = function(dir) {
                currentMonth += dir;
                if (currentMonth > 11) { currentMonth = 0; currentYear++; }
                if (currentMonth < 0)  { currentMonth = 11; currentYear--; }
                renderCalendar(currentYear, currentMonth);
            };

            renderCalendar(currentYear, currentMonth);
        }

        // ✅ PNR Dropdown
        function showPnrDropdown(dateKey, accentColor, entries, cellEl) {
            var dropdown = document.getElementById('pnrDropdown');

            document.getElementById('pnrDropdownDate').textContent  = dateKey;
            document.getElementById('pnrDropdownCount').textContent = entries.length + ' booking(s)';
            document.getElementById('pnrDropdownHeader').style.background = accentColor;

            var list = document.getElementById('pnrList');
            list.innerHTML = '';

            entries.forEach(function(entry) {
                var pnr = entry.pnr_id || ('Booking #' + entry.id);
                var li  = document.createElement('li');
                li.style.borderBottom = '1px solid #f5f5f5';

                var a = document.createElement('a');
                a.href = bookingIndexUrl + '?s=' + encodeURIComponent(pnr);
                a.style.cssText = 'display:flex;align-items:center;padding:9px 14px;color:#333;text-decoration:none;font-size:13px;';
                a.innerHTML = '<i class="fa fa-ticket" style="color:' + accentColor + ';margin-right:8px;font-size:12px;"></i>' + pnr;
                a.onmouseover = function() { this.style.background = '#f8f7ff'; };
                a.onmouseout  = function() { this.style.background = ''; };

                li.appendChild(a);
                list.appendChild(li);
            });

            var rect    = cellEl.getBoundingClientRect();
            var dropW   = 260;
            var leftPos = rect.left;
            if (leftPos + dropW > window.innerWidth) leftPos = window.innerWidth - dropW - 10;
            if (leftPos < 0) leftPos = 4;

            // ✅ fixed position use করছি তাই viewport relative — scrollY লাগবে না
            dropdown.style.left    = leftPos + 'px';
            dropdown.style.top     = (rect.bottom + 4) + 'px';
            dropdown.style.display = 'block';
        }

        // ✅ jQuery event delegation
        $(document).on('click', '.cal-day-cell', function(e) {
            e.stopPropagation();
            var calId   = $(this).data('cal');       // 'bookedCalendar' or 'issuedCalendar'
            var mapKey  = $(this).data('mapkey');
            var dateKey = $(this).data('date');
            var color   = calendarColorMap[calId];   // ✅ JS map থেকে color নেওয়া হচ্ছে
            var entries = calendarEntriesMap[mapKey] || [];
            showPnrDropdown(dateKey, color, entries, this);
        });

        $(document).on('click', '.cal-nav-btn', function(e) {
            e.stopPropagation();
            var calId = $(this).data('cal');
            var dir   = parseInt($(this).data('dir'));
            window['calNav_' + calId](dir);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#pnrDropdown').length) {
                $('#pnrDropdown').hide();
            }
        });

        // ✅ Init
        $(document).ready(function() {
            buildCalendar('bookedCalendar', bookedCalendarData, '#534AB7');
            buildCalendar('issuedCalendar', issuedCalendarData, '#0F6E56');
        });
    </script>
@endpush
