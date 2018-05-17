<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/5/2017
 * Time: 10:44 AM
 */

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['task']) && $_POST['task']=='loadMyMessagesList') {
    if (isset($UserClass, $OrderClass, $user_id) && $UserClass!=null && $OrderClass!=null) {

        $std = new stdClass();
        $std->msg = null;
        $std->error = true;

        $my_messages = $UserClass->list_messages_by_userId($user_id, 0, 10);

        if (is_array($my_messages) && !empty($my_messages)) {
            $std->msg = $my_messages;
            $std->error = false;
        }

        echo json_encode($std);

    }
}