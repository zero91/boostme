var g_add_category = new Array();
// 排序使用函数
function index_sort_func(x, y) {
    var int_x = parseInt(x['index']);
    var int_y = parseInt(y['index']);

    if (int_x > int_y) {
        return 1;
    }
    return -1;
}

// 获取地区option列表
function fetch_region_optionhtml() {
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

// 获取地区的学校option列表
function fetch_school_optionhtml(region_id) {
    var optionhtml = "<option value=''>学校</option>";
    if (m_region_dict[region_id] === undefined) {
        return optionhtml;
    }

    var school_list = m_region_dict[region_id]['school_list'];
    var school_index_list = Array();
    for (var k = 0; k < school_list.length; ++k) {
        var school_id = school_list[k];
        school_index_list.push({'index' : m_school_dict[school_id]['index'], 'id' : school_id});
    }
    school_index_list.sort(index_sort_func);

    for (var k = 0; k < school_index_list.length; ++k) {
        school_id = school_index_list[k]['id'];
        optionhtml += "<option value='" + school_id + "'>" + m_school_dict[school_id]['name'] + "</option>";
    }
    return optionhtml;
}

// 获取学校的院系option列表
function fetch_dept_optionhtml(school_id) {
    var optionhtml = "<option value=''>院系</option>";
    if (m_school_dict[school_id] === undefined) {
        return optionhtml;
    }

    var dept_list = m_school_dict[school_id]['dept_list'];
    var dept_index_list = Array();
    for (var k = 0; k < dept_list.length; ++k) {
        var dept_id = dept_list[k];
        dept_index_list.push({'index' : m_dept_dict[dept_id]['index'], 'id' : dept_id});
    }
    dept_index_list.sort(index_sort_func);

    for (var k = 0; k < dept_index_list.length; ++k) {
        dept_id = dept_index_list[k]['id'];
        optionhtml += "<option value='" + dept_id + "'>" + m_dept_dict[dept_id]['name'] + "</option>";
    }
    return optionhtml;
}

// 获取院系的专业option列表
function fetch_major_optionhtml(dept_id) {
    var optionhtml = "<option value=''>专业</option>";
    if (m_dept_dict[dept_id] === undefined) {
        return optionhtml;
    }

    var major_list = m_dept_dict[dept_id]['major_list'];
    var major_index_list = Array();
    for (var k = 0; k < major_list.length; ++k) {
        var major_id = major_list[k];
        major_index_list.push({'index' : m_major_dict[major_id]['index'], 'id' : major_id});
    }
    major_index_list.sort(index_sort_func);

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
    if (id[0] == 'R') return fetch_region_name(id);
    if (id[0] == 'S') return fetch_school_name(id);
    if (id[0] == 'D') return fetch_dept_name(id);
    if (id[0] == 'M') return fetch_major_name(id);
    return "";
}

function fetch_name_by_all(region_id, school_id, dept_id, major_id) {
    if (major_id) return fetch_name_by_id(major_id);
    if (dept_id) return fetch_name_by_id(dept_id);
    if (school_id) return fetch_name_by_id(school_id);
    if (region_id) return fetch_name_by_id(region_id);
    return "";
}

function create_easy_access(id, region_id, school_id, dept_id, major_id) {
    var label_color_array = {2 : 'label-warning',
                             1 : 'label-success',
                             5 : 'label-default',
                             3 : 'label-danger',
                             0 : 'label-primary',
                             4 : 'label-info'};

    var label_color = label_color_array[g_easy_access_cnt];
    var label_content = fetch_name_by_all(region_id, school_id, dept_id, major_id);
    var label_url = g_site_url + "/service/default?region_id=" + region_id
                                               + "&school_id=" + school_id
                                               + "&dept_id=" + dept_id
                                               + "&major_id=" + major_id;
    var link_html = '<p class="coms"><a class="label ' + label_color;
    link_html += ' " href="' + label_url + '">' + label_content + '</a>';
    link_html += '<a id="' + id + '" href="#" data-toggle="tooltip" class="tip" data-placement="bottom" data-original-title="删除此快捷入口">';
    link_html += '<small class="glyphicon glyphicon-minus-sign" id="remove_easy_access"></small></a></p>';

    $("#easy_access_list").append(link_html);
    g_easy_access_cnt += 1;
}

$(function() {
    $("body").on('click', '#remove_easy_access', function() {
        var id = $(this).parent().attr('id');
        if (confirm('确定删除该快捷入口?') === false) {
            return false;
        }
        var target = $(this).parent().parent();
        var user = new User();
        user.remove_easy_access({"id" : id}, function(response) {
            var error_dict = {
                101 : "参数错误",
                102 : "删除失败",
                103 : "用户尚未登录"
            };
            if (response.success) {
                target.remove();
                g_easy_access_cnt -= 1;
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    $("#add_easy_access").click(function() {
        if (confirm('确定添加该快捷入口?') === false) {
            return false;
        }
        var region_id = $.trim($("#select_region option:selected").val());
        var school_id = $.trim($("#select_school option:selected").val());
        var dept_id = $.trim($("#select_dept option:selected").val());
        var major_id = $.trim($("#select_major option:selected").val());

        var user = new User();
        user.add_easy_access({"region_id" : region_id,
                              "school_id" : school_id,
                              "dept_id" : dept_id,
                              "major_id" : major_id,
                              "type" : g_easy_access_type}, function(response) {
            var error_dict = {
                101 : "资料快捷链接数量不能超过3",
                102 : "最多只能添加3个快捷入口!",
                103 : "您还未登录"
            };
            if (response.success) {
                create_easy_access(response.id, region_id, school_id, dept_id, major_id);
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    if (typeof(g_easy_access_type) != 'undefined') {
        var user = new User();
        user.fetch_easy_access({"type" : g_easy_access_type}, function(response) {
            if (response.success) {
                for (var i = 0; i < response.easy_access_list.length; ++i) {
                    create_easy_access(response.easy_access_list[i].id,
                                       response.easy_access_list[i].region_id,
                                       response.easy_access_list[i].school_id,
                                       response.easy_access_list[i].dept_id,
                                       response.easy_access_list[i].major_id);
                }
            }
        });
    }

    $("#select_region").change(function() {
        var region_id = $("#select_region option:selected").val();

        $("#select_school").html(fetch_school_optionhtml(region_id));
        $("#select_school").removeAttr('disabled');

        $("#select_dept").html(fetch_dept_optionhtml());
        $("#select_dept").attr('disabled', 'disabled');

        $("#select_major").html(fetch_major_optionhtml());
        $("#select_major").attr('disabled', 'disabled');

        category_change_callback({"region_id" : region_id});
        g_region_id = region_id;
        g_school_id = "";
        g_dept_id = "";
        g_major_id = "";
        if ($('.selectpicker') && $('.selectpicker').selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
    }); 

    $("#select_school").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();

        $("#select_dept").html(fetch_dept_optionhtml(school_id));
        $("#select_dept").removeAttr('disabled');

        $("#select_major").html(fetch_major_optionhtml());
        $("#select_major").attr('disabled', 'disabled');

        category_change_callback({"region_id" : region_id, "school_id" : school_id});
        g_region_id = region_id;
        g_school_id = school_id;
        g_dept_id = "";
        g_major_id = "";
        if ($('.selectpicker') && $('.selectpicker').selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
    }); 

    $("#select_dept").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();
        var dept_id = $("#select_dept option:selected").val();

        $("#select_major").html(fetch_major_optionhtml(dept_id));
        $("#select_major").removeAttr('disabled');

        category_change_callback({"region_id" : region_id,
                                  "school_id" : school_id,
                                  "dept_id" : dept_id});
        g_region_id = region_id;
        g_school_id = school_id;
        g_dept_id = dept_id;
        g_major_id = "";
        if ($('.selectpicker') && $('.selectpicker').selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
    });

    $("#select_major").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();
        var dept_id = $("#select_dept option:selected").val();
        var major_id = $("#select_major option:selected").val();

        category_change_callback({"region_id" : region_id,
                                  "school_id" : school_id,
                                  "dept_id" : dept_id,
                                  "major_id" : major_id});
        g_region_id = region_id;
        g_school_id = school_id;
        g_dept_id = dept_id;
        g_major_id = major_id;
        if ($('.selectpicker') && $('.selectpicker').selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
    });

    $("#select_region").html(fetch_region_optionhtml());

    if (typeof(g_region_id) == 'undefined' || $.trim(g_region_id) == '') {
        $("#select_school").attr('disabled', 'disabled');
        $("#select_school").html(fetch_school_optionhtml());
    } else {
        $("#select_region").val(g_region_id);
        $("#select_school").html(fetch_school_optionhtml(g_region_id));
    }

    if (typeof(g_school_id) == 'undefined' || $.trim(g_school_id) == '') {
        $("#select_dept").attr('disabled', 'disabled');
        $("#select_dept").html(fetch_dept_optionhtml());
    } else {
        $("#select_school").val(g_school_id);
        $("#select_dept").html(fetch_dept_optionhtml(g_school_id));
    }

    if (typeof(g_dept_id) == 'undefined' || $.trim(g_dept_id) == '') {
        $("#select_major").attr('disabled', 'disabled');
        $("#select_major").html(fetch_major_optionhtml());
    } else {
        $("#select_dept").val(g_dept_id);
        $("#select_major").html(fetch_major_optionhtml(g_dept_id));
    }

    if (typeof(g_major_id) != 'undefined' && $.trim(g_major_id) != '') {
        $("#select_major").val(g_major_id);
    }

    $("#more").click(function() {
        g_view_page_num += 1;
        request_show_data({"page": g_view_page_num,
                           "region_id" : g_region_id,
                           "school_id" : g_school_id,
                           "dept_id" : g_dept_id,
                           "major_id" : g_major_id});
    });
    $("#more").click();

    $("#add_category").click(function() {
        var category_dict = fetch_choose_category();
        if (category_dict['region_id'] == ""
                && category_dict['school_id'] == ""
                && category_dict['dept_id'] == ""
                && category_dict['major_id'] == "") {
            return;
        }
        if (_.some(g_add_category, function(t_category) {
                                return _.isEqual(t_category, category_dict); })) {
            return;
        }
        var show_val = fetch_name_by_all(category_dict['region_id'],
                                         category_dict['school_id'],
                                         category_dict['dept_id'],
                                         category_dict['major_id']);
        $("#category_list").append("<p> + " + show_val + ' <a id="remove_category_' + g_add_category.length
            + '" class="glyphicon glyphicon-trash" style="color:grey;cursor:pointer;"></a></p>');
        g_add_category.push(category_dict);
        $("#select_region").val("");
        $("#select_region").change();
    });

    if ($("#save_material_btn").length > 0) {
        var mid = $("div[id^='material_id_']").attr('id').substr(12);
        var material = new Material();
        material.fetch_category({"mid" : mid}, function(response) {
            if (response.success) {
                _.each(response.cid_list, function(category_dict) {
                    var show_val = fetch_name_by_all(category_dict['region_id'],
                                                     category_dict['school_id'],
                                                     category_dict['dept_id'],
                                                     category_dict['major_id']);
                    $("#category_list").append("<p> + " + show_val + ' <a id="remove_category_' + g_add_category.length
                        + '" class="glyphicon glyphicon-trash" style="color:grey;cursor:pointer;"></a></p>');
                    g_add_category.push(category_dict);
                });
            }
        });
    }

    $("body").on('click', 'a[id^=remove_category_]', function() {
        var remove_index = $(this).attr("id").substr(16);
        $(this).parent().remove();
        g_add_category.splice(remove_index, 1);
    });
});

function category_change_callback(category_dict) {
    g_view_page_num = 1;
    category_dict['page'] = g_view_page_num;

    $("#board_show").empty();
    request_show_data(category_dict);
}

function fetch_choose_category() {
    var region_id = $.trim($("#select_region option:selected").val());
    var school_id = $.trim($("#select_school option:selected").val());
    var dept_id = $.trim($("#select_dept option:selected").val());
    var major_id = $.trim($("#select_major option:selected").val());
    return {"region_id" : region_id,
            "school_id" : school_id,
            "dept_id" : dept_id,
            "major_id" : major_id};
}

