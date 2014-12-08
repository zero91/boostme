<?php

// 系统定时任务处理
!defined('IN_SITE') && exit('Access Denied');

class crontabmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function auto_send_goods($crontab, $force=0) {
        $curtime = time();
        if (($crontab['lastrun'] + $crontab['minute'] * 60) < $curtime || $force) {
            $trade_list = $_ENV['trade']->get_trade_by_status(TRADE_STATUS_WAIT_SELLER_SEND_GOODS);

            foreach ($trade_list as $trade) {
                $transaction_id = $trade['transaction_id'];
                $logistics_name = "Boostme物流";
                $invoice_no     = "Boostme物流编号";
                $transport_type = 'EXPRESS'; // POST（平邮）、EXPRESS（快递）、EMS（EMS）

                $send_goods_result = $_ENV['ebank']->alipay_send_goods($transaction_id, $logistics_name, $invoice_no, $transport_type);

                if ($send_goods_result['is_success'] == 'T') {
                    $_ENV['trade']->update_trade_status($trade['trade_no'], TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS);
                    runlog('alipay', "[INFO] Auto send goods succeed! trade_no=[" . $trade['trade_no'] . "],new_status=[" . TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS . "]", 0);
                } else {
                    runlog('alipay', "[INFO] Auto send goods failed!", 0);
                }
            }

            if ($crontab) {
                $nextrun = $curtime + $crontab['minute'] * 60;
                $this->db->query("UPDATE crontab SET lastrun=$curtime,nextrun=$nextrun WHERE id=" . $crontab['id']);
            }
        }
    }

    private $db;
}

?>
