<div class="container-fluid" style="margin:30px 0 30px 0">
    <div class="row">
        <div class="col-xs-12 col-lg-3">
            <div class="row lays">
                <div class="col-8"><h5 class="text-justify"><span id="bc-one"></span>  <span id="bc-two"></span></h5></div>
                <div class="col-4">
                    <h5 class="text-justify" id="bc-two-pr"></h5>
                </div>
                <!--<div class="col-3">
                    <!--<h5 class="text-justify text-success">22.3%</h5>
                </div>-->
            </div>
            <div class="row lays">
                <div class="col">
                    <h5>Order Book</h5>

                    <div class="form-group">
                        <label for="ex-price" class="col-form-label">Enter Price</label>
                        <input type="text" class="form-control" name="ex-price" id="ex-price"/>
                    </div>
                    <div class="form-group">
                        <label for="ex-qty" class="col-form-label">Enter Quantity</label>
                        <input type="text" class="form-control" name="ex-qty" id="ex-qty"/>
                    </div>
                    <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Choose buy or sell</legend>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gridRadios" id="ex-rad-buy" value="ex-buy">
                            <label class="form-check-label" for="ex-rad-buy">
                                Buy
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gridRadios" id="ex-rad-sell" value="ex-sell">
                            <label class="form-check-label" for="ex-rad-sell">
                                Sell
                            </label>
                        </div>
                    </fieldset>
                    <div class="form-group form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" id="is_mkt"> Buy instantly at market rate?
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary <?=$action_class_buy_sell?>" id="ex-sub" name="ex-sub">Submit</button>
                    </div>
                </div>
            </div>
            <?php if ($user_logged_in) { ?>
            <div class="row lays">
                <div class="col">
                    <h5>Wallet</h5>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>BC</th>
                            <th>BAL.</th>
                        </tr>
                        </thead>
                        <tbody id="usr-bc-bal"></tbody>
                    </table>
                </div>
            </div>
            <?php } ?>

        </div>
        <div class="col-xs-12 col-lg-9">

        <!--Buy Sell div-->
        <div class="row lays">
            <?php include_once 'buy_sell_box.php'; ?>
        </div>
        <!--End Buy Sell Div-->

            <div id="accordion">
                <div class="card lays">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Recent Transactions
                            </button>
                            <span><a href="Recent_Transactions" target="_blank">View All</a></span>
                        </h5>
                    </div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            <?php include_once 'tx.php'; ?>
                        </div>
                    </div>
                </div>
                <?php if ($user_logged_in) { ?>
                <div class="card lays">
                    <div class="card-header" id="headingThree">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Order List
                            </button>
                            <span><a href="My_Orders" target="_blank">View All</a></span>
                        </h5>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body">
                            <?php include_once'myOrdersList.php'; ?>
                        </div>
                    </div>
                </div>

                <div class="card lays">
                    <div class="card-header" id="headingTwo">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                My Messages
                            </button>
                            <span><a href="My_Messages" target="_blank">View All</a></span>
                        </h5>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body">
                            <?php include_once 'user_messages.php';?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="lays">
                        <?php include_once 'traders_list.php'; ?>
                    </div>
                </div>
                <div class="col-xs-12 col-md-6">
                    <div class="lays">
                        <?php include_once "ltp.php";?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

