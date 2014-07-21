var popusertimer = null;
var query = '?';
$(document).ready(function() {
    /* 头部header浮动层 */
    $(".bm_ismore a").hover(function() {
        $(".bm_user_more_list").hide();
        $(this).parent().next("div").show();
        $(this).parent().next("div").hover(function() {
        }, function() {
            $(this).hide();
        });
    });

    //搜索框底层border
    $(window).bind("scroll", function() {
        var e = document.documentElement.scrollTop || document.body.scrollTop;
        e > 20 ? $(".js-fixed").addClass("fixed-theme") : $(".js-fixed").removeClass("fixed-theme");
    });

    //搜索提问按钮
    $("#search_btn").click(function() {
        document.searchform.action = g_site_url + '' + query + 'problem/search/2';
        document.searchform.submit();
    });
    $("#ask_btn").click(function() {
        document.searchform.action = g_site_url + '' + query + 'problem/add';
        document.searchform.submit();
    });

    //usercard关闭
    $("#usercard").hover(function() {
        if (popusertimer) {
            clearTimeout(popusertimer);
        }
        $("#usercard").show();
    }, function() {
        if (popusertimer) {
            clearTimeout(popusertimer);
        }
        popusertimer = setTimeout(function() {
            $("#usercard").hide();
        }, 300);
    });

});

function bytes(str)
{
    var len = 0;
    for (var i = 0; i < str.length; i++) {
        if (str.charCodeAt(i) > 127) {
            len++;
        }
        len++;
    }
    return len;
}

//问题分类选择函数
function initcategory(category1) {
    var selectedcid1 = $("#selectcid1").val();
    $("#category1").html('');
    for (var i = 0; i < category1.length; i++) {
        var selected = '';
        if (selectedcid1 === category1[i][0]) {
            selected = ' selected';
        }
        $("#category1").append("<option value='" + category1[i][0] + "' " + selected + ">" + category1[i][1] + "</option>");
    }
    $("#catedialog").dialog({
        autoOpen: false,
        width: 480,
        modal: true,
        resizable: false
    });
}
function fillcategory(category2, value1, cateid) {
    var optionhtml = '<option value="0">不选择</option>';
    var selectedcid = 0;
    if (cateid === "category2") {
        selectedcid = $("#selectcid2").val();
    } else if (cateid === "category3") {
        selectedcid = $("#selectcid3").val();
    }
    $("#" + cateid).html("");
    for (var i = 0; i < category2.length; i++) {
        if (value1 === category2[i][0]) {
            var selected = '';
            if (selectedcid === category2[i][1]) {
                selected = ' selected';
                $("#" + cateid).show();
            }
            optionhtml += "<option value='" + category2[i][1] + "' " + selected + ">" + category2[i][2] + "</option>";
        }
    }
    $("#" + cateid).html(optionhtml);
}

//验证码
function updatecode()
{
    var img = g_site_url + "index.php" + query + "user/code/" + Math.random();
    $('#verifycode').attr("src", img);
}

//验证码检测
function check_code()
{
    var code = $.trim($('#code').val());
    if ($.trim(code) == '') {
        $('#codetip').html("验证码错误");
        $('#codetip').attr('class', 'input_error');
        return false;
    }
    $.ajax({
        type: "GET",
        async: false,
        cache: false,
        url: g_site_url + "index.php" + query + "user/ajaxcode/"+code,
        success: function(flag) {
            if (1 == flag) {
                $('#codetip').html("&nbsp;");
                $('#codetip').attr('class', 'input_ok');
                return true;
            } else {
                $('#codetip').html("验证码错误");
                $('#codetip').attr('class', 'input_error');
                return false;
            }

        }
    });
}

//usercard弹出层
function pop_user_on(popobj, uid, type) {
    $("#usercard").load(g_site_url + "index.php" + query + "user/ajaxuserinfo/" + uid, {}, function() {
        var myalign = "left-27 bottom-30";
        if (type == 'text') {
            myalign = "left-21 bottom-10";
        } else if (type == 'image_active') {
            myalign = "left-40 bottom-43";
        }
        if (popusertimer) {
            clearTimeout(popusertimer);
        }
        popusertimer = setTimeout(function() {
            $("#usercard").show();
            $("#usercard").position({
                my: myalign,
                of: popobj
            });
        }, 300);

    });
}
function pop_user_out() {
    if (popusertimer) {
        clearTimeout(popusertimer);
    }
    popusertimer = setTimeout(function() {
        $("#usercard").hide();
    }, 600);
}

/*用户登陆*/
function login()
{
    $("#poplogin").remove();
    $("body").append('<div id="poplogin" title="欢迎登陆'+g_site_name+'"></div>');
    $("#poplogin").load(g_site_url + "index.php?user/ajaxpoplogin");
    $("#poplogin").dialog({
        width: 520,
        modal: true,
        resizable: false,
        position:{my:"bottom-60"}
    });
}

/*删除回答*/
function delete_answer(aid, qid) {
    if (confirm('确定删除问题？该操作不可返回！') === true) {
        document.location.href = g_site_url + '' + query + 'question/deleteanswer/' + aid + '/' + qid;
    }
}

function checkall(checkname) {
    var chkall = $("#chkall:checked").val();
    if (chkall && (chkall === 'chkall')) {
        $("input[name^='" + checkname + "']").each(function() {
            $(this).prop("checked", "checked");
        });
    } else {
        $("input[name^='" + checkname + "']").each(function() {
            $(this).removeProp("checked");
        });
    }
}
