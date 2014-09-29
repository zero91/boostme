<?php

!defined('IN_SITE') && exit('Access Denied');

class indexcontrol extends base {

    function indexcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('demand');
    }

    function ondefault() {
        $page = max(1, intval($this->get[2]));
        $pagesize = intval($this->setting['list_index_per_page']);

        $indexshowprob = $this->fromcache('indexshowprob');
        $total = count($indexshowprob);
        $page_indexshowprob = array_slice($indexshowprob, ($page - 1) * $pagesize, $pagesize);
        $departstr = page($total, $pagesize, $page, "index/default");
        include template('index');
    }

    function onhelp() {
        include template('help');
    }

    // 查询图片是否需要点击放大
    function onajaxchkimg() {
        list($width, $height, $type, $attr) = getimagesize($this->post['imgsrc']);
        ($width > 200) && exit('1');
        exit('-1');
    }
}

?>
