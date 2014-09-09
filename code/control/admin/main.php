<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_maincontrol extends base {

    function admin_maincontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('setting');
        $this->load('user');
        $this->load('problem');
    }

    function ondefault() {
        $type = "default";
        $statistics = $this->fromcache('statistics');

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_prob_page_size'];
        
        $problemlist = $_ENV['problem']->get_list(($page - 1) * $pagesize, $pagesize);

        $departstr = page($statistics['all_prob_num'], $pagesize, $page, "admin_main/default");

        include template('index', 'admin');
    }

    function onuser() {
        $type = "user";
        include template('user', 'admin');
    }

    function onheader() {
        include template('header', 'admin');
    }

    function onmenu() {
        include template('menu', 'admin');
    }

    function onstat() {
        $usercount = $this->db->fetch_total('user');
        $nosolves = $this->db->fetch_total('question', 'status=1');
        $solves = $this->db->fetch_total('question', 'status=2');

        $serverinfo = PHP_OS . ' / PHP v' . PHP_VERSION;
        $serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
        $fileupload = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<font color="red">否</font>';
        $dbsize = 0;
        $tablepre = DB_TABLEPRE;
        $query = $tables = $this->db->fetch_all("SHOW TABLE STATUS LIKE '$tablepre%'");
        foreach ($tables as $table) {
            $dbsize += $table['Data_length'] + $table['Index_length'];
        }
        $dbsize = $dbsize ? $this->_sizecount($dbsize) : '未知';
        $dbversion = $this->db->version();
        $magic_quote_gpc = get_magic_quotes_gpc() ? 'On' : 'Off';
        $allow_url_fopen = ini_get('allow_url_fopen') ? 'On' : 'Off';
        $verifyquestions = $this->db->fetch_total('question', '`status`=0');
        $verifyanswers = $this->db->fetch_total('answer', '`status`=0');
        include template('stat', 'admin');
    }

    function _sizecount($filesize) {
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }

    function onlogin() {
        $password = md5($this->post['password']);
        $user = $_ENV['user']->get_by_username($this->user['username']);
        if (is_array($user) && ($password == $user['password'])) {
            $_ENV['user']->refresh($user['uid'], 2);
            include template('index', 'admin');
        } else {
            $this->message('用户名或密码错误！', 'admin_main');
        }
    }

    /**
     * 数据校正
     */
    function onregulate() {
        include template("data_regulate", "admin");
    }
    
    function onajaxregulatedata(){
       if($this->user['grouptype']==1){
           $type = $this->get[2];
           if(method_exists($_ENV['setting'], 'regulate_'.$type)){
               call_user_method('regulate_'.$type, $_ENV['setting']);
           }
       }
        exit('ok');
    }

    function onlogout() {
        $_ENV['user']->refresh($this->user['uid'], 1);
        header("Location:" . SITE_URL);
    }
}

?>
