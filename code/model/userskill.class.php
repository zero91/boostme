<?php

!defined('IN_SITE') && exit('Access Denied');

class userskillmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_uid($uid, $limit='') {
        $sql = "SELECT DISTINCT skill FROM `user_skill` WHERE `uid`='$uid' ORDER BY `time` ASC";
        !empty($limit) && $sql .= " LIMIT 0,$limit ";

        $skill_array = $this->db->fetch_all($sql);
        $skill_list = array();
        foreach ($skill_array as $skill) {
            $skill_list[] = $skill['skill'];
        }
        return $skill_list;
    }

    public function list_by_skill($skill) {
        $skill_array = $this->db->fetch_all("SELECT * FROM `user_skill` WHERE `skill`='$skill'");

        $uidlist = array();
        foreach ($skill_array as $skill) {
            $uidlist[] = $skill['uid'];
        }
        return $uidlist;
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT count(uid) as user,skill FROM user_skill GROUP BY skill ORDER BY user DESC LIMIT $start,$limit");
    }

    // 系统用户总共拥有技能数量
    public function rownum() {
        return $this->db->result_first("SELECT COUNT(DISTINCT skill) FROM user_skill");
    }

    public function remove_user_skill($skill_list, $uid) {
        if (empty($skill_list)) {
            return false;
        }

        $skillstr = "'" . implode("','", $skill_list) . "'";
        $this->db->query("DELETE FROM user_skill WHERE `uid`='$uid' AND `skill` IN ($skillstr)");

        return $this->db->affected_rows();
    }

    public function multi_add($skill_list, $uid, $keep_old=true) {
        if (empty($skill_list)) {
            return false;
        }

        $old_skill_list = array();
        if ($keep_old) {
            $old_skill_list = $this->get_by_uid($uid);
        } else {
            $this->db->query("DELETE FROM `user_skill` WHERE `uid`='$uid'");
        }

        $insertsql = "INSERT INTO user_skill(`uid`,`skill`,`time`) VALUES ";
        $add_num = 0;
        foreach ($skill_list as $skill) {
            if (!in_array($skill, $old_skill_list)) {
                $insertsql .= "($uid,'$skill'," . time() . "),";
                $add_num += 1;
            }
        }

        if ($add_num > 0) {
            $this->db->query(substr($insertsql, 0, -1));
        }
        return $add_num;
    }

    public function remove_by_skill($skill_list) {
        $skillstr = "'" . implode("','", $skill_list) . "'";
        $this->db->query("DELETE FROM user_skill WHERE `skill` IN ($skillstr)");
    }

    private $db;
}

?>
