<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "profile") {
            location.href = "<?=SITE_URL?>?user/profile.html";
          } else if (e.target.id == "modavatar") {
            location.href = "<?=SITE_URL?>?user/editimg.html";
          } else if (e.target.id == "resume") {
            location.href = "<?=SITE_URL?>?user/resume.html";
          }
        });
        $("#menu_uppass").addClass("active");
    })
</script>
<div class="container" style="margin-top:5px;">
  <div class="col-md-3">
<? include template('leftmenu'); ?>
</div>
  <div class="col-md-9" style="font-size:13px;">
    <h5>个人信息</h5>
    <ul class="nav nav-tabs" role="tablist" id="myTab">
      <li><a href="#profile" id="profile" role="tab" data-toggle="tab">基本信息</a></li>
      <li class="active"><a href="#password" id="password" role="tab" data-toggle="tab">修改密码</a></li>
      <li><a href="#modavatar" id="modavatar" role="tab" data-toggle="tab">修改头像</a></li>
      <li><a href="#resume" id="resume" role="tab" data-toggle="tab">我的简历</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane" id="profile"></div>
      <div class="tab-pane in active" id="password">
        <form method="POST" name="upinfoForm" action="<?=SITE_URL?>?user/uppass.html" class="form-horizontal">
          <div class="form-group" style="margin-bottom:5px;margin-top:15px">
          <div class="form-group">
            <div for="oldpwd" class="col-sm-2 control-label">当前密码:</div>
            <div class="col-sm-5">
              <input type="password" class="form-control" id="oldpwd" name="oldpwd">
            </div>
          </div>
          <div class="form-group">
            <div for="newpwd" class="col-sm-2 control-label">新密码:</div>
            <div class="col-sm-5">
              <input type="password" class="form-control" id="newpwd" name="newpwd">
            </div>
          </div>
          <div class="form-group">
            <div for="newpwd" class="col-sm-2 control-label">确认密码:</div>
            <div class="col-sm-5">
              <input type="password" class="form-control"id="confirmpwd" name="confirmpwd">
            </div>
          </div>
          <div class="form-group">
            <div for="login_code" class="col-sm-2 control-label">验证码</div>
            <div class="col-sm-3">
              <input type="text" class="form-control" id="code" name="code" onblur="check_code()">
            </div>
            <div class="col-sm-3">
              <span class="verifycode">
                <img src="<?=SITE_URL?>?user/code.html" onclick="javascript:updatecode();" id="verifycode">
              </span>
              <a href="javascript:updatecode();" class="changecode">&nbsp;换一个</a>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-2"></div>
            <div class="col-sm-5">
              <button type="submit" name="submit" class="btn btn-primary btn-block">
                保存
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> <!-- end container -->
<? include template('footer'); ?>
