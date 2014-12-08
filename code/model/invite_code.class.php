<?php

!defined('IN_SITE') && exit('Access Denied');

class invite_codemodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_code($code) {
        return $this->db->fetch_first("SELECT * FROM invite_code WHERE `code`='$code'");
    }

    public function get_by_owner($owner, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM invite_code WHERE `owner`='$owner' ORDER BY `time` DESC LIMIT $start,$limit");
    }

    public function get_owner_code_num($owner) {
        return $this->db->fetch_total("invite_code", " `owner`='$owner' ");
    }

    public function add($code, $owner) {
        $this->db->query("INSERT INTO invite_code(code,owner,time) VALUES ('$code','$owner'," . time() . ")");
        return $this->db->affected_rows();
    }

    public function remove_by_code($code) {
        $this->db->query("DELETE FROM invite_code WHERE `code`='$code'");
        return $this->db->affected_rows();
    }

    public function remove_by_owner($owner) {
        $this->db->query("DELETE FROM invite_code WHERE `owner`='$owner'");
        return $this->db->affected_rows();
    }

    public function update_invite_user($code, $invite_user, $invite_username) {
        $this->db->query("UPDATE invite_code SET `invite_user`='$invite_user',`invite_username`='$invite_username' WHERE `code`='$code'");
        return $this->db->affected_rows();
    }

    // 生成唯一的邀请码
    public function create_code($sid) {
        $time = time();
        $code = cutstr("$time$sid" . random(32), 32, '');
        return $code;
    }

    private $db;
}

?>
