<?php

!defined('IN_SITE') && exit('Access Denied');

class materialmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get($mid) {
        return $this->db->fetch_first("SELECT * FROM material WHERE id='$mid'");
    }

    public function get_list($start=0, $limit='') {
        $sql = "SELECT * FROM `material` WHERE 1=1 ORDER BY `time` DESC";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function get_by_mids($mids) {
        return $this->db->fetch_all("SELECT * FROM `material` WHERE id IN ('" . implode("','", $mids) . "')");
    }

    public function get_all_material_num() {
        return $this->db->fetch_total('material');
    }

    // 前台问题搜索
    public function list_by_condition($condition, $start = 0, $limit = 10) {
        return $this->db->fetch_all("SELECT * FROM `material` WHERE $condition ORDER BY time DESC limit $start,$limit");
    }

    public function add($uid, $username, $picture, $title, $description, $price, $site_url, $access_code, $status=MATERIAL_STATUS_APPLY) {
        $time = time();
        $this->db->query("INSERT INTO material SET uid='$uid',username='$username',picture='$picture',title='$title',description='$description',price='$price',site_url='$site_url',access_code='$access_code',time='$time',status='$status'");
        return $this->db->insert_id();
    }

    public function update_avg_score($mid, $avg_score) {
        $this->db->query("UPDATE `material` SET `avg_score`='$avg_score' WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function update_sold_num($mid, $delta=1) {
        $this->db->query("UPDATE `material` SET `sold_num`=`sold_num`+($delta) WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function update_view_num($mid, $delta=1) {
        $this->db->query("UPDATE `material` SET `view_num`=`view_num`+($delta) WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function update_access($mid, $site_url, $access_code) {
        $this->db->query("UPDATE `material` SET `site_url`='$site_url',`access_code`='$access_code' WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function get_user_total_materials($uid) {
        return $this->db->fetch_total("material", " `uid`='$uid' " );
    }

    public function list_by_uid($uid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM material WHERE `uid`=$uid ORDER BY `time` DESC LIMIT $start,$limit");
    }

    public function update($mid, $title, $description, $price, $site_url, $access_code) {
        $time = time();
        $this->db->query("UPDATE `material` SET `title`='$title',`description`='$description',`price`='$price',`site_url`='$site_url',`access_code`='$access_code',`time`=$time WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function update_picture($mid, $picture) {
        $this->db->query("UPDATE `material` set `picture`='$picture' WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    // 更新求助状态
    public function update_status($mid, $status) {
        $this->db->query("UPDATE `material` set `status`=$status WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    public function update_auto_delivery($mid, $auto_delivery) {
        $this->db->query("UPDATE `material` set `auto_delivery`=$auto_delivery WHERE `id`=$mid");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
