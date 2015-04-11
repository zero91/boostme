<?php

!defined('IN_SITE') && exit('Access Denied');

class messagecontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load("message");
    }

    // 消息中心首页
    public function ondefault() {
        $navtitle = '消息中心';
        $this->check_login();
        $system_message_list = $_ENV['message']->list_by_touid($this->user['uid'], 0, 1);
        $system_message = $system_message_list[0];

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $personal_message_list = $_ENV['message']->group_by_touid($this->user['uid'],
                                                                  $startindex, $pagesize);

        $personal_usernum = $_ENV['message']->rownum_by_touid($this->user['uid']);
        $departstr = split_page($personal_usernum, $pagesize, $page, "/message/default");
        include template("message");
    }

    // 系统消息
    public function onsystem() {
        $navtitle = '系统消息';
        $this->check_login();

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $message_list = $_ENV['message']->list_by_touid($this->user['uid'],
                                                        $startindex, $pagesize);
        $message_num = $_ENV['message']->get_system_msg_num($this->user['uid']);
        $departstr = split_page($message_num, $pagesize, $page, "/message/system");
        //$_ENV['message']->read_by_fromuid(0);
        include template("view_system_message");
    }

    // 私人消息
    public function onpersonal() {
        $navtitle = '私人消息';
        $this->check_login();

        $fromuid = $this->post['uid'];
        $fromuser = $_ENV['user']->get_by_uid($fromuid);

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $message_list = $_ENV['message']->list_by_fromuid($this->user['uid'], $fromuid,
                                                          $startindex, $pagesize);
        $message_num = $_ENV['message']->tot_num_by_fromuid($this->user['uid'], $fromuid);
        $departstr = split_page($message_num, $pagesize,
                                $page, "/message/personal?uid=" . $fromuid);
        include template("view_personal_message");
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_system  [获取系统消息列表]
    // @request type         [GET]
    // @param[in]       page [页号，可选]
    // @return          成功 [success为true, msg_list为消息列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    public function onajax_fetch_system() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $page = max(intval($this->post['page']), 1);
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $messagelist = $_ENV['message']->list_by_touid($this->user['uid'],
                                                       $start, $pagesize);

        $res['success'] = true;
        $res['msg_list'] = $messagelist;
        echo json_encode($res);
    }

    // @onajax_fetch_personal [获取与某用户的私信列表]
    // @request type          [GET]
    //
    // @param[in]        page [页号]
    // @param[in]         uid [获取对象用户的ID号]
    //
    // @return           成功 [success为true, msg_list为消息列表]
    //                   失败 [success为false, error为相应的错误码]
    //
    // @error             101 [用户尚未登录]
    public function onajax_fetch_personal() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $fromuid = intval($this->post['uid']);
        $messagelist = $_ENV['message']->list_by_fromuid($this->user['uid'], $fromuid,
                                                         $start, $pagesize);

        $res['success'] = true;
        $res['msg_list'] = $messagelist;
        echo json_encode($res);
    }

    // @onajax_userlist      [获取私信用户列表，也即获取私信用户的第一条信息列表]
    // @request type         [GET]
    // @param[in]       page [页号]
    // @return          成功 [success为true, msg_list为消息列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    public function onajax_userlist() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $page = max(1, intval($this->post['page']));
        $pagesize = $this->setting['list_default'];
        $start = ($page - 1) * $pagesize;

        $res['success'] = true;
        $res['msg_list'] = $_ENV['message']->group_by_touid($this->user['uid'],
                                                            $start, $pagesize);
        echo json_encode($res);
    }

    // @onajax_usernum       [获取用户私信用户数量]
    // @request type         [GET/POST]
    // @return          成功 [success为true, msg_num为私信用户数量]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    public function onajax_usernum() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }
        $res['success'] = true;
        $res['msg_num'] = $_ENV['message']->rownum_by_touid($this->user['uid']);
        echo json_encode($res);
    }

    // @onajax_read_msg      [阅读某条消息]
    // @request type         [GET]
    // @param[in]        mid [消息ID号]
    // @return          成功 [success为true, msg_list为消息列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [该条消息接受者非此登陆用户，用户无权操作]
    // @error            103 [该条消息已被阅读]
    public function onajax_read_msg() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $mid = $this->post['mid'];
        $msg = $_ENV['message']->get($mid);
        if ($msg['touid'] == $this->user['uid']) {
            $affected_rows = $_ENV['message']->read_by_mid($mid);
            if ($affected_rows > 0) {
                $res['success'] = true;
            } else {
                $res['success'] = false;
                $res['error'] = 103; // 该条消息已被阅读
            }
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 该条消息接受者非此登陆用户，用户无权操作
        }
        echo json_encode($res);
    }

    // @onajax_send          [给用户发送私信]
    // @request type         [POST]
    // @param[in]   username [接收方用户昵称]
    // @param[in]    content [私信内容]
    // @return          成功 [success为true, id为新增消息ID号]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [接收方用户不存在]
    // @error            103 [不能给自己发消息]
    // @error            104 [消息内容不能为空]
    public function onajax_send() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $touser = $_ENV['user']->get_by_username($this->post['username']);
        if (empty($touser)) {
            $res['success'] = false;
            $res['error'] = 102; // 接收方用户不存在
            echo json_encode($res);
            return;
        }

        if ($touser['uid'] == $this->user['uid']) {
            $res['success'] = false;
            $res['error'] = 103; // 不能给自己发消息
            echo json_encode($res);
            return;
        }

        if (trim($this->post['content']) == '') {
            $res['success'] = false;
            $res['error'] = 104;  // 消息内容不能为空
            echo json_encode($res);
            return;
        }

        $id = $_ENV['message']->add($this->user['username'],
                                    $this->user['uid'],
                                    $touser['uid'],
                                    "", // $this->post['subject'],
                                    $this->post['content']);
        $res['success'] = true;
        $res['id'] = $id;
        echo json_encode($res);
    }

    // @onajax_remove        [删除一条消息]
    // @request type         [POST]
    // @param[in]         id [待删除消息ID号]
    // @return          成功 [success为true]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [消息id无效]
    public function onajax_remove() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $msgid = intval($this->post['id']);
        if ($msgid > 0) {
            // 待加入：删除不属于该用户信息的判断
            $_ENV['message']->remove($this->user['uid'], $msgid);
            $res['success' ] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 消息id无效
        }
        echo json_encode($res);
    }

    // @onajax_remove_dialog [删除与某用户所有对话]
    // @request type         [POST]
    // @param[in]        uid [待删除消息对话的用户id]
    // @return          成功 [success为true]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
    // @error            102 [用户id无效]
    public function onajax_remove_dialog() {
        $res = array();
        if (!$this->check_login(false)) {
            $res['success'] = false;
            $res['error'] = 101; // 用户尚未登录
            echo json_encode($res);
            return;
        }

        $fromuid = array(intval($this->post['uid']));
        if ($fromuid > 0) {
            $_ENV['message']->remove_by_author($this->user['uid'], $fromuid);
            $res['success'] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 用户id无效
        }
        echo json_encode($res);
    }
}

?>
