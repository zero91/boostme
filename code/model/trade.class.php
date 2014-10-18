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

    function get_trade_by_uid_status($uid, $status) {
        return $this->db->fetch_first("SELECT * FROM `trade` WHERE `uid`='$uid' AND `status`='$status'");
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

    function add_trade($trade_no, $uid, $username, $tot_price, $goods_num, $status=TRADE_STATUS_UNPAID) {
        $this->db->query("INSERT INTO `trade` SET trade_no='$trade_no',uid='$uid',username='$username',tot_price='$tot_price',goods_num='$goods_num',time='{$this->base->time}',status='$status'");
        return $this->db->insert_id();
    }

    function update_trade($trade_no, $tot_price, $goods_num, $status=TRADE_STATUS_UNPAID) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price',`goods_num`='$goods_num',`status`='$status' WHERE `trade_no`='$trade_no'");
    }

    function update_trade_tot_price($trade_no, $tot_price) {
        $this->db->query("UPDATE `trade` SET `tot_price`='$tot_price' WHERE `trade_no`='$trade_no'");
    }

    function update_trade_status($trade_no, $status) {
        $this->db->query("UPDATE `trade` SET `status`='$status' WHERE `trade_no`='$trade_no'");
    }

    function add_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $this->db->query("REPLACE INTO `trade_info`(`trade_no`,`mid`,`title`,`price`,`buy_num`,`time`) VALUES ('$trade_no','$mid','$title','$price','$buy_num','{$this->base->time}')");
    }

    function update_trade_info($trade_no, $mid, $title, $price, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `title`='$title',`price`='$price',`buy_num`='$buy_num',`time`='{$this->base->time}' WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
    }

    function update_trade_info_buy_num($trade_no, $mid, $buy_num) {
        $this->db->query("UPDATE `trade_info` SET `buy_num`='$buy_num' WHERE `trade_no`='$trade_no' AND `mid`='$mid')");
    }

    function remove_trade_info($trade_no, $mid) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no' AND `mid`='$mid'");
    }

    function remove_trade_info_by_trade_no($trade_no) {
        $this->db->query("DELETE FROM `trade_info` WHERE `trade_no`='$trade_no'");
    }
}

?>
