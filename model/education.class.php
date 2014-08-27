<?php

!defined('IN_SITE') && exit('Access Denied');

class educationmodel {
    var $db;
    var $base;

    function educationmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid) {
        $edu_list = array();

        $query = $this->db->query("SELECT * FROM `education` WHERE `uid`='$uid' ORDER BY `start_time` DESC");
        while ($edu = $this->db->fetch_array($query)) {
            $edu_list[] = $edu;
        }
        return $edu_list;
    }

    function add_edu($uid, $edu_type, $school, $department, $major, $start_time, $end_time) {
        $this->db->query("INSERT INTO education(`uid`,`edu_type`,`school`,`department`,`major`,`start_time`,`end_time`) VALUES('$uid','$edu_type','$school','$department','$major','$start_time','$end_time')");
    }

    function multi_add($uid, $edu_list) {
        $insertsql = "INSERT INTO education(`uid`,`edu_type`,`school`,`department`,`major`,`start_time`,`end_time`) VALUES ";
        foreach ($edu_list as $edu) {
            $insertsql .= "($uid,'{$edu['edu_type']}','{$edu['school']}','{$edu['dept']}','{$edu['major']}','{$edu['start_time']}','{$edu['end_time']}'),";
        }
        $this->db->query(substr($insertsql, 0, -1));
    }

    function update_edu($eid, $edu_type, $school, $department, $major, $start_time, $end_time) {
        $this->db->query("UPDATE education SET `edu_type`='$edu_type',`school`='$school',`department`='$department',`major`='$major',`start_time`='$start_time',`end_time`='$end_time' WHERE `eid`='$eid'");
    }

    function remove_edu($eid) {
        $this->db->query("DELETE FROM education WHERE `eid`='$eid'");
    }

    function remove_by_uid($uid) {
        $this->db->query("DELETE FROM education WHERE `uid`='$uid'");
    }
}

?>
