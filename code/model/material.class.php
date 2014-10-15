<?php

!defined('IN_SITE') && exit('Access Denied');

class materialmodel {
    var $db;
    var $base;
    var $search;
    var $index;

    function materialmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
        if ($this->base->setting['xunsearch_open']) {
            require_once $this->base->setting['xunsearch_sdk_file'];
            $xs = new XS('material');
            $this->search = $xs->search;
            $this->index = $xs->index;
        }
    }

    // 获取问题信息
    function get($mid) {
        $material = $this->db->fetch_first("SELECT * FROM material WHERE id='$mid'");
        if ($material) {
            $material['format_time'] = tdate($material['time']);
            $material['user_avartar'] = get_avatar_dir($material['uid']);
        }
        return $material;
    }

    function get_list($start=0, $limit='') {
        $sql = "SELECT * FROM `material` WHERE 1=1 ";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $sql .= " ORDER BY `time` DESC";

        $query = $this->db->query($sql);
        $material_list = array();
        while ($material = $this->db->fetch_array($query)) {
            $material['format_time'] = tdate($material['time']);
            $material_list[] = $material;
        }
        return $material_list;
    }

    function get_all_material_num() {
        return $this->db->fetch_total('material');
    }

    // 前台问题搜索
    function list_by_condition($condition, $start = 0, $limit = 10) {
        $material_list = array();
        $query = $this->db->query("SELECT * FROM `material` WHERE $condition order by time desc limit $start, $limit");
        while ($material = $this->db->fetch_array($query)) {
            $material['format_time'] = tdate($material['time']);
            $material_list[] = $material;
        }
        return $material_list;
    }

    function add($uid, $username, $title, $description, $price, $status=MATERIAL_STATUS_APPLY) {
        $this->db->query("INSERT INTO material SET uid='$uid',username='$username',title='$title',description='$description',price='$price',time='{$this->base->time}',status='$status'");
        return $this->db->insert_id();
    }

    function update_avg_score($mid, $avg_score) {
        $this->db->query("UPDATE `material` SET `avg_score`='$avg_score' WHERE `id`=$mid");
    }

    function update_sold_num($mid, $delta=1) {
        $this->db->query("UPDATE `material` SET `sold_num`=`sold_num`+($delta) WHERE `id`=$mid");
    }

    // 我的所有求助，用户中心
    function list_by_uid($uid, $start=0, $limit=10) {
        $material_list = array();
        $sql = "SELECT * FROM material WHERE `uid`=$uid ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($material = $this->db->fetch_array($query)) {
            $material['format_time'] = tdate($material['time']);
            $material_list[] = $material;
        }
        return $material_list;
    }

    // 更新求助
    function update($mid, $title, $description, $price, $status) {
        $this->db->query("UPDATE `material` SET `title`='$title',`description`='$description',`price`='$price',`status`=$status,`time`={$this->base->time} WHERE `id`=$mid");
        if ($this->base->setting['xunsearch_open']) { // 更新索引
            $problem = array();
            $problem['pid'] = $pid;
            $problem['status'] = $status;
            $problem['title'] = $title;
            $problem['description'] = $description;
            $doc = new XSDocument;
            $doc->setFields($problem);
            $this->index->update($doc);
        }
    }

    // 更新求助状态
    function update_status($mid, $status) {
        $this->db->query("UPDATE `material` set `status`=$status WHERE `id`=$mid");
    }

    function makeindex() {
        // TO BE finished
        return;
        if ($this->base->setting['xunsearch_open']) {
            $this->index->clean();
            $query = $this->db->query("SELECT * FROM problem ");
            while ($problem = $this->db->fetch_array($query)) {
                $data = array();
                $data['id'] = $problem['pid'];
                $data['author'] = $problem['author'];
                $data['authorid'] = $problem['authorid'];
                $data['status'] = $problem['status'];
                $data['time'] = $problem['time'];
                $data['title'] = $problem['title'];
                $data['description'] = $problem['description'];
                $doc = new XSDocument;
                $doc->setFields($data);
                $this->index->add($doc);
            }
        }
    }
}

?>