<?php

!defined('IN_SITE') && exit('Access Denied');

// 用户登录时，查询用户个人信息
// $user['msg_system'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid=0 AND status<>" . MSG_STATUS_TO_DELETED);
// $user['msg_personal'] = $this->db->fetch_total('message', " new=1 AND touid=$uid AND fromuid<>$uid AND fromuid<>0 AND status<>" . MSG_STATUS_TO_DELETED);

class messagemodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 读取消息内容
    public function get($mid) {
        return $this->db->fetch_first("SELECT * FROM message WHERE `mid`=$mid");
    } 

    // 发送消息
    public function add($msgfrom, $msgfromid, $msgtoid, $subject, $content) {
        $time = time();
        $this->db->query("INSERT INTO message SET `from`='$msgfrom',`fromuid`=$msgfromid,`touid`=$msgtoid,`subject`='$subject',`time`=$time,`content`='$content'");
        return $this->db->insert_id();
    }

    // 获取系统消息列表
    public function list_by_touid($touid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>" . MSG_STATUS_TO_DELETED . " AND fromuid=0 ORDER BY `time` DESC LIMIT $start,$limit");
    }

    // 获取消息列表，并按照发送者分组
    public function group_by_touid($touid, $start=0, $limit=10) {
        $sql = "SELECT * FROM (SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>". MSG_STATUS_TO_DELETED ." AND fromuid<>0 ORDER BY `time` DESC) t GROUP BY `from` ORDER BY `time` desc LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function rownum_by_touid($touid) {
        $query = $this->db->query("SELECT * FROM (SELECT * FROM message WHERE touid=$touid AND fromuid!=$touid AND status<>" . MSG_STATUS_TO_DELETED . " AND fromuid<>0  ORDER BY `time` DESC) t GROUP BY `from`");
        return $this->db->num_rows($query);
    }

    public function list_by_fromuid($uid, $fromuid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM message WHERE fromuid<>touid AND ((fromuid=$fromuid AND touid=" . $uid . ") AND status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_FROM_DELETED . ")) OR ((fromuid=" . $uid . " AND touid=" . $fromuid . ") AND  status IN (" . MSG_STATUS_NODELETED . "," . MSG_STATUS_TO_DELETED . ")) ORDER BY time DESC LIMIT $start,$limit");
    }

    // 得到新消息总数
    public function get_new_msg_num($uid) {
        return $this->db->fetch_total("message", "touid='$uid' AND touid>0 AND `new`=1");
    }

    public function remove($uid, $msgids) {
        $messageid = ($msgids && is_array($msgids)) ? implode(",", $msgids) : $msgids;

        $this->db->query("DELETE FROM message WHERE fromuid=0 AND touid=" . $uid ." AND `mid` IN ($messageid)");
        $this->db->query("DELETE FROM message WHERE touid=" . $uid ." AND status=" . MSG_STATUS_FROM_DELETED . " AND `mid` IN ($messageid)");
        $this->db->query("DELETE FROM message WHERE fromuid=" . $uid . " AND status= " . MSG_STATUS_TO_DELETED . " AND `mid` IN ($messageid)");
        $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED ." WHERE touid=". $uid . " AND status=" . MSG_STATUS_NODELETED . " AND `mid` IN ($messageid)");
        $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED . " WHERE fromuid=" . $uid . " AND status=" . MSG_STATUS_NODELETED . " AND `mid` IN ($messageid)");
        return $this->db->affected_rows();
    }

    // 根据发件人删除整个对话
    public function remove_by_author($uid, $authors) {
        foreach ($authors as $fromuid) {
            $this->db->query("DELETE FROM message WHERE fromuid<>touid AND fromuid=$fromuid AND touid=" . $uid . " AND status=" . MSG_STATUS_FROM_DELETED);
            $this->db->query("DELETE FROM message WHERE fromuid<>touid AND fromuid=" . $uid . " AND touid=$fromuid AND status=" . MSG_STATUS_TO_DELETED);
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED . " WHERE fromuid<>touid AND fromuid=$fromuid AND touid=" . $uid . " AND status=" . MSG_STATUS_NODELETED);
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED . " WHERE fromuid<>touid AND fromuid=" . $uid . " AND touid=$fromuid AND  status=" . MSG_STATUS_NODELETED);
        }
    }

    // 更新消息为已读状态
    public function read_by_fromuid($fromuid) {
        $this->db->query("UPDATE `message` set new=0  WHERE `fromuid`=$fromuid");
        return $this->db->affected_rows();
    }

    public function read_by_mid($mid) {
        $this->db->query("UPDATE `message` set new=0  WHERE `mid`='$mid'");
        return $this->db->affected_rows();
    }

    public function update_status($mid, $status) {
        $this->db->query("UPDATE `message` SET status=$status WHERE mid=$mid");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
