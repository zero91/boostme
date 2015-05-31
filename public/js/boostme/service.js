function request_show_data(req_data_dict) {
    if (g_regular != '/service/default') {
        return;
    }
    var service = new Service();
    service.fetch_list(req_data_dict, function(response) {
        for (var k = 0; k < response["service_list"].length; ++k) {
            $("#board_show").append(create_board(response["service_list"][k]));
        }
        if (response["service_list"].length == 0) {
            $("#more").hide();
        } else {
            $("#more").show();
        }
    });
    var para_cnt = 0;
    var url = g_site_url + g_regular;
    if (req_data_dict['region_id'] !== undefined && $.trim(req_data_dict['region_id']) != '') {
        para_cnt++ == 0 ? url += '?' : url += '&';
        url += 'region_id=' + req_data_dict['region_id'];
    }
    if (req_data_dict['school_id'] !== undefined && $.trim(req_data_dict['school_id']) != '') {
        para_cnt++ == 0 ? url += '?' : url += '&';
        url += 'school_id=' + req_data_dict['school_id'];
    }
    if (req_data_dict['dept_id'] !== undefined && $.trim(req_data_dict['dept_id']) != '') {
        para_cnt++ == 0 ? url += '?' : url += '&';
        url += 'dept_id=' + req_data_dict['dept_id'];
    }
    if (req_data_dict['major_id'] !== undefined && $.trim(req_data_dict['major_id']) != '') {
        para_cnt++ == 0 ? url += '?' : url += '&';
        url += 'major_id=' + req_data_dict['major_id'];
    }
    history.pushState({}, 0, url);
}

function create_board(service) {
    var html = '<div class="col-sm-6 col-md-4">';
    html    += '<a href="' + g_site_url + '/service/view?service_id=' + service.id + '" class="job-item-wrap" target="_blank">';
    html    += '    <div class="job-item">';
    html    += '        <div class="job-source light-green">';
    html    += '            <img class="img-responsive" src="' + service.avatar + '">';
    html    += '        </div>';
    html    += '        <div class="job-company">' + service.username + '</div>';
    html    += '        <div class="job-title">' + service.service_content + '</div>';
    html    += '        <div class="job-salary">价格：¥' +  parseFloat(service.price,2) + '</div>';
    html    += '        <div class="job-comments">';
    html    += '            <p>';
    html    += '                <span class="label label-default">服务' + service.service_num + '人</span>';
    html    += '                <span class="label label-default">浏览' + service.view_num + '次</span>';
    html    += '                <span class="label label-default">评论' + service.comment_num + '条</span>';
    html    += '                <span class="label label-default">平均' + parseFloat(service.avg_score).toFixed(2) + '分</span>';
    html    += '            </p>';
    html    += '        </div>';
    html    += '        <div class="job-meta">';
    html    += '            <span class="job-publish-time">' + service.format_time + '</span>';
    html    += '        </div>';
    html    += '    </div>';
    html    += '</a>';
    html    += '</div>';
    return html;
}

function raty_user_comment_score() {
    $('span[id^="user_score_"]').each(function(){
        var id = $(this).attr('id'); 
        var score = id.substr(id.lastIndexOf('_') + 1);

        $(this).raty({
            number : 10,
            path: g_site_url + "/public/js/plugin/raty/images",
            half: false,
            readOnly: true,
            score: score
        });
    });
}

function fetch_user_comment(service_id) {
    var service = new Service();
    service.fetch_user_comment({"service_id" : service_id}, function(response) {
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
            $('#self_star').raty({
                number : 10,
                hints: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                path: g_site_url + "/public/js/plugin/raty/images",
                starOff:"star-off-big.png",
                starOn:"star-on-big.png",
                starHalf:"star-half-big.png",
                half : false,
                round : {down: .26, full: .6, up: .76},
                score : show_score,
                readOnly:  read_only,
            });
        }
    });
};

