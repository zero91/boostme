<?php

!defined('IN_SITE') && exit('Access Denied');

class topiccontrol extends base {
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

    // 话题界面
    public function onview() {
        $topic_id = $this->get[2];

        $topic = $_ENV['topic']->get($topic_id);

        $page = max(intval($this->get[3]), 1);
        $pagesize = $this->setting['list_default'];
        $pagesize = 6;
        $start = ($page - 1) * $pagesize;

        $chat_list = $_ENV['chat']->list_by_topic($topic_id, $start, $pagesize);
        $tot_chat_num = $_ENV['chat']->get_topic_chat_num($topic_id);

        $departstr = page($tot_chat_num, $pagesize, $page, "topic/view/$topic_id");
        include template('chat');
    }

    // 随机获取当前能够获取到的匿名头像
    private function get_avatar($topic_id) {
        $used_avatar = $_ENV['topic_member']->list_topic_used_avatar($topic_id);
        $all_avatar = range(1, $this->setting['chat_avatar_num']);
        $avatar_resource = array();

        for ($k = 0; $k < count($all_avatar); ++$k) {
            if (array_key_exists($all_avatar[$k], $used_avatar)) {
                continue;
            }
            array_push($avatar_resource, $all_avatar[$k]);
        }

        if (count($avatar_resource) == 0) {
            return -1;
        }

        srand(time());
        $ret_ind = rand(0, count($avatar_resource));
        return $avatar_resource[$ret_ind];
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_add            [创建新话题]
    // @request type          [GET/POST]
    // @param[in]       title [新话题标题]
    // @param[in] description [新话题简介]
    // @return           成功 [success为true, id为新增加评论ID号]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    // @error             102 [数据库添加失败]
    public function onajax_add() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $title = trim($this->post['title']);
        $description = trim($this->post['description']);

        if (empty($title)) {
            $res['success'] = false;
            $res['error'] = 103; // 新话题标题不能为空
            echo json_encode($res);
            return;
        }

        $topic_id = $_ENV['topic']->add($this->user['uid'], $this->user['username'],
                                  $title, $description, $this->ip);

        if ($topic_id > 0) {
            $_ENV['topic_member']->add($topic_id, $this->user['uid'],
                                       $this->user['username'], 0, TOPIC_ADMIN);
            $_ENV['topic']->update_members($topic_id);

            $res['success'] = true;
            $res['topic_id'] = $topic_id;
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 数据库添加失败
        }
        echo json_encode($res);
    }

    // @onajax_join           [加入话题]
    // @request type          [GET/POST]
    // @param[in]    topic_id [话题ID号]
    // @return           成功 [success为true]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    // @error             102 [用户已经加入了该话题]
    // @error             103 [数据库添加失败]
    // @error             104 [参数错误，话题ID号无效]
    // @error             105 [用户为该话题owner]
    public function onajax_join() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $topic_id = $this->post['topic_id'];
        $user_topic = $_ENV['topic_member']->get_by_topic_uid($topic_id, $this->user['uid']);
        if (!empty($user_topic)) {
            $res['success'] = false;
            $res['error'] = 102; // 用户已经加入了该话题
            echo json_encode($res);
            return;
        }

        if ($_ENV['topic']->is_owner($topic_id, $this->user['uid'])) {
            $res['success'] = false;
            $res['error'] = 105; // 用户为该话题owner
            echo json_encode($res);
            return;
        }

        if ($topic_id > 0) {
            $avatar = $this->get_avatar($topic_id);
            $affected_rows = $_ENV['topic_member']->add($topic_id, $this->user['uid'],
                                                        $this->user['username'], $avatar);

            if ($affected_rows > 0) {
                $_ENV['topic']->update_members($topic_id);
                $res['success'] = true;
            } else {
                $res['success'] = false;
                $res['error'] = 103; // 数据库添加失败
            }

        } else {
            $res['success'] = false;
            $res['error'] = 104; // 参数错误，话题ID号无效
        }
        echo json_encode($res);
    }

    // @onajax_already_joined [用户是否已经加入了该话题]
    // @request type          [GET/POST]
    // @param[in]    topic_id [话题ID号]
    // @return           成功 [success为true]
    //                        topic_id为该话题ID号，isadmin表示该用户是否是该话题管理员]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    // @error             102 [用户已经加入了该话题]
    // @error             103 [数据库添加失败]
    // @error             104 [参数错误，话题ID号无效]
    // @error             105 [用户为该话题owner]
    public function onajax_already_joined() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $topic_id = $this->post['topic_id'];

        $topic_member = $_ENV['topic_member']->get_by_topic_uid($topic_id, $this->user['uid']);
        if (empty($topic_member)) {
            $res['success'] = false;
            $res['error'] = 102; // 用户暂未加入该话题

        } else {
            $res['success'] = true;
            $res['topic_id'] = $topic_id;
            $res['isadmin'] = ($topic_member['role'] == TOPIC_ADMIN);
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
