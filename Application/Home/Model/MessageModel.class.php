<?php
namespace Home\Model;
use Think\Model;

class MessageModel extends Model {
    protected $_auto = array(
        array('new', 1, self::MODEL_INSERT),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    // @brief  send  发送消息
    //
    // @param  integer  $from_uid       发送者ID号
    // @param  string   $from_nickname  发送者昵称
    // @param  integer  $to_uid         接受者ID号
    // @param  string   $to_nickname    接收者昵称
    // @param  string   $content        内容
    //
    // @return integer  发送成功 - 信息ID号，发送失败 - 错误编号
    //
    public function send($from_uid, $from_nickname, $to_uid, $to_nickname, $content) {
        $data = array(
            "from_uid"      => $from_uid,
            "from_nickname" => $from_nickname,
            "to_uid"        => $to_uid,
            "to_nickname"   => $to_nickname,
            "content"       => $content
        );

        if ($this->create($data)) {
            $id = $this->add();
            return $id > 0 ? $id : 0;
        } else {
            return $this->getError();
        }
    }

    // @brief  remove  登录指定用户
    //
    // @param  integer  $id   消息ID号
    // @param  integer  $uid  用户ID号
    //
    // @return integer  发送成功 - 信息ID号，发送失败 - 错误编号
    //
    public function remove($id, $uid) {
        $message = $this->field(true)->find($id);
        if (!is_array($message)) {
            return -4; // 该消息不存在
        }

        $status = -1;
        $new = $message['new'];
        if ($message["from_uid"] == $uid) {
            if ($message["status"] == MSG_STATUS_NOT_DELETED) {
                $status = MSG_STATUS_FROM_DELETED;
            } elseif ($message["status"] == MSG_STATUS_TO_DELETED) {
                $status = MSG_STATUS_BOTH_DELETED;
            } else {
                return -1; // 已删除
            }
        } elseif ($message["to_uid"] == $uid) {
            if ($message["status"] == MSG_STATUS_NOT_DELETED) {
                $status = MSG_STATUS_TO_DELETED;
            } elseif ($message["status"] == MSG_STATUS_FROM_DELETED) {
                $status = MSG_STATUS_BOTH_DELETED;
            } else {
                return -1; // 已删除
            }
            $new = 0;
        } else {
            return -2; // 无权操作
        }

        if ($this->save(array('id' => $id, 'status' => $status, "new" => $new))) {
            return $id;
        } else {
            return -3; // 更新失败
        }
    }

    // @brief  system  获取用户的系统消息
    //
    // @param  integer  $uid   用户ID号
    // @param  integer  $page  页码
    //
    // @return  array  系统消息列表
    //
    public function system($uid, $page = 1) {
        $num_per_page = C('MESSAGE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $condition = array(
            "from_uid" => 0,
            "to_uid"   => $uid,
            "status"   => array("NEQ", MSG_STATUS_TO_DELETED)
        );
        $message_list = D('Message')->field(true)
                                    ->where($condition)
                                    ->order('create_time DESC')
                                    ->limit($start, $num_per_page)
                                    ->select();

        return $message_list;
    }

    // @brief  dialog  获取用户的指定用户的消息对话
    //
    // @param  integer  $from_uid   操作用户ID号
    // @param  integer  $to_uid     指定获取消息的用户ID号
    // @param  integer  $page       页码
    //
    // @return  array  消息列表
    //
    public function dialog($from_uid, $to_uid, $page = 1, $field = true) {
        $num_per_page = C('MESSAGE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $condition = array (
            array(
                "from_uid" => $from_uid,
                "to_uid"   => $to_uid,
                "status"   => array("IN", array(MSG_STATUS_NOT_DELETED, MSG_STATUS_TO_DELETED))
            ),
            array(
                "from_uid" => $to_uid,
                "to_uid"   => $from_uid,
                "status"   => array("IN", array(MSG_STATUS_NOT_DELETED, MSG_STATUS_FROM_DELETED))
            ),
            "_logic" => "OR"
        );
        $message_list = D('Message')->field($field)
                                    ->where($condition)
                                    ->order('create_time DESC')
                                    ->limit($start, $num_per_page)
                                    ->select();
        return $message_list;
    }

    // @brief  remove_dialog  删除用户的指定用户的消息对话
    //
    // @param  integer  $from_uid   操作用户ID号
    // @param  integer  $to_uid     待删除消息用户ID号
    //
    public function remove_dialog($from_uid, $to_uid) {
        $condition = array(
            "from_uid" => $from_uid,
            "to_uid"   => $to_uid,
            "status"   => MSG_STATUS_NOT_DELETED
        );
        $this->where($condition)->save(array("status" => MSG_STATUS_FROM_DELETED));

        $condition = array(
            "from_uid" => $to_uid,
            "to_uid"   => $from_uid,
            "status"   => MSG_STATUS_NOT_DELETED
        );
        $this->where($condition)->save(array("status" => MSG_STATUS_TO_DELETED, "new" => 0));

        $condition = array(
            "from_uid" => $from_uid,
            "to_uid"   => $to_uid,
            "status"   => MSG_STATUS_TO_DELETED
        );
        $this->where($condition)->save(array("status" => MSG_STATUS_BOTH_DELETED));

        $condition = array(
            "from_uid" => $to_uid,
            "to_uid"   => $from_uid,
            "status"   => MSG_STATUS_FROM_DELETED
        );
        $this->where($condition)->save(array("status" => MSG_STATUS_BOTH_DELETED, "new" => 0));
    }

    public function latest($from_uid, $to_uid, $field = true) {
        $condition = array(
            array(
                "from_uid" => $from_uid,
                "to_uid"   => $to_uid,
                "status"   => array('in', array(MSG_STATUS_TO_DELETED, MSG_STATUS_NOT_DELETED)),
            ),
            array(
                "from_uid" => $to_uid,
                "to_uid"   => $from_uid,
                "status"   => array('in', array(MSG_STATUS_FROM_DELETED, MSG_STATUS_NOT_DELETED)),
            ),
            "_logic" => "OR"
        );
        $message = $this->field($field)
                        ->where($condition)
                        ->order("create_time DESC")->limit(1)->find();
        return $message;
    }
}
