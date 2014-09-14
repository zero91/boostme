<?php

!defined('IN_SITE') && exit('Access Denied');

class resourcecontrol extends base {
    function resourcecontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('userresume');
    }

    function onrequest() {
        $data_type = $this->get[2];
        $uid = intval($this->get[3]);

        if ($uid != $this->user['uid']) {
            return;
        }

        $resume = $_ENV['userresume']->get_by_uid($uid);

        $filepath = "";
        if ($data_type == 'ID') {
            $filepath = WEB_ROOT . "/" . $resume['ID_path'];

        } else if ($data_type == 'studentID') {
            $filepath = WEB_ROOT . "/" . $resume['studentID'];

        } else if ($data_type == 'resume') {
            $filepath = WEB_ROOT . "/" . $resume['resume_path'];
        }

        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$data_type");

        if (file_exists($filepath)) {
            echo readfromfile($filepath);
        }
    }
}

?>
