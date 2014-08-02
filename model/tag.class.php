<?php

!defined('IN_SITE') && exit('Access Denied');

class tagmodel {
    var $db;
    var $base;

    function tagmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_pid($pid, $limit=10) {
        $taglist = array();
        $query = $this->db->query("SELECT DISTINCT name FROM `problem_tag` WHERE pid=$pid ORDER BY `time` ASC LIMIT 0,$limit");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag['name'];
        }
        return $taglist;
    }

    function list_by_name($name) {
        return $this->db->fetch_first("SELECT * FROM `problem_tag` WHERE name='$name'");
    }

    function get_list($start=0, $limit=100) {
        $taglist = array();
        $query = $this->db->query("SELECT count(pid) as problem,name FROM problem_tag GROUP BY name ORDER BY problem DESC LIMIT $start,$limit");
        while ($tag = $this->db->fetch_array($query)) {
            $taglist[] = $tag;
        }
        return $taglist;
    }

    function rownum() {
        $query = $this->db->query("SELECT count(name) FROM problem_tag GROUP BY name");
        return $this->db->num_rows($query);
    }

    function multi_add($namelist, $pid) {
        if (empty($namelist)) {
            return false;
        }
        $this->db->query("DELETE FROM problem_tag WHERE pid=$pid");
        $insertsql = "INSERT INTO problem_tag(`pid`,`name`,`time`) VALUES ";
        foreach ($namelist as $name) {
            $insertsql .= "($pid,'$name',{$this->base->time}),";
        }
        $this->db->query(substr($insertsql, 0, -1));
    }

    function remove_by_name($names) {
        $namestr = "'" . implode("','", $names) . "'";
        $this->db->query("DELETE FROM problem_tag WHERE `name` IN ($namestr)");
    }
}

?>
