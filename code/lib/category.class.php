<?php

require_once(WEB_ROOT . '/code/third/xunsearch/sdk/php/lib/XS.php');

class Category {
    public function __construct() {
        $xs = new XS('category');
        $this->_search = $xs->search;
        $this->_index = $xs->index;
    }

    // 创建索引
    public function build_index($category_data_fname) {
        $source_data = file($category_data_fname, FILE_IGNORE_NEW_LINES);

        $category = array();
        foreach ($source_data as $line_num => $line) {
            list($region, $school, $dept, $major_list) = explode("\t", trim($line));
            $category[$region][$school][$dept] = explode(",", $major_list);
        }

        $this->_index->clean();
        $this->_index->openBuffer();
        // region : 2
        // school : 4
        // dept : 3
        // major : 2

        $region_id = 0;
        foreach ($category as $region => $school_dict) {
            ++$region_id;
            $region_id_str = sprintf("R%02s", $region_id);
            $region_doc = new XSDocument;
            $region_doc->setFields(array("id"        => $region_id_str,
                                         "region_id" => $region_id_str,
                                         "region"    => $region,
                                         "school_id" => "",
                                         "school"    => "",
                                         "dept_id"   => "",
                                         "dept"      => "",
                                         "major_id"  => "",
                                         "major"     => ""));
            $this->_index->add($region_doc);

            $school_id = 0;
            foreach ($school_dict as $school => $dept_dict) {
                ++$school_id;
                $school_id_str = sprintf("S%02s%04s", $region_id, $school_id);
                $school_doc = new XSDocument;
                $school_doc->setFields(array("id"        => $school_id_str,
                                             "region_id" => $region_id_str,
                                             "region"    => $region,
                                             "school_id" => $school_id_str,
                                             "school"    => $school,
                                             "dept_id"   => "",
                                             "dept"      => "",
                                             "major_id"  => "",
                                             "major"     => ""));
                $this->_index->add($school_doc);

                $dept_id = 0;
                foreach ($dept_dict as $dept => $major_list) {
                    ++$dept_id;
                    $dept_id_str = sprintf("D%02s%04s%03s", $region_id, $school_id, $dept_id);
                    $dept_doc = new XSDocument;
                    $dept_doc->setFields(array("id"        => $dept_id_str,
                                               "region_id" => $region_id_str,
                                               "region"    => $region,
                                               "school_id" => $school_id_str,
                                               "school"    => $school,
                                               "dept_id"   => $dept_id_str,
                                               "dept"      => $dept,
                                               "major_id"  => "",
                                               "major"     => ""));
                    $this->_index->add($dept_doc);

                    foreach ($major_list as $major_ind => $major) {
                        $major_id_str = sprintf("M%02s%04s%03s%02s", $region_id, $school_id,
                                                                  $dept_id,  $major_ind + 1);
                        $major_doc = new XSDocument;
                        $major_doc->setFields(array("id"        => $major_id_str,
                                                    "region_id" => $region_id_str,
                                                    "region"    => $region,
                                                    "school_id" => $school_id_str,
                                                    "school"    => $school,
                                                    "dept_id"   => $dept_id_str,
                                                    "dept"      => $dept,
                                                    "major_id"  => $major_id_str,
                                                    "major"     => $major));
                        $this->_index->add($major_doc);
                    }
                }
            }
        }
        $this->_index->closeBuffer();
    }

    // 根据ID号以及ID号类型获取相应的ID号信息
    public function get_by_id($category_id, $id_type="id", $start=0, $limit=10) {
        $result = array();

        (!is_array($category_id)) && $category_id = array($category_id);
        foreach ($category_id as $id) {
            $query = $id_type . ":" . $id;
            $docs = $this->_search->setQuery($query)->setLimit($limit, $start)->search();
            if (count($docs) == 0) {
                continue;
            }
            foreach ($docs as $doc) {
                $result[$id][] = $this->get_doc_result($doc);
            }
        }
        return $result;
    }

    // 根据中文信息搜索出符合相应内容的类别信息列表
    public function search($category, $start=0, $limit=10) {
        $result = array();

        $docs = $this->_search->setQuery($category)->setLimit($limit, $start)->search();
        foreach ($docs as $doc) {
            $result[] = $this->get_doc_result($doc);
        }
        return $result;
    }

    public function format_by_ids($category) {
        $category_str_arr = array();
        foreach ($category as $c) {
            $target_id = "";
            if ($c["major_id"] != "")       $target_id = $c["major_id"];
            else if ($c["dept_id"] != "")   $target_id = $c["dept_id"];
            else if ($c["school_id"] != "") $target_id = $c["school_id"];
            else if ($c["region_id"] != "") $target_id = $c["region_id"];
            else continue;

            $info = $this->get_by_id($target_id);
            if (count($info[$target_id]) > 0) {
                $category_str_arr[] = $this->glue_category($info[$target_id][0]);
            }
        }
        return implode(",", $category_str_arr);
    }

    private function glue_category($category) {
        $glue_str = $category['region'];
        $glue_str .= " " . $category['school'];
        $glue_str .= " " . $category['dept'];
        $glue_str .= " " . $category['major'];
        return $glue_str;
    }

    private function get_doc_result($doc) {
        $result = array("region_id" => $doc->region_id,
                        "region"    => $doc->region,
                        "school_id" => $doc->school_id,
                        "school"    => $doc->school,
                        "dept_id"   => $doc->dept_id,
                        "dept"      => $doc->dept,
                        "major_id"  => $doc->major_id,
                        "major"     => $doc->major);
        return $result;
    }

    private $_index;
    private $_search;
}

?>
