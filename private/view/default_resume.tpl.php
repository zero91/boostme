<? !defined('IN_SITE') && exit('Access Denied'); include template('header'); ?>
<link rel="stylesheet" href="<?=SITE_URL?>public/js/lightbox/lightbox.css"/>
<script src="<?=SITE_URL?>public/js/lightbox/lightbox.js" type="text/javascript"></script>
<script type="text/javascript">
  $(function(){
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      if (e.target.id == "profile") {
        location.href = "<?=SITE_URL?>?user/profile.html";
      } else if (e.target.id == "password") {
        location.href = "<?=SITE_URL?>?user/uppass.html";
      } else if (e.target.id == "modavatar") {
        location.href = "<?=SITE_URL?>?user/editimg.html";
      }
    });
    $("#menu_resume").addClass("active");
  })
</script>

<div class="container" style="margin-top:5px;">
  <div class="col-md-3">
<? include template('leftmenu'); ?>
</div>
  <div class="col-md-9" style="font-size:13px;">
    <h5>个人信息</h5>
    <ul class="nav nav-tabs" role="tablist" id="myTab">
      <li><a href="#profile" id="profile" role="tab" data-toggle="tab">基本信息</a></li>
      <li><a href="#password" id="password" role="tab" data-toggle="tab">修改密码</a></li>
      <li><a href="#modavatar" id="modavatar" role="tab" data-toggle="tab">修改头像</a></li>
      <li class="active"><a href="#resume" id="resume" role="tab" data-toggle="tab">我的简历</a></li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane" id="profile"></div>
      <div class="tab-pane" id="password"></div>
      <div class="tab-pane" id="modavatar"></div>
      <div class="tab-pane active" id="resume">
        <form method="POST" name="upinfoForm" action="<?=SITE_URL?>?user/resume.html" class="form-horizontal">
          <div class="panel panel-info">
            <div class="panel-heading" style="font-size:18px;">申请资料</div>
            <div class="panel-body">
              <h4>
                <span style="color:#3c86cf;font-size:16px;">教育经历</span>&nbsp;&nbsp;&nbsp;
                <span style="color:red;font-size:13px;">请输入教育经历！ </span>
              </h4>
              <? $edu_num = count($edu_list) ?>              <? if($edu_num == 0) { ?>              <div id="school_0">
                <div class="line_split"></div>
                <div style="color:#3c86cf;font-size:14px;padding-bottom:15px;">教育经历（1）</div>
                <div class="form-group">
                  <div id="delete_edu" style="position:absolute;right:40px;">
                    <a type="button" name="del_edu_0" class="glyphicon glyphicon-trash del_edu_btn" id="del_edu_0" style="color:grey"></a>
                  </div>
                  <label class="col-sm-2 control-label">学校:</label>
                  <div class="col-sm-5">
                    <input type="text" name="school[]" id="school-0" value="" onclick="pop(0)" class="form-control"/>
                    <div id="choose-box-wrapper-0" class="choose-box-wrapper">
                      <div id="choose-box-0" class="choose-box">
                        <div id="choose-box-title-0" class="choose-box-title"><span>选择学校</span></div>
                        <div id="choose-a-province-0" class="choose-a-province"></div>
                        <div id="choose-a-school-0" class="choose-a-school"> </div>
                        <div id="choose-box-bottom-0" class="choose-box-bottom"><input type="button" onclick="hide(0)" value="关闭"/></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">学历:</label>
                  <div class="col-sm-5">
                    <select name="edu_type[]" id="edu_type-0" class="form-control">
                      <option value="" selected>--请选择--</option>
                      <option value="<?=HIGH_SCHOOL?>">高中</option>
                      <option value="<?=BACHELOR?>">本科</option>
                      <option value="<?=MASTER?>">硕士研究生</option>
                      <option value="<?=DOCTOR?>">博士研究生</option>
                      <option value="<?=POST_DOCTOR?>">博士后</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">院系:</label>
                  <div class="col-sm-5"><input type="text" name="dept[]" id="dept-0" value="" class="form-control"/></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">专业:</label>
                  <div class="col-sm-5"><input type="text" name="major[]" id="major-0" value="" class="form-control"/></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">开始时间:</label>
                  <div class="col-sm-5">
                    <input type="text" name="start_time[]" id="datepicker-0-0" readonly class="form-control datepicker" value="" style="cursor:pointer;"/>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">结束时间:</label>
                  <div class="col-sm-5">
                    <input type="text" name="end_time[]" id="datepicker-1-0" readonly class="form-control datepicker" value="" style="cursor:pointer;" />
                  </div>
                </div>
              </div>
              <? } else { ?>              <? $edu_idx_list = range(0, $edu_num - 1); ?>              
<? if(is_array($edu_idx_list)) { foreach($edu_idx_list as $idx) { ?>
              <div id="school_<?=$idx?>">
                <div class="line_split"></div>
                <div style="color:#3c86cf;font-size:14px;padding-bottom:15px;">教育经历（<? echo $idx+1 ?>）</div>
                <div class="form-group" style="margin-bottom:5px;margin-top:15px"></div>
                <div class="form-group">
                  <div id="delete_edu" style="position:absolute;right:40px;">
                    <a type="button" name="del_edu_<?=$idx?>" class="glyphicon glyphicon-trash del_edu_btn" id="del_edu_<?=$idx?>" style="color:grey"></a>
                  </div>
                  <label class="col-sm-2 control-label">学校:</label>
                  <div class="col-sm-5">
                    <input type="text" name="school[]" id="school-<?=$idx?>" value="<?=$edu_list[$idx]['school']?>" onclick="pop(<?=$idx?>)" class="form-control"/>
                    <div id="choose-box-wrapper-<?=$idx?>" class="choose-box-wrapper">
                      <div id="choose-box-<?=$idx?>" class="choose-box">
                        <div id="choose-box-title-<?=$idx?>" class="choose-box-title">
                          <span>选择学校</span>
                        </div>
                        <div id="choose-a-province-<?=$idx?>" class="choose-a-province"></div>
                        <div id="choose-a-school-<?=$idx?>" class="choose-a-school"> </div>
                        <div id="choose-box-bottom-<?=$idx?>" class="choose-box-bottom"><input type="button" onclick="hide(<?=$idx?>)" value="关闭"/></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">学历:</label>
                  <div class="col-sm-5">
                      <select name="edu_type[]" id="edu_type-<?=$idx?>" class="form-control">
                      <option value="">--请选择--</option>
                      <option value="<?=HIGH_SCHOOL?>" <? if($edu_list[$idx]['edu_type']==HIGH_SCHOOL) { ?>selected<? } ?> >高中</option>
                      <option value="<?=BACHELOR?>"    <? if($edu_list[$idx]['edu_type']==BACHELOR) { ?>selected<? } ?>    >本科</option>
                      <option value="<?=MASTER?>"      <? if($edu_list[$idx]['edu_type']==MASTER) { ?>selected<? } ?>      >硕士研究生</option>
                      <option value="<?=DOCTOR?>"      <? if($edu_list[$idx]['edu_type']==DOCTOR) { ?>selected<? } ?>      >博士研究生</option>
                      <option value="<?=POST_DOCTOR?>" <? if($edu_list[$idx]['edu_type']==POST_DOCTOR) { ?>selected<? } ?> >博士后</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">院系:</label>
                  <div class="col-sm-5">
                    <input type="text" name="dept[]" id="dept-<?=$idx?>" value="<?=$edu_list[$idx]['department']?>" class="form-control"/>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">专业:</label>
                  <div class="col-sm-5">
                    <input type="text" name="major[]" id="major-<?=$idx?>" value="<?=$edu_list[$idx]['major']?>" class="form-control"/>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">开始时间:</label>
                  <div class="col-sm-5">
                    <input type="text" name="start_time[]" id="datepicker-0-<?=$idx?>" readonly class="form-control datepicker" value="<?=$edu_list[$idx]['start_time']?>" style="cursor:pointer;"/>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">结束时间:</label>
                  <div class="col-sm-5">
                    <input type="text" name="end_time[]" id="datepicker-1-<?=$idx?>" readonly class="form-control datepicker" value="<?=$edu_list[$idx]['end_time']?>" style="cursor:pointer;" />
                  </div>
                </div>
              </div>
              
<? } } ?>
              <? } ?>              <span name="submit" id="add_edu_btn" class="btn" onclick="add_edu()"
                  style="background:#f9f9f9;border:1px dashed #e8e8e8;margin-left:105px;width: 405px;color:#3c86cf;">
                  +&nbsp;&nbsp;增加更多教育经历
              </span>
              <div class="line_split"></div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="realname">真实姓名：</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="realname" name="realname" value="<?=$resume['realname']?>"/>
                </div>
                <span id="realname_tip"></span>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="phone">手机：</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="phone" name="phone" value="<?=$user['phone']?>"/>
                </div>
                <span id="phone_tip"></span>
              </div> 
              <div class="form-group">
                <label class="col-sm-3 control-label" for="QQ">QQ：</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="qq" name="qq" value="<?=$user['qq']?>"/>
                </div>
                <span id="qq_tip"></span>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label" for="wechat">微信：</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="wechat" name="wechat" value="<?=$user['wechat']?>"/>
                </div>
                <span id="wechat_tip"></span>
              </div> 
              <div class="form-group">
                <label class="col-sm-3 control-label" for="ID">身份证：</label>
                <div class="col-sm-5">
                  <input type="text" maxlength="18" class="form-control" name="ID" id="ID" value="<?=$resume['ID']?>" />
                </div>
                <span id="ID_tip"></span>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="vcode">持身份证正面照：</label>
                <div class="col-sm-2">
                  <input id="ID_upload" name="ID_upload" type="file" class="btn btn-primary form-control">
                </div>
                <div class="col-sm-1" <? if(empty($resume['ID_path'])) { ?>style="display:none;"<? } ?> id="ID_path_div">
                  <img src="<?=SITE_URL?>?resource/request/ID/<?=$user['uid']?>" width="40px" height="40px" id="img_id_path_pic">
                </div>
                <div class="col-sm-3 btn">.bmp .png .jpg .gif</div>
                <span id="ID_path_tip"></span>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="vcode">持学生证正面照：</label>
                <div class="col-sm-2">
                  <input id="studentID_upload" name="studentID_upload" type="file" class="btn btn-primary form-control">
                </div>
                <div class="col-sm-1" <? if(empty($resume['studentID'])) { ?>style="display:none;"<? } ?> id="studentID_div">
                  <img src="<?=SITE_URL?>?resource/request/studentID/<?=$user['uid']?>" width="40px" height="40px" id="img_studentID">
                </div>
                <div class="col-sm-3 btn">.bmp .png .jpg .gif</div>
                <span id="studentID_tip"></span>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label" for="vcode">电子简历：</label>
                <div class="col-sm-2">
                  <input id="file_upload" name="file_upload" type="file" class="btn btn-primary form-control">
                </div>
                <div class="col-sm-2" <? if(empty($resume['resume_path'])) { ?>style="display:none;"<? } ?> id="resume_div">
                  <a class="btn btn-link" href="<?=SITE_URL?>?resource/request/resume/<?=$user['uid']?>" target="_blank" id="resume_link">
                    <? if(empty($resume['realname'])) { ?><?=$user['username']?><? } else { ?><?=$resume['realname']?><? } ?>简历
                  </a>
                </div>
                <div class="col-sm-2"><span class="btn">.pdf .doc .docx</span></div>
                <span id="resume_tip"></span>
              </div>
              <h5>个人主要经历(500字以内):</h5>
              <div class="form-group">
                <div class="col-sm-10">
                  <textarea name="experience" id="experience" class="form-control" maxlength="500"
                    style="height:200px;overflow-y:hidden;padding-bottom:10px;margin-bottom:10px;" onpropertychange="adjust(this);" oninput="adjust(this);"><?=$resume['experience']?></textarea>
                </div>
                <span style="color:grey">提示：这部分信息会显示给你想要帮助的求助者，建议写上自己擅长的地方，曾经出色的经历</span>
              </div>
              <div class="form-group">
                <input type="hidden" name="operation" id="operation" value="<?=RESUME_SAVE?>"/>
                <div class="col-sm-3"></div>
                <div class="col-sm-3">
                  <button type="submit" name="submit" id="save" class="form-control btn btn-primary" onclick="check_data(this)">保&nbsp;存</button>
                </div>
                <div class="col-sm-3">
                  <button type="submit" name="submit" id="request" class="form-control btn btn-primary" onclick="check_data(this)">申请抢单资格</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<link href="<?=SITE_URL?>public/js/uploadify/uploadify.css" rel="stylesheet" type="text/css" />
