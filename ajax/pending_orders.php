<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['subject']) && trim($_POST['subject'])=='placeOrder') {
    if (isset($UserClass, $OrderClass)) {
    $std = new stdClass();
    $std->user = null;
    $std->order = null;
    $std->error = false;
    $std->msg = null;

    $place_order = "";
    $validate_user = "";
    $msss = "Order could not be placed.";

    if (isset($_POST['sel1'], $_POST['qty'], $_POST['price'], $_POST['sel2'], $_POST['bs_rad'], $_POST['is_mkt'])) {
        $WantAssetTypeId = trim($_POST['sel1']);
        $OfferAssetTypeId = trim($_POST['sel2']);
        $qty = (float) trim($_POST['qty']);
        $price = (float) trim($_POST['price']);
        $buy_sell = trim($_POST['bs_rad']);
        $is_mkt = trim($_POST['is_mkt']);

        if ($is_mkt == 'false') {
            $is_mkt = false;
        } elseif ($is_mkt=='true') {
            $is_mkt = true;
        } else {
            return;
        }

        $orderStatusId = 2; // 0 -> cancelled; 1 -> complete; 2 -> pending

        $is_sel1_valid= $OrderClass->is_bc_valid($WantAssetTypeId, 1, null);
        $is_sel2_valid= $OrderClass->is_bc_valid($OfferAssetTypeId, null, 1);

        if($WantAssetTypeId == '' || !$is_sel1_valid) {
            $std->error = true;
            $std->msg = "Please select first Blockchain contract.";
            echo json_encode($std);
            return false;
        }
        if($OfferAssetTypeId == '' || !$is_sel2_valid) {
            $std->error = true;
            $std->msg = "Please select second Blockchain contract.";
            echo json_encode($std);
            return false;
        }

        if ($WantAssetTypeId==$OfferAssetTypeId) {
            $std->error = true;
            $std->msg = "Both contracts cannot be same. Please select different contracts to trade.";
            echo json_encode($std);
            return false;
        }
        if($qty == '' || $qty < 0.0000000001) {
            $std->error = true;
            $std->msg = "Please provide a valid quantity to be traded.";
            echo json_encode($std);
            return false;
        }

        if ($is_mkt==false) {
            if($price == '' || $price < 1) {
                $std->error = true;
                $std->msg = "Please provide a valid price. Price cannot be less than $1.";
                echo json_encode($std);
                return false;
            }
        }

        $isValidQty = validate_decimal_place($qty, 10);
        $isValidPrice = validate_decimal_place($price, 10);

        if (!$isValidQty || !$isValidPrice) {
            $std->error = true;
            $std->msg = 'Please insert valid quantity and price. Maximum 10 decimal places allowed.';
            echo json_encode($std);
            return false;
        }

        if ($buy_sell=='ex-buy') {
            $orderTypeId = 0; // It is a buy
            $order_type = 'm-buy'; // for market req
            $total_trade_val = $qty * $price;
        } elseif($buy_sell=='ex-sell') {
            $orderTypeId = 1; // It is a sell
            $order_type = 'm-sell'; // for market req
            $total_trade_val = $qty;
        } else {
            $std->error = true;
            $std->msg = "Invalid Buy or Sell order. Please choose Buy or Sell to proceed.";
            echo json_encode($std);
            return false;
        }

            $validate_user = $UserClass->check_user($user_id);

            if($validate_user == "" || empty($validate_user)) {
                $std->error = true;
                $std->msg = "No such user exist. Please login again.";
                echo json_encode($std);
                return false;
            }

            $top_tbl = null;
            if ($orderTypeId == 0) {
                $user_current_bal = (float) $OrderClass->check_customer_balance($OfferAssetTypeId, $user_id)->balance;
                $top_tbl = TOP_BUYS_TABLE;
                $user_active_orders = $OrderClass->get_active_buy_order_of_user($user_id, $OfferAssetTypeId, $top_tbl);

                $frozen_bal = 0;
                if (is_array($user_active_orders) && !empty($user_active_orders)) {
                    foreach ($user_active_orders as $uao) {
                        $frozen_bal += (float) $uao->price * $uao->quantity;
                    }
                }
                $allowed_bid_amount = 0;
                if ($user_current_bal > $frozen_bal) {
                    $allowed_bid_amount = $user_current_bal - $frozen_bal;
                }
                $ext_st = "You can put bid up to $OfferAssetTypeId $allowed_bid_amount only.";
                $ext_st2 = "";
                if ($allowed_bid_amount == 0) {
                    $ext_st = "You don't have any $OfferAssetTypeId balance to spend.";
                }
                if ((float)$frozen_bal != 0) {
                    $ext_st2 = "You have already placed an order worth $OfferAssetTypeId $frozen_bal.";
                }
                $msss = "Insufficient Balance: $ext_st2 $ext_st";

            } elseif ($orderTypeId == 1) {
                $user_current_bal = (float) $OrderClass->check_customer_balance($WantAssetTypeId, $user_id)->balance;
                $top_tbl = TOP_SELLS_TABLE;
                $user_active_orders = $OrderClass->get_active_sell_order_of_user($user_id, $WantAssetTypeId, $top_tbl);
                $frozen_bal = 0;
                if (is_array($user_active_orders) && !empty($user_active_orders)) {
                    foreach ($user_active_orders as $uao) {
                        $frozen_bal += (float) $uao->quantity;
                    }
                }
                $allowed_bid_amount = 0;
                if ($user_current_bal > $frozen_bal) {
                    $allowed_bid_amount = $user_current_bal - $frozen_bal;
                }
                $ext_st = "You can sell maximum $WantAssetTypeId $allowed_bid_amount units.";
                if ($allowed_bid_amount == 0) {
                    $ext_st = "You don't have any $WantAssetTypeId to sell.";
                }
                $msss = "Insufficient Balance: You have already placed an order of $WantAssetTypeId $frozen_bal. $ext_st";
            }

            if ($frozen_bal + $total_trade_val > $user_current_bal) {
                $std->error = true;
                $std->msg = $msss;
                echo json_encode($std);
                return false;
            }

            if ($is_mkt) {
                $place_order = $OrderClass->market_order($order_type, $qty, $OfferAssetTypeId, $WantAssetTypeId);
            } else {
                $place_order = $OrderClass->insert_pending_order($orderTypeId, $qty, $price, $orderStatusId, $OfferAssetTypeId, $WantAssetTypeId);
            }
            $msss = "";

            $std->user = $validate_user;
            $std->order = $place_order;
            $std->error = false;
            $std->msg = $msss;
            echo json_encode($std);
            return false;
        }
    }

    $std->error = true;
    $std->msg = "Please fill all the fields.";
    echo json_encode($std);
    return false;
}


