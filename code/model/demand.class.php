<?php

!defined('IN_SITE') && exit('Access Denied');

class demandmodel {
    var $db;
    var $base;

    function demandmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_pid($pid, $start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM `demand` WHERE pid=$pid ORDER BY `time` ASC LIMIT $start,$limit");
        $demandlist = array();
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function get_demand_num_by_uid($pid) {
        return $this->db->result_first("SELECT count(*) FROM `demand` WHERE pid=$pid");
    }

    function get_wait_demands($pid, $start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM `demand` WHERE pid=$pid AND `result`=" . DEMAND_STATUS_QUEUE . " ORDER BY `time` ASC LIMIT $start,$limit");
        $demandlist = array();
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function get_accept_demand($pid) {
        $query = $this->db->query("SELECT * FROM `demand` WHERE pid=$pid AND `result`=" . DEMAND_STATUS_ACCEPT);

        $accept_list = array();
        while ($accept = $this->db->fetch_array($query)) {
            $accept_list[] = $accept;
            break;
        }
        return $accept_list;
    }

    function list_by_uid($uid) {
        $query = $this->db->query("SELECT * FROM `demand` WHERE uid='$uid'");
        $demandlist = array();
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function get_list($start=0, $limit=10) {
        $demandlist = array();
        $query = $this->db->query("SELECT count(uid) as uids,pid FROM demand GROUP BY pid ORDER BY uids DESC LIMIT $start,$limit");
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function remove_by_uid($uids) {
        $uidstr= "'" . implode("','", $uids) . "'";
        $this->db->query("DELETE FROM `demand` WHERE `uid` IN ($uidstr)");
        return $this->db->affected_rows();
    }

    function remove_by_uid_pid($uid, $pid) {
        $this->db->query("DELETE FROM `demand` WHERE `uid`=$uid AND `pid`=$pid");
        return $this->db->affected_rows();
    }

    function add($pid, $message=DEFAULT_DEMAND_MESSAGE) {
        $this->db->query("INSERT INTO demand(`uid`,`username`,`pid`,`time`,`result`,`message`) VALUES ({$this->base->user['uid']},'{$this->base->user['username']}',$pid,{$this->base->time}," . DEMAND_STATUS_QUEUE . ",'$message')");
        return $this->db->insert_id();
    }

    function already_demand($pid) {
        $demandnum = $this->db->result_first("SELECT COUNT(*) FROM `demand` WHERE `uid`={$this->base->user['uid']} AND `pid`=$pid");
        return $demandnum > 0;
    }

    function update_status($uid, $pid, $status=DEMAND_STATUS_QUEUE) {
        $this->db->query("UPDATE demand SET result=$status WHERE `uid`=$uid AND `pid`=$pid");
        return $this->db->affected_rows();
    }
}

?>
