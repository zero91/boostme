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

    // 资料首页
    public function ondefault() {
        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        include template('material');
    }

    // 分类浏览资料
    public function onlist() {
        $type = $this->post['type'];
        empty($type) && $type = "major";
        include template("list_material");
    }

    // 查看资料详细信息
    public function onview() {
        $mid = $this->post['id'];

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
        include template('view_material');
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

    function oncategorylist() {
        $school_id = $this->get[2];
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
            include template('addmaterial');
        }
    }

    public function onedit() {
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

    // 个人空间用户上传资料展示
    public function onuser() {
        $this->check_login();

        $uid = $this->user['uid'];
        $material_num = $_ENV['material']->get_user_total_materials($uid);

        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];

        $start = ($page - 1) * $pagesize;
        $departstr = page($material_num, $pagesize, $page, "material/user");
        $material_list = $_ENV['material']->list_by_uid($uid, $start, $pagesize);
        include template('user_material');
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_list    [获取material列表]
    // @request type         [GET/POST]
    // @param[in]  region_id [区域ID号]
    // @param[in]  school_id [学校ID号]
    // @param[in]    dept_id [院系ID号]
    // @param[in]   major_id [专业ID号]
    // @param[in]       page [页号]
    //
    // @return       success [true]
    //         material_list [material列表]
    public function onajax_fetch_list() {
        $region_id = $this->post['region_id'];
        $school_id = $this->post['school_id'];
        $dept_id = $this->post['dept_id'];
        $major_id = $this->post['major_id'];
        $page = max(intval($this->post['page']), 1);

        $pagesize = $this->setting['service_page_size'];
        $start = ($page - 1) * $pagesize;

        $material_list = $_ENV['material_category']->get_full($region_id,
                                                              $school_id,
                                                              $dept_id,
                                                              $major_id,
                                                              $start,
                                                              $pagesize);
        $arr = array();
        $res['success'] = true;
        $res['material_list'] = $material_list;
        echo json_encode($res);
    }

    // @onajax_fetch_info    [获取material详细信息]
    // @request type         [GET]
    // @param[in]        mid [material ID]
    // @return          成功 [success: true]
    //                       [services: service详细信息]
    //
    //                  失败 [success ：false]
    //                       [error ：为错误码]
    //
    // @error            101 [无效参数，该ID号对应material无效]
    public function onajax_fetch_info() {
        $mid = $this->post['mid'];
        $material = $_ENV['material']->get($mid);

        $res = array();
        if (empty($material)) {
            $res['success'] = false;
            $res['error'] = 101; // 参数无效
        } else {
            $res['success'] = true;
            $res['material'] = $material;
        }
        echo json_encode($res);
    }

    // @onajax_comment       [用户对资料进行评价]
    // @request type         [GET/POST]
    // @param[in]        mid [资料ID号]
    // @param[in]      score [评论分数]
    // @param[in]    content [评论内容]
    // @return         成功 [success为true, id为新增加评论ID号]
    //                 失败 [success为false, error为相应的错误码]
    //
    // @error           101 [用户尚未登录]
    // @error           102 [数据库添加失败]
    public function onajax_comment() {
        $res = array();
        if ($this->user['uid'] > 0) {
            $mid = $this->post['mid'];
            $score = $this->post['score'];
            $content = $this->post['content'];

            // 先添加分数，后面计算平均分需要使用到
            $_ENV['material_score']->add($this->user['uid'], $mid, $score);
            $comment_id = $_ENV['material_comment']->add($mid,
                                                         $content,
                                                         $this->user['uid'],
                                                         $this->user['username']);
            if ($comment_id > 0) {
                $res['success'] = true;
                $res['id'] = $comment_id;
            } else {
                $res['success'] = false;
                $res['error'] = 102; // 数据库添加失败
            }
        } else {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
        }
        echo json_encode($res);
    }

    // @onajax_comment_support [用户对评论进行顶或者踩]
    // @request type           [GET/POST]
    // @param[in]   comment_id [评论ID号]
    // @param[in]  thumbs_type [操作类型，0为顶，1为踩]
    // @param[in]      content [评论内容]
    // @return            成功 [success为true, num为该评论的该操作的总量]
    //                         [例如操作类型为0，表示顶的总量]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    // @error              102 [评论ID号无效]
    // @error              103 [该用户对该评论已经做出操作]
    public function onajax_comment_support() {
        $res = array();
        if ($this->user['uid'] > 0) {
            $comment_id = $this->post['comment_id'];
            $thumbs_type = $this->post['thumbs_type'];

            if (empty($comment_id)) {
                $res['success'] = false;
                $res['error'] = 102; // 评论ID号无效
            } else {
                $user_support = $_ENV['material_comment']->get_user_support($this->user['uid'],
                                                                            $comment_id);
                if (!empty($user_support)) {
                    $res['success'] = false;
                    $res['error'] = 103; // 该用户对该评论已经做出操作
                } else {
                    $_ENV['material_comment']->add_support($this->user['uid'],
                                                           $comment_id,
                                                           $thumbs_type);
                    $comment = $_ENV['material_comment']->get_comment($comment_id);

                    $res['success'] = true;
                    if ($thumbs_type == '0') {
                        $res['num'] = $comment['up'];
                    } else {
                        $res['num'] = $comment['down'];
                    }
                }
            }
        } else {
            $res['success'] = false;
            $res['error'] = 101;  // 用户尚未登录
        }
        echo json_encode($res);
    }

    // @onajax_add                 [添加资料]
    // @request type               [POST]
    // @param[in]            title [资料标题]
    // @param[in]      description [资料描述]
    // @param[in]            price [资料价格]
    // @param[in]         category [资料分类]
    // @param[in]  picture_tmp_url [临时上传的图片地址]
    // @param[in]         site_url [资料链接地址]
    // @param[in]      access_code [资料获取密码]
    // @return            成功 [success为true, forward表示新添加的资料的链接地址]
    //                    失败 [success为false, error为相应的错误码]
    //
    // @error              101 [用户尚未登录]
    // @error              102 [验证码错误]
    public function onajax_add() {
        $res = array();

        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
        } else {
            $title = htmlspecialchars($this->post['title']);
            $description = $this->post['description'];
            $price = doubleval($this->post["price"]);
            $category = $this->post['category'];
            $picture_tmp_url = $this->post['picture_tmp_url'];
            $site_url = $this->post['site_url'];
            $access_code = $this->post['access_code'];

            if ($this->setting['code_material_add'] && !$this->checkcode($this->user['sid'])) {
                $res['success'] = false;
                $res['error'] = 102; // 验证码错误
            } else {
                $type = get_image_type(WEB_ROOT . $picture_tmp_url);
                $picture_fname = date("YmdHis") . random(3) . "." . $type;
                $target_path = "/public/data/material/" . $picture_fname;

                if (file_exists(WEB_ROOT . $picture_tmp_url)) {
                    image_resize(WEB_ROOT . $picture_tmp_url, WEB_ROOT . $target_path, 500, 700);
                }
                $mid = $_ENV['material']->add($this->user['uid'], $this->user['username'],
                                              $target_path, $title, $description, $price,
                                              $site_url, $access_code);
                $_ENV['material_category']->multi_add($mid, $category, false);

                $res['success'] = true;
                $res['forward'] = SITE_URL .  "material/view/$mid";
            }
        }
        echo json_encode($res);
    }

    // @onajax_edit                [编辑资料]
    // @request type               [POST]
    // @param[in]            title [资料标题]
    // @param[in]      description [资料描述]
    // @param[in]            price [资料价格]
    // @param[in]         category [资料分类]
    // @param[in]  picture_tmp_url [临时上传的图片地址]
    // @param[in]         site_url [资料链接地址]
    // @param[in]      access_code [资料获取密码]
    // @return                成功 [success为true, forward表示新添加的资料的链接地址]
    //                        失败 [success为false, error为相应的错误码]
    //
    // @error                  101 [无效参数，未指定资料ID号]
    // @error                  102 [验证码错误]
    // @error                  103 [用户无权操作]
    public function onajax_edit() {
        $res = array();

        $mid = $this->post['mid'];
        if (empty($mid)) {
            $res['success'] = true;
            $res['error'] = 101; // 无效参数，未指定资料ID号
            echo json_encode($res);
            return;
        }

        if ($this->setting['code_material_add'] && !$this->checkcode($this->user['sid'])) {
            $res['success'] = false;
            $res['error'] = 102; // 验证码错误
            echo json_encode($res);
            return;
        }

        $material = $_ENV['material']->get($mid);
        if ($material['uid'] != $this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 103; // 用户无权操作
            echo json_encode($res);
            return;
        }

        $title = htmlspecialchars($this->post['title']);
        $description = $this->post['description'];
        $price = doubleval($this->post["price"]);
        $category = $this->post['category'];
        $picture_tmp_url = $this->post['picture_tmp_url'];
        $site_url = $this->post['site_url'];
        $access_code = $this->post['access_code'];

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

        $affected_rows = $_ENV['material']->update($mid, $title, $description,
                                                   $price, $site_url, $access_code);

        $_ENV['material_category']->multi_add($mid, $category, false);
        $res['success'] = true;
        $res['forward'] = SITE_URL . "material/view/$mid";
        echo json_encode($res);
    }

    // @onajax_user                [获取用户已上传资料列表]
    // @request type               [GET/POST]
    // @param[in]             page [资料页码列表]
    // @return                成功 [success为true, material_list为资料列表]
    //                        失败 [success为false, error为相应的错误码]
    //
    // @error                  101 [用户尚未登录]
    public function onajax_user() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101;
            echo json_encode($res);
            return;
        }

        $uid = $this->user['uid'];
        $material_num = $_ENV['material']->get_user_total_materials($uid);

        $pagesize = $this->setting['list_default'];
        $page = max(1, intval($this->post['page']));
        $start = ($page - 1) * $pagesize;

        $res['success'] = true;
        $res['material_list'] = $_ENV['material']->list_by_uid($uid, $start, $pagesize);
        echo json_encode($res);
    }

    // @onajax_user_material_num   [获取用户已上传资料总数量]
    // @request type               [GET/POST]
    // @return                成功 [success为true, num为该用户上传资料总数]
    //                        失败 [success为false, error为相应的错误码]
    //
    // @error                  101 [用户尚未登录]
    public function onajax_user_material_num() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101;
            echo json_encode($res);
            return;
        }

        $material_num = $_ENV['material']->get_user_total_materials($this->user['uid']);

        $res['success'] = true;
        $res['num'] = $material_num;
        echo json_encode($res);
    }

    public function onajax_build_index() {
        $_ENV['material']->build_index();
        echo "DONE";
    }

    // @onajax_search    [搜索资料]
    //
    // @param[in]  query [查询语句]
    // @param[in]   page [页码，可选]
    //
    // @request type     [GET]
    // @return      成功 [success: true]
    //                   [material_list: 搜索资料结果列表]
    //                   [tot_num : 符合搜索条件项的总数量]
    //                   [departstr: html分页片段]
    public function onajax_search() {
        $query = $this->post['query'];
        empty($query) && $query = "";

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $search_res = $_ENV['material']->search($query, true, $start, $pagesize);

        $res = array();
        $res['success'] = true;
        $res['material_list'] = $search_res["material_list"];
        $res['tot_num'] = $search_res["tot_num"];
        $res['departstr'] = split_page($search_res['tot_num'],
                                       $pagesize, $page,
                                       "query_search('$query', %s)", 1);
        echo json_encode($res);
    }
}

?>
