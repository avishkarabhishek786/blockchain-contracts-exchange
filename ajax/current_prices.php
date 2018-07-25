<?php
require_once '../includes/imp_files.php';

if (isset($_POST['task']) && trim($_POST['task']=='current_prices')) {
    $bc2 = isset($_POST['bc2']) ? $_POST['bc2'] : null;

    $std = new stdClass();
    $std->cp = array();
    $std->error = true;
    if (isset($OrderClass)) {

        $is_bc_valid= $OrderClass->is_bc_valid($bc2, null, 1);
        if ($is_bc_valid) {
            /*$wallet = $OrderClass->tx_data(null,$bc2,null);
            $rmteq = $OrderClass->tx_data(null,RMT,null);

            $usd_eq = array();
            if (is_array($wallet)&&!empty($wallet)) {
                foreach ($wallet as $w) {
                    if (isset($w->a_amount, $w->b_amount)) {
                        $b = $w->b_amount;
                        if ($w->b_amount == RMT && isset($_SESSION['RMT_TODAYS_PRICE'])) {
                            $b = $_SESSION['RMT_TODAYS_PRICE'];
                        }
                        $usd_eq[] = bc_to_usd($w->a_amount, $b); // bc eq in rmt * rmt eq in usd
                    }
                }
            }*/

            $bcs = $OrderClass->get_bc_list(null, 1, 1);
            $bccode = array();
            $bc2_eq = array();
            $rmt_eq = array();
            $usd_eq = array();

            $res = array(
                'BC'=>'',
                'BC2VAL'=>'',
                'RMTVAL'=>'',
                'USDVAL'=>''
            );
            if (is_array($bcs)&&!empty($bcs)) {
                foreach ($bcs as $i=>$b) {
                    $bccode[] = $b->bc_code;
                    $bc2_eq[] = isset($OrderClass->get_bc1_to_bc2_eq($b->bc_code, $bc2)->a_amount) ? $OrderClass->get_bc1_to_bc2_eq($b->bc_code, $bc2)->a_amount : 0;
                    $rmt_eq[] = isset($OrderClass->get_bc1_to_bc2_eq($b->bc_code, RMT)->a_amount) ? $OrderClass->get_bc1_to_bc2_eq($b->bc_code, RMT)->a_amount : 0;
                    if (isset($_SESSION['RMT_TODAYS_PRICE'])) {
                        $usd_eq[] = bc_to_usd($rmt_eq[$i], $_SESSION['RMT_TODAYS_PRICE']);
                    }
                    $res=['BC'=>$bccode, 'BC2VAL'=>$bc2_eq, 'RMTVAL'=>$rmt_eq, 'USDVAL'=>$usd_eq];
                }
            }

            $std->cp = $res;
            $std->error = false;
        }
    }
    echo json_encode($std);
}
return false;