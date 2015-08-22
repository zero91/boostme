$(function() {
    $("#add_question_btn").click(function() { add_question(); });

    image_light($("#images img"));
    //image_light($(".msg_content img"));
    //image_light($("#description img"));
    //SyntaxHighlighter.all();
    $(".bm_button_agree").hover(function() {
        var answerid = $(this).parent().attr("id");
        var supportobj = $(this);
        var answer = new Answer();
        answer.has_support({"answerid" : answerid}, function(response) {
            if (response.success) {
                supportobj.val("已赞");
            } else {
                supportobj.val("赞");
            }
        });
        $(this).css("font-weight", "normal");
    }, function() {
        var answerid = $(this).parent().attr("id");
        var supportobj = $(this);
        var answer = new Answer();
        answer.get_support({"answerid" : answerid}, function(response) {
            if (response.success) {
                supportobj.val(response.supports);
            }
        });
        $(this).css("font-weight", "bold");
    });

    $(".bm_button_agree").click(function() {
        var supportobj = $(this);
        var answerid = $(this).parent().attr("id");
        var answer = new Answer();
        answer.add_support({"answerid" : answerid}, function(response) {
            var error_dict = {
                101 : "用户尚未登录",
                102 : "已经点过赞，无需再点",
                103 : "参数无效"
            };
            if (response.success) {
                $("#support_tip").css({height:"0px", opacity:0});
                $("#support_tip").show();
                $("#support_tip").position({my:"top-40", of: supportobj});
                $("#support_tip").animate({"opacity":"1"}, 500).animate({"opacity":"0"}, 200);
                supportobj.val("已赞同");
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    $("#title").keyup(function() {
        var title = $(this).val();
        var chars_num = 0;
        for (var i = 0; i < title.length; ++i) {
            if (title.charCodeAt(i) > 255) {
                chars_num += 2;
            } else {
                ++chars_num;
            }

            if (chars_num > 80) {
                title = title.substr(0, i);
                $(this).val(title);
                $("#limit_num span").html(0);
                return false;
            }
        }
        $("#limit_num span").html(Math.floor((80 - chars_num)/2));
    });
});

function add_question() {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = "/user/login";
        return false;
    }
    var title = $.trim($("#title").val());
    var content = $.trim(UE.getEditor('content').getPlainTxt());
    if (title == '') {
        alert("请输入主题!");
        return false;
    }
    if (content == '') {
        alert("内容不能为空!");
        return false;
    }

    if (!check_code()) {
        return false;
    }

    var question = new Question();
    question.add({"title" : title, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "验证码错误",
            103 : "参数无效"
        };
        if (response.success) {
            alert("发表新帖成功");
            window.location.href = response.forward;
        } else {
            errno_alert(response.error, error_dict);
        }
    });
    return true;
}

// 加载回复的评论
function load_comment(answerid, page) {
    var answer = new Answer();
    answer.fetch_comment_list({"answerid" : answerid, "page" : page}, function(response) {
        if (response.success) {
            if (response.comment_list.length == 0 && page == 1) {
                var comments = '<div style="text-align:center;">暂无评论</div>';
                $("#comment_" + answerid + " .list-group").html(comments);
            } else {
                var comment_template = _.template($("#answer_comment_list_template").html());
                var comments = comment_template({'comment_list' : response.comment_list,
                                                 'departstr' : response.departstr});
                $("#comment_" + answerid + " .list-group").html(comments);
            }
        }
    });
}

// 显示回复的评论
function show_comment(answerid, page) {
    if ($("#comment_" + answerid).css("display") === "none") {
        //$("#show_" + answerid).removeAttr('onclick');
        $("#show_" + answerid).attr('onclick', "hide_comment(" + answerid + ");");
        $("#show_" + answerid).html("收起回复");
    }
    load_comment(answerid, page);
    $("#comment_" + answerid).slideDown();
}

// 隐藏回复的评论
function hide_comment(answerid) {
    $("#comment_" + answerid).slideUp();
    $("#show_" + answerid).attr('onclick', "show_comment(" + answerid + ", 1);");
    $("#show_" + answerid).html("回复");
}

// 添加评论
function add_comment(answerid) {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = "/user/login";
        return false;
    }

    var content = $("#comment_" + answerid + " textarea[name='content']").val();
    if (char_bytes($.trim(content)) < 5){
        alert("评论内容不能少于5字");
        return false;
    }
    var answer = new Answer();
    answer.add_comment({"answerid" : answerid, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "answer不存在，answerid无效",
            103 : "回复内容不能为空"
        };
        if (response.success) {
            $("#comment_" + answerid + " textarea[name='content']").val("");
            load_comment(answerid, 1);
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function answer_question(qid) {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = "/user/login";
        return false;
    }
    var answer_content = UE.getEditor('answer_question_content').getPlainTxt();
    if ($.trim(answer_content) == '') {
        alert("内容不能为空!");
        return false;
    }

    var question = new Question();
    question.answer({"qid" : qid, "content" : answer_content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "提交回答失败,帖子不存在",
            103 : "验证码错误",
            104 : "回复内容不能为空"
        };
        if (response.success) {
            window.location.reload();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
    return true;
}

/*
// 关注问题
function attentto_question(qid) {
    login();
    return false;
    g_forum.attention_2_question(qid, function(msg) {
        if (msg == 'ok') {
            if ($("#attenttoquestion").hasClass("btn-info")) {
                $("#attenttoquestion").removeClass("btn-info");
                $("#attenttoquestion").addClass("btn-success");
                $("#attenttoquestion").val("取消关注");
            } else {
                $("#attenttoquestion").removeClass("btn-success");
                $("#attenttoquestion").addClass("btn-info");
                $("#attenttoquestion").val("关注此贴");
            }
        }
    });
}
*/
