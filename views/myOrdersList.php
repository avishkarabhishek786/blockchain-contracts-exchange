<?php if ($user_logged_in) { ?>
    <div class="table-responsive">
        <table class="table table-striped" cellpadding="10" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Price ($)</th>
                <th>Quantity</th>
                <th>Cancel Order</th>
                <th>Item Sold</th>
                <th>Item Bought</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody id="myOrdersTable"></tbody>
        </table>
    </div>


            <!--<div class="col-lg-6">
                <h4 class="mt--2">My Transactions: </h4>
                <h5><a href="My_Transactions" target="_blank">View All</a></h5>
                <div class="table-responsive mt--2">
                    <table class="table table-striped" cellpadding="10">
                        <thead>
                        <tr>
                            <th>Seller: </th>
                            <th>Buyer: </th>
                            <th>Trade Price: </th>
                            <th>Quantity Traded: </th>
                            <th>Transaction Amount: </th>
                            <th>Trade Date: </th>
                        </tr>
                        </thead>
                        <tbody id="my-transactions-list"></tbody>
                        <?php /* if(isset($user_id) && $user_id !=null) {
                            $MyTransactions = $OrderClass->displayUserTransaction($user_id, 0, 10);*/?>
                            <?php /*if ($MyTransactions == null || !is_array($MyTransactions) || empty($MyTransactions)) { */?>
                            <p class="text-info">No transactions!</p>
                            <?php /*}} */?>
                    </table>
                </div>
            </div>-->

<?php } ?>