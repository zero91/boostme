<?php
namespace Home\Model;
use Think\Model;

class PostAnswerModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
    );

    protected $_validate = array(
        array('content', '5,2048', -1, self::EXISTS_VALIDATE, 'length'), // 验证内容长度是否合法
    );

    // @brief  answer  回复帖子
    //
    // @param  integer  $uid      用户ID
    // @param  string   $content  回复内容
    // @param  integer  $pid      帖子ID号
    // @param  string   $ptitle   帖子标题
    //
    // @return integer  成功 - 新增回复ID号， 失败 - 错误编码(<0)
    //
    public function answer($uid, $content, $pid, $ptitle) {
        $data = array(
            "uid"      => $uid,
            "nickname" => get_nickname($uid),
            "content"  => $content,
            "pid"      => $pid,
            "ptitle"  => $ptitle
        );

        if ($this->create($data)) {
            $id = $this->add();
            return $id ? $id : 0;
        } else {
            return $this->getError();
        }
    }
}
