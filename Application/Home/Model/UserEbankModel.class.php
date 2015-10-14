<?php
namespace Home\Model;
use Think\Model;

class UserEbankModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    // @brief  account  增加用户电子账户信息
    //
    // @param  integer  $uid            用户ID
    // @param  string   $ebank_account  电子账户名称
    // @param  integer  $ebank_type     电子账户密码
    //
    // @return boolean  ture-操作成功，false-操作失败
    //
    public function account($uid, $ebank_account, $ebank_type) {
        $account = array(
            "uid"          => $uid,
            "ebank_type"   => $ebank_type,
            "ebank_account" => $ebank_account
        );
        if ($this->where($account)->count() > 0) {
            return true;
        }
        if ($this->create($account)) {
            $this->add();
            return true;
        }
        return false;
    }
}
