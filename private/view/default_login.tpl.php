<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<br/><br/>
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-left:20px; margin-bottom:10px;">用户登陆</h4>
          <form name="loginform" class="form-horizontal" action="<?=SITE_URL?>?user/login.html" method="post">
            <div class="form-group">
              <label class="col-sm-2 control-label">用户名</label>
              <div class="col-sm-5">
                <input type="text" class="form-control" id="username" name="username" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label">密&nbsp;&nbsp;码</label>
              <div class="col-sm-5">
                <input type="password" class="form-control" id="password" name="password" />
              </div>
            </div>
            <? if($setting['code_login']) { ?>            <? } else { ?>            <div class="form-group">
              <label class="col-sm-2 control-label">验证码</label>
              <div class="col-sm-2">
                <input type="text" class="form-control" id="code" name="code" onblur="check_code();"/>
              </div>
              <div class="col-sm-5">
                <span class="verifycode"><img  src="<?=SITE_URL?>?user/code.html" onclick="javascript:updatecode();" id="verifycode"></span>
                <a class="changecode" href="javascript:updatecode();">&nbsp;看不清?</a>
                <span id="codetip"></span>
              </div>
            </div>
            <? } ?>            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                  <label><input type="checkbox" id="cookietime" name="cookietime" value="2592000"> 下次自动登录 </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-2 col-sm-10">
                <input type="hidden" name="forward" value="<?=$forward?>"/>
                <button type="submit" class="btn btn-primary" name="submit">登&nbsp;录</button>
              <!-- &nbsp;&nbsp;&nbsp;忘记密码了？请点击 <a href="<?=SITE_URL?>?user/getpass.html">找回密码</a>-->
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-body">
          <h5><a href="<?=SITE_URL?>?user/register.html">还没有账号？立即注册!</a></h5>
        </div>
      </div>
    </div>
  </div>
</div>
<? include template('footer'); ?>
