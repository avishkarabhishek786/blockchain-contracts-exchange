<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/6/2017
 * Time: 7:09 PM
 */

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if(isset($_POST['req']) && $_POST['req'] == 'loadMoreMyOrders') {

    if (isset($UserClass, $OrderClass, $user_id)) {
        $validate_user = $UserClass->check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }

        if (isset($_POST['records_per_page'], $_POST['start'])) {

            $start = (int) $_POST['start'];
            $records = (int) $_POST['records_per_page'];

            $iter = "";
            if (isset($OrderClass, $user_id)) {

                $myOrders = $OrderClass->UserOrdersList($user_id, $start, $records);

                if (is_array($myOrders) && !empty($myOrders)) {

                    foreach($myOrders as $myOrder):

                        switch ($myOrder->status) {
                            case '0':
                                $status = 'Cancelled';
                                break;
                            case '1':
                                $status = 'Successful';
                                break;
                            case '2':
                                $status = 'Pending';
                                break;
                            case '3':
                                $status = 'Pending';
                                break;
                            default:
                                $status = 'Pending';
                        }

                        if($myOrder->status == '1') {
                            $status = 'Successful';
                        } else if ($myOrder->status == '2') {
                            $status = 'Pending';
                        } else if ($myOrder->status == '3'){
                            $status = 'Pending';
                        }

                        if($myOrder->order_type == '1') {
                            $OrderType = 'Sell';
                        } elseif($myOrder->order_type == '0') {
                            $OrderType = 'Buy';
                        }

                        $iter .= "<tr>";
                        $iter .= "<td>$myOrder->price</td>";
                        $iter .= "<td>$myOrder->qty</td>";
                        $iter .= "<td>";
                        if(trim($status) == 'Pending') {
                            $iter .= "<button class='btn-danger del_order' id='del_$myOrder->id'>Cancel</button>";
                        }
                        $iter .= "</td>";
                        $iter .= "<td>$myOrder->offer_asset</td>";
                        $iter .= "<td>$myOrder->want_asset</td>";
                        $iter .= "<td>$status</td>";
                        $iter .= "<td>".date('d M, Y h:i:sa', strtotime($myOrder->insert_dt))."</td>";
                        $iter .= "</tr>";
                    endforeach;
                }
            }
            echo $iter;
        }
    }
}