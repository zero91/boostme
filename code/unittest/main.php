<?php

function run_all_test($class_inst) {
    echo "Begin Running \033[1;34m" . get_class($class_inst) . "\033[0m\n";

    foreach (get_class_methods($class_inst) as $method) {
        if ($method != "__construct") {
            $result = $class_inst->$method();

            echo "\tRun Test [\033[1;33m$method\033[0m]";
            if ($result) {
                echo " \033[1;32mPASSED\033[0m\n";
            } else {
                echo " \033[1;41mFAILED\033[0m\n";
            }
        }
    }
    echo "-------------------------------------------\n";
}

function test_lib_db() {
    require_once(WEB_ROOT . "/code/unittest/test_lib_db.php");

    $db_test = new db_test(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    run_all_test($db_test);
}

function test_lib_cache() {
    require_once(WEB_ROOT . "/code/unittest/test_lib_cache.php");
    require_once(WEB_ROOT . "/code/lib/db.class.php");

    $db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    $cache_test = new cache_test($db);
    run_all_test($cache_test);
}

function test_model_user() {
    require_once(WEB_ROOT . "/code/unittest/test_model_user.php");
    require_once(WEB_ROOT . "/code/lib/db.class.php");

    $db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    $model_user_test = new test_model_user($db);
    run_all_test($model_user_test);
}

function test_model_userskill() {
    require_once(WEB_ROOT . "/code/unittest/test_model_userskill.php");
    require_once(WEB_ROOT . "/code/lib/db.class.php");

    $db = new db(DB_HOST, DB_USER, DB_PW, DB_NAME, DB_CHARSET, DB_CONNECT);
    $model_userskill_test = new test_model_userskill($db);
    run_all_test($model_userskill_test);
}

if (basename(__FILE__) == 'main.php') {
    define('IN_SITE', TRUE);
    define('WEB_ROOT', dirname(dirname(dirname(__FILE__))));
    define('SITE_URL', 'http://www.boostme.cn:9507/');
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

    require_once(WEB_ROOT . "/code/config.php");

    test_lib_db();
    test_lib_cache();

    test_model_user();
    test_model_userskill();
}

?>
