{{--<div class="bravo-list-item">--}}
{{--    <div class="py-10 px-30 bg-white rounded-4 base-tr mt-0 item-loop-wrap inner-loop-wrap">--}}
{{--        <table width="100%">--}}
{{--            <tr>--}}
{{--                <td align="left">--}}
{{--                <div class="col-auto">--}}
{{--                    <div class="text-18">--}}
{{--                        <span class="fw-500 result-count">--}}
{{--                            @if($total_results > 1)--}}
{{--                                {{ __(":count flights available",['count'=>$total_results]) }}--}}
{{--                            @else--}}
{{--                                {{ __(":count flight available",['count'=>$total_results]) }}--}}
{{--                            @endif--}}

{{--                        </span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                </td>--}}
{{--                <td align="right">--}}
{{--                <div class="col-auto bc-form-order" style="float:right;">--}}
{{--                    @include('Layout::global.search.orderby',['routeName'=>'flight.search','hidden_map_button'=>1])--}}
{{--                </div>--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--        </table>--}}



{{--    </div>--}}
{{--    <!--<div class="py-30 px-30 bg-white rounded-4 base-tr mt-30 item-loop-wrap inner-loop-wrap">--}}
{{--        <div class="col-auto">--}}
{{--            <div class="text-18">--}}
{{--                <span class="fw-500">--}}
{{--                    Airlines List with logo, name, price--}}
{{--                </span>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>-->--}}

{{--    <div class="ajax-search-result" id="flightFormBook">--}}
{{--        @include('Flight::frontend.ajax.search-result')--}}
{{--    </div>--}}

{{--</div>--}}


<div class="bravo-list-item">
    <div class="py-10 px-30 bg-white rounded-4 base-tr mt-0 item-loop-wrap inner-loop-wrap">
        <table width="100%">
            <tr>
                <td align="left">
                    <div class="col-auto">
                        <div class="text-18">
                        <span class="fw-500 result-count">
                            @if($rows->meta->count > 1)
                                {{ __(":count flights available",['count'=>$rows->meta->count]) }}
                            @else
                                {{ __(":count flight available",['count'=>$rows->meta->count]) }}
                            @endif

                        </span>
                        </div>
                    </div>
                </td>
                <td align="right">
                    <div class="col-auto bc-form-order" style="float:right;">
                        @include('Layout::global.search.orderby',['routeName'=>'flight.search','hidden_map_button'=>1])
                    </div>
                </td>
            </tr>
        </table>



    </div>
    <!--<div class="py-30 px-30 bg-white rounded-4 base-tr mt-30 item-loop-wrap inner-loop-wrap">
        <div class="col-auto">
            <div class="text-18">
                <span class="fw-500">
                    Airlines List with logo, name, price
                </span>
            </div>
        </div>
    </div>-->

    <div class="ajax-search-result" id="flightFormBook">
        @include('Flight::frontend.ajax.search-result')
    </div>

</div>
