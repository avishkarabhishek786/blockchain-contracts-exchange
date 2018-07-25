<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 7/17/2018
 * Time: 3:34 PM
 */

class Viv extends Orders{

    public function insertTx($address=null, $parentId=null, $transferBalance=null) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("INSERT INTO ".VIV_TX_TBL."(`id`, `address`, `parentid`, `transferBalance`)
                    VALUES ('', :addr, :pid, :tb)");
            $query->bindParam("addr", $address);
            $query->bindParam("pid", $parentId);
            $query->bindParam("tb", $transferBalance);
            $query->execute();
            return true;
        }
        return false;
    }

    public function updateTx($bal, $id) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("UPDATE ".VIV_TX_TBL." SET transferBalance=:tb WHERE id=:id");
            $query->bindParam('tb', $bal);
            $query->bindParam('id', $id);
            $query->execute();
            return true;
        }
        return false;
    }

    public function insertLogs($primaryIDReference, $transferDescription, $transferIDConsumed=null, $blockchainReference) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("
            INSERT INTO ".VIV_LOGS." (primaryIDReference, transferDescription, transferIDConsumed, blockchainReference)
            VALUES (:pr, :td, :tc, :br)
        ");
            $query->bindParam("pr", $primaryIDReference);
            $query->bindParam("td",$transferDescription );
            $query->bindParam("tc", $transferIDConsumed);
            $query->bindParam("br", $blockchainReference);

            $query->execute();
            return true;
        }
        return false;
    }

    public function truncate_tbl($tbl='') {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("TRUNCATE TABLE ".$tbl);
            return true;
        }
        return false;
    }

    public function getAvailableTokens($addr='') {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("SELECT SUM(transferBalance) AS AVAILABLE_TOKENS FROM ".VIV_TX_TBL." WHERE address=:addr");
            $query->bindParam("addr", $addr);
            $query->execute();
            $sum= $query->fetchObject();
            $availableTokens = $sum->AVAILABLE_TOKENS;
            return (float) $availableTokens;
        }
        return null;
    }

    public function getTransactiontable($addr='') {
        $arr = [];
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("SELECT * FROM ".VIV_TX_TBL." WHERE address=:addr");
            $query->bindParam("addr", $addr);
            $query->execute();
            while ($q= $query->fetchObject()) {
                $arr[] = $q;
            }
        }
        return $arr;
    }

    public function getTransferBalanceById($id='') {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("SELECT transferBalance FROM ".VIV_TX_TBL." WHERE id=:id");
            $query->bindParam("id", $id);
            $query->execute();
            $id= $query->fetchObject()->transferBalance;
        }
        return $id;
    }

    public function getMostRecentId() {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->query("SELECT id FROM ".VIV_TX_TBL." ORDER BY id DESC LIMIT 1");
            $query->execute();
            if ($query->rowCount()) {
                return $query->fetchObject()->id;
            }
        }
        return false;
    }

    public function insertWebInfo($transferDescription, $blockchainReference) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare(
                "INSERT INTO ".VIV_WEB." (transferDescription, blockchainReference)
                 VALUES(:td, :br)"
            );
            $query->bindParam('td', $transferDescription);
            $query->bindParam('br', $blockchainReference);
            $query->execute();
            return true;
        }
        return false;
    }

    public function insertExtra($id, $lastblockscanned) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare(
                "INSERT INTO ".VIV_EXTRA." (id, lastblockscanned)
                 VALUES(:id, :ex)"
            );
            $query->bindParam('id', $id);
            $query->bindParam('ex', $lastblockscanned);
            $query->execute();
            return true;
        }
        return false;
    }

    public function updateExtra($id, $lastblockscanned) {
        if ($this->databaseConnection()) {
            $query = $this->db_connection->prepare("UPDATE ".VIV_EXTRA." SET lastblockscanned=".$lastblockscanned." WHERE id=:id");
            $query->bindParam('id', $id);
            $query->execute();
            return true;
        }
        return false;
    }

    public function getLastBlockScanned() {
        if ($this->databaseConnection()) {
            $id = 1;
            $query = $this->db_connection->prepare("SELECT lastblockscanned FROM ".VIV_EXTRA." WHERE id=:id");
            $query->bindParam('id', $id);
            $query->execute();
            if ($query->rowCount()) {
                return $query->fetchObject()->lastblockscanned;
            }
        }
        return false;
    }

}