<script src="<?=SITE_URL?>public/js/school.js" type="text/javascript"></script>
<script src="<?=SITE_URL?>public/js/uploadify/jquery.uploadify.js?ver=<?php echo rand(0,9999);?>" type="text/javascript"></script>
<script type="text/javascript">
var ta = document.getElementById('experience');
ta.style.height=ta.scrollHeight+'px';

g_edu_idx = Math.max(1, <? echo count($edu_list) ?>);

var ID_ok = <? echo count($resume['ID_path']) ?>;
var studentID_ok = <? echo count($resume['studentID']) ?>;
var resume_ok = <? echo count($resume['resume_path']) ?>;

$(document).ready(function() {
    $("#ID_path_div img, #studentID_div img").each(function(i) {
        var img = $(this);
        img.wrap("<a href='" + img.attr("src") + "' title='" + img.attr("title") + "' data-lightbox='comment'></a>");
        return;
    }); 

     $("body").on('click', '.glyphicon', function() {
         $(this).parent().parent().parent().remove();
     });

    $(".datepicker").datepicker({
        changeYear: true,
        changeMonth: true,
        yearRange: '-60:+5',
        dateFormat: 'yy-mm-dd'
    });

    $('#file_upload').uploadify({
        'swf': '<?=SITE_URL?>public/js/uploadify/uploadify.swf',
        'uploader': "<?=SITE_URL?>?user/upload_resume/<?=$user['uid']?>.html",
        'auto': true,
        'buttonText' : "上传简历",
        'buttonClass' : "btn btn-primary",
        'fileObjName': 'userresume',
        'height': '40px',
        'multi': false,
        'fileSizeLimit': "5MB",
        'fileTypeExts': '*.pdf;*.doc;*.docx',
        'fileTypeDesc': 'User resume(.pdf, .doc, .docx)',
        'onUploadSuccess': function(file, data, response) {
            alert('简历上传成功!'); 
            resume_ok = true;
            $("#resume_link").attr("href", data+"?"+Math.random());
            $("#resume_div").show();
        }
    });

    $('#ID_upload').uploadify({
        'swf': '<?=SITE_URL?>public/js/uploadify/uploadify.swf',
        'uploader': "<?=SITE_URL?>?user/upload_ID/<?=$user['uid']?>.html",
        'auto': true,
        'buttonText' : "上传证件照",
        'buttonClass' : "btn btn-primary",
        'fileObjName': 'userID',
        'height': '40px',
        'multi': false,
        'fileSizeLimit': "7MB",
        'fileTypeExts': '*.bmp;*.png;*.jpg;*.gif',
        'fileTypeDesc': 'User (.bmp, .png, .jpg, .gif)',
        'onUploadSuccess': function(file, data, response) {
            alert('证件照上传成功!');
            ID_ok = true;
            $("#img_id_path_pic").attr("src", "<?=SITE_URL?>?resource/request/ID/<?=$user['uid']?>");
            $("#ID_path_div").show();
            $("#img_modal_id_path").attr("src", "<?=SITE_URL?>?resource/request/ID/<?=$user['uid']?>");
            $(".uploadify-queue").hide();
        }
    });

    $('#studentID_upload').uploadify({
        'swf': '<?=SITE_URL?>public/js/uploadify/uploadify.swf',
        'uploader': "<?=SITE_URL?>?user/upload_studentID/<?=$user['uid']?>.html",
        'auto': true,
        'buttonText' : "上传学生证",
        'buttonClass' : "btn btn-primary",
        'fileObjName': 'studentID',
        'height': '40px',
        'multi': false,
        'fileSizeLimit': "7MB",
        'fileTypeExts': '*.bmp;*.png;*.jpg;*.gif',
        'fileTypeDesc': 'User (.bmp, .png, .jpg, .gif)',
        'onUploadSuccess': function(file, data, response) {
            alert('学生证上传成功!');
            studentID_ok = true;
            $("#img_studentID").attr("src", data+"?"+Math.random());
            $("#studentID_div").show();
            $("#img_modal_studentID").attr("src", data+"?"+Math.random());
            $(".uploadify-queue").hide();
        }
    });

    $('#submit').click(function() {
        $('#file_upload').uploadify("upload", "*");
        $('#ID_upload').uploadify("upload", "*");
        $('#studentID_upload').uploadify("upload", "*");
    }); 
});

