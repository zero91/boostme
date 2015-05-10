// 字符串转化为json对象
function str2json(json_str) {
    return eval("(" + json_str + ")");
}

// 同步请求
function sync_request(req_url, req_type, req_data_dict, callback_func) {
    $.ajax({
        type : req_type,  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(response) {  
            callback_func(response);
        }
    });
}

// 异步请求
function async_request(req_url, req_type, req_data_dict, callback_func) {
    $.ajax({
        type : req_type,  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : true, // 异步获取
        success : function(response) {  
            callback_func(response);
        }
    });
}

function errno_alert(errno, errno_info) {
    if (errno in errno_info) {
        alert(errno_info[errno]);
    } else {
        alert("发生未知错误");
    }
}

// 计算含中文字符的长度
function char_bytes(str) {
    var len = 0;
    for (var i = 0; i < str.length; ++i) {
        if (str.charCodeAt(i) > 127) {
            ++len;
        }
        ++len;
    }
    return len;
}

// 更新验证码
function update_code() {
    var img = g_site_url + "/user/ajax_code/" + Math.random();
    $('#verifycode').attr("src", img);
}

// 验证码检测
function check_code() {
    var code = $.trim($('#code').val());
    if ($.trim(code) == '') {
        $('#codetip').html("验证码错误");
        $('#codetip').attr('class', 'input_error');
        return false;
    }
    var result = true;
    var req_url = g_site_url + "/user/ajax_check_code";
    sync_request(req_url, "get", {"code" : code}, function(response) {
        if (response.success) {
            $('#codetip').html("&nbsp;");
            $('#codetip').attr('class', 'input_ok');
            result = true;
            return true;
        } else {
            $('#codetip').html("验证码错误");
            $('#codetip').attr('class', 'input_error');
            result = false;
            return false;
        }
    });
    return result;
}

// @check_image_size [检查image是否被缩放了]
// @param  image_obj [jquery对象的image]
// @return      true [图片被缩放了]
//             false [图片未被缩放]
function check_image_size(image_obj) {
    var image = new Image();
    image.src = image_obj.attr("src");
    return image.width > image_obj.width() || image.height > image_obj.height();
}

function image_light(image_target) {
    image_target.each(function() {
        var image = $(this);
        image.load(function() {
            if (check_image_size(image)) {
                var src = image.attr("src");
                var title = image.attr("title");
                image.wrap("<a href='" + src + "' title='" + title + "' data-lightbox='comment'></a>");
            }
        });
    });
}

function raty_ui(target, score, read_only) {
    target.raty({
        number : 5,
        hints: ['1', '2', '3', '4', '5'],
        path: g_site_url + "/public/js/plugin/raty/images",
        starOff:"star-off-big.png",
        starOn:"star-on-big.png",
        starHalf:"star-half-big.png",
        half : true,
        readOnly:  read_only,
        round : {down: .26, full: .6, up: .76},
        score: score
    });
}

