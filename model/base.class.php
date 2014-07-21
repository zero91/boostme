<?php

!defined('IN_SITE') && exit('Access Denied');

class base
{
    var $ip;
    var $time;
    var $db;
    var $cache;
    var $user = array();
    var $setting = array();
    var $category = array();
    var $nav = array();
    var $usergroup = array();
    var $get = array();
    var $post = array();
    var $regular;
    var $statusarray = array('all' => '全部', '0' => '待审核', '1' => '待解决', '2' => '已解决', '4' => '已过期', '8' => '已关闭');

    function base(& $get, & $post)
    {
        $this->time = time();
        $this->ip = getip();
        $this->get = & $get;
        $this->post = & $post;

        $this->init_db();
        $this->init_cache();

        $this->init_crontab();
        $this->init_user();
        $this->init_problem();
        $this->checkcode();
        $this->banned();
    }

    function init_db()
    {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }

    // 一旦setting的缓存文件读取失败，则更新所有cache
    function init_cache()
    {
        global $setting, $category, $badword;
        $this->cache = new cache($this->db);
        $setting = $this->setting = $this->cache->load('setting');
        $badword = $this->cache->load('badword', 'find');
    }

    // 从缓存中读取数据，如果失败，则自动去读取数据然后写入缓存
    function fromcache($cachename, $cachetime = 3)
    {
        $cachetime = ($this->setting['index_life'] == 0) ? 1 : $this->setting['index_life'] * 60;
        $cachedata = $this->cache->read($cachename, $cachetime);

        if ($cachedata)
            return $cachedata;

        switch ($cachename) {
        case 'tobesolved': // 待解决需求
            $this->load('problem');
            $cachedata = $_ENV['problem']->list_by_status(PB_STATUS_UNSOLVED);
            break;
        case 'solved': // 已经解决的需求
            $this->load('problem');
            $cachedata = $_ENV['problem']->list_by_status(PB_STATUS_SOLVED);
            break;
        case 'statistics'://首页统计，包含已解决、待解决
            $this->load('problem');
            $cachedata = array();
            $cachedata['solves'] = $this->db->fetch_total('problem', 'status IN (' . PB_STATUS_SOLVED . ')');   //已解决问题数
            $cachedata['nosolves'] = $this->db->fetch_total('problem', 'status=' . PB_STATUS_UNSOLVED); //待解决问题数
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

    function init_crontab()
    {
        $this->load('crontab');
        $crontablist = $this->cache->load("crontab");
        foreach ($crontablist as $crontab) {
            $crontab['available'] && $_ENV['crontab']->$crontab['method']($crontab);
        }
    }

    function load($model, $base = NULL)
    {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require WEB_ROOT . "/model/$model.class.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base);');
        }
        return $_ENV[$model];
    }

    function init_user()
    {
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
            $user['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid=0 AND status<>2");
            $user['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>2");
        }
        $_ENV['user']->refresh_session_time($sid, $user['uid']);
        $user['sid'] = $sid;
        $user['ip'] = $this->ip;
        $user['uid'] && $user['loginuser'] = $user['username'];
        $user['uid'] && $user['avatar'] = get_avatar_dir($user['uid']);
        $this->user = $user;
    }

    function init_problem()
    {
        $this->load('demand');
    }

    // 权限检测
    function checkable($url)
    {
        $this->regular = $url;
        return true;
        if (1 == $this->user['groupid'])
            return true;
        $regulars = explode(',', 'user/login,user/logout,user/code,user/getpass,user/resetpass,index/help,js/view,attach/upload,' . $this->user['regulars']);
        return in_array($url, $regulars);
    }

    // IP禁止
    function banned()
    {
        $ips = $this->cache->load('banned');
        $ips = (bool) $ips ? $ips : array();
        $userip = explode(".", $this->ip);
        foreach ($ips as $ip) {
            $bannedtime = $ip['expiration'] + $ip['time'] - $this->time;
            if ($bannedtime > 0 && ($ip['ip1'] == '*' || $ip['ip1'] == $userip[0]) && ($ip['ip2'] == '*' || $ip['ip2'] == $userip[1]) && ($ip['ip3'] == '*' || $ip['ip3'] == $userip[2]) && ($ip['ip4'] == '*' || $ip['ip4'] == $userip[3])
            ) {
                exit('IP被禁止访问,如有问题请联系:' . $this->setting['admin_email']);
            }
        }
    }

    // 中转提示页面
    function message($message, $url = '')
    {
        $seotitle = '操作提示';
        if ($url == '') {
            $redirect = SITE_URL;
        } else if ($url == 'BACK' || $url == 'STOP') {
            $redirect = $url;
        } else {
            $redirect = SITE_URL . $this->setting['seo_prefix'] . $url . $this->setting['seo_suffix'];
        }
        $tpldir = (0 === strpos($this->get[0], 'admin')) ? 'admin' : $this->setting['tpl_dir'];
        include template('tip', $tpldir);
        exit;
    }

    function send($from, $fromuid, $touid, $subject, $content)
    {
        if ($fromuid == 0) {
            $from = $this->setting['site_name'] . '管理员';
        }

        // 站内消息
        if ($this->setting['notify_message']) {
            $this->db->query("INSERT INTO message SET `from`='$from',`fromuid`=$fromuid,`touid`=$touid,`subject`='$subject',`time`={$this->time},`content`='$content'");
        }

        // 发邮件
        if ($this->setting['notify_mail']) {
            $touser = $this->db->fetch_first("SELECT * FROM user WHERE uid=$uid");
            sendmail($touser['username'], $touser['email'], $title, $content);
        }
    }
















    // 发送通知
    // 一、操作导致状态改变
    // A、当问题被人回答，系统会自动给问题提出者发送通知
    // B、当问题被采纳为答案，回答者会收到消息
    // 
    // 二、时间导致状态改变
    // A、问题变为关闭状态，给提问者发通知
    // 
    // 三、$type说明:
    // 0:问题有新回答
    // 1:回答被采纳
    // 2:问题超时自动关闭
    // 3:回答有新评分
    function send_old($uid, $pid, $type)
    {
        $problem = $this->db->fetch_first("SELECT * FROM problem WHERE pid='$pid'");
        $msgtpl = unserialize($this->setting['msgtpl']);
        //消息模板
        $message = array();
        foreach ($msgtpl[$type] as $msg => $val) {
            $message[$msg] = str_replace('{wtbt}', $problem['title'], $val);
            $message[$msg] = str_replace('{wtms}', $problem['description'], $message[$msg]);
            $message[$msg] = str_replace('{wzmc}', $this->setting['site_name'], $message[$msg]);
            $demand = $this->db->fetch_first("SELECT * FROM demand WHERE uid=$uid AND pid=$pid");
            $message[$msg] = str_replace('{hdnr}', $demand['message'], $message[$msg]);
        }
        $message['content'] .='<br /> <a href="' . url("problem/view/$pid", 1) . '">点击查看需求</a>';
        $time = $this->time;
        $msgfrom = $this->setting['site_name'] . '管理员';
        $touser = $this->db->fetch_first("SELECT * FROM user WHERE uid=$uid");
        //1,3,5,7 允许站内消息
        if ((1 & $touser['isnotify']) && $this->setting['notify_message']) {
            $this->db->query("INSERT INTO message SET `from`='$msgfrom',`fromuid`=0,`touid`=$uid,`subject`='{$message['title']}',`time`=$time,`content`='{$message['content']}'");
        }
        //2,3,6,7 允许发邮件
        if ((2 & $touser['isnotify']) && $this->setting['notify_mail']) {
            sendmail($touser, $message['title'], $message['content']);
        }
        //4,5,6,7 允许发手机短信
    }

    // 检查验证码
    function checkcode()
    {
        $this->load('user');
        if (isset($this->post['code']) && (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code())) {
            $this->message("验证码错误!", 'BACK');
        }
    }
}

?>
