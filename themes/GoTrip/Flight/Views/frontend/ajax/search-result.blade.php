
{{--<div class="row">--}}
{{--    @if($flights)--}}

{{--        @if($total_results > 0)--}}
{{--            @foreach($flights as $row)--}}
{{--                <div class="col-lg-12">--}}
{{--                    @include('Flight::frontend.layouts.search.custom_loop',['wrap_class'=>'item-loop-wrap inner-loop-wrap'])--}}
{{--                    @include('Flight::frontend.layouts.search.loop-grid',['wrap_class'=>'item-loop-wrap inner-loop-wrap'])--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        @endif--}}
{{--    @else--}}
{{--        <div class="col-lg-12">--}}
{{--            {{__("Flight not found")}}--}}
{{--        </div>--}}
{{--    @endif--}}
{{--</div>--}}

{{--<div class="bravo-pagination">--}}
{{--    @if($flights)--}}
{{--        @if($total_results > 0)--}}
{{--            <div class="text-center mt-30 md:mt-10">--}}
{{--                <div class="text-14 text-light-1">{{ __("Showing :from - :to of :total flights",["from"=>1,"to"=>$total_results,"total"=>$total_results]) }}</div>--}}
{{--            </div>--}}
{{--        @endif--}}
{{--    @endif--}}
{{--</div>--}}

<div class="row">
    @if($rows)
        @if($rows->meta->count > 0)
            @foreach($rows->data as $row)
                <div class="col-lg-12">
                    @include('Flight::frontend.layouts.search.loop-grid',['wrap_class'=>'item-loop-wrap inner-loop-wrap'])
                </div>
            @endforeach
        @endif
    @else
        <div class="col-lg-12">
            {{__("Flight not found")}}
        </div>
    @endif
</div>

<div class="bravo-pagination">
    @if($rows)
        @if($rows->meta->count > 0)
            <div class="text-center mt-30 md:mt-10">
                <div class="text-14 text-light-1">{{ __("Showing :from - :to of :total flights",["from"=>1,"to"=>$rows->meta->count,"total"=>$rows->meta->count]) }}</div>
            </div>
        @endif
    @endif
</div>
