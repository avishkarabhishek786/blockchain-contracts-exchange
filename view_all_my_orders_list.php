<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/5/2017
 * Time: 4:57 PM
 */
ob_start();
require_once 'includes/imp_files.php';
require_once 'includes/header.php';
if (!checkLoginStatus()) {
    redirect_to('index.php?msg=Please login!');
}
include_once VIEWS_DIR.'/view_all_my_orders_list.php';

include_once 'includes/footer.php';
?>
<script src="js/load_more_my_orders.js"></script>
