<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus()) {
    return false;
}

if (isset($_POST['job']) && trim($_POST['job']) == "update-user-bc-balance") {

    if (isset($_POST['bc_bal_updt'], $_POST['cus_id'], $_POST['_bc2'])) {
        $cus_id = (int)$_POST['cus_id'];
        $bc2 = trim($_POST['_bc2']);
        $balance = number_format((float)$_POST['bc_bal_updt'], 10);

        $std = new stdClass();
        $std->mesg = array();
        $std->error = true;

        $is_sel2_valid= $OrderClass->is_bc_valid($bc2, null, 1);

        if ($bc2==""||$bc2==null || !$is_sel2_valid) {
            $mess = "Please choose a Blockchain contract from second dropdown.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        $validate_user = $UserClass->check_user($cus_id);
        if($validate_user == "" || empty($validate_user)) {
            $mess = "No such user exist. Please re-check user ids.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        if ($balance < 0) {
            $mess = "Balance must be positive number!";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        if (!isset($OrderClass, $UserClass)) {
            $mess = "System Error!";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        $update_bal = null;

        /*Restrict decimal places while updating balance*/
        if (!validate_decimal_place($balance, 10)) {
            $mess = "Max 10 decimal places allowed.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        //Prev balance of user
        $bal_prev = (float) $OrderClass->check_customer_balance($bc2, $cus_id)->balance;

        $update_bal = $OrderClass->update_user_balance($bc2, $balance, $cus_id);

        if (!$update_bal) {
            $mess = "Failed to update balance.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        } else if($update_bal) {
            // Record this change
            $OrderClass->record_root_bal_update($cus_id, $bal_prev, $balance, $bc2);
            $mess = "Successfully updated balance!";
            $std->error = false;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        } else {
            $mess = "Something went wrong. Failed to update balance!";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }
    }
}