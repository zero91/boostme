<?php

!defined('IN_SITE') && exit('Access Denied');

class chatcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('topic');
        $this->load('topic_member');
        $this->load('chat');
    }

    // 爆料首页列表
    public function ondefault() {
        //$page = max(intval($this->post['page']), 1);
        $page = max(intval($this->get[2]), 1);
        $pagesize = $this->setting['list_default'];
        $pagesize = 6;
        $start = ($page - 1) * $pagesize;

        $topic_list = $_ENV['topic']->get_list($start, $pagesize);

        $tot_topic_num = $_ENV['topic']->get_total_num();

        $departstr = page($tot_topic_num, $pagesize, $page, "topic/default");
        include template('topic');
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_add            [新增话题回复]
    // @request type          [GET/POST]
    // @param[in]    topic_id [话题ID号]
    // @param[in]     content [回复内容]
    // @return           成功 [success为true, id为新增加评论ID号]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    // @error             102 [内容不能为空]
    // @error             103 [数据库添加失败]
    // @error             104 [该用户尚未加入该话题，无权回复]
    public function onajax_add() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $content = trim($this->post['content']);
        if (empty($content)) {
            $res['success'] = false;
            $res['error'] = 102; // 内容不能为空
            echo json_encode($res);
            return;
        }

        $topic_id = $this->post['topic_id'];
        $topic_member = $_ENV['topic_member']->get_by_topic_uid($topic_id, $this->user['uid']);
        if (empty($topic_member)) {
            $res['success'] = false;
            $res['error'] = 104; // 该用户尚未加入该话题，无权回复
        } else {
            $id = $_ENV['chat']->add($topic_id, $this->user['uid'], $this->user['username'],
                                     $topic_member['avatar'], $content, $this->ip);

            if ($id > 0) {
                $_ENV['topic']->update_chat_num($topic_id);
                $res['success'] = true;
                $res['id'] = $id;
            } else {
                $res['success'] = false;
                $res['error'] = 103; // 数据库添加失败
            }
        }
        echo json_encode($res);
    }

    // @onajax_fetch_list    [获取topic列表]
    // @request type         [GET/POST]
    // @param[in]       page [页号]
    // @return               [topic列表]
    public function onajax_fetch_list() {
        $page = max(intval($this->post['page']), 1);
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $topic_list = $_ENV['topic']->get_list($start, $pagesize);
        echo json_encode($topic_list);
    }
}

?>
