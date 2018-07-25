<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 9/27/2017
 * Time: 5:21 PM
 */

require_once '../includes/imp_files.php';

if(isset($_POST['job']) && $_POST['job'] == 'total_recent_transactions') {

    if (isset($UserClass, $OrderClass)) {
        echo $total_my_orders = (int) $OrderClass->total_recent_transactions();
    }

}
