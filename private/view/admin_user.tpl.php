<? !defined('IN_SITE') && exit('Access Denied'); include template(header,'admin'); ?>
<link rel="stylesheet" href="<?=SITE_URL?>js/lightbox/lightbox.css"/>
<script src="<?=SITE_URL?>js/lightbox/lightbox.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "jump1") {
            location.href = "<?=SITE_URL?>?admin_user/default.html";
          } else if (e.target.id == "jump2") {
            location.href = "<?=SITE_URL?>?admin_user/apply.html";
          }
        })
    })
</script>
<style type="text/css">
    .mybadge_div {margin-top: 10px; margin-bottom: 10px;}
    .mybadge {margin: 10px 20px;}
    .photo {position: absolute; top: 20px; left: 10px; }
    .action {position: absolute; right: 50px; bottom: 20px;} /* panel右下角的动作 */
    .list-group-item {min-height: 80px;border:0px;padding: 10px 5px;}
    .mypanel {margin: 0 10px 0 75px !important; padding: 10px; line-height: 26px;}
    .arrow {left:76px;top:40px;position:absolute;}
    .arrow:before,.arrow:after{color:transparent;bottom:100%;border:solid;content:"";height:0;width:0;position:absolute;pointer-events:none}
    .arrow:before{border-color:transparent #e7e7e7 transparent transparent;border-width:6px;left:50%;margin-left:-8px;top:50%;margin-top:-8px}
    .arrow:after{border-color:transparent #fff transparent transparent;border-width:6px;left:50%;margin-left:-6px;top:50%;margin-top:-8px}
</style>

<div class="container">
  <div class="col-md-2">
<? include template(leftmenu,'admin'); ?>
</div>
  <div class="col-md-9">
    <ul class="nav nav-tabs" role="tablist" style="font-size:13px;">
        <? if($type =="user/default") { ?>        <li class="active">
            <a href="javascript:void(0)" role="tab" data-toggle="tab">全部用户</a>
        </li>
        <? } else { ?>        <li>
            <a href="#no_content" id="jump1" role="tab" data-toggle="tab">全部用户</a>
        </li>
        <? } ?>        <? if($type =="user/apply") { ?>        <li class="active">
            <a href="javascript:void(0)" role="tab" data-toggle="tab">申请用户</a>
        </li>
        <? } else { ?>        <li>
            <a href="#no_content" id="jump2" role="tab" data-toggle="tab">申请用户</a>
        </li>
        <? } ?>    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="all_prob">
        <div class="list-group">
            
<? if(is_array($userlist)) { foreach($userlist as $user) { ?>
            <div id="<?=$problem['pid']?>" class="list-group-item">
                <div class="photo" style="max-width:60px">
                    <img class="img-circle" width="60px" height="60px" src=<?=$user['avatar']?>>
                </div>
                <div class="panel panel-default mypanel">
                    <p><a href="<?=SITE_URL?>?u-<?=$user['uid']?>.html" class="trigger_btn align_center"><?=$user['username']?></a></p>
                    <? if($type == "user/apply") { ?>                    <a class="accept-user pull-right" id="<?=$user['uid']?>" href="#" onclick="javascript:return false;">接受请求</a>
                    <p>真实姓名：<?=$user['realname']?></p>
                    <p>手机：<?=$user['phone']?></p>
                    <p>QQ：<?=$user['qq']?></p>
                    <p>wechat：<?=$user['wechat']?></p>
                    <p>身份证号：<?=$user['ID']?></p>
                    <p>身份证照片：<a href="<?=$user['ID_path']?>"><img src="<?=$user['ID_path']?>" width="50px" height="50px" id="img_id_path_pic_<?=$user['uid']?>"></a></p>
                    <p>学生证照片：<a href="<?=$user['studentID']?>"><img src="<?=$user['studentID']?>" width="40px" height="40px" id="img_studentID_<?=$user['uid']?>"></a></p>
                    <p>电子简历：<a class="btn btn-link" href="<?=$user['resume_path']?>" target="_blank" id="resume_link"><? if(empty($user['realname'])) { ?><?=$user['username']?><? } else { ?><?=$user['realname']?><? } ?>简历</a></p>
                    <? $edulist = $_ENV['education']->get_by_uid($user['uid']) ?>                    
<? if(is_array($edulist)) { foreach($edulist as $edu) { ?>
                    <span style="font-weight:bold">
                        <? if($edu['edu_type'] == HIGH_SCHOOL) { ?> 高中：
                        <? } elseif($edu['edu_type'] == BACHELOR) { ?> 本科：
                        <? } elseif($edu['edu_type'] == MASTER) { ?> 硕士：
                        <? } elseif($edu['edu_type'] == DOCTOR) { ?> 博士：
                        <? } elseif($edu['edu_type'] == POST_DOCTOR) { ?> 博士后：
                        <? } else { ?> 经历： <? } ?>                    </span>
                    <?=$edu['school']?>&nbsp;&nbsp;<?=$edu['department']?>&nbsp;&nbsp;<?=$edu['major']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$edu['start_time']?>&nbsp;至&nbsp;<?=$edu['end_time']?><br/>
                    <p>
                    
<? } } ?>
                    <p>申请时间：<?=$user['apply_time']?></p>

                    <? } else { ?>                    <p>E-mail：<?=$user['email']?></p>
                    <p>注册时间：<?=$user['regtime']?></p>
                    <p>注册IP：<?=$user['regip']?></p>
                    <? } ?>                    <span class="arrow"></span>
                </div>
                <div class="clearfix"></div>
            </div>   
            
<? } } ?>
        </div>    
        <ul class="pagination"><?=$departstr?></ul>
      </div><!-- existing problems tab end -->
      <div class="tab-pane" id="my_prob"></div>
      <div class="tab-pane" id="sys_sugg"></div>
    </div>
  </div>
</div><!-- container end -->

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

<script type="text/javascript">
var timer;
$(document).ready(function() {
    $(".panel img").each(function(i) {
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

    $(".accept-user").click(function(){
        if (confirm('确定赋予该用户资格?') === false) {
            return false;
        }

        var uid = $(this).attr('id');
        var supportobj = $(this);
        var hide_target = $(this).parent().parent();

        $.ajax({
            type: "GET",
            url:"<?=SITE_URL?>index.php?admin_user/accept_apply/" + uid,
            cache: false,
            success: function(succeed){
                alert("succeed = " + succeed);
                if (succeed == '-1') {
                    alert("操作失败！");
                } else if (succeed == '1') {
                    hide_target.hide();
                }
            }
        });
    });
});

</script>
<? include template(footer,'admin'); ?>
