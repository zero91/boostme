<?php
namespace Home\Controller;

class MessageController extends HomeController {
    public function index($page = 1) {
        $this->assign("title", "消息中心");
        $this->login();

        $uid = is_login();
        $num_per_page = C('MESSAGE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $latest_message_list = D('LatestMessage')->field(true)
                                                 ->where(array("uid" => $uid))
                                                 ->order("update_time DESC")
                                                 ->limit($start, $num_per_page)
                                                 ->select();
        $this->assign("latest_message_list", $latest_message_list);
        // TODO 分页显示
        $this->display();
    }

    public function system($page = 1) {
        $this->assign("title", "系统消息");
        $this->login();

        $uid = is_login();
        $message_list = D('Message')->system($uid, $page);
        $this->assign("message_list", $message_list);
        // TODO 分页显示
        // TODO 阅读消息
        $this->display();
    }

    public function personal($uid, $page = 1) {
        $this->assign("title", '私人消息');
        $this->login();

        $login_uid = is_login();
        $message_list = D('Message')->dialog($login_uid, $uid, $page);

        $this->assign("message_list", $message_list);
        $this->assign("to_uid", $uid);
        $this->assign("login_uid", $login_uid);

        $this->display();
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @brief  ajax_fetch_system  获取系统消息列表
    // @request  GET
    // @param  integer  $page  页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 消息列表)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_fetch_system($page=1) {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }

        $message_list = D('Message')->system($uid, $page);
        $this->ajaxReturn(array("success" => true, "list" => $message_list));
    }

    // @brief  ajax_fetch_personal  获取与某用户的私信列表
    // @request  GET
    //
    // @param  integer  $uid   获取对象的用户ID号
    // @param  integer  $page  页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 消息列表)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  不能获取与用户自己的对话
    //
    public function ajax_fetch_personal($uid, $page = 1) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        if ($uid == $login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 不能获取与自己的对话
        }

        $message_list = D('Message')->dialog($login_uid, $uid, $page);
        $this->ajaxReturn(array("success" => true, "list" => $message_list));
    }

    // @brief    ajax_fetch_latest  获取私信用户列表，也即获取私信用户的第一条信息列表
    // @request  GET
    // @param    integer  page  页号
    //
    // @ajaxReturn  成功 - array("success" => true, "list" => 消息列表)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_fetch_latest($page=1) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }

        $num_per_page = C('MESSAGE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $message_list = D('LatestMessage')->field(true)
                                          ->order("update_time DESC")
                                          ->limit($start, $num_per_page)
                                          ->select();

        $this->ajaxReturn(array("success" => true, "list" => $message_list));
    }

    // @brief  ajax_fetch_latest_num  获取用户私信用户数量
    // @request  GET
    // @ajaxReturn  成功 - array("success" => true, "num" => 私信用户数量)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_fetch_latest_num() {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }
        $this->ajaxReturn(array("success" => true, "num" => D('LatestMessage')->count()));
    }

    // @brief  ajax_read  阅读某条消息
    // @request  GET
    // @param  mid  消息ID号
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  该条消息接受者非此登陆用户，用户无权操作
    // @error  103  该条消息已被阅读
    // @error  104  消息ID号无效
    // @error  105  更新失败
    //
    public function ajax_read($id) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $message = D('Message')->find($id);
        if (is_array($message)) {
            if ($message["to_uid"] == $login_uid) {
                if ($message['new'] == 0) {
                    // 该条消息已被阅读
                    $this->ajaxReturn(array("success" => false, "error" => 105));
                }

                $res = D('Message')->where(array("id" => $id))->setField('new', 0);
                if ($res > 0) {
                    D('LatestMessage')->where(array(
                            "uid"    => $message["to_uid"],
                            "to_uid" => $message["from_uid"]
                    ))->setDec("new_num");
                    $this->ajaxReturn(array("success" => true));
                } else {
                    $this->ajaxReturn(array("success" => false, "error" => 105)); // 更新失败
                }
            } else {
                // 该条消息接受者非此登陆用户，用户无权操作
                $this->ajaxReturn(array("success" => false, "error" => 102));
            }
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 104)); // 无效ID号
        }
    }

    // @brief  ajax_send  给用户发送私信
    // @request  POST
    //
    // @param  string  $uid       接收方用户ID号
    // @param  string  $content   私信内容
    //
    // @ajaxReturn  成功 - array("success" => true, "id" => 新增消息ID号)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  接收方用户不存在
    // @error  103  不能给自己发消息
    // @error  104  消息内容不能为空
    // @error  105  数据库操作失败
    //
    public function ajax_send($uid, $content) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $to_user = M('User')->field(true)->find($uid);
        if (!is_array($to_user)) {
            $this->ajaxReturn(array("success" => false, "error" => 102)); // 接收方用户不存在
        }

        if ($to_user['uid'] == $login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 103)); // 不能给自己发消息
        }

        if (trim($content) == '') {
            $this->ajaxReturn(array("success" => false, "error" => 104)); // 消息内容不能为空
        }

        $id = D('Message')->send($login_uid,
                                 get_nickname($login_uid),
                                 $to_user['uid'],
                                 $to_user['nickname'],
                                 $content);

        if ($id > 0) {
            D('LatestMessage')->update($login_uid, $to_user['uid']);
            D('LatestMessage')->update($to_user['uid'], $login_uid);
            $this->ajaxReturn(array("success" => true));
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 105)); // 数据库操作失败
        }
    }

    // @brief  ajax_remove  删除一条消息
    // @request  POST
    // @param  integer  $id  待删除消息ID号
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  消息ID号无效
    // @error  103  已删除
    // @error  104  无权操作
    // @error  105  更新失败
    //
    public function ajax_remove($id) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        $res = D('Message')->remove($id, $login_uid);
        if ($res == $id) {
            $message = D('Message')->find($id);
            if (is_array($message)) {
                if ($login_uid == $message['from_uid']) {
                    D('LatestMessage')->update($login_uid, $message['to_uid']);
                } else if ($login_uid == $message['to_uid']) {
                    D('LatestMessage')->update($login_uid, $message['from_uid']);
                }
            }
            $this->ajaxReturn(array("success" => true));
        } else {
            $error = -1;
            switch($res) {
                case -1: $error = 103; break; // 已删除
                case -2: $error = 104; break; // 无权操作
                case -3: $error = 105; break; // 更新失败
                case -4: $error = 102; break; // 消息ID无效
                default: break;
            }
            $this->ajaxReturn(array("success" => false, "error" => $error)); // 用户尚未登录
        }
    }

    // @brief  ajax_remove_dialog  删除与某用户所有对话
    // @request  POST
    // @param  integer  uid  待删除消息对话的用户ID
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    //
    public function ajax_remove_dialog($uid) {
        $login_uid = is_login();
        if (!$login_uid) {
            $this->ajaxReturn(array("success" => false, "error" => 101)); // 用户尚未登录
        }

        D('Message')->remove_dialog($login_uid, $uid);
        D('LatestMessage')->remove_dialog($login_uid, $uid);
        $this->ajaxReturn(array("success" => true));
    }
}
