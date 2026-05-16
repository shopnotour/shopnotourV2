{{--<form action="{{route('sslpay')}}" method="post">--}}
{{--@csrf--}}
{{--<div class="form-checkout" id="form-checkout" >--}}
{{--    <input type="hidden" name="code" value="{{$booking->code}}">--}}
{{--    <div class="form-section">--}}
{{--        <div class="row x-gap-20 y-gap-20 pt-20">--}}

{{--            @if(is_enable_guest_checkout() && is_enable_registration())--}}
{{--                <div class="col-12">--}}
{{--                    <div class="">--}}
{{--                        <label for="confirmRegister" class="flex ">--}}
{{--                            <input style="width: auto" type="checkbox" name="confirmRegister" id="confirmRegister" value="1">--}}
{{--                            {{__('Create a new account?')}}--}}
{{--                        </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--            @if(is_enable_guest_checkout())--}}
{{--                <div class="col-12 d-none" id="confirmRegisterContent">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-6" >--}}
{{--                            <div class="form-input ">--}}
{{--                                <input type="password" class="form-control" name="password" autocomplete="off" >--}}
{{--                                <label class="lh-1 text-16 text-light-1" >{{__("Password")}} <span class="required">*</span></label>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6">--}}
{{--                            <div class="form-input ">--}}
{{--                                <input type="password" class="form-control" name="password_confirmation" autocomplete="off">--}}
{{--                                <label class="lh-1 text-16 text-light-1" >{{__('Password confirmation')}} <span class="required">*</span></label>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <hr>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--            <div class="col-md-6">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text"  class="form-control" value="{{$user->first_name ?? ''}}" name="first_name">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("First Name")}} <span class="required">*</span></label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text"  class="form-control" value="{{$user->last_name ?? ''}}" name="last_name">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Last Name")}} <span class="required">*</span></label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6 field-email">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="email"  class="form-control" value="{{$user->email ?? ''}}" name="email">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Email")}} <span class="required">*</span></label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text"  class="form-control" value="{{$user->phone ?? ''}}" name="phone">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Phone")}} <span class="required">*</span></label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <!--<div class="col-md-6 field-address-line-1">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text"  class="form-control" value="{{$user->address ?? ''}}" name="address_line_1">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Address line 1")}} </label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6 field-address-line-2">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text"  class="form-control" value="{{$user->address2 ?? ''}}" name="address_line_2">--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Address line 2")}} </label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6 field-city">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text" class="form-control" value="{{$user->city ?? ''}}" name="city" >--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("City")}} </label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6 field-state">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text" class="form-control" value="{{$user->state ?? ''}}" name="state" >--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("State/Province/Region")}} </label>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-md-6 field-zip-code">--}}
{{--                <div class="form-input ">--}}
{{--                    <input type="text" class="form-control" value="{{$user->zip_code ?? ''}}" name="zip_code" >--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("ZIP code/Postal code")}} </label>--}}
{{--                </div>--}}
{{--            </div>-->--}}
{{--                <div class="col-md-6 field-country">--}}
{{--                    <div class="form-input ">--}}
{{--                        <select name="country" class="form-control">--}}
{{--                            <option value="">{{__('-- Select --')}}</option>--}}
{{--                            @foreach(get_country_lists() as $id=>$name)--}}
{{--                                <option @if(($user->country ?? '') == $id) selected @endif value="{{$id}}">{{$name}}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                        <label class="lh-1 text-16 text-light-1" >{{__("Nationality")}} <span class="required">*</span> </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-md-6">--}}
{{--                    <div class="form-input">--}}
{{--                        <textarea name="customer_notes" cols="30" rows="2" class="form-control" ></textarea>--}}
{{--                        <label class="lh-1 text-16 text-light-1" >{{__("Special Requirements")}} </label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--Passengers section start--}}
{{--        <?php //echo '<pre>'; print_r($booking->pessengers); die(); ?>--}}

{{--        @if($totalPassenger = $booking->calTotalPassenger())--}}
{{--                <?php $old_data = old('passengers', []) ?>--}}
{{--            <div class="form-section mt-40">--}}
{{--                <h3 class="text-22 fw-500 mb-20">{{__("Traveler Details:")}}</h3>--}}
{{--                <div class="accordion -simple row y-gap-20 pt-30 js-accordion" id="passengers_info">--}}
{{--                    @for($i = 1 ; $i <= count($booking->pessengers) ; $i ++)--}}
{{--                            <?php--}}
{{--                            $old_item = $old_data[$i] ?? [];--}}
{{--                            $pid = $booking->pessengers[$i-1]->id;--}}
{{--                            ?>--}}

{{--                        <input type="hidden"  class="form-control"--}}
{{--                               value="{{$pid}}"--}}
{{--                               name="passengers[{{$i}}][id]">--}}
{{--                        <div class="col-12 accordion__item px-20 py-20 border-light rounded-4">--}}
{{--                            <div class="accordion__button d-flex items-center" id="passenger_heading_{{$i}}">--}}
{{--                                <div class="accordion__icon size-40 flex-center bg-light-2 rounded-full mr-20">--}}
{{--                                    <i class="icon-plus"></i>--}}
{{--                                    <i class="icon-minus"></i>--}}
{{--                                </div>--}}
{{--                                <button class="button text-dark-1" data-toggle="collapse" data-target="#passenger_{{$i}}" aria-expanded="true"--}}
{{--                                        aria-controls="passenger_{{$i}}">--}}
{{--                                    {{__("Traveler #:number",['number'=>$i])}}:--}}
{{--                                </button>--}}
{{--                            </div>--}}

{{--                            <div id="passenger_{{$i}}" class="accordion__content @if($i == 1) show @endif"--}}
{{--                                 aria-labelledby="passenger_heading_{{$i}}" data-parent="#passengers_info">--}}
{{--                                <div class="pt-20 pl-60">--}}
{{--                                    <div class="row y-gap-20">--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-input">--}}
{{--                                                <input type="text"  class="form-control"--}}
{{--                                                       value="{{$old_item['first_name'] ?? ''}}"--}}
{{--                                                       name="passengers[{{$i}}][first_name]">--}}
{{--                                                <label class="lh-1 text-16 text-light-1">{{__("First Name")}} </label>--}}

{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-input">--}}
{{--                                                <input type="text"  class="form-control"--}}
{{--                                                       value="{{$old_item['last_name'] ?? ''}}"--}}
{{--                                                       name="passengers[{{$i}}][last_name]">--}}
{{--                                                <label class="lh-1 text-16 text-light-1">{{__("Last Name")}}</label>--}}

{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <!--<div class="col-md-6 field-email ">--}}
{{--                                    <div class="form-input">--}}
{{--                                        <input type="email"--}}
{{--                                               class="form-control" value="{{$old_item['email'] ?? ''}}"--}}
{{--                                               name="passengers[{{$i}}][email]">--}}
{{--                                        <label class="lh-1 text-16 text-light-1">{{__("Email")}} </label>--}}

{{--                                    </div>--}}
{{--                                </div>-->--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-input">--}}
{{--                                                <input type="text"  class="form-control"--}}
{{--                                                       value="{{$old_item['phone'] ?? ''}}" name="passengers[{{$i}}][phone]">--}}
{{--                                                <label class="lh-1 text-16 text-light-1">{{__("Phone")}} </label>--}}

{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-6 field-country">--}}
{{--                                            <div class="form-input ">--}}
{{--                                                <select name="passengers[{{$i}}][country]" class="form-control">--}}
{{--                                                    <option value="BD">Bangladesh</option>--}}
{{--                                                    @foreach(get_country_lists() as $id=>$name)--}}
{{--                                                        <option @if(($user->country ?? '') == $id) selected @endif value="{{$id}}">{{$name}}</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
{{--                                                <label class="lh-1 text-16 text-light-1" >{{__("Nationality")}} <span class="required">*</span> </label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-6 field-zip-code">--}}
{{--                                            <div class="form-input ">--}}
{{--                                                <input type="text" class="form-control" value="{{$old_item['zip_code'] ?? ''}}" name="passengers[{{$i}}][zip_code]" >--}}
{{--                                                <label class="lh-1 text-16 text-light-1" >{{__("ZIP code/Postal code")}} </label>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endfor--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        --}}{{--Passengers section end--}}
{{--        @include ('Booking::frontend/booking/checkout-passengers')--}}
{{--        checkout deposit section start --}}
{{--        @if(floatval($booking->deposit) and in_array($booking->status,['draft','unpaid']))--}}
{{--                <?php--}}
{{--                $oldDeposit = $booking->getMeta('old_deposit');--}}
{{--                $deposit = $booking->deposit;--}}
{{--                if (empty(floatval($deposit))){--}}
{{--                    $deposit  = !empty($oldDeposit)?floatval($oldDeposit):0;--}}
{{--                }--}}
{{--                ;?>--}}
{{--            <hr>--}}
{{--            <div class="form-section">--}}
{{--                <h4 class="form-section-title">{{__("How do you want to pay?")}}</h4>--}}
{{--                <div class="deposit_types gateways-table accordion ">--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-header">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <h4 class="mb-0"><label ><input type="radio" checked name="how_to_pay" value="deposit">--}}
{{--                                        {{__("Pay deposit")}}--}}
{{--                                    </label></h4>--}}
{{--                                <span class="price"><strong>{{format_money($deposit)}}</strong></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="card">--}}
{{--                        <div class="card-header">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <h4 class="mb-0"><label ><input type="radio"  name="how_to_pay" value="full">--}}
{{--                                        {{__("Pay in full")}}--}}
{{--                                    </label></h4>--}}
{{--                                <span class="price"><strong>{{format_money($booking->total)}}</strong></span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endif--}}

{{--        --}}{{--        checkout deposit section end --}}
{{--        @include ('Booking::frontend/booking/checkout-deposit')--}}
{{--        checkout-payment start--}}
{{--        @if(isset($service->checkout_form_payment_file ))--}}
{{--            <div class="form-section mt-40">--}}
{{--                <h3 class="text-22 fw-500 mb-20">{{__("How do you want to pay?")}}</h3>--}}
{{--                <div class="accordion -simple row y-gap-20 pt-30 js-accordion">--}}
{{--                    <div class="col-12 accordion__item px-20">--}}
{{--                        <div class=" border-light rounded-4 py-20">--}}
{{--                            <div class="accordion__button d-flex items-center">--}}
{{--                                <button class="button text-dark-1 shrink-0  px-20 " type="button">--}}
{{--                                    <label class="d-flex items-center" data-toggle="collapse" data-target="" >--}}
{{--                                        <input type="radio" name="payment_gateway" value="ssl">--}}
{{--                                        <span class="shrink-0 ml-20">SSL</span>--}}
{{--                                    </label>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    @foreach($gateways as $k=>$gateway)--}}
{{--                        <div class="col-12 accordion__item px-20">--}}
{{--                            <div class=" border-light rounded-4 py-20">--}}
{{--                                <div class="accordion__button d-flex items-center">--}}
{{--                                    <button class="button text-dark-1 shrink-0  px-20 " type="button">--}}
{{--                                        <label class="d-flex items-center" data-toggle="collapse" data-target="#gateway_{{$k}}" >--}}
{{--                                            <input type="radio" name="payment_gateway" value="{{$k}}">--}}
{{--                                            @if($logo = $gateway->getDisplayLogo())--}}
{{--                                                <img src="{{$logo}}" alt="{{$gateway->getDisplayName()}}">--}}
{{--                                            @endif--}}
{{--                                            <span class="shrink-0 ml-20">{{$gateway->getDisplayName()}}</span>--}}

{{--                                        </label>--}}
{{--                                    </button>--}}

{{--                                </div>--}}
{{--                                <div id="gateway_{{$k}}" class="accordion__content" aria-labelledby="headingOne" data-parent="#accordionExample">--}}
{{--                                    <div class="pt-20 pl-60">--}}
{{--                                        <div class="gateway_name">--}}
{{--                                            {!! $gateway->getDisplayName() !!}--}}
{{--                                        </div>--}}
{{--                                        {!! $gateway->getDisplayHtml() !!}--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        @endif--}}

{{--        checkout-payment end--}}
{{--        @include ($service->checkout_form_payment_file ?? 'Booking::frontend/booking/checkout-payment')--}}

{{--        @php--}}
{{--            $term_conditions = setting_item('booking_term_conditions');--}}
{{--        @endphp--}}

{{--        @if(setting_item("booking_enable_recaptcha"))--}}
{{--            <div class="form-input ">--}}
{{--                {{recaptcha_field('booking')}}--}}
{{--            </div>--}}
{{--        @endif--}}
{{--        <div class="html_before_actions"></div>--}}

{{--        <p class="alert-text mt10" v-show=" message.content" v-html="message.content" :class="{'danger':!message.type,'success':message.type}"></p>--}}

{{--        <div class="row y-gap-20 items-center justify-between mb-40">--}}
{{--            <div class="col-auto">--}}
{{--                <div class="d-flex items-center">--}}
{{--                    <div class="form-checkbox ">--}}
{{--                        <input type="checkbox" name="term_conditions">--}}
{{--                        <div class="form-checkbox__mark">--}}
{{--                            <div class="form-checkbox__icon icon-check"></div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="text-14 lh-10 text-light-1 ml-10">{{__('I have read and accept the')}}--}}
{{--                        <a target="_blank" href="{{get_page_url($term_conditions)}}">{{__('terms and conditions')}}</a></div>--}}

{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="col-auto">--}}
{{--                            <button class="button h-60 px-24 -dark-1 bg-blue-1 text-white" @click="doCheckout">--}}
{{--                                {{__('Book Now')}}--}}
{{--                                <div class="icon-arrow-top-right ml-15"></div>--}}
{{--                                <i class="fa fa-spin fa-spinner" v-show="onSubmit"></i>--}}
{{--                            </button>--}}

{{--                <button type="submit" class="button h-60 px-24 -dark-1 bg-blue-1 text-white">--}}
{{--                {{__('Book Now')}}--}}
{{--                <div class="icon-arrow-top-right ml-15"></div>--}}
{{--                <i class="fa fa-spin fa-spinner" ></i>--}}
{{--                </button>--}}

{{--                <input  type="submit" value="Book now" >--}}

{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</form>--}}
{{--<form action="{{route('sslpay')}}" method="post">--}}
@csrf
<div class="form-checkout" id="form-checkout" >
    <input type="hidden" name="code" value="{{$booking->code}}">
    <div class="form-section">
        <div class="row x-gap-20 y-gap-20 pt-20">

            @if(is_enable_guest_checkout() && is_enable_registration())
                <div class="col-12">
                    <div class="">
                        <label for="confirmRegister" class="flex ">
                            <input style="width: auto" type="checkbox" name="confirmRegister" id="confirmRegister" value="1">
                            {{__('Create a new account?')}}
                        </label>
                    </div>
                </div>
            @endif
            @if(is_enable_guest_checkout())
                <div class="col-12 d-none" id="confirmRegisterContent">
                    <div class="row">
                        <div class="col-md-6" >
                            <div class="form-input ">
                                <input type="password" class="form-control" name="password" autocomplete="off" >
                                <label class="lh-1 text-16 text-light-1" >{{__("Password")}} <span class="required">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-input ">
                                <input type="password" class="form-control" name="password_confirmation" autocomplete="off">
                                <label class="lh-1 text-16 text-light-1" >{{__('Password confirmation')}} <span class="required">*</span></label>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            @endif
            <div class="col-md-6">
                <div class="form-input ">
                    <input  type="hidden"   class="form-control" value="{{$user->first_name ?? ''}}" name="first_name">
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("First Name")}} <span class="required">*</span></label>--}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-input ">
                    <input type="hidden"    class="form-control" value="{{$user->last_name ?? ''}}" name="last_name">
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Last Name")}} <span class="required">*</span></label>--}}
                </div>
            </div>
            <div class="col-md-6 field-email">
                <div class="form-input ">
                    <input type="hidden"    class="form-control" value="{{$user->email ?? ''}}" name="email">
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Email")}} <span class="required">*</span></label>--}}

                </div>
            </div>
            <div class="col-md-6">
                <div class="form-input ">
                    <input type="hidden"   class="form-control" value="{{$user->phone ?? ''}}" name="phone">
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Phone")}} <span class="required">*</span></label>--}}

                </div>
            </div>
            <!--<div class="col-md-6 field-address-line-1">
                <div class="form-input ">
                    <input type="text"  class="form-control" value="{{$user->address ?? ''}}" name="address_line_1">
                    <label class="lh-1 text-16 text-light-1" >{{__("Address line 1")}} </label>

                </div>
            </div>
            <div class="col-md-6 field-address-line-2">
                <div class="form-input ">
                    <input type="text"  class="form-control" value="{{$user->address2 ?? ''}}" name="address_line_2">
                    <label class="lh-1 text-16 text-light-1" >{{__("Address line 2")}} </label>

                </div>
            </div>
            <div class="col-md-6 field-city">
                <div class="form-input ">
                    <input type="text" class="form-control" value="{{$user->city ?? ''}}" name="city" >
                    <label class="lh-1 text-16 text-light-1" >{{__("City")}} </label>

                </div>
            </div>
            <div class="col-md-6 field-state">
                <div class="form-input ">
                    <input type="text" class="form-control" value="{{$user->state ?? ''}}" name="state" >
                    <label class="lh-1 text-16 text-light-1" >{{__("State/Province/Region")}} </label>

                </div>
            </div>
            <div class="col-md-6 field-zip-code">
                <div class="form-input ">
                    <input type="text" class="form-control" value="{{$user->zip_code ?? ''}}" name="zip_code" >
                    <label class="lh-1 text-16 text-light-1" >{{__("ZIP code/Postal code")}} </label>
                </div>
            </div>-->
            <div class="col-md-6 field-country">
                <div class="form-input " hidden="hidden">
                    <select type="hidden"   name="country" class="form-control">
                        <option value="">{{__('-- Select --')}}</option>
                        @foreach(get_country_lists() as $id=>$name)
                            <option @if(($user->country ?? '') == $id) selected @endif value="{{$id}}">{{$name}}</option>
                        @endforeach
                    </select>
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Nationality")}} <span class="required">*</span> </label>--}}
                </div>
            </div>
{{--            <div class="col-md-6">--}}
{{--                <div class="form-input">--}}
{{--                    <textarea name="customer_notes" cols="30" rows="2" class="form-control" ></textarea>--}}
{{--                    <label class="lh-1 text-16 text-light-1" >{{__("Special Requirements")}} </label>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
    {{--Passengers section start--}}
    <?php //echo '<pre>'; print_r($booking->pessengers); die(); ?>

    @if($totalPassenger = $booking->calTotalPassenger())
            <?php $old_data = old('passengers', []) ?>
        <div class="form-section mt-40">
            <h3 class="text-22 fw-500 mb-20">{{__("Traveler Details:")}}</h3>
            <div class="accordion -simple row y-gap-20 pt-30 js-accordion" id="passengers_info">
                @for($i = 1 ; $i <= $totalPassenger ; $i ++)
{{--                @for($i = 1 ; $i <= count($booking->pessengers) ; $i ++)--}}
                        <?php
                        $old_item = $old_data[$i] ?? [];
                        $pid = $booking->pessengers[$i-1]->id;
                        ?>

                    <input type="hidden"  class="form-control"
                           value="{{$pid}}"
                           name="passengers[{{$i}}][id]">
                    <div class="col-12 accordion__item px-20 py-20 border-light rounded-4">
                        <div class="accordion__button d-flex items-center" id="passenger_heading_{{$i}}">
                            <div class="accordion__icon size-40 flex-center bg-light-2 rounded-full mr-20">
                                <i class="icon-plus"></i>
                                <i class="icon-minus"></i>
                            </div>
                            <button class="button text-dark-1" data-toggle="collapse" data-target="#passenger_{{$i}}" aria-expanded="true"
                                    aria-controls="passenger_{{$i}}">
                                {{__("Traveler #:number",['number'=>$i])}}:
                            </button>
                        </div>

                        <div id="passenger_{{$i}}" class="accordion__content @if($i == 1) show @endif"
                             aria-labelledby="passenger_heading_{{$i}}" data-parent="#passengers_info">
                            <div class="pt-20 pl-60">
                                <div class="row y-gap-20">
                                    <div class="col-md-6">
                                        <div class="form-input">
                                            <input type="text"  class="form-control"
                                                   value="{{$old_item['first_name'] ?? ''}}"
                                                   name="passengers[{{$i}}][first_name]">
                                            <label class="lh-1 text-16 text-light-1">{{__("First Name*")}} </label>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-input">
                                            <input type="text"  class="form-control"
                                                   value="{{$old_item['last_name'] ?? ''}}"
                                                   name="passengers[{{$i}}][last_name]">
                                            <label class="lh-1 text-16 text-light-1">{{__("Last Name*")}}</label>

                                        </div>
                                    </div>
                                    <!--<div class="col-md-6 field-email ">
                                    <div class="form-input">
                                        <input type="email"
                                               class="form-control" value="{{$old_item['email'] ?? ''}}"
                                               name="passengers[{{$i}}][email]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Email")}} </label>

                                    </div>
                                </div>-->
                                    <div class="col-md-6">
                                        <div class="form-input">
                                            <input type="text"  class="form-control"
                                                   value="{{$old_item['phone'] ?? ''}}" name="passengers[{{$i}}][phone]">
                                            <label class="lh-1 text-16 text-light-1">{{__("Phone*")}} </label>

                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-input">
                                            <select name="passengers[{{$i}}][gender]" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                            <label class="lh-1 text-16 text-light-1">{{__("Gender*")}} </label>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-input">
                                            <input type="text"  class="form-control"
                                                   value="{{$old_item['passport_number'] ?? ''}}" name="passengers[{{$i}}][passport_number]">
                                            <label class="lh-1 text-16 text-light-1">{{__("Passport Number")}} </label>

                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-input ">
                                            <input type="text" class="date-picker has-value" value="" name="passengers[{{$i}}][passport_expiry_date]">
                                            <label class="lh-1 text-16 text-light-1">{{ __("Passport Expiry Date") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-input ">
                                            <input type="text" class="date-picker has-value" value="" name="passengers[{{$i}}][dob]">
                                            <label class="lh-1 text-16 text-light-1">{{ __("Date of Birth*") }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 field-country">
                                        <div class="form-input ">
                                            <select name="passengers[{{$i}}][country]" class="form-control">
                                                <option value="BD">Bangladesh</option>
                                                @foreach(get_country_lists() as $id=>$name)
                                                    <option @if(($user->country ?? '') == $id) selected @endif value="{{$id}}">{{$name}}</option>
                                                @endforeach
                                            </select>
                                            <label class="lh-1 text-16 text-light-1" >{{__("Nationality")}} <span class="required">*</span> </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 field-zip-code">
                                        <div class="form-input ">
                                            <input type="text" class="form-control" value="{{$old_item['zip_code'] ?? ''}}" name="passengers[{{$i}}][zip_code]" >
                                            <label class="lh-1 text-16 text-light-1" >{{__("ZIP code/Postal code")}} </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endif

    {{--Passengers section end--}}
    {{--        @include ('Booking::frontend/booking/checkout-passengers')--}}
    {{--        checkout deposit section start --}}
    @if(floatval($booking->deposit) and in_array($booking->status,['draft','unpaid']))
            <?php
            $oldDeposit = $booking->getMeta('old_deposit');
            $deposit = $booking->deposit;
            if (empty(floatval($deposit))){
                $deposit  = !empty($oldDeposit)?floatval($oldDeposit):0;
            }
            ;?>
        <hr>
        <div class="form-section">
            <h4 class="form-section-title">{{__("How do you want to pay?")}}</h4>
            <div class="deposit_types gateways-table accordion ">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h4 class="mb-0"><label ><input type="radio" checked name="how_to_pay" value="deposit">
                                    {{__("Pay deposit")}}
                                </label></h4>
                            <span class="price"><strong>{{format_money($deposit)}}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h4 class="mb-0"><label ><input type="radio"  name="how_to_pay" value="full">
                                    {{__("Pay in full")}}
                                </label></h4>
                            <span class="price"><strong>{{format_money($booking->total)}}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--        checkout deposit section end --}}
    {{--        @include ('Booking::frontend/booking/checkout-deposit')--}}
    {{--        checkout-payment start--}}
    {{--        @if(isset($service->checkout_form_payment_file ))--}}
    <div class="form-section mt-40">
        <h3 class="text-22 fw-500 mb-20">{{__("How do you want to pay?")}}</h3>
        <div class="accordion -simple row y-gap-20 pt-30 js-accordion">
            <div class="col-12 accordion__item px-20">
                <div class=" border-light rounded-4 py-20">
                    <div class="accordion__button d-flex items-center">
                        <button class="button text-dark-1 shrink-0  px-20 " type="button">
                            <label class="d-flex items-center" data-toggle="collapse" data-target="" >
                                <input type="radio" name="payment_gateway" value="ssl">
                                <span class="shrink-0 ml-20">SSL</span>
                            </label>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 accordion__item px-20">
                <div class=" border-light rounded-4 py-20">
                    <div class="accordion__button d-flex items-center">
                        <button class="button text-dark-1 shrink-0  px-20 " type="button">
                            <label class="d-flex items-center" data-toggle="collapse" data-target="" >
                                <input type="radio" name="payment_gateway" value="bkash">
                                <span class="shrink-0 ml-20">Bkash</span>
                            </label>
                        </button>
                    </div>
                </div>
            </div>
            @if(isset(auth()->user()->balangce ))
                @if($booking->total <= auth()->user()->balance)
                    <div class="col-12 accordion__item px-20">
                        <div class=" border-light rounded-4 py-20">
                            <div class="accordion__button d-flex items-center">
                                <button class="button text-dark-1 shrink-0  px-20 " type="button">
                                    <label class="d-flex items-center" data-toggle="collapse" data-target="" >
                                        <input type="radio" name="payment_gateway" value="wallet">
                                        <span class="shrink-0 ml-20">Wallet (Balance:{{auth()->user()->balance}})</span>
                                    </label>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif


            @foreach($gateways as $k=>$gateway)
                <div class="col-12 accordion__item px-20">
                    <div class=" border-light rounded-4 py-20">
                        <div class="accordion__button d-flex items-center">
                            <button class="button text-dark-1 shrink-0  px-20 " type="button">
                                <label class="d-flex items-center" data-toggle="collapse" data-target="#gateway_{{$k}}" >
                                    <input type="radio" name="payment_gateway" value="{{$k}}">
                                    @if($logo = $gateway->getDisplayLogo())
                                        <img src="{{$logo}}" alt="{{$gateway->getDisplayName()}}">
                                    @endif
                                    <span class="shrink-0 ml-20">{{$gateway->getDisplayName()}}</span>

                                </label>
                            </button>

                        </div>
                        <div id="gateway_{{$k}}" class="accordion__content" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="pt-20 pl-60">
                                <div class="gateway_name">
                                    {!! $gateway->getDisplayName() !!}
                                </div>
                                {!! $gateway->getDisplayHtml() !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{--        @endif--}}

    {{--        checkout-payment end--}}
    {{--        @include ($service->checkout_form_payment_file ?? 'Booking::frontend/booking/checkout-payment')--}}

    @php
        $term_conditions = setting_item('booking_term_conditions');
    @endphp

    @if(setting_item("booking_enable_recaptcha"))
        <div class="form-input ">
            {{recaptcha_field('booking')}}
        </div>
    @endif
    <div class="html_before_actions"></div>

    <p class="alert-text mt10" v-show=" message.content" v-html="message.content" :class="{'danger':!message.type,'success':message.type}"></p>

    <div class="row y-gap-20 items-center justify-between mb-40">
        <div class="col-auto">
            <div class="d-flex items-center">
                <div class="form-checkbox ">
                    <input type="checkbox" name="term_conditions">
                    <div class="form-checkbox__mark">
                        <div class="form-checkbox__icon icon-check"></div>
                    </div>
                </div>
                <div class="text-14 lh-10 text-light-1 ml-10">{{__('I have read and accept the')}}
                    <a target="_blank" href="{{get_page_url($term_conditions)}}">{{__('terms and conditions')}}</a></div>

            </div>
        </div>

        <div class="col-auto">
            <button class="button h-60 px-24 -dark-1 bg-blue-1 text-white" @click="doCheckout">
                {{__('Book Now')}}
                <div class="icon-arrow-top-right ml-15"></div>
                <i class="fa fa-spin fa-spinner" v-show="onSubmit"></i>
            </button>

            {{--                <button type="submit" class="button h-60 px-24 -dark-1 bg-blue-1 text-white">--}}
            {{--                {{__('Book Now')}}--}}
            {{--                <div class="icon-arrow-top-right ml-15"></div>--}}
            {{--                <i class="fa fa-spin fa-spinner" ></i>--}}
            {{--                </button>--}}

            {{--                <input  type="submit" value="Book now" >--}}

        </div>
    </div>
</div>
{{--</form>--}}
