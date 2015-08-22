<?php
namespace Home\Model;
use Think\Model;

class PostsModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
    );

    protected $_validate = array(
        array('title', '5,64', -1, self::EXISTS_VALIDATE, 'length'), // 标题长度不合法
        array('content', '0,2048', -2, self::EXISTS_VALIDATE, 'length'), // 内容长度不合法
    );
    
    // @brief  post  新增帖子
    //
    // @param  integer  $uid      用户ID
    // @param  integer  $title    帖子标题
    // @param  integer  $content  帖子内容
    //
    // @return integer  成功 - 新增帖子ID号， 失败 - 错误编码(<0)
    //
    public function post($uid, $title, $content) {
        $data = array(
            "uid"      => $uid,
            "nickname" => get_nickname($uid),
            "title"    => $title,
            "content"  => $content
        );

        if ($this->create($data)) {
            $id = $this->add();
            return $id ? $id : 0;
        } else {
            return $this->getError();
        }
    }
}
