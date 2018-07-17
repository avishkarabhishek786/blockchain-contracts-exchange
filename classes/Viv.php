<?php
/**
 * Created by PhpStorm.
 * User: Abhishek Kumar Sinha
 * Date: 7/17/2018
 * Time: 3:34 PM
 */

class Viv extends Users{

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

    public function insertLogs($primaryIDReference=null, $transferDescription=null, $transferIDConsumed=null, $blockchainReference=null) {
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


}