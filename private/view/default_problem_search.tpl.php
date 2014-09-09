<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "jump1") {
            location.href = "<?=SITE_URL?>?problem/search/<?=$word?>.html";
          } else if (e.target.id == "jump2") {
            <? $href_url = "problem/search/$word/" . PB_STATUS_SOLVED ?>            location.href = "<?=SITE_URL?>?<?=$href_url?>.html";
          }
        })
    })
</script>
<style type="text/css">
    .list-group-item {border:0px;}
    .panel{margin: 5px 0px;}
    .mypanel {margin: 0 5px 0 55px !important; padding: 10px; line-height: 26px;}
    .avatar{position: absolute; top: 20px; left: 10px;}
</style>
<div class="container" style="font-size:13px;">
    <div <? if(!$rownum) { ?>style="visibility:hidden;"<? } ?>>
      <h5 style="color:grey;"><?=$setting['site_name']?>为您找到相关结果约<?=$rownum?>个</h5>
    </div>

    <div class="col-md-9">
      <? if($corrected_words) { ?>      <h5 style="color:grey">您要找的是不是:
        
<? if(is_array($corrected_words)) { foreach($corrected_words as $correct_word) { ?>
        <a class="btn btn-default btn-sm" href="<?=SITE_URL?>?problem/search/<?=$correct_word?>.html"><?=$correct_word?></a>
        
<? } } ?>
      </h5>
      <? } ?>      <ul class="nav nav-tabs" role="tablist">
        <? if($status == "all") { ?>        <li class="active"><a href="javascript:void(0)" role="tab" data-toggle="tab">全部问题</a></li>
        <? } else { ?>        <li><a href="#no_content" id="jump1" role="tab" data-toggle="tab">全部问题</a></li>
        <? } ?>        <? if($status == PB_STATUS_SOLVED) { ?>        <li class="active"><a href="javascript:void(0)" role="tab" data-toggle="tab">已解决</a></li>
        <? } else { ?>        <li><a href="#no_content" id="jump2" role="tab" data-toggle="tab">已解决</a></li>
        <? } ?>      </ul>

      <div class="tab-content">
        <div class="tab-pane fade in active">
          <? if($problemlist) { ?>          
<? if(is_array($problemlist)) { foreach($problemlist as $problem) { ?>
          <div class="list-group-item">
            <div class="avatar" style="max-width:40px">
              <img class="img-circle" width="40px" height="40px" src=<? echo get_avatar_dir($problem['authorid']); ?>>
              <a href="<?=SITE_URL?>?u-<?=$problem['authorid']?>.html" class="trigger_btn align_center" ><?=$problem['author']?></a>
            </div>
            <div class="panel panel-default mypanel">
              <p>
                <?=$problem['title']?>&nbsp;&nbsp;
                <? if(in_array($problem['status'],array(PB_STATUS_SOLVED,PB_STATUS_CLOSED))) { ?>                <img title="已解决" src="<?=SITE_URL?>css/default/solved.gif" />
                <? } ?>              </p>
              <a href="<?=SITE_URL?>?p-<?=$problem['pid']?>.html" target="_blank">查看详情</a>
            </div>
          </div>
          
<? } } ?>
          <? } else { ?>          <div class="jumbotron" style="background-color:#ffffff;">
            <h4>抱歉，未找到和 "<code><?=$word?></code>" 相关的内容。</h4>
            <p>
              <strong>建议您：</strong>
              <ul>
                <li><span>检查输入是否正确</span></li>
                <li><span>简化查询词或尝试其他相关词</span></li>
              </ul>
            </p>
          </div>
          <? } ?>          <? if($departstr) { ?>          <div class="pages"><?=$departstr?></div>
          <? } ?>          <? if($setting['xunsearch_open']) { ?>          <div class="panel panel-info">
            <div class="panel-heading">
              相关搜索 ：
              
<? if(is_array($related_words)) { foreach($related_words as $index => $word) { ?>
              <? if($index<=3) { ?>              <a href="<?=SITE_URL?>?problem/search/<?=$word?>.html" class="btn btn-default btn-sm"><?=$word?></a>
              <? } ?>              
<? } } ?>
            </div>
            <div class="panel-body">
              <a href="<?=SITE_URL?>?problem/search/<?=$related_words['4']?>.html" class="btn btn-default btn-sm"><?=$related_words['4']?></a>
              <a href="<?=SITE_URL?>?problem/search/<?=$related_words['5']?>.html" class="btn btn-default btn-sm"><?=$related_words['5']?></a>
              <a href="<?=SITE_URL?>?problem/search/<?=$related_words['6']?>.html" class="btn btn-default btn-sm"><?=$related_words['6']?></a>
              <a href="<?=SITE_URL?>?problem/search/<?=$related_words['7']?>.html" class="btn btn-default btn-sm"><?=$related_words['7']?></a>
            </div>
          </div>
          <? } ?>        </div>
      </div>
      <div class="tab-pane" id="no_content"></div>
    </div>

    <div class="col-md-3">
      <h5>热门搜索</h5>
      <? if($hot_words) { ?>      <ul class="list-group">
        
<? if(is_array($hot_words)) { foreach($hot_words as $hostword) { ?>
        <li class="list-group-item"><a href="<?=SITE_URL?>?problem/search/<?=$hostword?>.html"><?=$hostword?></a></li>
        
<? } } ?>
      </ul>

      <? } else { ?>      <? $wordslist = unserialize($this->setting['hot_words']); ?>      <ul class="list-group">
        
<? if(is_array($wordslist)) { foreach($wordslist as $hotword) { ?>
        <li class="list-group-item">
          <a <? if($hotword['qid']) { ?>href="<?=SITE_URL?>?p-<?=$hotword['qid']?>.html" <? } else { ?>href="<?=SITE_URL?>?problem/search/<?=$hotword['w']?>.html"<? } ?>><?=$hotword['w']?></a>
        </li>
        
<? } } ?>
      </ul>
      <? } ?>    </div>
</div>
<? include template('footer'); ?>
