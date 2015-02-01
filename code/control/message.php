<?php

!defined('IN_SITE') && exit('Access Denied');

class messagecontrol extends base {

    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('user');
        $this->load("message");
    }

    // 私人消息
    public function onpersonal() {
        $navtitle = '私人消息';
        $type = 'personal';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $messagelist = $_ENV['message']->group_by_touid($this->user['uid'], $startindex, $pagesize);
        $messagenum = $_ENV['message']->rownum_by_touid($this->user['uid']);
        $departstr = page($messagenum, $pagesize, $page, "message/personal");
        include template("message");
    }

    // 系统消息
    public function onsystem() {
        $this->check_login();

        $navtitle = '通知';
        $type = 'system';
        $page = max(1, intval($this->get[2]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $messagelist = $_ENV['message']->list_by_touid($this->user['uid'], $startindex, $pagesize);
        $messagenum = $this->db->fetch_total('message', 'touid=' . $this->user['uid'] . ' AND fromuid=0 AND status<>'. MSG_STATUS_TO_DELETED);

        $departstr = page($messagenum, $pagesize, $page, "message/system");

        $_ENV['message']->read_by_fromuid(0);
        include template("message");
    }

    // 阅读消息
    public function onread() {
        $mid = $this->get[2];
        if (empty($mid)) {
            exit('-1');
        }
        $affected_rows = $_ENV['message']->read_by_mid($mid);
        exit("$affected_rows");
    }

    // 发消息
    function onsend() {
        $navtitle = '发站内消息';
        if (isset($this->post['submit'])) {
            $touser = $_ENV['user']->get_by_username($this->post['username']);
            (!$touser) && $this->message('该用户不存在!', "message/send");
            ($touser['uid'] == $this->user['uid']) && $this->message("不能给自己发消息!", "message/send");
            (trim($this->post['content']) == '') && $this->message("消息内容不能为空!", "message/send");
            $_ENV['message']->add($this->user['username'], $this->user['uid'], $touser['uid'], $this->post['subject'], $this->post['content']);
            $this->message('消息发送成功!', get_url_source());
        }
        include template('sendmsg');
    }

    // 查看消息
    public function onview() {
        $navtitle = "查看消息";
        $type = ($this->get[2] == 'personal') ? 'personal' : 'system';
        $fromuid = intval($this->get[3]);
        $page = max(1, intval($this->get[4]));
        $pagesize = $this->setting['list_default'];
        $startindex = ($page - 1) * $pagesize;
        $_ENV['message']->read_by_fromuid($fromuid);
        $fromuser = $_ENV['user']->get_by_uid($fromuid);
        $status = 1;
        $messagelist = $_ENV['message']->list_by_fromuid($fromuid, $startindex, $pagesize);
        $messagenum = $this->db->fetch_total('message', "fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->user['uid'] . ") AND status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_FROM_DELETED . ")) OR ((fromuid=" . $this->user['uid'] . " AND touid=" . $fromuid . ") AND status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_TO_DELETED . "))");
        $departstr = page($messagenum, $pagesize, $page, "message/view/$type/$fromuid");
        include template('viewmessage');
    }

    // 删除消息
    public function onremove() {
        $msgid = intval($this->get[2]);
        if ($msgid > 0) {
            $_ENV['message']->remove($this->user['uid'], $msgid);
            exit('1');
        }
        exit('-1');
    }

    // ajax删除对话
    function onremovedialog() {
        $fromuid = array(intval($this->get[2]));

        if ($fromuid > 0) {
            $affected_rows = $_ENV['message']->remove_by_author($fromuid);
            exit("$affected_rows");
        }
        exit('-1');
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_system  [获取系统消息列表]
    // @request type         [GET/POST]
    // @param[in]       page [页号]
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

    // @onajax_fetch_system  [获取与某用户的私信列表]
    // @request type         [GET/POST]
    // @param[in]       page [页号]
    // @return          成功 [success为true, msg_list为消息列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [用户尚未登录]
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
    // @request type         [GET/POST]
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
    // @request type         [GET/POST]
    // @param[in]       page [页号]
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
    // @request type         [GET/POST]
    // @param[in]   username [接收方用户昵称]
    // @param[in]    subject [私信主题]
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
        if (!$touser) {
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
                                    $this->post['subject'],
                                    $this->post['content']);
        $res['success'] = true;
        $res['id'] = $id;
        echo json_encode($res);
    }

    // @onajax_remove        [删除一条消息]
    // @request type         [GET/POST]
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
            $_ENV['message']->remove($this->user['uid'], $msgid);
            $res['success' ] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 消息id无效
        }
        echo json_encode($res);
    }

    // @onajax_remove_dialog [删除与某用户所有对话]
    // @request type         [GET/POST]
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
            $_ENV['message']->remove_by_author($fromuid);
            $res['success'] = true;
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 用户id无效
        }
        echo json_encode($res);
    }
}

?>
