<?php

require_once '../includes/imp_files.php';

if (isset($_POST['task'], $_POST['bc1'], $_POST['bc2']) && trim($_POST['task'])=='sel_bc_stats') {

    $bc1 = trim($_POST['bc1']);
    $bc2 = trim($_POST['bc2']);
    $std = new stdClass();
    $std->data = array();
    $std->error = true;

    if (isset($OrderClass)) {
        $is_sel1_valid= $OrderClass->is_bc_valid($bc1, 1, null);
        $is_sel2_valid= $OrderClass->is_bc_valid($bc2, null, 1);
        if (!$is_sel1_valid || !$is_sel2_valid) {
            return;
        }

        $data = $OrderClass->tx_data($bc1, $bc2, 1);
        if (!empty($data)) {
            if (isset($data->b_amount)) {
                $data->b_amount = round_it($data->b_amount);
            }
            $std->data = $data;
            $std->error = false;
        }
    }
    echo json_encode($std);

} else {
    return false;
}