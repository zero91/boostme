<?php

!defined('IN_SITE') && exit('Access Denied');

class materialcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('material');
        $this->load('material_category');
        $this->load('material_comment');
        $this->load('material_score');
        $this->load('register_material');
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

        $material_list = $_ENV['material_category']->get_full($region_id, $school_id, $dept_id, $major_id, $start, $pagesize);

        $this->load('easy_access');
        $user_access_list = $_ENV['easy_access']->get_by_uid_target($this->user['uid'], "material");
        foreach ($user_access_list as &$t_user_access) {
            $param = "";
            !empty($t_user_access['region_id']) && $param .= "region_id=" . $t_user_access['region_id'];
            !empty($t_user_access['school_id']) && $param .= "&school_id=" . $t_user_access['school_id'];
            !empty($t_user_access['dept_id']) && $param .= "&dept_id=" . $t_user_access['dept_id'];
            !empty($t_user_access['major_id']) && $param .= "&major_id=" . $t_user_access['major_id'];

            $t_user_access['param'] = $param;
        }
        include template('material');
    }

    // 获取material列表
    public function onfetch_list() {
        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        $page = max(intval($this->post['page']), 1);

        $pagesize = $this->setting['service_page_size'];
        $start = ($page - 1) * $pagesize;

        $material_list = $_ENV['material_category']->get_full($region_id, $school_id, $dept_id, $major_id, $start, $pagesize);

        foreach ($material_list as &$material) {
            $material['format_time'] = tdate($material['time']);
        }
        echo json_encode($material_list);
    }

    public function onview() {
        $mid = $this->get[2];
        if (empty($mid)) {
            $this->message("非法链接，缺少参数!", 'STOP');
        }

        $_ENV['material']->update_view_num($mid);
        $material = $_ENV['material']->get($mid);
        $material['cid_list'] = $_ENV['material_category']->get_by_mid($mid);

        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['service_page_size'];
        $start = ($page - 1) * $pagesize;

        $tot_comment_num = $_ENV['material_comment']->get_comment_num_by_mid($mid);
        $comment_list = $_ENV['material_comment']->get_full_by_mid($mid, $start, $pagesize);
        $user_comment = $_ENV['material_comment']->get_user_comment($this->user['uid'], $mid);

        $departstr = page($tot_comment_num, $pagesize, $page, "material/view/$mid");
        include template('viewmaterial');
    }

    // 用户对资料进行评价
    public function oncomment() {
        if ($this->user['uid'] > 0) {
            $mid = $this->post['mid'];
            $score = $this->post['score'];
            $content = $this->post['content'];

            // 先添加分数，后面计算平均分需要使用到
            $_ENV['material_score']->add($this->user['uid'], $mid, $score);
            $comment_id = $_ENV['material_comment']->add($mid, $content, $this->user['uid'], $this->user['username']);
            if ($comment_id > 0) {
                exit("1");
            }
            exit("-1");
        }
        exit('0');
    }

    public function oncomment_support(){
        if ($this->user['uid'] > 0) {
            $comment_id = $this->get[2];
            $thumbs_type = $this->get[3];

            if (empty($comment_id)) {
                exit('-1');
            }
            $user_support = $_ENV['material_comment']->get_user_support($this->user['uid'], $comment_id);
            if (!empty($user_support)) {
                exit('-2');
            }
            $_ENV['material_comment']->add_support($this->user['uid'], $comment_id, $thumbs_type);
            $comment = $_ENV['material_comment']->get_comment($comment_id);

            if ($thumbs_type == '0') {
                exit("{$comment['up']}");
            } else {
                exit("{$comment['down']}");
            }
        }
        exit('0');
    }

    /*
    function onupdate_data() {
        $category_list = $_ENV['material_category']->get_list();

        foreach ($category_list as $category) {
            $major_id = $category['major_id'];

            $region_id = 'R' . substr($major_id, 1, 2);
            $school_id = 'S' . substr($major_id, 1, 6);
            $dept_id = 'D' . substr($major_id, 1, 9);

            $_ENV['material_category']->update($category['id'], $category['material_id'], $region_id, $school_id, $dept_id, $major_id);
        }

        echo "SUCCEED";
    }
     */

    function oncategorylist() {
        $school_id = $this->get[2];

        include template('material_category_list');
        //include template('material_category_list');
    }

    // 浏览求助
    function oncategory() {
        $cid = $this->get[2]; //接收cid参数

        $category_name = $this->category[$cid]['name'];
        $category_desc = $this->category[$cid]['description'];

        $page = max(1, intval($this->get[3]));
        $pagesize = intval($this->setting['list_index_per_page']);

        $material_num = $_ENV['material_category']->get_cid_material_num($cid);
        $mid_list = $_ENV['material_category']->get_by_cid($cid, 'dept_id');

        $material_list = array();

        foreach ($mid_list as $mid) {
            $material = $_ENV['material']->get($mid);
            $material['cid_list'] = $_ENV['material_category']->get_by_mid($mid);
            $material_list[] = $material;
        }

        $departstr = page($material_num, $pagesize, $page, "material/view/$cid");
        include template('material_category');
    }

    public function onadd() {
        $this->check_login();
        $navtitle = "申请发布资料";
        $op_type = "add";
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $price = doubleval($this->post["price"]);
            $category = $this->post['category'];
            $picture_tmp_url = $this->post['picture_tmp_url'];
            $site_url = $this->post['site_url'];
            $access_code = $this->post['access_code'];

            $this->setting['code_material_add'] && $this->checkcode(); //检查验证码 

            $type = get_image_type(WEB_ROOT . $picture_tmp_url);
            $picture_fname = date("YmdHis") . random(3) . "." . $type;
            $target_path = "/public/data/material/" . $picture_fname;

            if (file_exists(WEB_ROOT . $picture_tmp_url)) {
                image_resize(WEB_ROOT . $picture_tmp_url, WEB_ROOT . $target_path, 375, 525);
            }
            $mid = $_ENV['material']->add($this->user['uid'], $this->user['username'], $target_path, $title, $description, $price, $site_url, $access_code);
            $_ENV['material_category']->multi_add($mid, $category, false);
            $this->jump("material/view/$mid");
        } else {
            /*
            if (0 == $this->user['uid']) {
                $this->message("请先登录!", "user/login");
            }
             */
            include template('addmaterial');
        }
    }

    function onedit() {
        $navtitle = "更改资料";
        $op_type = "edit";

        $mid = $this->get[2];
        if (empty($mid)) {
            $this->message("无效参数!", "STOP");
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $price = doubleval($this->post["price"]);
            $category = $this->post['category'];
            $picture_tmp_url = $this->post['picture_tmp_url'];
            $site_url = $this->post['site_url'];
            $access_code = $this->post['access_code'];

            $this->setting['code_material_add'] && $this->checkcode(); //检查验证码 

            if (!empty($picture_tmp_url)) {
                $picture_fname = end(explode("/", $picture_tmp_url));
                $picture_tmp_path = "/public/data/tmp/" . $picture_fname;
                $target_path = "/public/data/material/" . $picture_fname;

                if (file_exists(WEB_ROOT . $picture_tmp_path)) {
                    // rename(WEB_ROOT . $picture_tmp_path, WEB_ROOT . $target_path);
                    image_resize(WEB_ROOT . $picture_tmp_path, WEB_ROOT . $target_path, 400, 400);
                }
                $_ENV['material']->update_picture($mid, $target_path);
            }

            $affected_rows = $_ENV['material']->update($mid, $title, $description, $price, $site_url, $access_code);

            $_ENV['material_category']->multi_add($mid, $category, false);
            $this->jump("material/view/$mid");
        } else {
            if (0 == $this->user['uid']) {
                $this->message("请先登录!", "user/login");
            }

            $material = $_ENV['material']->get($mid);
            if ($material['uid'] != $this->user['uid']) {
                $this->message("您无权执行此操作!", "STOP");
            }

            $category_list = $_ENV['material_category']->get_by_mid($mid);
            include template('addmaterial');
        }
    }

    function onajaxrm_material_category() {
        $mid = $this->get[2];
        $major_id = $this->get[3];

        if (empty($mid) || empty($major_id)) {
            exit('-1');
        }

        $affected_rows = $_ENV['material_category']->remove_by_mid_majorid($mid, $major_id);

        if ($affected_rows > 0) {
            exit('1');
        }
        exit('-1');
    }

    function onupload_picture() {
        $session_id = $this->post['session_id'];
        $random_num = random(2);

        $output_dir = "/public/data/tmp";
        $extname = extname($_FILES["picture"]["name"]);

        $file_web_path = $output_dir . "/{$session_id}{$random_num}.{$extname}";

        $upload_target_fname = WEB_ROOT . $file_web_path;
        if (file_exists($upload_target_fname)) {
            unlink($upload_target_fname);
        }

        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $upload_target_fname)) {
            echo $file_web_path;
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

    function onreg() {
        if (0 == $this->user['uid']) {
            $this->message("请先登录!", "user/login");
        }

        if (isset($this->post['submit'])) {
            $description = $this->post['description'];

            $id = $_ENV['register_material']->add($description);
            $this->message('您想要的资料我们已经收到，我们会尽快帮您找到这份资料', "BACK");
        }
    }

    function onuser() {
        if (empty($this->get[2])) {
            $this->message("非法提交，缺少参数!", 'BACK');
        }

        $op_type = $this->get[2];

        if ($op_type == 'sold') {
            $material_num = $_ENV['material']->get_user_total_materials($this->user['uid']);

            $page = max(1, intval($this->get[3]));
            $pagesize = $this->setting['list_default'];

            $start = ($page - 1) * $pagesize;
            $departstr = page($material_num, $pagesize, $page, "material/user/sold");
            $material_list = $_ENV['material']->list_by_uid($this->user['uid'], $start, $pagesize);
        } else if ($op_type == 'buy') {
            $mid_list = $_ENV['trade']->get_user_mid_list($this->user['uid']);
            $material_list = $_ENV['material']->get_by_mids($mid_list);
        }

        include template('user_material');
    }
}

?>
