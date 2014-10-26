<?php

!defined('IN_SITE') && exit('Access Denied');

class answercontrol extends base {

    function __construct(& $get, & $post) {
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
                $viewurl = urlmap('user/space/' . $comment['authorid'], 2);

                if ($admin_control) {
                    $del_comment = str_replace("{commentid}", $comment['id'], $admin_control);
                    $del_comment = str_replace("{answerid}", $comment['aid'], $del_comment);
                }

                $commentstr .= "<div class=\"list-group-item\"><div style=\"position:absolute;left:5px;\"><a href=\"?$viewurl\" target=\"_blank\">" .
                                        "<img class=\"img-circle\" width=\"30\" height=\"30\" src=\"{$comment['avatar']}\"></a>" .
                                        "<p><a href=\"?$viewurl\" target=\"_blank\">{$comment['author']}</a></p></div>" .
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
}

?>
