<?php

!defined('IN_SITE') && exit('Access Denied');

class material_categorymodel {
    var $db;
    var $base;

    function material_categorymodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_mid($mid, $limit='') {
        $sql = "SELECT `cid` FROM `material_category` WHERE material_id=$mid";
        !empty($limit) && $sql .=  " LIMIT 0,$limit ";

        $query = $this->db->query($sql);
        $cid_list = array();
        while ($cid = $this->db->fetch_array($query)) {
            $cid_list[] = $cid['cid'];
        }
        return $cid_list;
    }

    function get_by_cid($cid, $start=0, $limit='') {
        $sql = "SELECT `material_id` FROM `material_category` WHERE cid='$cid'";
        !empty($limit) && $sql .=  " LIMIT $start,$limit ";

        $query = $this->db->query($sql);
        $mid_list = array();
        while ($mid = $this->db->fetch_array($query)) {
            $mid_list[] = $mid['material_id'];
        }
        return $mid_list;
    }

    function get_full_by_cid($cid, $start=0, $limit=10) {
        $sql = "SELECT * FROM `material` WHERE `id` IN (SELECT `material_id` FROM `material_category` WHERE cid='$cid' LIMIT $start,$limit)";

        $query = $this->db->query($sql);
        $material_list = array();
        while ($material = $this->db->fetch_array($query)) {
            $material['format_time'] = tdate($material['time']);
            $material_list[] = $material;
        }
        return $material_list; 
    }

    function get_cid_material_num($cid) {
        return $this->db->result_first("SELECT COUNT(*) FROM material_category WHERE cid='$cid'");
    }

    function multi_add($mid, $cid_list) {
        print_r($cid_list);
        if (empty($cid_list)) {
            return false;
        }
        $this->db->query("DELETE FROM material_category WHERE mid=$mid");
        $insertsql = "INSERT INTO material_category(`material_id`,`cid`) VALUES ";
        foreach ($cid_list as $cid) {
            $insertsql .= "('$mid','$cid'),";
        }
        $this->db->query(substr($insertsql, 0, -1));
        return true;
    }

    function remove_by_cid($cid_list) {
        $namestr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM material_category WHERE `cid` IN ($namestr)");
    }
}

?>
