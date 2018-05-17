<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/5/2017
 * Time: 11:46 AM
 */
ob_start();
require_once 'includes/imp_files.php';
require_once 'includes/header.php';

if (!checkLoginStatus()) {
    redirect_to('index.php?msg=Please login!');
}

//include_once VIEWS_DIR.'/buy_sell_div.php';
//include_once VIEWS_DIR.'/buy_sell_list.php';
include_once VIEWS_DIR.'/view_all_messages.php';

include_once 'includes/footer.php';

?>

<script src="js/load_more_my_messages.js"></script>