$(function() {
    $("#service_register_btn").click(function() {
        var service_content = $("#service_content").val();
        var service_time = $("#service_time").val();
        var price = $("#price").val();
        var phone = $("#phone").val();
        var qq = $("#qq").val();
        var wechat = $("#wechat").val();

        $("#add_category").click();

        var service = new Service();

        service.add_service({"service_content" : service_content,
                             "service_time" : service_time,
                             "price" : price,
                             "phone" : phone,
                             "qq" : qq,
                             "wechat" : wechat,
                             "category_list" : g_add_category}, function(response) {
            var error_dict = {
                101 : "尚未登录",
                102 : "服务尚未填写完整",
                103 : "未填写手机号"
            };
            if (response.success) {
                window.location.reload();
                alert("已成功提交申请，我们将在24小时内给您答复，请耐心等待");
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    $("a[id^=service_close_]").click(function() {
        var service_id = $(this).attr("id").substr("14");
        var service = new Service();
        var remove_target = $(this).parent();
        var change_target = remove_target.parent().prev();
        service.close_service({"service_id" : service_id}, function(response) {
            var error_dict = {
                101 : "尚未登录",
                102 : "服务ID号无效",
                103 : "用户无权关闭该服务",
                104 : "关闭操作失败"
            };
            if (response.success) {
                alert("关闭成功");
                remove_target.remove();
                change_target.text("暂停提供服务");
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    if (typeof(g_service_id) !== "undefined") {
        var service = new Service();
        service.fetch_category({"service_id" :  g_service_id}, function(response) {
            var error_dict = {
                101 : "无效参数",
            };
            if (response.success) {
                for (var k = 0; k < response.cid_list.length; ++k) {
                    $("#service_category_list").append("<p>" +
                            fetch_name_by_all(response.cid_list[k].region_id,
                                              response.cid_list[k].school_id,
                                              response.cid_list[k].dept_id,
                                              response.cid_list[k].major_id) + '</p><p class="li_text_info"></p>');
                }
            } else {
                errno_alert(response.error, error_dict);
            }
        });

        service.fetch_comment({"service_id" : g_service_id}, function(response) {
            var error_dict = {
                101 : "无效参数",
            };
            if (response.success) {
                var comment_template = _.template($("#comment_list_template").html());
                $("#comment_list").html(comment_template({'comment_list' : response.comment_list}));
                raty_user_comment_score();
            } else {
                errno_alert(response.error, error_dict);
            }
        });

        fetch_user_comment(g_service_id);

        $('#avg_star').raty({
            number : 10,
            path: g_site_url + "/public/js/plugin/raty/images",
            starOff:"star-off-big.png",
            starOn:"star-on-big.png",
            starHalf:"star-half-big.png",
            half : false,
            readOnly:  true,
            score: g_service_avg_score,
        });

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

            service.add_comment({"service_id" : g_service_id,
                                 "content" : content,
                                 "score" : score}, function(response) {
                var error_dict = {
                    101 : "用户尚未登录",
                    102 : "无效参数",
                };
                if (response.success) {
                    fetch_user_comment(g_service_id);
                } else {
                    errno_alert(response.error, error_dict);
                }
            });
        });
    }
});
/*
    $("a[id^='thumbs_']").click(function() {
        var id = $(this).attr('id'); 
        var comment_id = id.substr(id.lastIndexOf('_') + 1);
        var t_id = id.substr(0, id.lastIndexOf('_'));
        var thumbs_type = t_id.substr(t_id.lastIndexOf('_') + 1);

        var num_target = $(this).children('em');


        $.get("{SITE_URL}service/comment_support/" + comment_id + "/" + thumbs_type, "", function(data) {
            data = parseInt(data);
            if (data > 0) {
                if (thumbs_type == '0') {
                    num_target.html("有用(" + data + ")");
                } else {
                    num_target.html("无用(" + data + ")");
                }
            } else if (data == '0') {
                alert("请先登录");
                window.location.href = "{SITE_URL}user/login";
            } else if (data == '-1') {
                alert("操作失败");
            } else if (data == '-2') {
                alert("请勿重复操作");
            }
        });
    });
});
*/
