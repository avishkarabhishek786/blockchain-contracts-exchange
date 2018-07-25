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

function rmt_price_today() {
    $rmt_price = null;

    try {
        $url = "https://www.ranchimall.net/exchange/api/market_price";

        $json = file_get_contents($url);
        $data = json_decode($json);

        return $data;
        /*$rate = $data[1]["rate"];
        $usd_price = 1;
        $bit_price = round($rate/$usd_price , 8);*/
    } catch(Exception $e) {
        $rmt_price = null;
    }
    return (float) $rmt_price;
}

function bc_to_usd($bc_rmt_price, $current_rmt_price_in_usd) {
    return round(($bc_rmt_price * $current_rmt_price_in_usd), 2);
}

function user_rmt_bal($uid=0) {
    $bit_price = null;
    if (!is_int($uid)) {
        return false;
    }

    try {
        $url = "https://www.ranchimall.net/exchange/api/token_ratio/$uid/rmt";

        $json = file_get_contents($url);
        $data = json_decode($json, TRUE);

        $user_rmt_bal= $data["user"];
    } catch(Exception $e) {
        $user_rmt_bal = null;
    }

    return (float) $user_rmt_bal;
}

function get_tx_hash($addr=null) {
    $root_trans_hash = null;
    try {
        $string = "https://testnet.florincoin.info/ext/getaddress/$addr";
        $json = file_get_contents($string);
        $data = json_decode($json, TRUE);
        foreach ($data["last_txs"] as $cur) {
            if ($cur["type"] == "vout") {
                $root_trans_hash = $cur["addresses"];
                break;
            }
        }
    } catch (Exception $e) {
        return null;
    }
    return $root_trans_hash;
}

function get_block_hash($root_trans_hash) {
    $root_block_hash = null;
    try {
        $string = "https://testnet.florincoin.info/api/getrawtransaction?txid=".$root_trans_hash."&decrypt=1";
        $json = file_get_contents($string);
        $data = json_decode($json, TRUE);
        $root_block_hash = $data["blockhash"];
    } catch (Exception $e) {
        return null;
    }
    return $root_block_hash;
}

function get_block_index($root_block_hash) {
    $root_block_index = null;
    try {
        $string = "https://testnet.florincoin.info/api/getblock?hash=".$root_block_hash;
        $json = file_get_contents($string);
        $root_block_index = json_decode($json, TRUE);
    } catch (Exception $e) {
        return null;
    }
    return $root_block_index;
}

function get_current_block_count() {
    $current_block_index = null;
    try {
        $string = "https://testnet.florincoin.info/api/getblockcount";
        $json = file_get_contents($string);
        $data = json_decode($json, TRUE);
        $current_block_index = $data;
    } catch (Exception $e) {
        return null;
    }
    return $current_block_index;
}

function get_current_blockhash($block_index=null) {
    $blockhash = null;
    try {
        $string = "https://testnet.florincoin.info/api/getblockhash?index=".$block_index;
        $blockhash = file_get_contents($string);
    } catch (Exception $e) {
        return null;
    }
    return $blockhash;
}

function listcheck($element=null) {
    try {
        $element = (float) $element;
        if (!is_float($element)) {
            throw new Exception("Invalid float value");
        }
    } catch(Exception $e) {
        echo 'Message: ' .$e->getMessage();
        return 1;
    }
    return 0;
}

