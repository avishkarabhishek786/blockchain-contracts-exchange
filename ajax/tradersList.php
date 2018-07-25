<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 9/27/2017
 * Time: 2:41 PM
 */

require_once '../includes/imp_files.php';

if (isset($_POST['task'], $_POST['bc2']) && trim($_POST['task'])=='loadTradersList') {


    $bc2 = trim($_POST['bc2']);
    $std = new stdClass();
    $std->traders_list = array();
    $std->error = true;

    if (isset($OrderClass)) {

        $is_sel2_valid= $OrderClass->is_bc_valid($bc2, null, 1);
        if (!$is_sel2_valid) {
            return;
        }

        $tradersList = $OrderClass->UserBalanceList($bc2, 1);
        if (is_array($tradersList) && !empty($tradersList)) {
            $std->traders_list = $tradersList;
            $std->error = false;
        }
    }
    echo json_encode($std);

} else {
    return false;
}