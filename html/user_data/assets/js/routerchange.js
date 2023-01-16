/* カスタマイズ用Javascript */
var flatpickrdate = $('#flatpickrdate');
var minDate = new Date();
var all_days = 0;
var form_index = 1;
var option_index = 0;

// minDate.setDate(minDate.getDate() + 1);

const fpdate = flatpickrdate.flatpickr({
    inline: true,
    mode: "range",
    minDate: minDate,
    showMonths: 2,
    dateFormat: "Y-m-d",
    locale: "ja",
    onChange: function(selectedDates, dateStr, instance) {
        var dates;
        if (dateStr.includes('～')) {
          dates = dateStr.split('～');
        } else if (dateStr.includes('から')) {
          dates = dateStr.split('から');
        }
        if (dates != undefined){
            sdate = dates[0].trim().replaceAll("-","/");
            edate = dates[1].trim().replaceAll("-","/");
            $(".router_travelitem_"+form_index).html("期間: " + sdate + "〜" + edate + "<br>国名:" + $("#order_country_"+form_index).val());
            $(".simulation_travelitem_"+form_index).html("期間: " + sdate + "〜" + edate + "<br>国名:" + $("#order_country_"+form_index).val());
            $("#order_startdate_"+form_index).val(sdate);
            $("#order_enddate_"+form_index).val(edate);
            calcAllDays();
            calculatePrice($("#order_country_code_"+form_index).val(), $("#order_startdate_"+form_index).val(), $("#order_enddate_"+form_index).val());
        }
    },
});
calcAllDays();

// 日時数を計算
function calcAllDays(){
    all_days = 0;
    for(var i = 1; i <= 5; i++){
        if ($("#order_startdate_"+i).val() != undefined){
            var dt1 = new Date($("#order_startdate_"+i).val());
            dt1.setDate(dt1.getDate() + 1);
            var dt2 = new Date($("#order_enddate_"+i).val());
            dt2.setDate(dt2.getDate() + 1);
            var days = (dt2.getTime() - dt1.getTime()) / (1000 * 3600 * 24);
            $("#order_quantity_"+i).val(days);
            all_days = all_days + days;
        }
    }
}

// 画面ロード時に渡航国①を選択
$(".router--change .router--block .travellist a:first-child").addClass("selected");

form_load(1);
function form_load(selindex){
    form_index = selindex;

    // 渡航国①の期間を設定
	var dt1 = new Date($("#order_startdate_"+selindex).val());
    if (dt1.getTime() >= minDate.getTime()) {
        fpdate.setDate([$("#order_startdate_"+selindex).val(), $("#order_enddate_"+selindex).val()], true);
    } else {
        fpdate.setDate([minDate, $("#order_enddate_"+selindex).val()], true);
    }
    $("#country_select option[value='" + $("#order_country_code_"+selindex).val() + "']").prop('selected', true);
    
    // 画面ロード時に安全保障プランを選択
    option_index = 3;
    for(var i = 1; i < 3; i++){
        if ($("#option_order_product_id").val() == $("#option_product_id"+i).val()){
            option_index = i;
        }
    }
    $("#optionselection"+option_index).prop('checked',true);
    $(".insur_title").text("安⼼補償料（"+numberWithCommas($("#option_product_price"+option_index).val().replace(".00", ""))+"円/⽇）");
    
    calcPrice();
    calcSum();
}

// 渡航国を選択する際に
$(".router--change .router--block .travellist a").click(function() {
    $( ".router--change .router--block .travellist a" ).each(function( index ) {
        $(this).removeClass("selected");
    });
    $(this).addClass("selected");
    // 期間を設定
    
    form_load($(this).attr("data"));
});

$(".boxselect .boxselect--input input").click(function() {
    option_index = $(this).attr('data');
    $(".insur_title").text("安⼼補償料（"+numberWithCommas($("#option_product_price"+option_index).val().replace(".00", ""))+"円/⽇）");
    $(".insur_price").text(numberWithCommas($("#option_product_price"+option_index).val()*all_days) + "円");
    $("#order_insure_price").val($("#option_product_price"+option_index).val()*all_days);

    $("#option_order_product_id").val($("#option_product_id"+option_index).val());
    $("#option_order_product_price").val($("#option_product_price"+option_index).val().replace(".00", ""));

    calcSum();
});

