<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task']) && trim($_POST['task']=='update_user_wallet')) {
    $uid = isset($_POST['user_id']) ? (int) $_POST['user_id'] : (int) $_SESSION['user_id'];
    $std = new stdClass();
    $std->bc = array();
    $std->error = true;
    if (isset($UserClass, $user_id)) {

        $wallet = $UserClass->user_bc_bal($uid);

        $std->bc = $wallet;
        $std->error = false;

    }
    echo json_encode($std);
}
return false;