<?php

!defined('IN_SITE') && exit('Access Denied');

class materialcontrol extends base {

    function materialcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('material');
        $this->load('material_category');
        $this->load('material_comment');
        $this->load('material_score');
    }

    function oncategorylist() {
        include template('material_category_list');
    }

    // 浏览求助
    function oncategory() {
        $cid = $this->get[2]; //接收cid参数

        $category_name = $this->category[$cid]['name'];
        $category_desc = $this->category[$cid]['description'];

        $page = max(1, intval($this->get[3]));
        $pagesize = intval($this->setting['list_index_per_page']);

        $material_num = $_ENV['material_category']->get_cid_material_num($cid);
        $mid_list = $_ENV['material_category']->get_by_cid($cid);

        $material_list = array();
        foreach ($mid_list as $mid) {
            $material = $_ENV['material']->get($mid);
            $material['cid_list'] = $_ENV['material_category']->get_by_mid($mid);
            $material_list[] = $material;
        }

        $departstr = page($material_num, $pagesize, $page, "material/view/$cid");
        include template('material_category');
    }

    function onview() {
        $mid = $this->get[2];

        $material = $_ENV['material']->get($mid);
        $material['cid_list'] = $_ENV['material_category']->get_by_mid($mid);
        include template('viewmaterial');
    }

    function onadd() {
        $navtitle = "申请发布资料";
        if (isset($this->post['submit'])) {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $price = doubleval($this->post["price"]);
            $cid = trim($this->post['category_id']);

            $this->setting['code_material_add'] && $this->checkcode(); //检查验证码 

            /*
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
            */

            $mid = $_ENV['material']->add($this->user['uid'], $this->user['username'], $title, $description, $price);

            $cid_list = array();
            foreach (explode(",", $cid) as $t_cid) {
                if (array_key_exists($t_cid, $this->category)) {
                    $cid_list[] = $t_cid;
                }
            }

            $_ENV['material_category']->multi_add($mid, $cid_list);

            $this->message('我们已经接到您资料的申请，将在第一时间内给您答复', "material/view/$mid");
        } else {
            if (0 == $this->user['uid']) {
                $this->message("请先登录!", "user/login");
            }
            include template('addmaterial');
        }
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
}

?>
