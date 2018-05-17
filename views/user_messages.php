<?php
    if($user_logged_in) {?>
        <div class="table-responsive">
            <table class="table table-striped" cellpadding="10">
                <thead>
                <tr>
                    <th>S.No</th>
                    <th>Order No.</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody id="user_msg"></tbody>
            </table>
        </div>
<?php } ?>