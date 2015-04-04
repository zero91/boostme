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

