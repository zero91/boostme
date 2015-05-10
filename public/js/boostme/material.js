$(function() {
    $("#query_submit_btn").click(function() {
        var query = $("#search_query").val();
        if ($.trim(query) == "") {
            query = "";
        }
        query_search(query, 1);
    });

    $("#search_query").keydown(function(e) {
        var ev = document.all ? window.event : e;
        if (ev.which == 13) {
            $("#query_submit_btn").click();
            return false;
        }
    });

    $("#search_query").focus();
    raty_ui($("#avg_star"), g_material_avg_score, true);
    fetch_user_comment(g_material_id);

    var material = new Material();
    material.fetch_comment({"id" : g_material_id}, function(response) {
        var error_dict = {
            101 : "无效参数",
        };
        if (response.success) {
            var comment_template = _.template($("#material_comment_list_template").html());
            $("#comment_list").html(comment_template({'comment_list' : response.comment_list}));
            raty_user_comment_score();
        } else {
            errno_alert(response.error, error_dict);
        }
    });

    $("#access_info_btn").click(function() {
        $("#access_div").show();
    });

    image_light($("#description img"));

    $("#evaluation-submit-btn").click(function() {
        var score = $("input[name='score']").val();
        var content = $("#evaluation_content").val();
        if ($.trim(score) == '') {
            alert("您还没有选择评分");
            return false;
        }
        if ($.trim(content) == '') {
            alert("评论内容不能为空");
            return false;
        }
        material.add_comment({"mid" : g_material_id,
                             "content" : content,
                             "score" : score}, function(response) {
            var error_dict = {
                101 : "用户尚未登录",
                102 : "无效参数",
            };
            if (response.success) {
                fetch_user_comment(g_material_id);
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });
    /*
    $("a[id^='thumbs_']").click(function() {
        var id = $(this).attr('id'); 
        var comment_id = id.substr(id.lastIndexOf('_') + 1);
        var t_id = id.substr(0, id.lastIndexOf('_'));
        var thumbs_type = t_id.substr(t_id.lastIndexOf('_') + 1);

        var num_target = $(this).children('em');

        $.get("{SITE_URL}/material/comment_support/" + comment_id + "/" + thumbs_type, "", function(data) {
            data = parseInt(data);
            if (data > 0) {
                if (thumbs_type == '0') {
                    num_target.html("有用(" + data + ")");
                } else {
                    num_target.html("无用(" + data + ")");
                }
            } else if (data == '0') {
                alert("请先登录");
                window.location.href = "{SITE_URL}/user/login";
            } else if (data == '-1') {
                alert("操作失败");
            } else if (data == '-2') {
                alert("请勿重复操作");
            }
        });
    });
    */
});

// 搜索资料
function query_search(query, page) {
    var material = new Material();

    material.search({"query" : query, "page" : page}, function(response) {
        if (response.success) {
            var template_content = _.template($("#material_search_list_template").html());
            $("#query_result").html(template_content({material_list : response.material_list,
                                                      tot_num : response.tot_num,
                                                      departstr : response.departstr}));
        }
    });
}

function request_show_data(req_data_dict) {
    var material = new Material();
    if (g_material_type === undefined) {
        g_material_type = "major";
    }
    req_data_dict["type"] = g_material_type;
    material.fetch_list(req_data_dict, function(response) {
        var template_content = _.template($("#material_category_list_template").html());
        $("#board_show").append(template_content({"material_list" : response["material_list"],
                                                  "start" : response["start"]}));

        if (response["material_list"].length == 0) {
            $("#more").hide();
        } else {
            $("#more").show();
        }
    });
}

function raty_user_comment_score() {
    $('span[id^="user_score_"]').each(function(){
        var id = $(this).attr('id'); 
        var score = id.substr(id.lastIndexOf('_') + 1);
        raty_ui($(this), score, true);
    });
}

function fetch_user_comment(material_id) {
    var material = new Material();
    material.fetch_user_comment({"mid" : material_id}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "无效参数",
        };
        if (response.success) {
            var show_score = 0
            var read_only = false;
            if (response.comment) {
                $("#user_comment").html('<div style="padding:20px 0;"><p>' + 
                                    response.comment["content"] + "</p></div>");
                show_score = response.comment['score'];
                read_only = true;
            }
            raty_ui($("#self_star"), show_score, read_only);
        }
    });
};

