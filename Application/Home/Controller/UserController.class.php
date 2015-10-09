<?php
namespace Home\Controller;
use User\Api\UserApi;

class UserController extends HomeController {
    public function index() {
        $this->display();
    }

    public function login() {
        $this->assign('title', "登录");

        $forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __ROOT__;
        $this->assign('forward', $forward);
        $this->display();
    }

    public function logout() {
        if (is_login()) {
            D('User')->logout();
            $this->success('退出成功！', U('User/login'));
        } else {
            $this->redirect('User/login');
        }
    }  

    public function register() {
        if (!C('USER_ALLOW_REGISTER')) {
            $this->error('注册已关闭');
        }

        $this->assign('title', "注册");
        $this->display();
    }

    // 个人基本信息页
    public function profile() {
        $uid = is_login();
        if ($uid > 0) {
            $user_api = new UserApi();
            $user = $user_api->info($uid);
            $this->assign("user", $user);
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    // 更改账户密码
    public function password() {
        if (is_login()) {
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    // 身份认证
    public function authenticate() {
        $this->assign('title', '身份认证');
        if (is_login()) {
            //$resume = $_ENV['userresume']->get_by_uid($this->user['uid']);
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    // 上传学生证
    public function master() {
        if (is_login()) {
            //$resume = $_ENV['userresume']->get_by_uid($this->user['uid']);
            //$uid = $this->user['uid'];
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    // 验证码，用于登录和注册
    public function verify() {
        $verify = new \Think\Verify();
        $verify->entry($this->verify_id);
    }

    // 用户上传头像界面
    public function avatar() {
        if (is_login()) {
            $this->display();
        } else {
            $this->redirect('User/login');
        }
    }

    public function editimg($success = false) {
        $this->assign("success", $success);
        $this->display();
    }

    // 获取用户注册错误信息
    // @param  integer $code 错误编码
    // @return string        错误信息
    //
    private function showRegError($code = 0) {
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_login  登录
    // @request  POST
    //
    // @param  string  $username    用户名
    // @param  string  $password    登录密码
    // @param  string  $verify      验证码
    // @param  string  $forward     登录成功后的跳转页面
    // @param  integer $cookietime  cookie有效时间
    //
    // @ajaxReturn  成功 - array("success" => true, "forward" => 跳转页面)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户名存在，但密码错误
    // @error  102  用户不存在或被禁用
    // @error  103  验证码错误
    // @error  104  已登录
    //
    public function ajax_login($username="", $password="", $verify="", $forward="") {
        $res = array();
        if (!check_verify($verify)) {
            $res['success'] = false;;
            $res['error'] = 103; // 验证码错误
            $this->ajaxReturn($res);
        }
        //TODO: cookie有效时间的处理

        if (is_login() > 0) {
            // 用户已登录，无需再登录
            $this->ajaxReturn(array("success" => false, "error" => 104));
        }

        $user_api = new UserApi();
        $uid = $user_api->login($username, $password);
        if ($uid > 0) {
            $User = D('User');
            if ($User->login($uid)) { //登录用户
                //TODO:跳转到登录前页面
                $forward = empty($forward) ? __ROOT__ : $forward;
                $this->ajaxReturn(array("success" => true, "forward" => $forward));
            } else {
                $res['success'] = false;
                $res['error_info'] = $User->getError();
                $this->ajaxReturn($res);
            }
        } else { //登录失败
            switch ($uid) {
                case -1: $res['error'] = 102; break; // 用户不存在或被禁用
                case -2: $res['error'] = 101; break; // 密码错误
                default: break; // 未知错误
            }
            $res['success'] = false;
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_logout  注销登录
    // @request  POST
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  当前系统不存在此登录用户
    public function ajax_logout() {
        if (is_login()) {
            D('User')->logout();
            $this->ajaxReturn(array("success" => true));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }

    // @brief  ajax_register  注册
    // @request  POST
    //
    // @param  string  $username     用户名
    // @param  string  $password     密码
    // @param  string  $repassword   重复密码
    // @param  string  $email        邮箱
    // @param  string  $verify       验证码
    //
    // @ajaxReturn   成功 - array("success" => true)
    //               失败 - array("success" => false, "error" => 相应的错误码)
    //
    // @error  101  用户已登录
    // @error  102  系统注册功能暂时处于关闭状态
    // @error  103  当前IP已经超过当日最大注册数目
    // @error  104  用户名或密码不能为空
    // @error  105  邮件地址不合法
    // @error  106  用户名已存在
    // @error  107  此邮件地址已经注册
    // @error  108  用户名不合法
    // @error  109  验证码错误
    // @error  110  密码与重复密码不一致
    // @error  111  邮箱被禁止注册
    // @error  112  密码长度不在6-30个字符之间
    // @error  113  邮箱长度不在1-64个字符之间
    // @error  114  用户名长度不在16个字符以内
    //
    public function ajax_register($username = '',
                                  $password = '',
                                  $repassword = '',
                                  $email = '',
                                  $verify = '') {
        $res = array();
        if (!C('USER_ALLOW_REGISTER')) {
            $res['success'] = false;
            $res['error'] = 102;  // 系统注册功能暂时处于关闭状态
            $this->ajaxReturn($res);
        }

        if (!check_verify($verify)) {
            $res['success'] = false;
            $res['error'] = 109; // 验证码错误
            $this->ajaxReturn($res);
        }

        if ($password != $repassword) {
            $res['success'] = false;
            $res['error'] = 110; // 密码与重复密码不一致
            $this->ajaxReturn($res);
        }

        //TODO  101: 当前用户已登录
        //TODO  103: 当前IP已经超过当日最大注册数目
        if ($username == '' || $password == '') {
            $res['success'] = false;
            $res['error'] = 104; // 用户名或密码不能为空
            $this->ajaxReturn($res);
        }

        $User = new UserApi;
        $uid = $User->register($username, $password, $email);
        if ($uid > 0) { //注册成功
            //TODO: 发送验证邮件
            $res['success'] = true;
            $res['uid'] = $uid;
            $this->ajaxReturn($res);
        } else { //注册失败，显示错误信息
            $res['success'] = false;
            switch ($uid) {
                case -1:  $res['error'] = 114; break; // 用户名长度必须在16个字符以内
                case -2:  $res['error'] = 108; break; // 用户名不合法，被禁止注册
                case -3:  $res['error'] = 106; break; // 用户名被占用
                case -4:  $res['error'] = 112; break; // 密码长度必须在6-30个字符之间
                case -5:  $res['error'] = 105; break; // 邮箱格式不正确
                case -6:  $res['error'] = 113; break; // 邮箱长度必须在1-64个字符之间
                case -7:  $res['error'] = 111; break; // 邮箱被禁止注册
                case -8:  $res['error'] = 107; break; // 邮箱被占用
                case -9:  $res['error_info'] = '手机格式不正确！'; break;
                case -10: $res['error_info'] = '手机被禁止注册！'; break;
                case -11: $res['error_info'] = '手机号被占用！'; break;
                default:  $res['error_info'] = '未知错误'; break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_username  检查用户名是否可以使用
    // @request  GET
    // @param  username  用户名
    //
    // @ajaxReturn  尚未使用 - array("success" => true)
    //              已被占用 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户名已被使用
    // @error  102  用户名长度不合法
    // @error  103  用户名不能为空
    // @error  104  用户名禁止注册
    //
    public function ajax_username($username = "") {
        if (empty($username)) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 用户名不能为空
        }

        $user_api = new UserApi();
        $api_res = $user_api->checkUsername($username);

        if ($api_res == 1) {
            $this->ajaxReturn(array("success" => true));
        } else {
            $res = array();
            $res['success'] = false;
            switch($api_res) {
                case -1: $res['error'] = 102; break; // 用户名长度不合法
                case -2: $res['error'] = 104; break; // 用户名禁止注册
                case -3: $res['error'] = 101; break; // 用户名被占用
                default: $res['error_info'] = "其它未知错误"; break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_email  检查用户是否可以使用该邮件
    // @request  GET
    // @param  email  邮箱字符串
    //
    // @ajaxReturn  能够使用则success为true，否则success为false且error为相应的错误码
    //
    // @error  101  邮箱已被占用
    // @error  102  邮箱格式不正确
    // @error  103  邮箱不能为空
    // @error  104  邮箱长度不合法
    // @error  105  邮箱禁止注册
    //
    public function ajax_email($email = "") {
        $res = array();
        if (empty($email)) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // email不能为空
        }

        $user_api = new UserApi();
        $api_res = $user_api->checkEmail($email);

        if ($api_res == 1) {
            $this->ajaxReturn(array("success" => true));
        } else {
            $res = array();
            $res['success'] = false;
            switch($api_res) {
                case -5: $res['error'] = 102; break; // 邮箱格式不正确
                case -6: $res['error'] = 104; break; // 邮箱长度不合法
                case -7: $res['error'] = 105; break; // 邮箱禁止注册
                case -8: $res['error'] = 101; break; // 邮箱被占用
                default: $res['error_info'] = "其它未知错误"; break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_uppass  更改密码
    // @request  POST
    //
    // @param  string  newpwd  新密码
    // @param  string  oldpwd  老密码
    // @param  stirng  verify  验证码
    //
    // @return  正确则success为true，否则success为false且error为相应的错误码
    //
    // @error  101  用户尚未登录
    // @error  102  新密码长度不合法
    // @error  103  新密码与旧密码相同
    // @error  104  验证码错误
    // @error  105  旧密码不对
    //
    public function ajax_uppass($newpwd, $oldpwd, $verify='') {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 尚未登录
        }

        if (!check_verify($verify)) {
            $this->ajaxReturn(array("success" => false, "error" => 104)); // 验证码错误
        }

        $user_api = new UserApi();
        $res = $user_api->updateUserFields($uid, $oldpwd, array("password" => $newpwd));
        if ($res > 0) {
            $this->ajaxReturn(array("success" => true));
        } else {
            $json_array = array();
            switch ($res) {
                case 0: $json_array['error'] = 103; break; // 新旧密码相同
                case -101: $json_array['error'] = 105; break; // 旧密码不对
                case -4: $json_array['error'] = 102; break; // 新密码长度不合法
                default: break;
            }
            $json_array['success'] = false;
            $this->ajaxReturn($json_array);
        }
    }

    // @brief  ajax_update_profile  更改用户基本信息
    // @request  POST
    // @param  string  email     邮箱
    // @param  string  gender    性别
    // @param  string  phone     手机
    // @param  string  qq        QQ号
    // @param  string  wechat    微信号
    // @param  string  birthday  出生日期
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  邮件格式不正确
    // @error  103  邮件已被占用
    // @error  104  手机号被占用
    //
    public function ajax_update_profile($email = "",
                                        $gender = "",
                                        $mobile = "",
                                        $qq = "",
                                        $wechat = "",
                                        $birthday = "") {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 尚未登录
        }
        if (!check_email_format($email)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 邮件格式不正确
        }

        // TODO 更新邮箱时，需要验证邮箱
        // TODO 更新手机号时，需要验证手机号
        // TODO 手机号格式验证
        $update_data = array(
            "email"    => $email,
            "gender"   => $gender,
            "mobile"   => $mobile,
            "qq"       => $qq,
            "wechat"   => $wechat,
            "birthday" => $birthday
        );

        $user_api = new UserApi();
        $res = $user_api->updateInfo($uid, "password", $update_data);
        if ($res['success']) {
            $this->ajaxReturn(array("success" => true));
        } else {
            switch ($res['error']) {
                case -1:  break; //$error = '用户名长度必须在16个字符以内！'; break;
                case -2:  break; //$error = '用户名被禁止注册！'; break;
                case -3:  break; //$error = '用户名被占用！'; break;
                case -4:  break; //$error = '密码长度必须在6-30个字符之间！'; break;
                case -5:  break; //$error = '邮箱格式不正确！'; break;
                case -6:  break; //$error = '邮箱长度必须在1-32个字符之间！'; break;
                case -7:  break; //$error = '邮箱被禁止注册！'; break;
                case -8:  $error = 103; break; // 邮箱被占用
                case -9:  //$error = '手机格式不正确！'; break;
                case -10: //$error = '手机被禁止注册！'; break;
                case -11: $error = 104; break; // 手机号被占用
                default:  break; //$error = '未知错误';
            }
            $this->ajaxReturn(array("success" => false, "error" => $error));
        }
    }

    // @brief  ajax_crop_avatar  截取用户头像
    // @request  POST
    //
    // @param  string  $orig_pic  原始图片地址
    // @param  string  $x         截图图片x轴坐标
    // @param  string  $y         截图图片y轴坐标
    // @param  string  $w         截图图片宽度
    // @param  string  $h         截图图片高度
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    public function ajax_crop_avatar($orig_pic, $x, $y, $w, $h) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 尚未登录
        }

        $avatar_dir = get_avatar_dir($uid);
        forcemkdir($avatar_dir);

        $extname = extname($orig_pic);
        $crop_img = sprintf("%s/c_%s.%s", $avatar_dir, $uid, $extname);
        $sm_img = sprintf("%s/s_%s.%s", $avatar_dir, $uid, $extname);
        $md_img = sprintf("%s/m_%s.%s", $avatar_dir, $uid, $extname);
        $lg_img = sprintf("%s/l_%s.%s", $avatar_dir, $uid, $extname);

        $remove_file = glob($avatar_dir . "/c_{$uid}.*");
        $remove_file = array_merge($remove_file, glob($avatar_dir . "/s_{$uid}.*"));
        $remove_file = array_merge($remove_file, glob($avatar_dir . "/m_{$uid}.*"));
        $remove_file = array_merge($remove_file, glob($avatar_dir . "/l_{$uid}.*"));
        foreach ($remove_file as $imgfile) {
            if (strtolower($extname) != extname($imgfile)) {
                unlink($imgfile);
            }
        }
        image_crop(WEB_ROOT . $orig_pic, $crop_img, $x, $y, $w, $h, false);
        $success = true;
        $success = $success && image_resize($crop_img, $lg_img, 384, 384);
        $success = $success && image_resize($crop_img, $md_img, 244, 244);
        $success = $success && image_resize($crop_img, $sm_img, 100, 100);
        $this->ajaxReturn(array("success" => $success));
    }

    // @onajax_update_resume [更新用户学历信息]
    // @request type         [POST]
    // @param[in]   realname [真实姓名]
    // @param[in]      phone [手机]
    // @param[in]     wechat [微信号]
    // @param[in]         qq [QQ号]
    // @param[in]   edu_list [教育信息列表，每列信息包括下列字段：]
    //                       school     [学校]
    //                       dept       [院系]
    //                       major      [专业]
    //                       edu_type   [学历类型]
    //                       start_time [起始时间]
    //                       end_time   [结束时间，可为空]
    //
    // @return          成功 [success为true]
    //                  失败 [success为false，error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [更新失败]
    // @error            103 [未上传学生证]
    public function ajax_update_resume($edu_list) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if (!D('Education')->update($uid, $edu_list)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 更新失败
        }
        $this->ajaxReturn(array("success" => true));
        /*

        $affected_rows = 0;
        $realname = trim($this->post['realname']);
        if (!empty($realname)) {
            $affected_rows += $_ENV['userresume']->update_realname($this->user['uid'], $realname);
        }

        $phone = trim($this->post['phone']);
        $qq = trim($this->post['qq']);
        $wechat = trim($this->post['wechat']);
        if (!empty($phone) || !empty($qq) || !empty($wechat)) {
            $affected_rows += $_ENV['user']->update_contact_info($this->user['uid'],
                                                                 $phone, $qq, $wechat);
        }

        $edu_list = $this->post['edu_list'];
        $affected_rows += $_ENV['education']->remove_by_uid($this->user['uid']);
        $affected_rows += $_ENV['education']->multi_add($this->user['uid'], $edu_list);

        if ($this->post['apply']) {
            $resume = $_ENV['userresume']->get_by_uid($this->user['uid']);
            if (empty($resume['studentID'])) {
                $res['sucess'] = false;
                $res['error'] = 103; // 未上传学生证
                echo json_encode($res);
                return;
            } else {
                $affected_rows += $_ENV['userresume']->update_verify($this->user['uid'],
                                                                     RESUME_APPLY);
            }
        }
        */
    }

    // @brief  ajax_fetch_edu  获取用户教育信息
    // @request  GET
    // @param  uid  用户ID
    //
    // @ajaxReturn  成功 => array("success" => true, "list" => 该用户教育信息列表)
    //              失败 => array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  参数无效
    //
    public function ajax_fetch_edu($uid) {
        if (!is_login()) {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }

        $edu_list = D('Education')->field(true)
                                  ->where(array("uid" => $uid))
                                  ->order("start_time DESC")
                                  ->select();
        $this->ajaxReturn(array("success" => true, "list" => $edu_list));
    }

    // @brief  ajax_check_verify  验证验证码是否正确
    // @request GET
    // @param  string  $verify  验证码
    //
    // @ajaxReturn  正确 => array("success" => true),
    //              失败 => array("success" => false, "error" => 相应的错误码)
    //
    // @error  101  验证码书写错误
    //
    public function ajax_check_verify($verify) {
        $res = array();
        if (!check_verify($verify, $this->verify_id, false)) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 验证码书写错误
        } else {
            $this->ajaxReturn(array("success" => true));
        }
    }

    private $verify_id = 1;
}

/*
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

*/
