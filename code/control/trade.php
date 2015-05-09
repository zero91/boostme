<?php

!defined('IN_SITE') && exit('Access Denied');

class tradecontrol extends base {
    public function __construct(&$get, &$post) {
        parent::__construct($get, $post);
        $this->load('trade');
        $this->load('material');
        $this->load('service');
    }

    // 订单首页
    public function ondefault() {
        $this->check_login();

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        //$pagesize = 1;
        $start = ($page - 1) * $pagesize;
        $total_trade_num = $_ENV['trade']->get_history_trade_num_by_uid($this->user['uid']);

        $trade_list = $_ENV['trade']->get_trade_by_uid($this->user['uid'], $start, $pagesize);
        foreach ($trade_list as &$t_for_trade) {
            $t_for_trade['trade_info'] = $this->get_one_trade_full($t_for_trade['trade_no']);
        }
        $departstr = split_page($total_trade_num, $pagesize, $page, "/trade/default?page=%s");
        include template('trade');
    }

    // 查看单个订单的详情
    public function onview() {
        $trade_no = $this->post['trade_no'];

        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        $trade_info_list = $this->get_one_trade_full($trade_no);

        include template('view_trade');
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

    // 当订单有变更时，重新计算订单价格
    private function recalc_trade($trade_no) {
        $trade_info_list = $this->get_one_trade_full($trade_no);
        $total_price = 0;
        foreach ($trade_info_list as $trade_info) {
            $total_price += $trade_info['target_info']['price'] * $trade_info['buy_num'];
        }
        $_ENV['trade']->update_trade_tot_price($trade_no, $total_price);
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================
    // @onajax_fetch_list    [获取用户历史订单]
    // @request type         [GET]
    //
    // @param[in]       page [页号，可选]
    //
    // @return          成功 [success为true, trade_list为历史订单列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    public function onajax_fetch_list() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $total_trade_num = $_ENV['trade']->get_history_trade_num_by_uid($this->user['uid']);
        $trade_list = $_ENV['trade']->get_trade_by_uid($this->user['uid'], $start, $pagesize);

        foreach ($trade_list as &$t_for_trade) {
            $t_for_trade['trade_info'] = $this->get_one_trade_full($t_for_trade['trade_no']);
        }
        $res['success'] = true;
        $res['trade_list'] = $trade_list;
        echo json_encode($res);
    }

    // @onajax_fetch_tradeno [获取订单号]
    // @request type         [GET]
    // @return          成功 [success为true, tradeno为订单号]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    public function onajax_fetch_tradeno() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'],
                                                         TRADE_STATUS_WAIT_BUYER_PAY);
        if (empty($trade)) {
            $trade_no = generate_tradeno($this->user['sid']);
            $_ENV['trade']->add_trade($trade_no, $this->user['uid'], $this->user['username']);
        } else {
            $trade_no = $trade['trade_no'];
        }
        $res['success'] = true;
        $res['tradeno'] = $trade_no;
        echo json_encode($res);
    }

    // @onajax_remove_item   [删除购物车物品]
    // @request type         [POST]
    //
    // @param[in]   trade_no [订单号]
    // @param[in]  target_id [待删除项的ID号]
    // @param[in]      type [待删除项的类型，为"service"或"material"]
    //
    // @return          成功 [success为true]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [无效参数]
    // @error            103 [用户无权删除该订单物品]
    // @error            104 [删除失败]
    public function onajax_remove_item() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $trade_no = $this->post['trade_no'];
        $target_id = $this->post['target_id'];
        $type = $this->post['type'];
        if (empty($trade_no) || empty($target_id) || empty($type)) {
            $res['success'] = false;
            $res['error'] = 102; // 无效参数
            echo json_encode($res);
            return;
        }

        // check permission
        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 103; // 用户无权查询该订单号
            echo json_encode($res);
            return;
        }

        $affected_rows = $_ENV['trade']->remove_trade_info($trade_no, $target_id, $type);
        if ($affected_rows > 0) {
            $res['success'] = true;
            $this->recalc_trade($trade_no);
        } else {
            $res['success'] = false;
            $res['error'] = 104; // 删除失败
        }
        echo json_encode($res);
    }

    // @onajax_add_item      [为购物车添加商品]
    // @request type         [POST]
    //
    // @param[in]  target_id [待添加项的ID号]
    // @param[in]       type [待添加项的类型]
    // @param[in]   quantity [待添加项的数量]
    //
    // @return          成功 [success为true, trade_no为订单号]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [无效参数]
    // @error            103 [添加失败]
    public function onajax_add_item() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $target_id = $this->post['target_id'];
        $type = $this->post['type'];
        $quantity = $this->post['quantity'];
        if (empty($target_id) || empty($type) || empty($quantity)) {
            $res['success'] = false;
            $res['error'] = 102; // 无效参数
            echo json_encode($res);
            return;
        }

        $trade = $_ENV['trade']->get_trade_by_uid_status($this->user['uid'],
                                                         TRADE_STATUS_WAIT_BUYER_PAY);
        if (empty($trade)) {
            $trade_no = generate_tradeno($this->user['sid']);
            $_ENV['trade']->add_trade($trade_no, $this->user['uid'], $this->user['username']);
        } else {
            $trade_no = $trade['trade_no'];
        }
        $affected_rows = $_ENV['trade']->add_trade_info($this->user['uid'],
                                                        $this->user['username'],
                                                        $trade_no,
                                                        $target_id,
                                                        $type,
                                                        $quantity);
        if ($affected_rows > 0) {
            $res['success'] = true;
            $res['trade_no'] = $trade_no;
            $this->recalc_trade($trade_no);
        } else {
            $res['success'] = false;
            $res['error'] = 103; // 添加失败
        }
        echo json_encode($res);
    }

    // @onajax_update_quantity [更新订单单个项目的数量]
    // @request type         [POST]
    //
    // @param[in]   trade_no [订单号]
    // @param[in]  target_id [待更改项的ID号]
    // @param[in]       type [待更改项的类型]
    // @param[in]   quantity [待更改项的新数量]
    //
    // @return          成功 [success为true]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [无效参数]
    // @error            103 [用户无权操作]
    // @error            104 [更新失败]
    public function onajax_update_quantity() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }
        $trade_no = $this->post['trade_no'];
        $target_id = $this->post['target_id'];
        $type = $this->post['type'];
        $quantity = $this->post['quantity'];
        if (empty($trade_no) || empty($target_id) || empty($type) || empty($quantity)) {
            $res['success'] = false;
            $res['error'] = 102; // 无效参数
            echo json_encode($res);
            return;
        }

        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 103; // 用户无权操作
            echo json_encode($res);
            return;
        }

        $affected_rows = $_ENV['trade']->update_trade_info($trade_no, $target_id, $type, $quantity);
        if ($affected_rows > 0) {
            $res['success'] = true;
            $this->recalc_trade($trade_no);
        } else {
            $res['success'] = false;
            $res['error'] = 104; // 更新失败
        }
        echo json_encode($res);
    }

    // @onajax_fetch_info    [获取单个订单的详细信息]
    // @request type         [GET]
    //
    // @param[in]   trade_no [订单号]
    //
    // @return          成功 [success为true]
    //                       [trade_status为订单的状态]
    //                       [trade_info_list为订单详细物品列表]
    //
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [无效参数]
    // @error            103 [用户无权操作]
    public function onajax_fetch_info() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }
        $trade_no = $this->post['trade_no'];
        if (empty($trade_no)) {
            $res['success'] = false;
            $res['error'] = 102; // 无效参数
            echo json_encode($res);
            return;
        }

        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($trade['uid'] != $this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 103; // 用户无权操作
            echo json_encode($res);
            return;
        }

        $trade_info_list = $this->get_one_trade_full($trade_no);

        $res['success'] = true;
        $res['trade_status'] = $trade['status'];
        $res['trade_info_list'] = $trade_info_list;
        echo json_encode($res);
    }
}

?>
