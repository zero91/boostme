<?php

!defined('IN_SITE') && exit('Access Denied');

class discusscontrol extends base {

    function discusscontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('user');
        $this->load("discuss");
    }

    // 私人消息
    function onview() {
        $navtitle = '互动区';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $discuss_list = $_ENV['discuss']->get_discuss_list($startindex, $pagesize);
        $discuss_num = $_ENV['discuss']->get_total_num();
        $departstr = page($discuss_num, $pagesize, $page, "discuss/view");
        include template("viewdiscuss");
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
