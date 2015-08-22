<?php

define("ALIPAY_CONFIG_STR", serialize(array(
    'partner'       => '2088412913044765', // 合作身份者id，以2088开头的16位纯数字

    // 安全检验码，以数字和字母组成的32位字符
    'key'           => 'kzx0fmqogmxnj7cs8jph14uobpjc5luh',

    'sign_type'     => strtoupper('MD5'), //签名方式 不需修改

    'input_charset' => strtolower('utf-8'), //字符编码格式 目前支持 gbk 或 utf-8

    // ca证书路径地址，用于curl中ssl校验，请保证cacert.pem文件在当前文件夹目录中
    //'cacert'        => getcwd() . '/cacert.pem',
    'cacert'        =>  dirname(__FILE__) . '/cacert.pem',

    'transport'     => 'http', // 访问模式,若服务器支持ssl访问,请选择https; 若不支持请选择http

    'return_url'    => "http://www.boostme.cn:9507/alipay/returns",

    'notify_url'    => "http://www.boostme.cn:9507/alipay/notify",
)));
