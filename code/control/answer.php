<?php

!defined('IN_TIPASK') && exit('Access Denied');

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
        $commentlist = $_ENV['answer_comment']->get_by_aid($answerid, 0, 50);
        $commentstr = '<li class="loading">暂无评论 :)</li>';
        if ($commentlist) {
            $commentstr = "";
            $admin_control = ($this->user['grouptype'] == 1) ? '<span class="span-line">|</span><a href="javascript:void(0)" onclick="deletecomment({commentid},{answerid});">删除</a>' : '';
            foreach ($commentlist as $comment) {
                $viewurl = urlmap('user/space/' . $comment['authorid'], 2);
                if ($admin_control) {
                    $admin_control = str_replace("{commentid}", $comment['id'], $admin_control);
                    $admin_control = str_replace("{answerid}", $comment['aid'], $admin_control);
                }
                $commentstr.='<li><div class="other-comment"><a href="' . $viewurl . '" title="' . $comment['author'] . '" target="_blank" class="pic"><img width="30" height="30" src="' . $comment['avatar'] . '"  onmouseover="pop_user_on(this, \'' . $comment['authorid'] . '\', \'\');"  onmouseout="pop_user_out();"></a><p><a href="' . $viewurl . '" title="' . $comment['author'] . '" target="_blank">' . $comment['author'] . '</a>：' . $comment['content'] . '</p></div><div class="replybtn"><span class="times">' . $comment['format_time'] . '</span>' . $admin_control . '</div></li>';
            }
        }
        exit($commentstr);
    }

    function onaddcomment() {
        if (isset($this->post['content'])) {
            $content = $this->post['content'];
            $answerid = intval($this->post['answerid']);
            $answer = $_ENV['answer']->get($answerid);
            $_ENV['answer_comment']->add($answerid, $content, $this->user['uid'], $this->user['username']);
            if ($answer['authorid'] != $this->user['uid'])
                $_ENV['message']->add($this->user['username'], $this->user['uid'], $answer['authorid'], '您的回答有了新评论', '您对于问题 "' . $answer['title'] . '" 的回答 "' . $answer['content'] . '" 有了新评论 "' . $content . '"<br /> <a href="' . url('question/view/' . $answer['qid'], 1) . '">点击查看</a>');
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
        $supports = $_ENV['answer']->get_support_by_sid_aid($this->user['sid'], $answerid);
        $ret = $supports ? '1' : '-1';
        exit($ret);
    }

    function onajaxaddsupport() {
        $answerid = intval($this->get[2]);
        $answer = $_ENV['answer']->get($answerid);
        $_ENV['answer']->add_support($this->user['sid'], $answerid, $answer['authorid']);
        $answer = $_ENV['answer']->get($answerid);
        exit($answer['supports']);
    }

}

?>
