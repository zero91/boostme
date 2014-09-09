<? !defined('IN_SITE') && exit('Access Denied'); include template(header,'admin'); ?>
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
        <? if($type =="feedback/default") { ?>        <li class="active">
            <a href="javascript:void(0)" role="tab" data-toggle="tab">全部反馈</a>
        </li>
        <? } else { ?>        <li>
            <a href="#no_content" id="jump1" role="tab" data-toggle="tab">全部反馈</a>
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
            
<? if(is_array($fb_list)) { foreach($fb_list as $fb) { ?>
            <div id="<?=$fb['fid']?>" class="list-group-item" style="min-height:100px;">
                <div class="pull-right">
                    <a type="button" name="delete_<?=$fb['fid']?>" class="glyphicon glyphicon-trash" id="<?=$fb['fid']?>" style="color:grey"></a>
                </div>
                <div class="photo" style="max-width:60px">
                    <img class="img-circle" width="60px" height="60px" src=<?=$fb['avatar']?>>
                    <? if($fb['uid'] == 0) { ?>                    <span class="align_center">[匿名]</span>
                    <? } else { ?>                    <a href="<?=SITE_URL?>?u-<?=$fb['uid']?>.html" class="trigger_btn align_center"><?=$fb['username']?></a>
                    <? } ?>                </div>
                <div class="panel panel-default mypanel">
                    <? if($type == "feedback/default") { ?>                    <p style="font-size:14px">
                        <a href="<?=SITE_URL?>?<?=$fb['page']?>"><?=$fb['page']?></a>
                    </p>
                    <p> <?=$fb['content']?> </p>

                    <!--
                    fid
                    uid
                    status
                    -->

                    <? } else { ?>                    <p><?=$fb['time']?></p>
                    <p><?=$user['regtime']?></p>
                    <? } ?>                    <span class="arrow"></span>
                </div>
                <div style="position:absolute;bottom:5px;right:10px;">
                    <span style="color:grey"><?=$fb['format_time']?></span>
                    &nbsp;|&nbsp;
                    <span style="color:grey"><?=$fb['ip']?></span>
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
    $(".glyphicon").click(function() {
        fid = $(this).attr("id");
        grandparent = $(this).parent().parent();
        $.ajax({
            type: "GET",
            url:"<?=SITE_URL?>index.php?admin_feedback/remove/" + fid,
            cache: false,
            success: function(succeed) {
                if (succeed == '1') {
                    grandparent.hide();
                } else if (succeed == '-1') {
                    alert("删除失败，请重试！");
                }
            }
        });
    });
});

</script>
<? include template(footer,'admin'); ?>
