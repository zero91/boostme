<?php

!defined('IN_SITE') && exit('Access Denied');

class usercontrol extends base {

    function usercontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('user');
        $this->load('userskill');
        $this->load('problem');
        $this->load('userresume');
        $this->load("education");
    }

    function ondefault() {
        $this->onscore();
    }

    function oncode() {
        ob_clean();
        $code = random(4);
        $_ENV['user']->save_code(strtolower($code));
        makecode($code);
    }

    function onregister() {
        if ($this->user['uid']) {
            header("Location:" . SITE_URL);
        }

        $navtitle = '注册新用户';
        if (!$this->setting['allow_register']) {
            $this->message("系统注册功能暂时处于关闭状态!", 'STOP');
        }

        if (isset($this->base->setting['max_register_num']) && $this->base->setting['max_register_num'] && !$_ENV['user']->is_allowed_register()) {
            $this->message("您的当前的IP已经超过当日最大注册数目，如有疑问请联系管理员!", 'STOP');
            exit;
        }

        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        if (isset($this->post['submit'])) {
            $username = trim($this->post['username']);
            $password = trim($this->post['password']);
            $email = $this->post['email'];

            if ('' == $username || '' == $password) {
                $this->message("用户名或密码不能为空!", 'user/register');
            } else if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $email)) {
                $this->message("邮件地址不合法!", 'user/register');
            } else if ($this->db->fetch_total('user', " email='$email' ")) {
                $this->message("此邮件地址已经注册!", 'user/register');
            } else if (!$_ENV['user']->check_usernamecensor($username)) {
                $this->message("用户名不合法!", 'user/register');
            }
            $this->setting['code_register'] && $this->checkcode(); //检查验证码

            $user = $_ENV['user']->get_by_username($username);
            $user && $this->message("用户名 $username 已经存在!", 'user/register');

            $uid = $_ENV['user']->add($username, $password, $email);
            $_ENV['user']->refresh($uid);

            //发送邮件通知
            $subject = "恭喜您在" . $this->setting['site_name'] . "注册成功！";
            $message = '<p>现在您可以登录<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>，如有困难需要别人帮助，您可以自由发出您的求助信息。祝您使用愉快！</p>';
            sendmail($username, $email, $subject, $message);
            $this->message('恭喜，注册成功！');
        }
        include template('register');
    }

    function onlogin() {
        if ($this->user['uid']) {
            header("Location:" . SITE_URL);
        }

        $navtitle = '用户登录';
        if (isset($this->post['submit'])) {
            $username = trim($this->post['username']);
            $password = md5($this->post['password']);
            $cookietime = intval($this->post['cookietime']);
            $forward = isset($this->post['forward']) ? $this->post['forward'] : SITE_URL; 

            $this->setting['code_login'] && $this->checkcode(); //检查验证码
            $user = $_ENV['user']->get_by_username($username);
            if (is_array($user) && ($password == $user['password'])) {
                $_ENV['user']->refresh($user['uid'], 1, $cookietime);
                header("Location:" . $forward);
            } else {
                $this->message('用户名或密码错误！', 'user/login');
            }
        } else {
            $forward = isset($_SERVER['HTTP_REFERER'])  ? $_SERVER['HTTP_REFERER'] : SITE_URL;
            include template('login');
        }
    }

    // 用于ajax登录
    function onajaxlogin() {
        $username = $this->post['username'];
        if (WEB_CHARSET == 'GBK') {
            require_once(WEB_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $password = md5($this->post['password']);
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user) && ($password == $user['password'])) {
            exit('1');
        }
        exit('-1');
    }

    // 用于ajax检测用户名是否存在
    function onajaxusername() {
        $username = $this->post['username'];
        if (WEB_CHARSET == 'GBK') {
            require_once(WEB_ROOT . '/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }
        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user))
            exit('-1');
        $usernamecensor = $_ENV['user']->check_usernamecensor($username);
        if (FALSE == $usernamecensor)
            exit('-2');
        exit('1');
    }

    // 用于ajax检测用户名是否存在
    function onajaxemail() {
        $email = $this->post['email'];
        $user = $_ENV['user']->get_by_email($email);
        if (is_array($user))
            exit('-1');
        $emailaccess = $_ENV['user']->check_emailaccess($email);
        if (FALSE == $emailaccess)
            exit('-2');
        exit('1');
    }

    // 用于ajax检测验证码是否匹配
    function onajaxcode() {
        $code = strtolower(trim($this->get[2]));
        if ($code == $_ENV['user']->get_code()) {
            exit('1');
        }
        exit('0');
    }

    // 退出系统
    function onlogout() {
        $navtitle = '登出系统';
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $_ENV['user']->logout();
        $this->message('成功退出！');
    }

    function ondemand() {
        $navtitle = '我的求助';
        $status = intval($this->get[2]);
        @$page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $pagesize = 4;
        $startindex = ($page - 1) * $pagesize; //每页面显示$pagesize条
        $problemlist = $_ENV['problem']->list_by_uid($this->user['uid'], $status, $startindex, $pagesize);
        $problemtotal = intval($this->db->fetch_total('problem', 'authorid=' . $this->user['uid'] . $_ENV['problem']->statustable[$status]));

        $departstr = page($problemtotal, $pagesize, $page, "user/demand/$status");
        include template('mydemand');
    }

    // 用户个人空间
    function onspace() {
        //需要用户登录才能够看到用户空间的内容
        if ($this->user['uid'] == 0) {
            $this->message("请先登录!", "user/login");
        }

        $navtitle = "个人空间";

        if (empty($this->get[2])) {
            $userid = $this->user['uid'];
        } else {
            $userid = intval($this->get[2]);
        }

        $member = $_ENV['user']->get_by_uid($userid);
        $member['avatar'] = get_avatar_dir($userid);
        if ($member) {
            $uid_prob_list = $_ENV['problem']->list_by_uid($userid, 'all');
            $uid_solve_list = $_ENV['problem']->list_by_solverid($userid);

            $prob_idx = 0;
            $solve_idx = 0;

            $problemlist = array();
            while ($prob_idx < count($uid_prob_list) && $solve_idx < count($uid_solve_list)) {
                if (intval($uid_prob_list[$prob_idx]['time']) > intval($uid_solve_list[$solve_idx]['time'])) {
                    $uid_prob_list[$prob_idx]['data_type'] = 'prob';
                    $problemlist[] = $uid_prob_list[$prob_idx];
                    $prob_idx += 1;
                } else {
                    $uid_prob_list[$prob_idx]['data_type'] = 'solved';
                    $problemlist[] = $uid_solve_list[$solve_idx];
                    $solve_idx += 1;
                }
            }
            while ($prob_idx < count($uid_prob_list)) {
                $uid_prob_list[$prob_idx]['data_type'] = 'prob';
                $problemlist[] = $uid_prob_list[$prob_idx];
                $prob_idx += 1;
            }
            while ($solve_idx < count($uid_solve_list)) {
                $uid_prob_list[$solve_idx]['data_type'] = 'solved';
                $problemlist[] = $uid_solve_list[$solve_idx];
                $solve_idx += 1;
            }
            $navtitle = $member['username'] . $navtitle;
            include template('space');
        } else {
            $this->message("抱歉，该用户不存在！", 'BACK');
        }
    }

    function onproblem() {
        if (empty($this->get[2])) {
            $this->message("非法提交，缺少参数!", 'BACK');
        }

        $op_type = $this->get[2];

        if ($op_type == "all") {
            $problemlist = $_ENV['problem']->list_by_uid($this->user['uid']);
            include template('userprob');
        } else if ($op_type == "solved") {
            $problemlist = $_ENV['problem']->list_by_solverid($this->user['uid']);
            include template('userprob');
        }
    }

    // 找回密码
    function ongetpass() {
        $navtitle = '找回密码';
        if (isset($this->post['submit'])) {
            $email = $this->post['email'];
            $name = $this->post['username'];
            $this->checkcode(); //检查验证码
            $touser = $_ENV['user']->get_by_name_email($name, $email);
            if ($touser) {
                $authstr = strcode($touser['username'], $this->setting['auth_key']);
                $_ENV['user']->update_authstr($touser['uid'], $authstr);
                $getpassurl = SITE_URL . '?user/resetpass/' . urlencode($authstr);

                $subject = "找回您在" . $this->setting['site_name'] . "的密码";
                $message = '<p>如果您没有进行密码找回的操作，请忽略此邮件。</p><p>如果是您在<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>的密码丢失，请点击下面的链接找回：</p><p><a swaped="true" target="_blank" href="' . $getpassurl . '">' . $getpassurl . '</a></p><p>如果直接点击无法打开，请复制链接地址，在新的浏览器窗口里打开。</p>';
                sendmail($touser['username'], $touser['email'], $subject, $message);
                $this->message("找回密码的邮件已经发送到你的邮箱，请查收!", 'BACK');
            }
            $this->message("用户名或邮箱填写错误，请核实!", 'BACK');
        }
        include template('getpass');
    }

    // 重置密码
    function onresetpass() {
        $navtitle = '重置密码';
        @$authstr = $this->get[2] ? $this->get[2] : $this->post['authstr'];
        if (empty($authstr)) {
            $this->message("非法提交，缺少参数!", 'BACK');
        }

        $authstr = urldecode($authstr);
        $username = strcode($authstr, $this->setting['auth_key'], 'DECODE');
        $theuser = $_ENV['user']->get_by_username($username);

        if (!$theuser || ($authstr != $theuser['authstr'])) {
            $this->message("本网址已过期，请重新使用找回密码的功能!", 'BACK');
        }

        if (isset($this->post['submit'])) {
            $password = $this->post['password'];
            $repassword = $this->post['repassword'];
            if (strlen($password) < 6) {
                $this->message("密码长度不能少于6位!", 'BACK');
            }
            if ($password != $repassword) {
                $this->message("两次密码输入不一致!", 'BACK');
            }
            $_ENV['user']->uppass($theuser['uid'], $password);
            $_ENV['user']->update_authstr($theuser['uid'], '');
            $this->message("重置密码成功，请使用新密码登录!", "user/login");
        }
        include template('resetpass');
    }

    // 个人中心修改资料
    function onprofile() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }
        $navtitle = '个人资料';
        if (isset($this->post['submit'])) {
            $gender = $this->post['gender'];
            $bday = $this->post['birthday'];
            $phone = $this->post['phone'];
            $qq = $this->post['qq'];
            $wechat = $this->post['wechat'];
            $signature = $this->post['signature'];
            if (($this->post['email'] != $this->user['email']) && (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $this->post['email']) || $this->db->fetch_total('user', " email='" . $this->post['email'] . "' "))) {
                $this->message("邮件格式不正确或已被占用!", 'user/profile');
            }
            $_ENV['user']->update($this->user['uid'], $gender, $bday, $phone, $qq, $wechat, $signature);

            if (isset($this->post['skills']) ) {
                $skillstr = trim($this->post['skills']);
                !empty($skillstr) && $skill_list = explode(" ", $this->post['skills']);
            }

            $skill_list && $_ENV['userskill']->multi_add(array_unique($skill_list), $this->user['uid']);
            isset($this->post['email']) && $_ENV['user']->update_email($this->user['uid'], $this->post['email']);
            $this->message("个人资料更新成功", 'user/profile');
        } else {
            $skill_list  = $_ENV['userskill']->get_by_uid($this->user['uid']);
            $skillstr = implode(" ", $skill_list);
        }
        include template('profile');
    }

    function onuppass() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        $navtitle = "修改密码";
        if (isset($this->post['submit'])) {
            if (trim($this->post['newpwd']) == '') {
                $this->message("新密码不能为空！", 'user/uppass');
            } else if (trim($this->post['newpwd']) != trim($this->post['confirmpwd'])) {
                $this->message("两次输入不一致", 'user/uppass');
            } else if (trim($this->post['oldpwd']) == trim($this->post['newpwd'])) {
                $this->message('新密码不能跟当前密码重复!', 'user/uppass');
            } else if (md5(trim($this->post['oldpwd'])) == $this->user['password']) {
                $_ENV['user']->uppass($this->user['uid'], trim($this->post['newpwd']));
                $this->message("密码修改成功,请重新登录系统!", 'user/login');
            } else {
                $this->message("旧密码错误！", 'user/uppass');
            }
        }
        include template('uppass');
    }

    function onupload_resume() {
        if (0 == $this->user['uid']) {
            return;
        }

        if (isset($_FILES["userresume"])) {
            $uid = intval($this->get[2]);
            $resumedir = "/data/userdata/resume/";
            $extname = extname($_FILES["userresume"]["name"]);
            $uid = abs($uid);
            $uid = sprintf("%010d", $uid);
            $dir1 = $resumedir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 3);
            $dir3 = $dir2 . '/' . substr($uid, 6, 2);

            (!is_dir(WEB_ROOT . $dir1)) && forcemkdir(WEB_ROOT . $dir1);
            (!is_dir(WEB_ROOT . $dir2)) && forcemkdir(WEB_ROOT . $dir2);
            (!is_dir(WEB_ROOT . $dir3)) && forcemkdir(WEB_ROOT . $dir3);

            $file_web_path = $dir3 . "/{$uid}.{$extname}";

            $upload_target_fname = WEB_ROOT . $file_web_path;
            if (file_exists($upload_target_fname)) { //删除现有简历
                unlink($upload_target_fname);
            }

            if (move_uploaded_file($_FILES["userresume"]["tmp_name"], $upload_target_fname)) {
                $_ENV['userresume']->update_resume($uid, substr($file_web_path, 1));
                //echo 'ok';
                echo substr($file_web_path, 1);
            }
        }
    }

    function onupload_ID() {
        if (0 == $this->user['uid']) {
            return;
        }
        $uid = intval($this->get[2]);
        $resumedir = "/data/userdata/ID/";
        $extname = extname($_FILES["userID"]["name"]);
        $uid = abs($uid);
        $uid = sprintf("%010d", $uid);
        $dir1 = $resumedir . substr($uid, 0, 3);
        $dir2 = $dir1 . '/' . substr($uid, 3, 3);
        $dir3 = $dir2 . '/' . substr($uid, 6, 2);

        (!is_dir(WEB_ROOT . $dir1)) && forcemkdir(WEB_ROOT . $dir1);
        (!is_dir(WEB_ROOT . $dir2)) && forcemkdir(WEB_ROOT . $dir2);
        (!is_dir(WEB_ROOT . $dir3)) && forcemkdir(WEB_ROOT . $dir3);

        $file_web_path = $dir3 . "/{$uid}.{$extname}";

        $upload_target_fname = WEB_ROOT . $file_web_path;
        if (file_exists($upload_target_fname)) { //删除现有身份证照片
            unlink($upload_target_fname);
        }

        if (move_uploaded_file($_FILES["userID"]["tmp_name"], $upload_target_fname)) {
            $_ENV['userresume']->update_ID_path($uid, substr($file_web_path, 1));
            //echo 'ok';
            echo substr($file_web_path, 1);
        }
    }

    function onupload_studentID() {
        if (0 == $this->user['uid']) {
            return;
        }
        $uid = intval($this->get[2]);
        $resumedir = "/data/userdata/studentID/";
        $extname = extname($_FILES["studentID"]["name"]);
        $uid = abs($uid);
        $uid = sprintf("%010d", $uid);
        $dir1 = $resumedir . substr($uid, 0, 3);
        $dir2 = $dir1 . '/' . substr($uid, 3, 3);
        $dir3 = $dir2 . '/' . substr($uid, 6, 2);

        (!is_dir(WEB_ROOT . $dir1)) && forcemkdir(WEB_ROOT . $dir1);
        (!is_dir(WEB_ROOT . $dir2)) && forcemkdir(WEB_ROOT . $dir2);
        (!is_dir(WEB_ROOT . $dir3)) && forcemkdir(WEB_ROOT . $dir3);

        $file_web_path = $dir3 . "/{$uid}.{$extname}";

        $upload_target_fname = WEB_ROOT . $file_web_path;
        if (file_exists($upload_target_fname)) { //删除现有身份证照片
            unlink($upload_target_fname);
        }

        if (move_uploaded_file($_FILES["studentID"]["tmp_name"], $upload_target_fname)) {
            $_ENV['userresume']->update_studentID($uid, substr($file_web_path, 1));
            //echo 'ok';
            echo substr($file_web_path, 1);
        }
    }

    function oneditimg() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        if (isset($_FILES["userimage"])) {
            $uid = intval($this->get[2]);
            $avatardir = "/data/avatar/";
            $extname = extname($_FILES["userimage"]["name"]);
            if (!isimage($extname))
                exit('type_error');
            $upload_tmp_file = WEB_ROOT . '/data/tmp/user_avatar_' . $uid . '.' . $extname;
            $uid = abs($uid);
            $uid = sprintf("%010d", $uid);
            $dir1 = $avatardir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 3);
            $dir3 = $dir2 . '/' . substr($uid, 6, 2);
            (!is_dir(WEB_ROOT . $dir1)) && forcemkdir(WEB_ROOT . $dir1);
            (!is_dir(WEB_ROOT . $dir2)) && forcemkdir(WEB_ROOT . $dir2);
            (!is_dir(WEB_ROOT . $dir3)) && forcemkdir(WEB_ROOT . $dir3);
            $smallimg = $dir3 . "/small_" . $uid . '.' . $extname;
            if (move_uploaded_file($_FILES["userimage"]["tmp_name"], $upload_tmp_file)) {
                $avatar_dir = glob(WEB_ROOT . $dir3 . "/small_{$uid}.*");
                foreach ($avatar_dir as $imgfile) {
                    if (strtolower($extname) != extname($imgfile))
                        unlink($imgfile);
                }
                if (image_resize($upload_tmp_file, WEB_ROOT . $smallimg, 80, 80))
                    echo 'ok';
            }
        } else {
            include template("editimg");
        }
    }

    // 维护个人简历
    function onresume() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        $navtitle = "完善简历";
        if (isset($this->post['submit'])) {
            $realname = trim($this->post['realname']);
            $ID = trim($this->post['ID']);
            $experience = trim($this->post['experience']);

            $_ENV['userresume']->update($this->user['uid'], $realname, $ID, $experience);

            $edu_num = min(count($this->post['school']), 6);
            $edu_list = array();
            for ($i = 0; $i < $edu_num; ++$i) {
                $edu_list[] = array('edu_type'=> $this->post['edu_type'][$i],
                                      'school'=> $this->post['school'][$i],
                                        'dept'=> $this->post['dept'][$i],
                                       'major'=> $this->post['major'][$i],
                                  'start_time'=> $this->post['start_time'][$i],
                                    'end_time'=> $this->post['end_time'][$i]);
            }
            $_ENV['education']->remove_by_uid($this->user['uid']);
            $_ENV['education']->multi_add($this->user['uid'], $edu_list);

            if ($_ENV['userresume']->already_id_used($this->user['uid'], $ID)) {
                $this->message("您填写的身份证号不符合条件，请确认您填写了正确的身份证号", "STOP");
            }

            if ($this->post['operation'] == APPLY) {
                $_ENV['userresume']->update_verify($this->user['uid'], APPLY);
                $this->message("提交请求成功，Boostme将以最快的速度审核您的材料！", 'BACK');
            } else {
                $this->message("简历更改成功！", 'BACK');
            }
        } else {
            $resume = $_ENV['userresume']->get_by_uid($this->user['uid']);
            $edu_list = $_ENV['education']->get_by_uid($this->user['uid']);
        }
        include template("resume");
    }

    function onajaxpoplogin() {
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        include template("poplogin");
    }

    // 用户查看下详细信息 
    function onajaxuserinfo() {
        $uid = intval($this->get[2]);
        if ($uid) {
            $userinfo = $_ENV['user']->get_by_uid($uid);
            $userresume = $_ENV['userresume']->get_by_uid($uid);
            $userskill = $_ENV['userskill']->get_by_uid($uid);
            include template("usercard");
        }
    }
}

?>
