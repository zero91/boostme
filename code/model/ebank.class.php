<?php

!defined('IN_SITE') && exit('Access Denied');
require WEB_ROOT . '/code/lib/alipay/alipay_service.class.php';
require WEB_ROOT . '/code/lib/alipay/alipay_notify.class.php';

require(WEB_ROOT . '/code/lib/tenpay/RequestHandler.class.php');
require(WEB_ROOT . '/code/lib/tenpay/ResponseHandler.class.php');

class ebankmodel {
    var $db;
    var $base;

    function ebankmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function tenpaytransfer($out_trade_no, $total_fee, $desc, $trade_mode) {
        require_once(WEB_ROOT . '/code/conf/tenpay/tenpay_config.php');
        // 创建支付请求对象
        $reqHandler = new RequestHandler();
        $reqHandler->init();
        $reqHandler->setKey($key);
        $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

        //设置支付参数 
        $reqHandler->setParameter("partner", $partner);
        $reqHandler->setParameter("out_trade_no", $out_trade_no);
        $reqHandler->setParameter("total_fee", $total_fee);  //总金额
        $reqHandler->setParameter("return_url", $return_url);
        $reqHandler->setParameter("notify_url", $notify_url);
        $reqHandler->setParameter("body", $desc);
        $reqHandler->setParameter("bank_type", "DEFAULT");  //银行类型，默认为财付通

        //用户ip
        $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']); //客户端IP
        $reqHandler->setParameter("fee_type", "1");  //币种
        $reqHandler->setParameter("subject", $desc); //商品名称，（中介交易时必填）

        //系统可选参数
        $reqHandler->setParameter("sign_type", "MD5");  //签名方式，默认为MD5，可选RSA
        $reqHandler->setParameter("service_version", "1.0");  //接口版本号
        $reqHandler->setParameter("input_charset", "utf-8");  //字符集
        $reqHandler->setParameter("sign_key_index", "1");     //密钥序号

        //业务可选参数
        $reqHandler->setParameter("attach", "");  //附件数据，原样返回就可以了
        $reqHandler->setParameter("product_fee", "");        	  //商品费用
        $reqHandler->setParameter("transport_fee", "0");      	  //物流费用
        $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
        $reqHandler->setParameter("time_expire", "");             //订单失效时间
        $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
        $reqHandler->setParameter("goods_tag", "");               //商品标记
        $reqHandler->setParameter("trade_mode", $trade_mode);     //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
        $reqHandler->setParameter("transport_desc","");           //物流说明
        $reqHandler->setParameter("trans_type","1");              //交易类型
        $reqHandler->setParameter("agentid","");                  //平台ID
        $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
        $reqHandler->setParameter("seller_id","");                //卖家的商户号

        // 获取debug信息,建议把请求和debug信息写入日志，方便定位问题
        // $debugInfo = $reqHandler->getDebugInfo();
        $reqUrl = $reqHandler->getRequestURL();

        $reqUrl = "<script>window.location.href=\"$reqUrl\";</script>";
        echo $reqUrl;
    }

    //财付通即时到帐支付页面回调示例，商户按照此文档进行开发即可
    function tenpayreturn() {
        require(WEB_ROOT . '/code/lib/tenpay/function.php');

        log_result("进入前台回调页面");

        // 创建支付应答对象
        $resHandler = new ResponseHandler();
        foreach ($this->post as $k => $v) { 
            $this->setParameter($k, $v);
        }

        $resHandler->setKey($key);

        //判断签名
        if ($resHandler->isTenpaySign()) {
            $notify_id = $resHandler->getParameter("notify_id");           // 通知id
            $out_trade_no = $resHandler->getParameter("out_trade_no");     // 商户订单号
            $transaction_id = $resHandler->getParameter("transaction_id"); // 财付通订单号
            $total_fee = $resHandler->getParameter("total_fee");           // 金额,以分为单位

            //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
            $discount = $resHandler->getParameter("discount");
            $trade_state = $resHandler->getParameter("trade_state"); // 支付结果
            $trade_mode = $resHandler->getParameter("trade_mode");   // 交易模式,1即时到账
            
            if ("1" == $trade_mode ) {
                if ("0" == $trade_state) { 
                    echo "<br/>" . "即时到帐支付成功" . "<br/>";
                } else {
                    //当做不成功处理
                    echo "<br/>" . "即时到帐支付失败" . "<br/>";
                }

            } elseif ("2" == $trade_mode ) {
                if ("0" == $trade_state) {
                    echo "<br/>" . "中介担保支付成功" . "<br/>";
                } else {
                    //当做不成功处理
                    echo "<br/>" . "中介担保支付失败" . "<br/>";
                }
            }
            
        } else {
            echo "<br/>" . "认证签名失败" . "<br/>";
            echo $resHandler->getDebugInfo() . "<br>";
        }
    }

    function aliapytransfer($rechargemoney) {
        $aliapy_config = include WEB_ROOT . '/code/conf/alipay/alipay.config.php';
        $tradeid = "u-" . strtolower(random(6));
        //构造要请求的参数数组
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "payment_type" => "1",
            "partner" => trim($aliapy_config['partner']),
            "_input_charset" => trim(strtolower($aliapy_config['input_charset'])),
            "seller_email" => trim($aliapy_config['seller_email']),
            "return_url" => trim($aliapy_config['return_url']),
            "notify_url" => trim($aliapy_config['notify_url']),
            "out_trade_no" => $tradeid,
            "subject" => '财富充值',
            "body" => '财富充值',
            "total_fee" => $rechargemoney,
            "paymethod" => '',
            "defaultbank" => '',
            "anti_phishing_key" => '',
            "exter_invoke_ip" => '',
            "show_url" => '',
            "extra_common_param" => '',
            "royalty_type" => '',
            "royalty_parameters" => ''
        );
        //构造即时到帐接口
        $alipayService = new AlipayService($aliapy_config);
        $html_text = $alipayService->create_direct_pay_by_user($parameter);
        echo $html_text;
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    function aliapyverifyreturn() {
        $aliapy_config = include WEB_ROOT . '/code/conf/alipay/alipay.config.php';
        $alipayNotify = new AlipayNotify($aliapy_config, $this->base->get, $this->base->post);
        return $alipayNotify->verifyReturn();
    }
}

?>
