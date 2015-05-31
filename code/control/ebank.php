<?php

!defined('IN_SITE') && exit('Access Denied');

class ebankcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('ebank');
        $this->load('trade');

        $this->load('material');
        $this->load('service');
        $this->load('user');
        $this->load("user_ebank");
        $this->load('withdraw');
    }

    // 用户余额套现页面
    public function onwithdraw() {
        $this->check_login();
        $ebank_account_list = $_ENV['user_ebank']->get_by_uid($this->user['uid']);
        include template("withdraw");
    }

    // 验证账户
    public function onverify_account() {
        $verified_trade = $_ENV['trade']->get_user_verified_trade($this->user['uid']);
        if (empty($verified_trade)) {
            $out_trade_no = $_ENV['trade']->create_trade_no($this->user['sid']); // 订单号
            $_ENV['trade']->add_trade($out_trade_no, $this->user['uid'], $this->user['username'], 0.01);
        } else {
            $out_trade_no = $verified_trade['trade_no'];
        }

        $product_name = "Boostme支付宝账户验证"; // 商品名称
        $order_price = "0.01"; // 价格
        $_ENV['ebank']->alipaytransfer($out_trade_no, $order_price, $product_name);
    }

    // 页面返回回调
    public function onalipayreturn() {
        $result = $_ENV['ebank']->alipayreturn();
        $process_result = $this->process_alipay_result($result);

        $this->jump("trade/history");
        /*
        if ($process_result == 'success') {
            $this->jump("trade/history");
        } else{
            $this->message("付款失败，请重试", "trade/buy_now");
        }
         */
    }

    public function onalipaynotify() {
        $result = $_ENV['ebank']->alipaynotify();
        $process_result = $this->process_alipay_result($result);
        echo $process_result;
    }

    function send_msg($trade_no, $status) {
        $trade_info_list = array();

        if (!empty($trade_no)) {
            $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade_no);
        }

        $user_material = array();
        foreach ($trade_info_list as $trade_info) {
            $material = $_ENV['material']->get($trade_info['mid']);

            if (!array_key_exists($material['uid'], $user_material)) {
                $user_material[$material['uid']] = array();
            }
            $user_material[$material['uid']][] = $material;
        }

        $subject = "Boostme";
        if ($status == 'WAIT_SELLER_SEND_GOODS') {
            $subject = "您有了新的订单，订单号：" . $trade_no;

        } else if ($status == 'WAIT_BUYER_CONFIRM_GOODS') {
            $subject = "订单号 " . $trade_no . " 状态发生了改变，新状态：等待买家确认收货";

        } else if ($status == 'TRADE_FINISHED') {
            $subject = "订单号 " . $trade_no . " 状态发生了改变，新状态：交易成功";
        }

        foreach ($user_material as $uid => $material_list) {
            $content = "订购资料列表：";
            foreach ($material_list as $material) {
                $content .= '<a href="' . url("material/view/{$material['id']}", 1) . '">' . $material['title'] . '</a><br/>';
            }
            $this->send('', 0, $uid, $subject, $content);
        }
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
        if (!$this->user['uid']) {
            $this->message("您无权执行该操作!", "STOP");
            exit;
        }

        $out_trade_no = $this->post["trade_no"];        // 获取提交的订单号
        $product_name = $this->post["product_name"];    // 获取提交的商品名称
        $order_price = $this->post["order_price"];      // 获取提交的商品价格
        $trade_mode = $this->post["trade_mode"];        // 支付方式

        $total_fee = $order_price * 100; // 商品价格（包含运费），以分为单位
        $desc = "$product_name"; // 商品名称
        $desc = cutstr($desc, 32, '');


        $_ENV['ebank']->tenpaytransfer($out_trade_no, $total_fee, $desc, $trade_mode);
    }

    // 财付通回调
    function ontenpayreturn() {
        $result = $_ENV['ebank']->tenpayreturn();

        if ($result['is_tenpay_sign'] && $result['trade_mode'] == "1" && $result['trade_state'] == '0') {
            $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "tenpay", $trade['trade_mode']);
            $this->message("付款成功", "material/categorylist");
        } else {
            $this->message("付款失败，请重试", "trade/buy_now");
        }
    }

    function ontenpaynotify() {
        $result = $_ENV['ebank']->tenpaynotify();

        if ($result['is_tenpay_sign'] && $result['succeed']) {
            $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "tenpay", $trade['trade_mode']);
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    // 处理alipay的返回结果
    private function process_alipay_result($result) {
        $this->write_to_log($result);
        if ($result['succeed']) {
            if ($result['trade_status'] == 'WAIT_BUYER_PAY') {
                //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                // 暂不需要做任何处理
                //$affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_PAY);

            } else if ($result['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
                //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                $trade = $_ENV['trade']->get_trade_info_by_trade_no($result['trade_no']);
                if ($trade['status'] < TRADE_STATUS_WAIT_SELLER_SEND_GOODS) {
                    $affected_rows = $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['buyer_email'], $result['total_fee'], $result['discount'], "alipay", "1", TRADE_STATUS_WAIT_SELLER_SEND_GOODS);
                }

            } else if ($result['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
                //该判断表示卖家已经发了货，但买家还没有做确认收货的操作
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                $trade = $_ENV['trade']->get_trade_info_by_trade_no($result['trade_no']);
                if ($trade['status'] < TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS) {
                    $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS);
                }

            } else if ($result['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                $trade = $_ENV['trade']->get_trade_info_by_trade_no($result['trade_no']);
                if ($trade['status'] < TRADE_STATUS_FINISHED) {
                    $affected_rows = $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['buyer_email'], $result['total_fee'], $result['discount'], "alipay", "1", TRADE_STATUS_FINISHED);
                    if ($affected_rows > 0) {
                        $this->send_msg($result['trade_no'], $result['trade_status']);
                    }

                    $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($result['trade_no']);
                    if (count($trade_info_list) == 0) {
                        $trade = $_ENV['trade']->get_trade_by_trade_no($result['trade_no']);

                        if ($trade['tot_price'] == '0.01') { //符合：没有一条商品，价格为0.01的交易，即为用户支付宝验证交易
                            $_ENV['user_ebank']->add($trade['uid'], "alipay", $result['buyer_email']);
                        }
                    }

                    foreach ($trade_info_list as $trade_info) {
                        if ($trade_info['type'] == TRADE_TARGET_MATERIAL) {
                            $_ENV['material']->update_sold_num($trade_info['target_id'], $trade_info['buy_num']);
                            $material = $_ENV['material']->get($trade_info['target_id']);
                            $_ENV['user']->update_balance($material['uid'], $trade_info['buy_num'] * $material['price']);

                        } else if ($trade_info['type'] == TRADE_TARGET_SERVICE) {
                            $_ENV['service']->update_service_num($trade_info['target_id'], $trade_info['buy_num']);
                            //$service = $_ENV['service']->get_by_id($trade_info['target_id']);
                            //$_ENV['user']->update_balance($service['uid'], $trade_info['buy_num'] * $trade_info['price']);
                        }
                    }
                }

            } else if ($result['trade_status'] == 'TRADE_CLOSED') {
                $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_CLOSED);
            }
            return 'success';
        } else {
            runlog('alipay', "[WARNING]付款失败", 0);
        }
        return 'fail';
    }

    private function write_to_log($result) {
        $log  = "succeed=[" . $result['succeed'] . "], ";
        $log .= "trade_no=[" . $result['trade_no'] . "], ";
        $log .= "transaction_id=[" .  $result['transaction_id'] . "], ";
        $log .= "trade_status=[" .  $result['trade_status'] . "], ";
        $log .= "notify_id=[" . $result['notify_id'] . "], ";
        $log .= "total_fee=[" . $result['total_fee'] . "], ";
        $log .= "discount=[" .  $result['discount'] . "], ";
        $log .= "buyer_email=[" . $result['buyer_email'] . "], ";
        $log .= "buyer_id=[" . $result['buyer_id'] . "], ";
        $log .= "is_total_fee_adjust=[" . $result['is_total_fee_adjust'] . "], ";
        $log .= "receive_name=[" . $result['receive_name'] . "], ";
        $log .= "receive_address=[" . $result['receive_address'] . "], ";
        $log .= "receive_zip=[" . $result['receive_zip'] . "], ";
        $log .= "receive_phone=[" . $result['receive_phone'] . "], ";
        $log .= "receive_mobile=[" . $result['receive_mobile'] . "], ";
        $log .= "gmt_create=[" . $result['gmt_create'] . "], ";
        $log .= "gmt_payment=[" . $result['gmt_payment'] . "]";
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_alipay_transfer [支付宝支付]
    // @request type           [GET]
    // @param[in]     trade_no [订单号]
    // @return            成功 [success为true, html_text为跳转到支付宝的HTML/JS代码]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    // @error              102 [用户所支付的非本人订单]
    public function onajax_alipay_transfer() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $trade_no = $this->post['trade_no'];
        $trade = $_ENV['trade']->get_trade_by_trade_no($trade_no);
        if ($this->user['uid'] != $trade['uid']) {
            $res['success'] = false;
            $res['error'] = 102; // 用户所支付的非本人订单
            echo json_encode($res);
            return;
        }

        $out_trade_no = $trade['trade_no'];
        $product_name = "Boostme";
        $order_price = $trade['tot_price'];
        $html_text = $_ENV['ebank']->alipay_transfer($out_trade_no, $order_price, $product_name);

        $res['success'] = true;
        $res['html_text'] = $html_text;
        echo json_encode($res);
    }

    // @onajax_fetch_account   [获取用户已经验证的支付宝账号]
    // @request type           [GET]
    // @return            成功 [success为true, account_list为用户认证过的支付宝账户]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    public function onajax_fetch_account() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $res['success'] = true;
        $res['account_list'] = $_ENV['user_ebank']->get_by_uid($this->user['uid']);
        echo json_encode($res);
    }

    // @onajax_fetch_history   [获取用户历史申请套现列表]
    // @request type           [GET]
    // @return            成功 [success为true, withdraw_list为用户历史套现列表]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    public function onajax_fetch_history() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }
        $res['success'] = true;
        $res['withdraw_list'] = $_ENV['withdraw']->get_by_uid($this->user['uid']);
        echo json_encode($res);
    }

    // @onajax_add_withdraw     [获取用户历史申请套现列表]
    // @request type            [POST]
    //
    // @param[in]         money [套现金额]
    // @param[in] ebank_account [套现账户]
    // @param[in]    ebank_type [套现账户类型，暂定为：alipay]
    //
    // @return             成功 [success为true, withdraw_list为用户历史套现列表]
    //                     失败 [success为false, error为相应的错误码]
    //
    // @error               101 [用户尚未登录]
    // @error               102 [账户余额不足]
    // @error               103 [无效参数]
    // @error               104 [添加失败]
    public function onajax_add_withdraw() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $money = $this->post['money'];
        if ($money > $this->user['balance']) {
            $res['success'] = false;
            $res['error'] = 102; // 账户余额不足
            echo json_encode($res);
            return;
        }

        $ebank_account = $this->post['ebank_account'];
        $ebank_type = $this->post['ebank_type'];
        if (empty($ebank_account) || empty($ebank_type)) {
            $res['success'] = false;
            $res['error'] = 103; // 无效参数
            echo json_encode($res);
            return;
        }

        $id = $_ENV['withdraw']->add($this->user['uid'], $money, $ebank_type, $ebank_account);
        if ($id > 0) {
            $affected_rows = $_ENV['user']->update_balance($this->user['uid'], -$money);
            if ($affected_rows > 0) {
                $res['success'] = true;
                echo json_encode($res);
                return;
            } else {
                $_ENV['withdraw']->remove_by_id($id);
            }
        }
        $res['success'] = false;
        $res['error'] = 104; // 添加失败
        echo json_encode($res);
    }
}

?>
