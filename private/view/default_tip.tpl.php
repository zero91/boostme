<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<div class="container">
    <div class="jumbotron">
      <h3 style="margin-bottom:30px"><?=$message?>~~~</h3>
      <? if($redirect == 'BACK') { ?>      <a class="btn btn-primary" role="button" href="javascript:history.go(-1);">返回原处</a>&nbsp;&nbsp;
      <a class="btn btn-primary" role="button" href="<?=SITE_URL?>?user/space.html">我的主页</a>&nbsp;&nbsp;
      <a class="btn btn-primary" role="button" href="<?=SITE_URL?>">回到首页</a>
      <? } elseif($redirect!='STOP') { ?>      页面将在<span id="seconds">3</span>秒后自动跳转到下一页，你也可以直接点 <a href="<?=$redirect?>" >立即跳转</a>。
      <script type="text/javascript">
        var seconds = 3;
        window.setInterval(function() {
            seconds--;
            if (seconds == 1) {
                window.location = "<?=$redirect?>";
            }
            if (seconds < 0) seconds = 0;
            $("#seconds").html(seconds);
        }, 1000);
      </script>
      <? } ?>    </div>
</div>
<? include template('footer'); ?>
