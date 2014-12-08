<?php

!defined('IN_SITE') && exit('Access Denied');

class user_ebankmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM user_ebank WHERE `uid`='$uid'");
    }

    public function get_user_ebank_num($uid) {
        return $this->db->fetch_total("user_ebank", " `uid`='$uid' ");
    }

    public function get_user_default_account($uid) {
        return $this->db->fetch_first("SELECT * FROM user_ebank WHERE `uid`='$uid' AND isdefault='1'");
    }

    public function add($uid, $ebank_type, $ebank_account, $isdefault=0) {
        runlog("test008", "INSERT INTO user_ebank(uid,ebank_type,ebank_account,isdefault,time) VALUES ('$uid','$ebank_type','$ebank_account','$isdefault'," . time() . ")");
        $this->db->query("INSERT INTO user_ebank(uid,ebank_type,ebank_account,isdefault,time) VALUES ('$uid','$ebank_type','$ebank_account','$isdefault'," . time() . ")");
        return $this->db->affected_rows();
    }

    public function remove($uid, $ebank_type, $ebank_account) {
        $this->db->query("DELETE FROM user_ebank WHERE `uid`='$uid' AND `ebank_type`='$ebank_type' AND `ebank_account`='$ebank_account'");
        return $this->db->affected_rows();
    }

    public function update_default($uid, $ebank_type, $ebank_account, $isdefault) {
        $this->db->query("UPDATE user_ebank SET `isdefault`='$isdefault' WHERE `uid`='$uid' AND `ebank_type`='$ebank_type' AND `ebank_account`='$ebank_account'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
