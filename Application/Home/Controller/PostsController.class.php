<?php
namespace Home\Controller;

class PostsController extends HomeController {
    public function index($page = 1){
        $num_per_page = C('POSTS_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;
        /*
        $user_num = $_ENV['user']->rownum_alluser();
        $question_num = $_ENV['question']->get_total_num();
        $questionlist = $_ENV['question']->get_list($startindex, $pagesize);
        $departstr = split_page($question_num, $pagesize, $page, "/question/default&page=%s");
        */
        $this->display();
    }

    public function view($id, $page = 1) {
        $post_info = D('Posts')->field(true)->find($id);

        $num_per_page = C('POST_ANSWER_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;
        $answer_list = D('PostAnswer')->field(true)
                                      ->where(array("pid" => $id))
                                      ->limit($start, $num_per_page)
                                      ->select();

        $this->assign("answerlist", $answer_list);
        //$rownum = $this->db->fetch_total("answer", " qid=$qid ");
        //$departstr = split_page($rownum, $pagesize, $page, "/question/view?qid=" . $qid . "&page=%s");
        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_fetch_list  获取question列表
    // @request  GET
    // @param  integer  page  页号
    //
    // @ajaxReturn  成功 => array("success" => true, "tot" => 帖子总量, "list" => 帖子列表)
    //
    public function ajax_fetch_list($page = 1) {
        $num_per_page = C('POSTS_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $res = array();
        $res['success'] = true;
        $res['list'] = D('Posts')->field(true)
                                 ->order("update_time DESC")
                                 ->limit($start, $num_per_page)
                                 ->select();
        $res['tot'] = D('Posts')->count();
        $this->ajaxReturn($res);
    }

    // @brief  ajax_fetch_list_by_time  按照更新时间获取帖子列表
    // @request  GET
    //
    // @param  integer  $time     基准时间
    // @param  integer  $req_num  请求返回的最多条数，默认返回一页数量
    // @param  integer  $new      new=1表示取较基准时间更新的帖子，new=0则相反
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 帖子列表)
    //
    public function ajax_fetch_list_by_time($time, $req_num = '', $new = 1) {
        empty($req_num) && $req_num = C('POSTS_NUM_PER_PAGE');
        if ($new) {
            $post_list = D('Posts')->field(true)
                                   ->where(array("update_time" => array('gt', $time)))
                                   ->order("update_time ASC")
                                   ->limit(0, $req_num)
                                   ->select();
            $post_list = array_reverse($post_list);
        } else {
            $post_list = D('Posts')->field(true)
                                   ->where(array("update_time" => array('lt', $time)))
                                   ->order("update_time DESC")
                                   ->limit(0, $req_num)
                                   ->select();
        }
        $this->ajaxReturn(array("success" => true, "list" => $post_list));
    }

    // @brief  ajax_fetch_info  获取单个帖子的详细信息
    // @request  GET
    // @param  integer  id  帖子ID号
    //
    // @ajaxReturn  成功 - array("success" => true, "info" => 帖子详细信息)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  无效参数
    //
    public function ajax_fetch_info($id) {
        $post_info = D('Posts')->field(true)->find($id);

        if (is_array($post_info)) {
            $this->ajaxReturn(array("success" => true, "info" => $post_info));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }

    // @brief  ajax_add  新增帖子
    // @request  POST
    //
    // @param  string  title    标题
    // @param  string  content  内容
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增帖子ID号, "forward" => 跳转页面)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  验证码错误
    // @error  103  标题长度不在5,64之间
    // @error  104  内容长度不在0,2048之间
    //
    public function ajax_add($title, $content, $verify = "") {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if (!check_verify($verify)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 验证码错误
        }

        $id = D('Posts')->post($uid, $title, $content);
        if ($id > 0) {
            $forward = U("Posts/view?id=$id"); // TODO 确保跳转URL正确
            $this->ajaxReturn(array("success" => true, "id" => $id, "forward" => $forward));
        } else {
            $res = array("success" => false);
            switch ($id) {
                case -1: $res['error'] = 103; break; // 标题长度不在5,64之间
                case -2: $res['error'] = 104; break; // 内容长度不在0,2048之间
                default: break;
            }
            $this->ajaxReturn(res);
        }
    }

    // @brief  ajax_answer  新增帖子回复
    // @request  POST
    //
    // @param  integer  pid     帖子ID号
    // @param  string   content 回复内容
    // @param  string   verify  验证码
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增帖子回复ID号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  提交回答失败,帖子不存在
    // @error  103  验证码错误]
    // @error  104  回复内容长度不在5,2048之间
    //
    public function ajax_answer($pid, $content, $verify = "") {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if (!check_verify($verify)) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 验证码错误
        }

        $post_info = D('Posts')->field("title")->find($pid);

        if (is_array($post_info)) {
            $id = D('PostAnswer')->answer($uid, $content, $pid, $post_info['title']);

            if ($id > 0) {
                // TODO 发邮件通知相关用户
                D('Posts')->where(array("id" => $pid))->setInc('answers');
                $this->ajaxReturn(array("success" => true, "id" => $id));
            } else {
                $res = array("success" => false);
                switch ($id) {
                    case -1: $res['error'] = 104; break; // 回复内容长度不在5,2048之间
                    default: break;
                }
                $this->ajaxReturn($res);
            }
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 指定帖子不存在
        }
    }

    // @brief  ajax_fetch_answer_list  获取回复列表
    // @request  GET
    //
    // @param  integer  pid   帖子ID号
    // @param  integer  page  页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 回复列表)
    //
    public function ajax_fetch_answer_list($pid, $page = 1) {
        $num_per_page = C('POST_ANSWER_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $answer_list = D('PostAnswer')->field(true)
                                      ->where(array("pid" => $pid))
                                      ->order("create_time ASC")
                                      ->limit($start, $num_per_page)
                                      ->select();
        $this->ajaxReturn(array("success" => true, "list" => $answer_list));
    }

    // @brief  ajax_fetch_answer_info  获取单个回复详细信息
    // @request  GET
    // @param  integer  id  回复的ID号
    //
    // @ajaxReturn  成功 => array("success" => true, "info" => 回复详细信息)
    //              失败 => array("success" => false, "error" => 错误码)
    //
    // @error  101  回复ID参数无效
    //
    public function ajax_fetch_answer_info($id) {
        $answer = D('PostAnswer')->field(true)->find($id);
        if (is_array($answer)) {
            $this->ajaxReturn(array("success" => true, "info" => $answer));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }

    // @brief  ajax_comment  添加回复的评论
    // @request  POST
    //
    // @param  integer  answer_id  回复ID号
    // @param  string   content    评论内容
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 评论ID编号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  answer不存在
    // @error  103  comment内容长度不在1，512范围内
    //
    public function ajax_comment($answer_id, $content) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => true, "error" => 101)); // 用户尚未登录
        }

        $answer = D('PostAnswer')->field(true)->where(array("id" => $answer_id))->find();
        if (!is_array($answer)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // answer不存在
        }

        $id = D('AnswerComment')->comment($uid, $answer_id, $content);
        if ($id > 0) {
            D('PostAnswer')->where(array("id" => $answer_id))->setInc('comments');
            D('Posts')->where(array("id" => $answer['pid']))->setInc('answers');
            // TODO 发邮件通知相关用户
            $this->ajaxReturn(array("success" => true, "id" => $id));
        } else {
            $res = array("success" => false);
            switch ($id) {
                case -1: $res['error'] = 101; break; // 内容长度不在1，512内
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_fetch_comment_list  获取单个回复的评论列表
    // @request  GET
    //
    // @param  integer  answer_id  回复的ID编号
    // @param  integer  page       页码
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 回复的评论列表)
    //
    public function ajax_fetch_comment_list($answer_id, $page = 1) {
        $num_per_page = C('ANSWER_COMMENT_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $comment_list = D('AnswerComment')->field(true)
                                          ->order("create_time ASC")
                                          ->limit($start, $num_per_page)
                                          ->where(array("aid" => $answer_id))
                                          ->select();
        $this->ajaxReturn(array("success" => true, "list" => $comment_list));
    }

    // @brief  ajax_save  收藏/取消收藏指定帖子
    // @request  POST
    // @param  integer  pid  帖子ID号
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  操作失败
    //
    public function ajax_save($pid) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if (D('PostCollect')->collect($uid, $pid)) {
            $this->ajaxReturn(array("success" => true));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 操作失败
        }
    }
}
