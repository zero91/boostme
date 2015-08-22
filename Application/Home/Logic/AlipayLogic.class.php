<?php
namespace Home\Logic;

class AlipayLogic extends BaseLogic {

    protected $_validate = array();

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected function _init() {
        $this->tablePrefix = C('DB_PREFIX');
        $this->tableName = "trade";
    }

    // @brief  process  处理alipay的返回结果
    //
    // @param  array    支付宝返回的信息数组
    // @return boolean  处理成功 - true，处理失败 - false
    //
    public function process($result) {
        if (!$result['succeed']) {
            return false;
        }

        $trade = $this->field(true)->find($result['trade_no']);
        if (!is_array($trade)) {
            return false;
        }

        if ($result['trade_status'] == 'WAIT_BUYER_PAY') {
            // 该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款
            // 判断该笔订单是否在商户网站中已经做过处理
            // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            // 如果有做过处理，不执行商户的业务程序

            // 订单已经确定，不能再更改，更新状态
            if ($trade['status'] == 0) {
                $this->where(array("id" => $trade['id']))
                     ->save(array('status' => TRADE_STATUS_WAIT_BUYER_PAY));
            }
        } else if ($result['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
            // 该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货
            // 判断该笔订单是否在商户网站中已经做过处理
            // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            // 如果有做过处理，不执行商户的业务程序

            if ($trade['status'] == 0 || $trade['status'] == TRADE_STATUS_WAIT_BUYER_PAY) {
                $data = array(
                    "id"              => $trade['id'],
                    "status"          => TRADE_STATUS_WAIT_SELLER_SEND_GOODS,
                    "trade_total_fee" => $result['total_fee'],
                    "trade_discount"  => $result['discount'],
                    "trade_type"      => 1, // 支付类型(1:alipay, 2:tenpay)
                    "trade_mode"      => 2, // 支付方式(1:即时到账, 2:担保交易)
                    "transaction_id"  => $result['transaction_id'],
                    "pay_account"     => $result['buyer_email'],
                );
                $this->save($data);
            }
        } else if ($result['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
            // 该判断表示卖家已经发了货，但买家还没有做确认收货的操作
            // 判断该笔订单是否在商户网站中已经做过处理
            // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            // 如果有做过处理，不执行商户的业务程序

            if ($trade['status'] == 0
                            || $trade['status'] == TRADE_STATUS_WAIT_BUYER_PAY
                            || $trade['status'] == TRADE_STATUS_WAIT_SELLER_SEND_GOODS) {
                $data = array(
                    "id"     => $trade['id'],
                    "status" => TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS
                );
                $this->save($data);
            }
        } else if ($result['trade_status'] == 'TRADE_FINISHED') {
            // 判断该笔订单是否在商户网站中已经做过处理
            // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
            // 如果有做过处理，不执行商户的业务程序

            if ($trade['status'] == 0
                            || $trade['status'] == TRADE_STATUS_WAIT_BUYER_PAY
                            || $trade['status'] == TRADE_STATUS_WAIT_SELLER_SEND_GOODS
                            || $trade['status'] == TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS) {
                $trade_mode = 2;
                if ($trade['status'] == TRADE_STATUS_WAIT_BUYER_PAY) {
                    $trade_mode = 1;
                }

                $data = array(
                    "id"              => $trade['id'],
                    "status"          => TRADE_STATUS_FINISHED,
                    "trade_total_fee" => $result['total_fee'],
                    "trade_discount"  => $result['discount'],
                    "trade_type"      => 1, // 支付类型(1:alipay, 2:tenpay)
                    "trade_mode"      => $trade_mode, // 支付方式(1:即时到账, 2:担保交易)
                    "transaction_id"  => $result['transaction_id'],
                    "pay_account"     => $result['buyer_email'],
                );
                $this->save($data);
                // TODO 邮件通知用户交易已成功
                // TODO 更新订单内各产品的购买量等信息
            }
        } else if ($result['trade_status'] == 'TRADE_CLOSED') {
            $data = array(
                "id"     => $trade['id'],
                "status" => TRADE_STATUS_CLOSED
            );
            $this->save($data);
        }
        return true;
    }
}
