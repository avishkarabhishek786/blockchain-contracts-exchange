<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/6/2017
 * Time: 7:09 PM
 */

require_once '../includes/imp_files.php';

if(isset($_POST['req']) && $_POST['req'] == 'loadMoreRecentTransactions') {

    if (isset($UserClass, $OrderClass)) {

        $std = new stdClass();
        $std->msg = array();
        $std->error = true;

        if (isset($_POST['records_per_page'], $_POST['start'])) {

            $start = (int) $_POST['start'];
            $records = (int) $_POST['records_per_page'];

            $megs = $OrderClass->last_transaction_list($start, $records);

            if (is_array($megs) && !empty($megs)) {
                $std->trade_list = $megs;
                $std->error = false;
            }
        }
        echo json_encode($std);
    }
}