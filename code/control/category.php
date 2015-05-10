<?php

!defined('IN_SITE') && exit('Access Denied');

class categorycontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->_category = new Category();
    }

    public function onbuild_index() {
        $category_data_fname = WEB_ROOT . '/private/data/school_department_major.txt';
        $this->_category->build_index($category_data_fname);
        echo "DONE";
    }

    //===================================================================================
    //==========================  JSON Format Request/Response ==========================
    //===================================================================================

    // @onajax_fetch_list    [根据ID号获取相应的信息列表]
    // @request type         [GET]
    //
    // @param[in]         id [分类的ID号]
    // @param[in]    id_type [ID号的类别，默认为"id"]
    // @param[in]      start [搜索结果的起始条数]
    // @param[in]      limit [搜索结果最多的条数]
    //
    // @return          成功 [success为true, list为符合条件的分类列表]
    //                  失败 [success为false, error为相应的错误码]
    //
    // @error            101 [参数无效]
    // @error            102 [无法找到该ID号的信息]
    public function onajax_fetch_list() {
        $res = array();
        $category_id = $this->post['id'];
        if (empty($category_id)) {
            $res['success'] = false;
            $res['error'] = 101; // 参数无效
            echo json_encode($res);
            return;
        }

        $id_type = "id";
        if (isset($this->post['id_type'])) {
            $id_type = $this->post['id_type'];
        }
        $start = 0;
        $limit = 10;
        isset($this->post['start']) && $start = intval($this->post['start']);
        isset($this->post['limit']) && $limit = intval($this->post['limit']);

        $category_info_arr = $this->_category->get_by_id($category_id, $id_type, $start, $limit);
        if (array_key_exists($category_id, $category_info_arr)) {
            $res['success'] = true;
            $res['list'] = $category_info_arr[$category_id];
        } else {
            $res['success'] = false;
            $res['error'] = 102; // 无法找到改ID号
        }
        echo json_encode($res);
    }

    // @onajax_search        [搜索符合条件的类别]
    // @request type         [GET]
    //
    // @param[in]      query [中文查询语句]
    // @param[in]      start [搜索结果的起始条数]
    // @param[in]      limit [搜索结果最多的条数]
    //
    // @return          成功 [success为true, list为符合条件的分类列表]
    public function onajax_search() {
        $query = $this->post['query'];
        $start = 0;
        $limit = 10;
        isset($this->post['start']) && $start = intval($this->post['start']);
        isset($this->post['limit']) && $limit = intval($this->post['limit']);

        $category_arr = $this->_category->search($query, $start, $limit);

        $res = array();
        $res['success'] = true;
        $res['list'] = $category_arr;
        echo json_encode($res);
    }

    private $_category;
}

?>
