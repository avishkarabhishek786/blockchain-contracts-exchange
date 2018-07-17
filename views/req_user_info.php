<?php
if (!isset($user_id)) {
    $user_id = $_SESSION['user_id'];
}
if (!isset($user_email)) {
    $user_email = $_SESSION['email'];
}
if (!isset($log_fullName)) {
    $log_fullName = $_SESSION['full_name'];
}
if (($user_email == null) && ($user_logged_in == true)) {

    if (isset($_POST['user_em_id'], $UserClass) && is_email($_POST['user_em_id'])) {
        $email = trim($_POST['user_em_id']);
        $updateEmail = $UserClass->input_user_email($email, $user_id);
        if ($updateEmail) {
            $_SESSION['email'] = $email;
            redirect_to("index.php?msg=Email updated as $email successfully.&type=success");
        }
        redirect_to("index.php?msg=Email could not be updated.&type=warning");
    }
    ?>
    <script>
        $(document).ready(function() {
            $('#getUserInfo').modal('show');
        });
    </script>

    <div id="getUserInfo" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="background-color: #dfdfe9">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Your Email Id Required!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
                    <div class="modal-body">
                        <h5 class="text-dark">Hi <?=$log_fullName?></h5>
                        <p id="req_em_msg" class="text-secondary">We need your email address to verify your account.</p>
                        <input type="text" name="user_em_id" class="form-control" placeholder="Enter Your Email Id here...">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-dark" id="req_em_btn" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php }