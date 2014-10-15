<?php

!defined('IN_SITE') && exit('Access Denied');

class ordercontrol extends base {

    function ordercontrol(& $get, & $post) {
        parent::__construct($get, $post);
        //$this->load('ebank');
    }

    function onbuy_now() {
        if ($this->user['uid'] == 0) {
            $this->message("请先登录!", "user/login");
        }

        $mid = $this->post['mid'];
        $title = $this->post['title'];
        $price = $this->post['price'];
        $quantity = $this->post['quantity'];

        include template('order');
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
            $recharge_money = intval($this->post['money']);
            $recharge_money = 1;

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
