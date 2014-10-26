<?php

!defined('IN_SITE') && exit('Access Denied');

class material_categorymodel {
    var $db;
    var $base;

    function material_categorymodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // JUST FOR FIXED THE category INFO
    function get_list() {
        $query = $this->db->query("SELECT * FROM `material_category`");
        $category_list = array();
        while ($category = $this->db->fetch_array($query)) {
            $category_list[] = $category;
        }
        return $category_list;
    }

    function get_by_mid($mid, $limit='') {
        $sql = "SELECT * FROM `material_category` WHERE material_id=$mid";
        !empty($limit) && $sql .=  " LIMIT 0,$limit ";

        $query = $this->db->query($sql);
        $category_list = array();
        while ($category = $this->db->fetch_array($query)) {
            $category_list[] = $category;
        }
        return $category_list;
    }

    function get_by_cid($cid, $type="major_id", $start=0, $limit='') {
        $sql = "SELECT `material_id` FROM `material_category` WHERE $type='$cid'";
        !empty($limit) && $sql .=  " LIMIT $start,$limit ";

        $query = $this->db->query($sql);
        $mid_list = array();
        while ($mid = $this->db->fetch_array($query)) {
            $mid_list[] = $mid['material_id'];
        }
        return $mid_list;
    }

    function get_full_by_cid($cid, $type="major_id", $start=0, $limit=10) {
        $sql = "SELECT * FROM `material` WHERE `id` IN (SELECT `material_id` FROM `material_category` WHERE $type='$cid' LIMIT $start,$limit)";

        $query = $this->db->query($sql);
        $material_list = array();
        while ($material = $this->db->fetch_array($query)) {
            $material['format_time'] = tdate($material['time']);
            $material_list[] = $material;
        }
        return $material_list; 
    }

    function get_cid_material_num($cid, $type="major_id") {
        return $this->db->result_first("SELECT COUNT(*) FROM material_category WHERE $type='$cid'");
    }

    function multi_add($mid, $cid_list, $delete_old=1) {
        if (empty($cid_list)) {
            return false;
        }
        if ($delete_old) {
            $this->db->query("DELETE FROM material_category WHERE mid=$mid");
        }

        $insertsql = "INSERT INTO material_category(`material_id`,`region_id`,`school_id`,`dept_id`,`major_id`) VALUES ";
        foreach ($cid_list as $cid) {
            $insertsql .= "('$mid','" . $cid['region_id'] . "','" . $cid['school_id'] . "','" . $cid['dept_id'] . "','" . $cid['major_id'] . "'),";
        }
        $this->db->query(substr($insertsql, 0, -1));
        return true;
    }

    function remove_by_cid($cid_list, $type="major_id") {
        $cidstr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM material_category WHERE `$type` IN ($cidstr)");

        return $this->db->affected_rows();
    }

    function remove_by_mid_majorid($mid, $major_id) {
        $this->db->query("DELETE FROM material_category WHERE `material_id`='$mid' AND `major_id`='$major_id'");
        return $this->db->affected_rows();
    }

    function get_majorid_info($major_id_list) {
        $major_info_list = array();

        $sql = "SELECT * FROM `material_category_info` WHERE major_id IN (";
        foreach ($major_id_list as $major_id) {
            $sql .= "'$major_id',";
        }
        $sql = substr($sql, 0, -1) . ")";

        $query = $this->db->query($sql);
        while ($major_info = $this->db->fetch_array($query)) {
            $major_info_list[$major_info['major_id']] = $major_info;
        }
        return $major_info_list;
    }

    function update($id, $material_id, $region_id, $school_id, $dept_id, $major_id) {
        $this->db->query("UPDATE `material_category` SET `material_id`='$material_id',`region_id`='$region_id',`school_id`='$school_id',`dept_id`='$dept_id',`major_id`='$major_id' WHERE `id`='$id'");
        return $this->db->affected_rows();
    }
}

?>
