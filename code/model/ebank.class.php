<?php

!defined('IN_SITE') && exit('Access Denied');
//require WEB_ROOT . '/code/lib/alipay/alipay_service.class.php';
//require WEB_ROOT . '/code/lib/alipay/alipay_notify.class.php';

require(WEB_ROOT . '/code/lib/tenpay/RequestHandler.class.php');
require(WEB_ROOT . '/code/lib/tenpay/ResponseHandler.class.php');

class ebankmodel {
    public function __construct(&$db) {
        $this->db = & $db;
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
        require_once(WEB_ROOT . '/code/lib/tenpay/function.php');
        require_once(WEB_ROOT . '/code/conf/tenpay/tenpay_config.php');

        log_result("进入前台回调页面");

        // 创建支付应答对象
        $resHandler = new ResponseHandler();
        //foreach ($req_param as $k => $v) {$resHandler->setParameter($k, $v);}
        $resHandler->setKey($key);

        $result = array();
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

            $result['is_tenpay_sign'] = true;
            $result['notify_id'] = $notify_id;
            $result['trade_no'] = $out_trade_no;
            $result['transaction_id'] = $transaction_id;
            $result['total_fee'] = $total_fee;
            $result['discount'] = $discount;
            $result['trade_state'] = $trade_state;
            $result['trade_mode'] = $trade_mode;
        } else {
            $result['is_tenpay_sign'] = false;
            echo $resHandler->getDebugInfo() . "<br>";
        }
        return $result;
    }

    function tenpaynotify() {
        require(WEB_ROOT . '/code/lib/tenpay/client/ClientResponseHandler.class.php');
        require(WEB_ROOT . '/code/lib/tenpay/client/TenpayHttpClient.class.php');
        require_once(WEB_ROOT . '/code/lib/tenpay/function.php');
        require_once(WEB_ROOT . '/code/conf/tenpay/tenpay_config.php');

        log_result("进入后台回调页面");

        // 创建支付应答对象
        $resHandler = new ResponseHandler();
        //foreach ($req_param as $k => $v) { $resHandler->setParameter($k, $v); }
        $resHandler->setKey($key);

        $result = array();
        //判断签名
        if ($resHandler->isTenpaySign()) {
            $result['is_tenpay_sign'] = true;
            $notify_id = $resHandler->getParameter("notify_id"); //通知id
            
            //通过通知ID查询，确保通知来至财付通
            //创建查询请求
            $queryReq = new RequestHandler();
            $queryReq->init();
            $queryReq->setKey($key);
            $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
            $queryReq->setParameter("partner", $partner);
            $queryReq->setParameter("notify_id", $notify_id);
                
            //通信对象
            $httpClient = new TenpayHttpClient();
            $httpClient->setTimeOut(5);
            //设置请求内容
            $httpClient->setReqContent($queryReq->getRequestURL());
        
            //后台调用
            if ($httpClient->call()) {
                //设置结果参数
                $queryRes = new ClientResponseHandler();
                $queryRes->setContent($httpClient->getResContent());
                $queryRes->setKey($key);
                
                if ($resHandler->getParameter("trade_mode") == "1") {
                    //判断签名及结果（即时到帐）
                    //只有签名正确,retcode为0，trade_state为0才是支付成功
                    if ($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
                        log_result("即时到帐验签ID成功");
                        $out_trade_no = $resHandler->getParameter("out_trade_no"); //取结果参数做业务处理
                        $transaction_id = $resHandler->getParameter("transaction_id"); //财付通订单号
                        $total_fee = $resHandler->getParameter("total_fee"); //金额,以分为单位
                        $discount = $resHandler->getParameter("discount"); //如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee

                        $result['succeed'] = true;
                        $result['trade_no'] = $out_trade_no;
                        $result['transaction_id'] = $transaction_id;
                        $result['total_fee'] = $total_fee;
                        $result['discount'] = $discount;
                        
                        //处理业务完毕
                        log_result("即时到帐后台回调成功");
                    } else {
                        //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                        log_result("验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg"));
                        log_result("即时到帐后台回调失败");

                        $result['succeed'] = false;
                    }
                } elseif ($resHandler->getParameter("trade_mode") == "2") {
                    $result['succeed'] = false;
                    return $result;
                    //判断签名及结果（中介担保）
                    //只有签名正确,retcode为0，trade_state为0才是支付成功
                    if ($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0") {
                        log_result("中介担保验签ID成功");
                        $out_trade_no = $resHandler->getParameter("out_trade_no"); //取结果参数做业务处理
                        $transaction_id = $resHandler->getParameter("transaction_id"); //财付通订单号

                        //------------------------------
                        //处理业务开始
                        //------------------------------
                        
                        //处理数据库逻辑
                        //注意交易单不要重复处理
                        //注意判断返回金额
            
                        log_result("中介担保后台回调，trade_state=".$resHandler->getParameter("trade_state"));
                        switch ($resHandler->getParameter("trade_state")) {
                            case "0":	//付款成功
                                break;
                            case "1":	//交易创建
                                break;
                            case "2":	//收获地址填写完毕
                                break;
                            case "4":	//卖家发货成功
                                break;
                            case "5":	//买家收货确认，交易成功
                                break;
                            case "6":	//交易关闭，未完成超时关闭
                                break;
                            case "7":	//修改交易价格成功
                                break;
                            case "8":	//买家发起退款
                                break;
                            case "9":	//退款成功
                                break;
                            case "10":	//退款关闭			
                                break;
                            default:
                                //nothing to do
                                break;
                        }
                            
                        //------------------------------
                        //处理业务完毕
                        //------------------------------
                        echo "success";
                    } else {
                        //错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
                        //echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
                        log_result("中介担保后台回调失败");
                        echo "fail";
                    }
                }
                //获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
                log_result("\n------------------------------------------------------\n");
                log_result("http res:" . $httpClient->getResponseCode() . "," . $httpClient->getErrInfo());
                log_result("query req:" . htmlentities($queryReq->getRequestURL(), ENT_NOQUOTES, "GB2312") . "\n");
                log_result("query res:" . htmlentities($queryRes->getContent(), ENT_NOQUOTES, "GB2312") . "\n");
                log_result("query reqdebug:" . $queryReq->getDebugInfo() . "\n");
                log_result("query resdebug:" . $queryRes->getDebugInfo() . "\n");
            } else {
                $result['succeed'] = false;
                // 后台调用通信失败,写日志，方便定位问题
                log_result("\ncall err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "\n");
            } 
        } else {
            $result['is_tenpay_sign'] = false;
            log_result("\n" . "认证签名失败" . "\n");
            log_result($resHandler->getDebugInfo() . "\n");
        }
        return $result;
    }

    function alipaytransfer($out_trade_no, $total_fee, $product_name) {
        require_once(WEB_ROOT . '/code/conf/alipay/alipay.config.php');
        require_once(WEB_ROOT . '/code/lib/alipay/alipay_submit.class.php');

        //**************************请求参数**************************
        $payment_type = "1"; //支付类型，必填，不能修改

        // 服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数
        $notify_url = $alipay_config['notify_url'];

        // 页面跳转同步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        $return_url = $alipay_config['return_url'];

        $seller_email = 'boostme@qq.com';                // 卖家支付宝帐户，必填
        $out_trade_no = $out_trade_no;                   // 商户订单号，商户网站订单系统中唯一订单号，必填
        $subject = $product_name;                        // 订单名称，必填
        $price = $total_fee;                             // 付款金额，必填
        $quantity = "1";                                 // 商品数量，必填，建议默认为1，不改变值，把一次交易看成是一次下订单而非购买一件商品
        $logistics_fee = "0.00";                         // 物流费用，必填，即运费
        $logistics_type = "EXPRESS";                     // 物流类型，必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
        $logistics_payment = "SELLER_PAY";               // 物流支付方式，必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
        $body = "";                                      // 订单描述
        $show_url = "";                                  // 商品展示地址， 需以http://开头的完整路径，如：http://www.xxx.com/myorder.html
        $receive_name = "";                              // 收货人姓名，如：张三
        $receive_address = "";                           // 收货人地址，如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号
        $receive_zip = "";                               // 收货人邮编， 如：123456
        $receive_phone = "";                             // 收货人电话号码， 如：0571-88158090
        $receive_mobile = "";                            // 收货人手机号码，如：13312341234

        /*
        $body = "测试";
        $show_url = "http://www.boostme.cn:9507";
        $receive_name = "张健";
        $receive_address = "北京市海淀区月泉路逸成东苑小区22栋15单元04号";
        $receive_zip = "100085";
        $receive_phone = "0571-88158090";
        $receive_mobile = "13269963875";
         */

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
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
    }

    function alipayreturn() {
        require_once(WEB_ROOT . '/code/conf/alipay/alipay.config.php');
        require_once(WEB_ROOT . '/code/lib/alipay/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
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
        } else {
            $result['succeed'] = false;
            // 验证失败
            // 如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "验证失败";
        }

        return $result;
    }

    function alipaynotify() {
        require_once(WEB_ROOT . '/code/conf/alipay/alipay.config.php');
        require_once(WEB_ROOT . '/code/lib/alipay/alipay_notify.class.php');

        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
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

        } else {
            $result['succeed'] = false;
        }
        return $result;
    }

    function alipay_send_goods($transaction_id, $logistics_name, $invoice_no, $transport_type) {
        runlog("alipay", "transaction_id=$transaction_id,logistics_name=$logistics_name,invoice_no=$invoice_no,transport_type=$transport_type", 0);
        require_once(WEB_ROOT . '/code/conf/alipay/alipay.config.php');
        require_once(WEB_ROOT . '/code/lib/alipay/alipay_submit.class.php');

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
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($parameter);
        //解析XML
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
        $doc = new DOMDocument();
        $doc->loadXML($html_text);

        //runlog("alipay", $html_text, 0);
        $result = array();
        //解析XML
        if (!empty($doc->getElementsByTagName("is_success")->item(0)->nodeValue)) {
            $result['is_success'] = $doc->getElementsByTagName("is_success")->item(0)->nodeValue;
        }
        return $result;
    }

    private $db;
}

?>
