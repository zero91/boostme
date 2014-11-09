<?php

!defined('IN_SITE') && exit('Access Denied');

class userresumemodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_first("SELECT * FROM user_resume WHERE `uid`='$uid'");
    }

    public function get_apply_num() {
        return $this->db->fetch_total('user_resume', ' `verified`='.RESUME_APPLY.' ');
    }

    public function get_apply_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM user_resume ORDER BY apply_time DESC LIMIT $start,$limit");
    }

    public function update($uid, $realname, $ID, $experience) {
        $this->db->query("INSERT INTO user_resume(`uid`,`realname`,`ID`,`experience`) VALUES('$uid','$realname','$ID','$experience') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `realname`='$realname',`ID`='$ID',`experience`='$experience'");
        return $this->db->affected_rows();
    }

    public function update_verify($uid, $verified=RESUME_APPLY) {
        $this->db->query("INSERT INTO user_resume(`uid`,`verified`) VALUES ('$uid','$verified') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `verified`='$verified'");
        return $this->db->affected_rows();
    }

    public function update_apply_accepted($uid) {
        $this->db->query("INSERT INTO user_resume(`uid`,`verified`,`accepted_time`) VALUES ('$uid','" . RESUME_ACCEPTED . "','" . time() . "') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `verified`='" . RESUME_ACCEPTED . "',`accepted_time`='" . time() . "'");
        return $this->db->affected_rows();
    }

    public function update_experience($uid, $experience) {
        $this->db->query("INSERT INTO user_resume(`uid`,`experience`) VALUES ('$uid', '$experience') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `experience`='$experience'");
        return $this->db->affected_rows();
    }

    public function update_resume($uid, $resume_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`resume_path`) VALUES ('$uid', '$resume_path') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `resume_path`='$resume_path'");
        return $this->db->affected_rows();
    }

    public function update_ID_path($uid, $ID_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`ID_path`) VALUES ('$uid', '$ID_path') "
                        . " ON DUPLICATE KEY "
                        . "UPDATE `ID_path`='$ID_path'");
        return $this->db->affected_rows();
    }

    public function update_studentID($uid, $studentID_path) {
        $this->db->query("INSERT INTO user_resume(`uid`,`studentID`) VALUES ('$uid', '$studentID_path') "
                        . " ON DUPLICATE KEY "
                        . " UPDATE `studentID`='$studentID_path'");
        return $this->db->affected_rows();
    }

    public function already_id_used($uid, $ID) {
        $ID_num = $this->db->result_first("SELECT COUNT(*) FROM `user_resume` WHERE `ID`='$ID' AND `uid`!='$uid'");
        return $ID_num > 0;
    }

    private $db;
}

?>
