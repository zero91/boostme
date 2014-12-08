<?php

!defined('IN_SITE') && exit('Access Denied');

class invitecontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('invite_code');
    }

    public function ondefault() {
        $this->check_login();
        //$c_code = $_ENV['invite_code']->create_code($this->user['uid']);
        //$_ENV['invite_code']->add($c_code, $this->user['uid']);

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;

        $code_list = $_ENV['invite_code']->get_by_owner($this->user['uid'], $startindex, $pagesize);
        $tot_code_num = $_ENV['invite_code']->get_owner_code_num($this->user['uid']);

        $departstr = page($tot_code_num, $pagesize, $page, "invite/default");
        include template('invite');
    }
}

?>
