<?php

require_once '../includes/imp_files.php';
require ROOT_PATH . '/vendor/autoload.php';

if (isset($_POST['task']) && trim($_POST['task'])=='run_OrderMatchingAlgorithm') {

    if (isset($OrderClass, $UserClass, $_POST['sel1'], $_POST['sel2'])) {
        $slc1 = trim($_POST['sel1']);
        $slc2 = trim($_POST['sel2']);

        if ($slc1 == "" || $slc2 == "") {return;}

        $is_sel1_valid= $OrderClass->is_bc_valid($slc1, 1, null);
        $is_sel2_valid= $OrderClass->is_bc_valid($slc2, null, 1);
        if (!$is_sel1_valid || !$is_sel2_valid) {
            return;
        }

        $refresh_orders = $OrderClass->OrderMatchingService($slc1, $slc2);

        /*If user is logged in user send him messages, if any*/
        if (checkLoginStatus()) {

            $std = new stdClass();
            $std->user = null;
            $std->order = null;
            $std->error = false;
            $std->msg = null;

            if (isset($user_id)) {

                $validate_user = $UserClass->check_user($user_id);

                if($validate_user == "" || empty($validate_user)) {
                    $std->error = true;
                    $std->msg = "No such user exist. Please login again.";
                    echo json_encode($std);
                    return false;
                }

                $std->user = $validate_user;
                $std->order = $refresh_orders;
                $std->error = false;
                $std->msg = "userLoggedIn";

                echo json_encode($std);

            } else {
                return false;
            }
        }
    }
    } else {
    return false;
}