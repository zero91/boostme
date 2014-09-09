<? !defined('IN_SITE') && exit('Access Denied'); ?>

<div style="height:32px;"></div>
</div> <!-- end fix the foot -->


<style type="text/css">
    .align_center {text-align: center;}
</style>

<div class="container align_center" style="font-size:12px;margin-bottom:11px;">
    <a href="<?=SITE_URL?>" target="_blank">&nbsp;<?=$setting['site_name']?>&nbsp;</a>
    <span class="span-line">&nbsp;|&nbsp;</span> <a href="mailto:boostme@qq.com" target="_blank">&nbsp;联系我们&nbsp;</a>
    <!-- <span class="span-line">&nbsp;|&nbsp;</span> <a href="<?=SITE_URL?>?index/help.html" target="_blank">&nbsp;使用帮助&nbsp;</a> -->
    <span class="span-line">&nbsp;|&nbsp;</span><a href="http://www.miibeian.gov.cn" target="_blank">&nbsp;京ICP备14035508号&nbsp;</a>
</div>


<a class="btn btn-link" data-toggle="modal" id="feedback" data-target="#feedbackModal" style="position:fixed;left:5px;top:95%;font-size:12px;">
    网站建议<span class="glyphicon glyphicon-question-sign"></span>
</a>
<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
          <span class="sr-only">Close</span>
        </button>
        <h4 class="modal-title">Boostme使用反馈</h4>
      </div>
      <form name="feedback_form" action="<?=SITE_URL?>?feedback/add.html" method="post" id="feedback_dialog">
        <div class="modal-body" id="feedback_body">
          <input type="hidden" value="<?=$regular?>" name="fb_regular" id="fb_regular"/>
          <p>您的每一份反馈都是我们继续改善<?=$setting['site_name']?>的动力，让我们一起为打造一个更好的产品而努力！</p>
          <textarea class="form-control" name="fb_content" id="fb_content" rows="5" placeholder="您认为<?=$setting['site_name']?>现有功能有哪些可以改善的地方，您还希望加入哪些功能" required></textarea>
        </div>
        <div class="modal-footer">
          <!-- <input type="submit" class="btn btn-primary" data-dismiss="modal" id="submit_fb" value="提&nbsp;交" /> -->
          <input type="submit" class="btn btn-primary" id="submit_fb" value="提&nbsp;交" />
        </div>
      </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<a id="scrollUp" href="#top" title="" style="position: fixed; z-index: 2147483647; display: block;"></a>
<div class="usercard" id="usercard" style="display:none"></div>

<script src="<?=SITE_URL?>public/js/jquery-ui/jquery-ui.js" type="text/javascript"></script>
<script src="<?=SITE_URL?>public/js/jquery.scrollup.js" type="text/javascript"></script>
<script src="<?=SITE_URL?>public/js/common.js" type="text/javascript"></script>
<script type="text/javascript">
    $.scrollUp({
        scrollName: 'scrollUp', // Element ID
        topDistance: '260', // Distance from top before showing element (px)
        topSpeed: 300, // Speed back to top (ms)
        animation: 'fade', // Fade, slide, none
        animationInSpeed: 200, // Animation in speed (ms)
        animationOutSpeed: 200, // Animation out speed (ms)
        scrollText: '', // Text for element
        activeOverlay: false  // Set CSS color to display scrollUp active point, e.g '#00FFFF'
    });
</script>

</body>
</html> 
