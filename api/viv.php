<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 7/17/2018
 * Time: 6:48 AM
 */
error_reporting(E_ALL);
//require '../includes/imp_files.php';
require_once '../includes/defines.php';
require_once '../includes/config.php';
include_once '../includes/autoload.php';
include_once '../includes/functions.php';

//take in root address
$root_address = "oLu7TJH6EkR79LE8smqQjG1yXgg4hvdtE6";
$root_init_value = 21000;

if(!class_exists('Viv')) {
    echo "Class Viv not exists";
    return false;
}

/*$VivClass = new Viv();
$VivClass->truncate_tbl(VIV_TX_TBL);
$VivClass->truncate_tbl(VIV_LOGS);
$VivClass->truncate_tbl(VIV_WEB);
$VivClass->truncate_tbl(VIV_EXTRA);

$root_inserted = $VivClass->insertTx($root_address, 0, $root_init_value);
if (!$root_inserted) {
    echo "Failed to initialize root address.";
    exit;
}

$transferDescription = "Root address = (string) $root_address has been initialized with (string) $root_init_value tokens";
$blockchainReference = 'https://testnet.florincoin.info/tx/';

$log_inserted = $VivClass->insertLogs(1, $transferDescription, 0, $blockchainReference);
if (!$log_inserted) {
    echo "Failed to log transfer description.";
    exit;
}

//find root address's block
$string = "https://testnet.florincoin.info/ext/getaddress/$root_address";
$root_trans_hash = get_tx_hash($root_address);
$root_block_hash = get_block_hash($root_trans_hash);
$root_block_index = get_block_index($root_block_hash);
echo "Root block index: ".$root_block_index."<br>";

//get current block count
$current_block_index = get_current_block_count()["height"];
echo "Current Block index: ". $current_block_index."<br>";*/

$rr = dothemagic(26679);
print_r($rr);



