<?php $location_name = ""; $location_id = ''; $list_json = [];
$traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json , &$location_name, &$location_id) {
    foreach ($locations as $location) {
        $translate = $location->translate();
        if (Request::query('location_id') == $location->id){
            $location_name = $translate->name;
            $location_id = $location->id;
        }
        $list_json[] = [
            'id' => $location->id,
            'title' => $prefix . ' ' . $translate->name,
        ];
        $traverse($location->children, $prefix . '-');
    }
};
$traverse($list_location ?? $tour_location);
if (empty($inputName)){
    $inputName = 'location_id';
}

//echo '<pre>'; print_r($request[str_replace('_where','',$inputName)]); die();
//die($search_style);
//$search_style = 'autocompletePlace';
$type = $search_style ?? "normal";
?>
@if($type=='autocompletePlace')
    <div class="searchMenu-loc item">
        <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
        <div data-x-dd-click="searchMenu-loc">
            <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
            <div class="text-15 text-light-1 ls-2 lh-16 g-map-place">
                <input type="text" name="map_place" placeholder="{{__('Where are you going?')}}"  value="{{request()->input('map_place')}}" class="border-0">
                <div class="map d-none" id="map-{{\Illuminate\Support\Str::random(10)}}"></div>
                <input type="hidden" name="map_lat" value="{{request()->input('map_lat')}}">
                <input type="hidden" name="map_lgn" value="{{request()->input('map_lgn')}}">
            </div>
        </div>
    </div>
@else
    <div class="searchMenu-loc js-form-dd js-liverSearch item">
        <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
        <div data-x-dd-click="searchMenu-loc">
            <h4 class="text-15 fw-500 ls-2 lh-16">{{ $field['title'] }}</h4>
            <div class="text-15 text-light-1 ls-2 lh-16  smart-search   ">
                <input type="hidden" name="{{$inputName}}" class="js-search-get-id child_id" value="{{ $request[str_replace('_where','',$inputName)] ?? '' }}">
                <input type="text" autocomplete="on"  class="smart-search-location smart-search-{{$service_type}} parent_text js-search js-dd-focus" placeholder="{{__('Search')}}" value="{{ $request[str_replace('_where','_airport',$inputName)] ?? '' }}" data-onLoad="{{__('Loading...')}}" data-default="" required>
            </div>
        </div>
        <div class="searchMenu-loc__field shadow-2 js-popup-window  d-none " data-x-dd="searchMenu-loc" data-x-dd-toggle="-is-active">
            <div class="bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4">
                <div class="y-gap-5 js-results">

                </div>
            </div>
        </div>
    </div>
@endif
