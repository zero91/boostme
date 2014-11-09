<?php

!defined('IN_SITE') && exit('Access Denied');

class material_scoremodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid_mid($uid, $mid) {
        return $this->db->fetch_first("SELECT * FROM `material_score` WHERE uid='$uid' AND mid='$mid'");
    }

    public function get_avg_by_mid($mid) {
        return $this->db->result_first("SELECT AVG(`score`) FROM `material_score` WHERE mid='$mid'");
    }

    public function get_all_by_mid($mid) {
        return $this->db->fetch_all("SELECT * FROM `material_score` WHERE mid=$mid ORDER BY `time` DESC");
    }

    public function get_by_mid($mid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `material_score` WHERE mid=$mid ORDER BY `time` DESC limit $start,$limit");
    }

    public function add($uid, $mid, $score) {
        $time = time();
        $this->db->query("INSERT INTO `material_score`(`uid`,`mid`,`score`,`time`) values ('$uid','$mid','$score','$time')");
        return $this->db->insert_id();
    }

    public function remove_by_uid_mid($uid, $mid) {
        $this->db->query("DELETE FROM material_score WHERE `uid`='$uid' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    public function remove_by_mid($mid) {
        $this->db->query("DELETE FROM material_score WHERE `mid`='$mid'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
