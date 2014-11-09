<?php

!defined('IN_SITE') && exit('Access Denied');

class usermodel {
    public function __construct(& $db) {
        $this->db = & $db;
    }

    public function get_by_uid($uid, $loginstatus=1) {
        $user = $this->db->fetch_first("SELECT * FROM `user` WHERE uid='$uid'");
        $loginstatus && $user['islogin'] = $this->is_login($uid);
        return $user;
    }

    public function get_by_username($username) {
        return $this->db->fetch_first("SELECT * FROM user WHERE username='$username'");
    }

    public function get_by_email($email) {
        return $this->db->fetch_first("SELECT * FROM user WHERE email='$email'");
    }

    public function get_by_name_email($name, $email) {
        return $this->db->fetch_first("SELECT * FROM user WHERE email='$email' AND `username`='$name'");
    }

    public function get_user_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user ORDER BY uid DESC LIMIT $start,$limit");
    }

    public function get_active_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user ORDER BY problems DESC,solved DESC,lastlogin DESC LIMIT $start,$limit");
    }

    public function get_apply_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user_resume r INNER JOIN user u ON r.verified=" . RESUME_APPLY
                                  . " AND r.uid=u.uid ORDER BY r.apply_time ASC LIMIT $start,$limit");
    }

    public function get_lastest_register($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user ORDER BY regtime DESC LIMIT $start,$limit");
    }

    public function get_problems_top($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user ORDER BY problems DESC,lastlogin DESC LIMIT $start,$limit");
    }

    public function list_by_search_condition($condition, $start = 0, $limit = 10) {
        return $this->db->query("SELECT * FROM user WHERE $condition ORDER BY `uid` DESC LIMIT $start,$limit");
    }

    public function refresh_session_time($sid, $ip, $uid, $time='') {
        empty($time) && $time = time();

        $session = $this->db->fetch_first("SELECT * FROM session WHERE sid='$sid'");
        if ($session) {
            $this->db->query("UPDATE session SET `time`='$time',`ip`='$ip' WHERE sid='$sid'");

        } else {
            $this->db->query("INSERT INTO session (sid,`ip`,`uid`,`time`) VALUES ('$sid','$ip','$uid','$time')");
        }
        return $this->db->affected_rows();
    }

    public function update_session($sid, $uid, $islogin, $ip, $time='') {
        empty($time) && $time = time();
        $this->db->query("REPLACE INTO session (sid,uid,islogin,ip,`time`) VALUES ('$sid','$uid','$islogin','$ip','$time')");
        return $this->db->affected_rows();
    }

    // 添加用户，返回新用户id
    public function add($username, $password, $email = '', $uid = 0) {
        $password = md5($password);

        if ($uid) {
            $this->db->query("REPLACE INTO user (uid,username,password,email,regip,`lastlogin`)"
                            .  " VALUES ('$uid','$username','$password','$email','" . getip() . "'," . time() . ")");
        } else {
            $this->db->query("INSERT INTO user(username,password,email,regip,regtime,`lastlogin`)"
                            . " values ('$username','$password','$email','" . getip() . "'," . time() . "," . time() . ")");
            $uid = $this->db->insert_id();
        }
        return $uid;
    }

    //ip地址限制
    public function is_allowed_register() {
        global $setting;
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $usernum = $this->db->result_first("SELECT count(*) FROM user WHERE regip='" . getip() . "' AND regtime>$starttime AND regtime<$endtime ");
        return $usernum < $setting['max_register_num'];
    }

    // 修改用户密码
    public function uppass($uid, $password) {
        $password = md5($password);
        $this->db->query("UPDATE user SET `password`='$password' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    // 更新用户信息
    public function update($uid, $gender, $bday, $phone, $qq, $wechat, $signature) {
        $this->db->query("UPDATE user SET `gender`='$gender',`bday`='$bday',`phone`='$phone',`qq`='$qq',`wechat`='$wechat',`signature`='$signature' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_contact_info($uid, $phone, $qq, $wechat) {
        $this->db->query("UPDATE user SET `phone`='$phone',`qq`='$qq',`wechat`='$wechat' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_email($uid, $email) {
        $this->db->query("UPDATE user SET `email`='$email' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_problem_num($uid, $delta=1) {
        $this->db->query("UPDATE user SET `problems`=`problems`+($delta) WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_paid($uid, $price) {
        $this->db->query("UPDATE user SET `paid`=`paid`+$price WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_lastlogin($uid, $time='') {
        empty($time) && $time = time();
        $this->db->query("UPDATE user SET `lastlogin`='$time' WHERE `uid`='$uid'"); //更新最后登录时间
        return $this->db->affected_rows();
    }

    // 更新用户积分
    public function update_userpoint($uid, $delta) {
        $this->db->query("UPDATE user SET `userpoint`=`userpoint`+($delta) WHERE `uid`='$uid'");
        return $this->db->affected_rows();
    }

    // 用户解决了一个求助
    public function solve_problem($uid, $earned) {
        $this->db->query("UPDATE user SET `solved`=`solved`+1,`earned`=`earned`+$earned WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_solved_num($uid, $delta=1) {
        $this->db->query("UPDATE user SET `solved`=`solved`+($delta) WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_earned($uid, $delta) {
        $this->db->query("UPDATE user SET `earned`=`earned`+($delta) WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_balance($uid, $delta) {
        $this->db->query("UPDATE user SET `balance`=`balance`+($delta) WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_failed($uid, $delta=1) {
        $this->db->query("UPDATE user SET `failed`=`failed`+($delta) WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function update_can_teach($uid, $can_teach) {
        $this->db->query("UPDATE user SET `can_teach`='$can_teach' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    public function logout($sid) {
        tcookie('sid', '', 0);
        tcookie('auth', '', 0);
        if ($sid) {
            $this->db->query("DELETE FROM session WHERE `sid`='$sid'");
        }
    }

    public function save_code($uid, $sid, $code) {
        $islogin = $this->db->result_first("SELECT islogin FROM `session` WHERE sid='$sid'");
        $islogin = $islogin ? $islogin : 0;
        $this->db->query("REPLACE INTO session (sid,uid,code,islogin,`time`) VALUES ('$sid',$uid,'$code','$islogin'," . time() . ")");
        return $this->db->affected_rows();
    }

    public function get_code($sid) {
        return $this->db->result_first("SELECT code FROM session WHERE sid='$sid'");
    }

    public function get_session_by_sid($sid) {
        return $this->db->fetch_first("SELECT * FROM session WHERE sid='$sid'");
    }

    // 获取所有注册用户数目
    public function rownum_alluser() {
        return $this->db->fetch_total('user');
    }

    // 获取所有在线用户数目
    public function rownum_onlineuser() {
        global $setting;
        $end = time() - intval($setting['sum_onlineuser_time']) * 60;
        return $this->db->result_first("SELECT COUNT(DISTINCT `ip`) FROM session WHERE time>$end");
    }

    // 更新authstr 
    public function update_authstr($uid, $authstr) {
        $this->db->query("UPDATE user SET `authstr`='$authstr' WHERE `uid`=$uid");
        return $this->db->affected_rows();
    }

    // 关注帖子
    public function follow_question($qid, $followerid, $follower) {
        $this->db->query("INSERT INTO question_attention(qid,followerid,follower,time) VALUES ($qid,$followerid,'$follower'," . time() . ")");
        $this->db->query("UPDATE question SET attentions=attentions+1 WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    // 关注
    public function follow($sourceid, $followerid, $follower, $type = 'question') {
        $sourcefield = 'qid';
        ($type != 'question') && $sourcefield = 'uid';
        $this->db->query("INSERT INTO $type" . "_attention($sourcefield,followerid,follower,time) VALUES ($sourceid,$followerid,'$follower'," . time() . ")");
        if ($type == 'question') {
            $this->db->query("UPDATE question SET attentions=attentions+1 WHERE `id`=$sourceid");
        } else if ($type == 'user') {
            $this->db->query("UPDATE user SET followers=followers+1 WHERE `uid`=$sourceid");
            $this->db->query("UPDATE user SET attentions=attentions+1 WHERE `uid`=$followerid");
        }
    }

    // 取消关注
    public function unfollow($sourceid, $followerid, $type = 'question') {
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

    private function is_login($uid) {
        global $setting;
        $onlinetime = time() - intval($setting['sum_onlineuser_time']) * 60;

        $islogin = $this->db->result_first("SELECT islogin FROM session WHERE uid=$uid AND time>$onlinetime");
        if ($islogin && $uid > 0) {
            return $islogin;
        }
        return false;
    }

    private $db;
}

?>
