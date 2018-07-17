<?php

require_once '../includes/imp_files.php';

if (!checkLoginStatus() || !isset($OrderClass, $UserClass)) {
    return false;
}

if (isset($_POST['job']) && trim($_POST['job']) == "update-user-bc-balance") {

    if (isset($_POST['bc_bal_updt'], $_POST['cus_id'], $_POST['_bc2'])) {
        $cus_id = (int)$_POST['cus_id'];
        $bc2 = $_POST['_bc2'];
        $balance = number_format((float)$_POST['bc_bal_updt'], 10);

        $std = new stdClass();
        $std->mesg = array();
        $std->error = true;

        if ($bc2==""||$bc2==null || !is_array($bc2) || empty($bc2)) {
            $mess = "Please choose a Blockchain contract from the dropdown menu.";
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

        $update_bal = null;

        /*Restrict decimal places while updating balance*/
        if (!validate_decimal_place($balance, 10)) {
            $mess = "Max 10 decimal places allowed.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        foreach ($bc2 as $b2) {
            $is_sel2_valid= $OrderClass->is_bc_valid($b2, null, 1);
            if (!$is_sel2_valid) {
                $mess = "Unknown Blockchain contract.";
                $std->error = true;
                $std->mesg[] = $mess;
                echo json_encode($std);
                continue;
            }

            //Prev balance of user
            $bal_prev = (float) $OrderClass->check_customer_balance($b2, $cus_id)->balance;

            $update_bal = $OrderClass->update_user_balance($b2, $balance, $cus_id);

            if (!$update_bal) {
                $mess = "Failed to update $b2 balance.";
                $std->error = true;
                $std->mesg[] = $mess;
                echo json_encode($std);
                //return false;
            } else if($update_bal) {
                // Record this change
                $OrderClass->record_root_bal_update($cus_id, $bal_prev, $balance, $b2);
                $mess = "Successfully updated balance!";
                $std->error = false;
                $std->mesg[] = $mess;
                echo json_encode($std);
                //return false;
            } else {
                $mess = "Something went wrong. Failed to update $b2 balance!";
                $std->error = true;
                $std->mesg[] = $mess;
                echo json_encode($std);
                //return false;
            }
        }

    }
    return;
}