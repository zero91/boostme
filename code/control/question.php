<?php

!defined('IN_SITE') && exit('Access Denied');

//0、未审核 1、待解决、2、已解决 4、悬赏的 9、 已关闭问题
class questioncontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load("question");
        $this->load("answer");
    }

    // 提交问题
    public function onadd() {
        $this->check_login();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = htmlspecialchars($this->post['title']);
            $content = $this->post['content'];
            $askfromuid = $this->post['askfromuid'];
            $this->setting['code_ask'] && $this->checkcode(); //检查验证码
            $offerscore = $price;
            //检查审核和内容外部URL过滤
            $status = intval(1 != (1 & $this->setting['verify_question']));
            $allow = $this->setting['allow_outer'];
            if (3 != $allow && has_outer($content)) {
                0 == $allow && $this->message("内容包含外部链接，发布失败!", 'BACK');
                1 == $allow && $status = 0;
                2 == $allow && $content = filter_outer($content);
            }
            //检查标题违禁词
            $contentarray = checkwords($title);
            1 == $contentarray[0] && $status = 0;
            2 == $contentarray[0] && $this->message("问题包含非法关键词，发布失败!", 'BACK');
            $title = $contentarray[1];

            //检查问题描述违禁词
            $descarray = checkwords($content);
            1 == $descarray[0] && $status = 0;
            2 == $descarray[0] && $this->message("问题描述包含非法关键词，发布失败!", 'BACK');
            $content = $descarray[1];

            // 检查提问数是否超过组设置
            ($this->user['questionlimits'] && ($_ENV['userlog']->rownum_by_time('ask') >= $this->user['questionlimits'])) &&
                    $this->message("你已超过每小时最大提问数" . $this->user['questionlimits'] . ',请稍后再试！', 'BACK');

            $qid = $_ENV['question']->add($this->user['uid'], $this->user['username'], $title, $content, $status);

            if (0 == $status) {
                $this->message('问题发布成功！为了确保问答的质量', 'BACK');
            } else {
                $this->jump("question/view/$qid");
            }
        }
    }

    public function onview() {
        $this->setting['stopcopy_on'] && $_ENV['question']->stopcopy(); //是否开启了防采集功能
        $qid = $this->get[2]; //接收qid参数
        $question = $_ENV['question']->get($qid);
        empty($question) && $this->message('问题已经被删除！');
        ($question['status'] == 2) && $this->message('帖子为通过审核，您看看其他的帖子吧！');

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
        @$page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $rownum = $this->db->fetch_total("answer", " qid=$qid ");
        $answerlist = $_ENV['answer']->list_by_qid($qid, $startindex, $pagesize);
        $departstr = page($rownum, $pagesize, $page, "question/view/$qid/" . $this->get[3]);
        $navtitle = $question['title'];
        include template("question");
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

    /* 追加悬赏 */

    function onaddscore() {
        $qid = intval($this->post['qid']);
        $score = intval($this->post['score']);
        if ($this->user['credit2'] < $score) {
            $this->message("财富值不足!", 'BACK');
        }
        $_ENV['question']->update_score($qid, $score);
        $this->credit($this->user['uid'], 0, -$score, 0, 'offer');
        $viewurl = urlmap('question/view/' . $qid, 2);
        $this->message('追加悬赏成功！', $viewurl);
    }

    /* 修改回答 */

    function oneditanswer() {
        $navtitle = '修改回答';
        $aid = $this->get[2] ? $this->get[2] : $this->post['aid'];
        $answer = $_ENV['answer']->get($aid);
        (!$answer) && $this->message("回答不存在或已被删除！", "STOP");
        $question = $_ENV['question']->get($answer['qid']);
        $navlist = $_ENV['category']->get_navigation($question['cid'], true);
        if (isset($this->post['submit'])) {
            $content = $this->post['content'];
            $viewurl = urlmap('question/view/' . $question['id'], 2);

            //检查审核和内容外部URL过滤
            $status = intval(2 != (2 & $this->setting['verify_question']));
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

    /* 追问模块---追问 */

    function onappendanswer() {
        $this->load("message");
        $qid = intval($this->get[2]) ? $this->get[2] : intval($this->post['qid']);
        $aid = intval($this->get[3]) ? $this->get[3] : intval($this->post['aid']);
        $type = intval($this->get[4]) ? $this->get[4] : intval($this->post['type']);
        $question = $_ENV['question']->get($qid);
        $answer = $_ENV['answer']->get($aid);
        if (isset($this->post['submit'])) {
            $_ENV['answer']->add_tag($aid, $this->post['content'], $answer['tag']);
            $_ENV['message']->add($question['author'], $question['authorid'], $answer['authorid'], '问题追问:' . $question['title'], $question['description'] . '<br /> <a href="' . url('question/view/' . $qid, 1) . '">点击查看问题</a>');
            $viewurl = urlmap('question/view/' . $qid, 2);
            isset($type) ? $this->message('继续回答成功!', $viewurl) : $this->message('继续提问成功!', $viewurl);
        }
        include template("appendanswer");
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

    //移动分类
    function onmovecategory() {
        if (intval($this->post['category'])) {
            $cid = intval($this->post['category']);
            $cid1 = 0;
            $cid2 = 0;
            $cid3 = 0;
            $qid = $this->post['qid'];
            $viewurl = urlmap('question/view/' . $qid, 2);
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
            $_ENV['question']->update_category($qid, $cid, $cid1, $cid2, $cid3);
            $this->message('问题分类修改成功!', $viewurl);
        }
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
}

?>