function dothemagic($blockindex=null) {
    if ($blockindex==null || !class_exists('Viv')) {
        return;
    }

    $VivClass = new Viv();

    $blockindex = (int) $blockindex;
    $blockhash = get_current_blockhash($blockindex);
    $blockinfo = get_block_index($blockhash);

    foreach ($blockinfo["tx"] as $transaction) {
        $string = "https://testnet.florincoin.info/api/getrawtransaction?txid=".$transaction."&decrypt=1";
        $json = file_get_contents($string);
        $data = json_decode($json, TRUE);
        $text = substr($data["floData"], 5);
        $comment_list = explode("#", $text);

        if ($comment_list[0]=='ranchimall-rebc') {
            echo "<p>I just saw ranchimall-rebc</p>";
            $commentTransferAmount = $comment_list[1];

            if (strlen($commentTransferAmount)==0) {
                echo "Value for token transfer has not been specified";
                continue;
            }

            $returnval = listcheck($commentTransferAmount);
			if ($returnval == 1) {
                continue;
            }
            $commentTransferAmount_arr = [];
            array_push($commentTransferAmount_arr, $commentTransferAmount);
            $commentTransferAmount =$commentTransferAmount_arr;

            $inputlist = [];
			$querylist = [];

            foreach ($data["vin"] as $obj) {
                array_push($querylist, [$obj["txid"], $obj["vout"]]);
            }

            $inputval = 0;
			$inputadd = '';

            foreach ($querylist as $query) {
                $string = "https://testnet.florincoin.info/api/getrawtransaction?txid=".$query[0]."&decrypt=1";
                $json = file_get_contents($string);
                $content = json_decode($json, TRUE);

                foreach ($content["vout"] as $objec) {
                    if ($objec["n"] == $query[1]) {
                        $inputadd = $objec["scriptPubKey"]["addresses"][0];
						$inputval = $inputval + $objec["value"];
                    }
                }
            }

            array_push($inputlist, [$inputadd, $inputval]);

            if (count($inputlist) > 1) {
                print("Program has detected more than one input address ");
                print("This transaction will be discarded");
                continue;
            }

            $outputlist = [];
            foreach ($data["vout"] as $obj) {
                if ($obj["scriptPubKey"]["type"] == "pubkeyhash") {
                    if ($inputlist[0][0] == $obj["scriptPubKey"]["addresses"][0]) {
                        continue;
                    }
                    $temp = [];
                    array_push($temp, $obj["scriptPubKey"]["addresses"][0]);
                    array_push($temp, $obj["value"]);
                    array_push($outputlist, $temp);
                }
            }

            print("Input List");
            echo "<br>";
			print_r($inputlist);
            echo "<br>";
			print("Output List");
			print_r($outputlist);
            echo "<br>";

            if (count($inputlist)>1) {
                print("Program has detected more than one input address ");
				print("This transaction will be discarded");
				continue;
            }

            $availableTokens = $VivClass->getAvailableTokens($inputlist[0][0]);

            if ($availableTokens==null) {
                echo "The input address doesn't exist in our database";
                continue;
            } elseif($availableTokens < array_sum($commentTransferAmount)) {
                print("The transfer amount passed in the comments is more than the user owns\nThis transaction will be discarded");
				continue;
            } elseif ($availableTokens >= array_sum($commentTransferAmount)) {
                if (count($commentTransferAmount) !== count($outputlist)) {
                    print("The parameters in the comments aren't enough");
					print("This transaction will be discarded");
					continue;
                }

                for ($i=0; $i<count($commentTransferAmount); $i++) {
                    $table = $VivClass->getTransactiontable($inputlist[0][0]);

                    $pidlst = [];
					$checksum = 0;

                    foreach ($table as $row) {
                        if ($checksum >= $commentTransferAmount[$i]) {
							break;
                        }
                        array_push($pidlst, $row->id);
						$checksum = $checksum + $row->transferBalance;
                    }
                    $balance = $commentTransferAmount[$i];
                    $opbalance = $VivClass->getAvailableTokens($outputlist[$i][0]);

                    if ($opbalance==null) {
                        $opbalance = 0;
                    }

                    $ipbalance = $VivClass->getAvailableTokens($inputlist[0][0]);

                    print('$opbalance: '.$opbalance);
                    echo '<br>';
                    print('$ipbalance: '.$ipbalance);

                    foreach ($pidlst as $pid) {
                        $temp = $VivClass->getTransferBalanceById($pid);

                        if ($balance <= $temp) {
                            $VivClass->insertTx($outputlist[$i][0], $pid, $balance);
                            $bbal = (float) $temp-$balance;
                            $VivClass->updateTx($bbal, $pid);

                            // transaction logs section
                            $lastid = $VivClass->getMostRecentId();
                            $transferDescription = $balance . " tokens transferred to " . $outputlist[$i][0] . " from " . $inputlist[0][0];
                            $blockchainReference = 'https://testnet.florincoin.info/tx/' . $transaction;
                            $VivClass->insertLogs($lastid, $transferDescription, $pid, $blockchainReference);

                            $transferDescription = $inputlist[0][0] . " balance UPDATED from " . $temp . " to " . $bbal;
                            $blockchainReference = 'https://testnet.florincoin.info/tx/' .$transaction;
                            $VivClass->insertLogs($pid, $transferDescription, null, $blockchainReference);

                            //webpage table section
                            $VivClass->insertWebInfo($transferDescription, $blockchainReference);

                            $transferDescription = "UPDATE " . $outputlist[$i][0] . " balance from " . $opbalance . " to " . $opbalance . $commentTransferAmount[$i];
                            $VivClass->insertWebInfo($transferDescription, $blockchainReference);

                            $transferDescription = "UPDATE " . $inputlist[0][0] . " balance from " . $ipbalance . " to " . $ipbalance - $commentTransferAmount[$i];
                            $VivClass->insertWebInfo($transferDescription, $blockchainReference);

                            $balance = 0;
                        } elseif($balance > $temp) {

                            $VivClass->insertTx($outputlist[$i][0], $pid, $temp);
                            $VivClass->updateTx(0, $pid);

                            //transaction logs section
                            $lastid = $VivClass->getMostRecentId();
                            $transferDescription = $temp . " tokens transferred to " . $outputlist[$i][0] . " from " . $inputlist[0][0];
                            $blockchainReference = 'https://testnet.florincoin.info/tx/' . $transaction;
							$VivClass->insertLogs($lastid, $transferDescription, $pid, $blockchainReference);

                            $transferDescription = " balance UPDATED from " . $temp . " to 0";
                            $blockchainReference = 'https://testnet.florincoin.info/tx/' . $transaction;
                            $VivClass->insertLogs($pid, $transferDescription, null, $blockchainReference);

                            $balance = $balance - $temp;

                        }
                    }
                }
            }
        }
    }
    return true;
}

function update() {
    if (!class_exists('Viv')) {
        return false;
    }
    $VivClass = new Viv();
    $current_index = get_current_block_count();
    $lastblockscanned = $VivClass->getLastBlockScanned();
    $blockindex = $lastblockscanned+1;
    if ($blockindex <= $current_index) {
        if (dothemagic($blockindex)==true) {
            $VivClass->updateExtra(1, $blockindex);
            echo 'Last block scanned: '.$blockindex;
        } else {
            return false;
        }
    }
    return true;
}
