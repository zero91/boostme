$(function() {
    var ebank = new EBank();
    $("#alipay_submit_btn").click(function() {
        ebank.alipay_transfer({"trade_no" : g_trade_no}, function(response) {
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
    });
});
