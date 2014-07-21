<?php

!defined('IN_SITE') && exit('Access Denied');
/*
CREATE TABLE user_skill (
  `uid` int(10) NOT NULL,
  `skill` varchar(20) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `verified` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`skill`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/

class userskillmodel
{
    var $db;
    var $base;

    function userskillmodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid, $limit=10)
    {
        $skill_list = array();
        $query = $this->db->query("SELECT DISTINCT skill FROM `user_skill` WHERE uid=$uid ORDER BY `time` ASC LIMIT 0,$limit");
        while ($skill = $this->db->fetch_array($query)) {
            $skill_list[] = $skill['skill'];
        }
        return $skill_list;
    }

    function list_by_skill($skill)
    {
        return $this->db->fetch_first("SELECT * FROM `user_skill` WHERE `skill`='$skill'");
    }

    function get_list($start = 0, $limit = 100)
    {
        $skill_list = array();
        $query = $this->db->query("SELECT count(uid) as user,skill FROM user_skill GROUP BY skill ORDER BY user DESC LIMIT $start,$limit");
        while ($skill = $this->db->fetch_array($query)) {
            $skill_list[] = $skill;
        }
        return $skill_list;
    }

    function rownum()
    {
        $query = $this->db->query("SELECT count(skill) FROM user_skill GROUP BY skill");
        return $this->db->num_rows($query);
    }

    function multi_add($skill_list, $uid)
    {
        if (empty($skill_list)) {
            return false;
        }
        $this->db->query("DELETE FROM user_skill WHERE uid=$uid");
        $insertsql = "INSERT INTO user_skill(`uid`,`skill`,`time`) VALUES ";
        
        foreach ($skill_list as $skill) {
            $insertsql .= "($uid,'$skill',{$this->base->time}),";
        }
        $this->db->query(substr($insertsql, 0, -1));
    }

    function remove_by_skill($skills)
    {
        $skillstr = "'" . implode("','", $skills) . "'";
        $this->db->query("DELETE FROM user_skill WHERE `skills` IN ($skillstr)");
    }
}

?>
