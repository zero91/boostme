<?php
namespace Home\Controller;

class TradeController extends HomeController {
    public function index(){
        /*
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
        */
        $this->display();
    }

    public function view() {
        /*
        $trade_no = $this->post['trade_no'];

        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        $trade_info_list = $this->get_one_trade_full($trade_no);
        include template('view_trade');
        */
        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================
    // @brief  ajax_fetch_list  获取用户历史订单
    // @request  GET
    // @param  integer  page  页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 历史订单列表)
    //              失败 - array("success" => false, "error" => 相应的错误码)
    //
    // @error  101 - 用户尚未登录
    //
    public function ajax_fetch_list($page = 1) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $field = "id, price, item_num, status, create_time, update_time";
        $trade_list = D('Trade')->history($uid, $page, $field);
        $this->ajaxReturn(array("success" => true, "list" => $trade_list));
    }

    // @brief  ajax_fetch_id  获取订单号
    // @request  GET
    // @ajaxReturn  成功 - array("success" => true, "id" => 订单号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 创建新订单号失败
    //
    public function ajax_fetch_id() {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $id = D('Trade')->generate_id($uid);
        if ($id > 0) {
            $this->ajaxReturn(array("success" => true, "id" => $id));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 创建新订单号失败
        }
    }

    // @brief  ajax_remove_item  删除购物车物品
    // @request  POST
    //
    // @param  integer  trade_id   订单号
    // @param  integer  item_id    待删除项的ID号
    // @param  integer  item_type  待删除项的类型
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 订单号不存在
    // @error  103 - 用户无权操作该订单
    // @error  104 - 订单号中不存在该项产品
    // @error  105 - 订单已锁定，不能更改
    //
    public function ajax_remove_item($trade_id, $item_id, $item_type) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $ret = D('TradeInfo')->remove($uid, $trade_id, $item_id, $item_type);
        if ($ret > 0) {
            $item_cnt = D('TradeInfo')->where(array("trade_id" => $trade_id))->count();
            if ($item_cnt == 0) {
                D('Trade')->save(array("id" => $trade_id, "status" => 0));
            }
            $this->ajaxReturn(array("success" => true));
        } else {
            $res = array("success" => false);
            switch ($ret) {
                case 0: $res['error'] = 104; break; // 订单号中不存在该项产品
                case -1: $res['error'] = 102; break; // 订单号不存在
                case -2: $res['error'] = 103; break; // 用户无权操作该订单号
                case -3: $res['error'] = 105; break; // 订单已锁定，不能更改
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_add_item  新增或更新产品
    // @request  POST
    //
    // @param  integer  trade_id  待添加项的ID号
    // @param  integer  item_id   待添加项的类型
    // @param  integer  quantity  待添加项的数量
    //
    // @ajaxReturn  成功 - array("success" => true, "trade_id" => 订单号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 无效参数
    // @error  103 - 添加失败
    // @error  104 - 生成订单失败
    //
    public function ajax_add_item($item_id, $item_type, $quantity = 1) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $trade_id = D('Trade')->generate_id($uid);
        if ($trade_id <= 0) {
            $this->ajaxReturn(array("success" => false, "error" => 104)); // 生成订单失败
        }

        $ret = D('TradeInfo')->buy($uid, $trade_id, $item_id, $item_type, $quantity);
        if ($ret > 0) {
            // TODO 确保订单价格计算正确
            D('Trade')->save(array("id" => $trade_id, "status" => TRADE_STATUS_WAIT_BUYER_PAY));
            $this->ajaxReturn(array("success" => true, "trade_id" => $trade_id));
        } else {
            $res = array("success" => false);
            switch ($ret) {
                case 0: $res['error'] = 103; break; // 添加失败
                case -1: $res['error'] = 104; break; // 订单号不存在
                case -2: $res['error'] = 104; break; // 用户无权操作该订单号
                case -3: $res['error'] = 104; break; // 订单已锁定，不能更改
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_fetch_info  获取单个订单的详细信息
    // @request  GET
    // @param  integer  trade_id  订单号
    //
    // @ajaxReturn  成功 - array(
    //                        "success"     => true,
    //                        "price"       =>  订单消费总额,
    //                        "item_num"    =>  订单内产品种类数量,
    //                        "status"      =>  订单状态,
    //                        "update_time" =>  订单最后更新时间,
    //                        "list"        =>  订单产品详细信息)
    //
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 订单号不存在
    // @error  103 - 用户无权操作
    //
    public function ajax_fetch_info($trade_id) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $ret = D('TradeInfo')->detail($uid, $trade_id);
        if (is_array($ret)) {
            $trade = D('Trade')->field("price, item_num, status, update_time")->find($trade_id);
            $this->ajaxReturn(array_merge(array("success" => true, "list" => $ret), $trade));
        } else {
            $res = array("success" => false);
            switch ($ret) {
                case -1: $res['error'] = 102; break; // 订单号不存在
                case -2: $res['error'] = 103; break; // 用户无权操作该订单号
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }
}
