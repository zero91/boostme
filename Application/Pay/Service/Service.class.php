<?php

namespace Pay\Service;

abstract class Service {
    public function __construct() {
        $this->_init();
    }

    abstract protected function _init();
}
