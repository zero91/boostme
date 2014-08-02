<?php

define('WEB_ROOT', '/Users/zero91/workspace/project/boostme/web');

require_once(WEB_ROOT . '/lib/proxy_server.php');
require_once(WEB_ROOT . '/lib/proxy_header.php');

$server = new proxy_server("localhost", "9102");

$bm_req = new bm_req_t();
$bm_req->set_name("zhangjian");
$bm_req->set_age(23);
$bm_req->append_num(1);
$bm_req->append_num(3);
$bm_req->append_num(4);
$bm_req->append_num(5);
$bm_req->append_num(6);
$bm_req_str = $bm_req->SerializeToString();

$header = new proxy_header(1234, strlen($bm_req_str));
$bm_res = $server->send_req($header, $bm_req_str);

echo $bm_res->sum();

?>

