<?php

!defined('IN_SITE') && exit('Access Denied');

class maincontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('service');
        $this->load('service_category');

        $this->load('demand');
        $this->load('user');
        $this->load("question");
    }

    public function ondefault() {
        /*
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;

        $user_num = $_ENV['user']->rownum_alluser();
        $question_num = $_ENV['question']->get_total_num();
        $questionlist = $_ENV['question']->get_list($startindex, $pagesize);
        $departstr = page($question_num, $pagesize, $page, "forum/view");
         */
        $service_list = $_ENV['service']->get_list();
        foreach ($service_list as &$t_service) {
            $t_service['cid_list'] = $_ENV['service_category']->get_by_sid($t_service['id']);
        }
        include template("index");
    }

    function ontutor() {
        $page = max(1, intval($this->get[2]));
        $pagesize = intval($this->setting['list_index_per_page']);

        $indexshowprob = $this->fromcache('indexshowprob');
        $total = count($indexshowprob);
        $page_indexshowprob = array_slice($indexshowprob, ($page - 1) * $pagesize, $pagesize);
        $departstr = page($total, $pagesize, $page, "index/default");

        include template('index');
    }

    public function onhelp() {
        include template('help');
    }

    // 查询图片是否需要点击放大
    function onajaxchkimg() {
        list($width, $height, $type, $attr) = getimagesize($this->post['imgsrc']);
        runlog("test007", "width = $width, height = $height, type = $type, attr = $attr");
        ($width > 200) && exit('1');
        exit('-1');
    }
}

?>
