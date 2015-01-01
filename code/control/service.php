<?php

!defined('IN_SITE') && exit('Access Denied');

class servicecontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load("service");
        $this->load("service_category");
        $this->load("service_comment");

        $this->load("problem");
        $this->load("tag");
        $this->load("demand");
        $this->load('userresume');
        $this->load('userskill');
        $this->load('education');
        $this->load('user');
        $this->load('trade');
    }

    public function ondefault() {
        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        $page = max(intval($this->post['page']), 1);

        $pagesize = $this->setting['service_page_size'];
        $start = ($page - 1) * $pagesize;

        if (empty($region_id)) {
            $service_list = $_ENV['service']->get_list($start, $pagesize);
        } else {
            $service_list = $_ENV['service_category']->get_full($region_id, $school_id, $dept_id, $major_id, $start, $pagesize);
        }

        $this->load('easy_access');
        $user_access_list = $_ENV['easy_access']->get_by_uid_target($this->user['uid'], "service");
        foreach ($user_access_list as &$t_user_access) {
            $param = "";
            !empty($t_user_access['region_id']) && $param .= "region_id=" . $t_user_access['region_id'];
            !empty($t_user_access['school_id']) && $param .= "&school_id=" . $t_user_access['school_id'];
            !empty($t_user_access['dept_id']) && $param .= "&dept_id=" . $t_user_access['dept_id'];
            !empty($t_user_access['major_id']) && $param .= "&major_id=" . $t_user_access['major_id'];

            $t_user_access['param'] = $param;
        }
        include template('service');
    }

    // 获取service列表
    public function onfetch_list() {
        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        $page = max(intval($this->post['page']), 1);

        $pagesize = $this->setting['service_page_size'];
        $start = ($page - 1) * $pagesize;
        $service_list = $_ENV['service_category']->get_full($region_id, $school_id, $dept_id, $major_id, $start, $pagesize);

        runlog("test009", "start = $start, pagesize = $pagesize");
        runlog("test009", var_export($service_list, true));

        foreach ($service_list as &$t_service) {
            $t_service['format_time'] = tdate($t_service['time']);
        }
        echo json_encode($service_list);
    }

    public function onedit_picture() {
        if ($this->user['uid'] == 0) {
            echo "Please Login First";
            return;
        }

        $service = $_ENV['service']->get_by_uid($this->user['uid']);
        $picture = $service['picture'];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $x = intval($this->post['x']);
            $y = intval($this->post['y']);
            $w = intval($this->post['w']);
            $h = intval($this->post['h']);
            $pic = $this->post['src'];
            $extname = extname($pic);

            $uid = intval($this->user['uid']);
            $uid = sprintf("%010d", $uid);

            $type = get_image_type(WEB_ROOT . $pic);
            $target_path = "/public/data/service";
            $crop_img = $target_path . "/crop_" . $uid . "." . $type;
            $target_img = $target_path . "/" . $uid . "." . $type;

            $remove_file = glob(WEB_ROOT . $target_path . "/crop_{$uid}.*");
            $remove_file = array_merge($remove_file, glob(WEB_ROOT . $target_path . "/" . $uid . ".*"));
            foreach ($remove_file as $imgfile) {
                if (strtolower($extname) != extname($imgfile)) {
                    unlink($imgfile);
                }
            }
            image_crop(WEB_ROOT . $pic, WEB_ROOT . $crop_img, $x, $y, $w, $h, false);
            image_resize(WEB_ROOT . $crop_img, WEB_ROOT . $target_img, 200, 200);
            $picture = $target_img;
            $_ENV['service']->update_picture($service['id'], $picture);

            runlog("test007", "crop_img = $crop_img");
            runlog("test007", "target_img = $target_img");
        }
        include template("edit_service_picture");
    }

    public function onupload_picture() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $picname = $_FILES['service_pic']['name'];
            $picsize = $_FILES['service_pic']['size'];
            $extname = extname($picname);

            if (!isimage($extname)) {
                echo json_encode(array("error" => "101")); //type_error
                return;
            }
            if ($picsize > 5120000) {
                echo '图片大小不能超过5M';
                return;
            }

            runlog("test007", WEB_ROOT . $picname);
            $type = get_image_type(WEB_ROOT . $picname);
            $type = $extname;
            $uid = intval($this->user['uid']);
            $uid = sprintf("%010d", $uid);

            $target_pic_path = "/public/data/tmp/service";
            $pic_path = WEB_ROOT . $target_pic_path . "/" . $uid . "." . $type;

            $remove_file = glob(WEB_ROOT . $target_pic_path . "/$uid.*");
            foreach ($remove_file as $imgfile) {
                if (strtolower($extname) != extname($imgfile)) {
                    unlink($imgfile);
                }
            }
            move_uploaded_file($_FILES['service_pic']['tmp_name'], $pic_path);

            $size = round($picsize/1024, 2);
            $image_size = getimagesize($pic_path);
            $upload_pic = array(
                'name'   => $picname,
                'pic'    => $target_pic_path . "/" . $uid . "." . $type,
                'size'   => $size,
                'width'  => $image_size[0],
                'height' => $image_size[1]
            );
            runlog("test007", var_export($upload_pic, true));
        }
        include template("upload_service_picture");
    }

    // 上传service图像
    // public function onservice_picture() 
    public function oncrop_picture() {

        // if ($_SERVER['REQUEST_METHOD'] == 'POST')
        // if (file_exists($MemberFace)) unlink($MemberFace);
        // $MemberFace = $this->sliceBanner("cuteboy");
        // echo $MemberFace;
        // 此处根据自己的程序代码自行调整		
        // $table = "member"; //数据表名称
        // $MemberUser = "cuteboy"; //会员名字
        // mysql_query("UPDATE ".$table." SET MemberFace='".$MemberFace."' Where MemberUser = '".$MemberUser."'");
        // echo "<script>alert('头像修改成功!');location.href='index.php';<\/script>");
		$x = intval($this->post['x']);
		$y = intval($this->post['y']);
		$w = intval($this->post['w']);
		$h = intval($this->post['h']);
		$pic = $this->post['src'];
		
        $type = get_image_type($pic);
        
		$filename = $this->user['uid'] . "_" . date("YmdHis") . "." . $type;
        $dst_path = "/public/data/tmp/service/" . $filename;

        image_crop($pic, WEB_ROOT . $dst_path, $x, $y, $w, $h, false);
        $arr = array(
            'pic' => SITE_URL . substr($dst_path, 1),
            'src' => $dst_path
        );
        echo json_encode($arr);
	}

    public function onregister() {
        $this->check_login();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $picture = $this->fetch_picture_path("/public/data/service", $this->user['uid']);
            $category = $this->post['category'];
            $profile = $this->post['profile'];
            $price = $this->post['price'];
            $id = $this->post['id'];
            $phone = $this->post['phone'];
            $qq = $this->post['qq'];
            $wechat = $this->post['wechat'];

            if ($id > 0) {
                $_ENV['service']->update($id, $picture, $price, $profile, SERVICE_STATUS_APPLY);
            } else {
                $id = $_ENV['service']->add($this->user['uid'], $this->user['username'], $picture, $price, $profile);
            }

            if ($id > 0) { 
                $_ENV['service_category']->multi_add($id, $category, false);
            }

            $_ENV['user']->update_contact_info($this->user['uid'], $phone, $qq, $wechat);

            $arr = array('success' => true, 'id' => $id);
            echo json_encode($arr);
        } else {
            $service = $_ENV['service']->get_by_uid($this->user['uid']);
            $edu_list = $_ENV['education']->get_by_uid($this->user['uid']);
            if (!empty($service)) {
                $service['cid_list'] = $_ENV['service_category']->get_by_sid($service['id']);
            }
            include template("service_register");
        }
    }

    // 发布求助
    function onadd() {
        $op_type = "add"; // 操作类型：增加求助
        $navtitle = "发布求助";
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $tags = trim($this->post["ptags"]);
            $price = intval($this->post["price"]);
            $cid = trim($this->post['category_id']);

            $this->setting['code_problem'] && $this->checkcode(); //检查验证码

            $status = ($this->setting['verify_problem'] ? PB_STATUS_UNAUDIT : PB_STATUS_UNSOLVED);

            //检查标题违禁词
            $contentarray = checkwords($title);
            1 == $contentarray[0] && $status = PB_STATUS_UNAUDIT;
            2 == $contentarray[0] && $this->message("求助包含非法关键词，发布失败!", 'BACK');
            $title = $contentarray[1];

            //检查问题描述违禁词
            $descarray = checkwords($description);
            1 == $descarray[0] && $status = PB_STATUS_UNAUDIT;
            2 == $descarray[0] && $this->message("求助描述包含非法关键词，发布失败!", 'BACK');
            $description = $descarray[1];

            $pid = $_ENV['problem']->add($title, $description, $cid, $price);
            $_ENV['problem']->update_status($pid, $status);
            $_ENV['user']->update_problem_num($this->user['uid'], 1);

            // 插入标签
            !empty($tags) && $taglist = explode(" ", $tags);
            $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $pid);

            $viewurl = urlmap("problem/view/$pid", 2);
            $_ENV['userlog']->add('problem', "回报: $price");
            if (PB_STATUS_UNAUDIT == $status) {
                $this->message('求助发布成功！为了确保求助的合法性，我们会对您提的求助进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message("求助发布成功!", $viewurl);
            }
        } else {
            if (0 == $this->user['uid']) {
                $this->message("请先登录!", "user/login");
            }
            $word = $this->post['word'];
            include template('problem');
        }
    }

    function ondemand() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        if (0 == $this->user['can_teach']) { //没有获得教师的资格
            $this->message("您还没有获得提供帮助的资格，为了确保人员质量，请详细填写您的个人资料，通过审核之后，就可以提供帮助啦!", "user/resume");
        }

        $pid = $this->post['dialog_pid'];
        $message = trim($this->post['demand_message']);

        empty($pid) && $this->message("无效参数", "BACK");

        if ($_ENV['demand']->already_demand($pid)) {
            $this->message("您已发过一次请求，请您耐心等待!", "problem/view/$pid");
        }

        $last_insert_id = $_ENV['demand']->add($pid, $message);
        if ($last_insert_id > 0) {
            $_ENV['problem']->add_demand($pid);

            $problem = $_ENV['problem']->get($pid);
            $subject = "有人想帮您解决您的求助 \"{$problem['title']}\"";
            $content ='<a href="' . url("problem/view/$pid", 1) . '">点击查看</a>';
            $this->send('', 0, $problem['authorid'], $subject, $content);
            $_ENV['userlog']->add('demand', "uid:'{$this->user['uid']}',pid:'$pid'");
            $this->message("恭喜您，请求信息已经成功发送到对方账户，请您耐心等待！", "problem/view/$pid");
        } else {
            $this->message("Oops，请求发送失败！", "problem/view/$pid");
        }
    }

    function onajaxdemand() {
        if (0 == $this->user['uid']) {
            exit('-1');
        }

        if (0 == $this->user['can_teach']) { //没有获得教师的资格
            exit('3');
        }

        $pid = $this->get[2];
        empty($pid) && exit('0');

        if ($_ENV['demand']->already_demand($pid)) {
            exit('2');
        }

        $last_insert_id = $_ENV['demand']->add($pid);
        if ($last_insert_id > 0) {
            $_ENV['problem']->add_demand($pid);

            $problem = $_ENV['problem']->get($pid);
            $subject = "有人想帮您解决您的求助 \"" . cutstr($problem['title'], 30) . "\"";
            $content ='<a href="' . url("problem/view/$pid", 1) . '">点击查看</a>';
            $this->send('', 0, $problem['authorid'], $subject, $content);
            $_ENV['userlog']->add('demand', "uid:'{$this->user['uid']}',pid:'$pid'");
            exit('1');
        }
        exit('0');
    }

    function onajaxcancel() {
        if (0 == $this->user['uid']) {
            exit('-1');
        }
        $pid = $this->get[2];
        empty($pid) && exit('0');

        if ($_ENV['demand']->already_demand($pid) === false) {
            exit('2');
        }

        $affected_rows = $_ENV['demand']->remove_by_uid_pid($this->user['uid'], $pid);

        if ($affected_rows > 0) {
            $_ENV['problem']->update_demand($pid, -$affected_rows);
            $_ENV['userlog']->add('cancel', "uid:'{$this->user['uid']}',pid:'$pid'");
            exit('1');
        }
        exit('0');
    }

    function onajaxaccept() {
        if (!($this->user['phone'] || $this->user['qq'] || $this->user['wechat'])) {
            exit('2'); // 没有联系方式
        }
        $uid = $this->get[2];
        $pid = $this->get[3];
        (empty($uid) || empty($pid)) && exit('0');
        $affected_rows = $_ENV['demand']->update_status($uid, $pid, DEMAND_STATUS_ACCEPT);
        $affected_rows = 1;
        if ($affected_rows > 0) {
            $user = $_ENV['user']->get_by_uid($uid);
            $_ENV['problem']->update_solver($pid, $user['uid'], $user['username']);
            $problem = $_ENV['problem']->get($pid);
            $_ENV['user']->solve_problem($user['uid'], $problem['price']);

            $system = $problem['price'] * $user['charge'] / 100.0;

            $subject = "您抢求助 \"{$problem['title']}\" 成功啦";
            $content = "您可以通过以下方式联系到{$problem['author']}:<br/>";
            if ($this->user['phone']) $content .= "手机：{$this->user['phone']}<br/>";
            if ($this->user['qq']) $content .= "QQ：{$this->user['qq']}<br/>";
            if ($this->user['wechat']) $content .= "微信：{$this->user['wechat']}<br/>";
            $content .='<a href="' . url("problem/view/$pid", 1) . '">点击查看求助</a>';
            $this->send('', 0, $uid, $subject, $content);
            $_ENV['userlog']->add('accept', "pid:'$pid',uid:'$uid'");
            exit('1');
        }
        exit('0');
    }

    function onajaxdenied() {
        $uid = $this->get[2];
        $pid = $this->get[3];
        (empty($uid) || empty($pid)) && exit('0');

        $affected_rows = $_ENV['demand']->update_status($uid, $pid, DEMAND_STATUS_DENIED);
        if ($affected_rows > 0) {
            $problem = $_ENV['problem']->get($pid);
            $subject = "Sorry, 您没有成功抢到求助 \"{$problem['title']}\"";
            $content ='<a href="' . url("problem/view/$pid", 1) . '">点击查看</a>'; 
            $this->send('', 0, $uid, $subject, $content);
            $_ENV['userlog']->add('denied', "pid:'$pid',uid:'$uid'");
            exit('1');
        }
        exit('0');
    }

    public function onview() {
        $sid = $this->get[2];
        if (empty($sid)) {
            $this->message("非法链接，缺少参数!", 'STOP');
        }

        $_ENV['service']->update_view_num($sid);
        $service = $_ENV['service']->get_by_id($sid);
        $service['cid_list'] = $_ENV['service_category']->get_by_sid($sid);

        $user_comment = $_ENV['service_comment']->get_by_uid_sid($this->user['uid'], $sid);

        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;
        $tot_comment_num = $_ENV['service_comment']->get_comment_num_by_sid($sid);
        $comment_list = $_ENV['service_comment']->get_by_sid($sid, $start, $pagesize);

        $edu_list = $_ENV['education']->get_by_uid($service['uid']);

        $departstr = page($tot_comment_num, $pagesize, $page, "service/view/$sid");
        include template('viewservice');
    }

    public function oncomment() {
        if ($this->user['uid'] > 0) {
            $sid = $this->post['sid'];
            $score = min(5.0, floatval($this->post['score']));
            $content = $this->post['content'];

            $trade_info = $_ENV['trade']->get_trade_info_by_uid_target_id_type($this->user['uid'], $sid, TRADE_TARGET_SERVICE);
            if (empty($trade_info)) {
                exit("-2"); // 未购买过此服务
            }

            $trade = $_ENV['trade']->get_trade_by_trade_no($trade_info['trade_no']);
            if ($trade['status'] != TRADE_STATUS_FINISHED) {
                exit("-3"); // 购买过此服务，但未交易成功
            }

            $user_comment = $_ENV['service_comment']->get_by_uid_sid($this->user['uid'], $sid);
            if (!empty($user_comment)) {
                exit("-4"); // 已评论过此服务
            }

            $comment_id = $_ENV['service_comment']->add($sid, $content, $score, $this->user['uid'], $this->user['username']);
            if ($comment_id > 0) {
                // 得分超过3分（5分制）才能够拿到回报
                if ($score >= 3) {
                    $service = $_ENV['service']->get_by_id($sid);
                    $reward = floor($trade_info['buy_num'] * $service['price'] * $score / 5.0 * 100) / 100;
                    $_ENV['user']->update_balance($service['uid'], $reward);

                    //$info_money = floor($reward * (1 - $this->setting['alipay_fee_rate']) * 100) / 100;
                    $info_money = $reward;

                    $subject = "账户进账" . $info_money . "元";
                    $content = "用户" . $this->user['username'] . "给您的服务打" . $score . "分，账户进账" . $info_money . "元";
                    $this->send("", 0, $service['uid'], $subject, $content, true);

                    // 非满分的情况下，邀请者能够拿到收入
                    if ($score < 5) {
                        // 奖励邀请者
                        $service_user = $_ENV['user']->get_by_uid($service['uid']);
                        if ($service_user['remain_times'] > 0) {
                            $_ENV['user']->update_remain_times($service_user['uid']);

                            $tot_money = ($trade_info['buy_num'] * $service['price']) * 0.1;
                            $money = floor($tot_money * ($score - 2.5) / 2.5 * 100) / 100;
                            $_ENV['user']->update_balance($service_user['invited_by_uid'], $money);

                            //$info_money = floor($money * (1 - $this->setting['alipay_fee_rate']) * 100) / 100;
                            $info_money = $money;
                            $subject = "账户进账" . $info_money . "元";
                            $content = "您邀请的用户" . $service['username'] . "咨询服务获得用户打分：" . $score . "分，作为奖励，系统奖励您" . $info_money . "元";
                            $this->send("", 0, $service_user['invited_by_uid'], $subject, $content);
                        }
                    }
                }
                exit("1");
            }
            exit("-1");
        }
        exit('0');
    }

    public function oncomment_support() {
        if ($this->user['uid'] > 0) {
            $comment_id = $this->get[2];
            $thumbs_type = $this->get[3];

            if (empty($comment_id)) {
                exit('-1');
            }
            $user_support = $_ENV['service_comment']->get_user_support($this->user['uid'], $comment_id);
            if (!empty($user_support)) {
                exit('-2');
            }
            $_ENV['service_comment']->add_support($this->user['uid'], $comment_id, $thumbs_type);
            $comment = $_ENV['service_comment']->get_by_id($comment_id);

            if ($thumbs_type == '0') {
                exit("{$comment['up']}");
            } else {
                exit("{$comment['down']}");
            }
        }
        exit('0');
    }

    // 关闭求助
    function onclose() {
        $pid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['pid'];
        $problem = $_ENV['problem']->get($pid);

        if ($problem['authorid'] != $this->user['uid']) {
            $this->message('您无权执行此操作！', 'STOP');
        }

        if ($problem['status'] != PB_STATUS_UNSOLVED &&
            $problem['status'] != PB_STATUS_UNAUDIT) {
            $this->message('求助状态已经改变，关闭求助失败！', 'BACK');
        }

        $_ENV['problem']->update_status($pid, PB_STATUS_CLOSED);

        $subject = "您关闭求助\"{$problem['title']}\"成功！";
        $content ='<a href="' . url("problem/view/$pid", 1) . '">点击查看</a>'; 
        $this->send('', 0, $problem['authorid'], $subject, $content);
        $viewurl = urlmap("problem/view/$pid", 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    // 搜索求助
    function onsearch() {
        $pstatus = $status = $this->get[3] ? $this->get[3] : 'all';
        if ($status == 'all') {
            $pstatus = PB_STATUS_UNSOLVED . "," . PB_STATUS_SOLVED . "," . PB_STATUS_CLOSED;
        } else if ($status != PB_STATUS_SOLVED) {
            $status = $pstatus = PB_STATUS_SOLVED;
        }

        $word = urldecode($this->post['word'] ? str_replace("%27", "", $this->post['word']) : $this->get[2]);
        (!trim($word)) && $this->message("搜索关键词不能为空!", 'STOP');
        $navtitle = $word . '-搜索求助';
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        if (preg_match("/^tag:(.+)/", $word, $tagarr)) {
            $tag = $tagarr[1];
            $rownum = $_ENV['problem']->rownum_by_tag($tag, $pstatus);
            $problemlist = $_ENV['problem']->list_by_tag($tag, $pstatus, $startindex, $pagesize);
        } else {
            $problemlist = $_ENV['problem']->search_title($word, $pstatus, $startindex, $pagesize);
            $rownum = $_ENV['problem']->search_title_num($word, $pstatus);
        }
        $related_words = $_ENV['problem']->get_related_words();
        $hot_words = $_ENV['problem']->get_hot_words();
        $corrected_words = $_ENV['problem']->get_corrected_word($word);
        $departstr = page($rownum, $pagesize, $page, "problem/search/$word/$status");
        include template('problem_search');
    }

    // 按标签搜索求助
    function ontag() {
        $tag = urldecode($this->get['2']);
        $navtitle = $tag . '-标签搜索';
        @$page = max(1, intval($this->get[4]));
        $pstatus = $status = intval($this->get[3]);
        (PB_STATUS_UNSOLVED == $status) && ($pstatus = PB_STATUS_UNSOLVED . "," . PB_STATUS_SOLVED);
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("problem_tag", " tname='$tag' ");
        $pagesize = $this->setting['list_default'];
        $problemlist = $_ENV['problem']->list_by_tag($tag, $pstatus, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "problem/tag/$tag/$status");
        include template('problem_search');
    }

    function ondelete() {
        $_ENV['problem']->remove(intval($this->get[2]));
        $this->message('求助删除成功！');
    }

    //编辑问题
    function onedit() {
        $op_type = "edit"; // 操作类型：更改求助
        $navtitle = '更改求助信息';
        $pid = $this->get[2] ? $this->get[2] : $this->post['pid'];

        $problem = $_ENV['problem']->get($pid);

        if ($problem['authorid'] != $this->user['uid']) {
            $this->message("您没有发布该求助！", "BACK");
        }

        if (!$problem) {
            $this->message("求助不存在或已被删除！", "STOP");
        }

        if ($problem['status'] == PB_STATUS_SOLVED) {
            $this->message("求助已被解决！", "BACK");
        }

        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $cid = $this->post['category_id'];
            $tags = trim($this->post["ptags"]);
            $price = intval($this->post["price"]);

            $this->setting['code_problem'] && $this->checkcode(); //检查验证码
            $delta_price = $price - intval($problem['price']);
            $status = ($this->setting['verify_problem'] ? PB_STATUS_UNAUDIT : PB_STATUS_UNSOLVED);

            //检查标题违禁词
            $contentarray = checkwords($title);
            1 == $contentarray[0] && $status = PB_STATUS_UNAUDIT;
            2 == $contentarray[0] && $this->message("求助包含非法关键词，发布失败!", 'BACK');
            $title = $contentarray[1];

            //检查问题描述违禁词
            $descarray = checkwords($description);
            1 == $descarray[0] && $status = PB_STATUS_UNAUDIT;
            2 == $descarray[0] && $this->message("求助描述包含非法关键词，发布失败!", 'BACK');
            $description = $descarray[1];

            $_ENV['problem']->update($pid, $title, $description, $cid, $price, $status); 

            // 插入标签
            !empty($tags) && $taglist = explode(" ", $tags);
            $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $pid);

            $viewurl = urlmap("problem/view/$pid", 2);
            $_ENV['userlog']->add('problem', "更新: pid='$pid',delta_price='$delta_price'");
            if (PB_STATUS_UNAUDIT == $status) {
                $this->message('求助更新成功！为了确保求助的合法性，我们会对您提的求助进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message("求助更新成功！", $viewurl);
            }
        } else {
            $taglist = $_ENV['tag']->get_by_pid($pid);
            include template("problem");
        }
    }

    //编辑标签
    function onedittag() {
        $tag = trim($this->post['ptags']);
        $pid = intval($this->post['pid']);
        $viewurl = urlmap("problem/view/$pid", 2);
        $message = $tag ? "标签修改成功!" : "标签不能为空!";
        $taglist = explode(" ", $tag);
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $pid);
        $this->message($message, $viewurl);
    }

    function onevaluser(){
        $pid = intval($this->post['pid']);
        $solverdesc = trim($this->post['solverdesc']);
        $solverscore = intval($this->post['score']);
        if ($pid > 0 && $solverscore >= 1 && $solverscore <= 10) {
            $problem = $_ENV['problem']->get($pid);
            if ($problem['authorid'] == $this->user['uid']) {
                $_ENV['problem']->update_solver_score($pid, $solverscore, $solverdesc);
                $this->message("评价成功，您为大家提供了宝贵的一份反馈，谢谢！", "p-$pid");
            } else {
                $this->message("您无权执行此操作，请重新确认！", "STOP");
            }
        } else {
            $this->message("不良参数，请重新操作", "STOP");
        }
    }

    private function fetch_picture_path($target_path, $uid) {
        $uid = sprintf("%010d", intval($uid));
        $target_path = $target_path . "/" . $uid;
        if (file_exists(WEB_ROOT . $target_path . ".jpg"))
            return $target_path . ".jpg";
        if (file_exists(WEB_ROOT . $target_path . ".jepg"))
            return $target_path. ".jepg";
        if (file_exists(WEB_ROOT . $target_path. ".gif"))
            return $target_path . ".gif";
        if (file_exists(WEB_ROOT . $target_path . ".png"))
            return $target_path . ".png";
        return "";
    }
}

?>
