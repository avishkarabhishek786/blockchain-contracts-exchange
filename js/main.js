/**
 * Created by Abhishek Kumar Sinha on 5/1/2018.
 */
$(document).ready(function() {
    load_fresh_table_data();
    run_OrderMatchingAlgorithm();
});

$(document).on('click', '#is_mkt', function() {
    $('#ex-price').val('').toggle();
});

$(document).on('click', '#ex-sub', function() {

    var btn = $(this);
    var sel1 = $('#sel-bc-1').val();
    var sel2 = $('#sel-bc-2').val();
    var pr = $('#ex-price').val();
    var qty = $('#ex-qty').val();
    var bs_rad = $("input[name='gridRadios']:checked").val();
    var is_mkt = $('#is_mkt').is(":checked");

    btn.prop( "disabled", true );
    place_order(sel1, sel2, pr, qty, bs_rad, is_mkt, btn);

});

function displayNotice(msg, _type) {
    var v = '<li>'+msg+'</li>';

    switch (_type) {
        case 'success':
            $('#MsgModel').find('ul.msg-ul').removeClass('text-danger text-warning').addClass('text-info').html(v);
            break;
        case 'failure':
            $('#MsgModel').find('ul.msg-ul').removeClass('text-info text-warning').addClass('text-danger').html(v);
            break;
        case 'warning':
            $('#MsgModel').find('ul.msg-ul').removeClass('text-danger text-info').addClass('text-warning').html(v);
            break;
        default:
            $('#MsgModel').find('ul.msg-ul').removeClass('text-danger text-warning').addClass('text-info').html(v);
    }

    $('#MsgModel').modal('toggle');
}

function place_order(sel1, sel2, pr, qty, bs_rad, is_mkt, btn) {
    var subject = 'placeOrder';
    $.ajax({
        method: 'post',
        url: 'ajax/pending_orders.php',
        data: { subject:subject, sel1:sel1, sel2:sel2, price:pr, qty:qty, bs_rad:bs_rad, is_mkt:is_mkt},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function(data) {
            console.log(data);
            btn.prop( "disabled", false);
            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch(err) {
                IS_JSON = false;
            }

            if(IS_JSON) {
                if(d.error == true) {
                    $msg  = d.msg;
                    displayNotice($msg, "failure");
                } else if(d.order != null && d.order.error == true && d.order.message != null) {
                    displayNotice(d.order.message, "failure");
                } else if(d.user == '') {
                    displayNotice('There was a problem in identifying the user.', "failure");
                } else {
                    $('#empty_msg').hide();
                    var trade = "";
                    if($.trim(bs_rad)=="ex-buy") {
                        trade = "buy";
                    } else if ($.trim(bs_rad)=="ex-sell") {
                        trade = "sell";
                    }
                    displayNotice('You entered a '+trade+' order for '+qty + ' ' + sel1+ ' at '+pr+ ' '+sel2+'.', "success");
                    run_OrderMatchingAlgorithm();
                }
            } else {
                displayNotice('Something went wrong. Please contact the administrator.', "failure");
            }
        }
    });
}

function myTimeoutFunction() {
    run_OrderMatchingAlgorithm();
    setTimeout(myTimeoutFunction, 20000);
}

myTimeoutFunction();

// Update tables a/c to change in select
$(document).on('change', ".selbc", function() {
    load_fresh_table_data();
});

// function to check if JSON data is array or not
function isArray(what) {
    return Object.prototype.toString.call(what) === '[object Array]';
}

function load_fresh_table_data() {

    var bc1 = $('#sel-bc-1').val();
    var bc2 = $('#sel-bc-2').val();

    $.ajax({
        method:'post',
        url:'ajax/refresh_table.php',
        data: { task : 'refresh', bc1:bc1, bc2:bc2},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function(data) {

            if(data !== '') {
                var d = jQuery.parseJSON(data);
                console.log(d);
                //get_my_balance();

                var t = '';
                if(isArray(d.buys) && d.buys.length !== 0) {
                 for (var j=0; j<=d.buys.length-1 ; j++) {
                 t += '';
                 t += '<tr id="'+d.buys[j].order_id+'">';
                 t += '<td> '+d.buys[j].name+'</td>';
                 t += '<td> '+d.buys[j].price+'</td>';
                 t += '<td>'+d.buys[j].quantity+'</td>';
                 t += '</tr>';
                 }
                 }
                 $('#bd-buy').html(t);

                 var v = '';
                 if(isArray(d.sells) && d.sells.length !== 0) {
                 for (var k=0; k<=d.sells.length-1 ; k++) {
                 v += '';
                 v += '<tr id="'+d.sells[k].order_id+'">';
                 v += '<td>'+d.sells[k].name+'</td>';
                 v += '<td> '+d.sells[k].price+'</td>';
                 v += '<td>'+d.sells[k].quantity+'</td>';
                 v += '</tr>';
                 }
                 }
                 $('#bd-sell').html(v);
            }
        }
    });

}

function run_OrderMatchingAlgorithm() {

    var sel1 = $('#sel-bc-1').val();
    var sel2 = $('#sel-bc-2').val();

    if($.trim(sel1) == '' || $.trim(sel2) == '') {
        return;
    }

    $.ajax({
        method:'post',
        async: true,
        url:'ajax/OrderMatchingAlgorithmAjax.php',
        data: { task : 'run_OrderMatchingAlgorithm', sel1:sel1, sel2:sel2},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function(data) {

            load_fresh_table_data();

            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch(err) {
                IS_JSON = false;
            }

            if(IS_JSON) {
                if (d.error == false && d.msg=="userLoggedIn") {
                    if (isArray(d.order) && d.order.length != 0) {
                        for (var k = 0; k <= d.order.length - 1; k++) {
                            $.notify({
                                message: d.order[k]
                            },{
                                type: 'success'
                            });
                        }
                    }
                }
            }
        }
    });
}
