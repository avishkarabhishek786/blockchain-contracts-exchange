<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 7/17/2018
 * Time: 6:48 AM
 */
error_reporting(E_ALL);
require_once '../includes/defines.php';
require_once '../includes/config.php';
include_once '../includes/autoload.php';
include_once '../includes/functions.php';

//take in root address
$root_address = "oLu7TJH6EkR79LE8smqQjG1yXgg4hvdtE6";
$root_init_value = 21000;

if(!class_exists('Viv')) {
    echo "Class Viv does not exists";
    return false;
}

$VivClass = new Viv();
$VivClass->truncate_tbl(VIV_TX_TBL);
$VivClass->truncate_tbl(VIV_LOGS);
$VivClass->truncate_tbl(VIV_WEB);
$VivClass->truncate_tbl(VIV_EXTRA);


$transferDescription = "Root address = " . $root_address . " has been initialized with ". $root_init_value. " tokens";
$blockchainReference = "https://testnet.florincoin.info/tx/";
$rr=$VivClass->insertWebInfo($transferDescription, $blockchainReference);

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
$root_block_index = get_block_index($root_block_hash)["height"];
echo "Root block index: ".$root_block_index."<br>";

//get current block count
$current_block_index = get_current_block_count();
echo "Current Block index: ". $current_block_index."<br>";

$VivClass->insertExtra(1, $root_block_index);

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    var blockindex = <?=$root_block_index?>+1;
    var current_block_index = <?=$current_block_index?>+1;

    var recur_loop = function(blockindex) {
        var num = blockindex;

        if(num < current_block_index) {
            var bi = '';
            var job = 'loop_it';
            $.ajax({
                url:'../ajax/viv.php',
                type:"post",
                data: {job:job, bi:blockindex},
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                },
                success:function(data) {
                    if (data !== '') {
                        console.log('blockindex: '+data);
                        recur_loop(num+1);
                    } else {
                        console.log('loop stopped at blockindex '+data);
                    }
                }
            });
        }
    }
    recur_loop(blockindex);

</script>


