<?php

error_reporting(0);

$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('IN_SITE', TRUE);
define('WEB_ROOT', dirname(__FILE__));

define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, -9));

include WEB_ROOT . '/code/model/boostme.class.php';

$boostme = new boostme();
$boostme->run();

?>
