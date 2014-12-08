function index_sort_func(x, y) {
    var int_x = parseInt(x['index']);
    var int_y = parseInt(y['index']);

    if (int_x > int_y) {
        return 1;
    } else {
        return -1;
    }
}

function show_region() {
    var optionhtml = '';
    var region_index_list = Array();
    for (var region_id in m_region_dict) {
        region_index_list.push({'index' : m_region_dict[region_id]['index'], 'id' : region_id});
    }
    region_index_list.sort(index_sort_func);

    for (var k = 0; k < region_index_list.length; ++k) {
        region_id = region_index_list[k]['id'];
        optionhtml += "<option value='" + region_id + "'>" + m_region_dict[region_id]['name'] + "</option>";
    }
    $("#region").html(optionhtml);
}

function show_school(region_id) {
    var optionhtml = '';
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
    $("#school").html(optionhtml);
}

function show_dept(school_id) {
    var optionhtml = '';
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
    $("#dept").html(optionhtml);
}

function show_major(dept_id) {
    var optionhtml = '';
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
    $("#major").html(optionhtml);
}

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

function get_select_category() {
    var region_id = $.trim($("#select_region option:selected").val());
    var school_id = $.trim($("#select_school option:selected").val());
    var dept_id = $.trim($("#select_dept option:selected").val());
    var major_id = $.trim($("#select_major option:selected").val());

    var region_name = "";
    var school_name = "";
    var dept_name = "";
    var major_name = "";

    var add_category_dict = {};
    var show_val = "";
    if (region_id) {
        add_category_dict['region_id'] = region_id;
        show_val += m_region_dict[region_id]['name'];
        if (school_id) {
            add_category_dict['school_id'] = school_id;
            show_val += " " + m_school_dict[school_id]['name'];
            if (dept_id) {
                add_category_dict['dept_id'] = dept_id;
                show_val += " " + m_dept_dict[dept_id]['name'];
                if (major_id) {
                    add_category_dict['major_id'] = major_id;
                    show_val += " " + m_major_dict[major_id]['name'];
                }
            }
        }
    }
    return {'category' : add_category_dict, 'show_val' : show_val};
}

function clear_select_state() {
    $("#select_region").html(fetch_region_optionhtml(m_region_dict));

    $("#select_school").html("<option value=''>学校</option>");
    $("#select_school").attr('disabled', 'disabled');

    $("#select_dept").html("<option value=''>院系</option>");
    $("#select_dept").attr('disabled', 'disabled');

    $("#select_major").html("<option value=''>专业</option>");
    $("#select_major").attr('disabled', 'disabled');
}

$(function() {
    //$("#select_region").change(function() {
    $("select[id^='select_region']").change(function() {
        var region_id = $("#select_region option:selected").val();
        $("#select_school").html(fetch_school_optionhtml(m_region_dict, m_school_dict, region_id));
        $("#select_school").removeAttr('disabled');

        $("#select_dept").html("<option value=''>院系</option>");
        $("#select_dept").attr('disabled', 'disabled');

        $("#select_major").html("<option value=''>专业</option>");
        $("#select_major").attr('disabled', 'disabled');
        //$('.selectpicker').selectpicker('refresh');
    }); 

    //$("#select_school").change(function() {
    $("select[id^='select_school']").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();
        $("#select_dept").html(fetch_dept_optionhtml(m_school_dict, m_dept_dict, school_id));
        $("#select_dept").removeAttr('disabled');

        $("#select_major").html("<option value=''>专业</option>");
        $("#select_major").attr('disabled', 'disabled');
        //$('.selectpicker').selectpicker('refresh');
    });

    //$("#select_dept").change(function() {
    $("select[id^='select_dept']").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();
        var dept_id = $("#select_dept option:selected").val();
        $("#select_major").html(fetch_major_optionhtml(m_dept_dict, m_major_dict, dept_id));
        $("#select_major").removeAttr('disabled');
        //$('.selectpicker').selectpicker('refresh');
    });

    //$("#select_major").change(function() {
    $("select[id^='select_major']").change(function() {
        var region_id = $("#select_region option:selected").val();
        var school_id = $("#select_school option:selected").val();
        var dept_id = $("#select_dept option:selected").val();
        var major_id = $("#select_major option:selected").val();
    });

    $("#select_region").html(fetch_region_optionhtml(m_region_dict));
});

