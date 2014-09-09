<?php

!defined('IN_SITE') && exit('Access Denied');

class userskillmodel {
    var $db;
    var $base;

    function userskillmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid($uid, $limit='') {
        $sql = "SELECT DISTINCT skill FROM `user_skill` WHERE uid=$uid ORDER BY `time` ASC";
        !empty($limit) && $sql .= " LIMIT 0,$limit ";

        $query = $this->db->query($sql);
        $skill_list = array();
        while ($skill = $this->db->fetch_array($query)) {
            $skill_list[] = $skill['skill'];
        }
        return $skill_list;
    }

    function list_by_skill($skill) {
        $query = $this->db->query("SELECT * FROM `user_skill` WHERE `skill`='$skill'");
        $uidlist = array();
        while ($skill = $this->db->fetch_array($query)) {
            $uidlist[] = $skill['uid'];
        }
        return $uidlist;
    }

    function get_list($start=0, $limit=10) {
        $skill_list = array();
        $query = $this->db->query("SELECT count(uid) as user,skill FROM user_skill GROUP BY skill ORDER BY user DESC LIMIT $start,$limit");
        while ($skill = $this->db->fetch_array($query)) {
            $skill_list[] = $skill;
        }
        return $skill_list;
    }

    function rownum() {
        $query = $this->db->query("SELECT count(skill) FROM user_skill GROUP BY skill");
        return $this->db->num_rows($query);
    }

    function multi_add($skill_list, $uid) {
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

    function remove_by_skill($skills) {
        $skillstr = "'" . implode("','", $skills) . "'";
        $this->db->query("DELETE FROM user_skill WHERE `skills` IN ($skillstr)");
    }
}

?>
