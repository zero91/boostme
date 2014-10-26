<?php

!defined('IN_SITE') && exit('Access Denied');

class trademodel {
    var $db;
    var $base;

    function trademodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // 获取问题信息
    function get_trade_by_trade_no($trade_no) {
        $trade = $this->db->fetch_first("SELECT * FROM trade WHERE `trade_no`='$trade_no'");
        if ($trade) {
            $trade['format_time'] = tdate($trade['time']);
        }
        return $trade;
    }

    function get_trade_by_uid($uid) {
        $query = $this->db->query("SELECT * FROM `trade` WHERE `uid`='$uid'");
        $trade_list = array();
        while ($trade = $this->db->fetch_array($query)) {
            $trade['format_time'] = tdate($trade['time']);
            $trade_list[] = $trade;
        }
        return $trade_list;
    }

    function get_history_trade_num_by_uid($uid) {
        return $this->db->result_first("SELECT COUNT(*) FROM `trade` WHERE `uid`='$uid'");
    }

    function get_detailed_trade_by_uid($uid, $start=0, $limit=10) {
        $query = $this->db->query("SELECT * FROM `trade` WHERE `uid`='$uid' ORDER BY `time` DESC LIMIT $start,$limit");
        $trade_list = array();
        while ($trade = $this->db->fetch_array($query)) {
            $trade['format_time'] = tdate($trade['time']);
            $trade['trade_info'] = $this->get_trade_info_by_trade_no($trade['trade_no']);
            $trade_list[] = $trade;
        }
        return $trade_list;
    }

    function get_trade_by_status($status, $start='', $limit='') {
        $sql = "SELECT * FROM `trade` WHERE `status`='$status'";
        if (!empty($start) && !empty($limit)) {
            $sql .= " LIMIT $start,$limit";
        }
        $query = $this->db->query($sql);
        $trade_list = array();
        while ($trade = $this->db->fetch_array($query)) {
            $trade['format_time'] = tdate($trade['time']);
            $trade_list[] = $trade;
        }
        return $trade_list;
    }

    function get_trade_by_uid_status($uid, $status) {
        $query = $this->db->query("SELECT * FROM `trade` WHERE `uid`='$uid' AND `status`='$status'");

        $trade_list = array();
        while ($trade = $this->db->fetch_array($query)) {
            $trade['format_time'] = tdate($trade['time']);
            $trade_list[] = $trade;
        }
        return $trade_list;
    }

    function get_trade_info_by_trade_no($trade_no) {
        $query = $this->db->query("SELECT * FROM `trade_info` WHERE `trade_no`='$trade_no'");
        $trade_info_list = array();
        while ($trade_info = $this->db->fetch_array($query)) {
            $trade_info['format_time'] = tdate($trade_info['time']);
            $trade_info_list[] = $trade_info;
        }
        return $trade_info_list;
    }

    function get_trade_info_by_trade_no_mid($trade_no, $mid) {
        return $this->db->fetch_first("SELECT * FROM `trade_info` WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
    }

    function get_trade_list($start=0, $limit='') {
        $sql = "SELECT * FROM `trade` WHERE 1=1 ";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $sql .= " ORDER BY `time` DESC";

        $query = $this->db->query($sql);
        $trade_list = array();
        while ($trade = $this->db->fetch_array($query)) {
            $trade['format_time'] = tdate($trade['time']);
            $trade_list[] = $trade;
        }
        return $trade_list;
    }

    function add_trade($trade_no, $uid, $username, $tot_price, $goods_num, $status=TRADE_STATUS_WAIT_BUYER_PAY) {
        $this->db->query("INSERT INTO `trade` SET trade_no='$trade_no',uid='$uid',username='$username',tot_price='$tot_price',goods_num='$goods_num',time='{$this->base->time}',status='$status'");
        return $this->db->insert_id();
    }

    function update_trade($trade_no, $tot_price, $goods_num, $status=TRADE_STATUS_WAIT_BUYER_PAY) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price',`goods_num`='$goods_num',`status`='$status' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function update_trade_tot_price($trade_no, $tot_price) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function update_trade_status($trade_no, $status) {
        $this->db->query("UPDATE `trade` SET `status`='$status' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function update_trade_for_succeed($trade_no, $transaction_id, $total_fee, $discount, $trade_type, $trade_mode, $status = TRADE_STATUS_FINISHED) {
        $this->db->query("UPDATE `trade` SET `transaction_id`='$transaction_id',`status`='$status',`trade_total_fee`='$total_fee', `trade_discount`='$trade_discount',`trade_type`='$trade_type',`trade_mode`='$trade_mode' WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function add_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $this->db->query("REPLACE INTO `trade_info`(`trade_no`,`mid`,`title`,`price`,`buy_num`,`time`) VALUES ('$trade_no','$mid','$title','$price','$buy_num','{$this->base->time}')");
        return $this->db->affected_rows();
    }

    function update_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `title`='$title',`price`='$price',`buy_num`='$buy_num',`time`='{$this->base->time}' WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    function update_trade_info_buy_num($trade_no, $mid, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `buy_num`='$buy_num' WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    function remove_trade($trade_no) {
        $this->db->query("DELETE FROM `trade` WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function remove_trade_info($trade_no, $mid) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
        return $this->db->affected_rows();
    }

    function remove_trade_info_by_trade_no($trade_no) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no'");
        return $this->db->affected_rows();
    }

    function get_user_mid_list($uid) {
        $query = $this->db->query("SELECT DISTINCT(trade_info.mid) FROM `trade_info`,`trade` WHERE trade.uid='$uid' AND trade.trade_no=trade_info.trade_no AND trade.status IN (" . TRADE_STATUS_WAIT_SELLER_SEND_GOODS . "," . TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS . "," . TRADE_STATUS_FINISHED . ")"); 
        $mid_list = array();
        while ($mid = $this->db->fetch_array($query)) {
            $mid_list[] = $mid['mid'];
        }
        return $mid_list;
    }
}

?>
