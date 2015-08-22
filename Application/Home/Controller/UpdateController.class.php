<?php
namespace Home\Controller;

class UpdateController extends HomeController {

    // @brief  category  更新前端js读取的分类信息数据
    //
    public function category() {
        // TODO 权限控制
        $content = file_get_contents(MODULE_PATH . "Data/school_department_major.txt");
        $lines = explode("\n", $content);

        $data_dict = array();
        foreach ($lines as $line) {
            list($region, $school, $dept, $major_str) = explode("\t", $line);
            $major_list = explode(",", $major_str);

            if (!array_key_exists($region, $data_dict)) {
                $data_dict[$region] = array();
            }
            if (!array_key_exists($school, $data_dict[$region])) {
                $data_dict[$region][$school] = array();
            }
            $data_dict[$region][$school][$dept] = $major_list;
        }

        $res = array();
        foreach ($data_dict as $region => $school_dict) {
            $region_num = array_push($res, array("name" => $region, "school" => array()));
            $cur_region_info = &$res[$region_num - 1]["school"];

            foreach ($school_dict as $school => $dept_dict) {
                $school_num = array_push($cur_region_info,
                                                array("name" => $school, "dept" => array()));
                $cur_school_info = &$cur_region_info[$school_num - 1]["dept"];

                foreach ($dept_dict as $dept => $major_list) {
                    array_push($cur_school_info, array("name" => $dept, "major" => $major_list));
                }
            }
        }

        $js_category_path = C('PUBLIC_RESOURCE_PATH') . "js/data/category.data.js";
        $fout = fopen($js_category_path, 'w');
        fwrite($fout, "var school_data = " . json_encode($res) . ";");
        $this->ajaxReturn(array("success" => true));
    }
}
