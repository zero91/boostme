<?php

!defined('IN_SITE') && exit('Access Denied');

class messagemodel {
    var $db;
    var $base;

    function messagemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // 读取消息内容
    function get($mid) {
        $message = $this->db->fetch_first("SELECT * FROM message WHERE `mid`=$mid");
        $message['date'] = tdate($message['time']);
        return $message;
    } 

    // 发送消息
    function add($msgfrom, $msgfromid, $msgtoid, $subject, $message) {
        $time = $this->base->time;
        $this->db->query("INSERT INTO message SET `from`='$msgfrom',`fromuid`=$msgfromid,`touid`=$msgtoid,`subject`='$subject',`time`=$time,`content`='$message'");
        return $this->db->insert_id();
    }

    // 获取消息列表
    function list_by_touid($touid, $start=0, $limit=10) {
        $messagelist = array();
        $sql = "SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>" . MSG_STATUS_TO_DELETED . " AND fromuid=0 ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    // 获取消息列表，并按照发送者分组
    function group_by_touid($touid, $start=0, $limit=10) {
        $messagelist = array();
        $sql = "SELECT * FROM (SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>". MSG_STATUS_TO_DELETED ." AND fromuid<>0 ORDER BY `time` DESC) t GROUP BY `from` ORDER BY `time` desc LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    function rownum_by_touid($touid) {
        $query = $this->db->query("SELECT * FROM (SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>" . MSG_STATUS_TO_DELETED . " AND fromuid<>0  ORDER BY `time` DESC) t GROUP BY `from`");
        return $this->db->num_rows($query);
    }

    function list_by_fromuid($fromuid, $start=0, $limit=10) {
        $messagelist = array();
        $sql = "SELECT * FROM message WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_FROM_DELETED . ")) OR ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_TO_DELETED . ")) ORDER BY time DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($message = $this->db->fetch_array($query)) {
            $message['format_time'] = tdate($message['time']);
            $message['from_avatar'] = get_avatar_dir($message['fromuid']);
            $message['touser'] = $this->db->result_first("SELECT username FROM user WHERE uid=" . $message['touid']);
            $messagelist[] = $message;
        }
        return $messagelist;
    }

    // 得到新消息总数
    function get_num($uid) {
        /////// /////// /////// /////// /////// ///////
        $num = $this->db->result_first("SELECT count(*) FROM message WHERE touid='$uid' AND touid>0 AND `new`=1");
        return $num;
    }

    function remove($type, $msgids) {
        $messageid = ($msgids && is_array($msgids)) ? implode(",", $msgids) : $msgids;
        if ('inbox' == $type) {
            $this->db->query("DELETE FROM message WHERE fromuid=0 AND `mid` IN ($messageid)");
            $this->db->query("DELETE FROM message WHERE status = " . MSG_STATUS_FROM_DELETED . " AND `mid` IN ($messageid)");
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED ." WHERE status=" . MSG_STATUS_NODELETED . " AND `mid` IN ($messageid)");
        } else {
            $this->db->query("DELETE FROM message WHERE status = " . MSG_STATUS_TO_DELETED . " AND `mid` IN ($messageid)");
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED . " WHERE status=" . MSG_STATUS_NODELETED . " AND `mid` IN ($messageid)");
        }
    }

    // 根据发件人删除整个对话
    function remove_by_author($authors) {
        foreach ($authors as $fromuid) {
            $this->db->query("DELETE FROM message WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status=" . MSG_STATUS_FROM_DELETED . ")");
            $this->db->query("DELETE FROM message WHERE fromuid<>touid AND ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status=" . MSG_STATUS_TO_DELETED);
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED . " WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $this->base->user['uid'] . ") AND status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_FROM_DELETED . "))");
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED . " WHERE fromuid<>touid AND ((fromuid=" . $this->base->user['uid'] . " AND touid=" . $fromuid . ") AND  status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_TO_DELETED . "))");
        }
    }

    // 更新消息为已读状态
    function read_by_fromuid($fromuid) {
        $this->db->query("UPDATE `message` set new=0  WHERE `fromuid` =$fromuid");
    }

    function update_status($mid, $status) {
        $this->db->query("UPDATE message SET status=$status WHERE mid=$mid");
    }
}

?>
