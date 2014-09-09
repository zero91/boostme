<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "profile") {
            location.href = "<?=SITE_URL?>?user/profile.html";
          } else if (e.target.id == "password") {
            location.href = "<?=SITE_URL?>?user/uppass.html";
          } else if (e.target.id == "resume") {
            location.href = "<?=SITE_URL?>?user/resume.html";
          }
        });
        $("#menu_editimg").addClass("active");
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
      <li><a href="#password" id="password" role="tab" data-toggle="tab">修改密码</a></li>
      <li class="active"><a href="#modavatar" id="modavatar" role="tab" data-toggle="tab">修改头像</a></li>
      <li><a href="#resume" id="resume" role="tab" data-toggle="tab">我的简历</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane" id="profile"></div>
      <div class="tab-pane" id="password"></div>
      <div class="tab-pane in active" id="modavatar">
        <? if(isset($imgstr)) { ?>        <h5><?=$imgstr?></h5>
        <? } else { ?>        <form method="POST" action="<?=SITE_URL?>?user/uppass.html" class="form-horizontal">
          <h5>说明：</h5>
          <ol>
            <li>支持jpg、gif、png、jpeg四种格式图片上传</li>
            <li>图片大小不能超过2M;</li>
            <li>图片长宽大于80*80px时系统将自动压缩</li>
          </ol>
          <div class="form-group">
            <div class="col-md-2">
              <img class="img-circle" width="100px" height="100px" alt="<?=$user['username']?>" src="<?=$user['avatar']?>" />
            </div>
            <div class="col-md-3" style="margin-left:-10px;margin-top:10px;">
              <input type="file" id="file_upload" name="file_upload">
            </div> 
          </div>
          <!--
          <div style="margin-top:100px;">
            <p>浏览器不支持自动上传？点击“保存”按钮</p>
            <button type="button" name="uploadavatar" id="uploadavatar" class="btn btn-primary" style="width:120px;">
              保&nbsp;存
            </button>
          </div>
          -->
        </form>
        <? } ?>      </div>
      <div class="tab-pane" id="resume"></div>
    </div>
  </div>
</div>

<link href="<?=SITE_URL?>public/js/uploadify/uploadify.css" rel="stylesheet" type="text/css" />
<script src="<?=SITE_URL?>public/js/uploadify/jquery.uploadify.js?ver=<?php echo rand(0,9999);?>" type="text/javascript"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('#file_upload').uploadify({
        'swf': '<?=SITE_URL?>public/js/uploadify/uploadify.swf',
        'uploader': "<?=SITE_URL?>?user/editimg/<?=$user['uid']?>.html",
        'auto': true,
        'buttonText' : '更改头像',
        'buttonClass' : 'btn btn-primary',
        'height' : '40px',
        'fileObjName': 'userimage',
        'multi': false,
        'fileSizeLimit': "2MB",
        'fileTypeExts': '*.jpg;*.jpeg;*.gif;*.png',
        'fileTypeDesc': 'User Avatar(.JPG, .GIF, .PNG,.JPEG)',
        'onUploadSuccess': function() {
            alert('头像上传成功!');
            window.top.location.reload();
        }
    });
    /*
    $("#uploadavatar").click(function() {
        $('#file_upload').uploadify("upload", "*");
    });
    */
});

</script>
<? include template('footer'); ?>
