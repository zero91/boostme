<? !defined('IN_SITE') && exit('Access Denied'); ?>

<div style="height:32px;"></div>
</div> <!-- end fix the foot -->


<style type="text/css">
    .align_center {text-align: center;}
</style>

<div class="container align_center" style="font-size:12px;margin-bottom:11px;">
    <a href="<?=SITE_URL?>" target="_blank">&nbsp;<?=$setting['site_name']?>&nbsp;</a>
    <span class="span-line">&nbsp;|&nbsp;</span> <a href="mailto:boostme@qq.com" target="_blank">&nbsp;联系我们&nbsp;</a>
    <span class="span-line">&nbsp;|&nbsp;</span> <a href="<?=SITE_URL?>?index/help.html" target="_blank">&nbsp;使用帮助&nbsp;</a>
    <span class="span-line">&nbsp;|&nbsp;</span><a href="http://www.miibeian.gov.cn" target="_blank">&nbsp;京ICP备14035508号&nbsp;</a>
</div>


<a id="scrollUp" href="#top" title="" style="position: fixed; z-index: 2147483647; display: block;"></a>
<div class="usercard" id="usercard" style="display:none"></div>

<script src="<?=SITE_URL?>js/jquery-ui/jquery-ui.js" type="text/javascript"></script>
<script src="<?=SITE_URL?>js/jquery.scrollup.js" type="text/javascript"></script>
<script src="<?=SITE_URL?>js/common.js" type="text/javascript"></script>
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
