<?php

!defined('IN_SITE') && exit('Access Denied');

class withdrawmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 读取消息内容
    public function get_by_id($id) {
        return $this->db->fetch_first("SELECT * FROM withdraw WHERE `id`=$id");
    } 

    public function get_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM withdraw WHERE `uid`=$uid");
    }

    public function get_uid_unpaid($uid) {
        return $this->db->fetch_all("SELECT * FROM withdraw WHERE `uid`=$uid AND ispaid='0'");
    }

    public function move_by_id($id) {
        $this->db->query("DELETE FROM withdraw WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    // 发送消息
    public function add($uid, $money, $ebank_type, $ebank_account) {
        $time = time();
        $this->db->query("INSERT INTO withdraw SET `uid`='$uid',`money`='$money',`ebank_type`='$ebank_type',`ebank_account`='$ebank_account',`apply_time`='$time'");
        return $this->db->insert_id();
    }

    public function update_paid($id, $operator_id, $operator) {
        $time = time();
        $this->db->query("UPDATE withdraw SET `operator_id`='$operator_id',`operator`='$operator',`give_time`='$time',`ispaid`='1' WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
