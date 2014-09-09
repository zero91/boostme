<?php

!defined('IN_SITE') && exit('Access Denied');

class userlogmodel {
    var $db;
    var $base;

    function userlogmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_type($type, $start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM `userlog` WHERE `type`='$type' ORDER BY `time` DESC LIMIT $start,$limit");
        $loglist = array();
        while ($log = $this->db->fetch_array($query)) {
            $loglist[] = $log;
        }
        return $loglist;
    }

    // 添加用户操作记录
    // @param enum $type= login | problem | demand | cancel | accept | denied
    // @return int  
    function add($type, $comment='') {
        $comment = taddslashes($comment);
        $this->db->query("INSERT INTO userlog(`sid`,`uid`,`type`,`time`,`comment`) VALUES ('{$this->base->user['sid']}','{$this->base->user['uid']}','$type',{$this->base->time},'$comment')");
        return $this->db->insert_id();
    }

     // 按时间计算用户的操作次数
     // @param ENUM $type
     // @param INT $hours
     // @return INT 
    function rownum_by_time($type='problem', $hours=1) {
        $starttime = strtotime(date("Y-m-d H:00:00", $this->base->time));
        $endtime = $starttime + $hours * 3600;
        $sid = $this->base->user['sid'];
        return $this->db->fetch_total('userlog', " `time`>$starttime AND `time`<$endtime AND sid='$sid' AND type='$type'");
    }
}

?>
