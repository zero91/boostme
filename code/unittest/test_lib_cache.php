<?php

require_once(WEB_ROOT . '/code/lib/cache.class.php');
require_once(WEB_ROOT . '/code/lib/global.func.php');

class cache_test {

    public function __construct(&$db) {
        $this->cache = new cache($db);
    }

    public function test_load() {
        $data = $this->cache->load("user");
        $this->cache->remove("user");
        return !empty($data);
    }

    private $cache;
}

?>
