<?php

!defined('IN_SITE') && exit('Access Denied');

class tradecontrol extends base {

    function tradecontrol(&$get, &$post) {
        parent::__construct($get, $post);
        $this->load('trade');
        $this->load('material');
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

            $trade_list = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_WAIT_BUYER_PAY);
            if (count($trade_list) > 0) {
                $trade = $trade_list[0];
            } else {
                $trade = false;
            }

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
            //$this->message("成功加入到购物车", "trade/buy_now");
            $this->jump("trade/buy_now");
        } else {
            $trade_list = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_WAIT_BUYER_PAY);

            if (count($trade_list) > 0) {
                $trade = $trade_list[0];
                $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade['trade_no']);
            } else {
                $trade_info_list = array();
            }
            include template('trade_info_list');
        }
    }

    function onhistory() {
        $page = max(1, intval($this->get[2]));
        $total_trade_num = $_ENV['trade']->get_history_trade_num_by_uid($this->user['uid']);

        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;
        $trade_list = $_ENV['trade']->get_detailed_trade_by_uid($this->user['uid'], $start, $pagesize);
        $departstr = page($total_trade_num, $pagesize, $page, "trade/history");
        include template('trade_history');
    }

    function onajaxaccess_material() {
        $trade_no = $this->get[2];
        if (empty($trade_no)) {
            exit('-1');
        }

        $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade_no);
        $html_text = "<table style=\"text-align:center;\">";
        $html_text .= "<tr style=\"background-color:#f2f7ff;\">";
        $html_text .= "<th width=\"60%\" style=\"text-align:center;\">订单详情</th>";
        $html_text .= "<th width=\"20%\" style=\"text-align:center;\">链接</th>";
        $html_text .= "<th width=\"20%\" style=\"text-align:center;\">密码</th>";
        $html_text .= "</tr>";

        foreach($trade_info_list as $trade_info) {
            $material = $_ENV['material']->get($trade_info['mid']);

            $html_text .= "<tr><td>{$material['title']}</td>";
            $html_text .= "<td><a href=\"{$material['site_url']}\">{$material['site_url']}</a></td>";
            $html_text .= "<td>{$material['access_code']}</td></tr>";
        }
        $html_text .= "</table>";
        echo $html_text;
    }

    function onajaxdelete_material() {
        $trade_no = $this->get[2];
        $mid = $this->get[3];
        if (empty($trade_no) || empty($mid)) {
            exit('-1');
        }

        // check permission
        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            exit('-2');
        }

        $trade_info = $_ENV['trade']->get_trade_info_by_trade_no_mid($trade_no, $mid);

        $affected_rows = $_ENV['trade']->remove_trade_info($trade_no, $mid);
        if ($affected_rows > 0) {
            $new_tot_price = round($trade['tot_price'] - $trade_info['price'] * $trade_info['buy_num'], 2);
            if ($trade['goods_num'] > 1) {
                $_ENV['trade']->update_trade($trade_no, $new_tot_price, $trade['goods_num'] - 1);
            } else {
                $_ENV['trade']->remove_trade($trade_no);
            }
            exit('1');
        }
        exit('0');
    }

    function onajaxupdate_quantity() {
        $trade_no = $this->get[2];
        $mid = $this->get[3];
        $buy_num  = $this->get[4];

        if (empty($trade_no) || empty($mid) || empty($buy_num)) {
            exit('-1');
        }

        // check permission
        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            exit('-2');
        }

        $trade_info = $_ENV['trade']->get_trade_info_by_trade_no_mid($trade_no, $mid);

        $new_tot_price = round($trade['tot_price'] - $trade_info['price'] * $trade_info['buy_num'] + $buy_num * $trade_info['price'], 2);

        $_ENV['trade']->update_trade($trade_no, $new_tot_price, $trade['goods_num']);

        $affected_rows = $_ENV['trade']->update_trade_info_buy_num($trade_no, $mid, $buy_num);
        if ($affected_rows > 0) {
            exit('1');
        }
        exit('0');
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
