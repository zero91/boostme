<?php

!defined('IN_SITE') && exit('Access Denied');

class trademodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 获取问题信息
    public function get_trade_by_trade_no($trade_no) {
        return $this->db->fetch_first("SELECT * FROM trade WHERE `trade_no`='$trade_no'");
    }

    public function get_trade_by_uid($uid) {
        return $this->db->query("SELECT * FROM `trade` WHERE `uid`='$uid'");
    }

    public function get_history_trade_num_by_uid($uid) {
        return $this->db->fetch_total("trade", "`uid`='$uid'");
    }

    public function get_detailed_trade_by_uid($uid, $start=0, $limit=10) {
        $trade_list = $this->db->query("SELECT * FROM `trade` WHERE `uid`='$uid' ORDER BY `time` DESC LIMIT $start,$limit");

        foreach ($trade_list as &$trade) {
            $trade['trade_info'] = $this->get_trade_info_by_trade_no($trade['trade_no']);
        }
        return $trade_list;
    }

    public function get_trade_by_status($status, $start='', $limit='') {
        $sql = "SELECT * FROM `trade` WHERE `status`='$status'";
        if (!empty($start) && !empty($limit)) {
            $sql .= " LIMIT $start,$limit";
        }
        return $this->db->fetch_all($sql);
    }

    public function get_trade_by_uid_status($uid, $status) {
        return $this->db->fetch_all("SELECT * FROM `trade` WHERE `uid`='$uid' AND `status`='$status'");
    }

    public function get_trade_info_by_trade_no($trade_no) {
        return $this->db->fetch_all("SELECT * FROM `trade_info` WHERE `trade_no`='$trade_no'");
    }

    public function get_trade_info_by_trade_no_mid($trade_no, $mid) {
        return $this->db->fetch_first("SELECT * FROM `trade_info` WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
    }

    public function get_trade_list($start=0, $limit='') {
        $sql = "SELECT * FROM `trade` WHERE 1=1 ";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $sql .= " ORDER BY `time` DESC";

        return $this->db->fetch_all($sql);
    }

    public function add_trade($trade_no, $uid, $username, $tot_price, $goods_num, $status=TRADE_STATUS_WAIT_BUYER_PAY) {
        $time = time();
        $this->db->query("INSERT INTO `trade` SET trade_no='$trade_no',uid='$uid',username='$username',tot_price='$tot_price',goods_num='$goods_num',time='$time',status='$status'");
        return $this->db->insert_id();
    }

    public function update_trade($trade_no, $tot_price, $goods_num, $status=TRADE_STATUS_WAIT_BUYER_PAY) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price',`goods_num`='$goods_num',`status`='$status' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function update_trade_tot_price($trade_no, $tot_price) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function update_trade_status($trade_no, $status) {
        $this->db->query("UPDATE `trade` SET `status`='$status' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function update_trade_for_succeed($trade_no, $transaction_id, $total_fee, $discount, $trade_type, $trade_mode, $status = TRADE_STATUS_FINISHED) {
        $this->db->query("UPDATE `trade` SET `transaction_id`='$transaction_id',`status`='$status',`trade_total_fee`='$total_fee', `trade_discount`='$trade_discount',`trade_type`='$trade_type',`trade_mode`='$trade_mode' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function add_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $time = time();
        $this->db->query("REPLACE INTO `trade_info`(`trade_no`,`mid`,`title`,`price`,`buy_num`,`time`) VALUES ('$trade_no','$mid','$title','$price','$buy_num','$time')");
        return $this->db->affected_rows();
    }

    public function update_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `title`='$title',`price`='$price',`buy_num`='$buy_num',`time`='$time' WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    public function update_trade_info_buy_num($trade_no, $mid, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `buy_num`='$buy_num' WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    public function remove_trade($trade_no) {
        $this->db->query("DELETE FROM `trade` WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function remove_trade_info($trade_no, $mid) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    public function remove_trade_info_by_trade_no($trade_no) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    public function get_user_mid_list($uid) {
        $mid_array = $this->db->fetch_all("SELECT DISTINCT(trade_info.mid) FROM `trade_info`,`trade` WHERE trade.uid='$uid' AND trade.trade_no=trade_info.trade_no AND trade.status IN (" . TRADE_STATUS_WAIT_SELLER_SEND_GOODS . "," . TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS . "," . TRADE_STATUS_FINISHED . ")"); 
        $mid_list = array();
        foreach ($mid_array as $mid) {
            $mid_list[] = $mid['mid'];
        }
        return $mid_list;
    }

    private $db;
}

?>