//弹出窗口
function pop(id) {
    //将窗口居中
    makeCenter(id);

    //初始化省份列表
    initProvince(id);

    //默认情况下, 给第一个省份添加choosen样式
    $('[province-id="1"]').addClass('choosen');

    //初始化大学列表
    initSchool(1, id);
}

//隐藏窗口
function hide(id) {
    $('#choose-box-wrapper-' + id).css("display","none");
}

function initProvince(id) {
    target_name='#choose-a-province-' + id;
    //原先的省份列表清空
    $(target_name).html('');
    for(i=0;i<schoolList.length;i++)
    {
        $(target_name).append('<a class="province-item" province-id="'+schoolList[i].id+'">'+schoolList[i].name+'</a>');
    }
    //添加省份列表项的click事件
    $('.province-item').bind('click', function(){
            var item=$(this);
            var province = item.attr('province-id');
            var choosenItem = item.parent().find('.choosen');
            if(choosenItem)
                $(choosenItem).removeClass('choosen');
            item.addClass('choosen');
            //更新大学列表
            initSchool(province, id);
        }
    );
}

function initSchool(provinceID, id) {
    target_name = '#choose-a-school-' + id;
    //原先的学校列表清空
    $(target_name).html('');
    var schools = schoolList[provinceID-1].school;
    for(i=0;i<schools.length;i++)
    {
        $(target_name).append('<a class="school-item" school-id="'+schools[i].id+'">'+schools[i].name+'</a>');
    }
    //添加大学列表项的click事件
    $('.school-item').bind('click', function(){
            var item=$(this);

            var school = item.attr('school-id');
            //更新选择大学文本框中的值

            item.parents(".col-sm-5").children(".form-control").val(item.text());
            //item.parents(".form-group").children(".fohide();
            //item.parents(".input-bar").children(".normal-input").val(item.text());
            //$('#bachelor-school').val(item.text());
            //关闭弹窗
            hide(id);
        }
    );
}

