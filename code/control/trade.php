<?php

!defined('IN_SITE') && exit('Access Denied');

class tradecontrol extends base {

    function tradecontrol(&$get, &$post) {
        parent::__construct($get, $post);
        $this->load('trade');
    }

    // 财付通转账
    function onbuy_now() {
        if (!$this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        if (isset($this->post['submit'])) {
            $mid = $this->post['mid'];
            $title = $this->post['title'];
            $price = $this->post['price'];
            $quantity = $this->post['quantity'];

            $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_UNPAID);
            $trade_no = cutstr($this->user['sid'] . "{$this->base->time}" . random(32), 32, '');

            if ($trade) {
                $trade_no = $trade['trade_no'];
                $trade_info = $_ENV['trade']->get_trade_info_by_trade_no_mid($trade_no, $mid);

                if ($trade_info) {
                    $_ENV['trade']->update_trade_info($trade_no, $mid, $title, $price, $quantity + $trade_info['buy_num']);

                    // 资料价格有可能已经变动了
                    $tot_price = $trade['tot_price'] + ($price - $trade_info['price']) * $trade_info['buy_num'] + $price * $quantity;
                    $_ENV['trade']->update_trade($trade_no, $tot_price, $trade['goods_num']);
                } else {
                    $_ENV['trade']->add_trade_info($trade_no, $mid, $title, $price, $quantity);
                    $_ENV['trade']->update_trade($trade_no, $trade['tot_price'] + $price * $quantity, $trade['goods_num'] + 1);
                }
            } else {
                $tot_price = $price * $quantity;
                $_ENV['trade']->add_trade_info($trade_no, $mid, $title, $price, $quantity);
                $_ENV['trade']->add_trade($trade_no, $this->user['uid'], $this->user['username'], $tot_price, 1);
            }
        }
        $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_UNPAID);
        $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade['trade_no']);

        include template('trade_info_list');
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
