<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<style type="text/css">
    /* .photo {position: absolute; top: 20px; left: 20px; } */
    /* .mypanel {margin: 0 10px 0 120px !important; padding: 10px; line-height: 26px;} */
    .action {position: absolute; right: 50px; bottom: 5px;} /* panel右下角的动作 */
</style>

<div class="container" style="margin-top:5px;">

  <? if($userid == $this->user['uid']) { ?>  <div class="col-md-3"> 
<? include template('leftmenu'); ?>
 </div>
  <? } ?>  <script type="text/javascript">$("#menu_space").addClass("active");</script>
  <? if($userid == $this->user['uid']) { ?>  <div class="col-md-9">
  <? } else { ?>  <div>
  <? } ?>  <h5>
    <? if($userid != $this->user['uid']) { ?>    <div class="panel" style="position:relative;font-size:20px;">
      <img class="img-circle avatar" src="<?=$member['avatar']?>" width="70px" height="70px">
      <span style="color:blue;"><?=$member['username']?></span>的最近动态
    </div>
    <? } else { ?>最近动态<? } ?>  </h5>
    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="prob">
        <? if(count($problemlist) == 0) { ?>        <p style="color:grey;">暂无动态~</p>
        <? } else { ?>        
<? if(is_array($problemlist)) { foreach($problemlist as $problem) { ?>
        <div class="panel <? if($problem['data_type'] == 'prob') { ?>panel-info<? } else { ?>panel-success<? } ?>" style="margin-bottom:15px !important;">
          <? if($problem['data_type'] == 'prob') { ?>          <div class="panel-heading">发出求助</div>
          <? } else { ?>          <div class="panel-heading">解决求助</div>
          <? } ?>          <div class="panel-body" style="font-size:13px;">
            <span><?=$problem['title']?></span>
            <span>
              <a href='<?=SITE_URL?>?p-<?=$problem['pid']?>.html' target="_blank">查看</a>
            </span>
            <br/><span class="pull-right"><?=$problem['format_time']?></span>
          </div>
        </div>
        
<? } } ?>
        <? } ?>      </div><!-- existing problems tab end -->
    </div>
  </div>
</div><!-- container end -->
<? include template('footer'); ?>
