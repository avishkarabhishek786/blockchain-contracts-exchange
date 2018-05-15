<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Sinha
 * Date: 6/24/2017
 * Time: 8:38 PM
 */

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task']) && trim($_POST['task'])=='myTransactions') {

    $std = new stdClass();
    $std->trade_list = array();
    $std->error = true;

    if (isset($UserClass, $user_id)) {

        $tradeList = $UserClass->displayUserTransaction($user_id, 0, 10);

        $std->trade_list = $tradeList;
        $std->error = false;

    }
    echo json_encode($std);

} else {
    return false;
}