<?php
namespace Home\Model;
use Think\Model;

class EducationModel extends Model {
    protected $_auto = array();

    // @brief  update  更新用户教育信息
    //
    // @param  integer  $uid 用户ID
    // @return boolean  ture-登录成功，false-登录失败
    //
    public function update($uid, $edu_list) {
        $orig_list = $this->field(true)->where(array("uid" => $uid))->select();

        $this->where(array("uid" => $uid))->delete();
        foreach ($edu_list as &$edu) {
            $edu['uid'] = $uid;
        }

        if (!$this->addAll($edu_list)) {
            $this->addAll($orig_list);
            return false;
        }
        return true;
    }
}
