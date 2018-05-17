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
include_once VIEWS_DIR.'/view_all_recent_transactions.php';

include_once 'includes/footer.php';

?>
<script src="<?=JS_DIR?>/load_more_recent_transactions.js"></script>
