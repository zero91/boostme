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

    function update($uid, $realname, $ID, $experience) {
        $ret = $this->db->query("INSERT INTO user_resume(`uid`,`realname`,`ID`,`experience`) VALUES('$uid','$realname','$ID','$experience') "
            . " ON DUPLICATE KEY "
            . " UPDATE `realname`='$realname',`ID`='$ID',`experience`='$experience'");
    }

    function update_verify($uid, $verified=APPLY) {
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

    function already_id_used($uid, $ID) {
        $ID_num = $this->db->result_first("SELECT COUNT(*) FROM `user_resume` WHERE `ID`='$ID' AND `uid`!='$uid'");
        return $ID_num > 0;
    }
}

?>
