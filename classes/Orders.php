<?php
class Orders extends Users {

    protected $db_connection = null;
    private $errors = array();
    private $customerId = 0;
    private $orderTypeId = 0;
    private $quantity = 0;
    private $demanded_qty = 0;
    private $price = 0;
    private $orderStatusId = 2; //pending
    private $max_top_bids = 20;
    private $customer_balance = null; // Don't make it 0
    private $customer_frozen_balance = null; // Don't make it 0

    private function insert_order_in_active_table($top_table, $bc1, $bc2, $orderId, $price, $quantity) {

        if ($this->databaseConnection()) {

            $n = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
            $now = $n->format('Y-m-d H:i:s');

            $query = $this->db_connection->prepare("INSERT INTO $top_table(`id`, `bc1`, `bc2`, `price`, `quantity`, `order_id`, `uid`, `insert_dt`)
                      VALUES ('', :bc1, :bc2, :price, :quantity, :orderId, :user_id, '$now')");
            $query->bindParam("bc1", $bc1);
            $query->bindParam("bc2", $bc2);
            $query->bindParam("price", $price);
            $query->bindParam("quantity", $quantity);
            $query->bindParam("orderId", $orderId);
            $query->bindParam("user_id", $_SESSION['user_id']);

            if ($query->execute()) {
                $this->updateOrderStatus($orderId, 3);
                return true;
            }
            return false;
        }
        return false;
    }

    public function insert_pending_order($orderTypeId, $qty, $price, $orderStatusId, $OfferAssetTypeId=null, $WantAssetTypeId=null) {

        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $messages = null;

            $this->customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $this->orderTypeId = $orderTypeId;  // 0-> buy; 1 -> sell;
            $this->quantity = $qty;
            $this->price = $price;
            $this->orderStatusId = $orderStatusId;  // 0 -> cancelled; 1 -> complete; 2 -> pending; 3 ->order active

            $std = new stdClass();
            $std->insertedrowid = null;
            $std->orderTypeId = null; // 0 -> buy; 1 -> sell
            $std->item_qty = null;
            $std->item_price = null;
            $std->orderStatusId = null;
            $std->insert_date = null;
            $std->error = true;
            $std->message = null;

            // check user balance
            $assetType = null;
            $total_trade_val = null;
            if ($this->orderTypeId == 0) {
                $assetType = $WantAssetTypeId;
                $total_trade_val = $this->quantity * $this->price;
            } else if ($this->orderTypeId == 1) {
                $assetType = $OfferAssetTypeId;
                $total_trade_val = $this->quantity;
            }

            $customer_bal = (float)$this->check_customer_balance($assetType, $this->customerId)->balance;

            $this->customer_balance = $customer_bal;

            if ($this->customer_balance == '' || $this->customer_balance == null || !is_float($this->customer_balance)) {
                $messages = "0 balance: Your account balance is nill.";
                $std->message = $messages;
                //$this->storeMessages($order_id=null, $this->customerId, $messages);
                return $std;
            }

            if ($total_trade_val > $this->customer_balance) {
                $messages = "Insufficient balance: You have insufficient balance to continue this trade. Please recharge your wallet or lower the quantity.";
                $std->message = $messages;
                //$this->storeMessages($order_id=null, $this->customerId, $messages);
                return $std;
            }

            $query = $this->db_connection->prepare("INSERT INTO ".ORDERS_TABLE." (`id`, `uid`, `order_type`, `offer_asset`, `want_asset`, `qty`, `price`, `status`, `market_order`, `insert_dt`, `update_dt`)
                                    VALUES ('', " . $this->customerId . ", :a, :e, :f, :b, :c, :d, NULL, '$now', NULL)");

            $query->bindParam(':a', $this->orderTypeId, PDO::PARAM_STR);
            $query->bindParam(':e', $OfferAssetTypeId, PDO::PARAM_STR);
            $query->bindParam(':f', $WantAssetTypeId, PDO::PARAM_STR);
            $query->bindParam(':b', $this->quantity, PDO::PARAM_STR);
            $query->bindParam(':c', $this->price, PDO::PARAM_STR);
            $query->bindParam(':d', $this->orderStatusId);

            if ($query->execute()) {

                $insertedrowid = $this->db_connection->lastInsertId();

                // Check if $price is eligible to be inserted into top_buy or top_sell table
                $top_table = null;
                $asc_desc = null;
                $new_balance = null;
                $new_frozenbalance = null;

                if ($orderTypeId == '0') {
                    $top_table = TOP_BUYS_TABLE;
                } else if ($orderTypeId == '1') {
                    $top_table = TOP_SELLS_TABLE;
                }

                $trade_type = ($this->orderTypeId==1) ? "sell" : "buy";
                $messages = "You entered a $trade_type order for $qty token at $ $price per token for $ ".$qty*$price;

                //$this->storeMessages($insertedrowid, $this->customerId, $messages);

                // Change the order status to active and insert in active table in DB
                $insert_in_active_table = $this->insert_order_in_active_table($top_table, $WantAssetTypeId, $OfferAssetTypeId, $insertedrowid, $this->price, $this->quantity);

                if ($insert_in_active_table) {
                    $std->message = "Order moved to active table.";
                }

                $this->orderStatusId = 3; // order activated

                $std = new stdClass();
                $std->insertedrowid = $insertedrowid;
                $std->made_to_active_list = $insert_in_active_table;
                $std->orderTypeId = $this->orderTypeId; // 0 -> buy; 1 -> sell
                $std->item_qty = $qty;
                $std->item_price = $price;
                $std->orderStatusId = $this->orderStatusId;
                $std->insert_date = date('Y-m-d H:i:s');
                $std->insert_in_active_table = $insert_in_active_table;
                $std->error = false;

                return $std;
            }
            return null;
        }
        return false;
    }

    private function updateOrderStatus($orderId=null, $status=null) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("UPDATE ".ORDERS_TABLE." SET `status`= '$status' WHERE `id` = :id LIMIT 1");
            $query->bindParam("id", $orderId);
            if ($query->execute()) {
                return true;
            }
        }
        return false;
    }

    public function get_top_buy_sell_list($top_table, $WantAssetTypeId=null, $OfferAssetTypeId=null, $asc_desc) {

        if ($this->databaseConnection()) {

            $top_list = array();
            $st1 = "";
            if (trim($WantAssetTypeId) != null) {
                $st1 = " AND $top_table.bc1 = '".$WantAssetTypeId."' ";
            }
            $st2 = "";
            if (trim($OfferAssetTypeId) != null) {
                $st2 = " AND $top_table.bc2 = '".$OfferAssetTypeId."' ";
            }

            $query = $this->db_connection->query("
                SELECT $top_table.order_id, $top_table.uid, $top_table.quantity, $top_table.price, ".USERS_TABLE.".name, $top_table.bc1, $top_table.bc2
                FROM $top_table, ".USERS_TABLE."
                WHERE $top_table.uid = ".USERS_TABLE.".id
                $st1
                $st2
                ORDER BY price $asc_desc
                LIMIT $this->max_top_bids
            ");

            if ($query) {

                $rowCount = $query->rowCount();

                if ($rowCount > 0) {

                    while ($orders = $query->fetchObject()) {

                        $top_list[] = $orders;

                    }
                }

            } else {
                return false;
            }
            return $top_list;
        }
        return false;
    }

    private function get_highest_demand($bc1, $bc2) {
        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("SELECT ".TOP_BUYS_TABLE.".order_id, ".TOP_BUYS_TABLE.".price, ".TOP_BUYS_TABLE.".quantity FROM ".ORDERS_TABLE.", ".TOP_BUYS_TABLE." WHERE ".ORDERS_TABLE.".id = ".TOP_BUYS_TABLE.".order_id AND ".TOP_BUYS_TABLE.".bc1 =  '".$bc1."'
                                                AND ".TOP_BUYS_TABLE.".bc2 = '".$bc2."' ORDER BY ".TOP_BUYS_TABLE.".price DESC LIMIT 1");
            $rowCount_Qty = $query->rowCount();
            if (!$rowCount_Qty) {
                return false;
            }
            return $highest_demanded = $query->fetchObject();
        }
        return false;
    }

    public function get_active_buy_order_of_user($user_id, $bc=null, $top_table) {
        if ($this->databaseConnection()) {

            $st = "";
            if (trim($bc)!=null) {
                $st = " AND bc2 = :bc ";
            }
            $query = $this->db_connection->prepare("
                SELECT * FROM $top_table WHERE `uid`= :uid
                 ".$st."
                 ORDER BY `insert_dt` DESC
            ");
            $query->bindParam('uid', $user_id);
            if (trim($bc)!=null) {
                $query->bindParam('bc', $bc);
            }
            $query->execute();

            $arr = array();
            while ($qr = $query->fetchObject()) {
                $arr[] = $qr;
            }
            return $arr;
        }
        return false;
    }

    public function get_active_sell_order_of_user($user_id, $bc=null, $top_table) {
        if ($this->databaseConnection()) {
            $st = "";
            if (trim($bc)!=null) {
                $st = " AND bc1 = :bc ";
            }
            $query = $this->db_connection->prepare("
                SELECT * FROM $top_table WHERE `uid`= :uid
                 ".$st."
                 ORDER BY `insert_dt` DESC
            ");
            $query->bindParam('uid', $user_id);
            if (trim($bc)!=null) {
                $query->bindParam('bc', $bc);
            }
            $query->execute();

            $arr = array();
            while ($qr = $query->fetchObject()) {
                $arr[] = $qr;
            }
            return $arr;
        }
        return false;
    }


    public function OrderMatchingQuery($bc1, $bc2) {

        if ($this->databaseConnection()) {

            $query = $this->db_connection->query("
                SELECT ".TOP_SELLS_TABLE.".order_id, ".TOP_SELLS_TABLE.".price, ".TOP_SELLS_TABLE.".quantity, ".TOP_SELLS_TABLE.".order_id, (SELECT `uid` FROM ".TOP_BUYS_TABLE." ORDER BY price DESC LIMIT 1) AS BUYER_ID, ".TOP_SELLS_TABLE.".uid AS SELLER_ID
                FROM ".TOP_SELLS_TABLE.", ".ORDERS_TABLE."
                WHERE (
                 (".TOP_SELLS_TABLE.".price <= (SELECT `price` FROM ".TOP_BUYS_TABLE." ORDER BY price DESC LIMIT 1))
                 AND (".ORDERS_TABLE.".id = ".TOP_SELLS_TABLE.".order_id)
                 AND (".ORDERS_TABLE.".status = '3')
                 AND (".ORDERS_TABLE.".order_type= '1')
                 AND (".TOP_SELLS_TABLE.".bc1 = '".$bc1."')
                 AND (".TOP_SELLS_TABLE.".bc2 = '".$bc2."')
                 )
                ORDER BY ".TOP_SELLS_TABLE.".price ASC
            ");

            if($rowCount = $query->rowCount() > 0) { // Transaction is possible
                $matched_orders = array();
                while ($obj = $query->fetchObject()) {
                    $matched_orders[] = $obj;
                }
                return $matched_orders;
            }
            return false;
        }
        return false;
    }

    public function OrderMatchingService($bc1, $bc2) {

        if ($this->databaseConnection()) {

            $message = array();
            $trade_qty = 0;

            $get_highest_demand = $this->get_highest_demand($bc1, $bc2);
            if ($get_highest_demand == false) {
                return false;
            }

            $this->demanded_qty = (float)$get_highest_demand->quantity;
            $buy_order_id = $get_highest_demand->order_id;
            $buy_amount = $get_highest_demand->price;

            $supply_available = $this->OrderMatchingQuery($bc1, $bc2);

            if (trim($bc1) == '' || trim($bc2) == '' || $this->demanded_qty == false || $this->demanded_qty == '' || $this->demanded_qty == '0' || $supply_available == false || $supply_available == '' || !is_array($supply_available) || empty($supply_available)) {
                return false;
            }

            if(is_array($supply_available) && !empty($supply_available) ) {

                foreach ($supply_available as $available) {

                    if ($this->demanded_qty > 0) {
                        $supply_available = $this->OrderMatchingQuery($bc1, $bc2);
                        $seller_order_id = (int)$available->order_id;
                        $available->quantity = (float)$available->quantity;
                        $seller_id = $available->SELLER_ID;
                        $seller_balance_bc1 = $this->check_customer_balance($assetType = $bc1, $seller_id)->balance;
                        $seller_balance_bc2 = $this->check_customer_balance($assetType = $bc2, $seller_id)->balance;

                        $buyer_id = $available->BUYER_ID;
                        $buyer_balance_bc1 = $this->check_customer_balance($assetType = $bc1, $buyer_id)->balance;
                        $buyer_balance_bc2 = $this->check_customer_balance($assetType = $bc2, $buyer_id)->balance;

                        if ($this->demanded_qty > $available->quantity) {

                            $trade_qty = (float) $available->quantity;

                            $cost_of_total_supply = $available->quantity * $available->price;
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                /*Record the message*/
                                $this->storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You have insufficient ".$bc2." balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient ".$bc2." balance.";
                                }
                                // Delete the culprit order
                                $this->del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                /*Record the message*/
                                $this->storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient ".$bc1." balance");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient ".$bc1." balance";
                                }
                                $this->del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_bc2 = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_bc1 = $seller_balance_bc1 - $available->quantity; // deduct the btc sold

                                $new_buyer_balance_bc1 = $buyer_balance_bc1 + $available->quantity; // btc
                                $new_buyer_balance_bc2 = $buyer_balance_bc2 - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc1, $balance = $new_buyer_balance_bc1, $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc2, $balance = $new_seller_balance_bc2, $seller_id);

                                // record the commission in the commission account
                                // decrease respective balances
                                $this->update_user_balance($assetType = $bc1, $balance = $new_seller_balance_bc1, $seller_id);
                                $this->update_user_balance($assetType = $bc2, $balance = $new_buyer_balance_bc2, $buyer_id);
                            }

                            $this->demanded_qty = $this->demanded_qty - $available->quantity;

                            // update the quantity field for demand
                            $this->update_quantity($top_table = TOP_BUYS_TABLE, $this->demanded_qty, $buy_order_id);

                            // Delete the row from Sell list
                            $this->delete_order(TOP_SELLS_TABLE, $available->order_id);

                            // Mark this order status 1 i.e transaction successful
                            $this->updateOrderStatus($available->order_id, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $available->quantity ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $seller_msg = "Transaction successful: You sold $available->quantity ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $this->storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            $this->storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if ($this->check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            $available->quantity = 0;

                        } elseif ($this->demanded_qty == $available->quantity) {

                            $trade_qty = (float) $available->quantity;

                            $cost_of_total_supply = $available->quantity * $available->price;
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                /*Record the message*/
                                $this->storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You had insufficient ".$bc2." balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient ".$bc2." balance.";
                                }
                                $this->del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                /*Record the message*/
                                $this->storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient ".$bc1." balance.");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient ".$bc1." balance";
                                }
                                $this->del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_bc2 = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_bc1 = $seller_balance_bc1 - $available->quantity; // deduct the btc sold

                                $new_buyer_balance_bc1 = $buyer_balance_bc1 + $available->quantity; // btc
                                $new_buyer_balance_bc2 = $buyer_balance_bc2 - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc1, $balance = $new_buyer_balance_bc1, $user_id = $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc2, $balance = $new_seller_balance_bc2, $user_id = $seller_id);

                                // decrease respective balances
                                $this->update_user_balance($assetType = $bc1, $balance = $new_seller_balance_bc1, $user_id = $seller_id);
                                $this->update_user_balance($assetType = $bc2, $balance = $new_buyer_balance_bc2, $user_id = $buyer_id);
                            }

                            // Delete the row from Sell list And Buy list
                            $this->delete_order(TOP_SELLS_TABLE, $available->order_id);
                            $this->delete_order(TOP_BUYS_TABLE, $buy_order_id);

                            // Mark trades of buyer & seller 1 i.e 'successful'
                            $this->updateOrderStatus($available->order_id, $status='1');
                            $this->updateOrderStatus($buy_order_id, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $available->quantity ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $seller_msg = "Transaction successful: You sold $available->quantity ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $this->storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            $this->storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if ($this->check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            // save changes
                            $this->demanded_qty = 0;
                            $available->quantity = 0;

                        } elseif ($this->demanded_qty < $available->quantity) {

                            $trade_qty = (float) $this->demanded_qty;

                            $cost_of_total_supply = $this->demanded_qty * $available->price; // traditional or cash
                            $cost_of_total_supply = (float) $cost_of_total_supply;

                            if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                /*Record the message*/
                                $this->storeMessages($buy_order_id, $buyer_id, $msg="Transaction failed: You had insufficient ".$bc2." balance.");
                                if ($_SESSION['user_id'] == $buyer_id) {
                                    $message[] = "Transaction failed: You have insufficient ".$bc2." balance.";
                                }
                                $this->del_order($buy_order_id, $buyer_id);
                                break;
                            }
                            if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $this->demanded_qty)) {
                                /*Record the message*/
                                $this->storeMessages($seller_order_id, $seller_id, $msg="Transaction failed: You had insufficient ".$bc1." balance.");
                                if ($_SESSION['user_id'] == $seller_id) {
                                    $message[] = "Transaction failed: You had insufficient ".$bc1." balance";
                                }
                                $this->del_order($seller_order_id, $seller_id);
                                break;
                            }

                            if ($buyer_id != $seller_id) {
                                $new_seller_balance_bc2 = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                $new_seller_balance_bc1 = $seller_balance_bc1 - $this->demanded_qty; // deduct the btc sold

                                $new_buyer_balance_bc1 = $buyer_balance_bc1 + $this->demanded_qty; // btc
                                $new_buyer_balance_bc2 = $buyer_balance_bc2 - $cost_of_total_supply; // traditional or cash

                                // subtract the debit access (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc1, $balance = $new_buyer_balance_bc1, $user_id = $buyer_id);

                                // increment the credit asset (customers balance of $ or BTC)
                                $this->update_user_balance($assetType = $bc2, $balance = $new_seller_balance_bc2, $user_id = $seller_id);

                                // record the commission in the commission account
                                // decrease respective balances
                                $this->update_user_balance($assetType = $bc1, $balance = $new_seller_balance_bc1, $user_id = $seller_id);

                                $this->update_user_balance($assetType = $bc2, $balance = $new_buyer_balance_bc2, $user_id = $buyer_id);

                            }

                            // update the quantity field for $availableQuantity
                            $availableQuantity = $available->quantity - $this->demanded_qty;
                            $this->update_quantity($top_table = TOP_SELLS_TABLE, $availableQuantity, $available->order_id);

                            // Delete the row from Buy list
                            $this->delete_order(TOP_BUYS_TABLE, $buy_order_id);

                            // Mark this order status 1 i.e transaction successful
                            $this->updateOrderStatus($buy_order_id, $status='1');

                            // Record messages
                            $buyer_msg = "Transaction successful: You bought $this->demanded_qty ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $seller_msg = "Transaction successful: You sold $this->demanded_qty ".$bc1." for ".$bc2." $cost_of_total_supply at the rate of ".$bc2." $available->price per unit.";
                            $this->storeMessages($buy_order_id, $buyer_id, $msg=$buyer_msg);
                            $this->storeMessages($seller_order_id, $seller_id, $msg=$seller_msg);

                            if(isset($_SESSION['user_id'])) {
                                $logged_in_user = (int) $_SESSION['user_id'];
                                if ($this->check_user($logged_in_user) != false) {
                                    if ($logged_in_user == $buyer_id) {
                                        $message[] = $buyer_msg;
                                    } else if($logged_in_user == $seller_id) {
                                        $message[] = $seller_msg;
                                    }
                                }
                            }

                            // save changes
                            $this->demanded_qty = 0;
                        }

                        // Record the transaction
                        $this->record_transaction($buyer_id,$buy_order_id, $buy_amount, $bc1, $seller_id, $available->order_id, $available->price, $bc2, $trade_qty);
                    } else {
                        return false;
                    }
                    $this->OrderMatchingQuery($bc1, $bc2);
                }
                return $message;
            }
            return false;
        }
        return false;
    }

    private function insert_market_order($customerId, $orderTypeId, $OfferAssetTypeId=null, $WantAssetTypeId=null, $qty, $price) {
        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $query = $this->db_connection->prepare("INSERT INTO ".ORDERS_TABLE." (`id`, `uid`, `order_type`, `offer_asset`, `want_asset`, `qty`, `price`, `status`, `market_order`, `insert_dt`, `update_dt`)
                                                    VALUES ('', :u, :a, :d, :e, :b, :c, 1, 1, '$now', NULL)");
            $query->bindParam(':u', $customerId, PDO::PARAM_INT);
            $query->bindParam(':a', $orderTypeId, PDO::PARAM_INT);
            $query->bindParam(':d', $OfferAssetTypeId, PDO::PARAM_STR);
            $query->bindParam(':e', $WantAssetTypeId, PDO::PARAM_STR);
            $query->bindParam(':b', $qty, PDO::PARAM_STR);
            $query->bindParam(':c', $price, PDO::PARAM_STR);

            if ($query->execute()) {
                $insertedrowid = $this->db_connection->lastInsertId();

                $trade_type = ($orderTypeId=='1') ? "sell" : "buy";
                $messages = "You entered an instant $trade_type order for $qty units at $OfferAssetTypeId $price per unit for $OfferAssetTypeId ".$qty*$price;
                $this->storeMessages($insertedrowid, $customerId, $messages);

                return (int) $insertedrowid;
            }
            return false;
        }
        return false;
    }

    public function market_order($order_type, $qty, $OfferAssetTypeId, $WantAssetTypeId) {

    if ($this->databaseConnection()) {

        $message = array();

        // Check if it is a buy or sell
        if ($order_type == 'm-buy') {

            if (is_float($qty) || is_int($qty)) {
                if ($qty > 0) {

                    $sell_list = $this->get_top_buy_sell_list($top_table = TOP_SELLS_TABLE, $WantAssetTypeId, $OfferAssetTypeId, $asc_desc = 'ASC');

                    if (!empty($sell_list)) {

                        /*Code to find the last iteration of loop. Required to print shortage of token supply.*/
                        end($sell_list);
                        $last_iter = key($sell_list);

                        foreach ($sell_list as $key => $available) {

                            $trade_qty = 0;
                            $sell_order_id = (int)$available->order_id;
                            $available->quantity = (float)$available->quantity;

                            if ($available->quantity <= 0 || $qty <= 0) {
                                if (isset($message)) {
                                    if ($available->quantity <= 0) {
                                        $message[] = "Oops! There's shortage of the availability of Blockchain Contracts.";
                                    } else if ($qty <= 0) {
                            //$message[] = "The demanded asset is nill.";
                                    }
                                } else {
                                    exit;
                                }
                                return $message;
                            }

                            $seller_id = $available->uid;
                            $seller_balance_bc2 = (float)$this->check_customer_balance($assetType = $OfferAssetTypeId, $seller_id)->balance;
                            $seller_balance_bc1 = (float)$this->check_customer_balance($assetType = $WantAssetTypeId, $seller_id)->balance;
                            $buyer_id = $_SESSION['user_id'];
                            $buyer_balance_bc2 = (float)$this->check_customer_balance($assetType = $OfferAssetTypeId, $buyer_id)->balance;
                            $buyer_balance_bc1 = (float)$this->check_customer_balance($assetType = $WantAssetTypeId, $buyer_id)->balance;

                            switch ($qty) {
                                case ($qty > $available->quantity):

                                    $trade_qty = (float)$available->quantity;
                                    $cost_of_total_supply = $available->quantity * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Transaction failed: You have insufficient ".$OfferAssetTypeId." balance.";
                                        $this->storeMessages($buy_order_id = null, $buyer_id, $msg = "Transaction failed: You had insufficient ".$OfferAssetTypeId." balance.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                        $message[] = "Transaction failed: The seller has insufficient ".$WantAssetTypeId." balance.";
                                        $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction failed: The seller has insufficient ".$WantAssetTypeId." balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $available->quantity;
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $available->quantity; // traditional or cash
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '0', $OfferAssetTypeId, $WantAssetTypeId, $available->quantity, $available->price);

                                    $buy_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $buy_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// increase the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

// Record the transaction
                                    $this->record_transaction($buyer_id, $buy_order_id, $available->price, $WantAssetTypeId, $seller_id, $available->order_id, $available->price, $OfferAssetTypeId, $trade_qty);

// Delete the row from Sell list
                                    $this->delete_order(TOP_SELLS_TABLE, $available->order_id);

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($available->order_id, '1');

                                    $message[] = "Instant Transaction Successful: You bought $available->quantity ".$WantAssetTypeId." at ".$OfferAssetTypeId." $available->price per unit for ".$OfferAssetTypeId." $cost_of_total_supply.";

// Record message in DB
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Instant Transaction Successful: You bought $available->quantity $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction Successful: You sold $available->quantity $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");

                                    $qty = $qty - $available->quantity;
                                    $available->quantity = 0;
// save changes
                                    break;
                                case ($qty == $available->quantity):

                                    $trade_qty = (float)$available->quantity;
                                    $cost_of_total_supply = $available->quantity * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Instant Transaction failed: You have insufficient $OfferAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id = null, $buyer_id, $msg = "Transaction failed: You had insufficient $OfferAssetTypeId balance.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                        $message[] = "Instant Transaction failed: The seller has insufficient $WantAssetTypeId balance.";
                                        $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction failed: The seller has insufficient $WantAssetTypeId balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $qty;
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $available->quantity; // traditional or cash

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '0', $OfferAssetTypeId, $WantAssetTypeId, $available->quantity, $available->price);

                                    $buy_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $buy_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// increase the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

                                    $message[] = "Instant Transaction Successful: You bought $qty $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.";

// Record message in DB
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Instant Transaction Successful: You bought $available->quantity $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction Successful: You sold $available->quantity $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");

                                    $qty = $qty - $available->quantity; // should be equal to 0
                                    $available->quantity = 0;

// Record the transaction
                                    $this->record_transaction($buyer_id, $buy_order_id, $available->price, $WantAssetTypeId, $seller_id, $available->order_id, $available->price, $OfferAssetTypeId, $trade_qty);

// Delete the row from Sell list
                                    $this->delete_order(TOP_SELLS_TABLE, $available->order_id);

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($buy_order_id, '1');
                                    $this->UpdateOrderStatus($available->order_id, '1');

                                    break;
                                case ($qty < $available->quantity):

                                    $trade_qty = (float)$qty;
                                    $cost_of_total_supply = $qty * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Instant Transaction failed: You have insufficient $OfferAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id = null, $buyer_id, $msg = "Transaction failed: You had insufficient $OfferAssetTypeId balance.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $qty)) {
                                        $message[] = "Instant Transaction failed: The seller has insufficient $WantAssetTypeId balance.";
                                        $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction failed: The seller has insufficient $WantAssetTypeId balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $qty;
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $qty; // traditional or cash

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '0', $OfferAssetTypeId, $WantAssetTypeId, $qty, $available->price);

                                    $buy_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $buy_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// increase the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

// Record the transaction
                                    $this->record_transaction($buyer_id, $buy_order_id, $available->price, $WantAssetTypeId, $seller_id, $available->order_id, $available->price, $OfferAssetTypeId, $trade_qty);

                                    $available->quantity = $available->quantity - $qty;

// update the quantity field for supply
                                    $this->update_quantity($top_table = TOP_SELLS_TABLE, $available->quantity, $available->order_id);

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($buy_order_id, '1');

                                    $message[] = "Instant Transaction Successful: You bought $qty $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.";
// Record message in DB
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Instant Transaction Successful: You bought $qty $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Transaction Successful: You sold $qty $WantAssetTypeId at $OfferAssetTypeId $available->price per unit for $OfferAssetTypeId $cost_of_total_supply.");

// update the quantity field for demand
                                    $qty = 0;

                                    break;
                            }
                            if (($available->quantity <= 0) && ($qty > 0) && ($key === $last_iter)) {
                                /*The supply of token is 0. Stop further transaction. */
                                $message[] = "Instant Transaction failure: There's no token left to be sold any more. $qty tokens could not be bought.";
                                $this->storeMessages($buy_order_id = null, $buyer_id, $msg = "There's no token left to be sold any more. $qty tokens could not be bought.");
                            }
                        }
                        return $message;
                    } else {
                        $message[] = "empty_sell_list";
                        return $message;
                    }
                }
            }

        } elseif ($order_type == 'm-sell') {
            if (is_float($qty) || is_int($qty)) {
                if ($qty > 0) {

                    $buy_list = $this->get_top_buy_sell_list($top_table = TOP_BUYS_TABLE, $WantAssetTypeId, $OfferAssetTypeId, $asc_desc = 'DESC');

                    if (!empty($buy_list)) {
                        foreach ($buy_list as $available) {

                            $trade_qty = 0;
                            $buy_order_id = (int)$available->order_id;
                            $available->quantity = (float)$available->quantity;

                            if ($available->quantity <= 0 || $qty <= 0) {
                                if (isset($message)) {
                                    if ($available->quantity <= 0) {
                                        $message[] = "Instant Transaction Failure: The available asset is nill.";
                                    } else if ($qty <= 0) {
//$message[] = "The demanded asset is nill.";
                                    }
                                    return $message;
                                } else {
                                    exit;
                                }
                            }

                            $seller_id = $_SESSION['user_id'];
                            $seller_balance_bc2 = $this->check_customer_balance($assetType = $OfferAssetTypeId, $seller_id)->balance;
                            $seller_balance_bc1 = $this->check_customer_balance($assetType = $WantAssetTypeId, $seller_id)->balance;
                            $buyer_id = $available->uid;
                            $buyer_balance_bc2 = $this->check_customer_balance($assetType = $OfferAssetTypeId, $buyer_id)->balance;
                            $buyer_balance_bc1 = $this->check_customer_balance($assetType = $WantAssetTypeId, $buyer_id)->balance;

                            switch ($qty) {
                                case ($qty > $available->quantity):

                                    $trade_qty = (float)$available->quantity;
                                    $cost_of_total_supply = $available->quantity * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Instant Transaction failed: The buyer has insufficient $OfferAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $OfferAssetTypeId balance.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "A transaction was unprocessed due to insufficient $OfferAssetTypeId balance of buyer with id $buyer_id.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                        $message[] = "Instant Transaction failed: You have insufficient $WantAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $WantAssetTypeId balance of seller with id $seller_id.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "Transaction failed: You have insufficient $WantAssetTypeId balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $available->quantity; // deduct the btc sold
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $available->quantity; // btc

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '1', $OfferAssetTypeId, $WantAssetTypeId, $available->quantity, $available->price);

                                    $sell_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $sell_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// increase the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

// Delete the row from buy list
                                    $this->delete_order(TOP_BUYS_TABLE, $available->order_id);

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($available->order_id, '1');

// Record the transaction
                                    $this->record_transaction($buyer_id, $available->order_id, $available->price, $WantAssetTypeId, $seller_id, $sell_order_id, $available->price, $OfferAssetTypeId, $trade_qty);

                                    $message[] = "Instant Transaction Successful: You sold $available->quantity $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.";
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Instant Transaction Successful: You sold $available->quantity $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Transaction Successful: You bought $available->quantity $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");

                                    $qty = $qty - $available->quantity;

                                    break;
                                case ($qty == $available->quantity):

                                    $trade_qty = (float)$available->quantity;
                                    $cost_of_total_supply = $available->quantity * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Instant Transaction failed: The buyer has insufficient $OfferAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $OfferAssetTypeId balance.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "A transaction was unprocessed due to insufficient $OfferAssetTypeId balance of buyer with id $buyer_id.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $available->quantity)) {
                                        $message[] = "Instant Transaction failed: You have insufficient $WantAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $WantAssetTypeId balance of seller with id $seller_id.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "Transaction failed: You have insufficient $WantAssetTypeId balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $available->quantity; // deduct the btc sold
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $available->quantity; // traditional or cash

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '1', $OfferAssetTypeId, $WantAssetTypeId, $available->quantity, $available->price);

                                    $sell_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $sell_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// subtract the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

                                    $message[] = "Instant Transaction Successful: You sold $qty $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.";
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Instant Transaction Successful: You sold $available->quantity $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Transaction Successful: You bought $available->quantity $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");

                                    $qty = $qty - $available->quantity;

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($sell_order_id, '1');
                                    $this->UpdateOrderStatus($available->order_id, '1');

// Record the transaction
                                    $this->record_transaction($buyer_id, $available->order_id, $available->price, $WantAssetTypeId, $seller_id, $sell_order_id, $available->price, $OfferAssetTypeId, $trade_qty);

// Delete the row from buy list
                                    $this->delete_order(TOP_BUYS_TABLE, $available->order_id);

                                    break;
                                case ($qty < $available->quantity):

                                    $trade_qty = (float)$qty;
                                    $available->quantity = $available->quantity - $qty;
                                    $cost_of_total_supply = $qty * $available->price; // traditional or cash

                                    if ($buyer_balance_bc2 < $cost_of_total_supply) {
                                        $message[] = "Instant Transaction failed: The buyer has insufficient $OfferAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $OfferAssetTypeId balance.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "A transaction was unprocessed due to insufficient $OfferAssetTypeId balance of buyer with id $buyer_id.");
                                        return $message;
                                    }
                                    if (($seller_balance_bc1 == 0) || ($seller_balance_bc1 < $qty)) {
                                        $message[] = "Instant Transaction failed: You have insufficient $WantAssetTypeId balance.";
                                        $this->storeMessages($buy_order_id, $buyer_id, $msg = "Your order no $buy_order_id was unprocessed due to insufficient $WantAssetTypeId balance of seller with id $seller_id.");
                                        $this->storeMessages($sell_order_id = null, $seller_id, $msg = "Transaction failed: You have insufficient $WantAssetTypeId balance.");
                                        return $message;
                                    }

                                    $new_seller_cash_balance = $seller_balance_bc2 + $cost_of_total_supply;  // traditional or cash
                                    $new_seller_bit_balance = $seller_balance_bc1 - $qty; // deduct the btc sold
                                    $new_buyer_cash_balance = $buyer_balance_bc2 - $cost_of_total_supply; // traditional
                                    $new_buyer_bit_balance = $buyer_balance_bc1 + $qty; // traditional or cash

                                    $insert_market_order = $this->insert_market_order($_SESSION['user_id'], $orderTypeId = '1', $OfferAssetTypeId, $WantAssetTypeId, $qty, $available->price);

                                    $sell_order_id = 0;
                                    if ($insert_market_order == false) {
                                        return false;
                                    } else if (is_int($insert_market_order)) {
                                        $sell_order_id = (int)$insert_market_order;
                                    } else {
                                        return false;
                                    }

                                    if ($buyer_id != $seller_id) {
// increment the bits of buyer
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_buyer_bit_balance, $user_id = $buyer_id);

// deduct cash of buyer
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_buyer_cash_balance, $user_id = $buyer_id);

// subtract the cash of seller
                                        $this->update_user_balance($assetType = $OfferAssetTypeId, $balance = $new_seller_cash_balance, $user_id = $seller_id);

// deduct bits of seller
                                        $this->update_user_balance($assetType = $WantAssetTypeId, $balance = $new_seller_bit_balance, $user_id = $seller_id);
                                    }

// Record the transaction
                                    $this->record_transaction($buyer_id, $available->order_id, $available->price, $WantAssetTypeId, $seller_id, $sell_order_id, $available->price, $OfferAssetTypeId, $trade_qty);

// update the quantity field for supply
                                    $this->update_quantity($top_table = TOP_BUYS_TABLE, $available->quantity, $available->order_id);

// Update Order Status in Order table
                                    $this->UpdateOrderStatus($sell_order_id, '1');

                                    $message[] = "Instant Transaction Successful: You sold $qty $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.";
                                    $this->storeMessages($sell_order_id, $seller_id, $msg = "Instant Transaction Successful: You sold $qty $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");
                                    $this->storeMessages($buy_order_id, $buyer_id, $msg = "Transaction Successful: You bought $qty $WantAssetTypeId for $OfferAssetTypeId $cost_of_total_supply.");

// update the quantity field for demand
                                    $qty = 0;

                                    break;
                            }
                        }
                        return $message;
                    } else {
                        $message[] = "empty_buy_list";
                        return $message;
                    }
                }
            }
        }
    }
    return false;
}

    public function update_user_balance($assetType, $balance=null, $user_id) {

        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $sql = "";

            if ($balance >= 0) {
                $sql .= "UPDATE ".CREDITS_TABLE." ";
                $sql .= " SET `balance`= :balance, ";
                $sql .= " `update_date`= '$now' ";
                $sql .= " WHERE `uid`= :user_id ";
                $sql .= " AND `bc`= :asset_type ";
                $sql .= "LIMIT 1";

                $query = $this->db_connection->prepare($sql);

                if ($balance >= 0) {
                    $query->bindParam("balance", $balance);
                }
                $query->bindParam("user_id", $user_id);
                $query->bindParam("asset_type", $assetType);
                if ($query->execute()) {
                    //$this->record_bal_history($user_id, $balance, $assetType);
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    private function record_transaction($buyer, $buy_order_id, $buy_amount, $buy_bc, $seller, $sell_order_id, $sell_amount, $sell_bc, $trade_qty) {
        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $query = $this->db_connection->prepare("
            INSERT INTO ".TX_TABLE."(`txid`, `a_buyer`, `a_order_id`, `a_amount`, `a_bc`, `b_seller`, `b_order_id`, `b_amount`, `b_bc`, `qty_traded`, `insert_dt`, `update_dt`)
            VALUES ('', :buyer,:buy_order_id, :buy_amount, :buy_bc, :seller, :sell_order_id, :sell_amount, :sell_bc, :trade_qty, '$now', NULL)
            ");
            $query->bindParam("buyer", $buyer);
            $query->bindParam("buy_order_id", $buy_order_id);
            $query->bindParam("buy_amount", $buy_amount);
            $query->bindParam("buy_bc", $buy_bc);
            $query->bindParam("seller", $seller);
            $query->bindParam("sell_order_id", $sell_order_id);
            $query->bindParam("sell_amount", $sell_amount);
            $query->bindParam("sell_bc", $sell_bc);
            $query->bindParam("trade_qty", $trade_qty);
            if($query->execute()) {
                return true;
            }
        }
        return false;
    }

    private function delete_order($top_table, $orderId) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("DELETE FROM `$top_table` WHERE `order_id`=:id LIMIT 1");
            $query->bindParam('id', $orderId);
            if($query->execute()) {
                return true;
            }
            return false;
        }
        return false;
    }

    private function update_quantity($top_table, $qty, $orderId) {

        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $query = $this->db_connection->prepare("
                UPDATE $top_table
                SET `quantity`= :qty, `insert_dt`='$now'
                WHERE order_id = :orderId
                LIMIT 1
            ");
            $query->bindParam('qty', $qty);
            $query->bindParam('orderId', $orderId);
            if($query->execute()) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function del_order($order_id, $usid=null) {
        if ($this->databaseConnection()) {

            $user_id = 0;
            if (!isset($_SESSION['user_id'])) {
                return false;
            }
            $user_id = (int) $_SESSION['user_id'];
            // Allow Admin to delete order, if its not admin check owner of order
            if ($usid == null) {
                $is_owner = $this->isUserOrderOwner($order_id, $user_id);

                if (!$is_owner) {
                    return false;
                }
            } else if(($usid != null) && ($user_id == ADMIN_ID)) {   // This else part to be used by admin in delete_orders_of_user()
                $user_id = $usid;
            } else {
                return false;
            }

            // Finally cancel the order
            return $this->cancel_order($order_id, $user_id);
        }
        return false;
    }

    private function isUserOrderOwner($order_id=0, $user_id=0) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("
                SELECT `id` FROM ".ORDERS_TABLE."
                WHERE `id`=:o_id
                AND `uid`=:c_id
                LIMIT 1
            ");
            $query->bindParam('o_id', $order_id);
            $query->bindParam('c_id', $user_id);
            if ($query->execute()) {
                if ($query->rowCount()==1) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function cancel_order($order_id=null, $user_id=null) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("
                DELETE FROM ".TOP_BUYS_TABLE." WHERE `order_id`=:id AND uid = :cus_id;
                DELETE FROM ".TOP_SELLS_TABLE." WHERE `order_id`=:id AND uid = :cus_id
            ");

            $query->bindParam('id', $order_id);
            $query->bindParam('cus_id', $user_id);

            $query->execute();
            unset($query); // Unset the query

            $q = $this->db_connection->prepare("
                    UPDATE ".ORDERS_TABLE." SET `status`= 0
                    WHERE `id` = :ord
                    AND uid = :cust_id
                  ");
            $q->bindParam('ord', $order_id);
            $q->bindParam('cust_id', $user_id);

            $q->execute();
            unset($q);

            $query2 = $this->db_connection->prepare("
                        SELECT * FROM ".TOP_BUYS_TABLE." WHERE `order_id`=:o_id;
                        SELECT * FROM ".TOP_SELLS_TABLE." WHERE `order_id`=:o_id
                    ");
            $query2->bindParam('o_id', $order_id);

            if ($query2->execute()) {
                if ($query2->rowCount() == 0) {
                    if ($_SESSION['user_id']==ADMIN_ID) {
                        $this->storeMessages($order_id, ADMIN_ID, $msg="Order number $order_id was deleted by user id ".ADMIN_ID);
                        $this->storeMessages($order_id, $user_id, $msg="Order number $order_id was deleted by Admin.");
                    } else {
                        $this->storeMessages($order_id, $user_id, $msg="Order number $order_id was deleted by you.");
                    }
                    return true; // This means row was actually deleted
                }
            }
        }
        return false;
    }

    public function storeMessages($order_id=null, $user_id=null, $msg=null) {
        if($this->databaseConnection()) {
            $now = $this->time_now();
            if ($user_id == false) {
                return false;
            }
            $username = $this->get_username($user_id);

            $query = $this->db_connection->prepare("
                INSERT INTO ".MSG_TABLE."(`id`, `order_id`, `username_key`, `username`, `messages`, `datetime`)
                VALUES ('', :order_id, :user_id, :username, :msg, '$now')
            ");
            $query->bindParam("order_id", $order_id);
            $query->bindParam("user_id", $user_id);
            $query->bindParam("username", $username);
            $query->bindParam("msg", $msg);

            if ($query->execute()) {
                return true;
            }
        }
        return false;
    }

    public function last_transaction_list($start=0, $limit = 10, $a_bc=null, $b_bc=null, $uid=null) {
        if ($this->databaseConnection()) {

            $list = array();
            $st = "";
            $st2 = "";
            if ((int)$uid!=0 || (int)$uid!=null) {
                $st2 = " AND a_buyer = $uid OR b_seller=$uid ";
            }
            if (trim($a_bc)!=null && trim($b_bc == null)) {
                $st = "WHERE ".TX_TABLE.".a_bc = '".$a_bc."'";
            } elseif(trim($a_bc)==null && trim($b_bc)!=null) {
                $st = "WHERE ".TX_TABLE.".b_bc = '".$b_bc."'";
            } elseif(trim($a_bc)!=null && trim($b_bc)!=null) {
                $st = "WHERE ".TX_TABLE.".a_bc = '".$a_bc."' AND ".TX_TABLE.".b_bc = '".$b_bc."'";
            } elseif (trim($a_bc)==null && trim($b_bc)==null && $uid!=null) {
                $st2 = " WHERE a_buyer = $uid OR b_seller=$uid ";
            }
            $st.= $st2;

            $query = $this->db_connection->query("
                SELECT txid AS T_ID, a_buyer AS BUYER_ID, b_seller AS SELLER_ID, (SELECT ".USERS_TABLE.".name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".id=BUYER_ID) AS BUYER, (SELECT ".USERS_TABLE.".name FROM ".USERS_TABLE." WHERE ".USERS_TABLE.".id=SELLER_ID) AS SELLER, b_amount AS TRADE_PRICE, ".TX_TABLE.".insert_dt, ".TX_TABLE.".qty_traded AS TRADED_QTY
                FROM ".TX_TABLE.", ".USERS_TABLE."
                ".$st."
                GROUP BY T_ID
                ORDER BY T_ID DESC
                LIMIT $start, $limit
            ");

            if ($query->rowCount() > 0) {
                while ($ls = $query->fetchObject()) {
                    $list[] = $ls;
                }
                return $list;
            }
            return false;
        }
        return false;
    }

    public function UserBalanceList($bc1='', $is_active=null) {
        if ($this->databaseConnection()) {

            $list = array();

            $extraQuerry = "";
            $extraQuerry1 = "";
            $extraQuerry2 = "";

            if ($is_active != null) {
                $extraQuerry = "WHERE (".USERS_TABLE.".is_active = 0 OR ".USERS_TABLE.".is_active = 1) AND ".USERS_TABLE.".id = ".CREDITS_TABLE.".uid";
            } else {
                $extraQuerry = "WHERE ".USERS_TABLE.".is_active = 1 AND ".USERS_TABLE.".id = ".CREDITS_TABLE.".uid";
            }

            if (trim($bc1)!=null) {
                $extraQuerry1 = "AND ".CREDITS_TABLE.".bc = :bc1";
                $extraQuerry2 = "ORDER BY ".CREDITS_TABLE.".balance DESC";
            } else {
                $extraQuerry2 = "ORDER BY ".USERS_TABLE.".name ASC";
            }

            $query = $this->db_connection->prepare("
                SELECT DISTINCT ".USERS_TABLE.".name, ".USERS_TABLE.".id AS UID, ".USERS_TABLE.".fb_id AS FACEBOOK_ID, ".CREDITS_TABLE.".balance, ".CREDITS_TABLE.".bc, ".USERS_TABLE.".is_active
                FROM ".USERS_TABLE.", ".CREDITS_TABLE."
                $extraQuerry
                $extraQuerry1
                $extraQuerry2
            ");

            if (trim($bc1)!=null) {
                $query->bindParam('bc1', $bc1);
            }
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($ls = $query->fetchObject()) {
                    $list[] = $ls;
                }
                return $list;
            }
            return false;
        }
        return false;
    }

    public function UserOrdersList($user_id, $start=0, $limit=10) {
        if ($this->databaseConnection()) {

            $list = array();
            $query = $this->db_connection->prepare("
            SELECT *
            FROM ".ORDERS_TABLE."
            WHERE `uid`=:u_id
            ORDER  BY insert_dt DESC
            LIMIT $start, $limit
            ");
            $query->bindParam('u_id', $user_id);
            if ($query->execute()) {
                if ($query->rowCount() > 0) {
                    while ($ls = $query->fetchObject()) {
                        $list[] = $ls;
                    }
                    return $list;
                }
            }
            return false;
        }
        return false;
    }

    public function tx_data($bc1=null, $bc2=null, $limit=null) {
        if ($this->databaseConnection()) {
            $st = '';
            $st2 = '';
            if ($bc1!=null && $bc2!=null) {
                $st = 'WHERE a_bc = :a AND b_bc = :b GROUP BY b_bc ';
            } else if ($bc1!=null && $bc2==null) {
                $st = 'WHERE a_bc = :a GROUP BY a_bc ';
            } else if ($bc1==null && $bc2!=null) {
                $st = 'WHERE b_bc = :b GROUP BY b_bc ';
            } else {
                $st=' GROUP BY a_bc ';
            }
            if ($limit != null) {
                $st2 = " LIMIT $limit";
            }
            $query = $this->db_connection->prepare("
                    SELECT DISTINCT *
                    FROM ".TX_TABLE."
                    ".$st."
                    ORDER BY insert_dt DESC
                    $st2
            ");
            if ($bc1!=null && $bc2!=null) {
                $query->bindParam("a", $bc1);
                $query->bindParam("b", $bc2);
            } else if ($bc1!=null && $bc2==null) {
                $query->bindParam("a", $bc1);
            } else if ($bc1==null && $bc2!=null) {
                $query->bindParam("b", $bc2);
            }

            $query->execute();
            $dat = null;
            if ($query->rowCount()) {
                if ($limit > 1 || $limit==null) {
                    $dat = array();
                    while ($data = $query->fetchObject()) {
                        $dat[] = $data;
                    }
                } else {
                    $dat = $query->fetchObject();
                }
            }
            return $dat;
        }
        return false;
    }

    public function get_bc1_to_bc2_eq($bc1, $bc2) {
        $res = "";
        if (trim($bc1) !="" && trim($bc2) !="") {
            $res = $this->tx_data($bc1, $bc2, $limit=1);
        }
        return $res;
    }

    public function record_root_bal_update($uid, $bal_prev, $bal_now, $bal_type) {
        if ($this->databaseConnection()) {
            $now = $this->time_now();
            $root = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $query = $this->db_connection->prepare("
                INSERT INTO ".ADMIN_BAL_RECORDS."(`BalStatusHistoryId`, `user_id`, `bal_prev`, `bal_now`, `type`, `root_id`, `UpdateDate`)
                VALUES ('', :uid, :prev, :now, :btype, :root, '$now')
            ");
            $query->bindParam("uid", $uid);
            $query->bindParam("prev", $bal_prev);
            $query->bindParam("now", $bal_now);
            $query->bindParam("btype", $bal_type);
            $query->bindParam("root", $root);

            if ($query->execute()) {
                return true;
            }
        }
        return false;
    }

    public function list_root_bal_changes() {
        if ($this->databaseConnection()) {
            $list_details = array();
            $query = $this->db_connection->prepare("
                SELECT ".ADMIN_BAL_RECORDS.".*, ".USERS_TABLE.".name, ".USERS_TABLE.".email
                FROM ".ADMIN_BAL_RECORDS.", ".USERS_TABLE."
                WHERE ".ADMIN_BAL_RECORDS.".user_id=".USERS_TABLE.".id
                ORDER BY UpdateDate DESC
                LIMIT 200
            ");
            $query->execute();

            if ($query->rowCount() > 0) {
                while ($list = $query->fetchObject()) {
                    $list_details[] = $list;
                }
            }
            return $list_details;
        }
        return false;
    }

    public function get_last_order_date($date=null) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("SELECT * FROM ".ORDERS_TABLE." WHERE `insert_dt`> '$date'");
            if ($query->rowCount()) {
                return true;
            }
        }
        return false;
    }

    public function delete_orders_of_user($user_id=null) {
        if ($this->databaseConnection()) {
            $order_ids = array();
            $query = $this->db_connection->prepare("
            SELECT order_id FROM ".TOP_BUYS_TABLE." WHERE `uid`=:uid
            UNION
            SELECT order_id FROM ".TOP_SELLS_TABLE." WHERE `uid`=:uid
            ");
            $query->bindParam('uid', $user_id);
            $query->execute();
            if ($query->rowCount() > 0) {
                while ($rr = $query->fetchObject()) {
                    $order_ids[] = $rr;
                }
                foreach ($order_ids as $oid) {
                    $this->del_order($oid->orderId, $user_id);
                }
                return true;
            }
        }
        return false;
    }

    public function storeMessagesPublic($order_id=null, $user_id=null, $msg=null) {
        if ($this->databaseConnection()) {
            $this->storeMessages($order_id, $user_id, $msg);
        }
    }

    public function total_recent_transactions()
    {
        if ($this->databaseConnection()) {
            $total_orders = 0;

            $query = $this->db_connection->prepare("
                SELECT COUNT(*) AS TOTAL_ORDERS
                FROM ".TX_TABLE."
            ");
            if ($query->execute()) {
                $fetch = $query->fetchObject();
                $total_orders = (int)$fetch->TOTAL_ORDERS;
            }
            return $total_orders;
        }
        return false;
    }

    function total_my_messages() {
        if ($this->databaseConnection()) {
            $my_total_messages = 0;
            if (isset($_SESSION['user_id'])) {
                $user_id = (int) $_SESSION['user_id'];
            } else {
                return $my_total_messages;
            }
            $query = $this->db_connection->prepare("
                SELECT COUNT(*) AS MY_TOTAL_MESSAGES
                FROM ".MSG_TABLE."
                WHERE `username_key`=:u_id
            ");
            $query->bindParam('u_id', $user_id);
            if ($query->execute()) {
                $fetch = $query->fetchObject();
                $my_total_messages = (int) $fetch->MY_TOTAL_MESSAGES;
            }
            return $my_total_messages;
        }
        return false;
    }

    public function total_my_orders()
    {
        if ($this->databaseConnection()) {
            $my_total_orders = 0;
            if (isset($_SESSION['user_id'])) {
                $user_id = (int)$_SESSION['user_id'];
            } else {
                return $my_total_orders;
            }
            $query = $this->db_connection->prepare("
                SELECT COUNT(*) AS MY_TOTAL_ORDERS
                FROM ".ORDERS_TABLE."
                WHERE `uid`=:u_id
            ");
            $query->bindParam('u_id', $user_id);
            if ($query->execute()) {
                $fetch = $query->fetchObject();
                $my_total_orders = (int)$fetch->MY_TOTAL_ORDERS;
            }
            return $my_total_orders;
        }
        return false;
    }

    /*Blockchain Contract Queries*/

    public function get_bc_list($bc_name = null, $tradable_bc1=null, $tradable_bc2=null) {
        $bcl = [];
        if ($this->databaseConnection()) {
            $st = '';
            $st2 = '';
            if ($bc_name != null) {
                $st2 = " AND bc_code=:b ";
            }
            if ($tradable_bc1!=null && $tradable_bc2!=null) {
                $st = 'WHERE eligible_bc1 = 1 AND eligible_bc2 = 1  '.$st2;
            } else if ($tradable_bc1!=null && $tradable_bc2==null) {
                $st = 'WHERE eligible_bc1 = 1 '.$st2;
            } else if ($tradable_bc1==null && $tradable_bc2!=null) {
                $st = 'WHERE eligible_bc2 = 1  '.$st2;
            } else {
                if ($bc_name != null) {
                    $st2 = " WHERE bc_code=:b ";
                }
            }

            $query = $this->db_connection->prepare("SELECT * FROM ".BC_TABLE."
                            $st $st2 ");

            if ($bc_name != null) {
                $query->bindParam('b', $bc_name);
            }
            $query->execute();

            if ($query->rowCount()) {
                while ($l = $query->fetchObject()) {
                    $bcl[] = $l;
                }
            }
        }
        return $bcl;
    }

    public function is_bc_valid($bc=null, $val_bc1=null, $val_bc2=null) {
        if ($this->databaseConnection()) {
            $bc= trim($bc); $val_bc1=trim($val_bc1); $val_bc2=trim($val_bc2);
            if ($val_bc1 == null && $val_bc2==null && $bc==null) {
                return false;
            }
            if ($bc != null) {
                if ($bc=="RMT") {
                    return true;
                }
                $bc_list = array();
                $bcs = $this->get_bc_list(null, $val_bc1, $val_bc2);
                if (!empty($bcs)) {
                    foreach ($bcs as $bcl) {
                        $bc_list[] = $bcl->bc_code;
                    }
                }
                if (in_array($bc, $bc_list)) {
                    return true;
                }

            }
        }
        return false;
    }

    public function insert_new_bc($contractName, $contractCode, $contractAdmin, $isEligibleSel1, $isEligibleSel2, $incpDate) {
        if ($this->databaseConnection()) {

            $query = $this->db_connection->prepare("
                INSERT INTO ".BC_TABLE."(`id`, `contracts`, `bc_code`, `admin`, `eligible_bc1`, `eligible_bc2`, `incp`)
                VALUES('', :ctr, :bcc, :adm, $isEligibleSel1, $isEligibleSel2, :dt)
            ");
            $query->bindParam('ctr', $contractName);
            $query->bindParam('bcc', $contractCode);
            $query->bindParam('adm', $contractAdmin);
            $query->bindParam('dt', $incpDate);
            if ($query->execute()) {
                return true;
            }
        }
        return false;
    }

    public function update_bc_eligibility($bc=null, $sel=null, $val=null) {
        if ($this->databaseConnection()) {
            $st = '';
            if ($sel=="tdsel1") {
                $st = "SET `eligible_bc1`=".$val;
            } else if ($sel=='tdsel2') {
                $st = "SET `eligible_bc2`=".$val;
            }

            $query = $this->db_connection->prepare("
                UPDATE ".BC_TABLE."
                $st
                WHERE `bc_code`= :bc
            ");
            $query->bindParam('bc', $bc);
            if ($query->execute()) {
                return true;
            }
        }
        return false;
    }


}