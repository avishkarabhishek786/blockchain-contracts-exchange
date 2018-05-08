<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 10/3/2017
 * Time: 6:33 PM
 */

function round_it($num=0, $deci=2) {
    $decimal = abs(number_format((float)$num, $deci, '.', ''));
    return $decimal;
}

function redirect_to($url=null) {
    header('Location: '.$url);
    exit;
}

function checkLoginStatus() {
    if(!isset($_SESSION['fb_id']) || !isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
        return false;
    }
    return true;
}

function extract_int($string) {
    $int = intval(preg_replace('/[^0-9]+/', '', $string), 10);
    return $int;
}

function bitcoin_price_today() {
    $bit_price = null;

    try {
        $url = "https://bitpay.com/api/rates";

        $json = file_get_contents($url);
        $data = json_decode($json, TRUE);

        $rate = $data[1]["rate"];
        $usd_price = 1;
        $bit_price = round($rate/$usd_price , 8);
    } catch(Exception $e) {
        $bit_price = null;
    }

    return (float) $bit_price;
}

function bitcoin_calculator($usd=0) {
    $btc_usd_price = bitcoin_price_today();
    if (($usd > 0) && ($btc_usd_price > 0)) {
        return (float) $usd/$btc_usd_price;
    }
    return false;
}

function wapol_str($string) {
    if(preg_match('/[^a-z:\-0-9]/i', $string)) {
        return false;
    } else {
        return true;
    }
}

function sendReqtoURL($addr, $tokens) {

    $url = 'http://ranchimall.net/test/test.php';
    $myvars = 'addr=' . $addr . '&tokens=' . $tokens;

    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );

    curl_close($ch);

    return (int) $response;
}

function is_email($email='') {
    $email = trim($email);
    if ($email != null) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
    }
    return false;
}

function validate_decimal_place($num=0, $decimal_allowed=2) {

    $num = (float) $num;
    $decimal_places = strlen(substr(strrchr($num, "."), 1));

    //if(($decimal_places > 0) && ($decimal_places <= $decimal_allowed)) {
    if($decimal_places <= $decimal_allowed) {
        return true;
    }
    return false;
}