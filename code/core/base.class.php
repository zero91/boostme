<?php

!defined('IN_SITE') && exit('Access Denied');

class base {
    public function __construct(& $get, & $post) {
        $this->time = time();
        $this->ip = getip();
        $this->get = & $get;
        $this->post = & $post;

        $this->init_db();
        $this->init_cache();
        $this->init_crontab();
        $this->init_user();

        $this->banned();
        //$this->checkcode();
    } 

    // 权限检测
    public function checkable($url) {
        $this->regular = $url;
        if ('admin' == substr($url, 0, 5)) {
            return $this->user['isadmin'] == 1;
        }
        return true;
    }

    // 从缓存中读取数据，如果失败，则自动去读取数据然后写入缓存
    protected function fromcache($cachename, $cachetime = 1) {
        $cachetime = ($this->setting['index_life'] == 0) ? $cachetime : $this->setting['index_life'] * 60;
        $cachedata = $this->cache->read($cachename, $cachetime);
        if ($cachedata) return $cachedata;

        switch ($cachename) {
        case 'allprob': // 所有的求助
            $this->load('problem');
            $cachedata = $_ENV['problem']->get_list();
            break;
        case 'indexshowprob': //首页显示的求助
            $this->load('problem');
            $cachedata = $_ENV['problem']->list_by_status($this->setting["index_prob_status"]);
            break;

        case 'tobesolved': // 待解决求助
            $this->load('problem');
            $cachedata = $_ENV['problem']->list_by_status(PB_STATUS_UNSOLVED);
            break;
        case 'solved': // 已经解决的求助
            $this->load('problem');
            $cachedata = $_ENV['problem']->list_by_status(PB_STATUS_SOLVED);
            break;
        case 'statistics'://首页统计，包含已解决、待解决
            $this->load('problem');
            $this->load('material');
            $this->load('service');
            $cachedata = array();
            $cachedata['solves'] = $this->db->fetch_total('problem', 'status IN (' . PB_STATUS_SOLVED . ')');   //已解决问题数
            $cachedata['nosolves'] = $this->db->fetch_total('problem', 'status=' . PB_STATUS_UNSOLVED); //待解决问题数
            $cachedata['all_prob_num'] = $_ENV['problem']->get_all_prob_num(); // 全部求助数量
            $cachedata['all_user_num'] = $_ENV['user']->rownum_alluser();
            $cachedata['all_material_num'] = $_ENV['material']->get_all_material_num();
            $cachedata['all_service_num'] = $_ENV['service']->get_service_num();
            break;
        case 'onlineusernum':
            $this->load('user');
            $cachedata = $_ENV['user']->rownum_onlineuser();
            break;
        case 'allusernum':
            $this->load('user');
            $cachedata = $_ENV['user']->rownum_alluser();
            break;
        }
        $this->cache->write($cachename, $cachedata);
        return $cachedata;
    }

