<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<link href="<?=SITE_URL?>bootstrap/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<script src="<?=SITE_URL?>bootstrap/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "password") {
            location.href = "<?=SITE_URL?>?user/uppass.html";
          } else if (e.target.id == "modavatar") {
            location.href = "<?=SITE_URL?>?user/editimg.html";
          } else if (e.target.id == "resume") {
            location.href = "<?=SITE_URL?>?user/resume.html";
          }
        });
        $("#menu_profile").addClass("active");
        $("#birthday").datepicker({
            changeYear: true,
            changeMonth: true,
            yearRange: '-60:+0',
            dateFormat: 'yy-mm-dd'
        });
    })
</script>
<div class="container" style="margin-top:5px;">
  <div class="col-md-3">
<? include template('leftmenu'); ?>
</div>
  <div class="col-md-9">
    <h5>个人信息</h5>
    <ul class="nav nav-tabs" role="tablist" id="myTab" style="font-size:13px;">
      <li class="active"><a href="#profile" id="profile" role="tab" data-toggle="tab">基本信息</a></li>
      <li><a href="#password" id="password" role="tab" data-toggle="tab">修改密码</a></li>
      <li><a href="#modavatar" id="modavatar" role="tab" data-toggle="tab">修改头像</a></li>
      <li><a href="#resume" id="resume" role="tab" data-toggle="tab">我的简历</a></li>
    </ul>

    <div class="tab-content" style="font-size:13px;">
      <div class="tab-pane fade in active" id="profile">
        <form method="POST" name="upinfoForm" action="<?=SITE_URL?>?user/profile.html" class="form-horizontal">
          <div class="form-group" style="margin-bottom:5px;margin-top:15px">
            <div class="col-sm-2 control-label">用户名:</div>
            <div class="col-sm-10">
              <p class="form-control-static"><?=$user['username']?></p>
            </div>
          </div>
          <div class="form-group">
            <div for="email" class="col-sm-2 control-label">邮箱地址:</div>
            <div class="col-sm-5">
              <? if($user['email_verify']) { ?>              <p class="form-control-static"><?=$user['email']?></p>
              <input type="hidden" name="email" id="email" value="<?=$user['email']?>">
              <? } else { ?>              <input type="email" class="form-control" name="email" id="email" value="<?=$user['email']?>">
              <? } ?>            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-2 control-label">性别:</div>
              <div class="col-sm-10">
                <label class="radio-inline">
                  <input type="radio" name="gender" value="1" <? if((1 == $user['gender'])) { ?> checked <? } ?>> 男
                </label>
                <label class="radio-inline">
                  <input type="radio" name="gender" value="0" <? if((0 == $user['gender'])) { ?> checked <? } ?>> 女
                </label>
                <label class="radio-inline">
                  <input type="radio" name="gender" value="2" <? if((2 == $user['gender'])) { ?> checked <? } ?>> 保密
                </label>
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-2 control-label">生日:</div>
              <div class="col-sm-5">
                <input type="text" name="birthday" id="birthday" readonly class="form-control datepicker" value="<?=$user['bday']?>" style="cursor:pointer;"/>
              </div>
            </div>

            <div class="form-group">
              <div for="phone" class="col-sm-2 control-label">手机:</div>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="phone" id="phone" value="<?=$user['phone']?>">
              </div>
            </div>

            <div class="form-group">
              <div for="qq" class="col-sm-2 control-label">QQ:</div>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="qq" id="qq" value="<?=$user['qq']?>">
              </div>
            </div>
            <div class="form-group">
              <div for="wechat" class="col-sm-2 control-label">微信:</div>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="wechat" id="wechat" value="<?=$user['wechat']?>">
              </div>
            </div>
            <div class="form-group">
              <div for="skills" class="col-sm-2 control-label">擅长:</div>
              <div class="col-sm-5">
                <input type="text" class="form-control" name="skills" id="skills" value="<?=$skillstr?>" placeholder="擅长多个领域请以空格隔开">
              </div>
            </div>
            <div class="form-group">
              <div for="signature" class="col-sm-2 control-label">签名:</div>
              <div class="col-sm-5">
                <textarea class="form-control" name="signature" id="signature" rows="3" maxlength="200"><?=$user['signature']?></textarea>
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
</div>
<? include template('footer'); ?>
