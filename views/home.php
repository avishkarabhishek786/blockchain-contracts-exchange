<main role="main" class="container">
    <div class="d-flex align-items-center p-3 my-3 text-white-50 bg-purple rounded box-shadow">
        <img class="mr-3" src="https://getbootstrap.com/assets/brand/bootstrap-outline.svg" alt="" width="48" height="48">
        <div class="lh-100">
            <h6 class="mb-0 text-white lh-100">Ranchi Mall</h6>
            <small>Small Ideas. Big Dreams</small>
        </div>
        <div class="d-flex">
            <select class="form-control selbc" name="sel-bc-1" id="sel-bc-1">
                <option value=""> Select first coin..</option>
                <option value="REBC">Real Estate</option>
                <option value="IBC">Incorporation</option>
                <option value="FLOBC">Flo</option>
            </select>

            <select class="form-control selbc" name="sel-bc-2" id="sel-bc-2">
                <option value="">Select second coin..</option>
                <option value="RMT">RMT</option>
                <option value="REBC">Real Estate</option>
                <option value="IBC">Incorporation</option>
                <option value="FLOBC">Flo</option>
            </select>
        </div>
    </div>

    <div class="my-3 p-3 bg-white rounded box-shadow">
            <div class="form-group row">
                <label for="ex-price" class="col-sm-2 col-form-label">Enter Price</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="ex-price" id="ex-price"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="ex-qty" class="col-sm-2 col-form-label">Enter Quantity</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="ex-qty" id="ex-qty"/>
                </div>
            </div>
            <fieldset class="form-group">
                <div class="row">
                    <legend class="col-form-label col-sm-2 pt-0">Choose buy or sell</legend>
                    <div class="col-sm-10">
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
                    </div>
                </div>
            </fieldset>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_mkt">
                <label class="form-check-label" for="is_mkt">Buy instantly at market rate?</label>
            </div>
            <div class="form-group row">
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary" id="ex-sub" name="ex-sub">Submit</button>
                </div>
            </div>
    </div>

    <div class="my-3 p-3 bg-white rounded box-shadow">
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <h6>Buy list</h6>
                    <table class="table-borderless table-sm">
                        <thead>
                        <tr>
                            <th>Buyer</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody id="bd-buy"></tbody>
                    </table>
                </div>
            </div>

            <div class="col">
                <div class="table-responsive">
                    <h6>Sell list</h6>
                    <table class="table-borderless table-sm">
                        <thead>
                        <tr>
                            <th>Buyer</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody id="bd-sell"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>