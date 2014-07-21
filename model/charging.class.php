<?php

!defined('IN_SITE') && exit('Access Denied');

/*
CREATE TABLE charging (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromuid` int(10) NOT NULL,
  `from` char(18) NOT NULL,
  `touid` int(10) DEFAULT NULL,
  `to` char(18) DEFAULT NULL,
  `system` int(10) NOT NULL DEFAULT '0',
  `price` smallint(6) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`skill`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class chargingmodel
{
    var $db;
    var $base;

    function chargingmodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_pid($pid)
    {
        return $this->db->fetch_first("SELECT * FROM `charging` WHERE `pid`='$pid'");
    }

    function get_by_cid($cid)
    {
        return $this->db->fetch_first("SELECT * FROM `charging` WHERE `cid`='$cid'");
    }

    function get_by_fromuid($fromuid, $condition = "1", $limit = 20)
    {
        $charginglist = array();
        $query = $this->db->query("SELECT * FROM `charging` WHERE `fromuid`='$fromuid' and $condition ORDER BY `time` DESC LIMIT 0,$limit");
        while ($charging = $this->db->fetch_array($query)) {
            $charginglist[] = $charging;
        }
        return $charginglist;
    }

    function get_by_touid($touid, $condition = "1", $limit = 20)
    {
        $charginglist = array();
        $query = $this->db->query("SELECT * FROM `charging` WHERE `touid`='$touid' and $condition ORDER BY `time` DESC LIMIT 0,$limit");
        while ($charging = $this->db->fetch_array($query)) {
            $charginglist[] = $charging;
        }
        return $charginglist;
    }

    function add($pid, $fromuid, $from, $touid, $to, $system, $price)
    {
        $this->db->query("REPLACE INTO charging(`pid`, `fromuid`,`from`,`touid`,`to`,`system`,`price`,`time`) VALUES ('$pid','$fromuid','$from','$touid','$to','$system','$price', {$this->base->time})");
        return $this->db->insert_id();
    }

    function update_status_by_cid($cid, $status=CHARGING_STATUS_DEAL)
    {
        $this->db->query("UPDATE charging SET `status`=$status WHERE `cid`=$cid");
        return $this->db->affected_rows();
    }

    function update_status_by_pid($pid, $status=CHARGING_STATUS_DEAL)
    {
        $this->db->query("UPDATE charging SET `status`=$status WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }
}

?>
