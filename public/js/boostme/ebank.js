$(function() {
    $("#alipay_submit_btn").click(function() { alipay_transfer(g_trade_no); });

    $("input[id^='btn_pay_']").click(function() {
        var trade_no = $(this).attr("id").substr(8);
        alipay_transfer(trade_no);
    });

    if ($("#withdraw_history").length > 0) {
        fetch_withdraw_history();
    }

    $("#add_withdraw_btn").click(function() { add_withdraw() });
});

function alipay_transfer(trade_no) {
    var ebank = new EBank();
    ebank.alipay_transfer({"trade_no" : trade_no}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "用户所支付的非本人订单",
        };
        if (response.success) {
            $("body").append(response.html_text);
        } else {
            errno_alert(response.error, error_dict);
        }
    });

}

function fetch_withdraw_history() {
    var ebank = new EBank();
    ebank.fetch_history({}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
        };
        if (response.success) {
            if (response.withdraw_list.length > 0) {
                var withdraw_history_template = _.template($("#withdraw_history_template").html());
                $("#withdraw_history").html(withdraw_history_template({
                                            'withdraw_list' : response.withdraw_list}));
            }
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function add_withdraw() {
    var ebank_type_account = $("#ebank_account").val();
    if (!$.trim(ebank_type_account)) {
        alert("请选择您要套现的支付宝账号");
        return false;
    }
    var money = parseFloat($("#money").val());
    if (money <= 0) {
        alert("金额必须大于0，且不高于账户余额");
        return false;
    }

    var split_ind = ebank_type_account.search("_");
    var ebank_type = ebank_type_account.substr(0, split_ind);
    var ebank_account = ebank_type_account.substr(split_ind + 1);
    var ebank = new EBank();
    ebank.add_withdraw({"money" : money,
                        "ebank_account" : ebank_account,
                        "ebank_type" : ebank_type}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "账户余额不足",
            103 : "无效参数",
            104 : "添加失败"
        };
        if (response.success) {
            alert("申请套现成功，请您耐心等待。工作人员审核通过后，就会将现金打到你的账户上");
            window.location.reload();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
