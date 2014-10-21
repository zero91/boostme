<?php

error_reporting(0);

$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('IN_SITE', TRUE);
define('WEB_ROOT', dirname(dirname(dirname(__FILE__))));

define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);

include WEB_ROOT . '/code/model/boostme.class.php';

$boostme = new boostme();
$boostme->run_alipaynotify();

?>
