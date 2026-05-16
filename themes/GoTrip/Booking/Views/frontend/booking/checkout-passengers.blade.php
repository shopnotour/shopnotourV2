<?php //echo '<pre>'; print_r($booking->pessengers); die(); ?>

@if($totalPassenger = $booking->calTotalPassenger())
    <?php $old_data = old('passengers', []) ?>
    <div class="form-section mt-40">
        <h3 class="text-22 fw-500 mb-20">{{__("Traveler Details:")}}</h3>
        <div class="accordion -simple row y-gap-20 pt-30 js-accordion" id="passengers_info">
            @for($i = 1 ; $i <= count($booking->pessengers) ; $i ++)
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
                                        <label class="lh-1 text-16 text-light-1">{{__("First Name")}} </label>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-input">
                                        <input type="text"  class="form-control"
                                               value="{{$old_item['last_name'] ?? ''}}"
                                               name="passengers[{{$i}}][last_name]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Last Name")}}</label>

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
                                        <label class="lh-1 text-16 text-light-1">{{__("Phone")}} </label>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-input">
                                        <input type="text"  class="form-control"
                                               value="{{$old_item['dob'] ?? ''}}" name="passengers[{{$i}}][dob]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Date of Birth")}} </label>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-input">
                                        <select name="passengers[{{$i}}][gender]" class="form-control">
                                            <option value="BD">Male</option>
                                            <option value="BD">Female</option>
                                        </select>
                                        <input type="text"  class="form-control"
                                               value="{{$old_item['gender'] ?? ''}}" name="passengers[{{$i}}][gender]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Gender")}} </label>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-input">
                                        <input type="text"  class="form-control"
                                               value="{{$old_item['passport_number'] ?? ''}}" name="passengers[{{$i}}][passport_number]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Passport Number")}} </label>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-input">
                                        <input type="text"  class="form-control"
                                               value="{{$old_item['passport_expiry_date'] ?? ''}}" name="passengers[{{$i}}][passport_expiry_date]">
                                        <label class="lh-1 text-16 text-light-1">{{__("Passport Expiry Date")}} </label>

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
