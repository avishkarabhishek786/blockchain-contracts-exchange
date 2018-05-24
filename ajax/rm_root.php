<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus() || !isset($UserClass, $OrderClass)) {
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

    if (isset($_POST['job']) && trim($_POST['job']=="inset_bc")) {
        if (isset($_POST['ct_name'], $_POST['bccode'], $_POST['bcadmin'], $_POST['incpdt'])) {
            $contractName  = trim($_POST['ct_name']);
            $bcCode = strtoupper(trim($_POST['bccode']));
            $bcAdmin = trim($_POST['bcadmin']);
            $eliSel1 = (trim($_POST['ch1'])=='true'?1:0);
            $eliSel2 = (trim($_POST['ch2'])=='true'?1:0);
            $incp = trim($_POST['incpdt']);

            $std = new stdClass();
            $std->ctr = null;
            $std->msg = null;
            $std->error = true;

            if (strlen($bcCode)>8) {
                $std->msg = "Blockchain Code cannot be greater than 8 characters.";
                echo json_encode($std);
                return false;
            }

            $insertBC = $OrderClass->insert_new_bc($contractName, $bcCode, $bcAdmin, $eliSel1, $eliSel2, $incp);

            if ($insertBC) {
                $std->ctr = $insertBC;
                $std->msg = "New BC inserted successfully";
                $std->error = false;
            } else {
                $std->msg = "Failed to insert new BC.";
            }
        } else {
            $std->msg = "Please fill all the fields";
        }

        echo json_encode($std);
        return false;
    }

    if (isset($_POST['updt_job']) && trim($_POST['updt_job']=="update_sel_bc")) {
        if (isset($_POST['_id'])) {
            $id = trim($_POST['_id']);
            $exp = explode("_",$id);
            $bc = $exp[1];
            $sel = $exp[0];
            $val = (int) $exp[2];
            $val = ($val=='')?1:0;

            $std = new stdClass();
            $std->res = null;
            $std->val = null;

            if ($bc==''||$sel==''||$val==='') {
                echo json_encode($std);
                return;
            }

            $res = $OrderClass->update_bc_eligibility($bc, $sel, $val);

            $std = new stdClass();
            $std->res = $res;
            $std->val = $val;
            $std->new_id = $sel.'_'.$bc.'_'.$val;
            echo json_encode($std);
            return;
        }
    }

}