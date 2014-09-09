<?php

class proxy_header {
    var $id;
    var $body_len;

    function proxy_header($tid, $tbody_len) {
        $this->id = $tid;
        $this->body_len = $tbody_len;
    }

    function set_id($tid) {
        $this->id = $tid;
    }

    function get_id() {
        return $this->id;
    }

    function set_body_len($tbody_len) {
        $this->body_len = $tbody_len;
    }

    function get_body_len() {
        return $this->body_len;
    }

    function pack_str() {
        $header_str = pack("LL", $this->id, $this->body_len); 
        return $header_str;
    }

    function assign($header_str) {
        $pack_array = unpack("Lid/Lbody_len", $header_str);
        $this->id = $pack_array["id"];
        $this->body_len = $pack_array["body_len"];
    }

    function get_size() {
        $tot_size = 0;

        $id_size = 4;
        $body_len_size = 4;

        $tot_size += $id_size;
        $tot_size += $body_len_size;
        return $tot_size;
    }
}

?>
