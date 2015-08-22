<?php
namespace Home\Model;
use Think\Model;

class PostCollectModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('valid', 1, self::MODEL_INSERT),
    );

    // @brief  login  新增/更新收藏关系
    //
    // @param  integer  $uid 用户ID
    // @param  integer  $pid 帖子ID
    //
    // @return boolean  ture-操作成功，false-操作失败
    //
    public function collect($uid, $pid) {
        $collect_info = $this->field(true)->where(array("uid" => $uid, "pid" => $pid))->find();

        if (is_array($collect_info)) {
            $valid = $collect_info["valid"] ? 0 : 1;
            $this->where(array("pid" => $pid, "uid" => $uid))->setField("valid", $valid);
            return true;
        } else {
            $data = array(
                "pid"      => $pid,
                "uid"      => $uid,
                "nickname" => get_nickname($uid),
            );
            if ($this->create($data)) {
                $this->add();
                return true;
            } else {
                return false;
            }
        }
    }
}
