<?php

!defined('IN_SITE') && exit('Access Denied');

class usermodel {
    var $db;
    var $base;

    function usermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid, $loginstatus=1) {
        $user = $this->db->fetch_first("SELECT * FROM `user` WHERE uid='$uid'");
        $user['avatar'] = get_avatar_dir($uid);
        $user['lastlogin'] = tdate($user['lastlogin']);
        $loginstatus && $user['islogin'] = $this->is_login($uid);
        return $user;
    }

    function get_by_username($username) {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE username='$username'");
        return $user;
    }

    function get_by_email($email) {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE email='$email'");
        return $user;
    }

    //找回密码
    function get_by_name_email($name, $email) {
        $user = $this->db->fetch_first("SELECT * FROM user WHERE email='$email' AND `username`='$name'");
        return $user;
    }

    function get_user_list($start=0, $limit=10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY uid DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $user['lastlogintime'] = tdate($user['lastlogin']);
            $user['regtime'] = tdate($user['regtime']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_active_list($start=0, $limit=10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY problems DESC,solved DESC,lastlogin DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_apply_list($start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM user_resume r INNER JOIN user u ON r.verified=" . RESUME_APPLY . " AND r.uid=u.uid ORDER BY r.apply_time ASC LIMIT $start,$limit");

        $userlist = array();
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $user['apply_time'] = tdate($user['apply_time']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_lastest_register($start=0, $limit=10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY regtime DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function get_problems_top($start=0, $limit=10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user ORDER BY problems DESC,lastlogin DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function list_by_search_condition($condition, $start = 0, $limit = 10) {
        $userlist = array();
        $query = $this->db->query("SELECT * FROM user WHERE $condition ORDER BY `uid` DESC LIMIT $start,$limit");
        while ($user = $this->db->fetch_array($query)) {
            $user['avatar'] = get_avatar_dir($user['uid']);
            $user['regtime'] = tdate($user['regtime']);
            $user['lastlogintime'] = tdate($user['lastlogin']);
            $userlist[] = $user;
        }
        return $userlist;
    }

    function refresh($uid, $islogin=1, $cookietime=0) {
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

    function refresh_session_time($sid, $ip, $uid) {
        $lastrefresh = tcookie("lastrefresh");
        if (!$lastrefresh) {
            if ($uid) {
                $this->db->query("UPDATE session SET `time` = {$this->base->time},`ip`='$ip' WHERE sid='$sid'");
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
    function add($username, $password, $email = '', $uid = 0) {
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
    function is_allowed_register() {
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $usernum = $this->db->result_first("SELECT count(*) FROM user WHERE regip='{$this->base->ip}' AND regtime>$starttime AND regtime<$endtime ");
        if ($usernum >= $this->base->setting['max_register_num']) {
            return false;
        }
        return true;
    }

    // 修改用户密码
    function uppass($uid, $password) {
        $password = md5($password);
        $this->db->query("UPDATE user SET `password`='$password' WHERE `uid`=$uid");
    }

    // 更新用户信息
    function update($uid, $gender, $bday, $phone, $qq, $wechat, $signature) {
        $this->db->query("UPDATE user SET `gender`='$gender',`bday`='$bday',`phone`='$phone',`qq`='$qq',`wechat`='$wechat',`signature`='$signature' WHERE `uid`=$uid");
    }

    function update_contact_info($uid, $phone, $qq, $wechat) {
        $this->db->query("UPDATE user SET `phone`='$phone',`qq`='$qq',`wechat`='$wechat' WHERE `uid`=$uid");
    }

    function update_email($uid, $email) {
        $this->db->query("UPDATE user SET `email`='$email' WHERE `uid`=$uid");
    }

    function update_problem_num($uid, $delta=1) {
        $this->db->query("UPDATE user SET `problems`=`problems`+($delta) WHERE `uid`=$uid");
    }

    function update_paid($uid, $price) {
        $this->db->query("UPDATE user SET `paid`=`paid`+$price WHERE `uid`=$uid");
    }

    // 用户解决了一个求助
    function solve_problem($uid, $earned) {
        $this->db->query("UPDATE user SET `solved`=`solved`+1,`earned`=`earned`+$earned WHERE `uid`=$uid");
    }

    function update_solved_num($uid, $delta=1) {
        $this->db->query("UPDATE user SET `solved`=`solved`+($delta) WHERE `uid`=$uid");
    }

    function update_earned($uid, $delta) {
        $this->db->query("UPDATE user SET `earned`=`earned`+($delta) WHERE `uid`=$uid");
    }

    function update_failed($uid, $delta=1) {
        $this->db->query("UPDATE user SET `failed`=`failed`+($delta) WHERE `uid`=$uid");
    }

    function update_can_teach($uid, $can_teach) {
        $this->db->query("UPDATE user SET `can_teach`='$can_teach' WHERE `uid`=$uid");
    }

    // 删除用户
    function remove_users($uids, $all = 0) {
        // 需要进一步完善
        $this->db->query("DELETE FROM `user` WHERE `uid` IN ($uids)");
        // 删除求助
        if ($all) {
            //$this->db->query("DELETE FROM `problem` WHERE `authorid` IN ($uids)");
        }
    }

    function logout() {
        $sid = $this->base->user['sid'];
        tcookie('sid', '', 0);
        tcookie('auth', '', 0);
        tcookie('loginuser', '', 0);
        if ($sid) {
            $this->db->query("DELETE FROM session WHERE `sid`='$sid'");
        }
    }

    function save_code($code) {
        $uid = $this->base->user['uid'];
        $sid = $this->base->user['sid'];
        $islogin = $this->db->result_first("SELECT islogin FROM session WHERE sid='$sid'");
        $islogin = $islogin ? $islogin : 0;
        $this->db->query("REPLACE INTO session (sid,uid,code,islogin,`time`) VALUES ('$sid',$uid,'$code','$islogin',{$this->base->time})");
    }

    function get_code() {
        $sid = $this->base->user['sid'];
        return $this->db->result_first("SELECT code FROM session WHERE sid='$sid'");
    }

    function get_session_by_sid($sid) {
        return $this->db->fetch_first("SELECT * FROM session WHERE sid='$sid'");
    }

    function is_login($uid = 0) {
        (!$uid) && $uid = $this->base->user['uid'];
        $onlinetime = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        $islogin = $this->db->result_first("SELECT islogin FROM session WHERE uid=$uid AND time>$onlinetime");
        if ($islogin && $uid > 0) {
            return $islogin;
        }
        return false;
    }

    // 检测用户名合法性
    function check_usernamecensor($username) {
        $censorusername = $this->base->setting['censor_username'];
        $censorexp = '/^(' . str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($censorusername = trim($censorusername)), '/')) . ')$/i';
        if ($censorusername && preg_match($censorexp, $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // 检测邮件地址合法性
    function check_emailaccess($email) {
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

    // 获取所有注册用户数目
    function rownum_alluser() {
        return $this->db->fetch_total('user', ' 1=1 ');
    }

    // 获取所有在线用户数目
    function rownum_onlineuser() {
        $end = $this->base->time - intval($this->base->setting['sum_onlineuser_time']) * 60;
        return $this->db->result_first("SELECT COUNT(DISTINCT `ip`) FROM session WHERE time>$end");
    }

    // 更新authstr 
    function update_authstr($uid, $authstr) {
        $this->db->query("UPDATE user SET `authstr`='$authstr' WHERE `uid`=$uid");
    }

    // 关注帖子
    function follow_question($qid, $followerid, $follower) {
        $this->db->query("INSERT INTO question_attention(qid,followerid,follower,time) VALUES ($qid,$followerid,'$follower',{$this->base->time})");
        $this->db->query("UPDATE question SET attentions=attentions+1 WHERE `qid`=$qid");
    }

    // 关注
    function follow($sourceid, $followerid, $follower, $type = 'question') {
        $sourcefield = 'qid';
        ($type != 'question') && $sourcefield = 'uid';
        $this->db->query("INSERT INTO $type" . "_attention($sourcefield,followerid,follower,time) VALUES ($sourceid,$followerid,'$follower',{$this->base->time})");
        if ($type == 'question') {
            $this->db->query("UPDATE question SET attentions=attentions+1 WHERE `id`=$sourceid");
        } else if ($type == 'user') {
            $this->db->query("UPDATE user SET followers=followers+1 WHERE `uid`=$sourceid");
            $this->db->query("UPDATE user SET attentions=attentions+1 WHERE `uid`=$followerid");
        }
    }

    // 取消关注
    function unfollow($sourceid, $followerid, $type = 'question') {
        $sourcefield = 'qid';
        ($type != 'question') && $sourcefield = 'uid';
        $this->db->query("DELETE FROM " . $type . "_attention WHERE $sourcefield=$sourceid AND followerid=$followerid");
        if ($type == 'question') {
            $this->db->query("UPDATE question SET attentions=attentions-1 WHERE `id`=$sourceid");
        } else if ($type == 'user') {
            $this->db->query("UPDATE user SET followers=followers-1 WHERE `uid`=$sourceid");
            $this->db->query("UPDATE user SET attentions=attentions-1 WHERE `uid`=$followerid");
        }
    }
}

?>
