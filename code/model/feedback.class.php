<?php

!defined('IN_SITE') && exit('Access Denied');

class feedbackmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `feedback` ORDER BY `time` ASC limit $start,$limit");
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `uid`='$uid'");
    }

    public function get_by_page($page) {
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `page`='$page'");
    }

    public function get_status_num($status) {
        return $this->db->fetch_total("feedback", " status=$status ");
    }

    public function get_by_status($status, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `feedback` WEHRE `status`=$status ORDER BY `time` ASC limit $start,$limit");
    }

    public function get_total_num() {
        return $this->db->fetch_total("feedback");
    }

    public function add($uid, $username, $content, $page, $ip) {
        $this->db->query("INSERT INTO feedback(`uid`,`username`,`content`,`page`,`ip`,`time`)"
            . " VALUES ('$uid','$username','$content','$page','$ip'," . time() . ")");
        return $this->db->insert_id();
    }

    public function remove_by_fids($fids) {
        $this->db->query("DELETE FROM `feedback` WHERE `fid` IN ($fids)");
        return $this->db->affected_rows();
    }

    public function remove_by_page($page) {
        $this->db->query("DELETE FROM `feedback` WHERE `page`='$page'");
        return $this->db->affected_rows();
    }

    public function remove_by_uids($uids) {
        $this->db->query("DELETE FROM `feedback` WHERE `uid` IN ($uids)");
        return $this->db->affected_rows();
    }

    // ip地址限制
    public function is_allowed_feedback($ip) {
        global $setting;
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $fb_num = $this->db->result_first("SELECT count(*) FROM feedback WHERE ip='$ip' AND time>$starttime AND time<$endtime");
        return $fb_num < $setting['max_feedback_num'];
    }

    private $db;
}

?>
