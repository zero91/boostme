<?php
/*
CREATE TABLE discuss (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `from` varchar(15) NOT NULL DEFAULT '', 
    `fromuid` int(10) unsigned NOT NULL DEFAULT '0',
    `ip` varchar(20) DEFAULT NULL,
    `time` int(10) unsigned NOT NULL DEFAULT '0',
    `subject` varchar(75) NOT NULL DEFAULT '', 
    `content` text NOT NULL
);
*/
!defined('IN_SITE') && exit('Access Denied');

class discussmodel {
    var $db;
    var $base;

    function discussmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // 读取消息内容
    function get($id) {
        $discuss = $this->db->fetch_first("SELECT * FROM discuss WHERE `id`=$id");
        $discuss['format_time'] = tdate($discuss['time']);
        return $discuss;
    }

    function get_discuss_list($start=0, $limit=10) {
        $discuss_list = array();
        $query = $this->db->query("SELECT * FROM discuss ORDER BY time DESC LIMIT $start,$limit");
        while ($discuss = $this->db->fetch_array($query)) {
            $discuss['avatar'] = get_avatar_dir($discuss['fromuid']);
            $discuss['format_time'] = tdate($discuss['time']);
            $discuss['format_ip'] = formatip($discuss['ip']);
            $discuss_list[] = $discuss;
        }
        return $discuss_list;
    }

    function get_total_num() {
        return $this->db->result_first("SELECT count(*) FROM discuss");
    }

    // 发送消息
    function add($from, $fromuid, $subject, $content) {
        $this->db->query("INSERT INTO discuss SET `from`='$from',`fromuid`=$fromuid,`ip`={$this->base->ip},`time`={$this->base->time},`subject`='$subject',`content`='$content'");
        return $this->db->insert_id();
    }

    function remove_by_id($id) {
        $this->db->query("DELETE FROM discuss WHERE id='$id'");
    }
}

?>
