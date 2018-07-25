<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/7/2017
 * Time: 11:07 AM
 */

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task'], $_POST['id']) && trim($_POST['task'])=="delOrder") {

    $del_id = extract_int($_POST['id']);

    if (isset($OrderClass, $UserClass, $user_id)) {

        $validate_user = $UserClass->check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }

        $del_order = $OrderClass->del_order($del_id);

        if ($del_order) {
            echo true;
        }
    }
    return false;
}