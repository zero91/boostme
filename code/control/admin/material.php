<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_materialcontrol extends base {
    public function __construct(& $get, & $post) {
        parent::__construct($get, $post);
        $this->load('material');
        $this->load('material_category');

        $this->load('register_material');
        $this->load('user');
    }

    public function ondefault() {
        $statistics = $this->fromcache('statistics');
        $pagesize = $this->setting['list_default'];

        $apply_page = max(1, intval($this->get[2]));
        $tot_apply_material_num = $_ENV['material']->get_status_num(MATERIAL_STATUS_APPLY);
        $apply_material_list = $_ENV['material']->get_list(($apply_page - 1) * $pagesize, $pagesize, MATERIAL_STATUS_APPLY);
        foreach ($apply_material_list as &$t1_material) {
            $t1_material['category'] = $_ENV['material_category']->get_by_mid($t1_material['id']);
        }
        //$apply_departstr = page($statistics['all_material_num'], $pagesize, $apply_page, "admin_material/default");
        $apply_departstr = page($tot_apply_material_num, $pagesize, $apply_page, "admin_material/default");

        $accept_page = max(1, intval($this->get[3]));
        $tot_accept_material_num = $_ENV['material']->get_status_num(MATERIAL_STATUS_PUBLISH);
        $accept_material_list = $_ENV['material']->get_list(($accept_page - 1) * $pagesize, $pagesize, MATERIAL_STATUS_PUBLISH);
        foreach ($accept_material_list as &$t2_material) {
            $t2_material['category'] = $_ENV['material_category']->get_by_mid($t2_material['id']);
        }
        $accept_departstr = page($tot_accept_material_num, $pagesize, $accept_page, "admin_material/default/$apply_page");
        include template('material', 'admin');
    }

    public function onaccept() {
        $mid = $this->get[2];

        $res = array();
        if ($mid > 0) {
            $affected_rows = $_ENV['material']->update_status($mid, MATERIAL_STATUS_PUBLISH);
            if ($affected_rows > 0) {
                $material = $_ENV['material']->get($mid);
                $subject = "您的资料\"" . $material['title'] . "\"通过了审核";
                $content = '<p>恭喜您，您上传的资料<a target="_blank" href="' . SITE_URL . "material/view/$mid\">" . $material['title'] . "</a>通过了审核！</p>";
                $this->send("", 0, $material['uid'], $subject, $content, true);

                $res['success'] = true;
            } else {
                $res['error'] = 102; // 更新失败
            }
        } else {
            $res['error'] = 101; // 无效material ID
        }
        echo json_encode($res);
    }

    function onregister() {
        $type = "material/register";

        $reg_list = $_ENV['register_material']->get_list();

        $reg_total_num = $_ENV['register_material']->get_total_num();
        $page = max(1, intval($this->get[2]));
        $pagesize = 10;
        $reg_list = $_ENV['register_material']->get_list(($page - 1) * $pagesize, $pagesize);
        $departstr = page($reg_total_num, $pagesize, $page, "admin_material/register");
        include template('material_register', 'admin');
    }

    function onajax_existed() {
        $id = $this->get[2];
        if (empty($id)) {
            exit("-1");
        }

        $affected_rows = $_ENV['register_material']->update_material_existed($id);
        if ($affected_rows > 0) {
            exit("1");
        }
        exit("-1");
    }
}

?>
