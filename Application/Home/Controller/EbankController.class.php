<?php
namespace Home\Controller;

class EbankController extends HomeController {

    // 我的钱包
    public function index() {
        $uid = is_login();
        if ($uid > 0) {
            $user = D('User')->where(array("uid" => $uid))->field("balance")->find();

            $account_list = D('UserEbank')->field(true)
                                          ->where(array("uid" => $uid))
                                          ->order("isdefault DESC, update_time DESC")
                                          ->select();

            $this->assign("ebank_account_list", $account_list);
            $this->assign("user", $user);
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_withdraw     [获取用户历史申请套现列表]
    // @request  POST
    //
    // @param  integer  $money          套现金额
    // @param  string   $ebank_account  套现账户
    // @param  integer  $ebank_type     套现账户类型
    //
    // @ajaxReturn  成功 => array("success" => true)
    //              失败 => array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  账户余额不足
    // @error  103  无效参数
    // @error  104  添加失败
    // @error  105  更新失败
    //
    public function ajax_withdraw($ebank_account, $ebank_type, $money) {
        // TODO 防止用户多次重复提交，导致绕过余额的判断，而多套现金额
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if ($money <= 0) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 无效参数
        }

        $balance = D('User')->where(array("uid" => $uid))->getField("balance");
        if ($money > $balance) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 账户余额不足
        }

        $remain = number_format($balance - $money, 2, '.', '');
        if (!D('User')->create(array("uid" => $uid, "balance" => $remain)) || !D('User')->save()) {
            $this->ajaxReturn(array("success" => false, "error" => 105)); // 更新失败
        }

        $data = array(
            "uid"           => $uid,
            "money"         => $money,
            "ebank_type"    => $ebank_type,
            "ebank_account" => $ebank_account,
            "status"        => WITHDRAW_APPLY
        );
        if (D('Withdraw')->create($data)) {
            if (D('Withdraw')->add()) {
                D('UserEbank')->account($uid, $ebank_account, $ebank_type);
                $this->ajaxReturn(array("success" => true));
            } else {
                D('User')->where(array("uid" => $uid))->setInc("balance", $money);
            }
        }
        $this->ajaxReturn(array("success" => false, "error" => 104)); // 添加失败
    }

    // @brief  ajax_fetch_withdraw  获取用户历史申请套现列表
    // @request  GET
    // @ajaxReturn  成功 => array("success" => true, "list" => 用户历史套现列表)
    //              失败 => array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_fetch_withdraw() {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array('success' => false, 'error' => 101)); // 用户尚未登录
        }
        $field = "ebank_account,ebank_type,money,status,create_time,update_time";
        $list = D('Withdraw')->field($field)
                             ->where(array("uid" => $uid))
                             ->order("update_time DESC")
                             ->select();
        foreach ($list as &$item) {
            $item['update_time'] = format_date($item['update_time']);
            $item['create_time'] = format_date($item['create_time']);
        }
        $this->ajaxReturn(array("success" => true, "list" => $list));
    }

    // @brief  ajax_fetch_account  获取用户已经验证的支付宝账号
    // @request  GET
    // @ajaxReturn  成功 => array("success" => true, "list" => 用户历史操作银行账户列表)
    //              失败 => array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_fetch_account() {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $list = D('UserEbank')->field(true)
                              ->where(array("uid" => $uid))
                              ->order("isdefault DESC, update_time DESC")
                              ->select();
        $this->ajaxReturn(array("success" => true, "list" => $list));
    }
}
