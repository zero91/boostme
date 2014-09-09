<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
 <style type="text/css">
    .avatar {position: absolute; top: 20px; left: 10px;}
    .msg_content {margin: 0 10px 0 55px !important; padding: 10px; line-height: 26px;}
</style>

<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "jump1") {
            location.href = "<?=SITE_URL?>?user/problem/all.html";
          } else if (e.target.id == "jump2") {
            location.href = "<?=SITE_URL?>?user/problem/solved.html";
          }
        })
    })
</script>

<div class="container" style="margin-top:5px;">
  <div class="col-md-3">
<? include template('leftmenu'); ?>
</div>
  <div class="col-md-9">
    <h5 style="float:left;">我的求助</h5>
    <div class="clearfix"></div> 
    <ul class="nav nav-tabs" role="tablist" style="font-size:13px;">
      <? if($op_type =="all") { ?>      <script type="text/javascript">
        $("#menu_problem_all").addClass("active");
      </script>
      <li class="active">
        <a href="javascript:void(0)" role="tab" data-toggle="tab">我的求助</a>
      </li>
      <? } else { ?>      <li>
        <a href="#no_content" id="jump1" role="tab" data-toggle="tab">我的求助</a>
      </li>
      <? } ?>      <? if($op_type =="solved") { ?>      <script type="text/javascript">
        $("#menu_problem_solved").addClass("active");
      </script>
      <li class="active">
        <a href="javascript:void(0)" role="tab" data-toggle="tab">解决求助</a>
      </li>
      <? } else { ?>      <li>
        <a href="#no_content" id="jump2" role="tab" data-toggle="tab">解决求助</a>
      </li>
      <? } ?>    </ul>

    <div class="tab-content">
      <div class="tab-pane active">
        <div class="list-group">
          
<? if(is_array($problemlist)) { foreach($problemlist as $problem) { ?>
          <div id="prob_<?=$problem['pid']?>" class="list-group-item 
              <? if($problem['solverid'] != 0) { ?>              list-group-item-success
              <? } elseif($problem['status'] != PB_STATUS_CLOSED) { ?>              list-group-item-warning
              <? } ?>"
              style="padding-top:15px;padding-bottom:28px;font-size:13px;margin-bottom:15px;">
            <span><?=$problem['title']?></span>
            <span><a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html">查看详细</a></span><br/>
            <span class="pull-right">
                <?=$problem['format_time']?>
                <? if($problem['solverid'] != 0) { ?>                &nbsp;|&nbsp;已解决
                <? } elseif($problem['status'] == PB_STATUS_CLOSED) { ?>                &nbsp;|&nbsp;已关闭
                <? } else { ?>                &nbsp;|&nbsp;正在进行
                <? } ?>            </span>
          </div>
          
<? } } ?>
          <div style="float:right"><?=$departstr?></div>
          <div class="clearfix"></div>
        </div>
        <div class="tab-pane" id="no_content"></div>
      </div>
    </div>
  </div>
</div>
<? include template('footer'); ?>
