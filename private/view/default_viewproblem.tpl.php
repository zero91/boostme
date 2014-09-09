<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<div style="height:29px;"></div>
<link rel="stylesheet" href="<?=SITE_URL?>public/js/lightbox/lightbox.css"/>
<script src="<?=SITE_URL?>public/js/lightbox/lightbox.js" type="text/javascript"></script>
<script type="text/javascript">
$(function(){
    $('.mypopover').popover({
        trigger:'manual',
        placement : 'right', //top, bottom, left or right
        title : '<div style="color:blue;text-align:center;font-size:14px;">用户信息</div>',
       html: 'true',
       animation: false
   });
    $('.mypopover').hover(function(){
        var jqObj = $(this);
        if (jqObj.attr("data-content") == "") {
            var uid=this.id.substring(7);
            $("#usercard").load(g_site_url + "index.php" + query + "user/ajaxuserinfo/" + uid, {}, function() {
                jqObj.attr("data-content", $('#usercard').html());
                jqObj.popover('show');
            });
        } else {
            jqObj.popover('show');
        }
    }, function(){
        $('.popover').popover('hide');
    });
})
</script>
<style type="text/css">
.pro_tags {margin: 10px 65px;}
.pro_tags a {margin-right: 10px; padding: 3px 7px;}
h4, h5, h6 { margin-top: 0px; margin-bottom: 0px;}

.photo {position: absolute; top: 15px; left: 15px; }
.action {position: absolute; right: 5px; top: 15px;}
.mypanel_for_action {margin: 0 70px 0 60px !important; padding: 0px 10px; line-height: 26px;}
.mypanel {margin: 0 0 0 65px !important; padding: 0px 10px; line-height: 26px; min-height: 90px;}
.panel-body {position: relative;}

#description img {max-width:200px;max-height:200px;}
</style>
<style type="text/css">
.input_ok {background: url("<?=SITE_URL?>public/css/default/input_ok.png") no-repeat scroll 6px 5px transparent;line-height: 30px;padding: 7px 6px 5px 28px;}
.input_error {background: url("<?=SITE_URL?>public/css/default/input_error.png") no-repeat scroll 6px 7px;font-size: 13px;color:red;line-height:30px;padding:6px 6px 7px 28px;}

