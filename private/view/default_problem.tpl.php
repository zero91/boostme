<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<script src="<?=SITE_URL?>public/js/editor/ueditor.config.js" type="text/javascript"></script> 
<script src="<?=SITE_URL?>public/js/editor/ueditor.all.js" type="text/javascript"></script>
<style type="text/css">
  .panel {position: relative; padding: 15px;}
  .tips { color: grey; float: right; margin: 0px; }
  #ptitle {margin: 10px 0;}
</style>

<div class="container">
  <? if($op_type == "add") { ?>  <h4>发布求助</h4>
  <? } else { ?>  <h4>编辑求助</h4>
  <? } ?>  <form enctype="multipart/form-data" method="POST" 
    <? if($op_type=="add") { ?>action="<?=SITE_URL?>?problem/add.html" <? } else { ?>action="<?=SITE_URL?>?problem/edit/<?=$problem['pid']?>.html"<? } ?>      name="askform" id="askform" onsubmit="return check_form();" class="form-horizontal">
    <div class="panel panel-info">
      <h4 style="display:inline;">请详细描述您的求助信息：</h4>
      <h4 id="limitNum" class="tips">还可输入 <mark><span>140</span></mark> 字</h4>
      <? if($op_type == "add") { ?>      <textarea class="form-control" name="title" id="ptitle" rows="5"><?=$word?></textarea>
      <? } else { ?>      <textarea class="form-control" name="title" id="ptitle" rows="5"><?=$problem['title']?></textarea>
      <? } ?>      <h4>求助补充:</h4>
      <div id="introContent"> 
        <script type="text/plain" id="editor" name="description" style="height: 220px;"></script>
        <? if($op_type == "add") { ?>        <script type="text/javascript">UE.getEditor('editor', UE.utils.extend({toolbars:[[<?=$toolbars?>]]}));</script>
        <? } else { ?>        <script type="text/javascript">UE.getEditor('editor', UE.utils.extend({toolbars:[[<?=$toolbars?>]], initialContent:"<? echo taddslashes($problem['description']) ?>"}));</script>
        <? } ?>      </div>
      <div class="row" style="margin-top:10px">
        <div class="form-group">
          <label class="col-sm-1 control-label">标签：</label>
          <div class="col-sm-5">
            <? if($op_type == "add") { ?>            <input type="text" class="form-control" placeholder="多个标签请以空格隔开" name="ptags"/>
            <? } else { ?>            <input type="text" class="form-control" placeholder="多个标签请以空格隔开" name="ptags" value="<? echo implode(' ',$taglist) ?>"/>
            <? } ?>          </div>
        </div>
      </div>
      <div class="row">
        <div class="form-group">
          <label class="col-sm-1 control-label">回报：</label>
          <div class="col-sm-2">
            <? if($op_type == "add") { ?>            <input type="number" class="form-control" id="price" name="price" step="1" value="100" min="0" max="10000"/>
            <? } else { ?>            <input type="number" class="form-control" id="price" name="price" step="1" value="<?=$problem['price']?>" min="0" max="10000"/>
            <? } ?>          </div>
          <div class="col-sm-1 btn">元/小时</div>
        </div>
      </div>

      <? if($setting['code_ask']) { ?>      <div class="row">
        <div class="form-group">
            <label for="login_code" class="col-sm-1 control-label">验证码：</label>
            <div class="col-sm-3">
              <input type="text" class="form-control" id="code" name="code" onblur="check_code()">
            </div>
            <div class="col-sm-3">
              <span class="verifycode"><img src="<?=SITE_URL?>?user/code.html" onclick="javascript:updatecode();" id="verifycode"></span>
              <a href="javascript:updatecode();" class="changecode">&nbsp;换一个</a>
            </div>
        </div>
      </div>
      <? } ?>      <? if($op_type == "add") { ?>      <button type="submit" class="btn btn-primary" name="submit" style="float:right">发布求助</button>
      <? } else { ?>      <button type="submit" class="btn btn-primary" name="submit" style="float:right">保存修改</button>
      <? } ?>      <div class="clearfix"></div>
    </div>
  </form>
</div>

<script type="text/javascript">
$(document).ready(function() {
  $("#ptitle").keyup(function() {
    var pbyte = bytes($.trim($(this).val()));
    var limit = 280 - pbyte;
    if (limit % 2 == 0) {
      $("#limitNum span").html((limit / 2));
    } else {
      $("#limitNum span").html(((limit + 1) / 2));
    }
  });
});

function check_form() {
  var ptitle = $("#ptitle").val();
  if (bytes($.trim(ptitle)) < 20 || bytes($.trim(ptitle)) > 280) {
    alert("问题标题长度不得少于10个字，不能超过140字！");
    $("#ptitle").focus();
    return false;
  }
}
</script>
<? include template('footer'); ?>