// 金額を計算
function calcPrice(){
    // 画面ロード時にレンタル料、安⼼補償料、オプション品料を表示
    $(".rental_price").text(numberWithCommas(220*all_days) + "円");
    $("#order_rental_price").val(220*all_days);
    $(".insur_price").text(numberWithCommas($("#option_product_price"+option_index).val()*all_days) + "円");
    $("#order_insure_price").val($("#option_product_price"+option_index).val()*all_days);

    if ($("#option_product_price4").val() != undefined){
        $(".option_price").text(numberWithCommas($("#option_product_price4").val()*all_days) + "円");
        $("#order_option_price").val($("#option_product_price4").val()*all_days);
    }else{
        $(".option_price").text("0円");
        $("#order_option_price").val(0);
    }

    // 画面ロード時に通信料を表示
    $("#des_price").text(numberWithCommas($("#order_price_"+form_index).val().replace(".00","")) + "円");
}

// 合計金額を計算
function calcSum(){
    // 合計金額 = レンタル料(220円)*日 + 安⼼補償料*日 + オプション品料(アダプター)*日 + オプション品料(ボックス)
    // 合計金額 = 合計金額 + 通信料1 + 通信料2 + 通信料3 + 通信料4
    var sum_price = parseInt($("#order_rental_price").val())+parseInt($("#order_insure_price").val())+parseInt($("#order_option_price").val());
    if ($("#option_product_price5").val() != undefined){
        sum_price = sum_price + parseInt($("#option_product_price5").val());
    }
    for(var i = 1; i <= 5; i++){
        if ($("#order_price_"+i).val() != undefined){
            sum_price = sum_price + parseInt($("#order_price_"+i).val());
        }
    }
    $(".sum_price").text(numberWithCommas(sum_price) + "円 （税込）");
	$("#order_sub_total").val(sum_price);
}

// 金額の表示形式を変換
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
$( "#continents_select" ).change(function() {
    let clickedOption = $(this).val();
    if (clickedOption != '*') {
      getCountryNames(clickedOption);
    }
});
$( "#country_select" ).change(function() {
    var $select = $("#country_select");
    var country = $select[0].value;
    $("#order_country_"+form_index).val($("#country_select option:selected").text());
    $("#order_country_code_"+form_index).val(country);
    $(".router_travelitem_"+form_index).html("期間: " + $("#order_startdate_"+form_index).val() + "〜" + $("#order_enddate_"+form_index).val() + "<br>国名:" + $("#order_country_"+form_index).val());
    $(".simulation_travelitem_"+form_index).html("期間: " + $("#order_startdate_"+form_index).val() + "〜" + $("#order_enddate_"+form_index).val() + "<br>国名:" + $("#order_country_"+form_index).val());
    calculatePrice(country, $("#order_startdate_"+form_index).val(), $("#order_enddate_"+form_index).val());
});


function getCountryNames(name) {
    $.ajax({
        url: "https://kw.api.gonosen.work/api/countries?continent=" + name,
        type: 'GET',
        crossDomain: true,
        dataType: 'json',
        headers: {
            'Content-type': 'application/json'
        },
        success:function(data){
            updateCountryName(data.data.countries);
        }

    });
}

