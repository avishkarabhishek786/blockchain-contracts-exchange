<?php
require_once '../includes/imp_files.php';

if (!checkLoginStatus() || !isset($OrderClass)) {
    return false;
}

if (isset($_POST['job']) && trim($_POST['job']) == "transfer_tokens") {
    if (isset($_POST['_from'], $_POST['_to'], $_POST['_tokens'], $_POST['_bc2'])) {
        $from = (int) $_POST['_from'];
        $to = (int) $_POST['_to'];
        $bc2 = trim($_POST['_bc2']);
        $tokens = number_format((float)$_POST['_tokens'], 10);

        $std = new stdClass();
        $std->mesg = array();
        $std->error = true;

        if ($from==$to) {
            $mess = "Sender and receiver cannot be same.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        $is_sel2_valid= $OrderClass->is_bc_valid($bc2, null, 1);

        if ($bc2==""||$bc2==null || !$is_sel2_valid) {
            $mess = "Please choose a Blockchain contract from second dropdown.";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        $validate_user_from = $UserClass->check_user($from);
        $validate_user_to = $UserClass->check_user($to);

        if($validate_user_from == "" || empty($validate_user_from) || $validate_user_to == "" || empty($validate_user_to)) {
            $mess = "No such user exist. Please re-check user ids.";
            $std->error = true;
            $std->mesg[] = $mess;
            //$OrderClass->storeMessagesPublic(null, $user_id, $mess);
            echo json_encode($std);
            return false;
        }

        $customer_bal_fr = (float) $OrderClass->check_customer_balance($bc2, $from)->balance;
        $customer_bal_to = (float) $OrderClass->check_customer_balance($bc2, $to)->balance;

        if ($tokens > $customer_bal_fr) {
            $mess = "Admin Token Transfer: The user has insufficient balance to make this ".$bc2." units transfer. His current Token balance is $customer_bal_fr ".$bc2.".";
            $std->error = true;
            $std->mesg[] = $mess;
            echo json_encode($std);
            $OrderClass->storeMessagesPublic(null, $from, $mess);
            return false;
        }

        if ($tokens < 0.0000000001) {
            $mess = "Admin Token Transfer: Please provide minimum amount of 0.0000000001 BC units!";
            $OrderClass->storeMessagesPublic(null, $from, $mess);
            $std->mesg[] = $mess;
            echo json_encode($std);
            return false;
        }

        // Check order in sell table
        $user_active_orders = $OrderClass->get_active_buy_order_of_user($from, $bc2, TOP_SELLS_TABLE);
        $frozen_bal_sells = 0;
        $allowed_bid_amount = $customer_bal_fr;
        if (is_array($user_active_orders) && !empty($user_active_orders)) {
            foreach ($user_active_orders as $uao) {
                $frozen_bal_sells += (float) $uao->quantity;
            }
            $allowed_bid_amount = $customer_bal_fr - $frozen_bal_sells;
            $ext_st = "The user can transfer up to $bc2 $allowed_bid_amount only.";
            if ($allowed_bid_amount == 0) {
                $ext_st = "The user doesn't have any $bc2 to transfer.";
            }
            $msss = "The user has requested to transfer $frozen_bal_sells $bc2. $ext_st Please cancel it or reduce your transfer amount.";
        }

        if ((float)$frozen_bal_sells + (float)$tokens > $customer_bal_fr) {
            $OrderClass->storeMessagesPublic(null, $from, $msss);
            $std->error = true;
            $std->mesg[] = $msss;
            echo json_encode($std);
            return false;
        }

        /*Finally, transfer the tokens*/

        $new_from_bal = $customer_bal_fr - $tokens;
        $new_to_bal = (float)$customer_bal_to + (float)$tokens;
        
        // Decrease tokens of 'from'
        $update_bal_fr = $OrderClass->update_user_balance($bc2, $new_from_bal, $from);
        
        // Increase tokens of 'to'
        $update_bal_to = $OrderClass->update_user_balance($bc2, $new_to_bal, $to);
        
        // Record the balance transfers or errors
        if (!$update_bal_fr) {
            $msss = "Failed to update Sender's balance.";
            $std->error = true;
            $std->mesg[] = $msss;
            $OrderClass->storeMessagesPublic(null, ADMIN_ID, $msss);
            echo json_encode($std);
            return false;
        } else if(!$update_bal_to) {
            $msss = "Failed to update Receiver's balance.";
            $std->error = true;
            $std->mesg[] = $msss;
            $OrderClass->storeMessagesPublic(null, ADMIN_ID, $msss);
            echo json_encode($std);
            return false;
        } else {
            $OrderClass->record_root_bal_update($from, $customer_bal_fr, $new_from_bal, $bc2);
            $OrderClass->record_root_bal_update($to, $customer_bal_to, $new_to_bal, $bc2);

            $msss = "$bc2 transfer for user id ".$from." and ".$to." was processed successfully.";
            $mess1 = "Your ".$tokens." $bc2 were transferred by Admin to user ".$to.".";
            $mess2 = "You received ".$tokens." $bc2 from user ".$from." transferred by Admin.";
            $std->error = false;
            $std->mesg[] = $msss;
            $OrderClass->storeMessagesPublic(null, ADMIN_ID, $msss);
            $OrderClass->storeMessagesPublic(null, $from, $mess1);
            $OrderClass->storeMessagesPublic(null, $to, $mess2);
            echo json_encode($std);
            return true;
        }
    }
}