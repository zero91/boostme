<?php
namespace Home\Model;
use Think\Model;

class UserResumeModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('status', RESUME_SAVE, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    // @brief  update  更新用户简历信息
    // @param  array  待更新或增加数据
    //
    // @return boolean  ture-操作成功，false-操作失败
    //
    public function update($data) {
        if (!isset($data['uid'])) {
            return false;
        }

        $exist = $this->where(array("uid" => $data['uid']))->count();
        if ($this->create($data) === false) {
            return false;
        }

        if ($exist) {
            $this->save();
        } else {
            $this->add();
        }
        return true;
    }
}
