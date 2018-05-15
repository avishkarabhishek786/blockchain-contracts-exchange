<?php

require_once '../includes/imp_files.php';
//echo $_POST['bc2'];
if (isset($_POST['task'], $_POST['bc1'], $_POST['bc2']) && trim($_POST['task'])=='refresh') {

    $bc1 = $_POST['bc1'];
    $bc2 = $_POST['bc2'];

    $std = new stdClass();
    $std->buys = null;
    $std->sells = null;
    $std->message = array();
    $std->error = true;

    if (isset($OrderClass, $UserClass)) {

        $buy_list = $OrderClass->get_top_buy_sell_list(TOP_BUYS_TABLE, $bc1, $bc2, $asc_desc='DESC');  // buy
        $sell_list = $OrderClass->get_top_buy_sell_list(TOP_SELLS_TABLE, $bc1, $bc2, $asc_desc='ASC');  // sell

        $std->buys = $buy_list;
        $std->sells = $sell_list;
        $std->error = false;
    }
    echo json_encode($std);

} else {
    return false;
}