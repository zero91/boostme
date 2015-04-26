<?php

!defined('IN_SITE') && exit('Access Denied');

class answercontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('answer');
        $this->load('answer_comment');
        $this->load('question');
        $this->load('message');
    }

    function ondeletecomment() {
        if (isset($this->post['commentid'])) {
            $commentid = intval($this->post['commentid']);
            $answerid = intval($this->post['answerid']);
            $_ENV['answer_comment']->remove($commentid, $answerid);
            exit('1');
        }
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_list    [获取回复列表]
    // @request type         [GET]
    // @param[in]        qid [问题ID]
    // @param[in]       page [页号，可选]
    // @return          成功 [success ：true]
    //                       [answer_list ：回复列表]
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [帖子id参数无效]
    public function onajax_fetch_list() {
        $res = array();

        $qid = $this->post['qid'];
        if ($qid > 0) {
            $page = max(1, intval($this->post['page']));
            $pagesize = $this->setting['list_default'];
            $start = ($page - 1) * $pagesize;
            $answer_list = $_ENV['answer']->list_by_qid($qid, $start, $pagesize);

            $res['success'] = true;
            $res['answer_list'] = $answer_list;
        } else {
            $res['success'] = false;
            $res['error'] = 101; // 帖子id参数无效
        }
        echo json_encode($res);
    }

    // @onajax_fetch_info    [获取单个回复详细信息]
    // @request type         [GET]
    // @param[in]        aid [回复的ID号]
    // @return          成功 [success: true]
    //                       [answer: 回复详细信息]
    //                  失败 [success: false]
    //                       [error: 为错误码]
    //
    // @error            101 [回复id参数无效]
    public function onajax_fetch_info() {
        $res = array();
        $aid = $this->post['aid'];
        if ($aid > 0) {
            $res['success'] = true;
            $res['answer'] = $_ENV['answer']->get($aid);
        } else {
            $res['success'] = false;
            $res['error'] = 101; // 回复id参数无效
        }
        echo json_encode($res);
    }

    // @onajax_add_comment   [添加回复的评论]
    // @request type         [POST]
    // @param[in]    content [评论内容]
    // @param[in]   answerid [回复的ID编号]
    // @return          成功 [success ：true]
    //                       [id ：评论ID编号]
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [answer不存在，answerid无效]
    // @error            103 [comment内容为空]
    public function onajax_add_comment() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $content = $this->post['content'];
        $answerid = intval($this->post['answerid']);

        if (empty($content)) {
            $res['success'] = false;
            $res['error'] = 103; // comment内容为空
            echo json_encode($res);
            return;
        }

        $answer = $_ENV['answer']->get($answerid);
        if (empty($answer)) {
            $res['success'] = false;
            $res['error'] = 102; // answer不存在，answerid无效
            echo json_encode($res);
            return;
        }

        $id = $_ENV['answer_comment']->add($answerid, $content,
                                           $this->user['uid'], $this->user['username']);

        $_ENV['question']->update_answers($answer['qid']);
        if ($answer['authorid'] != $this->user['uid']) {
            $msg_title = '您的回复有了新评论';
            $msg_content = '您对于问题 "' . $answer['title'] . '" 的回复 "' .
                $answer['content'] . '" 有了新评论 "' . $content . '"<br />' . 
                '<a href="' . SITE_URL . 'question/view/' . $answer['qid'] . '">点击查看</a>';
            $this->send("", 0, $answer['authorid'], $msg_title, $msg_content);
        }

        $res['success'] = true;
        $res['id'] = $id;
        echo json_encode($res);
    }

    // @onajax_fetch_comment_list [获取单个回复的评论列表]
    // @request type              [GET]
    // @param[in]        answerid [回复的ID编号]
    // @param[in]            page [页码，可选]
    // @return               成功 [success ：true]
    //                            [comment_list ：回复的评论列表]
    //                            [departstr ：回复分页html代码]
    public function onajax_fetch_comment_list() {
        $answerid = intval($this->post['answerid']);
        $answer = $_ENV['answer']->get($answerid);

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default']; 
        $startindex = ($page - 1) * $pagesize;

        $comment_list = $_ENV['answer_comment']->get_by_aid($answerid, $startindex, $pagesize);
        foreach ($comment_list as &$comment) {
            $comment['avatar'] = get_avatar_dir($comment['author']);
        }

        $res = array();
        $res['success'] = true;
        $res['comment_list'] = $comment_list;
        $res['departstr'] = split_page(intval($answer['comments']), $pagesize, $page,
                                     "show_comment($answerid, %s)", 1);
        echo json_encode($res);
    }

    // @onajax_has_support        [获取某个回复是否已被用户点赞]
    // @request type              [GET]
    // @param[in]        answerid [回复的ID编号]
    // @return               成功 [success ：true，用户已经赞过]
    //                       失败 [success ：false，用户尚未点赞]
    public function onajax_has_support() {
        if (!$this->check_login(false)) {
            $res['success'] = false;
            echo json_encode($res);
            return;
        }
        $answerid = intval($this->post['answerid']);
        $supports = $_ENV['answer']->get_support_by_uid_aid($this->user['uid'], $answerid);

        $res = array();
        if ($supports > 0) {
            $res['success'] = true;
        } else {
            $res['success'] = false;
        }
        echo json_encode($res);
    }

    // @onajax_add_support    [给某个回复点赞]
    // @request type          [POST]
    // @param[in]    answerid [回复的ID编号]
    // @return           成功 [success ：true，用户已经赞过]
    //                   失败 [success ：false，用户尚未点赞]
    // 
    // @error            101 [用户尚未登录]
    // @error            102 [用户已经点过赞]
    // @error            103 [参数无效]
    public function onajax_add_support() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $answerid = intval($this->post['answerid']);
        if ($answerid > 0) {
            $affected_rows = $_ENV['answer']->add_support($this->user['uid'], $answerid);
            if ($affected_rows > 0) {
                $answer = $_ENV['answer']->get($answerid);
                $res['success'] = true;
                $res['supports'] = $answer['supports'];
            } else {
                $res['success'] = false;
                $res['error'] = 102; // 用户已经点过赞
            }
        } else {
            $res['success'] = false;
            $res['error'] = 103; // 参数无效
        }
        echo json_encode($res);
    }

    // @onajax_get_support    [获取某回复的点赞数量]
    // @request type          [GET]
    // @param[in]    answerid [回复的ID编号]
    // @return           成功 [success ：true，supports为点赞数量]
    //                   失败 [success ：false]
    // 
    // @error             101 [参数无效]
    public function onajax_get_support() {
        $answerid = intval($this->post['answerid']);

        $res = array();
        if ($answerid > 0) {
            $answer = $_ENV['answer']->get($answerid);
            $res['success'] = true;
            $res['supports'] = $answer['supports'];
        } else {
            $res['success'] = false;
            $res['error'] = 101; // 参数无效
        }
        echo json_encode($res);
    }
}

?>
