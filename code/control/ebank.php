<?php

!defined('IN_SITE') && exit('Access Denied');

class ebankcontrol extends base {

    function ebankcontrol(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('ebank');
    }

    // 财付通回调
    function ontenpayback() {
        if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
            $this->message("充值成功", "user/score");
        } else {
            $this->message("服务器繁忙，请稍后再试!", 'STOP');
        }
    }

    // 财付通转账
    function ontenpaytransfer() {
        if (isset($this->post['submit'])) {
            if (!$this->user['uid']) {
                $this->message("您无权执行该操作!", "STOP");
                exit;
            }

            //$out_trade_no = $this->post["order_no"];        // 获取提交的订单号
            $product_name = $this->post["product_name"];    // 获取提交的商品名称
            $order_price = $this->post["order_price"];      // 获取提交的商品价格
            $trade_mode = $this->post["trade_mode"];        // 支付方式

            $out_trade_no = "123321";

            $total_fee = $order_price * 100; // 商品价格（包含运费），以分为单位

            $desc = "商品：$product_name"; // 商品名称

            $_ENV['ebank']->tenpaytransfer($out_trade_no, $total_fee, $desc, $trade_mode);
        }
    }

    // 财付通回调
    function ontenpayreturn() {




    }

    // 支付宝回调
    function onaliapyback() {
        if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
            //$credit2 = $_GET['total_fee'] * $this->setting['recharge_rate'];
            //$this->credit($this->user['uid'], 0, $credit2, 0, "支付宝充值");
            $this->message("充值成功", "user/score");
        } else {
            $this->message("服务器繁忙，请稍后再试!", 'STOP');
        }
    }

    // 支付宝转账
    function onaliapytransfer() {
        if (isset($this->post['submit'])) {
            $recharge_money = intval($this->post['money']);
            if (!$this->user['uid']) {
                $this->message("您无权执行该操作!", "STOP");
                exit;
            }
            if (!$this->setting['recharge_open']) {
                $this->message("财富充值服务已关闭，如有问题，请联系管理员!", "STOP");
            }
            if ($recharge_money <= 0 || $recharge_money > 20000) {
                $this->message("输入充值金额不正确!充值金额必须为整数，且单次充值不超过20000元!", "STOP");
                exit;
            }
            $_ENV['ebank']->aliapytransfer($recharge_money);
        }
    }
}

?>
