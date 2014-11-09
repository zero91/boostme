<?php

!defined('IN_SITE') && exit('Access Denied');

class servicemodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_id($id) {
        return $this->db->fetch_first("SELECT * FROM service WHERE id='$id'");
    }

    public function get_by_uid($uid) {
        return $this->db->fetch_all("SELECT * FROM service WHERE `uid`='$uid'");
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM service LIMIT $start,$limit ORDER BY `time` DESC");
    }

    public function get_service_num() {
        return $this->db->fetch_total('service');
    }

    public function add($uid, $username, $picture, $price, $profile, $status=0) {
        $time = time();
        $this->db->query("INSERT INTO service SET uid='$uid',username='$username',picture='$picture',price='$price',profile='$profile',status='$status'");
        return $this->db->insert_id();
    }

    public function update_picture($id, $picture) {
        $this->db->query("UPDATE service set `picture`='$picture' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_price($id, $price) {
        $this->db->query("UPDATE service SET `price`='$price' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_profile($id, $profile) {
        $this->db->query("UPDATE service SET `profile`='$profile' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_status($id, $status) {
        $this->db->query("UPDATE service SET `status`='$status' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_avg_score($id, $avg_score) {
        $this->db->query("UPDATE service SET `avg_score`='$avg_score' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_service_num($id) {
        $this->db->query("UPDATE service SET `service_num`=`service_num`+1 WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update($id, $picture, $price, $profile) {
        $time = time();
        $this->db->query("UPDATE service SET `picture`='$picture',`price`='$price',`profile`='$profile' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
