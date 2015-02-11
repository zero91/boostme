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

//验证码
function updatecode() {
    var img = g_site_url + "user/code/" + Math.random();
    $('#verifycode').attr("src", img);
}

//验证码检测
function check_code() {
    var code = $.trim($('#code').val());
    if ($.trim(code) == '') {
        $('#codetip').html("验证码错误");
        $('#codetip').attr('class', 'input_error');
        return false;
    }
    var result = true;
    $.ajax({
        type: "GET",
        async: false,
        cache: false,
        url: g_site_url + "user/ajaxcode/" + code,
        success: function(flag) {
            if ("1" == flag) {
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
        }
    });
    return result;
}

function bytes(str) {
    var len = 0;
    for (var i = 0; i < str.length; i++) {
        if (str.charCodeAt(i) > 127) {
            len++;
        }
        len++;
    }
    return len;
}

// 排序使用函数
function index_sort_func(x, y) {
    var int_x = parseInt(x['index']);
    var int_y = parseInt(y['index']);

    if (int_x > int_y) {
        return 1;
    } else {
        return -1;
    }
}

// 获取地区option列表
function fetch_region_optionhtml(m_region_dict) {
    var region_index_list = Array();
    for (var region_id in m_region_dict) {
        region_index_list.push({'index' : m_region_dict[region_id]['index'], 'id' : region_id});
    }
    region_index_list.sort(index_sort_func);

    var optionhtml = "<option value=''>地区</option>";
    for (var k = 0; k < region_index_list.length; ++k) {
        region_id = region_index_list[k]['id'];
        optionhtml += "<option value='" + region_id + "'>" + m_region_dict[region_id]['name'] + "</option>";
    }
    return optionhtml;
}

// 获取某地区的学校列表
function fetch_school_optionhtml(m_region_dict, m_school_dict, region_id) {
    var school_list = m_region_dict[region_id]['school_list'];

    var school_index_list = Array();
    for (var k = 0; k < school_list.length; ++k) {
        var school_id = school_list[k];
        school_index_list.push({'index' : m_school_dict[school_id]['index'], 'id' : school_id});
    }
    school_index_list.sort(index_sort_func);

    var optionhtml = "<option value=''>学校</option>";
    for (var k = 0; k < school_index_list.length; ++k) {
        school_id = school_index_list[k]['id'];
        optionhtml += "<option value='" + school_id + "'>" + m_school_dict[school_id]['name'] + "</option>";
    }
    return optionhtml;
}

// 获取某学校的院系列表
function fetch_dept_optionhtml(m_school_dict, m_dept_dict, school_id) {
    var dept_list = m_school_dict[school_id]['dept_list'];

    var dept_index_list = Array();
    for (var k = 0; k < dept_list.length; ++k) {
        var dept_id = dept_list[k];
        dept_index_list.push({'index' : m_dept_dict[dept_id]['index'], 'id' : dept_id});
    }
    dept_index_list.sort(index_sort_func);

    var optionhtml = "<option value=''>院系</option>";
    for (var k = 0; k < dept_index_list.length; ++k) {
        dept_id = dept_index_list[k]['id'];
        optionhtml += "<option value='" + dept_id + "'>" + m_dept_dict[dept_id]['name'] + "</option>";
    }
    return optionhtml;
}

function fetch_major_optionhtml(m_dept_dict, m_major_dict, dept_id) {
    var major_list = m_dept_dict[dept_id]['major_list'];

    var major_index_list = Array();
    for (var k = 0; k < major_list.length; ++k) {
        var major_id = major_list[k];
        major_index_list.push({'index' : m_major_dict[major_id]['index'], 'id' : major_id});
    }
    major_index_list.sort(index_sort_func);

    var optionhtml = "<option value=''>专业</option>";
    for (var k = 0; k < major_index_list.length; ++k) {
        major_id = major_index_list[k]['id'];
        optionhtml += "<option value='" + major_id + "'>" + m_major_dict[major_id]['name'] + "</option>";
    }
    return optionhtml;
}

function fetch_region_name(region_id) {
    return m_region_dict[region_id]['name'];
}

function fetch_school_name(school_id) {
    var region_name = fetch_region_name(m_school_dict[school_id]['region']);
    return region_name + " " + m_school_dict[school_id]['name'];
}

function fetch_dept_name(dept_id) {
    var school_name = fetch_school_name(m_dept_dict[dept_id]['school']);
    return school_name + " " + m_dept_dict[dept_id]['name'];
}

function fetch_major_name(major_id) {
    var dept_name = fetch_dept_name(m_major_dict[major_id]['dept']);
    return dept_name + " " + m_major_dict[major_id]['name'];
}

function fetch_name_by_id(id) {
    if (id[0] == 'R') {
        return fetch_region_name(id);

    } else if (id[0] == 'S') {
        return fetch_school_name(id);

    } else if (id[0] == 'D') {
        return fetch_dept_name(id);

    } else if (id[0] == 'M') {
        return fetch_major_name(id);
    }
    return "";
}

function fetch_name_by_all(region_id, school_id, dept_id, major_id) {
    if (major_id) {
        return fetch_name_by_id(major_id);

    } else if (dept_id) {
        return fetch_name_by_id(dept_id);

    } else if (school_id) {
        return fetch_name_by_id(school_id);

    } else if (region_id) {
        return fetch_name_by_id(region_id);
    }
    return "";
}

/*
function update_category_value() {
    var choose_num = $("#choose_list option").length;

    var text_array = new Array();
    var value_array = new Array();
    var already_choose = false;
    for (var i = 0; i < choose_num; ++i) {
        text_array.push($("#choose_list").get(0).options[i].text);
        value_array.push($("#choose_list").get(0).options[i].value);

        if ($("#major option:selected").html() == $("#choose_list").get(0).options[i].text) {
            already_choose = true;
        }
    }

    if (already_choose == true || $("#major").length == 0) {
        $("#category_add_btn").attr('disabled', 'disabled');
    } else {
        $("#category_add_btn").removeAttr('disabled');
    }

    if ($("#choose_list option:selected").length == 0) {
        $("#category_del_btn").attr('disabled', 'disabled');
    } else {
        $("#category_del_btn").removeAttr('disabled');
    }

    $("#category").val(text_array.join(","));
    $("#category_id").val(value_array.join(","));

    if (choose_num > 0) {
        $("#category_btn").removeAttr('disabled');
    } else {
        $("#category_btn").attr('disabled', 'disabled');
    }
}

function check_form() {
    var title = $("#title").val();
    if (bytes($.trim(title)) < 20 || bytes($.trim(title)) > 100) {
        alert("标题长度不得少于10个字，不能超过50字！");
        $("#title").focus();
        return false;
    }
    <!--{if $op_type == "add"}-->
    if ($("#category_id").val().length == 0) {
        alert("您还没有添加分类信息哦！");
        $("#category").click();
        return false;
    }
    <!--{/if}-->
    return true;
}
*/
