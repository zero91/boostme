<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_servicecontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load("service");
        $this->load("service_category");
        $this->load("invite_code");
        $this->load("userresume");
        $this->load("education");
        $this->load("user");
    }

    public function ondefault() {
        $type = "admin_service/default";
        $statistics = $this->fromcache('statistics');

        $apply_page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['admin_user_page_size'];

        $start = ($apply_page - 1) * $pagesize;
        $apply_service_list = $_ENV['service']->get_by_status(SERVICE_STATUS_APPLY, $start, $pagesize);
        $tot_apply_service_num = $_ENV['service']->get_status_num(SERVICE_STATUS_APPLY);

        $apply_departstr = page($tot_apply_service_num, $pagesize, $apply_page, "admin_service/default");

        foreach ($apply_service_list as &$t_service) {
            $t_service['user_info'] = $_ENV['user']->get_by_uid($t_service['uid']);
            $t_service['resume'] = $_ENV['userresume']->get_by_uid($t_service['uid']);
            $t_service['edu_list'] = $_ENV['education']->get_by_uid($t_service['uid']);
            $t_service['category'] = $_ENV['service_category']->get_by_sid($t_service['id']);
        }

        $tot_accept_service_num = $_ENV['service']->get_status_num(SERVICE_STATUS_ACCEPTED);
        $accept_page = max(1, intval($this->get[3]));
        $start = ($accept_page - 1) * $pagesize;
        $accept_service_list = $_ENV['service']->get_by_status(SERVICE_STATUS_ACCEPTED, $start, $pagesize);
        $accept_departstr = page($tot_accept_service_num, $pagesize, $accept_page, "admin_service/default/$apply_page");

        foreach ($accept_service_list as &$t_service) {
            $t_service['user_info'] = $_ENV['user']->get_by_uid($t_service['uid']);
            $t_service['resume'] = $_ENV['userresume']->get_by_uid($t_service['uid']);
            $t_service['edu_list'] = $_ENV['education']->get_by_uid($t_service['uid']);
        }

        include template('service', 'admin');
    }

    public function ondenied_service() {
        $service_id = $this->get[2];
        $res = array();
        $message = $this->post['message'];

        if ($service_id > 0) {
            $_ENV['service']->update_status($service_id, SERVICE_STATUS_DENIED);

            $service = $_ENV['service']->get_by_id($service_id);

            $subject = "您未通过Boostme提供咨询的审核";
            $content = "<p>原因：$message</p>";
            $content .= "<p>您可以修改您的申请信息，再重新提交。更改<a href=\"" . SITE_URL . "service/register\">申请咨询</a></p>";
            $this->send("", 0, $service['uid'], $subject, $content, true);

            $res['success'] = true;
        } else {
            $res['error'] = 101; // 无效service ID
        }
        echo json_encode($res);
    }

    public function onaccept_service() {
        $service_id = $this->get[2];
        $res = array();

        if ($service_id > 0) {
            $_ENV['service']->update_status($service_id, SERVICE_STATUS_ACCEPTED);

            $service = $_ENV['service']->get_by_id($service_id);
            for ($k = 0; $k < $this->setting['service_give_code_num']; ++$k) {
                $c_code = $_ENV['invite_code']->create_code($this->user['sid']);
                $_ENV['invite_code']->add($c_code, $service['uid']);
            }

            $subject = "恭喜您通过了Boostme提供咨询的审核";
            $content = "现在您的信息将放在Boostme的“咨询区”内。<p>另外，系统奖励您3枚邀请码，您可用来邀请您的小伙伴们加入Boostme提供咨询的队伍，您邀请的提供咨询的小伙伴还能够给你带来额外的现金收益哦！邀请码见 <a href=\"" . SITE_URL . "invite/default\">邀请码</a></p>";
            $this->send("", 0, $service['uid'], $subject, $content, true);

            $res['success'] = true;
        } else {
            $res['error'] = 101; // 无效service ID
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
