<?php

!defined('IN_SITE') && exit('Access Denied');

class categorymodel {
    var $db;
    var $base;

    function categorymodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_list() {
        $query = $this->db->query("SELECT * FROM category");
        $category_list = array();
        while ($category = $this->db->fetch_array($query)) {
            $category_list[$category['cid']] = $category;
        }
        return $category_list;
    }

    function get_by_cid($cid) {
        return $this->db->fetch_first("SELECT * FROM category WHERE cid='$cid'");
    }
}

?>
