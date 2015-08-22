<?php
namespace Home\Controller;
use Pay\Api\AlipayApi;

class AlipayController extends HomeController {
    // 支付宝返回页面回调
    public function returns() {
        $Alipay = new AlipayApi;
        $result = $Alipay->returns();

        $ret = D('Alipay', 'Logic')->process($result);
        $this->redirect("Trade/index");
    }

    // 支付宝后台同质页面回调
    public function notify() {
        $Alipay = new AlipayApi;
        $result = $Alipay->notify();

        $ret = D('Alipay', 'Logic')->process($result);
        if ($ret) {
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_transfer  获取跳转到支付宝付款界面的html文本
    // @request   GET
    // @param  integer  trade_id  订单号
    //
    // @ajaxReturn  成功 - array("success" => true, "html" => 跳转到支付宝的HTML/JS代码)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 用户所支付的非本人订单
    // @error  103 - 订单无效
    //
    public function ajax_transfer($trade_id) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $trade = D('Trade')->field(true)->find($trade_id);
        if (!is_array($trade)) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 订单无效
        }

        if ($uid != $trade['uid']) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 用户所支付非本人订单
        }

        $Alipay = new AlipayApi;
        $html = $Alipay->transfer($trade_id, $trade['price'], C('PAY_PRODUCT_NAME'));
        $this->ajaxReturn(array("success" => true, "html" => $html));
    }
}
