<?php

!defined('IN_SITE') && exit('Access Denied');

require_once(WEB_ROOT . '/code/third/xunsearch/sdk/php/lib/XS.php');

class materialmodel {
    public function __construct(&$db) {
        $this->_db = & $db;
        $this->_category = new Category();

        $xs = new XS('material');
        $this->_search = $xs->search;
        $this->_index = $xs->index;
    }

    public function get($mid) {
        return $this->_db->fetch_first("SELECT * FROM material WHERE id='$mid'");
    }

    public function get_list($start=0, $limit='', $type="major",
                                                  $status=MATERIAL_STATUS_PUBLISH) {
        $sql = "SELECT * FROM `material` WHERE status='$status' AND `type`='$type'" .
                                       " ORDER BY `time` DESC";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $material_list = $this->_db->fetch_all($sql);
        foreach ($material_list as &$material) {
            $material['format_time'] = tdate($material['time']);
            $material['desc_content'] = strip_tags($material['description']);
            $images_arr = fetch_img_tag($material['description']);
            $material['desc_images'] = $images_arr[0];
        }
        return $material_list;
    }

    public function get_by_mids($mids) {
        $sql = "SELECT * FROM `material` WHERE id IN ('" . implode("','", $mids) . "')";
        return $this->_db->fetch_all($sql);
    }

    public function get_all_material_num() {
        return $this->_db->fetch_total('material');
    }

    public function get_status_num($status) {
        return $this->_db->fetch_total("material", "`status`='$status'");
    }

    public function add($uid, $username, $picture, $title, $description, $price, $site_url,
                        $access_code, $type="major", $status=MATERIAL_STATUS_APPLY) {
        $time = time();
        $this->_db->query("INSERT INTO material SET uid='$uid',username='$username'," .
                         "picture='$picture',title='$title',description='$description'," . 
                         "price='$price',site_url='$site_url',access_code='$access_code'," .
                         "time='$time',`type`='$type',status='$status'");
        $insert_id = $this->_db->insert_id();
        if ($insert_id > 0) {
            $this->add_index($insert_id);
        }
        return $insert_id;
    }

    public function update_avg_score($mid, $avg_score) {
        $this->_db->query("UPDATE `material` SET `avg_score`='$avg_score' WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    public function update_sold_num($mid, $delta=1) {
        $this->_db->query("UPDATE `material` SET `sold_num`=`sold_num`+($delta) WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    public function update_view_num($mid, $delta=1) {
        $this->_db->query("UPDATE material SET `view_num`=`view_num`+($delta) WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    public function update_access($mid, $site_url, $access_code) {
        $sql = "UPDATE material SET site_url='$site_url',access_code='$access_code' WHERE id=$mid";
        $this->_db->query($sql);
        return $this->_db->affected_rows();
    }

    public function get_user_total_materials($uid) {
        return $this->_db->fetch_total("material", " `uid`='$uid' " );
    }

    public function list_by_uid($uid, $start=0, $limit=10) {
        $sql = "SELECT * FROM material WHERE uid=$uid ORDER BY `time` DESC LIMIT $start,$limit";
        $material_list = $this->_db->fetch_all($sql);
        foreach ($material_list as &$material) {
            $material['format_time'] = tdate($material['time']);
        }
        return $material_list;
    }

    public function update($mid, $title, $description, $price, $site_url, $access_code) {
        $time = time();
        $this->_db->query("UPDATE material SET title='$title',description='$description'," .
                         "price='$price',site_url='$site_url'," .
                         "access_code='$access_code',time=$time WHERE id=$mid");
        $affected_rows = $this->_db->affected_rows();
        if ($affected_rows > 0) {
            $this->add_index($mid, true);
        }
        return $affected_rows;
    }

    public function update_type($mid, $type) {
        $this->_db->query("UPDATE material set `type`='$type' WHERE id='$mid'");
        return $this->_db->affected_rows();
    }

    public function update_picture($mid, $picture) {
        $this->_db->query("UPDATE `material` set `picture`='$picture' WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    // 更新求助状态
    public function update_status($mid, $status) {
        $this->_db->query("UPDATE `material` set `status`=$status WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    public function update_auto_delivery($mid, $auto) {
        $this->_db->query("UPDATE `material` set `auto_delivery`=$auto` WHERE `id`=$mid");
        return $this->_db->affected_rows();
    }

    // 搜索
    public function search($query, $highlight=true, $start=0, $limit=10) {
        $result = array();

        $docs = $this->_search->setQuery($query)->setLimit($limit, $start)->search();
        $tot_num = $this->_search->lastCount; // 符合query的总条数
        foreach ($docs as $doc) {
            $result[] = $this->get_doc_result($doc, $highlight);
        }
        return array("tot_num" => $tot_num, "material_list" => $result);
    }

    // 重建索引
    public function build_index() {
        $this->_index->clean();
        $this->_index->openBuffer();

        $status = MATERIAL_STATUS_PUBLISH;
        $mid_list = $this->_db->fetch_all("SELECT id FROM material WHERE status='$status'", "", "id");
        foreach ($mid_list as $mid) {
            $this->add_index($mid);
        }

        $this->_index->closeBuffer();
    }

    private function add_index($mid, $update=false) {
        if ($mid > 0) {
            $m = $this->get($mid);
            $sql = "SELECT * FROM material_category WHERE material_id='$mid'";
            $c = $this->_db->fetch_all($sql);

            $doc = new XSDocument;
            $category_str = $this->_category->format_by_ids($c);

            $doc->setFields(array("mid"         => $m["id"],
                                  "title"       => $m["title"],
                                  "description" => strip_tags($m["description"]),
                                  "uid"         => $m["uid"],
                                  "username"    => $m["username"],
                                  "category"    => $category_str,
                                  "datetime"    => $m["time"]));
            if ($update) {
                $this->_index->update($doc);
            } else {
                $this->_index->add($doc);
            }
        }
    }

    private function get_doc_result($doc, $highlight=false) {
        if ($highlight) {
            $title = $this->_search->highlight($doc->title);
            $description = $this->_search->highlight($doc->description);
            $category = $this->_search->highlight($doc->category);
        } else {
            $title = $doc->title;
            $description = $doc->description;
            $category = $doc->category;
        }

        $result = array("mid"         => $doc->mid,
                        "title"       => $title,
                        "description" => $description,
                        "uid"         => $doc->uid,
                        "username"    => $doc->username,
                        "category"    => $category,
                        "datetime"    => $doc->datetime,
                        "format_time" => tdate($doc->datetime),
                        "rank"        => $doc->rank(),
                        "percent"     => $doc->percent());
        return $result;
    }

    private $_db;
    private $_search;
    private $_index;
    private $_category;
}

?>
