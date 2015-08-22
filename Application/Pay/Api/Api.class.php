<?php

namespace Pay\Api;

define('PAY_CLIENT_PATH', dirname(dirname(__FILE__)));

require_cache(PAY_CLIENT_PATH . '/Conf/config.php'); // 载入配置文件

abstract class Api {

	// @brief  $model API调用模型实例
	// @access  protected
    // @var object
    //
	protected $model;

	public function __construct() {
		$this->_init();
	}

	// 抽象方法，用于设置模型实例
	abstract protected function _init();
}
