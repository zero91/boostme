<?php

!defined('IN_SITE') && exit('Access Denied');

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
        $this->db->query("INSERT INTO message SET `from`='$msgfrom'," .
                                                 "`fromuid`=$msgfromid," . 
                                                 "`touid`=$msgtoid," .
                                                 "`subject`='$subject'," .
                                                 "`time`=$time," .
                                                 "`content`='$content'");
        return $this->db->insert_id();
    }

    // 获取系统消息列表
    public function list_by_touid($touid, $start=0, $limit=10) {
        $sql = "SELECT * FROM message WHERE touid=$touid " . 
                                    " AND fromuid!=$touid " .
                                    " AND status<>" . MSG_STATUS_TO_DELETED . " " .
                                    " AND fromuid=0 " . 
                                    " ORDER BY `time` DESC LIMIT $start,$limit";
        $message_list = $this->db->fetch_all($sql);
        foreach ($message_list as &$message) {
            $message['format_time'] = tdate($message['time']);
        }
        return $message_list;
    }

    // 获取两人私信信息列表
    public function list_by_fromuid($uid, $fromuid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM message WHERE " .
                                            " fromuid<>touid " .
                                            " AND ( " .
                                                    " ( " .
                                                        " fromuid=$fromuid " .
                                                        " AND touid=$uid " .
                                                        " AND status IN (" .
                                                            MSG_STATUS_NODELETED . "," .
                                                            MSG_STATUS_FROM_DELETED .
                                                                       ")" .
                                                    " ) OR ( " . 
                                                        " fromuid=$uid " .
                                                        " AND touid=$fromuid " .
                                                        " AND status IN (" .
                                                            MSG_STATUS_NODELETED . "," .
                                                            MSG_STATUS_TO_DELETED .
                                                                       ")" .
                                                    " ) " .
                                               " ) " . 
                                            " ORDER BY time DESC LIMIT $start,$limit");
    }

    // 获取两人私信信息列表
    public function tot_num_by_fromuid($uid, $fromuid) {
        return $this->db->result_first("SELECT COUNT(*) FROM message WHERE " .
                                            " fromuid<>touid " .
                                            " AND ( " .
                                                    " ( " .
                                                        " fromuid=$fromuid " .
                                                        " AND touid=$uid " .
                                                        " AND status IN (" .
                                                            MSG_STATUS_NODELETED . "," .
                                                            MSG_STATUS_FROM_DELETED .
                                                                       ")" .
                                                    " ) OR ( " . 
                                                        " fromuid=$uid " .
                                                        " AND touid=$fromuid " .
                                                        " AND status IN (" .
                                                            MSG_STATUS_NODELETED . "," .
                                                            MSG_STATUS_TO_DELETED .
                                                                       ")" .
                                                    " ) " .
                                               " ) ");
    }

    // 获取消息列表，并按照发送者分组
    public function group_by_touid($touid, $start=0, $limit=10) {
        $sql = "SELECT *, COUNT(*) as tot_msg, sum(new) as tot_new_msg FROM (" . 
                                "SELECT * FROM message WHERE touid=$touid " .
                                    " AND fromuid!=$touid AND status<>". MSG_STATUS_TO_DELETED .
                                    " AND fromuid<>0 " .
                                    " ORDER BY `time` DESC " .
                             ") t " .
                             " GROUP BY `fromuid` " . 
                             " ORDER BY `time` desc LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    // 用户私信的人数
    public function rownum_by_touid($touid) {
        $query = $this->db->query("SELECT * FROM (" .
                                                    "SELECT * FROM message WHERE touid=$touid " .
                                                    " AND fromuid!=$touid " .
                                                    " AND status<>" . MSG_STATUS_TO_DELETED .
                                                    " AND fromuid<>0 " .
                                                    " ORDER BY `time` DESC" .
                                                ") t GROUP BY `fromuid`");
        return $this->db->num_rows($query);
    }

    // 得到新消息总数
    public function get_new_msg_num($uid) {
        return $this->db->fetch_total("message", "touid='$uid' AND touid>0 AND `new`=1");
    }

    // 获取用户系统消息数量
    public function get_new_system_msg_num($uid) {
        return $this->db->fetch_total("message",
                          "touid='$uid' AND fromuid=0 AND new=1 AND status<>" . MSG_STATUS_TO_DELETED);
    }

    // 获取用户系统消息数量
    public function get_system_msg_num($uid) {
        return $this->db->fetch_total("message",
                          "touid='$uid' AND fromuid=0 AND status<>" . MSG_STATUS_TO_DELETED);
    }

    // 删除用户的信息列表
    public function remove($uid, $msgids) {
        $messageid = ($msgids && is_array($msgids)) ? implode(",", $msgids) : $msgids;

        // 删除属于系统消息的部分
        $this->db->query("DELETE FROM message WHERE fromuid=0 " .
                                              " AND touid=$uid " .
                                              " AND `mid` IN ($messageid)");

        // 删除对方（发送方）已经删除的消息
        $this->db->query("DELETE FROM message WHERE touid=$uid " .
                                              " AND status=" . MSG_STATUS_FROM_DELETED .
                                              " AND `mid` IN ($messageid)");

        // 删除对方（接收方）已经删除的消息
        $this->db->query("DELETE FROM message WHERE fromuid=$uid " .
                                              " AND status= " . MSG_STATUS_TO_DELETED .
                                              " AND `mid` IN ($messageid)");

        // 更新对方（发送方）还未删除的消息状态为接收方已删除
        $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED .
                                            " WHERE touid=$uid " .
                                              " AND status=" . MSG_STATUS_NODELETED .
                                              " AND `mid` IN ($messageid)");

        // 更新对方（接收方）还未删除的消息状态为发送方已删除
        $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED .
                                            " WHERE fromuid=$uid " .
                                              " AND status=" . MSG_STATUS_NODELETED .
                                              " AND `mid` IN ($messageid)");
        return $this->db->affected_rows();
    }

    // 根据发件人删除整个对话
    public function remove_by_author($uid, $authors) {
        foreach ($authors as $fromuid) {
            // 删除对方（发送方）已删除的消息
            $this->db->query("DELETE FROM message WHERE fromuid<>touid " .
                                                  " AND fromuid=$fromuid AND touid=$uid " .
                                                  " AND status=" . MSG_STATUS_FROM_DELETED);

            // 删除对方（接收方）已删除的消息
            $this->db->query("DELETE FROM message WHERE fromuid<>touid " .
                                                 " AND fromuid=$uid AND touid=$fromuid " .
                                                 " AND status=" . MSG_STATUS_TO_DELETED);

            // 更新对方（发送方）未删除的消息状态为接收方已删除
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_TO_DELETED .
                                            " WHERE fromuid<>touid " .
                                              " AND fromuid=$fromuid " .
                                              " AND touid=$uid " .
                                              " AND status=" . MSG_STATUS_NODELETED);

            // 更新对方（接收方）未删除的消息状态为发送方已删除
            $this->db->query("UPDATE message SET status=" . MSG_STATUS_FROM_DELETED .
                                            " WHERE fromuid<>touid " .
                                              " AND fromuid=$uid " .
                                              " AND touid=$fromuid " .
                                              " AND status=" . MSG_STATUS_NODELETED);
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
