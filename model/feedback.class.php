<?php

!defined('IN_SITE') && exit('Access Denied');

class feedbackmodel {
    var $db;
    var $base;

    function feedbackmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get() {
        return $this->db->fetch_first("SELECT * FROM `feedback` ORDER BY `time` DESC");
    }

    function get_by_uid($uid) {
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `uid`='$uid'");
    }

    function get_by_page($page) {
        $page = taddslashes($page);
        return $this->db->fetch_first("SELECT * FROM `feedback` WHERE `page`='$page'");
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
}

?>
