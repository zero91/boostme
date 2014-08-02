<?php

!defined('IN_SITE') && exit('Access Denied');

class chargingmodel {
    var $db;
    var $base;

    function chargingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_pid($pid) {
        return $this->db->fetch_first("SELECT * FROM `charging` WHERE `pid`='$pid'");
    }

    function get_by_cid($cid) {
        return $this->db->fetch_first("SELECT * FROM `charging` WHERE `cid`='$cid'");
    }

    function remove_by_pid($pid) {
        $this->db->query("DELETE FROM `charging` WHERE `pid`='$pid'");
    }

    function remove_by_cid($cid) {
        $this->db->query("DELETE FROM `charging` WHERE `cid`='$cid'");
    }

    function get_by_fromuid($fromuid, $condition="1=1", $limit = 20) {
        $charginglist = array();
        $query = $this->db->query("SELECT * FROM `charging` WHERE `fromuid`='$fromuid' and $condition ORDER BY `time` DESC LIMIT 0,$limit");
        while ($charging = $this->db->fetch_array($query)) {
            $charginglist[] = $charging;
        }
        return $charginglist;
    }

    function get_by_touid($touid, $condition="1=1", $limit = 20) {
        $charginglist = array();
        $query = $this->db->query("SELECT * FROM `charging` WHERE `touid`='$touid' and $condition ORDER BY `time` DESC LIMIT 0,$limit");
        while ($charging = $this->db->fetch_array($query)) {
            $charginglist[] = $charging;
        }
        return $charginglist;
    }

    function add($pid, $fromuid, $from, $touid, $to, $system, $price, $status=CHARGING_STATUS_DEAL) {
        $this->db->query("REPLACE INTO charging(`pid`,`fromuid`,`from`,`touid`,`to`,`system`,`price`,`status`,`time`) VALUES ('$pid','$fromuid','$from','$touid','$to','$system','$price','$status',{$this->base->time})");
        return $this->db->insert_id();
    }

    function update_status_by_cid($cid, $status=CHARGING_STATUS_SUCCEED) {
        $this->db->query("UPDATE charging SET `status`=$status WHERE `cid`=$cid");
        return $this->db->affected_rows();
    }

    function update_status_by_pid($pid, $status=CHARGING_STATUS_SUCCEED) {
        $this->db->query("UPDATE charging SET `status`=$status WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }
}

?>