    // 加载model类
    protected function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require WEB_ROOT . "/code/model/$model.class.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base->db);');
        }
        return $_ENV[$model];
    }

    // 检查验证码
    protected function checkcode() {
        $this->load('user');
        if (isset($this->post['code']) && (strtolower(trim($this->post['code'])) !=
                                            $_ENV['user']->get_code($this->user['sid']))) {
            //$this->message("验证码错误!", 'BACK');
            return false;
        }
        return true;
    }

    // 中转提示页面
    protected function message($message, $url = '') {
        $seotitle = '操作提示';
        if ($url == '') {
            $redirect = SITE_URL;
        } else if ($url == 'BACK' || $url == 'STOP') {
            $redirect = $url;
        } else {
            $redirect = SITE_URL . "/" . $url . $this->setting['seo_suffix'];
        }
        $tpldir = (0 === strpos($this->get[0], 'admin')) ? 'admin' : 'default';
        include template('tip', $tpldir);
        exit;
    }

    // 跳转到指定页面
    protected function jump($url, $full=false) {
        $jump_url = $url;
        if (!$full) {
            $jump_url = SITE_URL . $url . $this->setting['seo_suffix'];
        }
        header("Location: $jump_url");
    }

    // 判断用户是否登录
    protected function check_login($jump=true) {
        if ($this->user['uid'] > 0) {
            return true;
        }

        if ($jump) {
            $this->jump("user/login");
        }
        return false;
    }

    // 给用户发送消息和邮件
    protected function send($from, $fromuid, $touid, $subject, $content, $mail=false) {
        if ($fromuid == 0) {
            $from = $this->setting['site_name'] . '管理员';
        }

        // 站内消息
        $this->load('message');
        $_ENV['message']->add($from, $fromuid, $touid, $subject, $content);

        // 发邮件
        if ($mail) {
            $this->load('user');
            $touser = $_ENV['user']->get_by_uid($touid);
            sendmail($touser['username'], $touser['email'], $subject, $content);
        }
    }

    protected function refresh(&$user, $islogin=1, $cookietime=0) {
        global $setting;
        @$sid = tcookie('sid');

        $_ENV['user']->update_lastlogin($user['uid']);

        $auth = strcode($user['uid'] . "\t" . $user['password'], $setting['auth_key'], 'ENCODE');
        if ($cookietime) {
            tcookie('auth', $auth, $cookietime);
        } else {
            tcookie('auth', $auth);
        }
    }

    // 防采集
    protected function stopcopy($ip) {
        global $setting;
        $time = time();
        $bengintime = $time - 60;
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent = strtolower($useragent);
        $allowagent = explode("\n", $setting['stopcopy_allowagent']);
        $allow = false;
        foreach ($allowagent as $agent) {
            if (false !== strpos($useragent, $agent)) {
                $allow = true;
                break;
            }
        }
        !$allow && exit('Boostme禁止不明浏览行为！');
        $stopagent = explode("\n", $setting['stopcopy_stopagent']);
        foreach ($stopagent as $agent) {
            if (false !== strpos($useragent, $agent)) {
                exit('Boostme禁止不明浏览行为！');
            }
        }

        $visits = $this->db->fetch_total('visit', " time>$bengintime AND ip='$ip' ");
        if ($visits > $setting['stopcopy_maxnum']) {
            $userip = explode(".", $ip);
            $expiration = 3600 * 24;
            $this->db->query("INSERT INTO `banned` (`ip1`,`ip2`,`ip3`,`ip4`,`admin`,`time`,`expiration`) VALUES ('{$userip[0]}', '{$userip[1]}', '{$userip[2]}', '{$userip[3]}', 'SYSTEM', '{$time}', '{$expiration}')");
            exit('你采集的速度太快了吧 : ) ');
        } else {
            $this->db->query("INSERT INTO visit (`ip`,`time`) values ('$ip','$time')"); //加入数据库记录visit表中
        }
    }

    private function init_db() {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }

    // 一旦setting的缓存文件读取失败，则更新所有cache
    private function init_cache() {
        global $setting, $badword;
        $this->cache = new cache($this->db);
        $setting = $this->setting = $this->cache->load('setting', 'k', 'v');
        $badword = $this->cache->load('badword', 'find');
    }

    // 初始化定时任务，并调用定时任务的方法
    private function init_crontab() {
        $this->load('crontab');
        $this->load('trade');
        $this->load('ebank');
        $crontablist = $this->cache->load("crontab");
        foreach ($crontablist as $crontab) {
            $crontab['available'] && $_ENV['crontab']->$crontab['method']($crontab);
        }
    }

    // 初始化用户信息
    private function init_user() {
        @$sid = tcookie('sid');
        @$auth = tcookie('auth');

        $user = array();
        @list($uid, $password) = empty($auth) ? array(0, 0) : taddslashes(explode("\t", strcode($auth, $this->setting['auth_key'], 'DECODE')), 1);

        if (!$sid) {
            $sid = substr(md5(time() . $this->ip . random(6)), 16, 16);
            tcookie('sid', $sid, 31536000);
        }

        $this->load('user');
        if ($uid && $password) {
            $user = $_ENV['user']->get_by_uid($uid, 0);
            ($password != $user['password']) && $user = array();
        }

        if (!$user) {
            $user['uid'] = 0;
        } else {
            $this->load('message');
            $user['new_msg_num'] = $_ENV['message']->get_new_msg_num($user['uid']);

            $user['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid=0 AND status<>" . MSG_STATUS_TO_DELETED);
            $user['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>" . MSG_STATUS_TO_DELETED);
        }

        if ($user['uid'] > 0) {
            $this->refresh($user, 1);
        }

        @$lastrefresh = tcookie("lastrefresh");
        if (!$lastrefresh) {
            $_ENV['user']->refresh_session_time($sid, $this->ip, $user['uid']);
            tcookie("lastrefresh", '1', 60);
        }

        $user['sid'] = $sid;
        $user['ip'] = $this->ip;
        $user['uid'] && $user['avatar'] = get_avatar_dir($user['uid']);
        $this->user = $user;
    }

    // 判断用户IP是否已经被禁止
    private function banned() {
        $ips = $this->cache->load('banned');
        $ips = (bool) $ips ? $ips : array();
        $userip = explode(".", $this->ip);
        foreach ($ips as $ip) {
            $bannedtime = $ip['expiration'] + $ip['time'] - $this->time;
            if ($bannedtime > 0
                && ($ip['ip1'] == '*' || $ip['ip1'] == $userip[0])
                && ($ip['ip2'] == '*' || $ip['ip2'] == $userip[1])
                && ($ip['ip3'] == '*' || $ip['ip3'] == $userip[2])
                && ($ip['ip4'] == '*' || $ip['ip4'] == $userip[3])) {
                exit('IP被禁止访问,如有问题请联系:' . $this->setting['admin_email']);
            }
        }
    }

    protected $ip;
    protected $time;
    protected $db;
    protected $cache;
    protected $regular; // 访问路径
    protected $user = array();
    protected $setting = array();
    protected $category = array();
    protected $get = array();
    protected $post = array();
}

?>
