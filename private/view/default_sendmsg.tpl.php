<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<style type="text/css">
    .input_ok {background: url("<?=SITE_URL?>/css/default/input_ok.png") no-repeat scroll 6px 5px transparent;line-height: 30px;padding: 7px 6px 5px 28px;}
    .input_error {background: url("<?=SITE_URL?>/css/default/input_error.png") no-repeat scroll 6px 7px;font-size: 13px;color:red;line-height:30px;padding:6px 6px 7px 28px;}
</style>
<script src="<?=SITE_URL?>js/editor/ueditor.config.js" type="text/javascript"></script> 
<script src="<?=SITE_URL?>js/editor/ueditor.all.js" type="text/javascript"></script> 
<div class="container" style="margin-top:30px;">
  <div class="col-sm-1"></div>
  <div class="col-sm-9">
    <div class="panel panel-info">
      <div class="panel-heading">发送消息</div>
      <div class="panel-body">
        <span class="pull-right">
          <a href="<?=SITE_URL?>?message/personal.html">返回消息列表</a>
        </span>
      <form action="<?=SITE_URL?>?message/send.html" method="post" class="form-horizontal" id="msg_form" onsubmit="return docheck()">
        <div class="form-group" style="margin-top:30px;">
          <div for="username" class="col-sm-2 control-label">收件人:</div>
          <div class="col-sm-7">
            <input type="text" id="username" name="username" class="normal-input" value="<?=$sendto['username']?>" onblur="check_user()" />
            <span id="usernametip"></span>
          </div>
        </div>
        <div class="form-group" style="margin-top:30px;">
          <div for="subject" class="col-sm-2 control-label">主题:</div>
          <div class="col-sm-7">
            <input type="text" id="subject" name="subject" value="" style="width:500px;"/>
          </div>
        </div>
        <div class="form-group" style="margin-top:30px;">
          <div for="content" class="col-sm-2 control-label">内容:</div>
          <div class="col-sm-7">
            <script type="text/plain" id="content" name="content" style="height: 122px;width:500px;"></script>
            <script type="text/javascript">UE.getEditor('content', UE.utils.extend({toolbars:[[<?=$toolbars?>]]}));</script>
          </div>
        </div>
        <div class="form-group" style="margin-top:30px;">
          <div for="code" class="col-sm-2 control-label">验证码:</div>
          <div class="col-sm-3">
            <input type="text" class="code-input" id="code" name="code" onblur="check_code();"/>
          </div>
          <span id="codetip"></span>
        </div>
        <div class="form-group">
          <div class="col-sm-2"></div>
          <div class="col-sm-3">
            <span class="verifycode">
              <img  src="<?=SITE_URL?>?user/code.html" onclick="javascript:updatecode();" id="verifycode">
            </span>
            <a class="changecode" href="javascript:updatecode();">&nbsp;看不清?</a>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-2"></div>
          <div class="col-sm-3">
            <input type="submit" name="submit" class="btn btn-primary col-sm-7" value="发&nbsp;送" />
          </div>
        </div>
      </form>
      </div>
    </div>
   </div>
</div>
<script type="text/javascript">
var user_exist = 0;

function docheck() {
    return check_code() && check_user();
}

function check_user() {
    var username = $.trim($('#username').val());
    var length = bytes(username);
    if (length < 3 || length > 15) {
        $('#usernametip').html("用户名不存在");
        $('#usernametip').attr('class', 'input_error');
        usernameok = false;
    } else {
        $.post("<?=SITE_URL?>index.php?user/ajaxusername", {username: username}, function(flag) {
            if (-1 == flag) {
                $('#usernametip').html("&nbsp;");
                $('#usernametip').attr('class', 'input_ok');
                user_exist = true;
            } else {
                $('#usernametip').html("用户名不存在");
                $('#usernametip').attr('class', 'input_error');
                user_exist = false;
            }
        });
    }
}

</script>
<? include template('footer'); ?>
