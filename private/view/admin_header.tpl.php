<? !defined('IN_SITE') && exit('Access Denied'); ?>
<!DOCTYPE html>
<html>
    <? global $starttime;
        $mtime = explode(' ', microtime());
        $setting = $this->setting;
        $user = $this->user;
        $toolbars="'".str_replace(",", "','", $setting['editor_toolbars'])."'";
        $regular = $this->regular;
         ?><head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=WEB_CHARSET?>"/>
    <title><? if($navtitle) { ?><?=$navtitle?> - <? } ?><?=$setting['site_name']?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?=$setting['site_name']?>" />
    <meta name="keywords" content="<?=$seo_keywords?>" />
    <meta name="robots" content="index, follow">
    <script src="<?=SITE_URL?>public/js/jquery.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/js/jquery-ui/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?=SITE_URL?>public/bootstrap/css/docs.min.css" />
    <script src="<?=SITE_URL?>public/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        var g_site_url = "<?=SITE_URL?>";
        var g_site_name = "<?=$setting['site_name']?>";
        var g_uid = <?=$user['uid']?>;
    </script>
    <style type="text/css">
    html,body {font-family: "Microsoft Yahei";height: 100%;}
    /* body {font-family: "Microsoft Yahei"; min-height: 100%; height: auto !important; height: 100%; margin: 0 auto -60px; } */

    .header {position: relative;}
    .header>a {padding: 6px 12px !important;  margin: 8px 3px !important; }
    .navbar-nav>li>button, .navbar-nav>li>a {padding: 6px 12px !important;  margin: 8px 3px !important; }
    .navbar-nav>li>a {background:#0066CC !important; color:#EEE !important;}
    .navbar-nav>li>a:hover {background:#0066CC !important; color:#FFF !important;}
    .align_center {text-align: center;}
    h4, h5, h6 { line-height: 26px; }
    .container { width: 100% !important;}
    .logo {
      width:200px;
      height:50px;
      font-size: 38px;
    } 
    </style>
</head>

<body>

<div style="min-height:92%;height:auto !important;"> <!-- fix the foot -->

<div class="navbar navbar-inverse navbar-nav navbar-fixed-top" role="navigation" style="background:#0066CC;">
  <div class="container header">
    <div class="navbar-header" style="width:175px;height:50px;">
      <a href="<?=SITE_URL?>" style="width:100%;height:100%"><img src="<?=SITE_URL?>public/css/default/logo.png" /></a>
    </div>
    <form class="navbar-form navbar-left" id="search_form" role="search" action="<?=SITE_URL?>?problem/search.html" method="post">
      <div class="form-group">
        <input type="text" class="form-control" style="width:300px" id="search-kw" name="word" placeholder="搜索求助" value="<?=$word?>">
        <span class="glyphicon glyphicon-search" style="margin-left:-30px;cursor:pointer;" onclick="search_form.submit();"></span>
      </div>
    </form>
    <a type="button" href="<?=SITE_URL?>?problem/add.html" class="btn btn-primary" style="background:#0e78e7;font-size:13px;">我要求助</a>
    <? if($user['uid'] != 0) { ?>    <ul class="nav navbar-nav" style="position:absolute;right:0px;top:0px;">
      <li><a class="btn btn-link" href="<?=SITE_URL?>?u-<?=$user['uid']?>.html" style="font-size:13px;"><?=$user['username']?></a></li>
      <li class="dropdown">
        <a class="btn btn-link dropdown-toggle" data-toggle="dropdown" style="font-size:13px;">
          收件箱
          <? if(($user['msg_system']+$user['msg_personal']) > 0) { ?>          <span class="badge alert-info"><? echo $user['msg_system']+$user['msg_personal'] ?></span>
          <? } ?>          <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
          <li><a onclick="msgClear();" href="<?=SITE_URL?>?message/personal.html">私人消息<? if($user['msg_personal']>0) { ?><span class="badge alert-info"><?=$user['msg_personal']?></span><? } ?></a></li>
          <li><a onclick="msgClear();" href="<?=SITE_URL?>?message/system.html">系统消息<? if($user['msg_system']>0) { ?><span class="badge alert-info"><?=$user['msg_system']?></span><? } ?></a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a type="button" class="dropdown-toggle btn dropdown-toggle" data-toggle="dropdown" style="font-size:13px;">
              账号 <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
          <li><a target="_self" href="<?=SITE_URL?>?user/profile.html">修改资料</a></li>
          <li class="divider"></li>
          <li><a href="<?=SITE_URL?>?user/logout.html" class="tuser_logout">退出</a></li>
        </ul>
      </li>
      <? if($user['isadmin']) { ?>      <li><a class="btn btn-link" href="<?=SITE_URL?>?admin_main/default.html" style="font-size:13px;">后台管理</a></li>
      <? } ?>    </ul>
    <? } else { ?>    <ul class="nav navbar-nav navbar-right">
      <li><a class="btn btn-link" data-toggle="modal" id="login_link" data-target="#loginModal" style="color:#EEE;font-size:13px;">登录</a></li>
      <li><a class="btn btn-link" href="<?=SITE_URL?>?user/register.html" style="color:#EEE;font-size:13px;">注册</a></li>
    </ul>
    <? } ?>  </div>
</div>

<div style="height:51px;"></div>

