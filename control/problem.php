<?php

!defined('IN_SITE') && exit('Access Denied');

class problemcontrol extends base {

    function problemcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load("problem");
        $this->load("tag");
        $this->load("userlog");
        $this->load("demand");
        $this->load("charging");
        $this->load('userresume');
        $this->load('userskill');
    }

    // 发布求助
    function onadd() {
        $navtitle = "发布求助";
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $tags = trim($this->post["ptags"]);
            $price = intval($this->post["price"]);

            $this->setting['code_problem'] && $this->checkcode(); //检查验证码
            (intval($this->user['balance']) < $price) && $this->message("账户余额不足!", 'BACK');

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

            $pid = $_ENV['problem']->add($title, $description, $price);
            $_ENV['problem']->update_status($pid, $status);
            $_ENV['user']->add_problem($this->user['uid'], $price);

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
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看</a>';
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
            $subject = "有人想帮您解决您的求助 \"{$problem['title']}\"";
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看</a>';
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
        $uid = $this->get[2];
        $pid = $this->get[3];
        (empty($uid) || empty($pid)) && exit('0');

        $affected_rows = $_ENV['demand']->update_status($uid, $pid, DEMAND_STATUS_ACCEPT);
        $affected_rows = 1;
        if ($affected_rows > 0) {
            $user = $_ENV['user']->get_by_uid($uid);
            $_ENV['problem']->update_solver($pid, $user['uid'], $user['username']);
            $problem = $_ENV['problem']->get($pid);

            $system = $problem['price'] * $user['charge'] / 100.0;
            $cid = $_ENV['charging']->add($problem['pid'], $problem['authorid'], $problem['author'], $user['uid'], $user['username'], $system, $problem['price']);

            $subject = "您抢求助 \"{$problem['title']}\" 成功啦";
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看</a>';
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
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看</a>'; 
            $this->send('', 0, $uid, $subject, $content);
            $_ENV['userlog']->add('denied', "pid:'$pid',uid:'$uid'");
            exit('1');
        }
        exit('0');
    }

    // 浏览求助
    function onview() {
        $this->setting['stopcopy_on'] && $_ENV['problem']->stopcopy(); //是否开启了防采集功能
        $pid = $this->get[2]; //接收pid参数
        $_ENV['problem']->add_views($pid); //更新求助浏览次数

        $problem = $_ENV['problem']->get($pid);
        empty($problem) && $this->message('求助已经被删除！');
        (PB_STATUS_UNAUDIT == $problem['status']) && $this->message('求助正在审核中，请耐心等待！');

        // 求助过期处理
        if ($problem['endtime'] < $this->time && ($problem['status'] == PB_STATUS_UNSOLVED)) {
            $problem['status'] = PB_STATUS_CLOSED;
            $_ENV['problem']->update_status($pid, PB_STATUS_CLOSED);

            $user = $_ENV['user']->get_by_uid($problem['authorid']);

            $subject = "您的求助\"{$problem['title']}\"已过期";
            $content ='<br/><a href="' . url("problem/view/{$problem['pid']}", 1) . '">点击查看</a>'; 
            $this->send('', 0, $user['uid'], $subject, $content);
        }
        $asktime = tdate($problem['time']);
        $endtime = timeLength($problem['endtime'] - $this->time);
        $solvetime = tdate($problem['endtime']);

        $navtitle = $problem['title'];

        $taglist = $_ENV['tag']->get_by_pid($pid);
        $demand_user_lists = $_ENV['demand']->get_wait_uids($pid);
        $accept_users = $_ENV['demand']->get_accept_uid($pid);

        for ($i = 0; $i < count($accept_users); ++$i) {
            $accept_users[$i]['userinfo'] = $_ENV['user']->get_by_uid($accept_users[$i]['uid']);
            $accept_users[$i]['userresume'] = $_ENV['userresume']->get_by_uid($accept_users[$i]['uid']);
            $accept_users[$i]['userskill'] = $_ENV['userskill']->get_by_uid($accept_users[$i]['uid']);
        } 
        include template("viewproblem");
    }

    // 关闭求助
    function onclose() {
        $pid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['pid'];
        $_ENV['problem']->update_status($pid, PB_STATUS_CLOSED);
        $viewurl = urlmap("problem/view/$pid", 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    // 搜索求助
    function onsearch() {
        $pstatus = $status = $this->get[3] ? $this->get[3] : PB_STATUS_UNSOLVED;
        (PB_STATUS_UNSOLVED == $status) && ($pstatus = PB_STATUS_UNSOLVED . "," . PB_STATUS_SOLVED);
        (PB_STATUS_SOLVED == $status) && ($pstatus = PB_STATUS_SOLVED);

        $word = urldecode($this->post['word'] ? str_replace("%27", "", $this->post['word']) : $this->get[2]);
        (!trim($word)) && $this->message("搜索关键词不能为空!", 'BACK');
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
        $navtitle = '更改求助信息';
        $pid = $this->get[2] ? $this->get[2] : $this->post['pid'];

        $problem = $_ENV['problem']->get($pid);

        if ($problem['authorid'] != $this->user['uid']) {
            $this->message("您没有发布该求助！", "STOP");
        }

        if (!$problem) {
            $this->message("求助不存在或已被删除！", "STOP");
        }

        if ($problem['status'] == PB_STATUS_SOLVED) {
            $this->message("求助已被解决！", "STOP");
        }
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $tags = trim($this->post["ptags"]);
            $price = intval($this->post["price"]);

            $this->setting['code_problem'] && $this->checkcode(); //检查验证码
            $delta_price = $price - intval($problem['price']);
            (intval($this->user['balance']) < $delta_price) && $this->message("账户余额不足!", 'BACK');
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

            $_ENV['problem']->update($pid, $title, $description, $price, $status); 
            $_ENV['user']->update_balance($problem['authorid'], -$delta_price);

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
            $title = $problem['title'];
            $description = $problem['description'];
            $price = $problem['price'];
            $status = $problem['status'];
            include template("edit_problem");
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
}

?>
