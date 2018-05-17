/**
 * Created by Abhishek Kumar Sinha on 5/1/2018.
 */
$(document).ready(function() {
    var sel1 = $('#sel-bc-1').val();
    var sel2 = $('#sel-bc-2').val();
    //load_fresh_table_data(sel1, sel2);
    run_OrderMatchingAlgorithm();
    tradeList();
    MyOrders();
    MyTransactions();
    sel_bc_stats(sel1, sel2);
    user_wallet();
    current_prices();
    load_messages();
});

$(document).on('click', '#is_mkt', function() {
    $('#ex-price').val('').toggle();
});

var my_date_format = function(input){
    var d = new Date(Date.parse(input.replace(/-/g, "/")));
    var month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var date = d.getDate() + " " + month[d.getMonth()] + ", " + d.getFullYear();
    var time = d.toLocaleTimeString().toLowerCase().replace(/([\d]+:[\d]+):[\d]+(\s\w+)/g, "$1$2");
    return (date + " " + time);
};

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
    user_wallet();
    current_prices(sel2);
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
    check_new_orders();
    setTimeout(myTimeoutFunction, 20000);
}

myTimeoutFunction();

function run_all() {
    run_OrderMatchingAlgorithm();
    tradeList();
    tradersList();
    MyOrders();
    MyTransactions();
    load_messages();
}

function check_new_orders() {
    $.ajax({
        method:'post',
        url:'ajax/check_new_orders.php',
        async: true,
        error: function(xhr, status, error) {
            console.log(xhr, status, error);
        },
        success: function(data) {
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                run_all();
            }
        }
    });
}

// Update tables a/c to change in select
$(document).on('change', ".selbc", function() {
    var bc1 = $('#sel-bc-1').val();
    var bc2 = $('#sel-bc-2').val();
    load_fresh_table_data(bc1, bc2);
    tradeList(bc1, bc2);
    tradersList(bc2);
    sel_bc_stats(bc1, bc2);
    current_prices(bc2);
});

// function to check if JSON data is array or not
function isArray(what) {
    return Object.prototype.toString.call(what) === '[object Array]';
}

function load_fresh_table_data(bc1, bc2) {

    $.ajax({
        method:'post',
        url:'ajax/refresh_table.php',
        data: { task : 'refresh', bc1:bc1, bc2:bc2},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function(data) {
            console.log(data);
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

            load_fresh_table_data(sel1, sel2);

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

function tradeList(bc1, bc2) {

    $.ajax({
        method:'post',
        url:'ajax/tradeList.php',
        data: { task : 'loadTradeList',bc1:bc1, bc2:bc2},
        error: function() {
            console.log('error');
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
            var v = '';
            if(isArray(d.trade_list) && d.trade_list.length != 0) {
                for (var k=0; k<= d.trade_list.length-1; k++) {
                    v += '';
                    v += '<tr>';
                    v += '<td>'+d.trade_list[k].SELLER+'</td>';
                    v += '<td>'+d.trade_list[k].BUYER+'</td>';
                    v += '<td>'+d.trade_list[k].TRADE_PRICE+'</td>';
                    v += '<td>'+d.trade_list[k].TRADED_QTY+'</td>';
                    v += '<td>'+(d.trade_list[k].TRADED_QTY * d.trade_list[k].TRADE_PRICE).toFixed(5)+'</td>';
                    v += '<td>'+my_date_format(d.trade_list[k].insert_dt)+'</td>';
                    v += '</tr>';
                }
                $('#_ltp').text('$ '+d.trade_list[0].TRADE_PRICE);
            } else {
                v += '<p class="text-info">No transactions.</p>';
            }
            $('#trade-list').html(v);
        }
    }
    });
}

/*Traders List*/
function tradersList(bc2) {
    $.ajax({
        method:'post',
        url:'ajax/tradersList.php',
        data: { task : 'loadTradersList', bc2:bc2},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
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
                var v = '';
                if(isArray(d.traders_list) && d.traders_list.length != 0) {
                    $('#bcn').text(d.traders_list[0].bc);
                    for (var k=0; k<= d.traders_list.length-1; k++) {
                        v += '';
                        v += '<tr>';
                        v += '<td>'+d.traders_list[k].name+'</td>';
                        v += '<td>'+d.traders_list[k].balance+'</td>';
                        v += '</tr>';
                    }
                }
                $('#traders-list').html(v);
            }
        }
    });
}

/*My Orders*/
function MyOrders() {
    $.ajax({
        method:'post',
        url:'ajax/myOrders.php',
        data: { task : 'loadMyOrdersList'},
        error:function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success:function(data) {
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                $('#myOrdersTable').html(data);
            }
        }
    });
}
/*My Transactions*/
function MyTransactions() {
    $.ajax({
        method:'post',
        url:'ajax/myTransactions.php',
        data: { task : 'myTransactions'},
        error:function(xhr, status, error) {
            console.log(xhr.responseText);
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
                var v = '';
                if(isArray(d.trade_list) && d.trade_list.length != 0) {
                    for (var k=0; k<= d.trade_list.length-1; k++) {
                        v += '';
                        v += '<tr>';
                        v += '<td>'+d.trade_list[k].SELLER+'</td>';
                        v += '<td>'+d.trade_list[k].BUYER+'</td>';
                        v += '<td>$ '+d.trade_list[k].TRADE_PRICE+'</td>';
                        v += '<td>'+d.trade_list[k].TRADED_QTY+'</td>';
                        v += '<td>$ '+(d.trade_list[k].TRADED_QTY * d.trade_list[k].TRADE_PRICE).toFixed(5)+'</td>';
                        v += '<td>'+my_date_format(d.trade_list[k].insert_dt)+'</td>';
                        v += '</tr>';
                    }
                }
                $('#my-transactions-list').html(v);
            }
        }
    });
}

