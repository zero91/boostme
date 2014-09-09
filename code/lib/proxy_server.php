<?php

require_once(WEB_ROOT . '/lib/protocolbuf/parser/pb_parser.php');
require_once(WEB_ROOT . '/lib/protocolbuf/message/pb_message.php');
require_once(WEB_ROOT . '/lib/pb_proto_interface.php');
require_once(WEB_ROOT . '/lib/proxy_header.php');

// 后续需要加入连接池功能
class proxy_server {
    var $service_port;
    var $service_addr;
    var $service_socket;

    //$test = new PBParser();
    //$str = $test->parse('interface.proto');
    function proxy_server($address, $port) {
        $this->service_addr = $address;
        $this->service_port = $port;

        $this->service_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->service_socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }

        $result = socket_connect($this->service_socket, $this->service_addr, $this->service_port);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->service_socket)) . "\n";
        }
    }

    function send_req($bm_req_header, $bm_req_str) {
        $send_data = $bm_req_header->pack_str() . $bm_req_str;
        socket_write($this->service_socket, $send_data, strlen($send_data));

        $bm_res = socket_read($this->service_socket, 65536);

        $bm_res_header = new proxy_header(0, 0);
        $bm_res_header->assign(substr($bm_res, 0, $bm_res_header->get_size()));

        $bm_res_str = substr($bm_res, $bm_res_header->get_size());

        $bm_res_ret = new bm_res_t();
        $bm_res_ret->ParseFromString($bm_res_str);
        return $bm_res_ret;
    }
}

?>
