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

    function update($uid, $realname, $bachelor_school, $bachelor_dept, $bachelor_major, $bachelor_year, $bachelor_month, $master_school, $master_dept, $master_major, $master_year, $master_month, $doctor_school, $doctor_dept, $doctor_major, $doctor_year, $doctor_month, $experience,$ID) {
        $this->db->query("INSERT INTO user_resume(`uid`,`realname`,`bachelor_school`,`bachelor_dept`,`bachelor_major`,`bachelor_year`,`bachelor_month`,`master_school`,`master_dept`,`master_major`,`master_year`,`master_month`,`doctor_school`,`doctor_dept`,`doctor_major`,`doctor_year`,`doctor_month`,`experience`,`ID`) VALUES('$uid','$realname','$bachelor_school','$bachelor_dept','$bachelor_major','$bachelor_year','$bachelor_month','$master_school','$master_dept','$master_major','$master_year','$master_month','$doctor_school','$doctor_dept','$doctor_major','$doctor_year','$doctor_month','$experience','$ID') "
            . " ON DUPLICATE KEY "
            . " UPDATE `realname`='$realname',`bachelor_school`='$bachelor_school',`bachelor_dept`='$bachelor_dept',`bachelor_major`='$bachelor_major',`bachelor_year`='$bachelor_year',`bachelor_month`='$bachelor_month',`master_school`='$master_school',`master_dept`='$master_dept',`master_major`='$master_major',`master_year`='$master_year',`master_month`='$master_month',`doctor_school`='$doctor_school',`doctor_dept`='$doctor_dept',`doctor_major`='$doctor_major',`doctor_year`='$doctor_year',`doctor_month`='$doctor_month',`experience`='$experience',`ID`='$ID'");
    }

    function update_verify($uid, $verified=1) {
        $this->db->query("INSERT INTO user_resume(`uid`,`verified`) VALUES ('$uid', '$verified') ON DUPLICATE KEY UPDATE `verified`='$verified'");
    }

    function update_experience($uid, $experience) {
        $this->db->query("INSERT INTO user_resume(`uid`,`experience`) VALUES ('$uid', '$experience') ON DUPLICATE KEY UPDATE `experience`='$experience'");
    }

    function update_resume($uid, $resume_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`resume_path`) VALUES ('$uid', '$resume_path') ON DUPLICATE KEY UPDATE `resume_path`='$resume_path'");
    }

    function update_ID_path($uid, $ID_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`ID_path`) VALUES ('$uid', '$ID_path') ON DUPLICATE KEY UPDATE `ID_path`='$ID_path'");
    }

    function update_studentID($uid, $studentID_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`studentID`) VALUES ('$uid', '$studentID_path') ON DUPLICATE KEY UPDATE `studentID`='$studentID_path'");
    }
}

?>
