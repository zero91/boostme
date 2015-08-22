<?php
namespace Home\Model;
use Think\Model;

class ServiceCategoryModel extends Model {

    protected $_validate = array(
        array('region', '1,15', -1, self::EXISTS_VALIDATE, 'length'), // 验证地区长度是否合法
    );

    // @brief  update  更新服务的分类信息
    //
    // @param  integer  $service_id  服务ID号
    // @param  array    $category    分类信息
    // @param  boolean  $remain_old  是否删除原有分类信息
    //
    // @return  integer  更新数量
    //
    public function update($service_id, $category, $remain_old = false) {
        foreach ($category as &$c) {
            $c['service_id'] = $service_id;
        }

        if (!$remain_old) {
            $this->where(array("service_id" => $service_id))->delete();
        }
        return $this->addAll($category);
    }
}
