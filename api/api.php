<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Carbon\Carbon;
use Carbon\CarbonInterval;

require '../includes/imp_files.php';
require '../vendor/autoload.php';

$app = new \Slim\App;

if (isset($OrderClass, $UserClass)) {

    // Get User data by email
    $app->get('/user_by_email/{em}', function (Request $request, Response $response) {
        try {
            $UserClass = new Users();
            $email = (string) trim($request->getAttribute('em'));
            $is_email = is_email($email);
            if ($is_email) {
                $stmt = $UserClass->get_user_by_email($email);
                $user_details = $stmt;

                echo json_encode($user_details);
                return;
            }
            echo '{"error": {"text": "Invalid email"}}';

        } catch (PDOException $e) {
            echo '{"error": {"text": ' . $e->getMessage() . '}}';
        }
    });


    // Update RMT balance in BCX
    $app->put('/up_val/rmt/{uid}', function (Request $request, Response $response) {
        try {
            $OrderClass = new Orders();
            $data = $request->getParsedBody(); // Array([new_bal] => 115)
            //$data = $request->getParam('new_bal'); // 115
            $uid = $request->getAttribute('uid');

            $add_bal = (float) $data['new_bal'];

            $prev_bal = (float) $OrderClass->check_customer_balance($assetType = RMT, $uid)->balance;

            $new_bal = $prev_bal + $add_bal;

            if ($new_bal < 0) {
                echo '{"process": {"text": "Invalid amount"}}';
                return;
            }

            if (isset($data['pass']) && trim($data['pass'])=="secret") {
                $update_successful = $OrderClass->update_user_balance(RMT, $new_bal, $uid);

                if ($update_successful) {
                    echo '{"process": {"text": "success"}}';
                    return;
                }
            }
            echo '{"process": {"text": "failed"}}';

        } catch (PDOException $e) {
            echo '{"process": {"text": ' . $e->getMessage() . '}}';
        }
    });

    $app->run();
}