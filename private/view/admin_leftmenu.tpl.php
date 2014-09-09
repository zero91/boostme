<? !defined('IN_SITE') && exit('Access Denied'); ?>
<style type="text/css">
    .avatar {position: absolute; top:5px; left:5px;}
    .userinfo {margin: 0 10px 0 80px !important; padding: 15px; line-height: 26px;}

    .nav-sidebar > .active > a, .nav-sidebar > .active > a:hover, .nav-sidebar > .active > a:focus {
      color: #fff;
      background-color: #428bca;
    }
    .nav-sidebar > li > a {
      padding-right: 20px;
      padding-left: 20px;
    }
    .sidebar {
      margin-top: 20px;
    }
    .nav-sidebar {
      margin-top: 5px;
      margin-bottom: 5px;
      background: #f5f5f5;
    }
</style>
<div class="sidebar">
  <div class="panel" style="position:relative;">
    <img class="img-circle avatar" src="<?=$user['avatar']?>" width="70px" height="70px">
    <div class="userinfo" style="font-size:13px;height:80px;">
        <h5><?=$user['username']?><br/>[管理员]</h5>
    </div>
  </div>

  <ul class="list-unstyled" style="font-size:13px;">
    <li><h5>系统用户</h5></li>
    <ul class="nav nav-sidebar">
      <li id="menu_user_all" <? if($type == "user/default") { ?> class="active"<? } ?> > <a href="<?=SITE_URL?>?admin_user/default.html">全部用户</a> </li> 
      <li id="menu_user_apply" <? if($type == "user/apply") { ?> class="active"<? } ?> > <a href="<?=SITE_URL?>?admin_user/apply.html">申请用户</a> </li> 
    </ul>

    <li><h5>我的求助</h5></li>
    <ul class="nav nav-sidebar">
      <li id="menu_problem_all" <? if($type == "user/problem/all") { ?> class="active"<? } ?> > <a href="<?=SITE_URL?>?user/problem/all.html">我的求助</a> </li>
      <li id="menu_problem_solved" <? if($type == "user/problem/solved") { ?> class="active"<? } ?> > <a href="<?=SITE_URL?>?user/problem/solved.html">解决求助</a> </li>
    </ul>

    <li><h5>个人信息</h5></li>
    <ul class="nav nav-sidebar">
      <li id="menu_profile"  <? if($type == "user/profile") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?user/profile.html">基本信息</a></li>
      <li id="menu_uppass" <? if($type == "user/uppass") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?user/uppass.html">修改密码</a></li>
      <li id="menu_editimg" <? if($type == "user/editimg") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?user/editimg.html">修改头像</a></li>
      <li id="menu_resume" <? if($type == "user/resume") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?user/resume.html">我的简历</a></li>
    </ul>

    <li><h5>收件箱</h5></li>
    <ul class="nav nav-sidebar">
      <li id="menu_personal" <? if($type == "message/personal") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?message/personal.html">私人消息</a></li>
      <li id="menu_system" <? if($type == "message/resume") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?message/system.html">系统消息</a></li>
    </ul>

    <li><h5>系统相关</h5></li>
    <ul class="nav nav-sidebar">
      <li id="menu_feedback" <? if($type == "feedback/default") { ?> class="active"<? } ?> ><a href="<?=SITE_URL?>?admin_feedback/default.html">用户反馈</a></li>
    </ul>
  </ul>
</div>

