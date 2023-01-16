/***************************************************************************
 *
 * SIMULATION JS
 *
 ***************************************************************************/

$(document).ready(function() {

    // GLOBAL VAR
    var clstore_countries_name = 'simu_countries_data';
    var cookie_checkstore_name = 'simu_store_has_save';
    var $popupArea = $('#popupArea');
    var $simuLoader = $('.loader');
    var $simpleSimuLoader = $('.checkCountry .loader');
    let rental_fee = 220;
    let rental_fee_notax = 200;
    let tab_name = '渡航国';
    let tab_number_icon = {
        '1' : '①',
        '2' : '②',
        '3' : '③', 
        '4' : '④'
    };
    let cart_target_url = 'https://kw.gonosen.work/shopping/option';

    
    // LOAD SIMULATION

    $simuLoader.stop().fadeIn(100);
    $simpleSimuLoader.stop().fadeIn(100);
    
    // SET CURRENT DATE VAL DATERANGE PICKER
    var current_date = moment(new Date()).format('YYYY/MM/DD');
    var next_date = moment(new Date()).add('days', 1).format('MM/DD');
    
    var url = window.location.href;
    var cart_id = "";
    if (url.includes("?cart_id=")) {
        cart_id = url.split("?cart_id=")[1];
    }

    if (cart_id != "") {
        $.ajax({
            type : "get",
            dataType : "json",
            url : 'https://kw.api.gonosen.work/api/cart/'+cart_id,
            crossDomain: true,
            headers: {
                'Content-type': 'application/json'
            },
            // context: this,
            beforeSend: function(){},
            success: function(response) {
                var cart_data = '';
                if( response.status && response.data ) {
                    cart_data = response.data;
                }
                if (cart_data.length > 0) {
                    for (var i = 0; i < cart_data.length; i++) {
                        var start_date = new Date(cart_data[i].from_date);
                        var end_date = new Date(cart_data[i].to_date);
                        var rent_dates = dateDiffIndays(start_date, end_date);
                        if (i == 0) {
                            var countrie_data = {
                                country_name : cart_data[i].country_name_jp,
                                country_code : cart_data[i].country_code,
                                price : cart_data[i].total_price / (rent_dates + 1),
                                date_start : cart_data[i].from_date,
                                date_end : cart_data[i].to_date,
                                date_count : rent_dates,
                                productclass_id : '',
                            }
                            let $tab_active = $('.simulation .simuItem.actived');
                            if( $tab_active.length > 0 ) {
                                $tab_active.find('.simuInput .countries-suggest').removeClass('istyping').val(cart_data[i].country_name_jp);
                                $tab_active.find('.startDate').text(cart_data[i].from_date);
                                $tab_active.find('.endDate').text('～'+cart_data[i].to_date.substring(5));
                            }
                            updateCountrieData(countrie_data);
                        } else {
                            addSimuTabWithCartItem(cart_data[i].country_name_jp + '_' + cart_data[i].from_date.substring(2) + '_' + cart_data[i].to_date.substring(2) + '_' + cart_data[i].country_code, cart_data[i].total_price / (rent_dates + 1), ((cart_data[i].total_price / (rent_dates + 1) + rental_fee) * rent_dates), '');
                        }
                    }
                    updatePriceResult();
                } else {
                    $('.simulation .simuOption .simuInput.date .resultDate .startDate').text(current_date);
                    $('.simulation .simuOption .simuInput.date .resultDate .endDate').text('～'+next_date);

                    // BEGIN ADD TAB 2
                    addSimuTab();
                }
                
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            },
            error: function( jqXHR, textStatus, errorThrown ) {
                console.log( 'The following error occured: ' + textStatus, errorThrown );
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            }
        });
    } else {
        $.ajax({
            type : "get",
            dataType : "json",
            url : '/get_cart',
            // context: this,
            beforeSend: function(){},
            success: function(response) {
                if (response.length > 0) {
                    for (var i = 0; i < response.length; i++) {
                        if (i == 0) {
                            var content = response[i]['name'].split('_');
                            var countrie_data = {
                                country_name : content[0],
                                country_code : content[3],
                                price : response[i]['rental'] / ((response[i]['total'] - response[i]['rental']) / rental_fee),
                                date_start : '20'+content[1],
                                date_end : '20'+content[2],
                                date_count : (response[i]['total'] - response[i]['rental']) / rental_fee,
                                productclass_id : response[i]['id'],
                            }
                            let $tab_active = $('.simulation .simuItem.actived');
                            if( $tab_active.length > 0 ) {
                                $tab_active.find('.simuInput .countries-suggest').removeClass('istyping').val(content[0]);
                                $tab_active.find('.startDate').text('20'+content[1]);
                                $tab_active.find('.endDate').text('～'+content[2].substring(3));
                            }
                            updateCountrieData(countrie_data);
                        } else {
                            addSimuTabWithCartItem(response[i]['name'], response[i]['rental'], response[i]['total'], response[i]['id']);
                        }          
                    }

                    updatePriceResult();
                } else {
                    $('.simulation .simuOption .simuInput.date .resultDate .startDate').text(current_date);
                    $('.simulation .simuOption .simuInput.date .resultDate .endDate').text('～'+next_date);

                    // BEGIN ADD TAB 2
                    addSimuTab();
                }
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            },
            error: function( jqXHR, textStatus, errorThrown ) {
                console.log( 'The following error occured: ' + textStatus, errorThrown );
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            }
        });
    }
    
    

    // GET DATA COUNTRIES SAVE LOCALSTORE
    if( !getCookie(cookie_checkstore_name) || !localStorage.getItem(clstore_countries_name) ) {
        $simuLoader.stop().fadeIn(100);
        $simpleSimuLoader.stop().fadeIn(100);
        $.ajax({
            type : "get",
            dataType : "json",
            url : 'https://kw.api.gonosen.work/api/countries',
            crossDomain: true,
            headers: {
                'Content-type': 'application/json'
            },
            // context: this,
            beforeSend: function(){},
            success: function(response) {
                if( response.status && response.data ) {
                    localStorage.setItem( clstore_countries_name, JSON.stringify(response.data.countries) );
                    setCookie(cookie_checkstore_name, '1');
                }
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            },
            error: function( jqXHR, textStatus, errorThrown ) {
                console.log( 'The following error occured: ' + textStatus, errorThrown );
                $simuLoader.stop().fadeOut(100);
                $simpleSimuLoader.stop().fadeOut(100);
            }
        });
    }


    


    // EVENT


    // COUNTRIES SUGGEST ACTION
    var timeout = null;
    $(document).on('keyup', '.countries-suggest', function(){
        var text_suggest = $(this).val();
        var $that = $(this);
        $(this).addClass('istyping');
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            // GET HTML
            let countries_html = '';
            if( text_suggest ) {
                let countries_data_str = localStorage.getItem(clstore_countries_name);
                let countries_data = JSON.parse(countries_data_str);
                for (var i = 0; i < countries_data.length; i++) {
                    let countrie_price = countries_data[i].price;
                    countrie_price = (countrie_price) ? countrie_price : '0';
                    let countrie_price_fm = stringToPrice(countrie_price);
                    let countrie_code = countries_data[i].country_code;
                    if( countries_data[i].country_name_jp.includes(text_suggest) ) {
                        countries_html += '<li data-price="'+countrie_price+'" data-code="'+countrie_code+'">';
                        countries_html += '<p class="nameArea">'+countries_data[i].country_name_jp+'</p>';
                        countries_html += '<p class="priceArea roboto">¥<span>'+countrie_price_fm+'</span></p>';
                        countries_html += '</li>';
                    }
                }
            }
            $that.next('.listArea').html(countries_html);
            if( countries_html ) {
                $that.next('.listArea').stop().fadeIn(100);
                $that.closest('.simuInput').addClass('hasSuggest');
            } else {
                $that.next('.listArea').stop().hide();
                $that.closest('.simuInput').removeClass('hasSuggest');
            }
            
        }, 800);
    });


    // CONUTRIES SUGGEST SELECTED
    $(document).on('click', '.simulation .simuOption .simuInput .listArea li', function(){
        let count_price = $(this).attr('data-price');
        let count_name = $(this).find('.nameArea').text();
        let count_code = $(this).attr('data-code');
        let countrie_data = {
            country_name : count_name,
            country_code : count_code,
            price : count_price
        };
        updateCountrieData(countrie_data);
        $(this).closest('.simuInput').find('.countries-suggest').removeClass('istyping').val(count_name);
        $(this).closest('.listArea').stop().fadeOut(100);
        $(this).closest('.simuInput').removeClass('hasSuggest');
    });


    // MAP AREA HOVER CHANGE COLOR
    $(document).on('mouseover mouseout', '.simulation .simuArea .mapButton .btn a', function() {
        var area_id = $(this).attr('data-svg');
        var $group = $(this).closest('.simuArea').find('.mapArea g[data-group="'+area_id+'"]');
        $group.attr('class', function(index, attr) {
            return attr == 'inActive' ? null : 'inActive';
        });
    });


    // MAP AREA CLICK
    $(document).on('click', '.simulation .simuArea .mapButton .btn a', function(){
        let conti_name = $(this).attr('data-conti-name');
        let countries_data_html = '';

        if( conti_name ) {
            countries_data_html = getCountriesHtmlByArea(conti_name);

            // ACTIVE AREA
            let $area_active =  $('.popupArea .areaNameList li a[data-conti-name="'+conti_name+'"]');
            if( $area_active.length > 0 ) {
                let area_active_name = $area_active.text();
                $('.popupArea .countryList .countryTitle .areaName').text(area_active_name);
                $area_active.closest('.areaNameList').find('li').removeClass('selected');
                $area_active.closest('li').addClass('selected');
            }
            
        }

        // SHOW DATA
        $('.popupArea .countryList .countryWrap').html(countries_data_html);
        Fancybox.show([{ src: "#popupArea", type: "inline" }]);
    });


    // AREA POPUP FILTER CLICK
    $(document).on('click', '.popupArea .areaList .areaNameList li a', function(){
        let $area_active = $(this);
        let conti_name = $(this).attr('data-conti-name');
        let area_active_name = $area_active.text();
        $('.popupArea .countryList .countryTitle .areaName').text(area_active_name);
        $area_active.closest('.areaNameList').find('li').removeClass('selected');
        $area_active.closest('li').addClass('selected');
        // SHOW COUNTRIES
        let countries_data_html = '';
        if( conti_name ) {
            countries_data_html = getCountriesHtmlByArea(conti_name);
        }
        $('.popupArea .countryList .countryWrap').html(countries_data_html);
    });


    // COUNTRIES POPUP ITEM CLICK
    $(document).on('click', '.popupArea .countryList .countryNameList li a', function(){
        let countrie_price = $(this).attr('data-price');
        let countrie_name = $(this).attr('data-name');
        let countrie_code = $(this).attr('data-code');
        let countrie_data = {
            country_name : countrie_name,
            country_code : countrie_code,
            price : countrie_price
        };
        // UPDATE COUNTRY INPUT
        let $tab_active = $('.simulation .simuItem.actived');
        if( $tab_active.length > 0 ) {
            $tab_active.find('.simuInput .countries-suggest').removeClass('istyping').val(countrie_name);
        }
        // UPDATE COUNTRIES DATA
        updateCountrieData(countrie_data);
        Fancybox.close(false);
    });


    // DATE PICKER APPLY
    datePickerSelected('#daterange');
    function datePickerSelected( id_datepicker ) {
        if( id_datepicker ) {
            $(id_datepicker).on('apply.daterangepicker', function(ev, picker) {
                let date_start = picker.startDate.format('YYYY/MM/DD');
                let date_end = picker.endDate.format('YYYY/MM/DD');
                let date_end_md = picker.endDate.format('MM/DD');
                let date_count = dateDiffIndays(picker.startDate, picker.endDate);
                let countrie_data = {
                    date_start : date_start,
                    date_end : date_end,
                    date_count : date_count
                };
                updateCountrieData(countrie_data);
                // SHOW DATE SELECT
                let $date_show = $(id_datepicker).prev('.resultDate ');
                $date_show.find('.startDate').text(date_start);
                $date_show.find('.endDate').text('～'+date_end_md);
                // ADD CLASS HAS SELECTED
                $date_show.addClass('selected');

            });
        }
    }


    // SETUP DATERANGER FOR TAB1
    setUpDaterangePicker('#daterange');
    

    // ADD SIMU TAB
    $(document).on('click', '.simulation .simuTab .addTab a', function(){
        $('.simulation .simuOption .simuInput.date .resultDate .startDate').text(current_date);
        $('.simulation .simuOption .simuInput.date .resultDate .endDate').text('～'+next_date);
        addSimuTab();
    });


    // TAB SELECT ACTION
    $(document).on('click', '.simulation .simuTab .listTab li a', function(){
        let tab_num = $(this).attr('data-tab');
        let tab_name = $(this).text();
        $('.simulation .simuTab .listTab li').removeClass('active');
        $(this).parent().addClass('active');
        // ACTIVE TAB
        $('.simulation .simuItem').removeClass('actived');
        let $tab_item = $('.simulation .simuItem[data-tab="'+tab_num+'"]');
        $tab_item.addClass('actived');
        $tab_item.find('.simuOption .simuPrice .nameTab').text(tab_name);
        // ACTIVE RESULT
        let $result_item_active = $('.simulation .resultSimu .resultList.resultListSelect li[data-tab="'+tab_num+'"]').not('.selected');
        if( $result_item_active.length > 0 ) {
            let result_item_html = $result_item_active.prop('outerHTML');
            $('.simulation .resultSimu .resultList.resultCurrent').html(result_item_html);
            $result_item_active.closest('.resultListSelect').find('li').removeClass('selected');
            $result_item_active.addClass('selected');
            // SHOW RESULT PRICE
            updatePriceResult();
        }
    });


    // REMOVE SIMU TAB
    $(document).on('click', '.simulation .simuTab .listTab li .delete', function(){
        let $tab_item = $(this).closest('li').find('a');
        let tab_name = $tab_item.text();
        let tab_num = $tab_item.attr('data-tab');
        if( tab_num ) {
            $('.popupDelete .title .nameTab').text(tab_name);
            $('.popupDelete .btnSubmit a').attr('data-tab', tab_num);
            Fancybox.show([{ src: "#popupDelete", type: "inline" }]);
        }
    });


    // APPLY REMOVE SIMU TAB
    $(document).on('click', '.popupDelete .btnSubmit a', function(){
        let tab_num = $(this).attr('data-tab');
        removeSimuTab( tab_num );
        Fancybox.close(false);
    });


    // RESULT SHOW SELECTER
    $(document).on('click', '.simulation .resultSimu .resultList.resultCurrent', function(){
        $('.simulation .resultSimu .resultList.resultListSelect').stop().fadeToggle();
    });


    // RESULT SELECTED ACTION
    $(document).on('click', '.simulation .resultContent .resultSimu .resultList.resultListSelect li', function(){
        // ACTIVE RESULT
        let select_item = $(this).prop('outerHTML');
        $('.simulation .resultContent .resultSimu .resultList.resultCurrent').html(select_item);
        $(this).closest('.resultListSelect').find('li').removeClass('selected');
        $(this).addClass('selected');
        $(this).closest('.resultListSelect').stop().fadeOut(100);
    });


    // OUTSIDE ELEMENT EVENT
    $(document).click(function(event) {
        var $target = $(event.target);
        if( $target.closest('.simulation .listArea').length <= 0 ) {
            $('.simulation .simuContent .simuInput .listArea').stop().fadeOut(100);
            $('.simulation .simuContent .simuInput').removeClass('hasSuggest');
        }
        if( $target.closest('.simulation .resultList').length <= 0 ) {
            $('.simulation .resultList.resultListSelect').stop().fadeOut(100);
        }
    });


    // RESET DATA TAB
    $(document).on('click', '.simulation .countryDelData a', function(){
        let $current_tab = $(this).closest('.simuItem');
        if( $current_tab.length > 0 ) {
            let tab_num = $current_tab.attr('data-tab');
            let productclass_id = $current_tab.find('.country_data[name="productclass_id"]').val();
            if (tab_num == 1 && productclass_id) {
                $simuLoader.stop().fadeIn(100);
                $.ajax({
                    url: '/delete_cartitem',
                    method: 'post',
                    data: {
                      'productclass_id': productclass_id
                    },
                    success: function(response) {
                        $simuLoader.stop().fadeOut(100);
                    },
                    error: function( jqXHR, textStatus, errorThrown ) {
                        console.log( 'The following error occured: ' + textStatus, errorThrown );
                        $simuLoader.stop().fadeOut(100);
                    }
                });
            }
            // RESET TAB DATA
            $current_tab.find('.country_data').val('');
            $current_tab.find('.countries-suggest').val('');
            $current_tab.find('.simuPrice .numPrice').text('ーーー');
            $current_tab.find('.simuInput.date .resultDate .startDate').text(current_date);
            $current_tab.find('.simuInput.date .resultDate .endDate').text('～'+next_date);
            // RESET RESULT DATA
            let $result_item = $('.simulation .resultSimu .resultList.resultListSelect li[data-tab="'+tab_num+'"]');
            if( $result_item.length > 0 ) {
                $result_item.find('.date span').text('ー/ー/ー～ー/ー');
                $result_item.find('.area span').text('ーーー');
            }

            // RESET CURRENT DATA
            let current_selected_num = $('.resultCurrent li').attr('data-tab');
            if (current_selected_num == tab_num) {
                $('.resultCurrent').find('.date span').text('ー/ー/ー～ー/ー');
                $('.resultCurrent').find('.area span').text('ーーー');
            }
            // UPDATE RESULT PRICE
            updatePriceResult();
        }
    });


    // ADD CART ACTION
    $(document).on('click', '.simulation .resultContent .resultSubmit .addCartButton', function(){
        let data_addcart = getCountriesDataAddCart();
        if( data_addcart.length > 0 ) {
            data_addcart = JSON.stringify(data_addcart);
            // AJAX POST CART
            $simuLoader.stop().fadeIn(100);
            $.ajax({
                type : "post",
                dataType : "json",
                url : 'add_products',
                data : {
                    'cart_data' : data_addcart
                },
                // context: this,
                beforeSend: function(){},
                success: function(response) {
                    if( response == 'success' ) {
                        // let data_response = JSON.parse(response.data);
                        // if( data_response.status && data_response.data.cart_id ) {
                            // let cart_id = data_response.data.cart_id;
                            window.location.href = $('#shopping_option').val();
                        // }
                    };
                    $simuLoader.stop().fadeOut(100);
                },
                error: function( jqXHR, textStatus, errorThrown ) {
                    console.log( 'The following error occured: ' + textStatus, errorThrown );
                    $simuLoader.stop().fadeOut(100);
                }
            });
        }
    });

    
    // FUNCTIONS


    // GET COUNTRIES HTML BY AREA
    function getCountriesHtmlByArea( area_name ) {
        if( area_name ) {
            let countries_data_html = '';
            let countries_data_str = localStorage.getItem(clstore_countries_name);
            let countries_data = JSON.parse(countries_data_str);
            let countries_data_area = [];
            for (var i = 0; i < countries_data.length; i++) {
                let countrie_price = countries_data[i].price;
                countrie_price = (countrie_price) ? countrie_price : '0';
                if( countries_data[i].continent == area_name ) {
                    let data_item = {
                        name : countries_data[i].country_name_jp,
                        code : countries_data[i].country_code,
                        price : countrie_price
                    };
                    countries_data_area.push(data_item);
                }
            }

            if( countries_data_area ) {
                countries_data_area = getArrayChunk(countries_data_area, 3);
                for (var i = 0; i < countries_data_area.length; i++) {
                    countries_data_html += '<ul class="countryNameList">';
                    for (var j = 0; j < countries_data_area[i].length; j++) {
                        let countries_data_col = countries_data_area[i][j];
                        let countrie_price = countries_data_col.price;
                        countrie_price = (countrie_price) ? countrie_price : '0';
                        let countrie_price_fm = stringToPrice(countrie_price);
                        countries_data_html += '<li><a href="javascript:void(0);" data-price="'+countrie_price+'" data-name="'+countries_data_col.name+'" data-code="'+countries_data_col.code+'"><span class="name">・'+countries_data_col.name+'</span><span class="price">¥<label class="num">'+countrie_price_fm+'</label></span></a></li>';
                    }
                    countries_data_html += '</ul>';
                }
            }

            return countries_data_html;
        }
        return '';
    }


    // UPDATE COUNTRIE DATA FOR ACTION
    function updateCountrieData( countrie_data ) {
        if( countrie_data ) {
            let rent_price = 0;
            let $dom_datecount = $('.simulation .simuItem.actived .country_data[name="dater_count"]');
            let $dom_price = $('.simulation .simuItem.actived .country_data[name="country_price"]');
            let date_count = (countrie_data.date_count) ? countrie_data.date_count : 0;
            let price = (countrie_data.price) ? countrie_data.price : 0;
            let tab_active = $('.simulation .simuItem.actived').attr('data-tab');
            let result_active = $('.simulation .resultSimu .resultList.resultCurrent li').eq(0).attr('data-tab');

            // GET CURRENT DATA
            if( date_count <= 0 && $dom_datecount.val() ) {
                date_count = $dom_datecount.val();
            }
            if( !price && $dom_price.val() ) {
                price = $dom_price.val();
            }

            if( countrie_data.country_name ) {
                $('.simulation .simuItem.actived .country_data[name="country_name"]').val(countrie_data.country_name);
            }
            if( countrie_data.country_code ) {
                $('.simulation .simuItem.actived .country_data[name="country_code"]').val(countrie_data.country_code);
            }
            if( countrie_data.date_start ) {
                $('.simulation .simuItem.actived .country_data[name="dater_start"]').val(countrie_data.date_start);
            }
            if( countrie_data.date_end ) {
                $('.simulation .simuItem.actived .country_data[name="dater_end"]').val(countrie_data.date_end);
            }
            if( countrie_data.productclass_id ) {
                $('.simulation .simuItem.actived .country_data[name="productclass_id"]').val(countrie_data.productclass_id);
            }
            if( countrie_data.date_count ) {
                $dom_datecount.val(countrie_data.date_count);
            }
            if( price || date_count ) {
                date_count = (date_count) ? parseInt(date_count) : 0;
                price = (price) ? parseInt(price) : 0;
                
                if (!isNaN(price)) {
                    rent_price = price * date_count;
                }
                $('.simulation .simuItem.actived .simuOption .simuPrice .numPrice').text( stringToPrice(rent_price) );
                $dom_price.val(price);
            }

            // UPDATE RESULT SELECTER
            updateResultSelecter(countrie_data);

            // UPDATE RESULT DATA
            updatePriceResult();
        }
    }


    // UPDATE RESULT SELECTER
    function updateResultSelecter( countrie_data ) {
        let current_tab = $('.simulation .simuItem.actived').attr('data-tab');
        if( countrie_data && current_tab ) {
            let $item_result = $('.simulation .simuContent .resultSimu .resultList li[data-tab="'+current_tab+'"]');
            if( countrie_data.date_start && countrie_data.date_end ) {
                let date_start = countrie_data.date_start;
                let date_end = countrie_data.date_end;
                let date_result = date_start + '～' + date_end.substring(5);
                $item_result.find('.date span').text(date_result);
            }
            if( countrie_data.country_name ) {
                $item_result.find('.area span').text(countrie_data.country_name);
            }
        }
    }


    // UPDATE PRICE RESULT SIDE
    function updatePriceResult( result_data ) {
        var total_price = 0;
        var total_rent_price = 0;
        // GET TOTAL ALL TAB
        $('.simulation .simuItem').each(function(index){
            let tab_num = $(this).attr('data-tab');
            let date_count = $(this).find('.country_data[name="dater_count"]').val();
            let price = $(this).find('.country_data[name="country_price"]').val();
            if( !isNaN(price) ) {
                date_count = (date_count) ? parseInt(date_count) : 0;
                price = (price) ? parseInt(price) : 0;
                let rental_price = parseInt(date_count) * rental_fee;
                price = parseInt(date_count) * price;
                total_price += price;
                total_rent_price += rental_price;
            }
        });

        // SHOW PRICE RESULT
        $('.simulation .resultContent .resultPrice .communi .numPrice label').text( stringToPrice(total_price) );
        $('.simulation .resultContent .resultPrice .rental .numPrice label').text( stringToPrice(total_rent_price) );
        $('.simulation .resultContent .totalPrice .numPrice label').text( stringToPrice(total_price + total_rent_price) );
    }


    // UPDATE COUNTRIES NUMBER
    function updateCountriesNumTotal() {
        let total_num_res = '渡航国 ① ②';
        var tab_num_icons = '';
        $('.simulation .simuItem').each(function(index){
            let tab_num = $(this).attr('data-tab');
            if( tab_num.length > 0 && tab_number_icon[tab_num] ) {
                tab_num_icons += ' ' + tab_number_icon[tab_num];
            }
        });
        if( tab_num_icons.length > 0 ) {
            total_num_res = '渡航国' + tab_num_icons;
        }
        $('.simulation .totalPrice .countriesTabShow').text(total_num_res);
    }


    // ADD TAB
    function addSimuTab() {
        let data_tab_clone = $('.simulation .simuItemClone').html();
        let count_tab = $('.simulation .simuTab .listTab li').length;
        if( data_tab_clone && count_tab < 4 ) {
            // ADD TAB HEAD
            let newtab_num = count_tab + 1;
            let newtab_icon = (tab_number_icon[newtab_num.toString()]) ? tab_number_icon[newtab_num.toString()] : '②';
            let newtab_html = '<li class="added"><a href="javascript:void(0);" data-tab="'+newtab_num+'">渡航国'+newtab_icon+'</a><span class="delete">×</span></li>'
            $('.simulation .simuTab .listTab').append(newtab_html);

            // ADD TAB CONTENT
            data_tab_clone = data_tab_clone.replace('{TAB}', newtab_num);
            let new_id_daterange = 'daterange' + newtab_num;
            data_tab_clone = data_tab_clone.replace('{ID_DATERANGE}', new_id_daterange);
            data_tab_clone = data_tab_clone.replace('{COUNTRY_NAME}', '');
            data_tab_clone = data_tab_clone.replace('{COUNTRY_NAME1}', '');
            data_tab_clone = data_tab_clone.replace('{COUNTRY_CODE}', '');
            data_tab_clone = data_tab_clone.replace('{COUNTRY_PRICE}', '');
            data_tab_clone = data_tab_clone.replace('{DATER_COUNT}', '');
            data_tab_clone = data_tab_clone.replace('{START_DATE}', '');
            data_tab_clone = data_tab_clone.replace('{START_DATE1}', '');
            data_tab_clone = data_tab_clone.replace('{END_DATE}', '');
            data_tab_clone = data_tab_clone.replace('{END_DATE1}', '');
            data_tab_clone = data_tab_clone.replace('{PRODUCTCLASS_ID}', '');
            data_tab_clone = data_tab_clone.replace('②', newtab_icon);
            $('.simulation .simuContent .simuLeft').append(data_tab_clone);
            setUpDaterangePicker('#'+new_id_daterange);
            datePickerSelected('#'+new_id_daterange);

            // ADD RESULT ITEM
            let res_item_html = '<li data-tab="'+newtab_num+'">';
            res_item_html += '<p class="name">渡航国'+newtab_icon+'</p>';
            res_item_html += '<div class="info">';
            res_item_html += '<p class="date">期間：<span>ー/ー/ー～ー/ー</span></p>';
            res_item_html += '<p class="area">国名：<span>ーーー</span></p>';
            res_item_html += '</div>';
            res_item_html += '</li>';
            $('.simulation .resultContent .resultSimu .resultList.resultListSelect').append(res_item_html);

            // UPDATE COUNTRIES NUMBER TOTAL
            updateCountriesNumTotal();
        }
        if( count_tab >= 3 ) {
            $('.simulation .simuTab .addTab').hide();
        }
    }


    // ADD TAB WITH CART ITEM
    function addSimuTabWithCartItem(productName, rental, total, productclass_id) {
        let contents = productName.split('_');
        let data_tab_clone = $('.simulation .simuItemClone').html();
        let count_tab = $('.simulation .simuTab .listTab li').length;
        if( data_tab_clone && count_tab < 4 ) {
            // ADD TAB HEAD
            let newtab_num = count_tab + 1;
            let newtab_icon = (tab_number_icon[newtab_num.toString()]) ? tab_number_icon[newtab_num.toString()] : '②';
            let newtab_html = '<li class="added"><a href="javascript:void(0);" data-tab="'+newtab_num+'">渡航国'+newtab_icon+'</a><span class="delete">×</span></li>'
            $('.simulation .simuTab .listTab').append(newtab_html);

            // ADD TAB CONTENT
            data_tab_clone = data_tab_clone.replace('{TAB}', newtab_num);
            let new_id_daterange = 'daterange' + newtab_num;
			let date_counter = (total - rental) / rental_fee;
            data_tab_clone = data_tab_clone.replace('{ID_DATERANGE}', new_id_daterange);
            data_tab_clone = data_tab_clone.replace('{COUNTRY_NAME}', contents[0]);
            data_tab_clone = data_tab_clone.replace('{COUNTRY_NAME1}', contents[0]);
            data_tab_clone = data_tab_clone.replace('{START_DATE}', '20'+contents[1]);
            data_tab_clone = data_tab_clone.replace('{START_DATE1}', '20'+contents[1]);
            data_tab_clone = data_tab_clone.replace('{END_DATE}', contents[2].substring(3));
            data_tab_clone = data_tab_clone.replace('{END_DATE1}', '20'+contents[2]);
            data_tab_clone = data_tab_clone.replace('{COUNTRY_CODE}', contents[3]);
            data_tab_clone = data_tab_clone.replace('{DATER_COUNT}', date_counter);
            data_tab_clone = data_tab_clone.replace('{COUNTRY_PRICE}', (rental / date_counter).toString().replace('.00', ''));
            data_tab_clone = data_tab_clone.replace('ーーー', stringToPrice(rental.toString().replace('.00', '')));
            data_tab_clone = data_tab_clone.replace('{PRODUCTCLASS_ID}', productclass_id);
            
            $('.simulation .simuContent .simuLeft').append(data_tab_clone);
            setUpDaterangePicker('#'+new_id_daterange);
            datePickerSelected('#'+new_id_daterange);

            // ADD RESULT ITEM
            let res_item_html = '<li data-tab="'+newtab_num+'">';
            res_item_html += '<p class="name">渡航国'+newtab_icon+'</p>';
            res_item_html += '<div class="info">';
            res_item_html += '<p class="date">期間：<span>20'+contents[1]+'～'+contents[2].substring(3)+'</span></p>';
            res_item_html += '<p class="area">国名：<span>'+contents[0]+'</span></p>';
            res_item_html += '</div>';
            res_item_html += '</li>';
            $('.simulation .resultContent .resultSimu .resultList.resultListSelect').append(res_item_html);

            // UPDATE COUNTRIES NUMBER TOTAL
            updateCountriesNumTotal();
        }
        if( count_tab >= 3 ) {
            $('.simulation .simuTab .addTab').hide();
        }
    }


    // REMOVE SIMU TAB
    function removeSimuTab( tab_num ) {
        if( tab_num ) {
            tab_num = parseInt(tab_num);
            let count_tab = $('.simulation .simuTab .listTab li').length;
            let $tab_content = $('.simulation .simuItem[data-tab="'+tab_num+'"]');
            let productclass_id = $tab_content.find('.country_data[name="productclass_id"]').val();
            if (productclass_id) {
                $simuLoader.stop().fadeIn(100);
                $.ajax({
                    url: '/delete_cartitem',
                    method: 'post',
                    data: {
                      'productclass_id': productclass_id
                    },
                    success: function(response) {
                        $simuLoader.stop().fadeOut(100);
                    },
                    error: function( jqXHR, textStatus, errorThrown ) {
                        console.log( 'The following error occured: ' + textStatus, errorThrown );
                        $simuLoader.stop().fadeOut(100);
                    }
                });
            }
            if( $tab_content.hasClass('actived') ) {
                $('.simulation .simuTab .listTab li a[data-tab="1"]').parent().addClass('active');
                $('.simulation .simuItem[data-tab="1"]').addClass('actived');
            }
            // REMOVE TAB
            $('.simulation .simuTab .listTab li a[data-tab="'+tab_num+'"]').parent().remove();
            $tab_content.remove();
            $('.simulation .simuTab .addTab').stop().fadeIn(100);
            // REMOVE RESULT
            let $result_item = $('.simulation .resultSimu .resultList.resultListSelect li[data-tab="'+tab_num+'"]');
            let result_has_active = $result_item.hasClass('selected');
            $result_item.remove();
            // RESET TAB NUM
            if( tab_num < count_tab ) {
                for (var i = tab_num + 1; i <= count_tab; i++) {
                    let prev_tabnum = i - 1;
                    let $tab_head = $('.simulation .simuTab .listTab li a[data-tab="'+i+'"]');
                    let $tab_content = $('.simulation .simuItem[data-tab="'+i+'"]');
                    let $result_item = $('.simulation .resultSimu .resultList.resultListSelect li[data-tab="'+i+'"]');
                    let newtab_icon = (tab_number_icon[prev_tabnum.toString()]) ? tab_number_icon[prev_tabnum.toString()] : '②';
                    // TAB HEAD
                    $tab_head.attr('data-tab', prev_tabnum);
                    $tab_head.text('渡航国'+newtab_icon);
                    // TAB CONTENT
                    $tab_content.attr('data-tab', prev_tabnum);
                    let new_id_daterange = 'daterange' + prev_tabnum;
                    $tab_content.find('.simuInput.date input[name="daterange"]').attr('id', new_id_daterange );
                    $tab_content.find('.countryShowPrice p[class="textPrice"]').text('渡航国'+newtab_icon+'通信料');
                    // RESULT SELECT
                    $result_item.attr('data-tab', prev_tabnum);
                    $result_item.find('.name').text('渡航国'+newtab_icon);
                }
            }
            if( result_has_active ) {
                let tab_active_num = $('.simulation .simuItem.actived').attr('data-tab');
                if( tab_active_num ) {
                    let $result_item_active = $('.simulation .resultSimu .resultList.resultListSelect li[data-tab="'+tab_active_num+'"]');
                    let result_item_html = $result_item_active.prop('outerHTML');
                    $('.simulation .resultSimu .resultList.resultCurrent').html(result_item_html);
                    $result_item_active.closest('.resultListSelect').find('li').removeClass('selected');
                    $result_item_active.addClass('selected');
                }
            }
            // UPDATE RESULT PRICE
            updatePriceResult();
            // UPDATE COUNTRIES NUMBER TOTAL
            updateCountriesNumTotal();
        }
    }


    // GET COUNTRIES DATA ADD CART
    function getCountriesDataAddCart() {
        let count_tab = $('.simulation .simuTab .listTab li').length;
        let addcart_data = [];
        if( count_tab > 0 ) {
            for (var tab_num = 1; tab_num <= count_tab; tab_num++) {
                let $tab_item = $('.simulation .simuItem[data-tab="'+tab_num+'"]');
                if( $tab_item.length > 0 ) {
                    let country_code = $tab_item.find('.country_data[name="country_code"]').val();
                    let country_name = $tab_item.find('.country_data[name="country_name"]').val();
                    let date_start = $tab_item.find('.country_data[name="dater_start"]').val();
                    let date_end = $tab_item.find('.country_data[name="dater_end"]').val();
                    let date_count = $tab_item.find('.country_data[name="dater_count"]').val();
                    let country_price = $tab_item.find('.country_data[name="country_price"]').val();
                    let productclass_id = $tab_item.find('.country_data[name="productclass_id"]').val();
                    if( country_code && date_start && date_end ) {
                        // date_start = date_start.replaceAll('/', '-');
                        // date_end = date_end.replaceAll('/', '-');
                        let cart_item = {
                            from_date : date_start,
                            to_date : date_end,
                            date_count : date_count,
                            country_code : country_code,
                            country_name : country_name,
                            country_price : country_price,
                            productclass_id : productclass_id
                        };
                        addcart_data.push(cart_item);
                    }
                }
            }
        }
        return addcart_data;
    }


    // SETUP DATERANGE PICKER
    function setUpDaterangePicker( tabdate_id ) {
        if( tabdate_id ) {
            var current_date = new Date();
            if( $(window).width() <= 700 ) {
                // SETUP MOBILE
                $(tabdate_id).daterangepicker({
                    "startDate": current_date,
                    "minDate": current_date,
                    "opens": "center",
                    "linkedCalendars": false,
                    "locale": {
                        "format": "MM/DD/YYYY",
                        "separator": " ~ ",
                        "applyLabel": "決定",
                        "daysOfWeek": [
                            "日",
                            "月",
                            "火",
                            "水",
                            "木",
                            "金",
                            "土"
                        ],
                        "monthNames": [
                            "1月",
                            "2月",
                            "3月",
                            "4月",
                            "5月",
                            "6月",
                            "7月",
                            "8月",
                            "9月",
                            "10月",
                            "11月",
                            "12月"
                        ],
                        "firstDay": 1
                    },
                });
            } else {
                // SETUP DESKTOP
                $(tabdate_id).daterangepicker({
                    "startDate": current_date,
                    "minDate": current_date,
                    "opens": "center",
                    "locale": {
                        "format": "MM/DD/YYYY",
                        "separator": " ~ ",
                        "applyLabel": "決定",
                        "daysOfWeek": [
                            "日",
                            "月",
                            "火",
                            "水",
                            "木",
                            "金",
                            "土"
                        ],
                        "monthNames": [
                            "1月",
                            "2月",
                            "3月",
                            "4月",
                            "5月",
                            "6月",
                            "7月",
                            "8月",
                            "9月",
                            "10月",
                            "11月",
                            "12月"
                        ],
                        "firstDay": 1
                    },
                });
            }
            
        }
    }


    // STRING TO PRICE
    function stringToPrice( str ) {
        if( str ) {
            str = str.toString();
            return str.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        }
        return '0';
    }


    // ARRAY CHUNK
    function getArrayChunk( array_data, chunkSize ) {
        chunkSize = (chunkSize) ? chunkSize : 1;
        let array_length = array_data.length;
        let count_item = 1;
        if( chunkSize < array_length ) {
            count_item = Math.ceil(array_length / chunkSize);
        }
        let array_res = [];
        for (let i = 0; i < array_data.length; i += count_item) {
            let chunk = array_data.slice(i, i + count_item);
            array_res.push(chunk);
        }
        return array_res;
    }


    // DIFF DATE
    function dateDiffIndays(d1, d2) {
        var diff = Date.parse(d2) - Date.parse(d1);
        return Math.floor(diff / 86400000);
    }


    // SET COOKIE
    function setCookie(cname, cvalue) {
        document.cookie = cname + "=" + cvalue + ";path=/";
    }


    // GET COOKIE
    function getCookie(cname) {
        let name = cname + "=";
        let ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

});