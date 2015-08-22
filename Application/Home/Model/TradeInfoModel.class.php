<?php
namespace Home\Model;
use Think\Model;

class TradeInfoModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array();

    // @brief  remove  删除用户指定订单中的单个产品项
    //
    // @param  integer  $uid        用户ID
    // @param  integer  $trade_id   订单号
    // @param  integer  $item_id    产品ID号
    // @param  integer  $item_type  产品类型
    //
    // @return integer  0 - 订单中不存在该项产品
    //                 -1 - 该订单号不存在
    //                 -2 - 用户无权操作该笔订单
    //                 -3 - 该笔订单已无法更改
    //                 >0 - 删除条数
    //
    public function remove($uid, $trade_id, $item_id, $item_type) {
        $trade = D('Trade')->field("uid, status")->find($trade_id);
        if (!isset($trade)) {
            return -1; // 不存在该订单号
        }
        
        if ($trade['uid'] != $uid) {
            return -2; // 用户无权操作该笔订单
        }

        if ($trade['status'] != 0 && $trade['status'] != TRADE_STATUS_WAIT_BUYER_PAY) {
            return -3; // 该订单已无法更改
        }

        $condition = array(
            "trade_id"  => $trade_id,
            "item_id"   => $item_id,
            "item_type" => $item_type
        );
        return $this->where($condition)->delete();
    }

    // @brief  buy  用户购买某项产品，若购物车中已存在，则更新数量
    //
    // @param  integer  $uid        用户ID
    // @param  integer  $trade_id   订单号
    // @param  integer  $item_id    产品ID号
    // @param  integer  $item_type  产品类型
    // @param  integer  $quantity   购买该产品的数量
    //
    // @return integer >=0 - 更新条数  
    //                  -1 - 该订单号不存在
    //                  -2 - 用户无权操作该笔订单
    //                  -3 - 用户订单已无法更改
    //                  -4 - 创建数据失败
    //
    public function buy($uid, $trade_id, $item_id, $item_type, $quantity) {
        $trade = D('Trade')->field("uid, status")->find($trade_id);
        if (!isset($trade)) {
            return -1; // 不存在该订单号
        }
        
        if ($trade['uid'] != $uid) {
            return -2; // 用户无权操作该笔订单
        }

        if ($trade['status'] != 0 && $trade['status'] != TRADE_STATUS_WAIT_BUYER_PAY) {
            return -3; // 该订单已无法更改
        }

        $condition = array(
            "trade_id"  => $trade_id,
            "item_id"   => $item_id,
            "item_type" => $item_type
        );

        $exists = $this->where($condition)->count();
        if ($exists > 0) {
            return $this->where($condition)->setInc("quantity", $quantity);
        } else {
            $data = array(
                "uid"       => $uid,
                "username"  => get_username(),
                "trade_id"  => $trade_id,
                "item_id"   => $item_id,
                "item_type" => $item_type,
                "quantity"  => $quantity
            );
            if ($this->create($data)) {
                return $this->add();
            } else {
                return -4; // 创建数据失败
            }
        }
    }

    // @brief  detail  获取用户订单中产品列表详细信息
    //
    // @param  integer  $uid        用户ID
    // @param  integer  $trade_id   订单号
    // @param  boolean  $verbose    是否获取产品的详细描述信息
    //
    // @return array - 产品列表详细信息            OR
    //         integer -1 - 该订单号不存在         OR
    //         integer -2 - 用户无权操作该笔订单   OR
    //         integer -3 - 创建数据失败
    //
    public function detail($uid, $trade_id, $verbose = True) {
        $trade_uid = D('Trade')->where(array("id" => $trade_id))->getField("uid");
        if (!isset($trade_uid)) {
            return -1; // 不存在该订单号
        }
        
        if ($trade_uid != $uid) {
            return -2; // 用户无权操作该笔订单
        }

        $trade_info_list = $this->field(true)
                                ->order("create_time DESC")
                                ->where(array("trade_id" => $trade_id))
                                ->select();
        if ($verbose) {
            //TODO 获取产品具体信息
        }
        return $trade_info_list;
    }
}
