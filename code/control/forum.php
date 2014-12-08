<?php

!defined('IN_SITE') && exit('Access Denied');

class forumcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load("question");
    }

    // 私人消息
    function ondefault() {
        $navtitle = '考研帮';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;

        $user_num = $_ENV['user']->rownum_alluser();
        $question_num = $_ENV['question']->get_total_num();
        $questionlist = $_ENV['question']->get_list($startindex, $pagesize);
        $departstr = page($question_num, $pagesize, $page, "forum/default");
        include template("forum");
    }

    // 发消息
    function onsubmit() {
        if (isset($this->post['submit'])) {
            (trim($this->post['content']) == '') && $this->message("内容不能为空!", "discuss/view");
            $_ENV['discuss']->add($this->user['username'], $this->user['uid'], $this->post['subject'], $this->post['content']);
            $this->message('消息发送成功!', get_url_source());
        }
    }

    // 删除消息
    function onremove() {
        $msgid = intval($this->get[2]);
        if ($msgid > 0) {
            $_ENV['message']->remove($msgid);
            exit('1');
        }
        exit('-1');
    }

    // ajax删除对话
    function onremovedialog() {
        $fromuid = array(intval($this->get[2]));

        if ($fromuid > 0) {
            $_ENV['message']->remove_by_author($fromuid);
            exit('1');
        }
        exit('-1');
    }
}

?>
