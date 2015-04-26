<?php

!defined('IN_SITE') && exit('Access Denied');

// 0、未审核 1、待解决、2、已解决 4、悬赏的 9、 已关闭问题
class questioncontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load("question");
        $this->load("answer");
    }

    // 交流区首页
    public function ondefault() {
        $navtitle = '交流区';
        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;

        $user_num = $_ENV['user']->rownum_alluser();
        $question_num = $_ENV['question']->get_total_num();
        $questionlist = $_ENV['question']->get_list($startindex, $pagesize);
        $departstr = split_page($question_num, $pagesize, $page, "/question/default&page=%s");
        include template("question");
    }

    // 帖子详细页面
    public function onview() {
        $this->setting['stopcopy_on'] && $_ENV['question']->stopcopy(); //是否开启了防采集功能
        $qid = $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        $navtitle = $question['title'];
        //empty($question) && $this->message('问题已经被删除！');
        //($question['status'] == 2) && $this->message('帖子为通过审核，您看看其他的帖子吧！');

        $asktime = tdate($question['time']);
        if (isset($this->get[3]) && $this->get[3] == 1) {
            $ordertype = 1;
            $ordertitle = '正序查看回复';
        } else {
            $ordertype = 2;
            $ordertitle = '倒序查看回复';
        }

        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);

        //回答分页
        @$page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("answer", " qid=$qid ");
        $answerlist = $_ENV['answer']->list_by_qid($qid, $startindex, $pagesize);
        $departstr = split_page($rownum, $pagesize, $page, "/question/view?qid=" . $qid . "&page=%s");
        include template("view_question");
    }

    // 提交回答
    public function onanswer() {
        $this->check_login();

        $qid = $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question) {
            $this->message('提交回答失败,帖子不存在!');
        }

        $this->setting['code_reply'] && $this->checkcode(); //检查验证码
        $title = $this->post['title'];
        $content = $this->post['content'];

        //检查违禁词
        $contentarray = checkwords($content);
        2 == $contentarray[0] && $this->message("内容包含非法关键词，发布失败!", 'BACK');
        $content = $contentarray[1];

        // 检查提问数是否超过组设置
        ($this->user['answerlimits'] && ($_ENV['userlog']->rownum_by_time('answer') >= $this->user['answerlimits'])) &&
                $this->message("你已超过每小时最大回答数" . $this->user['answerlimits'] . ',请稍后再试！', 'BACK');

        $_ENV['answer']->add($this->user['uid'], $this->user['username'], $qid, $title, $content);
        $_ENV['question']->update_answers($qid);

        //给提问者发送通知
        //$this->send($question['authorid'], $question['qid'], 0);
        $this->jump('question/view/' . $qid);
    }

    // 采纳答案
    function onadopt() {
        $qid = intval($this->post['qid']);
        $aid = intval($this->post['aid']);
        $comment = $this->post['content'];
        $question = $_ENV['question']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        $ret = $_ENV['answer']->adopt($qid, $answer);
        if ($ret) {
            $this->load("answer_comment");
            $_ENV['answer_comment']->add($aid, $comment, $question['authorid'], $question['author']);
            //把问题的悬赏送给被采纳为答案的回答者,同时发消息通知回答者
            $this->credit($answer['authorid'], $this->setting['credit1_adopt'], intval($question['price'] + $this->setting['credit2_adopt']), 0, 'adopt');
            $this->send($answer['authorid'], $question['id'], 1);
            $viewurl = urlmap('question/view/' . $qid, 2);
        }

        $this->message('采纳答案成功！', $viewurl);
    }

    /* 结束问题，没有满意的回答，还可直接结束提问，关闭问题。 */

    function onclose() {
        $qid = intval($this->get[2]) ? intval($this->get[2]) : $this->post['qid'];
        $_ENV['question']->update_status($qid, 9);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('关闭问题成功！', $viewurl);
    }

    /* 补充提问细节 */

    function onsupply() {
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question)
            $this->message("问题不存在或已被删除!", "STOP");
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
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

            $question = $_ENV['question']->get($qid);
            //问题最大补充数限制
            (count(unserialize($question['supply'])) >= $this->setting['apend_question_num']) && $this->message("您已超过问题最大补充次数" . $this->setting['apend_question_num'] . ",发布失败！", 'BACK');
            $_ENV['question']->add_supply($qid, $question['supply'], $content, $status); //添加问题补充
            $viewurl = urlmap('question/view/' . $qid, 2);
            if (0 == $status) {
                $this->message('补充问题成功！为了确保问答的质量，我们会对您的提问内容进行审核。请耐心等待......', 'BACK');
            } else {
                $this->message('补充问题成功！', $viewurl);
            }
        }
        include template("supply");
    }

    /* 搜索问题 */

    function onsearch() {
        $qstatus = $status = $this->get[3] ? $this->get[3] : 1;
        (1 == $status) && ($qstatus = "1,2,6,9");
        (2 == $status) && ($qstatus = "2,6");
        $word = urldecode($this->post['word'] ? str_replace("%27", "", $this->post['word']) : $this->get[2]);
        (!trim($word)) && $this->message("搜索关键词不能为空!", 'BACK');
        $navtitle = $word . '-搜索问题';
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        if (preg_match("/^tag:(.+)/", $word, $tagarr)) {
            $tag = $tagarr[1];
            $rownum = $_ENV['question']->rownum_by_tag($tag, $qstatus);
            $questionlist = $_ENV['question']->list_by_tag($tag, $qstatus, $startindex, $pagesize);
        } else {
            $questionlist = $_ENV['question']->search_title($word, $qstatus, 0, $startindex, $pagesize);
            $rownum = $_ENV['question']->search_title_num($word, $qstatus);
        }
        $related_words = $_ENV['question']->get_related_words();
        $hot_words = $_ENV['question']->get_hot_words();
        $corrected_words = $_ENV['question']->get_corrected_word($word);
        $departstr = page($rownum, $pagesize, $page, "question/search/$word/$status");
        include template('search');
    }

    /* 按标签搜索问题 */

    function ontag() {
        $tag = urldecode($this->get['2']);
        $navtitle = $tag . '-标签搜索';
        @$page = max(1, intval($this->get[4]));
        $qstatus = $status = intval($this->get[3]);
        (!$status) && ($qstatus = "1,2,6");
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("question_tag", " tname='$tag' ");
        $pagesize = $this->setting['list_default'];
        $questionlist = $_ENV['question']->list_by_tag($tag, $qstatus, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "question/tag/$tag/$status");
        include template('search');
    }

    /* 提问自动搜索已经解决的问题 */

    function onajaxsearch() {
        $title = $this->get[2];
        $questionlist = $_ENV['question']->search_title($title, 2, 1, 0, 5);
        include template('ajaxsearch');
    }

    /* 顶指定问题 */

    function onajaxgood() {
        $qid = $this->get[2];
        $tgood = tcookie('good_' . $qid);
        !empty($tgood) && exit('-1');
        $_ENV['question']->update_goods($qid);
        tcookie('good_' . $qid, $qid);
        exit('1');
    }

    function ondelete() {
        $_ENV['question']->remove(intval($this->get[2]));
        $this->message('问题删除成功！');
    }

    //问题推荐
    function onrecommend() {
        $qid = intval($this->get[2]);
        $_ENV['question']->change_recommend($qid, 6, 2);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('问题推荐成功!', $viewurl);
    }

    //编辑问题
    function onedit() {
        $navtitle = '编辑问题';
        $qid = $this->get[2] ? $this->get[2] : $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (!$question)
            $this->message("问题不存在或已被删除!", "STOP");
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $viewurl = urlmap('question/view/' . $qid, 2);
            $title = trim($this->post['title']);
            (!trim($title)) && $this->message('问题标题不能为空!', $viewurl);
            $_ENV['question']->update_content($qid, $title, $this->post['content']);
            $this->message('问题编辑成功!', $viewurl);
        }
        include template("editquestion");
    }

    //编辑标签
    function onedittag() {
        $tag = trim($this->post['qtags']);
        $qid = intval($this->post['qid']);
        $viewurl = urlmap("question/view/$qid", 2);
        $message = $tag ? "标签修改成功!" : "标签不能为空!";
        $taglist = explode(" ", $tag);
        $taglist && $_ENV['tag']->multi_add(array_unique($taglist), $qid);
        $this->message($message, $viewurl);
    }

    //设为未解决
    function onnosolve() {
        $qid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['question']->change_to_nosolve($qid);
        $this->message('问题状态设置成功!', $viewurl);
    }

    //前台删除问题回答
    function ondeleteanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['answer']->remove_by_qid($aid, $qid);
        $this->message("回答删除成功!", $viewurl);
    }

    //前台审核回答
    function onverifyanswer() {
        $qid = intval($this->get[3]);
        $aid = intval($this->get[2]);
        $viewurl = urlmap('question/view/' . $qid, 2);
        $_ENV['answer']->change_to_verify($aid);
        $this->message("回答审核完成!", $viewurl);
    }
    
    //问题关注
    function onattentto() {
        $qid = intval($this->post['qid']);
        if (!$qid) {
            exit('error');
        }

        $is_followed = $_ENV['question']->is_followed($qid, $this->user['uid']);
        if ($is_followed) {
            $_ENV['user']->unfollow($qid, $this->user['uid']);
        } else {
            $_ENV['user']->follow_question($qid, $this->user['uid'], $this->user['username']);

            $question = taddslashes($_ENV['question']->get($qid), 1);
            $msgfrom = $this->setting['site_name'] . '管理员';
            $username = taddslashes($this->user['username']);
            $this->load("message");
            $viewurl = url("question/view/$qid", 1);
            //$_ENV['message']->add($msgfrom, 0, $question['authorid'], $username . "刚刚关注了您的问题", '<a target="_blank" href="' . url('user/space/' . $this->user['uid'], 1) . '">' . $username . '</a> 刚刚关注了您的问题' . $question['title'] . '"<br /> <a href="' . $viewurl . '">点击查看</a>');
        }
        exit('ok');
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_list    [获取question列表]
    // @request type         [GET]
    // @param[in]       page [页号，可选]
    // @return          成功 [success ：true]
    //                       [question_num ：论坛帖子总量]
    //                       [question_list ：论坛帖子列表]
    public function onajax_fetch_list() {
        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $res = array();
        $res['success'] = true;
        $res['question_num'] = $_ENV['question']->get_total_num();
        $res['question_list'] = $_ENV['question']->get_list($start, $pagesize);

        echo json_encode($res);
    }

    // @onajax_fetch_list_by_update_time    [按照更新时间获取question列表]
    // @request type                        [GET]
    // @param[in]                   req_num [请求返回的最多条数，默认为10]
    // @param[in]                  req_type [请求的数据类型]
    //                                  req_type=new [返回更新的数据，列表顺序为从旧到新]
    //                                  req_type=old [返回更旧的数据，列表顺序为从新到旧]
    // @param[in]               update_time [请求的时间戳]
    //
    // @return                         成功 [success ：true]
    //                                      [question_list ：论坛帖子列表]
    public function onajax_fetch_list_by_update_time() {
        $req_num = intval($this->post['req_num']);
        ($req_num == 0) && $req_num = 10;
        $req_type = $this->post["req_type"];
        $update_time = intval($this->post['update_time']);

        $res = array();
        $res['success'] = true;
        $res['question_list'] = $_ENV['question']->get_list_by_update_time($update_time,
                                                                           $req_type,
                                                                           $req_num);
        echo json_encode($res);
    }

    // @onajax_fetch_info    [获取单个question详细信息]
    // @request type         [GET]
    // @param[in]        qid [question ID]
    // @return          成功 [success ：true]
    //                       [question_num ：论坛帖子总量]
    //                       [question_list ：论坛帖子列表]
    //
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [无效参数]
    public function onajax_fetch_info() {
        $qid = $this->post['qid'];

        $res = array();
        if (!empty($qid)) {
            $res['success'] = true;
            $res['question'] = $_ENV['question']->get($qid);
        } else {
            $res['success'] = false;
            $res['error'] = 101;
        }
        echo json_encode($res);
    }

    // @onajax_add           [新增帖子]
    // @request type         [POST]
    // @param[in]      title [标题]
    // @param[in]    content [内容]
    // @return          成功 [success ：true]
    //                       [id ：新增帖子ID号]
    //                       [forward ：新增帖子查看链接，可作为成功后的跳转界面]
    //
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [验证码错误]
    // @error            103 [参数无效]
    public function onajax_add() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $title = htmlspecialchars($this->post['title']);
        if (strlen($title) < 5) {
            $res['success'] = false;
            $res['error'] = 103; // 参数无效
            echo json_encode($res);
            return;
        }

        $content = $this->post['content'];
        if ($this->setting['code_ask'] && !$this->checkcode()) {
            $res['success'] = false;
            $res['error'] = 102; // 验证码输入错误
            echo json_encode($res);
            return;
        }

        $qid = $_ENV['question']->add($this->user['uid'], $this->user['username'],
                                      $title, $content);

        $res['success'] = true;
        $res['id'] = $qid;
        $res['forward'] = SITE_URL . "/question/view?qid=$qid";
        echo json_encode($res);
    }

    // @onajax_answer        [新增帖子回复]
    // @request type         [POST]
    // @param[in]        qid [帖子ID号]
    // @param[in]    content [回复内容]
    // @return          成功 [success ：true]
    //                       [aid ：新增回复ID号]
    //                       [forward ：所回复的帖子链接，可作为成功后的跳转界面]
    //
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [提交回答失败,帖子不存在]
    // @error            103 [验证码错误]
    // @error            104 [回复内容不能为空]
    public function onajax_answer() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $qid = $this->post['qid'];
        $question = $_ENV['question']->get($qid);
        if (empty($question)) {
            $res['success'] = false;
            $res['error'] = 102; // 提交回答失败,帖子不存在!
            echo json_encode($res);
            return;
        }

        if ($this->setting['code_reply'] && !$this->checkcode()) {
            $res['success'] = true;
            $res['error'] = 103; // 验证码错误
            echo json_encode($res);
            return;
        }

        $content = $this->post['content'];
        if (empty($content)) {
            $res['success'] = true;
            $res['error'] = 104;
            echo json_encode($res);
            return;
        }

        $aid = $_ENV['answer']->add($this->user['uid'], $this->user['username'],
                                    $qid, $question['title'], $content);
        $_ENV['question']->update_answers($qid);

        $mail_subject = "您的帖子\"" . $question['title'] . "\"有新回复";
        $mail_content = '<p>现在查看<a swaped="true" target="_blank" href="'
                        . SITE_URL . "question/view/" . $question['qid'] 
                        . '">' . $question['title'] . '</a></p>';
        $this->send("", 0, $question['authorid'], $mail_subject, $mail_content, true);

        $res['success'] = true;
        $res['aid'] = $aid;
        $res['forward'] = SITE_URL . "question/view/$qid";
        echo json_encode($res);
    }
}

?>
