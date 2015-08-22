<?php
namespace Common\Controller;
use Think\Controller;

class CommonController extends Controller {
    public function _initialize() {
        if (!isset($_SESSION['username'])) {
            $this->redirect('/Home/user/login');
        } else {
            echo $_SESSION['username'];
        }
    }
}
