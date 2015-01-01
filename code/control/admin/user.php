<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_usercontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load('userresume');
        $this->load('education');

        $this->load('invite_code');
    }

    public function ondefault() {
        $type = "admin_user/default";
        $statistics = $this->fromcache('statistics');

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_user_page_size'];
        $userlist = $_ENV['user']->get_user_list(($page - 1) * $pagesize, $pagesize);

        foreach ($userlist as &$t_user) {
            if ($t_user['invited_by_uid'] > 0) {
                $tmp_user = $_ENV['user']->get_by_uid($t_user['invited_by_uid']);
                $t_user['invited_by_username'] = $tmp_user['username'];
            }
        }

        $departstr = page($statistics['all_user_num'], $pagesize, $page, "admin_user/default");

        include template('user', 'admin');
    }

    public function ongive_invite_code() {
        $uid = $this->get[2];

        $res = array();
        if ($uid > 0) {
            $c_code = $_ENV['invite_code']->create_code($this->user['sid']);
            $_ENV['invite_code']->add($c_code, $uid);

            $subject = "系统奖励您邀请码：" . $c_code;
            $content = "恭喜您获得系统奖励的一枚邀请码，您使用此邀请码邀请您的小伙伴，提供咨询的小伙伴还能够给你带来额外的现金收益哦！";
            $this->send("", 0, $uid, $subject, $content);

            $res['success'] = true;
            $res['code'] = $c_code;
        } else {
            $res['error'] = 101; //无效用户uid
        }

        echo json_encode($res);
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

    function onaccepted() {
        $type = "user/accepted";

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