/*===================for popover================*/
.trigger_btn {border: #ffffff; background: #ffffff; color: #428bca; display: block;}
.trigger_btn:hover {text-decoration: underline;}
.icon_common { margin-left: 2px; height: 15px; width: 8px;}
.icon_1 { background: url("<?=SITE_URL?>public/css/default/boy.gif") no-repeat scroll 0 50% rgba(0, 0, 0, 0); }
.icon_0 { background: url("<?=SITE_URL?>public/css/default/girl.gif") no-repeat scroll 0 50% rgba(0, 0, 0, 0); }
.popover {max-width: 700px !important; }<? if($problem['authorid'] != 0 && ($problem['authorid'] == $user['uid']) && (PB_STATUS_SOLVED == $problem['status'])) { ?>.star-rating {list-style:none; margin: 0px; padding:0px; width: 190px; height: 19px;position: relative; background: url("<?=SITE_URL?>css/default/heart.png") top left repeat-x; }
.star-rating li {padding:0px; margin:0px; float: left;position:relative;}
.star-rating li a{ display:block; width:19px; height:19px; text-decoration: none; text-indent: -9000px; z-index: 20; position: absolute; padding: 0px; }
.star-rating li a:hover{background: url("<?=SITE_URL?>css/default/heart.png") left bottom; z-index: 1; left: 0px;}
.star-rating a.star1{left: 0px;}
.star-rating a.star1:hover{width:19px;}
.star-rating a.stars2{left:19px;}
.star-rating a.stars2:hover{width:38px;}
.star-rating a.stars3{left:38px;}
.star-rating a.stars3:hover{width:57px;}
.star-rating a.stars4{left:57px; }
.star-rating a.stars4:hover{width:76px;}
.star-rating a.stars5{left:76px; }
.star-rating a.stars5:hover{width:95px;}
.star-rating a.stars6{left:95px; }
.star-rating a.stars6:hover{width:114px;}
.star-rating a.stars7{left:114px; }
.star-rating a.stars7:hover{width:133px;}
.star-rating a.stars8{left:133px; }
.star-rating a.stars8:hover{width:152px;}
.star-rating a.stars9{left:152px; }
.star-rating a.stars9:hover{width:171px;}
.star-rating a.stars10{left:171px; }
.star-rating a.stars10:hover{width:190px;}<? } ?></style>

<div class="container" 
    <? if($problem['authorid'] != 0 && ($problem['authorid'] == $user['uid']) && (PB_STATUS_SOLVED == $problem['status'])) { ?>    style="width:1220px !important"
    <? } ?> >
    <div class="row">
        <? if($problem['authorid'] != 0 && ($problem['authorid'] == $user['uid']) && (PB_STATUS_SOLVED == $problem['status'])) { ?>        <div class="col-md-7"> <? } else { ?> <div>
        <? } ?>            <div class="panel panel-info">
                <div class="panel-heading"><h3 class="panel-title">求助描述</h3></div>
                <div class="panel-body">
                    <div class="photo" style="max-width:60px">
                        <img width="60" height="60" src="<? echo get_avatar_dir($problem['authorid']); ?>" class="img-circle"/>
                    </div>
                    <div class="mypanel_for_action">
                    <div>
                        <a target="blank" href="<?=SITE_URL?>?u-<?=$problem['authorid']?>.html" class="" ><?=$problem['author']?></a>
                        <h5><?=$problem['title']?></h5>
                    </div>

                    <div class="pull-right">
                    <? if(PB_STATUS_UNSOLVED == $problem['status']) { ?>                        <? if($problem['authorid'] != 0 && ($problem['authorid'] != $user['uid'])) { ?>                            <? if($_ENV['demand']->already_demand($problem['pid'])) { ?>                            <a id="<?=$problem['pid']?>" type="button" class="action btn btn-link btn-sm prob_cancel_btn" role="button">撤销请求</a>
                            <? } else { ?>                            <a id="<?=$problem['pid']?>" type="button" class="action btn btn-link btn-sm prob_demand_btn" role="button">我要帮忙</a>
                            <? } ?>                        <? } ?>                    <? } elseif(PB_STATUS_SOLVED == $problem['status']) { ?>                        <a href="#" class="action btn btn-link btn-sm disabled" role="button">已成功</a>
                    <? } elseif(PB_STATUS_CLOSED == $problem['status']) { ?>                        <a href="#" class="action btn btn-link btn-sm disabled" role="button">求助已关闭</a>
                    <? } ?>                    </div>

                    <div class="">
                        
<? if(is_array($taglist)) { foreach($taglist as $tag) { ?>
                        <a class="btn btn-primary btn-xs" title="<?=$tag?>" href="<?=SITE_URL?>?problem/search/tag:<?=$tag?>.html"><?=$tag?></a>
                        
<? } } ?>
                    </div>
                    <div class="user-label" style="font-size:13px;">
                        <div class="user-label-info">
                            <span class="gold"><img src="<?=SITE_URL?>public/css/default/gold.gif">&nbsp;¥<?=$problem['price']?></span>
                            <span class="span-line">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                            <? if($problem['authorid'] == 0) { ?>                            <span><? if($problem['ip']) { ?><?=$problem['ip']?><? } else { ?>游客<? } ?></span>
                            <span class="span-line">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                            <? } ?>                            <span>浏览<?=$problem['views']?>次</span>&nbsp;&nbsp;|&nbsp;&nbsp;
                            <span><?=$problem['demands']?>人在抢</span>&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$problem['format_time']?>
                        </div>
                    </div>
                    <div class="" id="description" style="margin-top:13px;">
                      <p>详细要求：</p> <?=$problem['description']?>
                    </div>

                    <? if(PB_STATUS_CLOSED != $problem['status'] && 0!=$problem['authorid'] && ($problem['authorid']==$user['uid'])) { ?>                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h4>处理求助：</h4>
                            <h5> 如果尚未找到合适的人来解决你的求助，可以尝试以下操作：</h5>
                            <button class="btn btn-info btn-sm" type="button" title="修改求助标签" data-toggle="modal" data-target="#myModal">添加标签</button>
                            <button class="btn btn-info btn-sm" type="button" title="已解决，可以直接关闭求助" name="close_problem" id="close_problem">关闭求助</button>
                            <a href="<?=SITE_URL?>?problem/edit/<?=$problem['pid']?>.html" class="btn btn-info btn-sm" title="更改求助" name="edit_problem">更改求助</a>
                        </div>
                    </div>
                    <? } ?>                    </div>
                </div>
            </div><!-- problem sub panel end -->

            <? if($this->user['uid'] && $problem['solverid'] == $this->user['uid']) { ?>            <? $author_info = $_ENV['user']->get_by_uid($problem['authorid']) ?>            <p>您已成功抢得该求助，可以通过以下方式联系到<?=$problem['author']?></p>
            <p>手机：<?=$author_info['phone']?></p>
            <p>QQ：<?=$author_info['qq']?></p>
            <p>微信：<?=$author_info['wechat']?></p>
            <? } ?>            <? if(0!=$problem['authorid'] && ($problem['authorid']==$user['uid'])) { ?>            <? if($demand_user_lists) { ?>            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">有以下用户想要帮助您</h3>
                </div>
                <div class="panel-body">
                
<? if(is_array($demand_user_lists)) { foreach($demand_user_lists as $demand_user) { ?>
                    <!-- <div class="list-group-item"> -->
                    <div class="list-group-item" id="<?=$demand_user['uid']?>">
                        <? if($problem['status'] != PB_STATUS_SOLVED) { ?>                        <div class="pull-right">
                            <a type="button" class="btn btn-link btn-sm bm_demand_accept_btn" id="<?=$demand_user['uid']?>">接受</a><br/>
                            <a type="button" class="btn btn-link btn-sm bm_demand_denied_btn" id="<?=$demand_user['uid']?>">拒绝</a>
                        </div>
                        <? } ?>                        <div class="photo">
                            <a title="test002" target="_blank" href="<?=SITE_URL?>?u-<?=$demand_user['uid']?>.html">
                                <img width="60" height="60" src="<? echo get_avatar_dir($demand_user['uid']); ?>" class="img-circle" />
                            </a>
                            <a href="<?=SITE_URL?>?u-<?=$demand_user['uid']?>.html" target="blank" type="button" data-html="true" class="trigger_btn mypopover align_center" data-container="body"
                                data-toggle="popover" data-placement="left" data-trigger="manual" data-content='' id="popover<?=$demand_user['uid']?>">
                                <?=$demand_user['username']?>
                            </a>
                        </div>
                        <div class="mypanel"><h5>附加留言:<br><?=$demand_user['message']?></h5></div>
                    </div>
                
<? } } ?>
                </div>
            </div>
            <? } ?>            <? } ?>        </div><!-- left panel end -->

        <? if($problem['authorid'] != 0 && ($problem['authorid'] == $user['uid'])) { ?>        <? if($accept_users) { ?>        <div class="col-md-5">
            <div class="panel panel-info">
                <div class="panel-heading"><h3 class="panel-title">您选择以下用户解决您的求助</h3></div>
                
<? if(is_array($accept_users)) { foreach($accept_users as $accept_user) { ?>
                <div class="panel-body">
                    <div class="photo" style="max-width:60px">
                        <img width="60" height="60" src="<? echo get_avatar_dir($accept_user['uid']); ?>" class="img-circle">
                        <a target="blank" href="<?=SITE_URL?>?u-<?=$accept_user['uid']?>.html" class="trigger_btn align_center" ><?=$accept_user['username']?></a>
                    </div>
                    <div class="mypanel">
                        <? if(!($problem['solverscore'] > 0 && $problem['solverscore'] <= 10)) { ?>                        <p id="user_eval_tip" style="font-size:12px;color:grey;">
                            您还没有对该用户进行评价&nbsp;&nbsp;<a style="cursor:pointer;" data-toggle="modal" data-target="#eval_score">现在就去评价</a>
                        </p>
                        <? } ?>                        <h5>
                        <? if($accept_user['userinfo']['signature']) { ?>                        <p><span style="font-weight:bold">签名：</span><?=$accept_user['userinfo']['signature']?></p>
                        <? } ?>                        <? if($accept_user['userinfo']['phone']) { ?>                        <p><span style="font-weight:bold">手机：</span><?=$accept_user['userinfo']['phone']?></p>
                        <? } ?>                        <? if($accept_user['userinfo']['qq']) { ?>                        <p><span style="font-weight:bold">QQ：</span><?=$accept_user['userinfo']['qq']?></p>
                        <? } ?>                        <? if($accept_user['userinfo']['wechat']) { ?>                        <p><span style="font-weight:bold">微信：</span><?=$accept_user['userinfo']['wechat']?></p>
                        <? } ?>                        <? if($accept_user['userskill']) { ?>                        <p class="skill"><span style="font-weight:bold">擅长：</span>
                            
<? if(is_array($accept_user['userskill'])) { foreach($accept_user['userskill'] as $skill) { ?>
                            <i><?=$skill?></i>
                            
<? } } ?>
                        </p>
                        <? } ?>                        
<? if(is_array($accept_user['education'])) { foreach($accept_user['education'] as $edu) { ?>
                        <span style="font-weight:bold">
                            <? if($edu['edu_type'] == HIGH_SCHOOL) { ?> 高中：
                            <? } elseif($edu['edu_type'] == BACHELOR) { ?> 本科：
                            <? } elseif($edu['edu_type'] == MASTER) { ?> 硕士：
                            <? } elseif($edu['edu_type'] == DOCTOR) { ?> 博士：
                            <? } elseif($edu['edu_type'] == POST_DOCTOR) { ?> 博士后：
                            <? } else { ?> 经历： <? } ?>                        </span>
                        <?=$edu['school']?>&nbsp;&nbsp;<?=$edu['department']?>&nbsp;&nbsp;<?=$edu['major']?><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$edu['start_time']?>&nbsp;至&nbsp;<?=$edu['end_time']?><br/>
                        
<? } } ?>
                    </h5></div>
                    <div class="clearfix"></div>
                    <h5 style="font-size:11px;">成功抢单<?=$accept_user['userinfo']['solved']?>个</h5>
                    <? if($accept_user['userresume']['experience']) { ?>                    <div class="bs-callout bs-callout-info">
                        <h4>个人经历：</h4>
                        <h5><?=$accept_user['userresume']['experience']?></h5>
                    </div>
                    <? } ?>                </div><br/><br/>
                
<? } } ?>
            </div><!-- selected sub panel end -->
        </div>
        <? } ?>        <? } ?>    </div>
</div><? if(PB_STATUS_CLOSED != $problem['status'] && 0!=$problem['authorid'] && ($problem['authorid']==$user['uid'])) { ?><div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">编辑标签</h4>
      </div>
      <form name="edittagForm" action="<?=SITE_URL?>?problem/edittag.html" method="post">
      <div class="modal-body">
            <h6>多个标签请以空格隔开</h6>
            <input type="hidden"  value="<?=$problem['pid']?>" name="pid"/>
            <input type="text" placeholder="多个标签请以空格隔开" class="form-control" name="ptags" value="<? echo implode(' ',$taglist) ?>"/>
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="确&nbsp;认" />
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="eval_score" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">对该用户进行评价</h4>
      </div>
      <form name="edittagForm" action="<?=SITE_URL?>?problem/evaluser.html" method="post" onsubmit="return check_score();">
      <div class="modal-body"> 
        <textarea class="form-control" name="solverdesc" id="solverdesc" rows="5" placeholder="您觉得该用户的水平怎么样？您的收获大不大？"></textarea>
        <input type="hidden" name="pid" value="<?=$problem['pid']?>">
        <input type="hidden" name="score" id="user_score" value="0">
        <div style="margin-top:15px;">最终评分：<span id="star_tip"></span><span id="star_info_tip"></span></div>
        <div>
        <ul class="star-rating">
          <li><a id="star1" title="1" class="star1">1</a></li>
          <li><a id="star2" title="2" class="stars2">2</a></li>
          <li><a id="star3" title="3" class="stars3">3</a></li>
          <li><a id="star4" title="4" class="stars4">4</a></li>
          <li><a id="star5" title="5" class="stars5">5</a></li>
          <li><a id="star6" title="6" class="stars6">6</a></li>
          <li><a id="star7" title="7" class="stars7">7</a></li>
          <li><a id="star8" title="8" class="stars8">8</a></li>
          <li><a id="star9" title="9" class="stars9">9</a></li>
          <li><a id="star10" title="10" class="stars10">10</a></li>
        </ul>
            &nbsp;&nbsp;1&nbsp;&nbsp;2&nbsp;&nbsp;&nbsp;3&nbsp;&nbsp;4&nbsp;&nbsp;&nbsp;5&nbsp;&nbsp;6&nbsp;&nbsp;&nbsp;7&nbsp;&nbsp;8&nbsp;&nbsp;&nbsp;9&nbsp;&nbsp;10
        </div>
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="确&nbsp;认"/></div>
      </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --><? } ?> 

<div class="modal fade" id="demandModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">附加信息</h4>
      </div>
      <form name="edit_message" action="<?=SITE_URL?>?problem/demand.html" method="post" id="dialog_form">
        <div class="modal-body">
          <input type="hidden" value="" name="dialog_pid" id="dialog_pid"/>
          <input type="text" placeholder="请输入简短的请求信息" class="form-control" name="demand_message" id="demand_message"/>
        </div>
        <div class="modal-footer">
          <input type="submit" class="btn btn-primary" value="确&nbsp;认" />
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div style="height:29px;"></div>

<script type="text/javascript">

$(document).ready(function() {
    $("#description img").each(function(i) {
        var img = $(this);
        $.ajax({
            type: "POST",
            url: "<?=SITE_URL?>index.php?index/ajaxchkimg",
            async: true,
            data: "imgsrc=" + img.attr("src"),
            success: function(status) {
                if ('1' == status) {
                    img.wrap("<a href='" + img.attr("src") + "' title='" + img.attr("title") + "' data-lightbox='comment'></a>");
                }
            }
        });
    }); 

    $(".prob_demand_btn").click(function() {
        var pid = $(this).attr("id");
        $("#dialog_pid").val(pid);
        $("#demandModal").modal('show');
    });

    $(".star-rating").click(function(e) {
        var parentOffset = $(this).parent().offset(); 
        var relX = e.pageX - parentOffset.left;
        var relY = e.pageY - parentOffset.top;
        $(".star-rating>li>a").width(19);
        var elem_id = Math.ceil(relX / 19);
        $("#star_tip").html(elem_id + "分");
        $("#star" + elem_id).width(elem_id * 19);
        $("#user_score").val(elem_id);
        $("#star" + elem_id).css({"background":"url(\"<?=SITE_URL?>css/default/heart.png\") left bottom","z-index":"1","left":"0"});
        $('#star_info_tip').html("&nbsp;");
        $('#star_info_tip').removeClass('input_error');
    });

    $(".prob_cancel_btn").click(function(){
        if (<?=$user['uid']?> == 0) {
            alert("您还未登录，请先登录");
            window.location.href = "<?=SITE_URL?>index.php" + query + "user/login";
        }

        if (confirm('确定撤销请求?') === false) {
            return false;
        }

        var supportobj = $(this);
        var pid = $(this).attr("id");
        $.ajax({
            type: "GET",
            url:"<?=SITE_URL?>index.php" + query + "problem/ajaxcancel/" + pid,
            cache: false,
            success: function(succeed){
                if (succeed == '-1') {
                    alert("您还未登录，请先登录");
                    window.location.href = "<?=SITE_URL?>index.php" + query + "user/login";
                } else if (succeed == '0') {
                    alert("操作失败，请刷新页面重试");
                } else if (succeed == '1') {
                    supportobj.html("我要帮忙");
                } else if (succeed == '2') {
                    alert("您还没有发出过申请!");
                }
            }
        });
    });

    <? if(PB_STATUS_CLOSED != $problem['status'] && 0!=$problem['authorid'] && ($problem['authorid']==$user['uid'])) { ?>    //关闭求助
    $("#close_problem").click(function() {
        if (confirm('确定关闭该求助?') === true) {
            document.location.href = "<?=SITE_URL?>?problem/close/<?=$problem['pid']?>.html";
        }
    });
    
    //删除求助
    $("#delete_question").click(function() {
        if (confirm('确定删除求助？该操作不可返回！') === true) {
            document.location.href = "<?=SITE_URL?>?problem/delete/<?=$problem['pid']?>.html";
        }
    });
    <? } ?>    
    $(".bm_demand_accept_btn").click(function(){
        var supportobj = $(this);
        uid = $(this).attr("id");

        if (confirm('确定选择该用户?') === false) {
            return false;
        }
        $.ajax({
            type: "GET",
            url:"<?=SITE_URL?>index.php" + query + "problem/ajaxaccept/" + uid + "/<?=$problem['pid']?>",
            cache: false,
            success: function(succeed){
                if (succeed == '0') {
                    alert("交易失败，请刷新页面重试！");
                } else if (succeed == '1') {
                    window.location.reload();
                } else if (succeed == '2') {
                    alert("您还没有填写任何联系方式！");
                    document.location.href = "<?=SITE_URL?>?user/profile.html";
                } else {
                    alert("发生异常!");
                }
            }
        });
    }); 

    $(".bm_demand_denied_btn").click(function(){
        var supportobj = $(this);
        var grandparentobj = $(this).parent().parent(); 
        uid = $(this).attr("id");

        if (confirm("确定该用户不满足您的需求?") === false) {
            return false;
        }
        $.ajax({
            type: "GET",
            url:"<?=SITE_URL?>index.php" + query + "problem/ajaxdenied/" + uid + "/<?=$problem['pid']?>",
            cache: false,
            success: function(succeed){
                if (succeed == '1') {
                    grandparentobj.hide();
                } else {
                    alert("发生异常!");
                }
            }
        });
    });

    SyntaxHighlighter.all();
});

function hide_all_user_btn()
{
    $(".bm_demand_accept_btn").hide();
    $(".bm_demand_denied_btn").hide();
}<? if(PB_STATUS_CLOSED != $problem['status'] && 0!=$problem['authorid'] && ($problem['authorid']==$user['uid'])) { ?>function edittag() {
    $("#dialog_tag").dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        resizable: false
    });
    $("#dialog_tag").dialog("open");
}

function check_score() {
    var score = $('#user_score').val();
    if (score >= 1 && score <= 10) {
        $('#star_info_tip').html("&nbsp;");
        $('#star_info_tip').attr('class', 'input_ok');
        $('#user_eval_tip').hide();
        return true;
    } else {
        $('#star_info_tip').html("请选择评分");
        $('#star_info_tip').attr('class', 'input_error');
        return false;
    }
}<? } ?></script>
<? include template('footer'); ?>
