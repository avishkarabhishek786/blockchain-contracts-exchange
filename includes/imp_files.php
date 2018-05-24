<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/3/2017
 * Time: 7:49 PM
 */

if(!isset($_SESSION)) {
    session_start();
}

require_once 'defines.php';
require_once 'config.php';
include_once 'autoload.php';
include_once 'functions.php';

//if logged in store user DB details
$fb_id = null;
$user_name = null;
$user_id = null;
$log_fullName = null;
$user_email = null;

if (checkLoginStatus()) {
    if (isset($_SESSION['fb_id'], $_SESSION['user_name'], $_SESSION['user_id'])) {
        $fb_id = $_SESSION['fb_id'];
        $user_name = $_SESSION['user_name'];
        $user_id = $_SESSION['user_id'];
    } else {
        redirect_to("logout.php");
    }
    $log_fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
    $user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
}

$UserClass = null;
$OrderClass = null;
$ApiClass = null;
$MailClass = null;

if (class_exists('Users') && class_exists('Orders') && class_exists('SendMail')) {
    $UserClass = new Users();
    $OrderClass = new Orders();
    //$ApiClass = new Api();
    $MailClass = new SendMail();
}
/*Fetch current RMT price*/
if (!isset($_SESSION['RMT_TODAYS_PRICE'])) {
    $rmt = rmt_price_today();
    if (is_object($rmt) && isset($rmt->B_Amount) && $rmt->B_Amount>0 ) {
        $_SESSION['RMT_TODAYS_PRICE'] = (float)$rmt->B_Amount;
    }
}
