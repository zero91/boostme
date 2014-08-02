<?php

!defined('IN_SITE') && exit('Access Denied');

class userresumemodel {
    var $db;
    var $base;

    function userresumemodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid) {
        $resume = $this->db->fetch_first("SELECT * FROM user_resume WHERE uid='$uid'");
        return $resume;
    }

    function update($uid, $bachelor_school, $bachelor_dept, $bachelor_major, $bachelor_year, $bachelor_month, $master_school, $master_dept, $master_major, $master_year, $master_month, $doctor_school, $doctor_dept, $doctor_major, $doctor_year, $doctor_month, $experience) {
        $this->db->query("REPLACE INTO user_resume(`uid`,`bachelor_school`,`bachelor_dept`,`bachelor_major`,`bachelor_year`,`bachelor_month`,`master_school`,`master_dept`,`master_major`,`master_year`,`master_month`,`doctor_school`,`doctor_dept`,`doctor_major`,`doctor_year`,`doctor_month`,`experience`) VALUES('$uid','$bachelor_school','$bachelor_dept','$bachelor_major','$bachelor_year','$bachelor_month','$master_school','$master_dept','$master_major','$master_year','$master_month','$doctor_school','$doctor_dept','$doctor_major','$doctor_year','$doctor_month','$experience')");
    }

    function update_verify($uid, $verified=1) {
        $this->db->query("UPDATE user_resume SET `verified`=$verified WHERE `uid`=$uid");
    }

    function update_experience($uid, $experience) {
        $this->db->query("UPDATE user_resume SET `experience`='$experience' WHERE `uid`=$uid");
    }

    function update_resume($uid, $resume_path) {
        $this->db->query("UPDATE user_resume SET `resume_path`='$resume_path' WHERE `uid`=$uid");
    }
}

?>
