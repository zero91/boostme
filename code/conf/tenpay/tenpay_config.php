<?php
$spname="财付通双接口测试";
$partner = "1221798901";                      //财付通商户号
$key = "fe655d8665fe49f5dcaacd2d38ee86d1";    //财付通密钥

$return_url = SITE_URL . "?ebank/tenpayreturn";    //显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = SITE_URL . "?ebank/tenpaynotify";    //支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
?>
