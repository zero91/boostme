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

    function onajaxviewcomment() {
        $answerid = intval($this->get[2]);
        $answer = $_ENV['answer']->get($answerid);

        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default']; 
        $pagesize = 5;

        $departstr = page_ajax(intval($answer['comments']), $pagesize, $page, $answerid);

        $startindex = ($page - 1) * $pagesize;
        $commentlist = $_ENV['answer_comment']->get_by_aid($answerid, $startindex, $pagesize);

        $commentstr = '<div style="text-align:center;">暂无评论</div>';
        if ($commentlist) {
            $commentstr = "";

            //$admin_control = '&nbsp;&nbsp;|&nbsp;&nbsp;<span><a href="javascript:void(0)" onclick="deletecomment({commentid},{answerid});">删除</a></span>';
            foreach ($commentlist as $comment) {
                if ($admin_control) {
                    $del_comment = str_replace("{commentid}", $comment['id'], $admin_control);
                    $del_comment = str_replace("{answerid}", $comment['aid'], $del_comment);
                }

                $commentstr .= "<div class=\"list-group-item\"><div style=\"position:absolute;left:5px;\">" .
                                        "<img class=\"img-circle\" width=\"30\" height=\"30\" src=\"" . get_avatar_dir($comment['authorid']) . "\">" .
                                        "<p>{$comment['author']}</p></div>" .
                                        "<div style=\"margin-left:35px;\">" .
                                        "<div class=\"\">{$comment['content']}</div>" .
                                        "<div style=\"position:absolute;right:25px;bottom:1px;font-size:11px;color:grey;\"><span>{$comment['format_time']}</span>$del_comment</div></div></div>";
            }


            $commentstr .= "<center><div class=\"pages\">{$departstr}</div></center>";
        }
        exit($commentstr);
    }

    function onaddcomment() {
        if (isset($this->post['content'])) {
            $content = $this->post['content'];
            $answerid = intval($this->post['answerid']);
            $answer = $_ENV['answer']->get($answerid);
            $_ENV['answer_comment']->add($answerid, $content, $this->user['uid'], $this->user['username']);
            $_ENV['question']->update_answers($answer['qid']);

            if ($answer['authorid'] != $this->user['uid']) {
                $_ENV['message']->add($this->user['username'], $this->user['uid'], $answer['authorid'], '您的回答有了新评论', '您对于问题 "' . $answer['title'] . '" 的回答 "' . $answer['content'] . '" 有了新评论 "' . $content . '"<br /> <a href="' . url('question/view/' . $answer['qid'], 1) . '">点击查看</a>');
            }
            exit('1');
        }
    }

    function ondeletecomment() {
        if (isset($this->post['commentid'])) {
            $commentid = intval($this->post['commentid']);
            $answerid = intval($this->post['answerid']);
            $_ENV['answer_comment']->remove($commentid, $answerid);
            exit('1');
        }
    }

    function onajaxgetsupport() {
        $answerid = intval($this->get[2]);
        $answer = $_ENV['answer']->get($answerid);
        exit($answer['supports']);
    }

    function onajaxhassupport() {
        $answerid = intval($this->get[2]);
        $supports = $_ENV['answer']->get_support_by_uid_aid($this->user['uid'], $answerid);
        $ret = $supports ? '1' : '-1';
        exit($ret);
    }

    function onajaxaddsupport() {
        $answerid = intval($this->get[2]);
        $answer = $_ENV['answer']->get($answerid);
        $_ENV['answer']->add_support($this->user['uid'], $answerid, $answer['authorid']);
        $answer = $_ENV['answer']->get($answerid);
        exit($answer['supports']);
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_list    [获取回复列表]
    // @request type         [GET/POST]
    // @param[in]       page [页号]
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

    // @onajax_add_comment   [添加回复的评论]
    // @request type         [GET/POST]
    // @param[in]    content [评论内容]
    // @param[in]   answerid [回复的ID编号]
    // @return          成功 [success ：true]
    //                       [id ：评论ID编号]
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [用户尚未登录]
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
        $answer = $_ENV['answer']->get($answerid);
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

    public function onajax_has_support() {
        $res = array();

        $answerid = intval($this->post['answerid']);
        $supports = $_ENV['answer']->get_support_by_uid_aid($this->user['uid'], $answerid);

        if ($supports > 0) {
            $res['success'] = true;
        } else {
            $res['success'] = false;
        }
        echo json_encode($res);
    }

    public function onajax_add_support() {
        $answerid = intval($this->get[2]);
        $answer = $_ENV['answer']->get($answerid);
        $_ENV['answer']->add_support($this->user['uid'], $answerid, $answer['authorid']);
        $answer = $_ENV['answer']->get($answerid);
        exit($answer['supports']);

        echo json_encode($res);
    }
}

?>
