<?php

!defined('IN_SITE') && exit('Access Denied');

class demandmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_pid($pid, $start=0, $limit=10) {
        return $this->db->query("SELECT * FROM `demand` WHERE pid=$pid ORDER BY `time` ASC LIMIT $start,$limit");
    }

    public function get_demand_num_by_uid($pid) {
        return $this->db->fetch_total("demand", "`pid`='$pid'");
    }

    public function get_wait_demands($pid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `demand` WHERE pid=$pid AND `result`=" . DEMAND_STATUS_QUEUE . " ORDER BY `time` ASC LIMIT $start,$limit");
    }

    public function get_accept_demand($pid) {
        return $this->db->fetch_all("SELECT * FROM `demand` WHERE pid=$pid AND `result`=" . DEMAND_STATUS_ACCEPT);
    }

    public function list_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM `demand` WHERE uid='$uid'");
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT count(uid) as uids,pid FROM demand GROUP BY pid ORDER BY uids DESC LIMIT $start,$limit");
    }

    public function remove_by_uid($uids) {
        $uidstr = "'" . implode("','", $uids) . "'";
        $this->db->query("DELETE FROM `demand` WHERE `uid` IN ($uidstr)");
        return $this->db->affected_rows();
    }

    public function remove_by_uid_pid($uid, $pid) {
        $this->db->query("DELETE FROM `demand` WHERE `uid`=$uid AND `pid`=$pid");
        return $this->db->affected_rows();
    }

    public function add($uid, $username, $pid, $message=DEFAULT_DEMAND_MESSAGE) {
        $this->db->query("INSERT INTO demand(`uid`,`username`,`pid`,`time`,`result`,`message`) VALUES ('$uid','$username','$pid'," . time() . "," . DEMAND_STATUS_QUEUE . ",'$message')");
        return $this->db->insert_id();
    }

    public function already_demand($uid, $pid) {
        $demandnum = $this->db->fetch_total("demand", "uid='$uid' AND pid='$pid'");
        return $demandnum > 0;
    }

    public function update_status($uid, $pid, $status) {
        $this->db->query("UPDATE demand SET result=$status WHERE `uid`=$uid AND `pid`=$pid");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
