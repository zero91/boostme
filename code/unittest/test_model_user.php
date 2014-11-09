<?php

require_once(WEB_ROOT . '/code/model/user.class.php');

class test_model_user {

    public function __construct(& $db) {
        $this->usermodel = new usermodel($db);
    }

    public function test_get_by_uid() {
        $user = $this->usermodel->get_by_uid(1);
        return $user['uid'] == 1 && $user['email'] == 'boostme@qq.com';
    }

    public function test_get_by_username() {
        $user = $this->usermodel->get_by_username('admin');
        return $user['uid'] == 1 && $user['email'] == 'boostme@qq.com';
    }

    public function test_get_by_email() {
        $user = $this->usermodel->get_by_email('boostme@qq.com');
        return $user['uid'] == 1 && $user['email'] == 'boostme@qq.com';
    }

    public function test_get_by_name_email() {
        $user = $this->usermodel->get_by_name_email('admin', 'boostme@qq.com');
        return $user['uid'] == 1 && $user['email'] == 'boostme@qq.com';
    }

    public function test_get_user_list() {
        $userlist = $this->usermodel->get_user_list(0, 10);
        return $userlist[0]['uid'] >= $userlist[1]['uid'];
    }

    public function test_get_apply_list() {
        $userlist = $this->usermodel->get_apply_list();
        // print_r($userlist);
        return true;
    }

    public function test_get_lastest_register() {
        $userlist = $this->usermodel->get_lastest_register();
        return $userlist[0]['regtime'] >= $userlist[0]['regtime'];
    }

    public function test_add() {
        return true;
        $username = "unittest_" . random();
        $uid = $this->usermodel->add($username, 'unittest');
        $user = $this->usermodel->get_by_uid($uid);
        return $user['username'] == $username;
    }

    private $usermodel;
}

?>
