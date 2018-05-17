<?php
require_once '../includes/imp_files.php';

if (isset($_POST['task']) && trim($_POST['task']=='current_prices')) {
    $bc2 = isset($_POST['bc2']) ? $_POST['bc2'] : null;

    $std = new stdClass();
    $std->bc = array();
    $std->error = true;
    if (isset($OrderClass)) {

        $wallet = $OrderClass->tx_data(null,$bc2,null);

        $std->bc = $wallet;
        $std->error = false;

    }
    echo json_encode($std);
}
return false;