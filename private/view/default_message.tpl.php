<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
 <style type="text/css">
    .avatar {position: absolute; top: 20px; left: 10px;}
    .msg_content {margin: -15px 10px 0 65px !important; line-height: 36px;}
</style>

<script type="text/javascript">
    $(function(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
          if (e.target.id == "jump1") {
            location.href = "<?=SITE_URL?>?message/personal.html";
          } else if (e.target.id == "jump2") {
            location.href = "<?=SITE_URL?>?message/system.html";
          }
        })
    })
</script>

<div class="container" style="margin-top:5px;">
  <div class="col-md-3">
<? include template('leftmenu'); ?>
</div>
  <div class="col-md-9">
    <h5 style="float:left">收件箱</h5>
    <input type="button" value="写消息" class="btn btn-primary btn-sm"
        style="float:right;width:150px"
        onclick="javascript:document.location='<?=SITE_URL?>?message/send.html'"/>
    <div class="clearfix"></div> 

    <ul class="nav nav-tabs" role="tablist" style="font-size:13px;">
        <? if($type =="personal") { ?>        <script type="text/javascript">
            $("#menu_personal").addClass("active");
        </script>
        <li class="active">
            <a href="javascript:void(0)" role="tab" data-toggle="tab">私人消息</a>
        </li>
        <? } else { ?>        <li>
            <a href="#no_content" id="jump1" role="tab" data-toggle="tab">私人消息</a>
        </li>
        <? } ?>        <? if($type =="system") { ?>        <script type="text/javascript">
            $("#menu_system").addClass("active");
        </script>
        <li class="active">
            <a href="javascript:void(0)" role="tab" data-toggle="tab">系统消息</a>
        </li>
        <? } else { ?>        <li>
            <a href="#no_content" id="jump2" role="tab" data-toggle="tab">系统消息</a>
        </li>
        <? } ?>    </ul>

    <div class="tab-content">
        <div class="tab-pane active" style="padding-top:5px">
            
<? if(is_array($messagelist)) { foreach($messagelist as $message) { ?>
            <div id="msg<?=$message['mid']?>" class="list-group-item" <? if($message['new']) { ?>style="background-color:#D1EEEE;"<? } ?>>
                <div class="pull-right">
                    <? if($message['fromuid']) { ?>                    <a type="button" name="delete_<?=$message['mid']?>" class="del_personal_msg_btn glyphicon glyphicon-trash" id="<?=$message['fromuid']?>" style="color:grey;cursor:pointer;"></a>
                    <? } else { ?>                    <a type="button" name="delete_<?=$message['mid']?>" class="del_personal_msg_btn glyphicon glyphicon-trash" id="<?=$message['mid']?>" style="color:grey;cursor:pointer;"></a>
                    <? } ?>                </div>

                <? if($message['fromuid']) { ?>                <div class="avatar" style="max-width:60px">
                    <img alt="<?=$message['from']?>" class="img-circle" width="60px" height="60px" src="<?=$message['from_avatar']?>"/>
                </div>

                <div class="msg_content">
                    <a href="<?=SITE_URL?>?u-<?=$message['fromuid']?>.html" target="_blank"><?=$message['from']?></a>
                    <span style="font-weight:bold;font-size:17px;"> <?=$message['subject']?> </span><br/>
                    <span onclick="javascript:document.location = '<?=SITE_URL?>?message/view/<?=$type?>/<?=$message['fromuid']?>.html';" style="cursor:pointer;">
                        <? echo cutstr($message['content'],30,'') ?>...  
                    </span>
                </div>
                <? } else { ?>                <div style="padding-top:5px;padding-bottom:5px;">
                    <span class="glyphicon glyphicon-user"></span>
                    <span style="font-size:14px;"> <?=$message['subject']?> </span><br/>
                    <span style="font-size:13px;"> <?=$message['content']?> </span>
                </div>
                <? } ?>                <div style="position:absolute;bottom:2px;right:1px;">
                    <span style="color:grey"><?=$message['format_time']?></span>
                </div>
            </div>
            
<? } } ?>
            <div style="float:right"><?=$departstr?></div>
            <div class="clearfix"></div>
        </div>
        <div class="tab-pane" id="no_content"></div>
    </div>
  </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".del_personal_msg_btn").click(function() {
            <? if($type=='personal') { ?>            if (confirm('确定删除与该用户的私信?') === false) {
                return false;
            }
            fromuid = $(this).attr("id");
            grandparent = $(this).parent().parent();
            $.ajax({
                type: "GET",
                url:"<?=SITE_URL?>index.php" + query + "message/removedialog/" + fromuid,
                cache: false,
                success: function(succeed) {
                    if (succeed == '1') {
                        grandparent.hide();
                    } else if (succeed == '-1') {
                        alert("删除失败，请重试！");
                    }
                }
            });
            <? } else { ?>            if (confirm('确定删除该条信息?') === false) {
                return false;
            }
            msg_id = $(this).attr("id");
            grandparent = $(this).parent().parent();
            $.ajax({
                type: "GET",
                url:"<?=SITE_URL?>index.php" + query + "message/remove/" + msg_id,
                cache: false,
                success: function(succeed) {
                    if (succeed == '1') {
                        grandparent.hide();
                    } else if (succeed == '-1') {
                        alert("删除失败，请重试！");
                    }
                }
            });
            <? } ?>        });
    });
</script>
<? include template('footer'); ?>
