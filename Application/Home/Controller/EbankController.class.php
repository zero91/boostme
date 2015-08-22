<?php
namespace Home\Controller;

class EbankController extends HomeController {
    public function index(){
    }

    // 用户余额套现页面
    public function onwithdraw() {
        // TODO
        /*
        $this->check_login();
        $ebank_account_list = $_ENV['user_ebank']->get_by_uid($this->user['uid']);
        include template("withdraw");
        */
    }

    // 验证账户
    public function onverify_account() {
        // TODO

        /*
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
        */
    }

    // @onajax_fetch_account   [获取用户已经验证的支付宝账号]
    // @request type           [GET]
    // @return            成功 [success为true, account_list为用户认证过的支付宝账户]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    public function onajax_fetch_account() {
        // TODO
        /*
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
        */
    }

    // @onajax_fetch_history   [获取用户历史申请套现列表]
    // @request type           [GET]
    // @return            成功 [success为true, withdraw_list为用户历史套现列表]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    public function onajax_fetch_history() {
        // TODO
        /*
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
        */
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
        // TODO
        /*
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
        */
    }
}