function sel_bc_stats(bc1, bc2) {
    var bc_one = $('#bc-one');
    var bc_two = $('#bc-two');
    bc_one.text(bc1);
    bc_two.text(bc2);
    $.ajax({
        method:'post',
        url:'ajax/sel_bc_stats.php',
        data: { task : 'sel_bc_stats', bc1:bc1, bc2:bc2},
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function(data) {
            $('#bc-two-pr').text('');
            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch(err) {
                IS_JSON = false;
            }

            if(IS_JSON) {
                if((d.data.length != 0) && (bc_one.val()!='') && (bc_two.val()!='')) {
                    bc_one.text(d.data.a_bc);
                    bc_two.text(d.data.b_bc);
                    $('#bc-two-pr').text(d.data.b_amount);
                }
            }
        }
    });
}

function user_wallet() {
    $.ajax({
        method: 'post',
        async: true,
        url: 'ajax/update_user_wallet.php',
        data: {task: 'update_user_wallet'},
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function (data) {

            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch (err) {
                IS_JSON = false;
            }

            if (IS_JSON) {
                if (d.error == false) {
                    if (isArray(d.bc) && d.bc.length != 0) {
                        var t = '';
                        for (var k = 0; k <= d.bc.length - 1; k++) {
                            t += '<tr>';
                            t += '<td>'+ d.bc[k].bc+'</td>';
                            t += '<td>'+ d.bc[k].balance+'</td>';
                            t += '</tr>';
                        }
                        $('#usr-bc-bal').html(t);
                    }
                }
            }
        }
    });
}

function current_prices(bc2) {
    var ltpbc2 = $('#ltpbc2');
    var bccp = $('#bccp');
    bccp.text('No Data');
    ltpbc2.val();
    if(bc2 !="") {
        ltpbc2.val('('+bc2+')');
    }
    $.ajax({
        method: 'post',
        async: true,
        url: 'ajax/current_prices.php',
        data: {task: 'current_prices', bc2:bc2},
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        },
        success: function (data) {
            var IS_JSON = true;
            try {
                var d = jQuery.parseJSON(data);
            }
            catch (err) {
                IS_JSON = false;
            }

            if (IS_JSON) {
                if (d.error == false) {
                    if (isArray(d.bc) && d.bc.length != 0) {
                        var t = '';
                        var w = '';
                        for (var k = 0; k <= d.bc.length - 1; k++) {
                            w = d.bc[k].b_bc;
                            t += '<tr>';
                            t += '<td>'+ d.bc[k].a_bc+'</td>';
                            t += '<td>'+ d.bc[k].a_amount+'</td>';
                            t += '<td><span class="text-success">22%</span></td>';
                            t += '</tr>';
                        }
                        ltpbc2.html('('+w+')');
                        bccp.html(t);
                    }
                }
            }
        }
    });
}

/*Messages*/
function load_messages() {
    $.ajax({
        method:'post',
        url:'ajax/myMessages.php',
        data: { task : 'loadMyMessagesList'},
        error: function(xhr, status, error) {
            console.log(error);
        },
        success: function(data) {
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                var IS_JSON = true;
                try {
                    var d = jQuery.parseJSON(data);
                }
                catch(err) {
                    IS_JSON = false;
                }

                if (IS_JSON) {
                    var v = '0 message';
                    if(isArray(d.msg) && d.msg.length != 0) {
                        v = '';
                        var si = 0;
                        for (var k=0; k<= d.msg.length-1; k++) {
                            si = k+1;
                            v += '<tr>';
                            v += '<td>'+si+'</td>';
                            v += '<td>'+d.msg[k].order_id+'</td>';
                            v += '<td>'+d.msg[k].messages+'</td>';
                            v += '<td>'+my_date_format(d.msg[k].datetime)+'</td>';
                            v += '</tr>';
                        }
                    }
                    $('#user_msg').html(v);
                }
            }
        }
    });
}

/*Delete Orders*/
$(document).on('click', '.del_order', function (e) {
    e.preventDefault();
    var id = $(this).attr("id");

    $.ajax({
        method:'post',
        url:'ajax/delOrder.php',
        data: { task : 'delOrder', id:id},
        error: function(xhr, status, error) {
            console.log(error);
        },
        success: function(data) {
            if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                $.notify({
                    title: "<strong>Order Deleted!:</strong> ",
                    message: "You deleted the order successfully."
                },{
                    type: 'success'
                });
            } else {
                displayNotice("The order could not be deleted. Try again later.", "failure");
            }
            run_OrderMatchingAlgorithm();
            load_messages();
            MyOrders();
        }
    });
});

