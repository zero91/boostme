$(function() {
    $("#alipay_submit_btn").click(function() {
        alipay_transfer(g_trade_id);
    });

    $("input[id^='btn_pay_']").click(function() {
        var trade_id = $(this).attr("id").substr(8);
        alipay_transfer(trade_id);
    });

    if ($("#withdraw_history").length > 0) {
        fetch_withdraw_history();
    }

    $("#withdraw_btn").click(function() { withdraw() });
});

function add_ebank_account() {
    var input_html = '<input id="ebank_account" type="text" class="form-control" style="float:left; border-left:none;" value="">';
    $("#ebank_account").parent().html(input_html);
    $("#add_account").hide();
}

function alipay_transfer(trade_id) {
    var alipay = new Alipay();
    alipay.transfer({"trade_id" : trade_id}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "用户所支付的非本人订单",
            103 : "无效订单"
        };
        if (response.success) {
            $("body").append(response['html']);
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function fetch_withdraw_history() {
    var ebank = new EBank();
    ebank.fetch_withdraw({}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
        };
        if (response['success']) {
            if (response['list'].length > 0) {
                var withdraw_history_template = _.template($("#withdraw_history_template").html());
                $("#withdraw_history").html(withdraw_history_template({
                                            'list' : response['list']}));
            }
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}

function withdraw() {
    var ebank_type_account = $("#ebank_account").val();
    if (!$.trim(ebank_type_account)) {
        alert("请选择您要套现的支付宝账号");
        return false;
    }
    var money = parseFloat($("#money").val());
    if (isNaN(money) || money <= 0) {
        alert("金额必须大于0，且不高于可提取现金");
        return false;
    }

    var split_ind = ebank_type_account.search("_");
    var ebank_type = ebank_type_account.substr(0, split_ind);
    var ebank_account = ebank_type_account.substr(split_ind + 1);
    var ebank = new EBank();
    if (!$.trim(ebank_type)) {
        ebank_type = 1;
    }
    ebank.withdraw({"money" : money,
                    "ebank_account" : ebank_account,
                    "ebank_type" : ebank_type}, function(response) {
        var error_dict = {
            101 : "用户尚未登录",
            102 : "账户余额不足",
            103 : "无效参数",
            104 : "添加失败",
            105 : "更新失败",
        };
        if (response.success) {
            alert("申请套现成功，请您耐心等待。工作人员审核通过后，就会将现金打到您的账户上");
            window.location.reload();
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
