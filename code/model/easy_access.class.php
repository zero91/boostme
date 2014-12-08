<?php

!defined('IN_SITE') && exit('Access Denied');

class easy_accessmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list() {
        return $this->db->fetch_all("SELECT * FROM `easy_access`");
    }

    public function get_user_access_num($uid, $target_type) {
        return $this->db->fetch_total("easy_access", "uid='$uid' AND target_type='$target_type'");
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM `easy_access` WHERE uid='$uid' ORDER BY `time` ASC");
    }

    public function get_by_uid_target($uid, $target_type) {
        return $this->db->fetch_all("SELECT * FROM `easy_access` WHERE uid='$uid' AND `target_type`='$target_type' ORDER BY `time` ASC");
    }

    public function remove_by_id($id) {
        $this->db->query("DELETE FROM `easy_access` WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    public function remove_by_uid($uid, $target_type="") {
        $sql = "DELETE FROM easy_access WHERE `uid`='$uid'";
        if (!empty($target_type)) {
            $sql .= " AND `target_type`='$target_type'";
        }
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function add($uid, $region_id, $school_id, $dept_id, $major_id, $target_type) {
        $time = time();
        $this->db->query("INSERT INTO easy_access(`uid`,`region_id`,`school_id`,`dept_id`,`major_id`,`time`,`target_type`) VALUES ('$uid','$region_id','$school_id','$dept_id','$major_id','$time','$target_type')");
        return $this->db->insert_id();
    }

    private $db;
}

?>
