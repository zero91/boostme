<?php

!defined('IN_SITE') && exit('Access Denied');

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

require WEB_ROOT . '/code/lib/db.class.php';
require WEB_ROOT . '/code/lib/global.func.php';
require WEB_ROOT . '/code/model/base.class.php';
require WEB_ROOT . '/code/lib/cache.class.php';

class boostme {
    var $get = array();
    var $post = array();
    var $vars = array();

    function boostme() {
        $this->init_request();
        $this->load_control();
    }

    function init_request() {
        global $urlmap;

        require WEB_ROOT . '/code/config.php';
        header('Content-type: text/html; charset=' . WEB_CHARSET);

        $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        $pos = strpos($querystring, '.');
        if ($pos !== false) {
            $querystring = substr($querystring, 0, $pos);
        }

        // 处理简短url
        $pos = strpos($querystring, '-');
        ($pos !== false) && $querystring = urlmap($querystring);
        $andpos = strpos($querystring, "&");
        $andpos && $querystring = substr($querystring, 0, $andpos);

        $this->get = explode('/', $querystring);
        if (empty($this->get[0])) {
            $this->get[0] = 'index';
        }

        if (empty($this->get[1])) {
            $this->get[1] = 'default';
        }

        if (count($this->get) < 2) {
            exit(' Access Denied !');
        }
        unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

        $this->get = taddslashes($this->get, 1);
        $this->post = taddslashes(array_merge($_GET, $_POST));
        checkattack($this->post, 'post');
        checkattack($this->get, 'get');
        unset($_POST);
    }

    function load_control() {
        $controlfile = WEB_ROOT . '/code/control/' . $this->get[0] . '.php';

        $isadmin = ('admin' == substr($this->get[0], 0, 5));
        $isadmin && $controlfile = WEB_ROOT . '/code/control/admin/' . substr($this->get[0], 6) . '.php';

        if (false === @include($controlfile)) {
            $this->notfound("");
        }
    }

    function run() {
        $controlname = $this->get[0] . 'control';

        $control = new $controlname($this->get, $this->post);
        $method = 'on' . $this->get[1];

        if (method_exists($control, $method)) {
            $regular = $this->get[0] . '/' . $this->get[1];
            if ($control->checkable($regular)) {
                $control->$method();
            } else {
                $this->notfound("");
            }
        } else {
            $this->notfound("");
        }
    }

    function notfound($error) {
        @header('HTTP/1.0 404 Not Found');
        exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
    }
}

?>
