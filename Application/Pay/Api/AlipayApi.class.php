<?php
namespace Pay\Api;
use Pay\Api\Api;
use Pay\Service\AlipayService;

class AlipayApi extends Api {

    // @brief  _init  构造方法，实例化操作模型
    //
    public function _init() {
        $this->model = new AlipayService();
    }

    // @brief  transfer         注册一个新用户
    //
    // @param  string $username 用户名
    // @param  string $password 用户密码
    // @param  string $email    用户邮箱
    // @param  string $mobile   用户手机号码
    //
    // @return integer          注册成功-用户信息，注册失败-错误编号
    //
    public function transfer($trade_id, $price, $product_name) {
        return $this->model->transfer($trade_id, $price, $product_name);
    }

    // @brief  returns          获取支付前端返回页面信息
    //
    // @return 成功 - array("success" => true, 获取信息字段)
    //         失败 - array("success" => false)
    //
    public function returns() {
        return $this->model->returns();
    }

    // @brief  notify           获取支付后台返回通知信息
    //
    // @return 成功 - array("success" => true, 获取信息字段)
    //         失败 - array("success" => false)
    //
    public function notify() {
        return $this->model->notify();
    }

    // @brief  notify           通知支付宝相应的订单已经能够发货
    //
    // @return 成功 - array("is_success" => true)
    //         失败 - array("is_success" => false) 或为空数组
    //
    public function send($transaction_id, $logistics_name, $invoice_no, $transport_type) {
        return $this->model->send();
    }
}
