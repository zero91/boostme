<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<style type="text/css">
  .photo {position: absolute; top: 20px; left: 10px; }
  .action {position: absolute; right: 50px; bottom: 20px;}
  .list-group-item {min-height: 80px;border:0px;padding: 10px 5px;}
  .mypanel {margin: 0 10px 0 75px !important; padding: 10px; line-height: 26px;}
  .arrow {left:76px;top:40px;position:absolute;}
  .arrow:before,.arrow:after{color:transparent;bottom:100%;border:solid;content:"";height:0;width:0;position:absolute;pointer-events:none}
  .arrow:before{border-color:transparent #e7e7e7 transparent transparent;border-width:6px;left:50%;margin-left:-8px;top:50%;margin-top:-8px}
  .arrow:after{border-color:transparent #fff transparent transparent;border-width:6px;left:50%;margin-left:-6px;top:50%;margin-top:-8px}
</style>

<div class="container" style="margin-top:23px;">
  <!--
  <ul class="nav nav-tabs" role="tablist">
  <li class="active"><a href="#now_prob" role="tab" data-toggle="tab">全部</a></li>
  <li><a href="#my_prob" role="tab" data-toggle="tab">已解决</a></li>
  <li><a href="#sys_sugg" role="tab" data-toggle="tab">推荐</a></li> i
  </ul>
  -->
  <div class="col-sm-9">
    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="now_prob">
        <div class="list-group">
          
<? if(is_array($page_indexshowprob)) { foreach($page_indexshowprob as $problem) { ?>
          <div id="<?=$problem['pid']?>" class="list-group-item">
            <div class="photo" style="max-width:60px">
              <img class="img-circle" width="60px" height="60px" src=<? echo get_avatar_dir($problem['authorid']); ?>>
            </div>
            <div class="panel panel-default mypanel">
              <a href="<?=SITE_URL?>?u-<?=$problem['authorid']?>.html" class="trigger_btn align_center" ><?=$problem['author']?></a>
              <p style="font-size:14px"><?=$problem['title']?></p>
              <a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html" style="font-size:12px" target="_blank">查看详细信息</a>
              <span style="font-size:12px">&nbsp;&nbsp;回报：¥<?=$problem['price']?>元/小时&nbsp;&nbsp;|&nbsp;&nbsp;<?=$problem['demands']?>人在抢</span>
              <div class="action" style="font-size:12px">
                <? if($problem['solverid'] != 0) { ?>                <span class="last" id="<?=$problem['pid']?>" style="color:red;">已成功</span>
                <? } else { ?>                  <? if($problem['authorid'] != $user['uid']) { ?>                    <? if($_ENV['demand']->already_demand($problem['pid'])) { ?>                    <a class="prob_cancel last" id="<?=$problem['pid']?>" href="#" onclick="javascript:return false;">撤销请求</a>
                    <? } else { ?>                    <a class="prob_demand last" id="<?=$problem['pid']?>" href="#" onclick="javascript:return false;">我要帮忙</a>
                    <? } ?>                  <? } else { ?>                  <a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html" class="last" target="_blank">查看</a>
                  <? } ?>                <? } ?>              </div>
              <span class="arrow"></span>
            </div>
          </div>   
          
<? } } ?>
        </div>    
        <center><ul class="pagination"><?=$departstr?></ul></center>
      </div><!-- existing problems tab end -->
    </div>
  </div>
  <div class="col-sm-3">
    <span>3号群已满，同学们可以加入4号QQ群：32480019</span>
    <ul class="list-group" style="font-size:13px;">
      <li class="list-group-item">
        <div class="panel panel-info">
          <div class="panel-heading">找学长学姐</div>
          <div class="panel-body">
            <p>考研路，问题多，思来思去难奈何！</p>
            <p>亲，想找名校的牛掰学长学姐来给你答疑解惑吗？</p>
            <p>这就说明清楚自己的情况，寻找你心仪的学长学姐吧！</p>
            <a href="<?=SITE_URL?>?problem/add.html">点击进入</a>
          </div>
        </div>
      </li>
      <li class="list-group-item">
        <div class="panel panel-info">
          <div class="panel-heading">找家教兼职</div>
          <div class="panel-body">
            <p>考研路，坑满地，我们帮你跳过去！</p>
            <p>Boostme聚集了一大批985名校的牛掰学生，</p>
            <p>如果您是985名校的学生，欢迎你的加入！</p>
            <p>这就填写资料申请加入吧！&nbsp;<a href="<?=SITE_URL?>?user/resume.html">进入</a></p>
          </div>
        </div>
      </li>
    </ul>
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
    <? if($user['uid']) { ?>      <? if($user['can_teach']) { ?>      var pid = $(this).attr("id");
      $("#dialog_pid").val(pid);
      $("#demandModal").modal('show');
      <? } else { ?>      alert("您还没有获得资格，现在就去填写资料申请吧");
      window.location.href = "<?=SITE_URL?>?user/resume";
      <? } ?>    <? } else { ?>      alert("您还未登录，请先登录");
      window.location.href = "<?=SITE_URL?>?user/login";
    <? } ?>  });

  $(".prob_cancel").click(function(){
    <? if($user['uid']) { ?>    if (confirm('确定撤销请求?') === false) {
      return false;
    }
    var supportobj = $(this);
    var pid = $(this).attr("id");
    $.ajax({
      type: "GET",
      url:"<?=SITE_URL?>?problem/ajaxcancel/" + pid,
      cache: false,
      success: function(succeed){
        if (succeed == '-1') {
          alert("您还未登录，请先登录");
          window.location.href = "<?=SITE_URL?>?user/login";

        } else if (succeed == '0') {
          alert("操作失败，请刷新页面重试");

        } else if (succeed == '1') {
          supportobj.html("我要帮忙");

        } else if (succeed == '2') {
          alert("您还没有发出过申请!");
        }
      }
    });
    <? } else { ?>    alert("您还未登录，请先登录");
    window.location.href = "<?=SITE_URL?>?user/login";
    <? } ?>  });
});

</script>
<? include template('footer'); ?>
