<?php
namespace Home\Model;
use Think\Model;

class TradeModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array(
        //array('content', '1,512', -1, self::EXISTS_VALIDATE, 'length'), // 内容长度不合法
    );

    public function history($uid, $page = 1, $field = true) {
        $num_per_page = C('TRADE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $trade_list = $this->field($field)
                           ->where(array("uid" => $uid, "status" => array("NEQ", 0)))
                           ->order("create_time DESC")
                           ->limit($start, $num_per_page)
                           ->select();
        foreach ($trade_list as &$trade) {
            $trade['info'] = D('TradeInfo')->detail($uid, $trade['id']);
        }
        return $trade_list;
    }

    public function generate_id($uid) {
        $condition = array(
            "uid" => $uid,
            "status" => array("IN", array(0, TRADE_STATUS_WAIT_BUYER_PAY))
        );

        $trade_id = $this->where($condition)->getField("id");
        if (isset($trade_id)) {
            return $trade_id;
        }

        if ($this->create(array("uid" => $uid, "username" => get_username()))) {
            $trade_id = $this->add();
            return $trade_id ? $trade_id : 0;
        }
        return -1;
    }
}
