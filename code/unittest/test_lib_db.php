<?php

require_once(WEB_ROOT . '/code/lib/db.class.php');

class db_test {

    public function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset='utf8', $pconnect=0) { 
        $this->db = new db($dbhost, $dbuser, $dbpw, $dbname, $dbcharset, $pconnect);
    }

    public function test_version() {
        return $this->db->version() > '5.0.1';
    }

    public function test_fetch_first() {
        $result = $this->db->fetch_first("SELECT * FROM `user`");
        return $result['uid'] == '1' && $result['email'] == 'boostme@qq.com';
    }

    public function test_result() {
        $query = $this->db->query("SELECT * FROM `user`");
        $result = $this->db->result($query, 1, 1);
        return $result == 'william91';
    }

    public function test_result_first() {
        $query = $this->db->query("SELECT * FROM `user`");
        $result = $this->db->result($query, 1);
        return $result == '2';
    }

    public function test_fetch_row() {
        $query = $this->db->query("SELECT * FROM user");
        $result_1 = $this->db->fetch_row($query);
        $result_2 = $this->db->fetch_row($query);
        return $result_1[0] == '1' && $result_2[0] == '2';
    }

    public function test_fetch_total() {
        $result = $this->db->fetch_total("user", "uid<10 AND username!='admin'");
        return $result == 8;
    }

    public function test_num_rows() {
        $query = $this->db->query("SELECT * FROM `user` WHERE uid<10");
        $result = $this->db->num_rows($query);
        return $result == 9;
    }

    public function test_num_fields() {
        $query = $this->db->query("SELECT uid,username,email FROM `user` WHERE uid<10");
        $result = $this->db->num_fields($query);
        return $result == 3;
    }

    public function test_fetch_fields() {
        $query = $this->db->query("SELECT * FROM user");
        $result_1 = $this->db->fetch_fields($query);
        $result_2 = $this->db->fetch_fields($query);
        return $result_1->primary_key == 1 && $result_2->name == 'username';
    }

    public function test_fetch_all() {
        $result = $this->db->fetch_all("SELECT * FROM user WHERE uid<=200", 'email');
        return array_key_exists("boostme@qq.com", $result);
    }

    private $db;
}

?>
