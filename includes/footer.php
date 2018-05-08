<div id="MsgModel" class="modal fade bs-MsgModel-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" style="top:33%; text-align: center;">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog modal-lg vertical-align-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabelMsg"></h4>
                </div>
                <div class="modal-body">
                    <ul class="msg-ul"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="<?=JS_DIR?>/jquery-3.2.1.min.js"></script>
<script src="<?=JS_DIR?>/main.js"></script>
<script src="<?=JS_DIR?>/bootstrap.min.js"></script>
<script src="<?=JS_DIR?>/notify.js"></script>
<script>
    $(function () {
        'use strict'

        $('[data-toggle="offcanvas"]').on('click', function () {
            $('.offcanvas-collapse').toggleClass('open')
        })
    })
</script>
</body>
</html>