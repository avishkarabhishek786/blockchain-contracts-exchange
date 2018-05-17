<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/6/2017
 * Time: 7:09 PM
 */

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if(isset($_POST['req']) && $_POST['req'] == 'loadMoreMyMessages') {

    if (isset($UserClass, $OrderClass, $user_id)) {
        $validate_user = $UserClass->check_user($user_id);

        if($validate_user == "" || empty($validate_user)) {
            return false;
        }

        $std = new stdClass();
        $std->msg = array();
        $std->error = true;

        if (isset($_POST['records_per_page'], $_POST['start'])) {

            $start = (int) $_POST['start'];
            $records = (int) $_POST['records_per_page'];

            $megs = $UserClass->list_messages_by_userId($user_id, $start, $records);

            if (is_array($megs) && !empty($megs)) {
                $std->msg = $megs;
                $std->error = false;
            }
        }
        echo json_encode($std);
    }
}