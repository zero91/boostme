<?php

!defined('IN_SITE') && exit('Access Denied');

class usermodel
{
    var $db;
    var $base;

    function usermodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid, $loginstatus = 1)
    {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE uid='$uid'");
        $user['avatar'] = get_avatar_dir($uid);
        $user['lastlogin'] = tdate($user['lastlogin']);
        $loginstatus && $user['islogin'] = $this->is_login($uid);
        return $user;
    }

    function get_by_username($username)
    {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE username='$username'");
        return $user;
    }

    function get_by_email($email)
    {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE email='$email'");
        return $user;
    }

    //找回密码
    function get_by_name_email($name, $email)
    {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE email='$email' AND `username`='$name'");
        return $user;
    }

    function get_list($start = 0, $limit = 10)
    {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY uid DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['lastlogintime'] = tdate($user['lastlogin']);
            $user['regtime'] = tdate($user['regtime']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_active_list($start = 0, $limit = 10)
    {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY problems DESC,lastlogin DESC,solved DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_lastest_register($start = 0, $limit = 5)
    {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY regtime DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_problems_top($start = 0, $limit = 8)
    {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY problems DESC,lastlogin DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $userlist[] = $user;
        }
        return $userlist;
    }

    function list_by_search_condition($condition, $start = 0, $limit = 10)
    {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user WHERE $condition ORDER BY `uid` DESC LIMIT $start , $limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['regtime'] = tdate($user['regtime']);
            $user['lastlogintime'] = tdate($user['lastlogin']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function refresh($uid, $islogin = 1, $cookietime = 0)
    {
        @$sid = tcookie('sid');
        $this->base->user = $this->db->fetch_first("SELECT * FROM user WHERE `uid`='$uid'");
        $this->db->query("UPDATE user SET `lastlogin`={$this->base->time}  WHERE `uid`=$uid"); //更新最后登录时间
        $this->db->query("REPLACE INTO session (sid,uid,islogin,ip,`time`) VALUES ('$sid',$uid,$islogin,'{$this->base->ip}',{$this->base->time})");
        $password = $this->base->user['password'];
        $auth = strcode("$uid\t$password", $this->base->setting['auth_key'], 'ENCODE');

        list($uid, $password) = empty($auth) ? array(0, 0) : taddslashes(explode("\t", strcode($auth, $this->setting['auth_key'], 'DECODE')), 1);
        if ($cookietime)
            tcookie('auth', $auth, $cookietime);
        else
            tcookie('auth', $auth);

        tcookie('loginuser', '');
        $this->base->user['newmsg'] = 0;
    }

    function refresh_session_time($sid, $uid)
    {
        $lastrefresh = tcookie("lastrefresh");
        if (!$lastrefresh) {
            if ($uid) {
                $this->db->query("UPDATE session SET `time` = {$this->base->time} WHERE sid='$sid'");
            } else {
                $session = $this->db->fetch_first("SELECT * FROM session WHERE sid='$sid'");
                if ($session) {
                    $this->db->query("UPDATE session SET `time` = {$this->base->time} WHERE sid='$sid'");
                } else {
                    $this->db->query("INSERT INTO session (sid,`ip`,`time`) VALUES ('$sid','{$this->base->ip}',{$this->base->time})");
                }
            }
            tcookie("lastrefresh", '1', 60);
        }
    }

    // 添加用户，本函数需要返回uid
    function add($username, $password, $email = '', $uid = 0)
    {
        $password = md5($password);
        if ($uid) {
            $this->db->query("REPLACE INTO user (uid,username,password,email,regip,`lastlogin`) VALUES ('$uid','$username','$password','$email','" . getip() . "',{$this->base->time})");
        } else {
            $this->db->query("INSERT INTO user(username,password,email,regip,regtime,`lastlogin`) values ('$username','$password','$email','" . getip() . "',{$this->base->time},{$this->base->time})");
            $uid = $this->db->insert_id();
        }
        return $uid;
    }

    //ip地址限制
    function is_allowed_register()
    {
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $usernum = $this->db->result_first("SELECT count(*) FROM user WHERE regip='{$this->base->ip}' AND regtime>$starttime AND regtime<$endtime ");
        if ($usernum >= $this->base->setting['max_register_num']) {
            return false;
        }
        return true;
    }

    // 修改用户密码
    function uppass($uid, $password)
    {
        $password = md5($password);
        $this->db->query("UPDATE user SET `password`='$password' WHERE `uid`=$uid ");
    }

    // 更新用户信息
    function update($uid, $gender, $bday, $phone, $qq, $wechat, $signature)
    {
        $this->db->query("UPDATE user SET `gender`='$gender',`bday`='$bday',`phone`='$phone',`qq`='$qq',`wechat`='$wechat',`signature`='$signature' WHERE `uid`=$uid");
    }

    function update_email($uid, $email)
    {
        $this->db->query("UPDATE user SET `email`='$email' WHERE `uid`=$uid");
    }

    function update_realname($uid, $realname)
    {
        $this->db->query("UPDATE user SET `realname`='$realname' WHERE `uid`='$uid'");
    }

    // 更新authstr
    function update_authstr($uid, $authstr)
    {
        $this->db->query("UPDATE user SET `authstr`='$authstr' WHERE `uid`=$uid");
    }

    function update_problems($uid)
    {
        $this->db->query("UPDATE user SET `problems`=`problems`+1 WHERE `uid`=$uid");
    }

    // 用户发表了新求助
    function add_problem($uid, $price)
    {
        $this->db->query("UPDATE user SET `problems`=`problems`+1,`balance`=`balance`-$price WHERE `uid`=$uid");
    }

    // 删除用户
    function remove($uids, $all = 0)
    {
        // 需要进一步完善
        $this->db->query("DELETE FROM `user` WHERE `uid` IN ($uids)");
        // 删除问题和回答
        if ($all) {
            $this->db->query("DELETE FROM `problem` WHERE `authorid` IN ($uids)");
        }
    }

    function logout()
    {
        $sid = $this->base->user['sid'];
        tcookie('sid', '', 0);
        tcookie('auth', '', 0);
        tcookie('loginuser', '', 0);
        if ($sid) {
            $this->db->query("DELETE FROM session WHERE sid='$sid'");
        }
    }

    function save_code($code)
    {
        $uid = $this->base->user['uid'];
        $sid = $this->base->user['sid'];
        $islogin = $this->db->result_first("SELECT islogin FROM session WHERE sid='$sid'");
        $islogin = $islogin ? $islogin : 0;
        $this->db->query("REPLACE INTO session (sid,uid,code,islogin,`time`) VALUES ('$sid',$uid,'$code','$islogin',{$this->base->time})");
    }

    function get_code()
    {
        $sid = $this->base->user['sid'];
        return $this->db->result_first("SELECT code FROM session WHERE sid='$sid'");
    }

    function is_login($uid = 0)
    {
        (!$uid) && $uid = $this->base->user['uid'];
        $onlinetime = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        $islogin = $this->db->result_first("SELECT islogin FROM session WHERE uid=$uid AND time>$onlinetime");
        if ($islogin && $uid > 0) {
            return $islogin;
        }
        return false;
    }

    // 检测用户名合法性
    function check_usernamecensor($username)
    {
        $censorusername = $this->base->setting['censor_username'];
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censorusername = trim($censorusername)), '/')) . ')$/i';
        if ($censorusername && preg_match($censorexp, $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // 检测邮件地址合法性
    function check_emailaccess($email)
    {
        $setting = $this->base->setting;
        $accessemail = $setting['access_email'];
        $censoremail = $setting['censor_email'];
        $accessexp = '/(' . str_replace("\r\n", '|', preg_quote(trim($accessemail), '/')) . ')$/i';
        $censorexp = '/(' . str_replace("\r\n", '|', preg_quote(trim($censoremail), '/')) . ')$/i';
        if ($accessemail || $censoremail) {
            if (($accessemail && !preg_match($accessexp, $email)) || ($censoremail && preg_match($censorexp, $email))) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    function get_login_auth($uid, $type = 'wechat')
    {
        return $this->db->fetch_first("SELECT * FROM login_auth WHERE type='$type' AND uid=$uid");
    }

    function remove_login_auth($uid, $type='wechat')
    {
        $this->db->query("DELETE FROM login_auth WHERE type='$type' AND uid=$uid");
    }

    // 获取所有注册用户数目
    function rownum_alluser()
    {
        return array($this->db->fetch_total('user', ' 1=1'));
    }

    // 获取所有在线用户数目
    function rownum_onlineuser()
    {
        $end = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        return array($this->db->result_first("SELECT COUNT(DISTINCT `ip`) FROM session WHERE time>$end"));
    }
}

?>
