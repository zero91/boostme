<?php

!defined('IN_SITE') && exit('Access Denied');

class demandmodel
{
    var $db;
    var $base;

    function demandmodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_pid($pid, $condition = "1", $limit = 100)
    {
        $demandlist = array();
        $query = $this->db->query("SELECT * FROM `demand` WHERE pid=$pid and $condition ORDER BY `time` ASC LIMIT 0,$limit");
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function get_wait_uids($pid, $limit=100)
    {
        return $this->get_by_pid($pid, "result=" . DEMAND_STATUS_QUEUE, $limit);
    }

    function get_accept_uid($pid, $limit=1)
    {
        return $this->get_by_pid($pid, "result=" . DEMAND_STATUS_ACCEPT, $limit);
    }

    function list_by_uid($uid)
    {
        return $this->db->fetch_first("SELECT * FROM `demand` WHERE uid='$uid'");
    }

    function get_list($start = 0, $limit = 100)
    {
        $demandlist = array();
        $query = $this->db->query("SELECT count(uid) as uids,pid FROM demand GROUP BY pid ORDER BY uids DESC LIMIT $start,$limit");
        while ($demand = $this->db->fetch_array($query)) {
            $demandlist[] = $demand;
        }
        return $demandlist;
    }

    function remove_by_uid($uids)
    {
        $uidstr= "'" . implode("','", $uids) . "'";
        $this->db->query("DELETE FROM demand WHERE `uid` IN ($uidstr)");
        return $this->db->affected_rows();
    }

    function remove_by_uid_pid($uid, $pid)
    {
        $this->db->query("DELETE FROM demand where `uid`=$uid and `pid`=$pid");
        return $this->db->affected_rows();
    }

    function add($pid, $message=DEFAULT_DEMAND_MESSAGE)
    {
        $this->db->query("INSERT INTO demand(`uid`,`username`,`pid`,`time`,`result`,`message`) VALUES ({$this->base->user['uid']},'{$this->base->user['username']}',$pid,{$this->base->time}," . DEMAND_STATUS_QUEUE . ",'$message')");
        return $this->db->insert_id();
    }

    function already_demand($pid)
    {
        $demandnum = $this->db->result_first("SELECT COUNT(*) FROM demand WHERE `uid`={$this->base->user['uid']} AND `pid`=$pid");

        if ($demandnum > 0) {
            return true;
        }
        return false;
    }

    function update_status($uid, $pid, $status=DEMAND_STATUS_QUEUE)
    {
        $this->db->query("UPDATE demand SET result=$status WHERE `uid`=$uid AND `pid`=$pid");
        return $this->db->affected_rows();
    }
}

?>
