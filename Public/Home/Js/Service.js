function fetch_service(category) {
    var service = new Service();
    service.fetch_list(category, function(response) {
        if (response.success) {
            var service_template = _.template($("#service_board_template").html());
            for (var s = 0; s < response['list'].length; ++s) {
                $("#board_show").append(service_template({"service" : response['list'][s]}));
            }

            var param_arr = new Array();
            if (_.has(category, "region") && $.trim(category['region']).length > 0) {
                param_arr.push('region=' + category['region']);
            }
            if (_.has(category, "school") && $.trim(category['school']).length > 0) {
                param_arr.push('school=' + category['school']);
            }
            if (_.has(category, "dept") && $.trim(category['dept']).length > 0) {
                param_arr.push('dept=' + category['dept']);
            }
            if (_.has(category, "major") && $.trim(category['major']).length > 0) {
                param_arr.push('major=' + category['major']);
            }
            var url = g_site_url + g_regular;
            if (param_arr.length > 0) {
                url += "?" + param_arr.join("&");
            }
            history.pushState({}, 0, url);

            if (response["list"].length == 0) {
                $("#more_service").hide();
            } else {
                $("#more_service").show();
            }
        }
    });
}

function raty_user_comment_score() {
    $('span[id^="user_score_"]').each(function(){
        var id = $(this).attr('id'); 
        var score = id.substr(id.lastIndexOf('_') + 1);

        $(this).raty({
            number : 5,
            path: g_site_url + "/Public/Common/Js/third/raty/images",
            half: true,
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

        var star_config = {
            number : 5,
            hints : ['1', '2', '3', '4', '5'],
            path : g_site_url + "/Public/Common/Js/third/raty/images",
            starOff : "star-off-big.png",
            starOn : "star-on-big.png",
            starHalf :"star-half-big.png",
            half : true,
            round : {down: .26, full: .6, up: .76},
            readOnly:  false,
        };

        if (response.success) {
            $("#user_comment").html('<div style="padding:20px 0;"><p>' + 
                                        response["content"] + "</p></div>");
            star_config['score'] = response["score"];
            star_config['readOnly'] = true;
        }
        $('#self_star').raty(star_config);
    });
};

$(function() {
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
            console.log(response);
            var error_dict = {
                101 : "无效参数",
            };
            if (response.success) {
                for (var k = 0; k < response['category'].length; ++k) {
                    var c = response['category'][k].school + 
                                ' ' + response['category'][k].dept +
                                ' ' + response['category'][k].major;
                    $("#service_category_list").append("<p>" + c + '</p><p class="li_text_info"></p>');
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
                $("#comment_list").html(comment_template({'comment_list' : response['list']}));
                raty_user_comment_score();
            } else {
                errno_alert(response.error, error_dict);
            }
        });

        fetch_user_comment(g_service_id);

        $('#avg_star').raty({
            number : 5,
            path: g_site_url + "/Public/Common/Js/third/raty/images",
            starOff:"star-off-big.png",
            starOn:"star-on-big.png",
            starHalf:"star-half-big.png",
            half : true,
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
                console.log(response);
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
