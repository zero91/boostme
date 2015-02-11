<?php

!defined('IN_SITE') && exit('Access Denied');

class topicmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get($id) {
        return $this->db->fetch_first("SELECT * FROM topic WHERE id='$id'");
    }

    public function get_list($start=0, $limit=10) {
        //$sql = "SELECT * FROM `topic` ORDER BY `members` DESC,`time` DESC limit $start,$limit";
        $sql = "SELECT * FROM `topic` ORDER BY `last_time` DESC limit $start,$limit";
        $topic_list = $this->db->fetch_all($sql);

        foreach ($topic_list as &$topic) {
            $topic['format_time'] = tdate($topic['time']);
            $topic['format_last_time'] = tdate($topic['last_time']);
        }
        return $topic_list;
    }

    public function list_by_uid($uid, $start=0, $limit=10) {
        $sql = "SELECT * FROM `topic` WHERE `authorid`='$uid' ORDER BY `time` DESC limit $start,$limit";
        $topic_list = $this->db->fetch_all($sql);

        foreach ($topic_list as &$topic) {
            $topic['format_time'] = tdate($topic['time']);
        }
        return $topic_list;
    }

    public function is_owner($topic_id, $uid) {
        return $this->db->fetch_total("topic", "`authorid`='$uid' AND `id`='$topic_id'") > 0;
    }

    public function get_total_num() {
        return $this->db->fetch_total("topic");
    }

    public function get_user_total_num($uid) {
        return $this->db->fetch_total("topic", " authorid='$uid' ");
    }

    public function add($uid, $username, $title, $description, $ip) {
        $time = time();
        $sql = "INSERT INTO `topic` SET authorid='$uid',author='$username',title='$title'," .
               "description='$description',time='$time',last_time='$time',ip='$ip'";

        $this->db->query($sql);
        return $this->db->insert_id();
    }

    public function update_members($topic_id, $delta=1) {
        $time = time();
        $sql = "UPDATE `topic` SET `members`=`members`+($delta) WHERE `id`=$topic_id";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function update_chat_num($topic_id, $delta=1) {
        $time = time();
        $sql = "UPDATE `topic` SET `chat_num`=`chat_num`+($delta),`last_time`=$time " .
               " WHERE `id`=$topic_id";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function search_title($title, $start=0, $limit=10) {
        $sql = "SELECT * FROM topic WHERE title LIKE '%$title%' ORDER BY last_time DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function list_by_condition($cond, $start=0, $limit=10) {
        $sql = "SELECT * FROM topic WHERE $cond ORDER BY `last_time` DESC limit $start,$limit";

        return $this->db->fetch_all($sql);
    }

    private $db;
}

?>
