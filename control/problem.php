<?php

!defined('IN_SITE') && exit('Access Denied');

class problemcontrol extends base
{
    function problemcontrol(& $get, & $post)
    {
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
    function onadd()
    {
        $navtitle = "发布求助";
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $tags = trim($this->post["ptags"]);
            $price = intval($this->post["price"]);

            $this->setting['code_ask'] && $this->checkcode(); //检查验证码
            (intval($this->user['balance']) < $price) && $this->message("账户余额不足!", 'BACK');

            //检查审核和内容外部URL过滤
            //$status = intval(1 != (1 & $this->setting['verify_problem']));
            $status = ((1 & $this->setting['verify_problem']) ? PB_STATUS_UNAUDIT : PB_STATUS_UNSOLVED);
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($description)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = PB_STATUS_UNAUDIT;
                2 == $allow && $description = filter_outer($description);
            }

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

            // 检查提问数是否超过组设置
            ($this->user['problemlimits'] && ($_ENV['userlog']->rownum_by_time('problem') >= $this->user['problemlimits'])) &&
                    $this->message("你每小时所提求助量已经超过本站限制" . $this->user['problemlimits'] . ',请稍后再试！', 'BACK');

            $pid = $_ENV['problem']->add($title, $description, $price);
            $_ENV['problem']->update_status($pid, PB_STATUS_UNSOLVED); //后续可在此添加审核功能
            $_ENV['user']->add_problem($this->user['uid'], $price);

            // 插入标签
            !empty($tags) && $taglist = explode(" ", $tags);
            $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $pid);

            $viewurl = urlmap("problem/view/$pid", 2);
            $_ENV['userlog']->add('problem', '回报:' . $price);
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

    function ondemand()
    {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }
        if (0 == $this->user['can_teach']) { //没有获得教师的资格
            $this->message("您还没有获得提供帮助的资格，请详细填写您的个人资料，通过审核之后，就可以提供啦!", "user/resume");
        }

        $pid = $this->post['dialog_pid'];
        $message = $this->post['demand_message'];

        empty($pid) && $this->message("请提供求助编号，系统无法识别您的此次请求", "user/resume");

        if ($_ENV['demand']->already_demand($pid)) {
            $this->message("您之前已经发过一次请求，请您耐心等待!", "problem/view/$pid");
        }
        $last_insert_id = $_ENV['demand']->add($pid, $message);

