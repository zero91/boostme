<?php

!defined('IN_SITE') && exit('Access Denied');

class material_categorymodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list() {
        return $this->db->fetch_all("SELECT * FROM `material_category`");
    }

    public function get_by_mid($mid, $limit='') {
        $sql = "SELECT * FROM `material_category` WHERE material_id='$mid'";
        !empty($limit) && $sql .=  " LIMIT 0,$limit ";

        return $this->db->fetch_all($sql);
    }

    public function get_midlist_by_cid($cid, $type="major_id", $start=0, $limit='') {
        $sql = "SELECT `material_id` FROM `material_category` WHERE $type='$cid'";
        !empty($limit) && $sql .=  " LIMIT $start,$limit ";

        $mid_array = $this->db->fetch_all($sql);
        $mid_list = array();
        foreach ($mid_array as $mid) {
            $mid_list[] = $mid['material_id'];
        }
        return $mid_list;
    }

    public function get_full($region_id="", $school_id="", $dept_id="", $major_id="", $start=0, $limit=10, $status=MATERIAL_STATUS_PUBLISH) {
        if (!empty($major_id)) {
            return $this->get_full_by_cid($major_id, "major_id", $start, $limit, $status);
        }
        if (!empty($dept_id)) {
            return $this->get_full_by_cid($dept_id, "dept_id", $start, $limit, $status);
        }
        if (!empty($school_id)) {
            return $this->get_full_by_cid($school_id, "school_id", $start, $limit, $status);
        }
        if (!empty($region_id)) {
            return $this->get_full_by_cid($region_id, "region_id", $start, $limit, $status);
        }
        return $this->get_full_by_cid("", "", $start, $limit, $status);
    }

    public function get_full_by_cid($cid="", $type="major_id", $start=0, $limit=10, $status=MATERIAL_STATUS_PUBLISH) {
        $condition = "$type='$cid'";
        if (empty($cid)) {
            $condition = "1";
        }

        return $this->db->fetch_all("SELECT material.* FROM `material`, (SELECT DISTINCT(`material_id`) FROM `material_category` WHERE $condition) AS mid WHERE material.status='$status' AND material.id=mid.material_id ORDER BY `time` DESC LIMIT $start,$limit");
    }

    public function get_cid_material_num($cid, $type="major_id") {
        return $this->db->fetch_total("material_category", "$type='$cid'");
    }

    public function multi_add($mid, $cid_list, $keep_old=true) {
        if (empty($cid_list)) {
            return false;
        }

        if (!$keep_old) {
            $this->db->query("DELETE FROM material_category WHERE material_id=$mid");
        }

        $insertsql = "INSERT INTO material_category(`material_id`,`region_id`,`school_id`,`dept_id`,`major_id`) VALUES ";
        foreach ($cid_list as $cid) {
            $insertsql .= "('$mid','" . $cid['region_id'] . "','" . $cid['school_id'] . "','" . $cid['dept_id'] . "','" . $cid['major_id'] . "'),";
        }

        $this->db->query(substr($insertsql, 0, -1));
        return $this->db->affected_rows();
    }

    public function remove_by_cid($cid_list, $type="major_id") {
        $cidstr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM material_category WHERE `$type` IN ($cidstr)");
        return $this->db->affected_rows();
    }

    public function remove_by_id($id) {
        $this->db->query("DELETE FROM material_category WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    public function get_majorid_info($major_id_list) {
        $major_info_list = array();

        $sql = "SELECT * FROM `material_category_info` WHERE major_id IN (";
        $sql .= "'" . implode("','", $major_id_list) . "')";
        return $this->db->fetch_all($sql, "major_id");
    }

    public function update($id, $mid, $region_id, $school_id, $dept_id, $major_id) {
        $this->db->query("UPDATE `material_category` SET `material_id`='$mid',`region_id`='$region_id',`school_id`='$school_id',`dept_id`='$dept_id',`major_id`='$major_id' WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
