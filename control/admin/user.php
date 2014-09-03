<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_usercontrol extends base {

    function admin_usercontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('user');
        $this->load('userresume');
        $this->load('education');
    }

    function ondefault() {
        $type = "user/default";
        $statistics = $this->fromcache('statistics');

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_user_page_size'];
        $userlist = $_ENV['user']->get_user_list(($page - 1) * $pagesize, $pagesize);
        $departstr = page($statistics['all_user_num'], $pagesize, $page, "admin_user/default");

        include template('user', 'admin');
    }

    function onapply() {
        $type = "user/apply";

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_user_page_size'];
        $apply_num = $_ENV['userresume']->get_apply_num();
        $userlist = $_ENV['user']->get_apply_list(($page - 1) * $pagesize, $pagesize);
        $departstr = page($apply_num, $pagesize, $page, "admin_user/apply");
        include template('user', 'admin');
    }

    // 接受用户请求
    function onaccept_apply() {
        $uid = intval($this->get[2]);
        if ($uid > 0) {
            $_ENV['userresume']->update_verify($uid, RESUME_ACCEPTED);
            $_ENV['user']->update_can_teach($uid, 1);
            $subject = "恭喜您通过审核，获取抢单资格！";
            $content = '您现在就可以根据自己的特长去帮助别人啦，现在进入<a href="' . SITE_URL . '">首页</a>';
            $this->send('', 0, $uid, $subject, $content);
            exit("1");
        }
        exit("-1");
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