        if ($last_insert_id > 0) {
            $_ENV['problem']->add_demand($pid);

            $problem = $_ENV['problem']->get($pid);
            $subject = "有人想帮您解决您的求助 \"{$problem['title']}\"";
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看求助</a>';
            $this->send('', 0, $problem['authorid'], $subject, $content);
            $this->message("恭喜您，已经成功发送请求信息到对方账户，请您耐心等待！", "problem/view/$pid");
        } else {
            $this->message("Oops，请求发送失败！", "problem/view/$pid");
        }
    }

    function onajaxdemand()
    {
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
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看求助</a>';
            $this->send('', 0, $problem['authorid'], $subject, $content);
            exit('1');
        }
        exit('0');
    }

    function onajaxcancel()
    {
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
            exit('1');
        }
        exit('0');
    }

    function onajaxaccept()
    {
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
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看求助</a>';
            $this->send('', 0, $uid, $subject, $content);
            exit('1');
        }
        exit('0');
    }

    function onajaxdenied()
    {
        $uid = $this->get[2];
        $pid = $this->get[3];
        (empty($uid) || empty($pid)) && exit('0');

        $affected_rows = $_ENV['demand']->update_status($uid, $pid, DEMAND_STATUS_DENIED);
        if ($affected_rows > 0) {
            $problem = $_ENV['problem']->get($pid);
            $subject = "Sorry, 您没有成功抢到求助 \"{$problem['title']}\"";
            $content ='<br/><a href="' . url("problem/view/$pid", 1) . '">点击查看求助</a>';
            $this->send('', 0, $uid, $subject, $content);
            exit('1');
        }
        exit('0');
    }

    // 浏览问题
    function onview()
    {
        $this->setting['stopcopy_on'] && $_ENV['problem']->stopcopy(); //是否开启了防采集功能
        $pid = $this->get[2]; //接收pid参数
        $_ENV['problem']->add_views($pid); //更新问题浏览次数

        $problem = $_ENV['problem']->get($pid);
        empty($problem) && $this->message('问题已经被删除！');
        (PB_STATUS_UNAUDIT == $problem['status']) && $this->message('问题正在审核中，请耐心等待！');

        // 问题过期处理
        if ($problem['endtime'] < $this->time && ($problem['status'] == 1)) {
            $problem['status'] = 4;
            $_ENV['problem']->update_status($pid, 8);
            //$this->send($problem['authorid'], $problem['pid'], 2);
        }
        $asktime = tdate($problem['time']);
        $endtime = timeLength($problem['endtime'] - $this->time);
        $solvetime = tdate($problem['endtime']);
        $typearray = array('1' => 'nosolve', '2' => 'solve', '4' => 'nosolve', '8' => 'close');
        $typedescarray = array('1' => '待解决', '2' => '已解决', '4' => '已过期', '8' => '已关闭');
        $navtitle = $problem['title'];

        $taglist = $_ENV['tag']->get_by_pid($pid);
        //$teachers_list = $_ENV['user']->get_list();
        $demand_user_lists = $_ENV['demand']->get_wait_uids($pid);
        $accept_users = $_ENV['demand']->get_accept_uid($pid);

        for ($i = 0; $i < count($accept_users); ++$i) {
            $accept_users[$i]['userinfo'] = $_ENV['user']->get_by_uid($accept_users[$i]['uid']);
            $accept_users[$i]['userresume'] = $_ENV['userresume']->get_by_uid($accept_users[$i]['uid']);
            $accept_users[$i]['userskill'] = $_ENV['userskill']->get_by_uid($accept_users[$i]['uid']);
        } 

        $curnavname = $navlist[count($navlist) - 1]['name'];
        if (!$bestanswer) {
            $bestanswer = array();
            $bestanswer['content'] = '';
        }
        if ($this->setting['seo_problem_title']) {
            $seo_title = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_problem_title']);
            $seo_title = str_replace("{wtbt}", $problem['title'], $seo_title);
            $seo_title = str_replace("{wtzt}", $typedescarray[$problem['status']], $seo_title);
            $seo_title = str_replace("{flmc}", $curnavname, $seo_title);
        }
        if ($this->setting['seo_problem_description']) {
            $seo_description = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_problem_description']);
            $seo_description = str_replace("{wtbt}", $problem['title'], $seo_description);
            $seo_description = str_replace("{wtzt}", $typedescarray[$problem['status']], $seo_description);
            $seo_description = str_replace("{flmc}", $curnavname, $seo_description);
            $seo_description = str_replace("{wtms}", $problem['description'], $seo_description);
            $seo_description = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_description);
        }
        if ($this->setting['seo_problem_keywords']) {
            $seo_keywords = str_replace("{wzmc}", $this->setting['site_name'], $this->setting['seo_problem_keywords']);
            $seo_keywords = str_replace("{wtbt}", $problem['title'], $seo_keywords);
            $seo_keywords = str_replace("{wtzt}", $typedescarray[$problem['status']], $seo_keywords);
            $seo_keywords = str_replace("{flmc}", $curnavname, $seo_keywords);
            $seo_keywords = str_replace("{wtbq}", implode(",", $taglist), $seo_keywords);
            $seo_description = str_replace("{description}", $problem['description'], $seo_keywords);
            $seo_keywords = str_replace("{zjda}", strip_tags($bestanswer['content']), $seo_keywords);
        }
        include template("viewproblem");
    }

    // 关闭问题
    function onclose()
    {
        $pid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['pid'];
        $_ENV['problem']->update_status($pid, PB_STATUS_CLOSED);
        $viewurl = urlmap("problem/view/$pid", 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    // 搜索求助
    function onsearch()
    {
        $pstatus = $status = $this->get[3] ? $this->get[3] : PB_STATUS_UNSOLVED;
        (1 == $status) && ($pstatus = "1,2");
        (2 == $status) && ($pstatus = "2");
        $word = urldecode($this->post['word'] ? str_replace("%27", "", $this->post['word']) : $this->get[2]);
        (!trim($word)) && $this->message("搜索关键词不能为空!", 'BACK');
        $navtitle = $word . '-搜索问题';
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

    // 按标签搜索问题
    function ontag()
    {
        $tag = urldecode($this->get['2']);
        $navtitle = $tag . '-标签搜索';
        @$page = max(1, intval($this->get[4]));
        $pstatus = $status = intval($this->get[3]);
        (!$status) && ($pstatus = "1,2");
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("problem_tag", " tname='$tag' ");
        $pagesize = $this->setting['list_default'];
        $problemlist = $_ENV['problem']->list_by_tag($tag, $pstatus, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "problem/tag/$tag/$status");
        include template('problem_search');
    }

    // 提问自动搜索已经解决的问题
    function onajaxsearch() {
        $title = $this->get[2];
        $problemlist = $_ENV['problem']->search_title($title, 2, 0, 5);
        include template('ajaxsearch');
    }

    function ondelete()
    {
        $_ENV['problem']->remove(intval($this->get[2]));
        $this->message('问题删除成功！');
    }

    //编辑问题
    function onedit() {
        $navtitle = '编辑问题';
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $problem = $_ENV['problem']->get($qid);
        if (!$problem)
            $this->message("问题不存在或已被删除!", "STOP");
        $navlist = $_ENV['category']->get_navigation($problem['cid'], true);
        if (isset($this->post['submit'])) {
            $viewurl = urlmap('problem/view/' . $qid, 2);
            $title = trim($this->post['title']);
            (!trim($title)) && $this->message('问题标题不能为空!', $viewurl);
            $_ENV['problem']->update_content($qid, $title, $this->post['content']);
            $this->message('问题编辑成功!', $viewurl);
        }
        include template("editproblem");
    }

    //编辑标签
    function onedittag()
    {
        $tag = trim($this->post['ptags']);
        $pid = intval($this->post['pid']);
        $viewurl = urlmap("problem/view/$pid", 2);
        $message = $tag ? "标签修改成功!" : "标签不能为空!";
        $taglist = explode(" ", $tag);
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $pid);
        $this->message($message, $viewurl);
    }


//////////////////////////////////////////////////////////////////////
    //移动分类
    function onmovecategory() {
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
            $qid = $this->post['qid'];
            $viewurl = urlmap('problem/view/' . $qid, 2);
            $category = $this->cache->load('category');
            if ($category[$cid]['grade'] == 1) {
                $cid1 = $cid;
            } else if ($category[$cid]['grade'] == 2) {
                $cid2 = $cid;
                $cid1 = $category[$cid]['pid'];
            } else if ($category[$cid]['grade'] == 3) {
                $cid3 = $cid;
                $cid2 = $category[$cid]['pid'];
                $cid1 = $category[$cid2]['pid'];
            } else {
                $this->message('分类不存在，请更下缓存!', $viewurl);
            }
            $_ENV['problem']->update_category($qid, $cid, $cid1, $cid2, $cid3);
            $this->message('问题分类修改成功!', $viewurl);
        }
    }

    //设为未解决
    function onnosolve() {
        $qid = intval($this->get[2]);
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $_ENV['problem']->change_to_nosolve($qid);
        $this->message('问题状态设置成功!', $viewurl);
    }

    //前台删除问题回答
    function ondeleteanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $_ENV['answer']->remove_by_qid($aid, $qid);
        $this->message("回答删除成功!", $viewurl);
    }

    //前台审核回答
    function onverifyanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $_ENV['answer']->change_to_verify($aid);
        $this->message("回答审核完成!", $viewurl);
    }

    //问题推荐
    function onrecommend() {
        $qid = intval($this->get[2]);
        $_ENV['problem']->change_recommend($qid, 6, 2);
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $this->message('问题推荐成功!', $viewurl);
    }

    // 顶指定问题
    function onajaxgood() {
        $qid = $this->get[2];
        $tgood = tcookie('good_' . $qid);
        !empty($tgood) && exit('-1');
        $_ENV['problem']->update_goods($qid);
        tcookie('good_' . $qid, $qid);
        exit('1');
    }

    // 追问模块---追问
    function onappendanswer() {
        $this->load("message");
        $qid = intval($this->get[2]) ? $this->get[2] : intval($this->post['qid']);
        $aid = intval($this->get[3]) ? $this->get[3] : intval($this->post['aid']);
        $type = intval($this->get[4]) ? $this->get[4] : intval($this->post['type']);
        $problem = $_ENV['problem']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        if (isset($this->post['submit'])) {
            $_ENV['answer']->add_tag($aid, $this->post['content'], $answer['tag']);
            $_ENV['message']->add($problem['author'], $problem['authorid'], $answer['authorid'], '问题追问:' . $problem['title'], $problem['description'] . '<br /> <a href="' . url('problem/view/' . $qid, 1) . '">点击查看问题</a>');
            $viewurl = urlmap('problem/view/' . $qid, 2);
            isset($type) ? $this->message('继续回答成功!', $viewurl) : $this->message('继续提问成功!', $viewurl);
        }
        include template("appendanswer");
    }

    // 修改回答
    function oneditanswer() {
        $navtitle = '修改回答';
        $aid = $this->get[2] ? $this->get[2] : $this->post['aid'];
        $answer = $_ENV['answer']->get($aid);
        (!$answer) && $this->message("回答不存在或已被删除！", "STOP");
        $problem = $_ENV['problem']->get($answer['qid']);
        $navlist = $_ENV['category']->get_navigation($problem['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            $viewurl = urlmap('problem/view/' . $problem['id'], 2);

            //检查审核和内容外部URL过滤
            $status = intval(2 != (2 & $this->setting['verify_problem']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", $viewurl);
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", $viewurl);
            $content = $contentarray[1];

            $_ENV['answer']->update_content($aid, $content, $status);

            if (0 == $status) {
                $this->message('修改回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......', $viewurl);
            } else {
                $this->message('修改回答成功！', $viewurl);
            }
        }
        include template("editanswer");
    }

    // 追加悬赏
    function onaddscore() {
        $qid = intval($this->post['qid']);
        $score = intval($this->post['score']);
        if ($this->user['credit2'] < $score) {
            $this->message("财富值不足!", 'BACK');
        }
        $_ENV['problem']->update_score($qid, $score);
        $this->credit($this->user['uid'], 0, -$score, 0, 'offer');
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $this->message('追加悬赏成功！', $viewurl);
    }

    // 补充提问细节
    function onsupply() {
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $problem = $_ENV['problem']->get($qid);
        if (!$problem)
            $this->message("问题不存在或已被删除!", "STOP");
        $navlist = $_ENV['category']->get_navigation($problem['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_problem']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查违禁词
            $contentarray = checkwords($content);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
            $content = $contentarray[1];

            $problem = $_ENV['problem']->get($qid);
            //问题最大补充数限制
            (count(unserialize($problem['supply'])) >= $this->setting['apend_problem_num']) && $this->message("您已超过问题最大补充次数" . $this->setting['apend_problem_num'] . ",发布失败！", 'BACK');
            $_ENV['problem']->add_supply($qid, $problem['supply'], $content, $status); //添加问题补充
            $viewurl = urlmap('problem/view/' . $qid, 2);
            if (0 == $status) {
                $this->message('补充问题成功！为了确保问答的质量，我们会对您的提问内容进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message('补充问题成功！', $viewurl);
            }
        }
        include template("supply");
    }

    // 采纳答案
    function onadopt() {
        $qid = intval($this->post['qid']);
        $aid = intval($this->post['aid']);
        $comment = $this->post['content'];
        $problem = $_ENV['problem']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
            $this->load("answer_comment");
            $_ENV['answer_comment']->add($aid, $comment, $problem['authorid'], $problem['author']);
            //把问题的悬赏送给被采纳为答案的回答者,同时发消息通知回答者
            $this->credit($answer['authorid'], $this->setting['credit1_adopt'], intval($problem['price'] + $this->setting['credit2_adopt']), 0, 'adopt');
            //$this->send($answer['authorid'], $problem['id'], 1);
            $viewurl = urlmap('problem/view/' . $qid, 2);
        }

        $this->message('采纳答案成功！', $viewurl);
    }

    // 提交回答
    function onanswer() {
        //只允许专家回答问题
        if (isset($this->setting['allow_expert']) && $this->setting['allow_expert'] && !$this->user['expert']) {
            $this->message('站点已设置为只允许专家回答问题，如有疑问请联系站长.');
        }
        $qid = $this->post['qid'];
        $problem = $_ENV['problem']->get($qid);
        if (!$problem) {
            $this->message('提交回答失败,问题不存在!');
        }
        if ($this->user['uid'] == $problem['authorid']) {
            $this->message('提交回答失败，不能自问自答！', 'problem/view/' . $qid);
        }
        $this->setting['code_ask'] && $this->checkcode(); //检查验证码
        $already = $_ENV['problem']->already($qid, $this->user['uid']);
        $already && $this->message('不能重复回答同一个问题，可以修改自己的回答！', 'problem/view/' . $qid);
        $title = $this->post['title'];
        $content = $this->post['content'];
        //检查审核和内容外部URL过滤
        $status = intval(2 != (2 & $this->setting['verify_problem']));
        $allow = $this->setting['allow_outer'];
        if (3 != $allow && has_outer($content)) {
            0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
            1 == $allow && $status = 0;
            2 == $allow && $content = filter_outer($content);
        }
        //检查违禁词
        $contentarray = checkwords($content);
        1 == $contentarray[0] && $status = 0;
        2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
        $content = $contentarray[1];

        /* 检查提问数是否超过组设置 */
        ($this->user['answerlimits'] && ($_ENV['userlog']->rownum_by_time('answer') >= $this->user['answerlimits'])) &&
                $this->message("你已超过每小时最大回答数" . $this->user['answerlimits'] . ',请稍后再试！', 'BACK');

        $_ENV['answer']->add($qid, $title, $content, $status);
        //回答问题，添加积分
        $this->credit($this->user['uid'], $this->setting['credit1_answer'], $this->setting['credit2_answer']);
        //给提问者发送通知
        //$this->send($problem['authorid'], $problem['id'], 0);
        //如果ucenter开启，则postfeed
        if ($this->setting["ucenter_open"] && $this->setting["ucenter_answer"]) {
            $this->load('ucenter');
            $_ENV['ucenter']->answer_feed($problem, $content);
        }
        $viewurl = urlmap('problem/view/' . $qid, 2);
        $_ENV['userlog']->add('answer');
        if (0 == $status) {
            $this->message('提交回答成功！为了确保问答的质量，我们会对您的回答内容进行审核。请耐心等待......', 'BACK');
        } else {
            $this->message('提交回答成功！', $viewurl);
        }
    }
}

?>
