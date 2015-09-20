<?php
namespace Home\Model;
use Think\Model;

class LatestMessageModel extends Model {
    protected $_auto = array(
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    // @brief  update  更新用户之间最新的对话消息
    //
    // @param  integer  $uid     操作用户ID号
    // @param  integer  $to_uid  操作对象用户ID号
    //
    // @return integer  返回更新的条数
    //
    public function update($uid, $to_uid) {
        $message = D('Message')->latest($uid, $to_uid, "content");
        if (is_array($message)) {
            $content = $message["content"];
        } else {
            $content = "";
        }

        $condition = array("from_uid" => $to_uid, "to_uid" => $uid, "new" => 1);
        $new_num = D('Message')->where($condition)->count();
        $data = array(
            "uid"         => $uid,
            "to_uid"      => $to_uid,
            "to_nickname" => get_nickname($to_uid),
            "content"     => $content,
            "new_num"     => $new_num,
            "update_time" => NOW_TIME
        );
        return $this->add($data, array(), true);
    }

    // @brief  remove_dialog  删除用户的指定用户最新信息
    //
    // @param  integer  $from_uid   操作用户ID号
    // @param  integer  $to_uid     待删除消息用户ID号
    //
    public function remove_dialog($from_uid, $to_uid) {
        $this->where(array("uid" => $from_uid, "to_uid" => $to_uid))->delete();
    }
}
