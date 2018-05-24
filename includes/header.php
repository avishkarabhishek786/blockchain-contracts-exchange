<?php

// Turn off error reporting
//error_reporting(0);
//@ini_set('display_errors', 0);

$bc_list1 = array();
$bc_list2= array();
$tradersList = array();
$buy_list = array();
$sell_list = array();
include_once 'fbconfig.php';
$validate_user = null;

if (isset($UserClass)) {
    if (isset($fb_id)):
        // check if user already registered
        $validate_user = $UserClass->is_fb_registered($fb_id);

        if($validate_user == "" || $validate_user == false) {
            redirect_to('index.php');
        }
    endif;

    $bc_list1 = $OrderClass->get_bc_list(null, 1, null);
    $bc_list2 = $OrderClass->get_bc_list(null, null, 1);
}

$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "";
$user_logged_in = false;
$action_class_buy_sell = 'fb_log_in';
if(checkLoginStatus()) {
    $user_logged_in = true;
    $action_class_buy_sell = 'process';
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ranchi Mall Blockchain Contracts</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="<?=STYLE_DIR?>/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link href="<?=STYLE_DIR?>/main.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Ranchi Mall Blockchain Contract</a>

    <div class="sel-div float-right">
        <select class="custom-select selbc" name="sel-bc-1" id="sel-bc-1">
            <option value=""> Select BC1</option>
            <?php if(is_array($bc_list1) && !empty($bc_list1)): ?>
                <?php foreach($bc_list1 as $bcl):?>
                    <option value="<?=$bcl->bc_code?>"><?=$bcl->contracts?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <select class="custom-select selbc" name="sel-bc-2" id="sel-bc-2">
            <option value="">Select BC2</option>
            <option value="RMT" selected>RMT</option>
            <?php if(is_array($bc_list2) && !empty($bc_list2)): ?>
                <?php foreach($bc_list2 as $bcl):?>
                    <option value="<?=$bcl->bc_code?>"><?=$bcl->contracts?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <button class="navbar-toggler p-0 border-0" type="button" data-toggle="offcanvas">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="nav-scroller bg-white box-shadow">
    <nav class="nav nav-underline">
        <?php if($user_logged_in) { ?>
        <a class="nav-link active" href="#"><?php echo "Welcome ". (isset($_SESSION['full_name']) ? $_SESSION['full_name']:"")?></a>
        <?php } ?>
        <!--<a class="nav-link" href="#">
            Investors
            <span class="badge badge-pill bg-light align-text-bottom">127</span>
        </a>-->
        <a class="nav-link" href="https://www.ranchimall.net/exchange">Buy RMT</a>

        <?php if($user_logged_in) { ?>
            <a class="nav-link" href="logout.php">Log Out</a>
        <?php } elseif(isset($loginUrl)) {?>
            <a href="<?=$loginUrl?>" role="button" class="nav-link" name="fb_login">
                <div class="btn--facebook ">
                    Login with Facebook
                </div>
            </a>
        <?php } ?>
    </nav>
</div>

