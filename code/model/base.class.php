<?php

!defined('IN_SITE') && exit('Access Denied');

class base {
    var $ip;
    var $time;
    var $db;
    var $cache;
    var $user = array();
    var $setting = array();
    var $category = array();
    var $get = array();
    var $post = array();
    var $regular;

    function base(& $get, & $post) {
        $this->time = time();
        $this->ip = getip();
        $this->get = & $get;
        $this->post = & $post;

        $this->init_db();
        $this->init_cache();
        $this->init_crontab();
        $this->init_user();

        $this->checkcode();
        $this->banned();

        $this->auto_send_goods();
    }

    // 临时解决方案
    function auto_send_goods() {
        $this->load('trade');
        $this->load('ebank');
        $trade_list = $_ENV['trade']->get_trade_by_status(TRADE_STATUS_WAIT_SELLER_SEND_GOODS);

        foreach ($trade_list as $trade) {
            $transaction_id = $trade['transaction_id'];
            $logistics_name = "Boostme物流";
            $invoice_no     = "Boostme物流编号";
            $transport_type = 'EXPRESS'; // POST（平邮）、EXPRESS（快递）、EMS（EMS）

            $send_goods_result = $_ENV['ebank']->alipay_send_goods($transaction_id, $logistics_name, $invoice_no, $transport_type);

            if ($send_goods_result['is_success'] == 'T') {
                $_ENV['trade']->update_trade_status($trade['trade_no'], TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS);
                runlog('alipay', "[INFO] Auto send goods succeed! trade_no=[" . $trade['trade_no'] . "],new_status=[" . TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS . "]", 0);
            } else {
                runlog('alipay', "[INFO] Auto send goods failed!", 0);
            }
        }
    }

    function init_db() {
        $this->db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    }

    // 一旦setting的缓存文件读取失败，则更新所有cache
    function init_cache() {
        global $setting, $badword, $category;
        $this->cache = new cache($this->db);
        $setting = $this->setting = $this->cache->load('setting');
        $badword = $this->cache->load('badword', 'find');
        $category = $this->category = $this->cache->load('category', 'cid');
    }

    function init_crontab() {
        $this->load('crontab');
        $crontablist = $this->cache->load("crontab");
        foreach ($crontablist as $crontab) {
            $crontab['available'] && $_ENV['crontab']->$crontab['method']($crontab);
        }
    }

    function init_user() {
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
            $user['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid=0 AND status<>" . MSG_STATUS_TO_DELETED);
            $user['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>" . MSG_STATUS_TO_DELETED);
        }
        $_ENV['user']->refresh_session_time($sid, $this->ip, $user['uid']);
        $user['sid'] = $sid;
        $user['ip'] = $this->ip;
        $user['uid'] && $user['loginuser'] = $user['username'];
        $user['uid'] && $user['avatar'] = get_avatar_dir($user['uid']);
        $this->user = $user;
    }

    // 检查验证码
    function checkcode() {
        $this->load('user');
        if (isset($this->post['code']) && (strtolower(trim($this->post['code'])) != $_ENV['user']->get_code())) {
            $this->message("验证码错误!", 'BACK');
        }
    }

    // IP禁止
    function banned() {
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

    // 从缓存中读取数据，如果失败，则自动去读取数据然后写入缓存
    function fromcache($cachename, $cachetime = 3) {
        $cachetime = ($this->setting['index_life'] == 0) ? 1 : $this->setting['index_life'] * 60;
        $cachedata = $this->cache->read($cachename, $cachetime);

        if ($cachedata) {
            return $cachedata;
        }

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
            $cachedata = array();
            $cachedata['solves'] = $this->db->fetch_total('problem', 'status IN (' . PB_STATUS_SOLVED . ')');   //已解决问题数
            $cachedata['nosolves'] = $this->db->fetch_total('problem', 'status=' . PB_STATUS_UNSOLVED); //待解决问题数
            $cachedata['all_prob_num'] = $_ENV['problem']->get_all_prob_num(); // 全部求助数量
            $cachedata['all_user_num'] = $_ENV['user']->rownum_alluser();
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

    function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require WEB_ROOT . "/code/model/$model.class.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base);');
        }
        return $_ENV[$model];
    }

    // 中转提示页面
    function message($message, $url = '') {
        $seotitle = '操作提示';
        if ($url == '') {
            $redirect = SITE_URL;
        } else if ($url == 'BACK' || $url == 'STOP') {
            $redirect = $url;
        } else {
            $redirect = SITE_URL . $this->setting['seo_prefix'] . $url . $this->setting['seo_suffix'];
        }
        $tpldir = (0 === strpos($this->get[0], 'admin')) ? 'admin' : 'default';
        include template('tip', $tpldir);
        exit;
    }

    function jump($url, $full=0) {
        if ($full) {
            $jump_url = $url;
        } else {
            $jump_url = SITE_URL . $this->setting['seo_prefix'] . $url . $this->setting['seo_suffix'];
        }
        header("Location: $jump_url");
    }

    function send($from, $fromuid, $touid, $subject, $content) {
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

    // 权限检测
    function checkable($url) {
        $this->regular = $url; // 此项在后面需要使用到

        if ('admin' == substr($this->regular, 0, 5)) {
            return $this->user['isadmin'] == 1;
        }
        return true;
    }
}

?>
