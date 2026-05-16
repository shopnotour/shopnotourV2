(function ($){
    const form = $('.bravo_filter');
    const clearFilter = $('.bravo-clear-filter');
    const formTop = $('.bravo_form_search');
    let timeout = null;

    $(document).on('change','.bc-form-order select',function (){
        doSearch();
    });
    $(document).on('click','.switch-layout a',function (e){
        e.preventDefault();
        if(!$(this).hasClass('active')){
            $(this).addClass('active').siblings().removeClass('active');
            doSearch();
        }
    });
    $(document).on('click','.bravo-pagination a',function (e){
        e.preventDefault();
        doSearch($(this).attr('href'));
    });

    $('[name=s]','.civi-menu-filter').on('keyup',function(){
        if(timeout) window.clearTimeout(timeout);

        timeout = window.setTimeout(function (){
            doSearch();
        },300);
    })

    form.on('submit',function(e){
        e.preventDefault();
        doSearch();
    });
    formTop.on('submit',function(e){
        e.preventDefault();
        doSearch();
    });

    // Auto-trigger AJAX on filter changes
    form.on('change', 'input[type=checkbox], input[type=radio], select', function() {
        doSearch();
    });

    $('.civi-clear-top-filter').click(function(){
        formTop.find('input[type=text],input[type=hidden],select').val('');
        doSearch();
    })

    clearFilter.on('click',function (){
        clearFormFilter();
    })
    $(document).on('click','.ajax-search-result .civi-clear-filter',function (){
        clearFormFilter();
    });

    function clearFormFilter(){
        form.find('input[type=radio],input[type=checkbox]').prop('checked',false);
        form.find('input[type=text],input[type=hidden]').val('');
        clearFilter.hide();
        $('.ajax-search-result .civi-clear-filter').hide();
        doSearch();
    }


    function checkShowClear(){
        const filterFormData = {};
        form.find('input,textarea,select').serializeArray().map(function(x){ if(x.value)  filterFormData[x.name] = x.value;});

        if(Object.keys(filterFormData).length){
            toggleClearFilter(true);
        }else{
            toggleClearFilter(false);
        }
    }
    function toggleClearFilter(status){
        if(status){
            clearFilter.show();
            $('.ajax-search-result .civi-clear-filter').show();
        }else{
            clearFilter.hide();
            $('.ajax-search-result .civi-clear-filter').hide();
        }
    }
    checkShowClear();


    $('.orderby .dropdown-item').on('click',function (e){
        e.preventDefault();
        $('[name=orderby]').val($(this).data('value'));
        $('.orderby .dropdown-toggle').html($(this).html());
        doSearch();
    })

    window.doSearch = function(withUrl){
        let fullUrl = '';
        if(typeof withUrl === 'undefined') {
            const orderForm = $('.bc-form-order');
            let topFormData = [];
            let orderFormData = [];
            let filterFormData = [];
            topFormData = formTop.find('input,textarea,select').serializeArray().filter(function (x) {
                return x.value;
            });
            filterFormData = form.find('input,textarea,select').serializeArray().filter(function (x) {
                return x.value;
            });
            orderFormData = orderForm.find('input,textarea,select').serializeArray().filter(function (x) {
                return x.value;
            });
            // orderFormData.push({'name':'_display','value':$('.switch-layout .active').data('layout')});
            if (Object.keys(filterFormData).length) {
                // toggleClearFilter(true);
            } else {
                // toggleClearFilter(false);
            }
            const params = [...topFormData,...orderFormData, ...filterFormData];
            fullUrl = currentUrl + '?' + params.map((p)=>p.name+"="+p.value).join('&');
        }else{
            fullUrl = withUrl;
        }
        window.history.pushState({}, '', fullUrl);
        $('.item-loop-wrap').addClass('skeleton-loading');

        $.ajax({
            url:fullUrl,
            data:{
                _ajax:1
            },
            success : function(json){
                console.log('AJAX success, fragments:', Object.keys(json.fragments || {}));

                if(typeof json.fragments !== 'undefined'){
                    for(const k in json.fragments){
                        console.log('Updating fragment:', k);
                        $(k).html(json.fragments[k]);
                    }
                }
                window.lazyLoadInstance.update();

                if(typeof json.markers !== 'undefined'){
                    $('#map').trigger('update-markers',[json.markers])
                }

                // Remove skeleton loading
                $('.item-loop-wrap').removeClass('skeleton-loading');

                console.log('AJAX filter completed - tabs are handled by event delegation');

                console.log('Price slider container exists:', $('.js-price-searchPage').length);
                console.log('Price slider element exists:', $('.js-price-searchPage .js-slider').length);

                // Reinitialize price range slider after AJAX update with delay
                setTimeout(function() {
                    if (typeof priceRangeSliderDetailInit === 'function') {
                        try {
                            priceRangeSliderDetailInit();
                            console.log('Price slider reinitialized successfully');
                        } catch (error) {
                            console.error('Error reinitializing price slider:', error);
                        }
                    } else {
                        console.warn('priceRangeSliderDetailInit function not found');
                    }
                }, 200);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                $('.item-loop-wrap').removeClass('skeleton-loading');
            }
        })
    }

})(jQuery)
