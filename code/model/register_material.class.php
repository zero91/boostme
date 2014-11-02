<?php

!defined('IN_SITE') && exit('Access Denied');

class register_materialmodel {
    var $db;
    var $base;

    function register_materialmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_uid() {
        $query = $this->db->query("SELECT * FROM `register_material` ORDER BY `time` ASC");
        $reg_list = array();
        while ($reg = $this->db->fetch_array($query)) {
            $reg['avatar'] = get_avatar_dir($reg['uid']);
            $reg['format_time'] = tdate($reg['time']);
            $reg_list[] = $reg;
        }
        return $reg_list;
    }

    function get_list($start=0, $limit=10, $existed="0") {
        $query = $this->db->query("SELECT * FROM `register_material` WHERE `existed` IN ($existed) ORDER BY `time` ASC limit $start,$limit");

        $reg_list = array();
        while ($reg = $this->db->fetch_array($query)) {
            $reg['avatar'] = get_avatar_dir($reg['uid']);
            $reg['format_time'] = tdate($reg['time']);
            $reg_list[] = $reg;
        }
        return $reg_list;
    }

    function get_total_num($existed="0") {
        return $this->db->fetch_total("register_material", " existed IN ($existed)");
    }

    function add($description) {
        $this->db->query("INSERT INTO register_material(`uid`,`username`,`description`,`time`,`existed`) VALUES ('{$this->base->user['uid']}','{$this->base->user['username']}','$description','{$this->base->time}','0')");
        return $this->db->insert_id();
    }

    function update_material_existed($id, $existed=1) {
        $this->db->query("UPDATE `register_material` SET `existed`=$existed WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    function remove_by_ids($ids) {
        $this->db->query("DELETE FROM `register_material` WHERE `id` IN ($ids)");
        return $this->db->affected_rows();
    }

    function remove_by_uids($uids) {
        $this->db->query("DELETE FROM `register_material` WHERE `uid` IN ($uids)");
        return $this->db->affected_rows();
    }
}

?>
