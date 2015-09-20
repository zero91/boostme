// 获取地区option列表
function fetch_region_optionhtml() {
    return _.union(["<option value=''>地区</option>"], _.map(school_data, function(item) {
        return "<option value='" + item['name'] + "'>" + item['name'] + "</option>";
    }));
}

// 获取地区的学校option列表
function fetch_school_optionhtml(region) {
    return _.union(["<option value=''>学校</option>"],
                    _.chain(school_data)
                     .find(function(item) { return item['name'] == region; })
                     .find(function(value, key) { return key == "school"; })
                     .map(function(item) {
                         return "<option value='" + item['name'] + "'>" + item['name'] + "</option>"; })
                     .value()
    );
}

// 获取学校的院系option列表
function fetch_dept_optionhtml(region, school) {
    return _.union(["<option value=''>院系</option>"],
                    _.chain(school_data)
                     .find(function(item) { return item['name'] == region; })
                     .find(function(value, key) { return key == "school"; })
                     .find(function(item) { return item['name'] == school; })
                     .find(function(value, key) { return key == "dept"; })
                     .map(function(item) {
                         return "<option value='" + item['name'] + "'>" + item['name'] + "</option>"; })
                     .value()
    );
}

// 获取院系的专业option列表
function fetch_major_optionhtml(region, school, dept) {
    return _.union(["<option value=''>专业</option>"],
                    _.chain(school_data)
                     .find(function(item) { return item['name'] == region; })
                     .find(function(value, key) { return key == "school"; })
                     .find(function(item) { return item['name'] == school; })
                     .find(function(value, key) { return key == "dept"; })
                     .find(function(item) { return item['name'] == dept; })
                     .find(function(value, key) { return key == "major"; })
                     .map(function(major) {
                         return "<option value='" + major+ "'>" + major + "</option>"; })
                     .value()
    );
}

function select_region(callback) {
    var region = $("#select_region option:selected").val();

    if ($.trim(region).length > 0) {
        $("#select_school").html(fetch_school_optionhtml(region));
        $("#select_school").removeAttr('disabled');
    } else {
        $("#select_school").html(fetch_school_optionhtml());
        $("#select_school").attr('disabled', 'disabled');
    }

    $("#select_dept").html(fetch_dept_optionhtml());
    $("#select_dept").attr('disabled', 'disabled');

    $("#select_major").html(fetch_major_optionhtml());
    $("#select_major").attr('disabled', 'disabled');

    callback({"region" : region});
    if ($('.selectpicker') && $('.selectpicker').selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
} 

function select_school(callback) {
    var region = $("#select_region option:selected").val();
    var school = $("#select_school option:selected").val();

    if ($.trim(school).length > 0) {
        $("#select_dept").html(fetch_dept_optionhtml(region, school));
        $("#select_dept").removeAttr('disabled');
    } else {
        $("#select_dept").html(fetch_dept_optionhtml());
        $("#select_dept").attr('disabled', 'disabled');
    }

    $("#select_major").html(fetch_major_optionhtml());
    $("#select_major").attr('disabled', 'disabled');

    callback({"region" : region, "school" : school});
    if ($('.selectpicker') && $('.selectpicker').selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
}

function select_dept(callback) {
    var region = $("#select_region option:selected").val();
    var school = $("#select_school option:selected").val();
    var dept = $("#select_dept option:selected").val();

    if ($.trim(dept).length > 0) {
        $("#select_major").html(fetch_major_optionhtml(region, school, dept));
        $("#select_major").removeAttr('disabled');
    } else {
        $("#select_major").html(fetch_major_optionhtml());
        $("#select_major").attr('disabled', 'disabled');
    }

    callback({"region" : region, "school" : school, "dept" : dept});
    if ($('.selectpicker') && $('.selectpicker').selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
}

function select_major(callback) {
    var region = $("#select_region option:selected").val();
    var school = $("#select_school option:selected").val();
    var dept = $("#select_dept option:selected").val();
    var major = $("#select_major option:selected").val();

    callback({"region" : region, "school" : school, "dept" : dept, "major" : major});
    if ($('.selectpicker') && $('.selectpicker').selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
}

// 设置页面分类信息，并调用回调函数
function refresh_category(region, school, dept, major, callback) {
    $("#select_region").html(fetch_region_optionhtml());
    var school_disabled = true;
    var dept_disabled = true;
    var major_disabled = true;

    if (typeof(region) != 'undefined' && $.trim(region).length > 0) {
        school_disabled = false;
        $("#select_region").val(region);
        $("#select_school").html(fetch_school_optionhtml(region));

        if (typeof(school) != 'undefined' && $.trim(school).length > 0) {
            dept_disabled = false;
            $("#select_school").val(school);
            $("#select_dept").html(fetch_dept_optionhtml(region, school));

            if (typeof(dept) != 'undefined' && $.trim(dept).length > 0) {
                major_disabled = false;
                $("#select_dept").val(dept);
                $("#select_major").html(fetch_major_optionhtml(region, school, dept));

                if (typeof(major) != 'undefined' && $.trim(major).length > 0) {
                    $("#select_major").val(major);
                }
            }
        }
    }

    if (school_disabled) {
        $("#select_school").html(fetch_school_optionhtml());
        $("#select_school").attr('disabled', 'disabled');
    }
    if (dept_disabled) {
        $("#select_dept").html(fetch_dept_optionhtml());
        $("#select_dept").attr('disabled', 'disabled');
    }
    if (major_disabled) {
        $("#select_major").html(fetch_major_optionhtml());
        $("#select_major").attr('disabled', 'disabled');
    }
    if (typeof(callback) != 'undefined' && _.isFunction(callback)) {
        callback({"region" : region, "school" : school, "dept" : dept, "major" : major});
    }
    if ($('.selectpicker') && $('.selectpicker').selectpicker) {
        $('.selectpicker').selectpicker('refresh');
    }
}

function fetch_choose_category() {
    var region = $.trim($("#select_region option:selected").val());
    var school = $.trim($("#select_school option:selected").val());
    var dept = $.trim($("#select_dept option:selected").val());
    var major = $.trim($("#select_major option:selected").val());
    return {"region" : region, "school" : school, "dept" : dept, "major" : major};
}
