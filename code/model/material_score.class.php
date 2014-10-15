<?php

!defined('IN_SITE') && exit('Access Denied');
/*
CREATE TABLE material_score (
  `uid` int(10) unsigned NOT NULL,
  `mid` int(10) NOT NULL,
  `score` smallint(3) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`mid`),
  KEY `uid`(`uid`),
  KEY `mid`(`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
*/

class material_scoremodel {
    var $db;
    var $base;

    function material_scoremodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid_mid($uid, $mid) {
        return $this->db->fetch_first("SELECT * FROM `material_score` WHERE uid='$uid' AND mid='$mid'");
    }

    function get_avg_by_mid($mid) {
        return $this->db->result_first("SELECT AVG(`score`) FROM `material_score` WHERE mid='$mid'");
    }

    function get_all_by_mid($mid) {
        $scorelist = array();
        $query = $this->db->query("SELECT * FROM `material_score` WHERE mid=$mid ORDER BY `time` DESC");
        while ($score = $this->db->fetch_array($query)) {
            $scorelist[] = $score;
        }
        return $scorelist;
    }

    function get_by_mid($mid, $start=0, $limit=10) {
        $scorelist = array();
        $query = $this->db->query("SELECT * FROM `material_score` WHERE mid=$mid ORDER BY `time` DESC limit $start,$limit");
        while ($score = $this->db->fetch_array($query)) {
            $scorelist[] = $score;
        }
        return $scorelist;
    }

    function add($uid, $mid, $score) {
        $this->db->query("INSERT INTO `material_score`(`uid`,`mid`,`score`,`time`) values ('$uid','$mid','$score','{$this->base->time}')");
        return $this->db->insert_id();
    }

    function remove_by_uid_mid($uid, $mid) {
        $this->db->query("DELETE FROM material_score WHERE `uid`='$uid' AND `mid`='$mid'");
    }

    function remove_by_mid($mid) {
        $this->db->query("DELETE FROM material_score WHERE `mid`='$mid'");
    }
}

?>
