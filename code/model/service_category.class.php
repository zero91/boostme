<?php

!defined('IN_SITE') && exit('Access Denied');

class service_category_model {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list() {
        return $this->db->fetch_all("SELECT * FROM `service_category`");
    }

    public function get_by_sid($sid) {
        $cid_array = $this->db->fetch_all("SELECT * FROM `service_category` WHERE sid='$sid'");
        $cid_list = array();
        foreach ($cid_array as $cid) {
            $cid_list[] = $cid['cid'];
        }
        return $cid_list;
    }

    public function get_sidlist_by_cid($cid, $start=0, $limit=10) {
        $sid_array = $this->db->fetch_all("SELECT `sid` FROM `service_category` WHERE cid='$cid' LIMIT $start,$limit");
        $sid_list = array();
        foreach ($sid_array as $sid) {
            $sid_list[] = $sid['sid'];
        }
        return $sid_list;
    }

    public function get_full_by_cid($cid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `service` WHERE `id` IN (SELECT `sid` FROM `service_category` WHERE cid='$cid' LIMIT $start,$limit)");
    }

    public function get_cid_sid_num($cid) {
        return $this->db->fetch_total("service_category", "cid='$cid'");
    }

    public function multi_add($sid, $cid_list, $keep_old=true) {
        if (empty($cid_list)) {
            return false;
        }

        $old_list = array();
        if ($keep_old) {
            $old_list = $this->get_by_sid($sid);
        } else {
            $this->db->query("DELETE FROM service_category WHERE sid=$sid");
        }

        $add_num = 0;
        $insertsql = "INSERT INTO service_category(`sid`,`cid`) VALUES ";
        foreach ($cid_list as $cid) {
            if (!in_array($cid, $old_list)) {
                $insertsql .= "('$sid','$cid'),";
                $add_num += 1;
            }
        }
        if ($add_num > 0) {
            $this->db->query(substr($insertsql, 0, -1));
        }
        return $this->db->affected_rows();
    }

    public function remove_by_cid($cid_list) {
        $cidstr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM service_category WHERE `cid` IN ($cidstr)");
        return $this->db->affected_rows();
    }

    public function remove_by_sid_cid($sid, $cid) {
        $this->db->query("DELETE FROM service_category WHERE `sid`='$sid' AND `cid`='$cid'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
