/**
 * Created by Abhishek Kumar Sinha on 9/27/2017.
 */

$(document).ready(function(){
    var loading = false;
   
    $total_records = getTotalMyOrders();

    $records_per_page = 50;
    $number_of_pages = Math.ceil($total_records / $records_per_page);
    $current_page = 1;
    $start=($current_page*$records_per_page)-$records_per_page;

    loadMoreMyOrdersInitial();

    $current_page = 2;

    $(window).scroll(function() {
        if($(window).scrollTop() + window.innerHeight > $(document).height()-5) {
            $start = ($current_page*$records_per_page)-$records_per_page;
            if($current_page <= $number_of_pages && loading == false) {
                loadMoreMyOrders($start,$records_per_page);
                $current_page++;
            }
        }
    });
});

function getTotalMyOrders() {
    $total_records = 0;
    var job = 'total_my_orders';
    $.ajax({
        url: "ajax/MyOrdersTotal.php",
        data: {job: job},
        async: false,
        type:"post",
        success: function(data) {
            $total_records = data;
        },
        error: function(xhr) {
            $total_records = 0;
        }
    });
    return $total_records;
}

function loadMoreMyOrdersInitial() {
    loadMoreMyOrders($start,$records_per_page);
}

function loadMoreMyOrders($start,$records_per_page) {
    var loading = true;
    $.ajax({
        url: "ajax/loadMoreMyOrders.php",
        type:"post",
        data: {req: 'loadMoreMyOrders', records_per_page: $records_per_page, start: $start},
        beforeSend: function(){
            $('.ajax-loader-span').html("<img src='images/spinner.gif' class='ajax-loader'>");
        },
        complete: function() {
            $('.ajax-loader-span').remove();
        },
        success: function(data) {
            if ($.trim(data) != '' || $.trim(data) != undefined) {
                $('#view_all_orders_tb').append(data);
            }
        },
        error: function(xhr) {
            //console.log(xhr);
        }
    });
    loading = false;
}