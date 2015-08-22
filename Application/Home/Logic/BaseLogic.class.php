<?php
namespace Home\Logic;
use Think\Model;

class BaseLogic extends Model {

    // @brief  __construct  构造函数
    // @param string $name 模型名称
    // @param string $tablePrefix 表前缀
    // @param mixed $connection 数据库连接信息
    //
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        $this->_init();
        parent::__construct($name, $tablePrefix, $connection);
    }

    protected function _init() {}
}
