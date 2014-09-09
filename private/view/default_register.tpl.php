<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<style type="text/css">
    .input_desc {padding-top:15px;}
    .input_ok {background: url("<?=SITE_URL?>/css/default/input_ok.png") no-repeat scroll 6px 5px transparent;line-height: 30px;padding: 7px 6px 5px 28px;}
    .input_error {background: url("<?=SITE_URL?>/css/default/input_error.png") no-repeat scroll 6px 7px;font-size: 13px;color:red;line-height:30px;padding:6px 6px 7px 28px;}
    .right-tips dd {background: url("<?=SITE_URL?>/css/default/lidot.gif") no-repeat scroll 0 0 rgba(0, 0, 0, 0);background-position: 0 10px;color: #999999;line-height: 22px;padding: 0 5px 5px 10px; font-size:12px;}
</style>

<div class="container">
  <div class="col-md-9">
    <h5>注册</h5>
    <form name="loginform" action="<?=SITE_URL?>?user/register.html" method="post" class="form-horizontal panel panel-info" onsubmit="return docheck()">
      <div class="form-group">
        <h5 class="col-sm-2 control-label">用户名</h5>
        <div class="input_desc">
          <div class="col-sm-5">
            <input type="text" class="form-control" id="username" name="username" onblur="check_username();"/>
          </div>
          <span id="usernametip" class="control-label">
            不超过14个字节(中文，数字，字母和下划线)
          </span>
        </div>
      </div>
      <div class="form-group">
        <h5 class="col-sm-2 control-label">登陆密码</h5>
        <div class="input_desc">
          <div class="col-sm-5">
            <input type="password" class="form-control" id="password" name="password" onblur="check_passwd();" />
          </div>
          <span id="passwordtip" class="control-label">
            长度6-14位，字母区分大小写
          </span>
        </div>
      </div>
      <div class="form-group">
        <h5 class="col-sm-2 control-label">密码确认</h5>
        <div class="input_desc">
          <div class="col-sm-5">
            <input type="password" class="form-control" id="repassword" name="repassword"  onblur="check_repasswd();"/>
          </div>
          <span id="repasswordtip" class="input_desc">与登录密码输入一致</span>
        </div>
      </div>
      <div class="form-group">
        <h5 class="col-sm-2 control-label">电子邮箱</h5>
        <div class="input_desc">
          <div class="col-sm-5">
            <input type="text" class="form-control" id="email" name="email" onblur="check_email();"/>
          </div>
          <span id="emailtip" class="input_desc">请输入正确的电子邮箱地址</span>
        </div>
      </div>
      <? if($setting['code_register']) { ?>      <div class="form-group">
        <h5 class="col-sm-2 control-label">验证码</h5>
        <div class="input_desc">
          <div class="col-sm-5">
            <input type="text" class="form-control" id="code" name="code" onblur="check_code();"/>
          </div>
          <span id="codetip"></span>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
          <img src="<?=SITE_URL?>?user/code.html" onclick="javascript:updatecode();" id="verifycode">
          <a class="" href="javascript:updatecode();">&nbsp;看不清?</a>
        </div>
      </div>
      <? } ?>      <div class="form-group">
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
          <input type="submit" value="注&nbsp;册" class="btn btn-primary" name="submit"/>&nbsp;&nbsp;
          <input type="checkbox" checked="true" name="agreeclause" id="agreeclause" value="1"/>同意&nbsp;
          <a href="javascript:void(0);" data-toggle="modal" data-target="#myModal" style="">网站服务条款</a>
        </div>
      </div>
    </form>
  </div>

  <div class="col-md-3">
    <div style="padding-top:45px;"></div>
    <div class="" style="font-size:13px;">
      <h6><a href="<?=SITE_URL?>?user/login.html">已有账号？立即登陆!</a></h6>
      <dl class="right-tips">
        <dd>我们提醒您注意，您需要注册并登陆，才能享受我们的完整服务进行各项操作。</dd>
        <dd>密码过于简单有被盗的风险，一旦密码被盗你的个人信息有泄漏的危险。</dd>
        <dd>我们拒绝垃圾邮件，请使用有效的邮件地址。</dd>
      </dl>
    </div>		
  </div>

  <div class="modal fade" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title">网站服务条款</h4>
        </div>
        <div class="modal-body">
          <p><?=$setting['register_clause']?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">我知道了</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

</div> <!-- end container--> 
<script type="text/javascript">
    var usernameok = 1;
    var password = 1;
    var repasswdok = 1;
    var emailok = 1;
    var codeok = 1;
    function check_username() {
        var username = $.trim($('#username').val());
        var length = bytes(username);
        if (length < 3 || length > 15) {
            $('#usernametip').html("用户名请使用3到15个字符");
            $('#usernametip').attr('class', 'input_error');
            usernameok = false;
        } else {
            $.post("<?=SITE_URL?>index.php?user/ajaxusername", {username: username}, function(flag) {
                if (-1 == flag) {
                    $('#usernametip').html("此用户名已经存在");
                    $('#usernametip').attr('class', 'input_error');
                    usernameok = false;
                } else if (-2 == flag) {
                    $('#usernametip').html("用户名含有禁用字符");
                    $('#usernametip').attr('class', 'input_error');
                    usernameok = false;
                } else {
                    $('#usernametip').html("&nbsp;");
                    $('#usernametip').attr('class', 'input_ok');
                    usernameok = true;
                }
            });
        }
    }

    function check_passwd() {
        var passwd = $('#password').val();
        if (bytes(passwd) < 6 || bytes(passwd) > 16) {
            $('#passwordtip').html("密码最少6个字符，最长不得超过16个字符");
            $('#passwordtip').attr('class', 'input_error');
            password = false;
        } else {
            $('#passwordtip').html("&nbsp;");
            $('#passwordtip').attr('class', 'input_ok');
            password = 1;
        }
    }

    function check_repasswd() {
        repasswdok = 1;
        var repassword = $('#repassword').val();
        if (bytes(repassword) < 6 || bytes(repassword) > 16) {
            $('#repasswordtip').html("密码最少6个字符，最长不得超过16个字符");
            $('#repasswordtip').attr('class', 'input_error');
            repasswdok = false;
        } else {
            if ($('#password').val() == $('#repassword').val()) {
                $('#repasswordtip').html("&nbsp;");
                $('#repasswordtip').attr('class', 'input_ok');
                repasswdok = true;
            } else {
                $('#repasswordtip').html("两次密码输入不一致");
                $('#repasswordtip').attr('class', 'input_error');
                repasswdok = false;
            }
        }
    }

    function check_email() {
        var email = $.trim($('#email').val());
        if (!email.match(/^[\w\.\-]+@([\w\-]+\.)+[a-z]{2,4}$/ig)) {
            $('#emailtip').html("邮件格式不正确");
            $('#emailtip').attr('class', 'input_error');
            emailok = false;
        } else {
            $.post("<?=SITE_URL?>index.php?user/ajaxemail", {email: email}, function(flag) {
                if (-1 == flag) {
                    $('#emailtip').html("此邮件地址已经注册");
                    $('#emailtip').attr('class', 'input_error');
                    emailok = false;
                } else if (-2 == flag) {
                    $('#emailtip').html("邮件地址被禁止注册");
                    $('#emailtip').attr('class', 'input_error');
                    emailok = false;
                } else {
                    emailok = true;
                    $('#emailtip').html("&nbsp;");
                    $('#emailtip').attr('class', 'input_ok');
                }
            });
        }
    }

    function docheck() {
    <? if($setting['code_register']) { ?>        return (check_clause() && usernameok && repasswdok && emailok && check_code());
    <? } else { ?>        return (check_clause() && usernameok && repasswdok && emailok);
    <? } ?>    }

    function check_clause() {
        return $("#agreeclause:checked").val() == 1;
    }

$(document).ready(function() {
    $("#dialog").dialog({
        autoOpen: false,
        width: 600,
        modal: true,
        resizable: false
    });

    $("#showclause").click(function() {
        $("#dialog").dialog("open");
    });
});

</script>
<? include template('footer'); ?>
