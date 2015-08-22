<?php
namespace Home\Model;
use Think\Model;

class AnswerCommentModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
    );

    protected $_validate = array(
        array('content', '1,512', -1, self::EXISTS_VALIDATE, 'length'), // 内容长度不合法
    );

    // @brief  comment  评论回复
    //
    // @param  integer  $uid        用户ID
    // @param  integer  $answer_id  回复ID号
    // @param  string   $content    评论内容
    //
    // @return integer  成功 - 新增评论ID号， 失败 - 错误编码(<0)
    //
    public function comment($uid, $answer_id, $content) {
        $data = array(
            "uid" => $uid,
            "nickname" => get_nickname($uid),
            "content" => $content,
            "aid" =>  $answer_id,
        );
        if ($this->create($data)) {
            $id = $this->add();
            return $id ? $id : 0;
        } else {
            return $this->getError();
        }
    }
}
