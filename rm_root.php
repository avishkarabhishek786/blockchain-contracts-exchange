<?php
    ob_start(); date_default_timezone_set('Asia/Kolkata');
    $user_id = 0;
    require_once 'includes/header.php';
    require_once 'includes/imp_files.php';

    if (!checkLoginStatus()) {
        redirect_to("index.php");
    }

    if (isset($_SESSION['fb_id'], $_SESSION['user_id'], $_SESSION['user_name'])) {
        $root_fb = (int) $_SESSION['fb_id'];
        $root_user_id = (int) $_SESSION['user_id'];
        $root_user_name = (string) $_SESSION['user_name'];

        /*This should match ajax/rm_root.php too*/
        if ($root_fb != ADMIN_ID && $root_user_id != ADMIN_ID && $root_user_name != ADMIN_UNAME) {
            redirect_to("index.php");
        }

        $traders = $OrderClass->UserBalanceList('', 1);
        $BClist = $OrderClass->get_bc_list();

         ?>
<div class="container" style="margin-top:30px; margin-bottom:30px;">
    <div class="row">
        <!--Transfer tokens-->
        <div class="col-xs-12 col-md-12 col-lg-12 mb-50">
            <h2>Transfer tokens (Please select a BC from second select box above)</h2>
            <div class="mt--2 mb--2 p--1">
                <div class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="cust_id-fr">From (User Id)</label>
                        <input type="number" class="form-control" name="cust_id-fr" id="cust_id-fr" placeholder="From (User Id)">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="cust_id_to">To (User Id)</label>
                        <input type="number" class="form-control" name="cust_id_to" id="cust_id_to" placeholder="To (User Id)">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="toke_amt">Amount of BC units to transfer </label>
                        <input type="text" class="form-control" name="toke_amt" id="toke_amt" placeholder="Amount of BC units to transfer">
                    </div>
                    <input type="submit" class="btn-sm mt--1" id="btn-tr" value="Transfer tokens">
                </div>
            </div>
        </div>
        </div>
    <div class="row">
        <!--Insert New Blockchain Contract-->
        <div class="col-xs-12 col-md-12 col-lg-12 mb-50">
            <div>
                <h2>Blockchain Contracts Actions</h2>
                <div class="form-group">
                    <label for="ctrn">Contract Name:</label>
                    <input type="text" class="form-control" id="ctrn">
                </div>
                <div class="form-group">
                    <label for="bcc">BC Code (Not more than 8 characters):</label>
                    <input type="text" class="form-control" id="bcc">
                </div>
                <div class="form-group">
                    <label for="bcadm">Admin:</label>
                    <input type="text" class="form-control" id="bcadm">
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" id="eli_sel1"> Is Eligible for Select 1
                    </label>
                </div>
                <div class="form-group form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" id="eli_sel2"> Is Eligible for Select 2
                    </label>
                </div>
                <div class="form-group">
                    <label for="bcicpdt">Date of Incorporation:</label>
                    <input type="date" class="form-control" id="bcicpdt">
                </div>
                <button type="submit" class="btn btn-primary" id="btn_bcinst">Submit</button>
            </div>
        </div>
    </div>
    <div class="row">
        <!--Update BC-->
        <div class="col-xs-12 col-md-12 col-lg-12 mb-50">
            <div>
                <h2>Blockchain Contracts List</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                          <tr>
                            <th>Contracts</th>
                            <th>BC Code</th>
                            <th>BC Admin</th>
                            <th>Select BC 1</th>
                            <th>Select BC 2</th>
                            <th>Incorporated on</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if(is_array($BClist) && !empty($BClist)):
                              foreach ($BClist as $bcl):
                          ?>
                                  <tr>
                                      <td><?=$bcl->contracts?></td>
                                      <td><?=$bcl->bc_code?></td>
                                      <td><?=$bcl->admin?></td>
                                      <td>
                                          <button id="tdsel1_<?=$bcl->bc_code?>_<?=$bcl->eligible_bc1?>" class="btn btn-light btn_updt_bc"><?php echo $tt = ($bcl->eligible_bc1==1)?'On':'Off'?></button>
                                      </td>
                                      <td>
                                          <button id="tdsel2_<?=$bcl->bc_code?>_<?=$bcl->eligible_bc2?>" class="btn btn-light btn_updt_bc"><?php echo $tt = ($bcl->eligible_bc2==1)?'On':'Off'?></button>
                                      </td>
                                      <td><?=$bcl->incp?></td>
                                  </tr>
                          <?php endforeach; endif; ?>
                        </tbody>
                      </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!--User Actions-->
        <div class="col-xs-12 col-md-12 col-lg-12 mb-50">
            <h2>Actions table</h2>
            <div class="mt--2 mb--2 p--1">
                <div class="form-group">
                    <label class="sr-only" for="cus_id">User Id</label>
                    <input type="number" class="form-control" name="cus_id" id="cus_id" placeholder="User Id">
                </div>
                <div class="form-group">
                    <label class="sr-only" for="bal">Input Balance</label>
                    <input type="text" class="form-control" name="bal" id="bc-bal-updt" placeholder="Input Balance">
                </div>
                <select class="custom-select" id="bc_menue_sel" multiple>
                    <option value="" selected>Open this multiple select menu</option>
                    <option value="RMT">RMT</option>
        <?php if(is_array($BClist) && !empty($BClist)):
            foreach ($BClist as $bcl): ?>
                    <option value="<?=$bcl->bc_code; ?>"><?=$bcl->contracts?></option>
        <?php endforeach; endif;  ?>
                </select>
                <input type="submit" class="btn-sm mt--1" id="bc_tr_btn" value="Update balance">
            </div>

            <input type="text" id="search_traders" onkeyup="search_traders()" placeholder="Search for names..">

            <div class="table-responsive" id="traders_table">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>User</th>
                        <th>BC</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $action_class = null;
                    $btn_name = null;
                    $i=1;
                    if (is_array($traders) && !empty($traders)) {
                        foreach ($traders as $index=>$trader) {
                            if ($trader->is_active) {
                                $action_class = 'off';
                                $btn_name = "Deactivate Account";
                            } else {
                                $action_class = 'on';
                                $btn_name = "Activate Account";
                            }
                    ?>
                            <tr>
                                <td><?=$trader->UID?></td>
                                <td><a href="http://facebook.com/<?=$trader->FACEBOOK_ID?>" target="_blank"><?=$trader->name?></a></td>
                                <td><?=$trader->bc?></td>
                                <td><?=$trader->balance?></td>
                                <?php if($i!=$trader->UID) {continue;} ?>
                                <td><input type="button" class="btn-ra" id="<?=$action_class.'_'.$trader->UID?>" value="<?=$btn_name?>"></td>
                            </tr>
                        <?php $i++;
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <!--Update History-->
        <div class="col-xs-12 col-md-12 col-lg-12 mb-50">
            <div class="table-responsive">
            <div class="table-responsive">
        <?php $list_bal_changes = $OrderClass->list_root_bal_changes();  ?>
                <h2>Update History</h2>
                <input type="text" id="audit_input" onkeyup="search_audit_table()" placeholder="Search for names or id..">
                <table class="table" id="audit_table">
                    <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Investor's Id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Previous Balance</th>
                        <th>Updated Balance</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list_bal_changes as $ch): ?>
                    <tr>
                        <td><?=$ch->BalStatusHistoryId?></td>
                        <td><?=$ch->user_id?></td>
                        <td><?=$ch->name?></td>
                        <td><?=$ch->email?></td>
                        <td><?=$ch->bal_prev?></td>
                        <td><?=$ch->bal_now?></td>
                        <td><?=$ch->type?></td>
                        <td><?=$ch->UpdateDate?></td>
                    </tr>
                    <?php  endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>
<?php } ?>

<!--footer-->
<?php include_once 'includes/footer.php'; ?>

<script>
    $(document).on('click', '.btn-ra', function (e) {
        e.preventDefault();
        var btn = $(this);
        var btn_id = $(this).attr('id');
        var btn_val = parseInt(btn_id.replace ( /[^\d.]/g, '' ));
        $.ajax({
            method:'post',
            url:'ajax/rm_root.php',
            data: { task : 'act_user', btn_id:btn_id},
            error: function(xhr, status, error) {
                console.log(error);
            }, success: function(data) {
                data = $.trim(data);
                if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                    if (data == 'on') {
                        btn.attr("id", 'off_'+btn_val);
                        btn.prop("value", "Deactivate Account");
                        $.notify({
                            title: "<strong>Success!:</strong> ",
                            message: "User activated successfully."
                        },{
                            type: 'info'
                        });
                    } else if (data == 'off') {
                        btn.attr("id", 'on_'+btn_val);
                        btn.prop("value", "Activate Account");
                        $.notify({
                            title: "<strong>Success!:</strong> ",
                            message: "User de-activated successfully."
                        },{
                            type: 'info'
                        });
                    } else {
                        $.notify({
                            title: "<strong>Process Failed!:</strong> ",
                            message: "Process could not be completed."
                        },{
                            type: 'warning'
                        });
                    }

                } else {
                    displayNotice("Process could not be completed. Try again later.", "failure");
                }
                run_all();
            }
        });
    });

    $(document).on('click', '#bc_tr_btn', function() {
        var bc_bal_updt = $('#bc-bal-updt').val();
        var cus_id = $('#cus_id').val();
        var sel_bc2 = $('#bc_menue_sel').val();
        var job = 'update-user-bc-balance';
        var btn = this;

        if (sel_bc2=="") {
            $.notify({
                title: "<strong>Alert!: </strong> ",
                message: "Please choose a contract from the dropdown menu."
            },{
                type: 'warning'
            });
            return false;
        }
        $(btn).prop( "disabled", true );

        $.ajax({
            method: 'post',
            url: 'ajax/update_bc_bal.php',
            data: {job:job, cus_id:cus_id, bc_bal_updt:bc_bal_updt, _bc2:sel_bc2},
            error: function(xhr, status, error) {
                console.log(xhr, status, error);
            },
            success: function(data) {
                console.log(data);
                $(btn).prop( "disabled", false );
                if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                    var IS_JSON = true;
                    try {
                        var d = jQuery.parseJSON(data);
                    }
                    catch(err) {
                        IS_JSON = false;
                    }

                    if(IS_JSON) {
                        if (isArray(d.mesg) && d.mesg.length != 0) {
                            for (var k = 0; k <= d.mesg.length - 1; k++) {
                                var tp = (d.error == true) ? 'danger':'success';
                                $.notify({
                                    title: "<strong>Alert!:</strong> ",
                                    message: d.mesg[k]
                                },{
                                    type: tp
                                });
                            }
                        }
                    }
                }
            }
        });
    });
    
    function search_traders() {
        // Declare variables
        var input, filter, table, tr, td, i;
        input = document.getElementById("search_traders");
        filter = input.value.toUpperCase();
        table = document.getElementById("traders_table");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[1];

            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    
    // Audit table
    function search_audit_table() {
        var input, filter, table, tr, td, i;
        input = document.getElementById("audit_input");
        filter = input.value.toUpperCase();
        table = document.getElementById("audit_table");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        if(!isNaN(filter)) {
            for (i = 0; i < tr.length; i++) {
            tdi = tr[i].getElementsByTagName("td")[1];
            
            if (tdi) {
                //filter = input.value;
                if (tdi.innerHTML.indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
            
            }
        } else {
            for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[2];
            if (td) {
                if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }  
            }
        }
    }

    // Token transfer
    $(document).on('click', '#btn-tr', function (e) {
        var _from = $('#cust_id-fr').val();
        var _to = $('#cust_id_to').val();
        var _tokens = $('#toke_amt').val();
        var _bc2 = $('#sel-bc-2').val();
        var job = 'transfer_tokens';
        var btn = this;

        if (_bc2=="") {
            $.notify({
                title: "<strong>Alert!: </strong> ",
                message: "Please choose a contract from second dropdown at top."
            },{
                type: 'warning'
            });
            return false;
        }
        $(btn).val('Please wait....').prop( "disabled", true );

        $.ajax({
                method: 'post',
                url: 'ajax/transfer_tokens.php',
                data: {job:job, _from:_from, _to:_to, _bc2:_bc2, _tokens:_tokens},
            error: function(xhr, status, error) {
                console.log(xhr, status, error);
            },
            success: function(data) {
                console.log(data);
                $(btn).val('Transfer '+_bc2).prop( "disabled", false );
                if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                    var IS_JSON = true;
                    try {
                        var d = jQuery.parseJSON(data);
                    }
                    catch(err) {
                        IS_JSON = false;
                    }

                    if(IS_JSON) {
                        if (isArray(d.mesg) && d.mesg.length != 0) {
                            var tp = 'info';
                            if(d.error == true) {
                                tp = 'danger'
                            } else if(d.error == false) {
                                tp = 'success';
                            }
                            for (var k = 0; k <= d.mesg.length - 1; k++) {

                                $.notify({
                                    title: "<strong>Alert!:</strong> ",
                                    message: d.mesg[k]
                                },{
                                    type: tp
                                });
                            }
                        }
                    }
                }
            }
            });
    });

    /*Insert New BC*/
    $(document).on('click', '#btn_bcinst', function() {
        var btn = this;
        var ct_name = $('#ctrn').val();
        var bccode = $('#bcc').val();
        var bcadmin = $('#bcadm').val();
        var ch1 = $('#eli_sel1').is(":checked");
        var ch2 = $('#eli_sel2').is(":checked");
        var incpdt = $('#bcicpdt').val();
        var job = 'inset_bc';

        $(btn).val('Please wait...').prop( "disabled", true );

        $.ajax({
            method: 'post',
            url: 'ajax/rm_root.php',
            data: {job:job, ct_name:ct_name, bccode:bccode, bcadmin:bcadmin, ch1:ch1, ch2:ch2, incpdt:incpdt},
            error: function(xhr, status, error) {
                console.log(xhr, status, error);
            },
            success: function(data) {
                console.log(data);
                $(btn).val('Submit').prop( "disabled", false );
                if ($.trim(data) != '' && $.trim(data) != undefined && $.trim(data) != null) {
                    var IS_JSON = true;
                    try {
                        var d = jQuery.parseJSON(data);
                    }
                    catch(err) {
                        IS_JSON = false;
                    }

                    if(IS_JSON) {
                        console.log(d);
                    }
                }
            }
        });

    });

    /*Update BC*/
    $(document).on('click', '.btn_updt_bc', function() {

        var btn = this;
        var _id = $(btn).attr('id');
        var updt_job = 'update_sel_bc';
        $(btn).prop( "disabled", true);
        $.ajax({
            method: 'post',
            url: 'ajax/rm_root.php',
            data: {updt_job:updt_job, _id:_id},
            error: function(xhr, status, error) {
                console.log(xhr, status, error);
            },
            success: function(data) {
                var IS_JSON = true;
                try {
                    var d = jQuery.parseJSON(data);
                }
                catch(err) {
                    IS_JSON = false;
                }

                if(IS_JSON) {
                    if (d.res==true) {
                        var vv = (d.val==1)?'On':'Off';
                        $(btn).attr('id', d.new_id).text(vv).prop( "disabled", false );
                    }
                } else {
                    $.notify({
                        title: "<strong>Update failed!:</strong> ",
                        message: "Failed to update the state."
                    },{
                        type: 'danger'
                    });
                }
            }
        });

    })
    

</script>
