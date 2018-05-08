<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['subject']) && trim($_POST['subject'])=='placeOrder') {

    $std = new stdClass();
    $std->user = null;
    $std->order = null;
    $std->error = false;
    $std->msg = null;

    if (isset($_POST['sel1'], $_POST['qty'], $_POST['price'], $_POST['sel2'], $_POST['bs_rad'], $_POST['is_mkt'])) {
        $WantAssetTypeId = trim($_POST['sel1']);
        $OfferAssetTypeId = trim($_POST['sel2']);
        $qty = (float) trim($_POST['qty']);
        $price = (float) trim($_POST['price']);
        $buy_sell = trim($_POST['bs_rad']);
        $is_mkt = (bool) trim($_POST['is_mkt']);

        $orderStatusId = 2; // 0 -> cancelled; 1 -> complete; 2 -> pending

        if($WantAssetTypeId == '') {
            $std->error = true;
            $std->msg = "Please select first Blockchain contract.";
            echo json_encode($std);
            return false;
        }
        if($OfferAssetTypeId == '') {
            $std->error = true;
            $std->msg = "Please select second Blockchain contract.";
            echo json_encode($std);
            return false;
        }
        if($qty == '' || $qty < 0) {
            $std->error = true;
            $std->msg = "Please provide a valid quantity to be traded.";
            echo json_encode($std);
            return false;
        }
        if (!$is_mkt) {
            if($price == '' || $price < 1) {
                $std->error = true;
                $std->msg = "Please provide a valid price. Price cannot be less than $1.";
                echo json_encode($std);
                return false;
            }
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

        $place_order = "";
        $validate_user = "";
        if (isset($UserClass, $OrderClass)) {

            $validate_user = $UserClass->check_user($user_id);

            if($validate_user == "" || empty($validate_user)) {
                $std->error = true;
                $std->msg = "No such user exist. Please login again.";
                echo json_encode($std);
                return false;
            }

            if ($is_mkt) {
                $place_order = $OrderClass->market_order($order_type, $qty, $OfferAssetTypeId, $WantAssetTypeId);
            } else {
                $place_order = $OrderClass->insert_pending_order($orderTypeId, $qty, $price, $orderStatusId, $OfferAssetTypeId, $WantAssetTypeId);
            }

        }

        $std->user = $validate_user;
        $std->order = $place_order;
        $std->error = false;
        $std->msg = "Order placed successfully.";
        echo json_encode($std);
        return false;
    }
    $std->error = true;
    $std->msg = "Please fill all the fields.";
    echo json_encode($std);
    return false;
}


