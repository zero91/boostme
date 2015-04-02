$(function() {
    $("#login_btn").click(function() { login(); });
    $("#register_btn").click(function() { register(); });
});

function login() {
    var req_data_dict = {};
    req_data_dict['username'] = $("#username").val();
    req_data_dict['password'] = $("#password").val();
    req_data_dict['forward'] = $("#forward").val();
    req_data_dict['code'] = $("#code").val();

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
