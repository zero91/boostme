<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_feedbackcontrol extends base {

    function admin_feedbackcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('feedback');
    }

    function ondefault() {
        $type = "feedback/default";

        $fb_total_num = $_ENV['feedback']->get_total_num();
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_fb_page_size'];
        $fb_list = $_ENV['feedback']->get_list(($page - 1) * $pagesize, $pagesize);
        $departstr = page($fb_total_num, $pagesize, $page, "admin_feedback/default");

        include template('feedback', 'admin');
    }

    function onview() {
        echo "Hello World";
    }

    function onremove() {
        $fid = intval($this->get[2]);

        if ($fid > 0) {
            $_ENV['feedback']->remove_by_fids($fid);
            exit("1");
        }
        exit('-1');
    }
}

?>
