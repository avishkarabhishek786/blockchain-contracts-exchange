<?php

// Turn off error reporting
//error_reporting(0);
//@ini_set('display_errors', 0);

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

    //$tradersList = $OrderClass->UserBalanceList();
    //$buy_list[] = $OrderClass->get_top_buy_sell_list(TOP_BUYS_TABLE, $asc_desc='DESC');  // buy
    //$sell_list[] = $OrderClass->get_top_buy_sell_list(TOP_SELL_TABLE, $asc_desc='ASC');  // sell
}

$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "";
$user_logged_in = false;
$action_class_market = 'fb_log_in';
$action_class_buy_sell = 'fb_log_in';
if(checkLoginStatus()) {
    $user_logged_in = true;
    $action_class_market = 'market_submit_btn';
    $action_class_buy_sell = 'process';
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            <option value="REBC">Real Estate</option>
            <option value="IBC">Incorporation</option>
            <option value="FLOBC">Flo</option>
        </select>

        <select class="custom-select selbc" name="sel-bc-2" id="sel-bc-2">
            <option value="">Select BC2</option>
            <option value="RMT">RMT</option>
            <option value="REBC">Real Estate</option>
            <option value="IBC">Incorporation</option>
            <option value="FLOBC">Flo</option>
        </select>
    </div>

    <button class="navbar-toggler p-0 border-0" type="button" data-toggle="offcanvas">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="nav-scroller bg-white box-shadow">
    <nav class="nav nav-underline">
        <a class="nav-link active" href="#">Dashboard</a>
        <a class="nav-link" href="#">
            Investors
            <span class="badge badge-pill bg-light align-text-bottom">127</span>
        </a>
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

