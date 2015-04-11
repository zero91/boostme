<?php

!defined('IN_SITE') && exit('Access Denied');

class servicemodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_id($id) {
        $service = $this->db->fetch_first("SELECT * FROM service WHERE id='$id'");
        !empty($service) && $service['format_time'] = tdate($service['time']);
        return $service;
    }

    public function get_by_uid($uid) {
        $service_list = $this->db->fetch_all("SELECT * FROM service WHERE `uid`='$uid'");
        foreach ($service_list as &$service) {
            $service['format_time'] = tdate($service['time']);
        }
        return $service_list;
    }

    public function get_by_status($status, $start=0, $limit=10, $order="DESC") {
        $sql = "SELECT * FROM service WHERE `status`='$status' ORDER BY `time` $order " .
               " LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function get_status_num($status) {
        return $this->db->fetch_total("service", "`status`='$status'");
    }

    public function get_service_num() {
        return $this->db->fetch_total('service');
    }

    public function add($uid, $username, $service_content, $service_time, $price,
                                                 $status=SERVICE_STATUS_APPLY) {
        $time = time();
        $sql = "INSERT INTO service SET uid='$uid',username='$username',price='$price'" .
               ",service_content='$service_content',service_time='$service_time'" .
               ",status='$status',time='$time'";
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    public function update_price($id, $price) {
        $this->db->query("UPDATE service SET `price`='$price' WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update_service_content($id, $service_content) {
        $sql = "UPDATE service SET `service_content`='$service_content' WHERE `id`=$id";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function update_service_time($id, $service_time) {
        $sql = "UPDATE service SET `service_time`='$service_time' WHERE `id`=$id";
        $this->db->query($sql);
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

    public function update_service_num($id, $delta=1) {
        $sql = "UPDATE service SET `service_num`=`service_num`+($delta) WHERE `id`=$id";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function update_view_num($id, $delta=1) {
        $this->db->query("UPDATE `service` SET `view_num`=`view_num`+($delta) WHERE `id`=$id");
        return $this->db->affected_rows();
    }

    public function update($id, $service_content, $service_time, $price, $status="") {
        $time = time();
        $sql = "UPDATE service SET `price`='$price'" .
                                 ",`service_content`='$service_content'" .
                                 ",`service_time`='$service_time'";
        !empty($status) && $sql .= ",`status`='$status' ";
        $sql .= " WHERE `id`='$id'";

        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    private $db;
}

?>