function makeCenter(id) {
    target_name = '#choose-box-wrapper-' + id;
    $(target_name).css("display","block");
    $(target_name).css("position","absolute");
    $(target_name).css("z-index","1000");
}

function check_edu() {
    for (var i = 0; i < g_edu_idx; ++i) {
        if ($("#school_" + i).length > 0) {
            if (($("#school-" + i).val().length == 0) ||
                  ($("#edu_type-" + i).val().length == 0) ||
                  ($("#dept-" + i).val().length == 0) || 
                  ($("#major-" + i).val().length == 0) ||
                  ($("#datepicker-0-" + i).val().length == 0) ||
                  ($("#datepicker-1-" + i).val().length == 0)
                  ) {
                return false;
            }
        }
    }
    return true;
}

function check_data(obj) {
    if (obj.id == "save") {
        $("form").unbind('submit');
        $("#operation").val("<?=RESUME_SAVE?>");
        $("form").submit(function() { return true;});
    } else {
        $("form").unbind('submit');
        $("#operation").val("<?=RESUME_APPLY?>");
        $("form").submit(function() {
              if (check_edu()) {
              } else {
                  alert("教育经历信息没有填写完整，请检查");
                  return false;
              }

              if ($("#realname").val().length == 0) {
                  $('#realname_tip').html("请填写您的真实姓名");
                  $('#realname_tip').attr('class', 'input_error');
                  return false;
              } else {
                  $('#realname_tip').html("&nbsp;");
                  $('#realname_tip').attr('class', 'input_ok');
              }

              if ($("#phone").val().length == 11) {
                  $("#phone_tip").html("&nbsp;");
                  $('#phone_tip').attr('class', 'input_ok');
                  $("#qq_tip").html("&nbsp;");
                  $("#qq_tip").removeClass("input_error");
                  $("#wechat_tip").html("&nbsp;");
                  $("#wechat_tip").removeClass("input_error");
              } else if ($("#qq").val().length > 0) {
                  $("#qq_tip").html("&nbsp;");
                  $("#qq_tip").attr('class', 'input_ok');
                  $("#phone_tip").html("&nbsp;");
                  $("#phone_tip").removeClass("input_error");
                  $("#wechat_tip").html("&nbsp;");
                  $("#wechat_tip").removeClass("input_error");
              } else if ($("#wechat").val().length > 0) {
                  $("#wechat_tip").html("&nbsp;");
                  $("#wechat_tip").attr('class', 'input_ok');
                  $("#phone_tip").html("&nbsp;");
                  $("#phone_tip").removeClass("input_error");
                  $("#qq_tip").html("&nbsp;");
                  $("#qq_tip").removeClass("input_error");
              } else {
                  $('#phone_tip').html("三项至少填写一项");
                  $('#qq_tip').html("三项至少填写一项");
                  $('#wechat_tip').html("三项至少填写一项");
                  $('#phone_tip').attr('class', 'input_error');
                  $('#qq_tip').attr('class', 'input_error');
                  $('#wechat_tip').attr('class', 'input_error');
                  return false;
              }

              if ($("#ID").val().length != 18) {
                  $('#ID_tip').html("请填写您的真实身份证号");
                  $('#ID_tip').attr('class', 'input_error');
                  return false;
              } else {
                  $('#ID_tip').html("&nbsp;");
                  $('#ID_tip').attr('class', 'input_ok');
              }

              if (!ID_ok) {
                  $('#ID_path_tip').html("请上传您的身份证照片");
                  $('#ID_path_tip').attr('class', 'input_error');
                  return false;
              } else {
                  $('#ID_path_tip').html("&nbsp;");
                  $('#ID_path_tip').attr('class', 'input_ok');
              }

              if (!studentID_ok) {
                  $('#studentID_tip').html("请上传您的学生证照片");
                  $('#studentID_tip').attr('class', 'input_error');
                  return false;
              } else {
                  $('#studentID_tip').html("&nbsp;");
                  $('#studentID_tip').attr('class', 'input_ok');
              }

              if (!resume_ok) {
                  $('#resume_tip').html("请上传您的电子简历");
                  $('#resume_tip').attr('class', 'input_error');
                  return false;
              } else {
                  $('#resume_tip').html("&nbsp;");
                  $('#resume_tip').attr('class', 'input_ok');
              }
              return true;
          });
    }
}

