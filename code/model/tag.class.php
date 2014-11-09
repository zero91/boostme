<?php

!defined('IN_SITE') && exit('Access Denied');

class tagmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_pid($pid, $limit='') {
        $sql = "SELECT DISTINCT name FROM `problem_tag` WHERE pid=$pid ORDER BY `time` ASC";
        !empty($limit) && $sql .=  " LIMIT 0,$limit ";

        $tag_array = $this->db->fetch_all($sql);
        $taglist = array();
        foreach ($tag_array as $tag) {
            $taglist[] = $tag['name'];
        }
        return $taglist;
    }

    public function list_by_name($name) {
        $tag_array = $this->db->fetch_all("SELECT pid FROM `problem_tag` WHERE name='$name'");
        $pidlist = array();
        foreach ($tag_array as $tag) {
            $pidlist[] = $tag['pid'];
        }
        return $pidlist;
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT count(pid) as problem,name FROM problem_tag GROUP BY name ORDER BY problem DESC LIMIT $start,$limit");
    }

    public function rownum() {
        $query = $this->db->query("SELECT count(name) FROM problem_tag GROUP BY name");
        return $this->db->num_rows($query);
    }

    public function multi_add($name_list, $pid, $keep_old=true) {
        if (empty($namelist)) {
            return false;
        }

        $old_name_list = array();
        if (!$keep_old) {
            $this->db->query("DELETE FROM problem_tag WHERE pid=$pid");
        } else {
            $old_name_list = $this->get_by_pid($pid);
        }

        $insertsql = "INSERT INTO problem_tag(`pid`,`name`,`time`) VALUES ";
        $add_num = 0;
        foreach ($namelist as $name) {
            if (!in_array($name, $old_name_list)) {
                $insertsql .= "($pid,'$name'," . time() . "),";
                $add_num += 1;
            }
        }
        if ($add_num > 0) {
            $this->db->query(substr($insertsql, 0, -1));
        }
        return $add_num;
    }

    public function remove_by_name($name_list) {
        $namestr = "'" . implode("','", $name_list) . "'";
        $this->db->query("DELETE FROM problem_tag WHERE `name` IN ($namestr)");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
