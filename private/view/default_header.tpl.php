<? !defined('IN_SITE') && exit('Access Denied'); ?>
<!DOCTYPE html>
<html>
<head>
  <? $user = $this->user;
  $setting = $this->setting;
  $toolbars="'".str_replace(",", "','", $setting['editor_toolbars'])."'";
   ?>  <meta http-equiv="Content-Type" content="text/html; charset=<?=WEB_CHARSET?>"/>
  <title><? if($navtitle) { ?><?=$navtitle?> - <? } ?><?=$setting['site_name']?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="考研，家教，找人辅导，高校，名校" />
  <meta name="description" content="找名校学生做家教，找兼职" />
  <meta name="robots" content="index, follow">
  <script src="<?=SITE_URL?>public/js/jquery.js" type="text/javascript"></script>
  <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/css/default/main.css" />
  <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/js/jquery-ui/jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/bootstrap/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/bootstrap/css/docs.min.css" />
  <link rel="shortcut icon" href="<?=SITE_URL?>public/css/default/icon.png" />
  <script src="<?=SITE_URL?>public/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
  <script type="text/javascript">
    var g_site_url = "<?=SITE_URL?>";
  </script>
  <style type="text/css">
    html,body {font-family: "Microsoft Yahei";height: 100%;}
    .header {position: relative;}
    .header>a {padding: 6px 12px !important;  margin: 8px 3px !important; }
    .navbar-nav>li>button, .navbar-nav>li>a {padding: 6px 12px !important;  margin: 8px 3px !important; }
    .navbar-nav>li>a {background:#0066CC !important; color:#EEE !important;}
    .navbar-nav>li>a:hover {background:#0066CC !important; color:#FFF !important;}
    .align_center {text-align: center;}
    h4, h5, h6 { line-height: 26px; }
    .container { width: 1100px !important;}
    .logo {width:200px;height:50px;font-size: 38px;} 
    .input_ok {background: url("<?=SITE_URL?>public/css/default/input_ok.png") no-repeat scroll 6px 5px transparent;line-height: 30px;padding: 7px 6px 5px 28px;}
    .input_error {background: url("<?=SITE_URL?>public/css/default/input_error.png") no-repeat scroll 6px 7px;font-size: 13px;color:red;line-height:30px;padding:6px 6px 7px 28px;}
  </style>
</head>

<body>
<div style="min-height:92%;height:auto !important;"> <!-- fix the foot -->

<div class="navbar navbar-fixed-top" role="navigation" style="background:#0066CC;">
  <div class="container">
    <div class="navbar-header" style="width:175px;height:50px;">
      <a href="<?=SITE_URL?>" style="width:100%;height:100%"><img src="<?=SITE_URL?>public/css/default/logo.png" /></a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li>
          <form class="navbar-form navbar-left" id="search_form" role="search" action="<?=SITE_URL?>?problem/search.html" method="post">
            <div class="form-group">
              <input type="text" class="form-control" style="width:300px" id="search-kw" name="word" placeholder="搜索求助" value="<?=$word?>">
              <span class="glyphicon glyphicon-search" style="margin-left:-30px;cursor:pointer;" onclick="search_form.submit();"></span>
            </div>
          </form>
        </li>
        <li><a type="button" href="<?=SITE_URL?>?problem/add.html" class="btn btn-primary" style="background-color:#0e78e7;font-size:13px;">我要求助</a></li>
      </ul> 
      <? if($user['uid'] != 0) { ?>      <ul class="nav navbar-nav navbar-right">
        <li><a class="btn btn-link" href="<?=SITE_URL?>?u-<?=$user['uid']?>.html" style="font-size:13px;"><?=$user['username']?></a></li>
        <li class="dropdown">
          <a class="btn btn-link dropdown-toggle" data-toggle="dropdown" style="font-size:13px;">
            收件箱
            <? if(($user['msg_system'] + $user['msg_personal']) > 0) { ?>            <span class="badge alert-info"><? echo $user['msg_system'] + $user['msg_personal'] ?></span>
            <? } ?>            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="<?=SITE_URL?>?message/personal.html">私人消息<? if($user['msg_personal']>0) { ?><span class="badge alert-info"><?=$user['msg_personal']?></span><? } ?></a></li>
            <li><a href="<?=SITE_URL?>?message/system.html">系统消息<? if($user['msg_system']>0) { ?><span class="badge alert-info"><?=$user['msg_system']?></span><? } ?></a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a type="button" class="dropdown-toggle btn dropdown-toggle" data-toggle="dropdown" style="font-size:13px;">账号<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a target="_blank" href="<?=SITE_URL?>?user/profile.html">修改资料</a></li>
            <li class="divider"></li>
            <li><a href="<?=SITE_URL?>?user/logout.html">退出</a></li>
          </ul>
        </li>
        <? if($this->user['isadmin']) { ?>        <li><a class="btn btn-link" href="<?=SITE_URL?>?admin_main/default.html" style="font-size:13px;">后台管理</a></li>
        <? } ?>      </ul>
      <? } else { ?>      <ul class="nav navbar-nav navbar-right">
        <li><a class="btn btn-link" data-toggle="modal" id="login_link" data-target="#loginModal" style="color:#EEE;font-size:13px;">登录</a></li>
        <li><a class="btn btn-link" href="<?=SITE_URL?>?user/register.html" style="color:#EEE;font-size:13px;">注册</a></li>
      </ul>
      <? } ?>    </div>
  </div>
</div>

<div style="height:51px;"></div>

<div class="modal fade" id="loginModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
        </button>
        <h4 class="modal-title">欢迎登录Boostme</h4>
      </div>
      <div class="modal-body" id="login_body"></div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(function() {
  if ($("#login_link")) {
    $("#login_body").load("<?=SITE_URL?>?user/ajaxpoplogin");
  }
});
</script> 
