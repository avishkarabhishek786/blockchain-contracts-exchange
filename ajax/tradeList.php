<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Sinha
 * Date: 6/24/2017
 * Time: 8:38 PM
 */

require_once '../includes/imp_files.php';

if (isset($_POST['task'],$_POST['bc1'],$_POST['bc2']) && trim($_POST['task'])=='loadTradeList') {

    $std = new stdClass();
    $std->trade_list = array();
    $std->error = true;

    if (isset($OrderClass, $UserClass)) {

        $tradeList = $OrderClass->last_transaction_list(0,10,trim($_POST['bc1']),trim($_POST['bc2']));

        $std->trade_list = $tradeList;
        $std->error = false;

    }
    echo json_encode($std);

} else {
    return false;
}