function updateCountryName(country_arr) {
    var str_select = '#country_select';
    // var jsonData = JSON.parse(arr[0]);
    // var $select = $(str_select);
    
    var $select = $(str_select);
    var options = "";
    for (var i = country_arr.length - 1; i >= 0; i--) {
       options += '<option value="'+country_arr[i].country_code+'">'+country_arr[i].country_name_jp+'</option>';
    }

    // $select[0].innerHTML = options;
    $select.html(options);
    if (country_arr.length > 0){
        $("#order_country_"+form_index).val(country_arr[country_arr.length - 1].country_name_jp);
        $("#order_country_code_"+form_index).val(country_arr[country_arr.length - 1].country_code);
        $(".router_travelitem_"+form_index).html("期間: " + $("#order_startdate_"+form_index).val() + "〜" + $("#order_enddate_"+form_index).val() + "<br>国名:" + $("#order_country_"+form_index).val());
        $(".simulation_travelitem_"+form_index).html("期間: " + $("#order_startdate_"+form_index).val() + "〜" + $("#order_enddate_"+form_index).val() + "<br>国名:" + $("#order_country_"+form_index).val());
        calculatePrice(country_arr[country_arr.length - 1].country_code, $("#order_startdate_"+form_index).val(), $("#order_enddate_"+form_index).val());
    }
}
function calculatePrice(country, start, end) {
    start = start.replaceAll("/", "-");
    end = end.replaceAll("/", "-");
    $.ajax({
        url: "https://kw.api.gonosen.work/api/countries/price",
        method: 'post',
        crossDomain: true,
        dataType: 'json',
        data: {
          'data': [
            {
              'country_code': country,
              'from_date': start,
              'to_date': end
            },
          ]
        },
        success: function(data){
            des_price = data.data[0].total_price;
            $("#order_price_"+form_index).val(des_price);
            calcPrice();
            calcSum();
            var dt1 = new Date($("#order_startdate_"+form_index).val());
            dt1.setDate(dt1.getDate() + 1);
            var dt2 = new Date($("#order_enddate_"+form_index).val());
            dt2.setDate(dt2.getDate() + 1);
            // saveProduct($("#order_productid_"+form_index).val(), form_index, $("#order_country_"+form_index).val(), country, des_price, dt1.getTime(), dt2.getTime());
        }
    });
}

function saveProduct(product_id, index, country, country_code, price, start, end) {
    $('.buttonsubmit').prop('disabled', true);
    $.ajax({
        url: ajax_product_save_url,
        method: 'post',
        data: {
            'product_id': product_id,
            'index': index,
            'country': country,
            'country_code': country_code,
            'from': start,
            'to': end,
            'price': price
        },
        success: function(data){
            $('.buttonsubmit').prop('disabled', false);
            if (data != 'fail') {
                var str_elm = '#productclass_id_' + index;
                var $elm = $(str_elm);
                $elm.val(data);
            }
        },
        error: function(err) {
            $('.buttonsubmit').prop('disabled', false);
        }
    });
    }

// MAP AREA CLICK
$( ".map .map--images .mapButton .btn a" ).click(function() {
    let conti_name = $(this).attr('data-conti-name');
    let conti_code = $(this).attr('data-conti-code');
    let countries_data_html = '';
    if( conti_name ) {
        getCountryNames(conti_code);
    }
});

$(document).ready(function () {
    $(document).on('mouseover mouseout', '.map .map--images .mapButton .btn a', function() {
        var area_id = $(this).attr('data-svg');
        var $group = $('.mapArea').find('g[data-group="'+area_id+'"]');
        // console.log($('.mapArea').find('g[data-group="'+area_id+'"]'));
        $group.attr('class', function(index, attr) {
            return attr == 'inActive' ? null : 'inActive';
        });
    });

	// $('.list_item').css('flex', '0 0 (100/{{ ProductNum }})%');
	var product_num = $('#product_num').val();
	if ( product_num > 5) {
		$('.list_item').css('flex', '0 0 17%');
	} else if ( product_num == 5 ) {
		$('.list_item').css('flex', '0 0 20%');
	} else if ( product_num == 4 ) {
		$('.list_item').css('flex', '0 0 25%');
	} else if ( product_num == 3 ) {
		$('.list_item').css('flex', '0 0 34%');
	} else if ( product_num == 2 ) {
		$('.list_item').css('flex', '0 0 50%');
	} else {
		$('.list_item').css('flex', '0 0 100%');
	}
    
});