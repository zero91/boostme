$(function() {
    $("#login_btn").click(function() { login(); });
    $("#register_btn").click(function() { register(); });
    $("#update_passwd_btn").click(function() { update_passwd(); });
    $("#update_info_btn").click(function() { update_info(); });
});

function login() {
    var req_data_dict = {};
    req_data_dict['username'] = $("#username").val();
    req_data_dict['password'] = $("#password").val();
    req_data_dict['forward'] = $("#forward").val();
    req_data_dict['code'] = $("#code").val();

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
    req_data_dict['email'] = $.trim($('#email').val());
    req_data_dict['code'] = $('#code').val();
    req_data_dict['invite_code'] = $("#invite_code").val();
    req_data_dict['forward'] = $("#forward").val();

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
            109 : "验证码错误"
        };
        if (response.success) {
            alert("注册成功");
            self.location.href = response.forward;
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
    req_data_dict['phone'] = $('#phone').val();
    req_data_dict['qq'] = $('#qq').val();
    req_data_dict['wechat'] = $('#wechat').val();
    req_data_dict['bday'] = $("#bday").val();
    req_data_dict['signature'] = $('#signature').val();

    var user = new User();
    user.update_info(req_data_dict, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "邮件格式不正确",
            103 : "邮件已被占用"
        };
        if (response.success) {
            alert("修改成功");
            $(".jsk-setting-success").html('信息已经成功修改');
            $(".jsk-setting-success").fadeIn(800);
            $(".jsk-setting-success").fadeOut(1500);
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
