<?php

!defined('IN_SITE') && exit('Access Denied');

class tradecontrol extends base {
    public function __construct(&$get, &$post) {
        parent::__construct($get, $post);
        $this->load('trade');
        $this->load('material');
        $this->load('service');
    }

    public function onbuy_now() {
        if (!$this->user['uid']) {
            $this->jump("user/login");
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $target_id = $this->post['target_id'];
            $type = $this->post['type'];
            $quantity = $this->post['quantity'];

            $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_WAIT_BUYER_PAY);
            if (!empty($trade)) {
                $trade_no = $trade['trade_no'];
                $_ENV['trade']->add_trade_info($this->user['uid'], $this->user['username'], $trade_no, $target_id, $type, $quantity);
            } else {
                $trade_no = $_ENV['trade']->create_trade_no($this->user['sid']);
                $_ENV['trade']->add_trade_info($this->user['uid'], $this->user['username'], $trade_no, $target_id, $type, $quantity);
                $_ENV['trade']->add_trade($trade_no, $this->user['uid'], $this->user['username']);
            }
            $this->jump("trade/buy_now");
        } else {
            $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'], TRADE_STATUS_WAIT_BUYER_PAY);
            $trade_info_list = $this->get_one_trade_full($trade['trade_no']);
            include template('trade_info_list');
        }
    }

    // 删除购物车物品
    public function ondelete_goods() {
        $trade_no = $this->post['trade_no'];
        $target_id = $this->post['target_id'];
        $type = $this->post['type'];

        if (empty($trade_no) || empty($target_id) || empty($type)) {
            echo json_encode(array("error" => "101")); // 无效参数
            return;
        }
        // check permission
        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            echo json_encode(array("error" => "102")); // 无权操作
            return;
        }

        $affected_rows = $_ENV['trade']->remove_trade_info($trade_no, $target_id, $type);
        if ($affected_rows > 0) {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("error" => 103)); // Unknow error
        }
    }

    public function onhistory() {
        $this->check_login();

        $page = max(1, intval($this->get[2]));
        $total_trade_num = $_ENV['trade']->get_history_trade_num_by_uid($this->user['uid']);

        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;
        $trade_list = $_ENV['trade']->get_trade_by_uid($this->user['uid'], $start, $pagesize);
        foreach ($trade_list as &$t_for_trade) {
            $t_for_trade['trade_info'] = $this->get_one_trade_full($t_for_trade['trade_no']);
        }

        $departstr = page($total_trade_num, $pagesize, $page, "trade/history");
        include template('trade_history');
    }

    public function onajaxaccess_trade_info() {
        $trade_no = $this->get[2];
        if (empty($trade_no)) {
            exit('-1');
        }

        $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade_no);
        $html_text = "<table style=\"text-align:center;\" width=\"100%\">";
        $html_text .= "<tr style=\"background-color:#f2f7ff;\">";
        $html_text .= "<th width=\"60%\" style=\"text-align:center;\">订单详情</th>";
        $html_text .= "<th width=\"40%\" style=\"text-align:center;\">信息</th>";
        $html_text .= "</tr>";

        foreach($trade_info_list as $trade_info) {
            if ($trade_info['type'] == TRADE_TARGET_MATERIAL) {
                $material = $_ENV['material']->get($trade_info['target_id']);

                $html_text .= "<tr><td>{$material['title']}</td>";
                $html_text .= "<td style=\"text-align:left;\"><p>链接：<a href=\"{$material['site_url']}\">{$material['site_url']}</a></p>";
                $html_text .= "<p>密码：{$material['access_code']}</p></td></tr>";
            } else if ($trade_info['type'] == TRADE_TARGET_SERVICE) {
                $service = $_ENV['service']->get_by_id($trade_info['target_id']);
                $service_user = $_ENV['user']->get_by_uid($service['uid']);

                $html_text .= "<tr><td>{$service['profile']}</td>";
                $html_text .= "<td style=\"text-align:left;\"><p>手机号：{$service_user['phone']}</p>";
                $html_text .= "<p>QQ：  {$service_user['qq']}</p>";
                $html_text .= "<p>微信号：{$service_user['wechat']}</p></td></tr>";
            }
        }
        $html_text .= "</table>";
        echo $html_text;

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

    // 获取一次订单的详细信息
    private function get_one_trade_full($trade_no) {
        $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade_no);
        foreach ($trade_info_list as &$t_trade_info) {
            if ($t_trade_info['type'] == TRADE_TARGET_MATERIAL) {
                $t_trade_info['target_info'] = $_ENV['material']->get($t_trade_info['target_id']);
            } else if ($t_trade_info['type'] == TRADE_TARGET_SERVICE) {
                $t_trade_info['target_info'] = $_ENV['service']->get_by_id($t_trade_info['target_id']);
            }
        }
        return $trade_info_list;
    }
}

?>
