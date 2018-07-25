<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "includes/imp_files.php";

require __DIR__ . '/vendor/autoload.php';

//$command = new \Nbobtc\Command\Command('sendtoaddress', array("oXCsMUyX3mLJEdnn8SXoH6gyPW9Jd6kjYu", 1, "REBC", "", false, false, 1, 'UNSET', $flo_comment="test rebc"));
/*$client  = new \Nbobtc\Http\Client('http://abhishek:abhishekpassword@localhost:17313');

* @var \Nbobtc\Http\Message\Response 
$response = $client->sendCommand($command);

/** @var string */
//$contents = $response->getBody()->getContents();
//echo $contents;*/

$Viv = new Orders();

 $rr = $Viv->sendFloComment($bc1="RMT", $buyer_id=1, $new_buyer_balance_bc1=101, $new_buyer_balance_bc2=99, $bc2="REBC", $seller_id=2, $new_seller_balance_bc1=99, $new_seller_balance_bc2=101);



echo $rr;
//$sendFlo = $flocoin->sendToAddress(FLO_TX_ADDR, FLO_TX_SEND_AMOUNT, "REBC", "", false, false, 1, 'UNSET', $flo_comment="test rebc");
// $sendFlo = $flocoin->getBestBlockHash();
// print_r($sendFlo);

/*$flo = new stdClass();
$flo->buy_bc = "RMT";
$flo->buyer_id = 1;
$flo->buyer_balance_bc1 = 100;
$flo->buyer_balance_bc2 = 200;
$flo->sell_bc = "REBC";
$flo->seller_id = 2;
$flo->seller_balance_bc1 = 300; 
$flo->seller_balance_bc2 = 400;  

$floJSON = json_encode($flo);
$flo_comment = "ranchimall-bc==".$floJSON;

//echo $flo_comment;

echo "<br>";

$ex = explode("==", $flo_comment);

//print_r($ex[1]);

//die;

$dec = json_decode($ex[1]);
echo "<pre>", print_r($dec), "</pre>";
echo "<br>";
echo $comm = $dec->buy_bc;*/

//echo $comm;