<?php

!defined('IN_SITE') && exit('Access Denied');

class forumcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load("question");
    }

    public function ondefault() {
        $navtitle = '交流区';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;

        $user_num = $_ENV['user']->rownum_alluser();
        $question_num = $_ENV['question']->get_total_num();
        $questionlist = $_ENV['question']->get_list($startindex, $pagesize);
        $departstr = page($question_num, $pagesize, $page, "forum/default");
        include template("forum");
    }
}

?>
