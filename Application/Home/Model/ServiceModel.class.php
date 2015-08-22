<?php
namespace Home\Model;
use Think\Model;

class ServiceModel extends Model {

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    protected $_validate = array(
        array('content', '20,4096', -1, self::EXISTS_VALIDATE, 'length'), // 验证内容长度是否合法
        array('supplement', '0,1024', -2, self::EXISTS_VALIDATE, 'length'), // 验证补充信息长度是否合法
        array('price', '0,10000', -3, self::EXISTS_VALIDATE, 'between'), // 验证价格是否合法
        array('duration', '5,86400', -4, self::EXISTS_VALIDATE, 'between'), // 时长是否合法
    );

    // @brief  lists  根据分类信息选择服务列表
    //
    // @param  string   $region  地区
    // @param  string   $school  学校
    // @param  string   $dept    院系
    // @param  string   $major   专业
    // @param  integer  $page    页码
    //
    // @return  array  服务列表
    //
    public function lists($region = "",
                          $school = "",
                          $dept   = "",
                          $major  = "",
                          $page   = 1,
                          $field  = true) {
        $category = array(
            "region" => $region,
            "school" => $school,
            "dept"   => $dept,
            "major"  => $major
        );

        if (empty($category['region'])) {
            unset($category['region']);
        }
        if (empty($category['school'])) {
            unset($category['school']);
        }
        if (empty($category['dept'])) {
            unset($category['dept']);
        }
        if (empty($category['major'])) {
            unset($category['major']);
        }

        $num_per_page = C('SERVICE_NUM_PER_PAGE');
        $start = ($page - 1) * $num_per_page;;
        if (empty($category)) {
            $service_list = $this->field($field)
                                 ->order('update_time DESC')
                                 ->limit($start, $num_per_page)
                                 ->select();
        } else {
            $id_query = D('ServiceCategory')->distinct(true)
                                            ->field("service_id")
                                            ->where($category)
                                            ->buildSql();
            $service_list = $this->join($id_query . ' q ON id = q.service_id')
                                 ->field($field)
                                 ->order('update_time DESC')
                                 ->limit($start, $num_per_page)
                                 ->select();
        }
        return $service_list;
    }

    // @brief  provide  新增服务
    //
    // @param  integer  $uid         用户ID号
    // @param  string   $content     服务内容
    // @param  integer  $duration    服务时长
    // @param  double   $price       收费价格
    // @param  array    $category    服务分类
    // @param  string   $supplement  服务补充内容
    // @param  integer  $status      服务状态
    //
    // @return  integer  成功 - 新增服务ID号，失败 - 错误编码
    //
    public function provide($uid,
                            $content,
                            $duration,
                            $price,
                            $category,
                            $supplement,
                            $status = 0) {
        $data = array(
            "uid"        => $uid,
            "username"   => get_username(),
            "content"    => $content,
            "duration"   => $duration, 
            "supplement" => $supplement,
            "price"      => $price,
            "status"     => $status
        );

        if ($this->create($data)) {
            $id = $this->add();
            if ($id > 0) {
                D('ServiceCategory')->update($id, $category);
                return $id;
            }
            return 0;
        } else {
            return $this->getError();
        }
    }

    // @brief  update  更新服务
    //
    // @param  integer  $uid         用户ID号
    // @param  integer  $servie_id   服务ID号
    // @param  string   $content     服务内容
    // @param  integer  $duration    服务时长
    // @param  double   $price       收费价格
    // @param  array    $category    服务分类
    // @param  string   $supplement  服务补充内容
    // @param  integer  $status      服务状态
    //
    // @return  integer  成功 - 新增服务ID号，失败 - 错误编码
    //
    public function update($uid,
                           $service_id,
                           $content,
                           $duration,
                           $price,
                           $category,
                           $supplement,
                           $status = 0) {
        $service = $this->field("uid")->find($service_id);
        if (!is_array($service)) {
            return -8; // 服务ID号无效
        }
        if ($service['uid'] != $uid) {
            return -9; // 用户无权操作
        }

        $data = array(
            "id"         => $service_id,
            "content"    => $content,
            "duration"   => $duration, 
            "supplement" => $supplement,
            "price"      => $price,
            "status"     => $status
        );

        $ret = $this->save($data);
        if ($ret >= 0) {
            D('ServiceCategory')->update($service_id, $category);
            return $ret;
        } else {
            return $this->getError();
        }
    }
}
