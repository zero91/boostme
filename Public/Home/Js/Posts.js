$(function() {
    $("#add_post_btn").click(function() { add_post(); });

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

// 新增帖子
function add_post() {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = g_site_url + "/User/login";
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

    var verify = $("#verify").val();
    var posts= new Posts();
    posts.add({"title" : title, "content" : content, "verify" : verify}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "验证码错误",
            103 : "标题长度不在5,64之间",
            104 : "内容长度不在0,2048之间"
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

// 新增回复
function add_answer(pid) {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = g_site_url + "/User/login";
        return false;
    }
    var content = UE.getEditor('edit_answer').getPlainTxt();
    if ($.trim(content) == '') {
        alert("内容不能为空!");
        return false;
    }

    var posts = new Posts();
    posts.answer({"pid" : pid, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "提交回答失败,帖子不存在",
            103 : "验证码错误",
            104 : "回复内容长度不在5,2048之间"
        };
        if (response.success) {
            window.location.reload();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
    return true;
}

// 添加评论
function add_comment(answer_id) {
    if (g_userid == 0) {
        alert("您还未登录，请先登录");
        window.location.href = g_site_url + "/User/login";
        return false;
    }

    var content = $("#comment_" + answer_id + " textarea[name='content']").val();
    if ($.trim(content) == '') {
        alert("评论不能为空");
        return false;
    }
    var posts = new Posts();
    posts.comment({"answer_id" : answer_id, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "回复不存在",
            103 : "评论内容长度应小于512"
        };
        if (response.success) {
            $("#comment_" + answer_id + " textarea[name='content']").val("");
            load_comment(answer_id, 1);
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

// 加载回复的评论
function load_comment(answer_id, page) {
    var posts = new Posts();
    posts.fetch_comment_list({"answer_id" : answer_id, "page" : page}, function(response) {
        if (response.success) {
            if (response.list.length == 0 && page == 1) {
                var comments = '<div style="text-align:center;">暂无评论</div>';
                $("#comment_" + answer_id + " .list-group").html(comments);
            } else {
                var comment_template = _.template($("#answer_comment_list_template").html());
                var comments = comment_template({'comment_list' : response.list,
                                                 'departstr' : response.departstr});
                $("#comment_" + answer_id + " .list-group").html(comments);
            }
        }
    });
}

// 显示回复的评论
function show_comment(answer_id, page) {
    if ($("#comment_" + answer_id).css("display") === "none") {
        //$("#show_" + answerid).removeAttr('onclick');
        $("#show_" + answer_id).attr('onclick', "hide_comment(" + answer_id + ");");
        $("#show_" + answer_id).html("收起回复");
    }
    load_comment(answer_id, page);
    $("#comment_" + answer_id).slideDown();
}

// 隐藏回复的评论
function hide_comment(answerid) {
    $("#comment_" + answerid).slideUp();
    $("#show_" + answerid).attr('onclick', "show_comment(" + answerid + ", 1);");
    $("#show_" + answerid).html("回复");
}
