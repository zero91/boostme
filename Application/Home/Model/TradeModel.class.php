<?php
namespace Home\Model;
use Think\Model;

class TradeModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        //array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('update_time', 'time', self::MODEL_BOTH, 'function'),
    );

    protected $_validate = array(
        //array('content', '1,512', -1, self::EXISTS_VALIDATE, 'length'), // 内容长度不合法
    );

    public function history($uid, $page = 1, $field = true) {
        $num_per_page = C('TRADE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;

        $trade_list = $this->field($field)
                           ->where(array("uid" => $uid, "status" => array("NEQ", 0)))
                           ->order("update_time DESC")
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

    public function recalc($trade_id) {
        $trade_info_list = D('TradeInfo')->field(true)->where(array("trade_id" => $trade_id))->select();
        $tot = 0.0;
        foreach ($trade_info_list as &$trade_info) {
            $tot += $trade_info['item_price'] * $trade_info['quantity'];
        }
        $this->create(array("id" => $trade_id, "price" => $tot));
        return $this->save();
    }
}
