<?php
namespace Home\Controller;
use User\Api\UserApi;

class ServiceController extends HomeController {
    public function index($region = "", $school = "", $dept = "", $major = "", $page = 1) {
        $this->assign("region", $region);
        $this->assign("school", $school);
        $this->assign("dept", $dept);
        $this->assign("major", $major);
        $this->assign("page", $page);
        $this->display();
    }

    public function view($id) {
        $service = D('Service')->field(true)->find($id);
        $this->assign("title", "服务详情 - " . msubstr($service['content'], 0, 8));
        /*
        $_ENV['service']->update_view_num($id);
        $service = $_ENV['service']->get_by_id($id);
        $user_comment = $_ENV['service_comment']->get_by_uid_sid($this->user['uid'], $id);

        $page = max(1, intval($this->get[3]));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;
        $tot_comment_num = $_ENV['service_comment']->get_comment_num_by_sid($service_id);

        $edu_list = $_ENV['education']->get_by_uid($service['uid']);
        $departstr = page($tot_comment_num, $pagesize, $page, "service/view/$service_id");
        */
        $service['avatar'] = get_user_avatar($service['uid']);
        $this->assign("service", $service);
        $this->display();
    }

    public function register() {
        $this->assign("title", "我的服务");
        $this->login();

        $uid = is_login();
        $service_list = D('Service')->field(true)->where(array("uid" => $uid))->select();


        foreach ($service_list as &$t_service) {
            $t_service['category'] = D('ServiceCategory')->field("region,school,dept,major")
                                                         ->where(array(
                                                            "service_id" => $t_service['id']))
                                                         ->select();
        }
        $this->assign("service_list", $service_list);
        // $edu_list = $_ENV['education']->get_by_uid($this->user['uid']);
        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_fetch_list  获取service列表
    // @request  GET
    //
    // @param  string  region_id  区域ID号
    // @param  string  school_id  学校ID号
    // @param  string  dept_id    院系ID号
    // @param  string  major_id   专业ID号
    // @param  string  page       页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => service列表)
    //
    public function ajax_fetch_list($region = "",
                                    $school = "",
                                    $dept = "",
                                    $major = "",
                                    $page = 1) {
        $service_list = D('Service')->lists($region, $school, $dept, $major, $page);
        foreach ($service_list as &$service) {
            $service['avatar'] = get_user_avatar($service['uid']);
            $service['format_create_time'] = format_date($service['create_time']);
        }
        $this->ajaxReturn(array("success" => true, "list" => $service_list));
    }

    // @brief  ajax_fetch_info  获取单个service详细信息
    // @request  GET
    // @param  integer  id  service的ID号
    //
    // @ajaxReturn  成功 - array("success" => true,
    //                           "info" => service详细信息,
    //                           "category" => 分类信息)
    //
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  无效参数或指定的service不存在
    //
    public function ajax_fetch_info($id) {
        $service_info = D('Service')->field(true)->find($id);
        if (is_array($service_info)) {
            $res = array();
            $res['success'] = true;
            $res['info'] = $service_info;
            $res['category'] = D('ServiceCategory')->field("region, school, dept, major")
                                                   ->where(array("service_id" => $id))
                                                   ->select();
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }

    // @brief  ajax_fetch_category  获取service详细分类
    // @request  GET
    // @param  integer service_id  service的ID号
    //
    // @ajaxReturn  成功 - array("success" => true, "category" => 分类列表)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    public function ajax_fetch_category($service_id) {
        $category = D('ServiceCategory')->field("region, school, dept, major")
                                        ->where(array("service_id" => $service_id))
                                        ->select();
        $this->ajaxReturn(array("success"=>true, "category" => $category));
    }

    // @brief  ajax_fetch_comment  获取service的评论信息
    // @request  GET
    //
    // @param  integer  service_id  service的ID号
    // @param  integer  page        页码
    //
    // @ajaxReturn  成功 - array("success" => true, "tot" => 总评论条数, "list" => 评论列表)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  无效参数
    //
    public function ajax_fetch_comment($service_id, $page = 1) {
        $num_per_page = C('SERVICE_COMMENT_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $comment_list = D('ServiceComment')->field(true)
                                           ->where(array("service_id" => $service_id))
                                           ->order("update_time DESC")
                                           ->limit($start, $num_per_page)
                                           ->select();
        if (is_array($comment_list)) {
            $res = array();
            $res['success'] = true;
            $res['list'] = $comment_list;
            $res['tot'] = D('ServiceComment')->where(array("service_id" => $service_id))->count();
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 无效参数
        }
    }

    // @brief  ajax_fetch_user_comment  获取userid对service的评论
    // @request  GET
    // @param  integer  service_id  服务ID号
    //
    // @ajaxReturn  成功 - array("success" => true, "score" => 评分, "comment" => 评论内容)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  用户尚未对该服务评分
    //
    public function ajax_fetch_user_comment($service_id) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $comment = D('ServiceComment')->field('score, content')
                                      ->where(array("uid" => $uid, "service_id" => $service_id))
                                      ->find();
        if (is_array($comment)) {
            $res = array();
            $res["success"] = true;
            $res["score"] = $comment['score'];
            $res["content"] = $comment['content'];
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 用户尚未对该服务评分
        }
    }

    // @brief  ajax_comment  对服务进行评论
    // @request  POST
    //
    // @param  integer  $id       服务ID号
    // @param  string   $content  评论内容
    // @param  integer  $score    评分
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增评论ID号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 评论分数不合法
    // @error  103 - 评论内容长度不合法
    // @error  104 - 用户已评论
    // @error  105 - 用户未购买该服务，无权评论
    //
    public function ajax_comment($id, $content, $score) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $id = D('ServiceComment')->comment($id, $uid, $content, $score);
        if ($id > 0) {
            $this->ajaxReturn(array("success" => true, "id" => $id));
        } else {
            $res = array("success" => false);
            switch ($id) {
                case -1: $res['error'] = 102; break; // 评论分数不合法
                case -2: $res['error'] = 103; break; // 评论内容长度不合法
                case -5: $res['error'] = 104; break; // 用户已评论
                case -6: $res['error'] = 105; break; // 用户未购买该服务，无权评论
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_add  新增service
    // @request  POST
    //
    // @param  string   $content     服务具体内容
    // @param  integer  $duration    服务时长
    // @param  double   $price       服务价格
    // @param  array    $category    服务分类
    // @param  boolean  $apply       是否申请提供服务
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增服务ID号)
    //              失败 - array("success" => false, "error" => 错误码)
    // [[特例]]：当error=102时，保存成功，但由于没有手机号，申请失败。但会返回保存服务ID号
    //
    // @error  101  用户尚未登录
    // @error  102  保存成功，但由于没有手机号，申请提供服务操作失败。会多返回保存服务ID号
    // @error  103  内容长度不合法
    // @error  104  补充信息长度不合法
    // @error  105  价格不合法
    // @error  106  时长不合法
    //
    public function ajax_add($content, $duration, $price, $category, $apply = false) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $User = new UserApi();
        $user_info = $User->info($uid);

        $status = 0;
        if (isset($user_info['mobile']) && $apply) { // TODO 手机号有效性验证
            $status = SERVICE_STATUS_APPLY;
        }
        if (empty($category)) {
            $category = array();
        }
        $id = D('Service')->provide($uid,
                                    $content,
                                    $duration,
                                    $price,
                                    $category,
                                    $supplement,
                                    $status);

        if ($id > 0) {
            if (!isset($user_info['mobile']) && $apply) {
                // 102: 保存成功，但由于没有手机号，申请提供服务操作失败
                $this->ajaxReturn(array("success" => false, "error" => 102, "id" => $id));
            } else {
                $this->ajaxReturn(array("success" => true, "id" => $id));
            }
        } else {
            $res = array("success" => false);
            switch ($id) {
                case -1: $res['error'] = 103; break; // 内容长度不合法
                case -2: $res['error'] = 104; break; // 补充信息长度不合法
                case -3: $res['error'] = 105; break; // 价格不合法
                case -4: $res['error'] = 106; break; // 时长不合法
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_update  更新服务
    // @request  POST
    //
    // @param  integer  $id          服务ID号
    // @param  string   $content     服务具体内容
    // @param  integer  $duration    服务时长
    // @param  double   $price       服务价格
    // @param  array    $category    服务分类
    // @param  boolean  $apply       是否申请提供服务
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增服务ID号)
    //              失败 - array("success" => false, "error" => 错误码)
    // [[特例]]：当error=102时，保存成功，但由于没有手机号，申请失败。但会返回保存服务ID号
    //
    // @error  101  用户尚未登录
    // @error  102  保存成功，但由于没有手机号，申请提供服务操作失败。会多返回保存服务ID号
    // @error  103  内容长度不合法
    // @error  104  补充信息长度不合法
    // @error  105  价格不合法
    // @error  106  时长不合法
    // @error  107  服务ID号无效
    // @error  108  无权操作
    //
    public function ajax_update($id, $content, $duration, $price, $category, $apply = false) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $User = new UserApi();
        $user_info = $User->info($uid);

        $status = 0;
        if (isset($user_info['mobile']) && $apply) { // TODO 手机号有效性验证
            $status = SERVICE_STATUS_APPLY;
        }
        empty($category) && $category = array();
        $id = D('Service')->update($uid,
                                   $id,
                                   $content,
                                   $duration,
                                   $price,
                                   $category,
                                   $supplement,
                                   $status);

        if ($id > 0) {
            if (!isset($user_info['mobile']) && $apply) {
                // 102: 保存成功，但由于没有手机号，申请提供服务操作失败
                $this->ajaxReturn(array("success" => false, "error" => 102, "id" => $id));
            } else {
                $this->ajaxReturn(array("success" => true, "id" => $id));
            }
        } else {
            $res = array("success" => false);
            switch ($id) {
                case -1: $res['error'] = 103; break; // 内容长度不合法
                case -2: $res['error'] = 104; break; // 补充信息长度不合法
                case -3: $res['error'] = 105; break; // 价格不合法
                case -4: $res['error'] = 106; break; // 时长不合法
                case -8: $res['error'] = 107; break; // 服务ID号无效
                case -9: $res['error'] = 108; break; // 无权操作
                default: break;
            }
            $this->ajaxReturn($res);
        }
    }

    // @brief  ajax_update_status   开启/关闭服务
    // @request  POST
    // @param  id  服务ID号
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101 - 用户尚未登录
    // @error  102 - 服务ID号无效
    // @error  103 - 用户无权操作
    // @error  104 - 无效操作
    //
    public function ajax_update_status($id) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $service = D('Service')->field(true)->find($id);
        if (!is_array($service)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 服务ID号无效
        }

        if ($service['uid'] != $uid) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 用户无权操作
        }

        $cnt = 0;
        if ($service['status'] == SERVICE_STATUS_ACCEPTED) {
            $cnt = D('Service')->save(array(
                                    "id" => $service['id'],
                                    "status" => SERVICE_STATUS_CLOSED)
            );
            $this->ajaxReturn(array("success" => true));
        } else if ($service['status'] == SERVICE_STATUS_CLOSED) {
            $cnt = D('Service')->save(array(
                                    "id" => $service['id'],
                                    "status" => SERVICE_STATUS_ACCEPTED)
            );
            $this->ajaxReturn(array("success" => true));
        } else {
            $this->ajaxReturn(array("success" => true, "error" => 104)); // 无效操作
        }
    }
}
