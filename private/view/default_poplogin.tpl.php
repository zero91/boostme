<? !defined('IN_SITE') && exit('Access Denied'); ?>
<form class="form-horizontal" onsubmit="return checkform();" role="form" name="loginform" action="<?=SITE_URL?>?user/login.html" method="post">
  <div class="alert alert-danger col-sm-offset-2 col-sm-8" role="alert" id="user_error" style="display:none"></div>
  <div class="form-group">
    <label for="popusername" class="col-sm-3 control-label">用&nbsp;户&nbsp;名</label>
    <div class="col-sm-7">
      <input type="text" class="form-control" id="popusername" name="username"/>
    </div>
  </div>
  <div class="form-group">
    <label for="poppassword" class="col-sm-3 control-label">密&nbsp;&nbsp;&nbsp;码</label>
    <div class="col-sm-7">
      <input type="password" class="form-control" id="poppassword" name="password" />
    </div>
  </div>
  <!-- <? if($setting['code_login'] ) { ?> -->
  <div class="form-group">
    <label for="login_code" class="col-sm-3 control-label">验证码</label>
    <div class="col-sm-4">
      <input type="text" class="form-control" id="login_code" name="code" onblur="check_login_code();" />
    </div>
    <div class="col-sm-5">
      <span class="verifycode"><img  src="<?=SITE_URL?>?user/code.html" onclick="refresh_code();" id="verifylogincode"></span><a class="changecode" href="javascript:refresh_code();">&nbsp;看不清?</a>
    </div>
  </div>
  <!-- <? } ?> -->
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label><input type="checkbox" id="cookietime" name="cookietime" value="2592000">下次自动登录</label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-2">
        <input type="hidden" name="forward" value="<?=$forward?>"/>
        <input type="submit" value="&nbsp;登&nbsp;&nbsp;&nbsp;&nbsp;录&nbsp;" class="btn btn-primary" name="submit" />
    </div>
    <!-- 
    <div class="col-sm-2">
        <a href="<?=SITE_URL?>?user/getpass.html" class="btn btn-info">忘记密码?</a>
    </div>
    -->
    <div class="col-sm-3">
        <a href="<?=SITE_URL?>?user/register.html" class="btn btn-info">注册新账号</a>
    </div>
  </div>
  <!-- <? if($setting['sinalogin_open']) { ?> -->
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
        其他账号登陆：
        <!-- <? if($setting['sinalogin_open']) { ?> -->
        <a title="新浪微博登陆" href="<?=SITE_URL?>plugin/sinalogin/index.php" title="新浪微博登陆" class="sinaWebLogin"></a>
        <!-- <? } ?> -->
        <!-- <? if($setting['qqlogin_open']) { ?> -->
        <a  class="qqLogin" title="QQ账号登陆" href="<?=SITE_URL?>plugin/qqlogin/index.php"></a>
        <!-- <? } ?> -->
    </div>
  </div>
  <!-- <? } ?> -->
</form>


<script type="text/javascript">
    function checkform() {
        var username = $("#popusername").val();
        var password = $("#poppassword").val();
        if ($.trim(username) === '') {
            $("#user_error").html("请输入您的账号");
            $("#username").focus();
            $("#user_error").show();
            return false;
        }
        if (password === '') {
            $("#user_error").html("请输入您的密码");
            $("#password").focus();
            $("#user_error").show();
            return false;
        }
        $("#user_error").html("");
        $("#user_error").hide();
        check_login_code();
        if ($('#logincodetip').hasClass("input_error")) {
            $("#code").focus();
            return false;
        }
     
        $.ajax({
            type: "POST",
            async: false,
            cache: false,
            url: "<?=SITE_URL?>index.php?user/ajaxlogin",
            data: "username=" + $.trim(username) + "&password=" + password,
            success: function(ret) {
                if (ret == '-1') {
                    $("#user_error").html("用户名或密码错误");
                    $("#user_error").show();
                } else {
                    $("#user_error").html("");
                    $("#user_error").hide();
                }
            }
        });
        if ($("#user_error").html() != '') {
            return false;
        } else {
            return true;
        }

    }
    function refresh_code() {
        var img = g_site_url + "index.php" + query + "user/code/" + Math.random();
        $('#verifylogincode').attr("src", img);
    }
    function check_login_code() {
        var code = $.trim($('#login_code').val());
        if ($.trim(code) == '') {
            $('#logincodetip').html("验证码错误");
            $('#logincodetip').attr('class', 'input_error');
            return false;
        }
        $.ajax({
            type: "POST",
            async: false,
            cache: false,
            url: "<?=SITE_URL?>index.php?user/ajaxcode/"+code,
            success: function(flag) {                   
                if (1 == flag) {
                    $('#logincodetip').html("&nbsp;");
                    $('#logincodetip').attr('class', 'input_ok');
                    return true;
                } else {
                    $('#logincodetip').html("验证码错误");
                    $('#logincodetip').attr('class', 'input_error');
                    return false;
                }

            }
        });
    }
</script>
