<?php

!defined('IN_SITE') && exit('Access Denied');

class chatmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get($id) {
        return $this->db->fetch_first("SELECT * FROM chat WHERE id='$id'");
    }

    public function list_by_topic($topic_id, $start=0, $limit=10) {
        $sql = "SELECT * FROM `chat` WHERE `topic_id`='$topic_id' ORDER BY `time` DESC limit $start,$limit";
        $chat_list = $this->db->fetch_all($sql);
        foreach ($chat_list as &$chat) {
            $chat['format_time'] = tdate($chat['time']);
            $chat['format_ip'] = formatip($chat['ip']);
        }
        return $chat_list;
    }

    public function get_topic_chat_num($topic_id) {
        return $this->db->fetch_total("chat", "topic_id='$topic_id'");
    }

    public function add($topic_id, $authorid, $author, $avatar, $content, $ip) {
        $time = time();
        $sql = "INSERT INTO `chat` SET topic_id='$topic_id',authorid='$authorid'," .
               "author='$author',avatar='$avatar',content='$content',time='$time',ip='$ip'";
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    public function remove_by_id($id) {
        $this->db->query("DELETE FROM chat WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    public function remove_by_topic($topic_id) {
        $this->db->query("DELETE FROM chat WHERE `topic_id`='$topic_id'");
        return $this->db->affected_rows();
    }

    public function remove_by_topic_uid($topic_id, $uid) {
        $this->db->query("DELETE FROM chat WHERE `topic_id`='$topic_id' AND `authorid`='$uid'");
        return $this->db->affected_rows();
    }

    public function search_content($content, $start=0, $limit=10) {
        $sql = "SELECT * FROM chat WHERE content LIKE '%$content%' ORDER BY time DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    private $db;
}

?>