function add_edu() {
    if (g_edu_idx >= 6) {
        alert("请认真填写你的教育经历！");
        return false;
    }
    var more_edu = $('<div id="school_' + g_edu_idx + '"><div class="line_split"></div><div style="color:#3c86cf;font-size:14px;padding-bottom:15px;">教育经历（' + (g_edu_idx+1) + '）</div><div class="form-group" style="margin-bottom:5px;margin-top:15px"></div> <div class="form-group"><div id="delete_edu" style="position:absolute;right:40px;"><a type="button" name="del_edu_' + g_edu_idx + '" class="glyphicon glyphicon-trash del_edu_btn" id="del_edu_' + g_edu_idx + '" style="color:grey"></a></div><label class="col-sm-2 control-label">学校:</label> <div class="col-sm-5"><input type="text" name="school[]" id="school-' + g_edu_idx + '" value="" onclick="pop(' + g_edu_idx + ')" class="form-control"/> <div id="choose-box-wrapper-' + g_edu_idx + '" class="choose-box-wrapper"> <div id="choose-box-' + g_edu_idx + '" class="choose-box"> <div id="choose-box-title-' + g_edu_idx + '" class="choose-box-title"> <span>选择学校</span> </div> <div id="choose-a-province-' + g_edu_idx + '" class="choose-a-province"></div><div id="choose-a-school-' + g_edu_idx + '" class="choose-a-school"> </div> <div id="choose-box-bottom-' + g_edu_idx + '" class="choose-box-bottom"> <input type="button" onclick="hide(' + g_edu_idx + ')" value="关闭"/> </div> </div> </div> </div> </div> <div class="form-group"> <label class="col-sm-2 control-label">学历:</label> <div class="col-sm-5"> <select name="edu_type[]" id="edu_type-' + g_edu_idx + '" class="form-control"><option value="">--请选择--</option><option value="<?=HIGH_SCHOOL?>">高中</option> <option value="<?=BACHELOR?>">本科</option> <option value="<?=MASTER?>">硕士研究生</option> <option value="<?=DOCTOR?>">博士研究生</option> <option value="<?=POST_DOCTOR?>">博士后</option> </select> </div> </div> <div class="form-group"> <label class="col-sm-2 control-label">院系:</label> <div class="col-sm-5"> <input type="text" name="dept[]" id="dept-' + g_edu_idx + '" value="" class="form-control"/> </div> </div> <div class="form-group"> <label class="col-sm-2 control-label">专业:</label> <div class="col-sm-5"> <input type="text" name="major[]" id="major-' + g_edu_idx + '" value="" class="form-control"/> </div> </div> <div class="form-group"> <label class="col-sm-2 control-label">开始时间:</label> <div class="col-sm-5"> <input type="text" name="start_time[]" id="datepicker-0-' + g_edu_idx + '" readonly class="form-control datepicker" style="cursor:pointer;"/> </div> </div> <div class="form-group"> <label class="col-sm-2 control-label">结束时间:</label> <div class="col-sm-5"> <input type="text" name="end_time[]" id="datepicker-1-' + g_edu_idx + '" readonly class="form-control datepicker" style="cursor:pointer;"/> </div> </div></div>');

    more_edu.hide();
    $("#add_edu_btn").before(more_edu);
    g_edu_idx = g_edu_idx + 1;
    more_edu.fadeIn("slow");

    $(".datepicker").datepicker({
        changeYear: true,
        changeMonth: true,
        yearRange: '-60:+5',
        dateFormat: 'yy-mm-dd'
    });
    return false;
}

</script>
<? include template('footer'); ?>
