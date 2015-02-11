<?php

!defined('IN_SITE') && exit('Access Denied');

class service_categorymodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_list() {
        return $this->db->fetch_all("SELECT * FROM `service_category`");
    }

    public function get_by_sid($sid) {
        return $this->db->fetch_all("SELECT * FROM `service_category` WHERE service_id='$sid'");
    }

    public function get_sidlist($region_id, $school_id="", $dept_id="", $major_id="", $start=0, $limit=10) {
        if (!empty($major_id)) {
            return $this->get_sidlist_by_cid($major_id, "major_id", $start, $limit);
        }
        if (!empty($dept_id)) {
            return $this->get_sidlist_by_cid($dept_id, "dept_id", $start, $limit);
        }
        if (!empty($school_id)) {
            return $tihs->get_sidlist_by_cid($school_id, "school_id", $start, $limit);
        }
        if (!empty($region_id)) {
            return $this->get_sidlist_by_cid($region_id, "region_id", $start, $limit);
        }
        return $this->get_sidlist_by_cid();
    }

    public function get_sidlist_by_cid($cid="", $type="major_id", $start=0, $limit=10) {
        $condition = "$type='$cid'";
        if (empty($cid)) {
            $condition = "1";
        }

        $sid_array = $this->db->fetch_all("SELECT `service_id` FROM `service_category` WHERE $condition LIMIT $start,$limit");
        $sid_list = array();
        foreach ($sid_array as $sid) {
            $sid_list[] = $sid['service_id'];
        }
        return $sid_list;
    }

    public function get_full($region_id="", $school_id="", $dept_id="", $major_id="", $start=0, $limit=10, $status=SERVICE_STATUS_ACCEPTED) {
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

    public function get_full_by_cid($cid="", $type="major_id", $start=0, $limit=10, $status) {
        $condition = "$type='$cid'";
        if (empty($cid)) {
            $condition = "1";
        }

        $sql = "SELECT service.* FROM `service`,
                                      (SELECT DISTINCT(`service_id`)
                                       FROM `service_category`
                                       WHERE $condition
                                      ) AS sid
                                 WHERE service.status='$status'
                                   AND service.id=sid.service_id
                                 ORDER BY `time` DESC LIMIT $start,$limit";
        $service_list = $this->db->fetch_all($sql);
        foreach ($service_list as &$service) {
            $service['format_time'] = tdate($service['time']);
        }
        return $service_list;
    }

    public function get_cid_service_num($cid, $type="major_id") {
        return $this->db->fetch_total("service_category", "$type='$cid'");
    }

    public function multi_add($sid, $cid_list, $keep_old=true) {
        if (empty($cid_list)) {
            return false;
        }

        if (!$keep_old) {
            $this->db->query("DELETE FROM service_category WHERE service_id=$sid");
        }

        $insertsql = "INSERT INTO service_category(`service_id`,`region_id`,`school_id`,`dept_id`,`major_id`) VALUES ";
        foreach ($cid_list as $cid) {
            $insertsql .= "('$sid','" . $cid['region_id'] . "','" . $cid['school_id'] . "','" . $cid['dept_id'] . "','" . $cid['major_id'] . "'),";
        }

        $this->db->query(substr($insertsql, 0, -1));
        return $this->db->affected_rows();
    }

    public function remove_by_cid($cid_list, $type="major_id") {
        $cidstr = "'" . implode("','", $cid_list) . "'";
        $this->db->query("DELETE FROM service_category WHERE `$type` IN ($cidstr)");
        return $this->db->affected_rows();
    }

    public function remove_by_id($id) {
        $this->db->query("DELETE FROM service_category WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
