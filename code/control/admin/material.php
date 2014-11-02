<?php

!defined('IN_SITE') && exit('Access Denied');

class admin_materialcontrol extends base {

    function admin_materialcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('register_material');
        $this->load('user');
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
