<?php

!defined('IN_SITE') && exit('Access Denied');

class problem_categorymodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list() {
        return $this->db->fetch_all("SELECT * FROM `problem_category`");
    }

    public function get_by_pid($pid) {
        return $this->db->fetch_all("SELECT * FROM `problem_category` WHERE pid='$pid'");
    }

    public function get_pidlist($region_id, $school_id="", $dept_id="", $major_id="", $start=0, $limit=10) {
        if (!empty($major_id)) {
            return $this->get_pidlist_by_cid($major_id, "major_id", $start, $limit);
        }
        if (!empty($dept_id)) {
            return $this->get_pidlist_by_cid($dept_id, "dept_id", $start, $limit);
        }
        if (!empty($school_id)) {
            return $tihs->get_pidlist_by_cid($school_id, "school_id", $start, $limit);
        }
        if (!empty($region_id)) {
            return $this->get_pidlist_by_cid($region_id, "region_id", $start, $limit);
        }
        return $this->get_pidlist_by_cid();
    }

    public function get_pidlist_by_cid($cid="", $type="major_id", $start=0, $limit=10) {
        $condition = "$type='$cid'";
        if (empty($cid)) {
            $condition = "1";
        }

        $pid_array = $this->db->fetch_all("SELECT `pid` FROM `problem_category` WHERE $condition LIMIT $start,$limit");
        $pid_list = array();
        foreach ($pid_array as $pid) {
            $pid_list[] = $pid['pid'];
        }
        return $pid_list;
    }

    public function get_full($region_id="", $school_id="", $dept_id="", $major_id="", $start=0, $limit=10) {
        if (!empty($major_id)) {
            return $this->get_full_by_cid($major_id, "major_id", $start, $limit);
        }
        if (!empty($dept_id)) {
            return $this->get_full_by_cid($dept_id, "dept_id", $start, $limit);
        }
        if (!empty($school_id)) {
            return $this->get_full_by_cid($school_id, "school_id", $start, $limit);
        }
        if (!empty($region_id)) {
            return $this->get_full_by_cid($region_id, "region_id", $start, $limit);
        }
        return $this->get_full_by_cid("", "", $start, $limit);
    }

    public function get_full_by_cid($cid="", $type="major_id", $start=0, $limit=10) {
        $condition = "$type='$cid'";
        if (empty($cid)) {
            $condition = "1";
        }
        return $this->db->fetch_all("SELECT problem.* FROM `problem`, (SELECT DISTINCT(`pid`) FROM `problem_category` WHERE $condition) AS pid WHERE problem.pid=pid.pid ORDER BY `time` DESC LIMIT $start,$limit");
    }

    public function get_cid_service_num($cid, $type="major_id") {
        return $this->db->fetch_total("problem_category", "$type='$cid'");
    }

    public function multi_add($pid, $cid_list, $keep_old=true) {
        if (empty($cid_list)) {
            return false;
        }

        if (!$keep_old) {
            $this->db->query("DELETE FROM problem_category WHERE pid=$pid");
        }

        $insertsql = "INSERT INTO problem_category(`pid`,`region_id`,`school_id`,`dept_id`,`major_id`) VALUES ";
        foreach ($cid_list as $cid) {
            $insertsql .= "('$pid','" . $cid['region_id'] . "','" . $cid['school_id'] . "','" . $cid['dept_id'] . "','" . $cid['major_id'] . "'),";
        }

        $this->db->query(substr($insertsql, 0, -1));
        return $this->db->affected_rows();
    }

    public function remove_by_cid($cid_list, $type="major_id") {
        $cidstr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM problem_category WHERE `$type` IN ($cidstr)");
        return $this->db->affected_rows();
    }

    public function remove_by_id($id) {
        $this->db->query("DELETE FROM problem_category WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
