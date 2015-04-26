<?php

!defined('IN_SITE') && exit('Access Denied');

class usercontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load('userskill');
        $this->load('problem');
        $this->load('userresume');
        $this->load("education");

        $this->load('easy_access');
        $this->load('invite_code');
    }

    // 个人基本信息页
    public function ondefault() {
        $navtitle = "基本资料";
        $this->check_login();
        include template('user');
    }

    // 登录界面
    public function onlogin() {
        $navtitle = "登录";
        $forward = isset($_SERVER['HTTP_REFERER'])  ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        include template('login');
    }

    // 注册界面
    public function onregister() {
        $navtitle = "注册";
        $forward = isset($_SERVER['HTTP_REFERER'])  ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        include template('register');
    }

    // 更改账户密码
    public function onuppass() {
        $navtitle = "修改密码";
        $this->check_login();
        include template('uppass');
    }

    // 登出系统
    public function onlogout() {
        $navtitle = '登出系统';
        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $_ENV['user']->logout($this->user['sid']);
        $this->jump("/main/default");
    }

    // 用户上传头像
    public function onupload_avatar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $uid = intval($this->user['uid']);
            $picname = $_FILES['userimage']['name'];
            $picsize = $_FILES['userimage']['size'];
            $extname = extname($picname);

            if (!isimage($extname)) {
                echo json_encode(array("error" => "101")); //type_error
                return;
            }
            if ($picsize > 5120000) {
                echo '图片大小不能超过5M';
                return;
            }
            $upload_tmp_file = '/public/data/tmp/user_avatar_' . $uid . '.' . $extname;
            move_uploaded_file($_FILES["userimage"]["tmp_name"], WEB_ROOT . $upload_tmp_file);
            $size = round($picsize / 1024, 2);
            $image_size = getimagesize(WEB_ROOT . $upload_tmp_file);
     
            $upload_pic = array(
                'name'   => $picname,
                'pic'    => $upload_tmp_file,
                'size'   => $size,
                'width'  => $image_size[0],
                'height' => $image_size[1]
            );
        }
        include template("upload_avatar");
    }

    // 截取用户头像
    public function oneditimg() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $x = intval($this->post['x']);
            $y = intval($this->post['y']);
            $w = intval($this->post['w']);
            $h = intval($this->post['h']);
            $pic = $this->post['src'];
            $extname = extname($pic);

            $uid = intval($this->user['uid']);
            $uid = sprintf("%010d", $uid);
            $avatardir = "/public/data/avatar/";
            $dir1 = $avatardir . substr($uid, 0, 3);
            $dir2 = $dir1 . '/' . substr($uid, 3, 3);
            $dir3 = $dir2 . '/' . substr($uid, 6, 2);
            (!is_dir(WEB_ROOT . $dir1)) && forcemkdir(WEB_ROOT . $dir1);
            (!is_dir(WEB_ROOT . $dir2)) && forcemkdir(WEB_ROOT . $dir2);
            (!is_dir(WEB_ROOT . $dir3)) && forcemkdir(WEB_ROOT . $dir3);

            // crop image
            $crop_img = $dir3 . "/crop_" . $uid . '.' . $extname;
            $smallimg = $dir3 . "/small_" . $uid . '.' . $extname;
            $mediumimg = $dir3 . "/medium_" . $uid . '.' . $extname;
            $largeimg = $dir3 . "/large_" . $uid . '.' . $extname;

            $remove_file = glob(WEB_ROOT . $dir3 . "/crop_{$uid}.*");
            $remove_file = array_merge($remove_file, glob(WEB_ROOT . $dir3 . "/small_{$uid}.*"));
            $remove_file = array_merge($remove_file, glob(WEB_ROOT . $dir3 . "/medium_{$uid}.*"));
            $remove_file = array_merge($remove_file, glob(WEB_ROOT . $dir3 . "/large_{$uid}.*"));
            foreach ($remove_file as $imgfile) {
                if (strtolower($extname) != extname($imgfile)) {
                    unlink($imgfile);
                }
            }
            image_crop(WEB_ROOT . $pic, WEB_ROOT . $crop_img, $x, $y, $w, $h, false);
            $success = true;
            $success = $success && image_resize(WEB_ROOT . $crop_img, WEB_ROOT . $largeimg, 244, 244);
            $success = $success && image_resize(WEB_ROOT . $crop_img, WEB_ROOT . $mediumimg, 160, 160);
            $success = $success && image_resize(WEB_ROOT . $crop_img, WEB_ROOT . $smallimg, 100, 100);
        }
        include template("editimg");
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

    function onupload_resume() {
        if (isset($_FILES["userresume"])) {
            $uid = intval($this->get[2]);
            $session_id = $this->post['session_id'];
            $session_info = $_ENV['user']->get_session_by_sid($session_id);
            if ($uid != $session_info['uid'] || $this->ip != $session_info['ip']) {
                return;
            }

            $resumedir = "/private/userdata/resume/";
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
                echo 'ok';
            }
        }
    }

    function onupload_ID() {
        $uid = intval($this->get[2]);
        $session_id = $this->post['session_id'];
        $session_info = $_ENV['user']->get_session_by_sid($session_id);

        if ($uid != $session_info['uid'] || $this->ip != $session_info['ip']) {
            return;
        }

        $resumedir = "/private/userdata/ID/";
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
            echo 'ok';
        }
    }

    public function onupload_studentID() {
        $uid = intval($this->get[2]);
        $session_id = $this->post['session_id'];
        $session_info = $_ENV['user']->get_session_by_sid($session_id);
        if ($uid != $session_info['uid'] || $this->ip != $session_info['ip']) {
            return;
        }

        $resumedir = "/private/userdata/studentID/";
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
            echo 'ok';
        }
    }

    // 维护个人简历
    public function onresume() {
        $this->check_login();
        $navtitle = "完善简历";
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $realname = trim($this->post['realname']);
            $phone = trim($this->post['phone']);
            $qq = trim($this->post['qq']);
            $wechat = trim($this->post['wechat']);

            $_ENV['userresume']->update_realname($this->user['uid'], $realname);
            $_ENV['user']->update_contact_info($this->user['uid'], $phone, $qq, $wechat);

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

            if ($this->post['operation'] == RESUME_APPLY) {
                $_ENV['userresume']->update_verify($this->user['uid'], RESUME_APPLY);
                $this->message("提交请求成功，Boostme将以最快的速度审核您的材料！", 'BACK');
            } else {
                $this->jump("user/resume");
            }
        } else {
            $resume = $_ENV['userresume']->get_by_uid($this->user['uid']);
            $edu_list = $_ENV['education']->get_by_uid($this->user['uid']);
        }
        include template("resume");
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_login         [登录]
    // @request type         [POST]
    // @param[in]   username [用户名]
    // @param[in]   password [登录密码]
    // @param[in]       code [验证码，依系统配置是否需要]
    // @param[in]    forward [可选，登录成功后的跳转页面]
    // @param[in] cookietime [可选，cookie有效时间]
    // @return          成功 [success为true, forward为跳转页面]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户名存在，但密码错误]
    // @error            102 [用户名不存在]
    // @error            103 [验证码错误]
    // @error            104 [已登录]
    public function onajax_login() {
        $res = array();
        if ($this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 104;  // 已登录
            echo json_encode($res);
            return;
        }

        if ($this->setting['code_login'] && !$this->checkcode($this->user['sid'])) {
            $res['success'] = false;;
            $res['error'] = 103; // 验证码错误
            echo json_encode($res);
            return;
        }

        $username = $this->post['username'];
        if (WEB_CHARSET == 'GBK') {
            require_once(WEB_ROOT . '/code/lib/iconv.func.php');
            $username = utf8_to_gbk($username);
        }

        $password = md5($this->post['password']);
        $user = $_ENV['user']->get_by_username($username);
        $user['avatar'] = get_avatar_dir($user['uid']);
        $forward = isset($_SERVER['HTTP_REFERER'])  ? $_SERVER['HTTP_REFERER'] : SITE_URL;
        $cookietime = intval($this->post['cookietime']);
        if (isset($this->post['forward'])) {
            $forward = $this->post['forward'];
        }

        if (is_array($user) && ($password == $user['password'])) {
            $res['success'] = true;
            $res['user'] = $user;
            $res['forward'] = $forward;
            $this->refresh($user, 1, $cookietime);
        } else {
            $res['success'] = false;

            if (is_array($user)) {
                $res['error'] = 101; // 密码错误
            } else {
                $res['error'] = 102; // 用户名不存在
            }
        }
        echo json_encode($res);
    }

    // @onajax_logout       [登出]
    // @request type        [POST]
    // @return         成功 [success为true]
    //                 失败 [success为false, error为相应的错误码]
    //
    // @error           101 [当前系统不存在此登录用户]
    public function onajax_logout() {
        $affected_rows = $_ENV['user']->logout($this->user['sid']);
        $res = array();
        if ($affected_rows > 0) {
            $res['success'] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 101;
        }
        echo json_encode($res);
    }

    // @onajax_register       [注册]
    // @request type          [POST]
    //
    // @param[in]    username [用户名]
    // @param[in]    password [密码]
    // @param[in]       email [邮箱]
    // @param[in]        code [验证码，依系统配置是否需要]
    // @param[in] invite_code [邀请码，可选]
    // @param[in]     forward [可选，注册成功后的跳转页面]
    //
    // @return                [注册成功则success为true，否则success为false且error为相应的错误码]
    //
    // @error             101 [用户已登录]
    // @error             102 [系统注册功能暂时处于关闭状态]
    // @error             103 [当前IP已经超过当日最大注册数目]
    // @error             104 [用户名或密码不能为空]
    // @error             105 [邮件地址不合法]
    // @error             106 [用户名已存在]
    // @error             107 [此邮件地址已经注册]
    // @error             108 [用户名不合法]
    // @error             109 [验证码错误]
    public function onajax_register() {
        $res = array();
        if ($this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 101;  // 已登录
            echo json_encode($res);
            return;
        }

        if (!$this->setting['allow_register']) {
            $res['success'] = false;
            $res['error'] = 102;  // 系统注册功能暂时处于关闭状态
            echo json_encode($res);
            return;
        }

        if (isset($this->base->setting['max_register_num'])
                && $this->base->setting['max_register_num'] > 0
                && !$_ENV['user']->is_allowed_register()) {
            $res['success'] = false;
            $res['error'] = 103; // 当前IP已经超过当日最大注册数目
            echo json_encode($res);
            return;
        }

        $forward = SITE_URL;
        if (isset($this->post['forward'])) {
            $forward = $this->post['forward'];
        }

        $invite_code = trim($this->post['invite_code']);
        $username = trim($this->post['username']);
        $password = trim($this->post['password']);
        $email = $this->post['email'];

        if ($username == '' || $password == '') {
            $res['success'] = false;
            $res['error'] = 104; // 用户名或密码不能为空
            echo json_encode($res);
            return;
        } else if (!preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/",
                                $email)) {
            $res['success'] = false;
            $res['error'] = 105; // 邮件地址不合法
            echo json_encode($res);
            return;
        } else if ($_ENV['user']->is_username_existed($username)) {
            $res['success'] = false;
            $res['error'] = 106; // 用户名已存在
            echo json_encode($res);
            return;
        } else if ($_ENV['user']->is_email_existed($email)) {
            $res['success'] = false;
            $res['error'] = 107; // 此邮件地址已经注册
            echo json_encode($res);
            return;
        } else if (!check_usernamecensor($username)) {
            $res['success'] = false;
            $res['error'] = 108; // 用户名不合法
            echo json_encode($res);
            return;
        } else if ($this->setting['code_register'] && !$this->checkcode($this->user['sid'])) {
            $res['success'] = true;
            $res['error'] = 109; // 验证码错误
            echo json_encode($res);
            return;
        }

        $invited_by_uid = 0;
        $remain_times = 0;
        if (!empty($invite_code)) {
            $invite_code_info = $_ENV['invite_code']->get_by_code($invite_code);
            $invited_by_uid = $invite_code_info['owner'];
            $remain_times = $this->setting['invite_give_times'];
        }

        $uid = $_ENV['user']->add($username, $password, $email, $invited_by_uid, $remain_times);
        if ($invited_by_uid > 0) {
            $_ENV['invite_code']->update_invite_user($invite_code, $uid, $username);
        }

        $user = $_ENV['user']->get_by_uid($uid);
        $this->refresh($user);

        //发送邮件通知
        $subject = "恭喜您在" . $this->setting['site_name'] . "注册成功！";
        $message = '<p>现在您可以登录<a swaped="true" target="_blank" href="' . SITE_URL . '">' . $this->setting['site_name'] . '</a>。祝您使用愉快！</p>';
        sendmail($username, $email, $subject, $message);

        $res['success'] = true;
        $res['uid'] = $uid;
        $res['forward'] = $forward;
        echo json_encode($res);
    }

    // @onajax_username     [检查用户名是否可以使用]
    // @request type        [GET]
    // @param[in]  username [用户名]
    // @return              [能够使用则success为true，否则success为false且error为相应的错误码]
    //
    // @error           101 [用户名已被使用]
    // @error           102 [用户名不合法]
    // @error           103 [用户名不能为空]
    public function onajax_username() {
        $res = array();
        $username = $this->post['username'];
        if (empty($username)) {
            $res['success'] = false;
            $res['error'] = 103; // 用户名不能为空
            echo json_encode($res);
            return;
        }

        $user = $_ENV['user']->get_by_username($username);
        if (is_array($user)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户名已被使用
        } else {
            if (check_usernamecensor($username)) {
                $res['success'] = true;
            } else {
                $res['success'] = false;
                $res['error'] = 102;  // 用户名不合法
            }
        }
        echo json_encode($res);
    }

    // @onajax_email        [检查用户是否可以使用该邮件]
    // @request type        [GET]
    // @param[in]     email [邮箱字符串]
    // @return              [能够使用则success为true，否则success为false且error为相应的错误码]
    //
    // @error           101 [邮箱已被占用]
    // @error           102 [邮箱地址不合法]
    // @error           102 [邮箱地址不能为空]
    public function onajax_email() {
        $res = array();
        $email = $this->post['email'];
        if (empty($email)) {
            $res['success'] = false;
            $res['error'] = 103; // email不能为空
            echo json_encode($res);
            return;
        }

        $user = $_ENV['user']->get_by_email($email);
        if (is_array($user)) {
            $res['success'] = false;
            $res['error'] = 101; // 邮箱已被占用
        } else {
            if (check_emailaccess($email)) {
                $res['success'] = true;
            } else {
                $res['success'] = false;
                $res['error'] = 102; // 邮箱不合法
            }
        }
        echo json_encode($res);
    }

    // @onajax_check_code   [验证验证码是否正确]
    // @request type        [GET]
    // @param[in]      code [验证码]
    // @return              [正确则success为true，否则success为false且error为相应的错误码]
    //
    // @error           101 [验证码书写错误]
    public function onajax_check_code() {
        $code = strtolower(trim($this->post['code']));
        $res = array();
        if ($code == $_ENV['user']->get_code($this->user['sid'])) {
            $res['success'] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 101; // 验证码书写错误
        }
        echo json_encode($res);
    }

    // @onajax_code         [获取验证码图片]
    // @request type        [GET]
    // @return              [验证码图片]
    public function onajax_code() {
        ob_clean();
        $code = random(4);
        $_ENV['user']->save_code($this->user['uid'], $this->user['sid'], strtolower($code));
        makecode($code);
    }

    // @onajax_add_easy_access  [添加快捷链接]
    // @request type        [GET]
    // @param[in]  username [用户名]
    // @return              [成功success为true，id为key，否则success为false且error为相应的错误码]
    //
    // @error           101 [用户数量已经超过限制3]
    // @error           102 [添加操作失败]
    // @error           103 [用户尚未登录]
    public function onajax_add_easy_access() {
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 103;
            echo json_encode($res);
            return;
        }

        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        $type = $this->post['type'];

        $user_num = $_ENV['easy_access']->get_user_access_num($this->user['uid'], $type);

        $res = array();
        if ($user_num >= 3) {
            $res['success'] = false;
            $res['error'] = 101;  // 用户数量已经超过限制3
        } else {
            $id = $_ENV['easy_access']->add($this->user['uid'], $region_id, $school_id, $dept_id, $major_id, $type);
            if ($id > 0) {
                $res['success'] = true;
                $res['id'] = $id;
            } else {
                $res['success'] = false;
                $res['error'] = 102;  // 添加操作失败
            }
        }
        echo json_encode($res);
    }

    // @onajax_remove_easy_access [删除快捷链接]
    // @request type              [GET]
    // @param[in]            code [验证码]
    // @return                    [正确则success为true，否则success为false且error为相应的错误码]
    //
    // @error                 101 [参数错误，没有指定快捷链接ID号]
    // @error                 102 [删除失败]
    // @error                 103 [用户尚未登录]
    public function onajax_remove_easy_access() {
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 103; // 未登录
            echo json_encode($res);
            return;
        }

        $id = $this->post['id'];
        $res = array();
        if (empty($id)) {
            $res['success'] = false;
            $res['error'] = 101; // 参数错误，没有指定快捷链接ID号
        } else {
            $remove_num = $_ENV['easy_access']->remove_by_id($id);
            if ($remove_num > 0) {
                $res['success'] = true;
            } else {
                $res['success'] = false;
                $res['error'] = 102; // 删除失败
            }
        }
        echo json_encode($res);
    }

    // @onajax_fetch_easy_access    [获取用户首页快捷链接列表]
    // @request type          [GET]
    // @param[in]        type [快捷链接类型，枚举类型：material、service]
    // @return           成功 [success为true, link为该用户首页快捷链接列表]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    public function onajax_fetch_easy_access() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101;
            echo json_encode($res);
            return;
        }
        $type = $this->post['type'];
        $easy_access_list = $_ENV['easy_access']->get_by_uid_target($this->user['uid'],
                                                                    $type);

        $res['success'] = true;
        $res['easy_access_list'] = $easy_access_list;
        echo json_encode($res);
    }

    // @onajax_fetch_edu      [获取用户教育信息]
    // @request type          [GET]
    // @param[in]         uid [用户ID号,userid]
    // @return           成功 [success为true, edu_list为该用户教育信息列表]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    // @error             102 [参数无效]
    public function onajax_fetch_edu() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101;
            echo json_encode($res);
            return;
        }

        $uid = $this->post['uid'];
        if (empty($uid)) {
            $res['success'] = false;
            $res['error'] = 102; // 参数无效
        } else {
            $res['success'] = true;
            $res['edu_list'] = $_ENV['education']->get_by_uid($uid);
        }
        echo json_encode($res);
    }

    // @onajax_uppass       [更改密码]
    // @request type        [POST]
    // @param[in]    newpwd [新密码]
    // @param[in]    oldpwd [老密码]
    // @param[in]      code [验证码]
    // @return              [正确则success为true，否则success为false且error为相应的错误码]
    //
    // @error           101 [用户尚未登录]
    // @error           102 [新密码为空]
    // @error           103 [新密码与旧密码相同]
    // @error           104 [验证码错误]
    // @error           105 [旧密码不对]
    public function onajax_uppass() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 未登录

        } else if (trim($this->post['newpwd']) == '') {
            $res['success'] = false;
            $res['error'] = 102; // 新密码为空

        } else if (trim($this->post['oldpwd']) == trim($this->post['newpwd'])) {
            $res['success'] = false;
            $res['error'] = 103; // 新密码与旧密码相同

        } else if (!$this->checkcode($this->user['sid'])) {
            $res['success'] = false;
            $res['error'] = 104; // 验证码错误

        } else if (md5(trim($this->post['oldpwd'])) != $this->user['password']) {
            $res['success'] = false;
            $res['error'] = 105; // 旧密码不对

        } else {
            $_ENV['user']->uppass($this->user['uid'], trim($this->post['newpwd']));
            $res['success'] = true;
        }
        echo json_encode($res);
    }

    // @onajax_update_info  [更改用户基本信息]
    // @request type        [POST]
    // @param[in]     email [邮箱]
    // @param[in]    gender [性别]
    // @param[in]     phone [手机]
    // @param[in]        qq [QQ号]
    // @param[in]    wechat [微信号]
    // @param[in]      bday [出生日期]
    // @param[in] signature [签名]

    // @return         成功 [success为true]
    //                 失败 [success为false，error为相应的错误码]
    //
    // @error           101 [用户尚未登录]
    // @error           102 [邮件格式不正确]
    // @error           103 [邮件已被占用]
    public function onajax_update_info() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $email = $this->post['email'];
        $gender = $this->post['gender'];
        $phone = $this->post['phone'];
        $qq = $this->post['qq'];
        $wechat = $this->post['wechat'];
        $bday = $this->post['bday'];
        $signature = $this->post['signature'];
        if (!check_email_format($email)) {
            $res['success'] = false;
            $res['error'] = 102; // 邮件格式不正确
            echo json_encode($res);
            return;
        }

        if ($email != $this->user['email'] && $_ENV['user']->is_email_existed($email)) {
            $res['success'] = false;
            $res['error'] = 103;  // 邮件已被占用
            echo json_encode($res);
            return;
        }
        $_ENV['user']->update($this->user['uid'], $gender, $bday,
                              $phone, $qq, $wechat, $signature);

        if ($email != $this->user['email']) {
            $_ENV['user']->update_email($this->user['uid'], $email);
        }
        $res['success'] = true;
        echo json_encode($res);
    }
}

?>
