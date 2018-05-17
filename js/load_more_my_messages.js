/**
 * Created by Abhishek Kumar Sinha on 9/27/2017.
 */

$(document).ready(function(){
    var loading = false;
   
    $total_records = getTotalMyMessages();

    $records_per_page = 50;
    $number_of_pages = Math.ceil($total_records / $records_per_page);
    $current_page = 1;
    $start=($current_page*$records_per_page)-$records_per_page;

    loadMoreMyMessagesInitial();

    $current_page = 2;

    $(window).scroll(function() {
      //  console.log($(window).scrollTop() + window.innerHeight, $(document).height() - 35);
        if($(window).scrollTop() + window.innerHeight > $(document).height()-5) {
            $start = ($current_page*$records_per_page)-$records_per_page;
            if($current_page <= $number_of_pages && loading == false) {
                loadMoreMyMessages($start,$records_per_page);
                $current_page++;
            }
        }
    });


});

function getTotalMyMessages() {
    $total_records = 0;
    var job = 'total_my_messages';
    $.ajax({
        url: "ajax/MyMessagesTotal.php",
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

function loadMoreMyMessagesInitial() {
    loadMoreMyMessages($start,$records_per_page);
}

function loadMoreMyMessages($start,$records_per_page) {
    var loading = true;
    $.ajax({
        url: "ajax/loadMoreMyMessages.php",
        type:"post",
        data: {req: 'loadMoreMyMessages', records_per_page: $records_per_page, start: $start},
        beforeSend: function(){
            $('.ajax-loader-span').html("<img src='images/spinner.gif' class='ajax-loader'>");
        },
        complete: function() {
            $('.ajax-loader-span').remove();
        },
        success: function(data) {
            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch(err) {
                IS_JSON = false;
            }
            if(IS_JSON) {
                var v = '0 message';
                if(isArray(d.msg) && d.msg.length != 0) {
                    v = '';
                    var si = 0;
                    for (var k=0; k<= d.msg.length-1; k++) {
                        si = k+1;
                        v += '<tr>';
                        v += '<td>'+d.msg[k].order_id+'</td>';
                        v += '<td>'+d.msg[k].messages+'</td>';
                        v += '<td>'+my_date_format(d.msg[k].datetime)+'</td>';
                        v += '</tr>';
                    }
                }
                $("#myMessagesTable").append(v);
            }
        },
        error: function(xhr) {
            //console.log(xhr);
        }
    });
    loading = false;
}