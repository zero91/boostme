<?php

!defined('IN_SITE') && exit('Access Denied');

class educationmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM `education` WHERE `uid`='$uid' ORDER BY `start_time` DESC");
    }

    public function get_by_eid($eid) {
        return $this->db->fetch_first("SELECT * FROM `education` WHERE eid='$eid'");
    }

    public function add_edu($uid, $edu_type, $school, $department, $major, $start_time, $end_time) {
        $this->db->query("INSERT INTO education(`uid`,`edu_type`,`school`,`department`,`major`,`start_time`,`end_time`) VALUES('$uid','$edu_type','$school','$department','$major','$start_time','$end_time')");
        return $this->db->affected_rows();
    }

    public function multi_add($uid, $edu_list) {
        $sql = "INSERT INTO education(`uid`,`edu_type`,`school`,`department`," .
                                      "`major`,`start_time`,`end_time`) VALUES ";
        foreach ($edu_list as $edu) {
            $sql .= "($uid,'{$edu['edu_type']}','{$edu['school']}','{$edu['dept']}'," .
                        "'{$edu['major']}','{$edu['start_time']}','{$edu['end_time']}'),";
        }
        $this->db->query(substr($sql, 0, -1));
        return $this->db->affected_rows();
    }

    public function update_edu($eid, $edu_type, $school, $department, $major, $start_time, $end_time) {
        $this->db->query("UPDATE education SET `edu_type`='$edu_type',`school`='$school',`department`='$department',`major`='$major',`start_time`='$start_time',`end_time`='$end_time' WHERE `eid`='$eid'");
        return $this->db->affected_rows();
    }

    public function remove_by_eid($eid) {
        $this->db->query("DELETE FROM education WHERE `eid`='$eid'");
        return $this->db->affected_rows();
    }

    public function remove_by_uid($uid) {
        $this->db->query("DELETE FROM education WHERE `uid`='$uid'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
