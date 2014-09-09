<? !defined('IN_SITE') && exit('Access Denied'); include template(header,'admin'); ?>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "jump1") {
            location.href = "<?=SITE_URL?>?admin_main/default.html";
          } else if (e.target.id == "jump2") {
            location.href = "<?=SITE_URL?>?admin_main/user.html";
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
  <div class="col-md-3">
<? include template(leftmenu,'admin'); ?>
</div>
  <div class="col-md-9">
    <ul class="nav nav-tabs" role="tablist" style="font-size:13px;">
        <li class="active"><a href="javascript:void(0)" role="tab" data-toggle="tab">所有求助</a></li>

        <li><a href="#no_content" id="jump2" role="tab" data-toggle="tab">系统消息</a></li>
          <li><a href="#my_prob" role="tab" data-toggle="tab">已解决求助</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="all_prob">
        <div class="list-group">
            
<? if(is_array($problemlist)) { foreach($problemlist as $problem) { ?>
            <div id="<?=$problem['pid']?>" class="list-group-item">
                <div class="photo" style="max-width:60px">
                    <img class="img-circle" width="60px" height="60px" src=<? echo get_avatar_dir($problem['authorid']); ?>>
                    <a href="<?=SITE_URL?>?u-<?=$problem['authorid']?>.html" class="trigger_btn align_center" ><?=$problem['author']?></a>
                </div>
                <div class="panel panel-default mypanel">
                    <p style="font-size:14px"><?=$problem['title']?></p>
                    <a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html" style="font-size:12px" target="_blank">查看详细信息</a>
                    <span style="font-size:12px">&nbsp;&nbsp;回报：¥<?=$problem['price']?>&nbsp;&nbsp;|&nbsp;&nbsp;<?=$problem['demands']?>人在抢</span>
                    <div class="action" style="font-size:12px">
                        <? if($problem['authorid'] != $user['uid']) { ?>                        <? if($_ENV['demand']->already_demand($problem['pid'])) { ?>                        <a class="prob_cancel last" id="<?=$problem['pid']?>" href="#" onclick="javascript:return false;">撤销请求</a>
                        <? } else { ?>                        <a class="prob_demand last" id="<?=$problem['pid']?>" href="#" onclick="javascript:return false;">我要帮忙</a>
                        <? } ?>                        <? } else { ?>                        <a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html" class="last" target="_blank">查看</a>
                        <? } ?>                    </div>
                    <span class="arrow"></span>
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
    $(".prob_demand").click(function() {
        var pid = $(this).attr("id");
        $("#dialog_pid").val(pid);
        $("#demandModal").modal('show');
    });

    $(".prob_cancel").click(function(){
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

    //slider
    $(".pagination li").hover(function() {
        $(".pagination li[class='spanhover']").removeClass("spanhover");
        var topicid = $(this).attr("topicid");
        $(this).addClass("spanhover");
        timer = setTimeout(function() {
            $(".topic").hide();
            $("#" + topicid).show();
        }, 100);
    });
    $("#weektab").hover(function() {
        $(this).attr("class", "select");
        $("#alltab").attr("class", "not-selected");
        $("#weektop").show();
        $("#alltop").hide();
    });
    $("#alltab").hover(function() {
        $(this).attr("class", "select");
        $("#weektab").attr("class", "not-selected");
        $("#alltop").show();
        $("#weektop").hide();
    });
});

</script>
<? include template(footer,'admin'); ?>
