<?php

namespace Pay\Service;
use Pay\Service\Service;

class AlipayService extends Service {
    public function _init() {
        vendor('Alipay.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }

    public function transfer($out_trade_no, $total_fee, $product_name) {
        $alipay_config = unserialize(ALIPAY_CONFIG_STR);

        //**************************请求参数**************************
        $payment_type = "1"; //支付类型，必填，不能修改

        // 服务器异步通知页面路径，需http://格式的完整路径，
        // 不能加?id=123这类自定义参数
        $notify_url = $alipay_config['notify_url'];

        // 页面跳转同步通知页面路径，需http://格式的完整路径，
        // 不能加?id=123这类自定义参数，不能写成http://localhost/
        $return_url = $alipay_config['return_url'];

        // 卖家支付宝帐户，必填
        $seller_email = 'boostme@qq.com';
        // 商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $out_trade_no;
        // 订单名称，必填
        $subject = $product_name;
        // 付款金额，必填
        $price = $total_fee;
        // 商品数量，必填，建议默认为1，不改变值，
        // 把一次交易看成是一次下订单而非购买一件商品
        $quantity = "1";
        // 物流费用，必填，即运费
        $logistics_fee = "0.00";
        // 物流类型，必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
        $logistics_type = "EXPRESS";
        // 物流支付方式，必填，两个值可选：
        // SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
        $logistics_payment = "SELLER_PAY";
        // 订单描述
        $body = "";
        // 商品展示地址， 需以http://开头的完整路径，
        // 如：http://www.xxx.com/myorder.html
        $show_url = "";
        // 收货人姓名，如：张三
        $receive_name = "";
        // 收货人地址，如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号
        $receive_address = "";
        // 收货人邮编， 如：123456
        $receive_zip = "";
        // 收货人电话号码， 如：0571-88158090
        $receive_phone = "";
        // 收货人手机号码，如：13312341234
        $receive_mobile = "";

        //************************************************************
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "trade_create_by_buyer",
            "partner" => trim($alipay_config['partner']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "seller_email"	=> $seller_email,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "price"	=> $price,
            "quantity"	=> $quantity,
            "logistics_fee"	=> $logistics_fee,
            "logistics_type"	=> $logistics_type,
            "logistics_payment"	=> $logistics_payment,
            "body"	=> $body,
            "show_url"	=> $show_url,
            "receive_name"	=> $receive_name,
            "receive_address"	=> $receive_address,
            "receive_zip"	=> $receive_zip,
            "receive_phone"	=> $receive_phone,
            "receive_mobile"	=> $receive_mobile,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        return $html_text;
    }

    public function returns() {
        $alipay_config = unserialize(ALIPAY_CONFIG_STR);

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        $result = array();
        if ($verify_result) { //验证成功
            $result['succeed'] = true;

            // 命名成这样，是为了与财付通保持一致
            $result['trade_no']       = $_GET['out_trade_no'];  // 商户订单号
            $result['transaction_id'] = $_GET['trade_no'];      // 支付宝交易号
            $result['trade_status']   = $_GET['trade_status'];  // 交易状态
            $result['notify_id'] = $_GET['notify_id'];
            $result['total_fee'] = $_GET['total_fee'];
            $result['discount'] = $_GET['discount'];
            $result['buyer_email'] = $_GET['buyer_email'];   // 买家支付宝账号
            $result['buyer_id'] = $_GET['buyer_id'];   // 买家支付宝对应的唯一用户号
            $result['is_total_fee_adjust'] = $_GET['is_total_fee_adjust']; // 总价是否调整过
            $result['receive_name'] = $_GET['receive_name']; // 收货人姓名
            $result['receive_address'] = $_GET['receive_address']; // 收货人地址
            $result['receive_zip'] = $_GET['receive_zip']; // 收货人邮编
            $result['receive_phone'] = $_GET['receive_phone']; // 收货人电话
            $result['receive_mobile'] = $_GET['receive_mobile']; // 收货人手机
            $result['gmt_create'] = $_GET['gmt_create']; // 交易创建时间
            $result['gmt_payment'] = $_GET['gmt_payment']; // 交易支付时间

        } else {
            $result['succeed'] = false;
            // 验证失败
            // 如要调试，请看alipay_notify.php页面的verifyReturn函数
            // echo "验证失败";
        }
        return $result;
    }

    public function notify() {
        $alipay_config = unserialize(ALIPAY_CONFIG_STR);

        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        $result = array();
        if ($verify_result) {//验证成功
            $result['succeed'] = true;

            // 请在这里加上商户的业务逻辑程序代
            $result['trade_no'] = $_POST['out_trade_no']; //商户订单号
            $result['transaction_id'] = $_POST['trade_no']; //支付宝交易号
            $result['trade_status'] = $_POST['trade_status']; //交易状态
            $result['notify_id'] = $_POST['notify_id'];
            $result['total_fee'] = $_POST['total_fee'];
            $result['discount'] = $_POST['discount'];
            $result['buyer_email'] = $_POST['buyer_email'];   // 买家支付宝账号
            $result['buyer_id'] = $_POST['buyer_id'];   // 买家支付宝对应的唯一用户号
            $result['is_total_fee_adjust'] = $_POST['is_total_fee_adjust']; // 总价是否调整过
            $result['receive_name'] = $_POST['receive_name']; // 收货人姓名
            $result['receive_address'] = $_POST['receive_address']; // 收货人地址
            $result['receive_zip'] = $_POST['receive_zip']; // 收货人邮编
            $result['receive_phone'] = $_POST['receive_phone']; // 收货人电话
            $result['receive_mobile'] = $_POST['receive_mobile']; // 收货人手机
            $result['gmt_create'] = $_POST['gmt_create']; // 交易创建时间
            $result['gmt_payment'] = $_POST['gmt_payment']; // 交易支付时间
        } else {
            $result['succeed'] = false;
        }
        return $result;
    }

    public function send($transaction_id, $logistics_name, $invoice_no, $transport_type) {
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "send_goods_confirm_by_platform",
            "partner" => trim($alipay_config['partner']),
            "trade_no"	=> $transaction_id,
            "logistics_name"	=> $logistics_name,
            "invoice_no"	=> $invoice_no,
            "transport_type"	=> $transport_type,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($parameter);
        //解析XML
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
        $doc = new DOMDocument();
        $doc->loadXML($html_text);

        $result = array();
        //解析XML
        if (!empty($doc->getElementsByTagName("is_success")->item(0)->nodeValue)) {
            $result['is_success'] = $doc->getElementsByTagName("is_success")->item(0)->nodeValue;
        }
        return $result;
    }
}
