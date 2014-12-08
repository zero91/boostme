<?php

!defined('IN_SITE') && exit('Access Denied');

require(WEB_ROOT . '/code/lib/db.class.php');
require(WEB_ROOT . '/code/lib/global.func.php');
require(WEB_ROOT . '/code/lib/cache.class.php');
require(WEB_ROOT . '/code/core/base.class.php');

class boostme {
    public function __construct() {
        $this->init_request();
        $this->load_control();
    }

    public function run() {
        $controlname = $this->get[0] . 'control';
        $control = new $controlname($this->get, $this->post);
        $method = 'on' . $this->get[1];

        runlog('test007', "controlname = $controlname, method = $method", 0);

        if (method_exists($control, $method)) {
            $regular = $this->get[0] . '/' . $this->get[1];
            if ($control->checkable($regular)) {
                $control->$method();
            } else {
                $this->notfound();
            }
        } else {
            $this->notfound();
        }
    }

    private function init_request() {
        require(WEB_ROOT . '/code/config.php');
        header('Content-type: text/html; charset=' . WEB_CHARSET);

        $access_path = $_GET['access_path'];

        runlog("test007", "access_path = " . $access_path);
        runlog("test007", "_GET = " . var_export($_GET, true));
        runlog("test007", "_POST= " . var_export($_POST, true));
        $pos = strpos($access_path, '.');
        if ($pos !== false) {
            $access_path = substr($access_path, 0, $pos);
        }

        $this->get = explode('/', $access_path);
        if (empty($this->get[0])) {
            $this->get[0] = 'main';
        }

        if (empty($this->get[1])) {
            $this->get[1] = 'default';
        }

        if (count($this->get) < 2) {
            exit('Access Denied !');
        }

        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

        $this->get = taddslashes($this->get, 1);
        $this->post = taddslashes(array_merge($_GET, $_POST));
        checkattack($this->post, 'post');
        checkattack($this->get, 'get');
        //unset($_POST);
    }

    private function load_control() {
        $controlfile = WEB_ROOT . '/code/control/' . $this->get[0] . '.php';

        $isadmin = ('admin' == substr($this->get[0], 0, 5));
        $isadmin && $controlfile = WEB_ROOT . '/code/control/admin/' . substr($this->get[0], 6) . '.php';

        if (false === @include($controlfile)) {
            $this->notfound();
        }
    } 

    private function notfound($error="") {
        @header('HTTP/1.0 404 Not Found');
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
    }

    private $get = array();
    private $post = array();
    private $vars = array();
}

?>
