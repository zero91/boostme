$(function() {
    $("#reply_btn").click(function() { reply(); });
    $("a[id^='remove_']").click(function() { remove($(this).attr("id").substr(7)); });
    $(".glyphicon-trash").click(function() { remove_dialog($(this).attr("id").substr(14)); });
});

// 私信回复
function reply() {
    var content = UE.getEditor('content').getPlainTxt();
    if ($.trim(content) == '') {
        alert("消息内容不能为空!");
        return false;
    }
    var to_username = $("#to_username").val();
    var message = new Message();
    message.send({"username" : to_username, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "接收方用户不存在",
            103 : "不能给自己发消息",
            104 : "消息内容不能为空"
        };
        if (response.success) {
            window.location.reload();
            UE.getEditor('content').setContent("");
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

// 删除单条消息
function remove(mid) {
    if (confirm('确定删除该条消息?') === false) {
        return false;
    }
    var message = new Message();
    message.remove({"id" : mid}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "消息id无效"
        };
        if (response.success) {
            $("#" + mid).remove();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function remove_dialog(uid) {
    if (confirm('确定删除与该用户的私信?') === false) {
        return false;
    }
    console.log("uid = " + uid);

    var message = new Message();
    message.remove_dialog({"uid" : uid}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "用户id无效"
        };
        if (response.success) {
            $("#" + uid).remove();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

