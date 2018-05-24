<?php

require_once '../includes/imp_files.php';

if (isset($_POST['task']) && trim($_POST['task'])=='refresh') {

    $bc1 = trim($_POST['bc1']);
    $bc2 = trim($_POST['bc2']);

    $std = new stdClass();
    $std->buys = null;
    $std->sells = null;
    $std->message = array();
    $std->error = true;

    if (isset($OrderClass, $UserClass)) {
        if (isset($bc1) && trim($bc1)!=="") {
            $is_sel1_valid= $OrderClass->is_bc_valid($bc1, 1, null);
            if (!$is_sel1_valid) {
                return;
            }
        } else {$bc1=null;}

        if (isset($bc2) && trim($bc2)!=="") {
            $is_sel2_valid= $OrderClass->is_bc_valid($bc2, null, 1);
            if (!$is_sel2_valid) {
                return;
            }
        } else {$bc2=null;}

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