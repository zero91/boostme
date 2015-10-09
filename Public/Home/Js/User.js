$(function() {
    if ($("#login_btn")) {
        $("#login_btn").click(function() { login(); });
        trigger_enter_key($("#password"), function() {
            login();
        });
    }

    $("#register_btn").click(function() { register(); });
    $("#update_passwd_btn").click(function() { update_passwd(); });
    $("#update_info_btn").click(function() { update_info(); });

    if ($("#add_edu_btn").length > 0) {
        var user = new User();
        user.fetch_edu({"uid" : g_userid}, function(response) {
            var error_dict = {
                101 : "用户尚未登录",
                102 : "参数无效"
            };
            if (response.success) {
                if (response.list.length > 0) {
                    for (var k = 0; k < response.list.length; ++k) {
                        add_edu(response.list[k]);
                    }
                } else {
                    add_edu({});
                }
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    }

    $("#resume_update_btn").click(function() { update_resume(false); });
    $("#resume_apply_btn").click(function() { update_resume(true); });
    $("body").on("click", "a[id^='del_edu_']", function() {
        var id = $(this).attr("id").substr(8);
        $("#school_" + id).remove();
    });
});

function login() {
    var req_data_dict = {};
    req_data_dict['username'] = $("#username").val();
    req_data_dict['password'] = $("#password").val();
    req_data_dict['forward'] = $("#forward").val();
    req_data_dict['verify'] = $("#verify").val();

    if ($("#cookietime").is(":checked")) {
        req_data_dict['cookietime'] = $("#cookietime").val();
    }

    var user = new User();
    user.login(req_data_dict, function(response) {
        var error_dict = {
            101 : "用户名或密码错误",
            102 : "用户名或密码错误",
            103 : "验证码错误",
            104 : "已登录，请勿重复登录"
        };
        if (response.success) {
            self.location.href = response.forward;
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

var reg_usernameok = false;
var reg_passwordok = false;
var reg_repasswdok = false;
var reg_emailok = false;

function check_username() {
    var username = $.trim($('#username').val());
    var length = char_bytes(username);
    if (length < 3 || length > 15) {
        $('#usernametip').html("用户名请使用3到15个字符");
        $('#usernametip').attr('class', 'input_error');
        reg_usernameok = false;
    } else {
        var user = new User();
        user.check_username({"username" : username}, function(response) {
            if (response.success) {
                $('#usernametip').html("&nbsp;");
                $('#usernametip').attr('class', 'input_ok');
                reg_usernameok = true;
            } else if (response.error == 101) {
                $('#usernametip').html("此用户名已经存在");
                $('#usernametip').attr('class', 'input_error');
                reg_usernameok = false;
            } else if (response.error == 102) {
                $('#usernametip').html("用户名含有禁用字符");
                $('#usernametip').attr('class', 'input_error');
                reg_usernameok = false;
            } else if (response.error == 103) {
                $('#usernametip').html("用户名不能为空");
                $('#usernametip').attr('class', 'input_error');
                reg_usernameok = false;
            }
        });
    }
}

function check_email() {
    var email = $.trim($('#email').val());
    if (!email.match(/^[\w\.\-]+@([\w\-]+\.)+[a-z]{2,4}$/ig)) {
        $('#emailtip').html("邮件格式不正确");
        $('#emailtip').attr('class', 'input_error');
        reg_emailok = false;
    } else {
        var user = new User();
        user.check_email({"email": email}, function(response) {
            if (response.success) {
                $('#emailtip').html("&nbsp;");
                $('#emailtip').attr('class', 'input_ok');
                reg_emailok = true;
            } else if (response.error == 101) {
                $('#emailtip').html("此邮件地址已经注册");
                $('#emailtip').attr('class', 'input_error');
                reg_emailok = false;
            } else if (response.error == 102) {
                $('#emailtip').html("邮件地址被禁止注册");
                $('#emailtip').attr('class', 'input_error');
                reg_emailok = false;
            } else if (response.error == 103) {
                $('#emailtip').html("邮件地址不能为空");
                $('#emailtip').attr('class', 'input_error');
                reg_emailok = false;
            }
        });
    }
}

function check_passwd() {
    var passwd = $('#password').val();
    if (char_bytes(passwd) < 6 || char_bytes(passwd) > 16) {
        $('#passwordtip').html("密码最少6个字符，最长不得超过16个字符");
        $('#passwordtip').attr('class', 'input_error');
        reg_passwordok = false;
    } else {
        $('#passwordtip').html("&nbsp;");
        $('#passwordtip').attr('class', 'input_ok');
        reg_passwordok = true;
    }
}

function check_repasswd() {
    reg_repasswdok = 1;
    var repassword = $('#repassword').val();
    if (char_bytes(repassword) < 6 || char_bytes(repassword) > 16) {
        $('#repasswordtip').html("密码最少6个字符，最长不得超过16个字符");
        $('#repasswordtip').attr('class', 'input_error');
        reg_repasswdok = false;
    } else {
        if ($('#password').val() == $('#repassword').val()) {
            $('#repasswordtip').html("&nbsp;");
            $('#repasswordtip').attr('class', 'input_ok');
            reg_repasswdok = true;
        } else {
            $('#repasswordtip').html("两次密码输入不一致");
            $('#repasswordtip').attr('class', 'input_error');
            reg_repasswdok = false;
        }
    }
}

function check_reg_input() {
    if (!reg_usernameok) {
        check_username();
        return false;
    }
    if (!reg_passwordok) {
        check_passwd();
        return false;
    }
    if (!reg_repasswdok) {
        check_repasswd();
        return false;
    }
    if (!reg_emailok) {
        check_email();
        return false;
    }

    if (g_code_register) {
        return check_code();
    }
    return true;
}

function register() {
    if (!check_reg_input()) {
        return false;
    }
    var req_data_dict = {};
    req_data_dict['username'] = $.trim($('#username').val());
    req_data_dict['password'] = $('#password').val();
    req_data_dict['repassword'] = $('#repassword').val();
    req_data_dict['email'] = $.trim($('#email').val());
    req_data_dict['verify'] = $('#verify').val();

    var user = new User();
    user.register(req_data_dict, function(response) {
        var error_dict = {
            101 : "用户已登录",
            102 : "系统注册功能暂时处于关闭状态",
            103 : "当前IP已经超过当日最大注册数目",
            104 : "用户名或密码不能为空",
            105 : "邮件地址不合法",
            106 : "用户名已存在",
            107 : "此邮件地址已经注册",
            108 : "用户名不合法",
            109 : "验证码错误",
            110 : "密码与重复密码不一致",
            111 : "邮箱被禁止注册",
            112 : "密码长度不在6-30个字符之间",
            113 : "邮箱长度不在1-64个字符之间",
            114 : "用户名长度不在16个字符以内"
        };
        if (response.success) {
            self.location.href = g_site_url + "/User/login";
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function update_passwd() {
    if (!check_code()) {
        $("#code").focus();
        return false;
    }
    var newpwd = $.trim($("#newpwd").val());
    if (!newpwd) {
        $("#newpwd_tip").html("新密码不能为空");
        $("#newpwd_tip").attr("class", "input_error");
        $("#newpwd").focus();
        return false;
    }

    var confirmpwd = $.trim($("#confirmpwd").val());
    if (newpwd != confirmpwd) {
        $("#confirmpwd_tip").html("两次密码输入不一致");
        $("#confirmpwd_tip").attr("class", "input_error");
        $("#confirmpwd").focus();
        return false;
    }

    var oldpwd = $('#oldpwd').val();
    var user = new User();
    user.update_passwd({"newpwd" : newpwd, "oldpwd" : oldpwd}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "新密码为空",
            103 : "新密码与旧密码相同",
            104 : "验证码错误",
            105 : "旧密码不对"
        };
        if (response.success) {
            $("#oldpwd_tip").html("");
            $("#oldpwd_tip").attr("class", "");
            $("#oldpwd").val("");

            $("#newpwd_tip").html("");
            $("#newpwd_tip").attr("class", "");
            $("#newpwd").val("");

            $("#confirmpwd_tip").html("");
            $("#confirmpwd_tip").attr("class", "");
            $("#confirmpwd").val("");
            alert("修改成功");

        } else if (response.error == 102) {
            $("#newpwd_tip").html("新密码不能为空");
            $("#newpwd_tip").attr("class", "input_error");
            $("#newpwd").focus();

        } else if (response.error == 103) {
            $("#newpwd_tip").html('新密码与旧密码相同');
            $("#newpwd_tip").attr("class", "input_error");
            $("#newpwd").focus();

        } else if (response.error == 105) {
            $("#oldpwd_tip").html('旧密码错误');
            $("#oldpwd_tip").attr("class", "input_error");
            $("#oldpwd").focus();

        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function update_info() {
    var req_data_dict = {};
    req_data_dict['email'] = $('#email').val();
    req_data_dict['gender'] = $('#gender').val();
    req_data_dict['mobile'] = $('#mobile').val();
    req_data_dict['qq'] = $('#qq').val();
    req_data_dict['wechat'] = $('#wechat').val();
    req_data_dict['birthday'] = $("#birthday").val();

    console.log(req_data_dict);
    var user = new User();
    user.update_profile(req_data_dict, function(response) {
        console.log(response);
        var error_dict = {
            101 : "用户尚未登录",
            102 : "邮件格式不正确",
            103 : "邮件已被占用",
            104 : "手机号已被占用",
        };
        if (response.success) {
            $(".jsk-setting-success").html('信息已经成功修改');
            $(".jsk-setting-success").fadeIn(800);
            $(".jsk-setting-success").fadeOut(1500);
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

var g_edu_item_num = 0;
function add_edu(edu) {
    if (g_edu_item_num >= 6) {
        alert("教育经历不能超过6个！");
        return false;
    }
    var choose_school_template = _.template($("#resume_choose_school_template").html());
    var more_edu = $(choose_school_template({"id" : g_edu_item_num + 1,
                                             "edu" : edu}));
    more_edu.hide();
    $("#add_edu_btn").before(more_edu);
    more_edu.fadeIn("slow");

    $(".datepicker").datepicker({
        changeYear: true,
        changeMonth: true,
        yearRange: '-30:+0',
        dateFormat: 'yy-mm-dd'
    });
    g_edu_item_num += 1;
    return true;
}

function get_edu_list() {
    var edu_list = new Array();
    for (var i = 1; i <= g_edu_item_num; ++i) {
        if ($("#school_" + i).length > 0) {
            var degree     = $.trim($("#degree-" + i).val());
            var school     = $.trim($("#school-" + i).val());
            var dept       = $.trim($("#dept-" + i).val());
            var major      = $.trim($("#major-" + i).val());
            var start_time = $.trim($("#datepicker-0-" + i).val());
            var end_time   = $.trim($("#datepicker-1-" + i).val());
            edu_list.push({
                "school" : school,
                "dept" : dept,
                "major" : major,
                "degree" : degree,
                "start_time" : start_time,
                "end_time" : end_time
            });
        }
    }
    return edu_list;
}

function update_resume(apply) {
    var edu_list = get_edu_list();
    for (var k = 0; k < edu_list.length; ++k) {
        if (edu_list[k]["school"] == ""
                || edu_list[k]["dept"] == ""
                || edu_list[k]["major"] == ""
                || edu_list[k]["degree"] == ""
                || edu_list[k]["start_time"] == "") {
            alert("教育经历信息没有填写完整，请检查");
            return false;
        }
    }
    var realname = $.trim($("#realname").val());
    if (realname == "") {
        $('#realname_tip').html("请填写您的真实姓名");
        $('#realname_tip').attr('class', 'input_error');
        $("#realname").focus();
        return false;
    } else {
        $('#realname_tip').html("&nbsp;");
        $('#realname_tip').attr('class', 'input_ok');
    }

    var phone = $.trim($("#phone").val());
    if (phone.length == 11) {
        $("#phone_tip").html("&nbsp;");
        $('#phone_tip').attr('class', 'input_ok');
    } else {
        $('#phone_tip').html("请填写您的电话号码");
        $('#phone_tip').attr('class', 'input_error');
        $("#phone").focus();
        return false;
    }

    var wechat = $.trim($("#wechat").val());
    if (wechat.length > 0) {
        $("#wechat_tip").html("&nbsp;");
        $("#wechat_tip").attr('class', 'input_ok');
    } else {
        $('#wechat_tip').html("请填写您的微信号");
        $('#wechat_tip').attr('class', 'input_error');
        $("#wechat").focus();
        return false;
    }

    var qq = $.trim($("#qq").val());

    var req_data_dict = {};
    req_data_dict['realname'] = realname;
    req_data_dict['phone'] = phone;
    req_data_dict['wechat'] = wechat;
    req_data_dict['qq'] = qq;
    req_data_dict['edu_list'] = edu_list;

    if (apply) req_data_dict['apply'] = true;

    var user = new User();
    user.update_resume(req_data_dict, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "更新失败",
            103 : "未上传学生证"
        };
        if (response.success) {
            if (apply) alert("成功提交申请，请耐心等待，我们将在一个工作日内给予答复");
            else alert("更新成功");
            //window.location.reload();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
