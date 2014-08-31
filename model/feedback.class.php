<?php

!defined('IN_SITE') && exit('Access Denied');

class feedbackmodel {
    var $db;
    var $base;

    function feedbackmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list($start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM `feedback` ORDER BY `time` ASC limit $start,$limit");

        $fb_list = array();
        while ($fb = $this->db->fetch_array($query)) {
            $fb['avatar'] = get_avatar_dir($fb['uid']);
            $fb['format_time'] = tdate($fb['time']);
            $fb_list[] = $fb;
        }
        return $fb_list;
    }

    function get_by_uid($uid) {
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `uid`='$uid'");
    }

    function get_by_page($page) {
        $page = taddslashes($page);
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `page`='$page'");
    }

    function get_status_num($status) {
        return $this->db->fetch_total("feedback", " status=$status ");
    }

    function get_by_status($status, $start, $limit) {
        $query = $this->db->query("SELECT * FROM `feedback` WEHRE `status`=$status ORDER BY `time` ASC limit $start,$limit");

        $fb_list = array();
        while ($fb = $this->db->fetch_array($query)) {
            $fb['avatar'] = get_avatar_dir($fb['uid']);
            $fb_list[] = $fb;
        }
        return $fb_list;
    }

    function get_total_num() {
        return $this->db->fetch_total("feedback");
    }

    function add($content, $page) {
        $content = taddslashes($content);
        $this->db->query("INSERT INTO feedback(`uid`,`username`,`content`,`page`,`ip`,`time`)"
            . " VALUES ('{$this->base->user['uid']}','{$this->base->user['username']}','$content','$page','{$this->base->ip}','{$this->base->time}')");
        return $this->db->insert_id();
    }

    function remove_by_fids($fids) {
        $this->db->query("DELETE FROM `feedback` WHERE `fid` IN ($fids)");
    }

    function remove_by_page($page) {
        $this->db->query("DELETE FROM `feedback` WHERE `page`='$page'");
    }

    function remove_by_uids($uids) {
        $this->db->query("DELETE FROM `feedback` WHERE `uid` IN ($uids)");
    }

    //ip地址限制
    function is_allowed_feedback() {
        $starttime = strtotime("-1 day");
        $endtime = strtotime("+1 day");
        $fb_num = $this->db->result_first("SELECT count(*) FROM feedback WHERE ip='{$this->base->ip}' AND time>$starttime AND time<$endtime");
        if ($fb_num >= $this->base->setting['max_feedback_num']) {
            return false;
        }
        return true;
    }


}

?>
