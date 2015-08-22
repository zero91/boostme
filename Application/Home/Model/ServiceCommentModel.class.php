<?php
namespace Home\Model;
use Think\Model;

class ServiceCommentModel extends Model {

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array(
        array('score', '1,5', -1, self::EXISTS_VALIDATE, 'between'), // 验证分数范围是否合法
        array('content', '1,2048', -2, self::EXISTS_VALIDATE, 'length'), // 验证评论内容长度是否合法
    );

    // @brief  comment  对服务进行评论
    // @request  POST

    // @param  integer  $service_id   评论ID号
    // @param  integer  $uid          用户ID号
    // @param  string   $content      评论内容
    // @param  integer  $score        评分
    //
    // @return  integer  成功 - 新增评论ID号，失败 - 错误编号
    //
    public function comment($service_id, $uid, $content, $score) {
        $user_comment = $this->where(array("uid" => $uid, "service_id" => $service_id))->count();
        if ($user_comment > 0) {
            return -5; // 用户已评论
        }

        // TODO 判断用户是否购买成功过该服务
        // return -6;  该用户为购买过该服务

        $data = array(
            "uid"        => $uid,
            "nickname"   => get_nickname($uid),
            "service_id" => $service_id,
            "content"    => $content,
            "score"      => $score
        );
        if ($this->create($data)) {
            $id = $this->add();
            return $id ? $id : 0;
        } else {
            return $this->getError();
        }
    }
}
