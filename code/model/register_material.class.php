<?php

!defined('IN_SITE') && exit('Access Denied');

class register_materialmodel {
    public function register_materialmodel(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid() {
        return $this->db->fetch_all("SELECT * FROM `register_material` ORDER BY `time` ASC");
    }

    public function get_list($start=0, $limit=10, $existed="0") {
        return $this->db->fetch_all("SELECT * FROM `register_material` WHERE `existed` IN ($existed) ORDER BY `time` ASC limit $start,$limit");
    }

    public function get_total_num($existed="0") {
        return $this->db->fetch_total("register_material", " existed IN ($existed)");
    }

    public function add($uid, $username, $description) {
        $time = time();
        $this->db->query("INSERT INTO register_material(`uid`,`username`,`description`,`time`,`existed`) VALUES ('$uid','$username','$description','$time','0')");
        return $this->db->insert_id();
    }

    public function update_material_existed($id, $existed=1) {
        $this->db->query("UPDATE `register_material` SET `existed`=$existed WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    public function remove_by_ids($ids) {
        $this->db->query("DELETE FROM `register_material` WHERE `id` IN ($ids)");
        return $this->db->affected_rows();
    }

    public function remove_by_uids($uids) {
        $this->db->query("DELETE FROM `register_material` WHERE `uid` IN ($uids)");
        return $this->db->affected_rows();
    }
}

?>
