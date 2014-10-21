<?php

!defined('IN_SITE') && exit('Access Denied');

class ebankcontrol extends base {

    function ebankcontrol(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('ebank');
        $this->load('trade');
        $this->load('material');
    }

    // 财付通回调
    function ontenpayback() {
        if ($_GET['trade_status'] == 'TRADE_SUCCESS') {
            $this->message("充值成功", "user/score");
        } else {
            $this->message("服务器繁忙，请稍后再试!", 'STOP');
        }
    }

    // 财付通转账
    function ontenpaytransfer() {
        if (!$this->user['uid']) {
            $this->message("您无权执行该操作!", "STOP");
            exit;
        }

        $out_trade_no = $this->post["trade_no"];        // 获取提交的订单号
        $product_name = $this->post["product_name"];    // 获取提交的商品名称
        $order_price = $this->post["order_price"];      // 获取提交的商品价格
        $trade_mode = $this->post["trade_mode"];        // 支付方式

        $total_fee = $order_price * 100; // 商品价格（包含运费），以分为单位
        $desc = "$product_name"; // 商品名称
        $desc = cutstr($desc, 32, '');


        $_ENV['ebank']->tenpaytransfer($out_trade_no, $total_fee, $desc, $trade_mode);
    }

    // 财付通回调
    function ontenpayreturn() {
        $result = $_ENV['ebank']->tenpayreturn();

        if ($result['is_tenpay_sign'] && $result['trade_mode'] == "1" && $result['trade_state'] == '0') {
            $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "tenpay", $trade['trade_mode']);
            $this->message("付款成功", "material/categorylist");
        } else {
            $this->message("付款失败，请重试", "trade/buy_now");
        }
    }

    function ontenpaynotify() {
        $result = $_ENV['ebank']->tenpaynotify();

        if ($result['is_tenpay_sign'] && $result['succeed']) {
            $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "tenpay", $trade['trade_mode']);
            echo 'success';
        } else {
            echo 'fail';
        }
    }

    // 支付宝转账
    function onalipaytransfer() {
        if (!$this->user['uid']) {
            $this->message("您无权执行该操作!", "STOP");
            exit;
        }
        $out_trade_no = $this->post["trade_no"];        // 获取提交的订单号
        $product_name = $this->post["product_name"];    // 获取提交的商品名称
        $order_price = $this->post["order_price"];      // 获取提交的商品价格

        runlog('alipay', "[INFO]trade_no=[$out_trade_no],product_name=[$product_name],order_price=[$order_price]", 0);

        $_ENV['ebank']->alipaytransfer($out_trade_no, $order_price, $product_name);
    }

    function onalipayreturn() {
        $result = $_ENV['ebank']->alipayreturn();

        if ($result['succeed']) {
            if ($result['trade_status'] == 'WAIT_BUYER_PAY') {
                $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_PAY);

            } else if ($result['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
                $affected_rows = $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "alipay", "1", TRADE_STATUS_WAIT_SELLER_SEND_GOODS);

                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }

            } else if ($result['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
                $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS);

                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }

            } else if ($result['trade_status'] == 'TRADE_FINISHED') {
                $affected_rows = $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "alipay", "1", TRADE_STATUS_FINISHED);
                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }

            } else if ($result['trade_status'] == 'TRADE_CLOSED') {
                $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_CLOSED);
                $this->message("付款失败，请重试", "trade/buy_now");
            }
            //runlog('alipay', "[INFO]trade_no=[".$result['trade_no']."],trade_status=[".$result['trade_status']."],total_fee=[".$result['total_fee']."],discount=[".$result['discount']."]", 0);
            $this->jump("trade/history");
        } else {
            runlog('alipay', "[WARNING]付款失败", 0);
            $this->message("付款失败，请重试", "trade/buy_now");
        }
    }

    function onalipaynotify() {
        $result = $_ENV['ebank']->alipaynotify();

        if ($result['succeed']) {
            if ($result['trade_status'] == 'WAIT_BUYER_PAY') {
                $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_PAY);
                //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款
	
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                echo "success";		//请不要修改或删除
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                runlog('alipay', "trade_status=" . $result['trade_status'], 0);
            } else if ($result['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
                //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货
                $affected_rows = $_ENV['trade']->update_trade_for_succeed($result['trade_no'], $result['transaction_id'], $result['total_fee'], $result['discount'], "alipay", "1", TRADE_STATUS_WAIT_SELLER_SEND_GOODS);
                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }
	
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                echo "success";		//请不要修改或删除

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                runlog('alipay', "trade_status=" . $result['trade_status'], 0);

            } else if ($result['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
                //该判断表示卖家已经发了货，但买家还没有做确认收货的操作
                $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS);
                runlog("test.log", "WAIT_BUYER_CONFIRM_GOODS: affected_rows = $affected_rows", 0);
                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }
	
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                echo "success";		//请不要修改或删除
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                runlog('alipay', "trade_status=" . $result['trade_status'], 0);
            } else if ($result['trade_status'] == 'TRADE_FINISHED') {
                //该判断表示买家已经确认收货，这笔交易完成
                $affected_rows = $_ENV['trade']->update_trade_status($result['trade_no'], TRADE_STATUS_FINISHED);
                if ($affected_rows > 0) {
                    $this->send_msg($result['trade_no'], $result['trade_status']);
                }
        
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
			
                echo "success";		//请不要修改或删除
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                runlog('alipay', "trade_status=" . $result['trade_status'], 0);
            } else {
                //其他状态判断
                echo "success";
                //调试用，写文本函数记录程序运行情况是否正常
                //logResult ("这里写入想要调试的代码变量值，或其他运行的结果记录");
                runlog('alipay', "trade_status=" . $result['trade_status'], 0);
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        } else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            runlog('alipay', "fail", 0);
        }
        return $result;
    }

    function send_msg($trade_no, $status) {
        $trade_info_list = array();

        if (!empty($trade_no)) {
            $trade_info_list = $_ENV['trade']->get_trade_info_by_trade_no($trade_no);
        }

        $user_material = array();
        foreach ($trade_info_list as $trade_info) {
            $material = $_ENV['material']->get($trade_info['mid']);

            if (!array_key_exists($material['uid'], $user_material)) {
                $user_material[$material['uid']] = array();
            }
            $user_material[$material['uid']][] = $material;
        }

        $subject = "Boostme";
        if ($status == 'WAIT_SELLER_SEND_GOODS') {
            $subject = "您有了新的订单，订单号：" . $trade_no;

        } else if ($status == 'WAIT_BUYER_CONFIRM_GOODS') {
            $subject = "订单号 " . $trade_no . " 状态发生了改变，新状态：等待买家确认收货";

        } else if ($status == 'TRADE_FINISHED') {
            $subject = "订单号 " . $trade_no . " 状态发生了改变，新状态：交易成功";
        }

        foreach ($user_material as $uid => $material_list) {
            $content = "订购资料列表：";
            foreach ($material_list as $material) {
                $content .= '<a href="' . url("material/view/{$material['id']}", 1) . '">' . $material['title'] . '</a><br/>';
            }
            $this->send('', 0, $uid, $subject, $content);
        }
    }
}

?>
