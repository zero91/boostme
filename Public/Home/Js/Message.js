$(function() {
    $("#reply_btn").click(function() { reply(); });
    $("a[id^='remove_']").click(function() { remove($(this).attr("id").substr(7)); });
    $("a[id^='rm_dialog_']").click(function() { remove_dialog($(this).attr("id").substr(10)); });
});

// 私信回复
function reply() {
    var content = UE.getEditor('reply_content').getPlainTxt();
    if ($.trim(content) == '') {
        alert("消息内容不能为空!");
        return false;
    }
    var to_uid = $("#to_uid").val();
    var message = new Message();
    message.send({"uid" : to_uid, "content" : content}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "接收方用户不存在",
            103 : "不能给自己发消息",
            104 : "消息内容不能为空",
            105 : "操作失败"
        };
        if (response.success) {
            window.location.reload();
            UE.getEditor('reply_content').setContent("");
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
            102 : "消息ID号无效",
            103 : "已删除",
            104 : "无权操作",
            105 : "更新失败"
        };
        if (response.success) {
            $("#message_" + mid).remove();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function remove_dialog(uid) {
    if (confirm('确定删除与该用户的私信?') === false) {
        return false;
    }

    var message = new Message();
    message.remove_dialog({"uid" : uid}, function(response) {
        var error_dict = {
            101 : "用户尚未登录"
        };
        if (response.success) {
            $("#uid_" + uid).remove();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

