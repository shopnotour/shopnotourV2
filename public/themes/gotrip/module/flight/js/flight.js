(function ($) {
    var isEmpty = function isEmpty(f) {
        return (/^function[^{]+\{\s*\}/m.test(f.toString())
        );
    }

    // Show/Hide Details function for flight search results
    window.showHideDetails = function(flightId) {
        const detailsDiv = $('#showHideDetailsDiv-' + flightId);
        const detailsLink = $('#showHideDetailsA-' + flightId);

        if (detailsDiv.is(':visible')) {
            detailsDiv.slideUp(300);
            detailsLink.text('Show Details');
        } else {
            detailsDiv.slideDown(300);
            detailsLink.text('Hide Details');
            console.log('Details shown for flight:', flightId);
        }
    };

    var flightFormBookModal = new Vue({
        el:'#flightFormBookModal',
        data:{
            id:'',
            buyer_fees:[],
            message:{
                content:'',
                type:false
            },
            flight:{
                airline:{},
                airport_from:{},
                airport_to:{}
            },
            html:'',
            onSubmit:false,
            step:1,
            firstLoad:true,
            i18n:[],
            total_price_before_fee:0,
            total_price_fee:0,
            onLoading:false,
            modal:null
        },
        computed: {
            total_price:function() {
                var me = this;
                var total = 0;
                if(typeof me.flight.flight_seat !='undefined'){
                    _.forEach( me.flight.flight_seat,function (item) {
                        if(item.number >0){
                            total += item.number * item.price;
                        }
                    });
                }
                return total;
            },
            total_price_html:function(){
                if(!this.total_price) return '';
                return window.bravo_format_money(this.total_price);
            },
        },
        methods: {
            openModal(flightId) {
                this.modal.show();
                var me = this;
                me.id= flightId;
                if(me.onSubmit==true){
                    return false;
                }
                me.onSubmit = true;
                me.onLoading = true;// dung cai nay de them icon loading
                $.ajax({
                    url:bookingCore.module.flight+'/getData/61',
                    data:this.form,
                    dataType:'json',
                    method:'post',
                    success:function (json) {
                        if(json.status){
                            me.flight = json.data;
                        }
                        me.onSubmit = false;
                        me.onLoading = false;
                    },
                    error:function (e) {
                        me.onSubmit = false;
                        me.onLoading = false;
                    }
                });
            },
            flightCheckOut(flightData){
                // console.log('sarowar click');
                var flightId = flightData.data('id');
                var flight_data = flightData.data('flightdata');
                var request_data = flightData.data('requestdata');
                var discount = flightData.data('discount');           // ← ADD THIS
                var finalPrice = flightData.data('final-price');      // ← ADD THIS
                var me = this;
                me.message.content = '';

                // Try to get raw attributes if data() didn't work
                if (!flight_data) {
                    var flightDataAttr = flightData.attr('data-flightdata');
                    if (flightDataAttr) {
                        try {
                            flight_data = JSON.parse(flightDataAttr);
                        } catch(e) {
                            console.error('Error parsing flight_data from attribute:', e);
                        }
                    }
                }

                if (!request_data) {
                    var requestDataAttr = flightData.attr('data-requestdata');
                    if (requestDataAttr) {
                        try {
                            request_data = JSON.parse(requestDataAttr);
                        } catch(e) {
                            console.error('Error parsing request_data from attribute:', e);
                        }
                    }
                }

                // Only validate flightId - let backend validate the rest
                if (!flightId && flightId !== 0) {
                    console.error('Flight ID missing:', flightId);
                    if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showError) {
                        bookingCoreApp.showError('Flight ID is missing. Please try again.');
                    } else {
                        alert('Flight ID is missing. Please try again.');
                    }
                    return false;
                }

                // Ensure flightId is a number
                var serviceId = parseInt(flightId, 10);
                if (isNaN(serviceId) || serviceId <= 0) {
                    console.error('Invalid flight ID:', flightId);
                    if (typeof bookingCoreApp !== 'undefined' && bookingCoreApp.showError) {
                        bookingCoreApp.showError('Invalid flight ID. Please try again.');
                    } else {
                        alert('Invalid flight ID. Please try again.');
                    }
                    return false;
                }

                var params = {
                    flight_data: flight_data,
                    request_data: request_data,
                    service_id: serviceId, // Ensure it's a number
                    service_type: 'flight',
                    discount: discount || 0,              // ← ADD THIS
                    finalPrice: finalPrice || null        // ← ADD THIS
                    // Note: flight_seat is not needed - backend processes seat information from flight_data
                }
                console.log('Booking params:', params);
                console.log('Service ID type:', typeof params.service_id, 'Value:', params.service_id);
                console.log('Discount:', discount, 'Final Price:', finalPrice);  // ← ADD THIS for debugging
                if(me.onSubmit==true){
                    return false;
                }
                me.onSubmit = true;
                $.ajax({
                    url:bookingCore.url+'/booking/addToCart',
                    data:params,
                    dataType:'json',
                    method:'post',
                    success:function (json) {
                        if(!json.status){
                            me.onSubmit = false;
                        }
                        if(json.message)
                        {
                            me.message.content = json.message;
                            me.message.type = json.status;
                        }
                        if(json.url){
                            //exit;
                            window.location.href = json.url
                        }
                        if(json.errors && typeof json.errors == 'object')
                        {
                            var html = '';
                            for(var i in json.errors){
                                html += json.errors[i]+'<br>';
                            }
                            me.message.content = html;
                            bookingCoreApp.showError(html);
                        }
                        me.onSubmit = false;
                    },
                    error:function (e) {
                        me.onSubmit = false;
                        bravo_handle_error_response(e);
                        if(e.status == 401){
                            this.modal.hide();
                        }
                        if(e.status != 401 && e.responseJSON){
                            me.message.content = e.responseJSON.message ? e.responseJSON.message : 'Can not booking';
                            me.message.type = false;
                        }
                        me.onSubmit = false;
                    }
                });
            },

            minusNumberFlightSeat(flightSeat){
                if(flightSeat.number <= 0){
                    flightSeat.number = 0;
                }else{
                    flightSeat.number--;
                }
            },
            addNumberFlightSeat(flightSeat){
                if(flightSeat.number>=flightSeat.max_passengers){
                    flightSeat.number=flightSeat.max_passengers;
                }else{
                    flightSeat.number++;
                }
            },
            updateNumberFlightSeat(flightSeat){
                if(flightSeat.number>=flightSeat.max_passengers){
                    flightSeat.number=flightSeat.max_passengers;
                }
            }
        },
        mounted(){
            this.modal = new bootstrap.Modal('#flightFormBookModal');
        }
    })
    window.flightFormBook = new Vue({
        el:'#flightFormBook',
        data:{
        },
        methods:{
            openModalBook(flightData){
                //flightFormBookModal.openModal(flightId);
                flightFormBookModal.flightCheckOut(flightData);
                // var flightDataObj = flightData.data('flightdata');
                //
                // // Discount add korun
                // flightDataObj.discount = flightData.data('discount');
                // flightDataObj.finalPrice = flightData.data('final-price');
                //
                // flightFormBookModal.flightCheckOut(flightDataObj);
            }
        },
        created(){
            var me = this;
            flightId = $(this).data('id');
            $(document).on('click','.btn-choose-flight',function(){

                me.openModalBook($(this));
            })
        }
    })


    $(".bravo_filter .g-filter-item").each(function () {
        if($(window).width() <= 990){
            $(this).find(".item-title").toggleClass("e-close");
        }
        $(this).find(".item-title").on("click", function (e) {
            $(this).toggleClass("e-close");
            if($(this).hasClass("e-close")){
                $(this).closest(".g-filter-item").find(".item-content").slideUp();
            }else{
                $(this).closest(".g-filter-item").find(".item-content").slideDown();
            }
        });
        $(this).find(".btn-more-item").on("click", function (e) {
            $(this).closest(".g-filter-item").find(".hide").removeClass("hide");
            $(this).addClass("hide");
        });
        $(this).find(".bravo-filter-price").each(function () {
            var input_price = $(this).find(".filter-price");
            var min = input_price.data("min");
            var max = input_price.data("max");
            var from = input_price.data("from");
            var to = input_price.data("to");
            var symbol = input_price.data("symbol");
            input_price.ionRangeSlider({
                type: "double",
                grid: true,
                min: min,
                max: max,
                from: from,
                to: to,
                prefix: symbol
            });
        });
    });
    $(".bravo_form_filter input[type=checkbox]").change(function () {
        $(this).closest(".bravo_form_filter").submit();
    });


    $(document).off('click', '.shopno_tabs__button').on('click', '.shopno_tabs__button', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const $button = $(this);
        const selector = $button.attr('data-tab-target');
        const $tabsContainer = $button.closest('.js-tabs');
        const $controls = $tabsContainer.find('.js-tabs-controls');
        const $content = $tabsContainer.find('.js-tabs-content');

        console.log('Tab button clicked:', selector);
        console.log('Controls found:', $controls.length);
        console.log('Content container found:', $content.length);

        if (!$controls.length || !$content.length) {
            console.error('Tab controls or content not found');
            return false;
        }


        $controls.find('.is-tab-el-active').removeClass('is-tab-el-active');
        $content.find('.is-tab-el-active').removeClass('is-tab-el-active');


        $button.addClass('is-tab-el-active');
        const $targetContent = $content.find(selector);
        console.log('Target content found:', $targetContent.length);
        $targetContent.addClass('is-tab-el-active');

        console.log('Tab switched successfully to:', selector);
        return false;
    });

})(jQuery);
