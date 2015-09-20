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
    return true;
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

function trigger_enter_key(target, callback_func) {
    target.keydown(function(e) {
        var ev = document.all ? window.event : e;
        if (ev.which == 13) {
            callback_func();
            return true;
        }
    });
}

//弹出窗口
function pop(id) {
    // 将窗口居中
    makeCenter(id);

    // 初始化省份列表
    initProvince(id);

    // 默认情况下, 给第一个省份添加choosen样式
    $('[province-id="1"]').addClass('choosen');

    // 初始化大学列表
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
    for (i = 0; i < schoolList.length; ++i) {
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
    for (i = 0; i <schools.length; ++i) {
        $(target_name).append('<a class="school-item" school-id="'+schools[i].id+'">'+schools[i].name+'</a>');
    }
    //添加大学列表项的click事件
    $('.school-item').bind('click', function(){
            var item=$(this);

            var school = item.attr('school-id');
            //更新选择大学文本框中的值
            item.parents(".col-sm-5").children(".form-control").val(item.text());
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

