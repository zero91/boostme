<?php

!defined('IN_SITE') && exit('Access Denied');

class indexcontrol extends base {

    function indexcontrol(& $get, & $post) {
        $this->base($get, $post);
    }

    function ondefault() {
        $page = max(1, intval($this->get[2]));
        $pagesize = INDEX_PROBLEM_LIST_SIZE;

        $tobesolvedlist = $this->fromcache('tobesolved');
        $total = count($tobesolvedlist);
        $page_tobesolvedlist = array_slice($tobesolvedlist, ($page - 1) * $pagesize, $pagesize);
        $departstr = page($total, $pagesize, $page, "index/default");
        include template('index');
    }

    function onhelp() {
        include template('help');
    }

    // 查询图片是否需要点击放大
    function onajaxchkimg() {
        list($width, $height, $type, $attr) = getimagesize($this->post['imgsrc']);
        ($width > 300) && exit('1');
        exit('-1');
    }
}

?>
