<?php
ob_start();
date_default_timezone_set('Asia/Kolkata');
?>
<?php require_once "includes/imp_files.php";?>

<?php include_once 'includes/header.php'; ?>

<div class="container-fluid" style="margin:30px 0 30px 0">
    <div class="row">
        <div class="col-xs-12 col-lg-3">
            <div class="row lays">
                <div class="col-6"><h5 class="text-justify">RMTS/RSBC</h5></div>
                <div class="col-3">
                    <h5 class="text-justify">234</h5>
                </div>
                <div class="col-3">
                    <h5 class="text-justify text-success">22.3%</h5>
                </div>
            </div>
            <div class="row lays">
                <div class="col">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>BC</th>
                            <th>Price (RMT)</th>
                            <th>24h % shift</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>RSBC</td>
                            <td>223</td>
                            <td><span class="text-success">22.3%</span></td>
                        </tr>
                        <tr>
                            <td>ISBC</td>
                            <td>123.34</td>
                            <td><span class="text-danger">22.3%</span></td>
                        </tr>
                        <tr>
                            <td>HSBC</td>
                            <td>334.12</td>
                            <td><span class="text-success">22.3%</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row lays">
                <div class="col">
                    <h5>Order Book</h5>
                    <div class="form-group">
                        <label for="email">Email address:</label>
                        <input type="email" class="form-control" id="email">
                    </div>
                    <div class="form-group">
                        <label for="pwd">Password:</label>
                        <input type="password" class="form-control" id="pwd">
                    </div>
                    <div class="form-group form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox"> Remember me
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
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
                        <tbody>
                        <tr>
                            <td>RMT</td>
                            <td>34.99</td>
                        </tr>
                        <tr>
                            <td>RSBC</td>
                            <td>55</td>
                        </tr>
                        <tr>
                            <td>HSDC</td>
                            <td>55</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="col-xs-12 col-lg-9">
            <div id="accordion">
                <div class="card lays">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Collapsible Group Item #1
                            </button>
                        </h5>
                    </div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Seller</th>
                                    <th>Buyer</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>Katy Perkins</td>
                                    <td>234</td>
                                    <td>12.56</td>
                                    <td>12234.34</td>
                                    <td>23 July, 2016 12::32</td>
                                </tr>
                                <tr>
                                    <td>John Doe</td>
                                    <td>Katy Perkins</td>
                                    <td>234</td>
                                    <td>12.56</td>
                                    <td>12234.34</td>
                                    <td>23 July, 2016 12::32</td>
                                </tr>
                                <tr>
                                    <td>John Doe</td>
                                    <td>Katy Perkins</td>
                                    <td>234</td>
                                    <td>12.56</td>
                                    <td>12234.34</td>
                                    <td>23 July, 2016 12::32</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card lays">
                    <div class="card-header" id="headingTwo">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Collapsible Group Item #2
                            </button>
                        </h5>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body">
                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                        </div>
                    </div>
                </div>
                <div class="card lays">
                    <div class="card-header" id="headingThree">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Collapsible Group Item #3
                            </button>
                        </h5>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body">
                            Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>