<?php

!defined('IN_SITE') && exit('Access Denied');

class topic_membermodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function list_by_topic($topic_id, $start=0, $limit=0) {
        $sql = "SELECT * FROM `topic_member` ORDER BY join_time DESC WHERE topic_id='$topic_id' LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function list_topic_used_avatar($topic_id) {
        $sql = "SELECT avatar FROM `topic_member` WHERE topic_id='$topic_id'";
        return $this->db->fetch_all($sql, "avatar", "avatar");
    }

    public function list_sorted_topic($topic_id, $start=0, $limit=10) {
        $sql = "SELECT * FROM `topic_member` WHERE topic_id='$topic_id' " .
               " ORDER BY chat_num ASC,last_time ASC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function list_by_uid($uid, $start=0, $limit=10) {
        $sql = "SELECT * FROM `topic_member` WHERE uid='$uid' ORDER BY last_time DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function get_by_topic_uid($topic_id, $uid) {
        $sql = "SELECT * FROM `topic_member` WHERE topic_id='$topic_id' AND uid='$uid'";
        return $this->db->fetch_first($sql);
    }

    public function get_user_topic_num($uid) {
        return $this->db->fetch_total("topic_member", " uid='$uid' ");
    }

    public function add($topic_id, $uid, $username, $avatar, $role=TOPIC_USER) {
        $time = time();
        $sql = "INSERT INTO topic_member SET topic_id='$topic_id',uid='$uid'," .
                    "username='$username',avatar='$avatar',role='$role'," .
                    "join_time='$time',last_time='$time'";

        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function remove($topic_id, $uid) {
        $this->db->query("DELETE FROM topic_member WHERE $topic_id=$topic_id AND uid=$uid");
        return $this->db->affected_rows();
    }

    public function remove_by_topic($topic_id) {
        $this->db->query("DELETE FROM topic_member WHERE `topic_id`='$topic_id'");
        return $this->db->affected_rows();
    }

    public function remove_by_uid($uid) {
        $this->db->query("DELETE FROM topic_member WHERE `uid`='$uid'");
        return $this->db->affected_rows();
    }

    public function update_chat_num($topic_id, $uid, $delta=1) {
        $time = time();
        $sql = "UPDATE `topic_member` SET `chat_num`=`chat_num`+($delta),`last_time`=$time " .
               " WHERE `topic_id`=$topic_id AND `uid`='$uid'";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function update_avatar($avatar, $topic_id, $uid) {
        $sql = "UPDATE topic_member SET avatar='$avatar WHERE topic_id=$topic_id AND uid='$uid'";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function update_role($role, $topic_id, $uid) {
        $time = time();
        $sql = "UPDATE topic_member SET role=$role WHERE topic_id=$topic_id AND uid=$uid";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    private $db;
}

?>
