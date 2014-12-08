function Forum(site_url) {
    this.site_url = site_url;
}

Forum.prototype.get_site_url = function() {
    return this.site_url;
}

Forum.prototype.get_service_list = function(req_data_dict) {
    var req_url = this.site_url + "service/fetch_list";
    var service_list = {};

    /* // 异步
    $.post(req_url, req_data_dict, function(ret_service_list) {
        service_list = ret_service_list;
    }); */

    $.ajax({
        type : "post",  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(ret_service_list) {  
            service_list = ret_service_list;
        }
    });
    return service_list;
}

Forum.prototype.start_img_light = function(img_target) {
    //$(".content img").each(function(i) 
    img_target.each(function(i) {
        var img = $(this);
        $.ajax({
            type: "POST",
            url: "/main/ajaxchkimg",
            async: true,
            data: "imgsrc=" + img.attr("src"),
            success: function(status) {
                if (status == '1') {
                    img.wrap("<a href='" + img.attr("src") + "' title='" + img.attr("title") + "' data-lightbox='comment'></a>");
                }
            }
        });
    });
}

Forum.prototype.agree_answer= function(agree_target) {
    //$(".button_agree").hover(function()
    agree_target.hover(function(){
        var answerid = $(this).parent().attr("id");
        var supportobj = $(this);

        $.ajax({
            type: "GET",
            url:"/answer/ajaxhassupport/" + answerid,
            cache: false,
            success: function(hassupport){
                if (hassupport == '1'){
                    supportobj.val("已赞");
                } else {
                    supportobj.val("赞");
                }
            }
        });
        $(this).css("font-weight", "normal");
    }, function() {
        var answerid = $(this).parent().attr("id");
        var supportobj = $(this);

        $.ajax({
            type: "GET",
            url:"/answer/ajaxgetsupport/" + answerid,
            cache: false,
            success: function(support){
                supportobj.val(support);
            }
        });
        $(this).css("font-weight", "bold");
    });
}

Forum.prototype.agree_click = function(agree_target) {
    //$(".button_agree").click(function()
    agree_target.click(function(){
        var supportobj = $(this);
        var answerid = $(this).parent().attr("id");

        $.ajax({
            type: "GET",
            url:"/answer/ajaxhassupport/" + answerid,
            cache: false,
            success: function(hassupport) {
                if (hassupport != '1'){
                    $("#support_tip").css({height:"0px", opacity:0});
                    $("#support_tip").show();
                    $("#support_tip").position({my:"top-40", of: supportobj});
                    $("#support_tip").animate({"opacity":"1"}, 500).animate({"opacity":"0"}, 200);
                    $.ajax({
                        type: "GET",
                        cache:false,
                        url: "/answer/ajaxaddsupport/" + answerid,
                        success: function(comments) {
                            supportobj.val("已赞同");
                        }
                    });
                }
            }
        });
    });
}

Forum.prototype.add_comment = function(answerid, content, callback_func) {
    $.ajax({
        type: "POST",
        url: "/answer/addcomment",
        data: "content=" + content + "&answerid=" + answerid,
        success: function(data) {
            callback_func(data);
        }
    });
}

Forum.prototype.delete_comment = function(answerid, commentid, callback_func) {
    $.ajax({
        type: "POST",
        url: "/answer/deletecomment",
        data: "commentid=" + commentid + "&answerid=" + answerid,
        success: function(data) {
            callback_func(data);
        }
    });
}

Forum.prototype.load_comment = function(answerid, page, callback_func) {
    $.ajax({
        type: "GET",
        cache:false,
        url: "/answer/ajaxviewcomment/" + answerid + "/" + page,
        success: function(comments) {
            callback_func(comments);
        }
    });
}

Forum.prototype.attention_2_question = function(qid, callback_func) {
    $.post("/question/attentto", {qid: qid}, function(msg) {
        callback_func(msg);
    });
}

