<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_SESSION['fb_id'], $_SESSION['user_id'], $_SESSION['user_name'])) {
    $root_fb = (int) $_SESSION['fb_id'];
    $root_user_id = (int) $_SESSION['user_id'];
    $root_user_name = (string) $_SESSION['user_name'];

    if ($root_fb != ADMIN_FB_ID && $root_user_id != ADMIN_ID && $root_user_name != ADMIN_UNAME) {
        redirect_to("index.php");
    }

    if (isset($_POST['task'], $_POST['btn_id']) && trim($_POST['task']=="act_user")) {

        $u_id = explode('_', trim($_POST['btn_id']));
        $u_id_int = extract_int($u_id[1]);
        $u_id_str = (string) trim($u_id[0]);
        $act = "";

        if ($u_id_str == "off") {
            $act = "0";
        } else if($u_id_str == "on") {
            $act = "1";
        } else {
            return false;
        }
        if (isset($OrderClass, $UserClass)) {

            if ($u_id_str == "off") {
                $del_ord = $OrderClass->delete_orders_of_user($u_id_int);
            }
            $act_user = $UserClass->actions_user($u_id_int, $act);

            if ($act_user) {
                echo $u_id_str;
            }
        }
        return false;
    }

}